$(document).ready(function()
{
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

    $('#analyse').click(function(){
        var ready = $(this).attr('data-ready');
        if(ready) {
            $(this).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Analysing...');
        }
    });
});