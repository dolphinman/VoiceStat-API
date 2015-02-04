#!/bin/sh

SERVICE="$1"
RESULT=`pgrep -x ${SERVICE}`

if [ "${RESULT:-null}" = null ]; then
    echo "not running"
else
    echo "running"
fi

