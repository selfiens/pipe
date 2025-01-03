# Minimalistic, PHP-First Pipe

https://github.com/selfiens/pipe

This package is a `pipe` implementation with the following aims:

- **Simple** signature. no "wrapper" objects.
- Based on **PHP-callable**.
- Optional and extensible **"Helper Methods"** (see below).

## Code Example

An example of simple string manipulation:

```php
$x = pipe(
    " hello, pipe world ",
    'trim',
    'strtolower'
    'ucfirst',
);
// $x === "Hello, Pipe World"
```

The function calling order in the `pipe` matches the data processing order,
unlike the plain PHP in which often requires intermediate variables or reversed function nesting.

```php
// The equivalent code in plain PHP:
ucfirst( strtolower( trim( " hello, pipe world " ) ) );
```

## Installation

Use [Composer](https://getcomposer.org) to install this package.
This package requires PHP 8.0 or later.

```shell
composer require selfiens/pipe
```

### Using Global `pipe` Function

The `pipe` function can be installed in the global(root) namespace.

```php
// define pipe() function in the root namespace
\Selfiens\Pipe\Pipe::installGlobal();

pipe(...);
```

Alternatively, you can load the global `pipe` function via `autoload.php`.
Add `"vendor/selfiens/pipe/src/pipe_global.php"` to the `autoload/files` section of your `composer.json`:

```json
{
  "autoload": {
    "files": [
      "vendor/selfiens/pipe/src/pipe_global.php"
    ]
  }
}
```

**Note**: You may need to run `composer dump`

## The `pipe` Function Signature

The first argument is the initial data, followed by callables.

```php
pipe(mixed $data, ...$callables): mixed
```

## Real-World Examples

Plain PHP code to extract initials:

```php
// How many seconds to grasp?
$init = implode(".", 
    array_map(
        fn($w) => substr($w, 0, 1),
        explode(" ", strtoupper("John Doe"))
    )
);
// $init === "J.D"
```

The pipe improves readability by easing cognitive load:

```php
$init = pipe(
    "John Doe",
    strtoupper(...),
    fn($s) => explode(" ", $s),
    fn($a) => array_map(fn($w) => substr($w, 0, 1), $a),
    fn($a) => implode(".", $a),
);
// $init === "J.D"
```

This package offers optional **Helper Methods** to further enhance code readability.

```php
use Selfiens\Pipe as P;

$init = pipe(
    "John Doe",
    strtoupper(...),
    P::explode(" "),
    P::map(fn($w) => substr($w, 0, 1)),
    P::implode("."),
);
```

## Behavior In Depth

Each callable's output becomes the next callable's input:

```php
$x = pipe(
    'x', 
    fn($s) => $s . 'y',     // $s='x'
    fn($s) => $s . 'z',     // $s='xy'
    fn($s) => $s . '0',     // $s='xyz'
);
// $x === 'xyz0'
```

```php
// no callables
pipe('my data'); // = 'my data'
```

### You can use any callable type supported by your PHP version.

```php
pipe(
    'The first arg is the initial data',
    // --- Example of data transformer callables ---
    'trim',                             // global function name
    ['MyClass', 'myMethod'],            // static method (PHP 5.0)
    'MyClass::myMethod',                // static method (PHP 5.2)
    function($s) { return trim($s) },   // closure (anonymous function) (PHP 5.3)
    new MyClass(),                      // when the `__invoke()` is implemented (PHP 5.3)
    [$myObject, 'myMethod'],            // instance method (PHP 5.4)
    [MyClass::class, 'myMethod'],       // static method (PHP 7.0)
    fn($s) => trim($s),                 // arrow function (PHP 7.4)
    trim(...),                          // first-class callable (PHP 8.1)
    MyClass::myMethod(...),             // static method, first-class callable (PHP 8.1)
    $myObject->myMethod(...),           // instance method, first-class callable (PHP 8.1)
);
```

## The Helper Methods

The `Pipe` class offers helper methods to simplify common array handling, such as map, filter.

```php
use Selfiens\Pipe as P;

P::pipe(
    [1,2,3,4,5],
    P::map(fn($i) => $i*2),     // [2,4,6,8,10]
    P::filter(fn($i) => $i<10), // [2,4,6,8]
    P::take(3),                 // [2,4,6]
); // [2,4,6]
```

### Predefined Helper Methods

These methods are predefined in the `Pipe` class.

| Method      | Signature                                                    | What it does                              | Behavior  | Note                |
|-------------|--------------------------------------------------------------|-------------------------------------------|-----------|---------------------|
| `tap`       | tap(callable $fn):mixed                                      | peek data, usually for logging            |           |                     |
| `map`       | map(callable $fn):array                                      | `array_map`                               | array I/O |                     |
| `filter`    | filter(callable $fn):array                                   | `array_filter`                            | array I/O |                     |
| `filterNot` | filterNot(callable $fn):array                                | inverse of `array_filter`                 | array I/O |                     |
| `column`    | column(string\|int\|null $col, string\|int\|null $key):array | `array_column`                            | array I/O |                     |
| `reduce`    | reduce(callable $fn, mixed $initial = null):mixed            | `array_reduce`                            |           |                     |
| `flat`      | flat():array                                                 | extract values from an array of any depth | array I/O |                     |
| `merge`     | merge(array ...$arrays):array                                | `array_merge`                             |           |                     |
| `take`      | take(int $n, bool $preserveKeys = false):array               | first N elements                          | array I/O | supports negative N |
| `skip`      | skip(int $n, bool $preserveKeys = false):array               | from Nth element                          | array I/O | supports negative N |
| `explode`   | explode(string $separator, int $limit = MAX):array           | `explode`                                 |           |                     |
| `implode`   | implode(string $separator = ''):string                       | `implode`                                 |           |                     |
| `join`      | join(string $separator = ''):string                          | `implode`                                 |           | alias of `implode`  |
| `sort`      | sort(?calalble $fn = null):array                             | `sort` or `ucsort($fn)`                   |           |                     |
| `push`      | push(mixed ...$values):array                                 | `array_push`                              |           |                     |
| `all`       | all(callable $fn):bool                                       | true when all elements satisfy $fn        |           |                     |
| `some`      | some(callable $fn):bool                                      | true when some elements satisfy $fn       |           |                     |
| `none`      | none(callable $fn):bool                                      | true when no elements satisfy $fn         |           |                     |

- `array I/O` Behavior: If `$data` isn’t an array, it’s converted to `[$data]`.

## Adding Custom Helper Methods

### Extending the `Pipe` class

Extend the `Pipe` class and add your custom methods:

```php
use Selfiens\Pipe as P;

class MyPipe extends P {

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

### Using `define` method

The `Pipe::define()` method adds custom helper methods directly to the `Pipe` class.

```php
use Selfiens\Pipe as P;

P::define('sum', function(): Closure {
    return static function (array $data): int {
        return \array_sum($data);
    }
});

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

```

## More Examples

See the examples folder for more examples.

## Tests

```shell
composer test
```