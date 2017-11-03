<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Debug\Debug;

/**
 * QueryTest
 */
class QueryTest extends TestCase
{
    /**
     * @var \Dida\Db\Db
     */
    public $db = null;

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
        $this->db = new Dida\Db\Mysql\MysqlDb($cfg);
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
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $query = $this->db->table('test');
        print_r($query);

        $query
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
            ->where(['c', 'in', [1, 2, 3, 4]])
        ;
        $sql = $query->verb('select')->build();
        print_r($sql);
    }


    /**
     * 测试多表表名
     */
    public function testMultiTables()
    {
        $query = $this->db->table('test as a, test as b', 'zp_');

        $sql = $query->verb('select')->build();
        print_r($sql);
    }


    /**
     * 测试单表表名
     */
    public function testSingleTables()
    {
        $query = $this->db->table('test as t', 'zp_');

        $sql = $query->verb('select')->build();
        print_r($sql);
    }


    /**
     * 测试模拟数据能否正常使用
     */
    public function testResetMock()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $query = $this->db->table('test', 'zp_');
        $result = $query->select(["count(*)"])->getRow();
        print_r($result);
    }


    /**
     * 测试能够正常build一个简单的数据表表达式
     */
    public function test0Table()
    {
        $admin = $this->db->table('test')->build();
        $expected = <<<EOT
SELECT
    *
FROM
    zp_test
EOT;
        $this->assertEquals($expected, $admin['statement']);
        $this->assertEquals([], $admin['parameters']);
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


    /**
     * 测试 select()
     */
    public function test_select()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');
        $result = $t->verb('SELECT')->build();
        print_r($result);
        $expected = <<<'EOT'
SELECT
    *
FROM
    zp_test
EOT;
        $this->assertEquals($expected, $result['statement']);

        $result = $t->select()->getRows();
        print_r($result);
    }


    /**
     * 测试 count()
     */
    public function test_count()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');
        $result = $t->count()->verb('SELECT')->build();
        print_r($result);
        $expected = <<<'EOT'
SELECT
    COUNT(*)
FROM
    zp_test
EOT;
        $this->assertEquals($expected, $result['statement']);
    }


    /**
     * 测试 delete()
     */
    public function test_delete()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');
        $result = $t->verb('DELETE')->build();
        print_r($result);
        $expected = <<<'EOT'
DELETE FROM zp_test
EOT;
        $this->assertEquals($expected, $result['statement']);

        $result = $t->delete();
        $this->assertEquals(2, $result);
    }


    /**
     * 测试 truncate()
     */
    public function test_truncate()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');
        $result = $t->verb('TRUNCATE')->build();
        print_r($result);
        $expected = <<<'EOT'
TRUNCATE TABLE zp_test
EOT;
        $this->assertEquals($expected, $result['statement']);

        $result = $t->delete();
        $this->assertEquals(2, $result);
    }


    /**
     * 测试 insert()
     */
    public function test_insert()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $record = [
            'code'  => uniqid(),
            'name'  => '香蕉',
            'price' => 5.2,
        ];

        $t = $this->db->table('test');
        $result = $t->record($record)->verb('INSERT')->build();
        print_r($result);

        $result = $t->insert();
        $this->assertEquals(1, $result);
    }
}
