# \Dida\Db\Builder

负责将给出的tasklist编译成对应的SQL表达式和参数数组。

## 外部接口

### build()

```php
public function build(&$tasklist)
```

**返回值**

返回一个数组，形式为：

```php
[
    'statement'  => 表达式,
    'parameters' => [参数数组],
]
```