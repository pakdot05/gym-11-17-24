<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script>
function customAlert(type, msg, position = 'body') {
    let bs_class = (type === 'success') ? 'alert-success' : 'alert-danger';
    let element = document.createElement('div');
    element.innerHTML = `
        <div class="alert ${bs_class} alert-dismissible fade show custom-alert text-center" role="alert">
            <strong class="me-3">${msg}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    if (position === 'body') {
        document.body.append(element);
    } else {
        document.getElementById(position).appendChild(element);
        setTimeout(remAlert, 2000);
    }
}

function remAlert() {
    let alertElement = document.getElementsByClassName('alert')[0];
    if (alertElement) {
        alertElement.remove();
    }
}

function setActive() {
    let navbar = document.getElementById('nav-bar');
    
    if (navbar) {  // Check if navbar element exists
        let a_tags = navbar.getElementsByTagName('a');  // Use getElementsByTagName instead of getElementById

        for (let i = 0; i < a_tags.length; i++) {
            let file = a_tags[i].href.split('/').pop();
            let file_name = file.split('.')[0];

            if (document.location.href.indexOf(file_name) >= 0) {
                a_tags[i].classList.add('active');
            }
        }
    } else {
        console.error('Navbar element not found');
    }
}

</script>