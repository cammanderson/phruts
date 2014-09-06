<?php
namespace ActionTest;

use Phruts\Action\ActionMessage;
use Phruts\Action\ActionMessageItem;
use Phruts\Action\ActionMessages;
use Phruts\Action\ActionError;
use Phruts\Action\ActionErrors;

class ActionMessagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phruts\Action\ActionMessages;
     */
    protected $actionMessages;

    public function setUp()
    {
        $this->actionMessages = new ActionMessages();
    }

    public function testInstantiate()
    {
        $object = new ActionMessages();
        $this->assertTrue($object instanceof \Phruts\Action\ActionMessages);

        $object = new ActionMessage('key', 1, 2, 3);
        $this->assertTrue($object instanceof \Phruts\Action\ActionMessage);

        $object = new ActionErrors();
        $this->assertTrue($object instanceof \Phruts\Action\ActionMessages);

        $object = new ActionError('key', 1, 2, 3);
        $this->assertTrue($object instanceof \Phruts\Action\ActionMessage);
    }

    public function testCollection()
    {
        $this->assertEquals(0, $this->actionMessages->size());
        $this->assertTrue($this->actionMessages->isEmpty());

        $actionMessage = new ActionMessage('key');
        $this->actionMessages->add('property', $actionMessage);

        $this->assertEquals(1, $this->actionMessages->size());

        $actionMessage2 = new ActionMessage('key');
        $this->actionMessages->add('property', $actionMessage2);
        $this->assertEquals(2, $this->actionMessages->size());
        $this->assertFalse($this->actionMessages->isEmpty());

        $actionMessage3 = new ActionMessage('key');
        $this->actionMessages->add('property2', $actionMessage3);
        $this->assertEquals(1, $this->actionMessages->size('property2'));
        $this->actionMessages->add('property', $actionMessage3);
        $this->assertEquals(3, $this->actionMessages->size('property'));

        $actionMessages = new ActionMessages();
        $actionMessage4 = new ActionMessage('defg');
        $actionMessage5 = new ActionMessage('asdf');
        $actionMessages->add('property', $actionMessage4);
        $actionMessages->add('property2', $actionMessage5);

        $this->actionMessages->addMessages($actionMessages);
        $this->assertEquals(2, $this->actionMessages->size('property2'));
        $this->assertEquals(4, $this->actionMessages->size('property'));
        $this->assertEquals(6, $this->actionMessages->size());

        $this->assertEquals(array('property', 'property2'), $this->actionMessages->properties());

        $this->assertFalse($this->actionMessages->isAccessed());

        $this->assertEquals(0, count($this->actionMessages->get('property3')));
        $this->assertTrue($this->actionMessages->isAccessed());
        $this->assertEquals(6, count($this->actionMessages->get()));
        $this->assertEquals(2, count($this->actionMessages->get('property2')));
        $this->assertEquals(0, count($this->actionMessages->get('property3')));
        $this->actionMessages->clear();
        $this->assertEquals(0, $this->getSize());
        $this->assertTrue($this->actionMessages->isEmpty());

        $actionMessages2 = new ActionMessages();
        $this->assertEquals(0, count($actionMessages2->get()));
        $this->assertEquals(0, ($actionMessages2->size('property3')));
    }

    public function testActionMessage()
    {
        $actionMessage = new ActionMessage('1234', '1', '2', '3', '4');
        $this->assertEquals('1234', $actionMessage->getKey());
        $this->assertEquals(array('1','2','3','4'), $actionMessage->getValues());
    }

    public function testActionMessageItem()
    {
        $actionMessage1 = new ActionMessage('1234', '1', '2', '3', '4');
        $actionMessage2 = new ActionMessage('5678', '5', '6', '7', '8');
        $actionMessageItem = new ActionMessageItem(array($actionMessage1, $actionMessage2), 10);
        $this->assertEquals(10, $actionMessageItem->getOrder());
        $this->assertEquals(2, count($actionMessageItem->getList()));

        $actionMessageItem->setOrder(100);
        $this->assertEquals(100, $actionMessageItem->getOrder());

        $list = $actionMessageItem->getList();
        $actionMessage3 = new ActionMessage('9101112', '9', '10', '11', '12');
        $list[] = $actionMessage3;
        $actionMessageItem->setList($list);
        $this->assertEquals(3, count($actionMessageItem->getList()));

        $actionMessage4 = new ActionMessage('13141516', '13', '14', '15', '16');
        $actionMessageItem->add($actionMessage4);
        $this->assertEquals(4, count($actionMessageItem->getList()));
    }
}
 