<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files MUST retain the above copyright notice.
 */

namespace Dida\Db;

use \Exception;

/**
 * SQL查询
 */
class Query
{
    /**
     * 版本号
     */
    const VERSION = '20171127';

    /*
     * 单条插入后的返回类型
     */
    const INSERT_RETURN_COUNT = 1;  // 返回成功的条数
    const INSERT_RETURN_ID = 2;  // 返回 lastInsertId

    /*
     * 多条插入成功后的返回类型
     */
    const INSERT_MANY_RETURN_SUCC_COUNT = 1;    // 返回成功的条数
    const INSERT_MANY_RETURN_SUCC_LIST = 2;     // 返回成功的序号列表，[seq => last_insert_id]

    /*
     * 多条插入失败后的返回类型
     */
    const INSERT_MANY_RETURN_FAIL_COUNT = -1;    // 返回成功的条数
    const INSERT_MANY_RETURN_FAIL_LIST = -2;     // 返回失败的序号列表，[seq => errorCode]
    const INSERT_MANY_RETURN_FAIL_REPORT = -3;   // 返回失败的报告 [id => [errorCode, errorInfo]]

    /**
     * @var \Dida\Db\Db
     */
    protected $db = null;

    /**
     * Builder 实例。
     *
     * @var \Dida\Db\Builder
     */
    protected $builder = null;

    /**
     * SchemaInfo 实例。
     *
     * @var \Dida\Db\SchemaInfo
     */
    protected $schemainfo = null;

    /**
     * 任务清单
     *
     * @var array
     */
    protected $tasklist = [];

    /**
     * @var array
     */
    protected $taskbase = [
        'verb'        => 'SELECT',
        'prefix'      => '',
        'swap_prefix' => '###_',
    ];

    /**
     * 指向当前的 whereTree 节点。
     * 初始化时，默认是根节点。
     *
     * @var \Dida\Db\ConditionTree
     */
    protected $whereActive = null;

    /**
     * 所有命名节点的列表。
     *
     * @var array
     */
    protected $whereDict = [];

    /**
     * 指向当前的 havingTree 节点。
     * 初始化时，默认是根节点。
     *
     * @var \Dida\Db\ConditionTree
     */
    protected $havingActive = null;

    /**
     * 所有命名节点的列表。
     *
     * @var array
     */
    protected $havingDict = [];


    /**
     * Class construct.
     *
     * @param \Dida\Db\Db $db
     */
    public function __construct(&$db)
    {
        $this->db = $db;

        // 初始化 taskbase
        $cfg = $this->db->getConfig();
        $this->taskbase = array_merge($this->taskbase, [
            'driver'      => $cfg['db.driver'],
            'prefix'      => $cfg['db.prefix'],
            'swap_prefix' => $cfg['db.swap_prefix'],
        ]);

        // 初始化
        $this->init();
    }


    private function _________________________INIT()
    {
    }


    /**
     * 重置任务列表为空
     *
     * @return $this
     */
    public function init()
    {
        $this->tasklist = $this->taskbase;

        return $this;
    }


    /**
     * 只保留 taskbase 里面的 table 条目,其它条目全部删除
     */
    public function clear()
    {
        $table = $this->tasklist['table'];
        $this->table($table['name'], $table['prefix']);

        return $this;
    }


    private function _________________________BUILD()
    {
    }


    /**
     * build查询所需的SQL语句
     *
     * @return
     *      @@array
     *      [
     *          'statement'  => ...,
     *          'parameters' => ...,
     *      ]
     */
    public function build($verb = null)
    {
        // 获取 Builder 对象
        $builder = $this->db->getBuilder();
        if ($builder === null) {
            throw new \Dida\Db\Exceptions\InvalidBuilderException;
        }

        // 如果指定了verb，把verb转为大写
        if (is_string($verb)) {
            $verb = trim($verb);
            $verb = strtoupper($verb);
            $this->tasklist['verb'] = $verb;
        }

        // build
        return $builder->build($this->tasklist);
    }


    private function _________________________TABLE()
    {
    }


    /**
     * 设置要操作的数据表
     * 表名和别名用as或AS分隔，如：“products AS p”
     * 也可设置多个表，各个表之间以逗号分隔，如：“products AS p, orders AS o, users AS u”
     *
     * @param string $name_as_alias
     * @param string $prefix 如果不设置，则认为是$cfg["prefix"]的值。
     *
     * @return $this
     */
    public function table($name_as_alias, $prefix = null)
    {
        $this->init();

        $this->tasklist['table'] = [
            'name'   => $name_as_alias,
            'prefix' => $prefix,
        ];

        return $this;
    }


    private function _________________________COLUMNLIST()
    {
    }


