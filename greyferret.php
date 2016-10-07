<?php

/**
 * @package Greyferret
 * @version 1.0.0
 */
/*
  Plugin Name: Greyferret
  Description: This plugin is used to connect the Woocommerce with Greyferret api. This plugin requires Woocommerce plugin to be activated first.
  Version: 1.0.0
 */

class greyFerret {

    function __construct() {
		register_deactivation_hook( __FILE__, array($this, 'myplugin_deactivate') );
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            require_once 'admin/classAdminpages.php';
            require_once 'hooks/classProductpublish.php';
        }else{
			deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }
	
	public function myplugin_deactivate() {
		delete_option('jobStatus');
	}

}

new greyFerret;
