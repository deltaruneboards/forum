#!/bin/bash

set -e
mkdir -p logs
scp dumb:/home/logs/access_log.2* logs
ssh dumb "rm /home/logs/access_log.2*"
