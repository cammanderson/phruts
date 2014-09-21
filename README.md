phruts
======
Modern PHP implementation of Struts in current PHP-FIG Standards

![Travis build status master](https://travis-ci.org/cammanderson/phruts.svg?branch=master)
[![Coverage Status](https://coveralls.io/repos/cammanderson/phruts/badge.png?branch=master)](https://coveralls.io/r/cammanderson/phruts?branch=master)

 * Run through Silex (DIC, Security, support building up of other services)
 * Uses Symfony HTTPFoundation/HTTPKernel
 * Implements PSR's (Naming Conventions, Logging, Autoloading)

Install
-------
Add to your composer
```
    "require": {
        "cammanderson/struts": "dev-master"
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

// Register phruts service provider
$app->register(new \Phruts\PhrutsServiceProvider(), array(
        // Register our modules and configs
        \Phruts\Util\Globals::ACTION_KERNEL_CONFIG => array(
            'config' => '../app/config/web-config.xml',
        )
    ));


// Add template handling, eg.. Twig
$app->get('{path}', function($path) use ($app) {
        return $app['twig']->render($path);
    })
    ->assert('path', '.+.html.twig');

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
Note: Create your ```app/cache``` folder (this is a config option)


Prelim Dev Stages
-----------------
 * Initial port of Struts 1.2+ into PSR (underway)
 * Code Coverage 35%+ (done)
 * Finalise ActionKernel for mounting on Silex\Application (underway)
 * Silex bootstrap with example
 * Getting started documentation
 * Code Coverage 70%+
 * Port include 1.3.10 source