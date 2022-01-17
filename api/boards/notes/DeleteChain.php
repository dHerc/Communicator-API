<?php
require $_SERVER['DOCUMENT_ROOT']."/boards/BoardCommon.php";

use Communicator\Controller\BoardController as BoardController;
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
$chain_array = json_decode(file_get_contents('php://input'), true);
if($chain_array === null
    ||!isset($chain_array["board_id"])
    ||!isset($chain_array["chain_pos"]))
{
    $error = new Error(400, "Passed object does not contain enough information to delete chain, board_id and chain_pos required");
    $error->send();
    exit(); 
}
if(!$auth->checkBoardPermission($chain_array["board_id"],"editor"))
{
    $error = new Error(401, "Not enough permission to do this action");
    $error->send();
    exit();
}
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    $controller->deleteChain($chain_array["board_id"], $chain_array["chain_pos"]);
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