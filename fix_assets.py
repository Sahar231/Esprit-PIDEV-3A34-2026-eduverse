#!/usr/bin/env python3
import os
import re
from pathlib import Path

TEMPLATES_DIR = r"c:\Users\Sahar\Bureau\PIWEB\education\templates"

# Patterns to replace - convert relative asset paths to Twig asset() function
replacements = [
    # src="assets/..." -> src="{{ asset('...') }}"
    (r'src=["\']assets/([^"\']+)["\']', r'src="{{ asset(\'\1\') }}"'),
    # href="assets/..." -> href="{{ asset('...') }}"
    (r'href=["\']assets/([^"\']+)["\']', r'href="{{ asset(\'\1\') }}"'),
    # url(assets/...) -> url({{ asset('...') }})
    (r'url\(["\']?assets/([^)"\']+)["\']?\)', r'url({{ asset(\'\1\') }})'),
]

def process_file(filepath):
    """Process a single Twig file and replace asset paths"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        for pattern, replacement in replacements:
            content = re.sub(pattern, replacement, content)
        
        if content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
        return False

def main():
    templates_path = Path(TEMPLATES_DIR)
    
    if not templates_path.exists():
        print(f"Error: Templates directory not found: {TEMPLATES_DIR}")
        return
    
    # Get all Twig files
    twig_files = list(templates_path.glob('**/*.html.twig'))
    
    if not twig_files:
        print(f"No Twig files found in {TEMPLATES_DIR}")
        return
    
    print(f"Processing {len(twig_files)} Twig templates...")
    print("=" * 60)
    
    modified_count = 0
    
    for twig_file in sorted(twig_files):
        if process_file(twig_file):
            relative_path = twig_file.relative_to(templates_path)
            print(f"✓ Fixed: {relative_path}")
            modified_count += 1
    
    print("=" * 60)
    print(f"\nModified {modified_count} files")

if __name__ == "__main__":
    main()
