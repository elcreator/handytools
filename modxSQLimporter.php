<?php

$importer = new modxImporter('data');
$importer->setSourceTableName('data');
$importer->setSourceTree(array(
    'c0',
    'c1',
    'c2',
    'text'
));
$importer->setRootResource(0);
$importer->execute();


class modxImporter
{
    /** @var string */
    private $_sourceTableName;
    /** @var string */
    private $_modxPrefix = 'modx_';
    /** @var array */
    private $_sourceTree = array();
    /** @var int */
    private $_rootResource;

    /**
     * @param string $dbName
     * @param string $dbHost
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbCharset
     */
    public function __construct($dbName, $dbHost = 'localhost', $dbUser = 'root', $dbPassword = '', $dbCharset = 'utf8')
    {
        set_time_limit(0);
        mysql_connect($dbHost, $dbUser, $dbPassword);
        mysql_select_db($dbName);
        mysql_query('SET CHARACTER SET ' . $dbCharset);
        if (mysql_errno())
        {
            $this->_log('DB connection error: ' . mysql_error());
            exit;
        }
    }

    public function execute()
    {
        $this->_checkRequiredParams();
        $parentResourceId = $this->_insertDir($this->_rootResource, 'root resource');
        $this->_traverseTree(0, $parentResourceId, 0);
    }

    /**
     * @param $level
     * @param $parent
     * @param $parentId
     */
    private function _traverseTree($level, $parent, $parentId)
    {
        $maxLevel = count($this->_sourceTree) - 1;
        if ($level > $maxLevel)
        {
            return;
        }
        $column = $this->_sourceTree[$level];



        $resources = $this->_dbGet("SELECT * FROM `$this->_sourceTableName`" . $this->_makeWhere($column, $parent));

        foreach ($resources as $resource)
        {
            if ($level === $maxLevel)
            {
                $this->_insertFile($parentId, 'none', $resource[$column]);
            }
            else
            {
                $this->_insertDir($parentId, $resource[$column]);
            }
        }
    }

    /**
     * @param int $parentId
     * @param string $title
     * @return int
     */
    private function _insertDir($parentId, $title)
    {
        $arr = array(
            'pagetitle' => $title,
            'longtitle' => $title,
            'description' => $title,
            'menutitle' => $title,
            'alias' => $this->_transliterate($title),
            'published' => 1,
            'isfolder' => 1,
            'parent' => $parentId
        );
        return $this->_insert($arr);
    }

    /**
     * @param int $parentId
     * @param string $title
     * @param string $text
     */
    private function _insertFile($parentId, $title, $text)
    {
        $arr = array(
            'pagetitle' => $title,
            'longtitle' => $title,
            'description' => $title,
            'menutitle' => $title,
            'content' => $text,
            'introtext' => '',
            'alias' => $this->_transliterate($title),
            'published' => 1,
            'isfolder' => 0,
            'parent' => $parentId
        );
        $this->_insert($arr);
    }























    private function _makeDirectoryResource($fieldName, $parentId, $level)
    {




    /*
        $q = "SELECT DISTINCT $fieldName FROM `$this->_sourceTableName`" . $this->_makeWhere($this->_sourceTree[$level],
            );
        $r = mysql_query($q);
        while ($res = mysql_fetch_assoc($r))
        {
            $fieldName = $res[$fieldName];
            $arr = array(
                'pagetitle' => $fieldName,
                'longtitle' => $fieldName,
                'description' => $fieldName,
                'menutitle' => $fieldName,
                'alias' => $this->_transliterate($fieldName),
                'published' => 1,
                'isfolder' => 1,1
                'parent' => $parentId
            );
            $parentCategoryId = $this->_insert($arr);
            $fieldName = $this->_getNextFieldName($fieldName);
            if (is_null($fieldName))
            {
                return;
            }
            $this->_makeDirectoryResource($fieldName, $parentCategoryId, ++$level);
        }
        */
    }

    /**
     * @param string $field
     * @return string|NULL
     */
    private function _getNextFieldName($field)
    {
        $currentIndex = array_search($field, $this->_sourceTree);
        return isset($this->_sourceTree[$currentIndex]) ? $this->_sourceTree[$currentIndex] : NULL;
    }

    /**
     * @param $parentId
     * @param array $parentNames
     */
    private function _makeResource($parentId, array $parentNames)
    {/*
        $query = "SELECT * FROM `$this->_sourceTableName`" . $this->_makeWhere($parentNames);
        $result = mysql_query($query);
        while ($row = mysql_fetch_assoc($result))
        {
            $dishname = $row['dishname'];
            $id = $row['id'];
            $content = preg_replace('/\s\s+/u', ' ', nl2br($row['text'], true));
            $introtext = $row['ingredients'];
            $portions = $row['portions'];
            $cooktime = $row['cooktime'];
            $national = $row['national'];
            $arr = array(
                'pagetitle' => $dishname,
                'longtitle' => $dishname,
                'description' => $dishname,
                'menutitle' => $dishname,
                'content' => $content,
                'introtext' => $introtext,
                'portions' => $portions,
                'cooktime' => $cooktime,
                'national' => $national,
                'alias' => $id . '-' . $this->_transliterate($dishname),
                'published' => 1,
                'isfolder' => 0,
                'parent' => $parentId
            );
            $this->_insert($arr);
        }*/
    }

