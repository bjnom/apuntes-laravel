<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | The Client ID can be found in the OAuth Credentials under Service Account
    |
    */
    'client_id' => 'agendas@organizacion.cl',   // reemplazar por cuenta a configurar

    /*
    |--------------------------------------------------------------------------
    | Service account name
    |--------------------------------------------------------------------------
    |
    | The Service account name is the Email Address that can be found in the
    | OAuth Credentials under Service Account
    |
    */
    'service_account_name' => 'agendas@agendas-999999.iam.gserviceaccount.com', // reemplazar por cuenta a configurar

    /*
    |--------------------------------------------------------------------------
    | Key file location
    |--------------------------------------------------------------------------
    |
    | This is the location of the .p12 file from the Laravel root directory
    |
    */
    'key_file_location' => storage_path('app/google-calendar/sistema-999999-2222222.json'), // reemplazar por archivo credencial a configurar
];