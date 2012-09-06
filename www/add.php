<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'Add ';
 $page['action'] = 'Add';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (!$lm->o_login) {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "You should be logged in to access this page...");
   goto screen;
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'suser':
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
         $a = Act::add('Added the SSH User: '.$obj->username, 'login', $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "SSH User $obj has been added to database");
         goto screen;
       }
     break;
     case 'sgroup':
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
         $a = Act::add('Added the Server Group: '.$obj->name, 'login', $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Server Group $obj has been added to database");
         goto screen;
       }
     break;
     case 'ugroup':
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
         $a = Act::add('Added the User Group: '.$obj->name, 'login', $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "User Group $obj has been added to database");
         goto screen;
       }
     break;
     case 'check':
       $what = 'Check';
       $obj = new Check();
       $content = new Template('../tpl/form_check.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('name', 'description', 'f_root', 'frequency', 'm_error', 'm_warn', 'lua');
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
         $a = Act::add('Added the Check: '.$obj->name, 'login', $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Check $obj has been added to database");
         goto screen;
       }
     break;
     case 'pserver':
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
         $a = Act::add('Added the Physical Server: '.$obj->name, 'login', $lm->o_login);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Physical Server $obj has been added to database");
         goto screen;
       }
     break;
     case 'cluster':
       $what = 'Cluster';
       $obj = new Cluster();
       $content = new Template('../tpl/form_cluster.tpl');
       $a_os = OS::getAll(true, array(), array('ASC:name'));
       $content->set('oses', $a_os);
       $js = array('cluster.js');
       $foot->set('js', $js);
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
	     $a_server = Server::getAll(true, array('CST:'.$obj->fk_os => 'fk_os'), array('ASC:hostname'));
	     $content->set('a_server', $a_server);
	   }
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->insert();
         $a = Act::add('Added the Cluster', 'cluster', $obj);
	 $a->fk_login = $lm->o_login->id;
	 $a->update();
	 foreach($obj->a_server as $s) {
	   $s->fk_cluster = $obj->id;
	   $s->update();

           $a = Act::add('Added the server '.$s->hostname.' as node to the cluster', 'cluster', $obj);
	   $a->fk_login = $lm->o_login->id;
	   $a->update();
	 }
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Cluster $obj has been added to database");
         goto screen;
       }
     break;
     case 'server':
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
         $a = Act::add('Added the server ', 'server', $obj);
	 $a->fk_login = $lm->o_login->id;
	 $a->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Server $obj has been added to database");
         goto screen;
       }
     break;
     case 'user':
       if (!$lm->o_login->f_admin) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You should be administrator in to access this page...");
         goto screen;
       }
       $what = 'User';
       $obj = new Login();
       $content = new Template('../tpl/form_user.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('fullname', 'email', 'username', 'password', 'password_c', 'f_admin', 'f_ldap');
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
	 $obj->bcrypt($obj->password);
         $obj->insert();
         $a = Act::add('Added the user: '.$obj->username, 'login', $lm->o_login);
         $content = new Template('../tpl/message.tpl');
	 $content->set('msg', "User $obj has been added to database");
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
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>