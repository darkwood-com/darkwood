const fs = require('node:fs');
const path = require('node:path');
function buildDesignTokens(root = process.cwd()) {
    const css = fs.readFileSync(path.join(root, 'resources/css/themes/darkwood.css'), 'utf8');
    const hex = css.match(/--dw-blue:\s*#([0-9a-f]{6})\s*;/i)?.[1];
    if (!hex) throw Error('Missing canonical --dw-blue token');
    const rgb = [0,2,4].map(i => parseInt(hex.slice(i,i+2),16) / 255);
    const target = path.join(root, 'public/darkwood/filters/tricolor.svg');
    let svg = fs.readFileSync(target, 'utf8');
    ['R','G','B'].forEach((channel,i) => {
        svg = svg.replace(new RegExp(`(<feFunc${channel} type="discrete" tableValues=")0 [^" ]+ 1("/>)`), (_, prefix, suffix) => `${prefix}0 ${rgb[i]} 1${suffix}`);
    });
    fs.writeFileSync(target, svg);
}
module.exports = {buildDesignTokens};
