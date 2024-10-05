<?php

namespace Selfiens\Pipe;

use PHPUnit\Framework\TestCase;

class TestUserDefinedMethod extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Pipe::installGlobal();
        parent::setUpBeforeClass();
    }

    public function testDefineNullary()
    {
        try {
            Pipe::define('sum', function () {
                return static function (mixed $data): int {
                    return \array_sum($data);
                };
            });

            $actual = Pipe::pipe([1, 2, 3],
                Pipe::sum()
            );

            $this->assertEquals(6, $actual);
        } finally {
            Pipe::undefine("sum");
        }
    }

    public function testDefineUnary()
    {
        try {
            Pipe::define('addN', function ($n) {
                return static function (mixed $data) use ($n): array {
                    return \array_map(fn($i) => $i + $n, $data);
                };
            });

            $actual = Pipe::pipe([1, 2, 3],
                Pipe::addN(2)
            );

            $this->assertEquals([3, 4, 5,], $actual);
        } finally {
            Pipe::undefine("addOne");
        }
    }

    public function testDefineBinary()
    {
        try {
            Pipe::define('addMulti', function ($n, $m) {
                return static function (mixed $data) use ($n, $m): array {
                    return \array_map(fn($i) => ($i + $n) * $m, $data);
                };
            });

            $actual = Pipe::pipe([1, 2, 3],
                Pipe::addMulti(2, 3)
            );

            $this->assertEquals([9, 12, 15,], $actual);
        } finally {
            Pipe::undefine("addMulti");
        }
    }

    public function testDefineVariadic()
    {
        try {
            Pipe::define('addVariadic', function (...$args) {
                return static function (mixed $data) use ($args): array {
                    return \array_map(fn($i) => $i + array_sum($args), $data);
                };
            });

            $actual = Pipe::pipe([1, 2, 3],
                Pipe::addVariadic(1, 2, 3, 4)
            );

            $this->assertEquals([11, 12, 13,], $actual);
        } finally {
            Pipe::undefine("addVariadic");
        }
    }
}