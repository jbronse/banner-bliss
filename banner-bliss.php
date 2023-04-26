<?php
/**
 * Plugin Name: Banner Bliss
 * Plugin URI: https://example.com/banner-bliss
 * Description: A simple and customizable banner plugin for WordPress.
 * Version: 1.0.0
 * Author: Jesse Bronson
 * Author URI: n/a
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: banner-bliss
 */


function bb_display_banner() {
    // Check if the banner should be displayed on the current page
    if (!is_admin()) {
        global $post;
        $current_page_id = $post->ID;
        $selected_pages = get_option('bb_selected_pages', array());

        if (!in_array($current_page_id, $selected_pages)) {
            return;
        }
    }

    $banner_text = get_option('bb_banner_text', '');
    $cta_button_text = get_option('bb_cta_button_text', '');
    $cta_button_url = get_option('bb_cta_button_url', '');
    $banner_bg_color = get_option('bb_banner_bg_color', '');
    $cta_button_color = get_option('bb_cta_button_color', '');

    echo '<div class="banner-bliss" style="background-color: ' . esc_attr($banner_bg_color) . ';">';
    echo '<div class="banner-content">' . wp_kses_post($banner_text) . '</div>';
    echo '<a class="cta-button" href="' . esc_url($cta_button_url) . '" style="background-color: ' . esc_attr($cta_button_color) . ';">' . esc_html($cta_button_text) . '</a>';
    echo '</div>';
}

function bb_enqueue_styles() {
    wp_enqueue_style('banner-bliss', plugin_dir_url(__FILE__) . 'css/banner-bliss.css');
}
add_action('wp_enqueue_scripts', 'bb_enqueue_styles');

function bb_create_settings_page() {
    add_options_page('Banner Bliss', 'Banner Bliss', 'manage_options', 'banner-bliss', 'bb_settings_page_markup');
}
add_action('admin_menu', 'bb_create_settings_page');

function bb_settings_page_markup() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('banner_bliss_options');
            do_settings_sections('banner_bliss');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function bb_settings_init() {
    register_setting('banner_bliss_options', 'bb_banner_text');
    register_setting('banner_bliss_options', 'bb_cta_button_text');
    register_setting('banner_bliss_options', 'bb_cta_button_url');
    register_setting('banner_bliss_options', 'bb_banner_bg_color');
    register_setting('banner_bliss_options', 'bb_cta_button_color');
    register_setting('banner_bliss_options', 'bb_banner_position');
    register_setting('banner_bliss_options', 'bb_selected_pages');

    add_settings_section('banner_bliss_section', 'Banner Settings', 'bb_section_callback', 'banner_bliss');

    add_settings_field('bb_banner_text', 'Banner Text', 'bb_banner_text_callback', 'banner_bliss', 'banner_bliss_section');
    add_settings_field('bb_cta_button_text', 'CTA Button Text', 'bb_cta_button_text_callback', 'banner_bliss', 'banner_bliss_section');
    add_settings_field('bb_cta_button_url', 'CTA Button URL', 'bb_cta_button_url_callback', 'banner_bliss', 'banner_bliss_section');
    add_settings_field('bb_banner_bg_color', 'Banner Background Color', 'bb_banner_bg_color_callback', 'banner_bliss', 'banner_bliss_section');
    add_settings_field('bb_cta_button_color', 'CTA Button Color', 'bb_cta_button_color_callback', 'banner_bliss', 'banner_bliss_section');
    add_settings_field('bb_banner_position', 'Banner Position', 'bb_banner_position_callback', 'banner_bliss', 'banner_bliss_section');
    add_settings_field('bb_selected_pages', 'Display on Pages', 'bb_selected_pages_callback', 'banner_bliss', 'banner_bliss_section');
}
add_action('admin_init', 'bb_settings_init');

function bb_section_callback() {
    echo '<p>Customize the appearance and content of the banner displayed on your website.</p>';
}

function bb_banner_text_callback() {
    $setting = get_option('bb_banner_text');
    wp_editor($setting, 'bb_banner_text', array('textarea_name' => 'bb_banner_text'));
}

function bb_cta_button_text_callback() {
    $setting = get_option('bb_cta_button_text');
    echo '<input type="text" name="bb_cta_button_text" value="' . esc_attr($setting) . '">';
}

function bb_cta_button_url_callback() {
    $setting = get_option('bb_cta_button_url');
    echo '<input type="text" name="bb_cta_button_url" value="' . esc_attr($setting) . '">';
}

function bb_banner_bg_color_callback() {
    $setting = get_option('bb_banner_bg_color');
    echo '<input type="color" name="bb_banner_bg_color" value="' . esc_attr($setting) . '">';
}

function bb_cta_button_color_callback() {
    $setting = get_option('bb_cta_button_color');
    echo '<input type="color" name="bb_cta_button_color" value="' . esc_attr($setting) . '">';
}

function bb_banner_position_callback() {
    $setting = get_option('bb_banner_position');
    echo '<select name="bb_banner_position">';
    echo '<option value="top"' . selected($setting, 'top', false) . '>Top</option>';
    echo '<option value="bottom"' . selected($setting, 'bottom', false) . '>Bottom</option>';
    echo '</select>';
}

function bb_selected_pages_callback() {
    $selected_pages = get_option('bb_selected_pages', array());
    $pages = get_pages();

    echo '<select multiple name="bb_selected_pages[]">';

    foreach ($pages as $page) {
        $selected = in_array($page->ID, $selected_pages) ? 'selected' : '';
        echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
    }

    echo '</select>';
}

$banner_position = get_option('bb_banner_position', 'top');
if ($banner_position == 'top') {
    add_action('bb_custom_banner_hook', 'bb_display_banner');
} else {
    add_action('wp_footer', 'bb_display_banner');
}
