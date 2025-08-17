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
        // Define the labels for the Staff CPT
    $labels = [
        'name'                  => _x( 'Staff', 'Post type general name', 'faculty-finder' ),
        'singular_name'         => _x( 'Staff Member', 'Post type singular name', 'faculty-finder' ),
        'menu_name'             => _x( 'Staff', 'Admin Menu text', 'faculty-finder' ),
        'name_admin_bar'        => _x( 'Staff Member', 'Add New on Toolbar', 'faculty-finder' ),
        'add_new'               => __( 'Add New', 'faculty-finder' ),
        'add_new_item'          => __( 'Add New Staff Member', 'faculty-finder' ), // This fixes "Add New Post"
        'new_item'              => __( 'New Staff Member', 'faculty-finder' ),
        'edit_item'             => __( 'Edit Staff Member', 'faculty-finder' ),
        'view_item'             => __( 'View Staff Member', 'faculty-finder' ),
        'all_items'             => __( 'All Staff', 'faculty-finder' ),
        'search_items'          => __( 'Search Staff', 'faculty-finder' ),
        'parent_item_colon'     => __( 'Parent Staff Member:', 'faculty-finder' ),
        'not_found'             => __( 'No staff members found.', 'faculty-finder' ),
        'not_found_in_trash'    => __( 'No staff members found in Trash.', 'faculty-finder' ),
        'featured_image'        => _x( 'Staff Member Photo', 'Overrides the “Featured Image” phrase for this post type.', 'faculty-finder' ),
        'set_featured_image'    => _x( 'Set photo', 'Overrides the “Set featured image” phrase for this post type.', 'faculty-finder' ),
        'remove_featured_image' => _x( 'Remove photo', 'Overrides the “Remove featured image” phrase for this post type.', 'faculty-finder' ),
        'use_featured_image'    => _x( 'Use as photo', 'Overrides the “Use as featured image” phrase for this post type.', 'faculty-finder' ),
    ];

    $args = [
        'label'               => __( 'Staff', 'faculty-finder' ),
        'labels'              => $labels,
        'supports'            => [ 'thumbnail' ], // Default fields to use
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

    // Labels for the "Department" taxonomy
    $department_labels = [
        'name'              => _x( 'Departments', 'taxonomy general name', 'faculty-finder' ),
        'singular_name'     => _x( 'Department', 'taxonomy singular name', 'faculty-finder' ),
        'search_items'      => __( 'Search Departments', 'faculty-finder' ),
        'all_items'         => __( 'All Departments', 'faculty-finder' ),
        'parent_item'       => __( 'Parent Department', 'faculty-finder' ),
        'parent_item_colon' => __( 'Parent Department:', 'faculty-finder' ),
        'edit_item'         => __( 'Edit Department', 'faculty-finder' ),
        'update_item'       => __( 'Update Department', 'faculty-finder' ),
        'add_new_item'      => __( 'Add New Department', 'faculty-finder' ),
        'new_item_name'     => __( 'New Department Name', 'faculty-finder' ),
        'menu_name'         => __( 'Departments', 'faculty-finder' ), // This sets the menu text
    ];

    $department_args = [
        'hierarchical'      => true,
        'labels'            => $department_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'department' ],
    ];

    // Register the "Department" taxonomy
    register_taxonomy( 'department', [ 'staff' ], $department_args );

    // --- Now, define separate labels for the "Building" taxonomy ---

    // Labels for the "Building" taxonomy
    $building_labels = [
        'name'              => _x( 'Buildings', 'taxonomy general name', 'faculty-finder' ),
        'singular_name'     => _x( 'Building', 'taxonomy singular name', 'faculty-finder' ),
        'search_items'      => __( 'Search Buildings', 'faculty-finder' ),
        'all_items'         => __( 'All Buildings', 'faculty-finder' ),
        'edit_item'         => __( 'Edit Building', 'faculty-finder' ),
        'update_item'       => __( 'Update Building', 'faculty-finder' ),
        'add_new_item'      => __( 'Add New Building', 'faculty-finder' ),
        'new_item_name'     => __( 'New Building Name', 'faculty-finder' ),
        'menu_name'         => __( 'Buildings', 'faculty-finder' ), // This sets the menu text
    ];

    $building_args = [
        'hierarchical'      => false, // Buildings don't need a hierarchy
        'labels'            => $building_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'building' ],
    ];

    // Register the "Building" taxonomy
    register_taxonomy( 'building', [ 'staff' ], $building_args );
}
add_action( 'init', 'ffinder_register_taxonomies' );

