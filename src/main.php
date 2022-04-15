<?php

declare(strict_types=1);

error_reporting(E_ERROR | E_PARSE);

echo <<<TEXT

"Spread" array elements as arguments to functions.


TEXT;
function handle1(string $a, string $b, string $c): void
{
    echo "{$a} - {$b} - {$c}";
}

$data = ['foo', 'gist', 'alt'];

handle1(...$data);

echo <<<TEXT


Make variadic functions that collect the "rest" of the arguments into an array.


TEXT;
function handle2(string $first, string ...$listOfOthers): void
{
    echo "The first is: {$first}\n";

    foreach ($listOfOthers as $el) {
        echo "Followed by: {$el}\n";
    }
}

handle2(...$data);

echo <<<TEXT


A generic implementation of a static constructor for any class, wrapped in a trait so that it
can be used anywhere.

Notice the use of late static binding "new static()" for referencing the called class.


TEXT;
trait Makeable
{
    public static function make(...$args): static
    {
        return new static(...$args);
    }
}

class CustomerData
{
    use Makeable;

    public function __construct(public string $name, public string $email, public int $age)
    {
    }
}

$customerData = CustomerData::make('Joe', 'joe@email.com', 56);
echo "{$customerData->name}\n";
echo "{$customerData->email}\n";
echo "{$customerData->age}\n";
echo get_class($customerData);

echo <<<TEXT


Array spreading can also be used to combine arrays.
Only works if the input arrays have numeric keys exclusively - no textual keys allowed.


TEXT;
$arr1 = ['hey', 'bye', 87];
$arr2  = [60, 47.129];
$arr3 = [...$arr1, ...$arr2];
print_r($arr3);

echo <<<TEXT


Array destructuring is pulling elements out of arrays into variables.
Array destructuring allows you to skip elements.
Array destructuring can be used with arrays that have non-numerical keys.
Use cases include destructuring arrays returned by functions like pathinfo or parse_url.
You can use array destructuring in loops, e.g., two-dimensional array.


TEXT;
$arr = [67, 42, 90];
[$a, $b, $c] = $arr;
echo "{$a} - {$b} -{$c}\n";

list($d, $e, $f) = $arr;
echo "{$d} - {$e} -{$f}\n";

$arr = ['guten', 'tag', 'meine', 'Dame'];
[,,, $d] = $arr;
echo "{$d}\n";

$arr = ['a' => 77, 'b' => 475, 'c' => 20];
['c' => $c, 'a' => $a] = $arr;
echo "{$a} - {$c}\n";

['basename' => $file, 'dirname' => $directory,] = pathinfo('/usr/src/main.php');
echo "{$file} inside {$directory}\n";

$arr = [
    [
        'id' => 4,
        'name' => 'Jane',
    ],
    [
        'id' => 6,
        'name' => 'Joe',
    ]
];

foreach ($arr as ['name' => $name, 'id' => $id]) {
    echo "{$id}: {$name}\n";
}
