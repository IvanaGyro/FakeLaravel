<?php
namespace FakeLaravel\exceptions;

use \FakeLaravel\responses\JsonResponse;

class InvalidKeyException extends BaseException
{
    public function __construct($message = null, $code = INVALID_KEY_ERRCODE, Exception $previous = null)
    {
        parent::__construct($message, INVALID_KEY_ERRCODE, $previous);
        // $this->code = $code;
    }

    public function report()
    {
        // do log
    }

    public function render()
    {
        return new JsonResponse($this->message, $this->code);
    }
}
