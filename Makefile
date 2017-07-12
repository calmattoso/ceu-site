all:
	cd cib/ && pandoc index.md > index.html

upload:
	rsync -e ssh -avL . fsantanna@ceu-lang.org:site/

.PHONY: cib
