<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wp_Hide_Backed_Notices
 * @subpackage Wp_Hide_Backed_Notices/admin
 * @author     WP Republic <help@wprepublic.com>
 */
class Wp_Hide_Backed_Notices_Admin {

    private $plugin_name;
    private $version;
    private $option_name = 'manage_warnings_notice';

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Add admin menu 
        add_action('admin_menu', array($this, 'add_custom_menu_in_dashboard'));
        add_shortcode('warning_notices_settings', array($this, 'warning_notices_settings'));

        // Enqueue hooks
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function add_custom_menu_in_dashboard() {
        add_menu_page('Hide Notices', 'Hide Notices', 'manage_options', 'manage_notices_settings', array($this, 'warning_notices_settings'), plugin_dir_url(__FILE__) . 'images/hide-dash-menu.png', 100);
    }

    /**
     * Settings Page Logic
     */
    public function warning_notices_settings() {
        // 1. Determine Active Tab (Fixes the bug where it always reverted to 'Settings')
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';

        // 2. Save Logic
        if (isset($_POST['save_notice_box']) && check_admin_referer('save_settings_nonce', 'save_settings_nonce_field')) {
            
            $input = isset($_POST['hide_notice']) ? $_POST['hide_notice'] : array();
            
            // Sanitize Input
            $clean_data = array();
            foreach ($input as $key => $value) {
                $clean_data[sanitize_text_field($key)] = sanitize_text_field($value);
            }

            // WP automatically serializes arrays, no need to do it manually
            update_option($this->option_name, $clean_data);
            
            echo '<div class="updated"><p>Settings Saved.</p></div>';
        }

        // 3. Retrieve Options
        $posts_from_db = get_option($this->option_name, array());
        
        // MIGRATION FIX: If old data exists as a string (double serialized), fix it on the fly
        if (is_string($posts_from_db)) {
            $posts_from_db = maybe_unserialize($posts_from_db);
        }
        
        ?>
        <div class="main-wrap setting-top-wrap">
            <div class="tab">
                <button class="hide-tablinks-notices <?php echo ($active_tab === 'settings') ? 'active' : ''; ?>" onclick="openSettings(event, 'Settings', 'settings')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/hide-setting-white.png' ?>"> Settings
                </button>
                <button class="hide-tablinks-notices <?php echo ($active_tab === 'roles') ? 'active' : ''; ?>" onclick="openSettings(event, 'User-roles', 'roles')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/hide-setting-white.png' ?>"> User roles
                </button>
                <button class="hide-tablinks-notices <?php echo ($active_tab === 'notifications') ? 'active' : ''; ?>" onclick="openSettings(event, 'Notifications', 'notifications')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/dash-hide-white.png' ?>"> Notifications
                </button>
            </div>

            <form method="POST" action="<?php echo esc_url(add_query_arg('tab', $active_tab)); ?>">
                <?php wp_nonce_field('save_settings_nonce', 'save_settings_nonce_field'); ?>
                
                <div id="Settings" class="hide-tabcontent-notices" style="display: <?php echo ($active_tab === 'settings') ? 'block' : 'none'; ?>;">
                    <h3>Select what you want to hide</h3>
                    <div class="outer-gallery-box">
                        <div class="checkboxes-manage" style="margin-top: 10px;">
                            <?php 
                            $this->render_toggle('Hide Notices', 'Hide Dashboard Notices and Warnings', $posts_from_db);
                            $this->render_toggle('Hide Updates', 'Hide WordPress Update Notices', $posts_from_db);
                            $this->render_toggle('Hide PHP Updates', 'Hide PHP Update Required Notice', $posts_from_db);
                            ?>
                        </div>
                        <div class="save_btn_wrapper"><input type="submit" name="save_notice_box" class="save_post_gallery_box_cls" value="Save"></div>
                    </div>
                </div>

                <div id="User-roles" class="hide-tabcontent-notices" style="display: <?php echo ($active_tab === 'roles') ? 'block' : 'none'; ?>;">
                    <h3>Select user roles to hide notifications for.</h3>
                    <div class="outer-gallery-box">
                        <div class="checkboxes-manage" style="margin-top: 10px;">
                            <?php
                            $roles = wp_roles()->get_names();
                            foreach ($roles as $role_key => $role_name) {
                                $checked = (isset($posts_from_db[$role_key])) ? 'checked' : '';
                                echo '<h4>Enable for <b>' . esc_html($role_name) . '</b> role</h4>';
                                echo '<label class="switch">
                                        <input class="styled-checkbox" ' . $checked . ' name="hide_notice[' . esc_attr($role_key) . ']" type="checkbox" value="' . esc_attr($role_key) . '">
                                        <span class="slider round"></span>
                                      </label>';
                            }
                            ?>
                        </div>
                        <div class="save_btn_wrapper"><input type="submit" name="save_notice_box" class="save_post_gallery_box_cls" value="Save"></div>
                    </div>
                </div>
            </form>

            <div id="Notifications" class="hide-tabcontent-notices" style="display: <?php echo ($active_tab === 'notifications') ? 'block' : 'none'; ?>;">
                <h3>Dashboard notifications</h3>
                <?php
                if (isset($posts_from_db['Hide_Notices']) || in_array('Hide Notices', $posts_from_db)) {
                    echo '<div class="hide-notices-log-viewer">';
                    do_action('admin_notices');
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Helper to render toggles to keep code clean
     */
    private function render_toggle($key, $label, $options) {
        // Handle legacy array keys vs new sanitized keys
        $db_key = str_replace(' ', '_', $key); 
        $is_checked = (in_array($key, $options) || isset($options[$db_key])) ? 'checked' : '';
        
        echo '<h4>' . esc_html($label) . '</h4>';
        echo '<label class="switch">
                <input class="styled-checkbox" ' . $is_checked . ' name="hide_notice[' . esc_attr($db_key) . ']" type="checkbox" value="' . esc_attr($key) . '">
                <span class="slider round"></span>
              </label>';
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'css/wp-hide-backed-notices-admin.css', array(), $this->version);

        // Inject Dynamic CSS here instead of raw echo
        $custom_css = $this->generate_hiding_css();
        if ($custom_css) {
            wp_add_inline_style($this->plugin_name . '-admin', $custom_css);
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'js/wp-hide-backed-notices-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Logic to determine WHICH CSS to inject
     */
    private function generate_hiding_css() {
        $user = wp_get_current_user();
        $user_roles = (array) $user->roles;
        
        // Retrieve Options
        $options = get_option($this->option_name, array());
        if (is_string($options)) $options = maybe_unserialize($options); // Migration safety
        
        if (empty($options)) return '';

        // Check if current user role is targeted
        $should_hide = false;
        foreach ($user_roles as $role) {
            if (isset($options[$role]) || in_array($role, $options)) {
                $should_hide = true;
                break;
            }
        }
        
        // Administrator override logic from original code (if specific 'administrator' key is checked)
        if (in_array('administrator', $user_roles) && (isset($options['administrator']) || in_array('administrator', $options))) {
            $should_hide = true;
        }

        if (!$should_hide) return '';

        $css = '';

        // Hide Update Notifications
        if (isset($options['Hide_Updates']) || in_array('Hide Updates', $options)) {
            $css .= 'body.wp-admin .update-plugins, body.wp-admin .awaiting-mod, body.wp-admin #wp-admin-bar-updates { display: none !important; }';
        }

        // Hide General Notices
        if (isset($options['Hide_Notices']) || in_array('Hide Notices', $options)) {
            $css .= 'body.wp-admin #wp-admin-bar-seedprod_admin_bar, body.wp-admin .update-nag, body.wp-admin .updated, body.wp-admin .error, body.wp-admin .is-dismissible, body.wp-admin .notice, #yoast-indexation-warning { display: none !important; }';
            // Exception for Loco Translate as per original code
            $css .= 'body.wp-admin #loco-content .notice, body.wp-admin #loco-notices .notice { display: block !important; }';
        }

        // Hide PHP Updates
        if (isset($options['Hide_PHP_Updates']) || in_array('Hide PHP Updates', $options)) {
            $css .= '#dashboard_php_nag { display: none !important; }';
        }

        return $css;
    }
}
