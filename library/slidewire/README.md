# Symfony AI in Action - SlideWire

Support de présentation SlideWire pour une vidéo YouTube pédagogique en français :

> Symfony AI in Action - Construire des fonctionnalités IA réalistes avec Symfony

L'angle éditorial est volontairement différent d'une reprise de conférence : la deck explique Symfony AI comme une stack applicative, puis insiste sur le vrai levier produit, l'orchestration contrôlée entre modèles, tools, mémoire, RAG, MCP et workflows.

## Lancer la présentation

```bash
cd /Users/math/Mathieu/Darkwood/presentations/ressources/2026-04-24-symfony-ai-live-berlin-2026/slidewire
npm install
npm run dev
php artisan serve
```

Ouvrir ensuite :

```text
http://127.0.0.1:8000
```

La même présentation est aussi disponible sur :

```text
http://127.0.0.1:8000/slides/symfony-ai-in-action
```

## Exporter en PDF

SlideWire rend la présentation dans le navigateur. Pour exporter :

1. lancer `npm run dev` et `php artisan serve`
2. ouvrir `http://127.0.0.1:8000`
3. utiliser l'impression du navigateur
4. destination : `Save as PDF`
5. format paysage, arrière-plans activés, marges désactivées ou minimales

Alternative automatisable si Playwright est installé dans l'environnement :

```bash
/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome \
  --headless=new \
  --disable-gpu \
  --print-to-pdf=symfony-ai-in-action.pdf \
  http://127.0.0.1:8000
```

## Sources utilisées

- Présentation originale : `../symfony-ai-in-action-sflive-berlin-2026.pdf`
- Thème Darkwood : `/Users/math/Mathieu/Darkwood/presentations/themes/darkwood/darkwood.pdf`
- Assets Darkwood : logos, fond, polices Jost
- Documentation SlideWire : https://slidewire.dev/docs
- Brief éditorial fourni pour cette adaptation

## Intention éditoriale

La vidéo doit éviter le piège "appel API vers ChatGPT". Le fil narratif est :

1. l'intégration IA produit est difficile parce qu'elle touche aux données, aux droits, au streaming, aux effets de bord et à l'UX
2. Symfony AI apporte des briques : Platform, Agent, Store, AI Bundle, MCP Bundle, MCP SDK
3. ces briques deviennent utiles quand elles servent une orchestration applicative claire
4. MCP et les workflows ouvrent la voie à des agents contrôlés, observables et intégrés au système métier

Le ton visé est sombre, premium, technique et sobre, avec peu de texte par slide et des schémas simples.
