# next

PHP 8 Server for [Nex Protocol](nex://piclog.blue/nex/info/specification.txt), based on the [nex-php](https://github.com/YGGverse/nex-php) library

## Install

* `git clone https://github.com/YGGverse/next.git`
* `cd next`
* `composer update`

## Start

``` bash
php src/nex.php\
    host=127.0.0.1\
    port=1900\
    path=/path/to/dir
```

### Options

* `host` - optional string, `127.0.0.1` by default
* `port` - optional int, `1900` by default
* `path` - required string, destination files location (public folder)