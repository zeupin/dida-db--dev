<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

namespace Dida\Db\Traits;

/**
 * DbTrait
 */
trait DbTrait
{
    /**
     * @return boolean
     */
    public function isConnected()
    {
        return $this->getConnection()->errorCode();
    }


    /**
     * @return \PDO|false
     */
    public function getPDO()
    {
        return $this->getConnection()->getPDO();
    }


    /**
     * @return \PDOStatement
     */
    public function getPDOStatement()
    {
        return $this->getConnection()->getPDOStatement();
    }


    /**
     * @return string
     */
    public function errorCode()
    {
        return $this->getConnection()->errorCode();
    }


    /**
     * @return array
     */
    public function errorInfo()
    {
        return $this->getConnection()->errorInfo();
    }


    /**
     * @return boolean
     */
    public function execute($statement, array $parameters = null, $replace_prefix = false)
    {
        return $this->getConnection()->execute($statement, $parameters, $replace_prefix);
    }


    /**
     * @return array
     */
    public function executeRead($statement, array $parameters = null, $replace_prefix = false)
    {
        return $this->getConnection()->executeRead($statement, $parameters, $replace_prefix);
    }


    /**
     * @return int|false
     */
    public function executeWrite($statement, array $parameters = null, $replace_prefix = false)
    {
        return $this->getConnection()->executeWrite($statement, $parameters, $replace_prefix);
    }


    /**
     * @return string
     */
    public function lastInsertId()
    {
        return $this->getConnection()->getPDO()->lastInsertId();
    }
}
