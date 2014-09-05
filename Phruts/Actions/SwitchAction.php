<?php

namespace Phruts\Action;

/**
 * <p>A standard <strong>Action</strong> that switches to a new module
 * and then forwards control to a URI (specified in a number of possible ways)
 * within the new module.</p>
 *
 * <p>Valid request parameters for this Action are:</p>
 * <ul>
 * <li><strong>page</strong> - Module-relative URI (beginning with "/")
 *     to which control should be forwarded after switching.</li>
 * <li><strong>prefix</strong> - The module prefix (beginning with "/")
 *     of the module to which control should be switched.  Use a
 *     zero-length string for the default module.  The
 *     appropriate <code>ModuleConfig</code> object will be stored as a
 *     request attribute, so any subsequent logic will assume the new
 *     module.</li>
 * </ul>
 * * @since Struts 1.1
 */
class SwitchAction extends \Phruts\Action
{
    // See superclass for Doc
    public function execute(\Phruts\Config\ActionConfig $mapping, $form, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        //$log = Phruts_Util_Logger_Manager::getLogger( __CLASS__);

        // Identify the request parameters controlling our actions
        $page = $request->getParameter("page");
        $prefix = $request->getParameter("prefix");
        if (($page == null) || ($prefix == null)) {
            $message = $this->getActionKernel()->getInternal()->getMessage("switch.required");
            //$log->error($message);
            throw new ActionKernelException($message);
        }

        // Switch to the requested module
        RequestUtils::selectModule($prefix, $request, $this->getActionKernel()->getActionKernelContext());
        if ($request->attributes->get(\Phruts\Globals::MODULE_KEY) == null) {
            $message = $this->getActionKernel()->getInternal()->getMessage("switch.prefix", $prefix);
            //$log->error($message);
            $response->sendError(\Symfony\Component\HttpFoundation\Response::SC_BAD_REQUEST, $message);

            return (null);
        }

        // Forward control to the specified module-relative URI
        $forward = new \Phruts\Config\ForwardConfig();
        $forward->setPath($page);

        return $forward;
    }
}
