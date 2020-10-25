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
        $resposta['id'] = $conexao->insert_id;
        echo json_encode($resposta);
        http_response_code(201);
    }

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

} elseif (isset($post['id']) && ($post['atualizar'])) {
  $senha = password_hash($post['senha'], PASSWORD_DEFAULT);
  if (isset($post['nome']) && isset($post['senha'])) {
    $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, senha = ? WHERE id = ?");
    $stmt->bind_param("ssi", $post['nome'], $senha, $post['id']);
  } elseif (isset($post['nome'])) {
    $stmt = $conexao->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $post['nome'], $post['id']);
  } elseif (isset($post['senha'])) {
    $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $senha, $post['id']);
  }
  $stmt->execute();

  if ($stmt->execute() == false) {
    throw new Exception('Stmt falhou');
    http_response_code(500);
  }else {
    http_response_code(201);
  }
} elseif (isset($post['sair']) && isset($post['id'])) {
  $stmt = $conexao->prepare("UPDATE usuarios SET equipe = NULL WHERE id = ?");
  $stmt->bind_param("i", $post['id']);
  $stmt->execute();

  if ($stmt->execute() == false) {
    throw new Exception('Stmt falhou');
    http_response_code(500);
  }else {
    http_response_code(201);
  }
} elseif (isset($post['id']) && (isset($post['atualizarinscricao']))) {
  $stmt = $conexao->prepare("UPDATE usuarios SET inscricao = ? WHERE id = ?");
  $stmt->bind_param("si", $post['atualizarinscricao'], $post['id']);
  $stmt->execute();

  if ($stmt->execute() == false) {
    throw new Exception('Stmt falhou');
    http_response_code(500);
  }else {
    http_response_code(201);
  }
} elseif (isset($_GET['numerodepessoasinscritas'])) {
  $stmt = $conexao->prepare("SELECT COUNT(nome) AS numerodepessoasinscritas FROM usuarios");
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC)[0];
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);
}

$stmt->close();
$conexao->close();

?>
