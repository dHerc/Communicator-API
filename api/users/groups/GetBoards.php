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
if(!isset($_GET["id"]))
{
    $error = new Error(400, "No group id provided");
    $error->send();
    exit();
}
try {
    $controller = new \Communicator\Controller\GroupController($logger, $dbAccess);
    $boards = $controller->getBoards($_GET['id']);
    (new Response(200, ["boards" => $boards]))->send();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}