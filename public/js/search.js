$(document).ready(function()
{
    $('#reset-all').click(function()
    {
        $.ajax({
            url: 'api/empty/results',
            type: 'get',
            success: function(){
                location.reload();
            }
        });
    });

    $('.start-searching').click(function()
    {
        $('.disable-when-searching').prop('disabled', true);
    });

    $('#starting-date').datepicker({
        format: "MM dd, yyyy",
        endDate: "today",
        todayBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $(".check-all").click(function () {
        $("input:checkbox").prop('checked', $(this).prop("checked"));
    });
});