<?php

namespace Acme\Festival\Administrator\View\Project;

use Joomla\CMS\HTML\HTMLHelper;
use Acme\Festival\Site\Helper\FestivalHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Component\ComponentHelper;
use Acme\Festival\Administrator\Helper\FestivalHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		view.html.php
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
use Joomla\CMS\MVC\View\HtmlView;
namespace Acme\Festival\Administrator\View\Project;

/**
 * Project Html View class
 */
class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
    /**
     * display method of View
     * @return void
     */
    public function display($tpl = \null)
    {
        // set params
        $this->params = ComponentHelper::getParams('com_festival');
        // Assign the variables
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        $this->state = $this->get('State');
        // get action permissions
        $this->canDo = FestivalHelper::getActions('project', $this->item);
        // get input
        $jinput = Factory::getApplication()->input;
        $this->ref = $jinput->get('ref', 0, 'word');
        $this->refid = $jinput->get('refid', 0, 'int');
        $return = $jinput->get('return', \null, 'base64');
        // set the referral string
        $this->referral = '';
        if ($this->refid && $this->ref) {
            // return to the item that referred to this item
            $this->referral = '&ref=' . (string) $this->ref . '&refid=' . (int) $this->refid;
        } elseif ($this->ref) {
            // return to the list view that referred to this item
            $this->referral = '&ref=' . (string) $this->ref;
        }
        // check return value
        if (!\is_null($return)) {
            // add the return value
            $this->referral .= '&return=' . (string) $return;
        }
        // Set the toolbar
        $this->addToolBar();
        // Check for errors.
        if (is_array($errors = $this->get('Errors')) || ($errors = $this->get('Errors')) instanceof \Countable ? \count($errors = $this->get('Errors')) : 0) {
            throw new \Exception(\implode("\n", $errors), 500);
        }
        // Display the template
        parent::display($tpl);
        // Set the document
        $this->setDocument();
    }
    /**
     * Setting the toolbar
     */
    protected function addToolBar()
    {
        Factory::getApplication()->input->set('hidemainmenu', \true);
        $user = Factory::getUser();
        $userId = $user->id;
        $isNew = $this->item->id == 0;
        ToolbarHelper::title(Text::_($isNew ? 'COM_FESTIVAL_PROJECT_NEW' : 'COM_FESTIVAL_PROJECT_EDIT'), 'pencil-2 article-add');
        // Built the actions for new and existing records.
        if (FestivalHelper::checkString($this->referral)) {
            if ($this->canDo->get('core.create') && $isNew) {
                // We can create the record.
                \JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
            } elseif ($this->canDo->get('core.edit')) {
                // We can save the record.
                \JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
            }
            if ($isNew) {
                // Do not creat but cancel.
                \JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CANCEL');
            } else {
                // We can close it.
                \JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
            }
        } else {
            if ($isNew) {
                // For new records, check the create permission.
                if ($this->canDo->get('core.create')) {
                    \JToolBarHelper::apply('project.apply', 'JTOOLBAR_APPLY');
                    \JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
                    \JToolBarHelper::custom('project.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', \false);
                }
                \JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CANCEL');
            } else {
                if ($this->canDo->get('core.edit')) {
                    // We can save the new record
                    \JToolBarHelper::apply('project.apply', 'JTOOLBAR_APPLY');
                    \JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
                    // We can save this record, but check the create permission to see
                    // if we can return to make a new one.
                    if ($this->canDo->get('core.create')) {
                        \JToolBarHelper::custom('project.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', \false);
                    }
                }
                $canVersion = $this->canDo->get('core.version') && $this->canDo->get('project.version');
                if ($this->state->params->get('save_history', 1) && $this->canDo->get('core.edit') && $canVersion) {
                    ToolbarHelper::versions('com_festival.project', $this->item->id);
                }
                if ($this->canDo->get('core.create')) {
                    \JToolBarHelper::custom('project.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', \false);
                }
                \JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
            }
        }
        ToolbarHelper::divider();
        // set help url for this view if found
        $this->help_url = FestivalHelper::getHelpUrl('project');
        if (FestivalHelper::checkString($this->help_url)) {
            ToolbarHelper::help('COM_FESTIVAL_HELP_MANAGER', \false, $this->help_url);
        }
    }
    /**
     * Escapes a value for output in a view script.
     *
     * @param   mixed  $var  The output to escape.
     *
     * @return  mixed  The escaped value.
     */
    public function escape($var)
    {
        if (\strlen($var) > 30) {
            // use the helper htmlEscape method instead and shorten the string
            return FestivalHelper::htmlEscape($var, $this->_charset, \true, 30);
        }
        // use the helper htmlEscape method instead.
        return FestivalHelper::htmlEscape($var, $this->_charset);
    }
    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $isNew = $this->item->id < 1;
        if (!isset($this->document)) {
            $this->document = Factory::getDocument();
        }
        $this->document->setTitle(Text::_($isNew ? 'COM_FESTIVAL_PROJECT_NEW' : 'COM_FESTIVAL_PROJECT_EDIT'));
        $this->document->addStyleSheet(\JURI::root() . "administrator/components/com_festival/assets/css/project.css", FestivalHelper::jVersion()->isCompatible('3.8.0') ? ['version' => 'auto'] : 'text/css');
        $this->document->addScript(\JURI::root() . $this->script, FestivalHelper::jVersion()->isCompatible('3.8.0') ? ['version' => 'auto'] : 'text/javascript');
        $this->document->addScript(\JURI::root() . "administrator/components/com_festival/views/project/submitbutton.js", FestivalHelper::jVersion()->isCompatible('3.8.0') ? ['version' => 'auto'] : 'text/javascript');
        Text::script('view not acceptable. Error');
    }
}
