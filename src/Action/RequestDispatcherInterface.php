<?php
namespace Phruts\Action;

/**
 * Interface RequestDispatcherInterface
 * @package Phruts
 */
interface RequestDispatcherInterface
{
    public function doForward(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response);
    public function doInclude(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response);
}
