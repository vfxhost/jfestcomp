<?php
defined('_JEXEC') or die('Restricted access');

class FestivalController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = array())
    {
        // Set default view if not set
        $input = JFactory::getApplication()->input;
        $input->set('view', $input->getCmd('view', 'DefaultViewName'));

        // Call parent behavior
        parent::display($cachable, $urlparams);

        return $this;
    }

    // Other task methods can be added here, such as edit, save, cancel, etc.
}

// Get an instance of the controller prefixed by Festival
$controller = JControllerLegacy::getInstance('Festival');

// Perform the Request task
$input = JFactory::getApplication()->input;
$task = $input->getCmd('task');
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();