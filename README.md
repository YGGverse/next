# next

PHP 8 Server for [Nex Protocol](nex://piclog.blue/nex/info/specification.txt), based on the [nex-php](https://github.com/YGGverse/nex-php) library

## Install

* `git clone https://github.com/YGGverse/next.git`
* `cd next` - navigate into the server directory
* `composer update` - grab latest dependencies

## NEX

Optimal to serve static files

For security reasons, next server prevents any access to the hidden files (started with dot)

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
* `file` - index **file name** that server try to open in path requested, disabled by default
* `fail` - failure **file path** that contain template (e.g. `error.gmi`) for the error handler, `fail` text by default
* `list` - show content listing in the requested directory (when index file not found), `yes` by default
* `size` - limit request length in bytes, `1024` by default
* `dump` - dump queries or set blank to disable, default: `[{time}] [{code}] {host}:{port} {path}`
  * `{time}` - event time in `c` format
  * `{code}` - formal response code: `1` - found, `0` - not found
  * `{host}` - peer host
  * `{port}` - peer port
  * `{path}` - path requested
