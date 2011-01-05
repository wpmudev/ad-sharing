<?php
/*
Plugin Name: Ad Sharing
Plugin URI: http://premium.wpmudev.org/project/ad-sharing
Description: Simply split advertising revenues with your users with this easy to use plugin. You can use adsense, context ads or any combination of advertising you like. Time to reap (and share) blogging rewards!
Author: Andrew Billits, Ulrich Sossou (Incsub)
Version: 1.1.3
Network: true
Text Domain: ad_sharing
Author URI: http://premium.wpmudev.org/
WDP ID: 40
*/

/*
Copyright 2007-2009 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Store and count the number of ads on the current page
 **/
class Ad_Sharing_Page_Ads {
    var $page_ads = 0;

    function get_count() {
        return $this->page_ads;
    }

    function increase() {
        $this->page_ads += $this->page_ads;
    }
}
$ad_sharing_page_ads =& new Ad_Sharing_Page_Ads();

/**
 * Plugin main class
 **/
class Ad_Sharing {

	/**
	 * PHP4 constructor
	 **/
	function Ad_Sharing() {
		__construct();
	}

	/**
	 * PHP5 constructor
	 **/
	function __construct() {
		add_action( 'network_admin_menu', array( &$this, 'plug_network_pages' ) );
		add_action( 'admin_menu', array( &$this, 'plug_pages' ) );
		add_action( 'admin_init', array( &$this, 'process' ) );
		add_action( 'wp_head', array( &$this, 'advertising_quarter' ) );
		add_filter( 'the_content', array( &$this, 'display_ads' ), 20, 1 );

		// load text domain
		if( defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/ad-sharing.php' ) ) {
			load_muplugin_textdomain( 'ad_sharing', 'ad-sharing-files/languages' );
		} else {
			load_plugin_textdomain( 'ad_sharing', false, dirname( plugin_basename( __FILE__ ) ) . '/ad-sharing-files/languages' );
		}
	}

	/**
	 * Add settings page to network admin
	 **/
	function plug_network_pages() {
		add_submenu_page( 'settings.php', __( 'Advertising', 'ad_sharing' ), __( 'Advertising', 'ad_sharing' ), 'manage_network_options', 'site-advertising', array( &$this, 'site_output' ) );
	}

	/**
	 * Add settings page to site admin
	 **/
	function plug_pages() {
		add_submenu_page( 'ms-admin.php', __( 'Advertising', 'ad_sharing' ), __( 'Advertising', 'ad_sharing' ), 'manage_network_options', 'site-advertising', array( &$this, 'site_output' ) );
		add_submenu_page( 'options-general.php', __( 'Advertising', 'ad_sharing' ), __( 'Advertising', 'ad_sharing' ), 'manage_options', 'blog-advertising', array( &$this, 'blog_output' ) );
	}

	/**
	 * Change advertising quarter
	 *
	 * On each page load the quarter is incremented and the ads will be displayed depending on the current quarter
	 **/
	function advertising_quarter() {
		$advertising_quarter = get_option( 'advertising_quarter' );

		if( in_array( $advertising_quarter, array( 1, 2, 3 ) ) )
			$advertising_quarter++;
		else
			$advertising_quarter = '1';

		update_option( 'advertising_quarter', $advertising_quarter );
	}

	/**
	 * Return ad code depending on the current advertising quarter and the advertising share option
	 **/
	function get_ad_code( $ad_type ) {
		$advertising_share = get_site_option( 'advertising_share' );
		$advertising_quarter = get_option( 'advertising_quarter' );

		switch( $advertising_quarter ) {
			case '1':
				$ad_code_type = 'blog';
			break;

			case '2':
				if ( $advertising_share == '75' )
					$ad_code_type = 'blog';
				else
					$ad_code_type = 'site';
			break;

			case '3':
				if ( $advertising_share == '25' )
					$ad_code_type = 'site';
				else
					$ad_code_type = 'blog';
			break;

			case '4':
			default:
				$ad_code_type = 'site';
			break;
		}

		switch( $ad_code_type ) {
			case 'empty':
				$ad_code = '';
			break;

			case 'blog':
				if ( 'before' == $ad_type )
					$ad_code = get_option( 'advertising_before_code' );

				if ( 'after' == $ad_type )
					$ad_code = get_option( 'advertising_after_code' );
			break;

			case 'site':
			default:
				if ( 'before' == $ad_type )
					$ad_code = get_site_option( 'advertising_before_code' );

				if ( 'after' == $ad_type )
					$ad_code = get_site_option( 'advertising_after_code' );
			break;
		}

		return $ad_code;
	}

