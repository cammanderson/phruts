<?php
namespace Phruts\Util\ModuleProvider;

interface ModuleProviderInterface
{
    public function getModuleConfig($prefix = '', $config);
}