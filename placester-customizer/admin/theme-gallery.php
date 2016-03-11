<?php

global $placester_gallery_themes;


function placester_gallery_install_link($theme) {
	static $installed_themes;
	if(!isset($installed_themes))
		$installed_themes = array_keys(wp_get_themes());

	if(in_array($theme, $installed_themes))
		return '<span class="inactive install_theme">Installed</span>';
	else
		return '<a class="install_theme" href="'
			. admin_url('admin.php?page=placester_gallery&action=install&theme=' . $theme
			. '&_wpnonce=' . wp_create_nonce('placester-gallery-install_' . $theme))
			. '">Install</a>';
}


if($_REQUEST['action'] = 'install-theme' && $_REQUEST['theme'] && in_array($_REQUEST['theme'], array_keys($placester_gallery_themes))) {
	if ( ! current_user_can('install_themes') )
		wp_die( __( 'You do not have sufficient permissions to install themes on this site.' ) );

	include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' ); //for themes_api..

	$theme = $_REQUEST['theme']; $meta = $placester_gallery_themes[$theme];
	check_admin_referer( 'placester-gallery-install_' . $theme );

	$title = sprintf( __('Installing Theme: %s'), $meta['display_name'] );
	$nonce = 'placester-gallery-install_' . $theme;
	$url = 'admin.php?page=placester_gallery&action=install&theme=' . $theme;
	$type = 'web'; //Install theme type, From Web or an Upload.

	$upgrader = new Theme_Upgrader( new Theme_Installer_Skin( compact('title', 'url', 'nonce', 'theme') ) );
	$upgrader->install($meta['download_link']);
}


else { ?>

<table id="available-themes" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<?php foreach ($placester_gallery_themes as $theme => $meta): ?>
				<td class="available-theme top left">
					<h3><?php echo $meta['display_name']; ?></h3>
					<?php echo placester_gallery_install_link($theme); ?>
					<span>&nbsp;|&nbsp;</span>
					<a class="download_theme" target="_blank" href="<?php echo $meta['download_link']; ?>">Download</a>
					<span>&nbsp;|&nbsp;</span>
					<a id="theme_info_link" target="_blank" href="<?php echo $meta['info_link']; ?>">Details</a>
				</td>
			<?php endforeach; ?>
		</tr>
	</tbody>
</table>

<br class="clear">

<?php }
