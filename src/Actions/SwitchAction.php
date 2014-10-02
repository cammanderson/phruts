<?php

namespace Phruts\Actions;

use Phruts\Action\AbstractActionForm;
use Phruts\Action\ActionMapping;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
 * @since Struts 1.1
 */
class SwitchAction extends \Phruts\Action\Action
{
    // See superclass for Doc
    public function execute(ActionMapping $mapping, AbstractActionForm $form = null, Request $request, Response $response)
    {

        // Identify the request parameters controlling our actions
        $page = $request->get("page");
        $prefix = $request->get("prefix");
        if (($page == null) || ($prefix == null)) {
            $message = $this->getActionKernel()->getInternal()->getMessage("switch.required", $mapping->getPath());
            throw new \Phruts\Exception($message);
        }

        // Switch to the requested module
        \Phruts\Util\RequestUtils::selectModule($request, $this->getActionKernel()->getApplication());
        if ($request->attributes->get(\Phruts\Util\Globals::MODULE_KEY) == null) {
            $message = $this->getActionKernel()->getInternal()->getMessage("switch.prefix", $prefix);
            $response->setContent(400);
            $response->setContent($message);

            return (null);
        }

        // Forward control to the specified module-relative URI
        $forward = new \Phruts\Config\ForwardConfig();
        $forward->setPath($page);

        return $forward;
    }
}
