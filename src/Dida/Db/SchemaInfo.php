<?php
/**
 * Dida Framework  <http://dida.zeupin.com>
 *
 * Copyright 2017 Zeupin LLC.
 */

namespace Dida\Db;

use \Exception;

/**
 * SchemaInfo
 */
abstract class SchemaInfo
{
    /*
     * 列的基本数据类型
     */
    const COLUMN_TYPE_UNKNOWN = 'unknown';
    const COLUMN_TYPE_INT = 'int';
    const COLUMN_TYPE_FLOAT = 'float';
    const COLUMN_TYPE_STRING = 'string';
    const COLUMN_TYPE_BOOL = 'bool';
    const COLUMN_TYPE_TIME = 'time';
    const COLUMN_TYPE_ENUM = 'enum';
    const COLUMN_TYPE_SET = 'set';
    const COLUMN_TYPE_RESOURCE = 'res';
    const COLUMN_TYPE_STREAM = 'stream';

    /**
     * @var \Dida\Db\Db
     */
    protected $db = null;

    /**
     * 默认的数据库 schema
     *
     * @var string
     */
    protected $schema = null;

    /**
     * 数据表前缀
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * 保存的用于快捷查询信息。
     *
     * @var array
     * [
     *     表名 => [
     *                 'pri'        => 唯一主键的键名,
     *                 'pris'       => [复合主键的列名数组],
     *                 'unis'       => [unique约束的列名数组],
     *                 'columnlist' => [列名数组]
     *                 'columns'    => [列元数组],
     *             ]
     * ]
     * 其中，列元数组为：
     * [
     *     'datatype' => 'string',  // 基本数据类型，见 Schema::COLUMN_TYPE_*** 常量
     *     'nullable' => false,     // 可否为null
     *     'precision' => NULL,     // 数字精度
     *     'scale' => NULL,         // 数字小数
     *     'len' => '20',           // 字符串最大长度
     *     'charset' => 'utf8',     // 字符集
     * ]
     */
    public $info = [];


    /**
     * 类的构造函数。
     *
     * @param \Dida\Db\Db  $db
     */
    public function __construct(&$db)
    {
        $this->db = $db;

        $cfg = $this->db->getConfig();

        // 数据库名
        if (!isset($cfg['db.name'])) {
            throw new Exception('db.name 未配置');
        }
        $this->schema = $cfg['db.name'];

        // 默认的数据表前缀
        if (!isset($cfg['db.prefix']) || !is_string($cfg['db.prefix'])) {
            $this->prefix = '';
        } else {
            $this->prefix = $cfg['db.prefix'];
        }
    }


    /**
     * 列出<schema>中的所有表名
     *
     * @return array|false  有错返回false，成功返回array
     */
    abstract public function getTableList();


    /**
     * 获取<schema.table>的所有信息。
     *
     * @return array|false  有错返回false，成功返回array
     */
    abstract public function getTable($table);


    /**
     * 获取<schema.table>的表元信息。
     *
     * @return array|false  有错返回false，成功返回array
     */
    abstract public function getTableInfo($table);


    /**
     * 获取指定的<schema.table>的所有列元信息。
     *
     * @return array|false  有错返回false，成功返回array
     */
    abstract public function getColumnInfoList($table);


    /**
     * 获取<schema.table>的列名列表数组
     *
     * @return array|false  有错返回false，成功返回array
     */
    abstract public function getColumnList($table);


    /**
     * 获取指定列的相关信息
     *
     * @return array|false  有错返回false，成功返回array
     */
    abstract public function getColumnInfo($table, $column);


    /**
     * 获取<schema.table>的唯一主键的键名。
     *
     * @return string|null|false  有错返回false，没有返回null，成功返回string
     */
    abstract public function getPrimaryKey($table);


    /**
     * 获取<schema.table>的复合主键的列名列表。
     *
     * @return array|null|false  有错返回false，没有返回null，成功返回array
     */
    abstract public function getPrimaryKeys($table);


    /**
     * 获取<schema.table>的所有UNIQUE约束的列名数组
     *
     * @return array|null|false  有错返回false，没有返回null，成功返回array
     */
    abstract public function getUniqueColumns($table);


    /**
     * 获取基本类型，把驱动相关的数据类型转换为驱动无关的通用类型。
     */
    abstract public function getBaseType($datatype);
}
