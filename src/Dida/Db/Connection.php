<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
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
     * PDO实例
     *
     * @var \PDO
     */
    protected $pdo = null;

    /**
     * PDOStatement实例
     *
     * @var \PDOStatement
     */
    protected $pdoStatement = null;

    /**
     * 缺省设置
     *
     * @var array
     */
    protected $cfg = [
        /* 必填参数 */
        'db.name'           => null, // 数据库的名字
        'db.driver'         => null, // 数据库驱动类型,如“Mysql”
        'db.schemainfo_dir' => null, // SchemaInfo的缓存目录

        /* pdo 相关参数 */
        'db.dsn'      => null,
        'db.username' => null,
        'db.password' => null,
        'db.options'  => [], // pdo连接参数

        /* 可选参数 */
        'db.charset'     => 'utf8',
        'db.persistence' => false, // 是否用长连接
        'db.prefix'      => '', // 默认的数据表前缀
        'db.swap_prefix' => '###_', // 默认的数据表形式前缀
    ];


    /**
     * 类的构造函数
     *
     * @param array $config
     */
    public function __construct(array $cfg)
    {
        $this->setConfig($cfg);
    }


    /**
     * 设置$cfg
     *
     * @param array $cfg
     */
    public function setConfig(array &$cfg)
    {
        foreach ($this->cfg as $key => $value) {
            if (array_key_exists($key, $cfg)) {
                $this->cfg[$key] = $cfg[$key];
            }
        }

        return $this;
    }


    /**
     * 返回实例的$cfg数组
     *
     * @return array
     */
    public function &getConfig()
    {
        return $this->cfg;
    }


    /**
     * 立即连接数据库，并返回PDO实例
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
     * 返回当前的PDOStatement实例。
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
     * 连接数据库
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
     * 检查是否已经连接数据库
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
     * 断开数据库连接
     */
    public function disconnect()
    {
        $this->pdo = null;
    }


    /**
     * 执行一条通用SQL语句。
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
     * 执行一条SELECT语句
     *
     * @param string $statement
     * @param array $parameters 参数数组
     * @param boolean $replace_prefix 是否替换表前缀
     *
     * @return array 成功，返回一个二维数组；失败，返回false。
     */
    public function select($statement, array $parameters = null, $replace_prefix = false)
    {
        // 如果需要替换表前缀
        if ($replace_prefix) {
            $statement = $this->replacePrefix($statement);
        }

        $result = $this->execute($statement, $parameters);

        if ($result) {
            return $this->pdoStatement->fetchAll();
        } else {
            return false;
        }
    }


    /**
     * 执行一条INSERT语句
     *
     * @param string $statement 表达式
     * @param array $parameters 参数数组
     * @param boolean $replace_prefix 是否替换表前缀
     *
     * @return int|false 成功，返回成功插入的记录条数；失败，返回false。
     */
    public function insert($statement, array $parameters = null, $replace_prefix = false)
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
     * 执行一条UPDATE语句
     *
     * @param string $statement 表达式
     * @param array $parameters 参数数组
     * @param boolean $replace_prefix 是否替换表前缀
     *
     * @return int|false 成功，返回成功更新的记录条数；失败，返回false。
     */
    public function update($statement, array $parameters = null, $replace_prefix = false)
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
     * 执行一条DELETE语句
     *
     * @param string $statement 表达式
     * @param array $parameters 参数数组
     * @param boolean $replace_prefix 是否替换表前缀
     *
     * @return int|false 成功，返回成功删除的记录条数；失败，返回false。
     */
    public function delete($statement, array $parameters = null, $replace_prefix = false)
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