<?php

namespace Phruts\Util;

/**
 * NOTE: THIS CLASS IS UNDER ACTIVE DEVELOPMENT.
 * THE CURRENT CODE IS WRITTEN FOR CLARITY NOT EFFICIENCY.
 * NOT EVERY API FUNCTION HAS BEEN IMPLEMENTED YET.
 *
 * A helper object to expose the Phruts shared resources, which are be stored in
 * the actionKernel, session, or request contexts, as appropriate.
 *
 * An instance should be created for each request processed. The  methods which
 * return resources from the request or session contexts are not thread-safe.
 *
 * Provided for use by other actionKernels in the actionKernel so they can easily access
 * the Struts shared resources.
 *
 * The resources are stored under attributes in the actionKernel, session, or request
 * contexts.
 *
 * The \Phruts\Config\ActionConfig methods simply return the resources from under the context
 * and key used by the Struts ActionKernel when the resources are created.
 *
 * @since Struts 1.1
 */
class ConfigHelper
{
    // --------------------------------------------------------  Properites

    /**
     * The \Silex\Application actionKernel associated with this instance.
     */
    private $application = null;

    /**
     * Set the actionKernel associated with this instance.
     * [actionKernel->getActionKernelContext()]
     */
    public function getApplication(\Silex\Application $app)
    {
        $this->application = $app;
    }

    /**
     * The session associated with this instance.
     * \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session = null;

    /**
     * Set the session associated with this instance.
     */
    public function setSession(\Symfony\Component\HttpFoundation\Session $session)
    {
        $this->session = $session;
    }

    /**
     * The request associated with this instance.
     */
    private $request = null;

    /**
     * Set the request associated with this object.
     * Session object is also set or cleared.
     */
    public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->request = $request;
        if ($this->request == null)
            $this->setSession(null);
        else
            $this->setSession($this->request->getSession());
    }

    /**
     * The response associated with this instance.
     */
    private $response = null;

    /**
     * Set the response associated with this isntance.
     * Session object is also set or cleared.
     */
    public function setResponse(\Symfony\Component\HttpFoundation\Response $response)
    {
        $this->response = $response;
    }

    /**
     * The forward associated with this instance.
     */
    private $forward = null;

    /**
     * Set the forward associated with this instance.
     * // TODO: Is this supposed to be a ForwardConfig?
     */
    public function setForward(\Phruts\Config\ActionConfig $forward)
    {
        $this->forward = $forward;
    }

    /**
     * Set the actionKernel and request for this object instance.
     * The ActionKernelContext can be set by any actionKernel in the actionKernel.
     * The request should be the instant request.
     * Most of the other methods retrieve their own objects
     * by reference to the actionKernel, request, or session
     * attributes.
     * Do not call other methods without setting these first!
     * This is also called by the convenience constructor.
     *
     * @param \Silex\Application $app - The associated app.
     * @param \Symfony\Component\HttpFoundation\Request request - The associated HTTP request.
     * @param \Symfony\Component\HttpFoundation\Response response - The associated HTTP response.
     */
    public function setResources(\Silex\Application $app, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $this->setApp($app);
        $this->setRequest($request);
        $this->setResponse($response);
    }

    public function __construct(\Phruts\Action\ActionKernel $actionKernel, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $this->setResources($actionKernel, $request, $response);
    }

    // ------------------------------------------------ Application Context

    /**
     * The <strong>default</strong>
     * configured data source (which must implement
     * <code>phruts::util::DataSource</code>), if one is configured for this
     * actionKernel.
     * @return DataSource
     */
    public function getDataSource()
    {
        if ($this->actionKernel == null)
            return null;
        return $this->actionKernel->getAttribute(\Phruts\Util\Globals::DATA_SOURCE_KEY);

    }

    /**
	 * @return \Phruts\Action\ActionMessages
	 */
    public function getActionMessages()
    {
        if ($this->actionKernel == null)
            return null;
        return $this->actionKernel->getAttribute(\Phruts\Util\Globals::MESSAGE_KEY);

    }

    /**
     * The actionKernel resources for this actionKernel.
     * @return MessageResources
     */
    public function getMessageResources()
    {
        if ($this->actionKernel == null) {
            return null;
        }

        return $this->actionKernel->getAttribute(\Phruts\Util\Globals::MESSAGES_KEY);
    }

    /**
     * The path-mapped pattern (<code>/action/*</code>) or
     * extension mapped pattern ((<code>*.do</code>)
     * used to determine our Action URIs in this actionKernel.
     */
    public function getActionKernelMapping()
    {
        if ($this->actionKernel == null) {
            return null;
        }
        //return $this->actionKernel->getAttribute(\Phruts\Util\Globals::SERVLET_KEY);
        return $this->actionKernel->getActionKernelConfig()->getActionKernelMapping();
    }

    // ---------------------------------------------------- Session Context

    /**
     * The transaction token stored in this session, if it is used.
     * @return string
     */
    public function getToken()
    {
        if ($this->session == null) {
            return null;
        }

        return $this->session->getAttribute(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY);

    }

    // ---------------------------------------------------- Request Context

    /**
     * The runtime JspException that may be been thrown by a Struts tag
     * extension, or compatible presentation extension, and placed
     * in the request.
     * @return Exception
     */
    public function getException()
    {
        if ($this->request == null) {
            return null;
        }

        return $this->request->getAttribute(\Phruts\Util\Globals::EXCEPTION_KEY);

    }

