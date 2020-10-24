<?php
require '../cnfg/jwt.cnfg.php';

$post = json_decode(file_get_contents('php://input'), true);

if (isset($post['email']) && isset($post['senha'])) {
  try {
    $stmt = $conexao->prepare("UPDATE users SET senha = ? WHERE email=?;");
    $stmt->bind_param("ss", $senha, $post['email']);
    $senha = password_hash($post['senha'], PASSWORD_DEFAULT);
    $stmt->execute();

    if ($stmt->execute() == false) {
      throw new Exception('Stmt falhou');
      http_response_code(500);
    }else {
      http_response_code(201);
    }

    $stmt->close();
  } catch (\Exception $e) {
    throw $e;
  }


}elseif (isset($_GET['email'])) {
  $token = createResetToken($_GET['email']);
  $link = "https://ecoswim.com.br/esqueciasenha/".$token;

  $stmt = $conexao->prepare("SELECT nome FROM usuarios WHERE email = (?) LIMIT 1");
  $stmt->bind_param("s", $$_GET['email']);
  $stmt->execute();
  $result = $stmt->get_result();
  $nome = $result->fetch_all(MYSQLI_ASSOC)[0];


  $email_remetente = "contato@ecoswim.com.br";
  $email_destinatario = $_GET['email'];
  $email_assunto = "Ecoswim 2019 - Troca de senha";

  $email_headers = implode ("\r\n",array ( "From: Contato <$email_remetente>", "Reply-To: ".$email_remetente, "Subject: Ecoswim - Alteracao da senha","Return-Path:".$email_remetente,"MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));

  $email_mensagem ="
  <html lang='en' dir='ltr'>
    <head>
      <meta charset='utf-8'>
      <title>HTML email</title>
    </head>
    <body style='background: rgba(240, 240, 247, 1); margin: 0;'>
      <div class='header' style='background: rgba(130, 87, 229, 1); top: 0; left: 0; padding: 0.8rem 2.4rem;'>
        <h1 style='color:rgba(255, 255, 255, 1)'>Troca de Senha</h1>
      </div>
      <div class='content' style='margin: 2.4rem 5vw;'>
        <p style='color:#28166F'>Olá, ".$nome."!<br> Clique no link para trocar a sua senha:</p>
        <div style='texalign: center; display: flex; justify-content: space-around;'>
          <a style='appearance: button; text-decoration: none; background: #04D361; color: #FFFFFF; padding: 0.4rem 0.8rem; border-radius: 0.2rem; margin: 1rem auto;' href='{$link}'>Trocar a senha</a>
        </div>
        <p>Esse link expira 24 horas após seu envio. Esse link leva você ao site do Gerenciamento da conta, onde é possível inserir uma nova senha.</p>
        <hr>
        <p style='color:#28166F'>Essa mensagem de email foi enviada de um endereço somente para envio. Não responda a essa mensagem. Para informações e contato, acesse o site do ecoswim:
        <a href='https://ecoswim.com.br/'>https://ecoswim.com.br/</a></p>
        <p style='color:#28166F'>Qualquer dúvida ou necessidade, estamos a disposição.</p>
        <p style='color:#28166F'>Atenciosamente,</p>
        <p style='color:#28166F'><b>Equipe do Ecoswim</b></p>
      </div>
    </body>
  </html>
  ";

  if (mail($email_destinatario, $email_assunto, nl2br($email_mensagem), $email_headers)) {
    http_response_code(201);
  }
  else {
    http_response_code(500);
  }

}elseif (!empty(getAuthorizationHeader())) {
  $received = explode(".", getAuthorizationHeader());
  $tokenCheck = checkToken($received, $key);

  if ($tokenCheck) {
    $resposta['tokenCorreto'] = true;
    echo json_encode($resposta);
    http_response_code(200);

  }else if (!isset($tokenCheck)) {
    $resposta['error'] = "Esse link foi expirado.";
    echo json_encode($resposta);
    http_response_code(410);
  }else {
    $resposta['error'] = "Esse token não é valido.";
    echo json_encode($resposta);
    http_response_code(401);
  }
}

$conexao->close();
 ?>
