

let specialty_s_form = document.getElementById('specialty_s_form');

specialty_s_form.addEventListener('submit',function(e){
    e.preventDefault();
    add_specialty();
});

function add_specialty() {
    let data = new FormData();
    data.append('name', specialty_s_form.elements['specialty_name'].value);
    data.append('description', specialty_s_form.elements['specialty_desc'].value); // Adjusted to 'specialty_desc'
    data.append('add_specialty', '');

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/specialty.php", true);

    xhr.onload = function() {
        var myModal = document.getElementById('specialty-s');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        if (this.responseText == 1) {
            alert('success', 'New Service Added!');
            specialty_s_form.reset();
            get_specialty();
        } else {
            alert('error', 'Server Down!');
        }
    }

    xhr.send(data);
}


function get_specialty()
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/specialty.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function(){
        document.getElementById('specialty-data').innerHTML = this.responseText;
    }

    xhr.send('get_specialty');
}

function rem_specialty(val)
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/specialty.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function(){
        if(this.responseText == 1){
            alert('success', 'Service removed!');
            get_specialty();
        }
        else if(this.responseText == 'trainor_added')
        {
            alert('error', 'Service is added in trainor!'); 
        }
        else
        {
            alert('error', 'Server down!');
        }
    }

    xhr.send('rem_specialty='+val);
}

window.onload = function(){
    get_specialty();
}
