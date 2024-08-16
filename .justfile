set windows-shell:=["pwsh.exe","-c"]

default:
    just --list

build-abstract:
    pdflatex -aux-directory=latex-aux srs.tex