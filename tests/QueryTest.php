<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Debug\Debug;
use \Dida\Db\Query;

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

    protected $ALL_COLUMN_NAMES = 'id, code, name, price, modified_at';


    /**
     * 初始化测试环境
     */
    public function __construct()
    {
        $cfg = include(__DIR__ . "/db.config.php");
        $this->db = new Dida\Db\Mysql\MysqlDb($cfg);
        $schemainfo = $this->db->getSchemaInfo();
    }


    /**
     * 执行一个SQL文件
     */
    public function resetMock($sql_file)
    {
        $sql = file_get_contents($sql_file);
        $this->db->getPDO()->exec($sql);
    }


    public function test_all_columns()
    {
        $query = $this->db->table('test')->build('select');
        print_r($query);
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
        $sql = $query->build('SELECT');
        print_r($sql);
    }


    /**
     * 测试多表表名
     */
    public function testMultiTables()
    {
        $query = $this->db->table('test as a, test as b', 'zp_');

        $sql = $query->build('SELECT');
        print_r($sql);
    }


    /**
     * 测试单表表名
     */
    public function testSingleTables()
    {
        $query = $this->db->table('test as t', 'zp_');

        $sql = $query->build('SELECT');
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
    $this->ALL_COLUMN_NAMES
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
        $this->db->getSchemaInfo()->cacheAllTables();
    }


    /**
     * 读取表信息
     */
    public function testReadTableInfo()
    {
        $data = $this->db->getSchemaInfo()->getTable('zp_test');
        var_dump($data);
    }


    /**
     * 测试 select()
     */
    public function test_select()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');
        $result = $t->build('SELECT');
        print_r($result);
        $expected = <<<EOT
