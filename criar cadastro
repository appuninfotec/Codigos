<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once   '../db/Conexao.php';
require_once  '../master/classes/functions.php';

if ($_ativacom != "1") {
  header("location: ../");
  exit;
}

$getGeneralData = $connect->query("SELECT * FROM carteira WHERE Id = 1");
$generalData = $getGeneralData->fetch(PDO::FETCH_OBJ);
$apiToken = $generalData->tokenapi;

function validateInput($input)
{
  return htmlspecialchars(stripslashes(trim($input)));
}

function generateRandomPassword($length)
{
  $characters = '0123456789';
  $password = '';
  for ($i = 0; $i < $length; $i++) {
    $index = rand(0, strlen($characters) - 1);
    $password .= $characters[$index];
  }
  return $password;
}

if (isset ($_POST["email"])) {
  $emailLogin = validateInput($_POST['email']);
  $cellPhone = validateInput($_POST['celular']);
  $fullName = validateInput($_POST['nomec']);

  $checkUser = $connect->prepare("SELECT id FROM carteira WHERE login = :login");
  $checkUser->execute(['login' => $emailLogin]);

  if ($checkUser->rowCount() > 0) {
    header("location: ./?erroE=login");

    exit;
  }

  $checkUser = $connect->prepare("SELECT id FROM carteira WHERE celular = :celular");
  $checkUser->execute(['celular' => $cellPhone]);

  if ($checkUser->rowCount() > 0) {
    header("location: ./?erroC=login");

    exit;
  }

  $randomPassword = generateRandomPassword(6);
  $passwordHash = sha1($randomPassword);

  $currentDate = date("d/m/Y");
  $currentDateArray = explode("/", $currentDate);
  $subscriptionDate = date('Y-m-d', strtotime($currentDateArray[2] . '-' . $currentDateArray[1] . '-' . $currentDateArray[0] . ' +3 days'));
  $formattedSubscriptionDate = date("d/m/Y", strtotime($subscriptionDate));

  



  $addUser = $connect->prepare("INSERT INTO carteira (idm, login, senha, tipo, nome, celular, assinatura, nomecom, cnpj, enderecom, contato, msg, msgqr, msgpix, tokenapi, pagamentos, background, juros_diarios ) VALUES ('1', :login, :senha, '2', :nome, :celular, :assinatura, '', '', '', '', '', '', '', '', '', '', 0.00)");
  $addUser->execute([
    'login' => $emailLogin,
    'senha' => $passwordHash,
    'nome' => $fullName,
    'celular' => $cellPhone,
    'assinatura' => $formattedSubscriptionDate
  ]);
  
  
  
  
  $lastInsertId = $connect->lastInsertId();

  $bytes = random_bytes(16);
	$token = bin2hex($bytes);

  $connect->query("INSERT INTO conexoes(id_usuario, tokenid) VALUES ('" . $lastInsertId . "','" . $token . "')");

  $messageTemplates = [
    ['1', '#NOME# mensagem de com 5 dias antes do vencimento'],
    ['2', '#NOME# mensagem de com 3 dias antes do vencimento'],
    ['3', '#NOME# mensagem no dia do vencimento'],
    ['4', '#NOME# mensagem de mensalidade vencida'],
    ['5', '#NOME# mensagem de agradecimento'],
    ['6', '#NOME# mensagem de cobranca manual'],
  ];

  foreach ($messageTemplates as $template) {
    $connect->query("INSERT INTO mensagens(idu, tipo, msg) VALUES ('" . $lastInsertId . "','" . $template[0] . "','" . $template[1] . "')");
  }

  $accountCreationMessage = "NOVA CONTA CRIADA COM SUCESSO\n\nOlá " . $fullName . " sua conta foi criada com sucesso.\n\nSegue abaixo os dados de login:\n\n*URL*: " . $_urlmaster . "\n\n*Usuário*: " . $emailLogin . "\n\n*Senha*: " . $randomPassword . "\n\n*Esta é uma mensagem automática e não precisa ser respondida.*";

  $curl = curl_init();

  curl_setopt_array($curl, [
    CURLOPT_URL => $urlapi . "/message/sendText/AbC123" . $apiToken,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
      "number" => "55" . $cellPhone,
      "options" => ["delay" => 1200, "presence" => "composing", "linkPreview" => false],
      "textMessage" => ["text" => $accountCreationMessage]
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'apikey: ' . $apiToken]
  ]);

  $response = curl_exec($curl);

  $error = curl_error($curl);

  curl_close($curl);

  if ($error) {
    echo $error;
  } else {
    header("location: ./?sucesso=login");

    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
  <meta name="description"
    content="<?php print $_nomesistema; ?> é o melhor sistema para cobranças e notificações via WhatsApp">
  <meta name="keywords" content="financeiro, cobranças, whatsapp">
  <meta property="og:url" content="<?php print $_urlmaster; ?>">
  <meta property="og:title" content="<?php print $_nomesistema; ?>">
  <meta property="og:description" content="Cobranças automáticas para WhatsApp.">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?php print $_urlmaster; ?>/img/favicon.png">
  <meta property="og:image:width" content="520">
  <meta property="og:image:type" content="image/png">
  <meta property="og:site_name" content="<?php print $_nomesistema; ?>">
  <meta property="og:locale" content="pt-BR">
  <title>WhatsApp Cobranças: Integre sua empresa |
    <?php print $_nomesistema; ?>
  </title>
  <link rel="icon" href="<?php print $_urlmaster; ?>/img/favicon.png" sizes="32x32" type="image/png">
  <link href="../lib/font-awesome/css/font-awesome.css" rel="stylesheet">
  <link href="../lib/Ionicons/css/ionicons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/slim.css">
  <script src="https://www.google.com/recaptcha/api.js"></script>
</head>

<body style="background-color:#333333">
  <div class="signin-wrapper">
    <div class="signin-box">
      <h3>Criar Conta</h3>
      <hr />
      <form action="" method="post">
        <div class="form-group">
          <input type="text" class="form-control" name="nomec" placeholder="Nome Completo" required>
        </div>
        <div class="form-group">
          <input type="email" class="form-control" name="email" placeholder="Seu E-mail" required>
        </div>
        <div class="form-group">
          <input type="text" class="form-control" name="celular" id="celular" placeholder="Nº celular com WhatsApp"
            required>
        </div>
        <div class="form-group mg-b-1">
          <div class="g-recaptcha" data-callback="recaptchaCallback" data-sitekey="<?php print $_captcha; ?>"></div>
          <br>
        </div>
        <?php if (isset ($_GET["sucesso"])) { ?>
          <div class="form-group" style="color:#00CC00">
            <i class="fa fa-certificate"></i> Cadastrado com sucesso.
          </div>
          <meta http-equiv="refresh" content="1;URL=../" />
        <?php } ?>
        <?php if (isset ($_GET["erroE"])) { ?>
          <div class="form-group" style="color:#FF0000">
            <i class="fa fa-certificate"></i> E-mail já cadastrado. Tente outro.
          </div>
        <?php } ?>
        <?php if (isset ($_GET["erroC"])) { ?>
          <div class="form-group" style="color:#FF0000">
            <i class="fa fa-certificate"></i> Número de celular já cadastrado. Tente outro.
          </div>
        <?php } ?>
        <button type="submit" id="submit" name="submit" class="btn btn-dark btn-block" disabled="disabled">Criar
          Conta</button>
      </form>
      <a href="../" class="btn btn-warning btn-block mg-t-10">Voltar</a>
    </div>
  </div>
  <script src="../lib/jquery/js/jquery.js"></script>
  <script src="https://rawgit.com/RobinHerbots/Inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>
  <script>
    function upperCaseF(a) {
      setTimeout(function () {
        a.value = a.value.toUpperCase();
      }, 1);
    }
  </script>
  <script>
    $(document).ready(function () {
      $("input[id*='celular']").inputmask({
        mask: ['(99) 99999-9999'],
        keepStatic: true
      });
    });
  </script>
  <script>
    function recaptchaCallback() {
      jQuery("#submit").prop("disabled", false);
    }
  </script>
</body>

</html>