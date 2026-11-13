export function calculation(n) {
  if (!Number.isInteger(n) || n < 1 || n > 100000) throw new RangeError('Entier attendu entre 1 et 100 000.');
  let calls = 0;
  const memo = new Map();
  const compute = () => {
    if (!memo.has(n)) { calls++; memo.set(n, n * (n + 1) / 2); }
    return memo.get(n);
  };
  return { n, first: compute(), second: compute(), calls };
}

export function startRun(definition, id) {
  const total = definition.input.reduce((sum, n) => sum + n, 0);
  return { id, version: definition.version, cursor: 2, status: 'En pause', total,
    events: ['Collecter : ' + definition.input.length + ' valeurs [' + definition.input.join(', ') + ']', 'Additionner : total ' + total] };
}

export function resumeRun(run) {
  if (run.cursor !== 2) return run;
  return { ...run, cursor: 3, status: 'Terminée', events: [...run.events, 'Produire le bilan : ' + run.total + ' points'] };
}

export function gameAction(game, action) {
  if (action === 'forget') return { knowledge: false, phase: 'room', loops: 0 };
  if (action === 'observe') return { ...game, knowledge: true, phase: 'inscription' };
  if (action === 'rewind') return { ...game, phase: 'room', loops: game.loops + 1 };
  if (['moon', 'sun', 'star'].includes(action)) return { ...game, phase: action === 'moon' && game.knowledge ? 'won' : 'closed' };
  return game;
}

export function nextScene(scenes, input) {
  if (scenes.length >= 6) return scenes;
  const previous = scenes.at(-1), recalled = !!previous && input.memory;
  return [...scenes, {
    character: recalled ? previous.character : (previous ? 'Voyageur' : input.character.trim()),
    palette: recalled ? previous.palette : (previous ? 'bleu' : input.palette),
    prop: recalled ? previous.prop : (previous ? 'boussole' : 'lanterne'),
    place: ['La porte', 'Le pont', 'La forêt', 'La tour', 'Le sommet', 'Le retour'][scenes.length],
    parent: scenes.length, recalled
  }];
}

export function validState(value) {
  return value && value.version === 1
    && (value.result === null || (Number.isInteger(value.result?.n) && value.result.n >= 1 && value.result.n <= 100000 && value.result.result === value.result.n * (value.result.n + 1) / 2))
    && Array.isArray(value.runs) && value.runs.length <= 12
    && value.runs.every(run => typeof run.id === 'string' && run.id.length <= 40 && Number.isInteger(run.version) && [2, 3].includes(run.cursor) && ['En pause', 'Terminée'].includes(run.status) && Number.isFinite(run.total) && Array.isArray(run.events) && run.events.length === run.cursor && run.events.every(e => typeof e === 'string' && e.length <= 200))
    && typeof value.game?.knowledge === 'boolean' && ['room', 'inscription', 'closed', 'won'].includes(value.game.phase) && Number.isSafeInteger(value.game.loops) && value.game.loops >= 0
    && Array.isArray(value.scenes) && value.scenes.length <= 6
    && value.scenes.every(s => typeof s.character === 'string' && s.character.length > 0 && s.character.length <= 24 && ['ambre', 'bleu', 'vert'].includes(s.palette) && ['lanterne', 'boussole'].includes(s.prop) && typeof s.place === 'string' && s.place.length <= 40 && Number.isInteger(s.parent) && s.parent >= 0 && s.parent <= 5 && typeof s.recalled === 'boolean');
}

export function initialState() {
  return { version: 1, result: null, runs: [], game: { knowledge: false, phase: 'room', loops: 0 }, scenes: [] };
}
