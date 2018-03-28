<?php

namespace FakeLaravel\exceptions;

use \FakeLaravel\responses\Base64JsonResponse;

class DatabaseException extends BaseException
{
    public function __construct($message = null, $code = CANNOT_ACCESS_DB_ERRCODE, Exception $previous = null)
    {
        parent::__construct($message, CANNOT_ACCESS_DB_ERRCODE, $previous);
        // $this->code = $code;
    }

    public function report()
    {
        // do log
        $f = fopen("api_log.log", "a");
        if ($f) {
            fwrite($f, $this->message . "\n");
            fclose($f);
        }
    }

    public function render()
    {
        return new Base64JsonResponse("Something wrong, please call the administrator", UNKWOWN_ERRCODE);
    }
}
