<?php

namespace Selfiens\Pipe;

function map(array $array, callable $callable): array
{
    return \array_map($callable, $array);
}

class Pipe
{
    /**
     * Add pipe function to global namespace
     */
    public static function install(): void
    {
        require_once __DIR__.'/pipe_global.php';
    }

    /**
     * @param  callable  ...$transformers
     */
    public static function pipe(mixed $data, ...$transformers): mixed
    {
        return array_reduce(
            $transformers,
            fn($data, $transformer) => $transformer($data),
            $data
        );
    }
}