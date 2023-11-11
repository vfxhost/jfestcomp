<?php

namespace Acme\Festival\Administrator\Controller;

use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		import.php
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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Utilities\ArrayHelper;
namespace Acme\Festival\Administrator\Controller;

/**
 * Festival Import Base Controller
 */
class ImportController extends BaseController
{
    /**
     * Import an spreadsheet.
     *
     * @return  void
     */
    public function import()
    {
        // Check for request forgeries
        Session::checkToken() or \jexit(Text::_('JINVALID_TOKEN'));
        $model = $this->getModel('import');
        if ($model->import()) {
            $cache = Factory::getCache('mod_menu');
            $cache->clean();
            // TODO: Reset the users acl here as well to kill off any missing bits
        }
        $app = Factory::getApplication();
        $redirect_url = $app->getUserState('com_festival.redirect_url');
        if (empty($redirect_url)) {
            $redirect_url = Route::_('index.php?option=com_festival&view=import', \false);
        } else {
            // wipe out the user state when we're going to redirect
            $app->setUserState('com_festival.redirect_url', '');
            $app->setUserState('com_festival.message', '');
            $app->setUserState('com_festival.extension_message', '');
        }
        $this->setRedirect($redirect_url);
    }
}
