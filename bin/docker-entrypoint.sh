#!/bin/bash

set -e

cd /twfy

cp conf/httpd.docker /etc/apache2/sites-available/000-default.conf

mkdir -p data/

sed -r \
    -e 's!^(.*"OPTION_TWFY_DB_HOST", *)"[^"]*"!'"\\1'mariadb'!" \
    -e 's!^(.*"OPTION_TWFY_DB_USER", *)"[^"]*"!'"\\1'twfy'!" \
    -e 's!^(.*"OPTION_TWFY_DB_PASS", *)"[^"]*"!'"\\1'password'!" \
    -e 's!^(.*"OPTION_TWFY_DB_NAME", *)"[^"]*"!'"\\1'twfy'!" \
    -e 's!^(.*"OPTION_TWFY_MEMCACHED_HOST", *)"[^"]*"!'"\\1'memcache'!" \
    -e 's!^(.*"BASEDIR", *)"[^"]*"!'"\\1'/twfy/www/docs'!" \
    -e 's!^(.*"DOMAIN", *)"[^"]*"!'"\\1'localhost'!" \
    -e 's!^(.*"COOKIEDOMAIN", *)"[^"]*"!'"\\1'localhost'!" \
    -e 's!^(.*"RAWDATA", *)"[^"]*"!'"\\1'/twfy/data/pwdata/'!" \
    -e 's!^(.*"PWMEMBERS", *)"[^"]*"!'"\\1'/twfy/data/parlparse/members/'!" \
    -e "s/array\('127.0.0.1'\)/array\('sentinel'\)/" \
  conf/general-example > conf/general

bin/deploy.bash

# The regular deploy script doesn't install dev things
php composer.phar install

/usr/sbin/apache2ctl -DFOREGROUND
