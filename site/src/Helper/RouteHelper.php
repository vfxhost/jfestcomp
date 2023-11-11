<?php

namespace Acme\Festival\Site\Helper;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		route.php
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
namespace Acme\Festival\Site\Helper;

/**
 * Festival Route Helper
 **/
abstract class RouteHelper
{
    protected static $lookup;
    /**
     * @param int The route of the Project
     */
    public static function getProjectRoute($id = 0, $catid = 0)
    {
        if ($id > 0) {
            // Initialize the needel array.
            $needles = ['project' => [(int) $id]];
            // Create the link
            $link = 'index.php?option=com_festival&view=project&id=' . $id;
        } else {
            // Initialize the needel array.
            $needles = ['project' => []];
            // Create the link but don't add the id.
            $link = 'index.php?option=com_festival&view=project';
        }
        if ($catid > 1) {
            $categories = Categories::getInstance('festival.project');
            $category = $categories->get($catid);
            if ($category) {
                $needles['category'] = \array_reverse($category->getPath());
                $needles['categories'] = $needles['category'];
                $link .= '&catid=' . $catid;
            }
        }
        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid=' . $item;
        }
        return $link;
    }
    /**
     * Get the URL route for festival category from a category ID and language
     *
     * @param   mixed    $catid     The id of the items's category either an integer id or a instance of JCategoryNode
     * @param   mixed    $language  The id of the language being used.
     *
     * @return  string  The link to the contact
     *
     * @since   1.5
     */
    public static function getCategoryRoute_keep_for_later($catid, $language = 0)
    {
        if ($catid instanceof CategoryNode) {
            $id = $catid->id;
            $category = $catid;
        } else {
            throw new \Exception('First parameter must be JCategoryNode');
        }
        $views = [];
        $view = $views[$category->extension];
        if ($id < 1 || !$category instanceof CategoryNode) {
            $link = '';
        } else {
            //Create the link
            $link = 'index.php?option=com_festival&view=' . $view . '&category=' . $category->slug;
            $needles = [$view => [$id], 'category' => [$id]];
            if ($language && $language != "*" && Multilanguage::isEnabled()) {
                $db = Factory::getDbo();
                $query = $db->getQuery(\true)->select('a.sef AS sef')->select('a.lang_code AS lang_code')->from('#__languages AS a');
                $db->setQuery($query);
                $langs = $db->loadObjectList();
                foreach ($langs as $lang) {
                    if ($language == $lang->lang_code) {
                        $link .= '&lang=' . $lang->sef;
                        $needles['language'] = $language;
                    }
                }
            }
            if ($item = self::_findItem($needles, 'category')) {
                $link .= '&Itemid=' . $item;
            } else {
                if ($category) {
                    $catids = \array_reverse($category->getPath());
                    $needles = ['category' => $catids];
                    if ($item = self::_findItem($needles, 'category')) {
                        $link .= '&Itemid=' . $item;
                    } elseif ($item = self::_findItem(\null, 'category')) {
                        $link .= '&Itemid=' . $item;
                    }
                }
            }
        }
        return $link;
    }
    protected static function _findItem($needles = \null, $type = \null)
    {
        $app = Factory::getApplication();
        $menus = $app->getMenu('site');
        $language = $needles['language'] ?? '*';
        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language])) {
            self::$lookup[$language] = [];
            $component = ComponentHelper::getComponent('com_festival');
            $attributes = ['component_id'];
            $values = [$component->id];
            if ($language != '*') {
                $attributes[] = 'language';
                $values[] = [$needles['language'], '*'];
            }
            $items = $menus->getItems($attributes, $values);
            foreach ($items as $item) {
                if (isset($item->query) && isset($item->query['view'])) {
                    $view = $item->query['view'];
                    if (!isset(self::$lookup[$language][$view])) {
                        self::$lookup[$language][$view] = [];
                    }
                    if (isset($item->query['id'])) {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*') {
                            self::$lookup[$language][$view][$item->query['id']] = $item->id;
                        }
                    } else {
                        self::$lookup[$language][$view][0] = $item->id;
                    }
                }
            }
        }
        if ($needles) {
            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$language][$view])) {
                    if (FestivalHelper::checkArray($ids)) {
                        foreach ($ids as $id) {
                            if (isset(self::$lookup[$language][$view][(int) $id])) {
                                return self::$lookup[$language][$view][(int) $id];
                            }
                        }
                    } elseif (isset(self::$lookup[$language][$view][0])) {
                        return self::$lookup[$language][$view][0];
                    }
                }
            }
        }
        if ($type) {
            // Check if the global menu item has been set.
            $params = ComponentHelper::getParams('com_festival');
            if ($item = $params->get($type . '_menu', 0)) {
                return $item;
            }
        }
        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();
        if ($active && $active->component == 'com_festival' && ($language == '*' || \in_array($active->language, ['*', $language]) || !Multilanguage::isEnabled())) {
            return $active->id;
        }
        // If not found, return language specific home link
        $default = $menus->getDefault($language);
        return !empty($default->id) ? $default->id : \null;
    }
}
