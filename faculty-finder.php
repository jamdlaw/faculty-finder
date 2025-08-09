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