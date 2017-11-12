# \Dida\Db\Connection

负责和数据库进行物理连接，并管理相应的PDO对象。Connection不依赖于任何其它类，只需要传入正确的数据库配置，即可开始工作。

[TOC]

## 初始化

### __construct()

```php
public function __construct($cfg)
```

## 连接管理

### connect() --连接数据库。

```php
public function connect()
```
**返回值**

| 类型 | 说明
|---|---
| boolean | 是否连接成功。

### disconnect() --断开数据库连接。

```php
public function disconnect()
```

**返回值**

无返回值

### isConnected() --检查是否已经连接数据库。

```php
public function isConnected()
```

**返回值**

| 类型 | 说明
|---|---
| boolean | 是否连接。

### worksWell() --连接是否还能正常工作。
通过测试数据库能否正常执行一个`SELECT 1`的操作，来确定当前的数据库连接是否正常工作。

```php
public function worksWell()
```

## 输出接口

### getPDO() --立即连接数据库，并返回PDO实例。

```php
public function getPDO()
```

**返回值**

| 类型 | 说明
|---|---
| \PDO | 成功，返回PDO实例对象。
| false | 失败，返回false。

### getPDOStatement() --返回当前的 PDOStatement 实例。

```php
public function getPDOStatement()
```

**返回值**

| 类型 | 说明
|---|---
| \PDOStatement | 返回当前的PDOStatement实例对象。如果对象尚未初始化，返回null。

### errorCode() --获取跟上一次语句句柄操作相关的 SQLSTATE 错误码。

```php
public function errorCode()
```

**返回值**

| 类型 | 说明
|---|---
| string | 一个由5个字母或数字组成的在 ANSI SQL 标准中定义的 SQLSTATE 标识符。

### errorInfo() --返回一个关于上一次语句句柄执行操作的错误信息的数组。

```php
public function errorInfo()
```

**返回值**

| 类型 | 说明
|---|---
| array | 错误信息的数组。

该数组包含下列字段：
- 0 SQLSTATE 错误码（一个由5个字母或数字组成的在 ANSI SQL 标准中定义的标识符）。
- 1 具体驱动错误码。
- 2 具体驱动错误信息。

## 执行

### execute() --执行一条通用SQL语句，返回true/false。

```php
public function execute($statement, array $parameters = null, $replace_prefix = false)
```

**返回值**

| 类型 | 说明
|---|---
| true | 成功，返回true。
| false | 失败，返回false。

### executeRead() --执行一条查询类的语句（SELECT），返回一个DataSet。

```php
public function executeRead($statement, array $parameters = null, $replace_prefix = false)
```

**返回值**

| 类型 | 说明
|---|---
| \Dida\Db\DataSet | 成功，返回DataSet。
| false | 失败，返回false。

### executeWrite()  --执行一条修改类的语句（INSERT/UPDATE/DELETE)，并返回影响的记录条数。

```php
public function executeWrite($statement, array $parameters = null, $replace_prefix = false)
```

**返回值**

| 类型 | 说明
|---|---
| int | 成功，返回影响的记录条数。
| false | 失败，返回false。