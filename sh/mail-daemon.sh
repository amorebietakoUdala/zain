#!/bin/bash

NETFOLDER=/var/www/SF6/zain

sudo -u informatika -s `php $NETFOLDER/bin/console app:mail-daemon &>> $NETFOLDER/var/log/mail-daemon.log`


