#!/usr/bin/env bash
# sonar-perfil-completo.sh
#
# Cria (ou reaproveita) um perfil de qualidade com TODAS as regras de uma
# linguagem ativas, com os parâmetros padrão, num servidor SonarQube local,
# usando apenas a Web API. Configuração "completa" do experimento.
#
# Verificado contra o código do SonarQube Community Build 26.9.0.129388
# (server/sonar-webserver-webapi/.../qualityprofile/ws/*Action.java):
#   POST api/qualityprofiles/create         name, language
#   POST api/qualityprofiles/activate_rules targetKey + filtros de api/rules/search
#                                           (languages, statuses, ...)
#   POST api/qualityprofiles/set_default    language, qualityProfile
#   POST api/qualityprofiles/add_project    language, qualityProfile, project
#   GET  api/rules/search                   languages, qprofile, activation, statuses
#
# Uso:
#   export SONAR_TOKEN=...            # token de usuário com permissão "Administer Quality Profiles"
#   ./sonar-perfil-completo.sh
#
# Variáveis (todas opcionais, exceto SONAR_TOKEN):
#   SONAR_HOST_URL      padrão http://localhost:9000
#   LANGUAGES           padrão "php web": cria o perfil completo de PHP e o do
#                       analisador HTML (linguagem "web"), que também analisa
#                       arquivos .php (decisão de 25/09/2026); use "php" para
#                       só as regras de PHP
#   PROFILE_NAME        padrão "Completo (todas as regras)"
#   INCLUDE_DEPRECATED  padrão 1: inclui regras DEPRECATED (ex.: php:S1311,
#                       complexidade de classe). Use 0 para só READY/BETA.
#   SET_DEFAULT         padrão 1: torna o perfil o padrão da linguagem
#   PROJECT_KEY         se definido, associa o perfil só a esse projeto
#                       (add_project), o que dispensa SET_DEFAULT=1
#
# O token nunca é gravado no script: vem só da variável de ambiente.
# Requer: bash, curl, e jq ou python3 (para ler o JSON das respostas).

set -euo pipefail

: "${SONAR_TOKEN:?defina SONAR_TOKEN com um token de usuário do SonarQube}"
SONAR_HOST_URL="${SONAR_HOST_URL:-http://localhost:9000}"
SONAR_HOST_URL="${SONAR_HOST_URL%/}"
LANGUAGES="${LANGUAGES:-php web}"
PROFILE_NAME="${PROFILE_NAME:-Completo (todas as regras)}"
INCLUDE_DEPRECATED="${INCLUDE_DEPRECATED:-1}"
SET_DEFAULT="${SET_DEFAULT:-1}"
PROJECT_KEY="${PROJECT_KEY:-}"

if [ "$INCLUDE_DEPRECATED" = "1" ]; then
  STATUSES="READY,BETA,DEPRECATED"
else
  STATUSES="READY,BETA"
fi

# Leitura de um campo JSON (expressão no estilo jq, sem o ponto inicial
# para o python). Ex.: json_get '.profile.key'
json_get() {
  local expr="$1"
  if command -v jq >/dev/null 2>&1; then
    jq -r "$expr // empty"
  elif command -v python3 >/dev/null 2>&1; then
    python3 -c '
import json, sys
expr = sys.argv[1].lstrip(".")
d = json.load(sys.stdin)
for part in expr.split("."):
    if part.endswith("[0]"):
        d = d.get(part[:-3], [])
        d = d[0] if d else None
    else:
        d = d.get(part) if isinstance(d, dict) else None
    if d is None:
        break
print("" if d is None else d)
' "$expr"
  else
    echo "erro: instale jq ou python3" >&2
    exit 1
  fi
}

api_get() {  # api_get <caminho> [--data-urlencode k=v ...]
  local path="$1"; shift
  curl -sS --fail-with-body -u "${SONAR_TOKEN}:" -G "${SONAR_HOST_URL}/${path}" "$@"
}

api_post() {  # api_post <caminho> [--data-urlencode k=v ...]
  local path="$1"; shift
  curl -sS --fail-with-body -u "${SONAR_TOKEN}:" -X POST "${SONAR_HOST_URL}/${path}" "$@"
}

# Espera o servidor ficar UP (útil logo após "docker run").
for _ in $(seq 1 60); do
  status="$(curl -sS "${SONAR_HOST_URL}/api/system/status" 2>/dev/null | json_get '.status' || true)"
  [ "$status" = "UP" ] && break
  sleep 5
done
[ "${status:-}" = "UP" ] || { echo "erro: servidor em ${SONAR_HOST_URL} não está UP" >&2; exit 1; }

server_version="$(api_get api/server/version)"
echo "SonarQube ${server_version} em ${SONAR_HOST_URL}"

