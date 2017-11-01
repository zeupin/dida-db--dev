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
     * @var \Dida\Db\Builder
     */
    protected $builder = null;

    /**
     * @var boolean
     */
    public $built = false;

    /**
     * The result of $this->build()
     *
     * @var boolean
     */
    public $build_ok = false;

    /**
     * Enable/Disable pull-execution
     *
     * @see __call()
     * @var boolean
     */
    protected $pullexec = true;

    /**
     * SQL statement
     *
     * @var string
     */
    public $statement = null;

    /**
     * SQL parameters
     *
     * @var array
     */
    public $parameters = null;

    /**
     * @var array
     */
    protected $taskbase = [
        'verb'        => 'SELECT',
        'prefix'      => '',
        'swap_prefix' => '###_',
    ];

    /**
     * @var \Dida\Db\ConditionTree
     */
    public $whereTree = null;

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
     * @var type
     */
    protected $whereDict = [];

    /**
     * @var \Dida\Db\ConditionTree
     */
    public $havingTree = null;

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
     * @var type
     */
    protected $havingDict = [];

    /**
     * 任务清单
     *
     * @var array
     */
    protected $tasklist = [];


    /**
     * Class construct.
     *
     * @param array $options
     * @param \Dida\Db\Db $db
     */
    public function __construct(&$db)
    {
        $this->db = $db;

        $cfg = $db->getConfig();

        $this->taskbase = array_merge($this->taskbase, [
            'prefix'      => $cfg['db.prefix'],
            'swap_prefix' => $cfg['db.swap_prefix'],
        ]);

        // 初始化
        $this->init();
    }


    /**
     * 重置任务列表为空
     *
     * @return $this
     */
    public function init()
    {
        $this->tasklist = $this->taskbase;

        $this->whereInit();
        $this->havingInit();

        return $this;
    }


    /**
     * 如果检查到 tasklist 中的某个数组类型的键不存在，就先创建一个。
     *
     * @param type $name
     */
    protected function initArray($name)
    {
        if (!isset($this->tasklist[$name])) {
            $this->tasklist[$name] = [];
        }
    }


    /**
     * Implicit calling the methods in the DataSet class.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        // Pull-Execution feature
        if ($this->pullexec) {
            if (method_exists('\Dida\Db\DataSet', $name)) {
                $result = $this->execute();
                return call_user_func_array([$result, $name], $arguments);
            }
        }

        throw new Exception(sprintf('方法不存在 %s::%s', __CLASS__, $name));
    }


    /**
     * 导出当前的tasklist
     *
     * @return array
     */
    public function exportTaskList()
    {
        return $this->tasklist;
    }


    /**
     * 导入一个tasklist
     *
     * @param array $tasklist
     */
    public function importTaskList(array $tasklist)
    {
        $this->tasklist = $tasklist;
    }


    /**
     * DELETE
     *
     * @return $this
     */
    public function delete()
    {
        $this->tasklist['verb'] = 'DELETE';

        return $this;
    }


    /**
     * UPDATE
     *
     * @return $this
     */
    public function update()
    {
        $this->tasklist['verb'] = 'UPDATE';

        return $this;
    }


    /**
     * TRUNCATE
     *
     * @return $this
     */
    public function truncate()
    {
        $this->tasklist['verb'] = 'TRUNCATE';

        return $this;
    }


    /**
     * Builds the statement.
     *
     * @return $this
     */
    public function build()
    {
        $this->builder = $this->db->getBuilder();
        if ($this->builder === null) {
            throw new Exception('必须要指定一个Builder对象');
        }

        return $this->builder->build($this->tasklist);
    }


    /**
     * Executes the SQL statement built and returns a DataSet object.
     *
     * @param string $sql
     * @param array $sql_parameters
     *
     * @return DataSet
     */
    public function execute()
    {
        if (!$this->built) {
            $this->build();
        }

        // Makes a DB connection.
        if ($this->db->connect() === false) {
            throw new Exception('Fail to connect the database.');
        }

        try {
            $pdoStatement = $this->db->getPDO()->prepare($this->statement);
            $success = $pdoStatement->execute($this->parameters);
            return new DataSet($this->db, $pdoStatement, $success);
        } catch (Exception $ex) {
            return false;
        }
    }


    private function _________________________DONE()
    {
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
     * SELECT
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
    public function select($columnlist = null)
    {
        $this->initArray('columnlist');

        $this->tasklist['verb'] = 'SELECT';

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
        $this->initArray('columnlist');

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
        $this->initArray('columnlist');

        $this->tasklist['columnlist'][] = ['count', $columns, $alias];

        return $this;
    }


    private function _________________________WHERE()
    {
    }


    /**
     * 初始化 whereTree
     */
    protected function whereInit()
    {
        $this->tasklist['where'] = new ConditionTree('AND');
        $this->whereTree = &$this->tasklist['where'];
        $this->whereDict[''] = &$this->tasklist['where'];
        $this->whereActive = $this->whereTree;
    }


    /**
     * 在 whereTree 的当前节点添加一个 where 条件
     *
     * @param string|array $condition
     * @param array $data
     *
     * @return $this
     */
    public function where($condition, array $data = [])
    {
        if (is_string($condition)) {
            $this->whereActive->items[] = [$condition, 'RAW', $data];
        }

        if (is_array($condition)) {
            $this->whereActive->items[] = $condition;
        }

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
        $this->whereActive->logic = $logic;

        return $this;
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
        $group = new ConditionTree($logic);
        $group->items = $conditions;

        $this->whereActive->items[] = &$group;
        $this->whereDict[$name] = &$group;
        $this->whereActive = &$group;

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
    protected function havingInit()
    {
        $this->tasklist['having'] = new ConditionTree('AND');
        $this->havingTree = &$this->tasklist['having'];
        $this->havingDict[''] = &$this->tasklist['having'];
        $this->havingActive = $this->havingTree;
    }


    /**
     * 在 havingTree 的当前节点添加一个 having 条件
     *
     * @param string|array $condition
     * @param array $data
     *
     * @return $this
     */
    public function having($condition, array $data = [])
    {
        if (is_string($condition)) {
            $this->havingActive->items[] = [$condition, 'RAW', $data];
        }

        if (is_array($condition)) {
            $this->havingActive->items[] = $condition;
        }

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
        $this->havingActive->logic = $logic;

        return $this;
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
        $group = new ConditionTree($logic);
        $group->items = $conditions;

        $this->havingActive->items[] = &$group;
        $this->havingDict[$name] = &$group;
        $this->havingActive = &$group;

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
        $this->initArray('join');

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
        $this->initArray('join');

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
        $this->initArray('join');

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
        $this->initArray('join');

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
        $this->initArray('groupby');

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
        $this->initArray('orderby');

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
     * INSERT
     *
     * @return $this
     */
    public function insert(array $record)
    {
        $this->tasklist['verb'] = 'INSERT';

        $this->tasklist['insert'] = $record;

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
        $this->initArray('set');

        $this->tasklist['verb'] = 'UPDATE';

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
        $this->initArray('set');

        $this->tasklist['verb'] = 'UPDATE';

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
        $this->initArray('set');

        $this->tasklist['verb'] = 'UPDATE';

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
        $this->initArray('set');

        $this->tasklist['verb'] = 'UPDATE';

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
        $this->initArray('set');

        $this->tasklist['verb'] = 'UPDATE';

        $this->setExpr($column, "$column - ?", [$value]);

        return $this;
    }
}
