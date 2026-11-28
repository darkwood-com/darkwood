const form = document.getElementById('email-login-confirm');
const proof = new URLSearchParams(window.location.hash.slice(1));
const names = ['user', 'expires', 'hash', 'hash_parameters', 'flow'];

if (names.every((name) => proof.get(name))) {
    for (const name of names) {
        form.elements.namedItem(name).value = proof.get(name);
    }
    form.querySelector('button').disabled = false;
}
if (window.location.hash) {
    window.history.replaceState(null, '', window.location.pathname);
}
