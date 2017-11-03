<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db;

use \Exception;

/**
 * SchemaMap
 */
abstract class SchemaMap
{
    /**
     * @var \Dida\Db\Db
     */
    protected $db = null;

    /**
     * 缓存目录
     *
     * @var string
     */
    protected $cacheDir = null;

    /**
     * 数据库名
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
     * 列出<schema>中的所有表名
     */
    abstract public function listTableNames($prefix = null, $schema = null);


    /**
     * 获取<schema.table>的表元信息。
     */
    abstract public function getTableInfo($table, $schema = null);


    /**
     * 获取指定的<schema.table>的所有列元信息。
     */
    abstract public function getAllColumnInfo($table, $schema = null);


    /**
     * 把驱动相关的数据类型转换为驱动无关的通用类型
     */
    abstract public function getBaseType($datatype);


    /**
     * 获取<schema.table>的主键的列名列表
     */
    abstract public function getPrimaryKeys($table, $schema = null);


    /**
     * 获取<schema.table>的所有UNIQUE约束的列名数组
     */
    abstract public function getUniqueColumns($table, $schema = null);


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

        // SchemaMap的缓存目录
        if (!isset($cfg['db.schemamap_dir'])) {
            throw new Exception('db.schemamap_dir 未配置');
        }
        if (!$this->setCacheDir($cfg['db.schemamap_dir'])) {
            throw new Exception('db.schemamap_dir 配置有误');
        }
    }


    /**
     * 设置缓存目录
     *
     * @param string $cacheDir
     *
     * @return boolean 成功返回true，失败返回false
     */
    public function setCacheDir($cacheDir)
    {
        // 检查参数是否合法
        if (!is_string($cacheDir)) {
            $this->cacheDir = null;
            return false;
        }

        // 如果目录不存在，先尝试创建目录
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
     * 缓存所有表的信息
     *
     * @param string $schema
     * @param string $prefix
     */
    public function saveAllTableInfo()
    {
        $schema = $this->schema;
        $prefix = $this->prefix;

        // 先检查缓存目录是否已经设置，是否可写入
        $dir = $this->cacheDir;
        if (!is_string($dir) || !file_exists($dir) || !is_dir($dir) || !is_writeable($dir)) {
            return false;
        }

        // 清空缓存目录
        $this->clearDir($this->cacheDir);

        // 列出所有满足条件的数据表
        $tables = $this->listTableNames();

        // 准备好缓存目录
        $this->prepareCacheDir();

        // 依次把每个数据表资料都做一下缓存
        foreach ($tables as $table) {
            // 准备要写入的数据
            $data = $this->prepareTableInfo($table);

            // 准备写入文件的内容
            $content = "<?php\nreturn " . var_export($data, true) . ";\n";

            // 文件路径
            $filename = $this->tableInfoCachePath($table);

            // 保存文件
            file_put_contents($filename, $content);
        }
    }


    /**
     * 准备要缓存的TableInfo数据
     *
     * @param string $table
     */
    public function prepareTableInfo($table)
    {
        // 准备保存所有列的数据
        $columns = [];
        $allColumnMetas = $this->getAllColumnInfo($table); // 获取所有列的信息
        foreach ($allColumnMetas as $column) {
            // 把列名作为数组的key
            $columns[$column['COLUMN_NAME']] = $column;
        }

        // 处理单一主键和复合主键
        $primarykeys = $this->getPrimaryKeys($table, $schema);
        if ($primarykeys) {
            $pri = null;
            $multipri = null;
        } elseif (count($primarykeys) == 1) {
            $pri = $primarykeys[0];
            $multipri = null;
        } elseif (count($primarykeys) > 1) {
            $pri = null;
            $multipri = $primarykeys;
        }

        // 要保存的数据
        $data = [
            'pri'        => $pri,
            'multipri'   => $multipri,
            'uni'        => $this->getUniqueColumns($table),
            'columnlist' => array_keys($columns),
            'columns'    => $columns,
        ];

        return $data;
    }


    /**
     * 从缓存中读取一个数据表的表元信息和列元信息
     *
     * @param string $table
     *
     * @return array|false 成功返回表信息数组，失败返回false
     */
    public function readTableInfoFromCache($table)
    {
        $schema = $this->schema;

        // 先检查缓存目录是否已经设置
        $dir = $this->cacheDir;
        if (!is_string($dir) || !file_exists($dir) || !is_dir($dir) || !is_writeable($dir)) {
            return false;
        }

        // 从缓存中读取表信息
        $file = $this->tableInfoCachePath($table);
        if (file_exists($file)) {
            return include($file);
        }

        // 如果缓存不存在目标文件，则返回false
        return false;
    }


    /**
     *
     * @param string $table
     */
    protected function tableInfoCachePath($table)
    {
        $DS = DIRECTORY_SEPARATOR;
        $schema = $this->schema;
        $file = $this->cacheDir . "{$DS}{$schema}{$DS}{$table}.php";
        return $file;
    }


    /**
     * 准备好缓存目录
     */
    protected function prepareCacheDir()
    {
        $DS = DIRECTORY_SEPARATOR;
        $schema = $this->schema;
        $dir = $this->cacheDir . "{$DS}{$schema}";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }


    /**
     * 清空指定目录，包括其下所有文件和所有子目录。
     *
     * @return boolean 成功返回true，失败返回false
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
