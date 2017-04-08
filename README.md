[![Build Status](https://travis-ci.org/moorscode/wp-multi-object-cache.svg?branch=master)](https://travis-ci.org/moorscode/wp-multi-object-cache)

# wp-multi-object-cache
Configure per "group" what cache implementation should be used.

## Requirements
- Composer
- PHP 5.4

## You say "groups"?

In the WordPress cache layer, data is divided in groups.
This results in prefixes per keys, so keys will not overlap. It also provides the basis to support a per-group cache implementation.
 
The groups used in WordPress are the following:
- transient
- options
- users
- user_meta
- userlogins
- counts
- posts
- post_meta
- themes
- comment
- site-transient
- site-options
- non-persistent

Currently implemented services:
- Memcached
- Redis
- Predis
- PHP (non-persistent)

Upcoming services:
- Memcache
- _Create issue (or a PR) to send a request_

# Installation

1. Clone this repository to the `wp-content/mu-plugins` folder in your WordPress installation.
    1. Create the folder if it does not exist already.
1. Copy (or symlink) `object-cache.php` to the `wp-content` folder.
    1. If you have not used `wp-multi-object-cache` as folder name in the `mu-plugins` folder, update the `$base_path` variable in the `object-cache.php` to match the chosen directory name.
1. Create a configuration file at `config/object-cache.config.php`.
    1. An example configuration file has been provided in the `config` directory.
1. Run `composer install` in the repository directory.
    1. Install composer if needed, for instructions see https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx


## Example configuration
In the configuration file the `transient` and `site-transient` groups are stored in Memcached, `non-persistent` group (and aliases of this group) are stored in PHP, while the rest of the groups are stored in Redis.

It is very simple to configure a dedicated (Redis|Memcached|Memcache) service to hold all user-related, options or posts cache.

## Credits and thanks

Thanks to the amazing work done by https://github.com/Nyholm and https://github.com/aequasi setting up the PHP Cache initiative with PSR-6 and Simple Cache implementations for all major Cache methods.
 See https://github.com/php-cache/ for code and http://www.php-cache.com/ for more information.
 
### Redis
Configuration inspiration taken from the https://wordpress.org/plugins/redis-cache/ and https://github.com/ericmann/Redis-Object-Cache projects.
 
### Memcached
Configuration inspiration taken from the https://github.com/tollmanz/wordpress-pecl-memcached-object-cache project.