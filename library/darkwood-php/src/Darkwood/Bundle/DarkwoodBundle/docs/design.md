# Darkwood Design

Référentiel public des conventions graphiques effectivement partagées dans l'écosystème Darkwood.

Ce dépôt ne fournit ni framework CSS ni dépendance runtime. Il documente le plus petit vocabulaire commun observé dans le portail Darkwood, Uniflow, Darkwaar, Heroclip, DJ Matyo, Darkwood Flow, Memory, Intention et Slidewire. Un produit peut copier les tokens nécessaires ou les adapter à son contexte, à condition de conserver les rôles sémantiques et les garanties d'accessibilité.

## Fondations canoniques

- **Typographie** : Jost pour les interfaces et les présentations. `system-ui, sans-serif` reste le fallback. Les blocs de code utilisent JetBrains Mono ou un monospace système.
- **Couleur d'action** : `#007bff`. Le bleu identifie liens, focus, actions principales et progression. Il ne remplace pas les couleurs d'état.
- **Thèmes** : clair, sombre et sépia. Le changement porte sur des rôles sémantiques, jamais sur les composants métier.
- **Structure** : header de 60 px sur desktop, navigation mobile persistante lorsque le produit en a besoin, contenu limité à 1140 ou 1180 px.
- **Interaction** : cible minimale de 44 px, focus visible de 3 px, état désactivé perceptible autrement que par la couleur.
- **Mouvement** : transitions courtes de 200 ms et respect de `prefers-reduced-motion`.

Les tokens de référence sont dans [`tokens.css`](../public/tokens.css). La page [d’exemples](../public/design-examples.html) montre les composants sans JavaScript ni infrastructure. Le [guide d'intégration](design-integration.md) définit la méthode d'adoption.

## Composants de référence

1. marque carrée et nom de produit ;
2. header et liens de navigation ;
3. navigation mobile adaptée aux fonctions essentielles ;
4. card avec titre, description, métadonnées et action ;
5. boutons primaire, secondaire et désactivé ;
6. champs, sélecteurs et messages d'erreur ;
7. tableau lisible avec en-têtes explicites ;
8. états succès, avertissement et erreur ;
9. surfaces et séparateurs ;
10. blocs éditoriaux et blocs de code.

## Différences justifiées

- **Darkwaar** conserve une interface plus visuelle et tactile autour des jeux.
- **Heroclip Studio** conserve son environnement sombre cinématique et ses accents violets : ils servent le montage, pas l'identité générale du catalogue.
- **DJ Matyo Studio** conserve les waveforms et contrôles audio, tout en adoptant les rôles de couleur, la typographie et les thèmes communs.
- **Slidewire** conserve une échelle typographique propre au 16:9 et une navigation discrète.
- **Flow** reste une documentation technique ; aucune dépendance graphique ne doit entrer dans son runtime.
- **Uniflow** conserve Bootstrap et ses composants métier. Ses variables Sass correspondent déjà aux rôles Darkwood.

## Adoption

Chaque produit adopte les conventions par petits commits autonomes. Les tests fonctionnels, le responsive, les trois thèmes, le contraste et le focus clavier précèdent tout déploiement. Une différence utile au produit reste locale et doit être documentée plutôt que supprimée.

## Licence et logos

Les sources propres à la card suivent la licence du monorepo. Les marques et logos restent des identifiants Darkwood ; leur présence dans ce référentiel n'autorise pas une représentation trompeuse d'un produit tiers.
