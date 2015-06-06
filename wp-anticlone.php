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

global $wpac_version;
$wpac_db_version = '1.0';

function wpac_update_db_check() {
	global $wpac_db_version;

	if ( get_site_option( 'wpac_db_version' ) != $wpac_db_version ) {
		wpac_install();
	}
}

function wpac_install() {
	global $wpdb;
	global $wpac_db_version ;


	$table_name = $wpdb->prefix . 'wpac_referrer';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int NOT NULL AUTO_INCREMENT,
		time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
		referrer VARCHAR (255) NOT NULL,
		PRIMARY KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'wpac_db_version', $wpac_db_version );
	add_option( 'wpac_random_param', uniqid() );
}

add_action( 'plugins_loaded', 'wpac_update_db_check' );
add_action( 'wp_footer', 'wpac_add_js' );

function wpac_add_fakecss() {
  $pluginUrl = plugins_url();
  echo '<link rel="stylesheet" href="'.$pluginUrl.'/wp-anticlone/wpac.css" />';
}

function wpac_add_js() {
  $options = get_option('wpac_settings');
  $domains = $options['wpac_authDomains'];
  $message = $options['wpac_message'];
  if($domains == '') {
	  return;
  }
  $domains = explode(',', str_replace(' ', '', $domains));
  $domainString = '';
  foreach($domains as $domain){
    $domainString .= '\''.base64_encode(str_replace('.www', '', $domain)).'\',';
  }

	$span_start = '<span style="font-size: 16px;color: red;font-weight: bold;">';
	$span_end = '</span>';

	if ($message == '') {
		$message_start = base64_encode($span_start . 'Diese Seite wurde unrechtm&auml;&szlig;ig geklont. Die echte Seite finden Sie unter');
		$message_end = base64_decode($span_end);
	} else {
		if ($replace_position = strpos( $message, '%page%' ) ) {
			$message_start = base64_encode($span_start . substr($message, 0, $replace_position));
			$message_end = base64_encode(substr($message, $replace_position + 6) . $span_end);
		} else {
			$message_start = base64_encode( '<span style="font-size: 16px;color: red;font-weight: bold;">' . $message );
			$message_end = base64_decode($span_end);
		}
	}

	$niedblog = '<!-- -->';
	if ($options['wpac_niedblog'] == 1) {
		$niedblog = '<br/>' . $span_start . 'Weitere Information zu diesem illegalen Vorgehen finden Sie beispielsweise hier: <a href="http://niedblog.de/blog-kopiert-name-geklaut/" rel="nofollow" target="_blank">niedblog.de</a>' . $span_end;
	}
	$niedblog =  base64_encode($niedblog);

  $newlocation = uniqid('_');
  $has_matches = uniqid('_');
  $domains = uniqid('_');
  $loc = uniqid('_');
  $random_param = get_option('wpac_random_param', 'wpac_s');
  $content = <<<EOD
  <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=" style="display: none;" onload="window.{$domains}=[{$domainString}];var {$has_matches}=false;var {$loc}=btoa(window.location.host.replace('www.',''));var {$domains}=window.{$domains};for(var i in {$domains}){if({$domains}[i]=={$loc}){{$has_matches}=true;}}if({$has_matches}==false){var {$newlocation}=window.location.href.replace(window.location.host,atob({$domains}[0]));document.body.innerHTML=atob('{$message_start}')+{$newlocation}+atob('{$message_end}')+atob('{$niedblog}');{$newlocation}+='?{$random_param}='+window.location.host;window.location={$newlocation}}" />
EOD;

  echo $content;
}
add_action( 'admin_menu', 'wpac_add_admin_menu' );
add_action( 'admin_init', 'wpac_settings_init' );
add_action( 'plugins_loaded', 'wp_anticlone_frontend_init');

function wpac_add_admin_menu(  ) {

	add_options_page( 'WordPress Anti-Clone', 'WordPress Anti-Clone', 'manage_options', 'wp-anticlone', 'wp_anticlone_options_page' );

}


function wpac_settings_exist(  ) {

	if( false == get_option( 'wp-anticlone_settings' ) ) {

		add_option( 'wp-anticlone_settings' );
		update_option(
			'wp-anticlone_settings',
			array(
				'wpac_message' => 'Diese Seite wurde unrechtm&auml;&szlig;ig geklont. Die echte Seite finden Sie unter',
				'niedblog'     => 1,
			)
		);

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

	add_settings_field(
		'wpac_message',
		__( 'Nachricht die dem Besuchen angezeigt werden soll', 'wordpress' ),
		'wpac_text_field_1_render',
		'pluginPage',
		'wpac_pluginPage_section'
	);

	add_settings_field(
		'wpac_niedblog',
		__( 'Soll ein Link zum Niedblog Artikel gezeigt werden', 'wordpress' ),
		'wpac_checkbox_field_0_render',
		'pluginPage',
		'wpac_pluginPage_section'
	);

	add_settings_section(
		'wpac_pluginPage_stats',
		__( 'Statistiken', 'wordpress' ),
		'wpac_settings_section_stats',
		'pluginPage'
	);

}


function wpac_text_field_0_render(  ) {

	$options = get_option( 'wpac_settings' );
  echo "<input type='text' name='wpac_settings[wpac_authDomains]' value='".$options['wpac_authDomains']."'>";

}


function wpac_text_field_1_render(  ) {

	$options = get_option( 'wpac_settings' );
	echo "<input type='text' name='wpac_settings[wpac_message]' value='".$options['wpac_message']."'><small><strong>%page%<strong> wird durch die originale Seite ersetzt!";

}

function wpac_checkbox_field_0_render() {
	$options = get_option( 'wpac_settings' );
	echo "<input type='checkbox' name='wpac_settings[wpac_niedblog]' value='1'";
	if ($options['wpac_niedblog'] == 1) {
		echo 'checked="checked"';
	}

	echo "/>";
}


function wpac_settings_section_callback(  ) {

	echo __( '', 'wordpress' );

}

function wpac_settings_section_stats() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'wpac_referrer';

	$referrer = $wpdb->get_results('SELECT referrer, count(referrer) as c from ' . $table_name . ' GROUP BY referrer ORDER BY c DESC');

	echo '<ul>';
	foreach ($referrer as $domain) {
		echo '<li><strong>' . $domain->referrer . '</strong> (' . $domain->c . ')</li>';
	}
	echo '</ul>';

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



function wp_anticlone_frontend_init()
{
	$random = get_option( 'wpac_random_param', false);

	if (isset($_GET[$random])) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'wpac_referrer',
			array(
				'referrer' => $_GET[$random],
			)
		);
	}
}