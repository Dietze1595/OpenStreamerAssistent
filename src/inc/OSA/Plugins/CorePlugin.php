<?php


namespace OSA\Plugins;


trait CorePlugin
{
    function isActivated(): bool
    {
        return true;
    }
    function isInstalled(): bool
    {
        return true;
    }
    function install(): bool
    {
        return false;
    }
    function deinstall(): bool
    {
        return false;
    }
    function activate(): bool
    {
        return true;
    }
    function deactivate(): bool
    {
        return false;
    }
}