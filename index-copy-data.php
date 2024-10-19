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

    // استخراج الميتا المتعلقة بـ Elementor لكل صفحة
    $all_meta = [];
    foreach ($all_posts as $post) {
        $post_meta = get_post_meta($post->ID);
        $all_meta[$post->ID] = $post_meta;
    }

    // استخراج إعدادات Elementor (التي يتم حفظها في wp_options)
    $elementor_options = [];
    foreach (wp_load_alloptions() as $option_name => $option_value) {
        if (strpos($option_name, 'elementor') === 0) {
            $elementor_options[$option_name] = $option_value;
        }
    }

    // استخراج إعدادات Customize (المحفوظة في wp_options)
    $theme_slug = get_option('stylesheet');
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
        'all_meta' => $all_meta,
        'elementor_options' => $elementor_options,
        'customize_options' => $customize_options,
        'widgets' => $widgets,
    ];

    // تحويل البيانات إلى JSON
    $backup_json = json_encode($backup_data, JSON_PRETTY_PRINT);

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
        echo "<div class='notice notice-success'><p>Backup created successfully. <a href='". $backup_url ."' target='_blank'>Download Backup</a></p>";
    }

    echo '<div class="wrap">';
    echo '<h1>Elementor & Customize Backup</h1>';
    echo '<form method="post">';
    submit_button('Create Backup', 'primary', 'backup');
    echo '</form>';
    echo '</div>';
}
