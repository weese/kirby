#!/bin/bash

for package in tests/*; do
    echo -e "\033[1mTesting package $package...\033[0m"

    echo -e "\033[4mold version\033[0m"
    git checkout 0ce91f22e361f2df3054877d71b2d62fb833d292 &> /dev/null
    start=$(date +%s%N)
    ./vendor/bin/phpunit --coverage-clover build/logs/clover.xml $package
    durationOld=$((($(date +%s%N) - $start)/1000000))

    echo -e "\n\033[4mdevelop\033[0m"
    git checkout develop &> /dev/null
    start=$(date +%s%N)
    ./vendor/bin/phpunit --coverage-clover build/logs/clover.xml $package
    durationDevelop=$((($(date +%s%N) - $start)/1000000))

    echo -e "\033[32;4mtime comparison:\033[0;32m $durationOld ms -> $durationDevelop ms\033[0m\n\n"
done
