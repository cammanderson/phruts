<?php
namespace Phruts;

/**
 * Class PhrutsServiceProvider
 * @author Cameron Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
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
        $app[\Phruts\Globals::ACTION_KERNEL] = $app->share(function() use ($app) {
            return new ActionKernel($app);
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