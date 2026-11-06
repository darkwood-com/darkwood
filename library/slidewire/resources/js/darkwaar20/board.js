const COLS = 7;
const ROWS = 9;
const CHANNELS = 4;
const LEVELS = [0, 85, 170, 255];

const DIRS = [
    [1, 0],
    [-1, 0],
    [0, 1],
    [0, -1],
];

function index(x, y, channel) {
    return (y * COLS + x) * CHANNELS + channel;
}

function at(cells, x, y, channel) {
    return cells[index(x, y, channel)];
}

function neighbors(x, y) {
    const found = [];

    for (const [dx, dy] of DIRS) {
        const nx = x + dx;
        const ny = y + dy;

        if (nx < 0 || ny < 0 || nx >= COLS || ny >= ROWS) {
            continue;
        }

        found.push([nx, ny]);
    }

    return found;
}

function levelIndex(value) {
    if (value <= 42) {
        return 0;
    }

    if (value <= 127) {
        return 1;
    }

    if (value <= 212) {
        return 2;
    }

    return 3;
}

function snap(value) {
    return LEVELS[levelIndex(value)];
}

function nextChannel(cells, x, y, channel, condition) {
    const self = at(cells, x, y, channel);
    const around = neighbors(x, y);
    let neighborSum = 0;
    let support = 0;

    for (const [nx, ny] of around) {
        const value = at(cells, nx, ny, channel);
        neighborSum += value;

        if (Math.abs(value - self) <= 64) {
            support += 1;
        }
    }

    const neighborCount = around.length;

    if (condition === 'order') {
        const counts = [0, 0, 0, 0];
        counts[levelIndex(self)] += 1;

        for (const [nx, ny] of around) {
            counts[levelIndex(at(cells, nx, ny, channel))] += 1;
        }

        let bestIndex = 0;
        let bestCount = 0;

        for (let i = 0; i < 4; i += 1) {
            if (counts[i] > bestCount) {
                bestCount = counts[i];
                bestIndex = i;
            }
        }

        const weight = 1 + neighborCount * 3;
        const average = Math.trunc((self + neighborSum * 3) / weight);

        if (bestCount * 2 > neighborCount + 1) {
            return LEVELS[bestIndex];
        }

        return snap(average);
    }

    let average = self;

    if (neighborCount > 0) {
        average = Math.trunc((self + neighborSum) / (1 + neighborCount));
    }

    if (condition === 'life') {
        average = Math.min(255, average + (support >= 2 ? 48 : 16));
    } else if (condition === 'void') {
        average = Math.max(0, average - (support <= 1 ? 120 : 64));
    }

    return snap(average);
}

export function settle(cells, condition) {
    const next = new Array(cells.length);

    for (let y = 0; y < ROWS; y += 1) {
        for (let x = 0; x < COLS; x += 1) {
            for (let channel = 0; channel < CHANNELS; channel += 1) {
                next[index(x, y, channel)] = nextChannel(cells, x, y, channel, condition);
            }
        }
    }

    return next;
}

export function cellSum(cells, x, y) {
    let sum = 0;

    for (let channel = 0; channel < CHANNELS; channel += 1) {
        sum += at(cells, x, y, channel);
    }

    return sum;
}

export function kindAt(cells, x, y) {
    const sum = cellSum(cells, x, y);

    if (sum <= 340) {
        return 0;
    }

    if (sum >= 680) {
        return 2;
    }

    return 1;
}

export function classify(cells) {
    const terrain = new Array(COLS * ROWS);

    for (let y = 0; y < ROWS; y += 1) {
        for (let x = 0; x < COLS; x += 1) {
            terrain[y * COLS + x] = kindAt(cells, x, y);
        }
    }

    return terrain;
}

export function pickCore(cells, terrain) {
    let best = null;
    let bestScore = -1;

    for (let y = 0; y < ROWS; y += 1) {
        for (let x = 0; x < COLS; x += 1) {
            if (terrain[y * COLS + x] === 0) {
                continue;
            }

            const score = at(cells, x, y, 0) + at(cells, x, y, 1) + at(cells, x, y, 2) * 2;
            const wins = score > bestScore
                || (best && score === bestScore && (y < best[1] || (y === best[1] && x < best[0])));

            if (wins) {
                bestScore = score;
                best = [x, y];
            }
        }
    }

    return best;
}

function open(terrain, x, y) {
    if (x < 0 || y < 0 || x >= COLS || y >= ROWS) {
        return false;
    }

    const kind = terrain[y * COLS + x];

    return kind === 1 || kind === 2;
}

export function pickExit(terrain, core) {
    if (!core) {
        return null;
    }

    const seen = new Set([`${core[0]},${core[1]}`]);
    const queue = [core];
    let best = null;
    let bestDistance = -1;

    while (queue.length > 0) {
        const [x, y] = queue.shift();

        for (const [dx, dy] of DIRS) {
            const nx = x + dx;
            const ny = y + dy;
            const key = `${nx},${ny}`;

            if (seen.has(key) || !open(terrain, nx, ny)) {
                continue;
            }

            seen.add(key);
            queue.push([nx, ny]);

            if (terrain[ny * COLS + nx] !== 1 || (nx === core[0] && ny === core[1])) {
                continue;
            }

            const distance = Math.abs(nx - core[0]) + Math.abs(ny - core[1]);
            const wins = distance > bestDistance
                || (best && distance === bestDistance && (ny > best[1] || (ny === best[1] && nx > best[0])));

            if (wins) {
                bestDistance = distance;
                best = [nx, ny];
            }
        }
    }

    return best;
}

