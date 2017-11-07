<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
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
     *     'basetype' => 基本数据类型,见 COLUMN_TYPE_*** 常量
     *     'len'  => 长度,
     *     'precision' => 精度,
     *     'nullable' => 可否为空,
     * ]
     */
    protected $info = null;


    /**
     * 类的构造函数。
     *
     * @param \Dida\Db\Db $db
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
     * @return array
     */
    abstract public function getTableList();


    /**
     * 获取<schema.table>的表元信息。
     *
     * @return array
     */
    abstract public function getTableInfo($table);


    /**
     * 获取指定的<schema.table>的所有列元信息。
     *
     * @return array
     */
    abstract public function getColumnInfoList($table);


    /**
     * 获取<schema.table>的列名列表数组
     *
     * @return array
     */
    abstract public function getColumnList($table);


    /**
     * 获取指定列的相关信息
     *
     * @return array
     */
    abstract public function getColumnInfo($column, $table);


    /**
     * 获取<schema.table>的唯一主键的键名。
     *
     * @return string|null  如果没有唯一主键，或者不是唯一主键，则返回 null。
     */
    abstract public function getPrimaryKey($table);


    /**
     * 获取<schema.table>的复合主键的列名列表。
     *
     * @return array|null  如果没有复合主键，或者不是复合主键，则返回 null。
     */
    abstract public function getPrimaryKeys($table);


    /**
     * 获取<schema.table>的所有UNIQUE约束的列名数组
     *
     * @return array
     */
    abstract public function getUniqueColumns($table);


    /**
     * 获取基本类型，把驱动相关的数据类型转换为驱动无关的通用类型。
     */
    abstract public function getBaseType($datatype);
}
