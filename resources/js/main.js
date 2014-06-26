$(document).ready(function() {
    $("#submit").click(function (e) {
        e.preventDefault();
        var txt = $('#text').val();
        if( txt == '') {
            $('#status').html('Error! Type your message!')
                .removeClass('success')
                .addClass('error');
        } else {
            add();
        }
    });
});

function add() {
    $.ajax({
        type: 'POST',
        contentType: 'application/json',
        url: '/ajaxpost',
        data: {
                "text": $('#text').val()
            },
        success: function(data){
            console.log(data);
            $("#text").val("");
            $('#status').html('Your post is published! <a href="/">See it.</a>')
                .removeClass('error')
                .addClass('success');

        },
        error: function(jqXHR, textStatus){
            $('#status').html('Error!')
                .removeClass('success')
                .addClass('error');
        }
    });
}
