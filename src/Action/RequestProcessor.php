<?php
namespace Phruts\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
    const INCLUDE_KERNEL_PATH = 'Phruts_Include.actionserver_path';
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
	 */
    public function init(\Phruts\Action\ActionKernel $actionKernel, \Phruts\Config\ModuleConfig $moduleConfig)
    {
        $this->actions = array ();
        $this->actionKernel = $actionKernel;
        $this->moduleConfig = $moduleConfig;

        // Log
        $application = $this->actionKernel->getApplication();
        if (!empty($application['logger'])) {
            $this->log = $application['logger'];
        }
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
        $path = (string) $request->attributes->get(self::INCLUDE_PATH_INFO);
        if ($path == null) {
            $path = $request->getPathInfo();
        }

        // Always have a forward slash in the path
        if(substr($path, 0, 1) !== '/') $path = '/' . $path;

        // For extension matching, strip the module prefix and extension
        $prefix = $this->moduleConfig->getPrefix();
        if (!empty($prefix)) {
            if (!preg_match('#^/?' . $prefix . '/.*#', $path)) {
                $msg = $this->getInternal()->getMessage(null, "processPath", $request->getRequestURI());
                if (!empty($this->log)) {
                    $this->log->error($msg);
                }
                throw new BadRequestHttpException($msg);
            }
            // Strip module
            $path = preg_replace('#^/?' . $prefix . '#', '', $path);
        }

        return $path;
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
        if (!empty($contentType)) {
            $response->headers->set('Content-Type', $contentType);
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
    protected function processException(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Exception $exception, $form, \Phruts\Action\ActionMapping $mapping)
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
            $response->headers->set('Pragma', 'No-cache');
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
        if (!empty($internal)) {
            $msg = $this->getInternal()->getMessage(null, 'processInvalid', $path);
        } else {
            $msg = 'processInvalid';
        }

        if (!empty($this->log)) {
            $this->log->error($msg);
        }
        throw new BadRequestHttpException($msg);
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
	 * @param \Phruts\Action\ActionMapping $mapping The mapping we are using
	 * @return boolean
	 *
	 */
    protected function processRoles(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\ActionMapping $mapping)
    {
        // Is this action protected by role requirements?
        $roles = $mapping->getRoleNames();
        if (empty ($roles)) {
            return true;
        }

        // Check the current user against the list of required roles
        if (!empty($app['security'])) {
            $security = $app['security'];

            foreach ($roles as $role) {
                if ($security->isGranted($role)) {

                    if (!empty($this->log)) {
                        $token = $app['security']->getToken();
                        if (null !== $token) {
                            $user = $token->getUser();
                        }
                        $this->log->debug('  User "' . $user . '" has role "' . $role . '", granting access');
                    }

                    return true;
                }
            }
        }

        // The current user is not authorized for this action
        if (!empty($this->log)) {
            $this->log->debug('  User does not have any required role, denying access');
        }
        throw new AccessDeniedHttpException($this->getInternal()->getMessage(null, 'notAuthorized', $mapping->getPath()));
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
	 * @param \Phruts\Action\ActionMapping $mapping The mapping we are using
	 * @return \Phruts\Action\AbstractActionForm
	 */
    protected function processActionForm(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\ActionMapping $mapping)
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
            if(!empty($session))
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
	 * @param \Phruts\Action\ActionMapping $mapping The ActionMapping we are using
	 * @throws \Phruts\Exception - If thrown by \Phruts\Util\RequestUtils->populate()
	 */
    protected function processPopulate(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\AbstractActionForm $form = null, \Phruts\Action\ActionMapping $mapping)
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
    protected function processValidate(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\AbstractActionForm $form = null, \Phruts\Action\ActionMapping $mapping)
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
                $this->log->debug('  Validation failed but no input form available');
            }

            $msg = $this->getInternal()->getMessage(null, 'noInput', $mapping->getPath());
            throw new \Phruts\Exception($msg);
        }

        // Save our error messages and return to the input form if possible
        if (!empty($this->log)) {
            $this->log->debug('  Validation failed, returning to "' . $input . '"');
        }
        $request->attributes->set(\Phruts\Util\Globals::ERROR_KEY, $errors);

        if ($this->moduleConfig->getControllerConfig()->getInputForward()) {
            $forward = $mapping->findForward($input);
            $this->processForwardConfig($request, $response, $forward);
        } else {
            // Delegate the processing of this request
            if (!empty($this->log)) {
                $this->log->debug('  Delegating via forward to "' . $input . '"');
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
	 * @param \Phruts\Action\ActionMapping $mapping The \Phruts\Config\ActionConfig we are using
	 * @return boolean
	 */
    protected function processForward(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\ActionMapping $mapping)
    {
        // Are we going to processing this request?
        $forward = $mapping->getForward();
        if (!trim($forward)) {
            return true;
        }

        // Delegate the processing of this request
        if (!empty($this->log)) {
            $this->log->debug('  Delegating via forward to "' . $forward . '"');
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
	 * @param \Phruts\Action\ActionMapping $mapping The \Phruts\Config\ActionConfig we are using
	 * @return boolean
	 */
    protected function processInclude(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\ActionMapping $mapping)
    {
        // Are we going to processing this request?
        $include = $mapping->getInclude();
        if (!trim($include)) {
            return true;
        }

        // Delegate the processing of this request
        if (!empty($this->log)) {
            $this->log->debug('  Delegating via include to "' . $include . '"');
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
	 * @param \Phruts\Action\ActionMapping $mapping The mapping we are using
	 * @return \Phruts\Config\ForwardConfig
	 */
    protected function processActionCreate(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\ActionMapping $mapping)
    {
        // Acquire the Action instance we will be using (if there is one)
        $className = $mapping->getType();
        if (!empty($this->log)) {
            $this->log->debug('  Looking for Action instance for class ' . $className);
        }

        $instance = null;

        // Return any existing Action instance of this class
        if (array_key_exists($className, $this->actions)) {
            $instance = $this->actions[$className];
        }
        if (!is_null($instance)) {
            if (!empty($this->log)) {
                $this->log->debug('  Returning existing Action instance');
            }

            return $instance;
        }

        // Create an return a new Action instance
        if (!empty($this->log)) {
            $this->log->debug('  Creating new Action instance');
        }
        try {
            $instance = \Phruts\Util\ClassLoader::newInstance($className, '\Phruts\Action\Action');
        } catch (\Exception $e) {
            $msg = $this->getInternal()->getMessage(null, 'actionCreate', $mapping->getPath());
            if (!empty($this->log)) {
                $this->log->error($msg . ' - ' . $e->getMessage());
            }
            throw new HttpException(500, $msg);
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
	 * @param \Phruts\Action\ActionMapping $mapping The \Phruts\Config\ActionConfig instance to
	 * pass to this Action
	 * @return \Phruts\Config\ForwardConfig
	 * @throws \Phruts\Exception
	 */
    protected function processActionPerform(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Action\Action $action, $form, \Phruts\Action\ActionMapping $mapping)
    {
        try {
            return $action->execute($mapping, $form, $request, $response);
        } catch (\Exception $e) {
            if (!empty($this->log)) {
                $this->log->debug('  Exception caught of type ' . get_class($e));
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
    protected function processForwardConfig(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Phruts\Config\ForwardConfig $forward = null)
    {
        if (is_null($forward)) {
            return;
        }

        if (!empty($this->log)) {
            $this->log->debug('processForwardConfig(' . $forward . ')');
        }

        $forwardPath = $forward->getPath();

        if ($forward->getRedirect()) {
            // Build the forward path with a forward context relative URL
            $contextRelative = $forward->getContextRelative();
            if ($contextRelative) {
                $forwardPath = $request->getUriForPath($forwardPath);
            }

            // Author a redirect response
            $subResponse = $this->actionKernel->getApplication()->redirect($forwardPath);
            // Update our current response to bring in the response
            $response->setContent($subResponse->getContent());
            $response->setStatusCode($subResponse->getStatusCode());
            $response->setCharset($subResponse->getCharset());
            $response->setProtocolVersion($subResponse->getProtocolVersion());
            // Determine whether all headers are 'added' or should replace (?)
            $response->headers->add($subResponse->headers->all());
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
        // Create a new URI
        $uri = $request->getUriForPath($uri);

        // Consider using standard $_POST, $_FILES etc.
        $subRequest = Request::create($uri, $request->getMethod(), $request->getMethod() == 'POST' ? $request->request->all() : $request->query->all(), $request->cookies->all(), $request->files->all(), $request->server->all());

        // If it was a POST then ensure it also has any query parameters
        if ($request->getMethod() == 'POST') {
            $subRequest->query->add($request->query->all());
        }

        if ($request->getSession()) {
            $subRequest->setSession($request->getSession());
        }

        // Obtain a new subrequest without Silex attributes
        $allowedKeys = array_filter(array_keys($request->attributes->all()), function($key) {
                // Filter out silex "_" attributes
                return substr($key, 0, 1) != '_';
            });
        $subRequest->attributes->add(array_intersect_key($request->attributes->all(), array_flip($allowedKeys)));

        // Call for a sub-request (Note: Non-conventionally passes parent query/attributes)
        $subResponse = $this->actionKernel->getApplication()->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        // Update our current response to bring in the response
        $response->setContent($subResponse->getContent());
        $response->setStatusCode($subResponse->getStatusCode());
        $response->setCharset($subResponse->getCharset());
        $response->setProtocolVersion($subResponse->getProtocolVersion());

        // Determine whether all headers are 'added' or should replace (?)
        $response->headers->add($subResponse->headers->all());
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
        // Process the same as an internal forward
        $this->doForward($uri, $request, $response);
    }
}
