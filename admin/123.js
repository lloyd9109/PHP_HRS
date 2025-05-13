
// Open modal for adding new room
document.getElementById('addRoomBtn').onclick = function() {
    resetModal(); // Reset the modal contents for new input
    document.getElementById('formModal').style.display = 'block'; // Show the modal

    // Fetch the next available room number from the server
    $.get('fetch_next_room_number.php', function(data) {
        $('#room_number').val(data); // Set the fetched room number
    });

    $('#features').val([]).trigger('change');
    $('#amenities').val([]).trigger('change');
};


    // Function to close modal
    function closeModal() {
        document.getElementById('formModal').style.display = 'none'; // Hide modal
    }

    // Close the modal when "x" is clicked
    document.querySelector('.close').onclick = closeModal;

    // Reset modal contents for both add and edit
    function resetModal() {
        document.getElementById('roomForm').reset(); // Reset form inputs
        document.getElementById('roomId').value = '';
        document.getElementById('preview').src = ''; // Clear the image preview
        document.getElementById('previewContainer').style.display = 'none'; // Hide the preview
        document.getElementById('uploadBtn').style.display = 'block'; // Show the upload button
        document.getElementById('removeBtn').style.display = 'none'; // Hide the remove button initially
    }

    // Edit room button logic
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.onclick = function() {
            // Populate modal with room data
            document.getElementById('roomId').value = this.dataset.id;
            document.getElementById('room_type').value = this.dataset.room_type;
            document.getElementById('room_number').value = this.dataset.room_number;
            document.getElementById('room_size').value = this.dataset.room_size;
            document.getElementById('price').value = this.dataset.price;
            document.getElementById('rating').value = this.dataset.rating;
            document.getElementById('availability').value = this.dataset.availability;
            document.getElementById('description').value = this.dataset.description;
            
            // Set features in the modal
            const features = this.dataset.features ? this.dataset.features.split(", ") : [];
            $('#features').val(features).trigger('change'); // Set selected features

            // Set amenities in the modal
            const amenities = this.dataset.amenities ? this.dataset.amenities.split(", ") : [];
            $('#amenities').val(amenities).trigger('change');

            // Set image preview
            const imageUrl = this.dataset.image_url;
            document.getElementById('preview').src = imageUrl;
            document.getElementById('previewContainer').style.display = 'block';

            // Show remove button only when there is an image URL
            if (imageUrl) {
                document.getElementById('removeBtn').style.display = 'none'; // Hide remove button when editing
            }

            // Show modal
            document.getElementById('formModal').style.display = 'block';

            // Toggle buttons
            document.querySelector('button[name="add"]').style.display = 'none'; // Hide Add Room button
            document.querySelector('button[name="edit"]').style.display = 'block'; // Show Edit Room button
            
            // Hide upload button during edit
            document.getElementById('uploadBtn').style.display = 'none'; // Hide the upload button
        };
    });

    // Upload button triggers file input
    document.getElementById('uploadBtn').onclick = function() {
        document.getElementById('image_url').click(); // Trigger file input click
    };

    // Display image preview on file upload
    document.getElementById('image_url').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('previewContainer').style.display = 'block'; // Show preview container
                document.getElementById('uploadBtn').style.display = 'none'; // Hide upload button
                document.getElementById('removeBtn').style.display = 'block'; // Show remove button
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove button logic to clear the preview
    document.getElementById('removeBtn').onclick = function() {
        const previewContainer = document.getElementById('previewContainer');
        const preview = document.getElementById('preview');
        preview.src = ''; // Clear the image preview
        previewContainer.style.display = 'none'; // Hide the preview container
        document.getElementById('image_url').value = ''; // Reset file input
        document.getElementById('uploadBtn').style.display = 'block'; // Show upload button again
    };

    // Event to close modal when clicking outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('formModal');
        if (event.target == modal) {
            closeModal();
        }
    };


    function validateForm() {
        const requiredFields = [
            document.getElementById('room_type'),
            document.getElementById('room_number'),
            document.getElementById('room_size'),
            document.getElementById('price'),
            document.getElementById('rating'),
            document.getElementById('availability'),
            document.getElementById('description'),
            document.getElementById('features'),
            document.getElementById('amenities')
        ];
        
        let isValid = true;
    
        // Check each required field
        requiredFields.forEach(field => {
            if (!field.value) {
                isValid = false;
            }
        });
    
        // Only check image input if we are not updating an existing room
        const roomId = document.getElementById('roomId').value;
        const imageInput = document.getElementById('image_url');
    
        if (!roomId && !imageInput.files.length) {
            isValid = false; // Set isValid to false if no image is uploaded and we're adding a new room
        }
    
        // Display validation message if any field is empty
        const validationMessage = document.getElementById('formValidationMessage');
        if (!isValid) {
            validationMessage.style.display = 'block';
        } else {
            validationMessage.style.display = 'none'; // Hide the message if all fields are valid
        }
    
        return isValid; // Return true or false based on form validity
    }
    
    // Update your form submission logic to call validateForm
    document.getElementById('roomForm').onsubmit = function(event) {
        if (!validateForm()) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    };
    
    function openFullScreen(imageUrl) {
        const fullScreenContainer = document.getElementById('fullScreenImageContainer');
        const fullScreenImage = document.getElementById('fullScreenImage');
        
        // Set the image URL
        fullScreenImage.src = imageUrl;
        
        // Display the full-screen container
        fullScreenContainer.style.display = 'flex';
    }

    function closeFullScreenImage() {
        const fullScreenContainer = document.getElementById('fullScreenImageContainer');
        
        // Hide the full-screen container
        fullScreenContainer.style.display = 'none';
    }

    // Close full-screen when 'X' is clicked
    document.getElementById('closeImage').addEventListener('click', closeFullScreenImage);

    // Optional: Close full-screen when clicking outside the image
    document.getElementById('fullScreenImageContainer').addEventListener('click', function(e) {
        if (e.target === this) {
            closeFullScreenImage();
        }
    });

    $(document).ready(function() {
        // Initialize select2 for Features
        function initializeFeaturesSelect2() {
            $('#features').select2({
                placeholder: "Select features",
                allowClear: true,
                closeOnSelect: false,
                dropdownCssClass: 'custom-dropdown',
            });
    
            // Function to limit displayed tags for Features
            function limitDisplayedFeaturesTags() {
                const $selectedTags = $('#features + .select2 .select2-selection__choice');
                const totalTags = $selectedTags.length;
    
                // Clear previous "..more" if exists
                $('#features + .select2 .select2-selection__choice.more').remove();
    
                if (totalTags > 3) {
                    $selectedTags.slice(3).hide(); // Hide all tags after the 3rd
                    $('#features + .select2 .select2-selection__rendered').append(
                        `<li class="select2-selection__choice more">..more</li>`
                    );
                } else {
                    $selectedTags.show(); // Show all tags if 3 or less
                }
            }
    
            $('#features').on('change', function() {
                limitDisplayedFeaturesTags();
            });
    
            $('#features').on('select2:open', function() {
                $('#features + .select2 .select2-selection__choice').show();
                $('#features + .select2 .select2-selection__choice.more').hide();
            });
    
            $('#features').on('select2:close', function() {
                limitDisplayedFeaturesTags();
            });
    
            limitDisplayedFeaturesTags(); // Initial call
        }
    
        // Initialize select2 for Amenities
        function initializeAmenitiesSelect2() {
            $('#amenities').select2({
                placeholder: "Select amenities",
                allowClear: true,
                closeOnSelect: false,
                dropdownCssClass: 'custom-dropdown',
            });
    
            // Function to limit displayed tags for Amenities
            function limitDisplayedAmenitiesTags() {
                const $selectedTags = $('#amenities + .select2 .select2-selection__choice');
                const totalTags = $selectedTags.length;
    
                // Clear previous "..more" if exists
                $('#amenities + .select2 .select2-selection__choice.more').remove();
    
                if (totalTags > 3) {
                    $selectedTags.slice(3).hide(); // Hide all tags after the 3rd
                    $('#amenities + .select2 .select2-selection__rendered').append(
                        `<li class="select2-selection__choice more">..more</li>`
                    );
                } else {
                    $selectedTags.show(); // Show all tags if 3 or less
                }
            }
    
            $('#amenities').on('change', function() {
                limitDisplayedAmenitiesTags();
            });
    
            $('#amenities').on('select2:open', function() {
                $('#amenities + .select2 .select2-selection__choice').show();
                $('#amenities + .select2 .select2-selection__choice.more').hide();
            });
    
            $('#amenities').on('select2:close', function() {
                limitDisplayedAmenitiesTags();
            });
    
            limitDisplayedAmenitiesTags(); // Initial call
        }
    
        // Initialize both independently
        initializeFeaturesSelect2();
        initializeAmenitiesSelect2();
    });
    