import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import crypto from 'node:crypto';
import {startDie,roll,mix,RED,YELLOW,ORANGE,WHITE,EMPTY,replay,GREEN_PATH,apply,startLevel} from '../../resources/js/darkwaar27/rules.js';
test('Pigment rolls are reversible and do not mutate the die',()=>{for(const direction of ['N','E','S','W']){const original=startDie();let die=original;for(let n=0;n<4;n++)die=roll(die,direction);assert.deepEqual(die,original);assert.deepEqual(original,startDie());}});
test('Pigment mixing and replay keep the original rules',()=>{assert.equal(mix(RED,YELLOW),ORANGE);assert.equal(mix(RED,WHITE),EMPTY);const frames=replay(0,GREEN_PATH);assert.equal(frames.length,GREEN_PATH.length+1);let state=startLevel(0);for(let i=0;i<GREEN_PATH.length;i++){state=apply(state,GREEN_PATH[i]).state;assert.deepEqual(state,frames[i+1].state);}});
test('Board generation and Pigment rules preserve their reference logic',()=>{const reference=JSON.parse(fs.readFileSync('tests/fixtures/game-logic.json'));const sha=s=>crypto.createHash('sha256').update(s).digest('hex');const board=fs.readFileSync('resources/js/darkwaar20/board.js','utf8').split('export function mountGrid')[0];assert.equal(sha(board),reference.boardCore);assert.equal(sha(fs.readFileSync('resources/js/darkwaar27/rules.js','utf8')),reference.pigmentRules);});
