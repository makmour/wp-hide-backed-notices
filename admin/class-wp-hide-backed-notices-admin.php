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

        add_action('admin_menu', array($this, 'add_custom_menu_in_dashboard'));
        add_shortcode('warning_notices_settings', array($this, 'warning_notices_settings'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function add_custom_menu_in_dashboard() {
        add_menu_page(
            __('Hide Notices', 'wp-hide-backed-notices'), 
            __('Hide Notices', 'wp-hide-backed-notices'), 
            'manage_options', 
            'manage_notices_settings', 
            array($this, 'warning_notices_settings'), 
            plugin_dir_url(__FILE__) . 'images/hide-dash-menu.png', 
            100
        );
    }

    public function warning_notices_settings() {
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';

        if (isset($_POST['save_notice_box']) && check_admin_referer('save_settings_nonce', 'save_settings_nonce_field')) {
            $input = isset($_POST['hide_notice']) ? $_POST['hide_notice'] : array();
            $clean_data = array();
            foreach ($input as $key => $value) {
                $clean_data[sanitize_text_field($key)] = sanitize_text_field($value);
            }
            update_option($this->option_name, $clean_data);
            echo '<div class="updated"><p>' . esc_html__('Settings Saved.', 'wp-hide-backed-notices') . '</p></div>';
        }

        $posts_from_db = get_option($this->option_name, array());
        if (is_string($posts_from_db)) {
            $posts_from_db = maybe_unserialize($posts_from_db);
        }
        ?>
        <div class="main-wrap setting-top-wrap">
            <div class="tab">
                <button class="hide-tablinks-notices <?php echo ($active_tab === 'settings') ? 'active' : ''; ?>" onclick="openSettings(event, 'Settings', 'settings')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/hide-setting-white.png' ?>"> <?php esc_html_e('Settings', 'wp-hide-backed-notices'); ?>
                </button>
                <button class="hide-tablinks-notices <?php echo ($active_tab === 'roles') ? 'active' : ''; ?>" onclick="openSettings(event, 'User-roles', 'roles')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/hide-setting-white.png' ?>"> <?php esc_html_e('User roles', 'wp-hide-backed-notices'); ?>
                </button>
                <button class="hide-tablinks-notices <?php echo ($active_tab === 'notifications') ? 'active' : ''; ?>" onclick="openSettings(event, 'Notifications', 'notifications')">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/dash-hide-white.png' ?>"> <?php esc_html_e('Notifications', 'wp-hide-backed-notices'); ?>
                </button>
            </div>

            <form method="POST" action="<?php echo esc_url(add_query_arg('tab', $active_tab)); ?>">
                <?php wp_nonce_field('save_settings_nonce', 'save_settings_nonce_field'); ?>
                
                <div id="Settings" class="hide-tabcontent-notices" style="display: <?php echo ($active_tab === 'settings') ? 'block' : 'none'; ?>;">
                    <h3><?php esc_html_e('Select what you want to hide', 'wp-hide-backed-notices'); ?></h3>
                    <div class="outer-gallery-box">
                        <div class="checkboxes-manage" style="margin-top: 10px;">
                            <?php 
                            $this->render_toggle('Hide Notices', __('Hide Dashboard Notices and Warnings', 'wp-hide-backed-notices'), $posts_from_db);
                            $this->render_toggle('Hide Updates', __('Hide WordPress Update Notices', 'wp-hide-backed-notices'), $posts_from_db);
                            $this->render_toggle('Hide PHP Updates', __('Hide PHP Update Required Notice', 'wp-hide-backed-notices'), $posts_from_db);
                            ?>
                        </div>
                        <div class="save_btn_wrapper"><input type="submit" name="save_notice_box" class="save_post_gallery_box_cls" value="<?php esc_attr_e('Save', 'wp-hide-backed-notices'); ?>"></div>
                    </div>
                </div>

                <div id="User-roles" class="hide-tabcontent-notices" style="display: <?php echo ($active_tab === 'roles') ? 'block' : 'none'; ?>;">
                    <h3><?php esc_html_e('Select user roles to hide notifications for.', 'wp-hide-backed-notices'); ?></h3>
                    <div class="outer-gallery-box">
                        <div class="checkboxes-manage" style="margin-top: 10px;">
                            <?php
                            $roles = wp_roles()->get_names();
                            foreach ($roles as $role_key => $role_name) {
                                $checked = (isset($posts_from_db[$role_key])) ? 'checked' : '';
                                // Translators: %s is the name of the user role (e.g. Administrator)
                                echo '<h4>' . sprintf(esc_html__('Enable for %s role', 'wp-hide-backed-notices'), '<b>' . esc_html($role_name) . '</b>') . '</h4>';
                                echo '<label class="switch">
                                        <input class="styled-checkbox" ' . $checked . ' name="hide_notice[' . esc_attr($role_key) . ']" type="checkbox" value="' . esc_attr($role_key) . '">
                                        <span class="slider round"></span>
                                      </label>';
                            }
                            ?>
                        </div>
                        <div class="save_btn_wrapper"><input type="submit" name="save_notice_box" class="save_post_gallery_box_cls" value="<?php esc_attr_e('Save', 'wp-hide-backed-notices'); ?>"></div>
                    </div>
                </div>
            </form>

            <div id="Notifications" class="hide-tabcontent-notices" style="display: <?php echo ($active_tab === 'notifications') ? 'block' : 'none'; ?>;">
                <h3><?php esc_html_e('Dashboard notifications', 'wp-hide-backed-notices'); ?></h3>
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

    private function render_toggle($key, $label, $options) {
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
        $custom_css = $this->generate_hiding_css();
        if ($custom_css) {
            wp_add_inline_style($this->plugin_name . '-admin', $custom_css);
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'js/wp-hide-backed-notices-admin.js', array('jquery'), $this->version, false);
    }

    private function generate_hiding_css() {
        $user = wp_get_current_user();
        $user_roles = (array) $user->roles;
        $options = get_option($this->option_name, array());
        if (is_string($options)) $options = maybe_unserialize($options);
        
        if (empty($options)) return '';

        $should_hide = false;
        foreach ($user_roles as $role) {
            if (isset($options[$role]) || in_array($role, $options)) {
                $should_hide = true;
                break;
            }
        }
        
        if (in_array('administrator', $user_roles) && (isset($options['administrator']) || in_array('administrator', $options))) {
            $should_hide = true;
        }

        if (!$should_hide) return '';

        $css = '';
        if (isset($options['Hide_Updates']) || in_array('Hide Updates', $options)) {
            $css .= 'body.wp-admin .update-plugins, body.wp-admin .awaiting-mod, body.wp-admin #wp-admin-bar-updates { display: none !important; }';
        }
        if (isset($options['Hide_Notices']) || in_array('Hide Notices', $options)) {
            $css .= 'body.wp-admin #wp-admin-bar-seedprod_admin_bar, body.wp-admin .update-nag, body.wp-admin .updated, body.wp-admin .error, body.wp-admin .is-dismissible, body.wp-admin .notice, #yoast-indexation-warning { display: none !important; }';
            $css .= 'body.wp-admin #loco-content .notice, body.wp-admin #loco-notices .notice { display: block !important; }';
        }
        if (isset($options['Hide_PHP_Updates']) || in_array('Hide PHP Updates', $options)) {
            $css .= '#dashboard_php_nag { display: none !important; }';
        }

        return $css;
    }
}
