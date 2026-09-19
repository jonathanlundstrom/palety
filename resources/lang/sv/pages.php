<?php

return [
    'dashboard' => [
        'title' => 'Dashboard',
        'headline' => 'Global statistik',
    ],
    'parcels' => [
        'title' => 'Paket',
        'headline' => 'Alla paket',
        'subtitle' => 'Lägg till, redigera och ta bort paket.',
        'form' => [
            'creating' => [
                'title' => 'Skapa nytt paket',
                'subtitle' => 'Fyll i informationen nedan.',
            ],
            'editing' => [
                'title' => 'Redigera paket',
                'subtitle' => 'Uppdatera nuvarande information.',
            ],
            'duplicating' => [
                'title' => 'Duplicera paket',
                'subtitle' => 'Kontrollera informationen nedan.',
            ],
            'extras' => [
                'recipient_warning' => 'Välj bara en mottagare om paketet ska skickas separat och inte på en pall!',
            ],
        ],
    ],
    'pallets' => [
        'title' => 'Pallar',
        'headline' => 'Alla pallar',
        'subtitle' => 'Lägg till, redigera och ta bort pallar.',
        'form' => [
            'creating' => [
                'title' => 'Skapa ny pall',
                'subtitle' => 'Fyll i informationen nedan.',
            ],
            'editing' => [
                'title' => 'Redigera pall',
                'subtitle' => 'Uppdatera nuvarande information.',
            ],
            'duplicating' => [
                'title' => 'Duplicera pall',
                'subtitle' => 'Kontrollera informationen nedan.',
            ],
        ],
    ],
    'transports' => [
        'title' => 'Transporter',
        'headline' => 'Alla transporter',
        'subtitle' => 'Lägg till, redigera och ta bort transporter.',
        'form' => [
            'creating' => [
                'title' => 'Skapa ny transport',
                'subtitle' => 'Fyll i informationen nedan.',
            ],
            'editing' => [
                'title' => 'Redigera transport',
                'subtitle' => 'Uppdatera nuvarande information.',
            ],
        ],
    ],
    'content' => [
        'title' => 'Innehåll',
        'headline' => 'Allt innehåll',
        'subtitle' => 'Lägg till, redigera och ta bort paketinnehåll.',
        'form' => [
            'creating' => [
                'title' => 'Skapa nytt innehåll',
                'subtitle' => 'Fyll i informationen nedan.',
            ],
            'editing' => [
                'title' => 'Redigera innehåll',
                'subtitle' => 'Uppdatera nuvarande information.',
            ],
        ],
    ],
    'recipients' => [
        'title' => 'Mottagare',
        'headline' => 'Alla mottagare',
        'subtitle' => 'Lägg till, redigera och ta bort mottagare.',
        'form' => [
            'creating' => [
                'title' => 'Skapa ny mottagare',
                'subtitle' => 'Fyll i informationen nedan.',
            ],
            'editing' => [
                'title' => 'Redigera mottagare',
                'subtitle' => 'Uppdatera nuvarande information.',
            ],
            'extras' => [
                'IPN' => 'IPN',
                'EDRPOU' => 'EDRPOU',
            ],
        ],
    ],
    'users' => [
        'title' => 'Användare',
        'headline' => 'Alla användare',
        'subtitle' => 'Lägg till, redigera och ta bort användare.',
        'form' => [
            'creating' => [
                'title' => 'Skapa ny användare',
                'subtitle' => 'Fyll i informationen nedan.',
            ],
            'editing' => [
                'title' => 'Redigera användare',
                'subtitle' => 'Uppdatera nuvarande information.',
            ],
            'extras' => [
                'password_warning' => 'Ange bara ett lösenord i fältet om du vill ändra det nuvarande!',
            ],
        ],
    ],
    'settings' => [
        'title' => 'Inställningar',
        'headline' => 'Inställningar',
        'subtitle' => 'Hantera din profil och kontoinställningar.',
        'subpages' => [
            'profile' => [
                'title' => 'Profil',
                'headline' => 'Profil',
                'subtitle' => 'Hantera din profilinformation.',
                'extras' => [
                    'email_unverified' => 'Din e-postadress är overifierad.',
                    'resend_verification' => 'Klicka här för att skicka om verifieringsmail.',
                    'verification_sent' => 'En ny verifieringslänk har skickats till din e-postadress.',
                ],
            ],
            'delete' => [
                'title' => 'Ta bort konto',
                'subtitle' => 'Ta bort ditt konto och alla dess resurser',
                'modal' => [
                    'title' => 'Är du säker på att du vill ta bort ditt konto?',
                    'subtitle' => 'När ditt konto väl har tagits bort, raderas alla dess resurser och data permanent. Ange ditt lösenord för att bekräfta att du vill ta bort ditt konto permanent.',
                ],
            ],
            'password' => [
                'title' => 'Lösenord',
                'headline' => 'Uppdatera lösenord',
                'subtitle' => 'Se till att ditt konto använder ett långt, slumpmässigt lösenord för att förbli säkert.',
            ],
            'appearance' => [
                'title' => 'Utseende',
                'headline' => 'Utseende',
                'subtitle' => 'Uppdatera utseendeinställningarna för ditt konto.',
                'extras' => [
                    'light' => 'Ljust',
                    'dark' => 'Mörkt',
                    'system' => 'System',
                ],
            ],
        ],
    ],
];
