<?php

namespace Phruts\Action;

/**
 * An encapsulation of an individual error message returned by the validate()
 * method of an \Phruts\Action\AbstractActionForm.
 *
 * Consisting of a message key (to be used to look up message text in an
 * appropriate message resources database) plus up to four placeholder objects
 * that can be used for parametric replacement in the message text.
 *
 * @author Cameron MANDERSON <cameronmanderson@gmail.com> (Phruts Contributor)
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
class ActionError extends ActionMessage
{
}
