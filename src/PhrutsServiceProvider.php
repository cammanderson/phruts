<?php
namespace Phruts;

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
        $app[\Phruts\Util\Globals::ACTION_KERNEL] = $app->share(function() use ($app) {
            return new \Phruts\Action\ActionKernel($app);
        });

        // Register our digester for when we need it
        $app[\Phruts\Util\Globals::DIGESTER] = $app->share(function() use ($app) {
            return new \Phigester\Digester();
        });

        $app[\Phruts\Util\Globals::MODULE_CONFIG_PROVIDER] = $app->share(function() use ($app) {
            $provider = new FileCacheModuleProvider($app);
            $provider->setCachePath(getcwd() . '/../app/cache/');
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