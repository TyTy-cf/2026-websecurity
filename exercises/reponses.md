Exercice 6 — En-têtes HTTP de sécurité

1. c'est un entéte qui indique les contenu autorisé, il  limiter l'impact d'une faille XSS en bloquant les script js non autorisé

2. installer le elmio/security-bundle puis configurer  \config\packages\nelmio_security.yaml

3.  oui => content-security-policy :  default-src 'none'; base-uri 'self'; connect-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; img-src 'self' data:; script-src 'self' 'unsafe-inline' 'nonce-795691025cf098d507d214007eb69eea'; style-src 'self' 'unsafe-inline' 'nonce-7dc507d98bcc031f2eaff8140b42fa7a'; upgrade-insecure-requests

4.  Que se passe-t-il maintenant ? => plus aucune erreur 
    Regardez la console du navigateur : un message apparaît-il ? => oui
    Content-Security-Policy : Les paramètres de la page ont empêché l’exécution d’un gestionnaire d’évènement (script-src-attr) car il enfreint la directive suivante : « script-src 'self' 'unsafe-inline' 'nonce-6e16cad4c0f30847e2c2340527c3f26c' ». Envisagez l’utilisation d’une empreinte (« sha256-bhHHL3z2vDgxUt0W3dWQOrprscmda2Y5pLsLg4GF+pI= ») avec l’attribut « unsafe-hashes ».
    Source: alert(1) recherche
    Content-Security-Policy : Les paramètres de la page ont empêché le chargement d’une ressource (img-src) à l’adresse data:image/svg+xml,<svg xmlns=%22http://… car elle enfreint la directive suivante : « img-src 'self' https://picsum.photos https://fastly.picsum.photos » FaviconLoader.sys.mjs:232:20

5. 

6. Oui, on peux contourné le CSP et recréé les attaque

7. Non car on peut toujours modifier le front

8. 

9. l'iframe est bloqué 


Exercice 7 — Cross-Site Request Forgery (CSRF)

2. Quelle différence structurelle voyez-vous avec le lien Supprimer ? le formulaire et en méthode POST  alors que la suppression est en méthode  GET

4. le commentaire est supprimé

5. le commentaire est toujours supprimé, la route n'est pas du tout securisé 

6. un token csrf + un voter

9. Les attaque ne passe plus elles bloqué 

Exercice 8 — Integrity of JWT

1. {
       "iat": 1790077810,
       "exp": 1790081410,
       "roles": [
           "ROLE_USER"
       ],
       "username": "carter.davis1@example.com"
   }

2. Les données peuvent être dangereuse car le token révéle les roles et l'email de l'utilisateur.

3. Le token modifier est accepté 

4.

5. b64b0d856c8d7d95e17271a9ad3d276d => le token en cookie est chiffré

6. Quelle valeur proposez-vous pour remplacer ce qui y figure actuellement, qui ne soit ni une donnée personnelle, ni devinable, tout en restant propre à chaque utilisateur ? => un cryptedId 

7. Non le ROLE ne change rien, le ROLE est récupérer de la bdd et non du token 

Exercice 9 — Login Throttling

1. Rien ne se passe au bout de X tentative, la 20iéme est identique a la 1ière 

2. identique la réponse est toujours la même Identifiants invalides.

3.  Pourquoi l'absence de toute limite sur ces deux routes pose problème ? => on peut fcacilement attaquer le site ou un compte 
    Quel(s) type(s) d'attaque cela facilite-t-il ? => Brute force DDOS
    Combien de temps faudrait-il, en théorie, à un script pour tester 10 000 mots de passe sur un compte connu si rien ne le ralentit ? => instantané 

4.  https://symfony.com/bundles/SchebTwoFactorBundle/current/brute_force_protection.html

6.  Répétez les étapes 1 et 2. Après combien de tentatives échouées êtes-vous bloqué ? => 3
    Quel code HTTP et quel message obtenez-vous désormais ? Le comportement est-il le même sur les deux routes ?
    {
    "code": 401,
    "message": "Trop de tentatives de connexion échouées, veuillez réessayer dans 15 minutes."
    }

7. non si il a été bloqué pendant 15 minutes

9.  sur api_login et main
    login_throttling:
        max_attempts: 3
        interval: '15 minutes'

Exercice 10 — Rate Limiter

1. Aucun de bloquage même au bout de 100 tentative

2. Aucun de bloquage même au bout de 100 tentative

3. composer require symfony/rate-limiter (déjà présent), puis config/packages/rate_limiter.yaml

4. Limiteur `registration` (sliding_window, 5 / 15 minutes) consommé par IP dans SecurityController::register, après validation du formulaire :
   $limit = $registrationLimiter->create($request->getClientIp())->consume();
   if (!$limit->isAccepted()) { throw new HttpException(429, '...'); }


6. HTTP 429 sur les deux routes
   /api/topic : 30 requêtes acceptées, la 31e est bloquée.
   /inscription : 5 comptes créés, la 6e tentative est bloquée.


Exercice 11 — Account Enumeration

1. Que se passe-t-il ? => un mail part 

2. message => Adresse e-mail
   Cette adresse e-mail est déjà utilisée.

