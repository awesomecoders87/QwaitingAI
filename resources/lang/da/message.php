<?php

return [
    'error' => 'Fejl',
    'description' => 'Beskrivelse',
    'resolution' => 'Løsning',

    'ERR001' => [
        'message' => 'Vælg venligst en skranke først!',
        'description' => 'Brugeren forsøgte handling uden at vælge en skranke.',
        'resolution' => 'Bed brugeren vælge en skranke, før de fortsætter.',
    ],
    'ERR002' => [
        'message' => 'Ingen kald',
        'description' => 'Der er ikke et aktivt kald til at udføre operationen.',
        'resolution' => 'Start eller vent på et nyt kald.',
    ],
    'ERR003' => [
        'message' => 'Luk først det aktuelle kald!',
        'description' => 'Brugeren skal afslutte det aktive kald, før et nyt startes.',
        'resolution' => 'Luk det aktuelle kald, før du fortsætter.',
    ],
    'ERR004' => [
        'message' => 'Dette kald er midlertidigt på hold',
        'description' => 'Det valgte kald er midlertidigt sat på hold.',
        'resolution' => 'Genoptag kaldet, før du fortsætter.',
    ],
    'ERR005' => [
        'message' => 'Dette kald er midlertidigt på hold (start)',
        'description' => 'Køen er sat midlertidigt på hold.',
        'resolution' => 'Vent til holdet fjernes eller genoptag manuelt.',
    ],
    'ERR006' => [
        'message' => 'Kønummeret eksisterer allerede',
        'description' => 'Kønummeret findes allerede i systemet.',
        'resolution' => 'Generer et nyt unikt kønummer.',
    ],
    'ERR007' => [
        'message' => 'Systemet har ikke et kald, som du behandler!',
        'description' => 'Intet aktivt kald fundet, der er tildelt brugeren.',
        'resolution' => 'Sørg for, at et kald er aktivt.',
    ],
    'ERR008' => [
        'message' => 'Kønummeret findes allerede',
        'description' => 'Kønummeret findes allerede i databasen.',
        'resolution' => 'Kontroller kønummeret.',
    ],
    'ERR009' => [
        'message' => 'Den aktuelle kø er blevet nulstillet uden at være lukket!',
        'description' => 'Den aktuelle kø er blevet nulstillet.',
        'resolution' => 'Din kø er blevet nulstillet.',
    ],

    'BOOK001' => [
        'message' => 'Kunne ikke oprette billet pga. ugyldige regler.',
        'description' => 'Systemkonfigurationen blokerede oprettelsen.',
        'resolution' => 'Kontakt administrator for at gennemgå reglerne.',
    ],
    'BOOK002' => [
        'message' => 'Betaling mislykkedes: Noget gik galt',
        'description' => 'Der opstod en ukendt fejl under betalingen.',
        'resolution' => 'Prøv igen eller kontakt support.',
    ],
    'BOOK003' => [
        'message' => 'Manglende nøgler til betalingsservice',
        'description' => 'API-legitimationsoplysninger er ikke konfigureret.',
        'resolution' => 'Indstil API-nøgle og hemmelighed under indstillinger.',
    ],
    'BOOK004' => [
        'message' => 'Betalingsindstillinger er ikke konfigureret',
        'description' => 'Betalingsopsætningen er ufuldstændig.',
        'resolution' => 'Fuldfør betalingsopsætning i admin-panelet.',
    ],

    'SUCCESS001' => [
        'message' => 'Kald lykkedes',
    ],
    'SUCCESS002' => [
        'message' => 'Suspendering gennemført og meddelelser sendt',
    ],
    'SUCCESS003' => [
        'message' => 'Kald startet med succes',
    ],
    'SUCCESS004' => [
        'message' => 'Kald lukket med succes',
    ],
    'SUCCESS005' => [
        'message' => 'Kald overført med succes',
    ],
    'SUCCESS006' => [
        'message' => 'Genkald lykkedes',
    ],
    'SUCCESS007' => [
        'message' => 'Kald flyttet tilbage med succes',
    ],
    'SUCCESS008' => [
        'message' => 'Anmodning sendt til administrator',
    ],
    'SUCCESS009' => [
        'message' => 'Hold sat med succes',
    ],
    'SUCCESS0010' => [
        'message' => 'Annulleret med succes',
    ],
    'SUCCESS0011' => [
        'message' => 'SMS sendt med succes!',
    ],
    'SUCCESS0012' => [
        'message' => 'Kø oprettet med succes!',
    ],
    'SUCCESS0013' => [
        'message' => 'Notat opdateret med succes!',
    ],
    'SUCCESS0014' => [
        'message' => 'Kald tilbageført med succes',
    ],
    'SUCCESS0015' => [
        'message' => 'Besøgende redigeret med succes',
    ],
    'SUCCESS0016' => [
        'message' => 'Kald markeret som mistet',
    ],

    'VAL001' => [
        'message' => 'Indtast kønummer og kategori',
    ],
    'VAL002' => [
        'message' => 'Indtast type af pause og kommentar',
    ],

    'Click on the continue button to unlock this screen! Break time is for' => 'Klik på fortsæt for at låse skærmen op! Pausen varer',
    'minutes.' => 'minutter.',
    'CONTINUE' => 'FORTSÆT',
    'Call started Successfully' => 'Kald startet med succes',
    'success' => 'succes',
    'Suspension processed successfully with notifications sent' => 'Suspendering gennemført og meddelelser sendt',
    'Are you sure' => 'Er du sikker',
    'warning' => 'advarsel',
    'You want to revert this' => 'Vil du tilbageføre dette',
    'YES, REVERT IT' => 'JA, TILBAGEFØR',
    'No, CANCEL' => 'Nej, ANNULLER',
    'Please rate our service' => 'Vurder venligst vores service',
    'Excellent' => 'Fremragende',
    'Good' => 'God',
    'Neutral' => 'Neutral',
    'Poor' => 'Dårlig',
    'Please Wait' => 'Vent venligst',
    'Revert Queue' => 'Tilbagefør kø',
    'Cancelled' => 'Annulleret',
    'Your data is safe' => 'Dine data er sikre',
    'error' => 'fejl',
    "You won't be able to revert this" => 'Du kan ikke fortryde dette',
    'OK' => 'OK',
    'Cancel' => 'Annuller',
    'Please enter queue number and category' => 'Indtast kønummer og kategori',
    'Break' => 'Pause',
    'Choose Any Reason' => 'Vælg en årsag',
    'Comment' => 'Kommentar',
    'Please enter break type and comment' => 'Indtast type af pause og kommentar',
    'Enter Queue Number' => 'Indtast kønummer',
    'Select Category' => 'Vælg kategori',
    'Type of Break' => 'Type af pause',
    'Unlock Screen' => 'Lås skærmen op',
    'Updating' => 'Opdaterer',
];
