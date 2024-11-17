

function get_users()
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/appointment.php",true);
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
        xhr.open("POST","ajax/appointment.php",true);

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
    xhr.open("POST","ajax/appointment.php",true);
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
    xhr.open("POST","ajax/appointment.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function(){
        document.getElementById('users-data').innerHTML = this.responseText;
    }

    xhr.send('search_user&name='+username);
}

window.onload = function(){
    get_users();
}
