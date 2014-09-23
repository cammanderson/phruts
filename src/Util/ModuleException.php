<?php
namespace Phruts\Util;

use Exception;
use Phruts\Action\ActionMessage;

class ModuleException extends \Phruts\Exception
{
    /**
     * @var \Phruts\Action\ActionMessage
     */
    protected $actionMessage;

    protected $property;

    public function __construct(ActionMessage $actionMessage, $code = 0, Exception $previous = null)
    {
        parent::__construct($actionMessage->getKey(), $code, $previous);
    }

    /**
     * @param \Phruts\Action\ActionMessage $actionMessage
     */
    public function setActionMessage($actionMessage)
    {
        $this->actionMessage = $actionMessage;
    }

    /**
     * @return \Phruts\Action\ActionMessage
     */
    public function getActionMessage()
    {
        return $this->actionMessage;
    }

    /**
     * @param mixed $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }
}
