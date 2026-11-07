"""Merge fresh Chromium pages, strip volatile metadata, validate embedded fonts."""
import hashlib
import json
import sys
from pathlib import Path
from pypdf import PdfReader, PdfWriter, Transformation
from pypdf.generic import ByteStringObject, ArrayObject, RectangleObject, FloatObject, NameObject

directory, output, count = Path(sys.argv[1]), Path(sys.argv[2]), int(sys.argv[3])
pages = sorted(directory.glob('page-*.pdf'))
page_map_file = directory/'page-map.json'
page_map = json.loads(page_map_file.read_text()) if page_map_file.exists() else None
if page_map is not None:
    if len(page_map) != len(pages) or sorted(set(row['slide'] for row in page_map)) != list(range(1, count+1)):
        raise ValueError('Missing canonical slides in page map')
    if [row['slide'] for row in page_map] != sorted(row['slide'] for row in page_map):
        raise ValueError('Canonical slide order changed')
elif len(pages) != count:
    raise ValueError('Missing pages')
writer = PdfWriter()
for file in pages:
    reader = PdfReader(file)
    if len(reader.pages) != 1:
        raise ValueError('Expected exactly one page per frame')
    page = reader.pages[0]
    if abs(float(page.mediabox.width)-1080) > 1 or abs(float(page.mediabox.height)-607.5) > 1:
        raise ValueError('Unexpected PDF dimensions')
    if not page.extract_text().strip():
        raise ValueError('Missing searchable text')
    fonts = page['/Resources'].get('/Font', {})
    for reference in fonts.values():
        font = reference.get_object()
        if font.get('/Subtype') == '/Type3':
            # Chromium embeds CFF glyph outlines as Type3 CharProcs, rather than FontFile.
            if not font.get('/CharProcs') or not all(ref.get_object().get_data() for ref in font['/CharProcs'].values()):
                raise ValueError('Missing embedded Type3 glyph programs')
            continue
        descendants = font.get('/DescendantFonts', [font])
        for item in descendants:
            descriptor = item.get_object().get('/FontDescriptor')
            if not descriptor or not any(key in descriptor.get_object() for key in ['/FontFile', '/FontFile2', '/FontFile3']):
                raise ValueError('Unembedded font')
    # Scale uniformly to the canonical 1920×1080 point canvas.
    page.scale_by(1920 / float(page.mediabox.width))
    # Chromium rounds paper dimensions to printer units. Remove only the
    # sub-point blank paper excess, keeping uniform scale and exact font sizes.
    excess = float(page.mediabox.height) - 1080
    if abs(excess) > 2:
        raise ValueError('Unexpected paper rounding')
    page.add_transformation(Transformation().translate(ty=-excess))
    for annotation in page.get('/Annots', []):
        item = annotation.get_object()
        if '/Rect' in item:
            rect = item['/Rect']
            item[NameObject('/Rect')] = RectangleObject([rect[0], float(rect[1])-excess, rect[2], float(rect[3])-excess])
        if '/QuadPoints' in item:
            item[NameObject('/QuadPoints')] = ArrayObject([FloatObject(float(value)-(excess if i % 2 else 0)) for i, value in enumerate(item['/QuadPoints'])])
    page.mediabox = RectangleObject([0, 0, 1920, 1080])
    page.cropbox = RectangleObject([0, 0, 1920, 1080])
    writer.add_page(page)
writer.add_metadata({'/Producer': 'Slidewire canonical export'})
# Fixed document IDs; actual content identity is always the SHA-256 of final bytes.
writer._ID = ArrayObject([ByteStringObject(b'SlidewirePDFv001'), ByteStringObject(b'SlidewirePDFv001')])
with output.open('wb') as stream:
    writer.write(stream)
check = PdfReader(output)
if len(check.pages) != len(pages):
    raise ValueError('Merged page count mismatch')
result = {'pages': len(pages), 'canonical_slides': count, 'page_map_sha256':hashlib.sha256(page_map_file.read_bytes()).hexdigest() if page_map is not None else None, 'width_pt':1920, 'height_pt':1080, 'sha256':hashlib.sha256(output.read_bytes()).hexdigest(), 'bytes':output.stat().st_size}
(directory/'pdf-validation.json').write_text(json.dumps(result, indent=2)+'\n')
print(json.dumps(result))
