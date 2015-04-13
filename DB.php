<?php
/**
 * @file    DB.php
 * @brief   Simply PDO wrapper
 * @author  Artur Kirilyuk (artur.kirilyuk@gmail.com)
 */

class DB
{
    /**
     * @var PDO database handler
     */
    private static $dbh;
    private static $connectParams;
    private static $lastQuery;
    private static $lastQueryData = array();

    private function __construct()
    {
    }

    /**
     * @param string $database
     * @param string $user
     * @param string $password
     * @param string $host
     * @param string $encoding
     * @param string $dbms
     * @param null $socket
     */
    public static function init($database, $user, $password, $host = 'localhost', $encoding = 'utf8', $dbms = 'mysql',
        $socket = NULL)
    {
        self::$connectParams = array(
            'database' => $database,
            'user' => $user,
            'password' => $password,
            'host' => $host,
            'encoding' => $encoding,
            'dbms' => $dbms,
            'socket' => $socket
        );
    }

    /**
     * @param string $what
     * @param string $table
     * @param array|null $where
     * @param string|null $order
     * @param string|null $group
     * @return PDOStatement
     */
    public static function select($what, $table, array $where = NULL, $order = NULL, $group = NULL)
    {
        self::_connect();
        self::$lastQuery = 'SELECT ' . $what . ' FROM `' . $table . '` ' . self::_where($where)
                           . self::_order($order) . self::_group($group);
        $statement = self::$dbh->prepare(self::$lastQuery);
        self::$lastQueryData = $where;
        $statement->execute(self::$lastQueryData);
        return $statement;
    }

    /**
     * @param array $what
     * @param string $table
     * @return PDOStatement
     */
    public static function insert(array $what, $table)
    {
        self::_connect();
        self::$lastQuery = 'INSERT INTO `' . $table . '` ' . self::_insert($what);
        self::$lastQueryData = $what;
        $statement = self::$dbh->prepare(self::$lastQuery);
        $statement->execute(self::$lastQueryData);
        return $statement;
    }

    public static function delete($table, array $where = NULL)
    {
        self::_connect();
        self::$lastQuery = 'DELETE FROM `' . $table . '`' . ' ' . self::_where($where);
        self::$lastQueryData = $where;
        $statement = self::$dbh->prepare(self::$lastQuery);
        $statement->execute(self::$lastQueryData);
        return $statement;
    }

    /**
     * @param array $set
     * @param string $table
     * @param array $where
     * @return PDOStatement
     */
    public static function update(array $set, $table, array $where = NULL)
    {
        self::_connect();
        self::$lastQuery = 'UPDATE `' . $table . '`' . self::_set($set) . self::_where($where);
        $statement = self::$dbh->prepare(self::$lastQuery);
        self::$lastQueryData = is_null($where) ? $set : array_merge($set, $where);
        $statement->execute(self::$lastQueryData);
        return $statement;
    }

    /**
     * @param $unsafeRequest
     * @return int
     */
    public static function exec($unsafeRequest)
    {
        return self::$dbh->exec($unsafeRequest);
    }

    /**
     * @param PDOStatement $res
     * @return mixed
     */
    public static function fetchAssoc(PDOStatement $res)
    {
        return $res->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param PDOStatement $res
     * @return mixed
     */
    public static function fetchNum(PDOStatement $res)
    {
        return $res->fetch(PDO::FETCH_NUM);
    }

    /**
     * @param PDOStatement $res
     * @return array
     */
    public function fetchAll(PDOStatement $res)
    {
        return $res->fetchAll();
    }

    /**
     * @param $whereArray
     * @return string
     */
    private static function _where(&$whereArray)
    {
        if (empty($whereArray))
        {
            return '';
        }
        $logicOperators = array(
            'AND',
            'OR'
        );

        $whereString = ' WHERE';
        $newArray = array();

        foreach ($whereArray as $key => $value)
        {
            if (!is_string($key))
            {
                continue;
            }
            if (in_array(strtoupper($key), $logicOperators))
            {
                $whereString .= ' ' . $key;
            }
            else
            {
                if (is_array($value))
                {
                    $placeHolder = ':' . $key;
                    $whereString .= ' ' . $key . ' IN(' . $placeHolder . ')';
                    $newArray[$placeHolder] = implode(',', $value);
                }
                else
                {
                    $placeHolder = ':' . $key;
                    $whereString .= ' ' . $key . '=' . $placeHolder;
                    $newArray[$placeHolder] = $value;
                }
            }
        }
        $whereArray = $newArray;
        return $whereString;
    }

    /**
     * @param array $setArray
     * @return string
     */
    private static function _set(array &$setArray)
    {
        $setString = ' SET';
        $myArray = array();
        $placeholderArray = array();
        foreach ($setArray as $key => $value)
        {
            $placeholder = ':' . $key;
            $myArray[] = ' ' . $key . '=' . $placeholder;
            $placeholderArray[$placeholder] = $value;
        }
        $setArray = $placeholderArray;
        $setString .= implode(',', $myArray);
        return $setString;
    }

    /**
     * @param $order
     * @return string
     * @throws LogicException
     */
    private static function _order($order)
    {
        if (is_null($order))
        {
            return '';
        }
        if (is_string($order))
        {
            $orderString = ' ORDER BY ' . $order;
            return $orderString;
        }
        else
        {
            throw new LogicException ('Order by must be a string');
        }
    }

    /**
     * @param string $group
     * @return string
     * @throws LogicException
     */
    private static function _group($group)
    {
        if (is_null($group))
        {
            return '';
        }
        if (is_string($group))
        {
            $groupString = ' GROUP BY ' . $group;
            return $groupString;
        }
        else
        {
            throw new LogicException ('Group by must be a string');
        }
    }

    /**
     * @param array $dataArray
     * @return string
     * @throws LogicException
     */
    private static function _insert($dataArray)
    {
        if (is_array($dataArray) && !empty($dataArray))
        {
            $intoString = '(' . implode(',', array_keys($dataArray)) . ')';
            $valuesString = '(:' . implode(',:', array_keys($dataArray)) . ')';
            return $intoString . ' VALUES ' . $valuesString;
        }
        else
        {
            throw new LogicException ('Insert request require array');
        }
    }

    /**
     * connect to DB
     */
    private static function _connect()
    {
        if (!is_null(self::$dbh))
        {
            return;
        }
        $connectStr = self::$connectParams['dbms'] . ':host=' . self::$connectParams['host'] . ';dbname='
                      . self::$connectParams['database'] . ';charset=' . self::$connectParams['encoding'];
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . self::$connectParams['encoding']);
        try
        {
            self::$dbh = new PDO($connectStr, self::$connectParams['user'], self::$connectParams['password'], $options);
            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            Log::add($e->getMessage());
        }
    }
}
