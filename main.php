<?php
/*
  Plugin Name: Simple Universal Google Analytics
  Version: 1.0.2
  Plugin URI: http://wphowto.net/simple-universal-google-analytics-plugin-for-wordpress-822
  Author: naa986
  Author URI: http://wphowto.net/
  Description: Easily add Universal Google Analytics Tracking code to your WordPress site
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('SIMPLE_UNIVERSAL_GA')) {

    class SIMPLE_UNIVERSAL_GA {

        var $plugin_version = '1.0.2';

        function __construct() {
            define('SIMPLE_UNIVERSAL_GA_VERSION', $this->plugin_version);
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            }
            add_action('admin_init', array($this, 'settings_api_init'));
            add_action('admin_menu', array($this, 'add_options_menu'));
            add_action('wp_head', array($this, 'add_tracking_code'));
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
                $links[] = '<a href="options-general.php?page=simple-uga-settings">Settings</a>';
            }
            return $links;
        }
        function add_options_menu() {
            if (is_admin()) {
                add_options_page('Google Analytics', 'Google Analytics', 'manage_options', 'simple-uga-settings', array($this, 'options_page'));
            }
        }
        function settings_api_init(){
            	register_setting( 'simpleugapage', 'simple_uga_settings' );
                
                add_settings_section(
                        'simple_uga_section', 
                        __('General Settings', 'simpleuga'), 
                        array($this, 'simple_uga_settings_section_callback'), 
                        'simpleugapage'
                );
                
                add_settings_field( 
                        'uga_id', 
                        __('Tracking ID', 'simpleuga'), 
                        array($this, 'uga_id_render'), 
                        'simpleugapage', 
                        'simple_uga_section' 
                );
        }
        function uga_id_render() { 
            $options = get_option('simple_uga_settings');
            ?>
            <input type='text' name='simple_uga_settings[uga_id]' value='<?php echo $options['uga_id']; ?>'>
            <p class="description">Enter your Google Analytics Tracking ID for this website (e.g UA-35118216-1).</p>
            <?php
        }
        function simple_uga_settings_section_callback() { 
                //echo __( 'This section description', 'simpleuga' );
        }

        function options_page() {
            ?>           
            <div>
            <h2>Simple Universal Google Analytics - v<?php echo $this->plugin_version; ?></h2>
            <form action='options.php' method='post'>
            <?php
            settings_fields( 'simpleugapage' );
            do_settings_sections( 'simpleugapage' );
            submit_button();
            ?>
            </form>
            </div>
            <?php
        }
        
        function is_logged_in(){
            $is_logged_in = false;
            if(is_user_logged_in()){ //the user is logged in
                if(current_user_can('editor') || current_user_can('administrator')){
                    $is_logged_in = true;
                }
            }
            return $is_logged_in; 
        }
        
        function add_tracking_code() {
            if(!$this->is_logged_in()) {
                $options = get_option( 'simple_uga_settings' );
                $tracking_id = $options['uga_id'];
                if(isset($tracking_id) && !empty($tracking_id)){
                    $ouput = <<<EOT
                    <!-- Tracking code generated with Simple Universal Google Analytics plugin v{$this->plugin_version} -->
                    <script>
                    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                    ga('create', '$tracking_id', 'auto');
                    ga('send', 'pageview');

                    </script>
                    <!-- / Simple Universal Google Analytics plugin -->
EOT;

                    echo $ouput;
                }
            }
        }

    }

    $GLOBALS['simple_universal_ga'] = new SIMPLE_UNIVERSAL_GA();
}
