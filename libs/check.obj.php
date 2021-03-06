<?php
/**
 * Check object
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
class Check extends MySqlObj
{
  use logTrait;
    public static $RIGHT = 'CHK';

    public $id = -1;
    public $name = '';
    public $description = '';
    public $frequency = 0;
    public $lua = <<<CODE
function check()
    return 0;
end
CODE;
    public $m_error = '';
    public $m_warn = '';
    public $f_noalerts = 0;
    public $f_text = 0;
    public $f_root = 0;
    public $f_vm = 1;
    public $t_add = -1;
    public $t_upd = -1;
    public $obj = null;

    public $a_sgroup = array();
    public $f_except = array();

    public function link()
    {
        return '<a href="/view/w/check/i/'.$this->id.'">'.$this.'</a>';
    }

    public function valid($new = true)
    { /* validate form-based fields */
        $ret = array();

        if (empty($this->name)) {
            $ret[] = 'Missing Name';
        } else {
            if ($new) { /* check for already-exist */
                $check = new Check();
                $check->name = $this->name;
                if (!$check->fetchFromField('name')) {
                    $this->name = '';
                    $ret[] = 'Check Name already exist';
                    $check = null;
                }
            }
        }

        /* @TODO: Add LUA Validation code */

        if (count($ret)) {
            return $ret;
        } else {
            return;
        }
    }

    public function equals($z)
    {
        if (!strcmp($this->name, $z->name)) {
            return true;
        }
        return false;
    }


    public function getObjectList($name) {
        $ret = array(null);
        foreach($this->obj->{$name} as $i) {
            $ret[] = ''.$i;
        }
        unset($ret[0]);
        return $ret;
    }

  private function getLuaObject(&$s) {
      $lua = new Lua();
      $lua->registerCallback('exec', array(&$s, 'exec'));
      $lua->registerCallback('getObjectList', array(&$this, 'getObjectList'));
      $lua->registerCallback('execLUA', array(&$s, 'execLUA'));
      $lua->registerCallback('findBin', array(&$s, 'findBin'));
      $lua->registerCallback('isFile', array(&$s, 'isFile'));
      return $lua;
  }

  private function getStatusFromRet($ret) {

          $rc = -1;
          $msg = 'Check failed';
          if (!empty($ret)) {
              if (is_numeric($ret)) {
                  $rc = $ret;
                  $msg = '';
              } elseif (preg_match('/^([^:]*):([^$]*)/', $ret, $match)) {
                  if (is_numeric($match[1])) {
                      $rc = $match[1];
                      if (isset($match[2])) {
                          $msg = $match[2];
                      }
                  } else {
                      $rc = -1;
                  }
              }
          } else {
              $rc = 0;
              $msg = '';
          }
      return array('rc' => $rc, 'msg' => $msg);
  }

  private function updateResult(&$r, $ret) {
      // update message accordingly
      switch ($r->rc) {
        case 0:
          $r->f_ack = 1;
          $r->message = '';
          $r->details = $ret['msg'];
        break;
        case -1: // WARNING
          $r->f_ack = 0;
          $r->message = $this->m_warn;
          $r->details = $ret['msg'];
        break;
        case -2: // ERROR
          $r->f_ack = 0;
          $r->message = $this->m_error;
          $r->details = $ret['msg'];
        break;
        default:
          $r->f_ack = 0;
          $r->message = 'Unexpected return code, please check deeper...';
          $r->details = $ret['msg'];
        break;
      }
      return;
  }

  /* Call the proper method */
  public function doCheck(&$s)
  {
      if ($this->isLocked($s)) {
          $s->log("$this is already locked for $s ", LLOG_ERR);
          return -1;
      }

      if (($rc = $this->lockCheck($s))) {
          $s->log("$this Cannot acquire lock for $s ($rc)", LLOG_ERR);
          return -1;
      }

      switch(get_class($s)) {
          case 'Server':
              $fk = 'fk_server';
              break;
          case 'VM':
              $fk = 'fk_vm';
              break;
          default:
              throw new SPXException('doCheck(): unsupported server class');
              break;
      }

      $lua = $this->getLuaObject($s);
      $this->obj = $s;
      try {
          $lua->eval($this->lua);
          $ret = $lua->call("check");
          $this->obj = null;
          $ret = $this->getStatusFromRet($ret);
          $rc = $ret['rc'];
          $msg = $ret['msg'];
          $s->log("$this check on $s returned value: $rc ($msg)", LLOG_DEBUG);

          $r = new Result();
          $r->fk_check = $this->id;
          $r->{$fk} = $s->id;
          $r->rc = $rc;
          $this->updateResult($r, $ret);
          $done = false;
          if (isset($s->a_lr[$this->id]) &&
              $s->a_lr[$this->id]) {
              $s->log("Last result found for $this / $s", LLOG_DEBUG);
              if ($r->equals($s->a_lr[$this->id])) { /* same result, only update t_upd */
                  $s->a_lr[$this->id]->t_upd = time();
                  $s->a_lr[$this->id]->update();
                  $done = true;
                  $s->log("We only updated check timestamp for $this / $s", LLOG_DEBUG);
              }
          } else {
              $s->log("Check result not found for $this / $s", LLOG_DEBUG);
          }
          if (!$done) { // new check result
              $oldcr = null;
              if (isset($s->a_lr[$this->id])) {
                  $oldcr = $s->a_lr[$this->id];
              }
              $s->a_lr[$this->id] = $r;
              $s->a_lr[$this->id]->insert();
              if (!$this->f_noalerts) {
                  Notification::sendResult($s, $r, $oldcr);
              }
          }
      } catch (Exception $e) {
          $s->log("Error with LUA code: $e", LLOG_ERR);
          $rc = -1;
      }

      $this->unlockCheck($s);

      return $rc;
  }

  /* Check locking */
  public function lockCheck(&$obj)
  {
      $m = MySqlCM::getInstance();
      $pid = Pid::getMyPid();
      if (!$pid) {
          $pid = Pid::addMyPid();
          if (!$pid) {
              return -1;
          }
      }
      $args = array('idPid' => $pid->id,
                    'idCheck' => $this->id);

      switch(get_class($obj)) {
          case 'Server':
              $args['idVM'] = -1;
              $args['idServer'] = $obj->id;
              break;
          case 'VM':
              $args['idVM'] = $obj->id;
              $args['idServer'] = -1;
              break;
      }
      $ret = array('rc' => -1);
      if ($m->call('lockCheck', $args, $ret)) {
          return -1;
      }
      if ($ret['rc']) {
          return $ret['rc'];
      }
      return 0;
  }

    public function unlockCheck(&$obj)
    {
      $m = MySqlCM::getInstance();
      $args = array('idCheck' => $this->id);

      switch(get_class($obj)) {
          case 'Server':
              $args['idVM'] = -1;
              $args['idServer'] = $obj->id;
              break;
          case 'VM':
              $args['idVM'] = $obj->id;
              $args['idServer'] = -1;
              break;
      }
      $ret = array();
      if ($m->call('unlockCheck', $args, $ret)) {
          return -1;
      }
      return 0;
    }

    public function isLocked(&$obj)
    {
        $cl = new Lock();
        $cl->fk_check = $this->id;
        $fk = $cl->setIt($obj);
        if ($fk === false) {
            return false;
        }
        if ($cl->fetchFromFields(array('fk_check', $fk))) {
            return false;
        }
        return true;
    }

    public function fetchAll($all = 1)
    {
        try {
            $s->fetchJT('a_sgroup');
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function __toString()
    {
        $rc = $this->name;

        return $rc;
    }

    public function dump($s)
    {
    }

    public static function printCols($cfs = array())
    {
        return array('Name' => 'name',
                 'Description' => 'description',
                 'Frequency' => 'frequency',
                 'Need Root' => 'f_root',
                 'Support VM' => 'f_vm',
                 'Alerts Disabled' => 'f_noalerts',
                 'Send Text' => 'f_text',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'name' => $this->name,
                 'description' => $this->description,
                 'frequency' => Utils::parseFrequency($this->frequency),
                 'f_root' => $this->f_root,
                 'f_vm' => $this->f_vm,
                 'f_noalerts' => $this->f_noalerts,
                 'f_text' => $this->f_text,
                );
    }

    public function htmlDump()
    {
        $ret = array(
        'Name' => $this->name,
        'Description' => $this->description,
        'Error Message' => $this->m_error,
        'Warning Message' => $this->m_warn,
        'Frequency' => Utils::parseFrequency($this->frequency),
        'Send Text?' => ($this->f_text) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'No Alerts?' => ($this->f_noalerts) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'Need root?' => ($this->f_root) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'VM Support?' => ($this->f_vm) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'Updated on' => date('d-m-Y', $this->t_upd),
        'Added on' => date('d-m-Y', $this->t_add),
    );

        return $ret;
    }

    public function delete()
    {
        parent::_delAllJT();
        parent::delete();
    }

    public static function vm(&$s)
    {
        if (!count($s->a_check)) {
            return;
        }

        foreach ($s->a_check as $c) {
            $s->log("Launching $c ...", LLOG_INFO);
            $c->doCheck($s);
            $s->log("$c done", LLOG_DEBUG);
        }
    }

    public static function server(&$s)
    {
        if (!count($s->a_check)) {
            return;
        }

        foreach ($s->a_check as $c) {
            $s->log("Launching $c ...", LLOG_INFO);
            $c->doCheck($s);
            $s->log("$c done", LLOG_DEBUG);
        }
    }

    public static function jobVM(&$job, $sid)
    {
        $s = new VM($sid);
        if ($s->fetchFromId()) {
            throw new SPXException('VM not found in database');
        }
        $s->_job = $job;

        if (strcmp($s->status, 'running')) {
            $s->log($s.' is not in running state, exiting', LLOG_ERR);
            return;
        }   

        $s->fetchJT('a_sgroup');
        if ($job && $job->fk_login > 0) {
            $s->buildCheckList(true);
        } else {
            $s->buildCheckList();
        }

        if (!count($s->a_check)) {
            $s->log("No checks to be done on $s, skipping...", LLOG_INFO);
            return;
        }

        try {
            $s->log("Connecting to $s", LLOG_INFO);
            $s->connect();
            $s->log("Launching the checks", LLOG_DEBUG);
            Check::vm($s);
            $s->log("Disconnecting from VM", LLOG_INFO);
            $s->disconnect();
        } catch (Exception $e) {
            throw($e);
        }
    }


    public static function jobServer(&$job, $sid)
    {
        $s = new Server($sid);
        if ($s->fetchFromId()) {
            throw new SPXException('Server not found in database');
        }
        $s->_job = $job;
        $s->fetchJT('a_sgroup');
        if ($job && $job->fk_login > 0) {
            $s->buildCheckList(true);
        } else {
            $s->buildCheckList();
        }

        if (!count($s->a_check)) {
            $s->log("No checks to be done on $s, skipping...", LLOG_INFO);

            return;
        }

        try {
            $s->log("Connecting to $s", LLOG_INFO);
            $s->connect();
            $s->log("Launching the checks", LLOG_DEBUG);
            Check::server($s);
            $s->log("Disconnecting from server", LLOG_INFO);
            $s->disconnect();
        } catch (Exception $e) {
            throw($e);
        }
    }

    public static function vmChecks(&$job)
    {
        $s_vm = Setting::get('vm', 'enable');

        $slog = new VM();
        $slog->_job = $job;
               
        if (!$s_vm || $s_vm->value != 1) {
            Logger::log("VM Support is not enabled", $slog, LLOG_ERR);
            return -1;
        }
     
        $table = "`list_vm`";
        $index = "`id`";
        $cindex = "COUNT(`id`)";
        $where = "WHERE `status`='running' AND `hostname`!='' AND `fk_os`!=-1 AND `fk_suser`!=-1 AND `f_upd`='1'";
        $it = new mIterator('VM', $index, $table, array('q' => $where, 'a' => array()), $cindex);

        while (($s = $it->next())) {
            $s->fetchFromId();
            $s->fetchJT('a_sgroup');
            $s->buildCheckList();
            if (!count($s->a_check)) { /* check if there are checks to be done for this VM before adding a job */
                continue;
            }
            $j = new Job();
            $j->class = 'Check';
            $j->fct = 'jobVM';
            $j->arg = $s->id;
            $j->state = S_NEW;
            $j->insert();
            Logger::log("Added job to check VM $s", $slog, LLOG_INFO);
        }
    }


    public static function serverChecks(&$job)
    {
        $table = "`list_server`";
        $index = "`id`";
        $cindex = "COUNT(`id`)";
        $where = "WHERE `f_upd`='1'";
        $it = new mIterator('Server', $index, $table, array('q' => $where, 'a' => array()), $cindex);
        $slog = new Server();
        $slog->_job = $job;

        while (($s = $it->next())) {
            $s->fetchFromId();
            $s->fetchJT('a_sgroup');
            $s->buildCheckList();
            if (!count($s->a_check)) { /* check if there are checks to be done for this server before adding a job */
                Logger::log("Skipping job for server $s", $slog, LLOG_INFO);
                continue;
            }
            $j = new Job();
            $j->class = 'Check';
            $j->fct = 'jobServer';
            $j->arg = $s->id;
            $j->state = S_NEW;
            $j->insert();
            Logger::log("Added job to check server $s", $slog, LLOG_INFO);
        }
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_check';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE,
                        'description' => SQL_PROPE,
                        'frequency' => SQL_PROPE,
                        'lua' => SQL_PROPE,
                        'm_error' => SQL_PROPE,
                        'm_warn' => SQL_PROPE,
                        'f_noalerts' => SQL_PROPE,
                        'f_text' => SQL_PROPE,
                        'f_root' => SQL_PROPE,
                        'f_vm' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'description' => 'description',
                        'frequency' => 'frequency',
                        'lua' => 'lua',
                        'm_error' => 'm_error',
                        'm_warn' => 'm_warn',
                        'f_noalerts' => 'f_noalerts',
                        'f_text' => 'f_text',
                        'f_root' => 'f_root',
                        'f_vm' => 'f_vm',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

    $this->_log = Logger::getInstance();
                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_sgroup', 'SGroup', 'jt_check_sgroup', array('id' => 'fk_check'), array('id' => 'fk_sgroup'), array('f_except'));
  }
}