//    /**
//     * The multipart object for this request.
//     */
//    public MultipartRequestWrapper getMultipartRequestWrapper() {
//
//        if ($this->request == null) {
//            return null;
//        }
//        return (MultipartRequestWrapper) $this->request->getAttribute(\Phruts\Util\Globals::MULTIPART_KEY);
//    }

    /**
      * The <code>org.apache.struts.ActionMapping</code>
      * instance for this request.
      * @return \Phruts\Config\ActionConfig
      */
    public function getMapping()
    {
        if ($this->request == null) {
            return null;
        }

        return $this->request->getAttribute(\Phruts\Util\Globals::MAPPING_KEY);
    }

    // ---------------------------------------------------- Utility Methods

    /**
     * Return true if a message string for the specified message key
     * is present for the user's Locale.
     *
     * @param key Message key
     * @return boolean
     */
    public function isMessage($key)
    {
        // Look up the requested MessageResources
        $resources = $this->getMessageResources();

        if ($resources == null) {
            return false;
        }

        // Return the requested message presence indicator
        return $resources->isPresent(\Phruts\Util\RequestUtils::retrieveUserLocale($this->request, null), $key);
    }

    /**
     * Retrieve and return the <code>\Phruts\Action\AbstractActionForm</code> bean associated with
     * this mapping, creating and stashing one if necessary.  If there is no
     * form bean associated with this mapping, return <code>null</code>.
     * @return \Phruts\Action\AbstractActionForm
     */
    public function getActionForm()
    {
        // Is there a mapping associated with this request?
        $mapping = $this->getMapping();
        if ($mapping == null)
            return (null);

        // Is there a form bean associated with this mapping?
        $attribute = $mapping->getAttribute();
        if ($attribute == null)
            return (null);

        // Look up the existing form bean, if any
        $instance = null;
        if ($mapping->getScope() == "request") {
            $instance = $this->request->getAttribute($attribute);
        } else {
            $instance = $this->session->getAttribute($attribute);
        }

        return $instance;
    }

//    /**
//     * Return the form bean definition associated with the specified
//     * logical name, if any; otherwise return <code>null</code>.
//     *
//     * @param name Logical name of the requested form bean definition
//     */
//    public \Phruts\Action\AbstractActionFormBean getFormBean(String name) {
//        return null;
//    }

//    /**
//     * Return the forwarding associated with the specified logical name,
//     * if any; otherwise return <code>null</code>.
//     *
//     * @param name Logical name of the requested forwarding
//     */
//    public \Phruts\Config\ActionConfig getActionForward(String name) {
//        return null;
//    }

