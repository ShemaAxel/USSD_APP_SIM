<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbUtils
 *
 * @author amosl
 *
 * This class handles all DB manipulation queries.
 * USES the PDO transactions implementation
 *
 * Includes functions @function getCount (for count(*)), @function execute() execute(for Inserts and updates)
 * @query for select statements
 *
 * Currently handles the Parameterized queries
 *
 */
class DbUtils
{

    private $pdo;
    private $response = array('SUCCESS' => FALSE,
        'STATCODE' => 0,
        'REASON' => 'An error occured while processing the request.',
        'DATA' => array('RESULTS' => null, 'ROW_COUNT' => 0, 'COUNT' => 0, 'LAST_INSERT_ID' => null));
    private $_debugValues;

    /**
     * Default constructor
     */
    public function __construct()
    {
        try
        {
            $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch (PDOException $pdoe)
        {
            CoreUtils::flog4php(2, NULL, array('PDOEXCEPTION: ' => $pdoe->getMessage(), 'MESSAGE' => 'A connection to the database server could not be established, processing cannot continue.exiting now...'), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            $this->response['STATCODE'] = 7;
            $this->response['REASON'] = 'A connection to the database server could not be established, processing cannot continue.';
            $this->response['DATA'] = null;

            (php_sapi_name() == "fpm-fcgi" || php_sapi_name() == "cgi-fcgi") ? header("Status: 500 Internal Server Error") : header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1 " . ' 500 Internal Server Error', true, 500);
            exit(json_encode($this->response) . "\n");
        }
        catch (Exception $e)
        {
            CoreUtils::flog4php(2, NULL, array('EXCEPTION: ' => $e->getMessage(), 'MESSAGE' => 'An error occured while processing the request, processing cannot proceed.exiting now...'), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            $this->response['STATCODE'] = 7;
            $this->response['REASON'] = $e->getMessage();
            $this->response['DATA'] = null;

            (php_sapi_name() == "fpm-fcgi" || php_sapi_name() == "cgi-fcgi") ? header("Status: 500 Internal Server Error") : header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1 " . ' 500 Internal Server Error', true, 500);
            exit(json_encode($this->response) . "\n");
        }
    }

    public function prepare($statement, $driver_options = false)
    {
        if (!$driver_options)
            $driver_options = array();
        return self::$PDOInstance->prepare($statement, $driver_options);
    }

    /**
     * Function to execute a query that returns a result set.
     *
     * @param string $query The SQL string
     * @param boolean $limit	True: return a single row, False: return all rows
     * @param array $params		Array values to be bound to the prepared statement
     * @return array	An array: success state and a message and DATA if query returned a result set
     */
    public function query($query, $limit = false, $params = null)
    {
        try
        {
            $this->pdo->beginTransaction();

            if ($params)
            {
                $stmt = $this->pdo->prepare($query);
                $this->_debugValues = $params;
                $stmt->execute($params);

                $result = ($limit) ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                $result = ($limit) ? $this->pdo->query($query)->fetch(PDO::FETCH_ASSOC) : $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
            }

            if ($result) :
                $this->response['SUCCESS'] = true;
                $this->response['STATCODE'] = 1;
                $this->response['QUERY'] = preg_replace_callback('/:([0-9a-z_]+)/i', array($this, '_debugReplace'), $stmt->queryString);
                $this->response['REASON'] = 'Query processed successfully.';
                $this->response['DATA']['RESULTS'] = $result;
            else :
                $this->response['SUCCESS'] = false;
                $this->response['STATCODE'] = 0;
                $this->response['QUERY'] = preg_replace_callback('/:([0-9a-z_]+)/i', array($this, '_debugReplace'), $stmt->queryString);
                $this->response['REASON'] = 'Empty set.';
                CoreUtils::flog4php(3, NULL, array('REASON' => 'Empty set.'), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            endif;

            $this->pdo->commit();
        }
        catch (PDOException $pdoe)
        {
            CoreUtils::flog4php(3, NULL, array('PDOEXCEPTION' => $pdoe->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);

            $this->response['REASON'] = json_encode($pdoe);

            $this->pdo->rollBack();
        }
        catch (Exception $e)
        {
            CoreUtils::flog4php(3, NULL, array('EXCEPTION' => $e->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);

            $this->response['REASON'] = json_encode($e);
        }

        return $this->response;
    }

    /**
     * This function executes a database insert or update operation.
     *
     * @param type $query An insert/update SQL query
     * @param type $params  An array of values to be bound to query params
     *
     * @return array    An array with a success state and a message
     */
    public function execute($query, $params = null)
    {
        try
        {
            $this->pdo->beginTransaction();

            if ($params)
            {
                $stmt = $this->pdo->prepare($query);
                $this->_debugValues = array_values($params);
                $result = $stmt->execute(array_values($params));
                $this->response['DATA']['ROW_COUNT'] = $stmt->rowCount();
            }
            else
            {
                $result = $this->pdo->exec($query);
                $this->response['DATA']['ROW_COUNT'] = $result;
            }

            if ($result) :
                $this->response['SUCCESS'] = true;
                $this->response['STATCODE'] = 1;
                $this->response['QUERY'] = preg_replace_callback('/:([0-9a-z_]+)/i', array($this, '_debugReplace'), $stmt->queryString);
                $this->response['REASON'] = 'Request processed successfully.';
            else :
                $this->response['SUCCESS'] = false;
                $this->response['QUERY'] = preg_replace_callback('/:([0-9a-z_]+)/i', array($this, '_debugReplace'), $stmt->queryString);
                $this->response['REASON'] = json_encode($this->pdo->errorInfo());
                CoreUtils::flog4php(3, NULL, array('ERROR' => $this->pdo->errorInfo()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            endif;

            if ($this->pdo->lastInsertId())
            {
                $this->response['DATA']['LAST_INSERT_ID'] = $this->pdo->lastInsertId();
            }

            $this->pdo->commit();
        }
        catch (PDOException $pdoe)
        {
            CoreUtils::flog4php(3, NULL, array('PDOEXCEPTION: ' => $pdoe->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            
            if(isset($pdoe->errorInfo) && $pdoe->errorInfo[1] == 1062) : //Duplicate entry failing unique/composite key rule
                $this->response['SUCCESS'] = true;
                $this->response['STATCODE'] = 10;
                $this->response['REASON'] = 'Duplicate request already processed.';
            else :
                $this->response['REASON'] = 'An error occured while processing the request.';
            endif;

            $this->pdo->rollBack();
        }
        catch (Exception $e)
        {
            CoreUtils::flog4php(3, NULL, array('EXCEPTION: ' => $e->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
        }

        return $this->response;
    }

    protected function _debugReplace($m) {
        $v = $this->_debugValues[$m[1]];
        if ($v === null) {
            return "NULL";
        }
        if (!is_numeric($v)) {
            $v = str_replace("'", "''", $v);
        }

        return "'" . $v . "'";
    }

    /**
     * This function is solely used to execute a "select count(*) from TABLE_NAME" query!!!!!
     *
     * @param unknown $query The query
     * @param string $params	Optional array of params to be bound
     * @return int	The number of rows that meet the criteria "where" part of the query
     */
    public function getCount($query, $params = null)
    {
        try
        {
            if ($params)
            {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
                $numOfRows = $stmt->fetchColumn();
            }
            else
            {
                $numOfRows = $this->pdo->query($query)->fetchColumn();
            }

            if ($numOfRows) :
                $this->response['SUCCESS'] = true;
                $this->response['REASON'] = 'Request processed successfully.';
                $this->response['DATA']['COUNT'] = $numOfRows;
            else :
                $this->response['SUCCESS'] = false;
                $this->response['REASON'] = json_encode($this->pdo->errorInfo());
            endif;
        }
        catch (PDOException $pdoe)
        {
            CoreUtils::flog4php(3, NULL, array('PDOEXCEPTION' => $pdoe->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
        }
        catch (Exception $e)
        {
            CoreUtils::flog4php(3, NULL, array('EXCEPTION' => $e->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
        }

        return $this->response;
    }

    /**
     * Fetch the statusHistory, current dateModified, current overalStatus and current appID.
     *
     * @param type $tableName   The table.
     * @param type $pk_key      The primary key name
     * @param type $pk_value    The primary key value
     * @return type             Array values.
     */
    public function fetchStatusHistory($tableName, $pk_name, $pk_value)
    {
        $query = "SELECT statusHistory, dateModified, overalStatus, appID FROM $tableName "
                . " WHERE $pk_name = ? ";

        try
        {
            $this->query($query, true, array($pk_value));
        }
        catch (PDOException $pdoe)
        {
            CoreUtils::flog4php(3, NULL, array('PDOEXCEPTION' => $pdoe->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
        }
        catch (Exception $e)
        {
            CoreUtils::flog4php(3, NULL, array('EXCEPTION' => $e->getMessage()), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
        }

        return $this->response;
    }
    
    public function setArchiveConnection(){
        try
        {
            $this->pdo = new PDO(ARCHIVE_DS_DSN, ARCHIVE_DS_USERNAME, ARCHIVE_DS_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch (PDOException $pdoe)
        {
            CoreUtils::flog4php(2, NULL, array('PDOEXCEPTION: ' => $pdoe->getMessage(), 'MESSAGE' => 'A connection to the database server could not be established, processing cannot continue.exiting now...'), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            $this->response['STATCODE'] = 7;
            $this->response['REASON'] = 'A connection to the database server could not be established, processing cannot continue.';
            $this->response['DATA'] = null;

            (php_sapi_name() == "fpm-fcgi" || php_sapi_name() == "cgi-fcgi") ? header("Status: 500 Internal Server Error") : header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1 " . ' 500 Internal Server Error', true, 500);
            exit(json_encode($this->response) . "\n");
        }
        catch (Exception $e)
        {
            CoreUtils::flog4php(2, NULL, array('EXCEPTION: ' => $e->getMessage(), 'MESSAGE' => 'An error occured while processing the request, processing cannot proceed.exiting now...'), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            $this->response['STATCODE'] = 7;
            $this->response['REASON'] = $e->getMessage();
            $this->response['DATA'] = null;

            (php_sapi_name() == "fpm-fcgi" || php_sapi_name() == "cgi-fcgi") ? header("Status: 500 Internal Server Error") : header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1 " . ' 500 Internal Server Error', true, 500);
            exit(json_encode($this->response) . "\n");
        }
    }
    
    public function unsetArchiveConnection(){
        try
        {
            $this->pdo = new PDO(DS_DSN, DS_USERNAME, DS_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch (PDOException $pdoe)
        {
            CoreUtils::flog4php(2, NULL, array('PDOEXCEPTION: ' => $pdoe->getMessage(), 'MESSAGE' => 'A connection to the database server could not be established, processing cannot continue.exiting now...'), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            $this->response['STATCODE'] = 7;
            $this->response['REASON'] = 'A connection to the database server could not be established, processing cannot continue.';
            $this->response['DATA'] = null;

            (php_sapi_name() == "fpm-fcgi" || php_sapi_name() == "cgi-fcgi") ? header("Status: 500 Internal Server Error") : header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1 " . ' 500 Internal Server Error', true, 500);
            exit(json_encode($this->response) . "\n");
        }
        catch (Exception $e)
        {
            CoreUtils::flog4php(2, NULL, array('EXCEPTION: ' => $e->getMessage(), 'MESSAGE' => 'An error occured while processing the request, processing cannot proceed.exiting now...'), __FILE__, __FUNCTION__, __LINE__, "ussdfatal", USSD_LOG_PROPERTIES);
            $this->response['STATCODE'] = 7;
            $this->response['REASON'] = $e->getMessage();
            $this->response['DATA'] = null;

            (php_sapi_name() == "fpm-fcgi" || php_sapi_name() == "cgi-fcgi") ? header("Status: 500 Internal Server Error") : header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1 " . ' 500 Internal Server Error', true, 500);
            exit(json_encode($this->response) . "\n");
        }
    }

}

?>
