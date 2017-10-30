# dida-db

`Dida\Db` 的目标是做一个轻巧、包含常用数据库管理特性、易于编码、没有复杂依赖关系的数据库管理包，适用于大多数常见的数据库操作场景。它属于 [Dida框架](http://dida.zeupin.com) 的一部分。

## 特点

* MIT协议。
* 支持常见的网站数据库，MySQL, MariaDB, SQLite, MS SQL Server, Oracle, PostgreSQL等等。
* 懒加载。只有当要用到连接时，才会开始初始化数据库连接。
* 最低支持 PHP 5.4。毕竟中国很多主流主机商的虚拟主机都还只能提供 PHP 5.x 的系统环境。
* 不依赖任何其它的外部包。

## 运行要求

* PHP: ^5.4 || ^7.0。
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

* [API](docs/README.md)

## 作者

* [Macc Liu](https://github.com/maccliu)

## 鸣谢

* [宙品科技，Zeupin LLC](http://zeupin.com) , 尤其是 [Dida 框架团队](http://dida.zeupin.com)

## 版权协议

Copyright (c) 2017 Zeupin LLC. Released under the [MIT license](LICENSE)。