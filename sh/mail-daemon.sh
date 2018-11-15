#!/bin/bash

NETFOLDER=/var/www/zain

sudo -u informatika -s `php $NETFOLDER/bin/console app:mail-daemon &>> $NETFOLDER/var/logs/mail-daemon.log`


