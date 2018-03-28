<?php
namespace FakeLaravel\exceptions;

define("INVALID_KEY_ERRCODE", "0001");
define("CANNOT_ACCESS_DB_ERRCODE", "0002");
define("UNKWOWN_ERRCODE", "9999");

abstract class BaseException extends \Exception
{
    public function __construct($message = null, $code = "0000", Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->code = $code; // let the code become string
    }

    abstract public function report();
    abstract public function render();
}
