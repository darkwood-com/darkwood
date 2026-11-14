import { darkwaarDecision, heroclipDecision, djDecision, flowDecision, uniflowDecision } from './intent-model.js';

const dataElement = document.getElementById('demo-data');
if (dataElement) initialize(JSON.parse(dataElement.textContent));

function initialize({ slug }) {
  const $ = id => document.getElementById(id);
  const stateKey = `darkwood-intent-${slug}-v1`;
  const remember = values => { try { localStorage.setItem(stateKey, JSON.stringify(values)); $('storage-status').textContent = 'Choix conservés dans ce navigateur.'; } catch { $('storage-status').textContent = 'Stockage indisponible ; la démonstration reste utilisable sur cette page.'; } };
  const restore = defaults => { try { return { ...defaults, ...JSON.parse(localStorage.getItem(stateKey) || '{}') }; } catch { return defaults; } };
  const options = ids => Object.fromEntries(ids.map(id => [id, $(id).value]));
  const setOptions = values => Object.entries(values).forEach(([id, value]) => { if ($(id)) $(id).value = value; });

  if (slug === 'darkwaar') {
    const ids = ['darkwaar-intent', 'darkwaar-context']; setOptions(restore(options(ids)));
    const render = () => { const choice = options(ids), result = darkwaarDecision(choice[ids[0]], choice[ids[1]]); $('darkwaar-decision').textContent = result.decision; $('darkwaar-result').innerHTML = `<dt>Planification</dt><dd>${result.plan}</dd><dt>Résultat attendu</dt><dd>${result.result}</dd><dt>Évaluation</dt><dd>${result.evaluation}</dd>`; remember(choice); };
    $('darkwaar-run').addEventListener('click', render); render();
  }
  if (slug === 'heroclip') {
    const ids = ['hero-intent', 'hero-context']; setOptions(restore(options(ids)));
    const render = () => { const choice = options(ids), result = heroclipDecision(choice[ids[0]], choice[ids[1]]); $('hero-decision').textContent = result.decision; $('hero-plan').textContent = result.plan; $('hero-evaluation').textContent = result.evaluation; $('hero-storyboard').replaceChildren(...result.scenes.map((scene, index) => { const card = document.createElement('article'); card.className = `scene intent-scene scene-${choice['hero-intent']}`; card.innerHTML = `<p class="eyebrow">PLAN 0${index + 1}</p><div class="scene-symbol" aria-hidden="true"></div><h3>${scene}</h3><p>${index === 0 ? 'Décision initiale' : 'Conséquence du plan précédent'}</p>`; return card; })); remember(choice); };
    $('hero-run').addEventListener('click', render); render();
  }
  if (slug === 'djmatyo') {
    const ids = ['dj-intent', 'dj-duration']; setOptions(restore(options(ids))); let audio;
    const render = () => { const choice = options(ids), result = djDecision(choice[ids[0]], choice[ids[1]]); $('dj-decision').textContent = `${result.label} · ${result.bpm} BPM`; $('dj-transition').textContent = result.transition; $('dj-evaluation').textContent = result.evaluation; $('energy-curve').replaceChildren(...result.levels.map((level, index) => { const bar = document.createElement('span'); bar.style.setProperty('--energy', level); bar.setAttribute('aria-label', `Étape ${index + 1}, énergie ${level} sur 10`); return bar; })); $('energy-curve').dataset.bpm = result.bpm; remember(choice); };
    $('dj-run').addEventListener('click', render); $('dj-play').addEventListener('click', () => { audio?.close(); audio = new AudioContext(); const bpm = Number($('energy-curve').dataset.bpm); [0, 3, 7, 10, 7, 3, 5, 2].forEach((note, i) => { const osc = audio.createOscillator(), gain = audio.createGain(), at = audio.currentTime + i * 60 / bpm / 2; osc.frequency.value = 220 * 2 ** (note / 12); gain.gain.setValueAtTime(0.0001, at); gain.gain.exponentialRampToValueAtTime(0.12, at + 0.01); gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.18); osc.connect(gain).connect(audio.destination); osc.start(at); osc.stop(at + 0.2); }); }); $('dj-stop').addEventListener('click', () => audio?.close()); render();
  }
  if (slug === 'flow') {
    const ids = ['flow-intent', 'flow-work']; setOptions(restore(options(ids)));
    const render = () => { const choice = options(ids), result = flowDecision(choice[ids[0]], choice[ids[1]]); $('flow-decision').textContent = result.decision; $('flow-reason').textContent = result.reason; $('flow-limit').textContent = `Limite : ${result.limit}`; remember(choice); };
    $('flow-run').addEventListener('click', render); render();
  }
  if (slug === 'uniflow') {
    const ids = ['uni-intent', 'uni-context']; setOptions(restore(options(ids)));
    const render = () => { const choice = options(ids), result = uniflowDecision(choice[ids[0]], choice[ids[1]]); $('uni-decision').textContent = result.decision; $('uni-steps').replaceChildren(...result.steps.map(step => { const item = document.createElement('li'); item.textContent = step; return item; })); $('uni-evaluation').textContent = result.evaluation; remember(choice); };
    $('uni-run').addEventListener('click', render); render();
  }
}
