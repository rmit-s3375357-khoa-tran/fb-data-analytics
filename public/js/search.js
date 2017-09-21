$(document).ready(function()
{
    var keyword = $('#keyword').val(),
        stopWords = $('#stop-words').val(),
        numOfTweets = $('#number-of-tweets').val(),
        numOfFBPages = $('#number-of-facebook-pages').val(),
        numOfYTVideos = $('#number-of-youtube-videos').val();

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
            success: function(response) {
                $('#collect-twitter')
                    .html('Collect <i class="fa fa-twitter" aria-hidden="true"></i>')
                    .hide().delay(30000).fadeIn();
                $('#twitter-download-link').html('<em><a href="'+response+'">here</a></em>');
                $('#twitter-alert').show().delay(30000).fadeOut();
            }
        })
    });

});