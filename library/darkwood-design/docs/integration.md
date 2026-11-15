# Guide d'intégration

1. Inventorier les variables et composants existants avant toute modification.
2. Mapper les rôles locaux vers `--dw-text`, `--dw-background`, `--dw-surface`, `--dw-input`, `--dw-border`, `--dw-primary` et les états.
3. Conserver les noms publics du produit si les renommer casserait une API CSS ou un thème existant.
4. Ajouter Jost localement avec `font-display: swap` ; ne pas charger une police distante sans nécessité.
5. Persister le thème dans le navigateur seulement si l'application le faisait déjà.
6. Vérifier clair, sombre et sépia, puis clavier, contraste, réduction des mouvements, desktop et mobile.
7. Isoler le commit et le déploiement par produit.

## Package partagé

Un package runtime n'est pas justifié aujourd'hui. Les applications utilisent des stacks et cycles de publication différents. `tokens.css` sert de contrat documentaire et de source de comparaison. Un package deviendrait pertinent seulement si plusieurs produits devaient recevoir les mêmes correctifs synchronisés et si son versionnement réduisait réellement la maintenance.

## Compatibilité

Les produits peuvent exposer leurs anciens noms de variables comme alias vers les rôles `--dw-*`. Cette stratégie permet une migration progressive sans réécrire les composants métier.
