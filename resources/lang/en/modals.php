<?php

return [
    'merge' => [
        'title' => 'Merge content',
        'subtitle' => 'Select the content to merge into. All usages will be transferred and the source will be permanently deleted.',
        'target_label' => 'Merge into',
        'confirm' => 'Merge & delete source',
        'source_heading' => ':label (used :count :unit)',
        'note_placeholder' => 'Optional note for affected parcels and pallets.',
    ],
    'add_to_transport' => [
        'title' => 'Add to transport',
        'subtitle' => 'Select the transport to load this item onto. Only transports that are still being loaded can be selected.',
        'target_label' => 'Transport',
        'confirm' => 'Add to transport',
        'source_heading' => ':label #:id (:recipient)',
        'empty' => 'There are no transports being loaded right now. Create one first, or set an existing transport back to in progress.',
    ],
    'delivered' => [
        'title' => 'Mark as delivered',
        'subtitle' => 'Select the date when the transport was delivered. The status is updated to delivered once confirmed.',
        'date_label' => 'Delivery date',
        'confirm' => 'Mark as delivered',
        'source_heading' => 'Transport #:id (sent :sent)',
    ],
    'delete' => [
        'title' => 'Delete resource',
        'subtitle' => 'You are about to delete this resource. This action cannot be reversed. In order to continue, please confirm by typing the word "DELETE" in the input field below.',
    ],
];
