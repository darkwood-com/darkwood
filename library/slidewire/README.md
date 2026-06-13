# Symfony AI in Action - SlideWire

Support de présentation SlideWire pour [darkwood.com](https://darkwood.com)

## Lancer la présentation

```bash
cd /Users/math/Mathieu/Darkwood/presentations/ressources/2026-04-24-symfony-ai-live-berlin-2026/slidewire
npm install
npm run dev
composer install
composer setup
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

- Thème Darkwood : `Darkwood/presentations/themes/darkwood/darkwood.pdf`
- Assets Darkwood : logos, fond, polices Jost
- Documentation SlideWire : https://slidewire.dev/docs
- Brief éditorial fourni pour cette adaptation
