<?php

namespace Phruts\Actions;

use Phruts\Action\AbstractActionForm;
use Phruts\Action\ActionMapping;
use Phruts\Config\ForwardConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ForwardAction extends \Phruts\Action\Action
{
    public function execute(
        ActionMapping $mapping,
        AbstractActionForm $form = null,
        Request $request,
        Response $response
    ) {

        // Action Config defines the parameter for the forward configuration
        $parameter = $mapping->getParameter();
        if (empty($parameter)) {
            throw new \Phruts\Exception\IllegalArgumentException('Need to specify a parameter for this ForwardAction');
        }

        // Original strategy, let's assume it is a path
        if (!preg_match('/^[A-z]+$/', $parameter)) {
            $forward = new ForwardConfig();
            $forward->setPath($parameter);
            $forward->setContextRelative(true);

            return $forward;
        } else {
            // Forward the request
            $forward = $mapping->findForwardConfig($parameter);
            if (empty($forward)) {
                throw new \Phruts\Exception('ForwardAction parameter should reference a forward config name');
            }

            return $forward;
        }
    }
}
