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

/**
 * Generates the HTML output for the [faculty_finder] shortcode.
 *
 * This function creates the filter form and displays the initial grid of staff members.
 * The grid itself is designed to be replaced via AJAX calls.
 *
 * @since 1.0.0
 * @return string The HTML for the entire directory interface.
 */
function ffinder_display_directory_shortcode() {
    // Start output buffering to capture all HTML into a variable.
    ob_start();
    ?>

    <div class="ffinder-directory-wrap">

        <!-- =================================================================
        FILTER FORM
        This section builds the user-facing search and filter controls.
        ================================================================== -->
        <form id="ffinder-filters" class="ffinder-filters-form" role="search">
            <div class="ffinder-filter-group">
                <label for="ffinder-department-filter" class="ffinder-label"><?php _e( 'Filter by Department:', 'faculty-finder' ); ?></label>
                <select id="ffinder-department-filter" class="ffinder-select" name="department_filter">
                    <option value=""><?php _e( 'All Departments', 'faculty-finder' ); ?></option>
                    <?php
                        // Get all available 'department' terms to populate the dropdown.
                        $departments = get_terms( [ 
                            'taxonomy'   => 'department', 
                            'hide_empty' => true, // Only show departments that actually have staff.
                        ] );

                        if ( ! is_wp_error( $departments ) && ! empty( $departments ) ) {
                            foreach ( $departments as $department ) {
                                echo '<option value="' . esc_attr( $department->term_id ) . '">' . esc_html( $department->name ) . '</option>';
                            }
                        }
                    ?>
                </select>
            </div>

            <div class="ffinder-filter-group">
                <label for="ffinder-search-filter" class="ffinder-label"><?php _e( 'Search by Name:', 'faculty-finder' ); ?></label>
                <input type="text" id="ffinder-search-filter" name="search_filter" class="ffinder-input" placeholder="<?php _e( 'e.g., Jane Doe', 'faculty-finder' ); ?>">
            </div>

            <!-- Security nonce for the AJAX request. This is critical. -->
            <?php wp_nonce_field( 'ffinder_filter_nonce', '_wpnonce', false ); ?>
        </form>

        <!-- =================================================================
        DIRECTORY GRID
        This is the container that our AJAX will update with new results.
        It is pre-filled with all staff members on initial page load.
        ================================================================== -->
        <div id="ffinder-directory-grid" class="ffinder-grid" aria-live="polite">
            <?php
            // Define the arguments for the initial query to get all staff members.
            $args = [
                'post_type'      => 'staff',
                'posts_per_page' => -1, // -1 shows all posts.
                'orderby'        => 'title', // Order alphabetically by name (post title).
                'order'          => 'ASC',
            ];
            $staff_query = new WP_Query( $args );

            if ( $staff_query->have_posts() ) :
                while ( $staff_query->have_posts() ) : $staff_query->the_post();

                    // Get custom field data for the current staff member.
                    // IMPORTANT: Replace these meta keys with the actual keys you define in your meta box.
                    $job_title = get_post_meta( get_the_ID(), '_ffinder_job_title', true );
                    $email     = get_post_meta( get_the_ID(), '_ffinder_email', true );
                    $phone     = get_post_meta( get_the_ID(), '_ffinder_phone', true );
                    ?>
                    
                    <!-- STAFF MEMBER CARD HTML STRUCTURE -->
                    <div class="ffinder-card">
                        <div class="ffinder-card-image-wrap">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium_large' ); // Use a standard WordPress image size. ?>
                            <?php else : ?>
                                <!-- You can place a placeholder SVG or icon here -->
                                <div class="ffinder-image-placeholder"></div>
                            <?php endif; ?>
                        </div>
                        <div class="ffinder-card-content">
                            <h3 class="ffinder-card-name"><?php the_title(); ?></h3>

                            <?php if ( ! empty( $job_title ) ) : ?>
                                <p class="ffinder-card-title"><?php echo esc_html( $job_title ); ?></p>
                            <?php endif; ?>

                            <ul class="ffinder-card-contact">
                                <?php if ( ! empty( $email ) ) : ?>
                                    <li class="ffinder-contact-email">
                                        <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ( ! empty( $phone ) ) : ?>
                                    <li class="ffinder-contact-phone">
                                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                <?php
                endwhile;
            else :
                // Message to display if no staff members are found on initial load.
                echo '<p>' . __( 'No staff members have been added yet.', 'faculty-finder' ) . '</p>';
            endif;
            
            // Restore original post data to prevent conflicts with other page elements.
            wp_reset_postdata();
            ?>
        </div><!-- #ffinder-directory-grid -->

    </div><!-- .ffinder-directory-wrap -->

    <?php
    // Return the complete HTML content that was captured.
    return ob_get_clean();
}



add_shortcode( 'faculty_finder', 'ffinder_display_directory_shortcode' );