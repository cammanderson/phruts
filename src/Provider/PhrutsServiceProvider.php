<?php
namespace Phruts\Provider;

use Phigester\Digester;
use Phruts\Config\ConfigRuleSet;
use Phruts\Util\ModuleProvider\FileCacheModuleProvider;

/**
 * Class PhrutsServiceProvider
 * @package Phruts
 */
class PhrutsServiceProvider implements \Silex\ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param \Silex\Application $app An Application instance
     */
    public function register(\Silex\Application $app)
    {
        // Register our action server
        $app[\Phruts\Util\Globals::ACTION_KERNEL] = $app->share(function () use ($app) {
            return new \Phruts\Action\ActionKernel($app);
        });

        // Register our digester for when we need it
        $app[\Phruts\Util\Globals::DIGESTER] = $app->share(function () use ($app) {
            $digester = new Digester();
            $digester->addRuleSet(new ConfigRuleSet('phruts-config'));

            return $digester;
        });

        $app[\Phruts\Util\Globals::MODULE_CONFIG_PROVIDER] = $app->share(function () use ($app) {
                $provider = new FileCacheModuleProvider($app);

                // Set the cache
                $cache = $app[\Phruts\Util\Globals::CACHE_DIR];
                if(empty($cache)) $cache = getcwd() . '/../app/cache/';
                $provider->setCachePath($cache);

            return $provider;
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(\Silex\Application $app)
    {
    }
}
