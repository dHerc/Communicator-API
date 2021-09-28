<?php
require 'common.php';

use Communicator\Manage\BoardController as BoardController;
use Communicator\Utils\Response as Response;
use Communicator\Utils\Error as Error;
use Communicator\Exceptions as Exceptions;
use Communicator\Utils\ExceptionHandler as ExceptionHandler;

if($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    $error = new Error(405, "Wrong method used, GET expected");
    $error->send();
    exit();
}
if(!isset($_GET["id"]))
{
    $error = new Error(400, "No board id provided");
    $error->send();
    exit(); 
}
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    $board = $controller->getBoard($_GET["id"]);
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