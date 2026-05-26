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
        'duplicate' => 'The provided content already exists!',
        'delete' => [
            'success' => 'Content deleted successfully!',
            'failed' => 'Failed to delete content',
        ]
    ],
    'parcel' => [
        'failed' => 'Failed to save parcel',
        'saved' => 'Parcel saved successfully!',
        'added' => 'Successfully added parcel to list',
        'already_added' => 'This parcel has already been added!',
        'loaded' => 'The parcel is already loaded on a pallet or transport!',
        'no_recipient' => 'The parcel needs to have a recipient before it can be added to the transport!',
        'not_found' => 'Parcel could not be found in the database!',
        'delete' => [
            'success' => 'Parcel deleted successfully!',
            'failed' => 'Failed to delete parcel',
        ]
    ],
    'pallet' => [
        'failed' => 'Failed to save pallet',
        'saved' => 'Pallet saved successfully!',
        'added' => 'Successfully added pallet to list',
        'already_added' => 'The pallet has already been added!',
        'loaded' => 'The pallet is already loaded on a transport!',
        'no_recipient' => 'The parcel needs to have a recipient before it can be added to the transport!',
        'not_found' => 'Pallet could not be found in the database',
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
