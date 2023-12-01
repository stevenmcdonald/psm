#!/bin/bash

log1="log/auto_requests-"$(date +%s)

php auto_requests.php 2>&1 >$log1
