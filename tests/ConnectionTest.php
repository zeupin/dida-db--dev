<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Debug\Debug;

/**
 * ConnectionTest
 */
class ConnectionTest extends TestCase
{
    /**
     * @var \Dida\Db\Connection
     */
    public $conn = null;


    /**
     * 初始化测试环境
     */
    public function __construct()
    {
        $cfg = include(__DIR__ . "/db.config.php");
        $this->conn = new Dida\Db\Connection($cfg);
        $this->conn->setConfig($cfg);
    }


    /**
     * 执行一个SQL文件
     */
    public function resetMock($sql_file)
    {
        $sql = file_get_contents($sql_file);
        $this->conn->getPDO()->exec($sql);
    }


    public function test_init()
    {
        $cfg = include(__DIR__ . "/db.config.php");
        $this->conn = new Dida\Db\Connection($cfg);
        $this->conn->setConfig($cfg);
        $cfgCurrent = $this->conn->getConfig();
        echo Debug::varDump(__METHOD__, $cfg, $cfgCurrent);
    }


    /**
     * 测试phpunit是否正常工作
     */
    public function testPhpUnitWorksWell()
    {
        $value = 1;

        $this->assertEquals(1, $value);
    }


    /**
     * 这个测试要放到开始做，此时pdoStatement尚未被初始化,值为null
     */
    public function test_errorCode_errorInfo()
    {
        $this->assertNull($this->conn->errorCode());
        $this->assertNull($this->conn->errorInfo());
    }


    /**
     * 测试数据库是否可以连接
     */
    public function testConnectDb()
    {
        $this->conn->connect();
        $this->assertTrue($this->conn->isConnected());
    }


    /**
     * 测试数据库是否可以正常工作
     */
    public function testDbWorkWell()
    {
        $this->conn->connect();
        $this->assertTrue($this->conn->worksWell());
    }


    public function test_select()
    {
        // 没有设置data，应该是失败
        $result = $this->conn->select('SELECT * FROM this_table_not_exists');
        $this->assertEquals(false, $result);

        $this->resetMock(__DIR__ . '/zp_test.sql');

        $result1 = $this->conn->select('SELECT * FROM zp_test');
        $result2 = $this->conn->select('SELECT * FROM ###_test', [], true);
        $this->assertEquals($result1, $result2);
        echo gettype($this->conn->getPDOStatement());
//        echo Debug::varDump(__METHOD__, $result1, $result2);
    }


    public function test_insert()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        // 没有设置data，应该是失败
        $result = $this->conn->insert('INSERT INTO zp_test(code,name,price) VALUES (?,?,?)');
        $this->assertEquals(false, $result);

        // 正常插入
        $result = $this->conn->insert('INSERT INTO zp_test(id,code,name,price,modified_at) VALUES (?,?,?,?,?)', [
            5, 'orange', "江西脐橙", 6.8, date("Y-m-d H:i:s")
        ]);
        $this->assertEquals('00000', $this->conn->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->conn->errorCode(), $this->conn->errorInfo(), $this->conn->getPDO()->lastInsertId());

        // 测试部分插入
        $result = $this->conn->insert('INSERT INTO zp_test(code,name,price) VALUES (?,?,?)', [
            uniqid(), "江西脐橙", 6.8
        ]);
        $this->assertEquals('00000', $this->conn->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->conn->errorCode(), $this->conn->errorInfo(), $this->conn->getPDO()->lastInsertId());
    }


    public function test_update()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        // 没有设置data，应该是失败
        $result = $this->conn->update('UPDATE zp_test SET code=? WHERE id=?');
        $this->assertEquals(false, $result);

        // 正常测试
        $result = $this->conn->update('UPDATE zp_test SET code=? WHERE id=?', [uniqid(), 1]);
        $this->assertEquals('00000', $this->conn->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->conn->errorCode(), $this->conn->errorInfo());
    }


    public function test_delete()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        // 没有设置data，应该是失败
        $result = $this->conn->delete('DELETE FROM zp_test WHERE id=?');
        $this->assertEquals(false, $result);

        // 正常测试
        $result = $this->conn->delete('DELETE FROM zp_test WHERE id=?', [1]);
        $this->assertEquals('00000', $this->conn->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->conn->errorCode(), $this->conn->errorInfo());
    }


    public function test_disconnect()
    {
        $this->conn->disconnect();
    }
}
