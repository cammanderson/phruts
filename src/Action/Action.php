<?php
namespace Phruts\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * A Action is an adapter between the contents of an incoming HTTP
 * request and the corresponding business logic that should be executed to
 * process this request.
 *
 * <p>The controller (ActionKernel) will select an appropriate
 * Action for each request, create an instance (if necessary), and call
 * the <samp>execute</samp> method.</p>
 * <p>When a Action instance is first created, the controller actionKernel
 * will call <samp>setActionKernel</samp> with a non-null argument to identify the
 * controller actionKernel instance to which this Action is attached. When
 * the controller actionKernel is to be shut down (or restarted), the
 * <samp>setActionKernel</samp> method will be called with a null argument, which
 * can be used to clean up any allocated resources in use by this
 * Action.</p>
 *
 */
class Action
{

    /**
	 * The controller ActionKernel to which we are attached.
	 *
	 * @var \Phruts\Action\ActionKernel
	 */
    protected $actionKernel = null;

    final public function __construct()
    {

    }

    /**
	 * Return the controller action server instance to which we are attached.
	 *
	 * @return \Phruts\Action\ActionKernel
	 */
    public function getActionKernel()
    {
        return $this->actionKernel;
    }

    /**
	 * Set the controller app instance to which we are attached (if app
	 * is non-null), or release any allocated resources (if app is null).
	 *
	 * @param \Phruts\Action\ActionKernel $actionKernel The new controller server, if any
	 */
    public function setActionKernel(ActionKernel $actionKernel)
    {
        $this->actionKernel = $actionKernel;
    }

    /**
	 * Process the specified HTTP request, and create the corresponding HTTP
	 * response (or forward to another web component that will create it),
	 * with provision for handling exceptions thrown by the business logic.
	 *
	 * Return an ActionForward instance describing where and how control
	 * should be forwarded, or null if the response has already been completed.
	 *
	 * @param \Phruts\Config\ActionConfig $mapping The \Phruts\Config\ActionConfig used to select
	 * this instance
	 * @param \Phruts\Action\AbstractActionForm $form The optional \Phruts\Action\AbstractActionForm bean for this
	 * request (if any)
	 * @param \Symfony\Component\HttpFoundation\Request $request The HTTP request we are
	 * processing
	 * @param \Symfony\Component\HttpFoundation\Response $response The HTTP response we are
	 * creating
	 * @return \Phruts\Config\ForwardConfig
	 * @throws \Exception - if the application business logic throws an exception
	 */
    public function execute(ActionMapping $mapping, AbstractActionForm $form = null, Request $request, Response $response)
    {
        return null; // Override this method to provide functionality
    }

    /**
	 * Return the specified data source for the current module.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The actionKernel request we are
	 * processing
	 * @param string $key The key specified in the <data-source> element for
	 * the requested data source
	 * @return object
	 * @throws \Exception
	 */
    protected function getDataSource(Request $request, $key)
    {
        try {
            return $this->actionKernel->getDataSource($request, $key);
        } catch (\Exception $e) {
            // Log
            throw $e;
        }
    }

    /**
	 * Return the user's currently selected Locale.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The request we are processing
	 * @return string
	 */
    protected function getLocale(Request $request)
    {
        $session = $request->getSession();
        $locale = null;
        if (!empty($session)) {
            $locale = $session->get(\Phruts\Util\Globals::LOCALE_KEY);
        }

        if (is_null($locale)) {
            // Silex core parameter
            $app = $this->actionKernel->getApplication();
            $locale = $app['locale'];
        }

        return $locale;
    }

    /**
	 * Return the specified or default (key = "") message resources for the
	 * current module.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The actionKernel request we are
	 * processing
	 * @param string $key The key specified in the <message-resources> element
	 * for the requested bundle
	 * @return \Phruts\Util\MessageResources
	 * module ($request = null).
	 */
    protected function getResources(Request $request, $key = '')
    {
        if ($key == '') {
            return $request->attributes->get(\Phruts\Util\Globals::MESSAGES_KEY);
        } else {
            // Identify the current module
            $app = $this->actionKernel->getApplication();
            $moduleConfig = \Phruts\Util\RequestUtils::getModuleConfig($request, $app);

            // Return the requested message resources instance
            return $app[$key . $moduleConfig->getPrefix()];
        }
    }

    /**
	 * Returns true if the current form's cancel button was pressed.
	 *
	 * This method will check if the <samp>\Phruts\Util\Globals::CANCEL_KEY</samp>
	 * request attribute has been set, which normally occurs if the cancel button
	 * was pressed by the user in the current request. If true, validation
	 * performed by a \Phruts\Action\AbstractActionForm <samp>validate</samp> method will have
	 * been skipped by the controller actionKernel.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The actionKernel request we are
	 * processing
	 * @return boolean
	 */
    protected function isCancelled(Request $request)
    {
        return (!is_null($request->attributes->get(\Phruts\Util\Globals::CANCEL_KEY)));
    }

