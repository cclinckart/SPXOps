<?php
/**
 * Add various objects Page
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage data
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
 $page['title'] = 'Add ';
 $page['action'] = 'Add';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException(null, 6);
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'suser':
       if (!$lm->o_login->cRight('CUSER', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'SSH User';
       $obj = new SUser();
       $content = new Template('../tpl/form_suser.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('description', 'pubkey', 'username', 'password');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         $a = Act::add('Added the SSH User: '.$obj->username, $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "SSH User $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/susers',
                    'name' => 'Back to list of connect users',
                   ),
              );
         goto screen;
       }
     break;
     case 'sgroup':
       if (!$lm->o_login->cRight('SRVGRP', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Server Group';
       $obj = new SGroup();
       $content = new Template('../tpl/form_sgroup.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('name', 'description');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         $a = Act::add('Added the Server Group: '.$obj->name, $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Server Group $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/sgroup',
                    'name' => 'Back to list of server groups',
                   ),
              );
         goto screen;
       }
     break;
     case 'rjob':
       if (!$lm->o_login->cRight('RJOB', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Recurrent Job';
       $obj = new RJob();
       $content = new Template('../tpl/form_rjob.tpl');
       $page['title'] .= $what;
       $obj->fk_login = $lm->o_login->id;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('class', 'fct', 'frequency', 'arg');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         $a = Act::add('Added the Recurrent Job: '.$obj, $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Recurrent Job $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/rjob',
                    'name' => 'Back to list of recurrent jobs',
                   ),
              );
         goto screen;
       }
     break;
     case 'utoken':
         if ($lm->o_login->fk_utoken > 0) {
             throw new ExitException('There is already a token associated with this account');
         }
         /* being logged-in is enough to add a user token, so don't do right check here */
         $what = 'User Token';
         $obj = new UToken();
         $content = new Template('../tpl/form_utoken.tpl');
         $page['title'] .= $what;
         if (isset($_POST['submit'])) { /* clicked on the Add button */
             $self = $lm->o_login;
             $fields = array('secret', 'counter', 'type', 'digit');
             foreach($fields as $field) {
               if (!strncmp($field, 'f_', 2)) { // should be a checkbox
                 if (isset($_POST[$field])) {
                   $obj->{$field} = 1;
                 } else {
                   $obj->{$field} = 0;
                 }
               } else {
                 if (isset($_POST[$field])) {
                   $obj->{$field} = $_POST[$field];
                 }
               }
             }
             $errors = $obj->valid();
             if ($errors) {
               $content->set('error', $errors);
               $content->set('obj', $obj);
               goto screen;
             }
             $obj->insert();
             $self->fk_utoken = $obj->id;
             $self->update();
             $a = Act::add('Added an User Token for himself', $lm->o_login);
             $content = new Template('../tpl/message.tpl');
             $content->set('msg', "User Token has been added to database");
             $a_link = array(
                  array('href' => '/view/w/login/i/self',
                        'name' => 'Back to My Profile',
                       ),
                  );
 
         }
     break;
     case 'ugroup':
       if (!$lm->o_login->cRight('UGRP', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'User Group';
       $obj = new UGroup();
       $content = new Template('../tpl/form_ugroup.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('name', 'description');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         $a = Act::add('Added the User Group: '.$obj->name, $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "User Group $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/ugroup',
                    'name' => 'Back to list of user users groups',
                   ),
              );
         goto screen;
       }
     break;
     case 'check':
       if (!$lm->o_login->cRight('CHK', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Check';
       $obj = new Check();
       $content = new Template('../tpl/form_check.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('name', 'description', 'f_text', 'f_noalerts', 'f_vm', 'f_root', 'frequency', 'm_error', 'm_warn', 'lua');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         $a = Act::add('Added the Check: '.$obj->name, $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Check $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/check',
                    'name' => 'Back to list of checks',
                   ),
              );
         goto screen;
       }
     break;
     case 'pserver':
       if (!$lm->o_login->cRight('PHY', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Physical Server';
       $obj = new PServer();
       $content = new Template('../tpl/form_pserver.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('name');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         $a = Act::add('Added the Physical Server: '.$obj->name, $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Physical Server $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/pserver',
                    'name' => 'Back to list of physical servers',
                   ),
              );
         goto screen;
       }
     break;
     case 'cluster':
       if (!$lm->o_login->cRight('CLUSTER', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Cluster';
       $obj = new Cluster();
       $content = new Template('../tpl/form_cluster.tpl');
       $a_os = OS::getAll(true, array(), array('ASC:name'));
       $content->set('oses', $a_os);
       $js = array('cluster.js');
       $head->set('js', $js);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('name', 'description', 'f_upd', 'a_server', 'fk_os');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if (isset($_POST[$field])) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           if (isset($obj->fk_os) && is_numeric($obj->fk_os) && $obj->fk_os > 0) {
	     $a_server = Server::getAll(true, array('fk_os' => 'CST:'.$obj->fk_os), array('ASC:hostname'));
	     $content->set('a_server', $a_server);
	   }
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         Act::add("Added the Cluster: $obj", $lm->o_login);
	 foreach($obj->a_server as $s) {
	   $s->fk_cluster = $obj->id;
	   $s->update();
           Act::add('Added the server '.$s->hostname." as node to the cluster: $obj", $lm->o_login);
	 }
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Cluster $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/cluster',
                    'name' => 'Back to list of clusters',
                   ),
              );
         goto screen;
       }
     break;
     case 'server':
       if (!$lm->o_login->cRight('SRV', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Server';
       $obj = new Server();
       $content = new Template('../tpl/form_server.tpl');
       $a_suser = SUser::getAll(true, array(), array('ASC:username'));
       $a_pserver = PServer::getAll(true, array(), array('ASC:name'));
       $content->set('susers', $a_suser);
       $content->set('pservers', $a_pserver);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('hostname', 'description', 'fk_pserver', 'fk_suser', 'f_rce', 'f_upd');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
	 if ($obj->fk_pserver == -2) { /* should be added */
	   $ps = new PServer();
	   $ps->name = $obj->hostname;
	   $ps->insert();
	   $obj->fk_pserver = $ps->id;
	 }
         $obj->insert();
         Act::add("Added the server: $obj", $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Server $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/server',
                    'name' => 'Back to list of servers',
                   ),
              );
         goto screen;
       }
     break;
     case 'login':
       if (!$lm->o_login->cRight('USR', R_ADD)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'User';
       $obj = new Login();
       $content = new Template('../tpl/form_user.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('fullname', 'email', 'username', 'phone', 'password', 'password_c', 'f_noalerts', 'f_admin', 'f_api');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid();
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         /* Must crypt the password */
         $obj->encryptPassword($obj->password);
         $obj->insert();
         $a = Act::add('Added the user: '.$obj->username, $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "User $obj has been added to database");
         $a_link = array(
              array('href' => '/list/w/login',
                    'name' => 'Back to list of users',
                   ),
          );
         goto screen;
       }
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
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

} catch (ExitException $e) {

    if ($e->type == EXIT_LOGIN) { /* login needed */
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
