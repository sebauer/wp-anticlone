<?php
/**
 * Plugin Name: WordPress Anti-Clone
 * Plugin URI: https://github.com/sebauer/wp-anticlone
 * Description: A WordPress plugin to reduce the impact of unauthorized clones of your blog.
 * Version: 0.1.2
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
//add_action( 'wp_head', 'wpac_add_fakecss' );

function wpac_add_fakecss() {
  $pluginUrl = plugins_url();
  echo '<link rel="stylesheet" href="'.$pluginUrl.'/wp-anticlone/wpac.css" />';
}

function wpac_add_js() {
  // Default domains which should not be blacklisted, e.g. translation services
  $defaultDomains = 'translate.googleusercontent.com';

  $domains = get_option('wpac_settings');
  $domains = $domains['wpac_authDomains'];

  // Add default domains
  if($domains == '') {
    $domains = $defaultDomains;
  } else {
    $domains .= ','.$defaultDomains;
  }

  $domains = explode(',', str_replace(' ', '', $domains));
  $pluginsUrl = plugins_url();
  $domainString = '';
  foreach($domains as $domain){
    // Do not add empty domain string (could result from multiple commas)
    if(empty($domain)) continue;
    $domainString .= '\''.base64_encode(str_replace('.www', '', $domain)).'\',';
  }

  $has_matches = uniqid('_');
  $domains = uniqid('_');
  $loc = uniqid('_');
  $newlocation = uniqid('_');
  $content = <<<EOD
  <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=" style="display: none;" onload="window.{$domains}=[{$domainString}];var {$has_matches}=false;var {$loc}=btoa(window.location.host.replace('www.',''));var {$domains}=window.{$domains};for(var i in {$domains}){if({$domains}[i]=={$loc}){{$has_matches}=true;}}if({$has_matches}==false){var {$newlocation}=window.location.href.replace(window.location.host,atob({$domains}[0]));document.body.innerHTML=atob('PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTZweDtjb2xvcjogcmVkO2ZvbnQtd2VpZ2h0OiBib2xkOyI+RGllc2UgU2VpdGUgd3VyZGUgdW5yZWNodG0mYXVtbDsmc3psaWc7aWcgZ2VrbG9udC4gRGllIGVjaHRlIFNlaXRlIGZpbmRlbiBTaWUgdW50ZXIg')+{$newlocation}+atob('LiBXZWl0ZXJlIEluZm9ybWF0aW9uIHp1IGRpZXNlbSBpbGxlZ2FsZW4gVm9yZ2VoZW4gZmluZGVuIFNpZSBiZWlzcGllbHN3ZWlzZSBoaWVyOiA8YSBocmVmPSJodHRwOi8vbmllZGJsb2cuZGUvYmxvZy1rb3BpZXJ0LW5hbWUtZ2VrbGF1dC8iIHJlbD0ibm9mb2xsb3ciIHRhcmdldD0iX2JsYW5rIj5uaWVkYmxvZy5kZTwvYT48L3NwYW4+');{$newlocation}+='?wpac_s='+window.location.host;window.location={$newlocation}}" />
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
