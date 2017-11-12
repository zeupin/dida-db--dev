# Dida\Db\DataSet

[TOC]

## 系统相关的方法

### __construction() --构造函数。

## 底层方法
直接使用传入的PDOStatement对象的相关方法。

### columnCount() --返回结果的列数。
### rowCount() --返回结果的行数。
### debugDumpParams() --打印一条 SQL 预处理命令。
### errorCode() --获取跟上一次语句句柄操作相关的 SQLSTATE。
### errorInfo() --获取跟上一次语句句柄操作相关的扩展错误信息。
### setFetchMode() --为语句设置默认的获取模式。
### fetch() --从结果集中获取下一行。
### fetchAll() --从结果集中获取剩余所有行。
### fetchColumn() --从结果集中的下一行返回单独的一列。

## 高级方法

### getColumnNumber()  --获取指定列的列序号。
因为有些PDO调用需要使用列序号作为操作对象，而不是列名，所以用这个方法来将列名转为列序号。

```php
public function getColumnNumber(string|int $column)
```

返回值

| 类型 | 说明
|---|---
| int | 成功，返回列的序号值。
| false | 指定的$column不存在，返回false。

### getRow() --获取下一行。

```php
public function getRow()
```

### getRows() --获取剩余所有行。

```php
public function getRows()
```

### getColumn() --获取单独一列。

```php
public function getColumn(string|int $column, string|int $key = null)
```

| 参数 |	类型 | 说明
|---|---|---
| $column | string/int | 选择用哪一列作为value。可为列名或者列序号，其中第一列序号是0。
| $key | string/int | 选择用哪一列作为key。可为列名或者列序号，其中第一列序号是0。<br>如果$key为null，表示不需要指定返回数组的key。

返回值

| 类型 | 说明
|---|---
| array | 成功返回取回的数组。
| false | 失败返回false。

### getColumnDistinct() --获取指定列的无重复值。

```php
public function getColumnDistinct(string|int $column)
```

| 参数 |	类型 | 说明
|---|---|---
| $column | string/int | 选择用哪一列作为value。可为列名或者列序号，其中第一列序号是0。

返回值

| 类型 | 说明
|---|---
| array | 成功，返回获取的数组。
| false | 失败，返回false。

### getRowsAssocBy() --获取所有列，按照指定的列名进行关联。

```php
public function getRowsAssocBy($col1, $col2, ..., $colN)
```

返回值

| 类型 | 说明
|---|---
| array | 成功，返回获取的数组。注意，每个`[列1][列2][列N]`的值都是个一维数组。
| false | 失败，返回false。指定的任何一个$col不存在，返回false。

示例

```php
[列1][列2][列N] = [ 可以满足$col1,$col2,$colN的最后一行 ];
```

### getRowsGroupBy() --获取所有列，按照指定的列名进行分组。

```php
public function getRowsGroupBy($col1, $col2, ..., $colN)
```

返回值

| 类型 | 说明
|---|---
| array | 成功，返回获取的数组。注意，每个`[列1][列2][列N]`的值都是个二维数组。
| false | 失败，返回false。指定的任何一个$col不存在，返回false。

示例

```php
[列1][列2][列N] = [
                      [ 可以满足$col1,$col2,$colN的第1行 ],
                      [ 可以满足$col1,$col2,$colN的第2行 ],
                      [ 可以满足$col1,$col2,$colN的第N行 ],
                  ]
```

### getValue() --获取指定列的值。

```php
public function getValue($column = 0, $returnType = null)
```

| 参数 |	类型 | 说明
|---|---|---
| $column | string/int | 选择用哪一列作为value。可为列名或者列序号，其中第一列序号是0。
| $returnType | string | 可以为int或者float。如果设置的话，默认的返回值是字符串型。

返回值

| 类型 | 说明
|---|---
| mixed | 成功，返回获取的值。<br>如果读取的值为null，不管$returnType是什么，都返回null。
| false | 失败，返回false。<br>指定的$column不存在，返回false。
