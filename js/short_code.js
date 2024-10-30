jQuery(document).ready(function($) {
    $("input[name='s']").attr('placeholder','id, name, url');
    $('#listShortcodeElm .notice-success').hide();
    $("#checkNowBtn").click(function() {
        $(this).attr("disabled", true);
        var url = '/wp-json/mo_get_product/v1/checkNow';

        $.ajax({
            type: "POST",
            url: url,
            success: function(data)
            {
                $('#checkNowBtn').attr("disabled", false);
                $('#listShortcodeElm .notice-success').html("<p>Check Url Success. Please check email or check 'Total url error' in this table!</p>").show();
            }
        });
    });

    $("#checkUpdateBtn").click(function() {
        $(this).attr("disabled", true);
        var url = '/wp-json/mo_get_product/v1/updateNow';

        $.ajax({
            type: "POST",
            url: url,
            success: function(data)
            {
                $('#checkUpdateBtn').attr("disabled", false);
                $('#listShortcodeElm .notice-success').html("<p>Update Url Success, please wait 10 minutes. Then you can click button 'Check now' or check 'Total url error' in this table!</p>").show();
            }
        });
    });
});
