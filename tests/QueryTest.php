<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Debug\Debug;

/**
 * QueryTest
 */
class QueryTest extends TestCase
{
    public $db = null;

    /**
     * @var \Dida\Db\Query
     */
    public $sqlquery = null;


    /**
     * 初始化测试环境
     */
    public function __construct()
    {
        $cfg = include(__DIR__ . "/db.config.php");
        $conn = new Dida\Db\Connection($cfg);
        $this->sqlquery = new Dida\Db\Query($conn);
    }


    /**
     * 执行一个SQL文件
     */
    public function resetMock($sql_file)
    {
        $sql = file_get_contents($sql_file);
        $this->db->getPDO()->exec($sql);
    }


    public function test_ConditionTree()
    {
        $this->sqlquery
            ->where('id > 3')
            ->where('id < 6')
            ->whereGroup([
                ['a', '=', 1],
                ['b', '=', 1],
            ], 'OR', 'price')
            ->where('price > 8')
            ->where('price < 10')
            ->whereGoto('')
            ->where('age > 100')
            ->whereMatch(['name' => 'Mary', 'age' => 22])
            ->where(['c', 'in', [1,2,3,4]])

            ;
        print_r($this->sqlquery->whereTree);
        print_r($this->sqlquery->whereTree->build());
        //echo Debug::varDump(__METHOD__, $this->sqlquery->whereObject);
        die();
    }


    /**
     * 测试多表表名
     */
    public function testMultiTables()
    {
        $sql = $this->db->table('test as a, test as b', 'zp_');
        $result = $sql->select()->build();
    }


    /**
     * 测试单表表名
     */
    public function testSingleTables()
    {
        $sql = $this->db->table('test as t', 'zp_');
        $result = $sql->select()->build();
    }


    /**
     * 测试模拟数据能否正常使用
     */
    public function testResetMock()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $this->db->connect();
        $sql = $this->db->table('test', 'zp_');
        $result = $sql->select(["count(*)"])->execute()->getRow();
        $this->assertEquals(1, $result['id']);
    }


    /**
     * 测试能够正常build一个简单的数据表表达式
     */
    public function test0Table()
    {
        $admin = $this->db->table('test')
            ->build();
        $expected = <<<EOT
SELECT
    *
FROM
    zp_test
EOT;
        $this->assertEquals($expected, $admin->statement);
        $this->assertEquals([], $admin->parameters);
    }


    /**
     * 测试使用 getColumn() 方法时，用列号和列名是否能得到一致的结果
     */
    public function test_getColumn()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');

        $result1 = $t->getColumn(2);
        $result2 = $t->getColumn('name');

        // 期望$result1=$result2
        $this->assertEquals($result1, $result2);
    }


    /**
     * 测试使用 getColumn() 方法时，用列号和列名是否能得到一致的结果
     */
    public function test_getColumn_1()
    {
        $this->resetMock(__DIR__ . '/zp_test_truncate.sql');

        // user是个空表
        $t = $this->db->table('test');

        $result1 = $t->getColumn(2);
        $result2 = $t->getColumn('name');

        // 期望$result1=$result2
        $this->assertEquals($result1, $result2);
    }


    /**
     * 缓存所有表信息
     */
    public function testCacheAllTableInfo()
    {
        $this->db->getSchemaMap()->saveAllTableInfo();
    }


    /**
     * 读取表信息
     */
    public function testReadTableInfo()
    {
        $data = $this->db->getSchemaMap()->readTableInfoFromCache('zp_test');
        //var_dump($data);
    }
}
