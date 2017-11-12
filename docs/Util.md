# \Dida\Db\Util

一些实用工具的集合。

## 数组类

### arrayAssocBy() --将数组按照指定的列做索引

```php
public static function arrayAssocBy(array &$array, $keyN)
```

| 参数 |	类型 | 说明
|---|---|---
| $array | array | 原数组。数组应为一个二维数组。执行完本函数后，原数组内容可能会被清除。
| $keyN | string/int | 选择用哪一列作为key，可输入多个$key。


**返回值：**

| 类型 | 说明
|---|---
| array | 成功，返回生成的数组。
| false | 失败，返回false。如果指定的任何一个$keyN不存在，也返回false。

**注意：**

1. 考虑到处理超大数组时的内存占用问题，**原数组的数据被逐条处理后，会被删除**。
2. 需要自行保证给出的 key1,key2,keyN 的组合可以唯一确定一条记录。<br>否则，对于同一 key1,key2,keyN，后值将覆盖前值。
3. key1,key2,keyN 一般使用数据表的唯一主键、复合主键、或者有唯一值的字段名。

### arrayGroupBy() --将数组按照指定的列做分组

```php
public static function arrayGroupBy(array &$array, $keyN)
```

| 参数 |	类型 | 说明
|---|---|---
| $array | array | 原数组。数组应为一个二维数组。执行完本函数后，原数组内容可能会被清除。
| $keyN | string/int | 选择用哪一列作为key，可输入多个$key。


**返回值：**

| 类型 | 说明
|---|---
| array | 成功，返回生成的数组。
| false | 失败，返回false。如果指定的任何一个$keyN不存在，也返回false。

**注意：**

1. 考虑到处理超大数组时的内存占用问题，**原数组的数据被逐条处理后，会被删除**。
2. 需要自行保证给出的 key1,key2,keyN 的组合可以唯一确定一条记录。<br>否则，对于同一 key1,key2,keyN，后值将覆盖前值。
3. key1,key2,keyN 一般使用数据表的唯一主键、复合主键、或者有唯一值的字段名。

