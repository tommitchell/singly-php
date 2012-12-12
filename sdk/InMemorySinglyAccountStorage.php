<?php
require_once("SinglyAccountStorage.php"); 

/**
 * A simple SinglyAccountStorage implementation that stores access
 * tokens in memory.  
 * 
 * This class does not persist access tokens beyond the lifetime of the 
 * application. It is the default SinglyAccountStorage for the 
 * SinglyServiceImpl class. Most applications will want to create 
 * their own implementation and override the default.
 */
class InMemorySinglyAccountStorage
  implements SinglyAccountStorage {

  private $accounts = array();

  public function saveAccessToken($account, $accessToken) {
    $this->accounts[$account] = $accessToken;
  }

  public function hasAccessToken($account) {
    return array_key_exists($account, $this->accounts);    
  }

  public function getAccessToken($account) {
    return $this->accounts[$account];
  }

  public function removeAccessToken($account) {
    unset($this->accounts[$account]);
  }

}
?>