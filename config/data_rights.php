<?php

return [
    // Must be set to an approved operational SLA before accepting requests.
    // This value is not a statement of a jurisdiction's legal deadline.
    'due_days' => null,
    'artifact_hours' => 24,
    'disk' => 'data-exports',
    'lease_minutes' => 10,
    'sections' => ['account', 'profile', 'preferences', 'contexts', 'consents', 'data_requests'],
];
