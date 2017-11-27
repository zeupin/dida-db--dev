<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\Db;

/**
 * ConnectionPool
 *
 * 连接池，提供读写分离特性。
 */
class ConnectionPool
{
    /**
     * 版本号
     */
    const VERSION = '20171122';

    /*
     * CONN_FOR_READ
     */
    const CONN_FOR_READ = 'CONN_FOR_READ';

    /**
     * CONN_FOR_WRITE
     */
    const CONN_FOR_WRITE = 'CONN_FOR_WRITE';

    /**
     * 读连接池。
     *
     * @var array
     */
    protected $read_conn_pool = [];

    /**
     * 写连接池。
     *
     * @var array
     */
    protected $write_conn_pool = [];


    /**
     * 新增一个读连接。
     *
     * @param Dida\Db\Connection $connection
     * @param string|int|null $key
     */
    public function addConnForRead($connection, $key = null)
    {
        if (is_null($key)) {
            $this->read_conn_pool[] = $connection;
        } else {
            $this->read_conn_pool[$key] = $connection;
        }
    }


    /**
     * 新增一个写连接。
     *
     * @param Dida\Db\Connection $connection
     * @param string|int|null $key
     */
    public function addConnForWrite($connection, $key = null)
    {
        if (is_null($key)) {
            $this->write_conn_pool[] = $connection;
        } else {
            $this->write_conn_pool[$key] = $connection;
        }
    }


    /**
     * 获取一个读连接。
     *
     * @return \Dida\Db\Connection
     */
    public function &getConnForRead()
    {
        // 如果读连接池为空，返回null
        if (empty($this->read_conn_pool)) {
            return null;
        }

        // 如果只有一个连接，直接返回
        if (count($this->read_conn_pool) === 1) {
            return end($this->read_conn_pool);
        }

        // 如果有多个连接，随机返回一个连接
        $key = array_rand($this->read_conn_pool);
        return $this->read_conn_pool[$key];
    }


    /**
     * 获取一个读连接。
     *
     * @return \Dida\Db\Connection
     */
    public function getConnForWrite()
    {
        // 如果读连接池为空，返回null
        if (empty($this->write_conn_pool)) {
            return null;
        }

        // 如果只有一个连接，直接返回
        if (count($this->write_conn_pool) === 1) {
            return end($this->write_conn_pool);
        }

        // 如果有多个连接，随机返回一个连接
        $key = array_rand($this->write_conn_pool);
        return $this->write_conn_pool[$key];
    }
}
