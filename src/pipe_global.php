<?php

use Selfiens\Pipe\Pipe;

if (!\function_exists('\\pipe')) {
    function pipe(mixed $data, mixed ...$fns): mixed
    {
        return Pipe::pipe($data, ...$fns);
    }
}
