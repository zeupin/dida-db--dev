<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db;

use \PDO;
use \PDOStatement;
use \Exception;

/**
 * DataSet
 */
class DataSet
{
    /**
     * Version
     */
    const VERSION = '0.1.5';

    /**
     * 存储的PDOStatement实例。
     *
     * @var \PDOStatement
     */
    public $pdoStatement = null;

    /**
     * 缓存的列元的信息
     *
     * @var array
     */
    protected $column_metas = null;


    /**
     * 类的构造函数。
     *
     * @param \PDOStatement $pdoStatement
     */
    public function __construct(\PDOStatement $pdoStatement = null)
    {
        $this->pdoStatement = $pdoStatement;
    }


    /**
     * 低级操作，直接调用PDOStatement的setFetchMode()。
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
     * 低级操作，直接调用PDOStatement的fetch()。
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
     * 低级操作，直接调用PDOStatement的fetchAll()。
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
     * 返回下一条数据的指定列的数据。
     *
     * @param int $column_number
     */
    public function fetchColumn($column_number = 0)
    {
        return $this->pdoStatement->fetchColumn($column_number);
    }


    /**
     * 返回 PDOStatement::errorCode()
     *
     * @return string
     */
    public function errorCode()
    {
        return $this->pdoStatement->errorCode();
    }


    /**
     * 返回 PDOStatement::errorInfo()
     *
     * @return array
     */
    public function errorInfo()
    {
        return $this->pdoStatement->errorInfo();
    }


    /**
     * 获取结果的行数。
     *
     * 同 PDOStatement::rowCount()，参见PDO文档。
     * 有些数据可能返回由此语句返回的行数。但这种方式不能保证对所有数据有效，且对于可移植的应用不应依赖于此方式。
     */
    public function rowCount()
    {
        return $this->pdoStatement->rowCount();
    }


    /**
     * 获取结果的列数。
     * 同 PDOStatement::columnCount()，参见PDO文档。
     */
    public function columnCount()
    {
        return $this->pdoStatement->columnCount();
    }


    /**
     * 导出本次查询的参数数据。
     * 同 PDOStatement::debugDumpParams()，参见PDO文档。
     */
    public function debugDumpParams()
    {
        return $this->pdoStatement->debugDumpParams();
    }


    /**
     * 获取下一行，对fetch()的一个简单调用。
     *
     * @return array
     */
    public function getRow()
    {
        return $this->pdoStatement->fetch();
    }


    /**
     * 获取所有行。对fetchAll()的简单调用。
     *
     * @return array(array)
     */
    public function getRows()
    {
        return $this->pdoStatement->fetchAll();
    }


    /**
     * 获取指定列的所有行。
     *
     * 可以指定列名或者列序号，其中第一列的序号是0。
     *
     * @param int|string $column
     * @return array|false 成功返回数组，失败返回false。
     */
    public function getColumn($column)
    {
        $column_count = $this->pdoStatement->columnCount();

        /* 如果是列名 */
        if (is_string($column)) {
            $pos = $this->getColumnPosByName($column);
            if ($pos === false) {
                return false;
            } else {
                return $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $pos);
            }
        }

        /* 如果是列序号 */
        if (is_int($column)) {
            if ($column_count > $column) {
                return $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $column);
            } else {
                return false;
            }
        }

        /* 失败 */
        return false;
    }


    /**
     * 给出列名，查出找到对应的第一个列序号。
     *
     * 注意：SQL同一个列名完全可以对应不同的列号，如“SELECT id,id FROM user”。
     *
     * @param string $column_name
     *
     * @return int|false 找到返回列序号，没有找到返回false。
     */
    public function getColumnPosByName($column_name)
    {
        // 如果还没有列元信息，则先获取
        if ($this->column_metas === null) {
            $this->cacheColumnMetas();
        }

        // 返回第一个找到的列号
        $column_count = count($this->column_metas);
        for ($i = 0; $i < $column_count; $i++) {
            $column_meta = $this->column_metas[$i];
            if ($column_name == $column_meta['name']) {
                return $i;
            }
        }

        // 没有找到的话，返回false
        return false;
    }


    /**
     * 缓存列元数组
     */
    protected function cacheColumnMetas()
    {
        $this->column_metas = [];

        $column_count = $this->pdoStatement->columnCount();

        for ($i = 0; $i < $column_count; $i++) {
            $column_meta = $this->pdoStatement->getColumnMeta($i);
            $this->column_metas[$i] = $column_meta;
        }
    }
}
