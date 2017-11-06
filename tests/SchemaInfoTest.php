<?php

/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */
use \PHPUnit\Framework\TestCase;
use \Dida\Debug\Debug;

/**
 * SchemaInfoTest
 */
class SchemaInfoTest extends TestCase
{
    /**
     * @var \Dida\Db\Db
     */
    public $db = null;

    /**
     * @var \Dida\Db\SchemaInfo
     */
    public $schemainfo = null;


    /**
     * 初始化测试环境
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $cfg = include(__DIR__ . "/db.config.php");
        $this->db = new \Dida\Db\Mysql\MysqlDb($cfg);

        $this->schemainfo = $this->db->getSchemaInfo();
    }


    public function test_saveAllTableInfo()
    {
        $this->schemainfo->saveAllTableInfo();
    }
}
