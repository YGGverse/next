# next

PHP 8 Server for [NEX Protocol](nex://piclog.blue/nex/info/specification.txt), based on the [nex-php](https://github.com/YGGverse/nex-php) library

## Install

* `git clone https://github.com/YGGverse/next.git`
* `cd next` - navigate into the server directory
* `composer update` - grab latest dependencies

## NEX

Optimal to serve static files

For security reasons, `next` server prevents any access to the hidden files (started with dot)

### Start

Create as many servers as wanted by providing different `host` and `port` using optional arguments

``` bash
php src/nex.php host=127.0.0.1 port=1900 path=/target/dir
```

#### Arguments

##### Required

* `path` - **absolute path** to the public directory

##### Optional

* `host` - `127.0.0.1` by default
* `port` - `1900` by default
* `file` - index **filename** that server try to open on directory path requested, disabled by default
* `list` - show content listing in the requested directory (when index file not found), `yes` by default
* `fail` - **filepath** that contain failure text or template (e.g. `error.gmi`), `fail` text by default
* `size` - limit request length in bytes, `1024` by default
* `dump` - dump queries or blank to disable, default: `[{time}] [{code}] {host}:{port} {path} {real} {size} bytes`
  * `{time}` - event time in `c` format
  * `{code}` - formal response code: `1` - found, `0` - not found
  * `{host}` - peer host
  * `{port}` - peer port
  * `{path}` - path requested
  * `{real}` - **realpath** returned
  * `{size}` - response size in bytes

### Autostart

Launch server as the `systemd` service

Following example mean you have `next` server installed into home directory of `next` user (`useradd -m next`)

1. `mkdir /home/next/public` - make sure you have created public folder
2. `sudo nano /etc/systemd/system/next.service` - create new service:

``` next.service
[Unit]
After=network.target

[Service]
Type=simple
User=next
Group=next
ExecStart=/usr/bin/php /home/next/next/src/nex.php path=/home/next/public
StandardOutput=file:/home/next/debug.log
StandardError=file:/home/next/error.log
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

3. `sudo systemctl daemon-reload` - reload systemd configuration
4. `sudo systemctl enable next` - enable `next` service on system startup
5. `sudo systemctl start next` - start `next` server
