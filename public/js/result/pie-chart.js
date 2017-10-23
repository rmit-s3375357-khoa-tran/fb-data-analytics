$(document).ready(function() {
    //Calculations
    var total_twitter = pos_twitter_sentiment + neg_twitter_sentiment + neu_twitter_sentiment,
        total_youtube = pos_yt_sentiment + neg_yt_sentiment + neu_yt_sentiment;
    var max = Math.max(total_twitter, total_youtube);
    var new_pos_twitter_sentiment = 0,
        new_neg_twitter_sentiment = 0,
        new_neu_twitter_sentiment = 0,

        new_pos_yt_sentiment = 0,
        new_neg_yt_sentiment = 0,
        new_neu_yt_sentiment = 0;

    if (total_twitter != 0){
        new_pos_twitter_sentiment = pos_twitter_sentiment*(max/total_twitter);
        new_neg_twitter_sentiment = neg_twitter_sentiment*(max/total_twitter);
        new_neu_twitter_sentiment = neu_twitter_sentiment*(max/total_twitter);
    }

    if (total_youtube != 0 ){
        new_pos_yt_sentiment = pos_yt_sentiment*(max/total_youtube);
        new_neg_yt_sentiment = neg_yt_sentiment*(max/total_youtube);
        new_neu_yt_sentiment = neu_yt_sentiment*(max/total_youtube);
    }

    var total_pos = new_pos_twitter_sentiment + new_pos_yt_sentiment,
        total_neg = new_neg_twitter_sentiment + new_neg_yt_sentiment,
        total_neu = new_neu_twitter_sentiment+ new_neu_yt_sentiment,
        total = total_pos + total_neg + total_neu;

    var make_percentage = 100/total;
    total_pos = Math.round(total_pos*make_percentage* 100) / 100;
    total_neg = Math.round(total_neg*make_percentage* 100) / 100;
    total_neu = Math.round(total_neu*make_percentage* 100) / 100;
    new_pos_twitter_sentiment = Math.round(new_pos_twitter_sentiment*make_percentage* 100) / 100;
    new_neg_twitter_sentiment = Math.round(new_neg_twitter_sentiment*make_percentage* 100) / 100;
    new_neu_twitter_sentiment = Math.round(new_neu_twitter_sentiment*make_percentage* 100) / 100;
    new_pos_yt_sentiment = Math.round(new_pos_yt_sentiment*make_percentage* 100) / 100;
    new_neg_yt_sentiment = Math.round(new_neg_yt_sentiment*make_percentage* 100) / 100;
    new_neu_yt_sentiment = Math.round(new_neu_yt_sentiment*make_percentage* 100) / 100;
    //calculations

    var colors = Highcharts.getOptions().colors,
        categories = ['Positive', 'Negative', 'Neutral'],
        data = [{
            y: total_pos,
            color: '#0095BE',
            drilldown: {
                name: 'Positive',
                categories: ['Youtube', 'Twitter', 'Facebook'],
                data: [new_pos_yt_sentiment, new_pos_twitter_sentiment, 0],
                color: colors[0]
            }
        }, {
            y: total_neg,
            color: '#E16361',
            drilldown: {
                name: 'Negative',
                categories: ['Youtube', 'Twitter', 'Facebook'],
                data: [new_neg_yt_sentiment, new_neg_twitter_sentiment, 0],
                color: colors[1]
            }
        }, {
            y: total_neu,
            color:  '#7B8A8E',
            drilldown: {
                name: 'Neutral',
                categories: ['Youtube', 'Twitter', 'Facebook'],
                data: [new_neu_yt_sentiment, new_neu_twitter_sentiment, 0],
                color: colors[2]
            }
        }],
        sentimentData = [],
        platformData = [],
        i,
        j,
        dataLen = data.length,
        drillDataLen,
        brightness;


// Build the data arrays
    for (i = 0; i < dataLen; i += 1) {

        // add browser data
        sentimentData.push({
            name: categories[i],
            y: data[i].y,
            color: data[i].color
        });

        // add version data
        drillDataLen = data[i].drilldown.data.length;
        for (j = 0; j < drillDataLen; j += 1) {
            brightness = 0.2 - (j / drillDataLen) / 5;
            platformData.push({
                name: data[i].drilldown.categories[j],
                y: data[i].drilldown.data[j],
                color: Highcharts.Color(data[i].color).brighten(brightness).get()
            });
        }
    }

// Create the chart
var pie = new Highcharts.Chart({
        chart: {
            renderTo: 'pie',
            type: 'pie'
        },
        title: {
            text: 'Social media sentiment analysis'
        },
        subtitle: {
            text: 'Overall summary'
        },
        yAxis: {
            title: {
                text: 'Total percent market share'
            }
        },
        plotOptions: {
            pie: {
                shadow: false,
                center: ['50%', '50%']
            }
        },
        tooltip: {
            valueSuffix: '%'
        },
        series: [{
            name: 'Sentiment',
            data: sentimentData,
            size: '60%',
            dataLabels: {
                formatter: function () {
                    return this.y > 5 ? this.point.name : null;
                },
                color: '#ffffff',
                distance: -30
            }
        }, {
            name: 'Social media platform',
            data: platformData,
            size: '80%',
            innerSize: '60%',
            dataLabels: {
                formatter: function () {
                    // display only if larger than 1
                    return this.y > 1 ? '<b>' + this.point.name + ':</b> ' +
                    this.y + '%' : null;
                }
            },
            id: 'platform'
        }],
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 400
                },
                chartOptions: {
                    series: [{
                        id: 'platform',
                        dataLabels: {
                            enabled: false
                        }
                    }]
                }
            }]
        }
    });
});