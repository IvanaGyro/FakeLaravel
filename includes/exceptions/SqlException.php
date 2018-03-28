<?php
namespace FakeLaravel\exceptions;

use \FakeLaravel\base\DB;

class SqlException extends DatabaseException
{
    public $errSql;
    public $errNo;

    /**
     * If $message is omitted, this constructor will automatically create a
     * message with $errSql.
     *
     * @var string $errSql The SQL script that failed to execute.
     */
    public function __construct($errSql = null, $message = null, $code = d_DB_COMMON_ERR, Exception $previous = null)
    {
        if (is_null($errSql)) {
            $errSql = "";
        } else {
            // eliminate new line character and multiple space
            $errSql = $this->makeOneLineSql($errSql);
        }
        if (is_null($message)) {
            $message = $this->makeOneLineSql(DB::errMsg());
        }
        parent::__construct($message, $code, $previous);
        $this->errSql = $errSql;
        $this->errNo = DB::errNo();
    }

    public function appendSql($sql) {
        $sql = $this->makeOneLineSql($sql);
        $this->message .= " SQL:$sql";
    }

    private function makeOneLineSql($sql) {
        return preg_replace(["/[\n\r]+/", "/ +/"], [" ", " "], $sql);
    }
}
