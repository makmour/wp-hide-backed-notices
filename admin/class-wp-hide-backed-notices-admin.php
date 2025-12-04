<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wprepublic.com/
 * @since      1.1.0
 *
 * @package    Wp_Hide_Backed_Notices
 * @subpackage Wp_Hide_Backed_Notices/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Hide_Backed_Notices
 * @subpackage Wp_Hide_Backed_Notices/admin
 * @author     WP Republic <help@wprepublic.com>
 */
class Wp_Hide_Backed_Notices_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Add admin menu 
        add_action('admin_menu', array($this, 'add_custom_menu_in_dashboard'));
        add_shortcode('warning_notices_settings', array($this, 'warning_notices_settings'));

        add_action('admin_enqueue_scripts', array($this, 'hk_ds_admin_theme_style'));
        add_action('login_enqueue_scripts', array($this, 'hk_ds_admin_theme_style'));
    }

    public function add_custom_menu_in_dashboard() {

        // Create a custom capability 
        $capability = 'manage_options';

        add_menu_page('Hide Notices', 'Hide Notices', $capability, 'manage_notices_settings', array($this, 'warning_notices_settings'), plugin_dir_url(__FILE__) . 'images/hide-dash-menu.png', 100);
    }

    public function warning_notices_settings() {
        if (isset($_POST['save_settings_nonce_field']) && wp_verify_nonce($_POST['save_settings_nonce_field'], 'save_settings_nonce')) {

            if (isset($_POST['save_notice_box'])) {
                $roles = wp_roles()->get_names();
                foreach ($roles as $role_val => $role) {
                    if (empty($_POST['hide_notice'][$role_val])) {
                        $_POST['hide_notice'][$role_val] = "All Users";
                    }
                }
                $manage_warnings_notice = serialize($_POST['hide_notice']);
                update_option('manage_warnings_notice', $manage_warnings_notice);
                echo "<meta http-equiv='refresh' content='0'>";
            }
        }

        $custom_post_data = get_option('manage_warnings_notice');
        if (!empty($custom_post_data)) {
            $posts_from_db = unserialize($custom_post_data);
        }
        ?>
        <div class="main-wrap setting-top-wrap">
            <div class="tab">

                <?php
                $settings = '';
                $settings_dis = 'none';
                if (empty($_GET['tab']) || $_GET['tab'] == 'settings') {
                    $settings = 'active';
                    $settings_dis = 'block';
                }
                ?>
                <button class="hide-tablinks-notices <?php echo $settings; ?>" onclick="openSettings(event, 'Settings', 'settings')" id="defaultOpen">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/hide-setting-white.png' ?>">
                    Settings
                </button>
                <?php
                $roles = '';
                $roles_dis = 'none';
                if (isset($_GET['tab'])) {
                    if ($_GET['tab'] == 'roles') {
                        $roles = 'active';
                        $roles_dis = 'block';
                    }
                }
                ?>
                <button class="hide-tablinks-notices <?php echo $roles; ?>" onclick="openSettings(event, 'User-roles', 'roles')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/hide-setting-white.png' ?>">
                    User roles
                </button>
                <?php
                $notifications = '';
                $notifications_dis = 'none';
                if (isset($_GET['tab'])) {
                    if ($_GET['tab'] == 'notifications') {
                        $notifications = 'active';
                        $notifications_dis = 'block';
                    }
                }
                ?>
                <button class="hide-tablinks-notices <?php echo $notifications; ?>" onclick="openSettings(event, 'Notifications', 'notifications')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/dash-hide-white.png' ?>">
                    Notifications
                </button>
            </div>
            <form method="POST" class="gallery_meta_form" id="gallery_meta_form_id">
                <?php wp_nonce_field('save_settings_nonce', 'save_settings_nonce_field'); ?>
                <div id="Settings" class="hide-tabcontent-notices" style="display: <?php echo $settings_dis; ?>;">
                    <h3>Select what you want to hide</h3>
                    <div class="outer-gallery-box">
                        <div class="checkboxes-manage" style="margin-top: 10px;">
                            <?php
                            $checked_notice = '';
                            if (!empty($posts_from_db) && $posts_from_db != '') {
                                if (in_array('Hide Notices', $posts_from_db)) {
                                    $checked_notice = 'checked';
                                } else {
                                    $checked_notice = '';
                                }
                            }
                            ?>
                            <h4>Hide Dashboard Notices and Warnings</h4>
                            <label class="switch">
                                <input class="styled-checkbox" <?php echo $checked_notice; ?> id="Hide-Notices" name="hide_notice[Hide_Notices]" type="checkbox" value="Hide Notices">
                                <span class="slider round"></span>
                            </label>
                            <?php
                            $checked_update = '';
                            if (!empty($posts_from_db) && $posts_from_db != '') {
                                if (in_array('Hide Updates', $posts_from_db)) {
                                    $checked_update = 'checked';
                                } else {
                                    $checked_update = '';
                                }
                            }
                            ?>
                            <h4>Hide WordPress Update Notices</h4>
                            <label class="switch">
                                <input class="styled-checkbox" <?php echo $checked_update; ?> id="Hide-Updates" name="hide_notice[Hide_Updates]" type="checkbox" value="Hide Updates">
                                <span class="slider round"></span>
                            </label>
                            <?php
                            $checked_update = '';
                            if (!empty($posts_from_db) && $posts_from_db != '') {
                                if (in_array('Hide PHP Updates', $posts_from_db)) {
                                    $checked_update = 'checked';
                                } else {
                                    $checked_update = '';
                                }
                            }
                            ?>
                            <h4> Hide PHP Update Required Notice</h4>
                            <label class="switch">
                                <input class="styled-checkbox" <?php echo $checked_update; ?> id="hide-php-updates" name="hide_notice[Hide_PHP_Updates]" type="checkbox" value="Hide PHP Updates">
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="save_btn_wrapper">
                            <input type="submit" name="save_notice_box" id="save_post_gallery_box_id" class="save_post_gallery_box_cls" value="Save">
                        </div>
                    </div>
                </div>

                <!--User Role Tab-->
                <div id="User-roles" class="hide-tabcontent-notices" style="display: <?php echo $roles_dis; ?>;">
                    <h3>Select user role for whome you want to hide notifications.</h3>
                    <div class="outer-gallery-box">
                        <div class="checkboxes-manage" style="margin-top: 10px;">
                            <!-- for adminstrators -->
                            <?php
                            $roles = wp_roles()->get_names();
                            foreach ($roles as $role_val => $role) {
                                $AccessedBy = '';
                                if (!empty($posts_from_db) && $posts_from_db != '') {
                                    if (in_array($role_val, $posts_from_db)) {
                                        $AccessedBy = 'checked';
                                    } else {
                                        $AccessedBy = '';
                                    }
                                }
                                ?>
                                <h4>Enable for <b><?php echo $role; ?></b> role</h4>
                                <label class="switch">
                                    <input class="styled-checkbox" <?php echo $AccessedBy; ?> class="Hide-Accesse" name="hide_notice[<?php echo $role_val; ?>]" type="checkbox" value="<?php echo $role_val; ?>">
                                    <span class="slider round"></span>
                                </label>
                                <?php
                            }
                            ?>
                            <!-- end roles -->
                        </div>
                        <div class="save_btn_wrapper">
                            <input type="submit" name="save_notice_box" id="save_post_gallery_box_id" class="save_post_gallery_box_cls" value="Save">
                        </div>
                    </div>
                </div>
            </form>
            <!--Notification Tab-->
            <div id="Notifications" class="hide-tabcontent-notices" style="display: <?php echo $notifications_dis; ?>;">
                <h3>Dashboard notifications</h3>
                <?php
                if (!empty($posts_from_db) && $posts_from_db != '') {
                    if (in_array('Hide Notices', $posts_from_db)) {
                        do_action('admin_notices');
                    }
                }
                ?>
            </div>
            <!-- end  -->
        </div>
        <?php
    }

    // Hide warnings from the wordpress backend
    public function hk_ds_admin_theme_style() {
        $roles = wp_roles()->get_names();
        $user_role_val_list = [];
        $user_role_name_list = [];
        foreach ($roles as $role_val => $role) {
            $user_role_name_list[] = $role;
            $user_role_val_list[] = $role_val;
        }
        $custom_post_data = get_option('manage_warnings_notice');
        $user = wp_get_current_user();
        $CurentUserRoles = $user->roles;
        $CurentUserRoles = array_shift($CurentUserRoles);
        if (!empty($custom_post_data) && $custom_post_data != '') {
            $posts_from_db = unserialize($custom_post_data);
            if (!empty($posts_from_db)) {
                $role_val = array_values(array_filter($posts_from_db));
                $role_val_cnt = count($role_val);
                $role_type = [];
                for ($i = 0; $i < $role_val_cnt; $i++) {
                    if (!empty($role_val[$i]) && (in_array($role_val[$i], $user_role_val_list) || $CurentUserRoles == 'administrator')) {
                        $role_type[] = $role_val[$i];
                    } else {
                        $role_type[] = "";
                    }
                }
                $role_type = array_values(array_filter($role_type));
                if (!empty($role_type)) {
                    $role_type_cnt = count($role_type);
                    for ($j = 0; $j < $role_type_cnt; $j++) {
                        if ($role_type[$j] == $CurentUserRoles) {
                            // Hide Update notifications
                            if (in_array('Hide Updates', $posts_from_db)) {
                                echo '<style>
                                body.wp-admin .update-plugins, body.wp-admin .awaiting-mod, 
                                body.wp-admin #wp-admin-bar-updates {display: none !important;} 
                            </style>';
                            }
                            // Hide notices from the wordpress backend
                            if (in_array('Hide Notices', $posts_from_db)) {
                                echo '<style>  body.wp-admin #wp-admin-bar-seedprod_admin_bar, body.wp-admin .update-nag, body.wp-admin .updated, body.wp-admin .error, body.wp-admin .is-dismissible, body.wp-admin .notice, #yoast-indexation-warning{display: none !important;}body.wp-admin #loco-content .notice,body.wp-admin #loco-notices .notice{display:block !important;} </style>';
                            }

                            // Hide PHP Updates from the wordpress backend
                            if (in_array('Hide PHP Updates', $posts_from_db)) {
                                echo '<style> #dashboard_php_nag {display:none;} </style>';
                            }
                        }
                    }
                } else {
                    // Hide Update notifications
                    if (in_array('Hide Updates', $posts_from_db)) {
                        echo '<style>  body.wp-admin .update-plugins,body.wp-admin .awaiting-mod,  body.wp-admin #wp-admin-bar-updates {display: none !important;} </style>';
                    }

                    // Hide notices from the wordpress backend
                    if (in_array('Hide Notices', $posts_from_db)) {
                        echo '<style> body.wp-admin #wp-admin-bar-seedprod_admin_bar, body.wp-admin .update-nag,  body.wp-admin .updated,  body.wp-admin .error,  body.wp-admin .is-dismissible,  body.wp-admin .notice,  #yoast-indexation-warning{display: none !important;} body.wp-admin #loco-content .notice,  body.wp-admin #loco-notices .notice{display:block !important;}  </style>';
                    }

                    // Hide PHP Updates from the wordpress backend
                    if (in_array('Hide PHP Updates', $posts_from_db)) {
                        echo '<style>#dashboard_php_nag {display:none;}</style>';
                    }
                }
            }
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wp_Hide_Backed_Notices_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wp_Hide_Backed_Notices_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style('wp-hide-backed-notices-admin-css', plugin_dir_url(__FILE__) . 'css/wp-hide-backed-notices-admin.css', '', '1.2.3');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wp_Hide_Backed_Notices_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wp_Hide_Backed_Notices_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script('wp-hide-backed-notices-admin-js', plugin_dir_url(__FILE__) . 'js/wp-hide-backed-notices-admin.js', '', '1.2.3');
    }

}