    /**
	 * Save the specified error messages keys into the appropriate request
	 * attribute, if any messages are required.
	 *
	 * Otherwise, ensure that the request attribute is not created.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The actionKernel request we are
	 * processing
	 * @param \Phruts\Action\ActionErrors $errors Error messages object
	 */
    protected function saveErrors(Request $request, $errors)
    {
        if (!empty($errors) && !($errors instanceof \Phruts\Action\ActionErrors)) {
            throw new \Phruts\Exception\IllegalArgumentException('Errors should be an \Phruts\Action\ActionErrors object');
        }

        // Remove any error messages attribute if none are required
        if (is_null($errors) || $errors->isEmpty()) {
            $request->attributes->remove(\Phruts\Util\Globals::ERROR_KEY);

            return;
        }

        // Save the error messages we need
        $request->attributes->set(\Phruts\Util\Globals::ERROR_KEY, $errors);
    }

    /**
     * <p>Save the specified error messages keys into the appropriate session
     * attribute for use by the &lt;html:messages&gt; tag (if messages="false")
     * or &lt;html:errors&gt;, if any error messages are required. Otherwise,
     * ensure that the session attribute is empty.</p>
     *
     * @param \Symfony\Component\HttpFoundation\Session\Session session The session to save the error messages in.
     * @param \Phruts\Action\ActionMessages errors The error messages to save.
     * <code>null</code> or empty messages removes any existing error
     * \Phruts\Action\ActionMessages in the session.
     *
     * @since Struts 1.2.7
     */
    protected function saveErrorsSession(Session $session, ActionMessages $errors = null)
    {
        // Remove the error attribute if none are required
        if (($errors == null) || $errors->isEmpty()) {
            $session->remove(\Phruts\Util\Globals::ERROR_KEY);

            return;
        }

        // Save the errors we need
        $session->set(\Phruts\Util\Globals::ERROR_KEY, $errors);
    }

    /**
	 * Adds the specified errors keys into the appropriate request attribute
     * for use by the &lt;html:errors&gt; tag, if any messages are required.
	 * Initialize the attribute if it has not already been. Otherwise, ensure
     * that the request attribute is not set.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request request   The actionKernel request we are processing
	 * @param \Phruts\Action\ActionMessages errors  Errors object
	 * @since Struts 1.2.1
	 */
    protected function addErrors(Request $request, ActionMessages $errors = null)
    {
        if ($errors == null) {
            //	bad programmer! *slap*
            return;
        }

        // get any existing errors from the request, or make a new one
        $requestErrors = $request->attributes->get(\Phruts\Util\Globals::ERROR_KEY); //\Phruts\Action\ActionMessages
        if ($requestErrors == null) {
            $requestErrors = new \Phruts\Action\ActionMessages();
        }
        // add incoming errors
        $requestErrors->addMessages($errors);

        // if still empty, just wipe it out from the request
        if ($requestErrors->isEmpty()) {
            $request->attributes->remove(\Phruts\Util\Globals::ERROR_KEY);

            return;
        }

        // Save the errors
        $request->attributes->set(\Phruts\Util\Globals::ERROR_KEY, $requestErrors);
    }

    /**
     * <p>Save the specified messages keys into the appropriate request
     * attribute for use by the &lt;html:messages&gt; tag (if
     * messages="true" is set), if any messages are required. Otherwise,
     * ensure that the request attribute is not created.</p>
     *
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing.
     * @param \Phruts\Action\ActionMessages messages The messages to save. <code>null</code> or
     * empty messages removes any existing \Phruts\Action\ActionMessages in the request.
     *
     * @since Struts 1.1
     */
    protected function saveMessages(Request $request, ActionMessages $messages = null)
    {
        // Remove any messages attribute if none are required
        if (($messages == null) || $messages->isEmpty()) {
            $request->attributes->remove(\Phruts\Util\Globals::MESSAGE_KEY);

            return;
        }

        // Save the messages we need
        $request->attributes->set(\Phruts\Util\Globals::MESSAGE_KEY, $messages);
    }

    /**
     * <p>Save the specified messages keys into the appropriate session
     * attribute for use by the &lt;html:messages&gt; tag (if
     * messages="true" is set), if any messages are required. Otherwise,
     * ensure that the session attribute is not created.</p>
     *
     * @param \Symfony\Component\HttpFoundation\Session\Session session The session to save the messages in.
     * @param \Phruts\Action\ActionMessages messages The messages to save. <code>null</code> or
     * empty messages removes any existing \Phruts\Action\ActionMessages in the session.
     *
     * @since Struts 1.2
     */
    protected function saveMessagesSession(Session $session, ActionMessages $messages = null)
    {
        // Remove any messages attribute if none are required
        if (($messages == null) || $messages->isEmpty()) {
            $session->remove(\Phruts\Util\Globals::MESSAGE_KEY);

            return;
        }

        // Save the messages we need
        $session->set(\Phruts\Util\Globals::MESSAGE_KEY, $messages);
    }

