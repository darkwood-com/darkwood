import { calculation, startRun, resumeRun, gameAction, nextScene, initialState, validState } from './memory-model.js';

const dataElement = document.getElementById('demo-data');
if (dataElement) initialize(JSON.parse(dataElement.textContent));

function initialize(data) {
  const $ = id => document.getElementById(id);
  const element = (tag, text = null, className = '') => {
    const node = document.createElement(tag);
    if (text !== null) node.textContent = text;
    if (className) node.className = className;
    return node;
  };
  const number = n => new Intl.NumberFormat('fr-FR').format(n);
  const status = message => { $('storage-status').textContent = message; };
  const key = 'darkwood-memory-' + data.slug + '-v1';
  let state = initialState();
  try {
    const raw = localStorage.getItem(key);
    if (raw !== null) {
      if (raw.length > 100000) throw new Error('too large');
      const saved = JSON.parse(raw);
      if (validState(saved)) state = saved;
      else status('La mémoire locale est incompatible ; une nouvelle expérience est prête.');
    }
  } catch { status('Le stockage local ne peut pas être lu. Vous pouvez essayer la démonstration sur cette page.'); }
  function persist() {
    try { localStorage.setItem(key, JSON.stringify(state)); status('État conservé dans ce navigateur.'); }
    catch { status('Le stockage est indisponible. Les actions fonctionnent sur cette page, sans conservation après rechargement.'); }
  }

  if (data.slug === 'darkwood') {
    function showDecision(id) {
      id = id.trim().toUpperCase(); $('decision-id').value = id;
      const root = $('decision-result'), decision = Object.hasOwn(data.decisions, id) ? data.decisions[id] : null;
      root.replaceChildren();
      if (!decision) { root.append(element('p', 'Aucune décision ne correspond à cet identifiant. Aucun résultat n’est inventé.')); return; }
      const article = element('article', null, 'decision'); article.id = 'decision';
      const tags = element('div', null, 'tags');
      [id, decision.status, decision.date].forEach(t => tags.append(element('span', t)));
      article.append(tags, element('h2', decision.title), element('p', 'Provenance : ' + decision.source));
      const history = element('ol', null, 'timeline');
      decision.history.forEach(revision => {
        const li = element('li');
        li.append(element('span', 'v' + revision.revision + ' · ' + revision.date, 'mono'), element('h3', revision.text), element('p', revision.source)); history.append(li);
      });
      article.append(history); root.append(article);
    }
    showDecision(new URLSearchParams(location.search).get('id') || 'MEM-001');
    $('decision-search').addEventListener('submit', e => {
      e.preventDefault(); const id = $('decision-id').value.slice(0, 32).trim().toUpperCase(); showDecision(id);
      const url = new URL(location.href); url.searchParams.set('id', id); history.replaceState(null, '', url);
    });
  }

  if (data.slug === 'flow') {
    function render() {
      try {
        const value = calculation(Number($('flow-n').value));
        $('temporary-result').textContent = number(value.first);
        $('memo-result').textContent = value.calls + ' calcul · 2 lectures';
      } catch { $('temporary-result').textContent = 'Entier de 1 à 100 000'; $('memo-result').textContent = 'Calcul en attente'; }
      $('saved-result').textContent = state.result ? number(state.result.result) + ' · jusqu’à ' + state.result.n : 'Pas encore conservé';
    }
    $('flow-n').addEventListener('input', render);
    $('flow-form').addEventListener('submit', e => {
      e.preventDefault(); if (!$('flow-form').reportValidity()) return;
      const value = calculation(Number($('flow-n').value)); state.result = { n: value.n, result: value.first }; persist(); render();
    });
    $('flow-clear').addEventListener('click', () => { state.result = null; persist(); render(); }); render();
  }

  if (data.slug === 'uniflow') {
    function render() {
      const root = $('runs'); root.replaceChildren();
      $('run-resume').hidden = state.runs.at(-1)?.cursor !== 2;
      if (!state.runs.length) root.append(element('p', 'Aucune exécution pour l’instant. Lancez la recette.'));
      [...state.runs].reverse().forEach(run => {
        const article = element('article', null, 'run'), tags = element('div', null, 'tags');
        [run.id, run.status, run.cursor + '/3 étapes'].forEach(t => tags.append(element('span', t)));
        const events = element('ol'); run.events.forEach(event => events.append(element('li', event))); article.append(tags, events); root.append(article);
      });
    }
    $('run-start').addEventListener('click', () => { state.runs = [...state.runs, startRun(data.workflow, crypto.randomUUID().slice(0, 8))].slice(-12); persist(); render(); });
    $('run-resume').addEventListener('click', () => { if (state.runs.length) state.runs[state.runs.length - 1] = resumeRun(state.runs.at(-1)); persist(); render(); });
    $('run-clear').addEventListener('click', () => { state.runs = []; persist(); render(); }); render();
  }

  if (data.slug === 'darkwaar') {
    const descriptions = {
      room: ['Trois signes. Une issue.', 'Une inscription est visible près de la porte. Lisez-la avant de revenir au début.'],
      inscription: ['Une phrase dans la pierre.', '« Après le retour, choisissez la lune. » Revenez au début, puis ouvrez la porte.'],
      won: ['La porte s’ouvre.', 'Le monde avait recommencé. Votre connaissance vous a permis d’avancer.'],
      closed: ['La porte reste close.', 'Explorez la chambre avant de choisir un signe.']
    };
    function render() {
      $('game-loop').textContent = 'LA CHAMBRE / BOUCLE ' + state.game.loops;
      $('game-title').textContent = descriptions[state.game.phase][0]; $('game-description').textContent = descriptions[state.game.phase][1];
      $('knowledge').textContent = state.game.knowledge ? 'La lune ouvre la porte.' : 'Vous n’avez encore rien appris.';
      $('game-door').classList.toggle('opened', state.game.phase === 'won');
    }
    document.querySelectorAll('[data-game]').forEach(button => button.addEventListener('click', () => { state.game = gameAction(state.game, button.dataset.game); persist(); render(); })); render();
  }

  if (data.slug === 'heroclip') {
    function render() {
      const root = $('storyboard'); root.replaceChildren(); $('scene-next').disabled = state.scenes.length >= 6;
      if (!state.scenes.length) root.append(element('p', 'Créez le premier plan pour commencer.'));
      state.scenes.forEach((scene, i) => {
        const article = element('article', null, 'scene palette-' + scene.palette), art = element('div', null, 'scene-art'); art.setAttribute('aria-hidden', 'true');
        ['moon-shape', 'mountain', 'person', 'prop'].forEach(c => art.append(element('span', null, c)));
        const copy = element('div', null, 'scene-copy');
        copy.append(element('p', 'PLAN ' + (i + 1) + ' / ' + (scene.recalled ? 'CONTEXTE RAPPELÉ' : 'NOUVEAU CONTEXTE'), 'eyebrow'), element('h3', scene.place), element('p', scene.character + ' · ' + scene.palette + ' · ' + scene.prop), element('p', scene.parent ? 'Suite du plan ' + scene.parent : 'Début du récit', 'hint'));
        article.append(art, copy); root.append(article);
      });
    }
    $('scene-form').addEventListener('submit', e => {
      e.preventDefault(); if (!$('scene-form').reportValidity()) return;
      const character = $('scene-character').value.trim(); if (!character) { status('Indiquez un personnage.'); return; }
      state.scenes = nextScene(state.scenes, { character, palette: $('scene-palette').value, memory: $('scene-memory').checked }); persist(); render();
    });
    $('scene-clear').addEventListener('click', () => { state.scenes = []; persist(); render(); }); render();
  }
}
