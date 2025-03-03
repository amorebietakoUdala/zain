#!/bin/bash

NETFOLDER=/var/www/SF7/artzain

php $NETFOLDER/bin/console app:mail-daemon &>> $NETFOLDER/var/log/mail-daemon.log


