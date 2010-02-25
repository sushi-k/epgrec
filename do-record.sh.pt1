#!/bin/sh
echo "CHANNEL : $CHANNEL"
echo "DURATION: $DURATION"
echo "OUTPUT  : $OUTPUT"
echo "TUNER : $TUNER"
echo "TYPE : $TYPE"
echo "MODE : $MODE"

RECORDER=/usr/local/bin/recpt1

$RECORDER --b25 --strip $CHANNEL $DURATION ${OUTPUT} >/dev/null
