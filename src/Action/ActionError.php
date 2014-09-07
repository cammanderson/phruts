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
 */
class ActionError extends ActionMessage
{
}
