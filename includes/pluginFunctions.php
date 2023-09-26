<?php
if (!defined('ABSPATH'))
    exit;

add_action("wp_enqueue_scripts", "include_assets_enqueue_front_scripts", 5);
if (!function_exists("include_assets_enqueue_front_scripts")) {

    function include_assets_enqueue_front_scripts() {
        global $post;
        if (is_a($post, "WP_Post")) {
            $url = sanitize_text_field($post->post_name);
            $type = "frontend";
            include_assets_enqueu_scripts_fun($url, $type);
        }
    }

}
add_action("admin_enqueue_scripts", "include_assets_enqueue_back_scripts");

if (!function_exists("include_assets_enqueue_back_scripts")) {

    function include_assets_enqueue_back_scripts() {
        global $pagenow;
        // $i = 1;
        $type = "backend";
        include_assets_enqueu_scripts_fun($pagenow, $type);
    }
}

if (!function_exists("include_assets_enqueu_scripts_fun")) {

    function include_assets_enqueu_scripts_fun($url, $type) {
        $i = 1;
        $counter = 0;
        $page_url = "";
        $found = "";
        if ($type == "backend") {
            $page_url = $_SERVER["REQUEST_URI"];
        }
        $result_db = include_assets_get_results($type);

        if (!empty($result_db)) {
            foreach ($result_db as $row) {
                $slugs = explode(",", $row->slugs);
                $slugs = array_map("trim", $slugs);

                if ($type == "backend") {
                    foreach ($slugs as $sub_str) {
                        if ($row->inclusion == 1 || ($row->inclusion == 2 && str_contains($page_url, $sub_str)))
                        {
                            $found = "yes";
                        } elseif ($row->inclusion == 3) {
                            if (str_contains($page_url, $sub_str)) {
                                $found = "No";
                                break;
                            } else {
                                $found = "yes";
                            }
                        }
                    }
                    if ($found == "yes") {
                        include_assets_add_script_header_footer($row, $i);
                        $i++;
                        $found = "No";
                    }
                } elseif ($type == "frontend") {
                    if (
                            $row->inclusion == 1 ||
                            ($row->inclusion == 2 && in_array($url, $slugs)) ||
                            ($row->inclusion == 3 && !in_array($url, $slugs))
                    ) {
                        include_assets_add_script_header_footer($row, $i);
                        $i++;
                    }
                }
            }
        }
    }

}

if (!function_exists("include_assets_add_script_header_footer")) {

    function include_assets_add_script_header_footer($row, $i) {
        if ($row->location == "Footer") {
            if ($row->type == "JS") {
                wp_enqueue_script(
                        "JS" . $i . "",
                        $row->script_url,
                        ["jquery"],
                        null,
                        true
                );
            } elseif ($row->type == "CSS") {
                wp_enqueue_style("CSS" . $i . "", $row->script_url);
            }
        } elseif ($row->location == "Header") {
            if ($row->type == "JS") {
                wp_enqueue_script("JS" . $i . "", $row->script_url, ["jquery"]);
            } elseif ($row->type == "CSS") {
                wp_enqueue_style("CSS" . $i . "", $row->script_url);
            }
        }
    }

}

if (!function_exists("include_assets_get_results")) {

    function include_assets_get_results($type) {
        global $wpdb;
        $result_final = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *, GROUP_CONCAT(page_slug) as slugs
                FROM site_scripts 
                INNER JOIN exclude_scripts 
                ON site_scripts.id = exclude_scripts.script_id
                where script_type = %s
                GROUP BY script_id 
                ORDER BY sortOrder ASC",
                $type
            )
        );

        return $result_final;
    }

}
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_GET['page']) &&  $_GET['page']== 'include-assets') {
    //Sanitize input data
    $url = sanitize_text_field($_POST["url"]);
    $inclusion = intval($_POST["inclusion"]);
    $form_id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    $type = sanitize_text_field($_POST["type"]);
    $pages_slugs = sanitize_text_field($_POST["pages_slugs"]);
    $script_type = sanitize_text_field($_POST["script_type"]);
    $location = sanitize_text_field($_POST["location"]);
    $sortOrder = intval($_POST["sortOrder"]);
    $table_name = "site_scripts";
    $table_name_exclude = "exclude_scripts";
    $file_name = $url;
    $table = "exclude_scripts";
    if (!empty($form_id)) {
        global $wpdb;
        $dbData = [];
        $dbData["sortOrder"] = $sortOrder;
        $dbData["script_url"] = $url;
        $dbData["script_type"] = $script_type;
        $dbData["type"] = $type;
        $dbData["location"] = $location;
        $dbData["inclusion"] = $inclusion;

        $wpdb->update($table_name, $dbData, ["id" => $form_id]);
        $res = $wpdb->delete($table, ["script_id" => $form_id]);
        $result_final = $wpdb->get_results(
                "
                SELECT * 
                FROM site_scripts where script_url = '" .
                $url .
                "' AND script_type = '" .
                $script_type .
                "'
            "
        );
        $pages_slugs = explode("\n", $pages_slugs);
        $script_id = $result_final[0]->id;
        for ($i = 0; $i < count($pages_slugs); $i++) {
            $result = $wpdb->insert($table_name_exclude, [
                "script_id" => $script_id,
                "page_slug" => $pages_slugs[$i],
            ]);
        }
    } else {
        $result = $wpdb->insert($table_name, [
            "script_url" => $url,
            "type" => $type,
            "script_type" => $script_type,
            "location" => $location,
            "sortOrder" => $sortOrder,
            "inclusion" => $inclusion,
        ]);

        $query_run = $wpdb->prepare(
                "SELECT * FROM site_scripts WHERE script_url = %s AND script_type = %s ORDER BY sortOrder ASC",
                $url,
                $script_type
        );

        $result_final = $wpdb->get_results($query_run);

        $pages_slugs = explode("\n", $pages_slugs);
        $script_id = $result_final[0]->id;
        for ($i = 0; $i < count($pages_slugs); $i++) {
            $result = $wpdb->insert($table_name_exclude, [
                "script_id" => $script_id,
                "page_slug" => $pages_slugs[$i],
            ]);
        }
    }
}
if (isset($_GET["file_id"])) {
    global $wpdb;
    $url = get_admin_url() . "admin.php?page=include-assets";
    $table = "site_scripts";
    $table_exclude = "exclude_scripts";
    $id = intval($_GET["file_id"]); // Sanitize user input

    // Prepare and execute the delete queries
    $res = $wpdb->delete($table_exclude, ["script_id" => $id]);
    $res = $wpdb->delete($table, ["id" => $id]);
    if ($_GET["file_id"]) {
        header("Location: " . $url . "");
    }
}

