<?php


namespace OTA;


use Exception;

class EventEmitter
{
    /**
     * @var CallableArray[]
     */
    private array $eventArray;

    /**
     * EventEmitter constructor.
     * @param string ...$eventNames
     */
    public function __construct(string ...$eventNames)
    {
        foreach ($eventNames as $name) {
            $this->eventArray[strtolower(trim($name))] = new CallableArray();
        }
    }

    /**
     * @param string $event
     * @param callable $callable
     * @throws Exception
     */
    public function on(string $event, callable $callable) {
        $name = strtolower(trim($event));
        if(!isset($this->eventArray[$name])) {
            throw new Exception('Event not found.');
        }
        $this->eventArray[$name][] = $callable;
    }

    /**
     * @param string $event
     * @param mixed ...$args
     * @throws Exception
     */
    public function emit(string $event, ...$args) {
        $name = strtolower(trim($event));
        if(!isset($this->eventArray[$name])) {
            throw new Exception('Event not found.');
        }

        foreach ($this->eventArray[$name] as $cb) {
            call_user_func($cb, $name, ...$args);
        }
    }
}