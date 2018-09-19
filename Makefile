all:
	pandoc -s -o index.html md/index.md metadata.yaml --template=template.html --syntax-definition=ceu-syntax/ceu.xml --highlight-style ceu-syntax/my.theme
	pandoc -s -o manuals.html --template=template.html md/manuals.md
	pandoc -s -o tutorials.html --template=template.html md/tutorials.md
	pandoc -s -o cib.html --template=template.html md/cib.md

upload:
	rsync -e ssh -avL . fsantanna@ceu-lang.org:site/

.PHONY: cib
