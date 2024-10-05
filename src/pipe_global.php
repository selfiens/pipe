<?php

use Selfiens\Pipe\Pipe;

if (!\function_exists('\\pipe')) {
    function pipe(mixed $data, ...$callables): mixed
    {
        return Pipe::pipe($data, ...$callables);
    }
}
