# Taken from https://mg.readthedocs.io/latexmk.html
# Note: Use the `Perl` language mode to get the correct syntax highlighting
$pdf_mode = 1;        # tex -> pdf

$do-cd = 1; # Moves the parser into the directory itself. This avoids the mess with aux directories, etc.

@default_files = ('docs\srs.tex');