<?php
/**
 * Dida Framework  <http://dida.zeupin.com>
 *
 * Copyright 2017 Zeupin LLC.
 */

namespace Dida\Db\Exceptions;

/**
 * InvalidVerbException，指定了无效的verb。
 *
 * 原因是：
 * 1. Query 中没有指定 verb。
 * 2. verb 写错了。
 * 3. 配置的 Builder 尚不能处理这个 verb。
 */
class InvalidVerbException extends \Exception
{
}
