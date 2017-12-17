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
 * SQL表达式构造器。
 * 每次调用build()，只会生成一条SQL语句。
 */
class Builder
{
    /**
     * 版本号
     */
    const VERSION = '20171113';

    /**
     * 指向 Db 连接的指针
     *
     * @var \Dida\Db\Db
     */
    protected $db = null;

    /**
     * 和本次build相关的数据表信息，主要是为了表的别名的处理。
     * 只在一个build期间存在，build完了就销毁。
     * [
     *    表名     => 指向 db->schemainfo->info[表名],
     *    表的别名 => 指向 db->schemainfo->info[表名],
     * ]
     */
    protected $localSchemaInfo = [];

    /**
     * 任务列表
     *
     * @var array
     */
    protected $tasklist = [];

    /**
     * 主表
     *
     * [name=>, alias=>, nameAsAlias=>]
     *
     * @var array
     */
    protected $mainTable = [];

    /**
     * 最终的表达式数组(statement array)
     *
     * @var array
     */
    protected $ST = [];

    /**
     * 最终的参数数组(parameter array)
     *
     * @var array
     */
    protected $PA = [];

    /**
     * 支持的操作符集合
     */
    public static $opertor_set = [
        /* Raw SQL */
        'RAW' => 'RAW', //

        /* 等于 */
        'EQ' => 'EQ',
        '='  => 'EQ',
        '==' => 'EQ', //

        /* 不等于 */
        'NEQ' => 'NEQ',
        '<>'  => 'NEQ',
        '!='  => 'NEQ', //

        /* <,>,<=,>= */
        'GT'  => 'GT',
        '>'   => 'GT',
        'EGT' => 'EGT',
        '>='  => 'EGT',
        'LT'  => 'LT',
        '<'   => 'LT',
        'ELT' => 'ELT',
        '<='  => 'ELT', //

        /* LIKE */
        'LIKE'     => 'LIKE',
        'NOT LIKE' => 'NOTLIKE',
        'NOTLIKE'  => 'NOTLIKE', //

        /* IN */
        'IN'     => 'IN',
        'NOT IN' => 'NOTIN',
        'NOTIN'  => 'NOTIN', //

        /* BETWEEN */
        'BETWEEN'     => 'BETWEEN',
        'NOT BETWEEN' => 'NOTBETWEEN',
        'NOTBETWEEN'  => 'NOTBETWEEN', //

        /* EXISTS */
        'EXISTS'     => 'EXISTS',
        'NOT EXISTS' => 'NOTEXISTS',
        'NOTEXISTS'  => 'NOTEXISTS', //

        /* ISNULL */
        'ISNULL'      => 'ISNULL',
        'NULL'        => 'ISNULL',
        'ISNOTNULL'   => 'ISNOTNULL',
        'IS NOT NULL' => 'ISNOTNULL',
        'NOTNULL'     => 'ISNOTNULL',
        'NOT NULL'    => 'ISNOTNULL', //
    ];


    /**
     * 类的构造函数
     *
     * @param \Dida\Db\Db $db
     */
    public function __construct(&$db)
    {
        $this->db = $db;
    }


    /**
     * 初始化所有的变量，准备开始一次全新的build。
     */
    protected function init()
    {
        // 重置ST和PA
        $this->ST = [];
        $this->PA = [];
    }


    /**
     * 根据给出的$tasklist数组，构造出对应的SQL表达式
     *
     * @param array $tasklist
     *
     * @return
     * @@array
     *      [
     *          'statement'  => ...,
     *          'parameters' => ...,
     *      ]
     */
    public function build(&$tasklist)
    {
        // 重置内部变量
        $this->init();

        // 保存传过来的任务列表
        $this->tasklist = $tasklist;

        // 根据verb不同，选择对应的模板进行构建
        switch ($this->tasklist['verb']) {
            case 'SELECT':
                return $this->build_SELECT();
            case 'INSERT':
                return $this->build_INSERT();
            case 'UPDATE':
                return $this->build_UPDATE();
            case 'DELETE':
                return $this->build_DELETE();
            case 'TRUNCATE':
                return $this->build_TRUNCATE();
            default:
                throw new \Dida\Db\Exceptions\InvalidVerbException($this->tasklist['verb']);
        }
    }


