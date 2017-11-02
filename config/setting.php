<?php

return [
    'twitter_oauth' => [
        'customer_key'          => getenv('TWITTEROAUTH_CONSUMER_KEY'),
        'customer_secret'       => getenv('TWITTEROAUTH_CONSUMER_SECRET'),
        'access_token'          => getenv('TWITTEROAUTH_ACCESS_TOKEN'),
        'access_token_secret'   => getenv('TWITTEROAUTH_ACCESS_TOKEN_SECRET')
    ],

    'azure' => [
        'key1' => getenv('AZURE_KEY_1'),
        'key2' => getenv('AZURE_KEY_2')
    ],

    'google_map' => [
        'key' => getenv('Google_MAP_API')
    ],

    'datum_box' => [
        'key' => getenv('DATUM_BOX_API')
    ],

    'youtube' => [
        'key' => getenv('YOUTUBE_API')
    ]
];