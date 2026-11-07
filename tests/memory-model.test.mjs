import test from 'node:test';
import assert from 'node:assert/strict';
import { calculation, startRun, resumeRun, gameAction, nextScene, initialState, validState } from '../public/memory-model.js';

test('Flow reuses one computation and rejects invalid ranges', () => {
  assert.deepEqual(calculation(10), { n: 10, first: 55, second: 55, calls: 1 });
  for (const value of [-1, 0, 1.5, 100001, NaN]) assert.throws(() => calculation(value), RangeError);
});
test('Uniflow resumes the same checkpoint once without replay', () => {
  const run = startRun({ version: 1, input: [12, 8, 5] }, 'stable-id');
  const resumed = resumeRun(run);
  assert.equal(run.cursor, 2); assert.equal(resumed.id, run.id); assert.equal(resumed.cursor, 3);
  assert.deepEqual(resumed.events.slice(0, 2), run.events); assert.match(resumed.events[2], /25 points/);
  assert.deepEqual(resumeRun(resumed), resumed);
});
test('Darkwaar keeps knowledge across reset and allows explicit forgetting', () => {
  const fresh = initialState().game;
  assert.equal(gameAction(fresh, 'moon').phase, 'closed');
  const learned = gameAction(fresh, 'observe'), reset = gameAction(learned, 'rewind');
  assert.equal(reset.knowledge, true); assert.equal(reset.loops, 1); assert.equal(reset.phase, 'room');
  assert.equal(gameAction(reset, 'moon').phase, 'won'); assert.deepEqual(gameAction(reset, 'forget'), fresh);
});
test('Heroclip preserves context only when asked and bounds the storyboard', () => {
  const options = { character: 'Ada', palette: 'vert', memory: true };
  let scenes = nextScene([], options); scenes = nextScene(scenes, { ...options, character: 'Other' });
  assert.equal(scenes[1].character, 'Ada'); assert.equal(scenes[1].prop, 'lanterne');
  scenes = nextScene(scenes, { ...options, memory: false });
  assert.equal(scenes[2].character, 'Voyageur'); assert.equal(scenes[2].palette, 'bleu');
  for (let n = 0; n < 10; n++) scenes = nextScene(scenes, options);
  assert.equal(scenes.length, 6); assert.equal(scenes[5].parent, 5);
});
test('Browser state survives serialization and rejects corrupt values', () => {
  const state = initialState(); state.result = { n: 10, result: 55 };
  state.runs = [startRun({ version: 1, input: [12, 8, 5] }, 'id')];
  assert.equal(validState(JSON.parse(JSON.stringify(state))), true);
  assert.equal(!!validState(null), false);
  assert.equal(!!validState({ ...state, version: 2 }), false);
  assert.equal(!!validState({ ...state, result: { n: 10, result: 999 } }), false);
  assert.equal(!!validState({ ...state, game: { ...state.game, phase: '<script>' } }), false);
  assert.equal(!!validState({ ...state, runs: [...state.runs, { id: 'broken' }] }), false);
});
