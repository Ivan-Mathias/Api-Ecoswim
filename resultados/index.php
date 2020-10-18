<?php
require '../cnfg/db.cnfg.php';


$data = json_decode(file_get_contents('php://input'), true);

if (isset($_GET["ano"])) {
  if (isset($_GET['geral'])) {
    if (isset($_GET["limitar"])) {
      $stmt = $conexao->prepare("SELECT Equipe, Metragem FROM resultados WHERE Ano = (?) ORDER BY Metragem DESC LIMIT ?");
      $stmt->bind_param("ii", $ano, $limitar);
      $limitar = $_GET['limitar'];
    }else {
        $stmt = $conexao->prepare("SELECT Equipe, Metragem FROM resultados WHERE Ano = (?) ORDER BY Metragem DESC");
        $stmt->bind_param("i", $ano);
    }

  }elseif (isset($_GET['equipe'])) {
    if (isset($_GET["limitar"])) {
      $stmt = $conexao->prepare("SELECT Equipe, Membros FROM resultados WHERE Ano = (?) ORDER BY Membros DESC LIMIT ?");
      $stmt->bind_param("ii", $ano, $limitar);
      $limitar = $_GET['limitar'];
    }else {
        $stmt = $conexao->prepare("SELECT Equipe, Membros FROM resultados WHERE Ano = (?) ORDER BY Membros DESC");
        $stmt->bind_param("i", $ano);
    }

  }elseif (isset($_GET['horario'])) {
    $stmt = $conexao->prepare("SELECT Equipe, Metragem FROM resultados WHERE Ano = (?) AND Horário = (?) ORDER BY Metragem DESC;");
    $stmt->bind_param("ii", $ano, $horario);
    $horario = $_GET['horario'];

  }elseif(isset($_GET["limitar"])){
    $stmt = $conexao->prepare("SELECT * FROM resultados WHERE Ano = (?) ORDER BY Metragem DESC LIMIT ?");
    $stmt->bind_param("ii", $ano, $limitar);
    $limitar = $_GET['limitar'];
  }else {
    $stmt = $conexao->prepare("SELECT * FROM resultados WHERE Ano = (?) ORDER BY Metragem DESC");
    $stmt->bind_param("i", $ano);
  }
  $ano = $_GET["ano"];
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC);
}else {
  $stmt = $conexao->prepare("SELECT MAX(Ano) as AnoMaisRecente FROM resultados");
  $stmt->execute();
  $result = $stmt->get_result();
  $outp = $result->fetch_all(MYSQLI_ASSOC)[0];
}


echo json_encode($outp);
http_response_code(200);
$stmt->close();
$conexao->close();


 ?>
