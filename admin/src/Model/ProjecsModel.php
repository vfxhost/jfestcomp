<?php

namespace Acme\Festival\Administrator\Model;

use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Acme\Festival\Administrator\Helper\FestivalHelper;
use Joomla\CMS\Component\ComponentHelper;
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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
namespace Acme\Festival\Administrator\Model;

/**
 * Projecs List Model
 */
class ProjecsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['a.id', 'id', 'a.published', 'published', 'a.access', 'access', 'a.ordering', 'ordering', 'a.created_by', 'created_by', 'a.modified_by', 'modified_by', 'a.project_title', 'project_title'];
        }
        parent::__construct($config);
    }
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     */
    protected function populateState($ordering = \null, $direction = \null)
    {
        $app = Factory::getApplication();
        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }
        // Check if the form was submitted
        $formSubmited = $app->input->post->get('form_submited');
        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
        if ($formSubmited) {
            $access = $app->input->post->get('access');
            $this->setState('filter.access', $access);
        }
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);
        $created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
        $this->setState('filter.created_by', $created_by);
        $created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
        $this->setState('filter.created', $created);
        $sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
        $this->setState('filter.sorting', $sorting);
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $project_title = $this->getUserStateFromRequest($this->context . '.filter.project_title', 'filter_project_title');
        if ($formSubmited) {
            $project_title = $app->input->post->get('project_title');
            $this->setState('filter.project_title', $project_title);
        }
        // List state information.
        parent::populateState($ordering, $direction);
    }
    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        // Check in items
        $this->checkInNow();
        // load parent items
        $items = parent::getItems();
        // return items
        return $items;
    }
    /**
     * Method to build an SQL query to load the list data.
     *
     * @return	string	An SQL query
     */
    protected function getListQuery()
    {
        // Get the user object.
        $user = Factory::getUser();
        // Create a new query object.
        $db = Factory::getDBO();
        $query = $db->getQuery(\true);
        // Select some fields
        $query->select('a.*');
        // From the festival_item table
        $query->from($db->quoteName('#__festival_project', 'a'));
        // Filter by published state
        $published = $this->getState('filter.published');
        if (\is_numeric($published)) {
            $query->where('a.published = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(a.published = 0 OR a.published = 1)');
        }
        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
        // Filter by access level.
        $_access = $this->getState('filter.access');
        if ($_access && \is_numeric($_access)) {
            $query->where('a.access = ' . (int) $_access);
        } elseif (FestivalHelper::checkArray($_access)) {
            // Secure the array for the query
            $_access = ArrayHelper::toInteger($_access);
            // Filter by the Access Array.
            $query->where('a.access IN (' . \implode(',', $_access) . ')');
        }
        // Implement View Level Access
        if (!$user->authorise('core.options', 'com_festival')) {
            $groups = \implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }
        // Filter by search.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (\stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) \substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search) . '%');
                $query->where('(a.project_title LIKE ' . $search . ')');
            }
        }
        // Filter by Project_title.
        $_project_title = $this->getState('filter.project_title');
        if (\is_numeric($_project_title)) {
            if (\is_float($_project_title)) {
                $query->where('a.project_title = ' . (float) $_project_title);
            } else {
                $query->where('a.project_title = ' . (int) $_project_title);
            }
        } elseif (FestivalHelper::checkString($_project_title)) {
            $query->where('a.project_title = ' . $db->quote($db->escape($_project_title)));
        }
        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        if ($orderCol != '') {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }
        return $query;
    }
    /**
     * Method to get list export data.
     *
     * @param   array  $pks  The ids of the items to get
     * @param   JUser  $user  The user making the request
     *
     * @return mixed  An array of data items on success, false on failure.
     */
    public function getExportData($pks, $user = \null)
    {
        // setup the query
        if (($pks_size = FestivalHelper::checkArray($pks)) !== \false || 'bulk' === $pks) {
            // Set a value to know this is export method. (USE IN CUSTOM CODE TO ALTER OUTCOME)
            $_export = \true;
            // Get the user object if not set.
            if (!isset($user) || !FestivalHelper::checkObject($user)) {
                $user = Factory::getUser();
            }
            // Create a new query object.
            $db = Factory::getDBO();
            $query = $db->getQuery(\true);
            // Select some fields
            $query->select('a.*');
            // From the festival_project table
            $query->from($db->quoteName('#__festival_project', 'a'));
            // The bulk export path
            if ('bulk' === $pks) {
                $query->where('a.id > 0');
            } elseif ($pks_size > 500) {
                // Use lowest ID
                $query->where('a.id >= ' . (int) \min($pks));
                // Use highest ID
                $query->where('a.id <= ' . (int) \max($pks));
            } else {
                $query->where('a.id IN (' . \implode(',', $pks) . ')');
            }
            // Get global switch to activate text only export
            $export_text_only = ComponentHelper::getParams('com_festival')->get('export_text_only', 0);
            // Implement View Level Access
            if (!$user->authorise('core.options', 'com_festival')) {
                $groups = \implode(',', $user->getAuthorisedViewLevels());
                $query->where('a.access IN (' . $groups . ')');
            }
            // Order the results by ordering
            $query->order('a.ordering  ASC');
            // Load the items
            $db->setQuery($query);
            $db->execute();
            if ($db->getNumRows()) {
                $items = $db->loadObjectList();
                // Set values to display correctly.
                if (FestivalHelper::checkArray($items)) {
                    foreach ($items as $nr => &$item) {
                        // unset the values we don't want exported.
                        unset($item->asset_id);
                        unset($item->checked_out);
                        unset($item->checked_out_time);
                    }
                }
                // Add headers to items array.
                $headers = $this->getExImPortHeaders();
                if (FestivalHelper::checkObject($headers)) {
                    \array_unshift($items, $headers);
                }
                return $items;
            }
        }
        return \false;
    }
    /**
     * Method to get header.
     *
     * @return mixed  An array of data items on success, false on failure.
     */
    public function getExImPortHeaders()
    {
        // Get a db connection.
        $db = Factory::getDbo();
        // get the columns
        $columns = $db->getTableColumns("#__festival_project");
        if (FestivalHelper::checkArray($columns)) {
            // remove the headers you don't import/export.
            unset($columns['asset_id']);
            unset($columns['checked_out']);
            unset($columns['checked_out_time']);
            $headers = new \stdClass();
            foreach ($columns as $column => $type) {
                $headers->{$column} = $column;
            }
            return $headers;
        }
        return \false;
    }
    /**
     * Method to get a store id based on model configuration state.
     *
     * @return  string  A store id.
     *
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.id');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        // Check if the value is an array
        $_access = $this->getState('filter.access');
        if (FestivalHelper::checkArray($_access)) {
            $id .= ':' . \implode(':', $_access);
        } elseif (\is_numeric($_access) || FestivalHelper::checkString($_access)) {
            $id .= ':' . $_access;
        }
        $id .= ':' . $this->getState('filter.ordering');
        $id .= ':' . $this->getState('filter.created_by');
        $id .= ':' . $this->getState('filter.modified_by');
        $id .= ':' . $this->getState('filter.project_title');
        return parent::getStoreId($id);
    }
    /**
     * Build an SQL query to checkin all items left checked out longer then a set time.
     *
     * @return  a bool
     *
     */
    protected function checkInNow()
    {
        // Get set check in time
        $time = ComponentHelper::getParams('com_festival')->get('check_in');
        if ($time) {
            // Get a db connection.
            $db = Factory::getDbo();
            // Reset query.
            $query = $db->getQuery(\true);
            $query->select('*');
            $query->from($db->quoteName('#__festival_project'));
            // Only select items that are checked out.
            $query->where($db->quoteName('checked_out') . '!=0');
            $db->setQuery($query, 0, 1);
            $db->execute();
            if ($db->getNumRows()) {
                // Get Yesterdays date.
                $date = Factory::getDate()->modify($time)->toSql();
                // Reset query.
                $query = $db->getQuery(\true);
                // Fields to update.
                $fields = [$db->quoteName('checked_out_time') . '=\'0000-00-00 00:00:00\'', $db->quoteName('checked_out') . '=0'];
                // Conditions for which records should be updated.
                $conditions = [$db->quoteName('checked_out') . '!=0', $db->quoteName('checked_out_time') . '<\'' . $date . '\''];
                // Check table.
                $query->update($db->quoteName('#__festival_project'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $db->execute();
            }
        }
        return \false;
    }
}
