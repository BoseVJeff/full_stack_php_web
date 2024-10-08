This project exists to fulfill academic requirements.

Stack:

* Backend: PHP

* Frontend: HTML + CSS + Javascript

* Database: MySQL

Project structure:

Apart from the usual suspects (`.gitignore`, etc), the following files/folders are included in this project.

* `.vscode`: VS Code config for this project, including reccomended extensions and their configs

* `docs`: Documentation for this project, including a Software Requirements Specification (SRS).
    Authored in LaTeX, rendered as PDF

* `misc`: Material not directly related to project or its docs. Mostly contains source for certain materials that were used in the project.

* `src`: PHP source files. Note that this is where most of the PHP source files will be found.

* `.justfile`: Recipies for the [just](https://github.com/casey/just) command runner. In this project, it used to automate away repetitive tasks that need to be done as a part of developing this project.

* `Caddyfile`: Config for the Caddy server. At this stage, the project aims to run behind a [Caddy](https://github.com/caddyserver/caddy) server.

* `php.ini`: The PHP config. Note that at this stage, the project uses the dev version of this file. This will be converted to a production version later in the project's lifetime.