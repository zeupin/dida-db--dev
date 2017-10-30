<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Db\Connection;
use \Dida\Db\Db;
use \Dida\Db\DataSet;
use \Dida\Debug\Debug;

/**
 * DateSetTest
 */
class DateSetTest extends TestCase
{
    public $dataset = null;

    /**
     * @var \Dida\Db\Connection
     */
    public $conn = null;


    /**
     * 初始化测试环境
     */
    public function __construct()
    {
        $this->conn = new Dida\Db\Connection(include(__DIR__ . "/db.config.php"));
    }


    /**
     * 执行一个SQL文件
     */
    public function resetMock($sql_file)
    {
        $sql = file_get_contents($sql_file);
        $this->conn->getPDO()->exec($sql);
    }


    public function initData()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $result = $this->conn->execute('select * from zp_test');
        if ($result) {
            $this->dataset = new Dida\Db\DataSet($this->conn->getPDOStatement());
        }
    }


    public function test_fetch()
    {
        $this->initData();
        $record = $this->dataset->fetch();
        echo Debug::varDump(__METHOD__, $record);
    }


    public function test_fetchAll()
    {
        $this->initData();
        $records = $this->dataset->fetchAll();
        $this->assertNotNull($records);
        echo Debug::varDump(__METHOD__, $records);
    }


    public function test_fetchColumn()
    {
        $this->initData();
        $value1 = $this->dataset->fetchColumn();
        $this->assertEquals(1, $value1);
    }
}
