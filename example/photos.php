<?php
require_once("vendor/autoload.php");

use Singly\Client\SinglyClient;
use Singly\Client\InMemorySinglyAccountStorage;

session_start();

$singlyClient = $_SESSION["singlyClient"];

// setup the singly client, if not authenticated go to auth page
$account = $_SESSION["account"];
if (empty($account) || !$singlyClient->isAuthenticated($account)) {
  header("Location: /authentication.php");
}

$accountStorage = $singlyClient->getAccountStorage();
$accessToken = $accountStorage->getAccessToken($account);

// create the query parameters
$queryParams = array();
$queryParams["access_token"] = $accessToken;

// get the number of photos
$typeNodes = $singlyClient->doGetApiRequest("/types", $queryParams);
$numPhotos = isset($typesNodes["photos"]) ? $typesNodes["photos"] : NULL;

// get the photos
$photos = array();
$queryParams["limit"] = $numPhotos;
$photoNodes = $singlyClient->doGetApiRequest("/types/photos", $queryParams);
foreach ($photoNodes as $photoNode) {
  $photos[] = $photoNode["data"];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Singly PHP Photos Example</title>
    <link rel="stylesheet" type="text/css" href="/static/css/example.css" />
  </head>
  <body>
    <div id="content">
      <div id="header"><a href="/index.html"><img id="logo" src="/img/singly-logo.png" /></a></div>
      <h2>All Photos from All Services</h2> 
      <div><a href="/index.php">Back To Index</a></div>      
      <p>This example uses the /photos API to get all photos from all services.</p>      
      <table id="friends">
        <tr>      
        <?php 
        for ($i = 0; $i < count($photos); $i++) { 
          $photo = $photos[$i];
          ?>
          <td>
            <a href="<?= $photo['source'] ?>"><img src="<?= $photo['picture'] ?>" /></a>
          </td>
        <?php if (($i + 1) > 5 && (($i + 1) % 6 == 0)) { ?></ tr><tr><?php } ?>
        <?php } ?>
        </tr>
      </table>
    </div>
  </body>
</html>