<?php

namespace Acme\Festival\Administrator\Controller;

use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Acme\Festival\Administrator\Helper\FestivalHelper;
use Joomla\CMS\Router\Route;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		projecs.php
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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;
namespace Acme\Festival\Administrator\Controller;

/**
 * Projecs Admin Controller
 */
class ProjecsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_FESTIVAL_PROJECS';
    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JModelLegacy  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Project', $prefix = 'FestivalModel', $config = ['ignore_request' => \true])
    {
        return parent::getModel($name, $prefix, $config);
    }
    public function exportData()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
        // check if export is allowed for this user.
        $user = Factory::getUser();
        if ($user->authorise('project.export', 'com_festival') && $user->authorise('core.export', 'com_festival')) {
            // Get the input
            $input = Factory::getApplication()->input;
            $pks = $input->post->get('cid', [], 'array');
            // Sanitize the input
            $pks = ArrayHelper::toInteger($pks);
            // Get the model
            $model = $this->getModel('Projecs');
            // get the data to export
            $data = $model->getExportData($pks);
            if (FestivalHelper::checkArray($data)) {
                // now set the data to the spreadsheet
                $date = Factory::getDate();
                FestivalHelper::xls($data, 'Projecs_' . $date->format('jS_F_Y'), 'Projecs exported (' . $date->format('jS F, Y') . ')', 'projecs');
            }
        }
        // Redirect to the list screen with error.
        $message = Text::_('COM_FESTIVAL_EXPORT_FAILED');
        $this->setRedirect(Route::_('index.php?option=com_festival&view=projecs', \false), $message, 'error');
        return;
    }
    public function importData()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
        // check if import is allowed for this user.
        $user = Factory::getUser();
        if ($user->authorise('project.import', 'com_festival') && $user->authorise('core.import', 'com_festival')) {
            // Get the import model
            $model = $this->getModel('Projecs');
            // get the headers to import
            $headers = $model->getExImPortHeaders();
            if (FestivalHelper::checkObject($headers)) {
                // Load headers to session.
                $session = Factory::getSession();
                $headers = \json_encode($headers);
                $session->set('project_VDM_IMPORTHEADERS', $headers);
                $session->set('backto_VDM_IMPORT', 'projecs');
                $session->set('dataType_VDM_IMPORTINTO', 'project');
                // Redirect to import view.
                $message = Text::_('COM_FESTIVAL_IMPORT_SELECT_FILE_FOR_PROJECS');
                $this->setRedirect(Route::_('index.php?option=com_festival&view=import', \false), $message);
                return;
            }
        }
        // Redirect to the list screen with error.
        $message = Text::_('COM_FESTIVAL_IMPORT_FAILED');
        $this->setRedirect(Route::_('index.php?option=com_festival&view=projecs', \false), $message, 'error');
        return;
    }
}
