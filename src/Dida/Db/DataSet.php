<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files MUST retain the above copyright notice.
 */

namespace Dida\Db;

use \PDO;

/**
 * DataSet
 */
class DataSet
{
    /**
     * Version
     */
    const VERSION = '20171113';

    /**
     * 存储的PDOStatement实例。
     *
     * @var \PDOStatement
     */
    public $pdoStatement = null;

    /**
     * Statement的列数
     *
     * @var int
     */
    public $columnCount = null;

    /**
     * 缓存的列元的信息
     *
     * @var array
     */
    public $columnMetas = null;


    /**
     * 类的构造函数。
     *
     * @param \PDOStatement $pdoStatement
     */
    public function __construct(\PDOStatement $pdoStatement = null)
    {
        $this->pdoStatement = $pdoStatement;

        // 列数
        $this->columnCount = $pdoStatement->columnCount();

        // 列元信息
        $this->columnMetas = [];
        for ($i = 0; $i < $this->columnCount; $i++) {
            $this->columnMetas[$i] = $pdoStatement->getColumnMeta($i);
        }
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 setFetchMode()。
     *
     * bool PDOStatement::setFetchMode ( int $mode )
     * bool PDOStatement::setFetchMode ( int $PDO::FETCH_COLUMN , int $colno )
     * bool PDOStatement::setFetchMode ( int $PDO::FETCH_CLASS , string $classname , array $ctorargs )
     * bool PDOStatement::setFetchMode ( int $PDO::FETCH_INTO , object $object )
     *
     * @param int $mode
     * @param int|string|object $arg1
     * @param array $arg2
     */
    public function setFetchMode()
    {
        return call_user_func_array([$this->pdoStatement, 'setFetchMode'], func_get_args());
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 fetch()。
     *
     * PDOStatement::fetch (int $fetch_style );
     * PDOStatement::fetch (int $fetch_style, int $cursor_orientation = PDO::FETCH_ORI_NEXT);
     * PDOStatement::fetch (int $fetch_style, int $cursor_orientation = PDO::FETCH_ORI_NEXT, int $cursor_offset = 0 );
     *
     * @return mixed|false
     */
    public function fetch()
    {
        return call_user_func_array([$this->pdoStatement, 'fetch'], func_get_args());
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 fetchAll()。
     *
     * array PDOStatement::fetchAll(int $fetch_style, mixed $fetch_argument, array $ctor_args = array());
     *
     * @return array|false
     */
    public function fetchAll()
    {
        return call_user_func_array([$this->pdoStatement, 'fetchAll'], func_get_args());
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 fetchColumn()。
     *
     * @param int $column_number
     */
    public function fetchColumn($column_number = 0)
    {
        return $this->pdoStatement->fetchColumn($column_number);
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 errorCode()。
     *
     * @return string
     */
    public function errorCode()
    {
        return $this->pdoStatement->errorCode();
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 errorInfo()。
     *
     * @return array
     */
    public function errorInfo()
    {
        return $this->pdoStatement->errorInfo();
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 rowCount()。
     *
     * 有些数据可能返回由此语句返回的行数。但这种方式不能保证对所有数据有效，且对于可移植的应用不应依赖于此方式。
     */
    public function rowCount()
    {
        return $this->pdoStatement->rowCount();
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 columnCount()。
     */
    public function columnCount()
    {
        return $this->pdoStatement->columnCount();
    }


    /**
     * 低级操作，直接调用 PDOStatement 的 debugDumpParams()。
     */
    public function debugDumpParams()
    {
        return $this->pdoStatement->debugDumpParams();
    }


    /**
     * 获取下一行，对 fetch() 的一个简单调用。
     *
     * @return array|false
     */
    public function getRow()
    {
        return $this->pdoStatement->fetch();
    }


    /**
     * 获取所有行。
     *
     * @return array(array)|false
     */
    public function getRows()
    {
        $array = $this->pdoStatement->fetchAll();
        return $array;
    }


    /**
     * 获取键值化的 Rows。
     *
     * 注意：
     * 需要自行保证给出的 $col1,$col2,$colN 的组合可以唯一确定一条记录。
     * 否则，对于同一 $col1,$col2,$colN，后值将覆盖前值。
     *
     * @param string|int|array $colN    需要分组的列名，可用多个字段进行分组
     *
     */
    public function getRowsAssocBy($colN)
    {
        $array = $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC);

        if (is_array($colN)) {
            return Util::arrayAssocBy($array, $colN);
        } else {
            return Util::arrayAssocBy($array, func_get_args());
        }
    }


    /**
     * 获取分组化的Rows
     *
     * @param string|int|array $colN    需要分组的列名，可用多个字段进行分组
     */
    public function getRowsGroupBy($colN)
    {
        $array = $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC);

        if (is_array($colN)) {
            return Util::arrayGroupBy($array, $colN);
        } else {
            return Util::arrayGroupBy($array, func_get_args());
        }
    }


    /**
     * 给出列名，查出找到对应的第一个列序号。
     *
     * 注意：SQL同一个列名完全可以对应不同的列号，如“SELECT id,id FROM user”。
     *
     * @param int|string $column  指定列名或者列序号，其中第一列的序号是0。
     *
     * @return int|false 找到返回列序号，没有找到返回false。
     */
    public function getColumnNumber($column)
    {
        // 如果给出的是字符串
        if (is_string($column)) {
            // 匹配第一个找到的列号
            for ($i = 0; $i < $this->columnCount; $i++) {
                $column_meta = $this->columnMetas[$i];
                if ($column === $column_meta['name']) {
                    return $i;
                }
            }

            // 没有找到，返回 false
            return false;
        }

        // 如果给出的是列号
        if (is_int($column)) {
            // 合法性检查
            if (($column < 0) || ($column >= $this->columnCount)) {
                return false;
            }

            // 返回
            return $column;
        }

        // 非法
        return false;
    }


    /**
     * 获取指定列的所有行。
     *
     * @param int|string $column  指定列名或者列序号，其中第一列的序号是0。
     *
     * @return array|false 成功返回数组，失败返回false。
     */
    public function getColumn($column, $key = null)
    {
        // 列号
        $colnum = $this->getColumnNumber($column);
        if ($colnum === false) {
            return false;
        }

        if (!is_null($key)) {
            $key = $this->getColumnNumber($key);
            if ($key === false) {
                return false;
            }
        }

        if (is_null($key)) {
            // 返回指定列的所有行
            return $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $colnum);
        } else {
            // 返回指定列的所有行
            $array = $this->pdoStatement->fetchAll(PDO::FETCH_NUM);
            return array_column($input, $colnum, $key);
        }
    }


    /**
     * 获取指定列的唯一值。
     *
     * @param int|string $column  指定列名或者列序号，其中第一列的序号是0。
     *
     * @return array|false 成功返回数组，失败返回false。
     */
    public function getColumnDistinct($column)
    {
        // 列号
        $colnum = $this->getColumnNumber($column);
        if ($colnum === false) {
            return false;
        }

        // 返回指定列的所有行
        return $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE, $colnum);
    }


    /**
     * 获取DataSet下一行的指定列的值。
     *
     * 注意：似乎 fetchColumn() 返回的都是字符串类型的值，而不是预期的在数据库中定义的 int/float 等类型。
     * 估计他们的初衷是考虑到数据库的变量类型和PHP的变量类型转换有差异，所以把这个转换留给开发者自行处理。
     * 因此，如果需要返回特定类型的值，需要指定 $returnType 参数。
     *
     * @param int|string $column   指定列名或者列序号，其中第一列的序号是0。
     * @param string $returnType   返回的类型，可为 int/float
     */
    public function getValue($column = 0, $returnType = null)
    {
        // 列号
        $colnum = $this->getColumnNumber($column);
        if ($colnum === false) {
            return false;
        }

        // fetchColumn
        $v = $this->pdoStatement->fetchColumn($colnum);

        // 如果为空，返回null
        if (is_null($v)) {
            return null;
        }

        switch ($returnType) {
            case 'int':
                return (is_numeric($v)) ? intval($v) : false;
            case 'float':
                return (is_numeric($v)) ? floatval($v) : false;
            default:
                return $v;
        }
    }
}
