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
add_action( 'wp_head', 'wpac_add_js' );

function wpac_add_js($content) {
  $domains = get_option('wp-anticlone_settings');
  $domains = $domains['wpac_authDomains'];
  if($domains == '') return;
  $domains = explode(',', str_replace(' ', '', $domains));
  $content .= <<<EOD
    <script type="text/javascript">
      var wpac_domains = [
EOD;
  foreach($domains as $domain){
    $content .= '\''.base64_encode(str_replace('.www', '', $domain)).'\',';
  }
  $content .= <<<EOD
      ];
      var wpac_hasMatches = false;
      var wpac_loc = btoa(window.location.host.replace('www.', ''));
      for(var i in wpac_domains) {
        if(wpac_domains[i] == wpac_loc) {
          wpac_hasMatches = true;
        }
      }
      window.onload = function() {
        if(wpac_hasMatches == false) {
          var wpac_newlocation = window.location.href.replace(window.location.host, atob(wpac_domains[0]));
          document.body.innerHTML = '<span style="font-size: 16px;color: red;font-weight: bold;">Diese Seite wurde unrechtm&auml;&szlig;ig geklont. Die echte Seite finden Sie unter '+wpac_newlocation+'. Weitere Information zu diesem illegalen Vorgehen finden Sie beispielsweise hier: <a href="http://niedblog.de/blog-kopiert-name-geklaut/" target="_blank">niedblog.de</a></span>';
          window.location = wpac_newlocation;
        }
      }
    </script>
EOD;
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
		__( 'Liste von authorisierten Domains, kommasepariert (z.B. foobar.de,foobar.info )', 'wordpress' ),
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
