<?php
namespace Phruts\Action;

/**
 * RequestProcessor contains the processing logic that the PHruts
 * controller kernel performs as it receives each kernel request.
 *
 * You can customize the request processing behavior by subclassing this
 * class and overriding the method(s) whose behavior you are
 * interested in changing.
 *
 */
class RequestProcessor
{
    const INCLUDE_kernel_PATH = 'Phruts_Include.actionserver_path';
    const INCLUDE_PATH_INFO = 'Phruts_Include.path_info';

    /**
	 * Commons Logging instance.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
    private $log = null;

    /**
	 * The set of Action instances that have been created and initialized,
	 * keyed by the fully qualified PHP class name of the Action class.
	 *
	 * @var array
	 */
    protected $actions = array ();

    /**
	 * The ModuleConfiguration we are associated with.
	 *
	 * @var \Phruts\Config\ModuleConfig
	 */
    protected $moduleConfig = null;

    /**
	 * The controller kernel we are associated with.
	 *
	 * @var \Phruts\Action\ActionKernel
	 */
    protected $actionKernel = null;

//    public function __wakeup()
//    {
//    }

    final public function __construct()
    {
    }

    /**
	 * Return the MessageResources instance containing our internal message
	 * strings.
	 *
	 * @return \Phruts\Util\MessageResources
	 */
    protected function getInternal()
    {
        return $this->actionKernel->getInternal();
    }

    /**
	 * Initialize this request processor instance.
	 *
	 * @param \Phruts\Action\ActionKernel $actionKernel The ActionKernel we are
	 * associated with
	 * @param \Phruts\Config\ModuleConfig $moduleConfig The \Phruts\Config\ModuleConfig we are
	 * associated with
	 * @todo Actions initializations?
	 */
    public function init(\Phruts\Action\ActionKernel $actionKernel, \Phruts\Config\ModuleConfig $moduleConfig)
    {
        $this->actions = array ();
        $this->actionKernel = $actionKernel;
        $this->moduleConfig = $moduleConfig;
    }

    /**
	 * Process a \Symfony\Component\HttpFoundation\Request and create the corresponding
	 * \Symfony\Component\HttpFoundation\Response.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The server request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The server response we are
	 * creating
	 */
    public function process(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        try {
            // Identify the path component we will use to select a mapping
            $path = $this->processPath($request, $response);
            if (is_null($path)) {
                return;
            }
            if (!empty($this->log)) {
                $this->log->debug('Processing a "' . $request->getMethod() . '" for path "' . $path . '"');
            }

            // Select a Locale for the current user if requested
            $this->processLocale($request, $response);

            // Set the content type and no-caching headers if requested
            $this->processContent($request, $response);
            $this->processNoCache($request, $response);

            // General purpose preprocessing hook
            if (!$this->processPreprocess($request, $response)) {
                return;
            }

            //Identify the mapping for this request
            $mapping = $this->processMapping($request, $response, $path);
            if (is_null($mapping)) {
                return;
            }

            // Check for any role required to perform this action
            if (!$this->processRoles($request, $response, $mapping)) {
                return;
            }

            // Process any ActionForm bean related to this request
            $form = $this->processActionForm($request, $response, $mapping);
            $this->processPopulate($request, $response, $form, $mapping);
            if (!$this->processValidate($request, $response, $form, $mapping)) {
                return;
            }

            // Process a forward or include specified by this mapping
            if (!$this->processForward($request, $response, $mapping)) {
                return;
            }
            if (!$this->processInclude($request, $response, $mapping)) {
                return;
            }

            // Create or acquire the Action instance to process this request
            $action = $this->processActionCreate($request, $response, $mapping);
            if (is_null($action)) {
                return;
            }

            // Call the Action instance itself
            $forward = $this->processActionPerform($request, $response, $action, $form, $mapping);

            // Process the returned ActionForward instance
            $this->processForwardConfig($request, $response, $forward);
        } catch (\Phruts\Exception $e) {
            throw $e;
        }
    }

