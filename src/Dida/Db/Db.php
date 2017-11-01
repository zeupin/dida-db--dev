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
class Db
{
    /**
     * 版本号
     */
    const VERSION = '0.1.5';

    /**
     * @var \Dida\Db\Connection
     */
    protected $connection = null;

    /**
     * @var \Dida\Db\Builder
     */
    protected $builder = null;

    /**
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
     * 设置$cfg
     *
     * @param array $cfg
     */
    protected function setConfig(array &$cfg)
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
     * 获取的SchemaInfo实例
     *
     * @return \Dida\Db\SchemaInfo
     */
    public function &getSchemaInfo()
    {
        return $this->schemaInfo;
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
     * 返回连接
     *
     * @param string $name Connection的名称
     */
    public function &getConnection($name = null)
    {
        if (!is_string($name)) {
            $name = '';
        }

        if (array_key_exists($name, $this->connections)) {
            return $this->connections[$name];
        } else {
            return null;
        }
    }


    /**
     * 创建一个新的Query实例对象。
     * 对不同的数据库，建议重写对应的逻辑，覆盖掉本方法。
     *
     * @return Query
     */
    protected function newQuery()
    {
        $sql = new Query($this);
        return $sql;
    }


    /**
     * 创建一个新的Query实例对象，然后设置主表
     *
     * @param string $table
     * @param string $prefix
     *
     * @return \Dida\Db\Query
     */
    public function table($table, $prefix = null)
    {
        $sql = $this->newQuery();

        $sql->table($table, $prefix);

        return $sql;
    }
}
