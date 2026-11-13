# Présentations

## Créer une source

```sh
php artisan make:slidewire my-talk --title="Mon titre"
php artisan make:slidewire my-talk --multi --md
```

La forme simple crée un fichier Blade avec une couverture et une slide de contenu. `--multi` crée un dossier contenant `deck.blade.php` et les fichiers de slides. `--md` utilise Markdown pour le contenu principal tout en conservant la structure des composants natifs.

Les modèles sont dans `stubs/slidewire/`. Les titres sont du texte littéral ; le générateur protège les sources existantes. `--force` ne change pas leur format et ne supprime pas les fichiers additionnels.

## Enregistrer une présentation

Ajouter une entrée dans `resources/presentations.json` avec :

- `id` : UUID attribué une seule fois ;
- `source_slug` : nom de la source et suffixe de la route `/slides/SLUG` ;
- `title` : titre du catalogue.

Le catalogue et la route sont alors déclarés automatiquement. Exécuter `php artisan publications:catalog` pour actualiser le suivi en base.

Pour changer un titre ou un slug, conserver l'UUID et mettre à jour la source et le catalogue ensemble. Les associations externes restent attachées à l'UUID.

## Composer les slides

Utiliser les composants partagés `deck`, `slide`, `cover`, `section`, `heading`, `body`, `content`, `columns`, `code`, `terminal`, `footer` et `conclusion`. Les variantes de capture de code et terminal partagent leur surface avec les composants textuels.

Un en-tête contient un titre principal et, si nécessaire, un surtitre et un sous-titre. Les éléments absents ne doivent pas créer de marge vide. Les règles visuelles sont décrites dans le [design system](design-system.md).

```blade
<x-darkwood.code :source="$source" language="php" title="example.php" />
<x-darkwood.terminal :source="$commands" title="Terminal" />
```

Les exemples de code sont transmis comme chaînes, sans exécution. Les diagrammes Mermaid conservent leur notation dans le contenu de la présentation. Vérifier le comportement des fragments, jeux, animations et liens dans le lecteur avant de préparer un PDF.

## Auteurs

`config/authors.php` associe les identifiants d'auteur aux portraits locaux. Le composant `author-portrait` et les conclusions choisissent le portrait correspondant ; un auteur inconnu utilise le logo de repli et ne reçoit pas le portrait d'un autre auteur.

Conserver les proportions et les couleurs intrinsèques des images éditoriales. Les assets publics doivent être dépourvus de secrets et de métadonnées personnelles inutiles.
