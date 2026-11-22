# Lecteurs Darkwood dans un article

Insérez un bloc de code nommé `darkwood-embed` dans le Markdown de la note Joplin. Ajoutez un lien Markdown ordinaire après chaque bloc pour que la note reste consultable directement dans Joplin.

````markdown
```darkwood-embed
{"type":"game","url":"https://darkwaar.com/games/prime-lanterns","title":"Prime Lanterns","cover":"https://darkwaar.com/assets/docs/blog/prime-lanterns/images/promotional-2HIpfG-.png"}
```

[Jouer sur Darkwaar](https://darkwaar.com/games/prime-lanterns)
````

````markdown
```darkwood-embed
{"type":"clip","url":"https://heroclip.darkwood.com/clips/01a20241-c293-748f-9dd4-ccfead5f65d8","title":"Les nombres invisibles","orientation":"portrait","cover":"https://heroclip.darkwood.com/media/clips/les-nombres-invisibles/thumbnail.jpg"}
```

[Regarder sur HeroClip](https://heroclip.darkwood.com/clips/01a20241-c293-748f-9dd4-ccfead5f65d8)
````

````markdown
```darkwood-embed
{"type":"set","url":"https://djmatyo91.darkwood.com/sets/01a20241-c177-7ce9-a9a8-af260e305622","title":"Prime Frequencies","cover":"https://djmatyo91.darkwood.com/covers/prime-frequencies.svg"}
```

[Écouter sur DJ Matyo](https://djmatyo91.darkwood.com/sets/01a20241-c177-7ce9-a9a8-af260e305622)
````

````markdown
```darkwood-embed
{"type":"map","url":"https://geo.darkwood.com/maps/population-europe-2025","title":"Population au 1ᵉʳ janvier 2025","cover":"https://geo.darkwood.com/covers/population-europe-2025.svg"}
```

[Consulter la carte et le tableau sur Geo](https://geo.darkwood.com/maps/population-europe-2025)
````

Le titre, le type et l’URL sont requis. Les jeux et cartes utilisent un slug canonique. Les clips et sets utilisent leur identifiant UUID. Les URL doivent être exactement celles d’une fiche publique HTTPS, sans paramètres ni fragment. La description et la couverture sont facultatives. La couverture doit appartenir au répertoire d’images publiques du produit. Le Markdown est rendu avec CommonMark, via le même type de rendu des blocs clôturés que Mermaid.

Portal ouvre le lecteur à la demande, en 9:16 pour les jeux et 16:9 pour les clips par défaut. Le champ `orientation` accepte `portrait` ou `landscape`. Le lecteur audio utilise une zone compacte. Fermer un lecteur arrête la lecture en retirant l’iframe. Le lien original reste disponible si JavaScript est désactivé, et si le lecteur ou le média est indisponible.

Les articles utilisant ces blocs sont rendus comme du Markdown de données, avec HTML brut retiré, liens à schéma risqué désactivés et sans interpolation Twig. Les publications historiques sans bloc utilisent leur chemin de rendu existant. Les URL de lecteur sont construites par Portal. Aucun attribut HTML de la note n’est transmis à l’iframe. La CSP des articles intégrant un bloc autorise les quatre hôtes ci-dessus. Les lecteurs et la carte Geo acceptent uniquement l’origine `https://blog.darkwood.com`. Le lecteur vidéo est sandboxé sans scripts. Godot et le rejeu audio utilisent leurs scripts de produit. Le jeu autorise seulement les connexions vers son export, ce qui bloque l’accès aux API de session depuis le lecteur.

Les vidéos pédagogiques YouTube, articles scientifiques, PDF et posts X peuvent rester des liens Markdown. Les plans de mixage et fichiers audio du DJ restent ceux de la publication. Le lecteur intégré évite les commandes du studio et le téléchargement initial des fichiers pour dessiner des waveforms.
