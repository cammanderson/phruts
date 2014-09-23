phruts
======
PHP implementation of Struts in current framework interoperability group standards.

![Travis build status master](https://travis-ci.org/cammanderson/phruts.svg?branch=master)
[![Coverage Status](https://coveralls.io/repos/cammanderson/phruts/badge.png?branch=master)](https://coveralls.io/r/cammanderson/phruts?branch=master)

 * Run through Silex (e.g. get Dependency Injection, dispatcher, security, cache, esi, etc and support building up of other services)
 * Uses Symfony HTTPFoundation/HTTPKernel
 * Implements PSR's (Naming Conventions, Logging, Autoloading)
 * Use Twig or your other preference dependencies

What is Phruts?
---------------
Phruts is based on Apache Struts, a Java MVC framework. Your controllers are configured through an XML file, which then also controls application flow and forwarding. Some parts are OK, some parts are much better done by other frameworks.

Install
-------
Add to your composer and do a ```composer update```
```
"require": {
    "cammanderson/phruts": "dev-master"
}
```

Add to your Silex application
-----------------------------
```
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

// Use standard HttpFoundation
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Create a silex application
$app = new Silex\Application();

// Add in Twig as the template handler
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/../app/Resources/views'))
    ->register(new Silex\Provider\MonologServiceProvider(), array('monolog.logfile' => __DIR__.'/../app/logs/development.log'));

// Add in phruts to organise your controllers
$app->register(new \Phruts\PhrutsServiceProvider(), array(
        // Register our modules and configs
        \Phruts\Util\Globals::ACTION_KERNEL_CONFIG => array(
            'config' => '../app/config/web-config.xml', // Supports multiple modules/configurations
        )
    ));

// Add template handling, eg.. Twig
$app->get('{path}', function($path) use ($app) {
        return $app['twig']->render($path);
    })
    ->assert('path', '.+\.twig');

// Add routes to be matched by Phruts
$app->get('{path}', function (Request $request) use ($app) {
        return $app[\Phruts\Util\Globals::ACTION_KERNEL]->handle($request, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST, false);
    })
    ->assert('path', '.*')
    ->value('path', '/')
```

Configure your MVC config
-------------------------
Create your app/config/web-config.xml
```
<?xml version="1.0" encoding="UTF-8"?>
<phruts-config>
    <global-exceptions>
        <exception type="\Exception"
                   key="some.exception.key"
                   path="error.html.twig"/>
    </global-exceptions>

    <global-forwards>
        <forward name="welcome" path="/welcome.html.twig"/>
    </global-forwards>

    <action-mappings>
        <action path="/"
                type="\Phruts\Actions\ForwardAction"
                parameter="welcome"/>
    </action-mappings>
</phruts-config>
```

Extra things:
 * Create your ```app/cache``` folder (this is a config option)
 * Create a twig template ```app/Resources/views/welcome.html.twig```

Feature support:
 * Error handlers
 * Global forwards
 * Request Processor based on roles defined by Symfony Security
 * Action forwards (internal action chaining)
 * Wildcard path matching
 * Action switching and multiple module support
 * Multiple module config merging (e.g. multi config.xml)
 * Struts 1.2 API

Current Development Status
--------------------------
 * COMPLETE: Code Coverage 35%+
 * COMPLETE: Code Coverage 75%+
 * COMPLETE: Finalise ActionKernel for mounting on Silex\Application
 * UNDERWAY: Confirm Struts 1.2 code coverage for framework features
 * UNDERWAY: Write the standard Struts style Twig extensions for accessing form+messages
 * TODO: Documentation on working with Silex
 * Consider Struts Validation framework (we never used it, so might not make it forward)
 * Struts 1.3 framework features (for convenience techniques only)

Why do this? Best way to learn techniques in web development is through studying Request->Response web frameworks. I have a soft-spot for the old Struts application, and we have legacy PHPMVC code which we would like to easily move forward. This project makes it easy for us to port that legacy code and modernise it fast by implementing PSR's, giving that code base a future supported life.