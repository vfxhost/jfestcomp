<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class FestivalAdminController extends BaseController
{
    public function display($cachable = false, $urlparams = array())
    {
        // Set default view if not set
        $input = Factory::getApplication()->input;
        $input->set('view', $input->getCmd('view', 'DefaultAdminViewName'));

        // Call parent behavior
        parent::display($cachable, $urlparams);

        return $this;
    }

    // Other task methods can be added here, such as edit, save, cancel, etc.
}

// Get an instance of the controller prefixed by FestivalAdmin
$controller = BaseController::getInstance('FestivalAdmin');

// Perform the Request task
$input = Factory::getApplication()->input;
$task = $input->getCmd('task');
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
