# Symfony AI in Action - SlideWire

Support de présentation SlideWire pour [darkwood.com](https://darkwood.com)

## Lancer la présentation

```bash
cd /Users/math/Sites/darkwood/slidewire
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-com
composer install
test -f .env || cp .env.example .env
php artisan key:generate
test -f database/database.sqlite || touch database/database.sqlite
php artisan migrate
npm ci --ignore-scripts
npm run build
php artisan serve
```

L'initialisation est nécessaire au premier lancement : Laravel a besoin de la
clé dans `.env`, des tables SQLite et du manifeste Vite pour servir les slides.
Ne pas régénérer la clé d'une installation déjà configurée. Aux lancements
suivants, entrer dans le même environnement Nix puis lancer `php artisan serve`.
Pour développer les assets, lancer `npm run dev` dans un second terminal Nix.

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
