<?php
//Dados do banco de dados
$servername = "mysql.ecoswim.com.br";
$dBUsername = "ecoswim02";
$dBPassword = "wetrats02";
$dBName = "ecoswim02";

//Conexao com o banco de dados
$conexao = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);
$conexao->set_charset("utf8");

//Confere se a conexao funcionou
if (mysqli_connect_errno()) {
  json_encode("Não foi possivel acessar o banco de dados: %s", mysqli_connect_error());
  exit();
}


header("Content-Type: application/json; charset=UTF-8");
$allowed_domains = [
    "http://www.ecoswim.com.br",
    "http://ecoswim.com.br",
    "https://www.ecoswim.com.br",
    "https://ecoswim.com.br",
    "https://ipe.colabore.org/",
  ];

if (in_array($_SERVER['HTTP_ORIGIN'], $allowed_domains)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Methods: OPTIONS, GET,PUT,POST,DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