    /**
     * 设置 SELECT 的 columnlist
     *
     * @param $columnlist
     *      @@array 数组形式的列表。
     *          复杂的表达式推荐用这种形式，这种兼容性比较好。
     *          如：["列表达式一 AS A", "列表达式二 AS B"]
     *      @@string 字符串形式的列表。
     *          多个列表达式之间用逗号分隔
     *          注意：如果列表达式中包含有逗号，如 CONCAT(A,B,C) AS fullname，则这种情况一定要用数组形式，
     *          此时，字符串形式无法区分逗号是函数参数分隔符还是字段间的分隔符。
     *
     * @return $this
     */
    public function columnlist($columnlist = null)
    {
        $this->initArrayItem('columnlist');

        if (is_string($columnlist)) {
            $this->tasklist['columnlist'][] = ['raw', $columnlist];
        } elseif (is_array($columnlist)) {
            $this->tasklist['columnlist'][] = ['array', $columnlist];
        }

        return $this;
    }


    /**
     * DISTINCT.
     *
     * @param boolean $distinct
     *
     * @return $this
     */
    public function distinct()
    {
        $this->initArrayItem('columnlist');

        $this->tasklist['columnlist'][] = ['distinct'];

        return $this;
    }


    /**
     * SELECT COUNT(...)
     *
     * @param array $columns
     * @param string $alias
     *
     * @return $this
     */
    public function count(array $columns = null, $alias = null)
    {
        $this->initArrayItem('columnlist');

        $this->tasklist['columnlist'][] = ['count', $columns, $alias];

        return $this;
    }


    private function _________________________WHERE()
    {
    }


    /**
     * 初始化 whereTree
     */
    protected function initWhere()
    {
        if (isset($this->tasklist['where'])) {
            return;
        }

        $this->tasklist['where'] = new ConditionTree('AND');
        $this->whereDict = [];
        $this->whereDict[''] = &$this->tasklist['where'];
        $this->whereActive = &$this->tasklist['where'];
    }


    /**
     * 在 whereTree 的当前节点添加一个 where 条件
     *
     * 【标准模式】
     * @declare where(string $col_expr, string $op)
     * @declare where(string $col_expr, string $op, mixed $data)
     * @declare where(string $col_expr, string $op, mixed $data1, mixed $data2)
     *
     * 【RAW模式】 直接给出表达式
     * @declare where(string $condition)
     * @declare where(string $condition, array $parameters)
     *
     * 【匹配模式】 关联数组，参见 whereMatch()
     * @declare where(array $match)
     * @declare where(array $match, string $logic)
     * @declare where(array $match, string $logic, string $name)
     *
     * 【专业模式】 索引数组：[列表达式，操作符，数据，数据]
     * @declare where(array $condition)
     *
     * @return $this
     */
    public function where()
    {
        // 初始化 [where]
        $this->initWhere();

        // 生成参数变量
        $cnt = func_num_args();
        switch ($cnt) {
            case 4:
                $arg4 = func_get_arg(3);
            case 3:
                $arg3 = func_get_arg(2);
            case 2:
                $arg2 = func_get_arg(1);
                $arg2_is_array = is_array($arg2);
                $arg2_is_string = is_string($arg2);
            case 1:
                $arg1 = func_get_arg(0);
                $arg1_is_array = is_array($arg1);
                $arg1_is_string = is_string($arg1);
                if ($arg1_is_array) {
                    $arg1_array_type = $this->getArrayType($arg1);
                }
        }

        /*
         * 【标准模式】
         * @declare where(string $col_expr, string $op)
         * @declare where(string $col_expr, string $op, mixed $data)
         * @declare where(string $col_expr, string $op, mixed $data1, mixed $data2)
         */
        if ($cnt > 1 && $arg1_is_string && $arg2_is_string) {
            switch ($cnt) {
                case 2:
                    $this->whereActive->items[] = [$arg1, $arg2];
                    return $this;
                case 3:
                    $this->whereActive->items[] = [$arg1, $arg2, $arg3];
                    return $this;
                case 4:
                    $this->whereActive->items[] = [$arg1, $arg2, $arg3, $arg4];
                    return $this;
            }
        }

        /*
         * 【RAW模式】 直接给出表达式
         * @declare where(string $condition)
         * @declare where(string $condition, array $parameters)
         */
        if ($arg1_is_string) {
            if ($cnt == 1) {
                $this->whereActive->items[] = [$arg1, 'RAW', []];
                return $this;
            } elseif ($cnt == 2 && is_array($arg2)) {
                $this->whereActive->items[] = [$arg1, 'RAW', $arg2];
                return $this;
            }
        }

        /*
         * 【匹配模式】 关联数组，参见 whereMatch()
         * @declare where(array $match)
         * @declare where(array $match, string $logic)
         * @declare where(array $match, string $logic, string $name)
         */
        if ($arg1_is_array && ($arg1_array_type == 2)) {
            if ($cnt == 1) {
                return $this->whereMatch($arg1);
            } elseif ($cnt == 2 && $arg2_is_string) {
                return $this->whereMatch($arg1, $arg2);
            } elseif ($cnt == 3 && $arg2_is_string && is_string($arg3)) {
                return $this->whereMatch($arg1, $arg2, $arg3);
            }
        }

        /*
         * 【专业模式】 索引数组：[列表达式，操作符，数据，数据]
         * @declare where(array $condition)
         */
        if ($arg1_is_array && ($arg1_array_type == -1)) {
            $this->whereActive->items[] = $arg1;
            return $this;
        }

        /**
         * 如果上面模式都不匹配，则抛异常
         */
        throw new Exception('非法的where条件');
    }


