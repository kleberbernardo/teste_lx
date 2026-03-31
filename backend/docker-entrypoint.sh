#!/bin/bash
set -e

APP_DIR="/var/www/html/backend"

# ------------------------------------------------------------------
# 1. Instalar dependências Composer no volume (se necessário)
# ------------------------------------------------------------------
if [ ! -f "${APP_DIR}/vendor/autoload.php" ]; then
    echo "[entrypoint] Instalando dependências Composer..."
    cp /tmp/composer.json "${APP_DIR}/composer.json"
    cd "${APP_DIR}" && composer install --no-dev --prefer-dist
else
    echo "[entrypoint] Vendor já existe, pulando composer install."
fi

# ------------------------------------------------------------------
# 2. Baixar e extrair o Yii 1.1.29 (se necessário)
# ------------------------------------------------------------------
if [ ! -f "${APP_DIR}/framework/yii.php" ]; then
    echo "[entrypoint] Baixando Yii 1.1.29 (GitHub archive)..."
    # URL do source archive do GitHub — mais confiável que releases/download
    YII_URL="https://github.com/yiisoft/yii/archive/refs/tags/1.1.29.tar.gz"
    TMP_TAR="/tmp/yii.tar.gz"

    curl -fsSL --retry 3 -o "${TMP_TAR}" "${YII_URL}"

    echo "[entrypoint] Extraindo Yii..."
    tar -xzf "${TMP_TAR}" -C /tmp

    # GitHub archive extrai para yii-1.1.29/
    YII_EXTRACTED=$(find /tmp -maxdepth 1 -type d -name "yii-*" | head -1)
    if [ -d "${YII_EXTRACTED}/framework" ]; then
        mkdir -p "${APP_DIR}/framework"
        cp -r "${YII_EXTRACTED}/framework/." "${APP_DIR}/framework/"
        echo "[entrypoint] Yii 1.1.29 instalado em ${APP_DIR}/framework/"
    else
        echo "[entrypoint] ERRO: pasta framework não encontrada. Conteúdo extraído:"
        ls /tmp/
        exit 1
    fi

    rm -f "${TMP_TAR}"
    rm -rf "${YII_EXTRACTED}"
else
    echo "[entrypoint] Yii framework já existe, pulando download."
fi

# ------------------------------------------------------------------
# 3. Criar pasta de runtime do Yii (logs, cache)
# ------------------------------------------------------------------
mkdir -p "${APP_DIR}/protected/runtime"
mkdir -p "${APP_DIR}/protected/runtime/cache"
chmod -R 777 "${APP_DIR}/protected/runtime"

# ------------------------------------------------------------------
# 4. Atualizar db.php com variáveis de ambiente (se fornecidas)
# ------------------------------------------------------------------
if [ -n "${DB_HOST}" ]; then
    DB_CONFIG="${APP_DIR}/protected/config/db.php"
    sed -i "s|'host=127.0.0.1|'host=${DB_HOST}|g" "${DB_CONFIG}"
    sed -i "s|'username'         => 'root'|'username'         => '${DB_USER:-playlist}'|g" "${DB_CONFIG}"
    sed -i "s|'password'         => ''|'password'         => '${DB_PASS:-playlist123}'|g" "${DB_CONFIG}"
    echo "[entrypoint] db.php atualizado com DB_HOST=${DB_HOST}"
fi

echo "[entrypoint] Iniciando Apache..."
exec "$@"