    private function _________________________BUILD()
    {
    }


    protected function build_SELECT()
    {
        $this->prepare_SELECT();

        $STMT = [
            "SELECT\n    ",
            'columnlist' => &$this->ST['columnlist'],
            'from'       => &$this->ST['selectfrom'],
            'join'       => &$this->ST['join'],
            'where'      => &$this->ST['where'],
            'groupby'    => &$this->ST['groupby'],
            'having'     => &$this->ST['having'],
            'orderby'    => &$this->ST['orderby'],
            'limit'      => &$this->ST['limit'],
        ];

        $PARAMS = [
            'join'   => &$this->PA['join'],
            'where'  => &$this->PA['where'],
            'having' => &$this->PA['having'],
        ];

        return [
            'statement'  => implode('', $STMT),
            'parameters' => $this->util_combine_parameters($PARAMS),
        ];
    }


    protected function build_INSERT()
    {
        $this->prepare_INSERT();

        $STMT = [
            "INSERT INTO\n    ",
            'table'  => &$this->ST['table'],
            'record' => &$this->ST['record'],
        ];

        $PARAMS = [
            'record' => &$this->PA['record'],
        ];

        return [
            'statement'  => implode('', $STMT),
            'parameters' => $this->util_combine_parameters($PARAMS),
        ];
    }


    protected function build_UPDATE()
    {
        $this->prepare_UPDATE();

        $STMT = [
            "UPDATE\n    ",
            'table'   => &$this->ST['table'],
            'set'     => &$this->ST['set'],
            'join'    => &$this->ST['join'],
            'where'   => &$this->ST['where'],
            'groupby' => &$this->ST['groupby'],
            'having'  => &$this->ST['having'],
            'orderby' => &$this->ST['orderby'],
        ];

        $PARAMS = [
            'set'    => &$this->PA['set'],
            'join'   => &$this->PA['join'],
            'where'  => &$this->PA['where'],
            'having' => &$this->PA['having'],
        ];

        return [
            'statement'  => implode('', $STMT),
            'parameters' => $this->util_combine_parameters($PARAMS),
        ];
    }


    protected function build_DELETE()
    {
        $this->prepare_DELETE();

        $STMT = [
            'DELETE FROM ',
            'table'   => &$this->ST['table'],
            'join'    => &$this->ST['join'],
            'where'   => &$this->ST['where'],
            'groupby' => &$this->ST['groupby'],
            'having'  => &$this->ST['having'],
            'orderby' => &$this->ST['orderby'],
        ];

        $PARAMS = [
            'join'   => &$this->PA['join'],
            'where'  => &$this->PA['where'],
            'having' => &$this->PA['having'],
        ];

        return [
            'statement'  => implode('', $STMT),
            'parameters' => $this->util_combine_parameters($PARAMS),
        ];
    }


    protected function build_TRUNCATE()
    {
        $this->prepare_TRUNCATE();

        $STMT = [
            'TRUNCATE TABLE ',
            'table' => &$this->ST['table'],
        ];

        return [
            'statement'  => implode('', $STMT),
            'parameters' => [],
        ];
    }


    private function _________________________PREPARE()
    {
    }


    protected function prepare_SELECT()
    {
        $this->clause_TABLE();
        $this->clause_COLUMNLIST();
        $this->clause_JOIN();
        $this->clause_WHERE();
        $this->clause_GROUP_BY();
        $this->clause_HAVING();
        $this->clause_ORDER_BY();
        $this->clause_LIMIT();
    }


    protected function prepare_INSERT()
    {
        $this->clause_TABLE();
        $this->clause_JOIN();
        $this->clause_WHERE();
        $this->clause_GROUP_BY();
        $this->clause_HAVING();
        $this->clause_INSERT();
    }


