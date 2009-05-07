#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh

########
# Backups AMMAX database on a daily basis
########

announce_script "AMMAX daily backup"

if [ "$#" -lt 2 ]
then
  sh $BASH_PATH/baseBackup.sh dump ammaxuser userammax AMMAX /var/backup 7
else
  user=$1
  pass=$2
  sh $BASH_PATH/baseBackup.sh dump $user $pass AMMAX /var/backup 7
fi
