<?php
/*
Plugin Name: Index Elementor & Customize Backup
Description: A plugin to backup Elementor designs for Home and Blog pages, Widgets, and Customize settings to a JSON file.
Version: 1.3
Author: E477
*/

if (!defined('ABSPATH')) {
    exit;
}

function backup_home_blog_widgets_and_customize_data()
{
    global $wpdb;

    // الحصول على صفحات الهوم والبلوج
    $home_page_id = get_option('page_on_front');  // ID صفحة الهوم
    $blog_page_id = get_option('page_for_posts'); // ID صفحة البلوج

    // استرجاع بيانات الصفحات
    $pages = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} WHERE ID IN (%d, %d) AND post_status = 'publish'",
            $home_page_id,
            $blog_page_id
        )
    );

    // استرجاع بيانات الميتا الخاصة بالصفحات
    $meta = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->postmeta} WHERE post_id IN (%d, %d)",
            $home_page_id,
            $blog_page_id
        )
    );

    // استرجاع خيارات Elementor المخزنة في قاعدة البيانات
    $elementor_options = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'elementor%'");

    // استرجاع إعدادات Widgets المخزنة في قاعدة البيانات
    $widgets_options = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'widget_%'");

    // استرجاع إعدادات تخصيص القالب
    $theme_slug = get_option('stylesheet');
    $customize_options = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", "theme_mods_{$theme_slug}"));

    // دمج جميع البيانات في مصفوفة واحدة
    $backup_data = [
        'pages'            => $pages,
        'meta'             => $meta,
        'elementor_options'=> $elementor_options,
        'widgets_options'  => $widgets_options,
        'customize_options'=> $customize_options
    ];

    // تحويل البيانات إلى JSON
    $backup_json = json_encode($backup_data, JSON_PRETTY_PRINT);

    // استبدال localhost بالنص المطلوب
    $backup_json = str_replace('localhost', '{{{[index_iuu_siteURL]}}}', $backup_json);

    // حفظ النسخة الاحتياطية في مجلد uploads
    $upload_dir = wp_upload_dir();
    $backup_file = $upload_dir['basedir'] . '/home_blog_widgets_customize_backup.json';

    // التحقق من نجاح كتابة الملف
    if (file_put_contents($backup_file, $backup_json) === false) {
        return new WP_Error('file_write_error', 'Error writing backup file.');
    }

    return $upload_dir['baseurl'] . '/home_blog_widgets_customize_backup.json';
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
        $backup_url = backup_home_blog_widgets_and_customize_data();

        if (is_wp_error($backup_url)) {
            echo "<div class='notice notice-error'><p>Error creating backup: " . $backup_url->get_error_message() . "</p></div>";
        } else {
            echo "<div class='notice notice-success'><p>Backup created successfully. <a href='{$backup_url}' target='_blank'>Download Backup</a></p></div>";

            // إرسال النسخة الاحتياطية إلى API خارجي
            $json_data = file_get_contents($backup_url);

            $response = wp_remote_post('http://localhost:3000/api/templates', [
                'method'    => 'POST',
                'headers'   => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiI2NmJmMGUwMDQ2MTc2NTk3MGQyOWY1M2IiLCJ1c2VybmFtZSI6ImFkbWluIiwiZW1haWwiOiJlQGluZGV4LmNvbSIsImlhdCI6MTcyMzc5Njk5M30.giIn1KxbNaBKFXf7Uz48MtCuMw8HF1klknENQzDImuw',
                ],
                'body'      => $json_data, // إرسال البيانات في صيغة JSON
            ]);

            if (is_wp_error($response)) {
                error_log('Error sending request to API: ' . $response->get_error_message());
            } else {
                echo "<div class='notice notice-info'><p>Backup sent to the external API successfully.</p></div>";
            }
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Elementor & Customize Backup</h1>';
    echo '<form method="post">';
    submit_button('Create Backup', 'primary', 'backup');
    echo '</form>';
    echo '</div>';
}
