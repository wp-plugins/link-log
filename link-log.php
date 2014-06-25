<?php
/*
Plugin Name: link-log
Plugin URI: http://smartware.cc/wp-link-log
Description: Log external link clicks
Version: 1.1
Author: smartware.cc
Author URI: http://smartware.cc
License: GPL2
*/

/*  Copyright 2014  smartware.cc  (email : sw@smartware.cc)

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

// set version
define( 'SWCC_LINKLOG_VERSION', '1.1' );

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
  require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

// parse content and rewrite all external urls
function swcc_linklog_parse_content( $content ) {
  return preg_replace_callback( "/<a(\s[^>]*)href=[\"\']??([^\" >]*?)[\"\']??([^>]*)>(.*)<\/a>/siU", 'swcc_linklog_change_link', $content );
}

// callback function to change the link
function swcc_linklog_change_link( $linkparts ) {
  return '<a' . $linkparts[1].' href="' . swcc_linklog_make_url( $linkparts[2] ) . '"' . $linkparts[3]  . '>' . $linkparts[4] . '</a>'; 
}

// make the url
function swcc_linklog_make_url ( $url ) {
  $url = str_replace( '&#038;', '&', str_replace( '&amp;', '&', $url ) );
  if ( ( substr( strtolower( $url ), 0, 7 ) == 'http://' || substr( strtolower( $url ), 0, 8 ) == 'https://' ) &&  substr( strtolower( $url ), 0, strlen( home_url() ) ) != strtolower( home_url() ) ) {
    $url = home_url() . '?' . swcc_linklog_get_parametername() . '=' . urlencode( $url );
  }
  return $url;
}

// add given url parameter to query vars
function swcc_linklog_queryvar ($qvars) {
  $qvars[] = swcc_linklog_get_parametername();
  return $qvars;
}

// log and redirect
function swcc_linklog_redirect( $wp ) {
  if ( empty( $wp->request ) ) {
    // we are on the front page
    $urlparam = swcc_linklog_get_parametername();
    if ( array_key_exists ( $urlparam, $wp->query_vars ) ) {
      // goto key exitst
      global $wpdb;
      $url = str_replace ( ' ', '+', urldecode( $wp->query_vars[$urlparam] ) );
      wp_redirect( $url );
      $iplock = swcc_linklog_get_iplock();
      $ip = get_client_ip();
      $url = esc_sql( $url );
      $insert = true;
      if ( $iplock != 0 && $ip != '' ) {
        $test = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'linklog WHERE linklog_url = "' . $url . '" AND linklog_ip = "' . $ip . '" AND linklog_clicked >=  DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -' . $iplock . ' SECOND)' );
        if ( ! is_null($wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'linklog WHERE linklog_url = "' . $url . '" AND linklog_ip = "' . $ip . '" AND linklog_clicked >=  DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -' . $iplock . ' SECOND)' ) ) ) {
          $insert = false;
        }
      }
      if ( $insert ) {
        $wpdb->query( 'INSERT INTO ' . $wpdb->prefix . 'linklog ( linklog_url, linklog_ip ) VALUES ( "' .  $url . '", "' . $ip . '" )' ) ;
      }
      exit;
    }
  }
}

// get ip address
function get_client_ip() {
  if ($_SERVER['HTTP_CLIENT_IP']) {
    $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
  } elseif($_SERVER['HTTP_X_FORWARDED_FOR']) {
    $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } elseif($_SERVER['HTTP_X_FORWARDED']) {
    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
  } elseif($_SERVER['HTTP_FORWARDED_FOR']) {
    $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
  } elseif($_SERVER['HTTP_FORWARDED']) {
    $ipaddress = $_SERVER['HTTP_FORWARDED'];
  } elseif($_SERVER['REMOTE_ADDR']) {
    $ipaddress = $_SERVER['REMOTE_ADDR'];
  } else {
    $ipaddress = '';
  }
  return $ipaddress;
}

// show admin page log
function swcc_linklog_admin_log() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
  ?>
  <div class="wrap">
    <div id="icon-tools" class="icon32"></div>
    <h2>link-log Link Click Statistic</h2>
    <h3 class="title">the link-log plugin</h3>
    <ul class='subsubsub'>
      <li><a href="#">Please rate the plugin</a> |</li>
      <li><a href="#">Plugin homepage |</a></li>
      <li><a href="#">Author homepage</a></li>
    </ul>
    <div class="clear"></div>
    <h3 class="title">click statistics</h3>					
    <table class="widefat fixed" cellspacing="0" id="linklog-log">
      <thead>
        <tr>
          <th class="manage-column column-columnname max" scope="col">Link</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />this<br />month</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />last<br />month</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />this<br />year</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />last<br />year</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />total</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th class="manage-column column-columnname max" scope="col">Link</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />this<br />month</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />last<br />month</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />this<br />year</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />last<br />year</th>
          <th class="manage-column column-columnname num min" scope="col">Clicks<br />total</th>
        </tr>
      </tfoot>
      <tbody>
        <?php
          global $wpdb;
          $y = date( 'Y' );
          $y1 = $y - 1;
          $m = date( n );
          $m1 = $m - 1;
          if ( $m1 == 0 ) {
            $m1 = 12;
            $m1y = $y1;
          } else {
            $m1y = $y;
          }
          $logentries = ($wpdb->get_results( 'SELECT linklog_url, SUM( IF( MONTH( linklog_clicked ) = ' . $m . ' AND YEAR( linklog_clicked ) = ' . $y . ', 1, 0 ) ) AS month_cur, SUM( IF( MONTH( linklog_clicked ) = ' . $m1 . ' AND YEAR( linklog_clicked ) = ' . $m1y . ', 1, 0 ) ) AS month_last, SUM( IF( YEAR( linklog_clicked ) = ' . $y . ', 1, 0 ) ) AS year_cur, SUM( IF( YEAR( linklog_clicked ) = ' . $y1 . ', 1, 0 ) ) AS year_last, count(*) AS total FROM ' . $wpdb->prefix . 'linklog GROUP BY linklog_url' ) );
          foreach( $logentries as $logentry) {
            echo '<tr>';
            echo '<td class="column-columnname max"><a href="' . $logentry->linklog_url . '" title="' . $logentry->linklog_url . '">' . $logentry->linklog_url . '</a></td>';
            echo '<td class="column-columnname num min">' .$logentry->month_cur . '</td>';
            echo '<td class="column-columnname num min">' .$logentry->month_last . '</td>';
            echo '<td class="column-columnname num min">' .$logentry->year_cur . '</td>';
            echo '<td class="column-columnname num min">' .$logentry->year_last . '</td>';
            echo '<td class="column-columnname num min">' .$logentry->total . '</td>';
            echo '</tr>';
          }
        ?>
      </tbody>
    </table>
  </div>
  <?php
}

// show settings in admin / settings / link-log
function swcc_linklog_admin_settings() {
  echo '<div class="wrap">';
  screen_icon();
  echo '<h2>link-log Settings</h2><form method="post" action="options.php">';
  settings_fields( 'swcc_linklog' ); 
  do_settings_sections( 'link-log-settings' );
  submit_button(); 
  echo '</form></div>';
}

// sttings group : url 
function swcc_linklog_admin_settings_url() {
  echo '<p>Specify the parameter name to use in the generated URL for logging the link clicks (default "goto").<br />Example: <code>' . home_url() . '?<strong>goto</strong>=http://wordpress.org</code></p>';
}

// sttings group : iplock 
function swcc_linklog_admin_settings_iplock() {
  echo '<p>This feature avoids counting of multiple click.</p>';
}

// handle the settings field : url
function swcc_linklog_admin_urlparam() {
  echo '<input class="regular-text" type="text" name="swcc_linklog_urlparam" id="swcc_linklog_urlparam" value="' . swcc_linklog_get_parametername() . '" />';
}

// handle the settings field : iplock
function swcc_linklog_admin_iplockparam() {
  $curvalue = swcc_linklog_get_iplock();
  echo '<select name="swcc_linklog_iplockparam" id="swcc_linklog_iplockparam">';
  echo '<option value="0"' . ( ( $curvalue == 0 ) ? ' selected="selected"' : '' ) . '>Count all clicks</option>';
  echo '<option value="30"' . ( ( $curvalue == 30 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 30 seconds</option>';
  echo '<option value="60"' . ( ( $curvalue == 60 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 1 minute</option>';
  echo '<option value="300"' . ( ( $curvalue == 300 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 5 minutes</option>';
  echo '<option value="900"' . ( ( $curvalue == 900 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 15 minutes</option>';
  echo '<option value="1800"' . ( ( $curvalue == 1800 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 30 minutes</option>';
  echo '<option value="3600"' . ( ( $curvalue == 3600 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 1 hour</option>';
  echo '<option value="10800"' . ( ( $curvalue == 10800 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 3 hours</option>';
  echo '<option value="21600"' . ( ( $curvalue == 21600 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 6 hours</option>';
  echo '<option value="43200"' . ( ( $curvalue == 43200 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 12 hours</option>';
  echo '<option value="86400"' . ( ( $curvalue == 86400 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 24 hours</option>';
  echo '<option value="129600"' . ( ( $curvalue == 129600 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 36 hours</option>';
  echo '<option value="172800"' . ( ( $curvalue == 172800 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 48 hours</option>';
  echo '<option value="259200"' . ( ( $curvalue == 259200 ) ? ' selected="selected"' : '' ) . '>Do not count multiple clicks from the same IP within 72 hours</option>';
  echo '</select>';
}

// check input : url
function swcc_linklog_admin_urlparam_validate( $input ) {
  if ( empty( $input ) ) {
    $new = 'goto';
  }  elseif ( ctype_alnum( $input ) ) {
    $new = $input;
  } else {
    $new = swcc_linklog_get_parametername();
    add_settings_error( 'link-log-settings-url-err', 'link-log-settings-url-error', 'The parameter name must only contain letters and/or digits.', 'error' );	
  }
  return $new;
}

// check input : iploc
function swcc_linklog_admin_iplockparam_validate( $input ) {
  return $input;
}

// init backend 
function swcc_linklog_adminmenu() {
  // settings page
  add_options_page( 'link-log', 'link-log', 'manage_options', 'link-log-settings', 'swcc_linklog_admin_settings' );
  // log page
  add_submenu_page( 'tools.php', 'link-log', 'link-log', 'manage_options', 'link-log-log', 'swcc_linklog_admin_log' );
}

// register settings
function swcc_linklog_register_settings() {
  register_setting( 'swcc_linklog', 'swcc_linklog_urlparam', 'swcc_linklog_admin_urlparam_validate');
  add_settings_section( 'link-log-settings-url', 'URL Parameter', 'swcc_linklog_admin_settings_url', 'link-log-settings' );
  add_settings_field( 'swcc_linklog_settings_urlparam', 'Parameter Name to use in URL', 'swcc_linklog_admin_urlparam', 'link-log-settings', 'link-log-settings-url', array( 'label_for' => 'swcc_linklog_urlparam' ) );
  
  register_setting( 'swcc_linklog', 'swcc_linklog_iplockparam', 'swcc_linklog_admin_iplockparam_validate');
  add_settings_section( 'link-log-settings-iplock', 'IP Lock', 'swcc_linklog_admin_settings_iplock', 'link-log-settings' );
  add_settings_field( 'swcc_linklog_settings_iplockparam', 'IP Lock Setting', 'swcc_linklog_admin_iplockparam', 'link-log-settings', 'link-log-settings-iplock', array( 'label_for' => 'swcc_linklog_iplockparam' ) );
}

// load javascript in header
function swcc_linklog_add_scripts() {
  wp_enqueue_script( 'swcc-linklog-tablesorter', plugins_url( '/js/jquery.tablesorter.min.js', __FILE__ ), 'jquery' );
  wp_enqueue_script( 'swcc-linklog-init', plugins_url( '/js/link-log.js', __FILE__ ), 'swcc-linklog-tablesorter' );
}

// load css in header
function swcc_linklog_add_styles() {
  wp_enqueue_style( 'swcc-linklog-css', plugins_url('css/style.css', __FILE__ ) );
}

// get name of url parameter
function swcc_linklog_get_parametername() {
  return get_option( 'swcc_linklog_urlparam', 'goto' );
}

// get ip lock parameter value
function swcc_linklog_get_iplock() {
  return get_option( 'swcc_linklog_iplockparam', '0' );
}

// this function can be used in theme
// returns the new url
function get_linklog_url( $url ) {
  return swcc_linklog_make_url( $url );
}

// this function can be used in theme
// prints the new url
function the_linklog_url( $url ) {
  echo swcc_linklog_make_url( $url );
}

add_filter( 'the_content', 'swcc_linklog_parse_content' );
add_filter( 'query_vars', 'swcc_linklog_queryvar' );
add_filter( 'parse_request', 'swcc_linklog_redirect' );
add_action( 'admin_menu', 'swcc_linklog_adminmenu' );
add_action( 'admin_init', 'swcc_linklog_register_settings' );
add_action( 'admin_init', 'swcc_linklog_add_styles' );
add_action( 'admin_enqueue_scripts', 'swcc_linklog_add_scripts' );

// ***
// *** install / activate / new multisite blog
// ***

// on plugin installation
function swcc_linklog_install( $network_wide ) {
  if( $network_wide ) {
    swcc_linklog_install_network();
  } else {
    swcc_linklog_install_single();
  }
}

// for installation on wp single site or for a single blog within a multi site installation
function swcc_linklog_install_single() {
  swcc_linklog_create_table();
}

// for network wide installation on wp multi site
function swcc_linklog_install_network() {
  global $wpdb;
  $activeblog = $wpdb->blogid;
  $blogids = $wpdb->get_col( esc_sql( 'SELECT blog_id FROM ' . $wpdb->blogs ) );
  foreach ($blogids as $blogid) {
    switch_to_blog($blogid);
    swcc_linklog_create_table();
  }
  switch_to_blog( $activeblog );
}

// when a new blog is added on wp multi site 
function swcc_linklog_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    global $wpdb;
    if ( is_plugin_active_for_network( 'link-log/link-log.php' ) ) {
      $current = $wpdb->blogid;
      switch_to_blog( $blog_id );
      swcc_linklog_create_table();
      switch_to_blog( $current );
    }
}

// create single table
function swcc_linklog_create_table() {
  global $wpdb;
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta( 'CREATE TABLE ' . $wpdb->prefix . 'linklog (
    linklog_url VARCHAR(500) NOT NULL, 
    linklog_clicked TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    linklog_ip VARCHAR(50) 
    );'
  );
  update_option( "swcc_linklog_version", SWCC_LINKLOG_VERSION );
}

// update
function swcc_linklog_update() {
  $installed_version = get_option( "swcc_linklog_version" );
  if ( $installed_version != SWCC_LINKLOG_VERSION )  {
    swcc_linklog_create_table();
  }
}

register_activation_hook( __FILE__, 'swcc_linklog_install' );
add_action( 'plugins_loaded', 'swcc_linklog_update' );
add_action( 'wpmu_new_blog', 'swcc_linklog_new_blog', 10, 6);

// ***
// *** uninstall
// ***

// uninstall main function
function swcc_linklog_uninstall( ) {
  if( is_multisite() ) {
    swcc_linklog_uninstall_network();
  } else {
    swcc_linklog_uninstall_single();
  }
}

// for uninstall on wp single site
function swcc_linklog_uninstall_single() {
  swcc_linklog_delete_table();
}

// for network wide uninstall on wp multi site
function swcc_linklog_uninstall_network() {
  global $wpdb;
  $activeblog = $wpdb->blogid;
  $blogids = $wpdb->get_col( esc_sql( 'SELECT blog_id FROM ' . $wpdb->blogs ) );
  foreach ($blogids as $blogid) {
    switch_to_blog($blogid);
    swcc_linklog_delete_table();
  }
  switch_to_blog( $activeblog );
}

// delete table
function swcc_linklog_delete_table() {
  global $wpdb;
  $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'linklog' );
}

register_uninstall_hook( __FILE__, 'swcc_linklog_uninstall' );
?>