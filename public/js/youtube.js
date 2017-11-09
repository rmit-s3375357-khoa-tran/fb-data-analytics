$(document).ready(function()
{
    $('#search-youtube').click(function()
    {
        var keyword     = $('#keyword').val(),
            numOfVideos = $('#number-of-youtube-videos').val(),
            token       = $('#_token').val(),
            startingDate= $('#starting-date').val();

        $.ajax({
            url: 'youtube/api/search',
            type: 'post',
            data: {
                'keyword'   : keyword,
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
                else if(res['message'] === "NoVideoFound")
                {
                    $('#youtube-no-result').show();
                    $('#search-component').hide();
                    $('#search-results-youtube').show();
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

    $('#add-youtube-urls').click(function()
    {
        var urls    = $('#youtube-custom-urls').val(),
            token   = $('#_token').val();

        $.ajax({
            url: 'youtube/api/addCustomUrls',
            type: 'post',
            data: {
                'urls'      : urls,
                '_token'    : token
            },
            success: function(response)
            {
                var res = JSON.parse(response);

                if(! res['success'])
                {
                    $('#youtube-add-url-error').text(res['message']);
                    $('#youtube-add-url-failure').show().delay(30000).fadeOut();
                }
                else
                {
                    var results = res['data'];

                    for(var i=0; i<results.length; i++)
                    {
                        var result = results[i];
                        var index = 10+i;

                        $('#youtube-id-'+index).val(result['videoId']);
                        $('#youtube-title-'+index).text(result['title']);
                        $('#youtube-description-'+index).text(result['description']);
                        $('#youtube-publish-'+index).text(result['publishedAt']);

                        $('#youtube-row-'+index).show();
                    }
                }

                $('#youtube-url-group').hide();
            }
        })
    });

    $('#collect-youtube').click(function()
    {
        var keyword     = $('#keyword').val(),
            count       = $('#youtube-count').val(),
            stopWords   = $('#stop-words').val(),
            token       = $('#_token').val(),
            videoIds    = $('.youtube-video-id:checkbox:checked').map(function()
                            {
                                // collect all the visible checked video ids
                                if($(this).is(":visible"))
                                    return $(this).val();
                            }).get();

        $(this).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Collecting...');
        $('#youtube-count').prop('disabled', true);

        $.ajax({
            url: 'youtube/api/collect',
            type: 'post',
            data: {
                'videoIds'  : videoIds,
                'keyword'   : keyword,
                'count'     : count,
                'stopwords' : stopWords,
                '_token'    : token
            },
            success: function(response)
            {
                var res = JSON.parse(response);

                if(res['success'])
                {
                    $('#search-youtube').hide();
                    $('#youtube-download-link').attr('href', res["path"]);
                    $('#youtube-alert-success').show();

                    $('#search-results-youtube').hide();
                    $('#search-component').show();

                    stopWords = stopWords !== ""? stopWords : 'null';
                    var url = 'api/analyse/' + keyword + '/' + stopWords;
                    $('#analyse').attr('href', url).attr('data-ready', true);
                }
                else
                {
                    $('#collect-youtube').html('Collect <i class="fa fa-youtube" aria-hidden="true"></i>');

                    $('#search-youtube').hide().delay(30000).fadeIn();
                    $('#youtube-error-message').text(res['message']);
                    $('#youtube-alert-failure').show().delay(30000).fadeOut();

                    $('#search-results-youtube').hide();
                    $('#search-component').show();
                }
            }
        })

    });
});