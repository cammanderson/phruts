<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Util;


interface RequestDispatcherProviderInterface 
{
    public function getRequestDispatcher($uri);
} 