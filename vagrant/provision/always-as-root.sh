#!/usr/bin/env bash

#== Bash helpers ==

function info {
  echo " "
  echo "--> $1"
  echo " "
}

#== Provision script ==

info "Provision-script user: `whoami`"

info "Restart web-stack"
localedef ru_RU.UTF-8 -i ru_RU -f UTF-8
service php7.1-fpm restart
service nginx restart
# service mysql restart
# service elasticsearch restart
# service redis restart
service supervisor restart
