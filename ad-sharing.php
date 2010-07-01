<?php
/*
Plugin Name: Ad Sharing
Plugin URI: 
Description:
Author: Andrew Billits (Incsub)
Version: 1.1.1
Author URI:
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

//------------------------------------------------------------------------//
//---Classes--------------------------------------------------------------//
//------------------------------------------------------------------------//

class ad_sharing_page_ads {
    var $page_ads = 0;

    function get_count() {
        return $this->page_ads;
    }
	
    function increase() {
		$tmp_page_ads = $this->page_ads;
		$tmp_page_ads = $tmp_page_ads + 1;
        $this->page_ads = $tmp_page_ads;
    }
}
$ad_sharing_page_ads = new ad_sharing_page_ads();
//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
add_action('admin_menu', 'ad_sharing_plug_pages');
add_action('wp_head', 'ad_sharing_advertising_quarter');
add_filter('the_content', 'ad_sharing_output', 20, 1);
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//

function ad_sharing_plug_pages() {
	global $wpdb, $wp_roles, $current_user;
	if ( is_site_admin() ) {
		add_submenu_page('ms-admin.php', 'Advertising', 'Advertising', 10, 'site-advertising', 'ad_sharing_site_output');
	}
	if ( current_user_can('create_users') ) {
		add_submenu_page('options-general.php', 'Advertising', 'Advertising', '0', 'blog-advertising', 'ad_sharing_blog_output' );
	}
}

function ad_sharing_advertising_quarter() {
	$advertising_quarter = get_option('advertising_quarter');
	if ( $advertising_quarter == '1' ) {
		$advertising_quarter = '2';
	} else if ( $advertising_quarter == '2' ) {
		$advertising_quarter = '3';
	} else if ( $advertising_quarter == '3' ) {
		$advertising_quarter = '4';
	} else if ( $advertising_quarter == '4' ) {
		$advertising_quarter = '1';
	} else {
		$advertising_quarter = '1';
	}
	update_option('advertising_quarter', $advertising_quarter);
}

function ad_sharing_get_ad_code($ad_type) {
	$advertising_share = get_site_option('advertising_share');
	$advertising_quarter = get_option('advertising_quarter');

	if ( $advertising_quarter == '1' ) {
		if ( $advertising_share == '75' ) {
			$ad_code_type = 'blog';
		}
		if ( $advertising_share == '50' ) {
			$ad_code_type = 'blog';
		}
		if ( $advertising_share == '25' ) {
			$ad_code_type = 'blog';
		}
	}
	if ( $advertising_quarter == '2' ) {
		if ( $advertising_share == '75' ) {
			$ad_code_type = 'blog';
		}
		if ( $advertising_share == '50' ) {
			$ad_code_type = 'site';
		}
		if ( $advertising_share == '25' ) {
			$ad_code_type = 'site';
		}
	}
	if ( $advertising_quarter == '3' ) {
		if ( $advertising_share == '75' ) {
			$ad_code_type = 'blog';
		}
		if ( $advertising_share == '50' ) {
			$ad_code_type = 'blog';
		}
		if ( $advertising_share == '25' ) {
			$ad_code_type = 'site';
		}
	}
	if ( $advertising_quarter == '4' ) {
		if ( $advertising_share == '75' ) {
			$ad_code_type = 'site';
		}
		if ( $advertising_share == '50' ) {
			$ad_code_type = 'site';
		}
		if ( $advertising_share == '25' ) {
			$ad_code_type = 'site';
		}
	}
	if ( $ad_code_type == 'blog' ) {
		if ( $ad_type == 'before' ) {
			$ad_code = stripslashes( get_option('advertising_before_code') );
		}
		if ( $ad_type == 'after' ) {
			$ad_code = stripslashes( get_option('advertising_after_code') );
		}
	}
	if ( $ad_code_type == 'site' ) {
		if ( $ad_type == 'before' ) {
			$ad_code = stripslashes( get_site_option('advertising_before_code') );
		}
		if ( $ad_type == 'after' ) {
			$ad_code = stripslashes( get_site_option('advertising_after_code') );
		}
	}
	if ( empty($ad_code) ) {
		if ( $ad_type == 'before' ) {
			$ad_code = stripslashes( get_site_option('advertising_before_code') );
		}
		if ( $ad_type == 'after' ) {
			$ad_code = stripslashes( get_site_option('advertising_after_code') );
		}
	}
	if ( $ad_code == 'empty' ) {
		$ad_code = '';
	}
	return $ad_code;
}

function ad_sharing_output($content) {
	global $wpdb, $ad_sharing_page_ads;
	$advertising_ads_per_page = get_site_option('advertising_ads_per_page');
	$advertising_main_blog = get_site_option('advertising_main_blog', 'hide');
	$display_ads = 'yes';
	if ( $wpdb->blogid == 1 && $advertising_main_blog == 'hide' ) {
		$display_ads = 'no';
	}
	if ( $display_ads == 'yes' ) {
		if ( is_page() ) {
			if ( get_site_option('advertising_location_before_page_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = ad_sharing_get_ad_code('before') . $content;
					$ad_sharing_page_ads->increase();
				}
			}
			if ( get_site_option('advertising_location_after_page_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $content . ad_sharing_get_ad_code('after');
					$ad_sharing_page_ads->increase();
				}
			}
		} else {
			if ( get_site_option('advertising_location_before_post_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = ad_sharing_get_ad_code('before') . $content;
					$ad_sharing_page_ads->increase();
				}
			}
			if ( get_site_option('advertising_location_after_post_content') == '1' ) {
				$page_ads = $ad_sharing_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $content . ad_sharing_get_ad_code('after');
					$ad_sharing_page_ads->increase();
				}
			}
		}
	}
	return $content;
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//

function ad_sharing_site_output() {
	global $wpdb, $wp_roles, $current_user;
	
	if(!current_user_can('manage_options')) {
		echo "<p>" . __('Nice Try...') . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			$advertising_before_code = stripslashes( get_site_option('advertising_before_code') );
			if ($advertising_before_code == 'empty') {
				$advertising_before_code = '';
			}
			$advertising_after_code = stripslashes( get_site_option('advertising_after_code') );
			if ($advertising_after_code == 'empty') {
				$advertising_after_code = '';
			}
			$advertising_message = stripslashes( get_site_option('advertising_message') );
			if ($advertising_message == 'empty') {
				$advertising_message = '';
			}
			?>
			<h2><?php _e('Advertising') ?></h2>
            <form method="post" action="ms-admin.php?page=site-advertising&action=process">
            <table class="form-table">
            <tr valign="top">
            <th scope="row"><?php _e('Ad Sharing') ?></th>
            <td>
            <?php
            $advertising_share = get_site_option('advertising_share');
			?>
            <select name="advertising_share" id="advertising_share" >
                <option value="75" <?php if ( $advertising_share == '75' ) { echo 'selected="selected"'; } ?> ><?php _e('Site 25% / Blog 75%'); ?></option>
                <option value="50" <?php if ( $advertising_share == '50' ) { echo 'selected="selected"'; } ?> ><?php _e('Site 50% / Blog 50%'); ?></option>
                <option value="25" <?php if ( $advertising_share == '25' ) { echo 'selected="selected"'; } ?> ><?php _e('Site 75% / Blog 25%'); ?></option>
            </select>
            <br /><?php _e('Note that the ads are split over page loads. For a 50/50 split site ads will be shown every other page, etc.') ?>
            <br /><?php _e('Site advertising will be shown 100% of the time on blogs that have not setup advertising.') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Ad Locations') ?></th>
            <td>
            <label for="advertising_location_before_post_content">
            <input name="advertising_location_before_post_content" id="advertising_location_before_post_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_before_post_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('Before Post Content'); ?></label>
            <br />
            <label for="advertising_location_after_post_content">
            <input name="advertising_location_after_post_content" id="advertising_location_after_post_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_after_post_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('After Post Content'); ?></label>
            <br />
            <label for="advertising_location_before_page_content">
            <input name="advertising_location_before_page_content" id="advertising_location_before_page_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_before_page_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('Before Page Content'); ?></label>
            <br />
            <label for="advertising_location_after_page_content">
            <input name="advertising_location_after_page_content" id="advertising_location_after_page_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_after_page_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('After Page Content'); ?></label>
            </td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Ads per page') ?></th>
            <td>
            <?php
            $advertising_ads_per_page = get_site_option('advertising_ads_per_page', 3);
			?>
            <select name="advertising_ads_per_page" id="advertising_ads_per_page" >
                <option value="1" <?php if ( $advertising_ads_per_page == '1' ) { echo 'selected="selected"'; } ?> ><?php _e('1'); ?></option>
                <option value="2" <?php if ( $advertising_ads_per_page == '2' ) { echo 'selected="selected"'; } ?> ><?php _e('2'); ?></option>
                <option value="3" <?php if ( $advertising_ads_per_page == '3' ) { echo 'selected="selected"'; } ?> ><?php _e('3'); ?></option>
                <option value="4" <?php if ( $advertising_ads_per_page == '4' ) { echo 'selected="selected"'; } ?> ><?php _e('4'); ?></option>
                <option value="5" <?php if ( $advertising_ads_per_page == '5' ) { echo 'selected="selected"'; } ?> ><?php _e('5'); ?></option>
                <option value="6" <?php if ( $advertising_ads_per_page == '6' ) { echo 'selected="selected"'; } ?> ><?php _e('6'); ?></option>
                <option value="7" <?php if ( $advertising_ads_per_page == '7' ) { echo 'selected="selected"'; } ?> ><?php _e('7'); ?></option>
                <option value="8" <?php if ( $advertising_ads_per_page == '8' ) { echo 'selected="selected"'; } ?> ><?php _e('8'); ?></option>
                <option value="9" <?php if ( $advertising_ads_per_page == '9' ) { echo 'selected="selected"'; } ?> ><?php _e('9'); ?></option>
                <option value="10" <?php if ( $advertising_ads_per_page == '10' ) { echo 'selected="selected"'; } ?> ><?php _e('10'); ?></option>
            </select>
            <br /><?php _e('Maximum number of ads to be shown on a single page. For Google Adsense set this to "3".') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('"Before" Ad Code') ?></th>
            <td>
            <textarea name="advertising_before_code" type="text" rows="5" wrap="soft" id="advertising_before_code" style="width: 95%"/><?php echo $advertising_before_code; ?></textarea>
            <br /><?php _e('Used before post and page content.') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('"After" Ad Code') ?></th>
            <td>
            <textarea name="advertising_after_code" type="text" rows="5" wrap="soft" id="advertising_after_code" style="width: 95%"/><?php echo $advertising_after_code; ?></textarea>
            <br /><?php _e('Used after post and page content.') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Message') ?></th>
            <td>
            <textarea name="advertising_message" type="text" rows="5" wrap="soft" id="advertising_message" style="width: 95%"/><?php echo $advertising_message; ?></textarea>
            <br /><?php _e('This message is displayed at the top of the blog advertising page.') ?>
            <br /><?php _e('Tip: Use this message to explain the ad sharing.'); ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Main Blog') ?></th>
            <td>
            <?php
            $advertising_main_blog = get_site_option('advertising_main_blog', 'hide');
			?>
            <select name="advertising_main_blog" id="advertising_main_blog" >
                <option value="hide" <?php if ( $advertising_main_blog == 'hide' ) { echo 'selected="selected"'; } ?> ><?php _e('Hide Ads'); ?></option>
                <option value="show" <?php if ( $advertising_main_blog == 'show' ) { echo 'selected="selected"'; } ?> ><?php _e('Show Ads'); ?></option>
            </select>
            <br /><?php //_e('') ?></td>
            </tr>
            </table>
            
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			<input type="submit" name="Reset" value="<?php _e('Reset') ?>" />
            </p>
            </form>
			<?php
		break;
		//---------------------------------------------------//
		case "process":
			if ( isset( $_POST[ 'Reset' ] ) ) {
				update_site_option( "advertising_share", "50" );
				update_site_option( "advertising_before_code", "empty" );
				update_site_option( "advertising_after_code", "empty" );
				update_site_option( "advertising_message", "empty" );
				update_site_option( "advertising_ads_per_page", "1" );
				update_site_option( "advertising_location_before_post_content", "0" );
				update_site_option( "advertising_location_after_post_content", "0" );
				update_site_option( "advertising_location_before_page_content", "0" );
				update_site_option( "advertising_location_after_page_content", "0" );
				update_site_option( "advertising_main_blog", "hide" );
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='ms-admin.php?page=site-advertising&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
				</script>
				";			
			} else {
				if ( empty($_POST[ 'advertising_before_code' ]) ) {
					$advertising_before_code = 'empty';
				} else {
					$advertising_before_code = $_POST[ 'advertising_before_code' ];
				}
				if ( empty($_POST[ 'advertising_after_code' ]) ) {
					$advertising_after_code = 'empty';
				} else {
					$advertising_after_code = $_POST[ 'advertising_after_code' ];
				}
				if ( empty($_POST[ 'advertising_message' ]) ) {
					$advertising_message = 'empty';
				} else {
					$advertising_message = $_POST[ 'advertising_message' ];
				}
				
				if ( empty($_POST[ 'advertising_location_before_post_content' ]) ) {
					$advertising_location_before_post_content = 'empty';
				} else {
					$advertising_location_before_post_content = $_POST[ 'advertising_location_before_post_content' ];
				}
				if ( empty($_POST[ 'advertising_location_after_post_content' ]) ) {
					$advertising_location_after_post_content = 'empty';
				} else {
					$advertising_location_after_post_content = $_POST[ 'advertising_location_after_post_content' ];
				}
				if ( empty($_POST[ 'advertising_location_before_page_content' ]) ) {
					$advertising_location_before_page_content = 'empty';
				} else {
					$advertising_location_before_page_content = $_POST[ 'advertising_location_before_page_content' ];
				}
				if ( empty($_POST[ 'advertising_location_after_page_content' ]) ) {
					$advertising_location_after_page_content = 'empty';
				} else {
					$advertising_location_after_page_content = $_POST[ 'advertising_location_after_page_content' ];
				}
				
				update_site_option( "advertising_before_code", $advertising_before_code );
				update_site_option( "advertising_after_code", $advertising_after_code );
				update_site_option( "advertising_message", $advertising_message );
				update_site_option( "advertising_share", $_POST[ 'advertising_share' ] );
				update_site_option( "advertising_ads_per_page", $_POST[ 'advertising_ads_per_page' ] );
				update_site_option( "advertising_location_before_post_content", $advertising_location_before_post_content );
				update_site_option( "advertising_location_after_post_content", $advertising_location_after_post_content );
				update_site_option( "advertising_location_before_page_content", $advertising_location_before_page_content );
				update_site_option( "advertising_location_after_page_content", $advertising_location_after_page_content );
				update_site_option( "advertising_main_blog", $_POST[ 'advertising_main_blog' ] );
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='ms-admin.php?page=site-advertising&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "temp":
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function ad_sharing_blog_output() {
	global $wpdb, $wp_roles, $current_user;
	
	if(!current_user_can('manage_options')) {
		echo "<p>" . __('Nice Try...') . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	
	$advertising_message = stripslashes( get_site_option('advertising_message') );
	if ( $advertising_message == 'empty' ) {
		$advertising_message = '';
	}
	
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			?>
			<h2><?php _e('Advertising') ?></h2>
			<p><?php echo $advertising_message; ?></p>
            <form method="post" action="options-general.php?page=blog-advertising&action=process">
            <table class="form-table">
            <?php
			if ( get_site_option('advertising_location_before_post_content') == '1' || get_site_option('advertising_location_before_page_content') == '1' ) {
			?>
            <tr valign="top">
            <th scope="row"><?php _e('"Before" Ad Code') ?></th>
            <td>
            <textarea name="advertising_before_code" type="text" rows="5" wrap="soft" id="advertising_before_code" style="width: 95%"/><?php echo stripslashes( get_option('advertising_before_code') ) ?></textarea>
            <br /><?php _e('Used before post and page content.') ?></td>
            </tr>
            <?php
			}
			if ( get_site_option('advertising_location_after_post_content') == '1' || get_site_option('advertising_location_after_page_content') == '1' ) {
			?>
            <tr valign="top">
            <th scope="row"><?php _e('"After" Ad Code') ?></th>
            <td>
            <textarea name="advertising_after_code" type="text" rows="5" wrap="soft" id="advertising_after_code" style="width: 95%"/><?php echo stripslashes( get_option('advertising_after_code') ) ?></textarea>
            <br /><?php _e('Used after post and page content.') ?></td>
            </tr>
            <?php
			}
			?>
            </table>
            
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			<input type="submit" name="Reset" value="<?php _e('Reset') ?>" />
            </p>
            </form>
			<?php
		break;
		//---------------------------------------------------//
		case "process":
			if ( isset( $_POST[ 'Reset' ] ) ) {
				update_option( "advertising_before_code", "" );
				update_option( "advertising_after_code", "" );
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='options-general.php?page=blog-advertising&updated=true';
				</script>
				";			
			} else {
				update_option( "advertising_before_code", $_POST[ 'advertising_before_code' ] );
				update_option( "advertising_after_code", $_POST[ 'advertising_after_code' ] );
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='options-general.php?page=blog-advertising&updated=true';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "temp":
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

?>
