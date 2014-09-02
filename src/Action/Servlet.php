<?php

namespace Phruts\Action;

/**
 * ActionServlet represents the "controller" in the Model-View-Controller
 * (MVC) design pattern for web applications that is commonly known as
 * "Model 2".
 *
 * Generally, a "Model 2" application is architected as follows:
 * <ul>
 * <li>The user interface will generally be created with PHP pages, which
 *     will not themselves contain any business logic. These pages represent
 *     the "view" component of an MVC architecture.</li>
 * <li>Forms and hyperlinks in the user interface that require business logic
 *     to be executed will be submitted to a request URI that is mapped to the
 *     controller servlet.</li>
 * <li>There will be <b>one</b> instance of this servlet class,
 *     which receives and processes all requests that change the state of
 *     a user's interaction with the application. This component represents
 *     the "controller" component of an MVC architecture.</li>
 * <li>The controller servlet will select and invoke an action class to perform
 *     the requested business logic.</li>
 * <li>The action classes will manipulate the state of the application's
 *     interaction with the user, typically by creating or modifying PHPBeans
 *     that are stored as request or session attributes (depending on how long
 *     they need to be available). Such PHPBeans represent the "model"
 *     component of an MVC architecture.</li>
 * <li>Instead of producing the next page of the user interface directly,
 *     action classes will generally use the
 *     <samp>RequestDispatcher->forward</samp> facility of the servlet
 *     API to pass control to an appropriate PHP page to produce the next page
 *     of the user interface.</li>
 * </ul>
 *
 * <p>The standard version of ActionServlet implements the
 *    following logic for each incoming HTTP request. You can override
 *    some or all of this functionality by subclassing this servlet and
 *    implementing your own version of the processing.</p>
 * <ul>
 * <li>Identify, from the incoming request URI, the substring that will be
 *     used to select an action procedure.</li>
 * <li>Use this substring to map to the PHP class name of the corresponding
 *     action class (an implementation of the Action interface).
 *     </li>
 * <li>If this is the first request for a particular action class, instantiate
 *     an instance of that class and cache it for future use.</li>
 * <li>Optionally populate the properties of a \Phruts\Action\Form bean
 *     associated with this mapping.</li>
 * <li>Call the <samp>execute</samp> method of this action class, passing
 *     on a reference to the mapping that was used (thereby providing access
 *     to the underlying ActionServlet and ServletContext, as
 *     well as any specialized properties of the mapping itself), and the
 *     request and response that were passed to the controller.</li>
 * </ul>
 *
 * @author Cameron MANDERSON <cameronmanderson@gmail.com>
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) * @todo Add information in the class comment about the web deployment
 * descriptor "/WEB-INF/web.xml".
 * @todo Manage the possibility of subclassing the servlet controller.
 */
class Servlet extends \Serphlet\Http\Servlet
{
    /**
	 * Comma-separated list of context-relative path(s) to our configuration
	 * resource(s) for the default module.
	 *
	 * @var string
	 */
    protected $config = '/WEB-INF/phruts-config.xml';

    /**
	 * The digester used to produce ModuleConfig object from
	 * a PHruts configuration file.
	 *
	 * @var Digester
	 */
    protected $configDigester = null;

    /**
	 * The resources object for our internal resources.
	 *
	 * @var \Phruts\Util\PropertyMessageResources
	 */
    protected $internal = null;

    /**
	 * The PHP base name of our internal resources.
	 *
	 * @var string
	 */
    protected $internalName = '\Phruts\Action\ActionResources';

    /**
	 * Logging instance.
	 *
	 * @var Logger
	 */
    //$log = null;

    /**
	 * The RequestProcessor instance we will use to process all incoming
	 * requests.
	 *
	 * @var \Phruts\RequestProcessor
	 */
    protected static $processor = null;

    /**
	 * The factories data sources that has been configured for this module,
	 * if any.
	 *
	 * @var array
	 */
    protected $dataSourceFactories = array ();

