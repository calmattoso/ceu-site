#!/bin/sh

cp SAVE/timeout .
cp SAVE/lpeg.so .
cp SAVE/ceu .
echo "#!/home/fsantanna/lua-5.3.3/src/lua" > ceu.1
echo -n "--" >> ceu.1
cat ceu.1 ceu > ceu.2
mv ceu.2 ceu
chmod +x ceu
rm ceu.1
