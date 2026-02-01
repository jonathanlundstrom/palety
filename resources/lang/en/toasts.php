<?php

return [
    'recipient' => [
        'failed' => 'Failed to save recipient',
        'saved' => 'Recipient saved successfully!',
        'delete' => [
            'success' => 'Recipient deleted successfully!',
            'failed' => 'Failed to delete recipient',
        ]
    ],
    'content' => [
        'failed' => 'Failed to save content',
        'saved' => 'Content saved successfully!',
        'delete' => [
            'success' => 'Content deleted successfully!',
            'failed' => 'Failed to delete content',
        ]
    ],
    'parcel' => [
        'failed' => 'Failed to save parcel',
        'saved' => 'Parcel saved successfully!',
        'scanned' => 'Successfully scanned parcel',
        'loaded' => 'Scanned parcel is already reserved for another pallet or transport!',
        'not_found' => 'Scanned parcel could not be found in the database',
        'delete' => [
            'success' => 'Parcel deleted successfully!',
            'failed' => 'Failed to delete parcel',
        ]
    ],
    'pallet' => [
        'failed' => 'Failed to save pallet',
        'saved' => 'Pallet saved successfully!',
        'scanned' => 'Successfully scanned pallet',
        'loaded' => 'Scanned pallet is already reserved for another transport!',
        'not_found' => 'Scanned pallet could not be found in the database',
        'delete' => [
            'success' => 'Pallet deleted successfully!',
            'failed' => 'Failed to delete pallet',
        ]
    ],
    'transport' => [
        'failed' => 'Failed to save transport',
        'saved' => 'Transport saved successfully!',
        'delete' => [
            'success' => 'Transport deleted successfully!',
            'failed' => 'Failed to delete transport',
        ]
    ],
];
