<?php

return [
    'user' => [
        'failed' => 'Failed to update user',
        'saved' => 'User saved successfully!',
        'password' => [
            'failed' => 'Failed to update password',
            'saved' => 'Password updated successfully!',
        ]
    ],
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
        'loaded' => 'The scanned parcel is already loaded on pallet or transport!',
        'no_recipient' => 'The parcel needs to have a recipient before it can be added to the transport!',
        'not_found' => 'The scanned parcel could not be found in the database',
        'delete' => [
            'success' => 'Parcel deleted successfully!',
            'failed' => 'Failed to delete parcel',
        ]
    ],
    'pallet' => [
        'failed' => 'Failed to save pallet',
        'saved' => 'Pallet saved successfully!',
        'scanned' => 'Successfully scanned pallet',
        'loaded' => 'The scanned pallet is already loaded on transport!',
        'no_recipient' => 'The parcel needs to have a recipient before it can be added to the transport!',
        'not_found' => 'The scanned pallet could not be found in the database',
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
