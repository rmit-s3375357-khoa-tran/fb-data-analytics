$(document).ready(function()
{
    $('#collect-twitter').click(function()
    {
        var keyword     = $('#keyword').val(),
            stopWords   = $('#stop-words').val(),
            numOfTweets = $('#number-of-tweets').val(),
            token       = $('#_token').val();

        $(this).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Collecting...');

        $.ajax({
            url: 'twitter/api/collect',
            type: 'post',
            data: {
                'keyword'   : keyword,
                'stopwords' : stopWords,
                'count'     : numOfTweets,
                '_token'    : token
            },
            success: function(response)
            {
                var res = JSON.parse(response);

                if(res['success'])
                {
                    $('#collect-twitter')
                        .html('Collect <i class="fa fa-twitter" aria-hidden="true"></i>')
                        .hide();
                    $('#twitter-download-link').attr('href', res["path"]);
                    $('#twitter-alert-success').show();
                }
                else
                {
                    $('#collect-twitter')
                        .html('Collect <i class="fa fa-twitter" aria-hidden="true"></i>')
                        .hide().delay(30000).fadeIn();
                    $('#twitter-error-message').text(res['message']);
                    $('#twitter-alert-failure').show().delay(30000).fadeOut();
                }
            }
        })
    });
});