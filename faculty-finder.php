<?php
/**
 * Plugin Name:       FacultyFinder
 * Plugin URI:        https://github.com/jamdlaw/faculty-finder
 * Description:       A custom plugin to create, manage, and display a searchable staff directory.
 * Version:           1.0.0
 * Author:            James Lawrence
 * Text Domain:       faculty-finder
 *
 * @package           Faculty_Finder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

//custom post registration function
function ffinder_register_staff_cpt() {
    $labels = [ /* ... Define labels like 'Staff', 'Add New Staff', etc. ... */ ];
    $args = [
        'label'               => __( 'Staff', 'faculty-finder' ),
        'labels'              => $labels,
        'supports'            => [ 'title', 'editor', 'thumbnail' ], // Default fields to use
        'public'              => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-businessperson', // A fitting icon from Dashicons
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'faculty' ],
    ];
    register_post_type( 'staff', $args );
}
add_action( 'init', 'ffinder_register_staff_cpt' );

function ffinder_register_taxonomies() {
    // Register Department Taxonomy, linked to the 'staff' CPT
    register_taxonomy( 'department', 'staff', [ /* ... args ... */ ] );
    // Register Building Taxonomy, linked to the 'staff' CPT
    register_taxonomy( 'building', 'staff', [ /* ... args ... */ ] );
}
add_action( 'init', 'ffinder_register_taxonomies' );

/**
 * Enqueue the plugin's front-end stylesheet.
 *
 * This function is hooked into 'wp_enqueue_scripts', ensuring our styles
 * are loaded only on the public-facing pages of the site.
 *
 * @since 1.0.0
 */
function ffinder_enqueue_styles() {

    // The handle is a unique name for our stylesheet.
    $handle = 'faculty-finder-styles';

    // The source URL for our stylesheet.
    // plugin_dir_url( __FILE__ ) gets the URL of the current plugin's directory.
    $src = plugin_dir_url( __FILE__ ) . 'assets/css/ffinder-style.css';

    // Dependencies array. We don't have any, so it's empty.
    $deps = [];

    // The version number. Using filemtime() is a great trick for cache-busting.
    // It appends the file's last modification time to the URL,
    // forcing browsers to re-download it when you make changes.
    $version = filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/ffinder-style.css' );

    // Register and enqueue the stylesheet.
    wp_enqueue_style( $handle, $src, $deps, $version );

}
// This is the proper hook for enqueuing styles and scripts on the front end.
add_action( 'wp_enqueue_scripts', 'ffinder_enqueue_styles' );