    /**
     * 在当前 whereTree 节点处新增一个子树节点。
     *
     * @param array $conditions
     * @param string $logic
     * @param string $name
     *
     * @return $this
     */
    public function whereGroup(array $conditions = [], $logic = 'AND', $name = null)
    {
        // 初始化 [where]
        $this->initWhere();

        // 检查命名有无重复
        if (is_string($name)) {
            if (array_key_exists($name, $this->whereDict)) {
                throw new Exception("重复定义 where 命名组");
            }
        }

        // 生成新对象
        $group = new ConditionTree($logic);
        $group->name = $name;
        $group->items = $conditions;

        // 把新对象插入到当前位置的子节点上
        $this->whereActive->items[] = &$group;
        $this->whereActive = &$group;

        // 加入到速查字典
        if (is_string($name)) {
            $this->whereDict[$name] = &$group;
        }

        // 返回
        return $this;
    }


    /**
     * 设置当前 whereTree 节点的 logic 属性。
     *
     * @param string $logic
     *
     * @return $this
     */
    public function whereLogic($logic)
    {
        // 初始化 [where]
        $this->initWhere();

        // 设置当前节点的连接逻辑
        $this->whereActive->logic = $logic;

        return $this;
    }


    /**
     * 匹配一个给出的数组。
     *
     * @param array $array
     * @param string $logic
     *
     * @return $this
     */
    public function whereMatch(array $array, $logic = 'AND', $name = null)
    {
        // 初始化 [where]
        $this->initWhere();

        $conditions = [];
        foreach ($array as $key => $value) {
            $conditions[] = [$key, '=', $value];
        }

        $this->whereGroup($conditions, $logic, $name);

        return $this;
    }


    /**
     * 从 whereDict 命名列表中找到目标节点，然后将当前节点设置为目标节点。
     *
     * @param string $name
     *
     * @return $this
     */
    public function whereGoto($name)
    {
        if (!array_key_exists($name, $this->whereDict)) {
            throw new Exception("指定的节点不存在 $name");
        }

        $this->whereActive = &$this->whereDict[$name];

        return $this;
    }


    private function _________________________HAVING()
    {
    }


    /**
     * 初始化 havingTree
     */
    protected function initHaving()
    {
        if (isset($this->tasklist['having'])) {
            return;
        }

        $this->tasklist['having'] = new ConditionTree('AND');
        $this->havingDict = [];
        $this->havingDict[''] = &$this->tasklist['having'];
        $this->havingActive = &$this->tasklist['having'];
    }


