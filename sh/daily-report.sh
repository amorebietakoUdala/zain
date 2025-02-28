#!/bin/bash

NETFOLDER=/var/www/SF7/artzain

sudo -u informatika -s `php $NETFOLDER/bin/console app:daily-report-command &>> $NETFOLDER/var/log/daily-report-command.log`


