import gsap from 'gsap';
import { reduced } from './reduced';

export function stepTimeline(build) {
    const timeline = gsap.timeline({ paused: true });
    build(timeline);
    let current = 0;

    return {
        timeline,
        go(step, animate) {
            const label = `step${step}`;

            if (!(label in timeline.labels)) {
                return;
            }

            if (!animate || reduced()) {
                timeline.seek(label);
            } else {
                timeline.tweenTo(label, { duration: 0.55, ease: 'power1.inOut' });
            }

            current = step;
        },
        current() {
            return current;
        },
    };
}

export function staggerCells(count = 63) {
    const cols = 7;
    const order = [];

    for (let i = 0; i < count; i += 1) {
        const x = i % cols;
        const y = Math.floor(i / cols);
        order.push({ i, d: Math.abs(x - 3) + Math.abs(y - 4), x, y });
    }

    order.sort((a, b) => a.d - b.d || a.y - b.y || a.x - b.x);
    const rank = new Array(count);
    order.forEach((item, position) => {
        rank[item.i] = position;
    });

    return {
        each: 0.012,
        from: 'start',
        amount: reduced() ? 0 : 0.45,
        grid: 'auto',
        index(index) {
            return rank[index] ?? index;
        },
    };
}

export function tweenColors(targets, vars, position) {
    return gsap.to(targets, {
        duration: reduced() ? 0 : 0.7,
        ease: 'power1.inOut',
        ...vars,
    }, position);
}
