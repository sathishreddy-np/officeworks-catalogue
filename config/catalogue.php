<?php

// This file returns a settings array. Other code reads it with config('catalogue.something').

return [

    /*
    | The browser never calls this URL. PHP calls it.
    | Change the address here, or replace FakeStoreCatalogue, when the product source changes.
    */

    // env() reads CATALOGUE_BASE_URL from .env. If that line is missing, Fake Store is used.
    'base_url' => env('CATALOGUE_BASE_URL', 'https://fakestoreapi.com'),

    // How many seconds a successful product download is reused. 600 is 10 minutes.
    'cache_seconds' => (int) env('CATALOGUE_CACHE_SECONDS', 600),

    /*
    | Where enquiry email is addressed.
    | Locally the mail driver is "log", so the message is written to storage/logs/laravel.log.
    */

    // The sales inbox. No real mailbox is required for the review.
    'sales_inbox' => env('CATALOGUE_SALES_INBOX', 'sales@officeworks.example'),

    /*
    | These five Fake Store ids are the home-page products, in this order.
    | 11 SSD, 12 gaming drive, 3 cotton jacket, 18 boat-neck top, 5 bracelet.
    | A category page treats any of these ids in that category as "top rated".
    */

    // A normal PHP list of integers. The order here is the order on the home page.
    'featured_ids' => [11, 12, 3, 18, 5],

];
