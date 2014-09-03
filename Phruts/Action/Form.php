<?php

namespace Phruts\Action;

/**
 * A \Phruts\Action\Form is a PHPBean optionally associated with one or more
 * \Phruts\Config\Action.
 *
 * <p>Such a bean will have had its properties initialized from the
 * corresponding request parameters before the corresponding action's
 * <samp>execute</samp> method is called.</p>
 * <p>When the properties of this bean have been populated, but before the
 * <samp>execute</samp> method of the action is called, this bean's
 * <samp>validate</samp> method will be called, which gives the bean a chance
 * to verify that the properties submitted by the user are correct and valid.
 * If this method finds problems, it returns an error messages object that
 * encapsulates those problems, and the controller servlet will return control
 * to the corresponding input form.  Otherwise, the <samp>validate</samp>
 * method returns null, indicating that everything is acceptable and the
 * corresponding Action's <samp>execute()</samp> method should be
 * called.</p>
 * <p>This class must be subclassed in order to be instantiated. Subclasses
 * should provide property getter and setter methods for all of the bean
 * properties they wish to expose, plus override any of the public or protected
 * methods for which they wish to provide modified functionality.</p>
 *
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) * @todo Manage setServlet() calls with or without null argument.
 */
abstract class Form
{
    /**
	 * The controller servlet instance to which we are attached.
	 *
	 * @var ActionServlet
	 */
    protected $servlet = null;

    /**
	 * Return the controller servlet instance to which we are attached.
	 *
	 * @return ActionServlet
	 */
    public function getServlet()
    {
        return $this->servlet;
    }

    /**
	 * Set the controller servlet instance to which we are attached (if servlet
	 * is non-null), or release any allocated resources (if servlet is null).
	 *
	 * @param ActionServlet $servlet The new controller servlet, if any
	 * @todo Check if the parameter is a ActionServlet object.
	 */
    public function setServlet($servlet)
    {
        $this->servlet = $servlet;
    }

    /**
	 * Reset all bean properties to their default state.
	 *
	 * <p>This method is called before the properties are repopulated by the
	 * controller servlet.</p>
	 * <p>The default implementation does nothing. Subclasses should override
	 * this method to reset all bean properties to default values.</p>
	 * <p>This method is <b>not</b> the appropriate place to initialize form
	 * values for an "update" type page (this should be done in a setup
	 * Action). You mainly need to worry about setting checkbox values to
	 * false; most of the time you can leave this method unimplemented.</p>
	 *
	 * @param \Phruts\Config\Action $mapping The mapping used to select this
	 * instance
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 */
    public function reset(\Phruts\Config\Action $mapping, \Symfony\Component\HttpFoundation\Request $request)
    {
        // Default implementation does nothing
    }

    /**
	 * Validate the properties that have been set for this HTTP request, and
	 * return a \Phruts\Action\Errors object that encapsulates any validation errors
	 * that have been found.
	 *
	 * <p>If no errors are found, return null or an \Phruts\Action\Errors object
	 * with no recorded error messages.</p>
	 * <p>The default implementation performs no validation and returns null.
	 * Subclasses must override this method to provide any validation they wish
	 * to perform.</p>
	 *
	 * @param \Phruts\Config\Action $mapping The mapping used to select this
	 * instance
	 * @param \Symfony\Component\HttpFoundation\Request $request The servlet request we are
	 * processing
	 * @return \Phruts\Action\Errors
	 */
    public function validate(\Phruts\Config\Action $mapping, \Symfony\Component\HttpFoundation\Request $request)
    {
        return null;
    }
}
