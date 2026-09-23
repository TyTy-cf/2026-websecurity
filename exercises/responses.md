## Exercice 1

1. HttpOnly : false, secure: false, SameSite : null 
2. La config actuelle du projet met HttpOnly à false, secure à false et SameSite à null donc c'est identique
3. HttpOnly permet d'éviter une attaque XSS, SameSite permet d'évite une faille CSRF, Secure permet que le cookie soit utilisable et accessible que quand on est en https.
4. On obtient HttpOnly à true, Secure à true et SameSite à Lax

## Exercice 2

1. Le titre est devenu XSS
2. En navigation privé, le titre de la page est toujours XSS donc le script est socké dans la base de donnée
3. J'ai ajouté un script contenu dans exercice2.js dans le bloc commentaire et le résultat fait que ça stock en base le script ce qui affiche un message à la place de l'article.
4. Installation de Symfony Html Sanitizer, et ajout du "|sanitize_html" => "{{ comment.content|raw|sanitize_html }}"
5. Le XSS stocké

## Exercice 3

1. Je vois une liste de résultats.
2. Symfony/Twig inject via la variable app la query : {{ app.request.query.get('q') }}.
3. L'onglet ne change pas de titre, le script est affiché identique " <script>document.title = 'XSS'</script> ".
4. on peut ajouter un script js exécuté via la query
5. onfocus=alert(1) autofocus=x permet de bloquer la page
6. En envoyant un lien vers le site et la page de recherche contenant le script dans la query q
7. Ajouter des quotes autour de {{ app.request.query.get('q') }}.
8. XSS DOM-based

## Exercice 4

1. Ok
2. C'est avec un hash #CarterDavis1
3. La donnée est traitée côté client par du JS
4. Elle est insérée dans le div#ref-banner
5. La balise script n'est pas autorisé à jouer un script, elle ne passe pas
6. Utilise une img avec une src vide et utiliser le onerror pour éxecuter un script.
7. XSS Réfléchi car c'est depuis une URL
8. Envoyer un lien par mail à l'utilisateur en utilisant un label sur le lien afin de masquer l'url.
9. Utiliser textContent du JS pour sanitizer le script dans une nouvelle balise strong et l'insérer dans le DOM avec append.
10. La donnée ne passe jamais au serveur donc on ne peut pas la traiter de ce côté.

## Exercice 5

1. Ok
2. Oui
3. Ok la modification est passée
4. Ok
5. La modification est effective !
6. Ajout d'une règle sur le firewall : "{ path: ^/sujets/\d+/modifier$, roles: ROLE_USER }", création du TopicVoter.

## Exercice 6

1. Bloque l'exécution de scripts malveillants en interdisant les scripts inline, elle aide à empêcher le ClickJacking, limite la destination des requêtes.
2. Soit un listener, soit avec nelmio/security-bundle (solution choisie)
3. Voir le fichier nelmio_security.yaml
4. Elles sont toutes corrigées
5. Les images et les styles ne s'affichent plus donc il faut ajouter des règles dans la config pour autoriser les domaines et les base64 pour les photos, et mettre les feuilles de style en self
6. Oui, et on doit pouvoir contourner
7. Je pense qu'il faut avoir les deux pour maximiser la sécurité et les blocages et avoir des protections côté serveur et côté front.
8. Voir la config de nelmio_security.yaml
9. Voir config nelmio_security.yaml, ça corrige la faille du ClickJacking

## Exercice 7

1. Requête HTTP avec token passé en cookie et renvoyé par la réponse.
2. Le bouton supprimer est un lien <a> alors que le formulaire est submit par un button.
3. Ok
4. Le commentaire a disparu, la suppression a fonctionné.
5. Le commentaire disparait aussi, la route est public !
6. La route n'est pas sécurisée dans le firewall, il n'y a pas de Voter sur Comment pour valider que l'utilisateur est bien propriétaire, la route est en GET alors qu'elle devrait être en DELETE.
7. Envoi d'un mail avec le lien de suppression
8. Mise en place d'un formulaire post pour gérer le bouton et un crsf token en hidden, modifier la route en post, valider le token dans le contrôler, ajout d'un voter pour vérifier les droits, et sécurisé les route de suppression pour la rendre authentifiée.
9. La page piège ne supprime plus le commentaire.

