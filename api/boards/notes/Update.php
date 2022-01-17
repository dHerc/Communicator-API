<?php
require $_SERVER['DOCUMENT_ROOT']."/boards/BoardCommon.php";

use Communicator\Controller\BoardController as BoardController;
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
$note_array = json_decode(file_get_contents('php://input'), true);
if($note_array === null
    ||!isset($note_array["id"])
    ||!isset($note_array["type"])
    ||!isset($note_array["message"]))
{
    $error = new Error(400, "Passed object is not a proper note object");
    $error->send();
    exit(); 
}
if(!$auth->checkNotePermission($note_array["id"],"editor"))
{
    $error = new Error(401, "Not enough permission to do this action");
    $error->send();
    exit();
}
if(!isset($note_array["content"]))
    $note_array["content"] = null;
$controller = new BoardController($logger,$dbAccess,$noteFactory);
try
{
    $updated_note = $controller->updateNote($note_array);
    $response = new Response(200,$updated_note);
    $response->send();
    exit();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}