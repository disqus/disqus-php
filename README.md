# Disqus PHP

Disqus API bindings for PHP. This fork is the [packagist version of `disqus/disqus-php`](https://packagist.org/packages/disqus/disqus-php).

Requires PHP 5.3.0 or newer!

## Installation

### Composer

If you don't have Composer [install](http://getcomposer.org/doc/00-intro.md#installation) it:

```bash
$ curl -s https://getcomposer.org/installer | php
```

Add `disqus/disqus-php` to `composer.json`:

```bash
$ composer require "disqus/disqus-php"
```

## Usage

[Usage examples](README.rst).

Thanks to Composer you don't have to use `require('disqusapi/disqusapi.php');`...

``` php
$disqus = new DisqusAPI($secret_key);
$disqus->trends->listThreads();
```
