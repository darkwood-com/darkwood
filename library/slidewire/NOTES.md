# Notes orales

## 1. Titre

Ouvrir sur l'angle : Symfony AI, ce n'est pas "faire un curl vers ChatGPT depuis PHP". C'est une stack pour construire des fonctionnalités IA dans une vraie application Symfony.

Transition : avant de parler composants, poser le problème produit.

## 2. Le problème

Insister sur le contraste entre la facilité de la démo et la difficulté de la production : contexte, droits, coûts, latence, validation, logs.

Transition : Symfony AI devient intéressant quand on le regarde comme une réponse d'architecture.

## 3. Vision

Dire clairement que la valeur n'est pas le modèle en lui-même, mais la capacité à l'intégrer proprement dans l'application.

Transition : présenter les briques sans entrer tout de suite dans les détails.

## 4. Vue d'ensemble

Platform, Agent et Store sont le coeur conceptuel. Les bundles et SDK rendent ces briques utilisables dans Symfony et dans des architectures MCP.

Transition : commencer par la couche la plus basse, Platform.

## 5. Platform

Expliquer l'abstraction provider/modèle. Le code métier ne doit pas dépendre trop fortement de l'API d'un fournisseur.

Transition : montrer que cette abstraction protège le code.

## 6. Providers

Le snippet doit rester secondaire. L'idée forte : même intention applicative, modèle interchangeable.

Transition : après l'appel simple, parler du flux de réponse.

## 7. Streaming

Ne pas réduire le streaming à "ça écrit en direct". Les deltas typés permettent de gérer texte, usage tokens et tool calls.

Transition : élargir encore l'entrée/sortie.

## 8. Multimodal

Audio, image, PDF et binaire impliquent des pipelines applicatifs. Symfony sert à orchestrer ces pipelines.

Transition : revenir à la fiabilité côté backend.

## 9. Structured Output

Le point à marteler : une vraie app a besoin de types, pas de parser une réponse libre.

Transition : une fois qu'on a des sorties fiables, on peut laisser le modèle demander des actions.

## 10. Agent

Décrire la boucle : le modèle raisonne, demande un tool, Symfony exécute, puis le modèle formule la suite.

Transition : zoomer sur l'interface entre IA et code métier.

## 11. Tool Calling

`#[AsTool]` rend une capacité applicative visible au modèle. Le tool doit rester petit, nommé clairement, et contrôlé.

Transition : exposer une action ne veut pas dire l'exécuter aveuglément.

## 12. Human in the Loop

Mettre l'accent sur les opérations sensibles : remboursement, suppression, publication, modification de prix. Le modèle propose ; l'application garde l'autorité.

Transition : pour décider correctement, l'agent a besoin de contexte.

## 13. Memory

La mémoire utile n'est pas forcément une mémoire conversationnelle générique. Elle vient souvent du profil utilisateur, de l'historique applicatif et des permissions.

Transition : au-delà de la mémoire courte, il faut connecter les documents et connaissances métier.

## 14. Store

Présenter le pipeline : charger, filtrer, transformer, vectoriser. Le RAG dépend autant de cette préparation que du modèle.

Transition : passer de l'indexation à l'usage par l'agent.

## 15. RAG

RAG = récupérer le bon contexte au bon moment. `SimilaritySearch` sert à alimenter l'agent avec des données applicatives vérifiables.

Transition : quand plusieurs capacités apparaissent, le sujet devient l'orchestration.

## 16. Multi-agent

Être prudent : multi-agent ne veut pas dire "plein de bots qui discutent". Il faut des rôles bornés, des handoffs et des traces.

Transition : MCP donne une grammaire plus ouverte pour connecter ces capacités.

## 17. MCP

Expliquer MCP comme protocole d'interopérabilité entre agents, tools, IDE et applications. Le point n'est pas la mode du moment, mais la découverte et l'exécution contrôlée de capacités.

Transition : faire l'analyse personnelle Darkwood.

## 18. Analyse Darkwood

Phrase centrale : le chatbot est l'interface visible, mais l'orchestration est le système réel. Insister sur permissions, contexte, traces, reprise de contrôle.

Transition : proposer une stack cible cohérente.

## 19. Stack cible

Symfony AI apporte les briques IA. MCP ouvre l'interopérabilité. Flow, Navi et Uniflow structurent les workflows et la navigation agentique.

Transition : conclure sur ce qu'il faut construire.

## 20. Conclusion

Terminer sur "features IA, pas prompts". Une fonctionnalité IA crédible est typée, connectée aux données, observable, contrôlée et intégrée au produit.
