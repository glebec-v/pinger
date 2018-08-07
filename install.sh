#!/usr/bin/env bash

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo ""
echo "Copying pinger files"
cd ${script_dir}
cp -R pinger /home/www

## todo install to cron