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



