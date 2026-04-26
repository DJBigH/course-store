<?php

return [
    'titles' => [
        'packages' => 'Manage Teacher Plans',
        'create'   => 'Add Teacher Plan',
        'edit'     => 'Update Teacher Plan',
        'grant'    => 'Grant Exclusive Plan to Teacher',
    ],
    'messages' => [
        'create_success'          => 'Teacher plan created successfully.',
        'update_success'          => 'Teacher plan updated successfully.',
        'delete_success'          => 'Teacher plan deleted successfully.',
        'reorder_success'         => 'Plan order updated successfully.',
        'invalid_reorder_data'    => 'Invalid reorder data.',
        'incomplete_reorder_list' => 'Incomplete plan list for reordering.',
        'grant_success'           => 'Successfully granted plan :package to teacher :teacher.',
        'grant_pending'           => 'Grant notification sent to teacher :teacher for plan :package. They will receive it via notification.',
    ],
    'grant' => [
        'no_package' => 'No active plan',
    ],
];

