<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain;

use ArrayIterator;
use Canalizador\Shared\Shared\Domain\Exceptions\InvalidCollectionType;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Countable;
use IteratorAggregate;

abstract class Collection implements Countable, IteratorAggregate
{
    /* @throws InvalidCollectionType */
    public function __construct(protected array $items)
    {
        $type = $this->type();

        foreach ($items as $item) {
            if (!$item instanceof $type) {
                throw InvalidCollectionType::create();
            }
        }
    }

    abstract protected function type(): string;

    public function add($item): void
    {
        $this->items[] = $item;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items());
    }

    public function count(): int
    {
        return count($this->items());
    }

    public function items(): array
    {
        return $this->items;
    }

    public static function empty(): static
    {
        return new static([]);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->items);
    }

    public function findOrNull(IntegerId $id): ?object
    {
        foreach ($this->items as $item) {
            if (method_exists($item, 'id') && $item->id()->equals($id)) {
                return $item;
            }
        }

        return null;
    }

    public function first(): ?object
    {
        return $this->items()[0] ?? null;
    }

    public function last(): ?object
    {
        $items = $this->items();

        return end($items) ?: null;
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->items());
    }
}
