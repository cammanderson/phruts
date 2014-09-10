<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */

class MessageResourcesTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {

        // Test the factory
        $this->assertEquals('\Phruts\Util\PropertyMessageResourcesFactory', \Phruts\Util\MessageResourcesFactory::getFactoryClass());
        $propertyMessageResourcesFactory = \Phruts\Util\PropertyMessageResourcesFactory::createFactory();
        $this->assertNotEmpty($propertyMessageResourcesFactory);

        // Create the resources
        $propertyMessageResources = $propertyMessageResourcesFactory->createResources(__DIR__ . '/Example');
        $this->assertNotEmpty($propertyMessageResources->getMessage(null, 'example'));
    }

}
 