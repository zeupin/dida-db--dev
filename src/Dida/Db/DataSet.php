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
     * 获取所有行。对 fetchAll() 的简单调用。
     *
     * @return array(array)|false
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
     * @param int|string $column  列序号或列名
     *
     * @return array|false 成功返回数组，失败返回false。
     */
    public function getColumn($column)
    {
        // 列号
        $colnum = $this->getColumnNumber($column);
        if ($colnum === false) {
            return false;
        }

        // 返回指定列的所有行
        return $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $colnum);
    }


    /**
     * 获取DataSet下一行的指定列的值
     *
     * @param int|string $column  列序号或列名
     */
    public function getValue($column = 0)
    {
        // 列号
        $colnum = $this->getColumnNumber($column);
        if ($colnum === false) {
            return false;
        }

        // 返回指定列的值
        return $this->pdoStatement->fetchColumn($colnum);
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
}
