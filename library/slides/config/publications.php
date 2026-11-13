<?php

return [
    // No production HTTP administration. Local opt-in also requires an authenticated operator.
    'local_operator_ui' => env('PUBLICATIONS_LOCAL_OPERATOR_UI', false),
    'operator_ids' => array_values(array_filter(explode(',', (string) env('PUBLICATIONS_OPERATOR_IDS', '')))),
];