    protected function prepare_UPDATE()
    {
        $this->clause_TABLE();
        $this->clause_JOIN();
        $this->clause_SET();    // 必须在 WHERE 子句的前面，原因参见 set_FromTable()
        $this->clause_WHERE();
        $this->clause_GROUP_BY();
        $this->clause_HAVING();
        $this->clause_ORDER_BY();
    }


    protected function prepare_DELETE()
    {
        $this->clause_TABLE();
        $this->clause_JOIN();
        $this->clause_WHERE();
        $this->clause_GROUP_BY();
        $this->clause_HAVING();
        $this->clause_ORDER_BY();
    }


    protected function prepare_TRUNCATE()
    {
        $this->clause_TABLE();
    }


    private function _________________________TABLE()
    {
    }


    /**
     * FROM 子句
     */
    protected function clause_TABLE()
    {
        // 如果没有设置table，直接退出
        if (!$this->has('table')) {
            return;
        }

        /*
         * [
         *     'name'   => $name_as_alias,
         *     'prefix' => $prefix,
         * ]
         */
        extract($this->tasklist['table']);
        $name = trim($name);

        // 检查是一个表还是多个表
        if (strpos($name, ',') === false) {
            $this->parse_table_one($name, $prefix);
        } else {
            $this->parse_table_many($name, $prefix);
        }
    }


    /**
     * 处理 $tasklist['table'] 是单表的情况。
     *
     * @param string $name
     * @param string $prefix
     */
    protected function parse_table_one($name, $prefix)
    {
        // 分离name和alias
        $t = $this->util_split_name_alias($name);
        $name = $t['name'];
        $alias = $t['alias'];

        // 注册数据表。如果有错误，会抛异常出来
        $this->util_register_table($name, $alias, $prefix);

        // 加上prefix的表名
        $realname = $this->util_table_with_prefix($name, $prefix);

        // 设置为主表
        $this->mainTable = [
            'name'  => $realname,
            'alias' => $alias,
        ];

        // 设置ST字典
        $this->ST['table'] = $realname;
        $this->ST['table_with_alias'] = $this->util_table_with_alias($realname, $alias);
        $this->ST['table_ref'] = $this->util_get_table_ref($realname, $alias);
        $this->ST['selectfrom'] = "\nFROM\n    " . $this->util_table_with_alias($realname, $alias);
    }


    /**
     * 处理 $tasklist['table'] 是多表的情况。
     *
     * @param string $name
     * @param string $prefix
     */
    protected function parse_table_many($name, $prefix)
    {
        $firstTable = null;
        $selectfrom = [];

        $tables = explode(',', $name);
        foreach ($tables as $table) {
            $table = trim($table);
            if ($table === '') {
                continue;
            }

            // 分离name和alias
            $t = $this->util_split_name_alias($table);
            $name = $t['name'];
            $alias = $t['alias'];

            // 注册数据表。如果有错误，会抛异常出来
            $this->util_register_table($name, $alias, $prefix);

            // 加上prefix的表名
            $realname = $this->util_table_with_prefix($name, $prefix);

            // 记录第一个表
            if ($firstTable === null) {
                $firstTable = [
                    'name'  => $realname,
                    'alias' => $alias,
                ];
            }

            $selectfrom[] = $this->util_table_with_alias($realname, $alias);
        }

        // 主表设为第一个表
        $this->mainTable = $firstTable;

        // 设置ST字典
        $this->ST['table'] = $firstTable['name'];
        $this->ST['table_with_alias'] = $this->util_table_with_alias($firstTable['name'], $firstTable['alias']);
        $this->ST['table_ref'] = $this->util_get_table_ref($firstTable['name'], $firstTable['alias']);
        $this->ST['selectfrom'] = "\nFROM\n    " . implode(', ', $selectfrom);
    }


    private function _________________________COLUMNLIST()
    {
    }


