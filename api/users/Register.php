<?php
require 'AuthCommon.php';

use Communicator\Utils\Error as Error;
use Communicator\Utils\exceptionHandler;
use Communicator\Utils\Response;

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $error = new Error(405, "Wrong method used, POST expected");
    $error->send();
    exit();
}

$register_array = json_decode(file_get_contents('php://input'), true);
if(!isset($register_array["login"])
    ||!isset($register_array["password"])
    ||!isset($register_array["salt"]))
{
    $error = new Error(400, "Not enough arguments provided, login, password and salt required");
    $error->send();
    exit();
}

try
{
    $user = $auth->register($register_array["login"], $register_array["password"], $register_array["salt"]);
    $response = new Response(200,$user);
    $response->send();
    exit();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}
