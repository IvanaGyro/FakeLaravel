<?php
namespace FakeLaravel\base;

use FakeLaravel\exceptions\DatabaseException;

class Service
{
    private $dbHandle = null;
    private $queryResult = null;
    private $transIsStarted = false;

    public function __construct()
    {
    }
}
