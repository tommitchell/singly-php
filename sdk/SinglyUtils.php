<?php
define("SINGLY_SCHEME", "https");
define("SINGLY_HOST", "api.singly.com");

function getSinglyScheme() {
  return SINGLY_SCHEME;
}

function getSinglyHost() {
  return SINGLY_HOST;
}

/**
 * Creates a url using the base Singly api url, the path, and the query
 * parameters specified.
 * 
 * The url is assumed to be in UTF-8 format.  The query parameters are
 * not required.
 * 
 * @param scheme The url scheme.
 * @param host The url hostname.
 * @param path The url path.
 * @param qparams The optional url query parameters.
 * 
 * @return A formatted, UTF-8 singly url string.
 */
function createURL($scheme, $host, $path, $queryParams) {

  // query parameters are optional
  $url = $scheme . "://" . $host;
  if (!empty($path)) {
    $url .= $path;
  }
  if (!empty($queryParams)) {
    $url .= "?" . http_build_query($queryParams);
  }

  return $url;
}

/**
 * Creates a url using the base Singly api url, the path, and the query
 * parameters specified.
 * 
 * The query parameters are not required.
 * 
 * @param path The url path.
 * @param queryParams The optional url query parameters.
 * 
 * @return A formatted singly url string.
 */
function createSinglyURL($path, $queryParams) {

  // create the formatted url
  return createURL(SINGLY_SCHEME, SINGLY_HOST, $path, $queryParams);
}

?>