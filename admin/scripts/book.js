
function get_users(status) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/bookings.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            if (status === 0) {
                document.getElementById('users-data-pending').innerHTML = this.responseText;
            } else if (status === 1) {
                document.getElementById('users-data-approved').innerHTML = this.responseText;
            } else if (status === 3) {
                document.getElementById('users-data-completed').innerHTML = this.responseText;
            }
        } else {
            console.error("Error: " + xhr.statusText);
        }
    };

    // Correctly format the POST request
    xhr.send('action=get_users&status=' + status);  // Changed from 'get_users&status='
}

function remove_user(id) {
    if (confirm("Are you sure you want to remove this user?")) {
        let data = new FormData();
        data.append('id', id);
        data.append('remove_user', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/booking.php", true);

        xhr.onload = function() {
            if (this.responseText == 1) {
                alert('success', 'User removed!');
                // Reload the relevant tab after the user is removed
                get_users(0); // Assuming you want to reload Pending tab
            } else {
                alert('error', 'User removal failed!');
            }
        }
        xhr.send(data);
    }
}
$(document).on('click', '.toggle-status', function() {
    let button = $(this); 
    let id = button.data('id');
    let currentStatus = button.data('status'); 

    $.ajax({
        url: 'ajax/bookings.php',
        type: 'POST',
        data: {
            toggle_status: 1,
            id: id,
            status: currentStatus 
        },
        success: function(response) {
            if (response == 1) {
                alert('success', 'Status toggled!');

                // Update the button color and state on the client-side
                let nextStatus = currentStatus === 1 ? 3 : 1;

                // Update the button color and state on the client-side
                if (nextStatus === 3) {
                    button.removeClass('btn-primary');
                    button.addClass('btn-success');
                    button.prop('disabled', true);
                    button.text("Completed");
                } else {
                    button.removeClass('btn-danger');
                    button.addClass('btn-primary');
                    button.prop('disabled', false);
                    button.text("Approved");
                }

                // Reload the relevant tab after the status is toggled
                get_users(nextStatus); 
            } else {
                alert('error', 'Server down!');
            }
        }
    });
});

function search_user(username, status) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/booking.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (status === 0) {
            document.getElementById('users-data-pending').innerHTML = this.responseText;
        } else if (status === 1) {
            document.getElementById('users-data-approved').innerHTML = this.responseText;
        } else if (status === 3) {
            document.getElementById('users-data-completed').innerHTML = this.responseText;
        }
    }

    xhr.send('search_user&name=' + username + '&status=' + status);
}
$(document).ready(function() {
    get_users(0);
});

// Handle tab clicks
$(document).on('click', '.nav-link', function() {
    let status = $(this).data('status'); // Get status from data attribute
    get_users(status);
});

window.onload = function() {
    get_users(0); // Load Pending bookings
}