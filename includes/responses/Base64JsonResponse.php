<?php
namespace FakeLaravel\responses;

use \FakeLaravel\base\Response;

class Base64JsonResponse extends Response
{
    public function __construct($data, $retCode = "0000")
    {
        if (!is_array($data)) {
            $data = []; // clear the value of $data
        }
        $data["Rtn"] = $retCode;
        $this->returnData = base64_encode(json_encode($data));
    }
}
