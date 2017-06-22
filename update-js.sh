# This file is part of the API Platform project.
#
# (c) Kévin Dunglas <dunglas@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.

#!/bin/sh

dest=Resources/public/swagger-ui/

yarn add --production --no-lockfile swagger-ui-dist
if [ -d $dest ]; then
  rm -Rf $dest
fi
mkdir -p $dest

cp node_modules/swagger-ui-dist/swagger-ui-bundle.js $dest
cp node_modules/swagger-ui-dist/swagger-ui-bundle.js.map $dest
cp node_modules/swagger-ui-dist/swagger-ui-standalone-preset.js $dest
cp node_modules/swagger-ui-dist/swagger-ui-standalone-preset.js.map $dest
cp node_modules/swagger-ui-dist/swagger-ui.css $dest
cp node_modules/swagger-ui-dist/swagger-ui.css.map $dest
rm -Rf package.json node_modules/
