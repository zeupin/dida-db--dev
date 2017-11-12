# \Dida\Db\SchemaInfo

负责读取数据库和数据表的元信息，供Query和Builder使用。

### __construct()

```php
public function __construct(&$db)
```

### getTableList()  --列出`schema`中的所有表名

```php
abstract public function getTableList();
```

### getTable()  --获取`schema.table`的所有信息。

```php
abstract public function getTable($table);
```

### getTableInfo()  --获取`schema.table`的表元信息。

```php
abstract public function getTableInfo($table);
```

### getColumnInfoList()  --获取指定的`schema.table`的所有列元信息。

```php
abstract public function getColumnInfoList($table);
```

### getColumnList()  --获取`schema.table`的列名列表数组

```php
abstract public function getColumnList($table);
```

### getColumnInfo()  --获取指定列的相关信息。

```php
abstract public function getColumnInfo($table, $column);
```

### getPrimaryKey()  --获取`schema.table`的唯一主键的键名。

```php
abstract public function getPrimaryKey($table);
```

### getPrimaryKeys()  --获取`schema.table`的复合主键的列名列表。

```php
abstract public function getPrimaryKeys($table);
```

### getUniqueColumns()  --获取`schema.table`的所有UNIQUE约束的列名数组

```php
abstract public function getUniqueColumns($table);
```

### getBaseType()  --获取基本类型，把驱动相关的数据类型转换为驱动无关的通用类型。

```php
abstract public function getBaseType($datatype);
```