<?php
/**
 * Live RRD Display
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage rrd
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
 $page['title'] = 'RRD Live ';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException(null, EXIT_LOGIN);
 }
 if (!$lm->o_login->cRight('SRV', R_VIEW)) {
   throw new ExitException('Access Denied, please check your access rights!');
 }
 $a_s = Server::getAll();
 foreach($a_s as $s) { $s->fetchRL('a_rrd'); }

 $content = new Template('../tpl/rrdlive.tpl');
 $page['title'] .= 'Live RRD';
 $content->set('a_s', $a_s);
 $js = array('liverrd.js', 'jquery.jqplot.min.js', 'jqplot.highlighter.min.js', 'jqplot.logAxisRenderer.min.js', 'jqplot.dateAxisRenderer.min.js');
 $css = array('jquery.jqplot.min.css');
 $head->set('js', $js);
 $head->set('css', $css);

screen:
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $head->set('page', $page);
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
