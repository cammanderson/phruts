<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */
namespace Phruts;

use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class RequestDispatcher
 * @author Cameron Manderson <cameronmanderson@gmail.com>
 * @package Phruts
 */
class RequestDispatcher implements RequestDispatcherInterface
{

    protected $app;

    public function doForward(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // Obtain the information about the forward
        $forwardConfig = $request->attributes->get(\Phruts\Globals::FORWARD_CONFIG);

        // Perform the forward
        if ($forwardConfig->getRedirect() == false) {
            $subRequest = \Symfony\Component\HttpFoundation\Request::create($forwardConfig->getPath(), 'GET');

            return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        } else {
            $this->app->redirect($forwardConfig->getPath());
        }
    }

    public function doInclude(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        // TODO: Implement doInclude() method.
    }

}
