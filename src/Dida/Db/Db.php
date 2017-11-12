<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db;

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
     * @var \Dida\Db\SchemaInfo
     */
    protected $schemainfo = null;

    /**
     * @var \Dida\Db\Builder
     */
    protected $builder = null;

    /**
     * 缺省设置
     *
     * @var array
     */
    protected $cfg = [
        /* 必填参数 */
        'db.name'   => null, // 数据库的名字
        'db.driver' => null, // 数据库驱动类型,如“Mysql”

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
     * 扩展方法
     */
    use Traits\DbTrait;


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
        foreach ($cfg as $key => $value) {
            if (substr($key, 0, 3) === 'db.') {
                $this->cfg[$key] = $value;
            }
        }

        // 如果没有指定默认的fetchmode，则将其设置为FETCH_ASSOC
        if (!array_key_exists('db.options', $this->cfg)) {
            $this->cfg['db.options'] = [];
        }
        if (!array_key_exists(\PDO::ATTR_DEFAULT_FETCH_MODE, $this->cfg['db.options'])) {
            $this->cfg['db.options'][\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
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
     * 配置 Connection 实例。
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
    public function &getConnection()
    {
        return $this->connection;
    }


    /**
     * 配置 Builder 实例。
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
    public function &getBuilder()
    {
        return $this->builder;
    }


    /**
     * 配置 SchemaInfo 实例。
     *
     * @param \Dida\Db\SchemaInfo $schemainfo
     */
    public function setSchemaInfo($schemainfo)
    {
        $this->schemainfo = $schemainfo;

        return $this;
    }


    /**
     * 返回当前的 SchemaInfo 实例。
     *
     * @return  \Dida\Db\SchemaInfo
     */
    public function &getSchemaInfo()
    {
        return $this->schemainfo;
    }


    /**
     * 创建一个新的Query实例对象。
     *
     * 针对不同的数据库，应该重写对应的逻辑，覆盖掉本方法。
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
