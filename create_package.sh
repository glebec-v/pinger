#!/usr/bin/env bash

# git
REPOSITORY_HOST="10.0.0.52"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PACKAGE_DIR="${SCRIPT_DIR}/pinger-dist"
source ./config.cfg

# РАБОЧАЯ ЧАСТЬ

# создание папки сборки
echo "creating build folder"
if [ -d "${PACKAGE_DIR}" ]; then
    rm -rf "${PACKAGE_DIR}"
fi
mkdir -pv "${PACKAGE_DIR}"
cd "${SCRIPT_DIR}"

# развертывание pinger
project_name=pinger
echo ""
cd "${PACKAGE_DIR}"
git clone "http://${REPOSITORY_HOST}/${project_name}"
cd "${PACKAGE_DIR}/${project_name}"
git checkout ${pinger_branch}
mv ./installer.sh ../

# установка php-пакетов
composer install

# генерация классов для автозагрузки
composer dump-autoload --optimize

# удаление лишнего
echo "Cleaning pinger-dist folder"
rm -rf "${PACKAGE_DIR}/${project_name}/.git"
rm -rf "${PACKAGE_DIR}/${project_name}/config.cfg"
rm -fv "${PACKAGE_DIR}/${project_name}/.gitignore"
rm -fv "${PACKAGE_DIR}/${project_name}/composer.json"
rm -fv "${PACKAGE_DIR}/${project_name}/composer.lock"
rm -fv "${PACKAGE_DIR}/${project_name}/create_package.sh"

####
# архивация проекта
cd "${SCRIPT_DIR}"
tar -czf pinger-dist.tar.gz pinger-dist

echo "pinger-dist successfully created, unpack archive on target server and run pinger-dist/install.sh"