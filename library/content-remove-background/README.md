# Background Removal Tool

A Python-based tool for automatically removing backgrounds from images using the rembg library.

## Features

- Remove backgrounds from single images or entire folders
- Support for multiple image formats (JPG, JPEG, PNG, BMP, WebP, TIFF)
- Automated batch processing
- Preserves transparency with PNG output
- Easy-to-use command-line interface

## Installation

1. Install Python dependencies:
```bash
pip3 install rembg onnxruntime click
```

2. The AI model will be downloaded automatically on first use (176MB)

## Usage

### Automated Script (Recommended)

Run the provided Python script to process all images in the `images/` folder:

```bash
python3 remove_backgrounds.py
```

This will:
- Process all images in the `images/` directory
- Save results to `images-removed-background/` directory
- Add `_no_bg.png` suffix to output files

### Manual CLI Usage

Process single image:
```bash
rembg i input.jpg output.png
```

Process entire folder:
```bash
rembg p input_folder output_folder
```

Other commands:
```bash
rembg --help          # Show help
rembg d               # Download all models
rembg s               # Start HTTP server
```

## Project Structure

```
remove-background/
├── images/                          # Input images
│   ├── 1839.jpg
│   ├── 20999.jpg
│   └── gros-plan-couple-delassant.jpg
├── images-removed-background/       # Output images (auto-created)
│   ├── 1839_no_bg.png
│   ├── 20999_no_bg.png
│   └── gros-plan-couple-delassant_no_bg.png
├── remove_backgrounds.py           # Automation script
└── README.md                      # This file
```

## How It Works

The tool uses the rembg library which employs deep learning models (specifically U²-Net) to automatically detect and remove backgrounds from images. The default model (u2net) provides good results for most use cases.

## Supported Image Formats

- Input: JPG, JPEG, PNG, BMP, WebP, TIFF
- Output: PNG (preserves transparency)

## Requirements

- Python 3.7+
- rembg library
- PIL (Pillow)
- onnxruntime

## Notes

- First run will download the AI model (~176MB)
- Processing time depends on image size and complexity
- Output images are always saved as PNG to preserve transparency