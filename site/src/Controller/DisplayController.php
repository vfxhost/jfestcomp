<?php

namespace Acme\Festival\Site\Controller;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Router\Route;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		controller.php
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
/**
 * Festival Component Base Controller
 */
class DisplayController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JController  This object to support chaining.
     *
     */
    function display($cachable = \false, $urlparams = \false)
    {
        // set default view if not set
        $view = $this->input->getCmd('view', '');
        $this->input->set('view', $view);
        $isEdit = $this->checkEditView($view);
        $layout = $this->input->get('layout', \null, 'WORD');
        $id = $this->input->getInt('id');
        // $cachable	= true; (TODO) working on a fix [gh-238](https://github.com/vdm-io/Joomla-Component-Builder/issues/238)
        // insure that the view is not cashable if edit view or if user is logged in
        $user = Factory::getUser();
        if ($user->get('id') || $isEdit) {
            $cachable = \false;
        }
        // Check for edit form.
        if ($isEdit) {
            if ($layout == 'edit' && !$this->checkEditId('com_festival.edit.' . $view, $id)) {
                // Somehow the person just went to the form - we don't allow that.
                $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
                $this->setMessage($this->getError(), 'error');
                // check if item was opend from other then its own list view
                $ref = $this->input->getCmd('ref', 0);
                $refid = $this->input->getInt('refid', 0);
                // set redirect
                if ($refid > 0 && FestivalHelper::checkString($ref)) {
                    // redirect to item of ref
                    $this->setRedirect(Route::_('index.php?option=com_festival&view=' . (string) $ref . '&layout=edit&id=' . (int) $refid, \false));
                } elseif (FestivalHelper::checkString($ref)) {
                    // redirect to ref
                    $this->setRedirect(Route::_('index.php?option=com_festival&view=' . (string) $ref, \false));
                } else {
                    // normal redirect back to the list default site view
                    $this->setRedirect(Route::_('index.php?option=com_festival&view=', \false));
                }
                return \false;
            }
        }
        // we may need to make this more dynamic in the future. (TODO)
        $safeurlparams = ['catid' => 'INT', 'id' => 'INT', 'cid' => 'ARRAY', 'year' => 'INT', 'month' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT', 'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'filter-search' => 'STRING', 'print' => 'BOOLEAN', 'lang' => 'CMD', 'Itemid' => 'INT'];
        // should these not merge?
        if (FestivalHelper::checkArray($urlparams)) {
            $safeurlparams = FestivalHelper::mergeArrays([$urlparams, $safeurlparams]);
        }
        return parent::display($cachable, $safeurlparams);
    }
    protected function checkEditView($view)
    {
        if (FestivalHelper::checkString($view)) {
            $views = [];
            // check if this is a edit view
            if (\in_array($view, $views)) {
                return \true;
            }
        }
        return \false;
    }
}
