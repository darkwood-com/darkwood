/** Faithful port of Darkwaar27 Pigment rules.gd */

export const WIDTH = 5;
export const HEIGHT = 5;

export const EMPTY = 0;
export const RED = 1;
export const YELLOW = 2;
export const BLUE = 3;
export const WHITE = 4;
export const BLACK = 5;
export const ORANGE = 6;
export const PURPLE = 7;
export const GREEN = 8;

export const NAMES = {
    [EMPTY]: 'Null',
    [RED]: 'Red',
    [YELLOW]: 'Yellow',
    [BLUE]: 'Blue',
    [WHITE]: 'White',
    [BLACK]: 'Black',
    [ORANGE]: 'Orange',
    [PURPLE]: 'Purple',
    [GREEN]: 'Green',
};

export const GLYPHS = {
    [EMPTY]: '·',
    [RED]: 'R',
    [YELLOW]: 'Y',
    [BLUE]: 'B',
    [WHITE]: 'W',
    [BLACK]: 'K',
    [ORANGE]: 'O',
    [PURPLE]: 'P',
    [GREEN]: 'G',
};

export const COLORS = {
    [EMPTY]: '#2a241c',
    [RED]: '#d24a3a',
    [YELLOW]: '#e2b134',
    [BLUE]: '#3c6fd6',
    [WHITE]: '#f4efe4',
    [BLACK]: '#1a1a1a',
    [ORANGE]: '#e07a2f',
    [PURPLE]: '#8a4bb5',
    [GREEN]: '#3e9a5a',
};

export const DIRS = {
    N: [0, -1],
    E: [1, 0],
    S: [0, 1],
    W: [-1, 0],
};

export function startDie() {
    return { D: EMPTY, U: BLACK, N: RED, S: BLUE, E: YELLOW, W: WHITE };
}

export function roll(die, action) {
    const { D: d, U: u, N: n, S: s, E: e, W: w } = die;

    switch (action) {
        case 'E':
            return { D: e, U: w, N: n, S: s, E: u, W: d };
        case 'W':
            return { D: w, U: e, N: n, S: s, E: d, W: u };
        case 'N':
            return { D: n, U: s, N: u, S: d, E: e, W: w };
        case 'S':
            return { D: s, U: n, N: d, S: u, E: e, W: w };
        default:
            return { ...die };
    }
}

export function mix(existing, face) {
    if (face === EMPTY) {
        return existing;
    }
    if (face === WHITE) {
        return EMPTY;
    }
    if (face === BLACK) {
        return BLACK;
    }
    if (existing === EMPTY) {
        return face;
    }
    if (existing === face) {
        return existing;
    }

    const low = Math.min(existing, face);
    const high = Math.max(existing, face);

    if (low === RED && high === YELLOW) {
        return ORANGE;
    }
    if (low === RED && high === BLUE) {
        return PURPLE;
    }
    if (low === YELLOW && high === BLUE) {
        return GREEN;
    }

    return BLACK;
}

function inside(cell) {
    return cell[0] >= 0 && cell[1] >= 0 && cell[0] < WIDTH && cell[1] < HEIGHT;
}

function index(cell) {
    return cell[1] * WIDTH + cell[0];
}

function snapshot(state) {
    return {
        level: state.level,
        stains: state.stains.slice(),
        die: { ...state.die },
        pos: [...state.pos],
        spent: state.spent,
        phase: state.phase,
        marks: state.marks.map((mark) => ({ at: [...mark.at], color: mark.color })),
        limit: state.limit,
    };
}

export function startLevel(levelIndex) {
    const levels = [
        {
            name: 'First Face',
            limit: 1,
            start: [0, 0],
            marks: [{ at: [1, 0], color: YELLOW }],
            stains: [],
        },
        {
            name: 'Return',
            limit: 5,
            start: [0, 0],
            marks: [{ at: [1, 0], color: GREEN }],
            stains: [],
        },
        {
            name: 'What You Leave',
            limit: 5,
            start: [0, 0],
            marks: [
                { at: [1, 0], color: GREEN },
                { at: [0, 0], color: YELLOW },
            ],
            stains: [],
        },
        {
            name: 'The Other Pair',
            limit: 6,
            start: [0, 0],
            marks: [{ at: [1, 1], color: ORANGE }],
            stains: [],
        },
        {
            name: 'White',
            limit: 6,
            start: [0, 0],
            marks: [
                { at: [1, 0], color: YELLOW },
                { at: [2, 0], color: EMPTY },
            ],
            stains: [{ at: [2, 0], color: BLACK }],
        },
    ];

    const level = levels[levelIndex];
    const stains = Array(WIDTH * HEIGHT).fill(EMPTY);

    for (const stain of level.stains) {
        stains[index(stain.at)] = stain.color;
    }

    return {
        level: levelIndex,
        stains,
        die: startDie(),
        pos: [...level.start],
        spent: 0,
        phase: 'play',
        marks: level.marks.map((mark) => ({ at: [...mark.at], color: mark.color })),
        limit: level.limit,
        name: level.name,
    };
}

function marksMet(state) {
    return state.marks.every((mark) => state.stains[index(mark.at)] === mark.color);
}

function legal(state) {
    return Object.keys(DIRS).filter((action) => {
        const [dx, dy] = DIRS[action];
        return inside([state.pos[0] + dx, state.pos[1] + dy]);
    });
}

export function apply(state, action) {
    const current = snapshot(state);

    if (current.phase !== 'play' || !(action in DIRS)) {
        return { state: current, events: { accepted: false, action } };
    }

    const from = [...current.pos];
    const [dx, dy] = DIRS[action];
    const to = [from[0] + dx, from[1] + dy];

    if (!inside(to)) {
        return { state: current, events: { accepted: false, action, from } };
    }

    const die = roll(current.die, action);
    const at = index(to);
    const before = current.stains[at];
    const after = mix(before, die.D);

    current.stains[at] = after;
    current.die = die;
    current.pos = to;
    current.spent += 1;

    if (marksMet(current)) {
        current.phase = 'won';
    } else if (current.spent >= current.limit || legal(current).length === 0) {
        current.phase = 'lost';
    }

    return {
        state: current,
        events: {
            accepted: true,
            action,
            from,
            to,
            before,
            after,
            face: die.D,
            cleared: die.D === WHITE && before !== EMPTY,
        },
    };
}

export function replay(levelIndex, path) {
    let state = startLevel(levelIndex);
    const frames = [{ state: snapshot(state), events: null }];

    for (const action of path) {
        const result = apply(state, action);
        state = result.state;
        frames.push({ state: snapshot(state), events: result.events });
    }

    return frames;
}

export const GREEN_PATH = ['E', 'S', 'W', 'N', 'E'];
export const GREEN_ALT = ['E', 'S', 'E', 'N', 'W'];