    protected function clause_COLUMNLIST()
    {
        if (!$this->has('columnlist')) {
            $columnlist = $this->localSchemaInfo[$this->mainTable['name']]['columnlist'];
            if ($columnlist) {
                $this->ST['columnlist'] = implode(', ', $columnlist);
            } else {
                $this->ST['columnlist'] = '*';
            }

            return;
        }

        $final = '';

        $columnlist = $this->tasklist['columnlist'];
        foreach ($columnlist as $item) {
            $type = $item[0];
            switch ($type) {
                case 'raw':
                    $s = $item[1];
                    if ($final) {
                        $final .= ', ' . $s;
                    } else {
                        $final = $s;
                    }
                    break;

                case 'array':
                    $columnArray = $item[1];
                    $s = implode(', ', $columnArray);
                    if ($final) {
                        $final .= ', ' . $s;
                    } else {
                        $final = $s;
                    }
                    break;

                case 'distinct':
                    $final = "DISTINCT " . $final;
                    break;

                case 'count':
                    list($type, $columnlist_for_count, $alias) = $item;

                    if ($columnlist_for_count) {
                        //如果是数组形式，先要把其转为字符串形式
                        if (is_array($columnlist_for_count)) {
                            $columnlist_for_count = implode(', ', $columnlist_for_count);
                        }
                        $final = $final . (($final) ? ", " : '');
                        $final .= "COUNT($columnlist_for_count)";
                    } else {
                        if ($final === '') {
                            $final = 'COUNT(*)';
                        } else {
                            $final = "COUNT($final)";
                        }
                    }

                    // 如果有别名
                    if ($alias) {
                        $final = "$final AS $alias";
                    }
                    break;
            }
        }

        $this->ST['columnlist'] = $final;
    }


    private function _________________________JOIN()
    {
    }


    protected function clause_JOIN()
    {
        if (!$this->has('join')) {
            $this->ST['join'] = '';
            $this->PA['join'] = [];
            return;
        }

        $st = [];
        $pa = [];

        $joins = $this->tasklist['join'];
        foreach ($joins as $join) {
            list($jointype, $table, $on, $parameters) = $join;

            // 拆分为 table AS alias
            $table_alias = $this->util_split_name_alias($table);

            // 登记join进来的这个表
            $this->util_register_table($table_alias['name'], $table_alias['alias']);

            // 生成标准形式的 $table
            $tablename_with_prefix = $this->util_table_with_prefix($table_alias['name']);
            $table = $this->util_table_with_alias($tablename_with_prefix, $table_alias['alias']);

            $st[] = "\n{$jointype} {$table}\n    ON $on";
            $pa[] = $parameters;
        }

        $this->ST["join"] = implode("", $st);
        $this->PA['join'] = $this->util_combine_parameters($pa);
    }


    private function _________________________WHERE_and_HAVING()
    {
    }


    /**
     * WHERE 子句。
     */
    protected function clause_WHERE()
    {
        // 如果没有设置 WHERE 条件，直接返回
        if (!$this->has('where')) {
            $this->ST['where'] = '';
            $this->PA['where'] = [];
            return;
        }

        // 如果没有设置 WHERE 条件，直接返回
        $whereTree = $this->tasklist['where'];
        if (empty($whereTree->items)) {
            $this->ST['where'] = '';
            $this->PA['where'] = [];
            return;
        }

        // 解析 $whereTree
        $part = $this->parse_conditionTree($whereTree);

        // 存入仓库
        $this->ST['where'] = "\nWHERE\n    " . $part['statement'];
        $this->PA['where'] = $part['parameters'];
        return;
    }


