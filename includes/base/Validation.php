<?php
namespace FakeLaravel\base;

use \FakeLaravel\exceptions\InvalidKeyException;

class Validation
{
    private static $validateFuncs;

    protected static $request = null; // for validation function

    public static function init()
    {
        self::$validateFuncs = [
            "alpha" => function ($val) {
                return ctype_alpha($val->val);
            },
            "alpha_dash" => function ($val) {
                preg_match("/^[\d\w\-_]*$/", $val->val, $matches);
                return !empty($matches);
            },
            "alpha_num" => function ($val) {
                return ctype_alnum($val->val);
            },
            "array" => function ($val) {
                return is_array($val->val);
            },
            "between" => function ($val, $ruleVars) {
                list($min, $max) = $ruleVars;
                $size = self::size($val);
                if ($size < $min || $size > $max) {
                    return false;
                }
                return true;
            },
            "boolean" => function ($val) {
                if (!is_bool($val->val) &&
                    $val !== "0" &&
                    $val !== "1" &&
                    $val !== 1 &&
                    $val !== 0
                ) {
                    return false;
                }
                return true;
            },
            "date" => function ($val) {
                if (strtotime($val->val) === false) {
                    return false;
                }
                return true;
            },
            "date_format" => function ($val, $ruleVars) {
                if (\DateTime::createFromFormat($ruleVars[0], $val->val) === false) {
                    return false;
                }
                return true;
            },
            "digits" => function ($val, $ruleVars) {
                $valTmp = is_string($val->val)? $val->val : strval($val->val);
                if (!ctype_digit($valTmp) ||
                    strlen($valTmp) != $ruleVars[0]
                ) {
                    return false;
                }
                return true;
            },
            "digits_between" => function ($val, $ruleVars) {
                list($min, $max) = $ruleVars;
                $valTmp = is_string($val->val)? $val->val : strval($val->val);
                $len = strlen($valTmp);
                if (!ctype_digit($valTmp) ||
                    ($len < $min || $len > $max)
                ) {
                    return false;
                }
                return true;
            },
            "distinct" => function ($val) {
                if (is_array($val->val) &&
                    count(array_unique($val->val)) !== count($val->val)
                ) {
                    return false;
                }
                return true;
            },
            "in" => function ($val, $ruleVars) {
                return in_array($val->val, $ruleVars);
            },
            "in_array" => function ($val, $ruleVars) {
                $val2 = self::$request->input($ruleVars[0]);
                if (is_array($val2) && !in_array($val->val, $val2)) {
                    return false;
                }
                return true;
            },
            "integer" => function ($val) {
                if (is_numeric($val->val)) {
                    return intval($val) == $val;
                }
                return false;
            },
            "max" => function ($val, $ruleVars) {
                if (self::size($val) > $ruleVars[0]) {
                    return false;
                }
                return true;
            },
            "min" => function ($val, $ruleVars) {
                if (self::size($val) < $ruleVars[0]) {
                    return false;
                }
                return true;
            },
            "not_in" => function ($val, $ruleVars) {
                return !in_array($val->val, $ruleVars);
            },
            "numeric" => function ($val) {
                return is_numeric($val->val);
            },
            "present" => function ($val) {
                return isset($val->val);
            },
            "regex" => function ($val, $ruleVars) {
                // remerge the pattern
                $pattern = implode(",", $ruleVars);
                preg_match($pattern, $val->val, $matches);
                return !empty($matches);
            },
            "required" => function ($val) {
                return !empty($val->val);
            },
            "required_if" => function ($val, $ruleVars) {
                $val2 = self::$request->input(current($ruleVars));
                while (($checkVal = next($ruleVars)) !== false) {
                    if ($checkVal == $val2) {
                        return call_user_func_array(
                            self::$validateFuncs["required"],
                            [$val]
                        );
                    }
                }
                return null;
            },
            "required_unless" => function ($val, $ruleVars) {
                $val2 = self::$request->input(current($ruleVars));
                while (($checkVal = next($ruleVars)) !== false) {
                    if ($checkVal == $val2) {
                        return null;
                    }
                }
                return call_user_func_array(
                    self::$validateFuncs["required"],
                    [$val]
                );
            },
            "required_with" => function ($val, $ruleVars) {
                foreach ($ruleVars as $field) {
                    if (self::$request->has($field)) {
                        return call_user_func_array(
                            self::$validateFuncs["required"],
                            [$val]
                        );
                    }
                }
                return null;
            },
            "required_without" => function ($val, $ruleVars) {
                foreach ($ruleVars as $field) {
                    if (!self::$request->has($field)) {
                        return call_user_func_array(
                            self::$validateFuncs["required"],
                            [$val]
                        );
                    }
                }
                return null;
            },
            "required_without_all" => function ($val, $ruleVars) {
                foreach ($ruleVars as $field) {
                    if (self::$request->has($field)) {
                        return null;
                    }
                }
                return call_user_func_array(
                    self::$validateFuncs["required"],
                    [$val]
                );
            },
            "size" => function ($val, $ruleVars) {
                return self::size($val) == $ruleVars[0];
            },
            "string" => function ($val) {
                return is_string($val->val);
            },
            "unique"=> function ($val, $ruleVars) {
                $table = $ruleVars[0];
                $column = $ruleVars[1];
                $idColumn = isset($ruleVars[3]) ? $ruleVars[3] : "id";
                $exceptSql = isset($ruleVars[2]) ? "AND $idColumn!=?" : "";
                $inputAttr = isset($ruleVars[2]) ? [$val->val, $ruleVars[2]] : [$val->val];
                $sql = "SELECT COUNT($idColumn) AS count WHERE $column=? $exceptSql;";
                $ret = DB::queryGetAll($sql, $inputAttr);
                if ($ret[0]['count'] != 0) {
                    return false;
                }
                return true;
            }
        ];
    }

