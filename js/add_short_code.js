jQuery(document).ready(function($) {
    $("#addShortcodeForm").submit(function(e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.
        $('.submit input').prop('disabled', true);
        $('.mo-loader').show();
        var form = $(this);
        var url = form.attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), // serializes the form's elements.
            success: function(data)
            {
                if(data.status === 'true'){
                    window.location.href = '/wp-admin/admin.php?page=mo-prod-page-edit-short-code&id='+data.data;
                    let txtShortcode = `[mo_product_listing id=${data.data}]`;
                    $('#shortcodeElm').show();
                    $('#shortcodeElm input').val(txtShortcode);
                    $('#addShortcodeElm #err').hide();
                    $('#addShortcodeElm #notice-success').show();
                }else{
                    $('#shortcodeElm').hide();
                    $('#addShortcodeElm #notice-success').hide();
                    $('#addShortcodeElm #err').show();
                    $('#addShortcodeElm #messageError').html(data.message + ` <span><a href="${data.data}" target="_blank" style="color: rgba(234,84,85,1)">${data.data}</a></span>`);
                }
                $('.mo-loader').hide();
                $('.submit input').prop('disabled', false);
                $(window).scrollTop(0);
            }
        });
    });
});