    /**
	 * Set the default config prefix for the config rule set
	 */
    protected $configPrefix = 'phruts-config';

    public function __wakeup()
    {
//        if (is_null(self::$log)) {
//            self::$log = Aloi_Util_Logger_Manager::getLogger(__CLASS__);
//        }
    }

    /**
	 * @param ServletContext $context
	 */
    public function __construct()
    {
//        if (is_null(self::$log)) {
//            self::$log = Aloi_Util_Logger_Manager::getLogger(__CLASS__);
//        }
    }

    /**
	 * Return the MessageResources instance containing our internal message
	 * strings.
	 *
	 * @return \Phruts\Utils\MessageResources
	 */
    public function getInternal()
    {
        if (is_null($this->internal)) {
            $this->initInternal();
        }

        return $this->internal;
    }

    /**
	 * Initialize this servlet.
	 *
	 * Most of processing has been factored into support methods so that you can
	 * override particular functionality at a fairly granular level.
	 *
	 * @exception \Serphlet\Exception - If we cannot configure ourselves
	 * correctly
	 */
    public function init(\Serphlet\Config\ServletConfig $servletConfig)
    {
        try {
            // Set the config
            parent::init($servletConfig);

            $this->initInternal();
            $this->initServlet();

            // Initialize modules as needed
            $this->getServletContext()->setAttribute(\Phruts\Globals::ACTION_SERVLET_KEY, $this);

            // Determine the config specs
            $moduleConfigSpecs = array();
            $moduleConfigSpecs[] = array('prefix' => '', 'config' => $this->config);
            $names = $this->getServletConfig()->getInitParameterNames();
            $prefixes = array ();
            $configSet = false;
            if (!empty($names)) foreach ($names as $name) {
                if (substr($name, 0, 7) != 'config/')
                    continue;
                $prefix = substr($name, 6);
                $prefixes[] = $prefix;
                $moduleConfigSpecs[] = array('prefix' => $prefix, 'config' => $this->getServletConfig()->getInitParameter($name));
            }
            // Load the module configs
            $this->initModuleConfigs($moduleConfigSpecs);
            if (!empty ($prefixes)) {
                $this->getServletContext()->setAttribute(\Phruts\Globals::PREFIXES_KEY, $prefixes);
            }

            $this->configDigester = null;
        } catch (\Serphlet\Exception $e) {
            throw $e;
        }
    }

    private function initModuleConfigs($moduleConfigSpecs)
    {
        // Determine if any configs have changed
        $cacheExpired = false;
        foreach ($moduleConfigSpecs as $moduleConfigSpec) {
            $paths = split(',', $moduleConfigSpec['config']);
            foreach ($paths as $path) {
                if ($this->configurationExpired($path)) {
                    $cacheExpired = true;
                    break;
                }
            }
        }

        // Check the action
        if ($cacheExpired) {
            // We need to rebuild our config specs
            $moduleConfigs = array();
            foreach ($moduleConfigSpecs as $moduleConfigSpec) {
                $moduleConfig = $this->initModuleConfig($moduleConfigSpec['prefix'], $moduleConfigSpec['config']);
                $moduleConfig->freeze();
                $moduleConfigs[$moduleConfigSpec['prefix']] = $moduleConfig;
            }

            // Write the config cache
            $cacheFile = \Serphlet\Host::getRealPath(\Serphlet\Host::getCacheDirectory() . DIRECTORY_SEPARATOR . 'phruts.data');
            $serialData = serialize($moduleConfigs);
            if (is_writable(\Serphlet\Host::getCacheDirectory())) {
                file_put_contents($cacheFile, $serialData);
            }
        } else {
            // Load the configs
            $cacheFile = \Serphlet\Host::getRealPath(\Serphlet\Host::getCacheDirectory() . DIRECTORY_SEPARATOR . 'phruts.data');
            $serialData = file_get_contents($cacheFile);
            $moduleConfigs = unserialize($serialData);
        }

        foreach ($moduleConfigs as $prefix => $moduleConfig) {
            $this->getServletContext()->setAttribute(\Phruts\Globals::MODULE_KEY . $prefix, $moduleConfig);
            $this->initModuleMessageResources($moduleConfig);
            $this->initModuleDataSources($moduleConfig);
            $this->initModulePlugIns($moduleConfig);
        }
    }

