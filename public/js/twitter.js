$(document).ready(function()
{
    $('#stream-twitter').click(function()
    {
        var keyword     = $('#keyword').val(),
            stopWords   = $('#stop-words').val(),
            numOfTweets = $('#number-of-tweets').val(),
            token       = $('#_token').val(),
            startingDate= $('#starting-date').val();

        $(this).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Streaming...');
        $('#collect-twitter').hide();

        $.ajax({
            url: 'twitter/api/stream',
            type: 'post',
            data: {
                'keyword'   : keyword,
                'stopwords' : stopWords,
                'count'     : numOfTweets,
                'date'      : startingDate,
                '_token'    : token
            },
            success: function(response)
            {
                var res = JSON.parse(response);

                if(res['success'])
                {
                    $('#stream-twitter')
                        .html('Stream <i class="fa fa-twitter" aria-hidden="true"></i>')
                        .hide();
                    $('#twitter-explanation').hide();
                    $('#twitter-download-link').attr('href', res["path"]);
                    $('#twitter-alert-success').show();

                    stopWords = stopWords !== ""? stopWords : 'null';
                    var url = 'api/analyse/' + keyword + '/' + stopWords;
                    $('#analyse').attr('href', url).attr('data-ready', true);
                }
                else
                {
                    $('#stream-twitter')
                        .html('Stream <i class="fa fa-twitter" aria-hidden="true"></i>')
                        .hide().delay(30000).fadeIn();
                    $('#collect-twitter').delay(30000).fadeIn();
                    $('#twitter-error-message').text(res['message']);
                    $('#twitter-alert-failure').show().delay(30000).fadeOut();
                }
            }
        })
    });

    $('#collect-twitter').click(function()
    {
        var keyword     = $('#keyword').val(),
            stopWords   = $('#stop-words').val(),
            numOfTweets = $('#number-of-tweets').val(),
            token       = $('#_token').val(),
            startingDate= $('#starting-date').val();

        $(this).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Collecting...');
        $('#stream-twitter').hide();

        $.ajax({
            url: 'twitter/api/collect',
            type: 'post',
            data: {
                'keyword'   : keyword,
                'stopwords' : stopWords,
                'count'     : numOfTweets,
                'date'      : startingDate,
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
                    $('#twitter-explanation').hide();
                    $('#twitter-download-link').attr('href', res["path"]);
                    $('#twitter-alert-success').show();

                    stopWords = stopWords !== ""? stopWords : 'null';
                    var url = 'api/analyse/' + keyword + '/' + stopWords;
                    $('#analyse').attr('href', url).attr('data-ready', true);
                }
                else
                {
                    $('#collect-twitter')
                        .html('Collect <i class="fa fa-twitter" aria-hidden="true"></i>')
                        .hide().delay(30000).fadeIn();
                    $('#stream-twitter').delay(30000).fadeIn();
                    $('#twitter-error-message').text(res['message']);
                    $('#twitter-alert-failure').show().delay(30000).fadeOut();
                }
            }
        })
    });
});