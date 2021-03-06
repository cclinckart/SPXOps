<?php
/**
 * OS object
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
class OS extends MySqlObj
{
  public $id = -1;
    public $name = '';
    public $uname = '';
    public $class = '';
    public $f_zone = 0;

    public static $binPaths = array(
    "/bin",
    "/sbin",
    "/usr/bin",
    "/usr/sbin",
  );

    public static function detect($s)
    {
        if (!$s) {
            throw new SPXException('No server provided');
        }

        try {
            $uname = $s->findBin('uname', OS::$binPaths);
            $r = $s->exec($uname);
            $oso = new OS();
            $oso->uname = $r;
            if ($oso->fetchFromField('uname')) {
                throw new SPXException('OS unknown: '.$r);
            }

            return $oso;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function dump($s)
    {
        $oclass = $this->class;
        $hostid = $s->data('os:hostid');

        $oclass::dump($s);

        if (!empty($hostid)) {
            $s->log(sprintf("%15s: %s", 'Hostid', $hostid), LLOG_INFO);
        }
    }

    public function htmlDump($s)
    {
        $oclass = $this->class;
        $spec = $oclass::htmlDump($s);

        $myar = array(
        'OS Name' => $this->name,
            );

        return array_merge($myar, $spec);
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = "list_os";
      $this->_nfotable = null;
      $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "uname" => SQL_PROPE,
                        "class" => SQL_PROPE,
                        "f_zone" => SQL_PROPE,
                 );

      $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "uname" => "uname",
                        "class" => "class",
                        "f_zone" => "f_zone",
                 );
  }
}