    /**
	 * Determine if the config has changed more recently than our cached file
	 * @param unknown_type $config
	 */
    private function configurationExpired($config)
    {
        static $cacheTime;
        if (empty($cacheTime)) {
            $cachePath = \Serphlet\Host::getRealPath(\Serphlet\Host::getCacheDirectory() . DIRECTORY_SEPARATOR . 'phruts.data');
            if (!file_exists($cachePath)) {
                return true;
            }
            $cacheTime = filemtime($cachePath);
        }

        // Compare the cache file
        $filePath = \Serphlet\Host::getRealPath($config); // Pop the first
        $fileTime = filemtime($filePath);

        return $fileTime > $cacheTime;
    }

    /**
	 * Initialize our internal message resources bundle.
	 *
	 * @exception \Serphlet\Exception - If we cannot initialize these
	 * resources
	 */
    protected function initInternal()
    {
        // Create message resources
        $factory = \Phruts\Util\MessageResourcesFactory::createFactory();
        if (is_null($factory)) {
            $msg = 'Cannot load internal resources from "' . $this->internalName . '"';
            //self::$log->error($msg);
            throw new \Serphlet\Exception($msg);
        }

        $this->internal = $factory->createResources($this->internalName);
    }

    /**
	 * Initialize global characteristics of the controller servlet.
	 */
    protected function initServlet()
    {
        $value = $this->getServletConfig()->getInitParameter('config');
        if (!is_null($value)) {
            $this->config = $value;
        }
    }

    /**
	 * Initialize the application configuration information
	 * for the specified module.
	 *
	 * @param string $prefix Module prefix for this module
	 * @param string $paths Comma-separated list of context-relative resource
	 * path(s) for this module's configuration resource(s).
	 * @return ModuleConfig The new module configuration instance.
	 * @throws \Serphlet\Exception - If initialization cannot be performed
	 * @todo Check if $paths is empty.
	 */
    protected function initModuleConfig($prefix, $paths)
    {
//        if (self::$log->isDebugEnabled()) {
//            self::$log->debug('Initializing module "' . $prefix . '" configuration from "' . $paths . '"');
//        }

        // Parse the configuration for this module
        $config = new \Phruts\Config\ModuleConfig($prefix);

        // Configure the Digester instance we will use
        $digester = $this->initConfigDigester();

        // Process each specified resource path
        $temps = explode(',', $paths);
        foreach ($temps as $path) {
            $digester->push($config);
            try {
                $realPath = $this->getServletContext()->getRealPath($path);
                $digester->parse($realPath);
            } catch (\Exception $e) {
                $msg = $this->internal->getMessage(null, 'configParse', $paths);
                //self::$log->error($msg . ' - ' . $e->getMessage());
                throw new \Serphlet\Exception($msg);
            }
        }
//		$this->getServletContext()->setAttribute(\Phruts\Globals::MODULE_KEY . $prefix, $config);

        // Return the completed configuration object
        return $config;
    }

    /**
	 * Create (if needed) and return a new Digester instance that has been
	 * initialized to process PHruts module configuration file and
	 * configure a corresponding ModuleConfig object (which must be
	 * pushed on to the evaluation stack before parsing begins).
	 *
	 * @return Digester A new configured Digester instance.
	 */
    protected function initConfigDigester()
    {
        // Do we have an existing instance?
        if (!is_null($this->configDigester)) {
            return $this->configDigester;
        }

        // Obtain the configuration prefix (as optional parameter)
        $configPrefix = $this->getServletConfig()->getInitParameter('configPrefix');
        if(empty($configPrefix)) $configPrefix = $this->configPrefix;
        $this->configPrefix = $configPrefix;

        // Create a new Digester instance with standard capabilities
        $this->configDigester = new Aloi_Phigester_Digester();
        $this->configDigester->addRuleSet(new \Phruts\Config\ConfigRuleSet($this->configPrefix));

        return $this->configDigester;
    }

