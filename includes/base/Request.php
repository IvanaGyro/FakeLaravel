<?php
namespace FakeLaravel\base;

/**
 * This Request implements some of the functions in the Request of Laravel.
 * Please see the document of Laravel for more details.
 */
class Request
{
    protected $inputData;

    /**
     * @param $data input data
     */
    public function __construct($data = null)
    {
        if (is_null($data)) {
            $this->inputData = $_POST;

            // check session
        } else {
            $this->inputData = $data;
        }
    }

    /**
     * Get input request value with specific keys.
     *
     * @param string  $keys  This is the key of the user input. Use "dot" nota-
     *                       tion to access the array.
     * @param mixed   $default default value. This value will be returned if the
     *                       requested input value is not present on the
     *                       request.
     */
    public function input($keys, $default = null)
    {
        $keys = explode(".", $keys);
        $vals = [$this->inputData];
        $returnArray = false;
        foreach ($keys as $key) {
            if ($key === "*") {
                $returnArray = true;
                $refVals = &$vals;
                unset($vals);
                $vals = [];
                foreach ($refVals as $lastVal) {
                    if (is_array($lastVal)) {
                        $vals = array_merge($vals, array_values($lastVal));
                    } else {
                        $vals[] = $default;
                    }
                }
            } else {
                foreach ($vals as &$val) {
                    if (!isset($val[$key])) {
                        $val = $default;
                    } else {
                        $val = $val[$key];
                    }
                }
            }
        }
        if (!$returnArray) {
            return $vals[0];
        } else {
            return $vals;
        }
    }

    /**
     * Get all input.
     */
    public function all()
    {
        return $this->inputData;
    }

    /**
     * Validate the input requests. Please see the class, Validation, for
     * details.
     *
     * @param array $rulesArr
     *
     * @throws InvalidKeyException
     * @throws SqlException
     */
    public function validate($rulesArr)
    {
        Validation::make($this->all(), $rulesArr);
    }

    /**
     * Check if the input request data has the specific key.
     *
     * @param string $key
     */
    public function has($key)
    {
        if (empty($this->input($key))) {
            return false;
        } else {
            return true;
        }
    }
}
