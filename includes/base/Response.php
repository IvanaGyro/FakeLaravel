<?php

namespace FakeLaravel\base;

class Response
{
    public function response()
    {
        echo $this->returnData;
    }

    protected $returnData;
}
