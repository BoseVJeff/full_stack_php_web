set windows-shell:=["pwsh.exe","-c"]

default:
    just --list

build-abstract:
    pdflatex -aux-directory=docs/latex-aux docs/srs.tex

format-tex:
    latexindent -wd --silent ./docs/srs.tex