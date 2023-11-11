<?php

namespace Acme\Festival\Administrator\View\Import;

use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Acme\Festival\Administrator\Helper\FestivalHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
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
namespace Acme\Festival\Administrator\View\Import;

/**
 * Festival Import Html View
 */
class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
    protected $headerList;
    protected $hasPackage = \false;
    protected $headers;
    protected $hasHeader = 0;
    protected $dataType;
    public function display($tpl = \null)
    {
        if ($this->getLayout() !== 'modal') {
            // Include helper submenu
            FestivalHelper::addSubmenu('import');
        }
        $paths = new \stdClass();
        $paths->first = '';
        $state = $this->get('state');
        $this->paths =& $paths;
        $this->state =& $state;
        // get global action permissions
        $this->canDo = FestivalHelper::getActions('import');
        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
            $this->sidebar = Sidebar::render();
        }
        // get the session object
        $session = Factory::getSession();
        // check if it has package
        $this->hasPackage = $session->get('hasPackage', \false);
        $this->dataType = $session->get('dataType', \false);
        if ($this->hasPackage && $this->dataType) {
            $this->headerList = \json_decode($session->get($this->dataType . '_VDM_IMPORTHEADERS', \false), \true);
            $this->headers = FestivalHelper::getFileHeaders($this->dataType);
            // clear the data type
            $session->clear('dataType');
        }
        // Check for errors.
        if (is_array($errors = $this->get('Errors')) || ($errors = $this->get('Errors')) instanceof \Countable ? \count($errors = $this->get('Errors')) : 0) {
            throw new \Exception(\implode("\n", $errors), 500);
        }
        // Display the template
        parent::display($tpl);
    }
    /**
     * Setting the toolbar
     */
    protected function addToolBar()
    {
        \JToolBarHelper::title(Text::_('COM_FESTIVAL_IMPORT_TITLE'), 'upload');
        Sidebar::setAction('index.php?option=com_festival&view=import');
        if ($this->canDo->get('core.admin') || $this->canDo->get('core.options')) {
            \JToolBarHelper::preferences('com_festival');
        }
        // set help url for this view if found
        $this->help_url = FestivalHelper::getHelpUrl('import');
        if (FestivalHelper::checkString($this->help_url)) {
            ToolbarHelper::help('COM_FESTIVAL_HELP_MANAGER', \false, $this->help_url);
        }
    }
}
