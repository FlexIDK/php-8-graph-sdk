<?php

namespace One23\GraphSdk\GraphNodes;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use One23\GraphSdk\MapTypeTrait;

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    use MapTypeTrait;

    public function __construct(protected array $items = [])
    {
    }

    /**
     * Gets the value of a field from the Graph node.
     */
    public function getField(string $name, mixed $default = null): mixed
    {
        return $this->items[$name] ?? $default;
    }

    /**
     * Returns a list of all fields set on the object.
     */
    public function getFieldNames(): array
    {
        return array_keys($this->items);
    }

    /**
     * Get all of the items in the collection.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Run a map over each of the items.
     */
    public function map(\Closure $callback): static
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Get an item at a given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * Set the item at a given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        }
        else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->items[$offset])) {
            unset($this->items[$offset]);
        }
    }

    /**
     * Convert the collection to its string representation.
     */
    public function __toString(): string
    {
        return $this->asJson();
    }

    /**
     * Get the collection of items as JSON.
     */
    public function asJson(int $options = 0): string
    {
        return json_encode($this->asArray(), $options);
    }

    /**
     * Get the collection of items as a plain array.
     */
    public function asArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Collection ? $value->asArray() : $value;
        }, $this->items);
    }
}