	/**
	 * Ad ads to post content
	 **/
	function display_ads( $content ) {
		global $wpdb, $ad_sharing_page_ads, $post;

		$advertising_ads_per_page = get_site_option( 'advertising_ads_per_page' );

		// if we site admin doesn't want ads to be displayed on main blog?
		$advertising_main_blog = get_site_option( 'advertising_main_blog', 'hide' );
		if( 1 == $wpdb->blogid && 'hide' == $advertising_main_blog )
			return $content;

		if( 'page' == $post->post_type ) {
			if ( get_site_option('advertising_location_before_page_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $this->get_ad_code('before') . $content;
					$ad_sharing_page_ads->increase();
				}
			}
			if ( get_site_option('advertising_location_after_page_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $content . $this->get_ad_code('after');
					$ad_sharing_page_ads->increase();
				}
			}
		} elseif( 'post' == $post->post_type ) {
			if ( get_site_option('advertising_location_before_post_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $this->get_ad_code('before') . $content;
					$ad_sharing_page_ads->increase();
				}
			}
			if ( get_site_option('advertising_location_after_post_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $content . $this->get_ad_code('after');
					$ad_sharing_page_ads->increase();
				}
			}
		}

		return $content;
	}

	/**
	 * Save plugin settings
	 **/
	function process() {
		global $plugin_page, $wp_version;

		$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

		// are we on the plugin network option page and are we saving the settings
		if( 'site-advertising' == $plugin_page && 'process' == $action ) {
			check_admin_referer( 'ad-sharing-process_network_options' );

			if ( isset( $_POST[ 'Reset' ] ) ) {

				update_site_option( 'advertising_share', '50' );
				update_site_option( 'advertising_before_code', 'empty' );
				update_site_option( 'advertising_after_code', 'empty' );
				update_site_option( 'advertising_message', 'empty' );
				update_site_option( 'advertising_ads_per_page', '1' );
				update_site_option( 'advertising_location_before_post_content', '0' );
				update_site_option( 'advertising_location_after_post_content', '0' );
				update_site_option( 'advertising_location_before_page_content', '0' );
				update_site_option( 'advertising_location_after_page_content', '0' );
				update_site_option( 'advertising_main_blog', 'hide' );

			} else {

				$advertising_before_code = !empty( $_POST[ 'advertising_before_code' ] ) ? stripslashes( $_POST[ 'advertising_before_code' ] ) : 'empty';
				$advertising_after_code = !empty( $_POST[ 'advertising_after_code' ] ) ? stripslashes( $_POST[ 'advertising_after_code' ] ) : 'empty';
				$advertising_message = !empty( $_POST[ 'advertising_message' ] ) ? stripslashes( $_POST[ 'advertising_message' ] ) : 'empty';
				$advertising_location_before_post_content = !empty( $_POST[ 'advertising_location_before_post_content' ] ) ? stripslashes( $_POST[ 'advertising_location_before_post_content' ] ) : 'empty';
				$advertising_location_after_post_content = !empty( $_POST[ 'advertising_location_after_post_content' ] ) ? stripslashes( $_POST[ 'advertising_location_after_post_content' ] ) : 'empty';
				$advertising_location_before_page_content = !empty( $_POST[ 'advertising_location_before_page_content' ] ) ? stripslashes( $_POST[ 'advertising_location_before_page_content' ] ) : 'empty';
				$advertising_location_after_page_content = !empty( $_POST[ 'advertising_location_after_page_content' ] ) ? stripslashes( $_POST[ 'advertising_location_after_page_content' ] ) : 'empty';

				update_site_option( 'advertising_before_code', $advertising_before_code );
				update_site_option( 'advertising_after_code', $advertising_after_code );
				update_site_option( 'advertising_message', $advertising_message );
				update_site_option( 'advertising_share', stripslashes( $_POST[ 'advertising_share' ] ) );
				update_site_option( 'advertising_ads_per_page', stripslashes( $_POST[ 'advertising_ads_per_page' ] ) );
				update_site_option( 'advertising_location_before_post_content', $advertising_location_before_post_content );
				update_site_option( 'advertising_location_after_post_content', $advertising_location_after_post_content );
				update_site_option( 'advertising_location_before_page_content', $advertising_location_before_page_content );
				update_site_option( 'advertising_location_after_page_content', $advertising_location_after_page_content );
				update_site_option( 'advertising_main_blog', stripslashes( $_POST[ 'advertising_main_blog' ] ) );
			}

			$settings_page = version_compare( $wp_version, '3.0.9', '>' ) ? 'network/settings.php' : 'ms-admin.php';

			wp_redirect( admin_url( $settings_page . '?page=site-advertising&updated=true&updatedmsg=' . urlencode( __( 'Changes saved.', 'ad_sharing' ) ) ) );

		// are we on the plugin site option page and are we saving the settings
		} elseif( 'blog-advertising' == $plugin_page && 'process' == $action ) {
			check_admin_referer( 'ad-sharing-process_site_options' );

			if ( isset( $_POST[ 'Reset' ] ) ) {
				update_option( 'advertising_before_code', '' );
				update_option( 'advertising_after_code', '' );
			} else {
				update_option( 'advertising_before_code', stripslashes( $_POST[ 'advertising_before_code' ] ) );
				update_option( 'advertising_after_code', stripslashes( $_POST[ 'advertising_after_code' ] ) );
			}

			wp_redirect( admin_url( 'options-general.php?page=blog-advertising&updated=true' ) );

		}
	}

