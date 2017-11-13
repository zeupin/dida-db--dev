<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License
 * Redistributions of files MUST retain the above copyright notice.
 */

namespace Dida\Db;

/**
 * Util 工具类
 */
class Util
{
    /**
     * 版本号
     */
    const VERSION = '20171113';

    /**
     * 将一个数组按照给出的 key1,key2,keyN 进行键值化，返回处理后的数组。
     *
     * 注意：
     * 1. 考虑到处理超大数组时的内存占用问题，原数组的数据被逐条处理后，会被删除。
     * 2. 需要自行保证给出的 key1,key2,keyN 的组合可以唯一确定一条记录。
     *    否则，对于同一 key1,key2,keyN，后值将覆盖前值。
     * 3. key1,key2,keyN 一般使用数据表的唯一主键、复合主键、或者有唯一值的字段名。
     *
     * @param array $array
     * @param string|int $keyN   要分组的键名或键序号
     *
     * @return array|false   成功返回数组，有错返回false
     */
    public static function arrayAssocBy(array &$array, $keyN)
    {
        // 如果是 []/null/false，原样返回
        if (!$array) {
            return $array;
        }

        // 准备参数
        $args = func_get_args();
        array_shift($args);
        if (is_array($keyN)) {  // 如果是传入的是 keys 数组
            $args = $keyN;
        }

        // 结果数组
        $return = [];

        while ($row = array_shift($array)) {
            $cur = &$return;

            foreach ($args as $arg) {
                // 如果数组中没有找到key，返回失败
                if (!array_key_exists($arg, $row)) {
                    return false;
                }

                // 获取键值
                $key = $row[$arg];

                // 指向到对应节点
                if (!array_key_exists($key, $cur)) {
                    $cur[$key] = [];
                }
                $cur = &$cur[$key];
            }

            // 把当前行存入到当前位置。
            // 注意，是直接进行存储，如果已经有旧值，将覆盖掉旧值。
            $cur = $row;
        }

        // 返回结果
        return $return;
    }


    /**
     * 将一个数组按照给出的key进行Group处理，返回Group后的数组。
     *
     * 注意：
     * 1. 考虑到处理超大数组时的内存占用问题，原数组的数据被逐条处理后，会被删除。
     *
     * @param array $array
     * @param string|int $keyN   要分组的键名或键序号
     *
     * @return array|false   成功返回数组，有错返回false
     */
    public static function arrayGroupBy(array &$array, $keyN)
    {
        // 如果是 []/null/false，原样返回
        if (!$array) {
            return $array;
        }

        // 准备参数
        $args = func_get_args();
        array_shift($args);
        if (is_array($keyN)) {  // 如果是传入的是 keys 数组
            $args = $keyN;
        }

        // 结果数组
        $return = [];

        while ($row = array_shift($array)) {
            $cur = &$return;

            foreach ($args as $arg) {
                // 如果数组中没有找到key，返回失败
                if (!array_key_exists($arg, $row)) {
                    return false;
                }

                // 获取键值
                $key = $row[$arg];

                // 指向到对应节点
                if (!array_key_exists($key, $cur)) {
                    $cur[$key] = [];
                }
                $cur = &$cur[$key];
            }

            // 把当前行存入到当前位置。
            // 注意，是以新增一个数组单元的形式来存储。
            $cur[] = $row;
        }

        // 返回结果
        return $return;
    }
}
