<?php
/*
Plugin Name: Index Elementor & Customize Backup
Description: A plugin to backup Elementor designs and Customize settings to a JSON file using WordPress functions.
Version: 1.2
Author: E477
*/

if (!defined('ABSPATH')) {
    exit;
}

function backup_elementor_and_customize_data()
{
    // استخراج جميع الصفحات المصممة بـ Elementor
    $all_posts = get_posts([
        'post_type' => ['page', 'post'],
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $footer_id = Index_Layout::get_default_footer()->renderId;
    if($footer_id){
        $footer = get_post($footer_id);
        $footer->is_footer = true;
        array_push($all_posts,$footer);
    }




    foreach ($all_posts as $post) {
        $id = $post->ID;
        $post->_elementor_data = get_post_meta($id, '_elementor_data', true);
        $post->_elementor_edit_mode = get_post_meta($id, '_elementor_edit_mode', true);
        $post->_elementor_template_type = get_post_meta($id, '_elementor_template_type', true);
        $post->_elementor_controls_usage = get_post_meta($id, '_elementor_controls_usagee', true);
        $post->_elementor_page_settings = get_post_meta($id, '_elementor_page_settings', true);
        $post->_elementor_css = json_encode(get_post_meta($id, '_elementor_css', true));
        $post->_wp_page_template = get_post_meta($id, '_wp_page_template', true);
        $post->_edit_lock = get_post_meta($id, '_edit_lock', true);
        if(!isset($post->is_footer)){
            $post->is_footer = false;
        }
    }

    // استخراج إعدادات Elementor (التي يتم حفظها في wp_options)
    $elementor_options = [];
    foreach (wp_load_alloptions() as $option_name => $option_value) {
        if (strpos($option_name, 'elementor') === 0) {
            $elementor_options[$option_name] = $option_value;
        }
    }

    // استخراج إعدادات Customize (المحفوظة في wp_options)
    $customize_options = get_theme_mods();

    // استخراج إعدادات Widgets
    $widgets = [];
    foreach (wp_load_alloptions() as $option_name => $option_value) {
        if (strpos($option_name, 'widget_') === 0) {
            $widgets[$option_name] = $option_value;
        }
    }

    // دمج جميع البيانات في مصفوفة واحدة
    $backup_data = [
        'all_posts' => $all_posts,
        'customize_options' => $customize_options,
    ];

    // تحويل البيانات إلى JSON
    $backup_json = json_encode($backup_data, JSON_PRETTY_PRINT);

    if ($backup_json) {
        $backup_json = str_replace('localhost', '{{{[index_iuu_siteURL]}}}', $backup_json);
    }
    // حفظ النسخة الاحتياطية في مجلد uploads
    $upload_dir = wp_upload_dir();
    $backup_file = $upload_dir['basedir'] . '/elementor_customize_backup.json';

    file_put_contents($backup_file, $backup_json);

    return $upload_dir['baseurl'] . '/elementor_customize_backup.json';
}

add_action('admin_menu', function () {
    add_menu_page(
        'Index Elementor & Customize Backup',
        'Index Backup',
        'manage_options',
        'elementor-customize-backup',
        'render_backup_page',
        'dashicons-backup',
        80
    );
});

function render_backup_page()
{
    if (isset($_POST['backup'])) {
        $backup_url = backup_elementor_and_customize_data();
        echo "<div class='notice notice-success'><p>Backup created successfully. <a href='" . $backup_url . "' target='_blank'>Download Backup</a></p>";
    }

    echo '<div class="wrap">';
    echo '<h1>Elementor & Customize Backup</h1>';
    echo '<form method="post">';
    submit_button('Create Backup', 'primary', 'backup');
    echo '</form>';
    echo '</div>';
}
