<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Debug\Debug;

/**
 * DbTest
 */
class DbTest extends TestCase
{
    public $db = null;


    /**
     * 初始化测试环境
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->db = new \Dida\Db\Mysql\MysqlDb(include(__DIR__ . "/db.config.php"));
    }


    /**
     * 执行一个mock的SQL文件
     */
    public function resetMock($sql_file)
    {
        $sql = file_get_contents($sql_file);
        $this->db->getPDO()->exec($sql);
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
        $this->assertNull($this->db->errorCode());
        $this->assertNull($this->db->errorInfo());
    }


    /**
     * 测试数据库是否可以连接
     */
    public function testConnectDb()
    {
        $this->db->connect();
        $this->assertTrue($this->db->isConnected());
    }


    /**
     * 测试数据库是否可以正常工作
     */
    public function testDbWorkWell()
    {
        $this->db->connect();
        $this->assertTrue($this->db->worksWell());
    }


    public function test_select()
    {
        // 没有设置data，应该是失败
        $result = $this->db->executeRead('SELECT * FROM this_table_not_exists');
        $this->assertFalse( $result);

        $this->resetMock(__DIR__ . '/zp_test.sql');

        $result = $this->db->executeRead('SELECT * FROM zp_test');
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_insert()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        // 没有设置data，应该是失败
        $result = $this->db->insert('INSERT INTO zp_test(code,name,price) VALUES (?,?,?)');
        $this->assertEquals(false, $result);

        // 正常插入
        $result = $this->db->insert('INSERT INTO zp_test(id,code,name,price,modified_at) VALUES (?,?,?,?,?)', [
            5, 'orange', "江西脐橙", 6.8, date("Y-m-d H:i:s")
        ]);
        $this->assertEquals('00000', $this->db->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->db->errorCode(), $this->db->errorInfo(), $this->db->getPDO()->lastInsertId());

        // 测试部分插入
        $result = $this->db->insert('INSERT INTO zp_test(code,name,price) VALUES (?,?,?)', [
            uniqid(), "江西脐橙", 6.8
        ]);
        $this->assertEquals('00000', $this->db->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->db->errorCode(), $this->db->errorInfo(), $this->db->getPDO()->lastInsertId());
    }


    public function test_update()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        // 没有设置data，应该是失败
        $result = $this->db->update('UPDATE zp_test SET code=? WHERE id=?');
        $this->assertEquals(false, $result);

        // 正常测试
        $result = $this->db->update('UPDATE zp_test SET code=? WHERE id=?', [uniqid(), 1]);
        $this->assertEquals('00000', $this->db->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->db->errorCode(), $this->db->errorInfo());
    }


    public function test_delete()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        // 没有设置data，应该是失败
        $result = $this->db->delete('DELETE FROM zp_test WHERE id=?');
        $this->assertEquals(false, $result);

        // 正常测试
        $result = $this->db->delete('DELETE FROM zp_test WHERE id=?', [1]);
        $this->assertEquals('00000', $this->db->errorCode());
        $this->assertEquals(1, $result);
        echo Debug::varDump(__METHOD__, $result, $this->db->errorCode(), $this->db->errorInfo());
    }
}