export function route(terrain, core, exit) {
    if (!core || !exit) {
        return [];
    }

    const parent = new Map();
    const queue = [core];
    parent.set(`${core[0]},${core[1]}`, null);

    while (queue.length > 0) {
        const current = queue.shift();

        if (current[0] === exit[0] && current[1] === exit[1]) {
            break;
        }

        for (const [dx, dy] of DIRS) {
            const next = [current[0] + dx, current[1] + dy];
            const key = `${next[0]},${next[1]}`;

            if (parent.has(key) || !open(terrain, next[0], next[1])) {
                continue;
            }

            parent.set(key, current);
            queue.push(next);
        }
    }

    if (!parent.has(`${exit[0]},${exit[1]}`)) {
        return [];
    }

    const path = [];
    let walk = exit;

    while (!(walk[0] === core[0] && walk[1] === core[1])) {
        path.unshift(walk);
        walk = parent.get(`${walk[0]},${walk[1]}`);
    }

    return path;
}

export function counts(terrain) {
    return terrain.reduce((total, kind) => {
        if (kind === 0) {
            total.void += 1;
        } else if (kind === 2) {
            total.energy += 1;
        } else {
            total.ground += 1;
        }

        return total;
    }, { void: 0, ground: 0, energy: 0 });
}

export function channels(cells, x, y) {
    return [0, 1, 2, 3].map((channel) => at(cells, x, y, channel));
}

export function mountGrid(host, cols = COLS, rows = ROWS, cell = 36) {
    host.replaceChildren();
    host.style.setProperty('--cell', `${cell}px`);
    host.style.gridTemplateColumns = `repeat(${cols}, var(--cell))`;
    const nodes = [];

    for (let y = 0; y < rows; y += 1) {
        for (let x = 0; x < cols; x += 1) {
            const node = document.createElement('div');
            node.className = 'dw20-cell';
            node.dataset.x = String(x);
            node.dataset.y = String(y);

            for (let i = 0; i < 4; i += 1) {
                const quadrant = document.createElement('i');
                quadrant.dataset.q = String(i);
                node.append(quadrant);
            }

            host.append(node);
            nodes.push(node);
        }
    }

    return nodes;
}

export function mountBoard(host, options = {}) {
    return mountGrid(host, COLS, ROWS, options.cell ?? 36);
}

export function paintNodes(nodes, lists, mode = 'noise', mixAmount = 1) {
    nodes.forEach((node, i) => {
        const values = lists[i];
        const quadrants = node.querySelectorAll('i');
        const sum = values.reduce((total, value) => total + value, 0);

        quadrants.forEach((quadrant, q) => {
            quadrant.dataset.darkwoodLevel = String(levelIndex(values[q]));
            quadrant.style.removeProperty('background-color');
            quadrant.style.opacity = '1';
        });

        node.style.removeProperty('background-color');
        node.classList.toggle('is-void', mode !== 'noise' && sum <= 340);
    });
}

export function paintBoard(nodes, cells, mode = 'noise', mixAmount = 1) {
    const lists = nodes.map((node) => channels(cells, Number(node.dataset.x), Number(node.dataset.y)));
    paintNodes(nodes, lists, mode, mixAmount);
}

export function crop(cells, x0, y0, width, height) {
    const lists = [];

    for (let y = 0; y < height; y += 1) {
        for (let x = 0; x < width; x += 1) {
            lists.push(channels(cells, x0 + x, y0 + y));
        }
    }

    return lists;
}

export function markBoard(nodes, { core = null, exit = null, player = null, reach = null, voidKeep = false } = {}) {
    nodes.forEach((node) => {
        const x = Number(node.dataset.x);
        const y = Number(node.dataset.y);
        node.classList.toggle('is-core', !!core && core[0] === x && core[1] === y);
        node.classList.toggle('is-exit', !!exit && exit[0] === x && exit[1] === y);
        node.classList.toggle('is-player', !!player && player[0] === x && player[1] === y);
        node.classList.toggle('is-reach', !!reach && reach.has(`${x},${y}`));
        node.classList.toggle('is-empty', voidKeep && node.classList.contains('is-void'));
    });
}

export function reachable(terrain, core) {
    const found = new Set();

    if (!core) {
        return found;
    }

    const queue = [core];
    found.add(`${core[0]},${core[1]}`);

    while (queue.length > 0) {
        const [x, y] = queue.shift();

        for (const [dx, dy] of DIRS) {
            const nx = x + dx;
            const ny = y + dy;
            const key = `${nx},${ny}`;

            if (found.has(key) || !open(terrain, nx, ny)) {
                continue;
            }

            found.add(key);
            queue.push([nx, ny]);
        }
    }

    return found;
}

export function cellOrder() {
    const items = [];

    for (let y = 0; y < ROWS; y += 1) {
        for (let x = 0; x < COLS; x += 1) {
            items.push({
                index: y * COLS + x,
                distance: Math.abs(x - 3) + Math.abs(y - 4),
                x,
                y,
            });
        }
    }

    items.sort((a, b) => a.distance - b.distance || a.y - b.y || a.x - b.x);

    return items.map((item) => item.index);
}

export const board = {
    COLS,
    ROWS,
    CHANNELS,
    LEVELS,
};
