<?php

use Selfiens\Pipe\Pipe as P;

require_once __DIR__.'/../vendor/autoload.php';

$debug = function ($x) {
    echo json_encode($x)."\n";
};

P::define('even', function (): Closure {
    return function (array $data): array {
        return P::pipe(
            $data,
            P::filter(fn($i) => ($i % 2) == 0),
            P::values()
        );
    };
});

P::define('odd', function (): Closure {
    return function (array $data): array {
        return P::pipe(
            $data,
            P::filter(fn($i) => ($i % 2) != 0),
            P::values()
        );
    };
});

P::pipe(
    [1, 2, 3, 4, 5, 6, 7, 8, 9],
    P::even(),
    P::tap($debug),
    P::map(fn($i) => $i + 1),
    P::tap($debug),
    P::odd(),
    P::tap($debug),
);