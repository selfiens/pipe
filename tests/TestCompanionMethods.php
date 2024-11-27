<?php

namespace Selfiens\Pipe;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Selfiens\Pipe\Pipe as P;

class TestCompanionMethods extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        P::installGlobal();
        parent::setUpBeforeClass();
    }

    public function testTap()
    {
        $tapped = null;
        $actual = pipe(
            [1, 2, 3],
            P::tap(function ($n) use (&$tapped) {
                $tapped = $n;
            })
        );
        $this->assertEquals([1, 2, 3], $actual);
        $this->assertEquals([1, 2, 3], $tapped);
    }

    public function testMap()
    {
        $actual = pipe([1, 2, 3], P::map(fn($n) => $n + 1));
        $this->assertEquals([2, 3, 4], $actual);
    }

    public function testFilter()
    {
        $actual = pipe([1, 2, 3], P::filter(fn($n) => $n < 3));
        $this->assertEquals([1, 2], $actual);
    }

    public function testFilerNot()
    {
        $actual = pipe([1, 2, 3], P::filterNot(fn($n) => $n === 2));
        $this->assertEquals([1, 2 => 3], $actual);
    }

    public function testColumn()
    {
        $actual = pipe([['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], P::column('a'));
        $this->assertEquals([1, 3], $actual);

        $actual = pipe([['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], P::column(null));
        $this->assertEquals([['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], $actual);

        $actual = pipe([['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], P::column(null, 'a'));
        $this->assertEquals([1 => ['a' => 1, 'b' => 2], 3 => ['a' => 3, 'b' => 4]], $actual);
    }

    public function testReduce()
    {
        $actual = pipe([1, 2, 3], P::reduce(fn($carry, $item) => $carry + $item, 0));
        $this->assertEquals(6, $actual);
    }

    public function testFlat()
    {
        $actual = pipe([1, [2, [3], 4], 5], P::flat());
        $this->assertEquals([1, 2, 3, 4, 5], $actual);
    }

    public function testMerge()
    {
        $actual = pipe([1, 2, 3], P::merge([4, 5, 6], [7, 8, 9]));
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], $actual);
    }

    public function testTake()
    {
        $this->assertEquals([], pipe([1, 2, 3], P::take(0)));
        $this->assertEquals([1, 2], pipe([1, 2, 3], P::take(2)));

        $this->assertEquals([2, 3], pipe([1, 2, 3], P::take(-2)));
        $this->assertEquals([1 => 2, 2 => 3], pipe([1, 2, 3], P::take(-2, true)));
    }

    public static function provideSkipData(): array
    {
        return [
            [
                [], // data
                0, // skip(n)
                [], // expected
            ],
            // data, skip(n), expected
            [[1, 2, 3], 2, [3],],
            [[1, 2, 3], 3, [],],
            [[1, 2, 3], 4, [],],
            [[1, 2, 3], 0, [1, 2, 3],],
            [[1, 2, 3], 1, [2, 3],],
            [[1, 2, 3, 4], -1, [1, 2, 3],],
            [[1, 2, 3, 4], -2, [1, 2],],
            [[1, 2, 3, 4], -4, [],],
            [[1, 2, 3, 4], -5, [],],
        ];
    }

    #[DataProvider('provideSkipData')]
    public function testSkip($data, $n, $expected)
    {
        $this->assertEquals($expected, pipe($data, P::skip($n)));
    }

    public function testExplode()
    {
        $this->assertEquals(["1", "2", "3"], pipe("1,2,3", P::explode(",")));
        $this->assertEquals(["1", "2,3"], pipe("1,2,3", P::explode(",", 2)));
    }

    public function testImplode()
    {
        $this->assertEquals("1,2,3", pipe(["1", "2", "3"], P::implode(",")));
        $this->assertEquals("1,2,3", pipe(["1", "2", "3"], P::join(",")));
    }

    public function testSort()
    {
        $this->assertEquals([1, 2, 3], pipe([3, 2, 1], P::sort()));

        $actual = pipe([-2, 0, 3], P::sort(fn($a, $b) => ($a * -1) <=> ($b * -1)));
        $this->assertEquals([3, 0, -2], $actual);
    }

    public function testPush()
    {
        $this->assertEquals([1, 2, 3, 4, 5, 6], pipe([1, 2, 3], P::push(4, 5, 6)));
        $this->assertEquals([1, 2, 3, [4, 5, 6]], pipe([1, 2, 3], P::push([4, 5, 6])));
    }

    public function testAll()
    {
        $this->assertEquals(true, pipe([1, 2, 3], P::all(fn($i) => $i > 0)));
        $this->assertEquals(false, pipe([1, 2, 3], P::all(fn($i) => $i > 1)));
    }

    public function testAny()
    {
        $this->assertEquals(true, pipe([1, 2, 3], P::all(fn($i) => $i > 0)));
        $this->assertEquals(false, pipe([1, 2, 3], P::all(fn($i) => $i > 1)));
    }

    public function testNone()
    {
        $this->assertEquals(true, pipe([1, 2, 3], P::none(fn($i) => $i > 3)));
        $this->assertEquals(false, pipe([1, 2, 3], P::none(fn($i) => $i > 1)));
    }
}
