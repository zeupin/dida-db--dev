<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License
 * Redistributions of files MUST retain the above copyright notice.
 */

namespace Dida\Db;

use \PDO;
use \Exception;

/**
 * Connection
 */
class Connection
{
    /**
     * @var \Dida\Db\Db
     */
    protected $db = null;

    /**
     * PDO 实例
     *
     * @var \PDO
     */
    protected $pdo = null;

    /**
     * PDOStatement 实例
     *
     * @var \PDOStatement
     */
    protected $pdoStatement = null;

    /**
     * 配置
     *
     * @var array
     */
    protected $cfg = [];


    /**
     * 类的构造函数
     *
     * @param \Dida\Db\Db $db
     */
    public function __construct($cfg)
    {
        $this->cfg = [
            'db.driver'      => $cfg['db.driver'],
            'db.dsn'         => $cfg['db.dsn'],
            'db.username'    => $cfg['db.username'],
            'db.password'    => $cfg['db.password'],
            'db.options'     => $cfg['db.options'],
            'db.prefix'      => $cfg['db.prefix'],
            'db.swap_prefix' => $cfg['db.swap_prefix'],
        ];
    }


    /**
     * 连接数据库。
     *
     * @return boolean 成功返回true，失败返回false
     */
    public function connect()
    {
        // 如果连接已经建立
        if ($this->pdo !== null) {
            return true;
        }

        // 否则，建立一个连接
        try {
            $this->pdo = new PDO(
                $this->cfg['db.dsn'], $this->cfg['db.username'], $this->cfg['db.password'], $this->cfg['db.options']
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * 断开数据库连接。
     *
     * @return void
     */
    public function disconnect()
    {
        $this->pdo = null;
    }


    /**
     * 检查是否已经连接数据库
     *
     * @return boolean
     */
    public function isConnected()
    {
        return ($this->pdo !== null);
    }


    /**
     * 连接是否还能正常工作。
     * 检查是否已经连接数据库，且尚未被数据库断开，并能正常执行sql语句。
     *
     * @return boolean
     */
    public function worksWell()
    {
        if ($this->pdo === null) {
            return false;
        }

        // 检查是否能执行简单的SQL语句
        try {
            if ($this->pdo->query('SELECT 1') === false) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * 立即连接数据库，并返回PDO实例。
     *
     * @return \PDO|false
     */
    public function getPDO()
    {
        if ($this->connect()) {
            return $this->pdo;
        } else {
            return false;
        }
    }


    /**
     * 返回当前的 PDOStatement 实例。
     *
     * @return \PDOStatement
     */
    public function getPDOStatement()
    {
        return $this->pdoStatement;
    }


    /**
     * 获取跟上一次语句句柄操作相关的 SQLSTATE 错误码，（一个由5个字母或数字组成的在 ANSI SQL 标准中定义的标识符）。
     *
     * @return string|null 成功返回string，失败返回null
     */
    public function errorCode()
    {
        if ($this->pdoStatement === null) {
            return null;
        } else {
            return $this->pdoStatement->errorCode();
        }
    }


    /**
     * 返回一个关于上一次语句句柄执行操作的错误信息的数组。
     *
     * 该数组包含下列字段：
     * 0 SQLSTATE 错误码（一个由5个字母或数字组成的在 ANSI SQL 标准中定义的标识符）。
     * 1 具体驱动错误码。
     * 2 具体驱动错误信息。
     *
     * @return array
     */
    public function errorInfo()
    {
        if ($this->pdoStatement === null) {
            return null;
        } else {
            return $this->pdoStatement->errorInfo();
        }
    }


    /**
     * 执行一条通用SQL语句,返回true/false。
     *
     * @param string $statement 表达式
     * @param array $parameters 参数数组
     * @param boolean $replace_prefix 是否替换表前缀
     *
     * @return boolean 执行成功，返回true；失败，返回false。
     */
    public function execute($statement, array $parameters = null, $replace_prefix = false)
    {
        // 如果需要替换表前缀
        if ($replace_prefix) {
            $statement = $this->replacePrefix($statement);
        }

        try {
            $this->pdo = $this->getPDO();
            $this->pdoStatement = $this->pdo->prepare($statement);
            $result = $this->pdoStatement->execute($parameters);
            return $result;
        } catch (Exception $ex) {
            $this->pdoStatement = null;
            return false;
        }
    }


    /**
     * 执行一条查询类的语句（SELECT），返回一个DataSet。
     *
     * @param string $statement
     * @param array $parameters 参数数组
     * @param boolean $replace_prefix 是否替换表前缀
     *
     * @return \Dida\Db\DataSet|false 成功，返回一个DataSet；失败，返回false。
     */
    public function executeRead($statement, array $parameters = null, $replace_prefix = false)
    {
        // 如果需要替换表前缀
        if ($replace_prefix) {
            $statement = $this->replacePrefix($statement);
        }

        $result = $this->execute($statement, $parameters);

        if ($result) {
            $dataset = new DataSet($this->pdoStatement);
            return $dataset;
        } else {
            return false;
        }
    }


    /**
     * 执行一条修改类的语句（INSERT/UPDATE/DELETE)，并返回影响的记录条数。
     *
     * @param string $statement 表达式
     * @param array $parameters 参数数组
     * @param boolean $replace_prefix 是否替换表前缀
     *
     * @return int|false 成功，返回成功插入的记录条数；失败，返回false。
     */
    public function executeWrite($statement, array $parameters = null, $replace_prefix = false)
    {
        // 如果需要替换表前缀
        if ($replace_prefix) {
            $statement = $this->replacePrefix($statement);
        }

        $result = $this->execute($statement, $parameters);

        if ($result) {
            return $this->pdoStatement->rowCount();
        } else {
            return false;
        }
    }


    /**
     * 把SQL表达式中的 ###_XXX 替换为 prefix_XXX
     */
    protected function replacePrefix($statement)
    {
        $prefix = $this->cfg['db.prefix'];
        $swap_prefix = $this->cfg['db.swap_prefix'];

        if ($swap_prefix) {
            return str_replace($swap_prefix, $prefix, $statement);
        } else {
            return $statement;
        }
    }
}
