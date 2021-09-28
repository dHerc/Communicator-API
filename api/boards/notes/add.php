<?php
require $_SERVER['DOCUMENT_ROOT']."/api/boards/common.php";

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
if(!isset($_POST["board_id"])
    ||!isset($_POST["chain_pos"])
    ||!isset($_POST["type"])
    ||!isset($_POST["message"]))
{
    $error = new Error(400, "Not enough arguments provided, board_id, chain_pos type and message required");
    $error->send();
    exit(); 
}
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    $chain_id = $controller->getChain($_POST["board_id"], $_POST["chain_pos"]);
    $new_note = $controller->addNote(
        $chain_id,
        $_POST["type"],
        $_POST["message"],
        isset($_POST["content"])?$_POST["content"]:null);
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