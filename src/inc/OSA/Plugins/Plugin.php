<?php


namespace OSA\Plugins;


use OSA\Database\PDO;
use OSA\Webserver\Webserver;

abstract class Plugin
{


    private static array $self = [];

    private array $installConfig;



    protected function addRoute(string $route, callable $cb)
    {
        Webserver::getInstance()->addRoute($route, \Closure::fromCallable($cb));
    }

    public abstract function update(float $from);
    public abstract function getVersion() : float;
    protected final function setVersion(float $version) {
        $this->installConfig['version'] = $version;
        $this->saveInstallConfig();
    }

    private function getInstallationConfig(): array
    {
        $defaultConfig = [
            'installed' => 'false',
            'activated' => 'false',
            'version'   => 0
        ];

        $db = PDO::getInstance()->get();
        $stmt = $db->prepare('SELECT * FROM `plugin_installation` WHERE `classname` = :cn');
        $stmt->bindValue(':cn', get_class($this));
        $stmt->execute();
        $cnf = $stmt->rowCount() === 0 ? $defaultConfig : json_decode($stmt->fetch(\PDO::FETCH_ASSOC)['data'], true);
        PDO::getInstance()->put($db);
        return $cnf + $defaultConfig;
    }

    public static function getInstance() : static {
        if(isset(static::$self[static::class]))
            return static::$self[static::class];
        static::$self[static::class] = new static();
        return static::$self[static::class];
    }

    protected function __construct()
    {
        $this->installConfig = $this->getInstallationConfig();
        while($this->getVersion() > $this->installConfig['version']) {
            DEBUG_LOG('Update for '.$this->getPluginName().' ('.$this->installConfig['version'].'/'.$this->getVersion().')');
            $this->update($this->installConfig['version']);
        }
    }

    abstract public function getPluginName(): string;

    abstract public function getAuthorName(): string;

    abstract public static function checkPluginDependencies(): bool;

    private function saveInstallConfig()
    {
        $db = PDO::getInstance()->get();
        $stmt = $db->prepare('REPLACE INTO `plugin_installation` SET `classname` = :cn, `data` = :data');
        $stmt->bindValue(':cn', get_class($this));
        $stmt->bindValue(':data', json_encode($this->installConfig));
        $stmt->execute();
        PDO::getInstance()->put($db);
    }

    public function install(): bool
    {
        if ($this->isInstalled()) return false;
        $this->installConfig['installed'] = true;
        $this->saveInstallConfig();
        return true;
    }

    public function deinstall(): bool
    {
        if ($this->isActivated()) return false;
        $this->installConfig['installed'] = false;
        $this->installConfig['activated'] = false;
        $this->saveInstallConfig();
        return true;
    }

    abstract function onActivated(): void;

    abstract function onDeactivated(): void;

    public function activate(): bool
    {
        if($this->isActivated()) return false;
        if (static::checkPluginDependencies()) {
            $this->installConfig['activated'] = true;
            $this->saveInstallConfig();
            $this->onActivated();
            return true;
        }

        return false;
    }

    public function deactivate(): bool
    {
        if(!$this->isActivated()) return false;
        $this->installConfig['activated'] = false;
        $this->saveInstallConfig();
        $this->onDeactivated();
        return true;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isInstalled() && $this->installConfig['activated'];
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->installConfig['activated'];
    }
}