#!/usr/bin/env bash
# Exercice — absence de limite de débit sur l'API : GET /api/topic
# Usage : ./spam_api_topic.sh [nombre] [url]
set -u

MAX=${1:-100}
BASE=${2:-https://localhost:8443}
ok=0

for i in $(seq 1 "$MAX"); do
    read -r code time <<<"$(curl -sk -o /dev/null -w '%{http_code} %{time_total}' \
        -H "Accept: application/ld+json" \
        "$BASE/api/topic")"

    case "$code" in
        200) ok=$((ok + 1)); state="OK" ;;
        429) state="BLOQUÉ (rate limit)" ;;
        *)   state="réponse inattendue" ;;
    esac

    printf '%3d  HTTP %s  %6ss  %s\n' "$i" "$code" "$time" "$state"

    if [ "$code" = "429" ]; then
        echo "--- blocage après $ok requêtes acceptées ---"
        exit 0
    fi
done

echo "--- terminé : $ok/$MAX requêtes acceptées, aucun blocage ---"
