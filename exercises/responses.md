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

## Exercice 12

1. x-powered-by PHP/8.2.33, visible depuis les headers de la réponse serveur.
2. Il peut connaitre les failles de sécurités connues de cette version et les exploiter
3. On a la version de PHP et le type de Serveur, le code d'erreur.
4. Des logs avec 
5. Version de php
6. Personnaliser l'affichage des erreurs via TwigBundle en ajoutant les twigs au projet.