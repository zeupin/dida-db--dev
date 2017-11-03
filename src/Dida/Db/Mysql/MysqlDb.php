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

        // Set the dbtype
        $this->dbtype = 'Mysql';

        // 配置 Connection
        $this->connection = new \Dida\Db\Connection($cfg);

        // 配置 Builder，使用标准的 Builder
        $this->builder = new \Dida\Db\Builder($this);

        // 配置 SchemaMap，使用 MysqlSchemaMap
        $this->schemamap = new MysqlSchemaMap($this);
    }
}
