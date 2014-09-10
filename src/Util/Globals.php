<?php
namespace Phruts\Util;

/**
 * Global manifest constants for the entire PHruts framework.
 *
 */
class Globals
{
    /**
	 * @todo Comment the constant.
	 */
    const SESSION_GC = '\Phruts\Action\SESSION_GS_LASTTIME';

    /**
     * The session attributes key under which our transaction token is
     * stored, if it is used.
     */
    const TRANSACTION_TOKEN_KEY = '\Phruts\Action\TOKEN';
    const TOKEN_KEY = '\Phruts\Action\TOKEN_KEY';

    /**
	 * The property under which a Cancel button press is reported.
	 */
    const CANCEL_PROPERTY = '\Phruts\Action\CANCEL';

    /**
	 * The context attributes key under which our ActionKernel instance will
	 * be stored.
	 */
    const ACTION_SERVLET_KEY = '\Phruts\Action\ACTION_SERVLET';

    /**
	 * The base of the context attributes key under which our module
	 * MessageResources will be stored.
	 *
	 * For each request processed by the controller, the MessageResources object
	 * for the module selected by the request URI currently being processed will
	 * also be exposed under this key as a request attribute.
	 */
    const MESSAGES_KEY = '\Phruts\Action\ActionMessageS';

    /**
	 * The base of the context attribute key under which our ModuleConfig
	 * data structure will be stored.
	 *
	 * This will be suffixed with the actual module prefix (including the
	 * leading "/" character) to form the actual attributes key.
	 * For each request processed by the controller actionKernel, the ModuleConfig
	 * object for the module selected by the request URI currently being
	 * processed will also be exposed under this key as a request attribute.
	 *
	 */
    const MODULE_KEY = '\Phruts\Action\MODULE';

    /**
	 * The base of the context attributes key under which an array of PlugIn
	 * instances will be stored.
	 *
	 * This will be suffixed with the actual module prefix (including the leading
	 * "/" character) to form the actual attributes key.
	 */
    const PLUG_INS_KEY = '\Phruts\Action\PLUG_INS';

    /**
	 * The context attribute under which we store our prefixes list.
	 */
    const PREFIXES_KEY = '\Phruts\Action\PREFIXES';

    /**
	 * The base of the context attributes key under which our RequestProcessor
	 * instance will be stored.
	 *
	 * This will be suffixed with the actual module prefix (including the leading
	 * "/" character) to form the actual attributes key.
	 */
    const REQUEST_PROCESSOR_KEY = '\Phruts\Action\REQUEST_PROCESSOR';

    /**
	 * The session attributes key under which the user's selected Locale is
	 * stored, if any.
	 *
	 * If no such attribute is found, the system default locale will be used
	 * when retrieving internationalized messages. If used, this attribute is
	 * typically set during user login processing.
	 */
    const LOCALE_KEY = '\Phruts\Action\LOCALE';

    /**
	 * The request attributes key under which our \Phruts\Config\ActionConfig instance is passed.
	 */
    const MAPPING_KEY = '\Phruts\Action\MAPPING_INSTANCE';

    /**
	 * The request attributes key under which a boolean true value should be
	 * stored if this request was cancelled.
	 */
    const CANCEL_KEY = '\Phruts\Action\CANCEL';

    /**
	 * The request attributes key under which your action should store an
	 * \Phruts\Action\ActionErrors object.
	 */
    const ERROR_KEY = '\Phruts\Action\ActionError';

    /**
     * The request attributes key under which your action should store an
     * <code>\Phruts\Action\Action\Messages</code> object, if you are using the
     * corresponding custom tag library elements.
     *
     * @since Struts 1.1
     */
    const MESSAGE_KEY = '\Phruts\Action\ActionMessage';

    /**
	 * The context attribute key under which our default configured data source
	 * is stored, if one is configured for this module.
	 */
    const DATA_SOURCE_KEY = '\Phruts\Action\DATA_SOURCE';

    /**
	 * The request attributes key under which phruts custom tags might store a
	 * Throwable that caused them to report an exception at runtime. This value
	 * can be used on an error page to provide more detailed information about
	 * what realy went wrong
	 */
    const EXCEPTION_KEY = '\Phruts\Action\EXCEPTION';

    /**
	 * A generic attribute key for referencing form beans
	 */
    const FORM_BEAN = '\Phruts\Action\AbstractActionForm_BEAN';

    /**
     * Action Kernel
     */
    const ACTION_KERNEL = 'phruts.action_kernel';
}
