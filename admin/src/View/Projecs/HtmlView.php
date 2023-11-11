<?php

namespace Acme\Festival\Administrator\View\Projecs;

use Acme\Festival\Site\Helper\FestivalHelper;
use Acme\Festival\Administrator\Helper\FestivalHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Acme\festival\Administrator\Service\Html\Batch_;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\HTMLHelper;
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
namespace Acme\Festival\Administrator\View\Projecs;

/**
 * Festival Html View class for the Projecs
 */
class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
    /**
     * Projecs view display method
     * @return void
     */
    function display($tpl = \null)
    {
        if ($this->getLayout() !== 'modal') {
            // Include helper submenu
            FestivalHelper::addSubmenu('projecs');
        }
        // Assign data to the view
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->user = Factory::getUser();
        // Load the filter form from xml.
        $this->filterForm = $this->get('FilterForm');
        // Load the active filters.
        $this->activeFilters = $this->get('ActiveFilters');
        // Add the list ordering clause.
        $this->listOrder = $this->escape($this->state->get('list.ordering', 'a.id'));
        $this->listDirn = $this->escape($this->state->get('list.direction', 'DESC'));
        $this->saveOrder = $this->listOrder == 'a.ordering';
        // set the return here value
        $this->return_here = \urlencode(\base64_encode((string) Uri::getInstance()));
        // get global action permissions
        $this->canDo = FestivalHelper::getActions('project');
        $this->canEdit = $this->canDo->get('core.edit');
        $this->canState = $this->canDo->get('core.edit.state');
        $this->canCreate = $this->canDo->get('core.create');
        $this->canDelete = $this->canDo->get('core.delete');
        $this->canBatch = $this->canDo->get('project.batch') && $this->canDo->get('core.batch');
        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
            $this->sidebar = Sidebar::render();
            // load the batch html
            if ($this->canCreate && $this->canEdit && $this->canState) {
                $this->batchDisplay = Batch_::render();
            }
        }
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
        \JToolBarHelper::title(Text::_('COM_FESTIVAL_PROJECS'), 'joomla');
        Sidebar::setAction('index.php?option=com_festival&view=projecs');
        FormHelper::addFieldPath(\JPATH_COMPONENT . '/models/fields');
        if ($this->canCreate) {
            \JToolBarHelper::addNew('project.add');
        }
        // Only load if there are items
        if (FestivalHelper::checkArray($this->items)) {
            if ($this->canEdit) {
                \JToolBarHelper::editList('project.edit');
            }
            if ($this->canState) {
                \JToolBarHelper::publishList('projecs.publish');
                \JToolBarHelper::unpublishList('projecs.unpublish');
                \JToolBarHelper::archiveList('projecs.archive');
                if ($this->canDo->get('core.admin')) {
                    \JToolBarHelper::checkin('projecs.checkin');
                }
            }
            // Add a batch button
            if ($this->canBatch && $this->canCreate && $this->canEdit && $this->canState) {
                // Get the toolbar object instance
                $bar = Toolbar::getInstance('toolbar');
                // set the batch button name
                $title = Text::_('JTOOLBAR_BATCH');
                // Instantiate a new JLayoutFile instance and render the batch button
                $layout = new FileLayout('joomla.toolbar.batch');
                // add the button to the page
                $dhtml = $layout->render(['title' => $title]);
                $bar->appendButton('Custom', $dhtml, 'batch');
            }
            if ($this->state->get('filter.published') == -2 && ($this->canState && $this->canDelete)) {
                ToolbarHelper::deleteList('', 'projecs.delete', 'JTOOLBAR_EMPTY_TRASH');
            } elseif ($this->canState && $this->canDelete) {
                ToolbarHelper::trash('projecs.trash');
            }
            if ($this->canDo->get('core.export') && $this->canDo->get('project.export')) {
                \JToolBarHelper::custom('projecs.exportData', 'download', '', 'COM_FESTIVAL_EXPORT_DATA', \true);
            }
        }
        if ($this->canDo->get('core.import') && $this->canDo->get('project.import')) {
            \JToolBarHelper::custom('projecs.importData', 'upload', '', 'COM_FESTIVAL_IMPORT_DATA', \false);
        }
        // set help url for this view if found
        $this->help_url = FestivalHelper::getHelpUrl('projecs');
        if (FestivalHelper::checkString($this->help_url)) {
            ToolbarHelper::help('COM_FESTIVAL_HELP_MANAGER', \false, $this->help_url);
        }
        // add the options comp button
        if ($this->canDo->get('core.admin') || $this->canDo->get('core.options')) {
            \JToolBarHelper::preferences('com_festival');
        }
        // Only load published batch if state and batch is allowed
        if ($this->canState && $this->canBatch) {
            Batch_::addListSelection(Text::_('COM_FESTIVAL_KEEP_ORIGINAL_STATE'), 'batch[published]', HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions', ['all' => \false]), 'value', 'text', '', \true));
        }
        // Only load access batch if create, edit and batch is allowed
        if ($this->canBatch && $this->canCreate && $this->canEdit) {
            Batch_::addListSelection(Text::_('COM_FESTIVAL_KEEP_ORIGINAL_ACCESS'), 'batch[access]', HTMLHelper::_('select.options', HTMLHelper::_('access.assetgroups'), 'value', 'text'));
        }
        // Only load Project Title batch if create, edit, and batch is allowed
        if ($this->canBatch && $this->canCreate && $this->canEdit) {
            // Set Project Title Selection
            $this->project_titleOptions = FormHelper::loadFieldType('projecsfilterprojecttitle')->options;
            // We do some sanitation for Project Title filter
            if (FestivalHelper::checkArray($this->project_titleOptions) && isset($this->project_titleOptions[0]->value) && !FestivalHelper::checkString($this->project_titleOptions[0]->value)) {
                unset($this->project_titleOptions[0]);
            }
            // Project Title Batch Selection
            Batch_::addListSelection('- Keep Original ' . Text::_('COM_FESTIVAL_PROJECT_PROJECT_TITLE_LABEL') . ' -', 'batch[project_title]', HTMLHelper::_('select.options', $this->project_titleOptions, 'value', 'text'));
        }
    }
    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        if (!isset($this->document)) {
            $this->document = Factory::getDocument();
        }
        $this->document->setTitle(Text::_('COM_FESTIVAL_PROJECS'));
        $this->document->addStyleSheet(\JURI::root() . "administrator/components/com_festival/assets/css/projecs.css", FestivalHelper::jVersion()->isCompatible('3.8.0') ? ['version' => 'auto'] : 'text/css');
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
        if (\strlen($var) > 50) {
            // use the helper htmlEscape method instead and shorten the string
            return FestivalHelper::htmlEscape($var, $this->_charset, \true);
        }
        // use the helper htmlEscape method instead.
        return FestivalHelper::htmlEscape($var, $this->_charset);
    }
    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return ['a.ordering' => Text::_('JGRID_HEADING_ORDERING'), 'a.published' => Text::_('JSTATUS'), 'a.project_title' => Text::_('COM_FESTIVAL_PROJECT_PROJECT_TITLE_LABEL'), 'a.id' => Text::_('JGRID_HEADING_ID')];
    }
}
