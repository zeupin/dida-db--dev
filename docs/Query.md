# \Dida\Db\Query

Query负责根据客户意图，给出想要的结果。

[TOC]

## 初始化

### init()  --重置任务列表为空。

```php
public function init()
```

**返回值：  $this**

### clear()  --只保留 taskbase 里面的 table 条目,其它条目全部删除。

```php
public function clear()
```

**返回值：  $this**

## Build

### build()  --根据tasklist，构建出一个sql表达式和参数数组。

```php
public function build($verb = null)
```

## table

### table()  --设置当前数据表。

```php
public function table($name_as_alias, $prefix = null)
```

**返回值：  $this**

## ColumnList

### columnlist()  --设置select的columnlist。

```php
public function columnlist($columnlist = null)
```

**返回值：  $this**

### distinct()  --设置distinct表达式。

```php
public function distinct()
```

**返回值：  $this**

### count()  --设置count表达式。

```php
public function count(array $columns = null, $alias = null)
```

**返回值：  $this**

## Wheres

### where()  --设置一个where条件。
在 whereTree 的当前节点添加一个 where 条件。其中，支持的条件操作符列表请见本文附录一。

```php
【标准模式】
where(string $col_expr, string $op)
where(string $col_expr, string $op, mixed $data)
where(string $col_expr, string $op, mixed $data1, mixed $data2)

【RAW模式】 直接给出表达式
where(string $condition)
where(string $condition, array $parameters)

【匹配模式】 关联数组，参见 whereMatch()
where(array $match)
where(array $match, string $logic)
where(array $match, string $logic, string $name)

【专业模式】 索引数组：[列表达式，操作符，数据，数据]
where(array $condition)
```

**返回值：  $this**

### whereGroup()  --设置一组where条件。

```php
public function whereGroup(array $conditions = [], $logic = 'AND', $name = null)
```

**返回值：  $this**

### whereLogic()  --设置当前where组的logic。

```php
public function whereLogic($logic)
```

**返回值：  $this**

### whereMatch()  --设置where的匹配表达式。

```php
public function whereMatch(array $array, $logic = 'AND', $name = null)
```

**返回值：  $this**

### whereGoto()  --转到指定的where组。

```php
public function whereGoto($name)
```

**返回值：  $this**

## Havings

### having()  --设置一个having条件。
在 havingTree 的当前节点添加一个 having 条件。其中，支持的条件操作符列表请见本文附录一。

```php
【标准模式】
having(string $col_expr, string $op)
having(string $col_expr, string $op, mixed $data)
having(string $col_expr, string $op, mixed $data1, mixed $data2)

【RAW模式】 直接给出表达式
having(string $condition)
having(string $condition, array $parameters)

【匹配模式】 关联数组，参见 havingMatch()
having(array $match)
having(array $match, string $logic)
having(array $match, string $logic, string $name)

【专业模式】 索引数组：[列表达式，操作符，数据，数据]
having(array $condition)
```

**返回值：  $this**

### havingGroup()  --设置一组having条件。

```php
public function havingGroup(array $conditions = [], $logic = 'AND', $name = null)
```

**返回值：  $this**

### havingLogic()  --设置当前having组的logic。

```php
public function havingLogic($logic)
```

**返回值：  $this**

### havingMatch()  --设置having的匹配表达式。

```php
public function havingMatch(array $array, $logic = 'AND', $name = null)
```

**返回值：  $this**

### havingGoto()  --转到指定的having组。

```php
public function havingGoto($name)
```

**返回值：  $this**

## Joins

### join()  --JOIN

```php
public function join($tableB, $on, array $parameters = [])
```

**返回值：  $this**

### innerJoin()  --INNER JOIN

```php
public function innerJoin($tableB, $on, array $parameters = [])
```

**返回值：  $this**

### leftJoin()  --LEFT JOIN

```php
public function leftJoin($tableB, $on, array $parameters = [])
```

**返回值：  $this**

### rightJoin() --RIGHT JOIN

```php
public function rightJoin($tableB, $on, array $parameters = [])
```

**返回值：  $this**

## GroupBy, OrderBy, Limit

### groupBy()  --GROUP BY 子句

```php
public function groupBy($columns)
```

**返回值：  $this**

