<?php

namespace Phruts\Actions;

/**
 * <p>An abstract <strong>Action</strong> that dispatches to a public
 * method that is named by the request parameter whose name is specified
 * by the <code>parameter</code> property of the corresponding
 * ActionMapping.  This Action is useful for developers who prefer to
 * combine many similar actions into a single Action class, in order to
 * simplify their application design.</p>
 *
 * <p>To configure the use of this action in your <code>struts-config.xml</code>
 * file, create an entry like this:</p>
 *
 * <code> &lt;action path="/saveSubscription" type="actions::DispatchAction"
 * name="subscriptionForm" scope="request" input="/subscription.php" parameter="
 * method"/&gt;
 * </code>
 *
 * <p>which will use the value of the request parameter named "method"
 * to pick the appropriate "execute" method, which must have the same
 * signature (other than method name) of the standard Action.execute
 * method.  For example, you might have the following three methods in the
 * same action:</p>
 * <ul>
 * <li>public \Phruts\Config\ActionConfig delete(ActionMapping mapping, \Phruts\Action\AbstractActionForm form,
 * \Symfony\Component\HttpFoundation\Request request, \Symfony\Component\HttpFoundation\Response response)     throws
 * Exception</li>
 * <li>public \Phruts\Config\ActionConfig insert(ActionMapping mapping, \Phruts\Action\AbstractActionForm form,
 * \Symfony\Component\HttpFoundation\Request request, \Symfony\Component\HttpFoundation\Response response)     throws
 * Exception</li>
 * <li>public \Phruts\Config\ActionConfig update(ActionMapping mapping, \Phruts\Action\AbstractActionForm form,
 * \Symfony\Component\HttpFoundation\Request request, \Symfony\Component\HttpFoundation\Response response)     throws
 * Exception</li>
 * </ul>
 * <p>and call one of the methods with a URL like this:</p>
 * <code>
 *   http://localhost/myapp/index.php?do=saveSubscription&method=update
 * </code>
 *
 * <p><strong>NOTE</strong> - All of the other mapping characteristics of
 * this action must be shared by the various handlers.  This places some
 * constraints over what types of handlers may reasonably be packaged into
 * the same <code>DispatchAction</code> subclass.</p>
 */
class ActionDispatcher extends \Phruts\Action
{
    // ----------------------------------------------------- Instance Variables

    // --------------------------------------------------------- Public Methods

    /**
	 * Process the specified HTTP request, and create the corresponding HTTP
	 * response (or forward to another web component that will create it).
	 * Return an <code>ForwardConfig</code> instance describing where and how
	 * control should be forwarded, or <code>null</code> if the response has
	 * already been completed.
	 *
	 * @param mapping The \Phruts\Config\ActionConfig used to select this instance
	 * @param form The optional \Phruts\Action\AbstractActionForm bean for this request (if any)
	 * @param request The HTTP request we are processing
	 * @param response The HTTP response we are creating
	 * @return ForwardConfig
	 * @exception Exception if an exception occurs
	 */
    public function execute(\Phruts\Config\ActionConfig $mapping, $form, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        //$log = \Phruts\Utils\LoggerManager::getLogger(__CLASS__);

        // See if this is cancelled
        if ($this->isCancelled($request)) {
            $af = $this->cancelled($mapping, $form, $request, $response);
            if ($af != null) {
                return $af;
            }
        }

        // Identify the request parameter containing the method name
        $parameter = $mapping->getParameter();
        if ($parameter == null) {
            $message = $this->getActionKernel()->getInternal()->getMessage("dispatch.handler", $mapping->getPath());
            //$log->error($message);
            throw new \Phruts\Exception($message);
        }

        // Identify the method name to be dispatched to.
        // dispatchMethod() will call unspecified() if name is null
        $name = $request->get($parameter);

        if ($name == 'perform' || $name == 'execute') {
            $message = $this->getActionKernel()->getInternal()->getMessage("dispatch.recursive", $mapping->getPath());
            //$log->error($message);
            throw new \Phruts\Exception($message);
        }

        // Invoke the named method, and return the result
        return $this->dispatchMethod($mapping, $form, $request, $response, $name);
    }

    /**
	 * Method which is dispatched to when there is no value for specified
	 * request parameter included in the request.  Subclasses of
	 * <code>DispatchAction</code> should override this method if they wish
	 * to provide default behavior different than producing an HTTP
	 * "Bad Request" error.
	 *
	 */
    protected function unspecified(\Phruts\Config\ActionConfig $mapping, $form, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $message = $this->getActionKernel()->getInternal()->getMessage("dispatch.parameter", $mapping->getPath(), $mapping->getParameter());
        //$log = \Phruts\Utils\LoggerManager::getRootLogger();
        //$log->error($message);
        $response->setStatusCode(400);
        $response->setContent($message);

        return (null);
    }

    // ----------------------------------------------------- Protected Methods

    /**
	 * Dispatch to the specified method.
	 * @return \Phruts\Config\ActionConfig
	 */
    protected function dispatchMethod(\Phruts\Config\ActionConfig $mapping, $form, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, $name)
    {
        //$log = \Phruts\Utils\LoggerManager::getRootLogger();

        // Make sure we have a valid method name to call.
        // This may be null if the user hacks the query string.
        if ($name == null) {
            return $this->unspecified($mapping, $form, $request, $response);
        }

        // Identify the method object to be dispatched to
        $reflectionClass = new \ReflectionClass(get_class($this));
        $method = null;
        if($reflectionClass->hasMethod($name)) {
            $method = $reflectionClass->getMethod($name);
        }

        if (empty($method) || !$method->isPublic()) {
            $message = $this->getActionKernel()->getInternal()->getMessage("dispatch.method", $mapping->getPath(), $name);
            //$log->error($message);
            $response->setStatusCode(500);
            $response->setContent($message);

            return (null);
        }

        // Invoke the method
        $forward = call_user_func(array($this, $name), $mapping, $form, $request, $response);

        // Return the returned ActionForward instance
        return ($forward);
    }

    /**
     * Method which is dispatched to when the request is a cancel button submit.
     * Subclasses of <code>DispatchAction</code> should override this method if
     * they wish to provide default behavior different than returning null.
     * @since Struts 1.2.0
     */
    protected function cancelled(ActionMapping $mapping, $form, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        return null;
    }
}
