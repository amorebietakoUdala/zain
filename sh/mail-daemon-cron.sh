#!/bin/bash

NETFOLDER=/var/www/SF5/zain

sudo -u informatika -s `php $NETFOLDER/bin/console app:mail-daemon-cron &>> $NETFOLDER/var/log/mail-daemon-cron.log`


