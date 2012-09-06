<?php
/**
 * UGroup object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class UGroup extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $description = '';
  public $t_add = -1;
  public $t_upd = -1;

  public $a_login = array();

  public function __toString() {
    return $this->name;
  }

  public function equals($z) {
    if (!strcmp($this->name, $z->name)) {
      return true;
    }
    return false;
  }


  public function valid($new = true) { /* validate form-based fields */
    global $config;
    $ret = array();

    if (empty($this->name)) {
      $ret[] = 'Missing Name';
    } else {
      if ($new) { /* check for already-exist */
        $check = new UGroup();
        $check->name = $this->name;
        if (!$check->fetchFromField('name')) {
          $this->name = '';
          $ret[] = 'Server Group Name already exist';
          $check = null;
        }
      }
    }

    if (count($ret)) {
      return $ret;
    } else {
      return null;
    }
  }


  public static function printCols() {
    return array('Name' => 'name',
                 'Description' => 'description',
                 'Last Update' => 't_upd',
                );
  }

  public function toArray() {

    return array(
                 'name' => $this->name,
                 'description' => $this->description,
                 't_upd' => date('Y-m-d', $this->t_upd),
                );
  }

  public function htmlDump() {
    return array(
        'Name' => $this->name,
        'Description' => $this->description,
        'Updated on' => date('d-m-Y', $this->t_upd),
        'Added on' => date('d-m-Y', $this->t_add),
    );
  }


  public function link() {
    return '<a href="/view/w/ugroup/i/'.$this->id.'">'.$this.'</a>';
  }


 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "list_ugroup";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "description" => SQL_PROPE,
                        "t_add" => SQL_PROPE,
                        "t_upd" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "description" => "description",
                        "t_add" => "t_add",
                        "t_upd" => "t_upd"
                 );



                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_login', 'Login', 'jt_login_ugroup', array('id' => 'fk_ugroup'), array('id' => 'fk_login'), array());

  }

}
?>