/**
 * Changes the placeholder text for the title input field on the Staff edit screen.
 *
 * This function is hooked into the 'enter_title_placeholder' filter.
 *
 * @param string $placeholder The default placeholder text.
 * @return string The modified placeholder text.
 */
function ffinder_change_title_placeholder( $placeholder ) {
    // Get the current screen's post type
    $screen = get_current_screen();

    // Check if we are on the 'staff' post type edit screen
    if ( isset( $screen->post_type ) && $screen->post_type == 'staff' ) {
        return 'Enter full name here';
    }

    // For all other post types, return the original placeholder
    return $placeholder;
}
add_filter( 'enter_title_here', 'ffinder_change_title_placeholder' );

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

/**
 * Enqueues the plugin's front-end CSS and JavaScript files.
 *
 * This function loads the main stylesheet and the AJAX script. It uses
 * wp_localize_script to safely pass the AJAX URL from PHP to our
 * JavaScript file, which is a WordPress best practice.
 *
 * @since 1.0.0
 */
function ffinder_enqueue_assets() {
    // Enqueue the main stylesheet (as before).
    wp_enqueue_style(
        'faculty-finder-styles',
        plugin_dir_url( __FILE__ ) . 'assets/css/ffinder-style.css',
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/ffinder-style.css' )
    );

    // Enqueue the new AJAX JavaScript file.
    // The ['jquery'] part tells WordPress this script needs jQuery to be loaded first.
    wp_enqueue_script(
        'faculty-finder-ajax',
        plugin_dir_url( __FILE__ ) . 'assets/js/ffinder-ajax-scripts.js',
        [ 'jquery' ],
        filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/ffinder-ajax-scripts.js' ),
        true // The 'true' loads the script in the footer for better performance.
    );

    // Pass the AJAX URL to our JavaScript file.
    wp_localize_script(
        'faculty-finder-ajax',          // The handle of the script to receive the data.
        'ffinder_ajax_object',          // The name of the JavaScript object that will contain the data.
        [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] // The data to pass.
    );
}
add_action( 'wp_enqueue_scripts', 'ffinder_enqueue_assets' );


// -----------------------------------------------------------------
// STEP 4.2: THE PHP AJAX HANDLER
// -----------------------------------------------------------------
// Add this new function to your `faculty-finder.php` file.
// This is the server-side code that processes the filter requests.
// -----------------------------------------------------------------

/**
 * Handles the AJAX request for filtering the staff directory.
 *
 * This function checks the security nonce, sanitizes the incoming filter data,
 * builds a new WP_Query based on that data, and returns the resulting HTML.
 * It is hooked into both 'wp_ajax_' (for logged-in users) and
 * 'wp_ajax_nopriv_' (for visitors).
 *
 * @since 1.0.0
 */
