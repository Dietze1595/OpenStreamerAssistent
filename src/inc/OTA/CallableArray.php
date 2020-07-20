<?php
/**
 * Since PHP8 still does not allow typed arrays, in this example callable[], we create a Container class for this use case.
 */

namespace OTA;


use Countable;
use TypeError;
use Iterator;
use ArrayAccess;

class CallableArray implements ArrayAccess, Iterator, Countable
{
    /**
     * @var callable[] $container
     */
    private array $container = [];
    private int $position = 0;


    /**
     * @return callable
     */
    public function current() : callable
    {
        $keys = array_keys($this->container);
        return $this->container[$keys[$this->position]];
    }

    /**
     * @return int|void
     */
    public function next()
    {
        return ++$this->position;
    }

    /**
     * @return bool|float|int|mixed|string|null
     */
    public function key()
    {
        $keys = array_keys($this->container);
        return $keys[$this->position];
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $keys = array_keys($this->container);
        return isset($keys[$this->position]);
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return callable
     */
    public function offsetGet($offset) : callable
    {
        return $this->container[$offset];
    }

    /**
     * @param mixed $offset
     * @param callable $value
     */
    public function offsetSet($offset, $value)
    {
        if(!is_callable($value))
            throw new TypeError('value is not a callable');
        $this->container[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->container);
    }
}