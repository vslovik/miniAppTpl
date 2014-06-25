var rootURL = "http://localhost/cellar/api/wines";

$('#btnSave').click(function() {
    add();
    return false;
});

function add() {
    $.ajax({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL,
        dataType: "json",
        data: JSON.stringify({
                "name": $('#name').val()
            }),
        success: function(data){
            $('#btnDelete').show();
            $('#wineId').val(data.id);
        },
        error: function(jqXHR, textStatus){
            alert('addWine error: ' + textStatus);
        }
    });
}
