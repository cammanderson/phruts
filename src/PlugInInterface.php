<?php
namespace Phruts;

/**
 * A PlugIn is a configuration wrapper for a module-specific resource or
 * actionKernel that needs to be notified about application startup and application
 * shutdown events (corresponding to calls init() and destroy() on the
 * corresponding ActionKernel instance).
 *
 * <p>PlugIn Actions can be configured in the phruts-config.xml file, without
 * the need to subclass ActionKernel simply to perform application
 * lifecycle activities.</p>
 * <p>Implementations of this interface must supply a zero-argument constructor
 * for use by ActionKernel. Configuration can be accomplished by
 * providing standard PHPBeans property setter methods, which will all have
 * been called before the <samp>init</samp> method is invoked.</p>
 *
 * @author Cameron Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts)
 */
interface PlugInInterface
{
    /**
	 * Receive notification that our owning module is being shut down.
	 */
    public function destroy();

    /**
	 * Receive notification that the specified module is being started up.
	 *
	 * @param \Phruts\Action\ActionKernel $actionKernel ActionKernel that is managing
	 * all the module in this web application
	 * @param \Phruts\Config\ModuleConfig $config ModuleConfig for the module with
	 * which this plug-in is associated
	 * @throws \Phruts\Exception If this PlugIn cannot be
	 * successfully initialized
	 */
    public function init(\Phruts\Action\ActionKernel $actionKernel, \Phruts\Config\ModuleConfig $config);
}
