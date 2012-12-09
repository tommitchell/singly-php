<?php
require_once("vendor/autoload.php");
require_once("SinglyUtils.php");

use Guzzle\Http\Client;

/**
 * A client that handles authentication and requests to the Singly API.
 * 
 * An application must be authenticated and retrieve an access token for an 
 * account before it can make Singly API calls for that account.  The process
 * for authentication has three steps.
 * 
 * <ol>
 *   <li>The application use the getAuthenticationUrl(String, String)
 *   method and redirect the user to the authentication web page for the service
 *   against which the user is authenticating.</li>
 *   <li>Once the user authenticates the web page is redirected back to the
 *   redirectUrl in the application.  A <strong>code</strong> parameter is 
 *   parsed from the redirectUrl.  This is the authentication code.</li>
 *   <li>The application calls the completeAuthentication(String, String)
 *   method with the authentication code and account to store the access token
 *   for that account.</li>
 * </ol>
 * 
 * Once an access token is stored for an account, calls can be made to the API
 * using the doXApiRequest methods and passing in an account.  The access token
 * will be retrieved behind the scenes and passed to all API calls.
 * 
 * @see https://singly.com/docs/api
 */
 class SinglyClient {

  private $clientId;
  private $clientSecret;
  private $accountStorage;

  public function __construct($clientId, $clientSecret, $accountStorage) {
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
    $this->accountStorage = $accountStorage;
  }

  /**
   * Returns true if the application has been previously authenticated and 
   * has a Singly access token.
   * 
   * @param account The Singly account to check for authentication.
   * 
   * @return True if the account has a Singly access token.  False otherwise.
   */
  public function isAuthenticated($account) {
    return $this->accountStorage->hasAccessToken($account);
  }

  /**
   * The first step of the authentication process, returns the URL to which the
   * user is to be redirected for authentication with a given service.
   * 
   * The first time a user authenticates through Singly for a given application
   * a unique Singly account token is generated and stored during the 
   * ompleteAuthentication(String) method.  As a user authenticates 
   * with more services, those services are linked to the same Singly account.
   * 
   * If this is the first time your user is authenticating with Singly for your
   * application you won't have an account, set it to null.  Otherwise pass in
   * the Singly account token that was previously generated so the new services
   * can be properly linked to the same account.
   *  
   * Some services require extra parameters such as scope and flag to be passed
   * in.  Use the authExtra input to pass in the parameters by name.
   * 
   * @param account The Singly account for which to authenticate.
   * @param service The service to authenticate against.
   * @param redirectUrl The URL handled by the application to which we are 
   * redirected upon successful authentication.
   * @param scope Optional scope passed to the service.  Used by some services
   * to allow for extra permissions.
   * @param authExtra Any optional extra parameters used in oauth of services.
   * This includes scope and flag parameters.
   * 
   * @return The URL to redirect the user to for service authentication.
   */
  public function getAuthenticationUrl($account, $service, $redirectUrl, 
    $authExtra) {

    $queryParams = array();
    $queryParams["client_id"] = $this->clientId;
    $queryParams["redirect_uri"] = $redirectUrl;
    $queryParams["service"] = $service;

    // send access token if we have one
    if (!empty($account)) {
      $accessToken = $this->accountStorage->getAccessToken($account);
      if (!empty($accessToken)) {
        $queryParams["access_token"] = $accessToken;
      }
    }

    // add in scope and flag parameters if present
    if (!empty($authExtra)) {
      if (array_key_exists("scope", $authExtra)) {
        $queryParams["scope"] = $authExtra["scope"];
      }
      if (array_key_exists("flag", $authExtra)) {
        $queryParams["flag"] = $authExtra["flag"];
      }
    }

    // create the authentication url
    return createSinglyURL("/oauth/authenticate", $queryParams);
  }

  /**
   * Completes the authentication process, getting and storing the Singly access
   * token and account.
   *
   * The Singly account token is returned from this method and is also stored
   * by the SinglyAccountStorage implementation.
   * 
   * @param authCode The authentication code parsed from the redirect URL. This
   * is used to retrieve the Singly access token.
   * 
   * @return The Singly account or null if the authentication process did not
   * complete successfully.
   */
  public function completeAuthentication($authCode) {

    // create the post parameters
    $postParams = array();
    $postParams["client_id"] = $this->clientId;
    $postParams["client_secret"] = $this->clientSecret;
    $postParams["code"] = $authCode;

    // create the access token url
    $accessTokenUrl = createSinglyURL("/oauth/access_token");

    try {

      // create the http client and request
      $httpClient = new Client();
      $httpRequest = $httpClient->post($accessTokenUrl);
      $httpRequest->addPostFields($postParams);

      // send the http request, get a response and convert to JSON
      $httpResponse = $httpRequest->send();
      $data = $httpResponse->json();

      // save off the access token for the account to storage.
      $accessToken = $data["access_token"];
      $singlyAccount = $data["account"];
      $this->accountStorage->saveAccessToken($singlyAccount, $accessToken);

      return $singlyAccount;

    } catch (Exception $e) {
      // do nothing falls through to false, didn't complete
    }
  }

  /**
   * Makes a GET call to the Singly API.
   * 
   * If an API call requires an access token it must be added to the queryParams
   * passed into the method.
   * 
   * @param apiEndpoint The Singly API endpoint to call.
   * @param queryParams Any query parameters for the endpoint.
   * 
   * @return The JSON returned from the API.
   */
  public function doGetApiRequest($apiEndpoint, $queryParams) {

    // create the API endpoint url
    $getApiCallUrl = createSinglyURL($apiEndpoint, $queryParams);

    // call the api endpoint with a GET method
    try {

      // create the http client and request
      $httpClient = new Client();
      $httpRequest = $httpClient->get($getApiCallUrl);

      // send the http request, get a response and convert to JSON
      $httpResponse = $httpRequest->send();
      $data = $httpResponse->json();
      
      return $data;

    } catch (Exception $e) {
      // do nothing falls through to false, didn't complete
    }
  }

  /**
   * Makes a POST call to the Singly API.
   * 
   * If an API call requires an access token it must be added to either the 
   * queryParams or postParams passed into the method.
   * 
   * @param apiEndpoint The Singly API endpoint to call.
   * @param queryParams Any parameters to send in the url of the request.
   * @param postParams Any parameters to send in the post body of the request.
   * 
   * @return The JSON returned from the API.
   */
  public function doPostApiRequest($apiEndpoint, $queryParams, $postParams) {

    // create the API endpoint url
    $postApiCallUrl = !empty($queryParams)
      ? createSinglyURL($apiEndpoint, $queryParams) : 
        createSinglyURL($apiEndpoint);

    try {

      // create the http client and request
      $httpClient = new Client();
      $httpRequest = $httpClient->post($postApiCallUrl);
      if (!empty($postParams)) {
        $httpRequest->addPostFields($postParams);
      }

      // send the http request, get a response and convert to JSON
      $httpResponse = $httpRequest->send();
      $data = $httpResponse->json();

      return $data;

    } catch (Exception $e) {
      // do nothing falls through to false, didn't complete
    }
  }

  /**
   * Makes a POST call to the Singly API.
   * 
   * If an API call requires an access token it must be added to either the 
   * queryParams or postParams passed into the method.
   * 
   * Binary content can be posted as a multipart form by adding name => file 
   * location values to the files map.
   * 
   * @param apiEndpoint The Singly API endpoint to call.
   * @param queryParams Any parameters to send in the url of the request.
   * @param postParams Any parameters to send in the post body of the request.
   * @param postFiles Any file objects to send in the post body of the request.
   * 
   * @return The JSON returned from the API.
   */
  public function doPostMultipartApiRequest($apiEndpoint, $queryParams, 
    $postParams, $postFiles) {

    // create the API endpoint url
    $postApiCallUrl = !empty($queryParams)
      ? createSinglyURL($apiEndpoint, $queryParams) : 
        createSinglyURL($apiEndpoint);

    // create the http client and request
    $httpClient = new Client();
    $httpRequest = $httpClient->post($postApiCallUrl);
    if (!empty($postParams)) {
      $httpRequest->addPostFields($postParams);
    }
    if (!empty($postFiles)) {
      $httpRequest->addPostFiles($postFiles);
    }

    // send the http request, get a response and convert to JSON
    $httpResponse = $httpRequest->send();
    $data = $httpResponse->json();

    return $data;
  }

  /**
   * Makes a POST call to the Singly API passing in the body of the request. 
   * This is useful when doing sharing or proxying through the Singly API.
   * 
   * If an API call requires an access token it must be added to the queryParams
   * passed into the method.
   * 
   * @param apiEndpoint The Singly API endpoint to call.
   * @param queryParams Any query parameters for the endpoint.  Query parameters
   * are appended to the URL and are not passed through the POST body.
   * @param body The body of the POST request.
   * @param mime The mime type set in the header of the POST request.
   * @param charst The character set set in the header of the POST request.
   * 
   * @return The JSON returned from the API.
   */
  public function doPostBodyApiRequest($apiEndpoint, $queryParams, $body, 
    $mime, $charset) {

    // create the API endpoint url
    $postBodyApiCallUrl = createSinglyURL(apiEndpoint, $queryParams);

    try {

      // create the http client and request
      $httpClient = new Client();
      $httpRequest = $httpClient->post($postBodyApiCallUrl);
      $httpRequest->setHeader("Content-Type", $mime .  "; charset=" . $charset);     
      $httpRequest->setBody($body);

      // send the http request, get a response and convert to JSON
      $httpResponse = $httpRequest->send();
      $data = $httpResponse->json();

      return $data;

    } catch (Exception $e) {
      // do nothing falls through to false, didn't complete
    }
  }

  public function getClientId() {
    return $this->clientId;
  }

  public function setClientId($clientId) {
    $this->clientId = $clientId;
  }

  public function getClientSecret() {
    return $this->clientSecret;
  }

  public function setClientSecret($clientSecret) {
    $this->clientSecret = $clientSecret;
  }

  public function getAccountStorage() {
    return $this->accountStorage;
  }

  public function setAccountStorage($accountStorage) {
    $this->accountStorage = $accountStorage;
  }
}
?>