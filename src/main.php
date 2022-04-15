<?php
declare(strict_types=1);

error_reporting(E_ERROR | E_PARSE);

echo <<<TEXT

"Scalar" types are string, bool, int and float.
Parameter types and return types.

TEXT;
function formatMoney(int $money): string
{
    return sprintf('R$%.2f', $money);
}

print formatMoney(54);

echo <<<TEXT

Typed properties for classes.

TEXT;
class Offer
{
    public string $offerNumber;
    public Money $totalPrice;
}

class Money
{
    public int $amount;
}

$money = new Money();

echo <<<TEXT

The following would throw fatal error.
If amount did not have a type, it would be null. Since it has a type, its value is uninitialized.
Trying to access an uninitialized property will throw fatal error.

TEXT;
// var_dump($money->amount);

echo  <<<TEXT

You are allowed to write to an uninitialized property before reading it.

TEXT;
$money->amount = 100;
echo $money->amount;


class Bicycle
{
    public $make;
}

$bicycle = new Bicycle();
echo <<<TEXT

unset on untyped property will make it null.

TEXT;
unset($bicycle->make);
echo gettype($bicycle->make);

class Motorcycle
{
    public function __construct(public string $make)
    {}
}
$m = new Motorcycle('Honda');
echo "\n{$m->make}";

echo <<<TEXT

unset on typed property will make it uninitialized.

TEXT;
unset($m->make);

echo <<<TEXT

Type validation occurs when setting a typed property, so you can be sure
an invalid type will not be assigned. The following will throw fatal error.

TEXT;
// $m = new Motorcycle(856);


class Date
{
    public int $timestamp;
    /*
    Static constructor
    */
    public static function now(): self
    {
        $d = new Date();
        $d->timestamp = (new \DateTime())->getTimestamp();

        return $d;
    }

    public function format(): string
    {
        return (new \DateTime('now', new \DateTimeZone('America/Sao_Paulo')))
            ->setTimestamp($this->timestamp)
            ->format('d/m/Y H:i');
    }
}

/*
$paymentDate can either be Date or null.
$paymentDate is also initialized with null, ensuring the value is never uninitialized.
*/
class Invoice
{
    public ?Date $paymentDate = null;

    public function getPaymentDate(): ?Date
    {
        return $this->paymentDate;
    }
}

$invoice = new Invoice();

/* Before 7.0 */
echo isset($invoice->paymentDate) ? $invoice->paymentDate->timestamp : 'No payment date.';

echo <<<TEXT

Using the null coalescing operator.
It performs isset on its left-hand operand. If it returns false, return right-hand operand.

TEXT;
$date = $invoice->paymentDate->timestamp ?? 'No payment date.';
echo "\n{$date}";

echo <<<TEXT

7.4: Null coalescing assignment operator.

TEXT;
$temporaryPaymentDate = $invoice->paymentDate ??= Date::now();
echo "\n{$temporaryPaymentDate->format()}";

echo <<<TEXT

Commom use of null coalescing operator: memoization function that stores a result once it's calculated.

TEXT;
function match_pattern(string $input, string $pattern) {
    static $cache = [];

    return $cache[$input][$pattern] ??= (function(string $input, string $pattern) {
        preg_match($pattern, $input, $matches);

        return $matches[0];
    })($input, $pattern);
}

$match = match_pattern('hello world', '/world/') ?? 'Nothing found';
echo "\n{$match}";

echo <<<TEXT

The following will throw an error. Null coalescing operator does not work with method calls on null.

TEXT;
// $invoice->paymentDate = null;
// echo $invoice->paymentDate->format() ?? null;

echo <<<TEXT

Use the nullsafe operator, which will only perform method calls when possible and otherwise return null.

TEXT;
$invoice->paymentDate = null;
$result = $invoice->getPaymentDate()?->format();
echo gettype($result);

echo <<<TEXT

Consider the null object pattern.
Instead of one Exam class that is responsible for the exam date,
split the logic in separate classes that implement the interface Exam
and that implement their own logic for the exam date.

TEXT;

interface Exam
{
    public function getExamDate(): Date;
}

echo <<<TEXT

The null is replaced with an actual object.
Instead of a "date or null", it's a "date or unknown date".
Instead of an "exam with a state", it's a "pending exam or scheduled exam".

In PendingExam, we changed the getExamDate() signature (return type is UnknownDate).
This is known as type variance (>= 7.4).

TEXT;
class PendingExam implements Exam
{
    public function getExamDate(): UnknownDate
    {
        return new UnknownDate();
    }
}

class ScheduledExam implements Exam
{
    public function __construct(protected Date $examDate)
    {}

    public function getExamDate(): Date
    {
        return $this->examDate;
    }
}

class UnknownDate extends Date
{
    public function format(): string
    {
        return '/';
    }
}