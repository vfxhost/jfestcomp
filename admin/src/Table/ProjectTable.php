<?php

namespace Acme\Festival\Administrator\Table;

use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Table\Observer\ContentHistory;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\ApplicationHelper;
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
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
namespace Acme\Festival\Administrator\Table;

/**
 * Projecs Table class
 */
class ProjectTable extends Table
{
    /**
     * Ensure the params and metadata in json encoded in the bind method
     *
     * @var    array
     * @since  3.3
     */
    protected $_jsonEncode = ['params', 'metadata'];
    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct(&$db)
    {
        parent::__construct('#__festival_project', 'id', $db);
        // Adding History Options
        ContentHistory::createObserver($this, ['typeAlias' => 'com_festival.project']);
    }
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && \is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }
        if (isset($array['metadata']) && \is_array($array['metadata'])) {
            $registry = new Registry();
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string) $registry;
        }
        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }
        return parent::bind($array, $ignore);
    }
    /**
     * Overload the store method for the Project table.
     *
     * @param   boolean	Toggle whether null values should be updated.
     * @return  boolean  True on success, false on failure.
     * @since   1.6
     */
    public function store($updateNulls = \false)
    {
        $date = Factory::getDate();
        $user = Factory::getUser();
        if ($this->id) {
            // Existing item
            $this->modified = $date->toSql();
            $this->modified_by = $user->get('id');
        } else {
            // New project. A project created and created_by field can be set by the user,
            // so we don't touch either of these if they are set.
            if (!(int) $this->created) {
                $this->created = $date->toSql();
            }
            if (empty($this->created_by)) {
                $this->created_by = $user->get('id');
            }
        }
        if (isset($this->alias)) {
            // Verify that the alias is unique
            $table = Table::getInstance('project', 'FestivalTable');
            if ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0)) {
                $this->setError(Text::_('COM_FESTIVAL_PROJECT_ERROR_UNIQUE_ALIAS'));
                return \false;
            }
        }
        if (isset($this->url)) {
            // Convert IDN urls to punycode
            $this->url = PunycodeHelper::urlToPunycode($this->url);
        }
        if (isset($this->website)) {
            // Convert IDN urls to punycode
            $this->website = PunycodeHelper::urlToPunycode($this->website);
        }
        return parent::store($updateNulls);
    }
    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return  boolean  True on success.
     */
    public function check()
    {
        if (isset($this->alias)) {
            // Generate a valid alias
            $this->generateAlias();
            $table = Table::getInstance('project', 'festivalTable');
            while ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0)) {
                $this->alias = StringHelper::increment($this->alias, 'dash');
            }
        }
        /*
         * Clean up keywords -- eliminate extra spaces between phrases
         * and cr (\r) and lf (\n) characters from string.
         * Only process if not empty.
         */
        if (!empty($this->metakey)) {
            // Array of characters to remove.
            $bad_characters = ["\n", "\r", "\"", "<", ">"];
            // Remove bad characters.
            $after_clean = StringHelper::str_ireplace($bad_characters, "", $this->metakey);
            // Create array using commas as delimiter.
            $keys = \explode(',', $after_clean);
            $clean_keys = [];
            foreach ($keys as $key) {
                // Ignore blank keywords.
                if (\trim($key)) {
                    $clean_keys[] = \trim($key);
                }
            }
            // Put array back together delimited by ", "
            $this->metakey = \implode(", ", $clean_keys);
        }
        // Clean up description -- eliminate quotes and <> brackets
        if (!empty($this->metadesc)) {
            // Only process if not empty
            $bad_characters = ["\"", "<", ">"];
            $this->metadesc = StringHelper::str_ireplace($bad_characters, "", $this->metadesc);
        }
        // If we don't have any access rules set at this point just use an empty JAccessRules class
        if (!$this->getRules()) {
            $rules = $this->getDefaultAssetValues('com_festival.project.' . $this->id);
            $this->setRules($rules);
        }
        // Set ordering
        if ($this->published < 0) {
            // Set ordering to 0 if state is archived or trashed
            $this->ordering = 0;
        }
        return \true;
    }
    /**
     * Gets the default asset values for a component.
     *
     * @param   $string  $component  The component asset name to search for
     *
     * @return  JAccessRules  The JAccessRules object for the asset
     */
    protected function getDefaultAssetValues($component, $try = \true)
    {
        // Need to find the asset id by the name of the component.
        $db = Factory::getDbo();
        $query = $db->getQuery(\true)->select($db->quoteName('id'))->from($db->quoteName('#__assets'))->where($db->quoteName('name') . ' = ' . $db->quote($component));
        $db->setQuery($query);
        $db->execute();
        if ($db->loadRowList()) {
            // asset already set so use saved rules
            $assetId = (int) $db->loadResult();
            return Access::getAssetRules($assetId);
            // (TODO) instead of keeping inherited Allowed it becomes Allowed.
        } elseif ($try) {
            $try = \explode('.', $component);
            $result = $this->getDefaultAssetValues($try[0], \false);
            if ($result instanceof Rules) {
                if (isset($try[1])) {
                    $_result = (string) $result;
                    $_result = \json_decode($_result);
                    foreach ($_result as $name => &$rule) {
                        $v = \explode('.', $name);
                        if ($try[1] !== $v[0]) {
                            // remove since it is not part of this view
                            unset($_result->{$name});
                        } else {
                            // clear the value since we inherit
                            $rule = [];
                        }
                    }
                    // check if there are any view values remaining
                    if (\count((array) $_result)) {
                        $_result = \json_encode($_result);
                        $_result = [$_result];
                        // Instantiate and return the JAccessRules object for the asset rules.
                        $rules = new Rules();
                        $rules->mergeCollection($_result);
                        return $rules;
                    }
                }
                return $result;
            }
        }
        return Access::getAssetRules(0);
    }
    /**
     * Method to compute the default name of the asset.
     * The default name is in the form 'table_name.id'
     * where id is the value of the primary key of the table.
     *
     * @return	string
     * @since	2.5
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_festival.project.' . (int) $this->{$k};
    }
    /**
     * Method to return the title to use for the asset table.
     *
     * @return	string
     * @since	2.5
     */
    protected function _getAssetTitle()
    {
        if (isset($this->title)) {
            return $this->title;
        }
        return '';
    }
    /**
     * Get the parent asset id for the record
     *
     * @return	int
     * @since	2.5
     */
    protected function _getAssetParentId(Table $table = \NULL, $id = \NULL)
    {
        $asset = Table::getInstance('Asset');
        $asset->loadByName('com_festival');
        return $asset->id;
    }
    /**
     * Generate a valid alias from title / date.
     * Remains public to be able to check for duplicated alias before saving
     *
     * @return  string
     */
    public function generateAlias()
    {
        if (empty($this->alias)) {
            $this->alias = $this->project_title;
        }
        $this->alias = ApplicationHelper::stringURLSafe($this->alias);
        if (\trim(\str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }
        return $this->alias;
    }
}
