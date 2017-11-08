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
    /**
     * @var \Dida\Db\DataSet
     */
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
        $result = $this->dataset->fetch();
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_fetchAll()
    {
        $this->initData();
        $result = $this->dataset->fetchAll();
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_fetchColumn()
    {
        $this->initData();
        $result = $this->dataset->fetchColumn();
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_getRow()
    {
        $this->initData();
        $result = $this->dataset->getRow();
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_getRows()
    {
        $this->initData();
        $result = $this->dataset->getRows();
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_getColumn()
    {
        $this->initData();
        $result = $this->dataset->getColumn(0);
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);

        $this->initData();
        $result = $this->dataset->getColumn('id');
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_getColumnPosByName()
    {
        // 列名不存在，返回false
        $this->initData();
        $result = $this->dataset->getColumnNumber('not_exists');
        $this->assertFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_rowCount_columnCount()
    {
        $this->initData();
        $rowCount = $this->dataset->rowCount();
        $columnCount = $this->dataset->columnCount();
        $this->assertNotFalse($rowCount);
        $this->assertNotFalse($columnCount);
        echo Debug::varDump(__METHOD__, $rowCount, $columnCount);
    }


    public function test_errorCode_errorInfo()
    {
        $this->initData();
        $errorCode = $this->dataset->errorCode();
        $errorInfo = $this->dataset->errorInfo();
        $this->assertNotEmpty($errorCode);
        $this->assertNotEmpty($errorInfo);
        echo Debug::varDump(__METHOD__, $errorCode, $errorInfo);
    }


    public function test_debugDumpParams()
    {
        $this->initData();
        $result = $this->dataset->debugDumpParams();
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_setFetchMode()
    {
        $this->initData();
        $result = $this->dataset->setFetchMode(PDO::FETCH_BOTH);
        $this->assertTrue($result);

        // 看看结果
        $result = $this->dataset->fetchAll();
        $this->assertNotFalse($result);
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_getGroupRows()
    {
        $this->initData();
        $result = $this->dataset->getGroupRows('id');

        // 看看结果
        echo Debug::varDump(__METHOD__, $result);
    }


    public function test_getGroupRows2()
    {
        $sqlColumns = <<<'EOT'
SELECT
    `TABLE_NAME`,
    `COLUMN_NAME`,
    `ORDINAL_POSITION`,
    `COLUMN_DEFAULT`,
    `IS_NULLABLE`,
    `DATA_TYPE`,
    `CHARACTER_MAXIMUM_LENGTH`,
    `NUMERIC_PRECISION`,
    `NUMERIC_SCALE`,
    `DATETIME_PRECISION`,
    `CHARACTER_SET_NAME`,
    `COLLATION_NAME`,
    `COLUMN_TYPE`,
    `COLUMN_KEY`,
    `EXTRA`,
    `COLUMN_COMMENT`
FROM
    `information_schema`.`COLUMNS`
WHERE
    (`TABLE_SCHEMA` LIKE :schema) AND (`TABLE_NAME` LIKE :table)
ORDER BY
    `TABLE_NAME`, `ORDINAL_POSITION`
EOT;

        $stmt = $this->conn->getPDO()->prepare($sqlColumns);
        $stmt->execute([
            ':schema' => 'zeupin',
            ':table'  => 'zp_test',
        ]);
        $dataset = new DataSet($stmt);

        $result = $dataset->getGroupRows('TABLE_NAME', 'COLUMN_NAME');
        echo Debug::varDump(__METHOD__, $result);

        $stmt = $this->conn->getPDO()->prepare($sqlColumns);
        $stmt->execute([
            ':schema' => 'zeupin',
            ':table'  => 'zp_test',
        ]);
        $dataset = new DataSet($stmt);

        $result = $dataset->getGroupRowsByKeys('TABLE_NAME', 'COLUMN_NAME');
        echo Debug::varDump(__METHOD__, $result);
    }
}
