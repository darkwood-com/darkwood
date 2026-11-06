# Destinations de publication

Slidewire est la source canonique ; Speaker Deck et SlideShare sont des destinations. Les adaptateurs déclarent uniquement les capacités réellement vérifiées. Aucun endpoint privé ni cookie de navigateur n'est utilisé par Laravel.

## Speaker Deck

- Création : upload PDF dans l'interface officielle, puis métadonnées.
- Métadonnées : titre, description, catégorie et langue dans l'éditeur.
- Remplacement : **Reupload**, vérifié avec conservation de l'ID et de l'URL. Une confirmation explicite est requise avant de remplacer un document public.
- Suppression : disponible dans l'interface, irréversible ; non implémentée dans Slidewire.
- Lecture publique : oEmbed officiel, `https://speakerdeck.com/oembed.json?url=...`, permettant de vérifier le compte, le titre, le ratio et l'ID hexadécimal du player. Il ne fournit pas le hash du PDF.
- Aucun contrat d'API d'écriture officiel identifié : workflow assisté uniquement.
- Limites gratuites standard : 10 uploads/jour, 100 decks et 150 Mo/PDF. Vérifier les limites du compte avant un lot. Largeur recommandée : au moins 1920 px.

Une indication de traitement ou de dimensions peut rester momentanément ancienne dans l'éditeur. Vérifier le PDF servi publiquement et l'oEmbed avant de conclure à un échec ou de réessayer. Ne jamais créer un second deck pour résoudre une incertitude.

Sources : [FAQ officielle](https://speakerdeck.com/faq), [upload](https://help.speakerdeck.com/help/how-do-i-upload-a-deck). Compte cible : `matyo91`. Les ID et URLs déjà confirmés figurent dans [l'état des publications](publications-status.md).

## SlideShare

- API publique retirée définitivement, sans remplacement public ou privé. Les anciennes clés ne sont pas utilisables.
- Création : interface web uniquement. Dernier état observé le 6 novembre 2026 : upload désactivé pendant un incident de plateforme ; revérifier sa disponibilité avant publication.
- Métadonnées : édition web documentée pour titre, description, tags, catégorie, langue et confidentialité.
- Remplacement PDF : capacité non confirmée pour le compte ; aucune opération de remplacement n'est annoncée ou simulée.
- Suppression : non implémentée.
- Lecture et vérification : interface web assistée, sans API. Un ID numérique dans l'URL canonique est distinct du slug.
- Formats documentés : PDF et Office, plafond 300 Mo. Les PDF multipages sont classés comme documents.

Sources : [retrait de l'API](https://support.scribd.com/hc/en-us/articles/53231623793684-Slideshare-API-errors-the-public-API-has-been-retired), [incidents](https://support.scribd.com/hc/en-us/articles/53147543884692-Known-issues-on-Scribd-and-Slideshare), [métadonnées](https://support.scribd.com/hc/en-us/articles/8835209873300-Your-Slideshare-upload-settings), [formats](https://support.scribd.com/hc/en-us/articles/360055259612-Supported-Slideshare-file-formats-and-sizes). Compte cible : `MathieuLedru`.

## Prévention des doublons

Une correspondance ne se déduit jamais d'un titre ou d'un slug. Relever le véritable ID distant et faire confirmer la relation avec l'UUID canonique. Réserver une tentative persistée avant toute action web ; après interruption, réconcilier cette tentative au lieu de recommencer. Si un remplacement n'est pas pris en charge, signaler la limite et ne pas créer de doublon automatiquement.

Les publications antérieures observées sur les comptes sont conservées : aucune association non confirmée n'est créée pendant une intervention graphique. Le suivi des données et les commandes sont décrits dans [publications.md](publications.md).
