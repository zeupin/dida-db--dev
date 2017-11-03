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
     * @var \Dida\Db\SchemaMap
     */
    protected $schemamap = null;

    /**
     * 缺省设置
     *
     * @var array
     */
    protected $cfg = [
        /* 必填参数 */
        'db.name'          => null, // 数据库的名字
        'db.driver'        => null, // 数据库驱动类型,如“Mysql”
        'db.schemamap_dir' => null, // SchemaMap的缓存目录

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
     * 类构造函数
     */
    public function __construct(array $cfg)
    {
        $this->setConfig($cfg);
    }


    /**
     * 设置 $cfg
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
     * 返回实例的 $cfg
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->cfg;
    }


    /**
     * 设置当前的 Connection 实例。
     *
     * @param \Dida\Db\Connection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }


    /**
     * 返回当前的 Connection 实例。
     *
     * @return \Dida\Db\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }


    /**
     * 设置当前的 Builder 实例。
     *
     * @param \Dida\Db\Builder $builder
     */
    public function setBuilder($builder)
    {
        $this->builder = $builder;

        return $this;
    }


    /**
     * 返回当前的 Builder 实例。
     *
     * @return  \Dida\Db\Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }


    /**
     * 设置当前的 SchemaMap 实例。
     *
     * @param \Dida\Db\SchemaMap $schemamap
     */
    public function setSchemeMap($schemamap)
    {
        $this->schemamap = $schemamap;

        return $this;
    }


    /**
     * 返回当前的 SchemaMap 实例。
     *
     * @return  \Dida\Db\SchemaMap
     */
    public function getSchemaMap()
    {
        return $this->schemamap;
    }


    /**
     * 创建一个新的Query实例对象。
     *
     * 针对不同的数据库，建议重写对应的逻辑，覆盖掉本方法。
     *
     * @return Query
     */
    protected function newQuery()
    {
        $query = new Query($this);

        return $query;
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
        $query = $this->newQuery();

        $query->table($table, $prefix);

        return $query;
    }
}
