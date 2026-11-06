"""Inspect the actual pilot export: exact format and both historical footers."""
import logging
import sys
import pdfplumber

logging.getLogger('pdfminer.pdffont').setLevel(logging.ERROR)
with pdfplumber.open(sys.argv[1]) as document:
    assert len(document.pages) == 20
    for index, page in enumerate(document.pages, 1):
        assert (page.width, page.height) == (1920, 1080), index
        footer = [char for char in page.chars if char['top'] > 970]
        assert any(char['x0'] < 600 for char in footer), f'{index}: left footer missing'
        assert any(char['x0'] > 1500 for char in footer), f'{index}: right footer missing'
        assert all('Verdana' in char['fontname'] and abs(char['size']-34) < 0.01 for char in footer), index
print('20 PDF pages: 1920×1080 pt; left/right footer Verdana 34 pt')
