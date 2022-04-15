<?php

declare(strict_types=1);

error_reporting(E_ERROR | E_PARSE);

echo <<<TEXT


Alan Kay, inventor of the term "object-oriented programming", explains a critical problem
with OOP: we've taken a solution to a problem - OO code - we've scaled it by a factor of 100,
and expected it to work the same way. https://www.youtube.com/watch?v=oKg1hTOQXoY

Most of us learned OO in isolation with small examples and rarely at scale.
OOP is as good a tool as any other if used correctly.

Each object should be a little program on its own, with its own internal state.
Objects send messages between each other.

Classes and inheritance have nothing to do with OOP the way Alan Kay envisioned it.
Alan's vision only described objects - it did not explain how those objects were created.
Classes are not part of OOP's core idea. Inheritance has been abused to the point that
it has now earned a bad reputation. https://www.youtube.com/watch?v=OMPfEXIITVE


TEXT;

class PoemGeneratorV1
{
    public function generate(int $number): string
    {
        return "This is {$this->phrase($number)}.";
    }

    protected function phrase(int $number): string
    {
        $parts = $this->parts($number);

        return implode("\n      ", $parts);
    }


    protected function parts(int $number): array
    {
        return array_slice($this->data(), -$number, $number);
    }

    protected function data(): array
    {
        return [
            'the horse and the hound and the horn that belonged to',
            'the farmer sowing his corn that kept',
            'the rooster that crowed in the morn that woke',
            'the priest all shaven and shorn that married',
            'the man all tattered and torn that kissed',
            'the maiden all forlorn that milked',
            'the cow with the crumpled horn that tossed',
            'the dog that worried',
            'the cat that killed',
            'that rat that ate',
            'the malt that lay in',
            'the house that Jack built',
        ];
    }
}

$generator = new PoemGeneratorV1();
echo $generator->generate(4);

echo <<<TEXT


Another request: randomize the order of the phrases.


TEXT;

class RandomPoemGenerator extends PoemGeneratorV1
{
    protected function data(): array
    {
        $data = parent::data();

        shuffle($data);

        return $data;
    }
}

$randomPoemGenerator = new RandomPoemGenerator();
echo $randomPoemGenerator->generate(4);

echo <<<TEXT


Another request: repeat every line a second time.


TEXT;

class EchoPoemGenerator extends PoemGeneratorV1
{
    protected function parts(int $number): array
    {
        return array_reduce(
            parent::parts($number),
            fn (array $carry, string $item) => [...$carry, "{$item} {$item}"],
            []
        );
    }
}

$echoPoemGenerator = new EchoPoemGenerator();
echo $echoPoemGenerator->generate(4);

echo <<<TEXT


So far so good. However, another feature request comes in: one that combines both
random and echo behaviour. So which class should we extend?
If we extend PoemGenerator, we have to override both data() and parts(),
essentially copying code from the two subclasses.

We have fallen into the pitfall that is inheritance. Our code starts growing out of hand.

What changed during inheritance? In case of RandomPoemGenerator, it's data(). In case of
EchoPoemGenerator, it's parts(). Having to combine those two parts is what made our inheritance blow up.

data() and parts() are more than a protected implementation detail of our poem generator. They are the
essence of our program. With these two separate concerns identified, we can start extracting them into
separate entities.


TEXT;

/**
 * Whether lines should be randomized or not.
 */
interface Orderer
{
    public function order(array $data): array;
}

/**
 * How should it format the output: should it be echoed or not.
 */
interface Formatter
{
    public function format(array $lines): string;
}

class PoemGeneratorV2
{
    // Make our lives easier by proving defaults to $formatter and $orderer
    public function __construct(public ?Formatter $formatter = null, public ?Orderer $orderer = null)
    {
        $this->formatter ??= new DefaultFormatter();
        $this->orderer ??= new SequentialOrderer();
    }

    public function generate(int $number): string
    {
        return "This is {$this->phrase($number)}.";
    }

    protected function phrase(int $number): string
    {
        $parts = $this->parts($number);

        return $this->formatter->format($parts);
    }


    protected function parts(int $number): array
    {
        return array_slice($this->data(), -$number, $number);
    }

    protected function data(): array
    {
        return $this->orderer->order([
            'the horse and the hound and the horn that belonged to',
            'the farmer sowing his corn that kept',
            'the rooster that crowed in the morn that woke',
            'the priest all shaven and shorn that married',
            'the man all tattered and torn that kissed',
            'the maiden all forlorn that milked',
            'the cow with the crumpled horn that tossed',
            'the dog that worried',
            'the cat that killed',
            'that rat that ate',
            'the malt that lay in',
            'the house that Jack built',
        ]);
    }
}

class SequentialOrderer implements Orderer
{
    public function order(array $data): array
    {
        return $data;
    }
}

class RandomOrderer implements Orderer
{
    public function order(array $data): array
    {
        shuffle($data);

        return $data;
    }
}

class DefaultFormatter implements Formatter
{
    public function format(array $lines): string
    {
        return implode("\n        ", $lines);
    }
}

class EchoFormatter implements Formatter
{
    public function format(array $lines): string
    {
        $lines = array_reduce(
            $lines,
            fn (array $carry, string $item) => [...$carry, "{$item} {$item}"],
            []
        );

        return implode("\n        ", $lines);
    }
}

echo <<<TEXT
Having created our Formatter and Orderer implementations, we do not need
EchoPoemGenerator nor RandomPoemGenerator subclasses anymore.
That is the true power of OOP. By composing objects out of other objects,
we are able to make a flexible and durable solution.

This is composition over inheritance at work.


TEXT;

// Use named properties for passing arguments to constructor.
$generator = new PoemGeneratorV2(
    formatter: new EchoFormatter(),
    orderer: new RandomOrderer()
);
echo $generator->generate(4);
