# Céu in a Box

<img src="img/cib-big.png" with="200" align="right">

*Céu in a Box (CiB)* is a distribution of <a target="_blank" href="http://xubuntu.org/">Xubuntu</a> that
comes pre-installed with Céu.

The distribution contains the compiler together with bindings for the
environments
    <a target="_blank" href="http://github.com/fsantanna/ceu-arduino">Arduino</a>,
    <a target="_blank" href="http://github.com/fsantanna/ceu-libuv">libuv</a>, and
    <a target="_blank" href="http://github.com/fsantanna/ceu-sdl">SDL</a>.

CiB is distributed as a single `.ova` file to be used with
<a target="_blank" href="http://www.virtualbox.org/">VirtualBox</a>.

- <a target="_blank" href="http://www.ceu-lang.org/CiB-v0.20.ova">Download CiB-v0.20 (2.3Gb)</a>
<!--
- [Release History](https://github.com/fsantanna/ceu/blob/master/HISTORY)
-->

After downloading CiB, import it to VirtualBox:

<img src="img/cib-vb.png" style="width:100%;">

The usename and password for CiB are both `ceu`:

```
Username: ceu
Password: ceu
```

After logging in, open a terminal window and type the following commands to
check if everything is ok:

```
$ cd ceu/ceu/tst
$ ./run             # some examples might not compile because of the gcc version
```

The directory structure of CiB is as follows:

```
  - /home/ceu/              # home dir for the user "ceu"
    - arduino-1.8.1/        # distribution from http://arduino.cc
    - ceu/
      - ceu/                # clone of http://github.com/fsantanna/ceu/
      - ceu-arduino/        # clone of http://github.com/fsantanna/ceu-arduino/
      - ceu-libuv/          # clone of http://github.com/fsantanna/ceu-libuv/
      - ceu-sdl/            # clone of http://github.com/fsantanna/ceu-sdl/
        - ceu-sdl-leds/     # clone of http://github.com/fsantanna/ceu-sdl-leds/
        - ceu-sdl-birds/    # clone of http://github.com/fsantanna/ceu-sdl-birds/
        - ceu-sdl-storm/    # clone of http://github.com/fsantanna/ceu-sdl-storm/
```

## Keeping CiB up-to-date

```
$ cd ~/ceu/ceu/
$ git pull
$ make
$ sudo make install

$ cd ~/ceu/ceu-arduino/
$ git pull

$ cd ~/ceu/ceu-libuv/
$ git pull

$ cd ~/ceu/ceu-sdl/
$ git pull
```
