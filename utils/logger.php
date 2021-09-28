<?php declare(strict_types=1);
namespace Communicator\Utils;

use Psr\Log\AbstractLogger as AbstractLogger;
use Psr\Log\LogLevel as LogLevel;
use Psr\Log\InvalidArgumentException as InvalidArgumentException;

class Logger extends AbstractLogger
{
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $level_check = new \ReflectionClass(new LogLevel());
        $levels = $level_check->getConstants();
        if(!in_array($level,$levels))
            throw new InvalidArgumentException("invalid log level provided",2);
        $log_array = array("level" => $level, "message" => strval($message));
        $this->stringify($context,$log_array);
        error_log(json_encode($log_array));
    }
    private function stringify(array $context, array &$log_array): void
    {
        foreach($context as $key => $value)
        {
            if(is_array($value))
            {
                $log_array[$key] = array();
                $this->stringify($value,$log_array[$key]);
            }
            elseif(is_string($value) 
                   || !is_object($value) 
                   || (method_exists($value, '__toString')&&!$value instanceof \Exception)
                   || (strcasecmp($key,'exception')==0&&$value instanceof \Exception))
                $log_array[$key] = strval($value);
        }
    }
}
//$arr1 = array("data" => 1, "string" => "str", "exception" => new \Exception("exception"));
//$arr2 = array("data" => 1, "string" => "str", "invalid" => new Logger());
//$arr1["array"] = $arr2;
//$logger = new Logger();
//$logger->log(LogLevel::INFO,"msg",$arr1);
