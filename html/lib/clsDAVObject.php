<?php 
require_once "../vendor/autoload.php";
require_once "Hydrogen/db/clsDataSource.php";

use Sabre\VObject;
use Symfony\Component\Yaml\Yaml;
//define ("PROJECT_PATH_CONFIG","/var/www/config/");
class DAVObject {
    protected $objectID;
    protected $vobject;
    protected $ds;
    protected $dbconn;
    private $rowdata;
    protected $parenturi;
    protected $parentID;
    protected $modified;

    public function __construct() {
        global $dds;
        $this->ds=$dds;
        //this class and its children will depend on having MySQL as the back end
        $config = Yaml::parseFile(PROJECT_PATH_CONFIG . "baikal.yaml");
        $this->dbconn=new mysqli(
            $config['database']['mysql_host'],
            $config['database']['mysql_username'],
            $config['database']['mysql_password'],
            $config['database']['mysql_dbname']
            );

    } //end construct

    public function serialize() {
        return $this->vobject->serialize();
    }

    public function getReminderID() {
        return $this->objectID;
    }

    public function getRowData() {
        return $this->rowdata;
    }

    public function getParentURI() {
        return $this->parenturi;
    }

    public function getVObject() {
        return $this->vobject;
    }

    public function setData($vdata) {
        //the "nuclear option" for working with the object: explicitly set the whole thing
        $this->vobject = VObject\Reader::read($vdata, VObject\Reader::OPTION_FORGIVING);
        $this->modified=true;
    }

} //end class