    /**
	 * Identify and return the path component (from the request URI) that
	 * we will use to select a \Phruts\Config\ActionConfig to dispatch with.
	 *
	 * If no such path can be identified, create an error response
	 * and return null.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @return string
	 */
    protected function processPath(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // For prefix matching, match on the path info (if any)
//        $path = (string) $request->attributes->get(self::INCLUDE_PATH_INFO);
//        if ($path == null) {
            $path = $request->getPathInfo();
//        }
//        if (($path != null) && (strlen($path) > 0)) {
//            return ($path);
//        }

        // For extension matching, strip the module prefix and extension
//        $path = (string) $request->attributes->get(self::INCLUDE_KERNEL_PATH);
//        if ($path == null) {
//            $path = $request->getKernelPath();
//        }
        $prefix = $this->moduleConfig->getPrefix();
        if (substr($path, 0, strlen($prefix)) != $prefix) {
            $msg = $this->getInternal()->getMessage("processPath", $request->getRequestURI());
            //$this->log->error($msg);
            $response->setStatusCode(400);
            $response->setContent($msg);

            return null;
        }

        // TODO: Add back in support for kernel path
        $path = substr($path, strlen($prefix));
        $period = strrpos($path, ".");
        if (($period >= 0) && $period !== false) {
            $path = substr($path, 0, $period);
        }

        return ($path);
    }

    /**
	 * Automatically select a Locale for the current user, if requested.
	 *
	 * <b>NOTE</b> - configuring Locale selection will trigger the creation
	 * of a new \Symfony\Component\HttpFoundation\Session\Session if necessary.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 */
    protected function processLocale(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Are we configured to select the Locale automatically?
        if (!$this->moduleConfig->getControllerConfig()->getLocale()) {
            return;
        }

        // Has a Locale already been selected?
        $session = $request->getSession();
        if (empty($session) || !is_null($session->get(\Phruts\Util\Globals::LOCALE_KEY))) {
            return;
        }

        // Use the Locale returned by the system (if any)
        $locale = $request->getLocale();
        if (!is_null($locale)) {
            if (!empty($this->log)) {
                $this->log->debug('  Setting user locale "' . (string) $locale . '"');
            }
            $session->set(\Phruts\Util\Globals::LOCALE_KEY, $locale);
        }
    }

    /**
	 * Set the default content type (with optional character encoding) for
	 * all responses if requested.
	 *
	 * <b>NOTE</b> - This header will be overridden automatically if a
	 * <samp>RequestDispatcher->doForward</samp> call is ultimately
	 * invoked.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 */
    protected function processContent(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $contentType = $this->moduleConfig->getControllerConfig()->getContentType();
        if ($contentType != '') {
            $response->setContent($contentType);
        }
    }

    /**
	 * Ask our exception handler to handle the exception.  Return the
	 * <code>ActionForward</code> instance (if any) returned by the
	 * called <code>ExceptionHandler</code>.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are processing
	 * @param \Symfony\Component\HttpFoundation\Response response The kernel response we are processing
	 * @param \Exception exception The exception being handled
	 * @param \Phruts\Action\AbstractActionForm form The ActionForm we are processing
	 * @param \Phruts\Config\ActionConfig mapping The ActionMapping we are using
	 *
	 * @return \Phruts\Action\ActionForward
	 * @exception IOException if an input/output error occurs
	 * @exception kernelException if a kernel exception occurs
	 */
    protected function processException(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Exception $exception, $form, \Phruts\Config\ActionConfig $mapping)
    {
        // Is there a defined handler for this exception?
        $config = $mapping->findExceptionConfig(get_class($exception)); // ExceptionConfig
        if ($config == null) {
            // Check the module config for a global exception
            if (!empty($this->log)) {
                $this->log->debug($this->getInternal()->getMessage(null, 'nonactionException', get_class($exception)));
            }
            $config = $mapping->getModuleConfig()->findExceptionConfig(get_class($exception));
        }

        if ($config == null) {
            // There is no configuration for this exception
            if (!empty($this->log)) {
                $this->log->debug($this->getInternal()->getMessage(null, 'unhandledException', get_class($exception)));
            }
            // Throw the error
            throw $exception;
        }

        // Use the configured exception handling
        try {
            $handler = \Phruts\Util\ClassLoader::newInstance($config->getHandler(), '\Phruts\Action\ExceptionHandler'); //ExceptionHandler

            return ($handler->execute($exception, $config, $mapping, $form, $request, $response));
        } catch (\Exception $e) {
            throw new \Phruts\Exception($e);
        }
    }

