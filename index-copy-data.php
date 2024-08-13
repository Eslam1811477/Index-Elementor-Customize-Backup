<?php
/*
Plugin Name: Index Elementor & Customize Backup
Description: A plugin to backup Elementor designs and Customize settings to a JSON file.
Version: 1.0
Author: E477
*/

if (!defined('ABSPATH')) {
    exit; 
}

function backup_elementor_and_customize_data() {
    global $wpdb;

    $elementor_posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_content LIKE '%elementor%'");
    $elementor_meta = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%elementor%')");
    $elementor_options = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'elementor%'");

    $theme_slug = get_option('stylesheet');
    $customize_options = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", "theme_mods_{$theme_slug}"));

    $backup_data = [
        'elementor_posts' => $elementor_posts,
        'elementor_meta' => $elementor_meta,
        'elementor_options' => $elementor_options,
        'customize_options' => $customize_options
    ];

    $backup_json = json_encode($backup_data, JSON_PRETTY_PRINT);

    $upload_dir = wp_upload_dir();
    $backup_file = $upload_dir['basedir'] . '/elementor_customize_backup.json';

    file_put_contents($backup_file, $backup_json);

    return $upload_dir['baseurl'] . '/elementor_customize_backup.json';
}

add_action('admin_menu', function() {
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

function render_backup_page() {
    if (isset($_POST['backup'])) {
        $backup_url = backup_elementor_and_customize_data();
        echo "<div class='notice notice-success'><p>Backup created successfully. <a href='{$backup_url}' target='_blank'>Download Backup</a></p></div>";
    }

    echo '<div class="wrap">';
    echo '<h1>Elementor & Customize Backup</h1>';
    echo '<form method="post">';
    submit_button('Create Backup', 'primary', 'backup');
    echo '</form>';
    echo '</div>';
}
