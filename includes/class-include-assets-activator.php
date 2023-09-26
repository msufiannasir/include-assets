<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://techsolsint.com/
 * @since      1.0.0
 *
 * @package    Include-Assets
 * @subpackage Include-Assets/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Include-Assets
 * @subpackage Include-Assets/includes
 * @author     Techsols <techsols@gmail.com>
 */
class Include_Assets_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		 global $wpdb;
    $table1_name = 'exclude_scripts';
    $table2_name = 'site_scripts';

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table1_name)) != $table1_name) {
        // SQL query to create table
        $sql = "CREATE TABLE $table1_name  ( 
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `script_id` int(11) NOT NULL,
            `page_slug` text NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table2_name)) != $table2_name) {
        $sql2 = "CREATE TABLE $table2_name (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `script_url` text NOT NULL,
                  `type` varchar(50) NOT NULL,
                  `script_type` text NOT NULL,
                  `location` text NOT NULL,
                  `sortOrder` int(11) NOT NULL,
                  `inclusion` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql2);
    }

	}

}
