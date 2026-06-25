<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Media Disk
    |--------------------------------------------------------------------------
    |
    | Keep uploaded media on the same S3-compatible disk used by Laravel in
    | Docker. In local development this disk points to MinIO.
    |
    */

    'disk_name' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public')),

];
