#!/usr/bin/env python3
import os
import re
from pathlib import Path

SOURCE_DIR = r"c:\Users\Sahar\Bureau\symfony\template_education_bootstrap"
DEST_DIR = r"c:\Users\Sahar\Bureau\PIWEB\education\templates"

# Category mapping based on file name patterns
CATEGORY_MAP = {
    'admin-': 'admin',
    'course-': 'course',
    'book-class': 'course',
    'course-added': 'course',
    'course-categories': 'course',
    'course-video-player': 'course',
    'instructor-': 'instructor',
    'student-': 'student',
    'shop-': 'shop',
    'shop.': 'shop',
    'cart': 'shop',
    'checkout': 'shop',
    'empty-cart': 'shop',
    'wishlist': 'shop',
    'blog-': 'blog',
    'event-': 'event',
    'sign-in': 'auth',
    'sign-up': 'auth',
    'forgot-password': 'auth',
    'help-center': 'help',
    'workshop-detail': 'event',
}

def get_category(filename):
    """Determine category based on filename"""
    for pattern, category in CATEGORY_MAP.items():
        if pattern in filename:
            return category
    return 'main'

def extract_body_content(html_content):
    """Extract content between <body> tags"""
    # Match <body ...> ... </body>
    body_match = re.search(r'<body[^>]*>(.*?)</body>', html_content, re.DOTALL | re.IGNORECASE)
    if body_match:
        body_content = body_match.group(1)
        # Remove the main tag if it wraps everything
        if re.match(r'^\s*<main[^>]*>.*</main>\s*$', body_content, re.DOTALL):
            main_match = re.search(r'<main[^>]*>(.*?)</main>', body_content, re.DOTALL | re.IGNORECASE)
            if main_match:
                body_content = main_match.group(1)
        return body_content.strip()
    return html_content

def extract_page_title(html_content, filename):
    """Extract page title from <title> tag"""
    title_match = re.search(r'<title[^>]*>([^<]+)</title>', html_content, re.IGNORECASE)
    if title_match:
        title = title_match.group(1).strip()
        # Remove common suffixes
        title = title.replace(' - Eduport', '').replace('Eduport -', '').replace('Eduport', '').strip()
        return title if title else filename.replace('-', ' ').title()
    return filename.replace('-', ' ').title()

def create_twig_template(html_content, filename):
    """Create Twig template from HTML content"""
    page_title = extract_page_title(html_content, filename)
    body_content = extract_body_content(html_content)
    
    twig_template = f"""{{%% extends 'base.html.twig' %%}}

{{%% block title %%}}{page_title}{{%% endblock %%}}

{{%% block body %%}}
{body_content}
{{%% endblock %%}}
"""
    return twig_template

def main():
    source_path = Path(SOURCE_DIR)
    dest_path = Path(DEST_DIR)
    
    if not source_path.exists():
        print(f"Error: Source directory not found: {SOURCE_DIR}")
        return
    
    # Get all HTML files
    html_files = list(source_path.glob('*.html'))
    if not html_files:
        print(f"No HTML files found in {SOURCE_DIR}")
        return
    
    print(f"Found {len(html_files)} HTML files to convert")
    print("=" * 60)
    
    converted_count = 0
    errors = []
    
    for html_file in sorted(html_files):
        try:
            filename = html_file.stem  # filename without extension
            category = get_category(filename)
            
            # Read HTML content
            with open(html_file, 'r', encoding='utf-8') as f:
                html_content = f.read()
            
            # Create Twig content
            twig_content = create_twig_template(html_content, filename)
            
            # Write Twig file
            category_dir = dest_path / category
            category_dir.mkdir(parents=True, exist_ok=True)
            
            twig_file = category_dir / f"{filename}.html.twig"
            with open(twig_file, 'w', encoding='utf-8') as f:
                f.write(twig_content)
            
            print(f"✓ {filename:40} → {category:15} [{twig_file.relative_to(dest_path)}]")
            converted_count += 1
            
        except Exception as e:
            error_msg = f"✗ {filename}: {str(e)}"
            print(error_msg)
            errors.append(error_msg)
    
    print("=" * 60)
    print(f"\nConversion complete! {converted_count} files converted successfully")
    
    if errors:
        print(f"\n{len(errors)} errors encountered:")
        for error in errors:
            print(f"  {error}")

if __name__ == "__main__":
    main()
