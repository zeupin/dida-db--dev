<?php
/**
 * Dida Framework  <http://dida.zeupin.com>
 *
 * Copyright 2017 Zeupin LLC.
 */

namespace Dida\Db\Mysql;

/**
 * MysqlDb
 */
class MysqlDb extends \Dida\Db\Db
{
    /**
     * Class construct.
     *
     * @param array $cfg
     */
    public function __construct(array $cfg = array())
    {
        parent::__construct($cfg);

        // 数据库的 driver 名称
        $this->cfg['db.driver'] = 'Mysql';

        // 配置 Connection
        $conn = new \Dida\Db\Connection($this->getConfig());
        $this->connection = &$conn;

        // 配置 SchemaInfo，使用 MysqlSchemaInfo
        $schemainfo = new MysqlSchemaInfo($this);
        $this->schemainfo = &$schemainfo;

        // 配置 Builder，使用标准的 Builder
        $builder = new \Dida\Db\Builder($this);
        $this->builder = &$builder;
    }
}
