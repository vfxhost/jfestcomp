<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vfxhost 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0
	@build			11th November, 2023
	@created		28th August, 2023
	@package		Festival
	@subpackage		default_vdm.php
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

?>
<img alt="<?php echo JText::_('COM_FESTIVAL'); ?>" src="components/com_festival/assets/images/vdm-component.jpg">
<ul class="list-striped">
	<li><b><?php echo JText::_('COM_FESTIVAL_VERSION'); ?>:</b> <?php echo $this->manifest->version; ?>&nbsp;&nbsp;<span class="update-notice" id="component-update-notice"></span></li>
	<li><b><?php echo JText::_('COM_FESTIVAL_DATE'); ?>:</b> <?php echo $this->manifest->creationDate; ?></li>
	<li><b><?php echo JText::_('COM_FESTIVAL_AUTHOR'); ?>:</b> <a href="mailto:<?php echo $this->manifest->authorEmail; ?>"><?php echo $this->manifest->author; ?></a></li>
	<li><b><?php echo JText::_('COM_FESTIVAL_WEBSITE'); ?>:</b> <a href="<?php echo $this->manifest->authorUrl; ?>" target="_blank"><?php echo $this->manifest->authorUrl; ?></a></li>
	<li><b><?php echo JText::_('COM_FESTIVAL_LICENSE'); ?>:</b> <?php echo $this->manifest->license; ?></li>
	<li><b><?php echo $this->manifest->copyright; ?></b></li>
</ul>
<div class="clearfix"></div>
<?php if(FestivalHelper::checkArray($this->contributors)): ?>
	<?php if(count($this->contributors) > 1): ?>
		<h3><?php echo JText::_('COM_FESTIVAL_CONTRIBUTORS'); ?></h3>
	<?php else: ?>
		<h3><?php echo JText::_('COM_FESTIVAL_CONTRIBUTOR'); ?></h3>
	<?php endif; ?>
	<ul class="list-striped">
		<?php foreach($this->contributors as $contributor): ?>
		<li><b><?php echo $contributor['title']; ?>:</b> <?php echo $contributor['name']; ?></li>
		<?php endforeach; ?>
	</ul>
	<div class="clearfix"></div>
<?php endif; ?>