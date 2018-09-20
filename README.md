Install
=======

Install Apache & PHP:

```
$ sudo apt-get install apache2 php libapache2-mod-php
```

Put the "ceu-site" directory under the server root tree:

```
$ cd /var/www/html/
$ ln -s <...>/ceu-site/ ceu     # should be writeable by the server user
```

Create "tmp" directory and put "timeout" under "ceu-site":

```
$ cd <...>/ceu-site
$ mkdir tmp/                    # should be writeable by the server user
$ ln -s /usr/bin/timeout
```


Index.html
==========

Serves as a landing page featuring a major call to action to the online
interative tutorial for the language (refer to Try.php section of this
document, in order to find out more information about it).

This page also gives a brief explanation of the language, sided with a
small piece of code that highlights major features of Céu.

Bootstrap 4 was used to make the layout of the page.

Try.php
=======

The online interactive tutorial.

There is a simple **header** used to inform the user he is using the
online IDE.

The **footer** presents links where the user can find further
information about the language, such as instructions on how to download
and use a virtual machine.

The body of the page, features four sections:

-   **Lesson** panel: displays information relevant to the lesson the
    user is currently doing. The text makes use of Bootstrap styles to
    highlight interesting elements being presented. *Commands* are
    surrounded by the *code* tag; importante concepts are highlighted
    with *label* classes.
-   **Results** panel: displays the results of the code
    **compilation** and **execution**.
-   **Code** panel: where the user should enter Céu source code
-   **Input** panel: if necessary, where input can be given to the
    program.

Javascript
----------

Some javascript is executed on the page itself, upon DOM initialization.
Helper functions are present in the *try.js* file. Further explanation
regarding the code can be found in the source files.

Run.php
=======

The file which receives, compiles and executes Céu code. It expects to
receive the following data:

-   **samples**: the **id** for the current lesson
-   **go**: Run! (it checks if this attribute is set)
-   **mode**: used to specify if the code should be executed, which
    happens when it is set to **run**. Otherwise, the code will only be
    compiled.
-   **dfa**: if set, a **static analysis** will be performed on the file
-   **input**: input for the program

Pandoc
=======
Some pages, such as manuals.html and tutorials.html, are generated using [pandoc](https://pandoc.org/MANUAL.html) 2.3.1, a Haskell library for converting from one markup format to another, and a command-line tool that uses this library. 

To update the content of a page ```NAME.html```, first download pandoc 2.3 ([download link](https://github.com/jgm/pandoc/releases/tag/2.3)).

Then, edit the corresponding markdown file located at ```md``` folder.

```
pandoc -s -o NAME.html --template=template.html md/NAME.md
```

The index.html is generated use the following command:
```
pandoc -s -o index.html md/index.md metadata.yaml --template=template.html --syntax-definition=ceu-syntax/ceu.xml --highlight-style ceu-syntax/my.theme
```

The ```metadata.yaml``` is necessary to include the gray top section of the site. The ```syntax-definition``` and ```highlight-style```, by the other hand, are necessary to highlight Céu code. The highlight do not cover the whole Céu syntax, but is useful to prevent use of the HTML tag ```bold``` to highlight code. 
