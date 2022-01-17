<?php
require "GroupCommon.php";

use Communicator\Utils\Error as Error;
use Communicator\Utils\exceptionHandler;
use Communicator\Utils\Response;

if($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    $error = new Error(405, "Wrong method used, GET expected");
    $error->send();
    exit();
}
try {
    $controller = new \Communicator\Controller\GroupController($logger, $dbAccess);
    $groups = $controller->getGroups($auth->getUser()->id);
    (new Response(200, ["groups" => $groups]))->send();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}