<?php


namespace OTA;


class JSONConfig
{
    private string $filepath;
    private array $data;

    /**
     * @param string $path
     * @return static
     * @throws \Exception
     */
    public static function get(string $path) : static {
        static $CACHE = [];

        if(!file_exists($path)) {
            throw new \Exception('json file does not exist');
        }
        $path = realpath($path);
        if(isset($CACHE[$path])) {
            return $CACHE[$path];
        }
        $config = new static($path);
        $CACHE[$path] = $config;
        return $config;
    }
    private function __construct(string $path) {
        if(!file_exists($path)) {
            throw new \Exception('json file does not exist');
        }
        $data = file_get_contents($path);
        try {
            $this->data = json_decode($data, true);
            $this->filepath = $path;
        } catch (\Exception $ex) {
            throw new \Exception('Error decoding json from: '.$path);
        }
    }

    public function save() {
        file_put_contents($this->filepath, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    public function __get($name) {
        return $this->data[$name];
    }
    public function __set($name, $value) {
        if($this->data[$name]??null === $value) return;
        $this->data[$name] = $value;
        DEBUG_LOG(sprintf('changing config %s: %s = %s', $this->filepath, $name, $value));
        $this->save();
    }
    public function __isset($name) {
        return isset($this->data[$name]);
    }
    public function __unset($name) {
        unset($this->data[$name]);
    }
}