all:
	pandoc -s -o out/index.html md/index.md metadata.yaml --template=template.html --syntax-definition=ceu-syntax/ceu.xml --highlight-style ceu-syntax/my.theme
	pandoc -s -o out/manuals.html --template=template.html md/manuals.md --metadata=title:"Céu - Manuals"
	pandoc -s -o out/tutorials.html --template=template.html md/tutorials.md --metadata=title:"Céu - Tutorials"
	pandoc -s -o out/cib.html --template=template.html md/cib.md --metadata=title:"Céu - CiB"

clean:
	rm -f out/index.html
	rm -f out/cib.html
	rm -f out/manuals.html
	rm -f out/template.html

upload:
	cd out && rsync -e ssh -avL . fsantanna@ceu-lang.org:site/

.PHONY: cib
