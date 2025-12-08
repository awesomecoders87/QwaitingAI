<?php 
return [
    'error' => 'Erreur',
    'description' => 'Description',
    'resolution' => 'Solution',

    'ERR001' => [
        'message' => "Veuillez d'abord sélectionner un guichet !",
        'description' => "L'utilisateur a tenté une action sans sélectionner de guichet.",
        'resolution' => 'Inviter l’utilisateur à choisir un guichet avant de continuer.',
    ],
    'ERR002' => [
        'message' => 'Aucun appel',
        'description' => "Aucun appel en cours pour effectuer l'opération.",
        'resolution' => 'Démarrer ou attendre un nouvel appel.',
    ],
    'ERR003' => [
        'message' => 'Veuillez d’abord clôturer l’appel en cours !',
        'description' => "L'utilisateur doit terminer l'appel actif avant d'en commencer un nouveau.",
        'resolution' => "Clôturer l'appel en cours avant de continuer.",
    ],
    'ERR004' => [
        'message' => 'Cet appel est temporairement en attente',
        'description' => "L'appel sélectionné est temporairement mis en attente.",
        'resolution' => 'Reprendre l’appel avant de continuer.',
    ],
    'ERR005' => [
        'message' => 'Cet appel est temporairement en attente (début)',
        'description' => "L'appel mis en file d'attente est passé en attente temporaire.",
        'resolution' => 'Attendre la fin de l’attente ou reprendre manuellement.',
    ],
    'ERR006' => [
        'message' => 'Le numéro de file existe déjà',
        'description' => 'Le numéro de file existe déjà dans le système.',
        'resolution' => 'Générer un nouveau numéro de file unique.',
    ],
    'ERR007' => [
        'message' => "Aucun appel en cours n'est attribué à vous !",
        'description' => "Aucun appel actif trouvé attribué à l'utilisateur.",
        'resolution' => 'Vérifiez qu’un appel est actuellement en cours de traitement.',
    ],
    'ERR008' => [
        'message' => "Le numéro de file existe déjà",
        'description' => 'Le numéro de file existe déjà dans la base de données',
        'resolution' => 'Veuillez vérifier votre numéro de file',
    ],
    'ERR009' => [
        'message' => "La file actuelle a été réinitialisée sans être clôturée !",
        'description' => 'La file actuelle a été réinitialisée',
        'resolution' => 'Votre file a été réinitialisée',
    ],

    'BOOK001' => [
        'message' => "Impossible de générer le ticket en raison de règles invalides.",
        'description' => "La configuration du système a bloqué la création du ticket.",
        'resolution' => 'Contacter l’administrateur pour vérifier les règles de réservation.',
    ],
    'BOOK002' => [
        'message' => 'Échec du paiement : une erreur est survenue',
        'description' => 'Un problème inconnu est survenu lors du paiement.',
        'resolution' => 'Réessayer le paiement ou contacter le support.',
    ],
    'BOOK003' => [
        'message' => 'Les clés de service de paiement sont manquantes',
        'description' => 'Les identifiants API pour le paiement ne sont pas définis.',
        'resolution' => 'Configurer la clé API et le secret dans les paramètres.',
    ],
    'BOOK004' => [
        'message' => 'Paramètre de paiement non configuré',
        'description' => 'Les paramètres de paiement sont incomplets.',
        'resolution' => 'Compléter la configuration des paiements dans le panneau d’administration.',
    ],

    'SUCCESS001' => [
        'message' => 'Appel réussi',
    ],
    'SUCCESS002' => [
        'message' => 'Suspension traitée avec succès avec notifications envoyées',
    ],
    'SUCCESS003' => [
        'message' => 'Appel démarré avec succès'
    ],
    'SUCCESS004' => [
        'message' => 'Appel clôturé avec succès'
    ],
    'SUCCESS005' => [
        'message' => 'Transfert d’appel réussi'
    ],
    'SUCCESS006' => [
        'message' => 'Rappel réussi'
    ],
    'SUCCESS007' => [
        'message' => 'Retour de l’appel réussi'
    ],
    'SUCCESS008' => [
        'message' => 'Demande envoyée à l’administrateur'
    ],
    'SUCCESS009' => [
        'message' => 'Mise en attente réussie'
    ],
    'SUCCESS0010' => [
        'message' => 'Annulation réussie'
    ],
    'SUCCESS0011' => [
        'message' => 'SMS envoyé avec succès !'
    ],
    'SUCCESS0012' => [
        'message' => 'File générée avec succès !'
    ],
    'SUCCESS0013' => [
        'message' => 'Note d’estimation mise à jour avec succès !'
    ],
    'SUCCESS0014' => [
        'message' => 'Retour de l’appel réussi'
    ],
    'SUCCESS0015' => [
        'message' => 'Visiteur modifié avec succès'
    ],
    'SUCCESS0016' => [
        'message' => 'Appel manqué enregistré avec succès'
    ],
    'SUCCESS0017' => [
    'message' => 'Données enregistrées avec succès'
],


    'VAL001' => [
        'message' => 'Veuillez entrer le numéro de file et la catégorie',
    ],
    'VAL002' => [
        'message' => 'Veuillez entrer le type de pause et un commentaire',
    ],

    'Click on the continue button to unlock this screen! Break time is for' => 'Cliquez sur le bouton Continuer pour déverrouiller cet écran ! Le temps de pause est de',
    'minutes.' => 'minutes.',
    'CONTINUE' => 'CONTINUER',
    'Call started Successfully' => 'Appel démarré avec succès',
    'success' => 'succès',
    'Suspension processed successfully with notifications sent' => 'Suspension traitée avec succès avec notifications envoyées',
    'Are you sure' => 'Êtes-vous sûr',
    'warning' => 'avertissement',
    'You want to revert this' => 'Vous voulez annuler cela',
    'YES, REVERT IT' => 'OUI, ANNULER',
    'No, CANCEL' => 'Non, ANNULER',
    'Please rate our service' => 'Veuillez évaluer notre service',
    'Excellent' => 'Excellent',
    'Good' => 'Bon',
    'Neutral' => 'Neutre',
    'Poor' => 'Mauvais',
    'Please Wait' => 'Veuillez patienter',
    'Revert Queue' => 'Annuler la file',
    'Cancelled' => 'Annulé',
    'Your data is safe' => 'Vos données sont en sécurité',
    'error' => 'erreur',
    "You won't be able to revert this" => "Vous ne pourrez pas annuler cela",
    'OK' => 'OK',
    'Cancel' => 'Annuler',
    'Please enter queue number and category' => 'Veuillez entrer le numéro de file et la catégorie',
    'Break' => 'Pause',
    'Choose Any Reason' => 'Choisissez une raison',
    'Comment' => 'Commentaire',
    'Please enter break type and comment' => 'Veuillez entrer le type de pause et un commentaire',
    'Enter Queue Number' => 'Entrez le numéro de file',
    'Select Category' => 'Sélectionner une catégorie',
    'Type of Break' => 'Type de pause',
    'Unlock Screen' => 'Déverrouiller l’écran',
    'Updating' => 'Mise à jour',
    'Success!' => 'Succès !',
    'Yes, delete it' => 'Oui, supprimez-le',
    'Data Deleted Successfully' => 'Données supprimées avec succès',
    'No record selected' => 'Aucun enregistrement sélectionné',

];
