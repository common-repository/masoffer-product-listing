jQuery(document).ready(function($) {
    $("#editShortcodeForm").submit(function(e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.
        $('.mo-loader').show();
        $('.submit input').prop('disabled', true);
        var form = $(this);
        var url = form.attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), // serializes the form's elements.
            success: function(data)
            {
                if(data.status === 'true'){
                    $('#editShortcodeElm #err').hide();
                    $('#editShortcodeElm .urlErrorClass').hide();
                    $('#editShortcodeElm #notice-success').show();
                }else{
                    $('#editShortcodeElm #notice-success').hide();
                    $('#editShortcodeElm #err').show();
                    $('#editShortcodeElm #messageError').html(data.message + ` <span><a href="${data.data}" target="_blank" style="color: rgba(234,84,85,1)">${data.data}</a></span>`);
                }
                $('.mo-loader').hide();
                $('.submit input').prop('disabled', false);
                $(window).scrollTop(0);
            }
        });
    });
});