    /**
     * 在 havingTree 的当前节点添加一个 having 条件
     *
     * 【标准模式】
     * @declare having(string $col_expr, string $op)
     * @declare having(string $col_expr, string $op, mixed $data)
     * @declare having(string $col_expr, string $op, mixed $data1, mixed $data2)
     *
     * 【RAW模式】 直接给出表达式
     * @declare having(string $condition)
     * @declare having(string $condition, array $parameters)
     *
     * 【匹配模式】 关联数组，参见 havingMatch()
     * @declare having(array $match)
     * @declare having(array $match, string $logic)
     * @declare having(array $match, string $logic, string $name)
     *
     * 【专业模式】 索引数组：[列表达式，操作符，数据，数据]
     * @declare having(array $condition)
     *
     * @return $this
     */
    public function having()
    {
        // 初始化 [having]
        $this->initHaving();

        // 先先生成变量清单
        $cnt = func_num_args();
        switch ($cnt) {
            case 4:
                $arg4 = func_get_arg(3);
            case 3:
                $arg3 = func_get_arg(2);
            case 2:
                $arg2 = func_get_arg(1);
                $arg2_is_array = is_array($arg2);
                $arg2_is_string = is_string($arg2);
            case 1:
                $arg1 = func_get_arg(0);
                $arg1_is_array = is_array($arg1);
                $arg1_is_string = is_string($arg1);
                if ($arg1_is_array) {
                    $arg1_array_type = $this->getArrayType($arg1);
                }
        }

        /*
         * 【标准模式】
         * @declare having(string $col_expr, string $op)
         * @declare having(string $col_expr, string $op, mixed $data)
         * @declare having(string $col_expr, string $op, mixed $data1, mixed $data2)
         */
        if ($cnt > 2 && $arg1_is_string && $arg2_is_string) {
            switch ($cnt) {
                case 2:
                    $this->havingActive->items[] = [$arg1, $arg2];
                    return $this;
                case 3:
                    $this->havingActive->items[] = [$arg1, $arg2, $arg3];
                    return $this;
                case 4:
                    $this->havingActive->items[] = [$arg1, $arg2, $arg3, $arg4];
                    return $this;
            }
        }

        /*
         * 【RAW模式】 直接给出表达式
         * @declare having(string $condition)
         * @declare having(string $condition, array $parameters)
         */
        if ($arg1_is_string) {
            if ($cnt == 1) {
                $this->havingActive->items[] = [$arg1, 'RAW', []];
                return $this;
            } elseif ($cnt == 2 && is_array($arg2)) {
                $this->havingActive->items[] = [$arg1, 'RAW', $arg2];
                return $this;
            }
        }

        /*
         * 【匹配模式】 关联数组，参见 havingMatch()
         * @declare having(array $match)
         * @declare having(array $match, string $logic)
         * @declare having(array $match, string $logic, string $name)
         */
        if ($arg1_is_array && ($arg1_array_type == 2)) {
            if ($cnt == 1) {
                return $this->havingMatch($arg1);
            } elseif ($cnt == 2 && $arg2_is_string) {
                return $this->havingMatch($arg1, $arg2);
            } elseif ($cnt == 3 && $arg2_is_string && is_string($arg3)) {
                return $this->havingMatch($arg1, $arg2, $arg3);
            }
        }

        /*
         * 【专业模式】 索引数组：[列表达式，操作符，数据，数据]
         * @declare having(array $condition)
         */
        if ($arg1_is_array && ($arg1_array_type == -1)) {
            $this->havingActive->items[] = $arg1;
            return $this;
        }

        /**
         * 如果上面模式都不匹配，则抛异常
         */
        throw new Exception('非法的having条件');
    }


    /**
     * 在当前 havingTree 节点处新增一个子树节点。
     *
     * @param array $conditions
     * @param string $logic
     * @param string $name
     *
     * @return $this
     */
    public function havingGroup(array $conditions = [], $logic = 'AND', $name = null)
    {
        // 初始化 [having]
        $this->initHaving();

        // 检查命名有无重复
        if (is_string($name)) {
            if (array_key_exists($name, $this->havingDict)) {
                throw new Exception("重复定义HAVING命名组");
            }
        }

        // 生成新对象
        $group = new ConditionTree($logic);
        $group->name = $name;
        $group->items = $conditions;

        // 把新对象插入到当前位置的子节点上
        $this->havingActive->items[] = &$group;
        $this->havingActive = &$group;

        // 加入到速查字典
        if (is_string($name)) {
            $this->havingDict[$name] = &$group;
        }

        // 返回
        return $this;
    }


    /**
     * 设置当前 havingTree 节点的 logic 属性。
     *
     * @param string $logic
     *
     * @return $this
     */
    public function havingLogic($logic)
    {
        // 初始化 [having]
        $this->initHaving();

        // 设置当前节点的连接逻辑
        $this->havingActive->logic = $logic;

        return $this;
    }


    /**
     * 匹配一个给出的数组。
     *
     * @param array $array
     * @param string $logic
     *
     * @return $this
     */
    public function havingMatch(array $array, $logic = 'AND', $name = null)
    {
        // 初始化 [having]
        $this->initHaving();

        $conditions = [];
        foreach ($array as $key => $value) {
            $conditions[] = [$key, '=', $value];
        }

        $this->havingGroup($conditions, $logic, $name);

        return $this;
    }


    /**
     * 从 havingDict 命名列表中找到目标节点，然后将当前节点设置为目标节点。
     *
     * @param string $name
     *
     * @return $this
     */
    public function havingGoto($name)
    {
        if (!array_key_exists($name, $this->havingDict)) {
            throw new Exception("指定的节点不存在 $name");
        }

        $this->havingActive = &$this->havingDict[$name];

        return $this;
    }


    private function _________________________JOINS()
    {
    }


    /**
     * JOIN clause
     *
     * @param string $tableB
     * @param string $on
     * @param array $parameters
     *
     * @return $this
     */
    public function join($tableB, $on, array $parameters = [])
    {
        $this->initArrayItem('join');

        $this->tasklist['join'][] = ['JOIN', $tableB, $on, $parameters];

        return $this;
    }


