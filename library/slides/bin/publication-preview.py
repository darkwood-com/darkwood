"""Render the actual final PDF, not the pre-export browser screenshots."""
import sys
from pathlib import Path
import pypdfium2 as pdfium
from PIL import Image, ImageDraw

file = Path(sys.argv[1])
document = pdfium.PdfDocument(str(file))
directory = file.parent/'pdf-preview'
directory.mkdir(exist_ok=True)
sheet = Image.new('RGB', (4*368, ((len(document)+3)//4)*253), 'white')
for i, page in enumerate(document):
    image = page.render(scale=1440/page.get_width()).to_pil().convert('RGB')
    image.save(directory/f'page-{i+1:02}.png')
    tile = image.copy()
    tile.thumbnail((360, 225))
    x, y = (i % 4)*368, (i//4)*253
    sheet.paste(tile, (x, y))
    ImageDraw.Draw(sheet).text((x+8, y+229), str(i+1), fill='black')
sheet.save(directory/'contact-sheet.png')
print(directory/'contact-sheet.png')
