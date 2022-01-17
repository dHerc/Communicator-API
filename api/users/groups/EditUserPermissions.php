<?php
require "GroupCommon.php";

use Communicator\Utils\Error as Error;
use Communicator\Utils\exceptionHandler;
use Communicator\Utils\Response;

if($_SERVER['REQUEST_METHOD'] !== 'PUT')
{
    $error = new Error(405, "Wrong method used, PUT expected");
    $error->send();
    exit();
}

$group_array = json_decode(file_get_contents('php://input'), true);
if(!isset($group_array["group_id"])
    || !isset($group_array['username'])
    || !isset($group_array['permission']))
{
    $error = new Error(400, "Not enough arguments provided, group_id, username and permission required");
    $error->send();
    exit();
}
try {
    $controller = new \Communicator\Controller\GroupController($logger, $dbAccess);
    $controller->editUserPermissions($group_array["group_id"],
        $group_array['username'],
        $group_array['permission'],
        $auth->getUser()->id);
    (new Response(200))->send();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}