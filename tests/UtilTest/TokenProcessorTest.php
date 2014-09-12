<?php
namespace UtilTest;

use Phruts\Util\TokenProcessor;
use Symfony\Component\HttpFoundation\Request;

class TokenProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Phruts\Util\TokenProcessor
     */
    protected $tokenProcessor;

    public function setUp()
    {
        $this->request = new Request();
        $storage = new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);
        $this->request->setSession($session);
        $this->tokenProcessor = TokenProcessor::getInstance();
    }

    public function testFactory()
    {
        $this->assertNotEmpty($this->tokenProcessor);
    }

    public function testIsTokenValid()
    {
        $this->tokenProcessor->saveToken($this->request);
        $token = $this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY);
        $this->request->query->set(\Phruts\Util\Globals::TOKEN_KEY, $token);
        $this->assertTrue($this->tokenProcessor->isTokenValid($this->request));
    }

    public function testResetToken()
    {
        $this->tokenProcessor->saveToken($this->request);
        $this->assertNotEmpty($this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY));
        $this->tokenProcessor->resetToken($this->request);
        $this->assertEmpty($this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY));
    }

    public function testSaveToken()
    {
        $this->tokenProcessor->saveToken($this->request);
        $this->assertNotEmpty($this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY));
    }

    public function testGenerateToken()
    {
        $token = $this->tokenProcessor->generateToken($this->request);
        $token2 = $this->tokenProcessor->generateToken($this->request);
        $this->assertNotEmpty($token);
        $this->assertNotEmpty($token2);
        $this->assertNotEquals($token, $token2);
    }
}
 