    /**
     * INNER JOIN clause
     *
     * @param string $tableB
     * @param string $on
     * @param array $parameters
     *
     * @return $this
     */
    public function innerJoin($tableB, $on, array $parameters = [])
    {
        $this->initArrayItem('join');

        $this->tasklist['join'][] = ['INNER JOIN', $tableB, $on, $parameters];

        return $this;
    }


    /**
     * LEFT JOIN clause
     *
     * @param string $tableB
     * @param string $on
     * @param array $parameters
     *
     * @return $this
     */
    public function leftJoin($tableB, $on, array $parameters = [])
    {
        $this->initArrayItem('join');

        $this->tasklist['join'][] = ['LEFT JOIN', $tableB, $on, $parameters];

        return $this;
    }


    /**
     * RIGHT JOIN clause
     *
     * @param string $tableB
     * @param string $on
     * @param array $parameters
     *
     * @return $this
     */
    public function rightJoin($tableB, $on, array $parameters = [])
    {
        $this->initArrayItem('join');

        $this->tasklist['join'][] = ['RIGHT JOIN', $tableB, $on, $parameters];

        return $this;
    }


    private function _________________________GROUPBY_ORDERBY_LIMIT()
    {
    }


    /**
     * GROUP BY 子句。
     *
     * @param array|string $columns
     *
     * @return $this
     */
    public function groupBy($columns)
    {
        $this->initArrayItem('groupby');

        $this->tasklist['groupby'][] = $columns;

        return $this;
    }


    /**
     * ORDER BY 子句。
     *
     * @param array|string $columns
     *
     * @return $this
     */
    public function orderBy($columns)
    {
        $this->initArrayItem('orderby');

        $this->tasklist['orderby'][] = $columns;

        return $this;
    }


    /**
     * LIMIT 子句。
     *
     * @param int|string $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->tasklist['limit'] = $limit;

        return $this;
    }


    private function _________________________INSERT()
    {
    }


    /**
     * 设置要新增的记录。
     *
     * @param array $record
     */
    public function record(array $record)
    {
        $this->tasklist['record'] = $record;

        return $this;
    }


    private function _________________________UPDATE()
    {
    }


    /**
     * 用 一个具体的数值 赋值。
     *
     * setValue(string $column, mixed $value)
     * setValue(array $column)
     *
     * @param string|array $column
     * @param mixed|null $value
     */
    public function setValue($column, $value = null)
    {
        $this->initArrayItem('set');

        if (is_string($column)) {
            $this->tasklist['set'][$column] = [
                'type'   => 'value',
                'column' => $column,
                'value'  => $value,
            ];
        } elseif (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->tasklist['set'][$key] = [
                    'type'   => 'value',
                    'column' => $key,
                    'value'  => $value,
                ];
            }
        } else {
            throw new Exception(__METHOD__ . '参数类型错误');
        }

