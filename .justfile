set windows-shell:=["pwsh.exe","-c"]

default:
    just --list

build-abstract:
    pdflatex -aux-directory=docs/latex-aux docs/srs.tex --output-directory=docs/

format-tex:
    latexindent -wd --silent -c docs/latex-aux ./docs/srs.tex

diff-srs:
    git show HEAD~:docs/srs.tex --format="" --no-patch > docs/old.tex;
    latexdiff docs/old.tex docs/srs.tex > docs/diff.tex
    pdflatex -aux-directory=docs/latex-aux diff.tex --output-directory=docs/

start-ldev-server:
    echo "Starting PHP dev server running at localhost:8008"
    php -S localhost:8008 -c . -t src

# TODO:
# Use `caddy [start|stop|adapt]` for the caddy server, and `php-cgi -b 127.0.0.1:9000 -c .\php.ini` in a background job (using https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.core/about/about_jobs?view=powershell-7.4) for a full prod-ish setup.

setup-composer:
    php -c . -t src -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -c . -t src -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php -c . -t src composer-setup.php
    php -c . -t src -r "unlink('composer-setup.php');"

do-composer +ccmd="list":
    php -c . composer.phar {{ccmd}}