    /**
	 * Initialize the application message resources for the specified module.
	 *
	 * @param ModuleConfig $config ModuleConfig information for
	 * this module
	 * @exception \Serphlet\Exception - If initialization cannot be performed
	 */
    protected function initModuleMessageResources(\Phruts\Config\ModuleConfig $config)
    {
        $mrcs = $config->findMessageResourcesConfigs();
        foreach ($mrcs as $mrc) {
//            if (self::$log->isDebugEnabled()) {
//                self::$log->debug('Initializing module "' . $config->getPrefix() . '" message resources from "' . $mrc->getParameter() . '"');
//            }

            $factory = $mrc->getFactory();
            \Phruts\Util\MessageResourcesFactory::setFactoryClass($factory);
            $factoryObject = \Phruts\Util\MessageResourcesFactory::createFactory($factory);
            if (is_null($factoryObject)) {
                $msg = 'Cannot load resources from "' . $mrc->getParameter() . '"';
                //self::$log->error($msg);
                throw new \Serphlet\Exception($msg);
            }

            $resources = $factoryObject->createResources($mrc->getParameter());
            $resources->setReturnNull($mrc->getNull());
            $this->getServletContext()->setAttribute($mrc->getKey() . $config->getPrefix(), $resources);
        }
    }

    /**
	 * Initialize the data sources for the specified module.
	 *
	 * @param ModuleConfig $config ModuleConfig information for
	 * this module
	 * @throws \Serphlet\Exception - If initialization cannot be performed
	 */
    protected function initModuleDataSources(\Phruts\Config\ModuleConfig $config)
    {
//        if (self::$log->isDebugEnabled()) {
//            self::$log->debug('Initialization module path "' . $config->getPrefix() . '" data sources');
//        }

        $dscs = $config->findDataSourceConfigs();
        foreach ($dscs as $dsc) {
//            if (self::$log->isDebugEnabled()) {
//                self::$log->debug('Initialization module path "' . $config->getPrefix() . '" data source "' . $dsc->getKey() . '"');
//            }

            try {
                \Phruts\Util\DataSourceFactory::setFactoryClass($dsc->getType());
                $dsFactory = \Phruts\Util\DataSourceFactory::createFactory($dsc);

                //API::addInclude($dsc->getType());
            } catch (\Exception $e) {
                $msg = $this->internal->getMessage(null, 'dataSource.init', $dsc->getKey());
                //self::$log->error($msg . ' - ' . $e->getMessage());
                throw new \Serphlet\Exception($msg);
            }
            $this->dataSourceFactories[$dsc->getKey() . $config->getPrefix()] = $dsFactory;
        }
    }

    /**
	 * Initialize the plug ins for the specified module.
	 *
	 * @param ModuleConfig $config ModuleConfig information
	 * for this module
	 * @throws \Serphlet\Exception - If initialization cannot be performed
	 */
    protected function initModulePlugIns(\Phruts\Config\ModuleConfig $config)
    {
//        if (self::$log->isDebugEnabled()) {
//            self::$log->debug('Initializing module "' . $config->getPrefix() . '" plug ins');
//        }

        $plugInConfigs = $config->findPlugInConfigs();
        $plugIns = array ();
        foreach ($plugInConfigs as $plugInConfig) {
            try {
                $plugIn = \Serphlet\ClassLoader::newInstance($plugInConfig->getClassName(), '\Phruts\PlugIn');
                \Phruts\Util\BeanUtils::populate($plugIn, $plugInConfig->getProperties());
                $plugIn->init($this, $config);

                $plugIns[] = $plugIn;
            } catch (\Exception $e) {
                $msg = $this->internal->getMessage(null, 'plugIn.init', $plugInConfig->getClassName());
                //self::$log->error($msg . ' - ' . $e->getMessage());
                throw new \Serphlet\Exception($msg);
            }
        }
        $this->getServletContext()->setAttribute(\Phruts\Globals::PLUG_INS_KEY . $config->getPrefix(), $plugIns);
    }