if (isset($_GET["edit_file_id"])) {
    $id = $_GET["edit_file_id"];
    $table = "exclude_scripts";
    $result_final = $wpdb->get_results(
            "
            SELECT exclude_scripts.page_slug, site_scripts.script_url, site_scripts.id, site_scripts.type, site_scripts.script_type, site_scripts.location, site_scripts.sortOrder, site_scripts.inclusion FROM exclude_scripts 
            JOIN site_scripts
            ON exclude_scripts.script_id = site_scripts.id
            where script_id = '" .
            $id .
            "' "
    );
    if ($result_final[0]->script_type == "frontend") {
        $result_final = json_encode((array) $result_final);
        print_r($result_final);
    } elseif ($result_final[0]->script_type == "backend") {
        $result_final = json_encode((array) $result_final);
        print_r($result_final);
    }
    exit();
}

if (!function_exists("include_assets_advance_tab")) {

    function include_assets_advance_tab() {
        ?>

        <a class="advance-options">Advance Options<i class="fa fa-sub"></i><i class="fa fa-plus"></i></a>
      <div class="panel">
    <h5><?php esc_html_e('Exclude Scripts from pages:', 'include-assets'); ?></h5>
    <label><input type="radio" class="radio-options" id="1" name="inclusion" value="1" checked> 
        <?php esc_html_e('Include on entire site', 'include-assets'); ?><br></label>
    <label><input type="radio" class="radio-options" id="2" name="inclusion" value="2"> 
        <?php esc_html_e('Include on Specific Pages', 'include-assets'); ?><br></label>
    <label><input type="radio" class="radio-options" id="3" name="inclusion" value="3"> 
        <?php esc_html_e('Exclude from pages', 'include-assets'); ?><br></label>
    <textarea id="txtid" name="pages_slugs" rows="4" cols="50" placeholder="<?php esc_attr_e('Enter pages slugs', 'include-assets'); ?>"></textarea>
    <p class="include-assets-note"><?php esc_html_e('NOTE: One line per page slug.', 'include-assets'); ?></p>
</div>

        <?php
    }

}

if (!function_exists("include_assets_main_form")) {

    function include_assets_main_form($location) {
        ?>
        <div class="row include-assests-ar">
            <div class="include-asstes-col-3">
                <input type="text" name="url" class="url" placeholder="Enter script URL"> 
            </div>
            <div class="include-asstes-col-3" style="display:none;">
                <input type="text" name="id" class="id"> 
            </div>
            <div class="include-asstes-col-2 type">
                <select class="form-control type" name="type">
                    <option><?php esc_html_e('CSS', 'include-assets'); ?></option>
                    <option><?php esc_html_e('JS', 'include-assets'); ?></option>
                </select>
            </div>
            <div class="include-asstes-col-2 location">
                <select class="form-control location" name="location">
                    <option>Please select Location</option>
                    <option><?php esc_html_e('Header', 'include-assets'); ?></option>
                    <option><?php esc_html_e('Footer', 'include-assets'); ?></option>
                </select>
            </div>
            <div class="include-asstes-col-1">
                <input type="number" name="sortOrder" class="sortOrder" placeholder="Enter Sort Order"> 
            </div>
            <input type="hidden" name="script_type" value="<?php echo $location; ?>"> 
            <div class="col-md-2 add-script-button">
                <input type="submit" id="btn-s" value="Add Script" disabled> 
            </div>
            <div class="col-md-2 add-script-button">
                <input type="button" class="<?php echo esc_attr($location); ?>" id="btn-cancel" value="Cancel">   
            </div>
        </div>
        <?php
    }

}

