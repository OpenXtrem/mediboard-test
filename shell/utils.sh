#!/$HOME/sh

########
# Utilities
########

force_dir() 
{
  DIRPATH=$1
  if [ ! -d $DIRPATH ]
  then mkdir $DIRPATH
  fi
}

check_errs()
{
  RETURNCODE=$1
  FAILURETEXT=$2
  SUCCESSTEXT=$3
 
  cecho ">> status: \c" bold

  if [ "${RETURNCODE}" -ne "0" ]
  then
    cecho "ERROR # ${RETURNCODE} : ${FAILURETEXT}" red
    # as a bonus, make our script exit with the right error code.
    cecho "...Exiting..." bold
    exit ${RETURNCODE}
  fi

  cecho "${SUCCESSTEXT}"
}

announce_script()
{
  SCRIPTNAME=$1
  cecho "--- $SCRIPTNAME ($(date)) ---" bold
}

info_script()
{
  INFO=$1
  cecho ">> info: \c" bold
  cecho "${INFO}"
}

force_file()
{
  FILE=$1
  if [ ! -e $FILE ]
  then touch $FILE
  fi
}

package_lib()
{
  # $1 : library name
  # $2 : Url
  # $3 : Version
  
  echo "Retrieve dompdf from $2";
  svn co $2 tmp/$1;
  tar cfz tmp/$1-$3.tar.gz --directory ./tmp/ $1 --exclude=.svn;
  check_errs $? "Failed to package $1" "$1 packaged!";
  mv ./tmp/$1-$3.tar.gz libpkg/;
}

cecho ()
{
  # $1 = message
  # $2 = color
  message=$1                   
  color=${2:-"default"}        # Defaults to nothing, if not specified.

  case $color in
    bold    ) color="\033[1m"    ;;
    black   ) color="\033[0;30m" ;;
    red     ) color="\033[0;31m" ;;
    green   ) color="\033[0;32m" ;;
    yellow  ) color="\033[0;33m" ;;
    blue    ) color="\033[0;34m" ;;
    magenta ) color="\033[0;35m" ;;
    cyan    ) color="\033[0;36m" ;;
    white   ) color="\033[0;37m" ;;
    default ) color='' ;;
    *) 
      echo "Usage: second color param should be one of black, red, green, yellow, blue, magenta, cyan, white" 
      return 1
      ;;
  esac

  echo -e "$color$message"
  tput sgr0                    # Reset to normal.
}

