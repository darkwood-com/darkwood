export function reduced() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
