<?php
namespace Phruts\Action;

use Phruts\Util\PropertyMessageResources;
use Phruts\Util\RequestUtils;
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * @var array
     */
    protected $dataSourceFactories;

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
     * @param int     $type    The type of the request
     *                         (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch   Whether to catch exceptions or not
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
        } catch (\Exception $e) {
            if (false === $catch) {
                throw $e;
            }

            return $this->handleException($e, $request, $type);
        }

    }

    /**
     * @param  \Exception $e
     * @param  Request    $request
     * @param $type
     * @return Response
     */
    protected function handleException(\Exception $e, Request $request, $type)
    {
        // Handle exception using internal messaging?
        if (!empty($this->log)) {
            $this->log->error($e->getMessage());
        }
        $response = new Response($this->getInternal()->getMessage(null, 'actionKernel.exception'), 501);

        return $response;
    }

    /**
     *
     * @param Request  $request
     * @param Response $response
     */
    protected function process(Request $request, Response $response)
    {
        $this->init();
        RequestUtils::selectModule($request, $this->application);
        $this->getRequestProcessor($this->getModuleConfig($request))->process($request, $response);
    }

    /**
     * Initialise the module configurations
     * @throws \Phruts\Exception
     */
    protected function init()
    {
        // Initialise once
        if($this->init) return;

        $prefixes = array();

        // Obtain the module config provider (implements caching, etc)
        if (empty($this->application[\Phruts\Util\Globals::MODULE_CONFIG_PROVIDER])) {
            throw new \Phruts\Exception($this->getInternal()->getMessage('', 'moduleConfig.provider.missing'));
        }
        $moduleConfigProvider = $this->application[\Phruts\Util\Globals::MODULE_CONFIG_PROVIDER];

        // Get the configured modules
        foreach ($this->application[\Phruts\Util\Globals::ACTION_KERNEL_CONFIG] as $prefixParam => $config) {
            // Only read in the config params
            if(strlen($prefixParam) < 6 || substr($prefixParam, 0, 6) != 'config') continue;

            // Strip the config element
            $prefix = preg_replace('#config/?#', '', $prefixParam);

            // Get the module config
            $moduleConfig = $moduleConfigProvider->getModuleConfig($prefix, $config);
            if (empty($moduleConfig)) {
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
     * @param  Request                     $request
     * @return \Phruts\Config\ModuleConfig
     */
    protected function getModuleConfig(Request $request)
    {
        $config = $request->attributes->get(\Phruts\Util\Globals::MODULE_KEY);
        if (empty($config)) {
            if (empty($this->application[\Phruts\Util\Globals::MODULE_KEY])) {
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

    protected function initModuleMessageResources(\Phruts\Config\ModuleConfig $config)
    {
        $mrcs = $config->findMessageResourcesConfigs();
        foreach ($mrcs as $mrc) {
            /* @var $mrc \Phruts\Config\MessageResourcesConfig */
            if (!empty($this->log)) {
                $this->log->debug('Initializing module "' . $config->getPrefix() . '" message resources from "' . $mrc->getParameter() . '"');
            }

            $factory = $mrc->getFactory();
            \Phruts\Util\MessageResourcesFactory::setFactoryClass($factory);
            $factoryObject = \Phruts\Util\MessageResourcesFactory::createFactory($factory);
            if (is_null($factoryObject)) {
                $msg = 'Cannot load resources from "' . $mrc->getParameter() . '"';
                if (!empty($this->log)) {
                    $this->log->error($msg);
                }
                throw new \Phruts\Exception($msg);
            }

            $resources = $factoryObject->createResources($mrc->getParameter());
            $resources->setReturnNull($mrc->getNull());
            $this->application[$mrc->getKey() . $config->getPrefix()] = $resources;
        }
    }

    protected function initModuleDataSources(\Phruts\Config\ModuleConfig $config)
    {
        if (!empty($this->log)) {
            $this->log->debug('Initialization module path "' . $config->getPrefix() . '" data sources');
        }

        $dscs = $config->findDataSourceConfigs();
        foreach ($dscs as $dsc) {
            /* @var $dsc \Phruts\Config\DataSourceConfig */
            if (!empty($this->log)) {
                $this->log->debug('Initialization module path "' . $config->getPrefix() . '" data source "' . $dsc->getKey() . '"');
            }

            try {
                \Phruts\Util\DataSourceFactory::setFactoryClass($dsc->getType());
                $dsFactory = \Phruts\Util\DataSourceFactory::createFactory($dsc);
            } catch (\Exception $e) {
                $msg = $this->getInternal()->getMessage(null, 'dataSource.init', $dsc->getKey());
                if (!empty($this->log)) {
                    $this->log->error($msg . ' - ' . $e->getMessage());
                }
                throw new \Phruts\Exception($msg);
            }
            $this->dataSourceFactories[$dsc->getKey() . $config->getPrefix()] = $dsFactory;
        }
    }

    protected function initModulePlugIns(\Phruts\Config\ModuleConfig $config)
    {
        if (!empty($this->log)) {
            $this->log->debug('Initializing module "' . $config->getPrefix() . '" plug ins');
        }

        $plugInConfigs = $config->findPlugInConfigs();
        $plugIns = array ();
        foreach ($plugInConfigs as $plugInConfig) {
            /* @var $plugInConfig \Phruts\Config\PlugInConfig */
            try {

                /* @var $plugIn \Phruts\Action\PlugInInterface */
                $plugIn = \Phruts\Util\ClassLoader::newInstance($plugInConfig->getClassName(), '\Phruts\Action\PlugInInterface');

                \Phruts\Util\BeanUtils::populate($plugIn, $plugInConfig->getProperties());
                $plugIn->init($this, $config);

                $plugIns[] = $plugIn;
            } catch (\Exception $e) {
                $msg = $this->getInternal()->getMessage(null, 'plugIn.init', $plugInConfig->getClassName());
                if (!empty($this->log)) {
                    $this->log->error($msg . ' - ' . $e->getMessage());
                }
                throw new \Phruts\Exception($msg);
            }
        }
        $this->application[\Phruts\Util\Globals::PLUG_INS_KEY . $config->getPrefix()] = $plugIns;
    }

    /**
     * Use for messaging
     * @return \Phruts\Util\MessageResources
     */
    public function getInternal()
    {
        return new PropertyMessageResources(__DIR__ . '/ActionResources');
    }

    /**
     * @param  Request  $request
     * @param $key
     * @return \Phruts\
     */
    public function getDataSource(Request $request, $key)
    {
        // Identify the current module
        $moduleConfig = \Phruts\Util\RequestUtils::getModuleConfig($request, $this->getApplication());

        // Return the requested data source instance
        $keyPrefixed = $key . $moduleConfig->getPrefix();
        $dataSource = $request->attributes->get($keyPrefixed);
        if (empty($dataSource)) {
            if (!array_key_exists($keyPrefixed, $this->dataSourceFactories)) {
                return null;
            }
            /** @var \Phruts\Util\DataSourceFactory $dsFactory */
            $dsFactory = $this->dataSourceFactories[$keyPrefixed];
            try {
                $dataSource = $dsFactory->createDataSource();
            } catch (\Exception $e) {
                throw $e;
            }
            $request->attributes->set($keyPrefixed, $dataSource);
        }

        return $dataSource;
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
