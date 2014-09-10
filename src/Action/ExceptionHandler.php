<?php

namespace Phruts\Action;

/**
 * An ExceptionHandler is configured in the Struts configuration file to handle
 * a specific type of exception thrown by an Action's execute method.
 *
 * @since Struts 1.1
 */
class ExceptionHandler
{
    /**
     * Handle the exception.
     * Return the <code>ActionForward</code> instance (if any) returned by
     * the called <code>ExceptionHandler</code>.
     *
     * @param ex \Exception The exception to handle
     * @param ae \Phruts\Config\ExceptionConfig The ExceptionConfig corresponding to the exception
     * @param mapping \Phruts\Action\ActionMapping The ActionMapping we are processing
     * @param formInstance The \Phruts\Action\AbstractActionForm we are processing
     * @param request The actionKernel request we are processing
     * @param response The actionKernel response we are creating
     * @return \Phruts\Config\ForwardConfig
     * @exception ActionKernelException if a actionKernel exception occurs
     *
     * @since Struts 1.1
     */
    public function execute(\Exception $ex,
                                 \Phruts\Config\ExceptionConfig $ae,
                                 \Phruts\Action\ActionMapping $mapping,
                                 $formInstance,
                                 \Symfony\Component\HttpFoundation\Request $request,
                                 \Symfony\Component\HttpFoundation\Response $response) {

        $forward = null; //ActionForward
        $error = null; //\Phruts\Action\ActionError
        $property = null; //String

        // Build the forward from the exception mapping if it exists
        // or from the form input
        if ($ae->getPath() != null) {
            $forward = new \Phruts\Config\ForwardConfig();
            $forward->setPath($ae->getPath());
        } else {
            $forward = $mapping->getInputForward();
        }

        // Figure out the error
        if ($ex instanceof \Phruts\Util\ModuleException) {
            $error = $ex->getActionMessage();
            $property = $ex->getProperty();
        } else {
            $error = new \Phruts\Action\ActionError($ae->getKey(), $ex->getMessage());
            $property = $error->getKey();
        }

        // Store the exception
        $request->attributes->set(\Phruts\Util\Globals::EXCEPTION_KEY, $ex);
        $this->storeException($request, $property, $error, $forward, $ae->getScope());

        return $forward;
    }

    /**
     * Default implementation for handling an <b>\Phruts\Action\ActionError</b> generated
     * from an Exception during <b>Action</b> delegation.  The default
     * implementation is to set an attribute of the request or session, as
     * defined by the scope provided (the scope from the exception mapping).  An
     * <b>\Phruts\Action\ActionErrors</b> instance is created, the error is added to the collection
     * and the collection is set under the \Phruts\Util\Globals.ERROR_KEY.
     *
     * @param request - The request we are handling
     * @param property  - The property name to use for this error
     * @param error - The error generated from the exception mapping
     * @param forward - The forward generated from the input path (from the form or exception mapping)
     * @param scope - The scope of the exception mapping.
     */
    protected function storeException(\Symfony\Component\HttpFoundation\Request $request,
                        $property,
                        \Phruts\Action\ActionError $error,
                        \Phruts\Config\ForwardConfig $forward,
                        $scope) {

        $errors = new \Phruts\Action\ActionErrors();
        $errors->add($property, $error);

        if ($scope == "request") {
            $request->attributes->set(\Phruts\Util\Globals::ERROR_KEY, $errors);
        } else {
            $session = $request->getSession();
            if(!empty($session))
                $request->getSession()->set(\Phruts\Util\Globals::ERROR_KEY, $errors);
        }
    }
}
