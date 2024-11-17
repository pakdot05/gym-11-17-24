function get_users() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/users.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        document.getElementById('users-data').innerHTML = this.responseText;
        document.querySelectorAll('tr[data-user-id]').forEach(function(row) {
            row.addEventListener('click', function() {
                let userId = this.getAttribute('data-user-id');
                fetchUserDetails(userId);
            });
        });
    }

    xhr.send('get_users=true'); // Send the 'get_users' signal
}
function fetchUserDetails(userId) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/users.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // Use URL-encoded for this request

    xhr.onload = function() {
        if (this.responseText) {
            document.getElementById('modal-user-details').innerHTML = this.responseText;
            let userDetailsModal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
            userDetailsModal.show();

            // Add event listeners to the status buttons in the modal after loading
            document.querySelectorAll('button[data-booking-id]').forEach(function(button) {
                button.addEventListener('click', function() {
                    let bookingId = this.getAttribute('data-booking-id');
                    let currentStatus = parseInt(this.textContent.trim().replace(/[^0-9]/g, '')); // Extract number from button text
                    toggle_status(bookingId, currentStatus + 1); // Increment the status value
                });
            });

            // Update initial button status based on the actual booking status
            document.querySelectorAll('button[data-booking-id]').forEach(function(button) {
                let bookingId = button.getAttribute('data-booking-id');
                updateButtonStatus(bookingId); // Call updateButtonStatus to fetch and update the status
            });
        } else {
            alert('error', 'Failed to fetch user details!');
        }
    }

    xhr.send('fetch_user_details=' + userId);
}

function remove_booking(booking_id) {
    if (confirm("Are you sure you want to delete this appointment?")) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/users.php", true); // Assuming "ajax/users.php" handles both user and booking removal
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (this.responseText == 1) {
                alert('success', 'Appointment deleted!');
                // Update your UI to reflect the deletion (e.g., refresh the booking table)
                // ...
            } else {
                alert('error', 'Appointment deletion failed!');
            }
        }

        // Send the data as URL-encoded parameters
        xhr.send('remove_booking=1&booking_id=' + booking_id); 
    }
}

function toggle_status(bookingId) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/users.php", true);

    // Retrieve current status from the button's data attribute
    let statusButton = document.querySelector(`button[data-booking-id="${bookingId}"]`);
    let currentStatus = parseInt(statusButton.getAttribute('data-status'));

    // Calculate the next status based on the current status
    let newStatus = currentStatus + 1; 
    if (newStatus > 4) {  // Loop back or disable after 'Completed'
        newStatus = 4; // Keep at 'Completed' if it exceeds the maximum status value
    }

    // Create FormData and set the new status
    let formData = new FormData();
    formData.append('toggle_booking_status', bookingId);
    formData.append('value', newStatus);

    xhr.onload = function() {
        if (this.responseText == 1) {
            alert('success', 'Status updated!');
            // Update the button text and styles based on new status
            switch (newStatus) {
               
                case 0:
                    statusButton.textContent = 'Pending';
                    statusButton.classList.remove('btn-success', 'btn-danger', 'btn-dark');
                    statusButton.classList.add('btn-secondary');
                    break;
                case 1:
                    statusButton.textContent = 'Approved';
                    statusButton.classList.remove('btn-success', 'btn-danger', 'btn-secondary');
                    statusButton.classList.add('btn-dark');
                    break;
                case 2:
                    statusButton.textContent = 'Completed';
                    statusButton.classList.remove('btn-secondary', 'btn-dark');
                    statusButton.classList.add('btn-success');
                    statusButton.disabled = true; // Disable button after 'Completed'
                    break;
            }
            statusButton.setAttribute('data-status', newStatus); // Update data-status attribute
        } else {
            alert('error', 'Failed to update status');
        }
    };

    xhr.send(formData);
}
function toggleUserAccountStatus(userId, newStatus) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/users.php", true);
    
    let formData = new FormData();
    formData.append('toggle_user_status', true);
    formData.append('user_id', userId);
    formData.append('value', newStatus);
    
    xhr.onload = function() {
        if (this.responseText == '1') {
            customAlert('success', 'User status updated successfully!');  // Updated to use customAlert
            
            let userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
            let statusButton = userRow ? userRow.querySelector('button') : null;
            
            if (statusButton) {
                if (newStatus == 0) {
                    statusButton.textContent = 'Active';
                    statusButton.classList.add('btn-success');
                    statusButton.classList.remove('btn-danger');
                } else {
                    statusButton.textContent = 'Inactive';
                    statusButton.classList.add('btn-danger');
                    statusButton.classList.remove('btn-success');
                }
                
                statusButton.setAttribute('onclick', `toggleUserAccountStatus(${userId}, ${newStatus})`);

                // Check if the user has any activity
                fetchUserActivityStatus(userId, statusButton); 
            }
        } else {
            customAlert('error', 'Status Update Succesfully.');
        }
    };

    xhr.send(formData);
}
function remove_user(user_id) {
    if (confirm("Are you sure you want to delete this user?")) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/users.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (this.responseText == 1) {
                alert('User deleted successfully!');
                get_users(); // Refresh the user list
            } else {
                alert('Failed to delete user.');
            }
        }

        xhr.send('remove_user=1&user_id=' + user_id); // Sending remove_user signal along with user_id
    }
}

function fetchUserActivityStatus(userId, statusButton) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/users.php", true); // Assuming you have a users.php to handle this request

    xhr.onload = function() {
        if (this.responseText === '1') { 
        } else {
            if (statusButton.textContent === 'Active') {
                setTimeout(function() {
                    // Set the status to inactive after 7 days
                    toggleUserAccountStatus(userId, 1); // Assuming 1 is the inactive status
                }, 7 * 24 * 60 * 60 * 1000); // 7 days in milliseconds
            }
        }
    };

    xhr.send('check_user_activity&user_id=' + userId); // Send the request to check user activity
}


function search_user(username) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/users.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        document.getElementById('users-data').innerHTML = this.responseText;
        document.querySelectorAll('tr[data-user-id]').forEach(function(row) {
            row.addEventListener('click', function() {
                let userId = this.getAttribute('data-user-id');
                fetchUserDetails(userId);
            });
        });
    }

    xhr.send('search_user&name=' + username);
}

window.onload = function() {
    get_users();
}