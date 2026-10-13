<?php

return [
    // Show "Try the demo" and allow the nightly demo reset
    'demo_enabled' => (bool) env('DEMO_ENABLED', true),
    'demo_email' => 'demo@rakawarana.app',

    // Uploaded policy documents
    'max_upload_kb' => 5120,
    'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
];
