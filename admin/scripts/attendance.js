

function get_users()
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/attendances.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function(){
        document.getElementById('users-data').innerHTML = this.responseText;
    }

    xhr.send('get_users');

}

function remove_user(id)
{
    if(confirm("Are you sure you want to remove this user?"))
    {
        let data = new FormData();
        data.append('id',id);
        data.append('remove_user','');

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/attendances.php",true);

        xhr.onload = function(){

            if(this.responseText == 1){   
                alert('success', 'User removed!');
                get_users();
            }
            else
            {
                alert('error', 'User removal failed!');
            }               
        }
        xhr.send(data);
    }    
}

function toggle_status(id,val)
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/attendances.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function(){
        if(this.responseText==1){
            alert('success','Status toggled!');
            get_users();
        }
        else{
            alert('success','Server down!');
        }
    }

    xhr.send('toggle_status='+id+'&value='+val);

}

function search_user(username)
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/attendances.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function(){
        document.getElementById('users-data').innerHTML = this.responseText;
    }

    xhr.send('search_user&name='+username);
}

// Call the function after the page is loaded
window.onload = function() {
    get_users(); // Call the function to get users
};
 document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('.nav-link');
        $(document).ready( function () {
            $('#gymTable').DataTable();
        });

 
        function generateRandomCode(length) {
            const characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            let randomString = '';

            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * characters.length);
                randomString += characters.charAt(randomIndex);
            }

            return randomString;
        }

        function generateQrCode() {
            const qrImg = document.getElementById('qrImg');

            let text = generateRandomCode(10);
            $("#generatedCode").val(text);

            if (text === "") {
                alert("Please enter text to generate a QR code.");
                return;
            } else {
                const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(text)}`;

                qrImg.src = apiUrl;
                document.getElementById('name').style.pointerEvents = 'none';
                document.getElementById('address').style.pointerEvents = 'none';
                document.querySelector('.modal-close').style.display = '';
                document.querySelector('.qr-con').style.display = '';
                document.querySelector('.qr-generator').style.display = 'none';
            }
        }

        function setActiveLink() {
            const currentPath = window.location.pathname.split('/').pop();

            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active-nav-link');
                } else {
                    link.classList.remove('active-nav-link');
                }
            });
        }


        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                navLinks.forEach(link => link.classList.remove('active-nav-link'));
                this.classList.add('active-nav-link');
            });
        });

        setActiveLink();
    });

