<?php
/**
 * Joomla! Install From Web Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$extension_data = $displayData['extension'];
$breadcrumbs    = $displayData['breadcrumbs'];
$tags           = $extension_data->includes->value;
$commercial     = $extension_data->type->value != "free" ? true : false;
?>
<div class="item-view">
	<div class="grid-header">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a class="transcode" href="<?php echo AppsHelper::getAJAXUrl(['view' => 'dashboard']); ?>"><?php echo Text::_('COM_APPS_EXTENSIONS'); ?></a>
				</li>
				<?php foreach ($breadcrumbs as $bc) : ?>
					<li class="breadcrumb-item"><a class="transcode" href="<?php echo AppsHelper::getAJAXUrl(['view' => 'category', 'id' => $bc->id]); ?>"><?php echo $bc->name; ?></a></li>
				<?php endforeach; ?>
				<li class="breadcrumb-item active" aria-current="page"><?php echo $extension_data->core_title->value; ?></li>
			</ol>
		</nav>
	</div>
	<div>
		<h2>
			<?php echo trim($extension_data->core_title->value); ?>
			<?php if ($extension_data->popular->value == 1): ?>
				 <span class="badge badge-primary"><?php echo Text::_('COM_APPS_POPULAR_TEXT'); ?></span>
			<?php endif; ?>
		</h2>
	</div>
	<div class="row">
		<div class="col-md-4 col-lg-3">
			<img class="img-fluid" src="<?php echo $extension_data->image; ?>">
		</div>
		<div class="col-md-8 col-lg-9">
			<a target="_blank" href="<?php echo AppsHelper::getJEDUrl($extension_data) . '#reviews'; ?>"><?php echo strip_tags(Text::sprintf('COM_APPS_EXTENSION_VOTES_REVIEWS_LIST', $extension_data->score->value, $extension_data->num_reviews->value)); ?></a>
			<dl>
				<dt><?php echo Text::_('COM_APPS_EXTENSION_VERSION'); ?></dt>
				<dd>
					<?php echo $extension_data->version->value; ?>
					<?php if ($extension_data->core_modified_time->value != '0000-00-00 00:00:00') : ?>
						<?php echo ' ' . Text::sprintf('COM_APPS_EXTENSION_LAST_UPDATE', HTMLHelper::_('date', $extension_data->core_modified_time->value)); ?>
					<?php endif; ?>
				</dd>
				<?php if ($extension_data->license->value): ?>
					<dt><?php echo Text::_('COM_APPS_EXTENSION_LICENSE', $extension_data->license->text); ?></dt>
					<dd><?php echo $extension_data->license->text; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $extension_data->type->text; ?></dd>
				<?php endif; ?>
				<dt><?php echo Text::_('COM_APPS_EXTENSION_ADDEDON'); ?></dt>
				<dd>
					<?php echo HTMLHelper::_('date', $extension_data->core_created_time->value); ?>
				</dd>
			</dl>
			<div class="item-badge-container">
				<?php if ($commercial) : ?>
					<span title="<?php echo $extension_data->type->value; ?>" class="badge badge-warning"><?php echo Text::_('COM_APPS_COMMERCIAL'); ?></span>
				<?php endif; ?>
				<?php if (in_array('com', $tags)) : ?>
					<span title="<?php echo Text::_('COM_APPS_COMPONENT'); ?>" class="badge badge-success"><?php echo Text::_('COM_APPS_COMPONENT'); ?></span>
				<?php endif; ?>
				<?php if (in_array('lang', $tags)) : ?>
					<span title="<?php echo Text::_('COM_APPS_LANGUAGE'); ?>" class="badge badge-dark"><?php echo Text::_('COM_APPS_LANGUAGE'); ?></span>
				<?php endif; ?>
				<?php if (in_array('mod', $tags)) : ?>
					<span title="<?php echo Text::_('COM_APPS_MODULE'); ?>" class="badge badge-danger"><?php echo Text::_('COM_APPS_MODULE'); ?></span>
				<?php endif; ?>
				<?php if (in_array('plugin', $tags)) : ?>
					<span title="<?php echo Text::_('COM_APPS_PLUGIN'); ?>" class="badge badge-secondary"><?php echo Text::_('COM_APPS_PLUGIN'); ?></span>
				<?php endif; ?>
				<?php if (in_array('esp', $tags)) : ?>
					<span title="<?php echo Text::_('COM_APPS_EXTENSION_SPECIFIC_ADDON'); ?>" class="badge badge-primary"><?php echo Text::_('COM_APPS_EXTENSION_SPECIFIC_ADDON'); ?></span>
				<?php endif; ?>
				<?php if (in_array('tool', $tags)) : ?>
					<span title="<?php echo Text::_('COM_APPS_TOOL'); ?>" class="badge badge-light"><?php echo Text::_('COM_APPS_TOOL'); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<?php if ($extension_data->download_type > 1): ?>
				<input id="joomlaapsinstallatinput" type="hidden" name="installat" value=""/>
				<input id="joomlaapsinstallfrominput" type="hidden" name="installfrom" value="<?php echo $extension_data->downloadurl; ?>"/>
				<input type="hidden" name="installapp" value="<?php echo $extension_data->link_id ?? null; ?>"/>
			<?php endif; ?>
			<div class="card bg-light mb-3">
				<div class="card-body">
					<?php if ($extension_data->downloadurl && is_numeric($extension_data->download_type)): ?>
						<?php if ($extension_data->download_type == 0): ?>
							<a target="_blank" class="transcode install btn btn-success" href="<?php echo $extension_data->downloadurl; ?>"><span class="icon-download" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_INSTALL_DOWNLOAD_EXTERNAL') . "&hellip;"; ?></a>
						<?php elseif ($extension_data->download_type == 1): ?>
							<a class="install btn btn-success" href="#" onclick="return Joomla.installfromweb('<?php echo $extension_data->downloadurl; ?>', '<?php echo $extension_data->core_title->value; ?>')"><span class="icon-checkmark" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_INSTALL') . "&hellip;"; ?></a>
						<?php elseif ($extension_data->download_type == 2): ?>
							<button class="install btn btn-success" id="appssubmitbutton" onclick="return Joomla.installfromwebexternal('<?php echo $extension_data->downloadurl; ?>');" type="submit"><span class="icon-pencil" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_INSTALL_REGISTER') . "&hellip;"; ?></button>
						<?php elseif ($extension_data->download_type == 3): ?>
							<button class="install btn btn-success" id="appssubmitbutton" onclick="return Joomla.installfromwebexternal('<?php echo $extension_data->downloadurl; ?>');" type="submit"><span class="icon-cart" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_INSTALL_PURCHASE') . "&hellip;"; ?></button>
						<?php endif; ?>&nbsp;&nbsp;&nbsp;
					<?php elseif ($extension_data->download_link->value) : ?>
						<?php if ((is_numeric($extension_data->download_type) && $extension_data->download_type == 0) || $extension_data->download_type == 1 || (strtolower($extension_data->download_type->value) == "free" && !$extension_data->requires_registration->value)): ?>
							<a target="_blank" class="transcode install btn btn-success" href="<?php echo $extension_data->download_link->value; ?>"><span class="icon-download" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_INSTALL_DOWNLOAD_EXTERNAL') . "&hellip;"; ?></a>
						<?php elseif ($extension_data->download_type == 2 || (strtolower($extension_data->download_type->value) == "free" && $extension_data->requires_registration->value)): ?>
							<a target="_blank" class="install btn btn-success" href="<?php echo $extension_data->download_link->value; ?>"><span class="icon-pencil" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_INSTALL_REGISTER_DOWNLOAD_EXTERNAL') . "&hellip;"; ?></a>
						<?php elseif ($extension_data->download_type == 3 || (strtolower($extension_data->download_type->value) != "free")): ?>
							<a target="_blank" class="install btn btn-success" href="<?php echo $extension_data->download_link->value; ?>"><span class="icon-cart" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_INSTALL_PURCHASE_EXTERNAL') . "&hellip;"; ?></a>
						<?php endif; ?>&nbsp;&nbsp;&nbsp;
					<?php endif; ?>
					<a target="_blank" class="btn btn-primary" href="<?php echo AppsHelper::getJEDUrl($extension_data); ?>"><span class="icon-list" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_DIRECTORY_LISTING'); ?></a>
					<?php if ($extension_data->homepage_link->value) : ?>
						&nbsp;&nbsp;&nbsp;<a target="_blank" class="btn btn-primary" href="<?php echo $extension_data->homepage_link->text; ?>"><span class="icon-share-alt" aria-hidden="true"></span> <?php echo Text::_('COM_APPS_DEVELOPER_WEBSITE'); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<h4>
				<?php echo $extension_data->core_title->text; ?>
				<?php if ($extension_data->core_created_user_id->value): ?>
					 <small><?php echo Text::sprintf('COM_APPS_EXTENSION_AUTHOR', $extension_data->core_created_user_id->text); ?></small>
				<?php endif; ?>
			</h4>
			<div>
				<?php echo nl2br($extension_data->core_body->text); ?>
			</div>
		</div>
	</div>
</div>
