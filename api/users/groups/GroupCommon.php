<?php
use Communicator\Utils\Logger;
use Communicator\Database\DatabaseAccess as DBAccess;
use Communicator\Controller\AuthController as Auth;

require dirname($_SERVER['DOCUMENT_ROOT']).'/bootstrap.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

if($_SERVER['REQUEST_URI']==='users/BoardCommon.php')
    exit();

$logger = new Logger();
$dbAccess = new DBAccess();
$auth = new Auth($logger, $dbAccess);
if(!$auth->loadUser($_SERVER['HTTP_AUTHORIZATION'])) {
    (new \Communicator\Utils\Error(403, "Provided token is invalid"))->send();
    exit();
}