<?php
require $_SERVER['DOCUMENT_ROOT'].'/bootstrap.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

if($_SERVER['REQUEST_URI']==='api/boards/common.php')
    exit();
    
use Communicator\Utils\Logger as Logger;
use Communicator\Data\Database\DatabaseAccess as DBAccess;
use Communicator\Data\Boards\NoteFactory as NoteFactory;

$logger = new Logger();
$dbAccess = new DBAccess();
$noteFactory = new NoteFactory();