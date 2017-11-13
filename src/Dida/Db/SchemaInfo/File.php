<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License
 * Redistributions of files MUST retain the above copyright notice.
 */

namespace Dida\Db\SchemaInfo;

use \Exception;

/**
 * File
 *
 * 基于文件缓存的 SchemaInfo 实现方式
 */
abstract class File extends \Dida\Db\SchemaInfo
{
    /**
     * Version
     */
    const VERSION = '20171113';

    /**
     * 设置缓存目录
     *
     * @var string
     */
    protected $cacheDir = null;


    /**
     * 生成指定表的缓存文件
     *
     * @param string $table
     */
    abstract public function cacheTable($table);


    /**
     * 重新生成所有表的缓存文件
     */
    abstract public function cacheAllTables();


    /**
     * 构造函数
     *
     * @param \Dida\Db\Db  $db
     * @throws Exception
     */
    public function __construct(&$db)
    {
        parent::__construct($db);

        $cfg = $this->db->getConfig();

        // SchemaInfo的缓存目录
        if (!isset($cfg['db.schemainfo.cachedir'])) {
            throw new Exception('db.schemainfo.cachedir 未配置');
        }
        if (!$this->setCacheDir($cfg['db.schemainfo.cachedir'])) {
            throw new Exception('db.schemainfo.cachedir 配置有误');
        }
    }


    /**
     * 列出<schema>中的所有表名
     *
     * @return array|false  有错返回false，成功返回array
     */
    public function getTableList()
    {
        $path = $this->cacheDir . DIRECTORY_SEPARATOR . '.tablelist.php';
        if (!file_exists($path)) {
            return false;
        }

        $content = include($path);
        return $content;
    }


    public function &getTable($table)
    {
        // 不存在这个表，返回false
        if (!$this->tableExists($table)) {
            return false;
        }

        // 返回数据
        return $this->info[$table];
    }


    /**
     * 获取<schema.table>的表元信息。
     *
     * @return array|false  有错返回false，成功返回array
     */
    public function getTableInfo($table)
    {
        $path = $this->cacheDir . DIRECTORY_SEPARATOR . $table . '.table.php';
        if (!file_exists($path)) {
            return false;
        }

        $content = include($path);
        return $content;
    }


    /**
     * 获取指定的<schema.table>的所有列元信息。
     *
     * @return array|false  有错返回false，成功返回array
     */
    public function getColumnInfoList($table)
    {
        // 不存在这个表，返回false
        if (!$this->tableExists($table)) {
            return false;
        }

        // 否则返回列名列表或者null
        return $this->info[$table]['columns'];
    }


    /**
     * 获取<schema.table>的列名列表数组
     *
     * @return array|false  有错返回false，成功返回array
     */
    public function getColumnList($table)
    {
        // 不存在这个表，返回false
        if (!$this->tableExists($table)) {
            return false;
        }

        // 否则返回列名列表或者null
        return $this->info[$table]['columnlist'];
    }


    /**
     * 获取指定列的相关信息
     *
     * @return array|false  有错返回false，成功返回array
     */
    public function getColumnInfo($table, $column)
    {
        // 不存在这个表，返回false
        if (!$this->tableExists($table)) {
            return false;
        }

        // 否则返回列名列表或者null
        return $this->info[$table]['columns'][$column];
    }


    /**
     * 获取<schema.table>的唯一主键的键名。
     *
     * @return string|null|false  有错返回false，没有返回null，成功返回string
     */
    public function getPrimaryKey($table)
    {
        // 不存在这个表，返回false
        if (!$this->tableExists($table)) {
            return false;
        }

        // 否则返回列名列表或者null
        return $this->info[$table]['pri'];
    }


    /**
     * 获取<schema.table>的复合主键的列名列表。
     *
     * @return array|null|false  有错返回false，没有返回null，成功返回array
     */
    public function getPrimaryKeys($table)
    {
        // 不存在这个表，返回false
        if (!$this->tableExists($table)) {
            return false;
        }

        // 否则返回列名列表或者null
        return $this->info[$table]['pris'];
    }


    /**
     * 获取<schema.table>的所有UNIQUE约束的列名数组
     *
     * @return array|null|false  有错返回false，没有返回null，成功返回array
     */
    public function getUniqueColumns($table)
    {
        // 不存在这个表，返回false
        if (!$this->tableExists($table)) {
            return false;
        }

        // 否则返回列名列表或者null
        return $this->info[$table]['unis'];
    }


    /**
     * 是否有指定的表。
     *
     * @param string $table
     * @return boolean
     */
    public function tableExists($table)
    {
        if (array_key_exists($table, $this->info)) {
            return true;
        } else {
            return $this->loadTableFromCache($table);
        }
    }


    /**
     * 载入缓存的数据表信息。
     *
     * @param string $table
     */
    protected function loadTableFromCache($table)
    {
        // 文件路径
        $DS = DIRECTORY_SEPARATOR;
        $path = "{$this->cacheDir}{$DS}{$table}.php";

        // 文件是否存在
        if (!file_exists($path) || !is_file($path)) {
            return false;
        }

        // 载入数据
        $data = include($path);
        $this->info[$table] = $data;
        return true;
    }


    /**
     * 设置缓存目录
     *
     * @param string $cacheDir
     *
     * @return boolean 成功返回true，失败返回false
     */
    protected function setCacheDir($cacheDir)
    {
        // 检查参数是否合法
        if (!is_string($cacheDir)) {
            $this->cacheDir = null;
            return false;
        }

        // 如果目录不存在，尝试创建目录
        if (!file_exists($cacheDir)) {
            $result = mkdir($cacheDir, 0777, true);
            if ($result === true) {
                // 如果创建成功
                $this->cacheDir = realpath($cacheDir);
                return true;
            } else {
                // 如果目录创建失败
                $this->cacheDir = null;
                return false;
            }
        }

        // 如果不是目录，或者目录不可写，也返回失败
        if (!is_dir($cacheDir) || !is_writable($cacheDir)) {
            $this->cacheDir = null;
            return false;
        }

        // 如果一切正常，返回成功
        $this->cacheDir = realpath($cacheDir);
        return true;
    }


    /**
     * 清空指定目录，包括其下所有文件和所有子目录。
     *
     * @return boolean  成功返回true，失败返回false
     */
    protected function clearDir($dir)
    {
        // 如果非法，返回false
        if (!is_string($dir) || !file_exists($dir) || !is_dir($dir)) {
            return false;
        }

        $dir = realpath($dir);
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            try {
                if (is_dir($path)) {
                    $this->clearDir($path);
                } else {
                    unlink($path);
                }
            } catch (Exception $ex) {
                return false;
            }
        }

        return true;
    }
}
