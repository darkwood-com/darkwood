(() => {
  const root = document.getElementById('sequencer');
  if (!root) return;
  const notes = [{ name: 'La', hz: 440 }, { name: 'Sol', hz: 392 }, { name: 'Mi', hz: 329.63 }, { name: 'Do', hz: 261.63 }];
  const initial = { version: 1, tempo: 140, steps: [3, -1, 2, -1, 1, 2, 0, -1] };
  let pattern = structuredClone(initial), audio = null, timer = null, step = 0;
  const grid = document.getElementById('note-grid'), tempo = document.getElementById('tempo');
  const status = document.getElementById('music-status');
  const say = message => { status.textContent = message; };
  const valid = p => p && p.version === 1 && Number.isInteger(p.tempo) && p.tempo >= 80 && p.tempo <= 180 && Array.isArray(p.steps) && p.steps.length === 8 && p.steps.every(n => Number.isInteger(n) && n >= -1 && n < 4);
  function render() {
    grid.replaceChildren();
    notes.forEach((note, row) => {
      const label = document.createElement('span'); label.textContent = note.name; label.className = 'note-label'; grid.append(label);
      pattern.steps.forEach((value, column) => {
        const button = document.createElement('button');
        button.type = 'button'; button.className = 'note-cell'; button.dataset.step = column;
        button.setAttribute('aria-label', note.name + ', pas ' + (column + 1));
        button.setAttribute('aria-pressed', String(value === row));
        button.textContent = value === row ? '●' : '·';
        button.addEventListener('click', () => { pattern.steps[column] = value === row ? -1 : row; render(); });
        grid.append(button);
      });
    });
    tempo.value = pattern.tempo; document.getElementById('tempo-value').textContent = pattern.tempo;
  }
  function stop() {
    if (timer !== null) clearTimeout(timer);
    timer = null; document.getElementById('play').disabled = false;
    grid.querySelectorAll('.playing').forEach(c => c.classList.remove('playing'));
  }
  function tick() {
    grid.querySelectorAll('.playing').forEach(c => c.classList.remove('playing'));
    grid.querySelectorAll('[data-step="' + step + '"]').forEach(c => c.classList.add('playing'));
    const note = pattern.steps[step];
    if (note >= 0) {
      const now = audio.currentTime, oscillator = audio.createOscillator(), gain = audio.createGain();
      oscillator.type = 'triangle'; oscillator.frequency.value = notes[note].hz;
      gain.gain.setValueAtTime(0, now); gain.gain.linearRampToValueAtTime(0.12, now + 0.01);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.20);
      oscillator.connect(gain); gain.connect(audio.destination);
      oscillator.onended = () => { oscillator.disconnect(); gain.disconnect(); };
      oscillator.start(now); oscillator.stop(now + 0.22);
    }
    step = (step + 1) % 8; timer = setTimeout(tick, 60000 / pattern.tempo / 2);
  }
  document.getElementById('play').addEventListener('click', async () => {
    stop();
    try {
      const Audio = window.AudioContext || window.webkitAudioContext;
      if (!Audio) throw new Error('unsupported');
      audio ??= new Audio(); await audio.resume(); step = 0;
      document.getElementById('play').disabled = true; tick(); say('Lecture du motif original en boucle.');
    } catch { say('La synthèse audio n’est pas disponible dans ce navigateur. Le séquenceur et l’export restent utilisables.'); }
  });
  document.getElementById('stop').addEventListener('click', () => { stop(); say('Lecture arrêtée.'); });
  tempo.addEventListener('input', () => { pattern.tempo = Number(tempo.value); document.getElementById('tempo-value').textContent = pattern.tempo; });
  document.getElementById('save-pattern').addEventListener('click', () => {
    try { localStorage.setItem('darkwood-memory-motif-v1', JSON.stringify(pattern)); say('Motif conservé dans ce navigateur.'); }
    catch { say('Le navigateur refuse le stockage. Vous pouvez exporter le motif en JSON.'); }
  });
  document.getElementById('load-pattern').addEventListener('click', () => {
    try {
      const saved = JSON.parse(localStorage.getItem('darkwood-memory-motif-v1'));
      if (!valid(saved)) { say('Aucun motif compatible à retrouver. Conservez-en un ou partez du motif initial.'); return; }
      stop(); pattern = saved; render(); say('Motif retrouvé, notes et tempo restaurés.');
    } catch { say('La mémoire locale ne peut pas être lue.'); }
  });
  document.getElementById('vary-pattern').addEventListener('click', () => {
    pattern.steps = [pattern.steps.at(-1), ...pattern.steps.slice(0, -1)]; render();
    say('Variation : le motif a été décalé d’un pas. La version conservée reste intacte.');
  });
  document.getElementById('export-pattern').addEventListener('click', () => {
    const url = URL.createObjectURL(new Blob([JSON.stringify(pattern, null, 2)], { type: 'application/json' }));
    const link = document.createElement('a'); link.href = url; link.download = 'motif-original.json'; link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000); say('Export JSON préparé : notes et tempo, sans contenu audio tiers.');
  });
  document.getElementById('clear-pattern').addEventListener('click', () => {
    try { localStorage.removeItem('darkwood-memory-motif-v1'); say('La version conservée a été effacée. Le motif de travail reste disponible.'); }
    catch { say('Le navigateur refuse l’accès au stockage.'); }
  });
  window.addEventListener('pagehide', () => { stop(); audio?.close(); audio = null; });
  render();
})();
