# next

PHP 8 server for different protocols

Based on [Ratchet](https://github.com/ratchetphp/Ratchet) asynchronous socket library

## Features

* Async socket
* Multi-host
* Multi-protocol:
  * [x] [NEX](https://nightfall.city/nex/info/specification.txt)
  * [ ] [Gemini](https://geminiprotocol.net)
* Connection event log
* Optional:
  * file navigation on directory request
  * custom index name
  * custom failure template
* Simple and flexible server configuration by CLI arguments

## Install

* `git clone https://github.com/YGGverse/next.git`
* `cd next` - navigate into the project directory
* `composer update` - grab latest dependencies

## Launch

### Start

Create as many servers as wanted by providing different `type`, `host`, `port` and other arguments!

* for security reasons, `next` server prevents any access to the hidden files (started with dot)
* also, clients can't access any data out the `root` path, that defined on server startup

Startup example:

``` bash
php src/server.php type=nex host=127.0.0.1 port=1900 root=/target/dir
```

* `host` and `port` - optional arguments, read [Arguments documentation](#arguments) for details!

#### Arguments

Default argument values depending of server protocol selected.

Some arguments also defined in [default.json](https://github.com/YGGverse/next/blob/main/default.json) file. Do not change this file without understanding, use CLI instead!

##### Required

* `type` - server protocol, also auto-defines default `port`, supported options:
  * `nex` - [NEX Protocol](https://nightfall.city/nex/info/specification.txt)
* `root` - **absolute path** to the public directory, where browser navigation starting from

##### Optional

* `host` - default is `127.0.0.1` e.g. `localhost` connections only
* `port` - default value depends of server `type` selected, for example `1900` for `nex` or `1965` for `gemini`
* `file` - index **file name** that server try to open on directory path requested, disabled by default
* `list` - show content listing in the requested directory (when index `file` not found), enabled by default
* `time` - show file modification time as the alt text in directory listing (useful for gemfeed), disabled by default
* `fail` - **absolute path** to the failure template (e.g. `/path/to/error.gmi`), disabled by default
* `dump` - `enable` or `disable` server debug feature, enabled by default

### Autostart

#### systemd

Following example mean you have `next` server installed into home directory of `next` user (`useradd -m next`)

``` next.service
# /etc/systemd/system/next.service

[Unit]
After=network.target

[Service]
Type=simple
User=next
Group=next
ExecStart=/usr/bin/php /home/next/next/src/server.php type=nex root=/home/next/public
StandardOutput=file:/home/next/debug.log
StandardError=file:/home/next/error.log
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

* `systemctl daemon-reload` - reload systemd configuration
* `systemctl enable next` - enable service on system startup
* `systemctl start next` - start server
