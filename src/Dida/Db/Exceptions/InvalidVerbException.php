<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
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
