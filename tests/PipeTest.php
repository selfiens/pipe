<?php

namespace Selfiens\Pipe;

use PHPUnit\Framework\TestCase;

class PipeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Pipe::installGlobal();
        parent::setUpBeforeClass();
    }

    public function testBasic()
    {
        // Returns the data as it is when there is no transformer
        $this->assertEquals(1, pipe(1));
        $this->assertEquals([1, 2], pipe([1, 2]));
    }

    public function testReadMeCode1()
    {
        $actual = pipe(
            "  ,  1 ,  3,   2  ,   ",
            fn($s) => explode(",", $s),
            fn($a) => array_map('trim', $a),
            fn($a) => array_filter($a),
            fn($a) => array_map('intVal', $a),
            fn($a) => array_map(fn($n) => $n * 2, $a),
            fn($a) => join(", ", $a)
        );
        $this->assertEquals("2, 6, 4", $actual);
    }

    public function testReadMeCode2()
    {
        $actual = pipe(
            'x',
            fn($s) => $s.'y',
            fn($s) => $s.'z',
            fn($s) => $s.'0',
        );
        $this->assertEquals("xyz0", $actual);
    }

    public function testStringReverse()
    {
        $actual = pipe(
            "1 + 2 = 3",
            str_split(...),
            Pipe::map(trim(...)),
            array_filter(...),
            array_reverse(...),
            join(...),
        );
        $this->assertEquals("3=2+1", $actual);
    }

    public function testFirstParamIsCallable(): void
    {
        $actual = pipe(
            (fn() => "123")(),
            strrev(...)
        );
        $this->assertEquals("321", $actual);
    }

    public function testCreateFunctionWithPipe()
    {
        $fn = fn($p) => pipe(
            $p,
            trim(...),
            strrev(...),
            strtoupper(...),
        );

        $this->assertEquals("321", $fn(" 123 "));
    }

    public function testNonCallableInTheMiddle()
    {
        $log = [];
        $echo = function ($msg) use (&$log) {
            $log[] = $msg;
            return $msg;
        };

        pipe(
            'abc',
            strtoupper(...),
            $echo,
            'DEF',
            strtolower(...),
            $echo,
        );

        $this->assertEquals(["ABC", "def"], $log);
    }
}
