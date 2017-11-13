<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License
 * Redistributions of files MUST retain the above copyright notice.
 */

namespace Dida\Db\Mysql;

use \Dida\Db\SchemaInfo;

/**
 * MysqlSchemaInfo
 */
class MysqlSchemaInfo extends \Dida\Db\SchemaInfo\File
{
    /**
     * 版本号
     */
    const VERSION = '20171113';

    use MysqlSchemaInfoTrait;


    /**
     * 把驱动相关的数据类型转换为驱动无关的通用类型
     */
    public function getBaseType($datatype)
    {
        switch ($datatype) {
            /* string type */
            case 'varchar':
            case 'char':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return SchemaInfo::COLUMN_TYPE_STRING;

            /* numeric type */
            case 'int':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
            case 'timestamp':
                return SchemaInfo::COLUMN_TYPE_INT;

            case 'float':
            case 'double':
            case 'decimal':
                return SchemaInfo::COLUMN_TYPE_FLOAT;

            /* time type */
            case 'datetime':
            case 'date':
                return SchemaInfo::COLUMN_TYPE_TIME;

            /* enum */
            case 'enum':
                return SchemaInfo::COLUMN_TYPE_ENUM;

            /* set */
            case 'set':
                return SchemaInfo::COLUMN_TYPE_SET;

            /* binary */
            case 'varbinary':
                return SchemaInfo::COLUMN_TYPE_STREAM;

            /* unknown type */
            default:
                return SchemaInfo::COLUMN_TYPE_UNKNOWN;
        }
    }


    /**
     * 生成指定表的缓存文件
     *
     * @param string $table
     */
    public function cacheTable($table)
    {
        // 表元数据
        $tables = $this->queryTableInfo($table);
        $this->processTables($tables);

        // 列元数据
        $columns = $this->queryColumnInfo($table);
        $this->processColumns($columns);
    }


    /**
     * 重新生成所有表的缓存文件
     */
    public function cacheAllTables()
    {
        // 清空缓存目录
        $this->clearDir($this->cacheDir);

        // 表元数据
        $tables = $this->queryAllTableInfo();
        $this->processTables($tables);

        // 列元数据
        $columns = $this->queryAllColumnInfo();
        $this->processColumns($columns);
    }


    /**
     * 保存 “表名.metas.php”
     *
     * @param array $tables
     */
    protected function processTables(array $tables)
    {
        foreach ($tables as $table) {
            // 保存表元文件
            $path = $this->cacheDir . DIRECTORY_SEPARATOR . $table['TABLE_NAME'] . '.table.php';
            $content = "<?php\nreturn " . var_export($table, true) . ";\n";
            file_put_contents($path, $content);
        }
    }


    protected function processColumns(array $columns)
    {
        $info = [];

        foreach ($columns as $column) {
            $brief = $column;
            unset($brief['TABLE_NAME'], $brief['COLUMN_NAME']);
            $info[$column['TABLE_NAME']][$column['COLUMN_NAME']] = $brief;
        }

        // 保存原生的列元信息
        foreach ($info as $table => $data) {
            $path = $this->cacheDir . DIRECTORY_SEPARATOR . $table . '.columns.php';
            $content = "<?php\nreturn " . var_export($data, true) . ";\n";
            file_put_contents($path, $content);
        }

        // 保存portable的列元信息
        foreach ($info as $table => $data) {
            $path = $this->cacheDir . DIRECTORY_SEPARATOR . $table . '.php';

            $pri = null;
            $pris = [];  // primary keys
            $unis = [];  // unique keys
            $columnlist = [];  // 列名列表
            $columns = [];  // 列元列表

            foreach ($data as $col => $metas) {
                $columnlist[] = $col;

                if ($metas['COLUMN_KEY'] === 'PRI') {
                    $pris[] = $col;
                } elseif ($metas['COLUMN_KEY'] === 'UNI') {
                    $unis[] = $col;
                }

                $column = [
                    'datatype'  => $this->getBaseType($metas['DATA_TYPE']),
                    'nullable'  => ($metas['IS_NULLABLE'] === 'YES'),
                    'precision' => $metas['NUMERIC_PRECISION'],
                    'scale'     => $metas['NUMERIC_SCALE'],
                    'len'       => $metas['CHARACTER_MAXIMUM_LENGTH'],
                    'charset'   => $metas['CHARACTER_SET_NAME'],
                ];
                $columns[$col] = $column;
            }

            // 单一主键还是复合主键
            switch (count($pris)) {
                case 0:
                    $pri = null;
                    $pris = null;
                    break;
                case 1:
                    $pri = $pris[0];
                    $pris = null;
                    break;
                default:
                    $pri = null;
            }

            // 唯一键列表
            if (empty($unis)) {
                $unis = null;
            }

            // portable的列元数据
            $output = [
                'pri'        => $pri,
                'pris'       => $pris,
                'unis'       => $unis,
                'columnlist' => $columnlist,
                'columns'    => $columns,
            ];

            $content = "<?php\nreturn " . var_export($output, true) . ";\n";
            file_put_contents($path, $content);
        }
    }
}