for lang in $LANGUAGES; do
  echo
  echo "== Linguagem: ${lang} | perfil: \"${PROFILE_NAME}\" | status: ${STATUSES}"

  # 1. Reaproveita o perfil se já existir; senão cria.
  key="$(api_get api/qualityprofiles/search \
          --data-urlencode "language=${lang}" \
          --data-urlencode "qualityProfile=${PROFILE_NAME}" 2>/dev/null \
        | json_get '.profiles[0].key' || true)"
  if [ -z "$key" ]; then
    key="$(api_post api/qualityprofiles/create \
            --data-urlencode "language=${lang}" \
            --data-urlencode "name=${PROFILE_NAME}" \
          | json_get '.profile.key')"
    echo "perfil criado: ${key}"
  else
    echo "perfil já existia: ${key}"
  fi

  # 2. Ativa todas as regras da linguagem (parâmetros padrão).
  result="$(api_post api/qualityprofiles/activate_rules \
             --data-urlencode "targetKey=${key}" \
             --data-urlencode "languages=${lang}" \
             --data-urlencode "statuses=${STATUSES}")"
  echo "activate_rules: sucesso=$(echo "$result" | json_get '.succeeded') falha=$(echo "$result" | json_get '.failed')"

  # 3. Confere: quantas regras ativas e quantas ficaram de fora.
  total="$(api_get api/rules/search --data-urlencode "languages=${lang}" \
            --data-urlencode "statuses=${STATUSES}" --data-urlencode "ps=1" | json_get '.total')"
  ativas="$(api_get api/rules/search --data-urlencode "languages=${lang}" \
             --data-urlencode "statuses=${STATUSES}" --data-urlencode "qprofile=${key}" \
             --data-urlencode "activation=true" --data-urlencode "ps=1" | json_get '.total')"
  inativas="$(api_get api/rules/search --data-urlencode "languages=${lang}" \
               --data-urlencode "statuses=${STATUSES}" --data-urlencode "qprofile=${key}" \
               --data-urlencode "activation=false" --data-urlencode "ps=500" --data-urlencode "f=name")"
  n_inativas="$(echo "$inativas" | json_get '.total')"
  echo "regras da linguagem: ${total} | ativas no perfil: ${ativas} | inativas: ${n_inativas}"
  if [ "${n_inativas:-0}" != "0" ]; then
    echo "atenção: regras não ativadas:" >&2
    if command -v jq >/dev/null 2>&1; then
      echo "$inativas" | jq -r '.rules[] | "  \(.key)  \(.name)"' >&2
    else
      echo "$inativas" | python3 -c 'import json,sys; [print("  "+r["key"]+"  "+r.get("name","")) for r in json.load(sys.stdin)["rules"]]' >&2
    fi
  fi

  # 4. Associa o perfil: a um projeto específico ou como padrão da linguagem.
  if [ -n "$PROJECT_KEY" ]; then
    api_post api/qualityprofiles/add_project \
      --data-urlencode "language=${lang}" \
      --data-urlencode "qualityProfile=${PROFILE_NAME}" \
      --data-urlencode "project=${PROJECT_KEY}" >/dev/null
    echo "perfil associado ao projeto ${PROJECT_KEY}"
  fi
  if [ "$SET_DEFAULT" = "1" ]; then
    api_post api/qualityprofiles/set_default \
      --data-urlencode "language=${lang}" \
      --data-urlencode "qualityProfile=${PROFILE_NAME}" >/dev/null
    echo "perfil definido como padrão para ${lang}"
  fi

  # 5. Registro para o pacote de replicação: lista das regras ativas.
  out="perfil-completo-${lang}-regras-ativas.txt"
  api_get api/rules/search --data-urlencode "languages=${lang}" \
    --data-urlencode "qprofile=${key}" --data-urlencode "activation=true" \
    --data-urlencode "ps=500" --data-urlencode "f=name,severity,status" \
  | { if command -v jq >/dev/null 2>&1; then jq -r '.rules[] | [.key, .status, .name] | @tsv';
      else python3 -c 'import json,sys; [print(r["key"]+"\t"+r.get("status","")+"\t"+r.get("name","")) for r in json.load(sys.stdin)["rules"]]'; fi; } \
  | sort > "$out"
  echo "lista das regras ativas gravada em ${out}"

  # Backup do perfil em XML (reimportável via api/qualityprofiles/restore).
  api_get api/qualityprofiles/backup --data-urlencode "language=${lang}" \
    --data-urlencode "qualityProfile=${PROFILE_NAME}" > "perfil-completo-${lang}.xml"
  echo "backup do perfil gravado em perfil-completo-${lang}.xml"
done
