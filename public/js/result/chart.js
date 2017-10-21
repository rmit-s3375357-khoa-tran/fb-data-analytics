/**
 * Created by Nancy on 19-Oct-17.
 */

console.log("Testing");
console.log(pos_twitter_sentiment);
console.log(neg_twitter_sentiment);
console.log(neu_twitter_sentiment);
console.log(pos_yt_sentiment);
console.log(neg_yt_sentiment);
console.log(neu_yt_sentiment);


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
            data: [pos_twitter_sentiment, 0, pos_yt_sentiment]
        }, {
            name: 'Negative',
            data: [neg_twitter_sentiment, 0, neg_yt_sentiment]
        }, {
            name: 'Neutral',
            data: [neu_twitter_sentiment, 0, neu_yt_sentiment]
        }]
    });
});