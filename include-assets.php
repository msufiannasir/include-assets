<?php
/**
 * Plugin Name: Include Assets
 * Plugin URI:https://techsolsint.com/include-assets/
 * Description: This plugin allows you to include assets on your WordPress site, both in the backend and frontend, without editing code.
 * Author: Techsols
 * Author URI::https://techsolsint.com/
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
define('INCLUDE_ASSETS_VERSION', '1.0.0');

/* include pluginfunciton.php file */
include( plugin_dir_path(__FILE__) . '/includes/pluginFunctions.php');

    /**
 * Enqueue CSS and JavaScript files on the plugin's admin page.
 */
function include_assets_enqueue_files() {
    if (!empty($_GET['page']) && $_GET['page'] === 'include-assets') {
        wp_enqueue_style('include-assets-css', plugins_url('css/include_asset_style.css', __FILE__));
        wp_enqueue_script('include-assets-js', plugins_url('js/custom.js', __FILE__), array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'include_assets_enqueue_files');



    /**
 * Activation hook: Create necessary database tables.
 */
function activate_include_assets() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-include-assets-activator.php';
    Include_Assets_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_include_assets');
/**
 * Uninstall hook: Remove database tables.
 */
function include_assets_fn_uninstall() {
    global $wpdb;
   // User-supplied input or any variable you want to use in the query
   $table_names = 'exclude_scripts,site_scripts'; 

// Split the table names into an array and sanitize each one
$tables = array_map('sanitize_key', explode(',', $table_names));

// Build the SQL query dynamically
$query = "DROP TABLE IF EXISTS ";
$query .= implode(', ', $tables);

// Execute the query
$wpdb->query($query);

}

register_uninstall_hook(__FILE__, 'include_assets_fn_uninstall');


    /**
 * Create the admin menu for the plugin.
 */

add_action('admin_menu', 'include_assets_plugin_setup_menu');
if (!function_exists('include_assets_plugin_setup_menu')) {

    function include_assets_plugin_setup_menu() {
        add_menu_page('Include Assets', 'Include Assets', 'manage_options', 'include-assets', 'init_include_assets');
    }

}
    
/**
 * Callback function for the plugin's admin page.
 */
function init_include_assets() {
    ?>
    <form id="frontend" method="POST" action="<?php echo get_admin_url() . '/admin.php?page=include-assets'; ?>">
        <div class="wrap front-end">
             <h1><?php esc_html_e('Frontend Scripts', 'include-assets'); ?></h1>

        <?php
        $location = 'frontend';
        include_assets_main_form($location);
        ?>
        </div>
    <?php
    include_assets_advance_tab();
    ?>
    </form>

    <div clas="front-end-scripts-in-table">
        <?php
        global $wpdb;

         $script_type = 'frontend'; 

        $prepared_query = $wpdb->prepare(
            "SELECT * FROM site_scripts WHERE script_type = %s ORDER BY sortOrder ASC",
            $script_type
        );

        $result = $wpdb->get_results($prepared_query);

        ?>
        <table class="include-assets-tb">
            <thead>
                <tr>
                    <th class="first-col"><?php esc_html_e('Script URL', 'include-assets'); ?></th>
                    <th class="sec-col"><?php esc_html_e('Type ', 'type'); ?></th>
                    <th class="third-col"><?php esc_html_e('Script Type', 'include-assets'); ?></th>
                    <th class="fourth-col"><?php esc_html_e('Location', 'include-assets'); ?></th>
                    <th class="fifth-col"><?php esc_html_e('Sort Order', 'include-assets'); ?></th>
                </tr>
            </thead>
            <tbody>
    <?php
    if (empty($result)) {
        ?>
                    <tr>    
                        <td colspan="5"><p><?php esc_html_e('No Data Found', 'include-assets'); ?><p></td>
                    </tr>
                    <?php
                } else {
                    foreach ($result as $row) {
                        ?>
                        <tr>
                            <td><p class="include-assets-first-res"><a class="delete-script" href="">X</a><a class="edit-script" id="frontend" href=""><?php echo esc_html_e('Edit', 'include-assets') ?></a> <span class="id-sect" style="display:none"><?php echo $row->id; ?></span> <?php echo esc_url($row->script_url); ?><span></p></td>
                            <td><p><?php echo esc_html($row->type); ?><p></td>
                            <td><p><?php echo esc_html($row->script_type); ?><p></td>
                            <td><p><?php echo esc_html($row->location); ?><p></td>
                            <td><p><?php echo esc_html($row->sortOrder); ?><p></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <hr />
    <form id="backend" method="POST" name="backend_assets_form" action="<?php echo get_admin_url() . '/admin.php?page=include-assets'; ?>">
        <div class="wrap back-end">
             <h1><?php esc_html_e('Backend Scripts', 'include-assets'); ?></h1>

            <?php
            $location = 'backend';
            include_assets_main_form($location);
            ?>
        </div>
        <?php
        include_assets_advance_tab();
        ?>
    </form>
    <div class="Back-end-scripts-in-table">
        <?php
        global $wpdb;

        $script_type = 'backend';

        $include_assets_sql = $wpdb->prepare("
        SELECT * 
        FROM site_scripts 
        WHERE script_type = %s 
        ORDER BY sortOrder ASC
        ", $script_type);

        $result = $wpdb->get_results($include_assets_sql);
        ?>
        <table class="include-assets-tb">
            <thead>
                <tr>
                    <th class="first-col"><?php esc_html_e('Script URL', 'include-assets'); ?></th>
                    <th class="sec-col"><?php esc_html_e('Type ', 'include-assets'); ?></th>
                    <th class="third-col"><?php esc_html_e('Script Type', 'include-assets'); ?></th>
                    <th class="fourth-col"><?php esc_html_e('Location', 'include-assets'); ?></th>
                    <th class="fifth-col"><?php esc_html_e('Sort Order', 'include-assets'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($result)) {
                    ?>
                    <tr>    
                        <td colspan="5"><p><?php esc_html_e('No Data Found', 'include-assets'); ?><p></td>
                    </tr>
                    <?php
                } else {
                    foreach ($result as $row) {
                        ?>
                        <tr>
                            <td><p class="include-assets-first-res"><a class="delete-script" href="">X</a><a class="edit-script" id="backend" href="">Edit</a> <span class="id-sect" style="display:none"><?php echo $row->id; ?></span> <?php echo esc_url($row->script_url); ?><span></p></td>
                            <td><p><?php echo esc_html($row->type); ?><p></td>
                            <td><p><?php echo esc_html($row->script_type); ?><p></td>
                            <td><p><?php echo esc_html($row->location); ?><p></td>
                            <td><p><?php echo esc_html($row->sortOrder); ?><p></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <hr />
    <?php
}