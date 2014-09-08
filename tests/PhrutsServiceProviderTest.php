<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */

class PhrutsServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $application = new \Silex\Application();

        $service = new \Phruts\PhrutsServiceProvider();
        $service->register($application);
        $service->boot($application);
        $this->assertNotEmpty($application[\Phruts\Globals::ACTION_KERNEL]);
    }
}
 