<?php
namespace UtilTest;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testValidClassNames()
    {
        $this->assertTrue(\Phruts\Util\ClassLoader::isValidClassName('\Doctrine\Common\IsolatedClassLoader'), '\Doctrine\Common\IsolatedClassLoader');
        $this->assertTrue(\Phruts\Util\ClassLoader::isValidClassName('\Zend\Acl'), '\Zend\Acl');
        $this->assertTrue(\Phruts\Util\ClassLoader::isValidClassName('\namespace\package_name\Class_Name'), '\namespace\package_name\Class_Name');
        $this->assertTrue(\Phruts\Util\ClassLoader::isValidClassName('\namespace\package\Class_Name'), '\namespace\package\Class_Name');
    }

    public function testInvalidClassNames()
    {
        $this->assertFalse(\Phruts\Util\ClassLoader::isValidClassName('\1Doctrine\Common\IsolatedClassLoader'), '\1Doctrine\Common\IsolatedClassLoader');
        $this->assertFalse(\Phruts\Util\ClassLoader::isValidClassName('Doctrine\Common\IsolatedClassLoader'), 'Doctrine\Common\IsolatedClassLoader');
        $this->assertFalse(\Phruts\Util\ClassLoader::isValidClassName('\Doctrine\Common\Isolated.ClassLoader'), 'Doctrine\Common\Isolated.ClassLoader');
        $this->assertFalse(\Phruts\Util\ClassLoader::isValidClassName(''));
    }

    public function testGetClassName()
    {
        $this->assertEquals('Action', \Phruts\Util\ClassLoader::getClassName('\Phruts\Action\Action'), 'Action');
        $this->setExpectedException('\Phruts\Exception\ClassNotFoundException');
        \Phruts\Util\ClassLoader::getClassName('\NonExistant\Class');
    }

    public function testLoadClass()
    {
        $this->assertEquals('Action', \Phruts\Util\ClassLoader::loadClass('\Phruts\Action\Action'), '\Phruts\Action\Action');
        $this->setExpectedException('\Phruts\Exception\ClassNotFoundException');
        \Phruts\Util\ClassLoader::loadClass('\NonExistant\Class');
        $this->setExpectedException('\Phruts\Exception\IllegalArgumentException');
        \Phruts\Util\ClassLoader::loadClass('\1');
    }

    public function testClassIsAssignableFrom()
    {
        $this->assertTrue(\Phruts\Util\ClassLoader::classIsAssignableFrom('\Phruts\Actions\ActionDispatcher', '\Phruts\Action\Action'));
        $this->assertTrue(\Phruts\Util\ClassLoader::classIsAssignableFrom('\Phruts\Action\Action', '\Phruts\Action\Action'));
        $this->assertTrue(\Phruts\Util\ClassLoader::classIsAssignableFrom('\UtilTest\B', '\UtilTest\A'));
    }

    public function testNewInstance()
    {
        $this->assertNotEmpty(\Phruts\Util\ClassLoader::newInstance('\Phruts\Actions\ActionDispatcher', '\Phruts\Action\Action'));
        $this->assertNotEmpty(\Phruts\Util\ClassLoader::newInstance('\Phruts\Action\Action', '\Phruts\Action\Action'));

    }
    public function testNewInstanceAbstract()
    {
        // Test Abstract
        $this->setExpectedException('\Phruts\Exception\InstantiationException');
        \Phruts\Util\ClassLoader::newInstance('\Phruts\Action\AbstractActionForm');
    }

    public function testNewInstanceConstructor()
    {
        // Test params in Constructor
        $this->setExpectedException('\Phruts\Exception\InstantiationException');
        \Phruts\Util\ClassLoader::newInstance('\Phruts\Action\ActionKernel');
    }

    public function testNewInstancePrivateConstructor()
    {
        // TODO: Test private constructor
//        $this->setExpectedException('\Phruts\Exception\InstantiationException');
//        \Phruts\Util\ClassLoader::newInstance('C');
    }

}

// Inject a class test for assignable test
interface A {
    public function foo();
}
class B implements A {
    public function foo() {}
}
