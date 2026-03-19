<?php

return [
    'create' => [
        'success' => 'Created successfully',
        'failure' => 'Creation failed',
    ],

    'update' => [
        'success' => 'Updated successfully',
        'failure' => 'Update failed',
    ],

    'delete' => [
        'success' => 'Deleted successfully',
        'failure' => 'Deletion failed',
    ],

    'password' => [
        'update' => [
            'success' => 'Password updated successfully',
            'failure' => 'Password update failed',
        ],
    ],

    'profile' => [
        'update' => [
            'success' => 'Your profile has been updated on this page.',
            'error' => 'Unable to update at the moment. Please try again.',
            'confirm_email_sent' => 'We have sent a confirmation email to your new address. The new email will only be applied after you confirm it successfully.',
            'confirm_email_invalid' => 'The email confirmation link is invalid or has expired.',
            'confirm_email_conflict' => 'This new email address is already being used by another account.',
            'confirm_email_success' => 'Your new email has been confirmed and your profile information has been updated.',
        ],
    ],

    'verify_coupons' => [
        'coupon_apply_success' => 'Discount code applied successfully',
        'coupon_remove_success' => 'Discount code removed successfully',
        'coupon_remove_failed' => 'Failed to remove discount code',
        'coupon_required' => 'Please enter a discount code',
        'coupon_exp' => 'Invalid or expired coupon code',
    ],
];
