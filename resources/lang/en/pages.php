<?php

return [
    'dashboard' => [
        'title' => 'Dashboard',
        'headline' => 'Global statistics',
    ],
    'parcels' => [
        'title' => 'Parcels',
        'headline' => 'All parcels',
        'subtitle' => 'Add, edit and delete the list of parcels.',
        'form' => [
            'title' => 'Parcel details',
            'subtitle' => 'Fill in the information below.',
        ],
    ],
    'pallets' => [
        'title' => 'Pallets',
        'headline' => 'All pallets',
        'subtitle' => 'Add, edit and delete the list of pallets.',
        'form' => [
            'title' => 'Pallet details',
            'subtitle' => 'Fill in the information below.',
        ],
    ],
    'transports' => [
        'title' => 'Transports',
        'headline' => 'All transports',
        'subtitle' => 'Add, edit and delete the list of transports.',
        'form' => [
            'title' => 'Transport details',
            'subtitle' => 'Fill in the information below.',
        ],
    ],
    'content' => [
        'title' => 'Content',
        'headline' => 'All content',
        'subtitle' => 'Add, edit and delete the list of parcel content.',
        'form' => [
            'title' => 'Content details',
            'subtitle' => 'Fill in the information below.',
        ],
    ],
    'recipients' => [
        'title' => 'Recipients',
        'headline' => 'All recipients',
        'subtitle' => 'Add, edit and delete the list of recipients.',
        'form' => [
            'title' => 'Recipient details',
            'subtitle' => 'Fill in the information below.',
            'extras' => [
                'EDRPOU' => 'EDRPOU',
            ],
        ],
    ],
    'users' => [
        'title' => 'Users',
        'headline' => 'All users',
        'subtitle' => 'Add, edit and delete the list of users.',
        'form' => [
            'title' => 'User details',
            'subtitle' => 'Fill in the information below.',
        ],
    ],
    'settings' => [
        'title' => 'Settings',
        'headline' => 'Settings',
        'subtitle' => 'Manage your profile and account settings.',
        'subpages' => [
            'profile' => [
                'title' => 'Profile',
                'headline' => 'Profile',
                'subtitle' => 'Manage your profile information.',
                'extras' => [
                    'email_unverified' => 'Your email address is unverified.',
                    'resend_verification' => 'Click here to re-send the verification email.',
                    'verification_sent' => 'A new verification link has been sent to your email address.',
                ],
            ],
            'delete' => [
                'title' => 'Delete account',
                'subtitle' => 'Delete your account and all of its resources',
                'modal' => [
                    'title' => 'Are you sure you want to delete your account?',
                    'subtitle' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
                ]
            ],
            'password' => [
                'title' => 'Password',
                'headline' => 'Update password',
                'subtitle' => 'Ensure your account is using a long, random password to stay secure.',
            ],
            'appearance' => [
                'title' => 'Appearance',
                'headline' => 'Appearance',
                'subtitle' => 'Update the appearance settings for your account.',
                'extras' => [
                    'light' => 'Light',
                    'dark' => 'Dark',
                    'system' => 'System',
                ],
            ],
        ],
    ],
];
