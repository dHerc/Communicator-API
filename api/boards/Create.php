<?php
require 'BoardCommon.php';

use Communicator\Controller\BoardController as BoardController;
use Communicator\Utils\Response as Response;
use Communicator\Utils\Error as Error;
use Communicator\Exceptions as Exceptions;
use Communicator\Utils\ExceptionHandler as ExceptionHandler;

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $error = new Error(405, "Wrong method used, POST expected");
    $error->send();
    exit();
}
$board_array = json_decode(file_get_contents('php://input'), true);
if(!isset($board_array["name"]) || !isset($board_array["group_id"]))
{
    $error = new Error(400, "No board name or group_id provided");
    $error->send();
    exit(); 
}
if(!$auth->checkGroupPermission($board_array["group_id"],"editor"))
{
    $error = new Error(401, "Not enough permission to do this action");
    $error->send();
    exit();
}
$controller = new BoardController($logger,$dbAccess);
try
{
    $board = $controller->addBoard($board_array["name"], $board_array["group_id"]);
    $response = new Response(200,$board);
    $response->send();
    exit();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}