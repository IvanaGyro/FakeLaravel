<?php
namespace FakeLaravel\base;

class ValidationField
{
    public $val;
    public $isNumeric = false;

    /**
     * @param mixed $val input value
     */
    public function __construct($val)
    {
        $this->val = $val;
    }
}