    /**
     * makes WHERE part of requests based on parents array
     * @param string $parentColumnName
     * @param string $parentValue
     * @return string
     */
    private function _makeWhere($parentColumnName, $parentValue)
    {
        return empty($parentColumnName) ? '' : " WHERE $parentColumnName='" . $this->_escape($parentValue) . "'";
    }

    /**
     * @param string $query
     * @return array
     */
    private function _dbGet($query)
    {
        $results = array();
        $queryResult = mysql_query($query);
        while ($row = mysql_fetch_assoc($queryResult))
        {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * inserts one row to modX content table and returns ID of this
     * @param array $what
     * @return int
     */
    private function _insert(array $what)
    {
        foreach ($what as $key => $value)
        {
            if (gettype($value) === 'string')
            {
                $arr[$key] = "'" . $this->_escape($value) . "'";
            }
        }
        $fields = implode(',', array_keys($what));
        $values = implode(',', array_values($what));

        mysql_query("INSERT INTO modx_site_content ($fields) VALUES ($values)");
        if (mysql_errno())
        {
            $this->_log(mysql_error());
        }
        return mysql_insert_id();
    }

    private function _checkRequiredParams()
    {
        if (is_null($this->_sourceTableName))
        {
            die('Please specify source table name');
        }
        if (empty($this->_sourceTree))
        {
            die('Please specify columns of source table as array where first element corresponding to root of tree');
        }
    }

    /**
     * @param string $unsafe
     * @return string
     */
    private function _escape($unsafe)
    {
        return mysql_real_escape_string($unsafe);
    }

    /**
     * @param string $what
     */
    private function _log($what)
    {
        echo $what . '<br />' . PHP_EOL;
    }

    /**
     * @param string $original
     * @return string
     */
    private function _transliterate($original)
    {
        $transliterationTable = array(
            "Є" => "YE",
            "І" => "I",
            "Ѓ" => "G",
            "і" => "i",
            "№" => "-",
            "є" => "ye",
            "ѓ" => "g",
            "А" => "A",
            "Б" => "B",
            "В" => "V",
            "Г" => "G",
            "Д" => "D",
            "Е" => "E",
            "Ё" => "Yo",
            "Ж" => "Zh",
            "З" => "Z",
            "И" => "I",
            "Й" => "J",
            "К" => "K",
            "Л" => "L",
            "М" => "M",
            "Н" => "N",
            "О" => "O",
            "П" => "P",
            "Р" => "R",
            "С" => "S",
            "Т" => "T",
            "У" => "U",
            "Ф" => "F",
            "Х" => "X",
            "Ц" => "C",
            "Ч" => "Ch",
            "Ш" => "Sh",
            "Щ" => "Sch",
            "Ъ" => "'",
            "Ы" => "Y",
            "Ь" => "",
            "Э" => "E",
            "Ю" => "Yu",
            "Я" => "Ya",
            "а" => "a",
            "б" => "b",
            "в" => "v",
            "г" => "g",
            "д" => "d",
            "е" => "e",
            "ё" => "yo",
            "ж" => "zh",
            "з" => "z",
            "и" => "i",
            "й" => "j",
            "к" => "k",
            "л" => "l",
            "м" => "m",
            "н" => "n",
            "о" => "o",
            "п" => "p",
            "р" => "r",
            "с" => "s",
            "т" => "t",
            "у" => "u",
            "ф" => "f",
            "х" => "x",
            "ц" => "c",
            "ч" => "ch",
            "ш" => "sh",
            "щ" => "sch",
            "ъ" => "",
            "ы" => "y",
            "ь" => "",
            "э" => "e",
            "ю" => "yu",
            "я" => "ya",
            " " => "-",
            "—" => "-",
            "," => "-",
            "!" => "-",
            "@" => "-",
            "." => "",
            "#" => "-",
            "$" => "",
            "%" => "",
            "^" => "",
            "&" => "",
            "*" => "",
            "(" => "",
            ")" => "",
            "+" => "",
            "=" => "",
            ";" => "",
            ":" => "",
            "'" => "",
            "\"" => "",
            "~" => "",
            "`" => "",
            "?" => "",
            "/" => "",
            "\\" => "",
            "[" => "",
            "]" => "",
            "{" => "",
            "}" => "",
            "|" => ""
        );
        return strtr($original, $transliterationTable);
    }

    /**
     * @param string $sourceTableName
     */
    public function setSourceTableName($sourceTableName)
    {
        $this->_sourceTableName = $sourceTableName;
    }

    /**
     * @param array $sourceTree
     */
    public function setSourceTree(array $sourceTree)
    {
        $this->_sourceTree = $sourceTree;
    }

    /**
     * @param int $rootResource
     */
    public function setRootResource($rootResource)
    {
        $this->_rootResource = $rootResource;
    }

    /**
     * @param string $modxPrefix
     */
    public function setModxPrefix($modxPrefix)
    {
        $this->_modxPrefix = $modxPrefix;
    }
}
