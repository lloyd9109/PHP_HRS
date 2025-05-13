const textCenterDiv = document.querySelector('.text-center');

// Open modal for adding new room
document.getElementById('addRoomBtn').onclick = function() {
    resetModal(); // Reset the modal contents for new input
    document.getElementById('formModal').style.display = 'block'; // Show the modal
    textCenterDiv.textContent = 'Add Room';

    document.querySelector('button[name="add"]').style.display = 'block'; // Show Add Room button
    document.querySelector('button[name="edit"]').style.display = 'none'; // Hide Update Room button
    $('#room_number').val('00'); 

    $('#features').val([]).trigger('change');
    $('#amenities').val([]).trigger('change');
};

$('#room_type').change(function() {
    var roomType = $(this).val();
    if (roomType) {
        // Fetch the next room number for the selected room type
        $.get('fetch_next_room_number.php', { room_type: roomType }, function(data) {
            $('#room_number').val(data); // Update room number with the prefix
        });
    } else {
        $('#room_number').val('00'); // Default value if no room type is selected
    }
});


// Function to close modal
function closeModal() {
    document.getElementById('formModal').style.display = 'none'; 
    
    // Reset all error messages
    const fieldsToValidate = [
        'room_type', 'room_size', 'price', 'availability', 'description', 'features', 'amenities', 'image_url', 'guests'
    ];
    
    fieldsToValidate.forEach(fieldId => {
        const errorElement = document.getElementById(`${fieldId}_error`);
        if (errorElement) {
            errorElement.textContent = ""; // Clear error text
        }
    });
}
// Event to close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('formModal');
    if (event.target == modal) {
        closeModal();
    }
};
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
    btn.onclick = function () {
        resetModal(); // Reset modal contents
        textCenterDiv.textContent = 'Edit Room';

        // Populate modal with room data
        document.getElementById('roomId').value = this.dataset.id;
        document.getElementById('room_type').value = this.dataset.room_type;
        document.getElementById('guests').value = this.dataset.guests;
        document.getElementById('room_number').value = this.dataset.room_number;
        document.getElementById('room_size').value = this.dataset.room_size;
        document.getElementById('price').value = this.dataset.price;
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
        document.getElementById('previewContainer').style.display = 'block'; // Show the preview container

        // Show remove button only when there is an image URL
        if (imageUrl) {
            document.getElementById('removeBtn').style.display = 'block'; // Show remove button
        } else {
            document.getElementById('removeBtn').style.display = 'none'; // Hide remove button if no image
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

// Event listener for file input change
document.getElementById('image_url').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        const validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];

        // Check if the file type is valid
        if (!validImageTypes.includes(file.type)) {
            // Show SweetAlert error if the file is not an image
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: 'Please select a valid image file (jpeg, png, gif).',
            });

            // Clear the file input and hide the preview container
            document.getElementById('image_url').value = ''; // Reset file input
            document.getElementById('previewContainer').style.display = 'none'; // Hide the preview container
            document.getElementById('removeBtn').style.display = 'none'; // Hide the remove button
        } else {
            // If file is valid, display it in the preview
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('previewContainer').style.display = 'block'; // Show preview container
                document.getElementById('uploadBtn').style.display = 'none'; // Hide upload button
                document.getElementById('removeBtn').style.display = 'block'; // Show remove button
            };
            reader.readAsDataURL(file);
        }
    }
});
// Flag to track if the image is removed during edit
let imageRemoved = false;

document.getElementById('removeBtn').onclick = function(event) {
    event.preventDefault(); // Prevent form submission

    const previewContainer = document.getElementById('previewContainer');
    const preview = document.getElementById('preview');
    preview.src = ''; // Clear the image preview
    previewContainer.style.display = 'none'; // Hide the preview container
    document.getElementById('image_url').value = ''; // Reset file input (so that it's like no file was selected)
    document.getElementById('uploadBtn').style.display = 'block'; // Show the upload button again
    document.getElementById('removeBtn').style.display = 'none'; // Hide remove button
    
    // Mark that the image has been removed during edit
    imageRemoved = true;
};

// Updated validateForm function to skip image validation during edit if image exists
function validateForm() {
    let isValid = true;

    const errorMessages = {
        room_type: "Please select a room type.",
        guests: "Guests to Accommodate is required.",
        room_size: "Room size is required.",
        price: "Price is required.",
        availability: "Please select availability.",
        description: "Description is required.",
        features: "Please select at least one feature.",
        amenities: "Please select at least one amenity.",
        image_url: "Please upload an image."
    };

    const fieldsToValidate = [
        { id: 'room_type', type: 'select' },
        { id: 'guests', type: 'select' },
        { id: 'room_size', type: 'input' },
        { id: 'price', type: 'input' },
        { id: 'availability', type: 'select' },
        { id: 'description', type: 'input' },
        { id: 'features', type: 'multiselect' },
        { id: 'amenities', type: 'multiselect' },
        { id: 'image_url', type: 'file' }
    ];

    // Reset all error messages
    fieldsToValidate.forEach(field => {
        const errorElement = document.getElementById(`${field.id}_error`);
        if (errorElement) {
            errorElement.textContent = ""; // Clear error text
        }
    });

    // Determine if the form is in edit mode
    const isEditMode = document.getElementById('roomId').value !== ''; // If roomId exists, it's edit mode

    // Validate each field
    fieldsToValidate.forEach(field => {
        const element = document.getElementById(field.id);
        const errorElement = document.getElementById(`${field.id}_error`);

        if (field.type === 'select' && (!element.value || element.value === "")) {
            errorElement.textContent = errorMessages[field.id];
            isValid = false;
        } else if (field.type === 'input' && element.value.trim() === "") {
            errorElement.textContent = errorMessages[field.id];
            isValid = false;
        } else if (field.type === 'multiselect' && (!element.value || element.selectedOptions.length === 0)) {
            errorElement.textContent = errorMessages[field.id];
            isValid = false;
        } else if (field.type === 'file') {
            // Skip image validation if already has an image during edit
            if (isEditMode && document.getElementById('preview').src !== '' && !document.getElementById('image_url').files[0]) {
                return; // Skip file validation
            }

            const fileInput = element.files[0];
            if (!fileInput) {
                errorElement.textContent = errorMessages[field.id];
                isValid = false;
            } else {
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validImageTypes.includes(fileInput.type)) {
                    errorElement.textContent = "Only image files are allowed.";
                    isValid = false;
                }
            }
        }
    });

    return isValid;
}

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
