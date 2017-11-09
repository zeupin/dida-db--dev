<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db;

/**
 * Util 工具类
 */
class Util
{
    /**
     * 将一个数组按照给出的 key1,key2,keyN 进行分组，返回处理后的数组。
     *
     * 注意：
     * 1. 考虑到处理超大数组时的内存占用问题，原数组处理后，将会
     * 2. $keyN 一般使用数据表的唯一主键、复合主键、或者有唯一值的字段名。
     * 3. 需要自行保证 key1,key2,keyN 的组合能够唯一标识数组元素。否则，最终结果将
     *    丢弃掉前面的数据，只以最后一个 key1,key2,keyN 的数据为准。
     *
     * @param array $array
     * @param string|int $keyN   要分组的键名或键序号
     *
     * @return array|false   成功返回数组，有错返回false
     */
    public static function arrayBy(array &$array, $keyN)
    {
        // 如果是 []/null/false，原样返回
        if (!$array) {
            return $array;
        }

        // 准备参数
        $args = func_get_args();
        array_shift($args);

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
