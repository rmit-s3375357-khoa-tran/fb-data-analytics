<?php

return [
    'twitter_oauth' => [
        'customer_key'          => getenv('TWITTEROAUTH_CONSUMER_KEY'),
        'customer_secret'       => getenv('TWITTEROAUTH_CONSUMER_SECRET'),
        'access_token'          => getenv('TWITTEROAUTH_ACCESS_TOKEN'),
        'access_token_secret'   => getenv('TWITTEROAUTH_ACCESS_TOKEN_SECRET')
    ],

    'google_map' => [
        'key' => getenv('Google_MAP_API')
    ],

    'datum_box' => [
        'key' => getenv('DATUM_BOX_API'),
        'key2' => getenv('DATUM_BOX_API2'),
        'key3' => getenv('DATUM_BOX_API3'),
        'key4' => getenv('DATUM_BOX_API4')
    ],

    'youtube' => [
        'key' => getenv('YOUTUBE_API')
    ],

    'facebook' => [
        'id' => getenv('FACEBOOK_API_ID'),
        'secret' => getenv('FACEBOOK_API_SECRET')
    ]
];