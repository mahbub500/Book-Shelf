
console.log( 'tst' );
jQuery(document).ready(function($) {
    const modal = $("#editBookModal");
    const closeBtn = $(".close");
    const editForm = $("#edit-book-form");

    // Open modal and pre-fill form
    $(".edit-book").on("click", function() {
        const bookId = $(this).data("book-id");
        const authorId = $(this).data("author-id");
        const publisherId = $(this).data("publisher-id");
        const isbn = $(this).data("isbn");
        const price = $(this).data("price");

        // Fill modal fields
        $("#modal_book_id").val(bookId);
        $("#modal_author_id").val(authorId);
        $("#modal_publisher_id").val(publisherId);
        $("#modal_isbn_number").val(isbn);
        $("#modal_price").val(price);

        // Show the modal
        modal.show();
    });

    // Close modal when clicking on close button
    closeBtn.on("click", function() {
        modal.hide();
    });

    // Close modal when clicking outside of it
    $(window).on("click", function(event) {
        if ($(event.target).is(modal)) {
            modal.hide();
        }
    });

    // Handle form submission for updating the book details
    editForm.on("submit", function(event) {
        event.preventDefault(); // Prevent default form submission

        const formData = $(this).serialize(); // Serialize form data

        $.ajax({
            url: ajaxurl, // WordPress Ajax URL
            method: "POST",
            data: {
                action: "update_book",  // Action hook to trigger the update function
                form_data: formData  // Send form data
            },
            success: function(response) {
                if (response.success) {
                    // Close the modal and reload the page
                    modal.hide();
                    location.reload();  // Reload the page to show the updated data
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("An error occurred while updating the book.");
            }
        });
    });
});