    /**
	 * Set the no-cache headers for all responses, if requested.
	 *
	 * <b>NOTE</b> - This header will be overridden automatically if a
	 * <samp>RequestDispatcher->doForward</samp> call is ultimately
	 * invoked.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 */
    protected function processNoCache(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        if ($this->moduleConfig->getControllerConfig()->getNocache()) {
            $response->headers->add('Pragma', 'No-cache');
            $response->headers->set('Cache-Control', 'no-cache');
            $response->expire();
        }
    }

    /**
	 * General-purpose preprocessing hook that can be overridden as required
	 * by subclasses.
	 *
	 * Return true if you want standard processing to continue, or false if the
	 * response has already been completed. The default implementation does
	 * nothing.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @return boolean
	 */
    protected function processPreprocess(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        return true;
    }

    /**
	 * Select the mapping used to process the selection path for this request.
	 *
	 * If no mapping can be identified, create an error response and return null.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param string $path The portion of the request URI for selecting a mapping
	 * @return \Phruts\Config\ActionConfig
	 */
    protected function processMapping(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, $path)
    {
        // Is there a directly defined mapping for this path?
        $mapping = $this->moduleConfig->findActionConfig($path);
        if (!is_null($mapping)) {
            $request->attributes->set(\Phruts\Util\Globals::MAPPING_KEY, $mapping);

            return $mapping;
        }

        // Locate the mapping for unknown paths (if any)
        $configs = $this->moduleConfig->findActionConfigs();
        foreach ($configs as $config) {
            if ($config->getUnknown()) {
                $request->attributes->set(\Phruts\Util\Globals::MAPPING_KEY, $config);

                return $config;
            }
        }

        // No mapping can be found to process this request
        $internal = $this->getInternal();
        if(!empty($internal)) {
            $msg = $this->getInternal()->getMessage(null, 'processInvalid', $path);
        } else {
            $msg = 'processInvalid';
        }

        //$this->log->error($msg);
        $response->setStatusCode(400);
        $response->setContent($msg);

        return null;
    }

    /**
	 * If this action is protected by security roles, make sure that the
	 * current user possesses at least one of them.
	 *
	 * Return true to continue normal processing, or false if an appropriate
	 * response has been created and processing should terminate.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Config\ActionConfig $mapping The mapping we are using
	 * @return boolean
	 *
	 */
    protected function processRoles(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Config\ActionConfig $mapping)
    {
        // Is this action protected by role requirements?
        $roles = $mapping->getRoleNames();
        if (empty ($roles)) {
            return true;
        }

        // Check the current user against the list of required roles
        foreach ($roles as $role) {
            if ($request->isUserInRole($role)) {
                if (!empty($this->log)) {
                    $this->log->debug('  User "' . $request->getRemoteUser() . '" has role "' . $role . '", granting access');
                }

                return true;
            }
        }

        // The current user is not authorized for this action
        if (!empty($this->log)) {
            $this->log->debug('  User "' . $request->getRemoteUser() . '" does not have any required role, denying access');
        }
        $response->setStatusCode(403);
        $response->setContent($this->getInternal()->getMessage(null, 'notAuthorized', $mapping->getPath()));

        return false;
    }

