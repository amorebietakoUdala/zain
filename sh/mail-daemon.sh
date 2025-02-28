#!/bin/bash

NETFOLDER=/var/www/SF7/artzain

sudo -u informatika -s `php $NETFOLDER/bin/console app:mail-daemon &>> $NETFOLDER/var/log/mail-daemon.log`


