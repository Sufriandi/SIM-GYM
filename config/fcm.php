<?php

return [
    'project_id' => env('FCM_PROJECT_ID'),
    'service_account' => env('FCM_SERVICE_ACCOUNT', 'storage/app/firebase-service-account.json'),

    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    'oauth_token_url' => 'https://oauth2.googleapis.com/token',
    'fcm_send_url' => 'https://fcm.googleapis.com/v1/projects/%s/messages:send',

    // HARUS sama dengan Android channelId
    'android_channel_id' => env('FCM_ANDROID_CHANNEL_ID', 'betagym_general'),
];
