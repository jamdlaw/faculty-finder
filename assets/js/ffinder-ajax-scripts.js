jQuery(document).ready(function($) {

    // A simple flag to prevent multiple AJAX requests from firing at the same time.
    var isRequestRunning = false;

    // --- Main function to handle the AJAX request ---
    function performAjaxRequest() {
        if (isRequestRunning) {
            return; // Exit if a request is already in progress.
        }
        isRequestRunning = true;

        // Provide visual feedback to the user that something is happening.
        $('#ffinder-directory-grid').css('opacity', 0.5);

        // Collect all the data from our filter form.
        var filterData = {
            action: 'ffinder_filter_staff', // This MUST match the action in our PHP hooks.
            _wpnonce: $('#_wpnonce').val(),   // The security nonce.
            department: $('#ffinder-department-filter').val(),
            search_term: $('#ffinder-search-filter').val()
        };

        // --- The core jQuery AJAX call ---
        $.ajax({
            url: ffinder_ajax_object.ajax_url, // The URL passed from PHP.
            type: 'POST',
            data: filterData,
            success: function(response) {
                // On success, replace the content of our grid with the new HTML from the server.
                $('#ffinder-directory-grid').html(response);
            },
            error: function(error) {
                // Optional: Handle any errors gracefully.
                console.error('FacultyFinder AJAX Error:', error);
                $('#ffinder-directory-grid').html('<p>An error occurred. Please refresh the page and try again.</p>');
            },
            complete: function() {
                // Once the request is finished (whether success or error), reset everything.
                isRequestRunning = false;
                $('#ffinder-directory-grid').css('opacity', 1);
            }
        });
    }

    // --- Event Listeners ---
    // Trigger the AJAX request when the department dropdown is changed.
    $('#ffinder-department-filter').on('change', performAjaxRequest);

    // Use a short delay (debounce) on the search input to avoid firing an
    // AJAX request on every single keystroke, which is inefficient.
    var searchTimeout;
    $('#ffinder-search-filter').on('keyup', function() {
        clearTimeout(searchTimeout);
        // Wait for 500ms after the user stops typing before sending the request.
        searchTimeout = setTimeout(performAjaxRequest, 500);
    });
});
