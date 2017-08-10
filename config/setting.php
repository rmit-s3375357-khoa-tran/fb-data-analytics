<?php

return [
    'twitter_oauth' => [
        'customer_key'          => getenv('TWITTEROAUTH_CONSUMER_KEY'),
        'customer_secret'       => getenv('TWITTEROAUTH_CONSUMER_SECRET'),
        'access_token'          => getenv('TWITTEROAUTH_ACCESS_TOKEN'),
        'access_token_secret'   => getenv('TWITTEROAUTH_ACCESS_TOKEN_SECRET')
    ]
];