    /**
	 * Perform the standard request processing for this request, and create
	 * the corresponding response.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The servlet response we are
	 * creating
	 * @throws \Serphlet\Exception
	 */
    protected function process(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Include the boot configuration for setting up modules
        \Phruts\Util\RequestUtils::selectModule($request, $this->getServletContext());
        try {
            $this->getRequestProcessor($this->getModuleConfig($request))->process($request, $response);
        } catch (\Exception $e) {
            throw new \Serphlet\Exception($e->getMessage());
        }
    }

    public function doGet(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $this->process($request, $response);
    }

    public function doPost(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $this->process($request, $response);
    }

    /**
	 * Return the module configuration object for the currently selected module.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @return ModuleConfig
	 */
    protected function getModuleConfig(\Symfony\Component\HttpFoundation\Request $request)
    {
        $config = $request->getAttribute(\Phruts\Globals::MODULE_KEY);
        if (is_null($config)) {
            $config = $this->getServletContext()->getAttribute(\Phruts\Globals::MODULE_KEY);
        }

        return $config;
    }

    /**
	 * Look up and return the RequestProcessor responsible for the
	 * specified module, creating a new one if necessary.
	 *
	 * @param ModuleConfig $config The module configuration for which
	 * to acquire and return a RequestProcessor.
	 * @return RequestProcessor
	 * @exception \Serphlet\Exception - If we cannot instantiate
	 * a RequestProcessor instance
	 */
    protected function getRequestProcessor(\Phruts\Config\ModuleConfig $config)
    {
        $key = \Phruts\Globals::REQUEST_PROCESSOR_KEY . $config->getPrefix();
        $processor = $this->getServletContext()->getAttribute($key);

        if (is_null($processor)) {
            try {
                $processorClass = $config->getControllerConfig()->getProcessorClass();
                $processor = \Serphlet\ClassLoader::newInstance($processorClass, '\Phruts\RequestProcessor');

//				API::addInclude($processorClass);
            } catch (\Exception $e) {
                throw new \Serphlet\Exception('Cannot initialize RequestProcessor of class ' . $processorClass . ': ' . $e->getMessage());
            }
            $processor->init($this, $config);
            $this->getServletContext()->setAttribute($key, $processor);
        }

        return $processor;
    }

    /**
	 * Return the specified data source for the current module.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param string $key The key specified in the <data-source> element for
	 * the requested data source
	 * @return object
	 * @throws Exception
	 * @todo Throws an exception if key doesn't correspond to a data source.
	 */
    public function getDataSource(\Symfony\Component\HttpFoundation\Request $request, $key)
    {
        // Identify the current module
        $moduleConfig = \Phruts\Util\RequestUtils::getModuleConfig($request, $this->getServletContext());

        // Return the requested data source instance
        $keyPrefixed = $key . $moduleConfig->getPrefix();
        $dataSource = $request->getAttribute($keyPrefixed);
        if (is_null($dataSource)) {
            if (!array_key_exists($keyPrefixed, $this->dataSourceFactories)) {
                return null;
            }
            $dsFactory = $this->dataSourceFactories[$keyPrefixed];
            try {
                $dataSource = $dsFactory->createDataSource();
            } catch (\Exception $e) {
                throw $e;
            }
            $request->setAttribute($keyPrefixed, $dataSource);
        }

        return $dataSource;
    }
}
