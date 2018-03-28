<?php
namespace FakeLaravel\base;

use \FakeLaravel\exceptions\DatabaseException;
use \FakeLaravel\exceptions\SqlException;

define('d_DB_MODEL', 'mysqli');
define('d_DB_FETCH_MODE_E', ADODB_FETCH_ASSOC);

// define('ADODB_ERROR_LOG_TYPE', 3);
// define('ADODB_ERROR_LOG_DEST', __DIR__ . '/errors.log');

class DB
{
    protected static $connection;
    protected static $queryResult;

    /**
     * Initialize a database handle.
     * The handle will be put into self::$connection.
     *
     * @throws DatabaseException
     */
    public static function init()
    {
        global $_CONFIG;
        if (!isset(self::$connection)) {
            $dbHandle = NewADOConnection(d_DB_MODEL);
            $dbHandle->autoCommit = false;
            $dbHandle->autoRollback = true;
            $dbHandle->PConnect(
                $_CONFIG['DB']['host'],
                $_CONFIG['DB']['username'],
                $_CONFIG['DB']['password'],
                $_CONFIG['DB']['dbname']
            );
            if ($dbHandle->IsConnected()) {
                /**
                 * @link http://www.spearheadsoftwares.com/tutorials/php-performance-benchmarking/50-mysql-fetch-assoc-vs-mysql-fetch-array-vs-mysql-fetch-object
                 * @link https://stackoverflow.com/questions/9540483/mysql-fetch-assoc-vs-mysql-fetch-array
                 *
                 * In order to follow both of improving performace and better read-
                 * ability, here we choose ADODB_FETCH_ASSOC as the fetch mode.
                 * Althought I do not research if fetching with associative indices
                 * has better performance than with numberic indices, FakeLaravel may
                 * not use ADOdb as the interface to accessing the database in the
                 * future, and fetching with associative indices really has better
                 * performance for native PHP interface.
                 */
                $ADODB_FETCH_MODE = d_DB_FETCH_MODE_E;
                $dbHandle->SetCharSet('utf8');
                self::$connection = $dbHandle;
            } else {
                $host = $_CONFIG['DB']['host'];
                throw new DatabaseException(
                    "Cannot connect to the database:{$host}"
                );
            }
        }
    }

    /**
     * Execute the SQL script.
     * Must be called after initializing the database handle.
     *
     * @param string        $sql        The SQL script will be executed.
     * @param array|mixed   $inputarr   The array or the single value will replace
     *                                  the question marks in $sql in order.
     *
     * @throws SqlException
     */
    public static function query($sql, $inputarr = false)
    {
        global $ADODB_FETCH_MODE;
        /**
         * To be compatible with the original function, INSD_DB_query, we have to
         * assign the global variable $ADODB_FETCH_MODE again here and change back
         * to the original mode.
         */
        $ADODB_FETCH_MODE = d_DB_FETCH_MODE_E;
        self::$queryResult = self::$connection->Execute($sql, $inputarr);
        $ADODB_FETCH_MODE = ADODB_FETCH_BOTH;
        if (!self::$queryResult) {
            /**
             * Need to create the exception first to get the error number and the
             * error message of database error because rollback will flush the
             * error number and the error message
             */
            $e = new SqlException($sql, null, d_DB_QUERY_FAIL);
            /**
             * Ignore the smart transcation blocks and force to rollback.
             * This rollback only affects the scripts within the smart transaction.
             */
            self::$connection->transOff = 0;
            self::$connection->RollbackTrans();
            // replace "?" by items of $inputarr
            if ($inputarr !== false) {
                if (!is_array($inputarr)) {
                    $inputarr = array($inputarr);
                }
                $sqlArr = explode("?", $sql);
                $sql = $sqlArr[0];
                for ($i = 1; $i < count($sqlArr); ++$i) {
                    if (isset($inputarr[$i-1])) {
                        $sql .= $inputarr[$i-1];
                    } else {
                        $sql .= "?";
                    }
                    $sql .= $sqlArr[$i];
                }
            }
            $e->appendSql($sql);
            throw $e;
        }
    }