function ffinder_ajax_filter_handler() {
    // 1. SECURITY: Verify the nonce sent from our form.
    if ( ! check_ajax_referer( 'ffinder_filter_nonce', '_wpnonce', false ) ) {
        wp_send_json_error( 'Invalid security token.', 403 );
        wp_die();
    }

    // 2. SANITIZE & GET DATA: Safely get the filter values from the POST request.
    $department_id = isset( $_POST['department'] ) ? intval( $_POST['department'] ) : '';
    $search_term   = isset( $_POST['search_term'] ) ? sanitize_text_field( $_POST['search_term'] ) : '';

    // 3. BUILD QUERY ARGS: Create the arguments for our new database query.
    $args = [
        'post_type'      => 'staff',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];

    // If a search term was provided, add the 's' parameter to the query.
    if ( ! empty( $search_term ) ) {
        $args['s'] = $search_term;
    }

    // If a department was selected, add a 'tax_query' to filter by that department.
    if ( ! empty( $department_id ) ) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'department',
                'field'    => 'term_id',
                'terms'    => $department_id,
            ],
        ];
    }

    // 4. EXECUTE QUERY & GENERATE HTML: Run the query and build the response.
    $staff_query = new WP_Query( $args );

    if ( $staff_query->have_posts() ) {
        while ( $staff_query->have_posts() ) {
            $staff_query->the_post();
            
            // IMPORTANT: This HTML structure MUST MATCH the card structure in your shortcode function.
            // Reusing the same structure ensures the styling remains consistent.
            $job_title = get_post_meta( get_the_ID(), '_ffinder_job_title', true );
            $email     = get_post_meta( get_the_ID(), '_ffinder_email', true );
            $phone     = get_post_meta( get_the_ID(), '_ffinder_phone', true );
            ?>
            <div class="ffinder-card">
                <div class="ffinder-card-image-wrap">
                    <?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large' ); else : ?><div class="ffinder-image-placeholder"></div><?php endif; ?>
                </div>
                <div class="ffinder-card-content">
                    <h3 class="ffinder-card-name"><?php the_title(); ?></h3>
                    <?php if ( ! empty( $job_title ) ) : ?><p class="ffinder-card-title"><?php echo esc_html( $job_title ); ?></p><?php endif; ?>
                    <ul class="ffinder-card-contact">
                        <?php if ( ! empty( $email ) ) : ?>
                            <li class="ffinder-contact-email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
                        <?php endif; ?>
                        <?php if ( ! empty( $phone ) ) : ?>
                            <li class="ffinder-contact-phone"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php
        }
    } else {
        // If no posts match the criteria, show a friendly message.
        echo '<p>' . __( 'No staff members match your criteria.', 'faculty-finder' ) . '</p>';
    }

    // 5. END EXECUTION: Always use wp_die() at the end of an AJAX handler.
    wp_die();
}
// Hook our handler to the 'wp_ajax_{action}' and 'wp_ajax_nopriv_{action}' hooks.
// The {action} part must match the 'action' value from our JavaScript.
add_action( 'wp_ajax_ffinder_filter_staff', 'ffinder_ajax_filter_handler' );
add_action( 'wp_ajax_nopriv_ffinder_filter_staff', 'ffinder_ajax_filter_handler' );

// 1. Function to add the meta box
function ffinder_add_custom_meta_box() {
    add_meta_box(
        'ffinder_staff_details',          // Unique ID for the meta box
        'Staff Member Details',           // Title of the meta box
        'ffinder_display_meta_box_callback', // Callback function to display the HTML
        'staff',                          // The screen to show it on (our CPT)
        'normal',                         // Context (normal, side, advanced)
        'high'                            // Priority (high, low)
    );
}
add_action( 'add_meta_boxes', 'ffinder_add_custom_meta_box' );

// 2. Callback function to display the HTML fields
function ffinder_display_meta_box_callback( $post ) {
    // Add a nonce field for security
    wp_nonce_field( 'ffinder_save_meta_box_data', 'ffinder_meta_box_nonce' );

    // Get existing values from the database
    $first_name = get_post_meta( $post->ID, '_ffinder_first_name', true );
    $last_name  = get_post_meta( $post->ID, '_ffinder_last_name', true );
    $email      = get_post_meta( $post->ID, '_ffinder_email', true );
    $phone      = get_post_meta( $post->ID, '_ffinder_phone', true );

    // Display the fields
    echo '<p><label for="ffinder_first_name">First Name: </label>';
    echo '<input type="text" id="ffinder_first_name" name="ffinder_first_name" value="' . esc_attr( $first_name ) . '" size="25" /></p>';

    echo '<p><label for="ffinder_last_name">Last Name: </label>';
    echo '<input type="text" id="ffinder_last_name" name="ffinder_last_name" value="' . esc_attr( $last_name ) . '" size="25" /></p>';

    echo '<p><label for="ffinder_email">Email Address: </label>';
    echo '<input type="email" id="ffinder_email" name="ffinder_email" value="' . esc_attr( $email ) . '" size="25" /></p>';

    echo '<p><label for="ffinder_phone">Phone Number: </label>';
    echo '<input type="text" id="ffinder_phone" name="ffinder_phone" value="' . esc_attr( $phone ) . '" size="25" /></p>';
}

// 3. Function to save the meta box data
function ffinder_save_meta_box_data( $post_id ) {
    // Check if our nonce is set and valid.
    if ( ! isset( $_POST['ffinder_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ffinder_meta_box_nonce'], 'ffinder_save_meta_box_data' ) ) {
        return;
    }

    // Don't save on autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Sanitize and save the data.
    $fields = ['ffinder_first_name', 'ffinder_last_name', 'ffinder_email', 'ffinder_phone'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action( 'save_post', 'ffinder_save_meta_box_data' );