    /**
     * Validate the input requests. Please see the link to get details.
     *
     * @param array $rulesArr
     *
     * @throws InvalidKeyException
     * @throws SqlException
     *
     * implemented rules:
     * alpha, alpha_dash, alpha_num, array, between, boolean, date, date_format,
     * digits, digits_between, distinct, in, in_array, integer, max, min,
     * not_in, numeric, present, regex, required, required_if, required_unless,
     * required_with, required_without, required_without_all, size, string,
     * unique
     *
     * @link https://laravel.com/docs/5.5/validation#validation-quickstart
     * @link https://laravel.com/docs/5.5/validation#available-validation-rules
     */
    public static function make($data, $rulesArr)
    {
        self::$request = new Request($data);
        $invalidKeysArr = [];

        $throw_exception = function () use (&$invalidKeysArr) {
            $invalidKeys = array_keys($invalidKeysArr);
            throw new InvalidKeyException(
                "Invalid or missed keys: " . implode(", ", $invalidKeys)
            );
        };

        $validate = function (
            ValidationField $val,
            $rule,
            $isBail
        ) use (
            &$keys,
            &$invalidKeysArr,
            &$throw_exception
        ) {
            $pos = strpos($rule, ":");
            if ($pos !== false) {
                $ruleType = substr($rule, 0, $pos);
                if ($ruleType === "regex") { // allow comma in regex
                    $ruleVars = [[substr($rule, $pos + 1)]];
                } else {
                    $ruleVars = [str_getcsv(substr($rule, $pos + 1))];
                }
            } else {
                $ruleType = $rule;
                $ruleVars = [];
            }

            if (isset(self::$validateFuncs[$ruleType])) {
                if (!in_array("*", explode(".", $keys)) || !is_array($val->val)) {
                    $vals = [$val];
                } else {
                    $vals = array_map(
                        function ($val) {
                            return new ValidationField($val);
                        },
                        $val->val
                    );
                }
                foreach ($vals as $element) {
                    $vars = array_merge([$element], $ruleVars);
                    $ret = call_user_func_array(
                        self::$validateFuncs[$ruleType],
                        $vars
                    );
                    if ($ret === false) {
                        $invalidKeysArr[$keys] = true;
                        if ($isBail) {
                            $throw_exception();
                        }
                        return false;
                    } elseif ($ret === null) {
                        // special case for rules, required_...
                        return null;
                    }
                }
            }
            return true;
        };

        foreach ($rulesArr as $keys => $rules) {
            $val = new ValidationField(self::$request->input($keys));
            $isBail = false;
            $goNext = false; // Let anonymous tell this loop if the proccess
                             // should continue.

            $rules = preg_replace_callback(
                "/(^|\|)nullable/",
                function ($matches) use ($val, &$goNext) {
                    if (is_null($val->val)) {
                        $goNext = true;
                    }
                },
                $rules
            );
            if ($goNext) {
                continue;
            }

            $rules = preg_replace_callback(
                "/(^|\|)bail/",
                function ($matches) use (&$isBail) {
                    $isBail = true;
                },
                $rules
            );

            $rules = preg_replace_callback(
                "/(^|\|)(required([^|]*))/",
                function ($matches) use ($validate, $val, &$goNext, $isBail) {
                    $ret = $validate($val, $matches[2], $isBail);
                    if ($ret === true) { // required and exist
                    } elseif ($ret === false) { // required but not exist
                        $goNext = true;
                    } elseif ($ret === null) { // not required (hack)
                        $goNext = true;
                    }
                },
                $rules
            );
            if ($goNext) {
                continue;
            }

            $rules = preg_replace_callback(
                "/(^|\|)numeric/",
                function ($matches) use ($validate, $val, &$goNext, $isBail) {
                    $ret = $validate($val, "numeric", $isBail);
                    if ($ret === true) {
                        $val->isNumeric = true;
                    } elseif ($ret === false) {
                        $goNext = true;
                    }
                },
                $rules
            );

            $rules = explode("|", $rules);
            foreach ($rules as $rule) {
                // preg_replace_callback above
                // causes redundant "|" in some cases
                if ($rule === "") {
                    continue;
                }
                if (!$validate($val, $rule, $isBail)) {
                    break;
                }
            }
        }

        if (!empty($invalidKeysArr)) {
            $throw_exception();
        }
        return true;
    }

    protected static function size($val)
    {
        if ($val->isNumeric) {
            return $val->val;
        } elseif (is_string($val->val)) {
            return strlen($val->val);
        } elseif (is_numeric($val->val)) { // gettype == int || float
            return strlen(strval($val->val));
        } elseif (is_array($val->val)) {
            return count($val->val);
        } else {
            return null;
        }
    }
}

Validation::init();
