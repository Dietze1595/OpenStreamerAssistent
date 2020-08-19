<?php


namespace OSA\Plugins;


use OSA\Database\PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PluginSystem
{
    /**
     * @var Plugin[]
     */
    private static array $plugins = [];

    public static function checkInstallPluginTable() {

        $installed = PDO::getInstance()->tableExists('plugin_installation');
        if($installed) return;
        $db = PDO::getInstance()->get();
        $db->query('CREATE TABLE `plugin_installation` (
            `classname` VARCHAR(100),
            `data` TEXT,
            PRIMARY KEY(`classname`)
        )');
        DEBUG_LOG('CREATING TABLE: plugin_installation');
    }

    public static function init() : void {

        go(function() {
            self::checkInstallPluginTable();

            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT . '/inc/Plugins/'));
            $files = array();

            foreach ($rii as $file) {
                if ($file->isDir()) {
                    continue;
                }
                $files[] = $file->getPathname();
            }


            array_map(function (string $filename) {
                $filename = str_replace(ROOT . '/inc/', '', $filename);
                $className = str_replace('/', '\\', substr($filename, 0, -4));
                if (class_exists($className)) {
                    if (is_subclass_of($className, Plugin::class)) {
                        DEBUG_LOG('Load plugin: ' . $className);
                        $plugin = new $className();
                        self::$plugins[] = $plugin;
                    } else {
                        DEBUG_LOG($className . ' is not a plugin');
                    }
                }
            }, array_filter($files, fn($file) => str_ends_with($file, '.php')));

            DEBUG_LOG('Loaded ' . count(self::$plugins) . ' Plugins.');


            foreach(self::$plugins as $p) {
                if($p->isActivated()) {
                    $p->onActivated();
                }
            }
        });
    }

    /**
     * @return Plugin[]
     */
    public static function getPlugins(): array
    {
        return self::$plugins;
    }
}