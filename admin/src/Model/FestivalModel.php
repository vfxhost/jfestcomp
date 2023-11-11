<?php

namespace Acme\Festival\Administrator\Model;

use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Acme\Festival\Administrator\Helper\FestivalHelper;
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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
namespace Acme\Festival\Administrator\Model;

/**
 * Festival List Model
 */
class FestivalModel extends ListModel
{
    public function getIcons()
    {
        // load user for access menus
        $user = Factory::getUser();
        // reset icon array
        $icons = [];
        // view groups array
        $viewGroups = ['main' => []];
        // view access array
        $viewAccess = ['projecs.access' => 'project.access', 'project.access' => 'project.access', 'projecs.submenu' => 'project.submenu'];
        // loop over the $views
        foreach ($viewGroups as $group => $views) {
            $i = 0;
            if (FestivalHelper::checkArray($views)) {
                foreach ($views as $view) {
                    $add = \false;
                    // external views (links)
                    if (\strpos($view, '||') !== \false) {
                        $dwd = \explode('||', $view);
                        if (\count($dwd) == 3) {
                            [$type, $name, $url] = $dwd;
                            $viewName = $name;
                            $alt = $name;
                            $url = $url;
                            $image = $name . '.' . $type;
                            $name = 'COM_FESTIVAL_DASHBOARD_' . FestivalHelper::safeString($name, 'U');
                        }
                    } elseif (\strpos($view, '.') !== \false) {
                        $dwd = \explode('.', $view);
                        if (\count($dwd) == 3) {
                            [$type, $name, $action] = $dwd;
                        } elseif (\count($dwd) == 2) {
                            [$type, $name] = $dwd;
                            $action = \false;
                        }
                        if ($action) {
                            $viewName = $name;
                            switch ($action) {
                                case 'add':
                                    $url = 'index.php?option=com_festival&view=' . $name . '&layout=edit';
                                    $image = $name . '_' . $action . '.' . $type;
                                    $alt = $name . '&nbsp;' . $action;
                                    $name = 'COM_FESTIVAL_DASHBOARD_' . FestivalHelper::safeString($name, 'U') . '_ADD';
                                    $add = \true;
                                    break;
                                default:
                                    // check for new convention (more stable)
                                    if (\strpos($action, '_qpo0O0oqp_') !== \false) {
                                        [$action, $extension] = (array) \explode('_qpo0O0oqp_', $action);
                                        $extension = \str_replace('_po0O0oq_', '.', $extension);
                                    } else {
                                        $extension = 'com_festival.' . $name;
                                    }
                                    $url = 'index.php?option=com_categories&view=categories&extension=' . $extension;
                                    $image = $name . '_' . $action . '.' . $type;
                                    $alt = $viewName . '&nbsp;' . $action;
                                    $name = 'COM_FESTIVAL_DASHBOARD_' . FestivalHelper::safeString($name, 'U') . '_' . FestivalHelper::safeString($action, 'U');
                                    break;
                            }
                        } else {
                            $viewName = $name;
                            $alt = $name;
                            $url = 'index.php?option=com_festival&view=' . $name;
                            $image = $name . '.' . $type;
                            $name = 'COM_FESTIVAL_DASHBOARD_' . FestivalHelper::safeString($name, 'U');
                            $hover = \false;
                        }
                    } else {
                        $viewName = $view;
                        $alt = $view;
                        $url = 'index.php?option=com_festival&view=' . $view;
                        $image = $view . '.png';
                        $name = \ucwords($view) . '<br /><br />';
                        $hover = \false;
                    }
                    // first make sure the view access is set
                    if (FestivalHelper::checkArray($viewAccess)) {
                        // setup some defaults
                        $dashboard_add = \false;
                        $dashboard_list = \false;
                        $accessTo = '';
                        $accessAdd = '';
                        // access checking start
                        $accessCreate = isset($viewAccess[$viewName . '.create']) ? FestivalHelper::checkString($viewAccess[$viewName . '.create']) : \false;
                        $accessAccess = isset($viewAccess[$viewName . '.access']) ? FestivalHelper::checkString($viewAccess[$viewName . '.access']) : \false;
                        // set main controllers
                        $accessDashboard_add = isset($viewAccess[$viewName . '.dashboard_add']) ? FestivalHelper::checkString($viewAccess[$viewName . '.dashboard_add']) : \false;
                        $accessDashboard_list = isset($viewAccess[$viewName . '.dashboard_list']) ? FestivalHelper::checkString($viewAccess[$viewName . '.dashboard_list']) : \false;
                        // check for adding access
                        if ($add && $accessCreate) {
                            $accessAdd = $viewAccess[$viewName . '.create'];
                        } elseif ($add) {
                            $accessAdd = 'core.create';
                        }
                        // check if access to view is set
                        if ($accessAccess) {
                            $accessTo = $viewAccess[$viewName . '.access'];
                        }
                        // set main access controllers
                        if ($accessDashboard_add) {
                            $dashboard_add = $user->authorise($viewAccess[$viewName . '.dashboard_add'], 'com_festival');
                        }
                        if ($accessDashboard_list) {
                            $dashboard_list = $user->authorise($viewAccess[$viewName . '.dashboard_list'], 'com_festival');
                        }
                        if (FestivalHelper::checkString($accessAdd) && FestivalHelper::checkString($accessTo)) {
                            // check access
                            if ($user->authorise($accessAdd, 'com_festival') && $user->authorise($accessTo, 'com_festival') && $dashboard_add) {
                                $icons[$group][$i] = new \StdClass();
                                $icons[$group][$i]->url = $url;
                                $icons[$group][$i]->name = $name;
                                $icons[$group][$i]->image = $image;
                                $icons[$group][$i]->alt = $alt;
                            }
                        } elseif (FestivalHelper::checkString($accessTo)) {
                            // check access
                            if ($user->authorise($accessTo, 'com_festival') && $dashboard_list) {
                                $icons[$group][$i] = new \StdClass();
                                $icons[$group][$i]->url = $url;
                                $icons[$group][$i]->name = $name;
                                $icons[$group][$i]->image = $image;
                                $icons[$group][$i]->alt = $alt;
                            }
                        } elseif (FestivalHelper::checkString($accessAdd)) {
                            // check access
                            if ($user->authorise($accessAdd, 'com_festival') && $dashboard_add) {
                                $icons[$group][$i] = new \StdClass();
                                $icons[$group][$i]->url = $url;
                                $icons[$group][$i]->name = $name;
                                $icons[$group][$i]->image = $image;
                                $icons[$group][$i]->alt = $alt;
                            }
                        } else {
                            $icons[$group][$i] = new \StdClass();
                            $icons[$group][$i]->url = $url;
                            $icons[$group][$i]->name = $name;
                            $icons[$group][$i]->image = $image;
                            $icons[$group][$i]->alt = $alt;
                        }
                    } else {
                        $icons[$group][$i] = new \StdClass();
                        $icons[$group][$i]->url = $url;
                        $icons[$group][$i]->name = $name;
                        $icons[$group][$i]->image = $image;
                        $icons[$group][$i]->alt = $alt;
                    }
                    $i++;
                }
            } else {
                $icons[$group][$i] = \false;
            }
        }
        return $icons;
    }
}
