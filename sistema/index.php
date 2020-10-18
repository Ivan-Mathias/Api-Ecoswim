<?php
require '../cnfg/db.cnfg.php';

$post = json_decode(file_get_contents('php://input'), true);

if (isset($post['nome']) && isset($post['email']) && isset($post['senha'])) {
  try {
    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?);");
    $stmt->bind_param("sss", $post['nome'], $post['email'], $hash);
    $hash = password_hash($post['senha'], PASSWORD_DEFAULT);

    if ($stmt->execute() == false) {
      if ($conexao->errno == 1062) {
        $resposta['error'] = "Email já cadastrado";
        echo json_encode($resposta);
        http_response_code(409);
      } else {
        throw new Exception('Stmt falhou');
        http_response_code(500);
      }
    }else {
        http_response_code(201);
    }

    $stmt->close();
    $conexao->close();

  } catch (\Exception $e) {
    throw $e;
  }

} elseif (isset($_GET['email']) && isset($_GET['senha'])) {
  $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email=? LIMIT 1;");
  $stmt->bind_param("s", $_GET['email']);
  $stmt->execute();
  $resultado = $stmt->get_result();
  $usuario = $resultado->fetch_assoc();
  if (empty($usuario)) {
    $usuario['error'] = "Usuário não encontrado";
    http_response_code(404);
  }else {
    $senhaCerta = password_verify($_GET['senha'], $usuario['senha']);
    if ($senhaCerta) {
      unset($usuario['senha']);
    } else {
      unset($usuario);
      $usuario['error'] = "Senha incorreta";
      http_response_code(401);
    }
  }

  echo json_encode($usuario);
}

?>
