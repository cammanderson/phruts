<?php
namespace Phruts\Utils;

interface ModuleProviderInterface
{
    public function getModuleConfig($prefix = '', $config);
}