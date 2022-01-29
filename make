#/usr/bin/env bash

# This is a wrapper for make, so you can use commands like composer, php and dc (docker compose)
# with any arguments and flags.
ARGS="${@:2}" make $1