    /**
	 * Retrieve and return the ActionForm bean associated with this
	 * mapping, creating and stashing one if necessary.
	 *
	 * If there is no form bean associated with this mapping, return null.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Config\ActionConfig $mapping The mapping we are using
	 * @return \Phruts\Action\AbstractActionForm
	 */
    protected function processActionForm(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Config\ActionConfig $mapping)
    {
        // Create (if necessary a form bean to use)
        $instance = \Phruts\Util\RequestUtils::createActionForm($request, $mapping, $this->moduleConfig, $this->actionKernel);
        if (is_null($instance)) {
            return null;
        }

        // Store the new instance in the appropriate scope
        if (!empty($this->log)) {
            $this->log->debug('  Storing ActionForm bean instance in scope "' . $mapping->getScope() . '" under attribute key "' . $mapping->getAttribute() . '"');
        }
        if ($mapping->getScope() == 'request') {
            $request->attributes->set($mapping->getAttribute(), $instance);
        } else {
            $session = $request->getSession();
            $session->set($mapping->getAttribute(), $instance);
        }

        return $instance;
    }

    /**
	 * Populate the properties of the specified ActionForm instance from
	 * the request parameters included with this request.
	 *
	 * In addition, request attribute <samp>\Phruts\Util\Globals::CANCEL_KEY</samp> will be
	 * set if the request was submitted with a cancel button.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Action\AbstractActionForm $form The ActionForm instance we are
	 * populating
	 * @param \Phruts\Config\ActionConfig $mapping The ActionMapping we are using
	 * @throws \Phruts\Exception - If thrown by \Phruts\Util\RequestUtils->populate()
	 */
    protected function processPopulate(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, $form, \Phruts\Config\ActionConfig $mapping)
    {
        if (is_null($form)) {
            return;
        }

        // Populate the bean properties of this ActionForm instance
        if (!empty($this->log)) {
            $this->log->debug('  Populating bean properties from this request');
        }
        $form->setActionKernel($this->actionKernel);
        $form->reset($mapping, $request);

        try {
            \Phruts\Util\RequestUtils::populate($form, $mapping->getPrefix(), $mapping->getSuffix(), $request);
        } catch (\Phruts\Exception $e) {
            throw $e;
        }

        // Set the cancellation request attribute if appropriate
        if (!is_null($request->get(\Phruts\Util\Globals::CANCEL_PROPERTY))) {
            $request->attributes->set(\Phruts\Util\Globals::CANCEL_KEY, true);
        }
    }

    /**
	 * If this request was not cancelled, and the request's \Phruts\Config\ActionConfig
	 * has not disabled validation, call the validate method of the specified
	 * ActionForm, and forward back to the input form if there were any
	 * errors.
	 *
	 * Return true if we should continue processing, or false if we have already
	 * forwarded control back to the input form.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Action\AbstractActionForm $form The ActionForm instance we are
	 * populating
	 * @param \Phruts\Action\ActionMapping $mapping The \Phruts\Config\ActionConfig we are using
	 * @return boolean
	 */
    protected function processValidate(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, $form, \Phruts\Config\ActionConfig $mapping)
    {
        if (is_null($form)) {
            return true;
        }

        // Was this request cancelled?
        if (!is_null($request->attributes->get(\Phruts\Util\Globals::CANCEL_KEY))) {
            if (!empty($this->log)) {
                $this->log->debug('  Cancelled transaction, skipping validation');
            }

            return true;
        }

        // Has validation been turned off for this mapping?
        if (!$mapping->getValidate()) {
            return true;
        }

        // Call the form bean's validation method
        if (!empty($this->log)) {
            $this->log->debug('  Validating input form properties');
        }
        $errors = $form->validate($mapping, $request);
        if (is_null($errors) || $errors->isEmpty()) {
            if (!empty($this->log)) {
                $this->log->debug('  No errors detected, accepting input');
            }

            return true;
        }

        // Has an input form been specified for this mapping?
        $input = $mapping->getInput();
        if (is_null($input)) {
            if (!empty($this->log)) {
                //$this->log->debug('  Validation failed but no input form available');
            }
            $response->setStatusCode(500);
            $response->setContent($this->getInternal()->getMessage(null, 'noInput', $mapping->getPath()), $mapping->getPath());

            return false;
        }

        // Save our error messages and return to the input form if possible
        if (!empty($this->log)) {
            //$this->log->debug('  Validation failed, returning to "' . $input . '"');
        }
        $request->attributes->set(\Phruts\Util\Globals::ERROR_KEY, $errors);

        if ($this->moduleConfig->getControllerConfig()->getInputForward()) {
            $forward = $mapping->findForward($input);
            $this->processForwardConfig($request, $response, $forward);
        } else {
            // Delegate the processing of this request
            if (!empty($this->log)) {
                //$this->log->debug('  Delegating via forward to "' . $input . '"');
            }
            $this->doForward($input, $request, $response);
        }
    }

