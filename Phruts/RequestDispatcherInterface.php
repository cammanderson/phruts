<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */
namespace Phruts;

interface RequestDispatcherInterface
{
    public function doForward(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response);
    public function doInclude(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response);
}
