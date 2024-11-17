let add_trainor_form = document.getElementById('add_trainor_form');

add_trainor_form.addEventListener('submit',function(e){
    e.preventDefault();
    add_trainor();
});

function add_trainor()
{
    let data = new FormData();
    data.append('add_trainor','');
    data.append('name',add_trainor_form.elements['name'].value);
    data.append('address',add_trainor_form.elements['address'].value);
    data.append('contact_no',add_trainor_form.elements['contact_no'].value);
    data.append('email',add_trainor_form.elements['email'].value);
    data.append('info',add_trainor_form.elements['info'].value);
    
    let specialty = [];

    add_trainor_form.elements['specialty'].forEach(el =>{
        if(el.checked){
            specialty.push(el.value);
        }
    })

    data.append('specialty',JSON.stringify(specialty));

    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/trainors.php",true);

    xhr.onload = function(){

        var myModal = document.getElementById('add-trainor');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        if(this.responseText == 1){          
            alert('success', 'New Trainor Added!');
            add_trainor_form.reset();
            get_all_trainor();
        }
        else
        {
            alert('error', 'Server Down!');
        }               
    }

    xhr.send(data);

}

        function get_all_trainor()
        {
            let xhr = new XMLHttpRequest();
            xhr.open("POST","ajax/trainors.php",true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function(){
                document.getElementById('trainor-data').innerHTML = this.responseText;
            }

            xhr.send('get_all_trainor');

        }

        let edit_trainor_form = document.getElementById('edit_trainor_form');

    
        function edit_details(id)
        {
            let xhr = new XMLHttpRequest();
            xhr.open("POST","ajax/trainors.php",true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function(){
                let data = JSON.parse(this.responseText);  

                edit_trainor_form.elements['name'].value = data.trainordata.name;
                edit_trainor_form.elements['address'].value = data.trainordata.address;
                edit_trainor_form.elements['contact_no'].value = data.trainordata.contact_no;
                edit_trainor_form.elements['email'].value = data.trainordata.email;
                edit_trainor_form.elements['info'].value = data.trainordata.info;
                edit_trainor_form.elements['trainor_id'].value = data.trainordata.trainor_id;

                edit_trainor_form.elements['specialty'].forEach(el =>{
                    if(data.specialty.includes(Number(el.value))){
                        el.checked = true;
                    }
                })
            }

            xhr.send('get_trainor='+id);
        }

        edit_trainor_form.addEventListener('submit',function(e){
            e.preventDefault();
            submit_edit_trainor();
        });

        function submit_edit_trainor()
        {
            let data = new FormData();
            data.append('update_trainor','');
            data.append('trainor_id',edit_trainor_form.elements['trainor_id'].value);
            data.append('name',edit_trainor_form.elements['name'].value);
            data.append('address',edit_trainor_form.elements['address'].value);
            data.append('contact_no',edit_trainor_form.elements['contact_no'].value);
            data.append('email',edit_trainor_form.elements['email'].value);
            data.append('info',edit_trainor_form.elements['info'].value);
            
            let specialty = [];

            edit_trainor_form.elements['specialty'].forEach(el =>{
                if(el.checked){
                    specialty.push(el.value);
                }
            })

            data.append('specialty',JSON.stringify(specialty));

            let xhr = new XMLHttpRequest();
            xhr.open("POST","ajax/trainors.php",true);

            xhr.onload = function(){

                var myModal = document.getElementById('edit-trainor');
                var modal = bootstrap.Modal.getInstance(myModal);
                modal.hide();

                if(this.responseText == 1){   
                    alert('success', 'Trainor Data Edited!');
                    edit_trainor_form.reset();
                    get_all_trainor();
                }
                else
                {
                    alert('error', 'Server Down!');
                }               
            }

            xhr.send(data);

        }

        function remove_trainor(trainor_id)
        {
            if(confirm("Are you sure you want to remove this trainor?"))
            {
                let data = new FormData();
                data.append('trainor_id',trainor_id);
                data.append('remove_trainor','');

                let xhr = new XMLHttpRequest();
                xhr.open("POST","ajax/trainors.php",true);

                xhr.onload = function(){

                    if(this.responseText == 1){   
                        alert('success', 'Trainors removed!');
                        get_all_trainor();
                    }
                    else
                    {
                        alert('error', 'Trainors removal failed!');
                    }               
                }
                xhr.send(data);
            }    
        }

        function toggle_status(id,val)
        {
            let xhr = new XMLHttpRequest();
            xhr.open("POST","ajax/trainors.php",true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function(){
                if(this.responseText==1){
                    alert('success','Status toggled!');
                    get_all_trainor();
                }
                else{
                    alert('success','Server down!');
                }
            }

            xhr.send('toggle_status='+id+'&value='+val);

        }
        let add_image_form = document.getElementById('add_image_form');
        add_image_form.addEventListener('submit', function (e) {
            e.preventDefault();
            add_image();
        });
        
        function add_image() {
            let data = new FormData();
            data.append('image', add_image_form.elements['image'].files[0]);
            data.append('trainor_id', add_image_form.elements['trainor_id'].value);
            data.append('add_image', ''); // Trigger PHP handler
        
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/trainors.php", true);
        
            xhr.onload = function () {
                console.log("Server Response: ", this.responseText); // Log the response for debugging
                
                let imageAlert = document.getElementById('image-alert');
                imageAlert.innerHTML = ''; // Clear previous alert
        
                // Additional debug information
                console.log("Response status:", this.status);
                console.log("Response readyState:", this.readyState);
        
                if (this.responseText === 'inv_img') {
                    imageAlert.innerHTML = '<div class="alert alert-danger">Only JPG, WEBP, or PNG images are allowed!</div>';
                } else if (this.responseText === 'inv_size') {
                    imageAlert.innerHTML = '<div class="alert alert-danger">Image size should be less than 2MB!</div>';
                } else if (this.responseText === 'upd_failed') {
                    imageAlert.innerHTML = '<div class="alert alert-danger">Image upload failed. Server down!</div>';
                } else if (this.responseText === 'success') {
                    imageAlert.innerHTML = '<div class="alert alert-success">Image added successfully!</div>';
                    trainor_images(add_image_form.elements['trainor_id'].value, document.querySelector("#trainor-images .modal-title").innerText);
                    add_image_form.elements['image'].value = ''; // Clear file input
                } else {
                    imageAlert.innerHTML = '<div class="alert alert-danger">Images Inserted Successfully: ' + this.responseText + '</div>';
                }
            };
        
            xhr.send(data);
        }
        
        
        function trainor_images(id, rname) {
            document.querySelector("#trainor-images .modal-title").innerText = rname;
            add_image_form.elements['trainor_id'].value = id;
        
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/trainors.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
            xhr.onload = function() {
                document.getElementById('trainor-image-data').innerHTML = this.responseText;
            };
        
            xhr.send('get_trainor_images=' + id);
        }
        function rem_image(img_id, trainor_id) {
            let data = new FormData();
            data.append('image_id', img_id);
            data.append('trainor_id', trainor_id);
            data.append('rem_image', '');
    
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/trainors.php", true);
            xhr.onload = function() {
                if (this.responseText == 1) {
                    alert('Success','Image Removed !', 'image-alert');
                    trainor_images(trainor_id, document.querySelector("#trainor-images .modal-title").innerText);
                } else {
                    alert('error','Image removal failed!', 'image-alert');
                }
            };
            xhr.send(data);
        }
        function thumb_image(img_id, trainor_id) {
            let data = new FormData();
            data.append('image_id', img_id);
            data.append('trainor_id', trainor_id);
            data.append('thumb_image', '');
        
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/trainors.php", true);
            xhr.onload = function() {
                console.log("Response Text:", this.responseText); // Log the response for debugging
                if (this.responseText == 1) {
                    alert('success', 'Thumbnail Changed!', 'image-alert');
                    trainor_images(trainor_id, document.querySelector("#trainor-images .modal-title").innerText);
                } else {
                    alert('error', 'Thumbnail removal failed!', 'image-alert');
                }
            };
            xhr.send(data);
        }
        
        // Assuming alert is a custom function, it should be defined like this:
        function alert(type, msg, position) {
            // Your custom alert implementation here
            console.log(type, msg, position); // Example implementation for debugging
        }
        
        window.onload = function(){
            get_all_trainor();
        }