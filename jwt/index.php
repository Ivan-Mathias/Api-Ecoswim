<?php
require '../cnfg/jwt.cnfg.php';

if (isset($_GET['id']) && isset($_GET['nome']) && isset($_GET['equipe']) && isset($_GET['email']) && isset($_GET['chefe']) && isset($_GET['admin'])) {
  echo createLoginToken($_GET['id'], $_GET['nome'], $_GET['equipe'], $_GET['email'], $_GET['chefe'], isset($_GET['admin']), $key);

}elseif (!empty(getAuthorizationHeader())) {
  $received = explode(".", getAuthorizationHeader());

   if (checkToken($received, $key)) {
     $resposta['tokenCorreto'] = true;
     echo json_encode($resposta);
     http_response_code(200);
   }else {
     $resposta['tokenCorreto'] = false;
     echo json_encode($resposta);
     http_response_code(401);
   }
}

 ?>
