// Executed in the browser: audit painted computed styles, not source hex strings.
function auditDarkwoodPalette(root) {
    const allowed = new Set(['rgb(0, 0, 0)', 'rgb(255, 255, 255)', 'rgb(53, 121, 246)']);
    const violations = [];
    const colors = new Set();
    const codeColors = new Set();
    let checked = 0;
    const identify = node => `${node.tagName.toLowerCase()}.${node.getAttribute('class') || ''}`;
    const checkColor = (node, pseudo, property, value) => {
        // Fully transparent means no paint. Partial opacity is never accepted.
        if (['none', 'auto', 'rgba(0, 0, 0, 0)'].includes(value)) return;
        if (pseudo !== '::selection' && node.closest('.dw-original-code-window, pre.phiki, pre.slidewire-code')) {
            codeColors.add(value);
            return;
        }
        colors.add(value);
        if (!allowed.has(value)) violations.push({ element: identify(node), pseudo, property, value });
    };
    for (const node of [root, ...root.querySelectorAll('*')]) {
        const normal = getComputedStyle(node);
        if (!node.getClientRects().length || normal.display === 'none' || normal.visibility === 'hidden') continue;
        checked++;
        for (const pseudo of [null, '::before', '::after', ...(node.tagName === 'LI' ? ['::marker'] : []), '::selection']) {
            const style = getComputedStyle(node, pseudo);
            if (['::before', '::after'].includes(pseudo) && ['none', 'normal'].includes(style.content)) continue;
            const properties = ['color', 'backgroundColor', 'textDecorationColor', 'caretColor', 'webkitTextFillColor', 'fill', 'stroke', 'stopColor', 'floodColor', 'lightingColor'];
            if (parseFloat(style.webkitTextStrokeWidth) > 0) properties.push('webkitTextStrokeColor');
            if (style.outlineStyle !== 'none' && parseFloat(style.outlineWidth) > 0) properties.push('outlineColor');
            for (const edge of ['Top', 'Right', 'Bottom', 'Left']) {
                if (style[`border${edge}Style`] !== 'none' && parseFloat(style[`border${edge}Width`]) > 0) properties.push(`border${edge}Color`);
            }
            for (const property of properties) if (style[property]) checkColor(node, pseudo, property, style[property]);
            for (const property of ['backgroundImage', 'boxShadow', 'textShadow', 'backdropFilter']) {
                if (style[property] && style[property] !== 'none') violations.push({ element: identify(node), pseudo, property, value: style[property] });
            }
            if (pseudo === '::selection') continue;
            if (style.opacity !== '1') violations.push({ element: identify(node), pseudo, property: 'opacity', value: style.opacity });
            if (style.filter !== 'none' && !(node.matches('img.dw-original-logo') && /tricolor\.svg#darkwood-(tricolor|monochrome)/.test(style.filter))) {
                violations.push({ element: identify(node), pseudo, property: 'filter', value: style.filter });
            }
        }
    }
    return { checked, colors: [...colors], codeColors: [...codeColors], violations };
}

module.exports = { auditDarkwoodPalette };
