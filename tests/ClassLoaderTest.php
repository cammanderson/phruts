<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testValidClassNames()
    {
        $this->assertTrue(\Phruts\ClassLoader::isValidClassName('\Doctrine\Common\IsolatedClassLoader'), '\Doctrine\Common\IsolatedClassLoader');
        $this->assertTrue(\Phruts\ClassLoader::isValidClassName('\Zend\Acl'), '\Zend\Acl');
        $this->assertTrue(\Phruts\ClassLoader::isValidClassName('\namespace\package_name\Class_Name'), '\namespace\package_name\Class_Name');
        $this->assertTrue(\Phruts\ClassLoader::isValidClassName('\namespace\package\Class_Name'), '\namespace\package\Class_Name');
    }

    public function testInvalidClassNames()
    {
        $this->assertFalse(\Phruts\ClassLoader::isValidClassName('\1Doctrine\Common\IsolatedClassLoader'), '\1Doctrine\Common\IsolatedClassLoader');
        $this->assertFalse(\Phruts\ClassLoader::isValidClassName('Doctrine\Common\IsolatedClassLoader'), 'Doctrine\Common\IsolatedClassLoader');
        $this->assertFalse(\Phruts\ClassLoader::isValidClassName('\Doctrine\Common\Isolated.ClassLoader'), 'Doctrine\Common\Isolated.ClassLoader');
        $this->assertFalse(\Phruts\ClassLoader::isValidClassName(''));
    }

    public function testGetClassName()
    {
        $this->assertEquals('Action', \Phruts\ClassLoader::getClassName('\Phruts\Action'), 'Action');
        $this->setExpectedException('\Phruts\Exception\ClassNotFoundException');
        \Phruts\ClassLoader::getClassName('\NonExistant\Class');
    }

    public function testLoadClass()
    {
        $this->assertEquals('Action', \Phruts\ClassLoader::loadClass('\Phruts\Action'), '\Phruts\Action');
        $this->setExpectedException('\Phruts\Exception\ClassNotFoundException');
        \Phruts\ClassLoader::loadClass('\NonExistant\Class');
    }

    public function testClassIsAssignableFrom()
    {
        $this->assertTrue(\Phruts\ClassLoader::classIsAssignableFrom('\Phruts\Actions\ActionDispatcher', '\Phruts\Action'));
        $this->assertTrue(\Phruts\ClassLoader::classIsAssignableFrom('\Phruts\Action', '\Phruts\Action'));
        $this->assertTrue(\Phruts\ClassLoader::classIsAssignableFrom('B', 'A'));

    }
}


interface A {
    public function foo();
}
class B implements A {
    public function foo() {}
}