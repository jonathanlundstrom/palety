<?php

return [
    'merge' => [
        'title' => 'Slå ihop innehåll',
        'subtitle' => 'Välj det innehåll som ska ta emot alla användningar. Källan raderas permanent.',
        'target_label' => 'Slå ihop med',
        'confirm' => 'Slå ihop & ta bort källa',
        'source_heading' => ':label (används :count :unit)',
        'note_placeholder' => 'Valfri anteckning för berörda paket och pallar.',
    ],
    'add_to_transport' => [
        'title' => 'Lägg till i transport',
        'subtitle' => 'Välj vilken transport som ska lastas med denna enhet. Endast transporter som fortfarande lastas kan väljas.',
        'target_label' => 'Transport',
        'confirm' => 'Lägg till i transport',
        'source_heading' => ':label #:id – :recipient',
        'empty' => 'Det finns inga transporter som lastas just nu. Skapa en ny, eller sätt en befintlig transport tillbaka till pågående.',
    ],
    'delivered' => [
        'title' => 'Markera som levererad',
        'subtitle' => 'Välj det datum då transporten levererades. Statusen uppdateras till levererad när du bekräftar.',
        'date_label' => 'Leveransdatum',
        'confirm' => 'Markera som levererad',
        'source_heading' => 'Transport #:id (skickad :sent)',
    ],
    'delete' => [
        'title' => 'Ta bort resurs',
        'subtitle' => 'Du är på väg att ta bort denna resurs. Denna åtgärd kan inte ångras. För att fortsätta, bekräfta genom att skriva ordet "DELETE" i inmatningsfältet nedan.',
    ],
];
