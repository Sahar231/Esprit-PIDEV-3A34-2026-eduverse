#!/bin/bash
# Regenerate Twig templates with proper asset handling

SRC="c:/Users/Sahar/Bureau/symfony/template_education_bootstrap"
DEST="c:/Users/Sahar/Bureau/PIWEB/education/templates"

# Process each HTML file
for html_file in $SRC/*.html; do
    filename=$(basename "$html_file" .html)
    
    # Determine category
    if [[ "$filename" == admin-* ]]; then
        category="admin"
    elif [[ "$filename" == course-* ]]; then
        category="course"
    elif [[ "$filename" == instructor-* ]]; then
        category="instructor"
    elif [[ "$filename" == student-* ]]; then
        category="student"
    elif [[ "$filename" == shop* ]]; then
        category="shop"
    elif [[ "$filename" == blog-* ]]; then
        category="blog"
    elif [[ "$filename" == event-* ]] || [[ "$filename" == workshop-* ]]; then
        category="event"
    elif [[ "$filename" == help-* ]]; then
        category="help"
    elif [[ "$filename" == sign-* ]] || [[ "$filename" == forgot-* ]]; then
        category="auth"
    else
        category="main"
    fi
    
    # Create Twig file with asset() function replacements
    (
        echo "{% extends 'base.html.twig' %}"
        echo ""
        echo "{% block title %}${filename//-/ }{% endblock %}"
        echo ""
        echo "{% block body %}"
        
        # Extract and fix body content
        sed -n '/<body/,/<\/body>/p' "$html_file" | \
        sed 's/<body[^>]*>//g' | \
        sed 's/<\/body>//g' | \
        sed 's/src="assets\/\([^"]*\)"/src="{{ asset("\1") }}"/g' | \
        sed 's/href="assets\/\([^"]*\)"/href="{{ asset("\1") }}"/g'
        
        echo "{% endblock %}"
    ) > "$DEST/$category/$filename.html.twig"
    
    echo "✓ $filename"
done

echo "Templates regenerated!"
