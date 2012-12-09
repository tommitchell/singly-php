<?php
require_once("SinglyClient.php");
require_once("InMemorySinglyAccountStorage.php");

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
$countNodes = $singlyClient->doGetApiRequest("/friends", $queryParams);
$numFriends = $countNodes["all"];

// get the photos
$friends = array();

$blockSize = 20;
$blocks = $numFriends / $blockSize;
if (($blocks % $blockSize) > 0) {
  $blocks += 1;
}

// get all friends
$totalBlocks = $blocks >= 5 ? 5 : $blocks;
for ($i = 0; $i < $totalBlocks; $i++) {

  $offset = ($i * $blockSize);
  $limit = $blockSize;

  $queryParams = array();
  $queryParams["access_token"] = $accessToken;
  $queryParams["offset"] = $offset;
  $queryParams["limit"] = $limit;
  $queryParams["toc"] = "false";

  $friendNodes = $singlyClient->doGetApiRequest("/friends/all", $queryParams);
  foreach ($friendNodes as $friendNode) {
    $friend = array();
    $friend["name"] = $friendNode["name"];
    $friend["profileUrl"] = $friendNode["url"];
    $friend["imageUrl"] = $friendNode["thumbnail_url"];
    $friend["serviceIds"] = array_keys($friendNode["services"]);
    $friends[] = $friend;
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Singly PHP Friends Example</title>
    <link rel="stylesheet" type="text/css" href="/static/css/example.css" />
  </head>
  <body>
    <div id="content">
      <div id="header"><a href="/index.php"><img id="logo" src="/img/singly-logo.png" /></a></div>
      <h2>All Friends from All Services</h2> 
      <div><a href="/index.php">Back To Index</a></div>      
      <p>This example uses the /friends API to show up to the first 100 friends from all services.</p>      
      <table id="friends">
        <tr>    
        <?php 
        for ($i = 0; $i < count($friends); $i++) { 
          $friend = $friends[$i];
          ?>
          <td>
            <div class="friendServices">
              <?php foreach ($friend["serviceIds"] as $serviceId) { ?>
                <span class="serviceImage"><img src="/img/<?= $serviceId ?>.png" /></span>                
              <?php } ?>
            </div>
            <div class="friendInfo">
              <a href="<?= $friend['profileUrl'] ?>"><?= $friend["name"] ?></a>
            </div>
          </td>
        <?php if (($i + 1) > 4 && (($i + 1) % 5 == 0)) { ?></ tr><tr><?php } ?>
        <?php } ?>          
        </tr>
      </table>
    </div>
  </body>
</html>