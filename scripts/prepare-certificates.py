#!/usr/bin/env python3
"""Prepare the six public PDFs using the client's workbook ordering and labels.

Run with the bundled Python runtime (pypdf, reportlab, openpyxl).
Original PDFs are never overwritten. The watermark changes the public copies only.
"""
import argparse
import io
import json
import math
import re
import zipfile
from pathlib import Path

from openpyxl import load_workbook
from pypdf import PdfReader, PdfWriter
from reportlab.pdfgen.canvas import Canvas
from reportlab.pdfbase.pdfmetrics import stringWidth

FILES = {
    1: '01-mchs-license.pdf',
    2: '02-nostroy-construction.pdf',
    3: '03-nopriz-design.pdf',
    4: '04-laboratory-gost-17025.pdf',
    5: '05-cleanrooms-iso-14644.pdf',
    6: '06-quality-iso-9001.pdf',
}


def prepare(archive, output):
    output.mkdir(parents=True, exist_ok=True)
    manifest = []
    with zipfile.ZipFile(archive) as source:
        workbook_path = next(n for n in source.namelist() if n.endswith('.xlsx') and not n.startswith('__MACOSX/'))
        workbook = load_workbook(io.BytesIO(source.read(workbook_path)), data_only=True)
        rows = list(workbook.worksheets[0].values)
        if rows[0][2].strip() != 'Изменить наименование документа на:':
            raise ValueError('Unexpected workbook columns')
        documents = {int(row[0]): str(row[2]).strip() for row in rows[1:] if isinstance(row[0], (int, float)) and str(row[2]).strip().lower() != 'удалить'}
        if set(documents) != set(FILES):
            raise ValueError('Expected exactly six numbered documents')
        for number in sorted(documents):
            matches = [n for n in source.namelist() if n.lower().endswith('.pdf') and re.match(rf'^{number}\.\s', Path(n).name)]
            if len(matches) != 1:
                raise ValueError(f'Expected one PDF for document {number}')
            reader = PdfReader(io.BytesIO(source.read(matches[0])))
            if any(field.get('/FT') == '/Sig' for field in (reader.get_fields() or {}).values()):
                raise ValueError('Digitally signed source requires a separate publication workflow')
            writer = PdfWriter()
            writer.clone_document_from_reader(reader)
            for page in writer.pages:
                if page.rotation:
                    page.transfer_rotation_to_content()
                width, height = float(page.mediabox.width), float(page.mediabox.height)
                layer = io.BytesIO()
                canvas = Canvas(layer, pagesize=(width, height))
                canvas.saveState()
                # Repeating diagonal pattern, matching the previous certificate treatment.
                # Rebuild from source PDFs so no previous watermark is layered underneath.
                canvas.setFillColorRGB(0.20, 0.20, 0.20)
                canvas.setFillAlpha(0.38)
                canvas.translate(width / 2, height / 2)
                canvas.rotate(45)
                size = min(width, height) * 0.075
                canvas.setFont('Helvetica', size)
                step_x = stringWidth('biscorp.ru', 'Helvetica', size) + size * 0.75
                step_y = size * 2
                extent = math.hypot(width, height)
                row = 0
                y = -extent
                while y <= extent:
                    x = -extent + (step_x / 2 if row % 2 else 0)
                    while x <= extent:
                        canvas.drawString(x, y, 'biscorp.ru')
                        x += step_x
                    y += step_y
                    row += 1
                canvas.restoreState()
                canvas.save()
                page.merge_page(PdfReader(layer).pages[0])
            writer.add_metadata({'/Title': documents[number], '/Subject': 'biscorp.ru'})
            target = output / FILES[number]
            with target.open('wb') as stream:
                writer.write(stream)
            check = PdfReader(target)
            if len(check.pages) != len(reader.pages) or any((page.extract_text() or '').count('biscorp.ru') < 10 for page in check.pages):
                raise ValueError(f'Watermark validation failed for {target}')
            manifest.append({'number': number, 'label': documents[number], 'file': FILES[number], 'pages': len(check.pages)})
    return manifest


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('archive', type=Path)
    parser.add_argument('--output', type=Path, required=True)
    parser.add_argument('--manifest', type=Path, required=True)
    args = parser.parse_args()
    manifest = prepare(args.archive, args.output)
    args.manifest.write_text(json.dumps(manifest, ensure_ascii=False, indent=2))
    print(f'Prepared {len(manifest)} PDFs, {sum(item["pages"] for item in manifest)} pages')
