<?php

namespace Phruts\Action;

/**
 * A class that encapsulates the error messages being reported by the validate()
 * method of a \Phruts\Action\AbstractActionForm.
 *
 * <p>Validation errors are either global to the entire \Phruts\Action\AbstractActionForm bean
 * they are associated with, or they are specific to a particular bean property
 * (and, therefore, a particular input field on the corresponding form).</p>
 * <p>Each individual error is described by an \Phruts\Action\ActionError object, which
 * contains a message key (to be looked up in an appropriate message resources
 * database), and up to four placeholder arguments used for parametric
 * substitution in the resulting message.</p>
 */
class ActionErrors extends ActionMessages
{
    /**
	 * The "property name" marker to use for global errors, as opposed to those
	 * related to a specific property.
	 */
    const GLOBAL_ERROR = '\Phruts\Action\GLOBAL_ERROR';
}
