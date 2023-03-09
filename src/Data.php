<?php

declare(strict_types=1);

namespace Dojo;

use Dojo\Exception\Exception;
use Laminas\Json\Json;

use ArrayAccess;
use Iterator;
use Countable;
use Traversable;

use function array_key_exists;
use function count;
use function is_array;
use function is_object;
use function is_numeric;
use function next;
use function reset;


class Data implements ArrayAccess,Iterator,Countable
{
    /**
     * Identifier field of item
     * @var string|int $identifier
     */
    protected $identifier;

    /**
     * Collected items
     * @var array $items
     */
    protected $items = [];

    /**
     * Label field of item
     * @var string $label
     */
    protected $label;

    /**
     * Data container metadata
     * @var array $metadata
     */
    protected $metadata = [];

    /**
     * Constructor
     *
     * @param  string|null $identifier
     * @param  array|Traversable|null $items
     * @param  string|null $label
     * @return void
     */
    public function __construct(?string $identifier = null, $items = null, ?string $label = null)
    {
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $items) {
            $this->setItems($items);
        }
        if (null !== $label) {
            $this->setLabel($label);
        }
    }

    /**
     * Set the items to collect
     *
     * @param array|Traversable $items
     */
    public function setItems(array|Traversable $items): self
    {
        $this->clearItems();
        return $this->addItems($items);
    }

    /**
     * Set an individual item, optionally by identifier (overwrites)
     *
     * @param  array|object $item
     * @param  string|null $identifier
     */
    public function setItem(array|object $item, ?string $id = null): self
    {
        $item = $this->normalizeItem($item, $id);
        $this->items[$item['id']] = $item['data'];
        return $this;
    }

    /**
     * Add an individual item, optionally by identifier
     *
     * @param  array|object $item
     * @param  string|null $id
     */
    public function addItem(array|object $item, ?string $id = null): self
    {
        $item = $this->normalizeItem($item, $id);

        if ($this->hasItem($item['id'])) {
            throw new Exception('Overwriting items using addItem() is not allowed');
        }

        $this->items[$item['id']] = $item['data'];

        return $this;
    }

    /** Add multiple items at once */
    public function addItems(array|Traversable $items): self
    {
        if (!is_array($items) && (!is_object($items) || !($items instanceof Traversable))) {
            throw new Exception('Only arrays and Traversable objects may be added to ' . __CLASS__);
        }
        $this->setMetadata('totalLength', $this->count());

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Get all items as an array
     *
     * Serializes items to arrays.
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /** Does an item with the given identifier exist? */
    public function hasItem(string|int $id): bool
    {
        return array_key_exists($id, $this->items);
    }

    /**
     * Retrieve an item by identifier
     *
     * Item retrieved will be flattened to an array.
     *
     * @param  string|int $id
     */
    public function getItem(string|int $id): array
    {
        if (!$this->hasItem($id)) {
            return null;
        }
        return $this->items[$id];
    }

    /**
     * Remove item by identifier
     *
     * @param  string $id
     */
    public function removeItem(string|int $id): self
    {
        if ($this->hasItem($id)) {
            unset($this->items[$id]);
        }
        return $this;
    }

    /**
     * Remove all items at once
     */
    public function clearItems(): self
    {
        $this->items = [];
        return $this;
    }


    /**
     * Set identifier for item lookups
     *
     * string|int|null $identifier
     */
    public function setIdentifier(mixed $identifier): self
    {
        if (null === $identifier) {
            $this->identifier = null;
        } elseif (is_string($identifier)) {
            $this->identifier = $identifier;
        } elseif (is_numeric($identifier)) {
            $this->identifier = (int) $identifier;
        } else {
            throw new Exception('Invalid identifier; please use a string or integer');
        }

        return $this;
    }

    /**
     * Retrieve current item identifier
     */
    public function getIdentifier(): string|int|null
    {
        return $this->identifier;
    }


    /**
     * Set label to use for displaying item associations
     *
     * @param  string|null $label
     */
    public function setLabel(string|null $label): self
    {
        if (null === $label) {
            $this->label = null;
        } else {
            $this->label = (string) $label;
        }
        return $this;
    }

    /**
     * Retrieve item association label
     *
     * @return string|null
     */
    public function getLabel(): string|null
    {
        return $this->label;
    }

    /**
     * Set metadata by key or en masse
     *
     * @param  string|array $spec
     * @param  mixed $value
     */
    public function setMetadata(string|array $spec, $value = null): self
    {
        if (is_string($spec) && (null !== $value)) {
            $this->metadata[$spec] = $value;
        } elseif (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->setMetadata($key, $value);
            }
        }
        return $this;
    }

    /**
     * Get metadata item or all metadata
     *
     * @param  null|string $key Metadata key when pulling single metadata item
     */
    public function getMetadata(?string $key = null): mixed
    {
        if (null === $key) {
            return $this->metadata;
        }

        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        return null;
    }

    /**
     * Clear individual or all metadata item(s)
     *
     * @param  null|string $key
     */
    public function clearMetadata($key = null): self
    {
        if (null === $key) {
            $this->metadata = [];
        } elseif (array_key_exists($key, $this->metadata)) {
            unset($this->metadata[$key]);
        }
        return $this;
    }

    /**
     * Load object from array
     */
    public function fromArray(array $data): self
    {
        if (array_key_exists('identifier', $data)) {
            $this->setIdentifier($data['identifier']);
        }
        if (array_key_exists('label', $data)) {
            $this->setLabel($data['label']);
        }
        if (array_key_exists('items', $data) && is_array($data['items'])) {
            $this->setItems($data['items']);
        } else {
            $this->clearItems();
        }
        return $this;
    }

    /**
     * Load object from JSON
     *
     * @param  string $json
     */
    public function fromJson(string $json): self
    {
        if (!is_string($json)) {
            throw new Exception('fromJson() expects JSON input');
        }
        $data = Json::decode($json);
        return $this->fromArray($data);
    }

    /**
     * Seralize entire data structure, including identifier and label, to array
     */
    public function toArray(): array
    {
        if (null === ($identifier = $this->getIdentifier())) {
            throw new Exception('Serialization requires that an identifier be present in the object; first call setIdentifier()');
        }

        $array = array(
            'identifier' => $identifier,
            'items'      => array_values($this->getItems()),
        );

        $metadata = $this->getMetadata();
        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $array[$key] = $value;
            }
        }

        if (null !== ($label = $this->getLabel())) {
            $array['label'] = $label;
        }

        return $array;
    }

    /**
     * Serialize to JSON (dojo.data format)
     */
    public function toJson(): string
    {
        return Json::encode($this->toArray());
    }

    /**
     * Serialize to string (proxy to {@link toJson()})
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * ArrayAccess: does offset exist?
     */
    public function offsetExists(mixed $offset): bool
    {
        return (null !== $this->getItem($offset));
    }

    /**
     * ArrayAccess: retrieve by offset
     *
     * @param  string|int $offset
     */
    public function offsetGet($offset): array
    {
        return $this->getItem($offset);
    }

    /**
     * ArrayAccess: set value by offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setItem($value, $offset);
    }

    /**
     * ArrayAccess: unset value by offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeItem($offset);
    }

    /**
     * Iterator: get current value
     */
    public function current(): mixed
    {
        return current($this->items);
    }

    /**
     * Iterator: get current key
     *
     * return string|int
     */
    public function key(): mixed
    {
        return key($this->items);
    }

    /**
     * Iterator: advance the pointer
     */
    public function next(): void
    {
        next($this->items);
    }

    /**
     * Iterator: rewind to first value in collection
     */
    public function rewind(): void
    {
        reset($this->items);
    }

    /**
     * Iterator: is item valid?
     */
    public function valid(): bool
    {
        return (bool) $this->current();
    }

    /**
     * Countable: how many items are present
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Normalize an item to attach to the collection
     *
     * @param  array|object $item
     * @param  string|int|null $id
     */
    protected function normalizeItem(array|object $item, mixed $id): array
    {
        if (null === ($identifier = $this->getIdentifier())) {
            throw new Exception('You must set an identifier prior to adding items');
        }

        if (!is_object($item) && !is_array($item)) {
            throw new Exception('Only arrays and objects may be attached');
        }

        if (is_object($item)) {
            if (method_exists($item, 'toArray')) {
                $item = $item->toArray();
            } else {
                $item = get_object_vars($item);
            }
        }

        if ((null === $id) && !array_key_exists($identifier, $item)) {
            throw new Exception('Item must contain a column matching the currently set identifier');
        } elseif (null === $id) {
            $id = $item[$identifier];
        } else {
            $item[$identifier] = $id;
        }

        return array(
            'id'   => $id,
            'data' => $item,
        );
    }
}
