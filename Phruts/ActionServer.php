<?php
namespace Phruts;

class ActionServer
{
    protected $app;

    protected $processor;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function process(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Get the module config
        $this->getRequestProcessor($this->app['phruts.module_config_provider']->getModuleConfig($request))->process($request, $response);
    }

    protected function getModuleConfig(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->initModulePrefixes();
        // Check we have a module prefix/match
            // Digest the modules configs
            // Obtain the matching module config
                // Initialise the module config (e.g. all the datasource/plugins)
            // Return module config
    }

    /**
     * Add the modules (not including the default)
     */
    public function initModulePrefixes()
    {
        $prefixes = array();
        foreach($this->app[\Phruts\Globals::CONFIG] as $prefix => $config) {
            if(strlen($prefix) > 7) continue;
            $prefixes[] = substr($prefix, 7);
        }
        $this->app['phruts.module_prefixes'] = $prefixes;
    }


    /**
     * Instantiate the request processor if defined in the config
     * @param  \Phruts\Config\ModuleConfig $config
     * @return mixed
     * @throws \Exception
     */
    protected function getRequestProcessor(\Phruts\Config\ModuleConfig $config)
    {
        // Access from the dependency injector
        $key = \Phruts\Globals::REQUEST_PROCESSOR_KEY . $config->getPrefix();
        $processor = $this->app[$key];

        // If not found
        if (empty($processor)) {
            try {
                $processorClass = $config->getControllerConfig()->getProcessorClass();
                $processor = \Serphlet\ClassLoader::newInstance($processorClass, '\Phruts\RequestProcessor');

                // TODO: If supports DIC injection...

            } catch (\Exception $e) {
                throw new \Exception('Cannot initialize RequestProcessor of class ' . $processorClass . ': ' . $e->getMessage());
            }
            $processor->init($this, $config);
            $this->app[$key] = $processor;
        }

        return $this->app[$key];
    }

    protected function initPlugIns()
    {

    }

    protected function initDataSources()
    {

    }

}