        return $this;
    }


    /**
     * 用 一个列表达式 赋值。
     *
     * @param string $column
     * @param mixed $expr
     * @param array $parameters
     *
     * @return $this
     */
    public function setExpr($column, $expr, array $parameters = [])
    {
        $this->initArrayItem('set');



        $this->tasklist['set'][$column] = [
            'type'       => 'expr',
            'column'     => $column,
            'expr'       => $expr,
            'parameters' => $parameters,
        ];

        return $this;
    }


    /**
     * 用 另外一个表的指定列的值 赋值。
     *
     * @param string $column
     * @param string $tableB
     * @param string $columnB
     * @param string $colA
     * @param string $colB
     * @param boolean $checkExistsInWhere
     *
     * @return $this
     */
    public function setFromTable($column, $tableB, $columnB, $colA, $colB, $checkExistsInWhere = true)
    {
        $this->initArrayItem('set');

        $this->tasklist['set'][$column] = [
            'type'               => 'from_table',
            'column'             => $column,
            'tableB'             => $tableB,
            'columnB'            => $columnB,
            'colA'               => $colA,
            'colB'               => $colB,
            'checkExistsInWhere' => $checkExistsInWhere,
        ];

        return $this;
    }


    /**
     * 自增一个值。
     *
     * @param string $column
     * @param mixed $value
     */
    public function increment($column, $value = 1)
    {
        $this->initArrayItem('set');


        $this->setExpr($column, "$column + ?", [$value]);

        return $this;
    }


    /**
     * 自减一个值。
     *
     * @param string $column
     * @param mixed $value
     */
    public function decrement($column, $value = 1)
    {
        $this->initArrayItem('set');


        $this->setExpr($column, "$column - ?", [$value]);

        return $this;
    }


    private function _________________________EXECUTIONS()
    {
    }


    /**
     * SELECT
     *
     * @param string|array $columnlist
     *
     * @return \Dida\Db\DataSet|false 执行成功，返回一个DataSet；失败，返回false。
     */
    public function select($columnlist = null)
    {
        // 数据
        if (!is_null($columnlist)) {
            $this->columnlist($columnlist);
        }

        // 准备连接
        $conn = $this->db->getConnection();

        // 执行
        $this->tasklist['verb'] = 'SELECT';
        $sql = $this->build();
        $dataset = $conn->executeRead($sql['statement'], $sql['parameters']);

        // 返回结果
        return $dataset;
    }


    /**
     * 获取第一条匹配记录。
     *
     * @param string|array $columnlist
     *
     * @return array|false 执行成功，返回匹配的第一条记录；失败或者没有匹配记录，返回false。
     */
    public function getRow($columnlist = null)
    {
        // 执行select动作
        $dataset = $this->select($columnlist);

        // 如果执行出错，返回false
        if (!$dataset) {
            return false;
        }

        // 返回结果
        return $dataset->getRow();
    }


    /**
     * 获取所有匹配的记录。
     *
     * @param string|array $columnlist
     *
     * @return array|false 执行成功，返回匹配的所有记录；没有匹配记录，返回[]；有错返回false。
     */
    public function getRows($columnlist = null)
    {
        // 执行select动作
        $dataset = $this->select($columnlist);

        // 如果执行出错，返回false
        if (!$dataset) {
            return false;
        }

        // 从dataset中取出数据
        return $dataset->getRows();
    }


    /**
     * 从结果集的第一行返回对应列的值。
     *
     * @param type $column
     * @param type $returnType
     * @return boolean
     */
    public function getValue($column = 0, $returnType = null)
    {
        // 执行select动作
        $dataset = $this->select();

        // 如果执行出错，返回false
        if (!$dataset) {
            return false;
        }

        // 返回
        return $dataset->getValue($column, $returnType);
    }


    /**
     * 插入一条记录。
     *
     * @param array $record
     * @param int $insertReturn  返回类型
     *     INSERT_RETURN_COUNT  返回受影响的行数
     *     INSERT_RETURN_ID     返回插入的id
     */
    public function insertOne(array $record, $insertReturn = self::INSERT_RETURN_COUNT)
    {
        // 空数组，无需插入
        if (empty($record)) {
            return 0;
        }

        // 不是关联数组
        if (!$this->isAssociateArray($record)) {
            return false;
        }

        // 保存record
        $this->record($record);

        // 准备连接
        $conn = $this->db->getConnection();

        // 执行
        $this->tasklist['verb'] = 'INSERT';
        $sql = $this->build();
        $rowsAffected = $conn->executeWrite($sql['statement'], $sql['parameters']);

        switch ($insertReturn) {
            // 返回受影响的条数
            case self::INSERT_RETURN_COUNT:
                return $rowsAffected;

            // 返回新生成的id
            case self::INSERT_RETURN_ID:
                // 如果执行失败，返回false
                if ($rowsAffected === false) {
                    return false;
                }

                // 否则返回新生成的id
                return $conn->getPDO()->lastInsertId();
        }
    }


    /**
     * 插入多条记录
     *
     * @param array $records
     * @param int $insertReturn  返回类型
     *    self::INSERT_MANY_RETURN_SUCC_COUNT    执行成功的条数
     *    self::INSERT_MANY_RETURN_SUCC_LIST     执行成功的列表
     *    self::INSERT_MANY_RETURN_FAIL_COUNT    执行失败的条数
     *    self::INSERT_MANY_RETURN_FAIL_LIST     执行失败的列表
     *    self::INSERT_MANY_RETURN_FAIL_REPORT   执行失败的报告
     */
    public function insertMany(array $records, $returnType = self::INSERT_RETURN_COUNT)
    {
        // 空数组，无需插入
        if (empty($records)) {
            return 0;
        }

        // 不是索引数组
        if (!$this->isIndexedArray($records)) {
            return false;
        }

        // 准备统计
        $succ_count = 0;
        $succ_list = [];

        $fail_count = 0;
        $fail_list = [];
        $fail_report = [];

        // 准备连接
        $pdo = $this->db->getConnection()->getPDO();

        $last_keys = null;
        $last_statement = null;
        $stmt = null;

        foreach ($records as $seq => $record) {
            // 本条记录的keys列表
            $this_keys = array_keys($record);

            if ($last_keys !== $this_keys) {
                // 如果 $this_keys 和上次不一样，则需要重新build
                $this->tasklist['record'] = $record;
                $this->tasklist['verb'] = 'INSERT';
                $sql = $this->build();
                $last_statement = $sql['statement'];
                $last_keys = $this_keys;
                $values = array_values($record);

                // prepare
                $stmt = $pdo->prepare($last_statement);
                if ($stmt === false) {
                    $last_keys = null;
                    continue;
                }

                // 执行，返回成功的条数
                $result = $stmt->execute($values);

                // 统计
                if ($result) {
                    $succ_count++;
                    if ($returnType === self::INSERT_MANY_RETURN_SUCC_LIST) {
                        $succ_list[$seq] = $pdo->lastInsertId();
                    }
                } else {
                    $fail_count++;
                    if ($returnType === self::INSERT_MANY_RETURN_FAIL_LIST) {
                        $fail_list[$seq] = $pdo->errorCode();
                    } elseif ($returnType === self::INSERT_MANY_RETURN_FAIL_REPORT) {
                        $fail_report[$seq] = $pdo->errorInfo();
                    }
                }

                // 下一条
                continue;
            } else {
                // 如果 $this_keys 和上次一样，则直接用已经build好的statement
                $values = array_values($record);

                // 执行
                $result = $stmt->execute($values);

                // 统计
                if ($result) {
                    $succ_count++;
                    if ($returnType === self::INSERT_MANY_RETURN_SUCC_LIST) {
                        $succ_list[$seq] = $pdo->lastInsertId();
                    }
                } else {
                    $fail_count++;
                    if ($returnType === self::INSERT_MANY_RETURN_FAIL_LIST) {
                        $fail_list[$seq] = $pdo->errorCode();
                    } elseif ($returnType === self::INSERT_MANY_RETURN_FAIL_REPORT) {
                        $fail_report[$seq] = $pdo->errorInfo();
                    }
                }

                // 下一条
                continue;
            }
        }

        // 返回
        switch ($returnType) {
            case self::INSERT_MANY_RETURN_SUCC_COUNT:
                return $succ_count;
            case self::INSERT_MANY_RETURN_SUCC_LIST:
                return $succ_list;
            case self::INSERT_MANY_RETURN_FAIL_COUNT:
                return $fail_count;
            case self::INSERT_MANY_RETURN_FAIL_LIST:
                return $fail_list;
            case self::INSERT_MANY_RETURN_FAIL_REPORT:
                return $fail_report;
        }
    }


    /**
     * UPDATE
     *
     * @return int|false 成功，返回影响条数；失败，返回false。
     */
    public function update()
    {
        // 准备连接
        $conn = $this->db->getConnection();

        $sql = $this->build('UPDATE');

        $result = $conn->executeWrite($sql['statement'], $sql['parameters']);

        return $result;
    }


    /**
     * 主键不存在就插入，主键已存在就更新。
     *
     * @param array $data
     * @param string $pri_col 主键的列名
     *
     * @return boolean  成功返回true，失败返回false
     */
    public function insertOrUpdateOne(array $record, $pri_col)
    {
        // 重置 Query
        $this->clear();

        // 获取连接
        $pdo = $this->db->getConnection()->getPDO();

        // 插入本条记录
        $sql = $this->record($record)->build('INSERT');
        $stmt = $pdo->prepare($sql['statement']);
        $result = $stmt->execute($sql['parameters']);

        // 如果插入成功
        if ($result) {
            return true;
        }

        // 尝试更新
        $this->clear();
        $sql = $this->where($pri_col, '=', $record[$pri_col])
            ->setValue($record)
            ->build('UPDATE');
        $stmt = $pdo->prepare($sql['statement']);
        $result = $stmt->execute($sql['parameters']);

        // 如果更新成功，返回true，否则返回false
        if ($result && $stmt->rowCount()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 主键不存在就插入，主键已存在就更新。
     *
     * @param array $records
     * @param string $pri_col 主键的列名
     *
     * @return array 执行结果报告
     * [
     *     'succ' => [], // 执行成功的序号列表
     *     'fail' => [], // 执行失败的序号列表
     * ]
     */
    public function insertOrUpdateMany(array $records, $pri_col)
    {
        $succ = [];
        $fail = [];

        $last_keys = null;
        $stmtInsert = null;

        // 获取连接
        $pdo = $this->db->getConnection()->getPDO();

        // 逐个记录处理
        foreach ($records as $seq => $record) {
            // 尝试进行 INSERT
            $this_keys = array_keys($record);
            $values = array_values($record);
            if ($this_keys !== $last_keys) {
                $sql = $this->record($record)->build('INSERT');
                $stmtInsert = $pdo->prepare($sql['statement']);
                $last_keys = $this_keys;
            }

            // 如果插入成功
            $result = $stmtInsert->execute($values);
            if ($result && $stmtInsert->rowCount() > 0) {
                $succ[$seq] = null;
                continue;
            }

            // 尝试进行 UPDATE
            $this->clear();
            $sql = $this->where($pri_col, '=', $record[$pri_col])
                ->setValue($record)
                ->build('UPDATE');
            $stmtUpdate = $pdo->prepare($sql['statement']);
            $result = $stmtUpdate->execute($sql['parameters']);
            if ($result && $stmtUpdate->rowCount() > 0) {
                $succ[$seq] = null;
                continue;
            }

            // INSERT 和 UPDATE 都失败
            $fail[$seq] = null;
        }

        return [
            'succ' => $succ,
            'fail' => $fail,
        ];
    }


    /**
     * DELETE
     *
     * @return $this
     */
    public function delete()
    {
        // 准备连接
        $conn = $this->db->getConnection();

        // 执行
        $this->tasklist['verb'] = 'DELETE';
        $sql = $this->build();
        $rowsAffected = $conn->executeWrite($sql['statement'], $sql['parameters']);
        return $rowsAffected;
    }


    /**
     * TRUNCATE
     *
     * @return $this
     */
    public function truncate()
    {
        // 准备连接
        $conn = $this->db->getConnection();

        // 执行
        $this->tasklist['verb'] = 'TRUNCATE';
        $sql = $this->build();
        $rowsAffected = $conn->executeWrite($sql['statement'], $sql['parameters']);
        return $rowsAffected;
    }


    /**
     * 尝试执行未定义的方法。
     *
     * 虽然不倾向使用__call()，但是考虑到方便性，还是暂时保留。
     * 最好用 overload 或者 Trait 等方式明确定义。
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        // 如果是 DataSet 支持的方法
        if (method_exists('\Dida\Db\DataSet', $name)) {
            switch ($name) {
                case 'not support':
                    // 如果不想支持某些方法，在这里加上若干 case 去除。
                    break;
                default:
                    // 默认支持DataSet所有方法。
                    $dataset = $this->select();
                    return call_user_func_array([$dataset, $name], $arguments);
            }
        }

        throw new Exception(sprintf('方法不存在 %s::%s', __CLASS__, $name));
    }


    private function _________________________BACKUP_AND_RESTORE()
    {
    }


    /**
     * 备份当前的 tasklist 和相关变量
     *
     * @return array
     */
    public function backupTaskList()
    {
        $data = [
            'tasklist'     => $this->tasklist,
            'whereActive'  => $this->whereActive->name,
            'havingActive' => $this->havingActive->name,
        ];
        return $data;
    }


    /**
     * 恢复当前的 tasklist 和相关变量
     *
     * @param array $tasklist
     */
    public function restoreTaskList(array $data)
    {
        extract($data);

        $this->tasklist = $tasklist;

        if (isset($tasklist['where'])) {
            $this->whereDict = [];
            $this->tasklist['where']->getNamedDictionary($this->whereDict);  // 重新生成速查字典
            $this->whereActive = &$this->whereDict[$whereActive];  // 复位 whereActive
        } else {
            $this->whereDict = [];
            $this->whereActive = null;
        }

        if (isset($tasklist['having'])) {
            $this->havingDict = [];
            $this->tasklist['having']->getNamedDictionary($this->havingDict);  // 重新生成速查字典
            $this->havingActive = &$this->havingDict[$whereActive];  // 复位 havingActive
        } else {
            $this->havingDict = [];
            $this->havingActive = null;
        }
    }


    private function _________________________UTILITIES()
    {
    }


    /**
     * 如果检查到 tasklist 中的某个数组类型的键不存在，就先创建一个。
     *
     * @param type $name
     */
    protected function initArrayItem($name)
    {
        if (!isset($this->tasklist[$name])) {
            $this->tasklist[$name] = [];
        }
    }


    /**
     * 检查一个数组的类型是空数组、索引数组、混杂关联数组或纯粹关联数组。
     *
     * 规则是：
     * 空数组返回 0
     * 所有的keys都是整数的就是索引数组，返回 -1
     * 既有整数key，又有非整数key的，就是混杂关联数组，返回 1
     * 所有的keys都是非整数的就是纯粹关联数组，返回 2
     *
     * @param array $array
     *
     * @return int 0 空数组，-1 索引数组，1 混杂关联数组，2 纯粹关联数组
     */
    protected function getArrayType(array $array)
    {
        // 如果是空数组，返回0
        if (empty($array)) {
            return 0;
        }

        // 检查所有的keys
        $num = false;
        $nan = false; // Not a Number
        foreach ($array as $key => $item) {
            if (is_int($key)) {
                $num = true;
            } else {
                $nan = true;
            }
        }

        // 返回
        if ($nan) {
            return ($num) ? 1 : 2;
        } else {
            return -1;
        }
    }


    /**
     * 是否是纯粹关联数组
     *
     * @param array $array
     */
    protected function isAssociateArray(array $array)
    {
        foreach ($array as $key => $item) {
            if (is_int($key)) {
                return false;
            }
        }

        return true;
    }


    /**
     * 是否是索引数组
     *
     * @param array $array
     */
    protected function isIndexedArray(array $array)
    {
        foreach ($array as $key => $item) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
    }
}