    /**
     * HAVING 子句。
     * 和WHERE子句基本一模一样。
     */
    protected function clause_HAVING()
    {
        // 如果没有设置 HAVING 条件，直接返回
        if (!$this->has('having')) {
            $this->ST['having'] = '';
            $this->PA['having'] = [];
            return;
        }

        // 如果没有设置 HAVING 条件，直接返回
        $havingTree = $this->tasklist['having'];
        if (empty($havingTree->items)) {
            $this->ST['having'] = '';
            $this->PA['having'] = [];
            return;
        }

        // 解析 $havingTree
        $part = $this->parse_conditionTree($havingTree);

        // 存入仓库
        $this->ST['having'] = "\nHAVING\n    " . $part['statement'];
        $this->PA['having'] = $part['parameters'];
        return;
    }


    /**
     * 构建SQL表达式
     */
    protected function parse_conditionTree(ConditionTree $conditionTree)
    {
        $parts = [];

        foreach ($conditionTree->items as $condition) {
            if ($condition instanceof ConditionTree) {
                $parts[] = $this->parse_conditionTree($condition);
            } else {
                $parts[] = $this->cond($condition);
            }
        }

        // 合并 $parts 的 statement
        $stArray = array_column($parts, 'statement');
        $st = implode(" $conditionTree->logic ", $stArray);
        $st = "($st)";

        // 合并 $parts 的 parameters
        $paArray = array_column($parts, 'parameters');
        $pa = [];
        foreach ($paArray as $param) {
            $pa = array_merge($pa, $param);
        }

        return [
            'statement'  => $st,
            'parameters' => $pa,
        ];
    }


    protected function cond($condition)
    {
        // 检查条件表达式的参数个数
        $cnt = count($condition);

        // 根据类型检查
        if ($cnt === 3) {
            $column = array_shift($condition);
            $op = array_shift($condition);
            $data = array_shift($condition);
        } elseif ($cnt === 2) {
            // 如 isnull, isnotnull 运算
            $column = array_shift($condition);
            $op = array_shift($condition);
            $data = null;
        } elseif ($cnt === 4) {
            // 如 between
            $column = array_shift($condition);
            $op = array_shift($condition);
            $data1 = array_shift($condition);
            $data2 = array_shift($condition);
            $data = [$data1, $data2];
        } else {
            throw new Exception("不正确的条件表达式" . var_export($condition, true));
        }

        // 识别运算类型
        $op = strtoupper($op);
        if (!array_key_exists($op, self::$opertor_set)) {
            throw new Exception("不支持此运算类型 \"$op\"" . var_export($condition, true));
        }

        // 调用对应的 cond_**** 运算
        $method_name = 'cond_' . self::$opertor_set[$op];
        return $this->$method_name($column, $op, $data);
    }


    protected function cond_RAW($column, $op, $data)
    {
        // 帮RAW表达式加上括号。仅仅用了极其粗略的检查形式
        $column = "($column)";

        return [
            'statement'  => $column,
            'parameters' => $data,
        ];
    }


    protected function cond_COMPARISON($column, $op, $data)
    {
        $part = [
            'statement'  => "($column $op ?)",
            'parameters' => [$data],
        ];
        return $part;
    }


    protected function cond_EQ($column, $op, $data)
    {
        if (is_array($data)) {
            return $this->cond_IN($column, 'IN', $data);
        }

        return $this->cond_COMPARISON($column, '=', $data);
    }


    protected function cond_GT($column, $op, $data)
    {
        return $this->cond_COMPARISON($column, '>', $data);
    }


    protected function cond_LT($column, $op, $data)
    {
        return $this->cond_COMPARISON($column, '<', $data);
    }


    protected function cond_EGT($column, $op, $data)
    {
        return $this->cond_COMPARISON($column, '>=', $data);
    }


    protected function cond_ELT($column, $op, $data)
    {
        return $this->cond_COMPARISON($column, '<=', $data);
    }


    protected function cond_NEQ($column, $op, $data)
    {
        if (is_array($data)) {
            return $this->cond_NOTIN($column, $op, $data);
        }

        return $this->cond_COMPARISON($column, '<>', $data);
    }


    protected function cond_IN($column, $op, $data)
    {
        if (empty($data)) {
            throw new Exception('IN表达式不能为一个空数组');
        }

        $marks = implode(', ', array_fill(0, count($data), '?'));
        $part = [
            'statement'  => "($column $op ($marks))",
            'parameters' => array_values($data),
        ];
        return $part;
    }


