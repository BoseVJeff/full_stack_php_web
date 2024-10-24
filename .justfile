set windows-shell:=["pwsh.exe","-c"]

export DB_URI:="mysql:host=localhost:3305;dbname=the_files_spot"
export DB_USER:="root"
export DB_PASS:="vineet"

phpunit_version:="11"

default:
    just --list

build-gantt:
    .\node_modules\.bin\mmdc -i .\misc\gantt.mermaid -c ./misc/mermaid-config.json --cssFile ./misc/mermaid-style.css -o .\assets\gantt.png -b white

build-abstract: build-gantt
    pdflatex -aux-directory=docs/latex-aux docs/srs.tex --output-directory=docs/

format-tex:
    latexindent -wd --silent -c docs/latex-aux ./docs/srs.tex

diff-srs:
    git show HEAD~:docs/srs.tex --format="" --no-patch > docs/old.tex;
    latexdiff docs/old.tex docs/srs.tex > docs/diff.tex
    pdflatex -aux-directory=docs/latex-aux docs/diff.tex --output-directory=docs/

start-ldev-server:
    php -S localhost:8008 -c . -t public

# TODO:
# Use `caddy [start|stop|adapt]` for the caddy server, and `php-cgi -b 127.0.0.1:9000 -c .\php.ini` in a background job (using https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.core/about/about_jobs?view=powershell-7.4) for a full prod-ish setup.

setup-composer:
    php -c . -t src -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -c . -t src -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php -c . -t src composer-setup.php
    php -c . -t src -r "unlink('composer-setup.php');"

setup-phpunit:
    Invoke-WebRequest 'https://phar.phpunit.de/phpunit-{{phpunit_version}}.phar' -OutFile 'phpunit.phar'

do-composer +ccmd="list":
    php -c . composer.phar {{ccmd}}

phpunit +ccmd="--version":
    php -c . phpunit.phar {{ccmd}}