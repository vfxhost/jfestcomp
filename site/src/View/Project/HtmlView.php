<?php

namespace Acme\Festival\Site\View\Project;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		view.html.php
	@author			Kyriakos Liarakos <https://www/vfxhost.gr>	
	@copyright		Copyright (C) 2023. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
// No direct access to this file
\defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
/**
 * Festival Html View class for the Project
 */
class HtmlView extends HtmlView
{
    // Overwriting JView display method
    function display($tpl = \null)
    {
        // get combined params of both component and menu
        $this->app = Factory::getApplication();
        $this->params = $this->app->getParams();
        $this->menu = $this->app->getMenu()->getActive();
        // get the user object
        $this->user = Factory::getUser();
        // Initialise variables.
        $this->item = $this->get('Item');
        // Set the toolbar
        $this->addToolBar();
        // set the document
        $this->_prepareDocument();
        // Check for errors.
        if (is_array($errors = $this->get('Errors')) || ($errors = $this->get('Errors')) instanceof \Countable ? \count($errors = $this->get('Errors')) : 0) {
            throw new \Exception(\implode(\PHP_EOL, $errors), 500);
        }
        parent::display($tpl);
    }
    /**
     * Prepares the document
     */
    protected function _prepareDocument()
    {
        // Only load jQuery if needed. (default is true)
        if ($this->params->get('add_jquery_framework', 1) == 1) {
            HTMLHelper::_('jquery.framework');
        }
        // Load the header checker class.
        require_once \JPATH_COMPONENT_SITE . '/helpers/headercheck.php';
        // Initialize the header checker.
        $HeaderCheck = new \festivalHeaderCheck();
        // add the document default css file
        HTMLHelper::_('stylesheet', 'components/com_festival/assets/css/project.css', ['version' => 'auto']);
    }
    /**
     * Setting the toolbar
     */
    protected function addToolBar()
    {
        // set help url for this view if found
        $this->help_url = FestivalHelper::getHelpUrl('project');
        if (FestivalHelper::checkString($this->help_url)) {
            ToolbarHelper::help('COM_FESTIVAL_HELP_MANAGER', \false, $this->help_url);
        }
        // now initiate the toolbar
        $this->toolbar = Toolbar::getInstance();
    }
    /**
     * Escapes a value for output in a view script.
     *
     * @param   mixed  $var  The output to escape.
     *
     * @return  mixed  The escaped value.
     */
    public function escape($var, $sorten = \false, $length = 40)
    {
        // use the helper htmlEscape method instead.
        return FestivalHelper::htmlEscape($var, $this->_charset, $sorten, $length);
    }
}
