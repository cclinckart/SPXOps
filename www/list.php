<?php
/**
 * List objects
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage management
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
 require_once("../libs/utils.obj.php");

try {

 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   throw new ExitException('An error has occurred with the SQL Server and we were unable to process your request...');
 }
 $lm = LoginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'List of ';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException(null, EXIT_LOGIN);
 }

 $js = array();
 $css = array();
 $npp = 20;
 array_push($css, 'jquery.dataTables.min.css');
 array_push($css, 'dataTables.bootstrap.css');
 array_push($js, 'jquery.dataTables.min.js');
 array_push($js, 'dataTables.bootstrap.js');
 array_push($css, 'dataTables.CSV.css');
 array_push($js, 'dataTables.CSV.js');
 $head->set("js", $js);
 $head->set("css", $css);
 $skipColumn = array();

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'rjob':
       if (!$lm->o_login->cRight('RJOB', R_VIEW)) {
	 throw new ExitException('Access Denied, please check your access rights!');
       }
       $a_list = RJob::getAll(true, array(), array('ASC:class', 'ASC:fct'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login->cRight('RJOB', R_ADD)) {
         $actions = array(
                        'Add' => '/add/w/rjob',
                    );
         $content->set('actions', $actions);
       }
       if ($lm->o_login->cRight('RJOB', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('RJOB', R_EDIT)) $content->set('canMod', true);
       $content->set('canView', true);
       $content->set('what', 'Recurrent Jobs');
       $content->set('oc', 'RJob');
       $page['title'] .= 'Recurrent jobs';
     break;
     case 'sgroup':
       if (!$lm->o_login->cRight('SRVGRP', R_VIEW)) {
	 throw new ExitException('Access Denied, please check your access rights!');
       }
       $a_list = SGroup::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login->cRight('SRVGRP', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('SRVGRP', R_EDIT)) $content->set('canMod', true);

       if ($lm->o_login->cRight('SRVGRP', R_ADD)) {
         $actions = array(
                        'Add' => '/add/w/sgroup',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Server Groups');
       $content->set('oc', 'SGroup');
       $page['title'] .= 'Server Group';
     break;
     case 'ugroup':
       if (!$lm->o_login->cRight('UGRP', R_VIEW)) {
	 throw new ExitException('Access Denied, please check your access rights!');
       }
       $a_list = UGroup::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login->cRight('UGRP', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('UGRP', R_EDIT)) $content->set('canMod', true);
       if ($lm->o_login->cRight('UGRP', R_ADD)) {
         $actions = array(
                        'Add' => '/add/w/ugroup',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'User Groups');
       $content->set('oc', 'UGroup');
       $page['title'] .= 'User Group';
     break;
     case 'pid':
       if (!$lm->o_login->cRight('PID', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       } 
       $a_list = Pid::getAll(true, array(), array('ASC:agent', 'ASC:pid'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canKill', true);
       $content->set('what', 'Daemon Instance');
       $content->set('oc', 'Pid');
       $page['title'] .= 'Daemon Instance';
     break;
     case 'results':
       if (!$lm->o_login->cRight('CHKBOARD', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       } 
       $a_list = Result::getAll(true, array(), array('DESC:t_upd', 'DESC:t_add'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('what', 'Check Results');
       $content->set('notStripped', true);
       $content->set('oc', 'Result');
       $page['title'] .= 'Check Results';
     break;
     case 'check':
       if (!$lm->o_login->cRight('CHK', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $a_list = Check::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login->cRight('CHK', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('CHK', R_EDIT)) $content->set('canMod', true);
       if ($lm->o_login->cRight('CHK', R_ADD)) {
         $actions = array(
                        'Add' => '/add/w/check',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Checks');
       $content->set('oc', 'Check');
       $page['title'] .= 'Checks';
     break;
     case 'pserver':
       if (!$lm->o_login->cRight('PHY', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $npp = Setting::get('display', 'pserverPerPage')->value;
       $a_list = PServer::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login->cRight('PHY', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('PHY', R_ADD)) {
         $actions = array(
                        'Add' => '/add/w/pserver',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Physical Servers');
       $content->set('oc', 'PServer');
       $page['title'] .= 'Physical Servers';
     break;
     case 'vm':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       /* get custom fields for user */
       $cfs = $lm->o_login->getListPref('vm');
       $npp = Setting::get('display', 'vmPerPage')->value;
       $a_list = VM::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'VM');
       $content->set('oc', 'VM');
       $actions = array( 
                      'Display settings' => '/ds/w/vm',
                  );
       $content->set('actions', $actions);
       $content->set('cfs', $cfs);
       $page['title'] .= 'VMs';
     break;
     case 'server':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       /* get custom fields for user */
       $cfs = $lm->o_login->getListPref('server');
       $npp = Setting::get('display', 'serverPerPage')->value;
       $a_list = Server::getAll(true, array(), array('ASC:hostname'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login->cRight('SRV', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('SRV', R_EDIT)) $content->set('canMod', true);
       if ($lm->o_login->cRight('SRV', R_ADD)) {
         $actions = array( 
                        'Add' => '/add/w/server',
                        'Display settings' => '/ds/w/server',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Servers');
       $content->set('oc', 'Server');
       $content->set('cfs', $cfs);
       $page['title'] .= 'Servers';
     break;
     case 'cluster':
       if (!$lm->o_login->cRight('CLUSTER', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $npp = Setting::get('display', 'clusterPerPage')->value;
       $a_list = Cluster::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login->cRight('CLUSTER', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('CLUSTER', R_EDIT)) $content->set('canMod', true);
       if ($lm->o_login->cRight('CLUSTER', R_ADD)) {
         $actions = array(
                        'Add' => '/add/w/cluster',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Clusters');
       $content->set('oc', 'Cluster');
       $page['title'] .= 'Clusters';
     break;
     case 'logs':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $a_list = Log::getAll(true, array('o_class' => 'NOT:Log'), array('DESC:t_add'));
       $content = new Template('../tpl/list.tpl');
       $content->set('canView', true);
       $content->set('a_list', $a_list);
       $content->set('what', 'Logs');
       $content->set('oc', 'Log');
       $page['title'] .= 'Logs';
     break;
     case 'act':
       if (!$lm->o_login->cRight('ACT', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $a_list = Act::getAll(true, array(), array('DESC:t_add'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('what', 'Activities');
       $content->set('oc', 'Act');
       $page['title'] .= 'Activities';
     break;
     case 'jobs':
       if (!$lm->o_login->cRight('JOB', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       } 
       $filter = array();
       /* load Job() */
       new Job();
       if (isset($_GET['filter'])) {
           switch($_GET['filter']) {
               case 'sys':
                   $filter['fk_login'] = 'LT:1';
               break;
               case 'usr':
                   $filter['fk_login'] = 'GT:0';
               break;
               case 'failed':
                   $filter['state'] = S_FAIL;
               break;
               case 'stalled':
                   $filter['state'] = S_STALL;
               break;
               case 'running':
                   $filter['state'] = S_RUN;
               break;
           }
       }
       $npp = Setting::get('display', 'jobPerPage')->value;
       $a_list = Job::getAll(true, $filter, array('DESC:t_upd'), 0, 5000);
       $a_action = array();
       $a_action['Show only failed jobs'] = '/list/w/jobs/filter/failed';
       $a_action['Show only running jobs'] = '/list/w/jobs/filter/running';
       $a_action['Show only stalled jobs'] = '/list/w/jobs/filter/stalled';
       $a_action['Show only system jobs'] = '/list/w/jobs/filter/system';
       $a_action['Show only user jobs'] = '/list/w/jobs/filter/user';
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('actions', $a_action);
       $content->set('canView', true);
       if ($lm->o_login->cRight('JOB', R_DEL)) $content->set('canDel', true);
       $content->set('what', 'Jobs');
       $content->set('oc', 'Job');
       $page['title'] .= 'Jobs (last 5000)';
     break;
     case 'login':
       if (!$lm->o_login->cRight('USR', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       } 
       $npp = Setting::get('display', 'loginPerPage')->value;
       $a_list = Login::getAll(true, array(), array('ASC:username'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       if ($lm->o_login->cRight('USR', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('USR', R_EDIT)) $content->set('canMod', true);
       if ($lm->o_login->cRight('USR', R_ADD)) {
         $actions = array(
			'Add' => '/add/w/login',
	 	    );
	 $content->set('actions', $actions);
       }
       $content->set('what', 'Users');
       $content->set('oc', 'Login');
       $page['title'] .= 'Users';
     break;
     case 'susers':
       if (!$lm->o_login->cRight('CUSER', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $a_list = SUser::getAll(true, array(), array('ASC:username'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       if ($lm->o_login->cRight('CUSER', R_DEL)) $content->set('canDel', true);
       if ($lm->o_login->cRight('CUSER', R_EDIT)) $content->set('canMod', true);
       if ($lm->o_login->cRight('CUSER', R_ADD)) {
         $actions = array(
                        'Add' => '/add/w/suser',
                    );
         $content->set('actions', $actions);
       }
       $content->set('what', 'SSH Users');
       $content->set('oc', 'SUser');
       $page['title'] .= 'SSH Users';
     break;
     default:
       $content = new Template('../tpl/error.tpl');
       $content->set('error', 'Unknown option or not yet implemented');
     break;
   }
 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what to list...");
 }

screen:
 $head->set('page', $page);
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $head_code = '<script type="text/javascript" charset="utf-8">'."\n";
 $head_code .= '		$(document).ready(function() {'."\n";
 $t = '';
 if (count($skipColumn)) {
     $t = '"columnDefs": [ { "targets": [ ';
     $first = true;
     foreach($skipColumn as $c) {
         if (!$first) {
             $t .= ',';
             $first = false;
         }
         $t .= ' '.$c;
     }
     $t .= '], "bSortable": false } ],'."\n";
 }
 $head_code .= "		$('#datatable').dataTable( { \"dom\": \"Vlfrtip\", \"ordering\": false, \"pageLength\": $npp $t } );"."\n";
 $head_code .= '		} ); </script>'."\n";
 $head->set("head_code", $head_code);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

} catch (ExitException $e) {
     
    if ($e->type == 2) { 
        echo Utils::getJSONError($e->getMessage());
    } else if ($e->type == EXIT_LOGIN) { /* login needed */
        LoginCM::requestLogin();
    } else {
        $h = Utils::getHTTPError($e->getMessage());
        echo $h->fetch();
    }    
     
} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception');
    echo $h->fetch();
}
?>
