<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		script.php
	@author			Kyriakos Liarakos <https://www/vfxhost.gr>	
	@copyright		Copyright (C) 2023. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
JHTML::_('bootstrap.renderModal');

/**
 * Script File of Festival Component
 */
class com_festivalInstallerScript
{
	/**
  * Constructor
  *
  * @param \Joomla\CMS\Adapter\AdapterInstance $parent The object responsible for running this script
  */
 public function __construct(ComponentAdapter $parent) {}

	/**
	 * Called on installation
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(ComponentAdapter $parent) {}

	/**
	 * Called on uninstallation
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 */
	public function uninstall(ComponentAdapter $parent)
	{
		// Get Application object
		$app = Factory::getApplication();

		// Get The Database object
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Project alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_festival.project') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$project_found = $db->getNumRows();
		// Now check if there were any rows
		if ($project_found)
		{
			// Since there are load the needed  project type ids
			$project_ids = $db->loadColumn();
			// Remove Project from the content type table
			$project_condition = [$db->quoteName('type_alias') . ' = '. $db->quote('com_festival.project')];
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($project_condition);
			$db->setQuery($query);
			// Execute the query to remove Project items
			$project_done = $db->execute();
			if ($project_done)
			{
				// If successfully remove Project add queued success message.
				$app->enqueueMessage(Text::_('The (com_festival.project) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Project items from the contentitem tag map table
			$project_condition = [$db->quoteName('type_alias') . ' = '. $db->quote('com_festival.project')];
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($project_condition);
			$db->setQuery($query);
			// Execute the query to remove Project items
			$project_done = $db->execute();
			if ($project_done)
			{
				// If successfully remove Project add queued success message.
				$app->enqueueMessage(Text::_('The (com_festival.project) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Project items from the ucm content table
			$project_condition = [$db->quoteName('core_type_alias') . ' = ' . $db->quote('com_festival.project')];
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($project_condition);
			$db->setQuery($query);
			// Execute the query to remove Project items
			$project_done = $db->execute();
			if ($project_done)
			{
				// If successfully removed Project add queued success message.
				$app->enqueueMessage(Text::_('The (com_festival.project) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Project items are cleared from DB
			foreach ($project_ids as $project_id)
			{
				// Remove Project items from the ucm base table
				$project_condition = [$db->quoteName('ucm_type_id') . ' = ' . $project_id];
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($project_condition);
				$db->setQuery($query);
				// Execute the query to remove Project items
				$db->execute();

				// Remove Project items from the ucm history table
				$project_condition = [$db->quoteName('ucm_type_id') . ' = ' . $project_id];
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($project_condition);
				$db->setQuery($query);
				// Execute the query to remove Project items
				$db->execute();
			}
		}

		// If All related items was removed queued success message.
		$app->enqueueMessage(Text::_('All related items was removed from the <b>#__ucm_base</b> table'));
		$app->enqueueMessage(Text::_('All related items was removed from the <b>#__ucm_history</b> table'));

		// Remove festival assets from the assets table
		$festival_condition = [$db->quoteName('name') . ' LIKE ' . $db->quote('com_festival%')];

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__assets'));
		$query->where($festival_condition);
		$db->setQuery($query);
		$project_done = $db->execute();
		if ($project_done)
		{
			// If successfully removed festival add queued success message.
			$app->enqueueMessage(Text::_('All related items was removed from the <b>#__assets</b> table'));
		}

		// little notice as after service, in case of bad experience with component.
		echo '<h2>Did something go wrong? Are you disappointed?</h2>
		<p>Please let me know at <a href="mailto:liarakos@vfxhost.gr">liarakos@vfxhost.gr</a>.
		<br />We at Vfxhost are committed to building extensions that performs proficiently! You can help us, really!
		<br />Send me your thoughts on improvements that is needed, trust me, I will be very grateful!
		<br />Visit us at <a href="https://www/vfxhost.gr" target="_blank">https://www/vfxhost.gr</a> today!</p>';
	}

	/**
	 * Called on update
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(ComponentAdapter $parent){}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, ComponentAdapter $parent)
	{
		// get application
		$app = Factory::getApplication();
		// is redundant or so it seems ...hmmm let me know if it works again
		if ($type === 'uninstall')
		{
			return true;
		}
		// the default for both install and update
		$jversion = new Version();
		if (!$jversion->isCompatible('3.8.0'))
		{
			$app->enqueueMessage('Please upgrade to at least Joomla! 3.8.0 before continuing!', 'error');
			return false;
		}
		// do any updates needed
		if ($type === 'update')
		{
		}
		// do any install needed
		if ($type === 'install')
		{
		}
		// check if the PHPExcel stuff is still around
		if (File::exists(JPATH_ADMINISTRATOR . '/components/com_festival/helpers/PHPExcel.php'))
		{
			// We need to remove this old PHPExcel folder
			$this->removeFolder(JPATH_ADMINISTRATOR . '/components/com_festival/helpers/PHPExcel');
			// We need to remove this old PHPExcel file
			File::delete(JPATH_ADMINISTRATOR . '/components/com_festival/helpers/PHPExcel.php');
		}
		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, ComponentAdapter $parent)
	{
		// get application
		$app = Factory::getApplication();
		// We check if we have dynamic folders to copy
		$this->setDynamicF0ld3rs($app, $parent);
		// set the default component settings
		if ($type === 'install')
		{

			// Get The Database object
			$db = Factory::getDbo();

			// Create the project content type object.
			$project = new stdClass();
			$project->type_title = 'Festival Project';
			$project->type_alias = 'com_festival.project';
			$project->table = '{"special": {"dbtable": "#__festival_project","key": "id","type": "Project","prefix": "festivalTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$project->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "project_title","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"project_title":"project_title","alias":"alias","synopsis_original_language":"synopsis_original_language","synopsis":"synopsis","project_title_original_language":"project_title_original_language"}}';
			$project->router = 'FestivalHelperRoute::getProjectRoute';
			$project->content_history_options = '{"formFile": "administrator/components/com_festival/models/forms/project.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"}]}';

			// Set the object into the content types table.
			$project_Inserted = $db->insertObject('#__content_types', $project);


			// Install the global extension params.
			$query = $db->getQuery(true);
			// Field to update.
			$fields = [$db->quoteName('params') . ' = ' . $db->quote('{"autorName":"Kyriakos Liarakos","autorEmail":"liarakos@vfxhost.gr","check_in":"-1 day","save_history":"1","history_limit":"10"}')];
			// Condition.
			$conditions = [$db->quoteName('element') . ' = ' . $db->quote('com_festival')];
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$allDone = $db->execute();

			echo '<a target="_blank" href="https://www/vfxhost.gr" title="Festival">
				<img src="components/com_festival/assets/images/vdm-component.jpg"/>
				</a>';
		}
		// do any updates needed
		if ($type === 'update')
		{

			// Get The Database object
			$db = Factory::getDbo();

			// Create the project content type object.
			$project = new stdClass();
			$project->type_title = 'Festival Project';
			$project->type_alias = 'com_festival.project';
			$project->table = '{"special": {"dbtable": "#__festival_project","key": "id","type": "Project","prefix": "festivalTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$project->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "project_title","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"project_title":"project_title","alias":"alias","synopsis_original_language":"synopsis_original_language","synopsis":"synopsis","project_title_original_language":"project_title_original_language"}}';
			$project->router = 'FestivalHelperRoute::getProjectRoute';
			$project->content_history_options = '{"formFile": "administrator/components/com_festival/models/forms/project.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"}]}';

			// Check if project type is already in content_type DB.
			$project_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(['type_id']));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($project->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$project->type_id = $db->loadResult();
				$project_Updated = $db->updateObject('#__content_types', $project, 'type_id');
			}
			else
			{
				$project_Inserted = $db->insertObject('#__content_types', $project);
			}


			echo '<a target="_blank" href="https://www/vfxhost.gr" title="Festival">
				<img src="components/com_festival/assets/images/vdm-component.jpg"/>
				</a>
				<h3>Upgrade to Version 1.0.0 Was Successful! Let us know if anything is not working as expected.</h3>';
		}
		return true;
	}

	/**
	 * Remove folders with files
	 * 
	 * @param   string   $dir     The path to folder to remove
	 * @param   boolean  $ignore  The folders and files to ignore and not remove
	 *
	 * @return  boolean   True in all is removed
	 * 
	 */
	protected function removeFolder($dir, $ignore = false)
	{
		if (Folder::exists($dir))
		{
			$it = new RecursiveDirectoryIterator($dir);
			$it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			// remove ending /
			$dir = rtrim($dir, '/');
			// now loop the files & folders
			foreach ($it as $file)
			{
				if ('.' === $file->getBasename()) {
        continue;
    }
    if ('..' ===  $file->getBasename()) {
        continue;
    }
    // set file dir
				$file_dir = $file->getPathname();
				// check if this is a dir or a file
				if ($file->isDir())
				{
					$keeper = false;
					if ($this->checkArray($ignore))
					{
						foreach ($ignore as $keep)
						{
							if (strpos($file_dir, $dir.'/'.$keep) !== false)
							{
								$keeper = true;
							}
						}
					}
					if ($keeper)
					{
						continue;
					}
					Folder::delete($file_dir);
				}
				else
				{
					$keeper = false;
					if ($this->checkArray($ignore))
					{
						foreach ($ignore as $keep)
						{
							if (strpos($file_dir, $dir.'/'.$keep) !== false)
							{
								$keeper = true;
							}
						}
					}
					if ($keeper)
					{
						continue;
					}
					File::delete($file_dir);
				}
			}
			// delete the root folder if not ignore found
			if (!$this->checkArray($ignore))
			{
				return Folder::delete($dir);
			}
			return true;
		}
		return false;
	}

	/**
	 * Check if have an array with a length
	 *
	 * @input	array   The array to check
	 *
	 * @returns bool/int  number of items in array on success
	 */
	protected function checkArray($array, $removeEmptyString = false)
	{
		if (isset($array) && is_array($array) && ($nr = count((array)$array)) > 0)
		{
			// also make sure the empty strings are removed
			if ($removeEmptyString)
			{
				foreach ($array as $key => $string)
				{
					if (empty($string))
					{
						unset($array[$key]);
					}
				}
				return $this->checkArray($array, false);
			}
			return $nr;
		}
		return false;
	}

	/**
	 * Method to set/copy dynamic folders into place (use with caution)
	 *
	 * @return void
	 */
	protected function setDynamicF0ld3rs($app, $parent)
	{
		// get the instalation path
		$installer = $parent->getParent();
		$installPath = $installer->getPath('source');
		// get all the folders
		$folders = Folder::folders($installPath);
		// check if we have folders we may want to copy
		$doNotCopy = ['media', 'admin', 'site']; // Joomla already deals with these
		if (count((array) $folders) > 1)
		{
			foreach ($folders as $folder)
			{
				// Only copy if not a standard folders
				if (!in_array($folder, $doNotCopy))
				{
					// set the source path
					$src = $installPath.'/'.$folder;
					// set the destination path
					$dest = JPATH_ROOT.'/'.$folder;
					// now try to copy the folder
					if (!Folder::copy($src, $dest, '', true))
					{
						$app->enqueueMessage('Could not copy '.$folder.' folder into place, please make sure destination is writable!', 'error');
					}
				}
			}
		}
	}
}
