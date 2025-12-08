<?php
return [
    'error' => 'Chyba',
    'description' => 'Popis',
    'resolution' => 'Riešenie',

    'ERR001' => [
        'message' => 'Najprv prosím vyberte prepážku!',
        'description' => 'Používateľ sa pokúsil o akciu bez výberu prepážky.',
        'resolution' => 'Vyžiadajte od používateľa, aby pred pokračovaním vybral prepážku.',
    ],
    'ERR002' => [
        'message' => 'Žiadny hovor',
        'description' => 'Neexistuje aktívny hovor na vykonanie operácie.',
        'resolution' => 'Spustite alebo počkajte na nový hovor.',
    ],
    'ERR003' => [
        'message' => 'Najprv ukončite aktuálny hovor!',
        'description' => 'Používateľ musí ukončiť aktívny hovor pred spustením nového.',
        'resolution' => 'Ukončite aktuálny hovor pred pokračovaním.',
    ],
    'ERR004' => [
        'message' => 'Tento hovor je dočasne podržaný',
        'description' => 'Vybraný hovor je dočasne podržaný.',
        'resolution' => 'Pred pokračovaním obnovte hovor.',
    ],
    'ERR005' => [
        'message' => 'Tento hovor je dočasne podržaný (začiatok)',
        'description' => 'Hovor v rade bol dočasne podržaný.',
        'resolution' => 'Počkajte na ukončenie podržania alebo obnovte ručne.',
    ],
    'ERR006' => [
        'message' => 'Číslo frontu už existuje',
        'description' => 'Toto číslo frontu už existuje v systéme.',
        'resolution' => 'Vytvorte nové jedinečné číslo frontu.',
    ],
    'ERR007' => [
        'message' => 'Systém nemá hovor, ktorý obsluhujete!',
        'description' => 'Nebolo nájdené žiadne aktívne pridelené volanie.',
        'resolution' => 'Uistite sa, že momentálne obsluhujete hovor.',
    ],
    'ERR008' => [
        'message' => 'Číslo frontu už existuje',
        'description' => 'Číslo frontu už existuje v databáze.',
        'resolution' => 'Skontrolujte číslo frontu.',
    ],
    'ERR009' => [
        'message' => 'Aktuálny front bol resetovaný bez uzavretia!',
        'description' => 'Aktuálny front bol resetovaný.',
        'resolution' => 'Váš front bol resetovaný.',
    ],

    'BOOK001' => [
        'message' => 'Nie je možné vygenerovať lístok z dôvodu neplatných pravidiel.',
        'description' => 'Konfigurácia systému zablokovala vytvorenie lístka.',
        'resolution' => 'Kontaktujte administrátora kvôli kontrole pravidiel rezervácie.',
    ],
    'BOOK002' => [
        'message' => 'Platba zlyhala: Niečo sa pokazilo',
        'description' => 'Počas platby nastala neznáma chyba.',
        'resolution' => 'Skúste platbu zopakovať alebo kontaktujte podporu.',
    ],
    'BOOK003' => [
        'message' => 'Chýbajú kľúče platobnej služby',
        'description' => 'API poverenia pre platbu nie sú nastavené.',
        'resolution' => 'Nastavte API kľúč a tajomstvo v nastaveniach.',
    ],
    'BOOK004' => [
        'message' => 'Nastavenie platby nie je nakonfigurované',
        'description' => 'Nastavenie platby je neúplné.',
        'resolution' => 'Dokončite nastavenie platby v administrácii.',
    ],

    'SUCCESS001' => [
        'message' => 'Hovor úspešne prijatý',
    ],
    'SUCCESS002' => [
        'message' => 'Pozastavenie úspešne spracované a odoslané oznámenia',
    ],
    'SUCCESS003' => [
        'message' => 'Hovor úspešne spustený',
    ],
    'SUCCESS004' => [
        'message' => 'Hovor úspešne ukončený',
    ],
    'SUCCESS005' => [
        'message' => 'Hovor úspešne presunutý',
    ],
    'SUCCESS006' => [
        'message' => 'Opätovné volanie úspešné',
    ],
    'SUCCESS007' => [
        'message' => 'Hovor úspešne vrátený späť',
    ],
    'SUCCESS008' => [
        'message' => 'Žiadosť bola odoslaná administrátorovi',
    ],
    'SUCCESS009' => [
        'message' => 'Podržanie úspešné',
    ],
    'SUCCESS0010' => [
        'message' => 'Úspešne zrušené',
    ],
    'SUCCESS0011' => [
        'message' => 'SMS bola úspešne odoslaná!',
    ],
    'SUCCESS0012' => [
        'message' => 'Front bol úspešne vytvorený!',
    ],
    'SUCCESS0013' => [
        'message' => 'Poznámka bola úspešne aktualizovaná!',
    ],
    'SUCCESS0014' => [
        'message' => 'Hovor bol úspešne vrátený späť',
    ],
    'SUCCESS0015' => [
        'message' => 'Návštevník úspešne upravený',
    ],
    'SUCCESS0016' => [
        'message' => 'Hovor označený ako zmeškaný',
    ],

    'VAL001' => [
        'message' => 'Prosím, zadajte číslo frontu a kategóriu',
    ],
    'VAL002' => [
        'message' => 'Prosím, zadajte typ prestávky a komentár',
    ],

    'Click on the continue button to unlock this screen! Break time is for' => 'Kliknite na tlačidlo pokračovať pre odomknutie obrazovky! Čas prestávky je',
    'minutes.' => 'minút.',
    'CONTINUE' => 'POKRAČOVAŤ',
    'Call started Successfully' => 'Hovor úspešne spustený',
    'success' => 'úspech',
    'Suspension processed successfully with notifications sent' => 'Pozastavenie úspešne spracované a oznámenia odoslané',
    'Are you sure' => 'Ste si istý',
    'warning' => 'upozornenie',
    'You want to revert this' => 'Chcete toto vrátiť späť',
    'YES, REVERT IT' => 'ÁNO, VRÁTIŤ',
    'No, CANCEL' => 'Nie, ZRUŠIŤ',
    'Please rate our service' => 'Prosím, ohodnoťte našu službu',
    'Excellent' => 'Vynikajúce',
    'Good' => 'Dobré',
    'Neutral' => 'Neutrálne',
    'Poor' => 'Zlé',
    'Please Wait' => 'Prosím, čakajte',
    'Revert Queue' => 'Vrátiť front',
    'Cancelled' => 'Zrušené',
    'Your data is safe' => 'Vaše údaje sú v bezpečí',
    'error' => 'chyba',
    "You won't be able to revert this" => 'Túto akciu nebude možné vrátiť späť',
    'OK' => 'OK',
    'Cancel' => 'Zrušiť',
    'Please enter queue number and category' => 'Prosím, zadajte číslo frontu a kategóriu',
    'Break' => 'Prestávka',
    'Choose Any Reason' => 'Vyberte dôvod',
    'Comment' => 'Komentár',
    'Please enter break type and comment' => 'Zadajte typ prestávky a komentár',
    'Enter Queue Number' => 'Zadajte číslo frontu',
    'Select Category' => 'Vyberte kategóriu',
    'Type of Break' => 'Typ prestávky',
    'Unlock Screen' => 'Odomknúť obrazovku',
    'Updating' => 'Aktualizujem',
];
