import {
    BLACK,
    BLUE,
    COLORS,
    EMPTY,
    GLYPHS,
    GREEN,
    GREEN_ALT,
    GREEN_PATH,
    HEIGHT,
    NAMES,
    ORANGE,
    PURPLE,
    RED,
    WHITE,
    WIDTH,
    YELLOW,
    apply,
    mix,
    replay,
    roll,
    startDie,
    startLevel,
} from './rules';

function stop(event) {
    event.stopPropagation();
    event.preventDefault();
}

export function mountBoard(host, { cell = 36 } = {}) {
    host.innerHTML = '';
    host.style.setProperty('--pg-cell', `${cell}px`);
    host.classList.add('pg-board');

    const nodes = [];

    for (let y = 0; y < HEIGHT; y += 1) {
        for (let x = 0; x < WIDTH; x += 1) {
            const node = document.createElement('div');
            node.className = 'pg-cell';
            node.dataset.x = String(x);
            node.dataset.y = String(y);
            host.appendChild(node);
            nodes.push(node);
        }
    }

    return nodes;
}

export function paintBoard(nodes, state, { showDie = true } = {}) {
    const markMap = new Map(state.marks.map((mark) => [`${mark.at[0]},${mark.at[1]}`, mark.color]));

    nodes.forEach((node, i) => {
        const x = i % WIDTH;
        const y = Math.floor(i / WIDTH);
        const stain = state.stains[i];
        const mark = markMap.get(`${x},${y}`);
        const isDie = showDie && state.pos[0] === x && state.pos[1] === y;

        node.className = 'pg-cell';
        node.style.background = stain === EMPTY ? '' : COLORS[stain];
        node.classList.toggle('is-sealed', stain === BLACK);
        node.classList.toggle('is-die', isDie);
        node.classList.toggle('has-mark', mark !== undefined);
        node.innerHTML = '';

        if (mark !== undefined) {
            const ring = document.createElement('span');
            ring.className = 'pg-mark';
            ring.style.borderColor = COLORS[mark] || '#f4efe4';
            ring.textContent = GLYPHS[mark] ?? '·';
            node.appendChild(ring);
        }

        if (isDie) {
            const die = document.createElement('span');
            die.className = 'pg-die-token';
            const face = state.die.D;
            die.style.background = COLORS[face] || '#f4efe4';
            die.style.color =
                face === YELLOW || face === WHITE || face === EMPTY ? '#1a140f' : '#f4efe4';
            die.textContent = GLYPHS[face] ?? '·';
            node.appendChild(die);
        }
    });
}

export function paintCompass(host, die) {
    if (!host) {
        return;
    }

    const faces = [
        ['D', die.D],
        ['N', die.N],
        ['E', die.E],
        ['S', die.S],
        ['W', die.W],
    ];

    host.innerHTML = faces
        .map(
            ([key, face]) => `
        <div class="pg-swatch" data-face="${key}">
            <b style="background:${COLORS[face]}">${GLYPHS[face]}</b>
            <span>${key === 'D' ? 'Down' : key}</span>
        </div>`,
        )
        .join('');
}

export function bindTipDemo(slide) {
    const boardHost = slide.querySelector('[data-pg-board="tip"]');
    const compass = slide.querySelector('[data-pg-compass="tip"]');
    const status = slide.querySelector('[data-pg-status="tip"]');
    const nodes = mountBoard(boardHost, { cell: 44 });
    let state = startLevel(0);

    const render = (note) => {
        paintBoard(nodes, state);
        paintCompass(compass, state.die);
        if (status) {
            status.textContent = note;
        }
    };

    render('Tip East — yellow lands.');

    slide.querySelectorAll('[data-pg-tip]').forEach((button) => {
        button.addEventListener('click', (event) => {
            stop(event);
            const action = button.dataset.pgTip;
            const result = apply(state, action);

            if (!result.events.accepted) {
                render('Refused — no move spent.');
                return;
            }

            state = result.state;
            const face = NAMES[result.events.face];
            render(`${action} → ${face} → ${NAMES[result.events.after]}`);
        });
    });

    slide.querySelector('[data-pg-reset="tip"]')?.addEventListener('click', (event) => {
        stop(event);
        state = startLevel(0);
        render('Tip East — yellow lands.');
    });

    return {
        show() {
            render(status?.textContent || 'Tip East — yellow lands.');
        },
        setStep(step) {
            state = startLevel(0);
            if (step < 1) {
                render('Tip East — yellow lands.');
                return;
            }
            const result = apply(state, 'E');
            if (!result.events.accepted) {
                render('Refused — no move spent.');
                return;
            }
            state = result.state;
            render(`E → ${NAMES[result.events.face]} → ${NAMES[result.events.after]}`);
        },
        destroy() {},
    };
}

