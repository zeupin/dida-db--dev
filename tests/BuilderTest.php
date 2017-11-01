<?php

use \PHPUnit\Framework\TestCase;
use \PDO;
use \Exception;
use \Dida\Debug\Debug;
use \Dida\Db\Builder;
use \Dida\Db\Query;
use \Dida\Db\Connection;
use \Dida\Db\Db;

/**
 * BuilderTest
 */
class BuilderTest extends TestCase
{
    public $builder = null;
    public $db = null;
    public $conn = null;


    public function __construct()
    {
        $cfg = include(__DIR__ . "/db.config.php");
        $this->conn = new Dida\Db\Connection($cfg);

        $this->builder = new Builder($this->conn);
    }
}
