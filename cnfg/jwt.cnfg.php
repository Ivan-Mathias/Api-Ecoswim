<?php
require '../cnfg/db.cnfg.php';

$GLOBALS['key'] = 'pintomole';

function base64_encode_url($string) {
    return str_replace(['+','/','='], ['-','_',''], base64_encode($string));
}

function createLoginToken($id, $nome, $sobrenome, $email, $avatar) {
    $payload = [
      "sub" => $id,
      "nome" => $nome,
      "sobrenome" => $sobrenome,
      "email" => $email,
      "avatar" => $avatar,
      "iat" => time(),
    ];

    return createToken($payload);
}

function createResetToken($email) {
  $payload = [
    "email" => $email,
    "iat" => time(),
    "exp" => time() + 900,
  ];

  return createToken($payload);
}

function createToken ($payload) {

  $header = [
    "alg" => "HS256",
    "typ" => "JWT"
  ];

  $header = base64_encode_url(json_encode($header));
  $payload = base64_encode_url(json_encode($payload));

  $signature = base64_encode_url(hash_hmac('sha256', "$header.$payload", $GLOBALS['key'], true));

  return "$header.$payload.$signature";
}

function checkToken ($received) {
  $header = $received[0];
  $payload = $received[1];
  $signature = $received[2];

  $header = str_replace("Bearer ", "", $header);

  $exp = json_decode(base64_decode($payload), true)['exp'];

  if (isset($exp) && time() > $exp ) {
    return;
  }

  $validation = base64_encode_url(hash_hmac('sha256', "$header.$payload", $GLOBALS['key'], true));

  return $signature == $validation;
}

function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

 ?>
