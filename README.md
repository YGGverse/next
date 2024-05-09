# next

PHP 8 server for smallweb protocols

Based on [Ratchet](https://github.com/ratchetphp/Ratchet) asynchronous socket library

## Features

* Async socket
* Multi-host
* Multi-protocol:
  * [x] [NEX](https://nightfall.city/nex/info/specification.txt)
  * [ ] [Gemini](https://geminiprotocol.net)
* Multi-mode:
  * [x] Static file hosting
      * [x] filesystem navigation on directory request
        * [x] optional `gemfeed` file modification date
        * [x] unicode filenames support
        * [x] filter hidden context (started with dot)
        * [ ] sort order settings (currently dir first, asc)
      * [x] custom index file name
      * [x] custom failure template
      * [x] custom data directory location
  * [ ] KevaCoin file storage
  * [ ] Dynamic application
  * [ ] Reverse proxy
  * [ ] Stream server
* Connection event log
* Simple and flexible server configuration by CLI arguments

## Install

### Environment

``` bash
apt install git composer php-fpm php-mbstring
```

### Stable

Project under development, use [repository](#repository) version!

### Repository

* `git clone https://github.com/YGGverse/next.git`
* `cd next` - navigate into the project directory
* `composer update` - grab latest dependencies

## Launch

### Start

Create as many servers as wanted by providing different `type`, `host`, `port`, `type`, `mode` and other arguments!

* for security reasons, file server prevents any access to the hidden files (started with dot)
* also, clients can't access any data out the `root` path, that defined on server startup

#### Startup example

``` bash
php src/server.php host=127.0.0.1 port=1900 type=nex mode=fs root=/target/dir
```

* `host` and `port` - optional arguments, read [Arguments documentation](#arguments) for details!

#### Arguments

Optional arguments auto-defined by server protocol `type` selected

Some arguments also defined in [default.json](https://github.com/YGGverse/next/blob/main/default.json) - do not change it without understanding, use [CLI](#cli) instead!

##### CLI

Provide arguments in `key=value` format, separated by space

Children nodes dependent of parent arguments defined and would be skipped in other combinations!

Following list explains `key` dependencies and it `value` options (started with `=`)

* `host` - optional, default is `127.0.0.1` e.g. `localhost` connections only
* `port` - optional, default value depends of server `type` selected, for example `1900` for `nex` or `1965` for `gemini`
* `fail` - **absolute path** to the failure template (e.g. `/path/to/error.gmi`), disabled by default
* `dump` - `enable` or `disable` server debug feature, enabled by default
* `type` - required, server protocol, also auto-defines default `port`, supported options:
  * =`nex` - [NEX Protocol](https://nightfall.city/nex/info/specification.txt)
    * `mode` - server implementation variant, `fs` (filesystem) by default
      * =`fs` - static files hosting for the `root` location
        * `root` - **absolute path** to the public directory, where browser navigation starting from
        * `file` - index **file name** that server try to open on directory path requested, disabled by default
        * `list` - show content listing in the requested directory (when index `file` not found), enabled by default
        * `date` - show file modification date as the alt text in directory listing (useful for gemfeed), disabled by default

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