SELECT
    $this->ALL_COLUMN_NAMES
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
    public function test_count_stmt()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');
        $result = $t->count()->build('SELECT');
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
        $result = $t->build('DELETE');
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
        $result = $t->build('TRUNCATE');
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

        $record = ['code' => uniqid(), 'name' => '香蕉', 'price' => 5.2,];

        $t = $this->db->table('test');
        $result = $t->record($record)->build('INSERT');
        print_r($result);

        // 插入一条记录
        $result = $t->insertOne(['code' => uniqid(), 'name' => '香蕉', 'price' => 5.2,]);
        $this->assertEquals(1, $result);

        // 插入一条记录，返回id
        $result = $t->insertOne(['code' => uniqid(), 'name' => '香蕉', 'price' => 5.3,], Query::INSERT_RETURN_ID);
        $this->assertEquals(4, $result);

        // 插入多条记录
        $result = $t->insertMany([
            ['code' => 'a1', 'name' => '柚子', 'price' => 5.1,],
            ['code' => 'a1', 'name' => '柚子', 'price' => 5.2,], // 测试code重复
            ['code' => 'a3', 'name' => '柚子', 'price' => 5.3,],
        ]);
        $this->assertEquals(2, $result);

        // insertMany
        $result = $t->insertMany([
            ['code' => uniqid(), 'name' => '葡萄', 'price' => 8.1,],
            ['code' => uniqid(), 'name' => '葡萄', 'price' => 8.2,],
            ['code' => uniqid(), 'name' => '葡萄', 'price' => 8.3,],
        ]);
        $this->assertEquals(3, $result);

        // insertMany 的成功清单
        $result = $t->insertMany([
            ['code' => uniqid(), 'name' => '枇杷', 'price' => 8.1,],
            ['code' => uniqid(), 'name' => '枇杷', 'price' => 8.2,],
            ['code' => uniqid(), 'name' => '枇杷', 'price' => 8.3,],
            ], Query::INSERT_MANY_RETURN_SUCC_LIST);
        print_r($result);

        // insertMany 的错误清单
        $result = $t->insertMany([
            ['code' => 'apple', 'name' => '菠萝', 'price' => 8.1,], // 应该执行失败
            ['code' => uniqid(), 'name' => '菠萝', 'price' => 8.2,],
            ['code' => uniqid(), 'name' => '菠萝', 'price' => 8.3,],
            ], Query::INSERT_MANY_RETURN_FAIL_LIST);
        print_r($result);

        // insertMany 的错误详细报告
        $result = $t->insertMany([
            ['code' => 'apple', 'name' => '菠萝', 'price' => 8.1,], // 应该执行失败
            ['code' => uniqid(), 'name' => '菠萝', 'price' => 8.2,],
            ['code' => uniqid(), 'name' => '菠萝', 'price' => 8.3,],
            ], Query::INSERT_MANY_RETURN_FAIL_REPORT);
        print_r($result);
    }


    public function test_insertOrUpdate()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $record = ['code' => uniqid(), 'name' => '香蕉', 'price' => 5.2,];

        $t = $this->db->table('test');

        // 插入多条记录
        $result = $t->insertMany([
            ['id' => 101, 'code' => 'a1', 'name' => '柚子', 'price' => 5.1,],
            ['id' => 102, 'code' => 'a2', 'name' => '柚子', 'price' => 5.2,],
            ['id' => 103, 'code' => 'a3', 'name' => '柚子', 'price' => 5.3,],
        ]);

        // insertOrUpdateOne
        $t = $this->db->table('test');
        $result = $t->insertOrUpdateOne(
            ['id' => 104, 'code' => 'a4', 'name' => '柚子', 'price' => 5.4,], 'id');
        $this->assertTrue($result);

        // insertOrUpdateOne 失败
        $t = $this->db->table('test');
        $result = $t->insertOrUpdateOne(
            ['id' => 105, 'code' => 'a4', 'name' => '柚子', 'price' => 5.4,], 'id'); //code冲突
        $this->assertFalse($result);

        // many
        $t = $this->db->table('test');
        $records = [
            ['id' => 201, 'code' => '201', 'name' => '西瓜', 'price' => 9.1,],
            ['id' => 202, 'code' => '202', 'name' => '西瓜', 'price' => 9.2,],
            ['id' => 203, 'code' => '203', 'name' => '西瓜', 'price' => 9.3,],
            ['id' => 101, 'code' => 'a1', 'name' => '柚子', 'price' => 10.1,],
        ];
        $result = $t->insertOrUpdateMany($records, 'id');
        print_r($result);

        // many
        $t = $this->db->table('test');
        $records = [
            ['id' => 201, 'code' => '201', 'name' => '桂圆', 'price' => 7.1,],
            ['id' => 202, 'code' => '202', 'name' => '桂圆', 'price' => 7.2,],
            ['id' => 203, 'code' => '203', 'name' => '桂圆', 'price' => 7.3,],
            ['id' => 204, 'code' => '204', 'name' => '桂圆', 'price' => 7.4,],
        ];
        $result = $t->insertOrUpdateMany($records, 'id');
        print_r($result);
        $this->assertEmpty($result['fail']);

        // many
        $t = $this->db->table('test');
        $records = [
            ['id' => 301, 'code' => '201', 'name' => '301', 'price' => 7.1,],
            ['id' => 302, 'code' => '202', 'name' => '301', 'price' => 7.2,],
            ['id' => 303, 'code' => '203', 'name' => '301', 'price' => 7.3,],
            ['id' => 304, 'code' => '304', 'name' => '301', 'price' => 7.4,],
        ];
        $result = $t->insertOrUpdateMany($records, 'id');
        print_r($result);
        $this->assertEquals([3 => null], $result['succ']);
    }


    public function test_count()
    {
        $this->resetMock(__DIR__ . '/zp_test.sql');

        $t = $this->db->table('test');

        $t->insertOne(
            ['id' => 401, 'code' => '401', 'name' => '401', 'price' => null,]
            );

        $result = $t->select()->getRows();
        var_dump($result);

        $result = $t->count()->getValue();
        var_dump($result);
    }
}
