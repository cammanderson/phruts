<?php
namespace UtilTest;

use Phruts\Util\RequestUtils;

class RequestUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testGetModuleName()
    {
        $application = new \Silex\Application();
        $application[\Phruts\Util\Globals::PREFIXES_KEY] = array('admin', 'other');

        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())->method('getPathInfo')->willReturn('admin/example');
        $this->assertEquals('admin/example',$request->getPathInfo());
        $this->assertEquals('admin', RequestUtils::getModuleName($request, $application));

        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())->method('getPathInfo')->willReturn('example');
        $this->assertEquals('', RequestUtils::getModuleName($request, $application));

        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())->method('getPathInfo')->willReturn('other/example');
        $this->assertEquals('other', RequestUtils::getModuleName($request, $application));
    }
}
 