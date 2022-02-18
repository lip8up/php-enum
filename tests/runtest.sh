runDir=`pwd`
testsDir=`dirname $(readlink -f $0)`
projDir=`dirname $testsDir`

if test $runDir == $projDir; then
  tests="tests"
elif test $runDir == $testsDir; then
  tests="."
else
  echo "must run in project dir or tests dir"
  exit 1
fi

env="$projDir/.env"
envBack="$projDir/.env.back"
envTests="$projDir/.env.tests"

if test -e "$env" && test -e "$envTests"; then
  cp -f "$env" "$envBack"
  cp -f "$envTests" "$env"
fi

last="$tests/.runtestlast"

if test $# == 0; then
  if test -e "$last"; then
    file=`head -n 1 "$last"`
  else
    file="$tests"
  fi
elif test "$1" == "all"; then
  file="$tests"
else
  file="$1"
  echo "$1" > "$last"
fi

$tests/../vendor/bin/phpunit --testdox --color "$file"

if test -e "$env" && test -e "$envTests"; then
  mv "$envBack" "$env"
fi
