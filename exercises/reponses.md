Exercice 1
Avant
HttpOnly : false Secure: false SameSite : 
Aprés 
HttpOnly : true Secure: true SameSite : Lax

Exercice 2 :
Type Stocké


Exercice 3 : 
Type : DOM-based

Exercice 4
1 :#ref=CarterDavis1
. Ce correctif peut-il être fait côté serveur (Symfony/Twig) ? Pourquoi ? 

Exo 5 
Tout les utilisateurs ont acces à tout les modification y compris les utilisateur non connecté

Exercice 6
1 Mécanisme de sécurité web standardisé
2 Utilisation composer require nelmio/security-bundle
3 Content-Security-Policy : Les paramètres de la page ont empêché l’application d’un style (style-src-elem) à l’adresse https://localhost:8443/build/vendors-node_modules_bootstrap_dist_css_bootstrap_css.css, car il enfreint la directive suivante : « style-src 'unsafe-inline' 'nonce-f401ff2cd7af3abcf96857c085475268' »


Exercice 7

Excercice 8
"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3OTAwNzcyMTgsImV4cCI6MTc5MDA4MDgxOCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiY2FydGVyLmRhdmlzMUBleGFtcGxlLmNvbSJ9.EovVaqjKkw6FQPIxIQqUck8x0SZecnpKl_c4IQZUsUtAKml-axxAjWjioW1mws2FM8zR0L0VQYVvM1_7TGJ9KlXw-m6Q5P9RKgs9gYlQJBiJrzcoVEXQ8B_m3TjEa_W1EbrwnXEgsDhPgy2mZVELVsjClGmNmROWjHpjS4V-GENTfCAwy5C0JGcFmkg9E94gtbJ-bysm8KDWqYJfgHuGcqrwdESQziBty9eed8xmN2sLmiBOUYbTYnIzAgXBIavXEVrIrSuZ-5lDDvns0j2rCQUkkIZQUuJw_7s2qS6U6a0qjWs1xeXohW5yWZz1Op-lT_znrD40PSfImok6WA-M-Q"

décodé : 
{
"iat": 1790077936,
"exp": 1790081536,
"roles": ["ROLE_USER"],
"username": "carter.davis1@example.com"
}


Exercice 12.2
1 : Directory public/topic/image-X.extention

