$(document).ready(function() {

    $( "#twitter_pos_button" ).click(function() {
        $('#twitterData').toggle();
        var table_body = "";
        twitter_data.forEach(function(data, index) {
            if (data['sentiment'] == 'positive')
            {
                table_body += "<tr>"+
                    "<td>"+index+1+"</td>"+
                    "<td>"+data['created_at']+"</td>"+
                    "<td><div>"+data['text']+"</div></td>"+
                    "<td><div>"+data['location']+"</div></td>"+
                    "</tr>";
            }
        });
        $('#twitterData').html(start_table + table_body + end_table);
    });

    $( "#twitter_neu_button" ).click(function() {
        $('#twitterData').toggle();
        var table_body = "";
        twitter_data.forEach(function(data, index) {
            if (data['sentiment'] == 'neutral')
            {
                table_body += "<tr>"+
                    "<td>"+index+1+"</td>"+
                    "<td>"+data['created_at']+"</td>"+
                    "<td><div>"+data['text']+"</div></td>"+
                    "<td><div>"+data['location']+"</div></td>"+
                    "</tr>";
            }
        });
        $('#twitterData').html(start_table + table_body + end_table);
    });

    $( "#twitter_neg_button" ).click(function() {
        $('#twitterData').toggle();
        var table_body = "";
        twitter_data.forEach(function(data, index) {
            if (data['sentiment'] == 'negative')
            {
                table_body += "<tr>"+
                    "<td>"+index+1+"</td>"+
                    "<td>"+data['created_at']+"</td>"+
                    "<td><div>"+data['text']+"</div></td>"+
                    "<td><div>"+data['location']+"</div></td>"+
                    "</tr>";
            }
        });
        $('#twitterData').html(start_table + table_body + end_table);
    });
//////////////////
    $( "#youtube_pos_button" ).click(function() {
        $('#youtubeData').toggle();
        var table_body = "";
        youtube_data.forEach(function(data, index) {
            if (data['sentiment'] == 'positive')
            {
                var location = data['location'];
                if (location == undefined) { location = ""; }
                table_body += "<tr>"+
                    "<td>"+index+1+"</td>"+
                    "<td>"+data['created_at']+"</td>"+
                    "<td><div>"+data['text']+"</div></td>"+
                    "<td><div>"+data['author_display_name']+"</div></td>"+
                    "<td><div>"+data['author_channel_url']+"</div></td>"+
                    "<td><div>"+location+"</div></td>"+
                    "</tr>";
            }
        });
        $('#youtubeData').html(yt_start_table + table_body + end_table);
    });

    $( "#youtube_neu_button" ).click(function() {
        $('#youtubeData').toggle();
        var table_body = "";
        youtube_data.forEach(function(data, index) {
            if (data['sentiment'] == 'neutral')
            {
                var location = data['location'];
                if (location == undefined) { location = ""; }
                table_body += "<tr>"+
                    "<td>"+index+1+"</td>"+
                    "<td>"+data['created_at']+"</td>"+
                    "<td><div>"+data['text']+"</div></td>"+
                    "<td><div>"+data['author_display_name']+"</div></td>"+
                    "<td><div>"+data['author_channel_url']+"</div></td>"+
                    "<td><div>"+location+"</div></td>"+
                    "</tr>";
            }
        });
        $('#youtubeData').html(yt_start_table + table_body + end_table);
    });

    $( "#youtube_neg_button" ).click(function() {
        $('#youtubeData').toggle();
        var table_body = "";
        youtube_data.forEach(function(data, index) {
            if (data['sentiment'] == 'negative')
            {
                var location = data['location'];
                if (location == undefined) { location = ""; }
                table_body += "<tr>"+
                    "<td>"+index+1+"</td>"+
                    "<td>"+data['created_at']+"</td>"+
                    "<td><div>"+data['text']+"</div></td>"+
                    "<td><div>"+data['author_display_name']+"</div></td>"+
                    "<td><div>"+data['author_channel_url']+"</div></td>"+
                    "<td><div>"+location+"</div></td>"+
                    "</tr>";
            }
        });
        $('#youtubeData').html(yt_start_table + table_body + end_table);
    });

});

var start_table = "<table class='table-striped table-responsive'> <thead> <tr>"+
    "<td></td>"+
    "<td><h4>Time</h4></td> " +
    "<td><h4>Tweet</h4></td>" +
    "<td><h4>User Location</h4></td> " +
    "</tr></thead><tbody>";

var yt_start_table = "<table class='table-striped table-responsive'> <thead> <tr>"+
    "<td></td>"+
    "<td><h4>Time</h4></td> " +
    "<td><h4>Comment</h4></td>" +
    "<td><h4>Username</h4></td> " +
    "<td><h4>User url</h4></td> " +
    "<td><h4>User Location</h4></td> " +
    "</tr></thead><tbody>";

var end_table = "</tbody></table>";

