<?php
namespace FakeLaravel\responses;

use \FakeLaravel\base\Response;

class JsonResponse extends Response
{
    public function __construct($data, $retCode = "0000")
    {
        $this->returnData = json_encode(["RetCode"=>$retCode, "Data"=>$data]);
    }
}
