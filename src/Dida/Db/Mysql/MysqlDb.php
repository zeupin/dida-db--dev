<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files MUST retain the above copyright notice.
 */

namespace Dida\Db\Mysql;

/**
 * MysqlDb
 */
class MysqlDb extends \Dida\Db\Db
{
    /**
     * 版本号
     */
    const VERSION = '20171113';

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