### orderBy()  --ORDER BY 子句

```php
public function orderBy($columns)
```

**返回值：  $this**

### limit()  --LIMIT 子句

```php
public function limit($limit)
```

**返回值：  $this**

## Insert

### record()  设置要插入的记录。

```php
public function record(array $record)
```

**返回值：  $this**

## Update

### setValue()  --设置指定字段的值。

```php
public function setValue($column, $value = null)
```

**返回值：  $this**

### setExpr()  --设置指定字段的表达式。

```php
public function setExpr($column, $expr, array $parameters = [])
```

**返回值：  $this**

### setFromTable()  --从一个表的指定字段获取值。

```php
public function setFromTable($column, $tableB, $columnB, $colA, $colB, $checkExistsInWhere = true)
```

**返回值：  $this**

### increment()  --字段自增。

```php
public function increment($column, $value = 1)
```

**返回值：  $this**

### decrement()  --字段自减。

```php
public function decrement($column, $value = 1)
```

**返回值：  $this**

## Execution

### select()  --执行SELECT操作。

```php
public function select($columnlist = null)
```

### insertOne()  --插入一条数据。

```php
public function insertOne(array $record, $insertReturn = self::INSERT_RETURN_COUNT)
```

### insertMany()  --插入多条数据。

```php
public function insertMany(array $records, $returnType = self::INSERT_RETURN_COUNT)
```

### update()  --执行UPDATE操作。

```php
public function update()
```

### insertOrUpdateOne()  --插入或者更新一条记录。
如果记录已经存在，按照当前数据更新记录；如果记录不存在，按照当前数据创建记录。

```php
public function insertOrUpdateOne(array $record, $pri_col)
```

### insertOrUpdateMany()  --插入或者更新一批记录。
如果记录已经存在，按照当前数据更新记录；如果记录不存在，按照当前数据创建记录。

```php
public function insertOrUpdateMany(array $records, $pri_col)
```

### delete()  --执行DELETE操作。

```php
public function delete()
```

### truncate()  --清空当前的数据表。

```php
public function truncate()
```

### __call()  --动态调用

```php
public function __call($name, $arguments)
```

## 备份和恢复

### backupTaskList()  --备份当前的TaskList

```php
public function backupTaskList()
```

### restoreTaskList()  --恢复一个备份的TaskList

```php
public function restoreTaskList(array $data)
```

## 附录

### 附录一：支持的条件操作符列表

```php
/**
 * 支持的操作符集合
 * [操作符 => 内部名称]
 */
public static $opertor_set = [
    /* Raw SQL */
    'RAW' => 'RAW',

    /* 等于 */
    'EQ' => 'EQ',
    '='  => 'EQ',
    '==' => 'EQ',

    /* 不等于 */
    'NEQ' => 'NEQ',
    '<>'  => 'NEQ',
    '!='  => 'NEQ',

    /* <,>,<=,>= */
    'GT'  => 'GT',
    '>'   => 'GT',
    'EGT' => 'EGT',
    '>='  => 'EGT',
    'LT'  => 'LT',
    '<'   => 'LT',
    'ELT' => 'ELT',
    '<='  => 'ELT',

    /* LIKE */
    'LIKE'     => 'LIKE',
    'NOT LIKE' => 'NOTLIKE',
    'NOTLIKE'  => 'NOTLIKE',

    /* IN */
    'IN'     => 'IN',
    'NOT IN' => 'NOTIN',
    'NOTIN'  => 'NOTIN',

    /* BETWEEN */
    'BETWEEN'     => 'BETWEEN',
    'NOT BETWEEN' => 'NOTBETWEEN',
    'NOTBETWEEN'  => 'NOTBETWEEN',

    /* EXISTS */
    'EXISTS'     => 'EXISTS',
    'NOT EXISTS' => 'NOTEXISTS',
    'NOTEXISTS'  => 'NOTEXISTS',

    /* ISNULL */
    'ISNULL'      => 'ISNULL',
    'NULL'        => 'ISNULL',
    'ISNOTNULL'   => 'ISNOTNULL',
    'IS NOT NULL' => 'ISNOTNULL',
    'NOTNULL'     => 'ISNOTNULL',
    'NOT NULL'    => 'ISNOTNULL',
];
```