## Exercice 8

1. Des headers, un payload et une signature
2. Le mail et les rôles
3. J'ai réussis à me connecter comme admin en changeant le username et le rôle, mais pas le role seul.
4. le mail peut permettre au pirate de faire du brut force sur d'autres sites
5. Le PHPSESSID est juste un identifiant pour retrouver des données côté serveur donc on ne peut rien apprendre en le décodant.
6. Passage du username avec un uid par utilisateur
7. Non. Le rôle n'a pas besoin de figurer dans le jeton pour que l'application fonctionne. Le firewall `api` recharge l'utilisateur depuis la base via le user provider (`app_user_provider`, entité `User`), et l'autorisation s'appuie sur `User::getRoles()` lu en base : le claim `roles` du token n'est jamais utilisé pour accorder ou refuser un accès. Il est donc redondant, et même trompeur (on croit contrôler ses droits en le modifiant, alors qu'il est ignoré). En prime, l'exposer révèle inutilement le niveau de privilège de l'utilisateur dans un payload décodable. On peut donc le retirer sans aucun impact fonctionnel.

## Exercice 9

1. Aucune différence, pas de blocage, pas de message différent.
2. Idem
3. Brute force jusqu'à trouver le mot de passe correspondant à l'email. Le mot de passe peut être trouvé en quelques secondes à quelques minutes : sans limitation, un script enchaîne les tentatives au rythme des requêtes HTTP (des dizaines à des centaines par seconde), et 10 000 essais est un volume négligeable. Le seul frein réel est le coût de hachage (`password_hashers: auto`, bcrypt/argon2id), mais il est parallélisable et ne change pas l'ordre de grandeur. C'est précisément ce qui justifie un login throttling.
4. La fonctionnalité est le login_throttling
5. Installation de symfony/rate_limiter, puis config sur les firewalls de 5 tentatives en 15 mn et listener pour transformer la 401 en 429.
6. 5 tentatives, code 429 avec le message Trop de tentatives de connexion échouées, veuillez réessayer dans 14 minutes.
7. Ok, l'utilisateur peut se connecter du premier coup
8. Une fois le seuil atteint il faut attendre le temps indiqué dans le message.
9. Bundle symfony/rate_limiter avec la config de login_throttling: max_attempts: 5 interval: '15 minutes', sur chaque firewall dans security.yaml et un handler pour renvoyer une 429 à la place de la 401 par défaut.

## Exercice 10

1. Aucune limite
2. Aucune limite aussi
3. OK
4. OK
5. Oui c'est tout bon.

## Exercice 11

1. Le compte se créé, je reçois le mail et je peux me connecter avec.
2. Le message est : "Cette adresse e-mail est déjà utilisée." Dans un div#registration_email_error1
3. On peut trouver l'email d'une personne ayant un compte sur le site en spammant le login avec un script
4. Une fois le mail trouvé, on peut spammer le script jusqu'à trouver le mot de passe et tester la combinaison sur d'autres sites.
5. Le rate limiter ne l'empêche pas totalement mais ralentit les tentatives.
6. C'est l'annotation UniqueEntity sur le champ email.
7. On affiche toujours le même message de succès quoi qu'il arrive par contre on envoit le mail que si le compte n'existe pas encore.
8. Même comportement dans les deux cas, redirection sur le login avec un message de succès. Par contre on peut se connecter sans passer par la validation du compte donc j'ai modifié la config pour bloquer le compte tant qu'il n'est pas activé.

## Exercice 12.1

1. x-powered-by PHP/8.2.33, visible depuis les headers de la réponse serveur.
2. Il peut connaitre les failles de sécurités connues de cette version et les exploiter.
3. On a la version de PHP et le type de Serveur, le code d'erreur.
4. Des logs avec 
5. Version de php.
6. Personnaliser l'affichage des erreurs via TwigBundle en ajoutant les twigs au projet.


## Exercice 12.2

1. Le champ annonce une image mais côté serveur, dans le code de départ, l'`UploaderService` faisait confiance à l'extension fournie par le client (`getClientOriginalExtension()`) pour nommer le fichier stocké : rien ne vérifiait le contenu réel. Le fichier est déposé dans `public/uploads/topic/` et servi ensuite directement par Caddy, dont le `php_fastcgi` passe à php-fpm tout `.php` situé sous `public/`.
2. En contournant la validation JS (JS désactivé, ou requête interceptée / rejouée), on envoie un `shell.php`. Comme le nom stocké reprend l'extension du client, il est écrit tel quel en `.php` dans `/uploads/topic/`.
3. Avec le payload `system($_GET['cmd'])`, appeler `/uploads/topic/image-2.php?cmd=whoami` exécute la commande et renvoie l'utilisateur php-fpm (`appuser`). On peut lire le `.env` en passant une commande qui `cat` le fichier (ex. `?cmd=cat+/var/www/html/.env`) : on récupère alors les identifiants de la base, l'`APP_SECRET` et le `MAILER_DSN`.
4. Requête déclenchant l'exécution : `GET https://localhost:8443/uploads/topic/image-2.php?cmd=id`. Elle renvoie `uid=...` prouvant l'exécution côté serveur. Pour prouver l'accès à une info connue du seul serveur, `?cmd=cat+/var/www/html/.env` renvoie l'`APP_SECRET`.
5. Impact : RCE complète avec les droits de php-fpm. L'attaquant peut lire le `.env` (identifiants MariaDB → accès total à la base, `APP_SECRET` → forger cookies/JWT, `MAILER_DSN` → envoyer des mails au nom du site), lire/écrire tout fichier accessible à `appuser`, déposer d'autres webshells pour la persistance, et pivoter vers les autres conteneurs (base, mailpit).
6. Correctif en défense en profondeur, sur plusieurs lignes :
   - **Front** : validation JS de l'extension (alerte + vide le champ) — confort seulement, contournable.
   - **Form type** : contrainte `Assert\File(extensions: ['jpg','jpeg','png'])`.
   - **Contrôleur / service** : `UploaderService` vérifie le **type MIME réel** (`getMimeType()` = finfo, pas l'en-tête client), **re-décode puis ré-encode l'image via GD** (les fichiers polyglottes image+PHP perdent leur charge utile : seuls les pixels survivent), et nomme le fichier `uniqid()` + hexa du nom d'origine avec une **extension déduite du MIME** (jamais du nom client, donc pas de `.php` ni de traversée de chemin).
   - **Serveur web** : Caddy renvoie **403** sur tout `/uploads/*.php` (et variantes `.phtml`, `.phar`...) : même un script déposé n'est jamais passé à php-fpm.
   - **Mesures 12.1** : `disable_functions = exec,passthru,shell_exec,system` neutralise directement les payloads — même un `.php` exécuté ne pourrait plus lancer de commande shell. `allow_url_include = Off` empêche l'inclusion distante et `cgi.fix_pathinfo = 0` bloque l'attaque `image.jpg/x.php`. `open_basedir` n'est en revanche pas configuré dans le `security.ini` actuel ; s'il l'était, il confinerait les accès disque et empêcherait la lecture du `.env` hors du webroot. C'est une couche complémentaire, pas la première.
7. Revalidation : après correctif, `/uploads/topic/image-2.php?cmd=id` renvoie **403** (jamais exécuté). Un fichier non-image est refusé au MIME, et un polyglotte JPEG valide est accepté mais son payload est **supprimé au ré-encodage** (la chaîne `PWNED` disparaît du fichier stocké). Une image légitime reste publiable et affichable : `/uploads/topic/<nom>.jpg` renvoie **200** en `image/jpeg`.

## Exercice 12.3

1. Après publication, l'image est servie à une URL du type `/uploads/topic/image-1.jpg`. Le nommage est séquentiel : `image-` suivi d'un numéro incrémental.
2. Le second sujet donne `/uploads/topic/image-2.jpg`. On en déduit que le numéro s'incrémente à chaque upload : les URL sont donc entièrement prédictibles et énumérables (image-1, image-2, image-3...).
3. Oui : en construisant moi-même `/uploads/topic/image-N.jpg` j'accède à l'image de n'importe quel sujet sans jamais passer par sa page.
4. C'est une énumération / IDOR au niveau des fichiers. Le contrôle d'accès de l'application (sujet privé, supprimé ou réservé) ne s'applique pas au fichier statique : tant que le fichier est sur le disque, quiconque devine l'URL le récupère. Un tiers peut ainsi aspirer toutes les images en incrémentant le compteur, y compris celles de sujets auxquels il n'a pas accès.
5. Correctif dans `UploaderService` : le fichier n'est plus nommé par un compteur mais par `uniqid()` + l'hexa du nom d'origine (ex. `6ab38a027aa18-6d61...jpg`). L'URL n'est plus ni devinable ni énumérable, il n'y a plus de suite à incrémenter.
6. Revalidation : les nouvelles URL sont de la forme `/uploads/topic/6ab38a027aa18-<hexa>.jpg`, sans séquence exploitable. Une image légitime s'affiche toujours normalement : le chemin réel est stocké en base à la publication puis rendu dans le template, l'utilisateur n'a donc jamais à deviner le nom.

> Remarque : `uniqid()` repose sur l'horodatage et n'est pas cryptographiquement imprévisible. Il supprime l'énumération séquentielle, mais pour une garantie plus forte on pourrait utiliser `bin2hex(random_bytes(16))`.

## Exercice 13

1. composer audit, le projet compte 9 vulnérabilitées connues affectant 4 bundles.
2. HttpFoundation, Routing, SecurityHttp, Twig.
3. On prend les versions non vulnérable et on met à jour composer : twig en 3.29, security-http en 7.4.19, http-foundation en 7.4.19, symfony routing en 7.4.18.
4. un paquet abandonné ne comporte pas forcément de vulnérabilité mais symfony le signale car un paquet non maintenu ne sera jamais corrigé en cas de faille.
5. Ajouter un job dans le ci-cd pour valider le composer audit et bloquer les déploiement s'il est invalide.
6. Notre projet peut contenir un bundle vulnérable dans qu'on le sâche et il faut suivre et mettre à jour régulièrement le projet, utiliser des librairies maintenues.

## Exercice 14

1. On peut mettre un mot de passe de 1 caractère. Le compte est créé et, une fois activé via le mail, il est utilisable pour se connecter.
2. La validation se fait uniquement dans `RegistrationType` (champ `plainPassword`, `mapped: false`) avec une seule règle : `NotBlank`. Il faut distinguer deux choses :
   - **Stockage** : c'est correct. `SecurityController::register()` hache le mot de passe avec `UserPasswordHasherInterface`, et `password_hashers: auto` choisit bcrypt ou argon2id. En base il n'y a qu'un hash salé. `UserRepository::upgradePassword()` permet de re-hacher automatiquement le mot de passe si l'algorithme évolue.
   - **Qualité** : aucune règle. Un bon hachage ne protège pas un mot de passe `a` ou `123456`, qui tombe dès les premiers essais d'un brute force ou d'un dictionnaire.
3. Au départ, mon intuition était : au moins 12 caractères, un chiffre, une majuscule, une minuscule et un caractère spécial. Les références vont plus loin et nuancent l'idée de composition :
   - **CNIL** (recommandation 2022) : elle raisonne en **entropie**, 80 bits minimum si le mot de passe est le seul facteur. Par exemple 12 caractères mêlant les 4 types, ou 14 caractères sans contrainte de composition. Une phrase de passe convient aussi.
   - **NIST SP 800-63B** : la **longueur** compte (15 caractères si le mot de passe est seul, 8 avec du MFA), il faut accepter au moins 64 caractères et tous les caractères (espaces, Unicode), et **ne pas imposer de règles de composition** ni de renouvellement périodique. En revanche il faut **refuser les mots de passe compromis** ou trop courants.
   - Conclusion : imposer « une majuscule, un chiffre, un caractère spécial » pousse à des schémas prévisibles (`Azerty123!`) sans vraiment gagner en robustesse. La politique retenue est donc : **12 caractères minimum**, 4096 maximum (borne anti-DoS sur le hachage), une **robustesse mesurée** (`PasswordStrength`, niveau moyen, soit environ 80 bits d'entropie comme demandé par la CNIL), **pas de pseudo ni d'e-mail** dans le mot de passe, et un **refus des mots de passe présents dans une fuite** (`NotCompromisedPassword`).
4. Aujourd'hui, le formulaire d'inscription est la seule porte d'entrée : l'API Platform n'expose que `GET /user/me`, et il n'y a ni changement ou réinitialisation de mot de passe, ni CRUD User dans l'admin, ni commande console, ni fixtures. Mais ces portes arrivent vite dans la vie d'un projet (mot de passe oublié, profil, `POST /api/users`, création d'un admin en CLI). Si la règle reste dans le FormType, chaque nouvelle porte devra la redéclarer, et il suffit d'en oublier une pour la contourner. La règle doit donc vivre au niveau du **modèle** (contrainte réutilisable appliquée à l'utilisateur), pas du formulaire.
5. Correctif mis en place :
   - Contraintes sur le mot de passe : `NotBlank`, `Length(min: 12, max: 4096)`, `PasswordStrength(minScore: STRENGTH_MEDIUM)`, un `Callback` qui refuse un mot de passe contenant le pseudo ou la partie locale de l'e-mail, et `NotCompromisedPassword` (API Have I Been Pwned en k-anonymity : seuls les 5 premiers caractères du SHA-1 sont envoyés). Tous les messages d'erreur sont en français.
   - Les seuils sont déclarés en constantes (`PASSWORD_MIN_LENGTH`, `PASSWORD_MAX_LENGTH`, `PASSWORD_MIN_SCORE`) et réutilisés par le template.
   - Côté front : `autocomplete="new-password"`, `minlength`/`maxlength`, un bouton « œil » pour afficher le mot de passe, et une aide dynamique sous le champ (jauge de robustesse et checklist des règles). Le score est calculé en JS avec le même algorithme que `PasswordStrengthValidator::estimateStrength()`, donc identique à celui du serveur. Ce n'est qu'un confort : c'est la validation serveur qui fait autorité.
   - ⚠️ **Limite actuelle** : ces contraintes sont encore déclarées dans `RegistrationType`. Pour couvrir *toutes* les portes d'entrée, il reste à les regrouper dans une contrainte `Compound` (ex. `#[PasswordPolicy]`) posée sur une propriété `plainPassword` non persistée de `User`. Ainsi, tout formulaire, endpoint API ou commande qui valide un `User` appliquerait la même politique.
6. Revalidation (soumission du formulaire côté serveur) :
   - `a` est refusé : « Le mot de passe doit contenir au moins 12 caractères. » et « Le mot de passe est trop faible : allongez-le ou variez les types de caractères. »
   - `motdepassemotdepasse` est refusé comme trop faible (peu de caractères différents, une seule classe). Il apparaît aussi 826 fois dans Have I Been Pwned.
   - `Azertyuiop123456` passe la longueur et la robustesse, mais il apparaît **9 065 fois** dans Have I Been Pwned. `NotCompromisedPassword` le refuse donc : « Ce mot de passe apparaît dans une fuite de données connue, choisissez-en un autre. » C'est exactement le cas qu'une règle de composition seule laisserait passer. Note : la vérification est désactivée en environnement de test (`not_compromised_password: false` dans `validator.yaml`) pour ne pas appeler l'API pendant les tests.
   - Un mot de passe contenant le pseudo est refusé. Une phrase de passe comme `Vélo-Nuage-Piment-47` est acceptée.
   - Les messages sont compréhensibles, et l'aide dynamique montre avant l'envoi quelle règle n'est pas respectée.
7. Non, une politique stricte ne suffit pas : elle ne protège ni du phishing, ni d'un keylogger, ni de la réutilisation du même mot de passe sur un autre site qui fuit. D'autres mesures déjà vues agissent sur le même risque :
   - **Le hachage adaptatif** (bcrypt/argon2id) : en cas de fuite de la base, il ralentit le cassage hors ligne.
   - **Le login throttling / rate limiter** (ex. 9) : il rend le brute force et le credential stuffing en ligne très lents.
   - **L'anti-énumération des comptes** (ex. 11) : l'attaquant ne sait pas quels e-mails existent, ce qui l'empêche de cibler ses essais.
   - **Le cookie de session sécurisé** (`HttpOnly`, `Secure`, `SameSite`, ex. 1) et la protection XSS : ils évitent de voler la session sans même connaître le mot de passe.
   - La mesure qui protège **même si le mot de passe est connu de l'attaquant** est l'**authentification multifacteur (MFA / 2FA)** : TOTP, clé FIDO2, ou passkeys qui suppriment carrément le mot de passe. Sans le second facteur, le mot de passe seul ne suffit plus pour se connecter.
