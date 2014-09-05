<?php
namespace Phruts\Action;

/**
 * This class is used to store a set of messages associated with a property/key
 * and the position it was initially added to list.
 *
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
class ActionMessageItem
{
    /**
     * The list of \Phruts\Action\ActionMessage.
     *
     * @var array
     */
    protected $list = null;

    /**
     * The position in the list of messages.
     *
     * @var integer
     */
    protected $iOrder = 0;

    /**
     * @param array $list The list of ActionMessages.
     * @param integer $iOrder The position in the list of messages.
     */
    public function __construct(array $list, $iOrder)
    {
        $this->list = $list;
        $this->iOrder = (integer) $iOrder;
    }

    /**
     * @return array
     */
    public function & getList() {
        return $this->list;
    }

    /**
     * @param array $list
     */
    public function setList(array $list)
    {
        $this->list = $list;
    }

    /**
     * @return integer
     */
    public function getOrder()
    {
        return $this->iOrder;
    }

    /**
     * @param integer $iOrder
     */
    public function setOrder($iOrder)
    {
        $this->iOrder = (integer) $iOrder;
    }
}
