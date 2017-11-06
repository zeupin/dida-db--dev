<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
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
        $this->connection = new \Dida\Db\Connection($this);

        // 配置 Builder，使用标准的 Builder
        $this->builder = new \Dida\Db\Builder($this);

        // 配置 SchemaInfo，使用 MysqlSchemaInfo
        $this->schemainfo = new MysqlSchemaInfo($this);
    }
}