    /**
	 * Adds the specified messages keys into the appropriate request
	 * attribute for use by the &lt;html:messages&gt; tag (if
	 * messages="true" is set), if any messages are required.
	 * Initialize the attribute if it has not already been.
	 * Otherwise, ensure that the request attribute is not set.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request request   The actionKernel request we are processing
	 * @param \Phruts\Action\ActionMessages messages  Messages object
	 * @since Struts 1.2.1
	 */
    protected function addMessages(Request $request, ActionMessages $messages)
    {
        // get any existing errors from the request, or make a new one
        $requestMessages = $request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY); //\Phruts\Action\ActionMessages
        if ($requestMessages == null) {
            $requestMessages = new \Phruts\Action\ActionMessages();
        }
        // add incoming errors
        $requestMessages->addMessages($messages);

        // if still empty, just wipe it out from the request
        if ($requestMessages->isEmpty()) {
            $request->attributes->remove(\Phruts\Util\Globals::MESSAGE_KEY);

            return;
        }

        // Save the errors
        $request->attributes->set(\Phruts\Util\Globals::MESSAGE_KEY, $requestMessages);
    }

    /**
     * Retrieves any existing errors placed in the request by previous actions.  This method could be called instead
     * of creating a <code>new \Phruts\Action\ActionMessages()<code> at the beginning of an <code>Action<code>
     * This will prevent saveErrors() from wiping out any existing Errors
     *
     * @return array                         Errors that already exist in the request, or a new \Phruts\Action\ActionMessages object if empty.
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
     * @return \Phruts\Action\ActionMessages
     * @since Struts 1.2.1
     */
    protected function getErrors(Request $request)
    {
        $errors = $request->attributes->get(\Phruts\Util\Globals::ERROR_KEY); //\Phruts\Action\ActionMessages
        if (empty($errors)) {
            $errors = new \Phruts\Action\ActionErrors();
        }

        return $errors;
    }

    /**
	 * Retrieves any existing messages placed in the request by previous actions.  This method could be called instead
	 * of creating a <code>new \Phruts\Action\ActionMessages()<code> at the beginning of an <code>Action<code>
	 * This will prevent saveMessages() from wiping out any existing Messages
	 *
	 * @return array Messages that already exist in the request, or a new \Phruts\Action\ActionMessages object if empty.
	 * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
	 * @return \Phruts\Action\ActionMessages
     * @since Struts 1.2.1
	 */
    protected function getMessages(Request $request)
    {
        $messages = $request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY); // \Phruts\Action\ActionMessages
        if ($messages == null) {
            $messages = new \Phruts\Action\ActionMessages();
        }

        return $messages;
    }

    /**
	 * Set the user's currently selected Locale.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request The request we are processing
	 * @param string $locale The user's selected Locale to be set,
	 * or null to select the system's default Locale
	 */
    protected function setLocale(Request $request, $locale)
    {
        $session = $request->getSession();
        if (is_null($locale)) {
            // Silex core parameter
            $app = $this->actionKernel->getApplication();
            $locale = $app['locale'];
        }
        $session->set(\Phruts\Util\Globals::LOCALE_KEY, $locale);
    }

    /**
     * <p>Generate a new transaction token, to be used for enforcing a single
     * request for a particular transaction.</p>
     *
     * @param \Symfony\Component\HttpFoundation\Request request The request we are processing
     * @return string
     */
    protected function generateToken(Request $request)
    {
        $token = \Phruts\Util\TokenProcessor::getInstance(); // not application scope

        return $token->generateToken($request);
    }

    /**
     * <p>Return <code>true</code> if there is a transaction token stored in
     * the user's current session, and the value submitted as a request
     * parameter with this action matches it. Returns <code>false</code>
     * under any of the following circumstances:</p>
     * <ul>
     * <li>No session associated with this request</li>
     * <li>No transaction token saved in the session</li>
     * <li>No transaction token included as a request parameter</li>
     * <li>The included transaction token value does not match the
     *     transaction token in the user's session</li>
     * </ul>
     *
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
     * @param reset Should we reset the token after checking it?
     * @return boolean
     */
    protected function isTokenValid(Request $request, $reset = false)
    {
        $token = \Phruts\Util\TokenProcessor::getInstance(); // not application scope

        return $token->isTokenValid($request, $reset);
    }

    /**
     * <p>Reset the saved transaction token in the user's session. This
     * indicates that transactional token checking will not be needed
     * on the next request that is submitted.</p>
     *
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
     */
    protected function resetToken(Request $request)
    {
        $token = \Phruts\Util\TokenProcessor::getInstance(); // not application scope
        $token->resetToken($request);
    }

    /**
     * <p>Save a new transaction token in the user's current session, creating
     * a new session if necessary.</p>
     *
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
     */
    protected function saveToken(Request $request)
    {
        $token = \Phruts\Util\TokenProcessor::getInstance(); // not application scope
        $token->saveToken($request);
    }
}
