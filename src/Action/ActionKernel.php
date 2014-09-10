<?php
namespace Phruts\Action;

use Phruts\Config\MessageResourcesConfig;
use Phruts\Util\PropertyMessageResources;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ActionKernel
 *
 * @package Phruts\Action
 */
class ActionKernel implements HttpKernelInterface
{
    /**
     * @var \Silex\Application
     */
    protected $application;

    /**
     * @var \Phruts\Action\RequestProcessor
     */
    protected $processor;

    public function __construct(\Silex\Application $application)
    {
        $this->application = $application;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int $type The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            $response = new Response();
            $this->process($request, $response);
            return $response;
        } catch(\Exception $e) {
            if (false === $catch) {
                throw $e;
            }

            return $this->handleException($e, $request, $type);
        }

    }

    /**
     * @param \Exception $e
     * @param Request $request
     * @param $type
     * @return Response
     */
    protected function handleException(\Exception $e, Request $request, $type)
    {
        // TODO: Handle exception using internal messaging?
        $response = new Response('An error occurred processing this request', 501);
        return $response;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     */
    protected function process(Request $request, Response $response)
    {
        // Get the module config
        $this->getRequestProcessor($this->getModuleConfig($request))->process($request, $response);
    }

    /**
     * @param Request $request
     * @return \Phruts\Config\ModuleConfig
     */
    protected function getModuleConfig(Request $request)
    {
        $this->initModulePrefixes();
        // Check we have a module prefix/match
            // Digest the modules configs
            // Obtain the matching module config
                // Initialise the module config (e.g. all the datasource/plugins)
            // Return module config
        return;
    }

    /**
     * Add the modules (not including the default)
     * @return void
     */
    public function initModulePrefixes()
    {
        $prefixes = array();
        foreach($this->application[\Phruts\Util\Globals::CONFIG] as $prefix => $config) {
            if(strlen($prefix) > 7) continue;
            $prefixes[] = substr($prefix, 7);
        }
        $this->application['phruts.module_prefixes'] = $prefixes;
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
        $key = \Phruts\Util\Globals::REQUEST_PROCESSOR_KEY . $config->getPrefix();
        $processor = $this->application[$key];

        // If not found
        if (empty($processor)) {
            try {
                $processorClass = $config->getControllerConfig()->getProcessorClass();
                $processor = \Phruts\Util\ClassLoader::newInstance($processorClass, '\Phruts\RequestProcessor');

                // TODO: If supports DIC injection...

            } catch (\Exception $e) {
                throw new \Exception('Cannot initialize RequestProcessor of class ' . $processorClass . ': ' . $e->getMessage());
            }
            $processor->init($this, $config);
            $this->application[$key] = $processor;
        }

        return $this->application[$key];
    }

    protected function initPlugIns()
    {

    }

    protected function initDataSources()
    {

    }

    /**
     * Use for messaging
     * @return \Phruts\Util\MessageResources
     */
    public function getInternal() {
        return new PropertyMessageResources(__DIR__ . '/ActionResources');
    }

    /**
     * @param Request $request
     * @param $key
     * @return \Phruts\
     */
    public function getDataSource(Request $request, $key)
    {
        
    }

    /**
     * @param \Silex\Application $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * @return \Silex\Application
     */
    public function getApplication()
    {
        return $this->application;
    }


}
