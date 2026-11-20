# Outils PHP Navi

Le shell qualité sous `env/` fournit PHP 8.6 et les outils communs. Les
applications peuvent exécuter les binaires de ce shell avec leurs propres
configurations. Les extensions d’analyse qui dépendent de Doctrine, Twig
ou d’API Platform restent dans le projet qui les utilise.

L’analyse PHPStan de Navi charge l’autoloader de Navi. Son lock d’outil
contient uniquement le moteur. Les autres locks isolés sous `tools/`
permettent de lancer les tâches Castor existantes. Dans le profil qualité,
PHPStan, PHPUnit, PHP-CS-Fixer et Psalm utilisent des distributions
officielles épinglées par SHA-256. Rector utilise son verrou Composer.

Rector propose ses modifications en dry-run par défaut. Le jeu de règles
PHP de Navi cible 8.5 tant que le contrat de ce composant le requiert.
Les réglages Symfony suivent les dépendances Composer. Utiliser
PHP-CS-Fixer en mode de vérification avant d’appliquer des règles.
La version 3.95.32 refuse encore PHP 8.6. Ne pas forcer ce contrôle
ni appliquer les corrections sous un runtime non pris en charge.
