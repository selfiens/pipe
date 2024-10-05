# Companion Methods

This package provides a few unary methods that are frequently used in a typical `pipe` processing.

## How to use

The example code uses class alias for brevity.

Companion methods are in the `Pipe` class, and are all static methods.

```php
use Selfiens\Pipe as P;

P::pipe(
    "Hello, World",
    P::explode(" "),
    P::map(fn($w) => substr($w, 0, 1)),
    P::implode(" "),
);
// 
```

## The Predefined Methods List

| Method    | Signature                                          | What it does                                    | Behavior  | Note                |
|-----------|----------------------------------------------------|-------------------------------------------------|-----------|---------------------|
| `tap`     | tap(callable $fn):mixed                            | peek data, usually for logging                  |           |                     |
| `map`     | map(callable $fn):array                            | `array_map`                                     | array I/O |                     |
| `filter`  | filter(callable $fn):array                         | `array_filter`                                  | array I/O |                     |
| `reduce`  | reduce(callable $fn, mixed $initial = null):mixed  | `array_reduce`                                  |           |                     |
| `flat`    | flat():array                                       | flatten a multi-dimensional array into 1D array | array I/O |                     |
| `merge`   | merge(array ...$arrays):array                      | `array_merge`                                   |           |                     |
| `take`    | take(int $n, bool $preserveKeys = false):array     | first N elements                                | array I/O | supports negative N |
| `skip`    | skip(int $n, bool $preserveKeys = false):array     | last N elements                                 | array I/O | supports negative N |
| `explode` | explode(string $separator, int $limit = MAX):array | `explode`                                       |           |                     |
| `implode` | implode(string $separator = ''):string             | `implode`                                       |           |                     |
| `join`    | join(string $separator = ''):string                | `implode`                                       |           | alias of `implode`  |
| `sort`    | sort(?calalble $fn = null):array                   | `sort` or `ucsort($fn)`                         |           |                     |
| `push`    | push(mixed ...$values):array                       | `array_push`                                    |           |                     |
| `all`     | all(callable $fn):bool                             | true when all elements satisfy $fn              |           |                     |
| `some`    | some(callable $fn):bool                            | true when some elements satisfy $fn             |           |                     |
| `none`    | none(callable $fn):bool                            | true when no elements satisfy $fn               |           |                     |

### Behavior

- `array I/O`: if input `$data` is not an array, it will become `[$data]`.


## Adding User-Defined Methods

### Extending the `Pipe` class
```php

use Selfiens\Pipe as P;

class MyPipe extends P {
    /**
     *
     */
    public statuc function sum(): Closure
    {
        return static function (array $data): int {
            return \array_sum($data);
        }
    }
    
    public static function mapRemainder(inf $n): Closure
    {
        return static function (array $data) use ($n): array
        {
            return \array_map(fn($x) => $x % $n, $data);
        }
    }
}

```