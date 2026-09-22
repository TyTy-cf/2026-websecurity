#!/usr/bin/env bash
# Exercice — création de comptes en masse sur /inscription
# Usage : ./spam_register.sh [nombre] [url]
set -u

MAX=${1:-100}
BASE=${2:-https://localhost:8443}
JAR=$(mktemp)
RUN=$(date +%s)
created=0

trap 'rm -f "$JAR"' EXIT

for i in $(seq 1 "$MAX"); do
    # une session neuve toutes les 10 requêtes pour rester réaliste
    if (( i % 10 == 1 )); then
        : > "$JAR"
        page=$(curl -sk -c "$JAR" "$BASE/inscription")
        token=$(printf '%s' "$page" | grep -o 'name="registration\[_token\]"[^>]*value="[^"]*"' | sed 's/.*value="//;s/"$//')
    fi

    email="spam${RUN}.${i}@example.com"

    code=$(curl -sk -b "$JAR" -c "$JAR" -o /dev/null -w '%{http_code}' -X POST \
        -H "Origin: $BASE" \
        -H "Referer: $BASE/inscription" \
        --data-urlencode "registration[email]=$email" \
        --data-urlencode "registration[nickname]=Spam${RUN}_${i}" \
        --data-urlencode "registration[plainPassword][first]=Azerty123!" \
        --data-urlencode "registration[plainPassword][second]=Azerty123!" \
        --data-urlencode "registration[_token]=$token" \
        "$BASE/inscription")

    case "$code" in
        302) created=$((created + 1)); state="compte créé" ;;
        422) state="formulaire refusé (validation ou CSRF)" ;;
        429) state="BLOQUÉ (rate limit)" ;;
        *)   state="réponse inattendue" ;;
    esac

    printf '%3d  HTTP %s  %-40s %s\n' "$i" "$code" "$email" "$state"

    if [ "$code" = "429" ]; then
        echo "--- blocage après $created comptes créés ---"
        exit 0
    fi
done

echo "--- terminé : $created comptes créés sur $MAX tentatives, aucun blocage ---"