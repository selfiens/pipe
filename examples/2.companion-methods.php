<?php

/**
 * Demonstrates companion methods
 */

use Selfiens\Pipe\Pipe as P;

require_once __DIR__.'/../vendor/autoload.php';

$debug = function ($x) {
    echo json_encode($x)."\n";
};

$result = P::pipe(
    "12345",
    str_split(...),
    P::tap($debug), // ["1","2","3","4","5"]
    P::map(fn($i) => (int)$i),
    P::tap($debug), // [1,2,3,4,5]
    P::map(fn($i) => $i * 2),
    P::tap($debug), // [2,4,6,8,10]
    P::filter(fn($i) => $i < 7),
    P::tap($debug), // [2,4,6]
    P::reduce(fn($carry, $i) => $carry + $i, 0),
    P::tap($debug), // 12
    fn($n) => [[[[$n]]]],
    P::tap($debug), // [[[[12]]]]
    P::flat(),
    P::tap($debug), // [12]
    P::merge([24, 36], [48, 60]),
    P::tap($debug), // [12,24,36,48,60]
    P::take(4),
    P::tap($debug), // [12,24,36,48]
    P::skip(1),
    P::tap($debug), // [24,36,48]
    P::push(6),
    P::tap($debug), // [24,36,48,6]
    P::sort(),
    P::tap($debug), // [6,24,36,48]
    P::tap(fn($x) => $debug(P::pipe($x, P::all(fn($i) => $i >= 36)))), // false
    P::tap(fn($x) => $debug(P::pipe($x, P::any(fn($i) => $i >= 36)))), // true
    P::tap(fn($x) => $debug(P::pipe($x, P::none(fn($i) => $i >= 36)))), // false
    P::implode(","),
    P::tap($debug), // "6,24,36,48"
);

echo var_export($result, true);