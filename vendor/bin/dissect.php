#!/usr/bin/env sh
SRC_DIR="`pwd`"
cd "`dirname "$0"`"
cd "../jakubledl/dissect/bin"
BIN_TARGET="`pwd`/dissect.php"
cd "$SRC_DIR"
"$BIN_TARGET" "$@"
