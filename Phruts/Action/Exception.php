<?php

namespace Phruts\Action;

use Phruts\Config\ExceptionConfig;

/**
 * An <strong>\Phruts\Action\Exception</strong> represents a potential exception
 * that may occur during delegation to an Action class.
 * Instances of this class may be configured in association
 * with an <code>ActionMapping</code> instance for named lookup of potentially
 * multiple destinations for a particular mapping instance.
 * <p>
 * An <code>\Phruts\Action\Exception</code> has the following minimal set of properties.
 * Additional properties can be provided as needed by subclassses.
 *
 * <ul>
 * <li><strong>type</strong> - The fully qualified class name of the
 * exception to be associated to a particular <code>ActionMapping</code>.
 * <li><strong>key</strong>  - (Optional) Message key associated with the
 * particular exception.
 * <li><strong>path</strong> - (Optional) Context releative URI that should
 * be redirected to as a result of the exception occuring.  Will overide the
 * input form of the associated ActionMapping if one is provided.
 * <li><strong>scope</strong> - (Optional) The scope to store the exception in
 * if a problem should occur - defaults to 'request'.  Valid values are
 * 'request' and 'session'.
 *
 * <li><strong>hierarchical</strong> - (Optional) Defines whether or not the
 * Exception hierarchy should be used when determining if an occuring
 * exception can be assigned to a mapping instance.  Default is true.
 * <li><strong>handler</strong> - (Optional) The fully qualified class name
 * of the handler, which is responsible to handle this exception.
 * Default is 'org.apache.struts.action.ExceptionHandler'.
 * </ul>
 *
 * @author Cameron Manderson (Contributor from Aloi)
 * @author ldonlan * @deprecated Replaced by org.apache.struts.config.ExceptionConfig
 */
class Exception extends ExceptionConfig
{
    /**
	 * Returns an instance of an <code>\Phruts\Action\Error</code> configured for this
	 * exception.
	 * @return Error
	 */
    public function getError()
    {
        return new Error($this->key);
    }
}
