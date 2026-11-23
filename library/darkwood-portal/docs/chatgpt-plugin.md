# Plugin Darkwood

Endpoint cible : `https://darkwood.com/mcp`.

Ce serveur public de découverte remplace l'ancien périmètre « Darkwood Game ». Il expose uniquement `darkwood_search_apps`, `darkwood_get_app`, `darkwood_search_articles`, `darkwood_get_article` et `darkwood_get_ecosystem`. API Platform MCP est désactivé pour ce serveur afin de ne pas réexposer les outils historiques d'action ou de jeu.

Le catalogue associe correctement Darkwood Flow au moteur PHP d'orchestration asynchrone et Uniflow à la plateforme de workflows. Les articles sont limités aux contenus actifs, publics et non premium. Le serveur ne relaie aucune requête vers les MCP spécialisés.

Le catalogue et les tests unitaires sont validés. Le smoke HTTP local reste à finaliser avec l'environnement complet du portail avant tout déploiement ou scan OpenAI.
