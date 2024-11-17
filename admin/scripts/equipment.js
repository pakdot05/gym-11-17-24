let equipment_s_form = document.getElementById('equipment_s_form');

equipment_s_form.addEventListener('submit', function(e) {
    e.preventDefault();
    add_equipment();
});

function add_equipment() {
    let data = new FormData();
    data.append('name', equipment_s_form.elements['equipment_name'].value);
    data.append('description', equipment_s_form.elements['equipment_desc'].value);
    data.append('image', equipment_s_form.elements['image'].files[0]); // Add image file
    data.append('add_equipment', '');

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/equipment.php", true);

    xhr.onload = function() {
        var myModal = document.getElementById('equipment-s');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        if (this.status == 200) {
            if (this.responseText == '1') {
                alert('success', 'New Equipment Added!');
                equipment_s_form.reset();
                get_equipment();
            } else {
                alert('error', 'Server Down! Error: ' + this.responseText);
            }
        } else {
            alert('error', 'Error: ' + this.statusText);
        }
    }

    xhr.send(data);
}

function get_equipment() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/equipment.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        document.getElementById('equipment-data').innerHTML = this.responseText;
    }

    xhr.send('get_equipment');}