
// Pictures form
$('#add-picture').click(function(){
    // get the number of field
    const index = +$('#widgets-counter').val();  

    // get the prototype of the entries
    const tmpl = $('#trick_pictures').data('prototype').replace(/_name_/g, index);

    // injecting data into the <div>
    $('#trick_pictures').append(tmpl);

    // field's number =+1
    $('#widgets-counter').val(index +1); 

    // manages the delete button
    handleDeleteButtons();
});

// Videos form
$('#add-video').click(function(){
    // get the number of field
    const index = +$('#widgets-counter').val();  

    // get the prototype of the entries
    const tmpl = $('#trick_videos').data('prototype').replace(/_name_/g, index);

    // injecting data into the <div>
    $('#trick_videos').append(tmpl);

    // field's number =+1
    $('#widgets-counter').val(index +1); 

    // manages the delete button
    handleDeleteButtons();
});

// delete field
function handleDeleteButtons() {
    $('button[data-action="delete"]').click(function(){
        const target = this.dataset.target
        // console.log(target);
        $(target).remove();
    });
}

function updatePicturesCounter() {
    const count = +$('#trick_pictures div.form-group').length;

    $('#widgets-counter').val(count); 
}

function updateVideosCounter() {
    const count = +$('#trick_videos div.form-group').length;

    $('#widgets-counter').val(count); 
}

updatePicturesCounter()
updateVideosCounter()
handleDeleteButtons();