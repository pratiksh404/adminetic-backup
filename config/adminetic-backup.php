<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Available Drives
    |--------------------------------------------------------------------------
    |
    | local
    | dropbox
    | google
    | 
    */
    'drives' => ['local'],
    /*
    |--------------------------------------------------------------------------
    | Google Drive
    |--------------------------------------------------------------------------
    */
    'GOOGLE_DRIVE_CLIENT_ID' => env('GOOGLE_DRIVE_CLIENT_ID', null),
    'GOOGLE_DRIVE_CLIENT_SECRET' => env('GOOGLE_DRIVE_CLIENT_SECRET', null),
    'GOOGLE_DRIVE_REFRESH_TOKEN' => env('GOOGLE_DRIVE_REFRESH_TOKEN', null),
    'GOOGLE_DRIVE_FOLDER_ID' => env('GOOGLE_DRIVE_FOLDER_ID', null),


    'DROPBOX_ACCESS_TOKEN' => env('DROPBOX_ACCESS_TOKEN', null)
];
