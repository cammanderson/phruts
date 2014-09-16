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

    protected $init = false;

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
        $this->init();
        $this->getRequestProcessor($this->getModuleConfig($request))->process($request, $response);
    }


    /**
     * Initialise the module configurations
     * @throws \Phruts\Exception
     */
    protected function init() {
        // Initialise once
        if($this->init) return;

        $prefixes = array();

        // Obtain the module config provider (implements caching, etc)
        if(empty($this->application[\Phruts\Util\Globals::MODULE_CONFIG_PROVIDER])) {
            throw new \Phruts\Exception($this->getInternal()->getMessage('', 'moduleConfig.provider.missing'));
        }
        $moduleConfigProvider = $this->application[\Phruts\Util\Globals::MODULE_CONFIG_PROVIDER];

        // Get the configured modules
        foreach($this->application[\Phruts\Util\Globals::ACTION_KERNEL_CONFIG] as $prefixParam => $config) {
            if(strlen($prefixParam) > 7 && substr($prefixParam, 0, 7) == 'config/') continue;
            $prefix = substr($prefixParam, 7);

            // Get the module config
            $moduleConfig = $moduleConfigProvider->getModuleConfig($prefix, $config);
            if(empty($moduleConfig)) {
                throw new \Phruts\Exception($this->getInternal()->getMessage('', 'moduleConfig.missing', $prefix));
            }
            $this->application[\Phruts\Util\Globals::MODULE_KEY . $prefix] = $moduleConfig;

            // Initialise the module config
            $this->initModuleMessageResources($moduleConfig);
            $this->initModuleDataSources($moduleConfig);
            $this->initModulePlugIns($moduleConfig);

            $prefixes[] = $prefix;
        }
        $this->application[\Phruts\Util\Globals::PREFIXES_KEY] = $prefixes;

        $this->init = true;
        return;
    }

    /**
     * @param Request $request
     * @return \Phruts\Config\ModuleConfig
     */
    protected function getModuleConfig(Request $request) {
        $config = $request->attributes->get(\Phruts\Util\Globals::MODULE_KEY);
        if (empty($config)) {
            if(empty($this->application[\Phruts\Util\Globals::MODULE_KEY])) {
                throw new \Phruts\Exception($this->getInternal()->getMessage('', 'moduleConfig.missing.default'));
            }
            $config = $this->application[\Phruts\Util\Globals::MODULE_KEY];
        }
        return $config;
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

        // If not found
        if (empty($this->application[$key])) {
            try {
                $processorClass = $config->getControllerConfig()->getProcessorClass();
                $processor = \Phruts\Util\ClassLoader::newInstance($processorClass, '\Phruts\Action\RequestProcessor');

                $processor->init($this, $config);
                $this->application[$key] = $processor;
                return $this->application[$key];
            } catch (\Exception $e) {
                throw new \Exception('Cannot initialize RequestProcessor of class ' . $processorClass . ': ' . $e->getMessage());
            }
        }

        return $this->application[$key];
    }

    protected function initModuleMessageResources($moduleConfig)
    {
        // TODO:
    }

    protected function initModuleDataSources($moduleConfig)
    {
        // TODO:
    }

    protected function initModulePlugIns($moduleConfig)
    {
        // TODO:
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
        // TODO:
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
