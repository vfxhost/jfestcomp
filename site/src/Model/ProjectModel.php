<?php

namespace Acme\Festival\Site\Model;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		project.php
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
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Utilities\ArrayHelper;
/**
 * Festival Project Item Model
 */
class ProjectModel extends ItemModel
{
    /**
     * Model context string.
     *
     * @var        string
     */
    protected $_context = 'com_festival.project';
    /**
     * Model user data.
     *
     * @var        strings
     */
    protected $user;
    protected $userId;
    protected $guest;
    protected $groups;
    protected $levels;
    protected $app;
    protected $input;
    protected $uikitComp;
    /**
     * @var object item
     */
    protected $item;
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     *
     * @return void
     */
    protected function populateState()
    {
        $this->app = Factory::getApplication();
        $this->input = $this->app->input;
        // Get the itme main id
        $id = $this->input->getInt('id', \null);
        $this->setState('project.id', $id);
        // Load the parameters.
        $params = $this->app->getParams();
        $this->setState('params', $params);
        parent::populateState();
    }
    /**
     * Method to get article data.
     *
     * @param   integer  $pk  The id of the article.
     *
     * @return  mixed  Menu item data object on success, false on failure.
     */
    public function getItem($pk = \null)
    {
        $this->user = Factory::getUser();
        $this->userId = $this->user->get('id');
        $this->guest = $this->user->get('guest');
        $this->groups = $this->user->get('groups');
        $this->authorisedGroups = $this->user->getAuthorisedGroups();
        $this->levels = $this->user->getAuthorisedViewLevels();
        $this->initSet = \true;
        $pk = !empty($pk) ? $pk : (int) $this->getState('project.id');
        if ($this->_item === \null) {
            $this->_item = [];
        }
        if (!isset($this->_item[$pk])) {
            try {
                // Get a db connection.
                $db = Factory::getDbo();
                // Create a new query object.
                $query = $db->getQuery(\true);
                // Get from #__festival_project as a
                $query->select($db->quoteName(['a.id', 'a.asset_id', 'a.project_title', 'a.project_title_original_language', 'a.synopsis', 'a.synopsis_original_language', 'a.alias', 'a.published', 'a.created_by', 'a.modified_by', 'a.created', 'a.modified', 'a.version', 'a.hits', 'a.ordering'], ['id', 'asset_id', 'project_title', 'project_title_original_language', 'synopsis', 'synopsis_original_language', 'alias', 'published', 'created_by', 'modified_by', 'created', 'modified', 'version', 'hits', 'ordering']));
                $query->from($db->quoteName('#__festival_project', 'a'));
                // Reset the query using our newly populated query object.
                $db->setQuery($query);
                // Load the results as a stdClass object.
                $data = $db->loadObject();
                if (empty($data)) {
                    $app = Factory::getApplication();
                    // If no data is found redirect to default page and show warning.
                    $app->enqueueMessage(Text::_('COM_FESTIVAL_NOT_FOUND_OR_ACCESS_DENIED'), 'warning');
                    $app->redirect(\JURI::root());
                    return \false;
                }
                // Load the JEvent Dispatcher
                PluginHelper::importPlugin('content');
                $this->_dispatcher = Factory::getApplication();
                // Check if item has params, or pass whole item.
                $params = isset($data->params) && FestivalHelper::checkJson($data->params) ? \json_decode($data->params) : $data;
                // Make sure the content prepare plugins fire on synopsis_original_language
                $_synopsis_original_language = new \stdClass();
                $_synopsis_original_language->text =& $data->synopsis_original_language;
                // value must be in text
                // Since all values are now in text (Joomla Limitation), we also add the field name (synopsis_original_language) to context
                $this->_dispatcher->triggerEvent("onContentPrepare", ['com_festival.project.synopsis_original_language', &$_synopsis_original_language, &$params, 0]);
                // Make sure the content prepare plugins fire on synopsis
                $_synopsis = new \stdClass();
                $_synopsis->text =& $data->synopsis;
                // value must be in text
                // Since all values are now in text (Joomla Limitation), we also add the field name (synopsis) to context
                $this->_dispatcher->triggerEvent("onContentPrepare", ['com_festival.project.synopsis', &$_synopsis, &$params, 0]);
                // set data object to item.
                $this->_item[$pk] = $data;
            } catch (\Exception $e) {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    \JError::raiseWarning(404, $e->getMessage());
                } else {
                    $this->setError($e);
                    $this->_item[$pk] = \false;
                }
            }
        }
        return $this->_item[$pk];
    }
}
