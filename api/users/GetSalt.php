<?php
require 'AuthCommon.php';

use Communicator\Utils\Error as Error;
use Communicator\Utils\exceptionHandler;
use Communicator\Utils\Response;

if($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    $error = new Error(405, "Wrong method used, GET expected");
    $error->send();
    exit();
}

if(!isset($_GET["login"]))
{
    $error = new Error(400, "Not enough arguments provided, login required");
    $error->send();
    exit();
}

try
{
    $salt = $auth->getSalt($_GET["login"]);
    $response = new Response(200,['salt' => $salt]);
    $response->send();
    exit();
}
catch (\Exception $e)
{
    $error = ExceptionHandler::handleException($e,$logger);
    $error->send();
    exit();
}