export function bindDieOrientation(slide) {
    const compass = slide.querySelector('[data-pg-compass="die"]');
    const faceGrid = slide.querySelector('[data-pg-faces]');
    const note = slide.querySelector('[data-pg-status="die"]');
    let die = startDie();

    const render = (action) => {
        paintCompass(compass, die);

        if (faceGrid) {
            faceGrid.innerHTML = Object.entries(die)
                .map(
                    ([key, face]) => `
                <div class="pg-face-card">
                    <span>${key}</span>
                    <b style="background:${COLORS[face]};color:${face === YELLOW || face === WHITE || face === EMPTY ? '#1a140f' : '#f4efe4'}">${NAMES[face]}</b>
                </div>`,
                )
                .join('');
        }

        if (note) {
            note.textContent = action
                ? `After ${action}: Down is ${NAMES[die.D]}`
                : 'Initial orientation. Tip any direction.';
        }
    };

    render(null);

    slide.querySelectorAll('[data-pg-roll]').forEach((button) => {
        button.addEventListener('click', (event) => {
            stop(event);
            const action = button.dataset.pgRoll;
            die = roll(die, action);
            render(action);
        });
    });

    slide.querySelector('[data-pg-reset="die"]')?.addEventListener('click', (event) => {
        stop(event);
        die = startDie();
        render(null);
    });

    return {
        show() {
            render(null);
        },
        setStep(step) {
            die = startDie();
            if (step < 1) {
                render(null);
                return;
            }
            die = roll(die, 'E');
            render('E');
        },
        destroy() {},
    };
}

export function bindMixer(slide) {
    const result = slide.querySelector('[data-pg-mix-result]');
    const label = slide.querySelector('[data-pg-mix-label]');
    let existing = EMPTY;
    let face = YELLOW;

    const pairs = [
        [RED, YELLOW, 'Red + Yellow'],
        [RED, BLUE, 'Red + Blue'],
        [YELLOW, BLUE, 'Yellow + Blue'],
        [EMPTY, WHITE, 'White clears'],
        [ORANGE, BLACK, 'Black seals'],
        [GREEN, EMPTY, 'Null leaves'],
    ];

    const paint = (a, b, title) => {
        existing = a;
        face = b;
        const next = mix(a, b);

        if (result) {
            result.style.background = COLORS[next];
            result.textContent = GLYPHS[next];
            result.style.color = next === YELLOW || next === WHITE || next === EMPTY ? '#1a140f' : '#f4efe4';
        }

        if (label) {
            label.textContent = `${title} → ${NAMES[next]}`;
        }
    };

    const order = [0, 1, 2, 3, 4, 5];

    paint(...pairs[order[0]]);

    slide.querySelectorAll('[data-pg-mix]').forEach((button, i) => {
        button.addEventListener('click', (event) => {
            stop(event);
            const [a, b, title] = pairs[Number(button.dataset.pgMix ?? i)];
            paint(a, b, title);
        });
    });

    return {
        show() {
            paint(existing, face, `${NAMES[existing]} + ${NAMES[face]}`);
        },
        setStep(step) {
            const pair = pairs[order[Math.min(Math.max(step, 0), order.length - 1)]];
            paint(...pair);
        },
        destroy() {},
    };
}