    /**
	 * Process a forward requested by this mapping (if any).
	 *
	 * Return true if standard processing should continue, or false if we have
	 * already handled this request.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Config\ActionConfig $mapping The \Phruts\Config\ActionConfig we are using
	 * @return boolean
	 */
    protected function processForward(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Config\ActionConfig $mapping)
    {
        // Are we going to processing this request?
        $forward = $mapping->getForward();
        if (!trim($forward)) {
            return true;
        }

        // Delegate the processing of this request
        if (!empty($this->log)) {
            //$this->log->debug('  Delegating via forward to "' . $forward . '"');
        }
        $this->doForward($forward, $request, $response);

        return false;
    }

    /**
	 * Process an include requested by this mapping (if any).
	 *
	 * Return true if standard processing should continue, or false if we have
	 * already handled this request.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Config\ActionConfig $mapping The \Phruts\Config\ActionConfig we are using
	 * @return boolean
	 */
    protected function processInclude(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Config\ActionConfig $mapping)
    {
        // Are we going to processing this request?
        $include = $mapping->getInclude();
        if (!trim($include)) {
            return true;
        }

        // Delegate the processing of this request
        if (!empty($this->log)) {
            //$this->log->debug('  Delegating via include to "' . $include . '"');
        }
        $this->doInclude($include, $request, $response);

        return false;
    }

    /**
	 * Return a Action instance that will be used to process the current
	 * request, creating a new one if necessary.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Config\ActionConfig $mapping The mapping we are using
	 * @return \Phruts\Config\ForwardConfig
	 */
    protected function processActionCreate(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Config\ActionConfig $mapping)
    {
        // Acquire the Action instance we will be using (if there is one)
        $className = $mapping->getType();
        if (!empty($this->log)) {
            //$this->log->debug('  Looking for Action instance for class ' . $className);
        }

        $instance = null;

        // Return any existing Action instance of this class
        if (array_key_exists($className, $this->actions)) {
            $instance = $this->actions[$className];
        }
        if (!is_null($instance)) {
            if (!empty($this->log)) {
                //$this->log->debug('  Returning existing Action instance');
            }

            return $instance;
        }

        // Create an return a new Action instance
        if (!empty($this->log)) {
            //$this->log->debug('  Creating new Action instance');
        }
        try {
            $instance = \Phruts\Util\ClassLoader::newInstance($className, '\Phruts\Action');

//			API::addInclude($className);
        } catch (\Exception $e) {
            $msg = $this->getInternal()->getMessage(null, 'actionCreate', $mapping->getPath());
            //$this->log->error($msg . ' - ' . $e->getMessage());
            $response->setStatusCode(500);
            $response->setContent($msg);

            return null;
        }

        $instance->setActionKernel($this->actionKernel);
        $this->actions[$className] = $instance;

        return $instance;
    }

