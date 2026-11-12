#!/bin/bash

# Build script for Sokoban Mobile Game
echo "Building Sokoban Mobile Game..."

# Check if Godot is available
if ! command -v godot &> /dev/null; then
    echo "Error: Godot not found. Please install Godot 4.3+ and add it to PATH"
    exit 1
fi

# Create build directory
mkdir -p builds

# Build for Android
echo "Building for Android..."
godot --headless --export-release "Android" builds/sokoban-mobile.apk

# Build for iOS (requires macOS and Xcode)
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo "Building for iOS..."
    godot --headless --export-release "iOS" builds/sokoban-mobile.ipa
fi

# Build for Windows
echo "Building for Windows..."
godot --headless --export-release "Windows Desktop" builds/sokoban-mobile.exe

# Build for macOS
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo "Building for macOS..."
    godot --headless --export-release "macOS" builds/sokoban-mobile.app
fi

echo "Build complete! Check the builds/ directory for output files."
