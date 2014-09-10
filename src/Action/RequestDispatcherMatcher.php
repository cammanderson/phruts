<?php
namespace Phruts\Action;

class RequestDispatcherMatcher {
    protected $dispatcher;

    function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function getRequestDispatcher($uri)
    {
        return $this->dispatcher;
    }
}
 