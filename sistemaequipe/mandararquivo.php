<?php

function convertImage($originalImage, $outputImage, $quality) {
    // jpg, png, gif or bmp?
    $exploded = explode('.',$originalImage['name']);
    $ext = $exploded[count($exploded) - 1];

    if (preg_match('/jpg|jpeg/i',$ext))
        $imageTmp=imagecreatefromjpeg($originalImage['tmp_name']);
    else if (preg_match('/png/i',$ext))
        $imageTmp=imagecreatefrompng($originalImage['tmp_name']);
    else if (preg_match('/gif/i',$ext))
        $imageTmp=imagecreatefromgif($originalImage['tmp_name']);
    else if (preg_match('/bmp/i',$ext))
        $imageTmp=imagecreatefrombmp($originalImage['tmp_name']);
    else
        return 0;

    // quality is a value from 0 (worst) to 100 (best)
    imagejpeg($imageTmp, $outputImage, $quality);
    imagedestroy($imageTmp);

    return 1;
}

function upload_foto($arquivo, $nome_equipe) {
  // Configurações ftp
  $servidor_ftp = 'ftp.ecoswim.com.br';
  $usuario_ftp = 'ecoswim';
  $senha_ftp   = 'v2f6y4w3';
  $extensoes_autorizadas = array( '.png', '.jpg', '.jpeg', '.gif', '.bmp' );
  $caminho = 'api/';
  $limitar_tamanho = 0;
  $sobrescrever = 1;

  $nome_arquivo = $arquivo['name'];
  $tamanho_arquivo = $arquivo['size'];
  $arquivo_temp = $arquivo['tmp_name'];
  $extensao_arquivo = strrchr( $nome_arquivo, '.' );
  $foto_convertida = $nome_equipe . '.jpg';
  $destino = $caminho . "avatares das equipes/" . $foto_convertida;

  if ( ! $sobrescrever && file_exists( $destino ) ) {
  	exit('Arquivo já existe.');
  }

  if ( $limitar_tamanho && $limitar_tamanho < $tamanho_arquivo ) {
  	exit('Arquivo muito grande.');
  }

  if ( ! empty( $extensoes_autorizadas ) && ! in_array( $extensao_arquivo, $extensoes_autorizadas ) ) {
  	exit('Tipo de arquivo não permitido.');
  }

  convertImage($arquivo, $foto_convertida, 100);

  $conexao_ftp = ftp_connect( $servidor_ftp );
  $login_ftp = @ftp_login( $conexao_ftp, $usuario_ftp, $senha_ftp );

  if ( ! $login_ftp ) {
  	exit('Usuário ou senha FTP incorretos.');
  }

  ftp_pasv($conexao_ftp, true);

  ftp_put( $conexao_ftp, $destino, $foto_convertida, FTP_BINARY );

  unlink($foto_convertida);
  ftp_close( $conexao_ftp );
}
