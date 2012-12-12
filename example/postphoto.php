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

if ($_FILES["photo1"]) {

  // create the query parameters
  $postParams = array();
  $postParams["access_token"] = $accessToken;
  $postParams["to"] = "facebook";

  $postFiles = array();
  $postFiles["photo"] = $_FILES["photo1"]["tmp_name"];
  $singlyClient->doPostMultipartApiRequest("/types/photos", NULL,
    $postParams, $postFiles);
  $uploaded = true;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Singly PHP Photo Upload Example</title>
    <link rel="stylesheet" type="text/css" href="/static/css/example.css" />
  </head>
  <body>
    <div id="content">
      <div id="header"><a href="/index.php"><img id="logo" src="/img/singly-logo.png" /></a></div>
      <h2>Post a Photo to Facebook</h2> 
      <div><a href="/index.php">Back To Index</a></div>      
      <p>This example shows how to use the /types/photos API to post a photo to 
      Facebook.  You must already be authenticated with Facebook for this example
      to work.  Select one or more photos and click Upload.</p>
      <form name="uploadForm" action="postphoto.php" method="post" enctype="multipart/form-data">  
      <div>        
        <label for="photo1">Choose A Photo:</label>        
        <input type="file" name="photo1" />    
        <input type="submit" name="_submit" value="Post to Facebook" />       
      </div>
      <?php if ($uploaded) { ?><div style="color:green">You photo has been uploaded to facebook.</div><?php } ?>
      <c:if test="${uploaded}">
      </form>         
    </div>
  </body>
</html>