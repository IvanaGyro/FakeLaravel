<?php

namespace FakeLaravel\base;

use FakeLaravel\exceptions\BaseException;
use FakeLaravel\base\Route;
use FakeLaravel\base\Request;

class App
{
    public function __construct()
    {
        $this->request = new Request;
    }

    public static function show()
    {
        echo "This is App\n";
        echo "In App:".__DIR__."\n";
    }

    public function run()
    {
        try {
            $this->response = Route::route($this->request);
            // $this->request->getPost("123");
        } catch (BaseException $e) {
            $e->report();
            $this->response = $e->render();
        }
        $this->response->response();
    }

    private $request;
    private $response;
}