Exercice 13
appuser@b12989c08faf:/var/www/html$ composer audit
Found 9 security vulnerability advisories affecting 4 packages:
+-------------------+----------------------------------------------------------------------------------+
| Package           | symfony/http-foundation                                                          |
| Severity          | medium                                                                           |
| Advisory ID       | PKSA-y6py-qpv1-h52p                                                              |
| CVE               | CVE-2026-48736                                                                   |
| Title             | CVE-2026-48736: IpUtils::PRIVATE_SUBNETS Omits IPv6 Transition Forms (6to4,      |
|                   | NAT64, Teredo, IPv4-compatible): SSRF Bypass in NoPrivateNetworkHttpClient       |
| URL               | https://symfony.com/cve-2026-48736                                               |
| Affected versions | >=6.4.0,<6.4.41|>=7.0.0,<7.1.0|>=7.1.0,<7.2.0|>=7.2.0,<7.3.0|>=7.3.0,<7.4.0|>=7. |
|                   | 4.0,<7.4.13|>=8.0.0,<8.0.13                                                      |
| Reported at       | 2026-05-26T08:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | symfony/routing                                                                  |
| Severity          | medium                                                                           |
| Advisory ID       | PKSA-bf7t-jnpz-492k                                                              |
| CVE               | CVE-2026-48784                                                                   |
| Title             | CVE-2026-48784: UrlGenerator Dot-Segment Encoding Skips Every Other Chained      |
|                   | `../` or `./` → Generated URL Collapses Off-Route Under RFC 3986 Normalization   |
| URL               | https://symfony.com/cve-2026-48784                                               |
| Affected versions | >=2.0.0,<3.0.0|>=3.0.0,<4.0.0|>=4.0.0,<5.0.0|>=5.0.0,<5.1.0|>=5.1.0,<5.2.0|>=5.2 |
|                   | .0,<5.3.0|>=5.3.0,<5.4.0|>=5.4.0,<5.4.53|>=6.0.0,<6.1.0|>=6.1.0,<6.2.0|>=6.2.0,< |
|                   | 6.3.0|>=6.3.0,<6.4.0|>=6.4.0,<6.4.41|>=7.0.0,<7.1.0|>=7.1.0,<7.2.0|>=7.2.0,<7.3. |
|                   | 0|>=7.3.0,<7.4.0|>=7.4.0,<7.4.13|>=8.0.0,<8.0.13                                 |
| Reported at       | 2026-05-26T08:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | symfony/security-http                                                            |
| Severity          | high                                                                             |
| Advisory ID       | PKSA-c28x-6bj5-8spx                                                              |
| CVE               | CVE-2026-48489                                                                   |
| Title             | CVE-2026-48489: Security Firewall Bypass via failure_forward Subrequest:         |
|                   | Unauthenticated Access to access_control-Protected GET Routes                    |
| URL               | https://symfony.com/cve-2026-48489                                               |
| Affected versions | >=2.0.0,<3.0.0|>=3.0.0,<4.0.0|>=4.0.0,<5.0.0|>=5.0.0,<5.1.0|>=5.1.0,<5.2.0|>=5.2 |
|                   | .0,<5.3.0|>=5.3.0,<5.4.0|>=5.4.0,<5.4.53|>=6.0.0,<6.1.0|>=6.1.0,<6.2.0|>=6.2.0,< |
|                   | 6.3.0|>=6.3.0,<6.4.0|>=6.4.0,<6.4.41|>=7.0.0,<7.1.0|>=7.1.0,<7.2.0|>=7.2.0,<7.3. |
|                   | 0|>=7.3.0,<7.4.0|>=7.4.0,<7.4.13|>=8.0.0,<8.0.13                                 |
| Reported at       | 2026-05-26T08:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | twig/twig                                                                        |
| Severity          | high                                                                             |
| Advisory ID       | PKSA-8zx5-v2nz-58pb                                                              |
| CVE               | CVE-2026-49981                                                                   |
| Title             | Twig: Sandbox filter, tag and function allow-list bypass when sandbox state      |
|                   | changes between renders for a cached `Template`                                  |
| URL               | https://github.com/advisories/GHSA-529h-vh3j-85hq                                |
| Affected versions | <=3.26.0                                                                         |
| Reported at       | 2026-07-01T18:55:49+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | twig/twig                                                                        |
| Severity          | medium                                                                           |
| Advisory ID       | PKSA-fbvq-z33h-r2np                                                              |
| CVE               | CVE-2026-48808                                                                   |
| Title             | Sandbox property allowlist bypass via the `column` filter under                  |
|                   | `SourcePolicyInterface`                                                          |
| URL               | https://symfony.com/blog/cve-2026-48808-sandbox-property-allowlist-bypass-via-th |
|                   | e-column-filter-under-sourcepolicyinterface                                      |
| Affected versions | >=1.0.0,<2.0.0|>=2.0.0,<3.0.0|>=3.0.0,<3.27.0                                    |
| Reported at       | 2026-05-27T15:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | twig/twig                                                                        |
| Severity          | low                                                                              |
| Advisory ID       | PKSA-g9zw-qxh8-pq8w                                                              |
| CVE               | CVE-2026-48805                                                                   |
| Title             | Sandbox state regression in deprecated internal wrappers in                      |
|                   | `src/Resources/core.php`                                                         |
| URL               | https://symfony.com/blog/cve-2026-48805-sandbox-state-regression-in-deprecated-i |
|                   | nternal-wrappers-in-src-resources-core-php                                       |
| Affected versions | >=1.0.0,<2.0.0|>=2.0.0,<3.0.0|>=3.0.0,<3.27.0                                    |
| Reported at       | 2026-05-27T15:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | twig/twig                                                                        |
| Severity          |                                                                                  |
| Advisory ID       | PKSA-yd6k-t2gh-1m43                                                              |
| CVE               | CVE-2026-46636                                                                   |
| Title             | Sandbox filter, tag and function allow-list bypass when sandbox state changes    |
|                   | between renders                                                                  |
| URL               | https://symfony.com/blog/cve-2026-46636-sandbox-filter-tag-and-function-allow-li |
|                   | st-bypass-when-sandbox-state-changes-between-renders                             |
| Affected versions | >=1.0.0,<2.0.0|>=2.0.0,<3.0.0|>=3.0.0,<3.27.0                                    |
| Reported at       | 2026-05-27T15:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | twig/twig                                                                        |
| Severity          | medium                                                                           |
| Advisory ID       | PKSA-1tmc-rt7x-12w6                                                              |
| CVE               | CVE-2026-48806                                                                   |
| Title             | Sandbox `__toString()` policy bypass via dynamic mapping keys                    |
| URL               | https://symfony.com/blog/cve-2026-48806-sandbox-tostring-policy-bypass-via-dynam |
|                   | ic-mapping-keys                                                                  |
| Affected versions | >=1.0.0,<2.0.0|>=2.0.0,<3.0.0|>=3.0.0,<3.27.0                                    |
| Reported at       | 2026-05-27T15:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | twig/twig                                                                        |
| Severity          | medium                                                                           |
| Advisory ID       | PKSA-xx6c-6d96-db2w                                                              |
| CVE               | CVE-2026-48807                                                                   |
| Title             | Sandbox `__toString()` policy bypass via `Traversable` in `join`/`replace` and   |
|                   | `in`/`not in` operators                                                          |
| URL               | https://symfony.com/blog/cve-2026-48807-sandbox-tostring-policy-bypass-via-trave |
|                   | rsable-in-join-replace-and-in-not-in-operators                                   |
| Affected versions | >=1.0.0,<2.0.0|>=2.0.0,<3.0.0|>=3.0.0,<3.27.0                                    |
| Reported at       | 2026-05-27T15:00:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
Found 2 abandoned packages:
+------------------------------------+----------------------------------------------------------------------------------+
| Abandoned Package                  | Suggested Replacement                                                            |
+------------------------------------+----------------------------------------------------------------------------------+
| sebastian/code-unit                | none                                                                             |
| sebastian/code-unit-reverse-lookup | none                                                                             |
+------------------------------------+----------------------------------------------------------------------------------+

Composer update pour corriger les vulnérabilité
composer update symfony/http-foundation symfony/routing symfony/security-http twig/twig
