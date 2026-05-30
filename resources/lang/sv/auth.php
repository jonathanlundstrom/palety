<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'Dessa uppgifter stämmer inte med våra register.',
    'password' => 'Det angivna lösenordet är felaktigt.',
    'throttle' => 'För många inloggningsförsök. Försök igen om :seconds sekunder.',
    'login' => [
        'title' => 'Logga in på ditt konto',
        'description' => 'Ange din e-postadress och ditt lösenord nedan för att logga in.',
        'forgot' => 'Glömt ditt lösenord?',
        'remember_me' => 'Kom ihåg mig',
    ],
    'verify_email' => [
        'description' => 'Verifiera din e-postadress genom att klicka på länken vi just skickade till dig.',
        'link_sent' => 'En ny verifieringslänk har skickats till den e-postadress du angav vid registreringen.',
        'resend' => 'Skicka om verifieringsmail',
    ],
    'reset_password' => [
        'title' => 'Återställ lösenord',
        'description' => 'Ange ditt nya lösenord nedan.',
        'submit' => 'Återställ lösenord',
    ],
    'confirm_password' => [
        'title' => 'Bekräfta lösenord',
        'description' => 'Det här är ett säkert område i applikationen. Bekräfta ditt lösenord innan du fortsätter.',
    ],
    'forgot_password' => [
        'title' => 'Glömt lösenord',
        'description' => 'Ange din e-postadress för att ta emot en länk för återställning av lösenord.',
        'confirmation' => 'En återställningslänk har skickas om kontot finns.',
        'return_to' => 'Eller, återgå till',
        'submit' => 'Skicka återställningslänk',
    ],
];