    /**
     * Execute the SQL script and get all the results.
     * Must be called after initializing the database handle.
     *
     * @param string        $sql        The SQL script will be executed.
     * @param array|mixed   $inputarr   The array or the single value will replace
     *                                  the question marks in $sql in order.
     *
     * @throws SqlException
     *
     * @return array A 2-D array, if there is no row be fetched, return an empty
     *               array.
     * @return null  If the laststatement execution fails.
     */
    public static function queryGetAll($sql, $inputarr = false)
    {
        self::query($sql, $inputarr);
        return self::getAll();
    }

    /**
     * Get one row obtained the query.
     *
     * @return array If there is at least a row can be fetched.
     * @return null  If no row can be fetched.
     */
    public static function fetchRow()
    {
        $ret = self::$queryResult->FetchRow();
        if ($ret === false) {
            return null;
        }
        return $ret;
    }

    /**
     * Get all rows obtained the query.
     *
     * @return array A 2-D array, if there is no row be fetched, return an empty
     *               array.
     * @return null  If the laststatement execution fails.
     */
    public static function getAll()
    {
        $ret = self::$queryResult->GetAll();
        if ($ret === false) {
            return null;
        }
        return $ret;
    }

    /**
     * Get the last auto-increment number of any table, generated by an insert on a
     * table with an auto-increment column.
     *
     * @return int      If the index is exist.
     * @return null     If no such insertion has occurred or the database does not
     *                  support the operation.
     *
     * @throws DatabaseException
     */
    public static function insertID()
    {
        $ret = self::$connection->Insert_ID();
        if ($ret === false) {
            throw new DatabaseException("Fail to get the last insert ID.");
        }
        return $ret;
    }

    public static function disconnect()
    {
        self::$connection->Close();
    }

    public static function errMsg()
    {
        return self::$connection->ErrorMsg();
    }

    public static function errNo()
    {
        return self::$connection->ErrorNo();
    }

    /**
     * Start the smart transaction.
     * With using INSD_DB_startTrans_E, INSD_DB_completeTrans_E, and INSD_DB_query_E
     * to control a transaction, you do not need to manually roll back the tran-
     * saction when something are wrong. INSD_DB_query_E will force to roll back and
     * throw an exception automatically. You can also handle the exception that
     * INSD_DB_query_E throw out with your own. If INSD_DB_completeTrans is called
     * after INSD_DB_query_E rolls back the transaction, no error will occur.
     *
     * @throws DatabaseException
     */
    public static function startTrans()
    {
        $ret = self::$connection->StartTrans();
        if (!$ret) {
            throw new DatabaseException(
                "Fail to start a transaction."
            );
        }
    }

    /**
     * End the smart transaction.
     * Please see the comment above the function, startTrans.
     *
     * @param bool $autoComplete If the value of $autoComplete is set to false,
     *                           a rollback is forced.
     *
     * @throws DatabaseException
     */
    public static function completeTrans($autoComplete = true)
    {
        /**
         * @return true commit
         * @return false rollback
         */
        $ret = self::$connection->CompleteTrans($autoComplete);
        if (!$ret) {
            // do nothing
        }
    }

    /**
     * Get the increment of auto-increment.
     *
     * @throws SqlException
     *
     * @return int
     */
    public static function autoIncrementIncrement()
    {
        $ret = self::queryGetAll("SELECT @@auto_increment_increment AS a;");
        return intval($ret[0]["a"]);
    }


    public function array2Sql($array, $inBrackets = true)
    {
        if (!is_array($array)) {
            return $array;
        }
        $array = array_map(array(self::$connection, 'qstr'), $array);
        if ($inBrackets) {
            return "(".implode(",", $array).")";
        }
        return implode(",", $array);
    }
}

DB::init();
