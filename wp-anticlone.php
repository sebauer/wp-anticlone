<?php
/**
 * Plugin Name: WordPress Anti-Clone
 * Plugin URI: https://github.com/sebauer/wp-anticlone
 * Description: A WordPress plugin to reduce the impact of unauthorized clones of your blog.
 * Version: 0.1.0
 * Author: Sebastian Bauer
 * Author URI: https://github.com/sebauer
 * License: GPL2
 */
/*  Copyright 2014  Sebastian Bauer  (email : gjlnetwork@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
add_action( 'wp_footer', 'wpac_add_js' );
add_action( 'wp_head', 'wpac_add_fakecss' );

function wpac_add_fakecss() {
  $pluginUrl = plugins_url();
  echo '<link rel="stylesheet" href="'.$pluginUrl.'/wp-anticlone/wpac.css" />';
}

function wpac_add_js() {
  $domains = get_option('wpac_settings');
  $domains = $domains['wpac_authDomains'];
  if($domains == '') return;
  $domains = explode(',', str_replace(' ', '', $domains));
  $pluginsUrl = plugins_url();
  $domainString = '';
  foreach($domains as $domain){
    $domainString .= '\''.base64_encode(str_replace('.www', '', $domain)).'\',';
  }
  $content = <<<EOD
  <img src="{$pluginsUrl}/wp-anticlone/wpac.png" style="display: none;" onload="window.wpac_domains=[{$domainString}];var inj=document.createElement(atob('c2NyaXB0'));inj.src='{$pluginsUrl}/wp-anticlone/wpac.css';document.getElementsByTagName('head')[0].appendChild(inj);"/>
EOD;
  echo $content;
}
add_action( 'admin_menu', 'wpac_add_admin_menu' );
add_action( 'admin_init', 'wpac_settings_init' );


function wpac_add_admin_menu(  ) {

	add_options_page( 'WordPress Anti-Clone', 'WordPress Anti-Clone', 'manage_options', 'wp-anticlone', 'wp_anticlone_options_page' );

}


function wpac_settings_exist(  ) {

	if( false == get_option( 'wp-anticlone_settings' ) ) {

		add_option( 'wp-anticlone_settings' );

	}

}


function wpac_settings_init(  ) {

	register_setting( 'pluginPage', 'wpac_settings' );

	add_settings_section(
		'wpac_pluginPage_section',
		__( 'Einstellungen', 'wordpress' ),
		'wpac_settings_section_callback',
		'pluginPage'
	);

	add_settings_field(
		'wpac_authDomains',
		__( 'Liste von autorisierten Domains, kommasepariert (z.B. foobar.de,foobar.info )', 'wordpress' ),
		'wpac_text_field_0_render',
		'pluginPage',
		'wpac_pluginPage_section'
	);


}


function wpac_text_field_0_render(  ) {

	$options = get_option( 'wpac_settings' );
  echo "<input type='text' name='wpac_settings[wpac_authDomains]' value='".$options['wpac_authDomains']."'>";

}


function wpac_settings_section_callback(  ) {

	echo __( '', 'wordpress' );

}


function wp_anticlone_options_page(  ) {

	?>
	<form action='options.php' method='post'>

		<h2>WordPress Anti-Clone</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}
