<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Db\Builder;

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
