<?php

namespace Phruts\Action;

/**
 * <p>An encapsulation of an individual message returned by the
 * <code>validate()</code> method of an <code>\Phruts\Action\Form</code>, consisting
 * of a message key (to be used to look up message text in an appropriate
 * message resources database) plus up to four placeholder objects that can
 * be used for parametric replacement in the message text.</p>
 *
 * @author Cameron MANDERSON <cameronmanderson@gmail.com>
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) * @since Struts 1.1
 */
class Message
{
    /**
	 * The message key for this message.
	 *
	 * @var string
	 */
    protected $key = null;

    /**
	 * The replacement values for this message.
	 *
	 * @var array
	 */
    protected $values = null;

    /**
	 * Construct an action message with the specified replacement values.
	 *
	 * @param string $key The Message key for this message
	 * @param string $value0 First replacement value
	 * @param string $value1 Second replacement value
	 * @param string $value2 Third replacement value
	 * @param string $value3 Fourth replacement value
	 */
    public function __construct($key, $value0 = '', $value1 = '', $value2 = '', $value3 = '')
    {
        $this->key = (string) $key;

        $this->values = array (
            (string) $value0,
            (string) $value1,
            (string) $value2,
            (string) $value3
        );
    }

    /**
	 * Get the message key for this message.
	 *
	 * @return string
	 */
    public function getKey()
    {
        return $this->key;
    }

    /**
	 * Get the replacement values for this message.
	 *
	 * @return array
	 */
    public function getValues()
    {
        return $this->values;
    }
}
