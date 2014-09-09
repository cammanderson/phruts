<?php
namespace Phruts\Action;

/**
 * <p>An <strong>ActionMapping</strong> represents the information that the
 * controller, <code>RequestProcessor</code>, knows about the mapping of a
 * particular request to an instance of a particular <code>Action</code>
 * class. The <code>ActionMapping</code> instance used to select a particular
 * <code>Action</code> is passed on to that <code>Action</code>, thereby
 * providing access to any custom configuration information included with the
 * <code>ActionMapping</code> object.</p>
 *
 * <p>Since Struts 1.1 this class extends <code>ActionConfig</code>.
 *
 * <p><strong>NOTE</strong> - This class would have been deprecated and
 * replaced by <code>org.apache.struts.config.ActionConfig</code> except for
 * the fact that it is part of the public API that existing applications are
 * using.</p>
 *
 */
class ActionMapping extends \Phruts\Config\ActionConfig
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * <p>Find and return the <code>ForwardConfig</code> instance defining how
     * forwarding to the specified logical name should be handled. This is
     * performed by checking local and then global configurations for the
     * specified forwarding configuration. If no forwarding configuration can
     * be found, return <code>null</code>.</p>
     *
     * @param forwardName string Logical name of the forwarding instance to be
     *                    returned
     * @return \Phruts\Config\ForwardConfig The local or global forward with the specified name.
     */
    public function findForward($forwardName)
    {
        $config = $this->findForwardConfig($forwardName);

        if (empty($config)) {
            $config = $this->getModuleConfig()->findForwardConfig($forwardName);
        }

        if (empty($config)) {
            if (!empty($this->log)) {
                $this->log->warn("Unable to find '" + $forwardName + "' forward.");
            }
        }

        return $config;
    }

    /**
     * <p>Return the logical names of all locally defined forwards for this
     * mapping. If there are no such forwards, a zero-length array is
     * returned.</p>
     *
     * @return array The forward names for this action mapping.
     */
    public function findForwards()
    {
        $results = array();
        $forwardConfigs = $this->findForwardConfigs();

        foreach ($forwardConfigs as $forwardConfig) {
            $results[] = $forwardConfig->getName();
        }

        return $results;
    }

    /**
     * <p>Create (if necessary) and return an {@link ActionForward} that
     * corresponds to the <code>input</code> property of this Action.</p>
     *
     * @return \Phruts\Config\ForwardConfig The input forward for this action mapping.
     * @since Struts 1.1
     */
    public function getInputForward()
    {
        $controllerInputForward = null;
        $controllerConfig = $this->getModuleConfig()->getControllerConfig();
        if(!empty($controllerConfig)) {
            $controllerInputForward = $controllerConfig->getInputForward();
        }

        if ($controllerInputForward == true) {
            return ($this->findForward($this->getInput()));
        } else {
            $forward = new \Phruts\Config\ForwardConfig();
            $forward->setPath($this->getInput());
            return $forward;
        }
    }
}