    /**
     * 尽量不要使用 NOTIN 操作，执行时的性能非常差。
     */
    protected function cond_NOTIN($column, $op, $data)
    {
        return $this->cond_IN($column, 'NOT IN', $data);
    }


    protected function cond_LIKE($column, $op, $data)
    {
        if (is_scalar($data)) {
            $data = [$data];
        }

        $part = [
            'statement'  => "($column $op ?)",
            'parameters' => $data,
        ];
        return $part;
    }


    protected function cond_NOTLIKE($column, $op, $data)
    {
        return $this->cond_LIKE($column, 'NOT LIKE', $data);
    }


    protected function cond_BETWEEN($column, $op, $data)
    {
        $part = [
            'statement'  => "($column $op ? AND ?)",
            'parameters' => $data,
        ];
        return $part;
    }


    protected function cond_NOTBETWEEN($column, $op, $data)
    {
        return $this->cond_BETWEEN($column, 'NOT BETWEEN', $data);
    }


    protected function cond_ISNULL($column, $op, $data = null)
    {
        $part = [
            'statement'  => "($column IS NULL)",
            'parameters' => [],
        ];
        return $part;
    }


    protected function cond_ISNOTNULL($column, $op, $data = null)
    {
        $part = [
            'statement'  => "($column IS NOT NULL)",
            'parameters' => [],
        ];
        return $part;
    }


    protected function cond_EXISTS($column, $op, $data)
    {
        $part = [
            'statement'  => "(EXISTS ($column))",
            'parameters' => $data,
        ];
        return $part;
    }


    protected function cond_NOTEXISTS($column, $op, $data)
    {
        $part = [
            'statement'  => "(NOT EXISTS ($column))",
            'parameters' => $data,
        ];
        return $part;
    }


    private function _________________________GROUPBY_ORDERBY_LIMIT()
    {
    }


    /**
     * GROUP BY 子句
     */
    protected function clause_GROUP_BY()
    {
        if (!$this->has('groupby')) {
            $this->ST['groupby'] = '';
            return;
        }

        $groupbys = $this->tasklist['groupby'];
        if (empty($groupbys)) {
            $this->ST['groupby'] = '';
            return;
        }

        // 合并 $groupbys 数组
        $s = implode(', ', $groupbys);

        if ($groupbys) {
            $this->ST['groupby'] = "\nGROUP BY\n    $s";
        } else {
            $this->ST['groupby'] = '';
        }
        return;
    }


    /**
     * ORDER BY 子句
     */
    protected function clause_ORDER_BY()
    {
        if (!$this->has('orderby')) {
            $this->ST['orderby'] = '';
            return;
        }

        $orderbys = $this->tasklist['orderby'];
        if (empty($orderbys)) {
            $this->ST['orderby'] = '';
            return;
        }

        // 合并 $orderbys 数组
        $s = implode(', ', $orderbys);

        if ($orderbys) {
            $this->ST['orderby'] = "\nORDER BY\n    $s";
        } else {
            $this->ST['orderby'] = '';
        }
        return;
    }


    /**
     * LIMIT 子句
     */
    protected function clause_LIMIT()
    {
        // 如果没有设置 limit
        if (!$this->has('limit')) {
            $this->ST['limit'] = '';
            return;
        }

        // 生成 limit 子句
        $limit = $this->tasklist['limit'];
        $this->ST['limit'] = "\nLIMIT $limit";
        return;
    }


    private function _________________________SET()
    {
    }


    protected function clause_SET()
    {
        $set = $this->tasklist['set'];

        $parts = [];
        foreach ($set as $item) {
            switch ($item['type']) {
                case 'value':
                    $parts[] = $this->set_Value($item);
                    break;
                case 'expr':
                    $parts[] = $this->set_Expr($item);
                    break;
                case 'from_table':
                    $parts[] = $this->set_FromTable($item);
                    break;
            }
        }

        $result = $this->util_combine_parts($parts, ",\n    ");

        $st = $result['statement'];
        $pa = $result['parameters'];

        $this->ST['set'] = "\nSET\n    " . $st;
        $this->PA['set'] = $pa;
    }


