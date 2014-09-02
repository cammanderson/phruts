<?php

namespace Phruts\Util;

/**
 * General purpose utility methods related to processing a servlet request
 * in the PHruts controller framework.
 *
 * @author Cameron MANDERSON <cameronmanderson@gmail.com> (Aloi Contributor)
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
class RequestUtils
{
    /**
	 * Commons Logging instance.
	 *
	 * @var Logger
	 */
    //$log = null;

    /**
	 * Select the module to which the specified request belongs, and add
	 * corresponding request attributes to this request.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param \Serphlet\Config\ServletContext $context The \Serphlet\Config\ServletContext for this
	 * web application
	 */
    public static function selectModule(\Symfony\Component\HttpFoundation\Request $request, \Serphlet\Config\ServletContext $context)
    {
        // Compute module name
        $prefix = self::getModuleName($request, $context);

        // Expose the resources for this module
        $config = $context->getAttribute(\Phruts\Globals::MODULE_KEY . $prefix);
        if (is_null($config)) {
            $request->removeAttribute(\Phruts\Globals::MODULE_KEY);
        } else {
            $request->setAttribute(\Phruts\Globals::MODULE_KEY, $config);
        }
        $resources = $context->getAttribute(\Phruts\Globals::MESSAGES_KEY . $prefix);
        if (is_null($resources)) {
            $request->removeAttribute(\Phruts\Globals::MESSAGES_KEY);
        } else {
            $request->setAttribute(\Phruts\Globals::MESSAGES_KEY, $resources);
        }
    }

    /**
	 * Get the module name to which the specified request belong.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param ServletContext $context The ServletContext for this
	 * web application
	 * @return string The module prefix or ""
	 */
    public static function getModuleName(\Symfony\Component\HttpFoundation\Request $request, \Serphlet\Config\ServletContext $context)
    {
        $path = $request->getPathInfo();
//        if (self::$log->isDebugEnabled()) {
//            self::$log->debug('Get module name for path "' . $path . '"');
//        }

        $prefixes = $context->getAttribute(\Phruts\Globals::PREFIXES_KEY);
        if (is_null($prefixes)) {
            $prefix = '';
        } else {
            $slashPosition = strrpos($path, '/');
            if ($slashPosition === false) {
                $prefix = '';
            } else {
                $prefix = substr($path, 0, $slashPosition);
                if (!in_array($prefix, $prefixes)) {
                    $prefix = '';
                }
            }
        }
//        if (self::$log->isDebugEnabled()) {
//            self::$log->debug('Module name found: ' . (($prefix == '') ? 'default' : $prefix));
//        }

        return $prefix;
    }

    /**
	 * Return the ModuleConfig object if it exists, null otherwise.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param \Serphlet\Config\ServletContext $context The \Serphlet\Config\ServletContext for this
	 * web application
	 * @return ModuleConfig The ModuleConfig object
	 */
    public static function getModuleConfig(\Symfony\Component\HttpFoundation\Request $request, \Serphlet\Config\ServletContext $context)
    {
        $moduleConfig = $request->getAttribute(\Phruts\Globals::MODULE_KEY);
        if (is_null($moduleConfig)) {
            $moduleConfig = $context->getAttribute(\Phruts\Globals::MODULE_KEY);
        }

        return $moduleConfig;
    }

    /**
	 * Create (if necessary) and return a \Phruts\Action\Form instance appropriate
	 * for this request.
	 *
	 * If no \Phruts\Action\Form instance is required, return null.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param \Phruts\Config\Action $mapping The action mapping for this request
	 * @param ModuleConfig $moduleConfig The configuration for this
	 * module
	 * @param ActionServlet $servlet The action servlet
	 * @return \Phruts\Action\Form Form instance associated with this
	 * request
	 * @todo Manage exception for ClassLoader::loadClass.
	 */
    public static function createActionForm(\Symfony\Component\HttpFoundation\Request $request, \Phruts\Config\Action $mapping, \Phruts\Config\ModuleConfig $moduleConfig, \Phruts\Action\Servlet $servlet)
    {
        // Is there a form bean associated with this mapping?
        $attribute = $mapping->getAttribute();
        if (is_null($attribute)) {
            return null;
        }

        // Look up the form bean configuration information to use
        $name = $mapping->getName();
        $config = $moduleConfig->findFormBeanConfig($name);
        if (is_null($config)) {
            return null;
        }

        // Look up any existing form bean instance
//        if (self::$log->isDebugEnabled()) {
//            self::$log->debug('  Looking for \Phruts\Action\Form bean instance in scope "' . $mapping->getScope() . '" under attribute key "' . $attribute . '"');
//        }
        $instance = null;
        $session = null;
        if ($mapping->getScope() == 'request') {
            $instance = $request->getAttribute($attribute);
        } else {
            \Serphlet\ClassLoader::loadClass($config->getType());

            $session = $request->getSession();
            $instance = $session->getAttribute($attribute);
        }

        // Can we recycle the existing form bean instance (if there is one)?
        if (!is_null($instance)) {
            $configClass = $config->getType();
            $instanceClass = get_class($instance);
            if (\Serphlet\ClassLoader::classIsAssignableFrom($configClass, $instanceClass)) {
//                if (self::$log->isDebugEnabled()) {
//                    self::$log->debug('  Recycling existing \Phruts\Action\Form instance' . ' of class "' . $instanceClass . '"');
//                }

                return $instance;
            }
        }

        // Create and return a new form bean instance
        try {
            $instance = \Serphlet\ClassLoader::newInstance($config->getType(), '\Phruts\Action\Form');
//            if (self::$log->isDebugEnabled()) {
//                self::$log->debug('  Creating new \Phruts\Action\Form instance of type "' . $config->getType() . '"');
//            }
            $instance->setServlet($servlet);
        } catch (\Exception $e) {
            $msg = $servlet->getInternal()->getMessage(null, 'formBean', $config->getType());
            //self::$log->error($msg . ' - ' . $e->getMessage());
        }

        return $instance;
    }

    /**
	 * Populate the properties of the specified PHPBean from the specified HTTP
	 * request, based on matching each parameter name (plus an optional prefix
	 * and/or suffix) against the corresponding JavaBeans "property setter"
	 * methods in the bean's class.
	 *
	 * If you specify a non-null prefix and non-null suffix, the parameter name
	 * must match <b>both</b> conditions for its value(s) to be used in populating
	 * bean properties.
	 *
	 * @param object $bean The PHPBean whose properties are to be set
	 * @param string $prefix The prefix (if any) to be prepend to bean property
	 * names when looking for matching parameters
	 * @param string $suffix The suffix (if any) to be appended to bean property
	 * names when looking for matching parameters
	 * @param \Symfony\Component\HttpFoundation\Request $request The HTTP request whose parameters
	 * are to be used to populate bean properties
	 * @throws \Serphlet\Exception - If an exception is thrown while setting
	 * property values
	 */
    public static function populate($bean, $prefix, $suffix, \Symfony\Component\HttpFoundation\Request $request)
    {
        $prefix = (string) $prefix;
        $suffix = (string) $suffix;
        $prefixLength = strlen($prefix);
        $suffixLength = strlen($suffix);

        // Build a list of revelant request parameters from this request
        $properties = array ();
        $names = $request->getParameterNames();
        foreach ($names as $name) {
            $stripped = $name;
            if ($prefix != '') {
                $subString = substr($stripped, 0, $prefixLength);
                if ($subString != $prefix) {
                    continue;
                }
                $stripped = substr($stripped, $prefixLength);
            }
            if ($suffix != '') {
                $subString = substr($stripped, -1, $suffixLength);
                if ($subString != $suffix) {
                    continue;
                }
                $stripped = substr($stripped, 0, strlen($stripped) - $suffixLength);
            }
            $properties[$stripped] = $request->getParameter($name);
        }

        // Set the corresponding properties of our bean
        try {
            \Phruts\Util\BeanUtils::populate($bean, $properties);
        } catch (\Exception $e) {
            throw new \Serphlet\Exception('\Phruts\Util\BeanUtils->populate() - ' . $e->getMessage());
        }
    }

    /**
	 * Returns the appropriate MessageResources object for the current module
	 * and the given bundle.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param \Serphlet\Config\ServletContext $context The \Serphlet\Config\ServletContext for this
	 * web application
	 * @param string $bundle The bundle name to look for. If this is null, the
	 * default bundle name is used
	 * @return MessageResources
	 * @todo If MessageResources is null throw Exception.
	 */
    public static function retrieveMessageResources(\Symfony\Component\HttpFoundation\Request $request, \Serphlet\Config\ServletContext $context, $bundle)
    {
        if (is_null($bundle)) {
            $bundle = \Phruts\Globals::MESSAGES_KEY;
        } else {
            $bundle = (string) $bundle;
        }
        $resources = $request->getAttribute($bundle);

        if (is_null($resources)) {
            $config = $request->getAttribute(\Phruts\Globals::MODULE_KEY);
            if (is_null($config)) {
                $prefix = '';
            } else {
                $prefix = $config->getPrefix();
            }
            $resources = $context->getAttribute(\Phruts\Globals::MESSAGES_KEY . $prefix);
        }

        return $resources;
    }

    /**
	 * Look up and return current user locale, based on the specified parameters.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @param string $locale Name of the session attribute for our user's
	 * Locale. If this is null, the default locale key is used for the
	 * lookup
	 * @return Locale
	 */
    public static function retrieveUserLocale(\Symfony\Component\HttpFoundation\Request $request, $locale = null)
    {
        if (is_null($locale)) {
            $locale = \Phruts\Globals::LOCALE_KEY;
        } else {
            $locale = (string) $locale;
        }
        $session = $request->getSession();
        $userLocale = $session->getAttribute($locale);

        if (is_null($userLocale)) {
            $userLocale = $request->getLocale();
        }

        return $userLocale;
    }

    /**
     * <p>Return <code>string</code> representing the scheme, server, and port
     * number of the current request. Server-relative URLs can be created by
     * simply appending the server-relative path (starting with '/') to this.
     * </p>
     *
     * @param \Symfony\Component\HttpFoundation\Request request The servlet request we are processing
     *
     * @return string URL representing the scheme, server, and port number of
     *                the current request
     * @since Struts 1.2.0
     */
    public static function requestToServerStringBuffer(\Symfony\Component\HttpFoundation\Request $request)
    {
        return self::createServerStringBuffer($request->getScheme(), $request->getServerName(), $request->getServerPort());
    }

    /**
     * <p>Return <code>StringBuffer</code> representing the scheme, server, and port number of
     * the current request.</p>
     *
     * @param scheme The scheme name to use
     * @param server The server name to use
     * @param port The port value to use
     *
     * @return string in the form scheme: server: port
     * @since Struts 1.2.0
     */
    public static function createServerStringBuffer($scheme, $server, $port)
    {
        $url = '';
        if ($port < 0) {
            $port = 80;
        }
        $url .= $scheme;
        $url .= "://";
        $url .= $server;
        if (($scheme == "http" && $port != 80) || ($scheme == "https" && $port != 443)) {
            $url .= ':';
            $url .= $port;
        }

        return $url;
    }

    /**
     * <p>Return <code>string</code> representing the scheme, server, and port
     * number of the current request.</p>
     *
     * @param scheme The scheme name to use
     * @param server The server name to use
     * @param port The port value to use
     * @param uri The uri value to use
     *
     * @return StringBuffer in the form scheme: server: port
     * @since Struts 1.2.0
     */
    public static function createServerUriStringBuffer($scheme, $server, $port, $uri)
    {
        $serverUri = self::createServerStringBuffer($scheme, $server, $port);
        $serverUri .= $uri;

        return $serverUri;

    }
}

//\Phruts\Util\RequestUtils::$log = Aloi_Util_Logger_Manager::getLogger('\Phruts\Util\RequestUtils');
