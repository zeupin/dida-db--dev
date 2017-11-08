<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db\Exceptions;

/**
 * InvalidVerbBuilderException
 */
class InvalidVerbBuilderException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        $s = sprintf('[Builder] Invalid verb "%s"', $message);

        parent::__construct($s, $code, $previous);
    }
}
