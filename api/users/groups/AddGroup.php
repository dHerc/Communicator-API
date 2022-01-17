<?php
require "GroupCommon.php";

use Communicator\Utils\Error as Error;
use Communicator\Utils\exceptionHandler;
use Communicator\Utils\Response;

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $error = new Error(405, "Wrong method used, POST expected");
    $error->send();
    exit();
}

$group_array = json_decode(file_get_contents('php://input'), true);
if(!isset($group_array["name"]))
{
    $error = new Error(400, "Not enough arguments provided, name required");
    $error->send();
    exit();
}
try {
$controller = new \Communicator\Controller\GroupController($logger, $dbAccess);
$group = $controller->addGroup($group_array["name"],$auth->getUser()->id);
    (new Response(200, $group))->send();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}