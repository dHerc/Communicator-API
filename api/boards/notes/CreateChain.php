<?php
require $_SERVER['DOCUMENT_ROOT']."/boards/BoardCommon.php";

use Communicator\Controller\BoardController as BoardController;
use Communicator\Utils\Logger as Logger;
use Communicator\Database\DatabaseAccess as DBAccess;
use Communicator\Model\Boards\NoteFactory as NoteFactory;
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
$chain_array = json_decode(file_get_contents('php://input'), true);
if(!isset($chain_array["board_id"])
    ||!isset($chain_array["type"])
    ||!isset($chain_array["message"]))
{
    $error = new Error(400, "Not enough arguments provided, board_id, type and message required");
    $error->send();
    exit(); 
}
if(!$auth->checkBoardPermission($chain_array["id"],"editor"))
{
    $error = new Error(401, "Not enough permission to do this action");
    $error->send();
    exit();
}
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    $chain_id = $controller->addChain(
        $chain_array["board_id"]);
    $new_note = $controller->addNote(
        $chain_id,
        $chain_array["type"],
        $chain_array["message"],
        isset($chain_array["content"])?$chain_array["content"]:null,
        0);
    $response = new Response(200,$new_note);
    $response->send();
    exit();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}