    /**
     * @param array $item
     * [
     *     'type'   => 'value',
     *     'column' => $column,
     *     'value'  => $value,
     * ]
     */
    protected function set_Value($item)
    {
        extract($item);

        return [
            'statement'  => "$column = ?",
            'parameters' => [$value],
        ];
    }


    /**
     * @param array $item
     * [
     *     'type'       => 'expr',
     *     'column'     => $column,
     *     'expr'       => $expr,
     *     'parameters' => $parameters,
     * ]
     */
    protected function set_Expr($item)
    {
        extract($item);

        return [
            'statement'  => "$column = $expr",
            'parameters' => $parameters,
        ];
    }


    /**
     * @param array $item
     * [
     *     'type'               => 'from_table',
     *     'column'             => $column,
     *     'tableB'             => $tableB,
     *     'columnB'            => $columnB,
     *     'colA'               => $colA,
     *     'colB'               => $colB,
     *     'checkExistsInWhere' => $checkExistsInWhere,
     * ]
     */
    protected function set_FromTable($item)
    {
        extract($item);

        $table_ref = $this->ST['table_ref'];

        $target = "(SELECT $tableB.$columnB FROM $tableB WHERE $table_ref.$colA = $tableB.$colB)";
        $statement = "$column = $target";

        if ($checkExistsInWhere) {
            $this->tasklist['where']->items[] = ["(EXISTS $target)", 'RAW', []];
        }

        return [
            'statement'  => $statement,
            'parameters' => [],
        ];
    }


    private function _________________________INSERT()
    {
    }


    protected function clause_INSERT()
    {
        if (!$this->has('record')) {
            return;
        }

        $record = $this->tasklist['record'];
        $columns = array_keys($record);
        $values = array_values($record);

        $columnlist = '(' . implode(', ', $columns) . ')';
        $marklist = $this->util_make_marklist(count($columns), true);

        $this->ST['record'] = "{$columnlist}\nVALUES\n    {$marklist}";
        $this->PA['record'] = $values;
    }


    private function _________________________UTIL()
    {
    }


    /**
     * 检查任务清单中是否有某个键
     *
     * @param string $key
     *
     * @return boolean
     */
    protected function has($key)
    {
        return array_key_exists($key, $this->tasklist);
    }


    /**
     * 把若干个part合成一个part。
     *
     * 其中每个part都是一个形如
     * [
     *     'statement'  => ...,
     *     'parameters' => ...,
     * ]
     * 的数组。
     *
     * @param array $parts
     * @param string $stmt_glue  statement间的连接字符串
     *
     * @return array 合并后的part
     */
    protected function util_combine_parts(array $parts, $stmt_glue)
    {
        $statement_array = array_column($parts, 'statement');
        $statement = implode($stmt_glue, $statement_array);

        $parameters_array = array_column($parts, 'parameters');
        $parameters = $this->util_combine_parameters($parameters_array);

        return [
            'statement'  => $statement,
            'parameters' => $parameters,
        ];
    }


    /**
     * 把多个参数数组合并成一个。
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function util_combine_parameters(array $parameters)
    {
        $ret = [];
        foreach ($parameters as $array) {
            $ret = array_merge($ret, array_values($array));
        }
        return $ret;
    }


    /**
     * 把表名和前缀拼接起来。
     *
     * @param string $name
     * @param string $prefix
     *
     * @return string
     */
    protected function util_table_with_prefix($name, $prefix = null)
    {
        if (!is_string($prefix)) {
            $prefix = $this->tasklist['prefix'];
        }
        return $prefix . $name;
    }


