<?php

namespace Selfiens\Pipe;

use PHPUnit\Framework\TestCase;

class PipeTest extends TestCase
{
    protected function setUp(): void
    {
        Pipe::install();
        parent::setUp();
    }

    public function testBasic()
    {
        // Calling pipe without any transformer should return the same data
        $this->assertEquals(1, pipe(1));
        $this->assertEquals([1, 2], pipe([1, 2]));
    }

    public function testReadMeCode()
    {
        $x = pipe(
            "  ,  1 ,  3,   2  ,   ",
            fn($s) => explode(",", $s),
            fn($a) => array_map('trim', $a),
            fn($a) => array_filter($a),
            fn($a) => array_map('intVal', $a),
            fn($a) => array_map(fn($n) => $n * 2, $a),
            fn($a) => join(", ", $a)
        );
        // "1, 2, 3"
        $this->assertEquals("2, 6, 4", $x);
    }
}
