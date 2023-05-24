<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the plugin options
delete_option('weather_api_key');
delete_option('weather_background_color');
delete_option('weather_text_color');
delete_option('weather_hide_fields');
delete_option('weather_id_field');
delete_option('weather_class_field');