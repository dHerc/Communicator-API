<?php
require $_SERVER['DOCUMENT_ROOT']."/api/boards/common.php";

use Communicator\Manage\BoardController as BoardController;
use Communicator\Utils\Response as Response;
use Communicator\Utils\Error as Error;
use Communicator\Exceptions as Exceptions;
use Communicator\Utils\ExceptionHandler as ExceptionHandler;

if($_SERVER['REQUEST_METHOD'] !== 'DELETE')
{
    $error = new Error(405, "Wrong method used, DELETE expected");
    $error->send();
    exit();
}
$board_array = json_decode(file_get_contents('php://input'), true);
if($board_array === null
    ||!isset($board_array["id"]))
{
    $error = new Error(400, "Passed object does not contain id of board to remove");
    $error->send();
    exit(); 
}
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    $controller->deleteBoard($board_array["id"]);
    $response = new Response(200);
    $response->send();
    exit();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}