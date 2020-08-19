<?php


namespace OSA;


use Exception;

class EventEmitter
{
    /**
     * @var CallableArray[]
     */
    private array $eventArray;
    private bool $allowUnknownEvents = false;

    /**
     * EventEmitter constructor.
     * @param string ...$eventNames
     */
    public function __construct(string ...$eventNames)
    {
        $this->eventArray = [];
        foreach ($eventNames as $name) {
            $this->eventArray[strtolower(trim($name))] = new CallableArray();
        }
    }


    public function allowUnknownEvents() {
        $this->allowUnknownEvents = true;
    }
    public function disallowUnknownEvents() {
        $this->allowUnknownEvents = false;
    }

    /**
     * @param string $event
     * @param callable $callable
     * @throws Exception
     */
    public function on(string $event, callable $callable) {
        $name = strtolower(trim($event));
        if(!isset($this->eventArray[$name])) {
            if(!$this->allowUnknownEvents)
                throw new Exception('Event not found.');
            $this->eventArray[$name] = [];
        }
        $this->eventArray[$name]->add($callable);
    }

    /**
     * @param string $event
     * @param mixed ...$args
     * @throws Exception
     */
    public function emit(string $event, ...$args) {
        $name = strtolower(trim($event));
        if(!isset($this->eventArray[$name])) {
            if(!$this->allowUnknownEvents)
                throw new Exception('Event not found.');
            return;
        }

        foreach ($this->eventArray[$name] as $cb) {
            call_user_func($cb, $name, ...$args);
        }
    }
}