#!/bin/sh
ROOT_DIR="$(cd -P "$(dirname $0)/../../../" && pwd)"

# Applies various changes to simplesamlphp, this should be ran when composer has installed it in vendor

cd $ROOT_DIR

# Add/override SimpleSamlPhp config
mkdir -p vendor/simplesamlphp/simplesamlphp/config/
cp app/config/simplesamlphp/config/* vendor/simplesamlphp/simplesamlphp/config/

# Add/override SimpleSamlPhp metadata
mkdir -p vendor/simplesamlphp/simplesamlphp/metadata/
cp app/config/simplesamlphp/metadata/* vendor/simplesamlphp/simplesamlphp/metadata/
