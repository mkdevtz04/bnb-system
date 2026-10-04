<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase project
    |--------------------------------------------------------------------------
    |
    | Guests sign in with Google through Firebase Authentication. The browser
    | completes the Google popup, then posts the resulting Firebase ID token to
    | this application, which verifies it before trusting anything inside it.
    |
    | None of these three values is a secret — they identify the project and are
    | visible in any Firebase web app's source. Verification needs no service
    | account key, because Firebase ID tokens are signed with Google's own keys
    | and checked against Google's published public certificates. There is
    | therefore no private key for this integration to leak.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),

    // From Firebase console → Project settings → Your apps → Web app config.
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Google OAuth client id (optional, but preferred)
    |--------------------------------------------------------------------------
    |
    | Set this and the browser signs in through Google Identity Services, which
    | talks to accounts.google.com and identitytoolkit.googleapis.com only.
    |
    | Leave it empty and Firebase's signInWithPopup is used instead. That popup
    | loads <project>.firebaseapp.com/__/auth/handler, which lives on Firebase
    | Hosting — an address range that some networks and ISPs reset outright,
    | producing ERR_CONNECTION_RESET while every other Google service works. Where
    | that happens, setting this value is the fix.
    |
    | Google Cloud console → APIs & Services → Credentials → OAuth 2.0 Client IDs
    | → "Web client (auto created by Google Service)". Looks like
    | 1234567890-abc123.apps.googleusercontent.com, and is a public identifier.
    |
    */

    'google_client_id' => env('FIREBASE_GOOGLE_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Google's token-signing certificates
    |--------------------------------------------------------------------------
    |
    | Rotated by Google every few hours, so they are fetched and cached rather
    | than pinned. The cache TTL follows the Cache-Control header on the
    | response, with this as the floor if that header is missing.
    |
    */

    'certificates_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com',

    'certificates_cache_key' => 'firebase:securetoken:certs',

    'certificates_min_ttl' => 300,

    // Tolerance for clock drift between this server and Google, in seconds.
    'leeway' => 60,

];
