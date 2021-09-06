baseDir=`dirname $(readlink -f $0)`

runDir=`pwd`

if [ $baseDir != $runDir ]; then cd $baseDir; fi

./vendor/bin/phpunit --testdox --color  ${1:-tests}

if [ $baseDir != $runDir ]; then cd - > /dev/null; fi
