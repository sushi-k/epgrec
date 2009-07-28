#!/bin/sh

# サムネールを取る時間をFORMER_TIME+αだけずらします
# お好きな時間だけずらしてください

offset=`expr ${FORMER} + 2`

${FFMPEG} -i ${OUTPUT} -r 1 -s 160x90 -ss ${offset} -vframes 1 -f image2 ${THUMB}
