<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db;

/**
 * ConditionTree
 */
class ConditionTree
{
    /**
     * 条目间的连接运算符。
     *
     * @var string
     */
    public $logic = 'AND';

    /**
     * 条目列表。
     *
     * 其中每个条目要么是一个简单的条件节点（数组类型），要么是一个条件子树（ConditionTree类型）。
     *
     * @var
     */
    public $items = [];


    /**
     * 类的构造函数
     *
     * @param string $logic 条目间的连接运算符
     * @param string $name 如果设置了，表示把节点登记为命名节点。
     */
    public function __construct($logic = 'AND')
    {
        $this->logic = $logic;
    }
}
