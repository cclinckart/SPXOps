<?php
/**
 * SUser object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class SUser extends mysqlObj
{
  public $id = -1;
  public $username = '';
  public $password = '';
  public $description = '';
  public $pubkey = '';
  public $t_add = -1;
  public $t_upd = -1;

  public function valid($new = true) { /* validate form-based fields */
    global $config;
    $ret = array();

    if (empty($this->username)) {
      $ret[] = 'Missing Username';
    }

    if (!empty($this->pubkey)) {
      if (!$this->pubkey()) {
        $ret[] = 'Specified public key path not found';
      }
    }

    if (count($ret)) {
      return $ret;
    } else {
      return null;
    }
  }

  public function pubkey() {
    if (!file_exists($this->pubkey) || !file_exists($this->pubkey.'.pub')) {
      return false;
    }
    return $this->pubkey;
  }

  public function __toString() {
    return $this->username;
  }


  public static function printCols() {
    return array('Username' => 'username',
                 'Fullname' => 'fullname',
                 'Description' => 'description',
                 'Updated' => 't_upd',
                 'Added' => 't_add',
                );
  }

  public function toArray() {

    return array(
                 'username' => $this->username,
                 'fullname' => $this->fullname,
                 'description' => $this->description,
                 't_add' => date('d-m-Y', $this->t_add),
                 't_upd' => date('d-m-Y', $this->t_upd),
                );
  }


  public function htmlDump($s) {

    return array(
	'SSH User' => $this->username,
    );
  }

  public function dump($s) {
    $s->log(sprintf("%15s: %s", 'User', $this->username.' / '.$this->description), LLOG_INFO);
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "list_suser";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "username" => SQL_PROPE|SQL_EXIST,
                        "password" => SQL_PROPE,
                        "description" => SQL_PROPE,
                        "pubkey" => SQL_PROPE,
                        "t_add" => SQL_PROPE,
                        "t_upd" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "username" => "username",
                        "password" => "password",
                        "description" => "description",
                        "pubkey" => "pubkey",
                        "t_add" => "t_add",
                        "t_upd" => "t_upd"
                 );
  }

}
?>