//    /**
//     * Return the mapping associated with the specified request path, if any;
//     * otherwise return <code>null</code>.
//     *
//     * @param path Request path for which a mapping is requested
//     */
//    public ActionMapping getActionMapping(String path) {
//        return null;
//    }

    /**
     * Return the form action converted into an action mapping path.  The
     * value of the <code>action</code> property is manipulated as follows in
     * computing the name of the requested mapping:
     * <ul>
     * <li>Any filename extension is removed (on the theory that extension
     *     mapping is being used to select the controller actionKernel).</li>
     * <li>If the resulting value does not start with a slash, then a
     *     slash is prepended.</li>
     * </ul>
     * @return String
     */
    public function getActionMappingName($action)
    {
        $value = $action;
        if (preg_match('/\?/', $action)) {
            $question = strpos($action, "?");
            if ($question >= 0)
                $value = substr($value, 0, $question);
        }
        $slash = strrchr($value, "/");
        $period = strrchr($value, ".");
        if (($period >= 0) && ($period > $slash))
            $value = substr($value, 0, $period);

        if (substr($value, 0, 1) == "/")
            return ($value);
        else
            return ("/" . $value);
    }

    /**
     * Return the form action converted into a server-relative URL.
     * @return string
     */
    public function getActionMappingURL($action)
    {
        $contextPath = '';
        // TODO: See if we can get the context path from the request '/context/action' otherwise context is ''
        $path = $this->request->getParameter($this->actionKernel->getActionKernelConfig()->getPathParam());
        if (!empty($path) && preg_match('/^\/?([^\/]+\/)[.]+/', $path, $matches)) {
            $contextPath = $matches[1]; // Get the context
        }

        // Make our context/path
        $ref = $contextPath . $action;

        // Use our actionKernel mapping, if one is specified
        $actionKernelMapping = $this->getActionKernelMapping();
        if ($actionKernelMapping == null) {
             $actionKernelMapping = 'index.php?do=*';
        }

        // Query incomming?
        $queryString = null;
        if (preg_match('/\?/', $action)) {
            $queryString = substr($action, strpos($action, '?') + 1);
        }
        $actionMapping = $this->getActionMappingName($action);

        $value = preg_replace('/\*/', $contextPath . $actionMapping, $actionKernelMapping);

        if (!empty($queryString)) {
            if (preg_match('/\?/', $actionKernelMapping)) {
                $value .= '&' . $queryString;
            } else {
                $value .= '?' . $queryString;
            }
        }

        // Don't start with the slash if the result is not relative (e.g. absolute)
        if (substr($value, 0, 1) == '/') {
            $value = substr($value, 1);
        }

        // Return the completed value
        return ($value);

    }

    /**
     * Return the url encoded to maintain the user session, if any.
     */
    public function getEncodeURL($url)
    {
        // TODO: SESSION_ID request param for session?
        return $url;
    }

    // ------------------------------------------------ Presentation API

    /**
     * Renders the reference for a HTML <base> element
     */
    public function getOrigRef()
    {
        if ($this->request == null)
            return null;
        $result = \Phruts\Util\RequestUtils::requestToServerUriStringBuffer($this->request);

        return $result;
    }

    /**
     * Renders the reference for a HTML <base> element.
     */
    public function getBaseRef()
    {
        if ($this->request == null)
            return null;

        $result = \Phruts\Util\RequestUtils::requestToServerStringBuffer($this->request);
        $path = null;
        if ($this->forward == null)
            $path = $this->request->getRequestURI();
        else {
            $contextPath = '';
            // TODO: See if we can get the context path from the request '/context/action' otherwise context is ''
            $requestPath = $this->request->getParameter($this->actionKernel->getActionKernelConfig()->getPathParam());
            if (!empty($requestPath) && preg_match('/([^\/]+)\/[.]+/', $requestPath, $matches)) {
                $contextPath = $matches[1]; // Get the context
            }
            $path = $contextPath . $this->forward->getPath();
        }
        $result .= $path;
        // Remove the last part of the request uri
        $result = substr($result, 0, strrpos($result, '/') + 1);

        return $result;
    }

//    /**
//     * Return the path for the specified forward,
//     * otherwise return <code>null</code>.
//     *
//     * @param name Name given to local or global forward.
//     */
//    public function getLink($name) {
//
//        ActionForward forward = $this->getActionForward(name);
//        if (forward == null)
//            return null;
//
//        StringBuffer path = new StringBuffer($this->request->getContextPath());
//        path.append(forward->getPath());
//
//        // :TODO: What about runtime parameters?
//
//        return getEncodeURL(path.toString());
//
//    }

    /**
     * Return the localized message for the specified key,
     * otherwise return <code>null</code>.
     *
     * @param key Message key
     */
    public function getMessage($key, $args)
    {
        $resources = $this->getMessageResources();
        if ($resources == null)
            return null;

        return $resources->getMessage(\Phruts\Util\RequestUtils::retrieveUserLocale($this->request, null), $key, $args);
    }

    /**
     * Return the URL for the specified ActionMapping,
     * otherwise return <code>null</code>.
     *
     * @param path Name given to local or global forward.
     */
    public function getAction($path)
    {
        return $this->getEncodeURL($this->getActionMappingURL($path));
    }

    // --------------------------------------------- Presentation Wrappers

//    /**
//     * Wrapper for getLink(String)
//     *
//     * @param name Name given to local or global forward.
//     */
//    public String link(String name) {
//        return getLink(name);
//    }


    /**
     * Wrapper for getMessage(String,Object[])
     *
     * @param string key Message key to be looked up and returned
     * @param array args Replacement parameters for this message
     */
    public function message($key, $args = array())
    {
        return $this->getMessage(key, $args);
    }

    /**
     * Wrapper for getAction(String)
     *
     * @param path Name given to local or global forward.
     */
    public function action($path)
    {
        return $this->getAction($path);
    }
}
