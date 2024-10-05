<?php
/**
 * Load `pipe` function into global(root) namespace
 */

\Selfiens\Pipe\Pipe::installGlobal();

$x = pipe(
    "123",
    str_split(...),
    array_reverse(...),
);
assert($x == ["3", "2", "1"]);