<?php

namespace Phruts\Action;

/**
 * An ExceptionHandler is configured in the Struts configuration file to handle
 * a specific type of exception thrown by an Action's execute method.
 *
 * @author Cameron Manderson (Contributor from Aloi)
 * @author Original struts author unknown
 * @since Struts 1.1 */
class ExceptionHandler
{
    /**
     * Handle the exception.
     * Return the <code>ActionForward</code> instance (if any) returned by
     * the called <code>ExceptionHandler</code>.
     *
     * @param ex The exception to handle
     * @param ae The ExceptionConfig corresponding to the exception
     * @param mapping The ActionMapping we are processing
     * @param formInstance The \Phruts\Action\Form we are processing
     * @param request The servlet request we are processing
     * @param response The servlet response we are creating
     * @return ActionForward
     * @exception ServletException if a servlet exception occurs
     *
     * @since Struts 1.1
     */
    public function execute(\Exception $ex,
                                 \Phruts\Config\ExceptionConfig $ae,
                                 \Phruts\Config\Action $mapping,
                                 $formInstance,
                                 \Symfony\Component\HttpFoundation\Request $request,
                                 \Symfony\Component\HttpFoundation\Response $response) {

        $forward = null; //ActionForward
        $error = null; //\Phruts\Action\Error
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
        if ($ex instanceof \Phruts\Config\ModuleException) {
            $error = $ex->getError();
            $property = $ex->getProperty();
        } else {
            $error = new \Phruts\Action\Error($ae->getKey(), $ex->getMessage());
            $property = $error->getKey();
        }

        // Store the exception
        $request->setAttribute(\Phruts\Globals::EXCEPTION_KEY, $ex);
        $this->storeException($request, $property, $error, $forward, $ae->getScope());

        return $forward;
    }

    /**
     * Default implementation for handling an <b>\Phruts\Action\Error</b> generated
     * from an Exception during <b>Action</b> delegation.  The default
     * implementation is to set an attribute of the request or session, as
     * defined by the scope provided (the scope from the exception mapping).  An
     * <b>\Phruts\Action\Errors</b> instance is created, the error is added to the collection
     * and the collection is set under the \Phruts\Globals.ERROR_KEY.
     *
     * @param request - The request we are handling
     * @param property  - The property name to use for this error
     * @param error - The error generated from the exception mapping
     * @param forward - The forward generated from the input path (from the form or exception mapping)
     * @param scope - The scope of the exception mapping.
     */
    protected function storeException(\Symfony\Component\HttpFoundation\Request $request,
                        $property,
                        \Phruts\Action\Error $error,
                        \Phruts\Config\ForwardConfig $forward,
                        $scope) {

        $errors = new \Phruts\Action\Errors();
        $errors->add($property, $error);

        if ($scope == "request") {
            $request->setAttribute(\Phruts\Globals::ERROR_KEY, $errors);
        } else {
            $request->getSession()->setAttribute(\Phruts\Globals::ERROR_KEY, $errors);
        }
    }
}
