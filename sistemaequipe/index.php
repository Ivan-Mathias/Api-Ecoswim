<?php
require '../cnfg/jwt.cnfg.php';
require '../sistemaequipe/mandararquivo.php';

$fotoequipe = $_FILES['fotoequipe'];

if (isset($_POST['id']) && isset($_POST['nomeequipe']) && isset($_POST['tipo']) && isset($_POST['senhaequipe'])) {
  try {
    $conexao->autocommit(FALSE);

    $stmt = $conexao->prepare("INSERT INTO equipes (nome, senha, tipo) VALUES (?, ?, ?);");
    $stmt->bind_param("sss", $_POST['nomeequipe'], $_POST['senhaequipe'], $_POST['tipo']);

    if ($stmt->execute() == false) {
      throw new Exception('Stmt falhou');
      http_response_code(500);
    }

    $stmtid = $conexao->prepare("UPDATE usuarios SET equipe = ?, chefe = TRUE WHERE id = ?");
    $stmtid->bind_param("ii", $idequipe, $_POST['id']);
    $idequipe = $conexao->insert_id;

    if ($stmtid->execute() == false) {
      throw new Exception('Stmt id falhou');
      http_response_code(500);
    }

    if (isset($fotoequipe)){
      $foto = 'https://ecoswim.com.br/api/avatares%20das%20equipes/'.$idequipe.'.jpg';
      upload_foto($fotoequipe, $idequipe);

      $stmtfoto = $conexao->prepare("UPDATE equipes SET foto = ? WHERE id = ?");
      $stmtfoto->bind_param("si", $foto, $idequipe);

      if ($stmtfoto->execute() == false) {
        throw new Exception('Stmt foto falhou');
        http_response_code(500);
      }
    }

  } catch (\Exception $e) {
    $conexao->rollback();
    throw $e;
  }finally {
    isset($stmtid) && $stmtid->close();
    isset($stmtfoto) && $stmtfoto->close();
    $conexao->autocommit(TRUE);
    $outp['id'] = $idequipe;
    echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
    http_response_code(201);
  }
}elseif (isset($_POST['idequipe']) && isset($_POST['alterardados'])) {
  if (isset($_POST['nomeequipe']) && isset($_POST['tipo']) && isset($_POST['senhaequipe'])) {
    if (isset($fotoequipe)) {
      upload_foto($fotoequipe, $_POST['idequipe']);
      $foto = 'https://ecoswim.com.br/api/avatares%20das%20equipes/'.$_POST['idequipe'].'.jpg';
    }else {
      $foto = 'https://ecoswim.com.br/api/avatares%20das%20equipes/semfoto.jpg';
    }
    $stmt = $conexao->prepare("UPDATE equipes set nome = ?, senha = ?, tipo = ?, foto = ? WHERE id = ?;");
    $stmt->bind_param("ssssi", $_POST['nomeequipe'], $_POST['senhaequipe'], $_POST['tipo'], $foto, $_POST['idequipe']);

    if ($stmt->execute() == false) {
      throw new Exception('Stmt falhou');
      http_response_code(500);
    }else {
      http_response_code(201);
    }

  }
} elseif (isset($_GET['idequipe']) && isset($_GET['senhaequipe'])) {
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

}elseif (isset($_GET['idequipe']) && isset($_GET['dados'])) {
  switch ($_GET['dados']) {
    case 'equipe':
      $stmt = $conexao->prepare("SELECT * FROM equipes WHERE id = ?");
      break;
    case 'membros':
      $stmt = $conexao->prepare("SELECT id, nome, inscricao FROM usuarios WHERE equipe =  ?");
      break;
    case 'extras':
      // code...
      break;
  }
  $stmt->bind_param("i", $_GET['idequipe']);
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);
} elseif (isset($_GET['numerodeequipes'])) {
  $stmt = $conexao->prepare("SELECT COUNT(nome) AS numerodeequipes FROM equipes");
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC)[0];
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);
}elseif (isset($_GET['nomeequipe'])) {
  $stmt = $conexao->prepare("SELECT equipes.id, equipes.foto, equipes.nome, equipes.tipo, equipes.horario, COUNT(usuarios.equipe) AS membros
    FROM equipes INNER JOIN usuarios ON equipes.id = usuarios.equipe WHERE equipes.nome LIKE CONCAT('%', (?), '%') GROUP BY equipes.id ORDER BY membros DESC");
  $stmt->bind_param("s", $_GET['nomeequipe']);
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);
}else {
  $stmt = $conexao->prepare("SELECT equipes.id, equipes.foto, equipes.nome, equipes.tipo, equipes.horario, COUNT(usuarios.equipe) AS membros
                          	FROM equipes INNER JOIN usuarios ON equipes.id = usuarios.equipe GROUP BY equipes.id ORDER BY membros DESC");
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode($outp, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
  http_response_code(200);

}

$stmt->close();
$conexao->close();
