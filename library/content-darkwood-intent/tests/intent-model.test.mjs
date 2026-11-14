import test from 'node:test';
import assert from 'node:assert/strict';
import { darkwaarDecision, heroclipDecision, djDecision, flowDecision, uniflowDecision } from '../public/intent-model.js';

test('Darkwaar changes its decision when the route is known', () => {
  assert.equal(darkwaarDecision('carry', 'unknown').decision, 'Reconnaître la rive d’abord');
  assert.equal(darkwaarDecision('carry', 'known').decision, 'Suivre la route reconnue');
});

test('Heroclip changes narrative and continuity independently', () => {
  assert.notDeepEqual(heroclipDecision('mystery', 'strict').scenes, heroclipDecision('escape', 'strict').scenes);
  assert.notEqual(heroclipDecision('mystery', 'strict').evaluation, heroclipDecision('mystery', 'free').evaluation);
});

test('DJ intentions produce bounded, distinct energy curves', () => {
  const opening = djDecision('opening', 'standard');
  const peak = djDecision('peak', 'standard');
  assert.ok(peak.bpm > opening.bpm);
  assert.ok(peak.levels.every(level => level >= 1 && level <= 10));
});

test('Flow selects inline, event loop or measured parallelism', () => {
  assert.equal(flowDecision('simple', 'io').decision, 'PHP inline');
  assert.equal(flowDecision('wait', 'io').decision, 'Amp / Revolt (H1)');
  assert.match(flowDecision('wait', 'cpu').decision, /Mesurer/);
});

test('Uniflow stops a definition before effects when context is incomplete', () => {
  assert.equal(uniflowDecision('register', 'missing').decision, 'Suspendre avant effet');
  assert.equal(uniflowDecision('register', 'missing').steps.length, 2);
  assert.equal(uniflowDecision('register', 'complete').steps.length, 3);
});
