<?php
/**
 * Result object
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
class Result extends MySqlObj
{
    public $id = -1;
    public $rc = 0;
    public $message = '';
    public $details = '';
    public $f_ack = 0;
    public $fk_check = -1;
    public $fk_server = -1;
    public $fk_vm = -1;
    public $fk_login = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_check = null;
    public $o_server = null;
    public $o_vm = null;
    public $o_login = null;

    public static function getHash($c, $o) {
        $hs = '';
        if ($c->fk_server > 0) {
            $fk = 'fk_server';
        } else {
            $fk = 'fk_vm';
        }
        if ($c->rc < 0) {
            $hs .= $c->id.$c->fk_check.$c->{$fk};
        } else {
            $hs .= $o->id.$o->fk_check.$o->{$fk};
        }
        return md5($hs);
    }

    public function equals($r)
    {
        if ($r->rc == $this->rc &&
            $r->fk_check == $this->fk_check &&
            $r->fk_server == $this->fk_server &&
            $r->fk_vm == $this->fk_vm &&
            !strcmp($r->details, $this->details)) {
            return true;
        }

        return false;
    }

    public function ackBy()
    {
        if (!$this->o_login && $this->fk_login > 0) {
            $this->fetchFK('fk_login');
        }
        if ($this->f_ack) {
            if ($this->o_login) {
                return $this->o_login->link().'<button type="button" class="close" onClick="nackCheck('.$this->id.');">×</button>';
            } else {
                return 'N/A';
            }
        } else {
            return '<button type="button" class="btn-xs btn btn-primary btn-mini" onClick="ackCheck('.$this->id.');">Ack!</button>';
        }
    }

    public static function getLast($c, $s)
    {
        $sort = array('DESC:t_upd');
        $filter = array();

        $filter['fk_check'] = 'CST:'.$c->id;
        switch(get_class($s)) {
            case 'Server':
                $filter['fk_server'] = 'CST:'.$s->id;
            break;
            case 'VM':
                $filter['fk_vm'] = 'CST:'.$s->id;
            break;
            default:
                throw new SPXException('Result::getLast: $s unsupported type');
            break;
        }

        $r = Result::getAll(true, $filter, $sort, 0, 1);
        if (count($r)) {
            $r = $r[0];

            return $r;
        }
        return;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_vm && $this->fk_vm > 0) {
                $this->fetchFK('fk_vm');
            }

            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }

            if (!$this->o_check && $this->fk_check > 0) {
                $this->fetchFK('fk_check');
            }

            if (!$this->o_login && $this->fk_login > 0) {
                $this->fetchFK('fk_login');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public static function printCols($cfs = array())
    {
        return array('Check' => 'check',
                 'Server' => 'server',
                 'Result' => '_color',
                 'Message' => 'message',
                 'Checked at' => 't_upd',
                );
    }

    public static function colorRC($rc, $ack = false)
    {
        $c_rc = 'error';
        if ($rc >= 0 && !$ack) {
            $c_rc = 'success';
        } elseif ($rc == -1) {
            $c_rc = 'warning';
        } elseif ($rc == -2) {
            $c_rc = 'danger';
        } elseif ($ack) {
            $c_rc = 'info';
        }

        return $c_rc;
    }

    public function toArray($cfs = array())
    {
        if (!$this->o_server && $this->fk_server > 0) {
            $this->fetchFK('fk_server');
        }
        if (!$this->o_vm && $this->fk_vm > 0) {
            $this->fetchFK('fk_vm');
        }
        if (!$this->o_check && $this->fk_check > 0) {
            $this->fetchFK('fk_check');
        }

        $ret = array(
                 '_color' => Result::colorRC($this->rc),
                 'check' => $this->o_check->link(),
                 'message' => $this->message,
                 't_upd' => date('d-m-Y H:i:s', $this->t_upd),
                );

        if ($this->o_server) {
           $ret['server'] = $this->o_server->link();
        }
        if ($this->o_vm) {
           $ret['server'] = $this->o_vm->link();
        }
        return $ret;
    }

    public function __toString()
    {
        try {
            if (!$this->o_vm && $this->fk_vm > 0) {
                $this->fetchFK('fk_vm');
            }
            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }
            if (!$this->o_check && $this->fk_check > 0) {
                $this->fetchFK('fk_check');
            }
        } catch (Exception $e) {
            echo '';
        }

        if ($this->o_server) {
            $rc = $this->o_check.'/'.$this->o_server.'='.$this->rc;
        } else if ($this->o_vm) {
            $rc = $this->o_check.'/'.$this->o_vm.'='.$this->rc;
        } else {
            $rc = $this->o_check.'/null='.$this->rc;
        }

        return $rc;
    }

    public function html()
    {
        $rc = '';
        try {
            if (!$this->o_vm && $this->fk_vm > 0) {
                $this->fetchFK('fk_vm');
            }
            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }
            if (!$this->o_check && $this->fk_check > 0) {
                $this->fetchFK('fk_check');
            }
            if ($this->o_server) {
                $rc = $this->o_check->link().'/'.$this->o_server->link().'='.Result::colorRC($this->rc);
            } elseif ($this->o_vm) {
                $rc = $this->o_check->link().'/'.$this->o_vm->link().'='.Result::colorRC($this->rc);
            } else {
                $rc = $this->o_check->link().'/null='.Result::colorRC($this->rc);
            }
        } catch (Exception $e) {
            echo '';
        }

        return $rc;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_result';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'rc' => SQL_PROPE,
                        'message' => SQL_PROPE,
                        'details' => SQL_PROPE,
                        'f_ack' => SQL_PROPE,
                        'fk_check' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        'fk_vm' => SQL_PROPE,
                        'fk_login' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'rc' => 'rc',
                        'message' => 'message',
                        'details' => 'details',
                        'f_ack' => 'f_ack',
                        'fk_check' => 'fk_check',
                        'fk_server' => 'fk_server',
                        'fk_vm' => 'fk_vm',
                        'fk_login' => 'fk_login',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");
      $this->_addFK("fk_vm", "o_vm", "VM");
      $this->_addFK("fk_check", "o_check", "Check");
      $this->_addFK("fk_login", "o_login", "Login");
  }
}
