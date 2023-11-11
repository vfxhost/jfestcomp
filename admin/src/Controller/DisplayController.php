<?php

namespace Acme\Festival\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Acme\Festival\Administrator\Helper\FestivalHelper;
use Joomla\CMS\Language\Text;
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
namespace Acme\Festival\Administrator\Controller;

/**
 * General Controller of Festival component
 */
class DisplayController extends BaseController
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     * Recognized key values include 'name', 'default_task', 'model_path', and
     * 'view_path' (this list is not meant to be comprehensive).
     *
     * @since   3.0
     */
    public function __construct($config = [])
    {
        // set the default view
        $config['default_view'] = 'festival';
        parent::__construct($config);
    }
    /**
     * display task
     *
     * @return void
     */
    function display($cachable = \false, $urlparams = \false)
    {
        // set default view if not set
        $view = $this->input->getCmd('view', 'festival');
        $data = $this->getViewRelation($view);
        $layout = $this->input->get('layout', \null, 'WORD');
        $id = $this->input->getInt('id');
        // Check for edit form.
        if (FestivalHelper::checkArray($data)) {
            if ($data['edit'] && $layout == 'edit' && !$this->checkEditId('com_festival.edit.' . $data['view'], $id)) {
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
                    // normal redirect back to the list view
                    $this->setRedirect(Route::_('index.php?option=com_festival&view=' . $data['views'], \false));
                }
                return \false;
            }
        }
        return parent::display($cachable, $urlparams);
    }
    protected function getViewRelation($view)
    {
        // check the we have a value
        if (FestivalHelper::checkString($view)) {
            // the view relationships
            $views = ['project' => 'projecs'];
            // check if this is a list view
            if (\in_array($view, $views)) {
                // this is a list view
                return array('edit' => \false, 'view' => \array_search($view, $views), 'views' => $view);
            }
            // check if it is an edit view
            if (\array_key_exists($view, $views)) {
                // this is a edit view
                return array('edit' => \true, 'view' => $view, 'views' => $views[$view]);
            }
        }
        return \false;
    }
}
