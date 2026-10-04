import gsap from 'gsap';
import { motions } from '../darkwaar20/motions';

let booted = false;

export function bindDeck() {
    if (booted) {
        return 'ok';
    }

    const root = document.querySelector('[x-ref="deckRoot"]');

    if (!root) {
        return 'wait';
    }

    if (!root.querySelector('[data-dw-motion]')) {
        return 'skip';
    }

    if (root.querySelectorAll('.slidewire-frame').length === 0) {
        return 'wait';
    }

    booted = true;
    let visit = null;
    let last = null;

    const read = () => {
        const frames = [...root.querySelectorAll('.slidewire-frame')];
        const found = frames.findIndex((frame) => frame.classList.contains('is-active'));
        const index = found >= 0 ? found : 0;
        const step = frames[index]?.querySelectorAll('.slidewire-fragment-visible').length ?? 0;

        return { index, step };
    };

    const bareBoard = (index) => {
        const frame = root.querySelectorAll('.slidewire-frame')[index];

        if (!frame?.querySelector('[data-board], [data-patch]')) {
            return false;
        }

        return frame.querySelectorAll('.dw20-cell').length === 0;
    };

    const apply = (index, step, animate) => {
        const frame = root.querySelectorAll('.slidewire-frame')[index];
        const slide = frame?.querySelector('[data-dw-motion]');
        const factory = slide ? motions[slide.dataset.dwMotion] : null;
        const missing = bareBoard(index);

        if (!visit || visit.index !== index || visit.slide !== slide || missing) {
            visit?.ctx.revert();
            visit = null;

            if (!slide || !factory) {
                return;
            }

            let controller = null;
            const ctx = gsap.context(() => {
                controller = factory(slide);
            }, slide);
            visit = { index, slide, ctx, controller };
        }

        visit.ctx.add(() => {
            visit.controller?.show?.(step, animate);
        });
    };

    const sync = () => {
        const next = read();
        const unchanged = last && last.index === next.index && last.step === next.step;

        if (unchanged && !bareBoard(next.index)) {
            return;
        }

        const indexChanged = !last || last.index !== next.index;
        last = next;
        apply(next.index, next.step, indexChanged ? next.step === 0 : true);
    };

    const observer = new MutationObserver(sync);
    observer.observe(root, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['class'],
    });
    sync();

    return 'ok';
}
