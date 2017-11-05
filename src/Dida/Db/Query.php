<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db;

use \Exception;

/**
 * SQL查询
 */
class Query
{
    /**
     * Version
     */
    const VERSION = '0.1.5';

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
     * SchemaMap 实例。
     *
     * @var \Dida\Db\SchemaMap
     */
    protected $schemamap = null;

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
            throw new Exception('Builder实例未指定');
        }

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
                $this->tasklist['set'][$column] = [
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
     * @return \Dida\Db\DataSet
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
     * INSERT 一条或者多条记录
     */
    public function insert(array $data = [])
    {
        $data_type = $this->getArrayType($data);
        switch ($data_type) {
            case 0:  // 空数组
                return 0;
            case -1:  // 索引数组
                return $this->insertMany($data);
            case 2:  // 关联数组
                return $this->insertOne($data);
        }
    }


    /**
     * 插入一条记录，返回影响的行数。
     *
     * @param array $record
     */
    public function insertOne(array $record)
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
        return $rowsAffected;
    }


    /**
     * 插入多条记录，返回影响的行数。
     *
     * @param array $records
     */
    public function insertMany(array $records)
    {
        // 空数组，无需插入
        if (empty($records)) {
            return 0;
        }

        // 不是索引数组
        if (!$this->isIndexedArray($records)) {
            return false;
        }

        // 准备连接
        $conn = $this->db->getConnection();

        $last_keys = [];
        $last_statement = null;
        $rowsAffected = 0;
        foreach ($records as $record) {
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

                // 执行，返回成功的条数
                $result = $conn->executeWrite($last_statement, $values);

                // 累加
                if (is_int($result)) {
                    $rowsAffected += $result;
                }
                continue;
            } else {
                // 如果 $this_keys 和上次一样，则直接用已经build好的statement
                $values = array_values($record);

                // 执行，返回成功的条数
                $result = $conn->executeWrite($last_statement, $values);

                // 累加
                if (is_int($result)) {
                    $rowsAffected += $result;
                }
                continue;
            }
        }

        // 返回
        return $rowsAffected;
    }


    /**
     * UPDATE
     */
    public function update()
    {
        $this->tasklist['verb'] = 'UPDATE';
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
                case 'getRow':
                case 'getRows':
                case "getColumn":
                    $dataset = $this->select();
                    return call_user_func_array([$dataset, $name], $arguments);
            }
        }

        throw new Exception(sprintf('方法不存在 %s::%s', __CLASS__, $name));
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
