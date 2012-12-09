<?php
require_once("SinglyClient.php");
require_once("InMemorySinglyAccountStorage.php");

define("CLIENT_ID", "your_client_id");
define("CLIENT_SECRET", "your_client_secret");

session_start(); 

$singlyClient = $_SESSION["singlyClient"];
if (empty($singlyClient)) {
  $singlyClient = new SinglyClient(
      CLIENT_ID,
      CLIENT_SECRET, 
      new InMemorySinglyAccountStorage());
  $_SESSION["singlyClient"] = $singlyClient;
}

// setup the singly client, if not authenticated go to auth page
$account = $_SESSION["account"];
if (empty($account) || !$singlyClient->isAuthenticated($account)) {
  header("Location: /authentication.php");
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Singly PHP Examples</title>
    <link rel="stylesheet" type="text/css" href="/static/css/example.css" />
  </head>
  <body>
    <div id="content">
      <div id="header"><a href="/index.php"><img id="logo" src="/img/singly-logo.png" /></a></div>
      <h2>Examples</h2>
      <p>Once authenticated, you can now call Singly APIs to get retrieve your
      user's social data.</p>
      <ol>
        <li><a href="/authentication.php">Authenticate with different services</a></li>
        <li><a href="/friends.php">Get friends from all services</a></li>
        <li><a href="/photos.php">Get photos from services</a></li>
        <li><a href="/postphoto.php">Post a photo to Facebook</a></li>
      </ol>
    </div>
  </body>
</html>