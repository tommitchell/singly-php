# Singly PHP SDK

## Beta
Be aware that the Singly PHP Client is in a beta state. Class and method names are subject to change. As we work to improve the SDK we're not currently concerned with breaking backwards compatibility.  This will change in the future as the SDK becomes more stable.

## Source Code
This Singly PHP SDK is open source and is [hosted on Github](https://github.com/Singly/singly-php).

## Overview
This repository contains two different projects. The first is the Singly PHP client library. This is a library project you can include into your PHP apps that makes it easy to use the Singly API. The second in an example project that show usage of the Singly client in a web app.

The Singly PHP client is a library supporting the [Singly](https://singly.com) social API that will:

  - Allow users to easily authenticate with any service supported by Singly; for example Facebook, Twitter, Github, Foursquare and others.
  - Make requests to the Singly API to retrieve your users' social data for use in your app.

The library code is contained in the project in the SDK folder. The `SinglyClient.php` class is the entry point to using the Singly API in your PHP project.

Sample implementations are contained in the example folder. This is a PHP webapp that demonstrates the usage of authentication with the Singly api and retrieving social data.

## Register with Singly
You will need to register with Singly to get your client id and client secret. These are used when making API calls.

1. Go to https://singly.com and register or login.
2. Create a new Singly application or use the default application. This is your Singly app.
3. Get the client id and client secret for your Singly app.

## Install Composer and Dependencies
Composer is a dependency manager. It provides an easy way to mange the downloading of dependencies for the SDK.

1. Install Composer - http://getcomposer.org/
2. Go to the sdk root directory. 
3. Run `composer install`. This downloads the SDK dependencies into a vendor folder in the SDK root directory.

## Setting up a Virtual Host
To run the example you will need to include the SDk folder in your PHP source path and setup the example folder as the root of your webapp. An easy way to do this is through an Apache virtual host. Here is an example of an Apache virtual host setup on a Linux system.

    <VirtualHost *:80>
      ServerName singlyphp
      ErrorLog /var/log/apache2/singlyphp-error.log
      CustomLog /var/log/apache2/singlyphp-access.log combined
      DocumentRoot "/your/path/to/singly-php/example" 
      <Directory "/your/path/to/singly-php/example">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
        php_value include_path ".:/your/path/to/singly-php/sdk"
      </Directory>
    </VirtualHost>

You would also need to edit your hosts file, `/etc/hosts` on nix systems, to point singlyphp to localhost.

  127.0.0.1   localhost singlyphp

## Running the Example
1. Paste your client id and client secret lines 5-6 of the `index.php` file in the example folder.
2. Ensure that the `sdk` folder is in the php path.  This can be done in an Apache virtual host as shown above.
3. Startup your web server
4. Open your browser to http://singlyphp/ to use the web application.

## Calling the SinglyClient
The `SinglyClient.php` is the main client class you will use within your application to authenticate and make API calls.  Here is an example of how you would use the SinglyClient.

    require_once("SinglyClient.php");
    require_once("InMemorySinglyAccountStorage.php");

    define("CLIENT_ID", "your_client_id");
    define("CLIENT_SECRET", "your_client_secret");

    $singlyClient = $_SESSION["singlyClient"];
    if (empty($singlyClient)) {
      $singlyClient = new SinglyClient(
          CLIENT_ID,
          CLIENT_SECRET, 
          new InMemorySinglyAccountStorage());
      $_SESSION["singlyClient"] = $singlyClient;
    }

    $services = $singlyClient->doGetApiRequest("/services");
    ... 
    use the data in your app

Usually a custom `SinglyAccountStorage.php` implementation would need to be created to save accounts and access tokens to a db or other permanent storage. 

Calling other methods in the SDK would require authenticating and getting an access token first.

    // get the account from the session, then get the access token
    $account = $_SESSION["account"];
    $accountStorage = $singlyClient->getAccountStorage();
    $accessToken = $accountStorage->getAccessToken($account);

    // create the query parameters
    $queryParams = array();
    $queryParams["access_token"] = $accessToken;

    // get the number of photos
    $friends = $singlyClient->doGetApiRequest("/friends", $queryParams);

Support
--------------

This is a work in progress. If you have questions or comments

* Join our live chatroom at http://support.singly.com
* Email support@singly.com