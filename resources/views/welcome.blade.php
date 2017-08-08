<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Laravel 5</div>
                <?php
                    use Abraham\TwitterOAuth\TwitterOAuth;
                    $CONSUMER_KEY = "JLbyO2CtUynqtNHJnhrNZ6PZl";
                    $CONSUMER_SECRET = "i2OwurNBeCVbh6kw7tgysmOaDKt3RB8HYnmwRRtwDCCudzsjNV";
                    $access_token = "894781214451617792-angr8pULQrjcKFLmu42U739Uv0hTpd3";
                    $access_token_secret = "WC8v9XS5jJU2CV1yXYhEN21N6iUlHVamClR40HoF75Dsf";
                    $connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $access_token, $access_token_secret);
                    $content = $connection->get("account/verify_credentials");
                    $statuses = $connection->get("search/tweets", ["q" => "twitterapi"]);
                    var_dump($statuses);
                ?>
            </div>
        </div>
    </body>
</html>