    /**
     * 把一个“name AS alias”形式的字符串解析出来。
     *
     * 注意：参数必须以“{空格}AS/as/As{空格}”为分隔符才能被识别出来。
     *
     * @param string $name_as_alias
     *      可能的取值为："name" 或者 "name AS alias"
     */
    protected function util_split_name_alias($name_as_alias)
    {
        $name_as_alias = trim($name_as_alias);

        // 找到第一个“空白+AS+空白”，前部分是name，后部分是alias
        $i = strripos($name_as_alias, ' AS ');

        // 如果没有别名
        if ($i === false) {
            return [
                'name'  => $name_as_alias,
                'alias' => null,
            ];
        }

        // 如果找到了别名部分
        $name = substr($name_as_alias, 0, $i);
        $alias = substr($name_as_alias, $i + 4);

        // 去除空白，代码强壮性更好
        $name = trim($name);
        $alias = trim($alias);

        // 返回
        return [
            'name'  => $name,
            'alias' => $alias,
        ];
    }


    /**
     * 把表名和别名拼接起来。
     *
     * 别名存在时，返回“表名 AS 别名”。
     * 别名不存在时，只返回“表名”。
     *
     * @param string $table
     * @param string $alias
     *
     * @return string
     */
    protected function util_table_with_alias($table, $alias)
    {
        if (is_string($alias) && $alias) {
            return $table . ' AS ' . $alias;
        } else {
            return $table;
        }
    }


    /**
     * 把列表达式和别名拼接起来。
     *
     * 别名存在时，返回“列表达式名 AS 别名”。
     * 别名不存在时，只返回“列表达式名”。
     *
     * @param string $col_expr
     * @param string $alias
     *
     * @return string
     */
    protected function util_col_with_alias($col_expr, $alias)
    {
        if (is_string($alias) && $alias) {
            return $col_expr . ' AS ' . $alias;
        } else {
            return $col_expr;
        }
    }


    /**
     * 把SQL表达式中的 ###_XXX 替换为 prefix_XXX
     */
    protected function util_replace_swap_prefix($swapsql)
    {
        $prefix = $this->tasklist['prefix'];
        $swap_prefix = $this->tasklist['swap_prefix'];
        if ($swap_prefix) {
            return str_replace($swap_prefix, $prefix, $swapsql);
        } else {
            return $swapsql;
        }
    }


    /**
     * 生成一个用于参数化查询的问号列表
     *
     * @param int|array $count 参数的个数。
     * @param boolean $braket  结果是否需要包含括号
     *
     * @return string
     */
    protected function util_make_marklist($count, $braket = false)
    {
        // 如果是数组，计算个数
        if (is_array($count)) {
            $count = count($count);
        }

        // 生成问号列表数组
        $list = implode(', ', array_fill(0, $count, '?'));

        // 返回
        return ($braket) ? "($list)" : $list;
    }


    /**
     * 如果数据表有别名，返回别名；如果没有别名，返回表名。
     *
     * @param string $name
     * @param string $alias
     * @return string
     */
    protected function util_get_table_ref($name, $alias)
    {
        return ($alias) ? $alias : $name;
    }


    /**
     * 登记一个数据表到 $locaSchemaInfo
     *
     * @param string $name
     * @param string $alias
     */
    protected function util_register_table($name, $alias, $prefix = null)
    {
        // 实际的表名
        $realname = $this->util_table_with_prefix($name, $prefix);

        // 指向到 schemainfo 的对应节点处
        $tableinfo = $this->db->getSchemaInfo()->getTable($realname);
        if (!$tableinfo) {
            throw new Exception("SchemaInfo中没有找到数据表{$realname}的相关信息");
        }

        // 指向到 schemainfo->info[表名]
        $this->localSchemaInfo[$realname] = $tableinfo;

        // 如果有别名，也指向到 schemainfo->info[表名]
        if ($alias) {
            if (!isset($this->localSchemaInfo[$alias])) {
                $this->localSchemaInfo[$alias] = $tableinfo;
            }
        }
    }
}
