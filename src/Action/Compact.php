<?php

namespace Phruts\Action\Compact;

use Phruts\Action;

/**
 * Similar to a dispatch action, the compact action allows you
 * to write more compact actions by eliminating method parameters
 * and adopting a convention of 'actionAction' in the method name
 *
 * @author Cameron Manderson <cameronmanderson@gmail.com> (Aloi Contributor)
 */
class Compact extends Action
{
    const ACTION_METHOD_PREPEND = 'execute';
    const ACTION_PARAMETER = 'action';

    // Instance Variables
    protected $request;
    protected $response;
    protected $form;
    protected $mapping;
    protected $method;

    public function init(\Phruts\Config\Action $mapping, $form, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Assign the local scope
        $this->request = $request;
        $this->response = $response;
        $this->form = $form;
        $this->mapping = $mapping;
    }

    // --------------------- Execute/Dispatch --------------
    public function execute(\Phruts\Config\Action $mapping, $form, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Initialise
        $this->init($mapping, $form, $request, $response);

        // Log local here
        //$log = Aloi_Util_Logger_Manager::getLogger(__CLASS__);

        // Check for cancelled actions
        if ($this->isCancelled($request)) {
            //$log->info('Action cancelled');
            $forward = $this->cancelledAction();
            if(!empty($forward)) return $forward;
        }

        // Look for the name of the parameter
        $parameter = self::ACTION_PARAMETER;

        // Identify the action method from the request
        $this->method = $request->getParameter($parameter);

        // Init
        return $this->dispatchCompactMethod($this->method);
    }

    protected function dispatchCompactMethod($method)
    {
        //$log = Aloi_Util_Logger_Manager::getLogger(__CLASS__);

        // Look for the corresponding method
        if (!trim($method)) {
            return $this->executeIndex();
        }

        // Dispatch the method
        $localMethodName = self::ACTION_METHOD_PREPEND . ucfirst($method);
        if (!method_exists($this, $localMethodName)) {
            //$log = Aloi_Util_Logger_Manager::getLogger(__CLASS__);
            $message = $this->getServlet()->getInternal()->getMessage('compact.dispatchcompactmethod', $this->getMapping()->getPath(), $this->getMapping()->getParameter());
            //$log->error($message);
            $response->sendError(\Symfony\Component\HttpFoundation\Response::SC_BAD_REQUEST, $message);

            return null;
        }

        // Log an info
        //$log->info('Dispatching method: ' . $localMethodName);

        // invoke
        $forward = call_user_func(array($this, $localMethodName));

        return $forward;
    }

    // --------------------- Default actions --------------
    public function executeIndex()
    {
        //$log = Aloi_Util_Logger_Manager::getLogger(__CLASS__);
        $message = $this->getServlet()->getInternal()->getMessage('compact.index', $this->getMapping()->getPath(), $this->getMapping()->getParameter());
        //$log->error($message);
        $response->sendError(\Symfony\Component\HttpFoundation\Response::SC_BAD_REQUEST, $message);
    }
    public function executeCancelled()
    {
        return null;
    }

    // --------------------- Internal accessors --------------
    protected function getRequest()
    {
        return $this->request;
    }
    protected function getResponse()
    {
        return $this->response;
    }
    protected function getForm()
    {
        return $this->form;
    }
    protected function getMapping()
    {
        return $this->mapping;
    }
}
