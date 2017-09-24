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
                    $('#twitter-download-link').html('<em><a href="'+res["path"]+'">download</a></em>');
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

    $('#search-youtube').click(function()
    {
        var keyword     = $('#keyword').val(),
            stopWords   = $('#stop-words').val(),
            numOfVideos = $('#number-of-youtube-videos').val(),
            token       = $('#_token').val(),
            startingDate= $('#starting-date').val();

        $.ajax({
            url: 'youtube/api/search',
            type: 'post',
            data: {
                'keyword'   : keyword,
                'stopwords' : stopWords,
                'count'     : numOfVideos,
                'date'      : startingDate,
                '_token'    : token
            },
            success: function(response)
            {
                var res = JSON.parse(response);

                if(! res['success'])
                {
                    $('#search-youtube').hide().delay(30000).fadeIn();
                    $('#youtube-error-message').text(res['message']);
                    $('#youtube-alert-failure').show().delay(30000).fadeOut();
                }
                else
                {
                    var results = res['data'];
                    $('.keyword-text').text(keyword);

                    for(var i=0; i<results.length; i++)
                    {
                        var result = results[i];

                        $('#youtube-id-'+i).val(result['videoId']);
                        $('#youtube-title-'+i).text(result['title']);
                        $('#youtube-description-'+i).text(result['description']);
                        $('#youtube-publish-'+i).text(result['publishedAt']);

                        $('#youtube-row-'+i).show();
                    }

                    $('#search-component').hide();
                    $('#search-results-youtube').show();
                }
            }
        })
    });
});