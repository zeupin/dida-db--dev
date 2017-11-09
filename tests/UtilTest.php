<?php

use \PHPUnit\Framework\TestCase;
use \Dida\Debug\Debug;

/**
 * UtilTest
 */
class UtilTest extends TestCase
{
    public function test_arrayBy()
    {
        $array = [
            ['c1' => 'A', 'c2' => 'a', 'c3' => 1, 'c4' => 11],
            ['c1' => 'A', 'c2' => 'b', 'c3' => 2, 'c4' => 12],
            ['c1' => 'A', 'c2' => 'b', 'c3' => 3, 'c4' => 13],
            ['c1' => 'B', 'c2' => 'c', 'c3' => 4, 'c4' => 14],
        ];

        $ret = \Dida\Db\Util::arrayBy($array, 'c1', 'c2');
        echo Debug::varDump($ret);

        $expect = [
            'A' => [
                'a' => ['c1' => 'A', 'c2' => 'a', 'c3' => 1, 'c4' => 11],
                'b' => ['c1' => 'A', 'c2' => 'b', 'c3' => 3, 'c4' => 13],
            ],
            'B' => [
                'c' => ['c1' => 'B', 'c2' => 'c', 'c3' => 4, 'c4' => 14],
            ],
        ];

        $this->assertEquals($expect, $ret);
    }


    public function test_arrayGroupBy()
    {
        $array = [
            ['c1' => 'A', 'c2' => 'a', 'c3' => 1, 'c4' => 11],
            ['c1' => 'A', 'c2' => 'b', 'c3' => 2, 'c4' => 12],
            ['c1' => 'A', 'c2' => 'b', 'c3' => 3, 'c4' => 13],
            ['c1' => 'B', 'c2' => 'c', 'c3' => 4, 'c4' => 14],
        ];

        $ret = \Dida\Db\Util::arrayGroupBy($array, 'c1', 'c2');
        echo Debug::varDump($ret);

        $expect = [
            'A' => [
                'a' => [
                    0 => ['c1' => 'A', 'c2' => 'a', 'c3' => 1, 'c4' => 11],
                ],
                'b' => [
                    0 => ['c1' => 'A', 'c2' => 'b', 'c3' => 2, 'c4' => 12],
                    1 => ['c1' => 'A', 'c2' => 'b', 'c3' => 3, 'c4' => 13],
                ],
            ],
            'B' => [
                'c' => [
                    0 => ['c1' => 'B', 'c2' => 'c', 'c3' => 4, 'c4' => 14],
                ],
            ],
        ];

        $this->assertEquals($expect, $ret);
    }
}