	/**
	 * Network option page content
	 **/
	function site_output() {

		if( !current_user_can( 'manage_network_options' ) ) {
			echo '<p>' . __( 'Nice Try...', 'ad_sharing' ) . '</p>';
			return;
		}

		if( isset( $_GET['updated'] ) ) {
			echo '<div id="message" class="updated fade"><p>' . urldecode( $_GET['updatedmsg'] ) . '</p></div>';
		}

		// retrieve options to be displayed
		$advertising_before_code = get_site_option( 'advertising_before_code' );
		if( 'empty' == $advertising_before_code )
			$advertising_before_code = '';

		$advertising_after_code = get_site_option( 'advertising_after_code' );
		if( 'empty' == $advertising_after_code )
			$advertising_after_code = '';

		$advertising_message = get_site_option( 'advertising_message' );
		if( $advertising_message == 'empty' )
			$advertising_message = '';
		?>

		<div class="wrap">
			<h2><?php _e( 'Advertising', 'ad_sharing' ) ?></h2>
			<form method="post" action="?page=site-advertising&action=process">
				<?php wp_nonce_field( 'ad-sharing-process_network_options' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Ad Sharing', 'ad_sharing' ) ?></th>
						<td>
							<?php $advertising_share = get_site_option('advertising_share'); ?>
							<select name="advertising_share" id="advertising_share" >
								<option value="75"<?php selected( $advertising_share, '75' ) ?>><?php _e( 'Site 25% / Blog 75%', 'ad_sharing' ); ?></option>
								<option value="50"<?php selected( $advertising_share, '50' ) ?>><?php _e( 'Site 50% / Blog 50%', 'ad_sharing' ); ?></option>
								<option value="25"<?php selected( $advertising_share, '25' ) ?>><?php _e( 'Site 75% / Blog 25%', 'ad_sharing' ); ?></option>
							</select>
							<br /><?php _e( 'Note that the ads are split over page loads. For a 50/50 split site ads will be shown every other page, etc.', 'ad_sharing' ) ?>
							<br /><?php _e( 'Site advertising will be shown 100% of the time on blogs that have not setup advertising.', 'ad_sharing' ) ?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Ad Locations', 'ad_sharing' ) ?></th>
						<td>
							<label for="advertising_location_before_post_content">
								<input name="advertising_location_before_post_content" id="advertising_location_before_post_content" value="1" type="checkbox"<?php checked( get_site_option('advertising_location_before_post_content'), '1' ) ?>>
								<?php _e( 'Before Post Content', 'ad_sharing' ); ?>
							</label>
							<br />
							<label for="advertising_location_after_post_content">
								<input name="advertising_location_after_post_content" id="advertising_location_after_post_content" value="1" type="checkbox"<?php checked( get_site_option('advertising_location_after_post_content'), '1' ) ?>>
								<?php _e( 'After Post Content', 'ad_sharing' ); ?>
							</label>
							<br />
							<label for="advertising_location_before_page_content">
								<input name="advertising_location_before_page_content" id="advertising_location_before_page_content" value="1" type="checkbox"<?php checked( get_site_option('advertising_location_before_page_content'), '1' ) ?>>
								<?php _e( 'Before Page Content', 'ad_sharing' ); ?>
							</label>
							<br />
							<label for="advertising_location_after_page_content">
								<input name="advertising_location_after_page_content" id="advertising_location_after_page_content" value="1" type="checkbox"<?php checked( get_site_option('advertising_location_after_page_content'), '1' ) ?>>
								<?php _e( 'After Page Content', 'ad_sharing' ); ?>
							</label>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Ads per page', 'ad_sharing' ) ?></th>
						<td>
							<?php $advertising_ads_per_page = get_site_option( 'advertising_ads_per_page', 3 ); ?>
							<select name="advertising_ads_per_page" id="advertising_ads_per_page" >
								<?php
								for( $i = 1; $i <= 10; $i++ ) {
									echo '<option value="' . $i . '"' . selected( $advertising_ads_per_page, $i, false ) . '>' . $i . '</option>';
								}
								?>
							</select>
							<br /><?php _e( 'Maximum number of ads to be shown on a single page. For Google Adsense set this to "3".', 'ad_sharing' ) ?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( '"Before" Ad Code', 'ad_sharing' ) ?></th>
						<td>
							<textarea name="advertising_before_code" type="text" rows="5" wrap="soft" id="advertising_before_code" style="width: 95%"/><?php echo esc_textarea( $advertising_before_code ); ?></textarea>
							<br /><?php _e( 'Used before post and page content.', 'ad_sharing' ) ?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( '"After" Ad Code', 'ad_sharing' ) ?></th>
						<td>
							<textarea name="advertising_after_code" type="text" rows="5" wrap="soft" id="advertising_after_code" style="width: 95%" /><?php echo esc_textarea( $advertising_after_code ); ?></textarea>
							<br /><?php _e( 'Used after post and page content.', 'ad_sharing' ) ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Message', 'ad_sharing' ) ?></th>
						<td>
							<textarea name="advertising_message" type="text" rows="5" wrap="soft" id="advertising_message" style="width: 95%" /><?php echo esc_textarea( $advertising_message ); ?></textarea>
							<br /><?php _e( 'This message is displayed at the top of the blog advertising page.', 'ad_sharing' ) ?>
							<br /><?php _e( 'Tip: Use this message to explain the ad sharing.', 'ad_sharing' ); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Main Blog', 'ad_sharing' ) ?></th>
						<td>
							<?php $advertising_main_blog = get_site_option( 'advertising_main_blog', 'hide' ); ?>
							<select name="advertising_main_blog" id="advertising_main_blog" >
								<option value="hide"<?php selected( $advertising_main_blog, 'hide' ) ?>><?php _e( 'Hide Ads', 'ad_sharing' ); ?></option>
								<option value="show"<?php selected( $advertising_main_blog, 'show' ) ?>><?php _e( 'Show Ads', 'ad_sharing' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e( 'Save Changes', 'ad_sharing' ) ?>" />
					<input type="submit" name="Reset" value="<?php _e( 'Reset', 'ad_sharing' ) ?>" />
				</p>
			</form>
		</div>

		<?php
	}

	/**
	 * Network option page content
	 **/
	function blog_output() {

		if( !current_user_can( 'manage_options' ) ) {
			echo '<p>' . __( 'Nice Try...' ) . '</p>';
			return;
		}

		$advertising_message = stripslashes( get_site_option( 'advertising_message' ) );
		$advertising_message = ( 'empty' !== $advertising_message ) ? $advertising_message : '';
		?>

		<div class="wrap">
			<h2><?php _e( 'Advertising', 'ad_sharing' ) ?></h2>
			<p><?php echo $advertising_message; ?></p>

			<form method="post" action="options-general.php?page=blog-advertising&action=process">
				<?php wp_nonce_field( 'ad-sharing-process_site_options' ); ?>

				<table class="form-table">
					<?php
					if ( get_site_option('advertising_location_before_post_content') == '1' || get_site_option('advertising_location_before_page_content') == '1' ) {
					?>
						<tr valign="top">
							<th scope="row"><?php _e( '"Before" Ad Code', 'ad_sharing' ) ?></th>
							<td>
								<textarea name="advertising_before_code" type="text" rows="5" wrap="soft" id="advertising_before_code" style="width: 95%" /><?php echo esc_textarea( get_option('advertising_before_code') ) ?></textarea>
								<br /><?php _e( 'Used before post and page content.', 'ad_sharing' ) ?>
							</td>
						</tr>
					<?php
					}
					if ( get_site_option('advertising_location_after_post_content') == '1' || get_site_option('advertising_location_after_page_content') == '1' ) {
					?>
					<tr valign="top">
							<th scope="row"><?php _e( '"After" Ad Code', 'ad_sharing' ) ?></th>
							<td>
								<textarea name="advertising_after_code" type="text" rows="5" wrap="soft" id="advertising_after_code" style="width: 95%" /><?php echo esc_textarea( get_option('advertising_after_code') ) ?></textarea>
								<br /><?php _e( 'Used after post and page content.', 'ad_sharing' ) ?>
							</td>
						</tr>
					<?php
					}
					?>
				</table>

				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e( 'Save Changes', 'ad_sharing' ) ?>" />
					<input type="submit" name="Reset" value="<?php _e( 'Reset', 'ad_sharing' ) ?>" />
				</p>
			</form>
		</div>
		<?php
	}

}

$ad_sharing =& new Ad_Sharing();

if ( !function_exists( 'wdp_un_check' ) ) {
	add_action( 'admin_notices', 'wdp_un_check', 5 );
	add_action( 'network_admin_notices', 'wdp_un_check', 5 );

	function wdp_un_check() {
		if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'edit_users' ) )
			echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
	}
}
