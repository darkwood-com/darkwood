"""Validate local PDF proofs of the two actual closing slides; never publish."""
import hashlib
import json
import logging
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path
import pdfplumber
import pypdfium2 as pdfium
from pypdf import PdfReader

logging.getLogger('pdfminer.pdffont').setLevel(logging.ERROR)
root = Path('storage/app/catalogue')
slugs = ['symfony-ai-in-action', 'holding-the-core-security-engineering-through-darkwaar40-breach']
output = root / 'ending-proofs.pdf'
with tempfile.TemporaryDirectory(dir=root) as temporary:
    for index, slug in enumerate(slugs, 1):
        source = root / f'ending-{slug}.pdf'
        assert len(PdfReader(source).pages) == 1, slug
        shutil.copyfile(source, Path(temporary) / f'page-{index:03}.pdf')
    subprocess.run([sys.executable, 'bin/publication-merge.py', temporary, str(output), '2'], check=True)
reader = PdfReader(output)
links = [a.get_object() for a in reader.pages[1].get('/Annots', [])]
assert any(a.get('/A', {}).get('/URI') == 'https://darkwaar.com/games/01a16e69-3593-7df1-b460-e25c4912eae1' for a in links)
for annotation in links:
    rect = annotation.get('/Rect')
    if rect:
        assert 0 <= float(rect[0]) <= float(rect[2]) <= 1920
        assert 0 <= float(rect[1]) <= float(rect[3]) <= 1080
rows = []
with pdfplumber.open(output) as document:
    for index, page in enumerate(document.pages):
        assert (page.width, page.height) == (1920, 1080)
        footer = [char for char in page.chars if char['top'] > 970]
        assert any(char['x0'] < 600 for char in footer)
        assert any(char['x0'] > 1500 for char in footer)
        assert all('Verdana' in char['fontname'] and abs(char['size']-34) < .01 for char in footer)
        assert not [c for c in page.chars if c['x0'] < -1 or c['x1'] > 1921 or c['top'] < -1 or c['bottom'] > 1081]
        assert len(page.images) >= 2
        assert any(tuple(image['srcsize']) == (640, 640) for image in page.images)
        rows.append({'slug': slugs[index], 'pages': 1, 'images': len(page.images), 'image_sizes': [[img['srcsize'][0], img['srcsize'][1]] for img in page.images]})
document = pdfium.PdfDocument(output)
for index in range(len(document)):
    document[index].render(scale=.75).to_pil().save(root / f'ending-proof-{index+1:02}.png')
(root / 'pdf-validation.json').write_text(json.dumps({'file': str(output), 'pages': 2, 'width_pt': 1920, 'height_pt': 1080, 'sha256': hashlib.sha256(output.read_bytes()).hexdigest(), 'excerpts_only': True, 'presentations': rows}, indent=2)+'\n')
print('Two closing PDF proofs: one page each, embedded fonts, 16:9, images and footers preserved, no text outside page')
