<?php

namespace Acme\Festival\Site\Helper;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Version;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Object\CMSObject;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		festival.php
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
// register this component namespace
\spl_autoload_register(function ($class) {
    // project-specific base directories and namespace prefix
    $search = ['libraries/jcb_powers/VDM.Joomla' => 'VDM\Joomla'];
    // Start the search and load if found
    $found = \false;
    $found_base_dir = "";
    $found_len = 0;
    foreach ($search as $base_dir => $prefix) {
        // does the class use the namespace prefix?
        $len = \strlen($prefix);
        if (\strncmp($prefix, $class, $len) === 0) {
            // we have a match so load the values
            $found = \true;
            $found_base_dir = $base_dir;
            $found_len = $len;
            // done here
            break;
        }
    }
    // check if we found a match
    if (!$found) {
        // not found so move to the next registered autoloader
        return;
    }
    // get the relative class name
    $relative_class = \substr($class, $found_len);
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = \JPATH_ROOT . '/' . $found_base_dir . '/src' . \str_replace('\\', '/', $relative_class) . '.php';
    // if the file exists, require it
    if (\file_exists($file)) {
        require $file;
    }
});
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Language;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
/**
 * Festival component helper
 */
use VDM\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\FormHelper;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
abstract class FestivalHelper
{
    /**
     * Composer Switch
     *
     * @var      array
     */
    protected static $composer = [];
    /**
     * The Main Active Language
     *
     * @var      string
     */
    public static $langTag;
    /**
     * Load the Composer Vendors
     */
    public static function composerAutoload($target)
    {
        // insure we load the composer vendor only once
        if (!isset(self::$composer[$target])) {
            // get the function name
            $functionName = \VDM\Joomla\Utilities\StringHelper::safe('compose' . $target);
            // check if method exist
            if (\method_exists(self::class, $functionName)) {
                return self::$functionName();
            }
            return \false;
        }
        return self::$composer[$target];
    }
    /**
     * Convert a json object to a string
     *
     * @input    string  $value  The json string to convert
     *
     * @returns a string
     * @deprecated 3.3 Use JsonHelper::string(...);
     */
    public static function jsonToString($value, $sperator = ", ", $table = \null, $id = 'id', $name = 'name')
    {
        return JsonHelper::string($value, $sperator, $table, $id, $name);
    }
    /**
     * Load the Component xml manifest.
     */
    public static function manifest()
    {
        $manifestUrl = \JPATH_ADMINISTRATOR . "/components/com_festival/festival.xml";
        return \simplexml_load_file($manifestUrl);
    }
    /**
     * Joomla version object
     */
    protected static $JVersion;
    /**
     * set/get Joomla version
     */
    public static function jVersion()
    {
        // check if set
        if (!ObjectHelper::check(self::$JVersion)) {
            self::$JVersion = new Version();
        }
        return self::$JVersion;
    }
    /**
     * Load the Contributors details.
     */
    public static function getContributors()
    {
        // get params
        $params = ComponentHelper::getParams('com_festival');
        // start contributors array
        $contributors = [];
        // get all Contributors (max 20)
        $searchArray = \range('0', '20');
        foreach ($searchArray as $nr) {
            if (\NULL !== $params->get("showContributor" . $nr) && ($params->get("showContributor" . $nr) == 2 || $params->get("showContributor" . $nr) == 3)) {
                // set link based of selected option
                if ($params->get("useContributor" . $nr) == 1) {
                    $link_front = '<a href="mailto:' . $params->get("emailContributor" . $nr) . '" target="_blank">';
                    $link_back = '</a>';
                } elseif ($params->get("useContributor" . $nr) == 2) {
                    $link_front = '<a href="' . $params->get("linkContributor" . $nr) . '" target="_blank">';
                    $link_back = '</a>';
                } else {
                    $link_front = '';
                    $link_back = '';
                }
                $contributors[$nr]['title'] = \VDM\Joomla\Utilities\StringHelper::html($params->get("titleContributor" . $nr));
                $contributors[$nr]['name'] = $link_front . \VDM\Joomla\Utilities\StringHelper::html($params->get("nameContributor" . $nr)) . $link_back;
            }
        }
        return $contributors;
    }
    /**
     *	Can be used to build help urls.
     **/
    public static function getHelpUrl($view)
    {
        return \false;
    }
    /**
     * Get any component's model
     */
    public static function getModel($name, $path = \JPATH_COMPONENT_SITE, $Component = 'Festival', $config = [])
    {
        // fix the name
        $name = \VDM\Joomla\Utilities\StringHelper::safe($name);
        // full path to models
        $fullPathModels = $path . '/models';
        // load the model file
        BaseDatabaseModel::addIncludePath($fullPathModels, $Component . 'Model');
        // make sure the table path is loaded
        if (!isset($config['table_path']) || !\VDM\Joomla\Utilities\StringHelper::check($config['table_path'])) {
            // This is the JCB default path to tables in Joomla 3.x
            $config['table_path'] = \JPATH_ADMINISTRATOR . '/components/com_' . \strtolower($Component) . '/tables';
        }
        // get instance
        $model = BaseDatabaseModel::getInstance($name, $Component . 'Model', $config);
        // if model not found (strange)
        if ($model == \false) {
            \jimport('joomla.filesystem.file');
            // get file path
            $filePath = $path . '/' . $name . '.php';
            $fullPathModel = $fullPathModels . '/' . $name . '.php';
            // check if it exists
            if (File::exists($filePath)) {
                // get the file
                require_once $filePath;
            } elseif (File::exists($fullPathModel)) {
                // get the file
                require_once $fullPathModel;
            }
            // build class names
            $modelClass = $Component . 'Model' . $name;
            if (\class_exists($modelClass)) {
                // initialize the model
                return new $modelClass($config);
            }
        }
        return $model;
    }
    /**
     * Add to asset Table
     */
    public static function setAsset($id, $table, $inherit = \true)
    {
        $parent = Table::getInstance('Asset');
        $parent->loadByName('com_festival');
        $parentId = $parent->id;
        $name = 'com_festival.' . $table . '.' . $id;
        $title = '';
        $asset = Table::getInstance('Asset');
        $asset->loadByName($name);
        // Check for an error.
        $error = $asset->getError();
        if ($error) {
            return \false;
        }
        // Specify how a new or moved node asset is inserted into the tree.
        if ($asset->parent_id != $parentId) {
            $asset->setLocation($parentId, 'last-child');
        }
        // Prepare the asset to be stored.
        $asset->parent_id = $parentId;
        $asset->name = $name;
        $asset->title = $title;
        // get the default asset rules
        $rules = self::getDefaultAssetRules('com_festival', $table, $inherit);
        if ($rules instanceof Rules) {
            $asset->rules = (string) $rules;
        }
        if (!$asset->check() || !$asset->store()) {
            Factory::getApplication()->enqueueMessage($asset->getError(), 'warning');
            return \false;
        } else {
            // Create an asset_id or heal one that is corrupted.
            $object = new \stdClass();
            // Must be a valid primary key value.
            $object->id = $id;
            $object->asset_id = (int) $asset->id;
            // Update their asset_id to link to the asset table.
            return Factory::getDbo()->updateObject('#__festival_' . $table, $object, 'id');
        }
        return \false;
    }
    /**
     * Gets the default asset Rules for a component/view.
     */
    protected static function getDefaultAssetRules($component, $view, $inherit = \true)
    {
        // if new or inherited
        $assetId = 0;
        // Only get the actual item rules if not inheriting
        if (!$inherit) {
            // Need to find the asset id by the name of the component.
            $db = Factory::getDbo();
            $query = $db->getQuery(\true)->select($db->quoteName('id'))->from($db->quoteName('#__assets'))->where($db->quoteName('name') . ' = ' . $db->quote($component));
            $db->setQuery($query);
            $db->execute();
            // check that there is a value
            if ($db->getNumRows()) {
                // asset already set so use saved rules
                $assetId = (int) $db->loadResult();
            }
        }
        // get asset rules
        $result = Access::getAssetRules($assetId);
        if ($result instanceof Rules) {
            $_result = (string) $result;
            $_result = \json_decode($_result);
            foreach ($_result as $name => &$rule) {
                $v = \explode('.', $name);
                if ($view !== $v[0]) {
                    // remove since it is not part of this view
                    unset($_result->{$name});
                } elseif ($inherit) {
                    // clear the value since we inherit
                    $rule = [];
                }
            }
            // check if there are any view values remaining
            if (\count((array) $_result)) {
                $_result = \json_encode($_result);
                $_result = [$_result];
                // Instantiate and return the JAccessRules object for the asset rules.
                $rules = new Rules($_result);
                // return filtered rules
                return $rules;
            }
        }
        return $result;
    }
    /**
     * xmlAppend
     *
     * @param   SimpleXMLElement   $xml      The XML element reference in which to inject a comment
     * @param   mixed              $node     A SimpleXMLElement node to append to the XML element reference, or a stdClass object containing a comment attribute to be injected before the XML node and a fieldXML attribute containing a SimpleXMLElement
     *
     * @return  void
     * @deprecated 3.3 Use FormHelper::append($xml, $node);
     */
    public static function xmlAppend(&$xml, $node)
    {
        FormHelper::append($xml, $node);
    }
    /**
     * xmlComment
     *
     * @param   SimpleXMLElement   $xml        The XML element reference in which to inject a comment
     * @param   string             $comment    The comment to inject
     *
     * @return  void
     * @deprecated 3.3 Use FormHelper::comment($xml, $comment);
     */
    public static function xmlComment(&$xml, $comment)
    {
        FormHelper::comment($xml, $comment);
    }
    /**
     * xmlAddAttributes
     *
     * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
     * @param   array              $attributes   The attributes to apply to the XML element
     *
     * @return  null
     * @deprecated 3.3 Use FormHelper::attributes($xml, $attributes);
     */
    public static function xmlAddAttributes(&$xml, $attributes = [])
    {
        FormHelper::attributes($xml, $attributes);
    }
    /**
     * xmlAddOptions
     *
     * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
     * @param   array              $options      The options to apply to the XML element
     *
     * @return  void
     * @deprecated 3.3 Use FormHelper::options($xml, $options);
     */
    public static function xmlAddOptions(&$xml, $options = [])
    {
        FormHelper::options($xml, $options);
    }
    /**
     * get the field object
     *
     * @param   array      $attributes   The array of attributes
     * @param   string     $default      The default of the field
     * @param   array      $options      The options to apply to the XML element
     *
     * @return  object
     * @deprecated 3.3 Use FormHelper::field($attributes, $default, $options);
     */
    public static function getFieldObject(&$attributes, $default = '', $options = \null)
    {
        return FormHelper::field($attributes, $default, $options);
    }
    /**
     * get the field xml
     *
     * @param   array      $attributes   The array of attributes
     * @param   array      $options      The options to apply to the XML element
     *
     * @return  object
     * @deprecated 3.3 Use FormHelper::xml($attributes, $options);
     */
    public static function getFieldXML(&$attributes, $options = \null)
    {
        return FormHelper::xml($attributes, $options);
    }
    /**
     * Render Bool Button
     *
     * @param   array   $args   All the args for the button
     *                             0) name
     *                             1) additional (options class) // not used at this time
     *                             2) default
     *                             3) yes (name)
     *                             4) no (name)
     *
     * @return  string    The input html of the button
     *
     */
    public static function renderBoolButton()
    {
        $args = \func_get_args();
        // check if there is additional button class
        $additional = isset($args[1]) ? (string) $args[1] : '';
        // not used at this time
        // button attributes
        $buttonAttributes = [
            'type' => 'radio',
            'name' => isset($args[0]) ? \VDM\Joomla\Utilities\StringHelper::html($args[0]) : 'bool_button',
            'label' => isset($args[0]) ? \VDM\Joomla\Utilities\StringHelper::safe(\VDM\Joomla\Utilities\StringHelper::html($args[0]), 'Ww') : 'Bool Button',
            // not seen anyway
            'class' => 'btn-group',
            'filter' => 'INT',
            'default' => isset($args[2]) ? (int) $args[2] : 0,
        ];
        // set the button options
        $buttonOptions = ['1' => isset($args[3]) ? \VDM\Joomla\Utilities\StringHelper::html($args[3]) : 'JYES', '0' => isset($args[4]) ? \VDM\Joomla\Utilities\StringHelper::html($args[4]) : 'JNO'];
        // return the input
        return FormHelper::field($buttonAttributes, $buttonAttributes['default'], $buttonOptions)->input;
    }
    /**
     * Get a variable
     *
     * @param   string   $table        The table from which to get the variable
     * @param   string   $where        The value where
     * @param   string   $whereString  The target/field string where/name
     * @param   string   $what         The return field
     * @param   string   $operator     The operator between $whereString/field and $where/value
     * @param   string   $main         The component in which the table is found
     *
     * @return  mix string/int/float
     * @deprecated 3.3 Use GetHelper::var(...);
     */
    public static function getVar($table, $where = \null, $whereString = 'user', $what = 'id', $operator = '=', $main = 'festival')
    {
        return GetHelper::var($table, $where, $whereString, $what, $operator, $main);
    }
    /**
     * Get array of variables
     *
     * @param   string   $table        The table from which to get the variables
     * @param   string   $where        The value where
     * @param   string   $whereString  The target/field string where/name
     * @param   string   $what         The return field
     * @param   string   $operator     The operator between $whereString/field and $where/value
     * @param   string   $main         The component in which the table is found
     * @param   bool     $unique       The switch to return a unique array
     *
     * @return  array
     * @deprecated 3.3 Use GetHelper::vars(...);
     */
    public static function getVars($table, $where = \null, $whereString = 'user', $what = 'id', $operator = 'IN', $main = 'festival', $unique = \true)
    {
        return GetHelper::vars($table, $where, $whereString, $what, $operator, $main, $unique);
    }
    public static function isPublished($id, $type)
    {
        if ($type == 'raw') {
            $type = 'item';
        }
        $db = Factory::getDbo();
        $query = $db->getQuery(\true);
        $query->select(['a.published']);
        $query->from('#__festival_' . $type . ' AS a');
        $query->where('a.id = ' . (int) $id);
        $query->where('a.published = 1');
        $db->setQuery($query);
        $db->execute();
        $found = $db->getNumRows();
        if ($found) {
            return \true;
        }
        return \false;
    }
    public static function getGroupName($id)
    {
        $db = Factory::getDBO();
        $query = $db->getQuery(\true);
        $query->select(['a.title']);
        $query->from('#__usergroups AS a');
        $query->where('a.id = ' . (int) $id);
        $db->setQuery($query);
        $db->execute();
        $found = $db->getNumRows();
        if ($found) {
            return $db->loadResult();
        }
        return $id;
    }
    /**
     * Get the action permissions
     *
     * @param  string   $view        The related view name
     * @param  int      $record      The item to act upon
     * @param  string   $views       The related list view name
     * @param  mixed    $target      Only get this permission (like edit, create, delete)
     * @param  string   $component   The target component
     * @param  object   $user        The user whose permissions we are loading
     *
     * @return  object   The JObject of permission/authorised actions
     *
     */
    public static function getActions($view, &$record = \null, $views = \null, $target = \null, $component = 'festival', $user = 'null')
    {
        // load the user if not given
        if (!ObjectHelper::check($user)) {
            // get the user object
            $user = Factory::getUser();
        }
        // load the JObject
        $result = new CMSObject();
        // make view name safe (just incase)
        $view = \VDM\Joomla\Utilities\StringHelper::safe($view);
        if (\VDM\Joomla\Utilities\StringHelper::check($views)) {
            $views = \VDM\Joomla\Utilities\StringHelper::safe($views);
        }
        // get all actions from component
        $actions = Access::getActionsFromFile(\JPATH_ADMINISTRATOR . '/components/com_' . $component . '/access.xml', "/access/section[@name='component']/");
        // if non found then return empty JObject
        if (empty($actions)) {
            return $result;
        }
        // get created by if not found
        if (ObjectHelper::check($record) && !isset($record->created_by) && isset($record->id)) {
            $record->created_by = GetHelper::var($view, $record->id, 'id', 'created_by', '=', $component);
        }
        // set actions only set in component settings
        $componentActions = ['core.admin', 'core.manage', 'core.options', 'core.export'];
        // check if we have a target
        $checkTarget = \false;
        if ($target) {
            // convert to an array
            if (\VDM\Joomla\Utilities\StringHelper::check($target)) {
                $target = [$target];
            }
            // check if we are good to go
            if (\VDM\Joomla\Utilities\ArrayHelper::check($target)) {
                $checkTarget = \true;
            }
        }
        // loop the actions and set the permissions
        foreach ($actions as $action) {
            // check target action filter
            if ($checkTarget && self::filterActions($view, $action->name, $target)) {
                continue;
            }
            // set to use component default
            $fallback = \true;
            // reset permission per/action
            $permission = \false;
            $catpermission = \false;
            // set area
            $area = 'comp';
            // check if the record has an ID and the action is item related (not a component action)
            if (ObjectHelper::check($record) && isset($record->id) && $record->id > 0 && !\in_array($action->name, $componentActions) && (\strpos($action->name, 'core.') !== \false || \strpos($action->name, $view . '.') !== \false)) {
                // we are in item
                $area = 'item';
                // The record has been set. Check the record permissions.
                $permission = $user->authorise($action->name, 'com_' . $component . '.' . $view . '.' . (int) $record->id);
                // if no permission found, check edit own
                if (!$permission) {
                    // With edit, if the created_by matches current user then dig deeper.
                    if (($action->name === 'core.edit' || $action->name === $view . '.edit') && $record->created_by > 0 && $record->created_by == $user->id) {
                        // the correct target
                        $coreCheck = (array) \explode('.', $action->name);
                        // check that we have both local and global access
                        if ($user->authorise($coreCheck[0] . '.edit.own', 'com_' . $component . '.' . $view . '.' . (int) $record->id) && $user->authorise($coreCheck[0] . '.edit.own', 'com_' . $component)) {
                            // allow edit
                            $result->set($action->name, \true);
                            // set not to use global default
                            // because we already validated it
                            $fallback = \false;
                        } else {
                            // do not allow edit
                            $result->set($action->name, \false);
                            $fallback = \false;
                        }
                    }
                } elseif (\VDM\Joomla\Utilities\StringHelper::check($views) && isset($record->catid) && $record->catid > 0) {
                    // we are in item
                    $area = 'category';
                    // set the core check
                    $coreCheck = \explode('.', $action->name);
                    $core = $coreCheck[0];
                    // make sure we use the core. action check for the categories
                    if (\strpos($action->name, $view) !== \false && \strpos($action->name, 'core.') === \false) {
                        $coreCheck[0] = 'core';
                        $categoryCheck = \implode('.', $coreCheck);
                    } else {
                        $categoryCheck = $action->name;
                    }
                    // The record has a category. Check the category permissions.
                    $catpermission = $user->authorise($categoryCheck, 'com_' . $component . '.' . $views . '.category.' . (int) $record->catid);
                    if (!$catpermission && !\is_null($catpermission)) {
                        // With edit, if the created_by matches current user then dig deeper.
                        if (($action->name === 'core.edit' || $action->name === $view . '.edit') && $record->created_by > 0 && $record->created_by == $user->id) {
                            // check that we have both local and global access
                            if ($user->authorise('core.edit.own', 'com_' . $component . '.' . $views . '.category.' . (int) $record->catid) && $user->authorise($core . '.edit.own', 'com_' . $component)) {
                                // allow edit
                                $result->set($action->name, \true);
                                // set not to use global default
                                // because we already validated it
                                $fallback = \false;
                            } else {
                                // do not allow edit
                                $result->set($action->name, \false);
                                $fallback = \false;
                            }
                        }
                    }
                }
            }
            // if allowed then fallback on component global settings
            if ($fallback) {
                // if item/category blocks access then don't fall back on global
                if ($area === 'item' && !$permission || $area === 'category' && !$catpermission) {
                    // do not allow
                    $result->set($action->name, \false);
                } else {
                    $result->set($action->name, $user->authorise($action->name, 'com_' . $component));
                }
            }
        }
        return $result;
    }
    /**
     * Filter the action permissions
     *
     * @param  string   $action   The action to check
     * @param  array    $targets  The array of target actions
     *
     * @return  boolean   true if action should be filtered out
     *
     */
    protected static function filterActions(&$view, &$action, &$targets)
    {
        foreach ($targets as $target) {
            if (\strpos($action, $view . '.' . $target) !== \false || \strpos($action, 'core.' . $target) !== \false) {
                return \false;
                break;
            }
        }
        return \true;
    }
    /**
     * Check if have an json string
     *
     * @input	string   The json string to check
     *
     * @returns bool true on success
     * @deprecated 3.3 Use JsonHelper::check($string);
     */
    public static function checkJson($string)
    {
        return JsonHelper::check($string);
    }
    /**
     * Check if have an object with a length
     *
     * @input	object   The object to check
     *
     * @returns bool true on success
     * @deprecated 3.3 Use ObjectHelper::check($object);
     */
    public static function checkObject($object)
    {
        return ObjectHelper::check($object);
    }
    /**
     * Check if have an array with a length
     *
     * @input	array   The array to check
     *
     * @returns bool/int  number of items in array on success
     * @deprecated 3.3 Use UtilitiesArrayHelper::check($array, $removeEmptyString);
     */
    public static function checkArray($array, $removeEmptyString = \false)
    {
        return \VDM\Joomla\Utilities\ArrayHelper::check($array, $removeEmptyString);
    }
    /**
     * Check if have a string with a length
     *
     * @input	string   The string to check
     *
     * @returns bool true on success
     * @deprecated 3.3 Use UtilitiesStringHelper::check($string);
     */
    public static function checkString($string)
    {
        return \VDM\Joomla\Utilities\StringHelper::check($string);
    }
    /**
     * Check if we are connected
     * Thanks https://stackoverflow.com/a/4860432/1429677
     *
     * @returns bool true on success
     */
    public static function isConnected()
    {
        // If example.com is down, then probably the whole internet is down, since IANA maintains the domain. Right?
        $connected = @\fsockopen("www.example.com", 80);
        // website, port  (try 80 or 443)
        if ($connected) {
            //action when connected
            $is_conn = \true;
            \fclose($connected);
        } else {
            //action in connection failure
            $is_conn = \false;
        }
        return $is_conn;
    }
    /**
     * Merge an array of array's
     *
     * @input	array   The arrays you would like to merge
     *
     * @returns array on success
     * @deprecated 3.3 Use UtilitiesArrayHelper::merge($arrays);
     */
    public static function mergeArrays($arrays)
    {
        return \VDM\Joomla\Utilities\ArrayHelper::merge($arrays);
    }
    // typo sorry!
    public static function sorten($string, $length = 40, $addTip = \true)
    {
        return self::shorten($string, $length, $addTip);
    }
    /**
     * Shorten a string
     *
     * @input	string   The you would like to shorten
     *
     * @returns string on success
     * @deprecated 3.3 Use UtilitiesStringHelper::shorten(...);
     */
    public static function shorten($string, $length = 40, $addTip = \true)
    {
        return \VDM\Joomla\Utilities\StringHelper::shorten($string, $length, $addTip);
    }
    /**
     * Making strings safe (various ways)
     *
     * @input	string   The you would like to make safe
     *
     * @returns string on success
     * @deprecated 3.3 Use UtilitiesStringHelper::safe(...);
     */
    public static function safeString($string, $type = 'L', $spacer = '_', $replaceNumbers = \true, $keepOnlyCharacters = \true)
    {
        return \VDM\Joomla\Utilities\StringHelper::safe($string, $type, $spacer, $replaceNumbers, $keepOnlyCharacters);
    }
    /**
     * Convert none English strings to code usable string
     *
     * @input	an string
     *
     * @returns a string
     * @deprecated 3.3 Use UtilitiesStringHelper::transliterate($string);
     */
    public static function transliterate($string)
    {
        return \VDM\Joomla\Utilities\StringHelper::transliterate($string);
    }
    /**
     * make sure a string is HTML save
     *
     * @input	an html string
     *
     * @returns a string
     * @deprecated 3.3 Use UtilitiesStringHelper::html(...);
     */
    public static function htmlEscape($var, $charset = 'UTF-8', $shorten = \false, $length = 40)
    {
        return \VDM\Joomla\Utilities\StringHelper::html($var, $charset, $shorten, $length);
    }
    /**
     * Convert all int in a string to an English word string
     *
     * @input	an string with numbers
     *
     * @returns a string
     * @deprecated 3.3 Use UtilitiesStringHelper::numbers($string);
     */
    public static function replaceNumbers($string)
    {
        return \VDM\Joomla\Utilities\StringHelper::numbers($string);
    }
    /**
     * Convert an integer into an English word string
     * Thanks to Tom Nicholson <http://php.net/manual/en/function.strval.php#41988>
     *
     * @input	an int
     * @returns a string
     * @deprecated 3.3 Use UtilitiesStringHelper::number($x);
     */
    public static function numberToString($x)
    {
        return \VDM\Joomla\Utilities\StringHelper::number($x);
    }
    /**
     * Random Key
     *
     * @returns a string
     * @deprecated 3.3 Use UtilitiesStringHelper::random($size);
     */
    public static function randomkey($size)
    {
        return \VDM\Joomla\Utilities\StringHelper::random($size);
    }
}
