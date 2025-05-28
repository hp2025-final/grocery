<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | This file contains all the company-specific configurations that are used
    | throughout the application, including PDFs and other documents.
    |
    */    'name' => env('COMPANY_NAME', 'Stech Enterprises'),
    'address' => env('COMPANY_ADDRESS', 'Office 01, Absher Lodge, Church Street, Saddar, Karachi, Pakistan'),
    'email' => env('COMPANY_EMAIL', 'stech.ent.saddar@gmail.com'),    'phone' => env('COMPANY_PHONE', '+92 324 170 5732'),
    'powered_by' => env('COMPANY_POWERED_BY', 'Developer: NFT'),
    'authentication_notice' => env('COMPANY_AUTH_NOTICE', 'This document is computer generated and does not require any manual authentication.'),
];
