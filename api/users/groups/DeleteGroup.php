<?php
require "GroupCommon.php";

use Communicator\Utils\Error as Error;
use Communicator\Utils\exceptionHandler;
use Communicator\Utils\Response;

if($_SERVER['REQUEST_METHOD'] !== 'DELETE')
{
    $error = new Error(405, "Wrong method used, DELETE expected");
    $error->send();
    exit();
}

$group_array = json_decode(file_get_contents('php://input'), true);
if(!isset($group_array["id"]))
{
    $error = new Error(400, "Not enough arguments provided, id required");
    $error->send();
    exit();
}
try {
    $controller = new \Communicator\Controller\GroupController($logger, $dbAccess);
    $controller->delGroup($group_array["id"],$auth->getUser()->id);
    (new Response(200))->send();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}