3. Sans jamais vous connecter, rien qu'en observant la réponse du formulaire, comment pourriez-vous déterminer si une adresse e-mail donnée possède déjà un compte sur Reddit-Ish ? avec le message d'erreur on sait qu'un compte existe déjà avec cette adresse email 

4.  Qu'apprendriez-vous en les soumettant une par une à ce formulaire ? => on peux savoir qui posséde un compte sur ce site
    En quoi est-ce dangereux pour les personnes concernées, même sans jamais obtenir leur mot de passe ? => ces adresse email peux être ciblé par du mail fishing pour récupérer des comptes ou des donnée 

5.  Le rate limiter mis en place à l'exercice 10 sur /inscription empêche-t-il ce scénario ? Justifiez votre réponse
Il ne le corrige pas a 100% mais le limite grandement car tester des centaines d'adresse mail prendra du temps éventuellement plusieurs jour mais l'attaquant pourrais utiliser plusieurs ip différente pour contourner le probléme 

6. #[UniqueEntity(fields: ['email'], message: 'Cette adresse e-mail est déjà utilisée.')]


Exercice 11 — Account Enumeration

6. #[UniqueEntity(fields: ['email'], message: 'Cette adresse e-mail est déjà utilisée.')] sur src/Entity/User.php.
   C'est lui qui refuse le formulaire avec un message spécifique quand l'adresse existe déjà.

Exercice 12.1 — Durcissement de php.ini

1. Dans l'entête de la réponse on a l'info x-powered-by : PHP/8.2.33

2. il peut identifier si la version utiliser est toujours maintenu et contient des faille connu 

3. Il optient les log de l'erreur donc une parti du code, le chemin vers les fichiers impacté par l'erreur ....

4. rien, un attaquant peux récupérer des information sans qu'on ne le sache

5. 

Exercice 12.2 — Upload de fichier

1.  Le champ annonce une image, mais que vérifie-t-il côté serveur avant d'enregistrer le fichier ? => on vérifie juste que c'est un document aucun paramétre cmd vérifier
    Où le fichier est-il déposé, et par qui est-il servi ensuite ? => le fichier est déposé dans public/uploads/topic et est donc accessible par tout le monde

3.  Que cela vous renvoie t'il ? => un terminal
    Pouvez vous essayer de voir le .env ? => oui : https://localhost:8443/uploads/topic/image-2.php?cmd=cat%20../../../.env

5. Une fois ce point atteint, qu'est-ce qu'un attaquant peut faire ? => il peux avoir accés au terminal et a donc tout les droit d'un terminal classique (suppresion, création d'accés, récupération de fichier et de donnée)

6.  Les mesures de l'exercice 12.1 (disable_functions, open_basedir) jouent-elles un rôle ici, et lequel ?
    - disable_functions => désactive des fonction tel que  shell_exec().
    - open_basedir => bloque les accés 

Exercice 12.3 — Images à URL prédictibles

1. Que remarquez-vous sur la façon dont elle est nommée ? => image-{le nombre d'image}

2. Que pouvez-vous en déduire ? => on peut visualiser toute les image en changeant le nombre dans le nom de l'image 

3.  Y arrivez-vous ? => oui 

6.  Après correctif, les URL sont-elles toujours prédictibles ? => non, trés difficilement
    Une image légitimement publiée s'affiche-t-elle toujours correctement ? => oui 

Exercice 13 — Audit des dépendances

1. composer audit
   Résultat de la commande => Found 9 security vulnerability advisories affecting 4 packages: et Found 2 abandoned packages:

2.  quel paquet est concerné ? => symfony/http-foundation
    quelles versions sont affectées? => >=6.4.0,<6.4.41|>=7.0.0,<7.1.0|>=7.1.0,<7.2.0|>=7.2.0,<7.3.0|>=7.3.0,<7.4.0|>=7. 4.0,<7.4.13|>=8.0.0,<8.0.13
    quelle version corrige le problème ? =>  7.4.13.
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

3. la version des fichier concerné aiinsi que leur dépendance

4.  Est-ce une vulnérabilité au même titre ? => potencielement, si une vulnérabilité est découverte elle ne sera pas corrigé
    Que faut-il en penser, et faut-il forcément agir ? => Il faut surveillé ce package et le changé dés que possible

5. ajouter des vérification bloquante pour le déploiement dans la CI

6. oui il faut réaliser des composer audit réguliérement

Exercice 14 — Politique de mot de passe

1. => oui il est utilisable 

3. régle de la cnil 
Pour un mot de passe de douze caractères ou plus, votre phrase doit contenir au moins :
    Un nombre
    Une majuscule
    Un signe de ponctuation ou un caractère spécial (dollar, dièse, ...)
    Une douzaine de mots

4. non le formulaire d'inscription est aussi concerné, donc les utilisateur ne pourront plus se connecter avec leur ancien mot de passe

5.  Une politique stricte suffit-elle à protéger un compte ? => non 
    Quelles autres mesures, déjà vues dans ce parcours, agissent sur le même risque — et laquelle protège même si le mot de passe est connu de l'attaquant ? double authentification permetrait de grandement protéger les compte des utilisateur.
