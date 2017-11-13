<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\Db;

/**
 * ConditionTree
 */
class ConditionTree
{
    /**
     * 名字
     *
     * @var string
     */
    public $name = null;

    /**
     * 条目间的连接运算符。
     *
     * @var string
     */
    public $logic = 'AND';

    /**
     * 条目列表。其中：
     * 1.每个条目要么是一个简单的条件节点（数组类型），要么是一个条件子树（ConditionTree类型）。
     * 2.简单的条件节点的格式为：[字段表达式, 运算符, 数据]。
     *
     * @var array
     */
    public $items = [];


    /**
     * 类的构造函数
     *
     * @param string $logic 条目间的连接运算符
     * @param string $name 如果设置了，表示把节点登记为命名节点。
     */
    public function __construct($logic = 'AND', $name = null)
    {
        $this->logic = $logic;
        $this->name = $name;
    }


    /**
     * 生成命名节点的速查字典。
     *
     * @param array $dict
     */
    public function getNamedDictionary(array &$dict)
    {
        // 如果当前节点是命名节点，则登记到速查字典中。
        if (is_string($this->name)) {
            $dict[$this->name] = &$this;
        }

        // 遍历子节点
        foreach ($this->items as $key => $item) {
            if ($item instanceof ConditionTree) {
                $this->items[$key]->getNamedDictionary($dict);
            }
        }
    }
}
