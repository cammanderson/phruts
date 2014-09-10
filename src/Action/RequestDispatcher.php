<?php
namespace Phruts\Action;

use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class RequestDispatcher
 * @package Phruts
 */
class RequestDispatcher implements RequestDispatcherInterface
{

    /**
     * @var \Silex\Application
     */
    protected $application;

    public function doForward(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Obtain the information about the forward
        $forwardConfig = $request->attributes->get(\Phruts\Globals::FORWARD_CONFIG);

        // Perform the forward
        if ($forwardConfig->getRedirect() == false) {
            $subRequest = \Symfony\Component\HttpFoundation\Request::create($forwardConfig->getPath(), 'GET');

            return $this->application->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        } else {
            $this->application->redirect($forwardConfig->getPath());
        }
    }

    public function doInclude(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // TODO: Implement doInclude() method.
    }

}
