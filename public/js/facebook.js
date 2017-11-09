$(document).ready(function()
{
    $('#collect-facebook').click(function()
    {
        var keyword         = $('#keyword').val(),
            pageId          = $('#facebook-page-id').val(),
            stopWords       = $('#stop-words').val(),
            numOfComments   = $('#number-of-facebook-comments').val(),
            token           = $('#_token').val(),
            startingDate    = $('#starting-date').val();

        $(this).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Collecting...');

        $.ajax({
            url: 'facebook/api/collect',
            type: 'post',
            data: {
                'keyword'   : keyword,
                'pageId'    : pageId,
                'count'     : numOfComments,
                'date'      : startingDate,
                '_token'    : token
            },
            success: function(response)
            {
                var res = JSON.parse(response);

                if(res['success'])
                {
                    $('#collect-facebook')
                        .html('Collect <i class="fa fa-facebook-official" aria-hidden="true"></i>')
                        .hide();
                    $('#facebook-download-link').attr('href', res["path"]);
                    $('#facebook-alert-success').show();

                    stopWords = stopWords !== ""? stopWords : 'null';
                    var url = 'api/analyse/' + keyword + '/' + stopWords;
                    $('#analyse').attr('href', url).attr('data-ready', true)
                        .removeClass('btn-default').addClass('btn-success');
                }
                else
                {
                    $('#collect-facebook')
                        .html('Collect <i class="fa fa-facebook" aria-hidden="true"></i>')
                        .hide().delay(30000).fadeIn();
                    $('#facebook-error-message').text(res['message']);
                    $('#facebook-alert-failure').show().delay(30000).fadeOut();
                }
            }
        })
    });
});