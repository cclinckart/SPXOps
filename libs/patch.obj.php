<?php
/**
 * Solaris Patch object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class Patch extends MySqlObj
{
  public $id = -1;
    public $patch = '';
    public $fk_server = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;

    public function log($str)
    {
        Logger::log($str, $this);
    }

    public function equals($z)
    {
        if (!strcmp($this->patch, $z->patch) && $this->fk_server && $z->fk_server) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function __toString()
    {
        return $this->patch;
    }

    public static function printCols($cfs = array())
    {
        return array('Patch-ID' => 'patch',
                 'More Info' => 'minfo',
                 'Added on' => 't_add',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'patch' => $this->patch,
                 'minfo' => '<a href="http://wesunsolve.net/patch/id/'.$this->patch.'" target="_blank">info</a>',
                 't_add' => date('d-m-Y', $this->t_add),
                );
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_patch';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'patch' => SQL_PROPE|SQL_EXIST,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'patch' => 'patch',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");
  }
}
