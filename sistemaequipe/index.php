<?php
require '../cnfg/jwt.cnfg.php';
require '../sistemaequipe/mandararquivo.php';

$fotoequipe = $_FILES['fotoequipe'];

if (isset($_POST['nomeequipe']) && isset($_POST['tipo']) && isset($_POST['senhaequipe'])) {
  if (isset($fotoequipe)){
    $foto = 'https://ecoswim.com.br/api/avatares%20das%20equipes/'.$_POST['nomeequipe'].'.jpg';
  }else {
    $foto = 'https://ecoswim.com.br/api/avatares%20das%20equipes/semfoto.jpg';
  }
  try {
    $stmt = $conexao->prepare("INSERT INTO equipes (nome, senha, tipo, foto) VALUES (?, ?, ?, ?);");
    $stmt->bind_param("ssss", $_POST['nomeequipe'], $_POST['senhaequipe'], $_POST['tipo'], $foto);

    if ($stmt->execute() == false) {
      throw new Exception('Stmt falhou');
      http_response_code(500);
    }else {
        http_response_code(201);
    }

    upload_foto($fotoequipe, $_POST['nomeequipe']);

  } catch (\Exception $e) {
    throw $e;
  }
}elseif (isset($_GET['idequipe']) && isset($_GET['senhaequipe'])) {
  $stmt = $conexao->prepare("SELECT senha FROM equipes WHERE id = ? LIMIT 1");
  $stmt->bind_param("s", $_GET['idequipe']);
  $stmt->execute();
  $result = $stmt->get_result();
  $senhaequipe = $result->fetch_all(MYSQLI_ASSOC)[0]['senha'];

  if ($_GET['senhaequipe'] == $senhaequipe) {
    $stmt = $conexao->prepare("UPDATE usuarios SET equipe = ? WHERE id = ?");
    $stmt->bind_param("ii", $_GET['idequipe'], $_GET['id']);

    if ($stmt->execute() == false) {
      throw new Exception('Stmt falhou');
      http_response_code(500);
    }else {
        http_response_code(201);
    }
  }else {
    http_response_code(401);
  }

} elseif (isset($_GET['numerodeequipes'])) {
  $stmt = $conexao->prepare("SELECT COUNT(nome) AS numerodeequipes FROM equipes");
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC)[0];
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);
}elseif (isset($_GET['nomeequipe'])) {
  $stmt = $conexao->prepare("SELECT equipes.id, equipes.foto, equipes.nome, equipes.tipo, equipes.horario, COUNT(usuarios.equipe) AS membros
    FROM equipes INNER JOIN usuarios ON equipes.id = usuarios.equipe WHERE equipes.nome LIKE CONCAT('%', (?), '%') GROUP BY equipes.id");
  $stmt->bind_param("s", $_GET['nomeequipe']);
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);
}else {
  $stmt = $conexao->prepare("SELECT equipes.id, equipes.foto, equipes.nome, equipes.tipo, equipes.horario, COUNT(usuarios.equipe) AS membros
                          	FROM equipes INNER JOIN usuarios ON equipes.id = usuarios.equipe GROUP BY equipes.id");
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);

}

$stmt->close();
$conexao->close();