    /**
	 * Ask the specified Action instance to handle this request.
	 *
	 * Return the ActionForward instance (if any) returned by the called
	 * Action for further processing.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Action $action The Action instance to be used
	 * @param \Phruts\Action\AbstractActionForm $form The ActionForm instance to pass to
	 * this Action
	 * @param \Phruts\Config\ActionConfig $mapping The \Phruts\Config\ActionConfig instance to
	 * pass to this Action
	 * @return \Phruts\Config\ForwardConfig
	 * @throws \Phruts\Exception
	 */
    protected function processActionPerform(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action $action, $form, \Phruts\Config\ActionConfig $mapping)
    {
        try {
            return $action->execute($mapping, $form, $request, $response);
        } catch (\Exception $e) {
            if (!empty($this->log)) {
                //$this->log->debug('  Exception caught of type ' . get_class($e));
            }

            return $this->processException($request, $response, $e, $form, $mapping);
        }
    }

    /**
	 * Forward or redirect to the specified destination, by the specified
	 * mechanism.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The kernel request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The kernel response we are
	 * creating
	 * @param \Phruts\Config\ForwardConfig $forward The ForwardConfig controlling
	 * where we go next
	 */
    protected function processForwardConfig(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, $forward)
    {
        if (is_null($forward)) {
            return;
        }

        if (!empty($this->log)) {
            //$this->log->debug('processForwardConfig(' . $forward . ')');
        }

        // Add back in support for calling 'nextActionPath' in the forward config
        $nextActionPath = $forward->getNextActionPath();
        if(!empty($nextActionPath)) $forwardPath = (substr($nextActionPath, 0, 1) == '/' ? '' : '/') . $nextActionPath . '.do'; // TODO: Base on current mapping
        else $forwardPath = $forward->getPath();

        if ($forward->getRedirect()) {
            // Build the forward path with a forward context relative URL
            $contextRelative = $forward->getContextRelative();
            if ($contextRelative) {
                $forwardPath = $request->getContextPath() . $forwardPath;
            }

            // TODO: Author a redirect response
            $response->sendRedirect($response->encodeRedirectURL($forwardPath));
        } else {
            $this->doForward($forwardPath, $request, $response);
        }
    }

    /**
	 * Do a forward to specified uri using request dispatcher.
	 *
	 * This method is used by all internal method needing to do a forward.
	 *
	 * @param string $uri Context-relative URI to forward to
	 * @param \Symfony\Component\HttpFoundation\Request $request Current page request
	 * @param \Symfony\Component\HttpFoundation\Response $response Current page response
	 */
    protected function doForward($uri, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Identify configured action chaining
        if (preg_match('/(\/[A-z0-9]+)\.do$/', $uri, $matches)) { // TODO: Base on current kernel mapping
            if (!empty($this->log)) {
                //$this->log->debug('  Forward identified as an action chain request');
            }
            // Set the action do path in the request and then process
            $newPath = $matches[1];

            // TODO: Set the path info on the request
            $request->setPathInfo($newPath);
            $this->process($request, $response);

            return;
        }

        // TODO: Update to match the request dispatcher
        $app = $this->actionKernel->getApplication();
        $rd = $app['request_dispatcher'];
        if (is_null($rd)) {
            $response->setStatusCode(500);
            $response->setContent($this->getInternal()->getMessage(null, 'requestDispatcher', $uri));

            return;
        }
        $rd->doForward($request, $response);
    }

    /**
	 * Do an include of specified uri using request dispatcher.
	 *
	 * This method is used by all internal method needing to do an include.
	 *
	 * @param string $uri Context-relative URI to include
	 * @param \Symfony\Component\HttpFoundation\Request $request Current page request
	 * @param \Symfony\Component\HttpFoundation\Response $response Current page response
	 */
    protected function doInclude($uri, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // TODO: Update to match the request dispatcher
        $app = $this->actionKernel->getApplication();
        $rd = $app['request_dispatcher'];
        if (is_null($rd)) {
            $response->setStatusCode(500);
            $response->setContent($this->getInternal()->getMessage(null, 'requestDispatcher', $uri));

            return;
        }
        $rd->doInclude($request, $response);
    }
}
