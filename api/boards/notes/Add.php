<?php
require $_SERVER['DOCUMENT_ROOT']."/boards/BoardCommon.php";

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
$note_array = json_decode(file_get_contents('php://input'), true);
if(!isset($note_array["board_id"])
    ||!isset($note_array["chain_pos"])
    ||!isset($note_array["type"])
    ||!isset($note_array["message"]))
{
    $error = new Error(400, "Not enough arguments provided, board_id, chain_pos type and message required");
    $error->send();
    exit(); 
}
if(!$auth->checkBoardPermission($note_array["board_id"],"editor"))
{
    $error = new Error(401, "Not enough permission to do this action");
    $error->send();
    exit();
}
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    try {
        $chain_id = $controller->getChain($note_array["board_id"], $note_array["chain_pos"]);
    } catch (Exceptions\Boards\ChainNotFoundException $e) {
        $chain_id = $controller->addChain($note_array['board_id']);
    }
    $new_note = $controller->addNote(
        $chain_id,
        $note_array["type"],
        $note_array["message"],
        $note_array["content"] ?? null);
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