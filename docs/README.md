# Dida\Db

## 介绍

`Dida\Db` 的目标是做一个轻巧的、运行快速的、开发友好的、没有复杂依赖关系的数据库管理包，用于大多数常见的数据库操作场景，它是 [Dida框架](http://dida.zeupin.com) 的一部分。

## 特点

* MIT协议。
* 支持常见的网站数据库，MySQL, MariaDB, SQLite, MS SQL Server, Oracle, PostgreSQL等。
* 最低支持 PHP 5.5。
* 所有需要的文件都包含在`\Dida\Db`命名空间内，不依赖任何其它的外部包。
* 懒加载。只有当要用到连接时，才会开始初始化数据库连接。
* 灵活的条件树机制（ConditionTree），可以在不脱离流式操作的情况下，方便设置各种查询条件。

## 运行要求

* PHP: ^5.5 || ^7.0。
* 确保相关的数据库驱动扩展(php_pdo_xxx)已经安装，且在 `php.ini` 里面已经正确地配置好。
* Mbstring扩展，用于Unicode字符串的处理。

## 安装

### Composer方式安装

```bash
composer require dida/db
```

### 升级

```bash
composer update
```

## API文档

| 类名 | 说明
|---|---
| [Dida\Db\Db](Db.md)                  | 数据库
| [Dida\Db\Connection](Connection.md)  | 负责数据库的物理连接
| [Dida\Db\DataSet](DataSet.md) 	   | 查询结果的数据集
| [Dida\Db\Query](Query.md) 	       | 负责管理查询请求
| [Dida\Db\Builder](Builder.md) 	   | 查询构造器，负责把查询请求编译成对应的SQL表达式
| [Dida\Db\SchemaInfo](SchemaInfo.md)  | 实现Schema的查询处理
| [Dida\Db\Util](Util.md) 	           | 工具类

## 几种典型的配置文件

[典型的数据库配置文件](conf.md)

## 作者

* [Macc Liu](https://github.com/maccliu)

## 鸣谢

* [宙品科技，Zeupin LLC](http://zeupin.com) , 尤其是 [Dida 框架团队](http://dida.zeupin.com)

## 版权协议

Copyright (c) 2017 Zeupin LLC. Released under the [MIT license](LICENSE)。