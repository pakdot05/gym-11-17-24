function get_users() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/booking.php", true); // Corrected URL
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        document.getElementById('users-data').innerHTML = this.responseText;
    }

    xhr.send('get_users');
}

function remove_user(btn) {
    let bookingId = btn.getAttribute('data-id'); 

    if (confirm('Are you sure you want to delete this booking?')) {
        let data = new FormData();
        data.append('id', bookingId);
        data.append('remove_user', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/booking.php", true); // Corrected URL

        xhr.onload = function() {
            if (this.responseText == 1) {
                alert('success', 'Booking deleted!');
                get_users();
            } else {
                alert('error', 'Booking deletion failed!');
            }
        }
        xhr.send(data);
    }
}
function toggle_status(id, value) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/booking.php", true); // Corrected URL
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (this.responseText == 1) {
            alert('success', 'Status toggled!');
            get_users();
        } else {
            alert('error', 'Server down!');
        }
    }

    xhr.send('toggle_status=' + id + '&value=' + value);
}

function search_user(value) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/booking.php", true); // Corrected URL
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        document.getElementById('users-data').innerHTML = this.responseText;
    }

    xhr.send('search_user&name=' + value);
}

window.onload = function() {
    get_users();
}