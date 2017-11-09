console.log("Testing");
console.log(pos_twitter_sentiment);
console.log(neg_twitter_sentiment);
console.log(neu_twitter_sentiment);
console.log(pos_yt_sentiment);
console.log(neg_yt_sentiment);
console.log(neu_yt_sentiment);
console.log(pos_fb_sentiment);
console.log(neg_fb_sentiment);
console.log(neu_fb_sentiment);
$(document).ready(function() {
    var chart = new Highcharts.Chart({
        chart: {
            renderTo: 'graph',
            type: 'column'
        },
        title: {
            text: 'Social Media Sentiment Analysis'
        },
        xAxis: {
            categories: [
                'Twitter',
                'Facebook',
                'Youtube'
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: 'sentiment'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: 'Positive',
            color: '#0095BE',
            data: [pos_twitter_sentiment, pos_fb_sentiment, pos_yt_sentiment]
        }, {
            name: 'Negative',
            color: '#E16361',
            data: [neg_twitter_sentiment, neg_fb_sentiment, neg_yt_sentiment]
        }, {
            name: 'Neutral',
            color: '#7B8A8E',
            data: [neu_twitter_sentiment, neu_fb_sentiment, neu_yt_sentiment]
        }]
    });
});