export function bindGreenReplay(slide) {
    const boardHost = slide.querySelector('[data-pg-board="green"]');
    const compass = slide.querySelector('[data-pg-compass="green"]');
    const status = slide.querySelector('[data-pg-status="green"]');
    const stepLabel = slide.querySelector('[data-pg-step]');
    const nodes = mountBoard(boardHost, { cell: 40 });
    const frames = replay(1, GREEN_PATH);
    let index = 0;

    const render = () => {
        const frame = frames[index];
        paintBoard(nodes, frame.state);
        paintCompass(compass, frame.state.die);

        if (stepLabel) {
            stepLabel.textContent = `${index} / ${GREEN_PATH.length}`;
        }

        if (status) {
            if (index === 0) {
                status.textContent = 'Start at (0,0). Target: Green at (1,0).';
            } else {
                const events = frame.events;
                status.textContent = `${events.action}: ${NAMES[events.face]} → ${NAMES[events.after]} · phase ${frame.state.phase}`;
            }
        }
    };

    render();

    slide.querySelector('[data-pg-next="green"]')?.addEventListener('click', (event) => {
        stop(event);
        index = Math.min(index + 1, frames.length - 1);
        render();
    });

    slide.querySelector('[data-pg-prev="green"]')?.addEventListener('click', (event) => {
        stop(event);
        index = Math.max(index - 1, 0);
        render();
    });

    slide.querySelector('[data-pg-reset="green"]')?.addEventListener('click', (event) => {
        stop(event);
        index = 0;
        render();
    });

    return {
        show(step) {
            if (typeof step === 'number') {
                index = Math.min(Math.max(step, 0), frames.length - 1);
            }
            render();
        },
        destroy() {},
    };
}

export function bindLeaveCompare(slide) {
    const aHost = slide.querySelector('[data-pg-board="leave-a"]');
    const bHost = slide.querySelector('[data-pg-board="leave-b"]');
    const aNodes = mountBoard(aHost, { cell: 28 });
    const bNodes = mountBoard(bHost, { cell: 28 });
    const aFrames = replay(2, GREEN_PATH);
    const bFrames = replay(2, GREEN_ALT);
    const aEnd = aFrames.at(-1).state;
    const bEnd = bFrames.at(-1).state;

    paintBoard(aNodes, aEnd);
    paintBoard(bNodes, bEnd);

    return {
        show() {
            paintBoard(aNodes, aEnd);
            paintBoard(bNodes, bEnd);
        },
        destroy() {},
    };
}

export function paintMiniLevels(slide) {
    const hosts = slide.querySelectorAll('[data-pg-mini]');

    hosts.forEach((host) => {
        const level = Number(host.dataset.pgMini);
        const nodes = mountBoard(host, { cell: 18 });
        const state = startLevel(level);
        paintBoard(nodes, state, { showDie: true });
    });

    return {
        show() {
            hosts.forEach((host) => {
                const level = Number(host.dataset.pgMini);
                const nodes = [...host.querySelectorAll('.pg-cell')];
                paintBoard(nodes, startLevel(level));
            });
        },
        destroy() {},
    };
}

export function bindUndoDemo(slide) {
    const boardHost = slide.querySelector('[data-pg-board="undo"]');
    const status = slide.querySelector('[data-pg-status="undo"]');
    const nodes = mountBoard(boardHost, { cell: 36 });
    let state = startLevel(0);
    const history = [];

    const render = (note) => {
        paintBoard(nodes, state);
        if (status) {
            status.textContent = note;
        }
    };

    render('S₀ · tip East, then Undo.');

    slide.querySelector('[data-pg-undo-tip]')?.addEventListener('click', (event) => {
        stop(event);
        const result = apply(state, 'E');
        if (!result.events.accepted) {
            return;
        }
        history.push(state);
        state = result.state;
        render(`S₁ · ${NAMES[result.events.after]} · spent ${state.spent}`);
    });

    slide.querySelector('[data-pg-undo-back]')?.addEventListener('click', (event) => {
        stop(event);
        if (!history.length) {
            render('Nothing to undo.');
            return;
        }
        state = history.pop();
        render(`S₀ restored · spent ${state.spent}`);
    });

    slide.querySelector('[data-pg-reset="undo"]')?.addEventListener('click', (event) => {
        stop(event);
        state = startLevel(0);
        history.length = 0;
        render('S₀ · tip East, then Undo.');
    });

    return {
        show() {
            render(status?.textContent || 'S₀ · tip East, then Undo.');
        },
        setStep(step) {
            state = startLevel(0);
            history.length = 0;
            if (step < 1) {
                render('S₀ · tip East, then Undo.');
                return;
            }
            const result = apply(state, 'E');
            if (!result.events.accepted) {
                render('Refused.');
                return;
            }
            history.push(state);
            state = result.state;
            if (step < 2) {
                render(`S₁ · ${NAMES[result.events.after]} · spent ${state.spent}`);
                return;
            }
            state = history.pop();
            render(`S₀ restored · spent ${state.spent}`);
        },
        destroy() {},
    };
}

export { GREEN_PATH, PURPLE, ORANGE };
