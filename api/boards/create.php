<?php
require 'common.php';

use Communicator\Manage\BoardController as BoardController;
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
if(!isset($_POST["name"]))
{
    $error = new Error(400, "No board name provided");
    $error->send();
    exit(); 
}
$controller = new BoardController($logger,$dbAccess);
try
{
    $board = $controller->addBoard($_POST["name"]);
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