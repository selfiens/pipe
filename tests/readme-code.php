<?php

use Selfiens\Pipe\Pipe;

echo Pipe::pipe([' a ', ' b ', ' c '],
    fn($a) => array_map('trim', $a),
    fn($a) => array_map('strtoupper', $a),
    fn($a) => join("", $a),
);
// "ABC"