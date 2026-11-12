#!/usr/bin/env python3
import os
from rembg import remove
from PIL import Image

def process_images(input_folder, output_folder):
    # Create output folder if it doesn't exist
    os.makedirs(output_folder, exist_ok=True)
    
    # Supported image extensions
    supported_formats = ('.jpg', '.jpeg', '.png', '.bmp', '.webp', '.tiff')
    
    # Process each image in the input folder
    for filename in os.listdir(input_folder):
        if filename.lower().endswith(supported_formats):
            input_path = os.path.join(input_folder, filename)
            
            # Change extension to .png for output (to preserve transparency)
            name_without_ext = os.path.splitext(filename)[0]
            output_filename = f"{name_without_ext}_no_bg.png"
            output_path = os.path.join(output_folder, output_filename)
            
            print(f"Processing: {filename}")
            
            try:
                # Open image and remove background
                with Image.open(input_path) as img:
                    output_img = remove(img)
                    output_img.save(output_path)
                    print(f"✓ Saved: {output_filename}")
            
            except Exception as e:
                print(f"✗ Error processing {filename}: {e}")

if __name__ == "__main__":
    input_folder = "images"
    output_folder = "images-removed-background"
    
    print(f"Removing backgrounds from images in '{input_folder}'...")
    print(f"Output will be saved to '{output_folder}'...")
    print("-" * 50)
    
    process_images(input_folder, output_folder)
    
    print("-" * 50)
    print("Background removal complete!")