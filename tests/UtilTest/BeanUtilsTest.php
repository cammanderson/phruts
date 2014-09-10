<?php
namespace UtilTest;

class BeanUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testPopulate()
    {

        // Properties
        $properties = array(
            'var' => 'value1',
            'variableTwo' => 'value2',
            'private' => 'value3',
            'hidden' => 'value4'
        );
        $bean = new Bean;

        \Phruts\Util\BeanUtils::populate($bean, $properties);
        $this->assertEquals('value1', $bean->getVar());
        $this->assertEquals('value2', $bean->variableTwo);

        $this->setExpectedException('\Exception');
        \Phruts\Util\BeanUtils::populate(null, $properties);
    }
}


class Bean {
    protected $var;
    public $variableTwo;
    protected $var3;
    protected $hidden;
    protected $private;

    /**
     * @param mixed $var
     */
    public function setVar($var)
    {
        $this->var = $var;
    }

    /**
     * @return mixed
     */
    public function getVar()
    {
        return $this->var;
    }

    /**
     * @param mixed $var3
     */
    public function setVar3($var3)
    {
        $this->var3 = $var3;
    }

    /**
     * @return mixed
     */
    public function getVar3()
    {
        return $this->var3;
    }

    /**
     * @param mixed $variableTwo
     */
    public function setVariableTwo($variableTwo)
    {
        $this->variableTwo = $variableTwo;
    }

    /**
     * @return mixed
     */
    public function getVariableTwo()
    {
        return $this->variableTwo;
    }

    /**
     * @param mixed $private
     */
    private function setPrivate($private)
    {
        $this->private = $private;
    }

    /**
     * @return mixed
     */
    private function getPrivate()
    {
        return $this->private;
    }



}
 