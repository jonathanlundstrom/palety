<?php

return [
    'user' => [
        'failed' => 'Misslyckades med att uppdatera användaren',
        'saved' => 'Användaren sparades!',
        'password' => [
            'failed' => 'Misslyckades med att uppdatera lösenordet',
            'saved' => 'Lösenordet uppdaterades!',
        ],
    ],
    'recipient' => [
        'failed' => 'Misslyckades med att spara mottagaren',
        'saved' => 'Mottagaren sparades!',
        'delete' => [
            'success' => 'Mottagaren togs bort!',
            'failed' => 'Misslyckades med att ta bort mottagaren',
        ],
    ],
    'content' => [
        'failed' => 'Misslyckades med att spara innehållet',
        'saved' => 'Innehållet sparades!',
        'duplicate' => 'Det angivna innehållet finns redan!',
        'delete' => [
            'success' => 'Innehållet togs bort!',
            'failed' => 'Misslyckades med att ta bort innehållet',
        ],
    ],
    'parcel' => [
        'failed' => 'Misslyckades med att spara paketet',
        'saved' => 'Paketet sparades!|:count paket sparades!',
        'added' => 'Paketet lades till i listan',
        'already_added' => 'Det här paketet har redan lagts till!',
        'loaded' => 'Paketet är redan lastat på en pall eller transport!',
        'no_recipient' => 'Paketet måste ha en mottagare innan det kan läggas till i transporten!',
        'not_found' => 'Paketet hittades inte i databasen!',
        'delete' => [
            'success' => 'Paketet togs bort!',
            'failed' => 'Misslyckades med att ta bort paketet',
        ],
    ],
    'pallet' => [
        'failed' => 'Misslyckades med att spara pallen',
        'saved' => 'Pallen sparades!',
        'added' => 'Pallen lades till i listan',
        'already_added' => 'Pallen har redan lagts till!',
        'loaded' => 'Pallen är redan lastad på en transport!',
        'no_recipient' => 'Pallen måste ha en mottagare innan den kan läggas till i transporten!',
        'not_found' => 'Pallen hittades inte i databasen',
        'delete' => [
            'success' => 'Pallen togs bort!',
            'failed' => 'Misslyckades med att ta bort pallen',
        ],
    ],
    'transport' => [
        'failed' => 'Misslyckades med att spara transporten',
        'saved' => 'Transporten sparades!',
        'delete' => [
            'success' => 'Transporten togs bort!',
            'failed' => 'Misslyckades med att ta bort transporten',
        ],
    ],
    'label' => [
        'printing' => [
            'success' => 'Etikettutskrift startades!',
            'failed' => 'Misslyckades med att starta etikettutskrift',
        ],
    ],
];
