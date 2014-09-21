<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Util;


use Phruts\Action\RequestDispatcher;

class RequestDispatcherProvider implements RequestDispatcherProviderInterface
{
    public function getRequestDispatcher($uri)
    {
        $rd = new RequestDispatcher();
        $rd->setUri($uri);
        return $rd;
    }
}
 