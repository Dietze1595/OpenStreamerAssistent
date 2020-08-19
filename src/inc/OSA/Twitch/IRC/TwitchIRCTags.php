<?php


namespace OSA\Twitch\IRC;


class TwitchIRCTags implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var string[]
     */
    private array $container;
    private int $position;


    public function addData(array $data) {
        foreach($data as $k => $v) {
            $this[$k] = $v;
        }
    }

    public function __construct() {
        $this->container = [];
        $this->position = 0;
    }



    /**
     * @return string
     */
    public function current() : string
    {
        $keys = array_keys($this->container);
        return $this->container[$keys[$this->position]];
    }


    public function next() : int
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
    public function valid() : bool
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
    public function offsetExists($offset) : bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return string
     */
    public function offsetGet($offset) : string
    {
        return $this->container[$offset];
    }

    /**
     * @param mixed $offset
     * @param string $value
     */
    public function offsetSet($offset, $value)
    {
        if(!is_string($value))
            throw new \TypeError('value is not a string');
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
    public function count() : int
    {
        return count($this->container);
    }
}