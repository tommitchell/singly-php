<?php
/**
 * The contract for storing and retrieving Singly access tokens.
 * 
 * An application that uses the Singly API, such as a web application, will be
 * storing many access tokens, usually one per account.  This interface allows
 * developers to create their own implementations to store and retrieve those
 * Singly access tokens on a per account basis.
 * 
 * The  SinglyService uses a SinglyAccountStorage implementation during
 * its authentication processing and when making calls to the Singly API.
 * 
 * @see InMemorySinglyAccountStorage
 */
interface SinglyAccountStorage {

  /**
   * Saves a Singly access token for a given account. In almost all applications 
   * there is one access token per account.
   * 
   * @param account The account the access token belongs to.
   * 
   * @param accessToken
   */
  public function saveAccessToken($account, $accessToken);
  
  /**
   * Returns true if an access token has been stored for the account.
   * 
   * @param account The account to check for an access token.
   * 
   * @return True if an access token has been stored.  False otherwise.
   */
  public function hasAccessToken($account);
  
  /**
   * Get the access token for a given account.
   * 
   * @param account The account for which to get the access token.
   * 
   * @return The account's access token.
   */
  public function getAccessToken($account);
  
  /**
   * Remove the access token from storage.
   * 
   * @param account The account for which to remove the access token.
   */
  public function removeAccessToken($account);  

}
?>