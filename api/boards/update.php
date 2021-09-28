<?php
require $_SERVER['DOCUMENT_ROOT']."/api/boards/common.php";

use Communicator\Manage\BoardController as BoardController;
use Communicator\Utils\Response as Response;
use Communicator\Utils\Error as Error;
use Communicator\Exceptions as Exceptions;
use Communicator\Utils\ExceptionHandler as ExceptionHandler;

if($_SERVER['REQUEST_METHOD'] !== 'PATCH')
{
    $error = new Error(405, "Wrong method used, PATCH expected");
    $error->send();
    exit();
}
$board_array = json_decode(file_get_contents('php://input'), true);
if($board_array === null
    ||!isset($board_array["id"])
    ||!isset($board_array["name"]))
{
    $error = new Error(400, "Passed object is not a proper board object");
    $error->send();
    exit(); 
}
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    $controller->updateBoard($board_array);
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