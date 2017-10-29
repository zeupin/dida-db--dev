<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db;

use \PDO;
use \Exception;

/**
 * Db
 */
abstract class Db
{
    /**
     * 版本号
     */
    const VERSION = '0.1.5';

    /**
     * 缺省设置
     *
     * @var array
     */
    protected $cfg = [
        /* pdo 相关参数 */
        'db.dsn'      => null,
        'db.username' => null,
        'db.password' => null,
        'db.options'  => [], // pdo连接参数

        /* 必填参数 */
        'db.name'           => null, // 数据库的名字
        'db.driver_type'    => null, // 数据库驱动类型,如“Mysql”
        'db.schemainfo_dir' => null, // SchemaInfo的缓存目录

        /* 可选参数 */
        'db.charset'     => 'utf8',
        'db.persistence' => false, // 是否用长连接
        'db.prefix'      => '', // 默认的表前缀
        'db.swap_prefix' => '###_', // 默认的形式表前缀
    ];

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
     * 指明数据库类型
     *
     * @var string
     */
    public $dbtype = null;

    /**
     * 配置的SqlBuilder实例
     *
     * @var \Dida\Db\Builder
     */
    protected $builder = null;

    /**
     * 配置的SchemaInfo实例
     *
     * @var \Dida\Db\SchemaInfo
     */
    protected $schemaInfo = null;


    /**
     * 类构造函数
     */
    public function __construct(array $cfg = [])
    {
        $this->setConfig($cfg);
    }


    /**
     * 类析构函数
     */
    public function __destruct()
    {
        $this->pdo = null;
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
     * 配置的SchemaInfo实例
     *
     * @param \Dida\Db\SchemaInfo $schemaInfo
     * @return $this
     */
    public function setSchemaInfo(&$schemaInfo)
    {
        $this->schemaInfo = $schemaInfo;

        return $this;
    }


    /**
     * 获取的SchemaInfo实例
     *
     * @return \Dida\Db\SchemaInfo
     */
    public function &getSchemaInfo()
    {
        return $this->schemaInfo;
    }


    /**
     * 配置Builder实例
     *
     * @param \Dida\Db\Builder $builder
     *
     * @return $this
     */
    public function setBuilder(&$builder)
    {
        $this->builder = $builder;

        return $this;
    }


    /**
     * 获取配置的Builder实例
     *
     * @return \Dida\Db\Builder
     */
    public function &getBuilder()
    {
        return $this->builder;
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
     * 连接数据库
     *
     * @return boolean 成功返回true，失败返回false
     */
    public function connect()
    {
        // If connection exists
        if ($this->pdo !== null) {
            return true;
        }

        // Try to make a connection
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
     * 创建一个新的SqlQuery实例对象。
     * 对不同的数据库，建议重写对应的逻辑，覆盖掉本方法。
     *
     * @return SqlQuery
     */
    protected function newSqlQuery()
    {
        $sql = new SqlQuery($this);
        return $sql;
    }


    /**
     * 创建一个新的SqlQuery实例对象，然后设置主表
     *
     * @param string $table
     * @param string $prefix
     *
     * @return \Dida\Db\SqlQuery
     */
    public function table($table, $prefix = null)
    {
        $sql = $this->newSqlQuery();

        $sql->table($table, $prefix);

        return $sql;
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
     * 执行一条通用SQL语句。
     *
     * @param string $statement 表达式
     * @param array $parameters 参数数组
     *
     * @return boolean 执行成功，返回true；失败，返回false。
     */
    public function execute($statement, array $parameters = null)
    {
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
     * @param array $parameters
     *
     * @return array 成功，返回一个二维数组；失败，返回false。
     */
    public function select($statement, array $parameters = null)
    {
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
     *
     * @return int|false 成功，返回成功插入的记录条数；失败，返回false。
     */
    public function insert($statement, array $parameters = null)
    {
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
     *
     * @return int|false 成功，返回成功更新的记录条数；失败，返回false。
     */
    public function update($statement, array $parameters = null)
    {
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
     *
     * @return int|false 成功，返回成功删除的记录条数；失败，返回false。
     */
    public function delete($statement, array $parameters = null)
    {
        $result = $this->execute($statement, $parameters);

        if ($result) {
            return $this->pdoStatement->rowCount();
        } else {
            return false;
        }
    }
}
