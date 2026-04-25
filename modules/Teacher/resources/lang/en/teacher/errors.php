<?php

return [
    '403' => [
        'title' => 'Access Denied',
        'message' => 'Oops! You do not have permission to access this page.',
        'description' => 'Please contact the administrator if you believe this is a mistake.',
        'button' => 'Back to Dashboard',
    ],
    '404' => [
        'title' => 'Page Not Found',
        'message' => 'Oops! The page you are looking for does not exist.',
        'description' => 'The page might have been removed or the link has changed.',
        'button' => 'Back to Dashboard',
    ],
    '419' => [
        'title' => 'Page Expired',
        'message' => 'The page has expired due to inactivity.',
        'description' => 'Please refresh the page and try again.',
        'button' => 'Refresh Page',
    ],
    '500' => [
        'title' => 'Server Error',
        'message' => 'Something went wrong on our end.',
        'description' => 'We are working to fix it. Please try again later.',
        'button' => 'Back to Dashboard',
    ],
    '503' => [
        'title' => 'System Maintenance',
        'message' => 'We are down for maintenance.',
        'description' => 'We will be back shortly. Thank you for your patience.',
        'button' => 'Back to Dashboard',
    ],
];
