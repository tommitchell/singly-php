<?php
require_once("vendor/autoload.php");

use Singly\Client\SinglyClient;
use Singly\Client\InMemorySinglyAccountStorage;

session_start();

$accessToken = NULL;
$singlyClient = NULL;
$account = NULL;

if (isset($_SESSION["singlyClient"])) {

  $singlyClient =$_SESSION["singlyClient"];
  if (isset($_SESSION["account"])) {
    $account = $_SESSION["account"];
    $accountStorage = $singlyClient->getAccountStorage();
    $accessToken = $accountStorage->getAccessToken($account);
  }
}

$authCode = isset($_REQUEST["code"]) ? $_REQUEST["code"] : NULL;
$service = isset($_REQUEST["service"]) ? $_REQUEST["service"] : NULL;
$profile = isset($_REQUEST["profile"]) ? $_REQUEST["profile"] : NULL;


if (!empty($service)) {

  if (!empty($profile)) {

    $postParams = array();
    $postParams["delete"] = $profile . "@" . $service;
    $postParams["access_token"] = $accessToken;
    $singlyClient->doPostApiRequest("/profiles", NULL, $postParams);
    header("Location: /authentication.php");
  } 
  else {

    $serviceAuthURL = $singlyClient->getAuthenticationUrl($account, $service,
      "http://" . $_SERVER["SERVER_NAME"] . "/authentication.php", NULL);
    header("Location: " . $serviceAuthURL);    
  }

}
elseif (!empty($authCode)) {

  // parse the authentication code and pass to complete the authentication
  $account = $singlyClient->completeAuthentication($authCode);
  $_SESSION["account"] = $account;

  // if so then redirect to authentication URL
  header("Location: /authentication.php");
}

// get services
$services = array();
$serviceNodes = $singlyClient->doGetApiRequest("/services", NULL);
foreach ($serviceNodes as $key => $value) {

  $authService = array();
  $authService["id"] = $key;
  $authService["name"] = $value["name"];
  $icons = array();

  if (isset($value["icons"])) {
    foreach ($value["icons"] as $iconNode) {
      $iconKey = $iconNode["height"] . "x" . $iconNode["width"];
      $source = $iconNode["source"];
      $icons[$iconKey] = $source;
    }
  }
  $authService["icons"] = $icons;

  $services[$key] = $authService;
}
ksort($services);

// get profiles
$profiles = array();
$queryParams = array();
$queryParams["access_token"] = $accessToken;
if (!empty($accessToken)) {
  $profileNodes = $singlyClient->doGetApiRequest("/profiles", $queryParams);
  foreach ($profileNodes as $profileName => $profileNode) {
    if ($profileName != "id") {
      $profiles["$profileName"] = $profileNode[0];
    }
  }
}

$authenticated = $singlyClient->isAuthenticated($account);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Singly PHP Authentication Example</title>
    <link rel="stylesheet" type="text/css" href="/static/css/example.css" />
  </head>
  <body>
    <div id="content">
      <div id="header"><a href="/index.php"><img id="logo" src="/img/singly-logo.png" /></a></div>
      <h2>Step 1: Authenticate with a Service</h2>
      <div><a href="/index.php">Back To Index</a></div>      
      <p>A user of your application first authenticates with a service such as
      Facebook, Twitter, or LinkedIn.  This gives you a Singly access token on
      on a per user basis.  The user can authenticate with multiple services 
      and you can pull data from each.</p>
      <?php if ($authenticated) { ?>
        <h2>Step 2: Authenticated! Your account is: <?= $account ?></h2>
        <p>Now that the user has authenticated you can call Singly APIs or proxy 
        through to the service APIs. <a href="/index.php">Go To Examples</a></p>
      <?php } ?>      
      <table id="serviceList">
        <tr>
        <?php
        $servicesValues = array_values($services);
        for ($i = 0; $i < count($servicesValues); $i++) {
          $service = $servicesValues[$i];
          $serviceId = $service["id"];
          $serviceIcon = isset($service["icons"]["32x32"]) ? $service["icons"]["32x32"] : NULL;
          $serviceName = $service["name"];
          $profile = isset($profiles[$serviceId]) ? $profiles[$serviceId] : NULL;
          ?>
          <td <?php if (!empty($profile)) { echo "class=\"hasProfile\""; } ?>>
            <div class="serviceCell">
              <?php if (!empty($profile)) { ?>
                <a href="/authentication.php?service=<?= $serviceId ?>&profile=<?= $profile ?>">
                  <div class="minus">
                    <img src="/img/minus.png" alt="Remove authentication for this service" />
                  </div>
                </a>
              <?php } ?>
              <a href="/authentication.php?service=<?= $serviceId ?>">
                <img src="/img/<?= $serviceId ?>.png" />
                <br>
                <?= $serviceName ?>
              </a>
            </div>
          </td>
          <?php if (($i + 1) > 6 && (($i + 1) % 7 == 0)) { ?></ tr><tr><?php } ?>
        <?php } ?>
        </tr>
      </table>
    </div>
  </body>
</html>