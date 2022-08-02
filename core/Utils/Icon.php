<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class Icon extends Singleton {
	public function launch( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_launch', '<i class="fas fa-rocket"></i>' ) . '</span>';
	}

	public function customers( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_customers', '<i class="fas fa-users"></i>' ) . '</span>';
	}

	public function blueprint( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_blueprint', '<i class="fas fa-sync-alt"></i>' ) . '</span>';
	}

	public function staging( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_staging', '<i class="fas fa-clone"></i>' ) . '</span>';
	}

	public function dev_tools( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_tools', '<i class="fas fa-code"></i>' ) . '</span>';
	}

	public function dev_details( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_details', '<i class="fal fa-info"></i>' ) . '</span>';
	}

	public function dev_code_editor( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_code_editor', '<i class="fal fa-codiad"></i>' ) . '</span>';
	}

	public function dev_home( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_home', '<i class="fas fa-home"></i>' ) . '</span>';
	}

	public function dev_sftp( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_sftp', '<i class="fas fa-bookmark"></i>' ) . '</span>';
	}

	public function dev_php( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_php', '<i class="fas fa-tachometer"></i>' ) . '</span>';
	}

	public function dev_database( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_database', '<i class="fal fa-adminer"></i>' ) . '</span>';
	}

	public function dev_cli( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_cli', '<i class="fal fa-shell"></i>' ) . '</span>';
	}

	public function plugins( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_plugins', '<i class="fas fa-plug"></i>' ) . '</span>';
	}

	public function themes( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_themes', '<i class="fas fa-paint-roller"></i>' ) . '</span>';
	}

	public function domains( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_domain', '<i class="fas fa-globe"></i>' ) . '</span>';
	}

	public function backups( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_backups', '<i class="fas fa-history"></i>' ) . '</span>';
	}

	public function migration( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_migration', '<i class="fas fa-truck-moving"></i>' ) . '</span>';
	}

	public function delete( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_delete', '<i class="fas fa-trash-alt"></i>' ) . '</span>';
	}

	public function manage( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_manage', '<i class="fas fa-cog"></i>' ) . '</span>';
	}

	public function dashboard( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_woo_dashboard', '<i class="fas fa-dashboard"></i>' ) . '</span>';
	}

	public function orders( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_orders', '<i class="fas fa-orders"></i>' ) . '</span>';
	}

	public function my_subscription( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_subscriptions', '<i class="fas fa-subscriptions"></i>' ) . '</span>';
	}

	public function downloads( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_downloads', '<i class="fas fa-downloads"></i>' ) . '</span>';
	}

	public function address( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_address', '<i class="fas fa-edit-address"></i>' ) . '</span>';
	}

	public function account_details( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_account_details', '<i class="fas fa-edit-account"></i>' ) . '</span>';
	}

	public function logout( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_logout', '<i class="fas fa-sign-out-alt"></i>' ) . '</span>';
	}

	public function filter( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_filter', '<i class="fas fa-filter"></i>' ) . '</span>';
	}

	public function list( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_list', '<i class="fas fa-list"></i>' ) . '</span>';
	}

	public function grid( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_grid', '<i class="fas fa-th"></i>' ) . '</span>';
	}

	public function preview( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_preview', '<i class="fas fa-desktop"></i>' ) . '</span>';
	}

	public function login( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_login', '<i class="fas fa-tools"></i>' ) . '</span>';
	}

	public function site_view( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_site_view', '<i class="fas fa-external-link-alt"></i>' ) . '</span>';
	}

	public function refresh( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_refresh', '<i class="fas fa-sync-alt"></i>' ) . '</span>';
	}

	public function user_role( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_user_role', '<i class="fas fa-user-gear"></i>' ) . '</span>';
	}

	public function dns( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dns', '<i class="fas fa-atlas"></i>' ) . '</span>';
	}

	public function site_login( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_site_login', '<i class="fas fa-user-shield"></i>' ) . '</span>';
	}

	public function search( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_search', '<i class="fas fa-search"></i>' ) . '</span>';
	}

	public function notifications( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_notifications', '<i class="fas fa-bell"></i>' ) . '</span>';
	}

	public function close( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_close', '<i class="fas fa-times-circle"></i>' ) . '</span>';
	}

	public function clock( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_clock', '<i class="fas fa-clock"></i>' ) . '</span>';
	}

	public function save( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_save', '<i class="fas fa-save"></i>' ) . '</span>';
	}

	public function task( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_task', '<i class="fas fa-tools"></i>' ) . '</span>';
	}

	public function user( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_user', '<i class="fas fa-user-circle"></i>' ) . '</span>';
	}

	public function users( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_users', '<i class="fas fa-users"></i>' ) . '</span>';
	}

	public function site( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_site', '<i class="fas fa-columns"></i>' ) . '</span>';
	}

	public function live_site( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_live_site', '<i class="fas fa-globe"></i>' ) . '</span>';
	}

	public function dev_site( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_dev_site', '<i class="fas fa-cog"></i>' ) . '</span>';
	}

	public function settings( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_settings', '<i class="fas fa-cogs"></i>' ) . '</span>';
	}

	public function wordpress( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_wordpress', '<i class="fab fa-wordpress-simple"></i>' ) . '</span>';
	}

	public function notice( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_notice', '<i class="fas fa-info"></i>' ) . '</span>';
	}

	public function comments( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_comments', '<i class="fas fa-comments"></i>' ) . '</span>';
	}

	public function email( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_email', '<i class="fas fa-envelope"></i>' ) . '</span>';
	}

	public function disk_space( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_disk_space', '<i class="fas fa-hdd"></i>' ) . '</span>';
	}

	public function blog_post( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_blog_post', '<i class="fas fa-newspaper"></i>' ) . '</span>';
	}

	public function read_more( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_read_more', '<i class="fas fa-long-arrow-right"></i>' ) . '</span>';
	}

	public function updates( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_updates', '<i class="fas fa-download"></i>' ) . '</span>';
	}

	public function version( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_version', '<i class="fas fa-php"></i>' ) . '</span>';
	}

	public function arrow_right( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_arrow_right', '<i class="fas fa-long-arrow-right"></i>' ) . '</span>';
	}

	public function arrow_left( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_arrow_left', '<i class="fas fa-long-arrow-left"></i>' ) . '</span>';
	}

	public function arrow_down( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_arrow_down', '<i class="fas fa-long-arrow-down"></i>' ) . '</span>';
	}

	public function arrow_up( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_arrow_up', '<i class="fas fa-long-arrow-up"></i>' ) . '</span>';
	}

	public function angle_right( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_arrow_right', '<i class="fas fa-angle-right"></i>' ) . '</span>';
	}

	public function angle_left( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_angle_left', '<i class="fas fa-angle-left"></i>' ) . '</span>';
	}

	public function angle_down( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_angle_down', '<i class="fas fa-angle-down"></i>' ) . '</span>';
	}

	public function angle_up( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_angle_up', '<i class="fas fa-angle-up"></i>' ) . '</span>';
	}

	public function logs( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_angle_down', '<i class="fas fa-file-alt"></i>' ) . '</span>';
	}

	public function site_dashboard( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_site_dashboard', '<i class="fas fa-columns"></i>' ) . '</span>';
	}

	public function alert( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_arrow_down', '<i class="fas fa-exclamation-circle"></i>' ) . '</span>';
	}

	public function check( $classes = null ) {
		return '<span class="dol-icon ' . $classes . '">' . apply_filters( 'dollie_icon_arrow_down', '<i class="fas fa-check"></i>' ) . '</span>';
	}
}
