#!/usr/bin/env bash

echo "Type server you want to build with s prefix"
read SERVER

# Copy config files
cp tools/${SERVER}/db_config.php web/single/config/db_config.php
cp tools/${SERVER}/server_list_config.php web/single/config/server_list_config.php
cp tools/${SERVER}/source_config.php web/single/config/source_config.php
cp tools/${SERVER}/url_config.php web/single/config/url_config.php
cp tools/${SERVER}/server_config.php web/single/config/server_config.php

echo "Build done"