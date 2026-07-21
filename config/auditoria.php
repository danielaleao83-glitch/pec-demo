<?php

declare(strict_types=1);

return [

    'hash_algorithm' => 'sha256',

    'audit_key' => env('AUDIT_KEY'),

    'enable_event_chain' => true,

    'enable_snapshot_validation' => true,

];