Exo 1

- cookie_samesite = protège contre les attaques XSSI (Cross-site script inclusion)
- cookie_httponly = protège contre les attaques XSS (Cross-Site Scripting)
- cookie_samesite = CSRF (Cross-Site Request Forgery ou Falsification de requête intersite)

Si un des cookies n'est pas présent, il peut injecter des scripts, ou voler la session, executer des actions non voulues

secure	true
samesite	"Lax"
httpOnly	true

Exo 2

1. Oui le titre de l'onglet est devenu XSS
2. oui le XSS est toujours présent quel que soit le endpoint car executé à chaque chargement. La faille réside entre 
   le js du navigateur et la bdd (stockée en bdd)
4. la balise script est interprétée et ne devrait pas
c'est une Cross-Site Scripting
utiliser un sanitize_html(sanitizer = "default")

Exo 3

<input class="form-control" type="search" name="q" value="fut" placeholder="Rechercher des sujets..." aria-label="Rechercher">
=> inséré via attribut value
Que se passe-t-il ? Le titre de l'onglet change-t-il ? 
=> non ça ne change pas le titre

Regardez à nouveau le code source à l'endroit où votre terme de recherche apparaît : que sont devenus les caractères < et > ?
<input class="form-control" type="search" name="q" value="&lt;script&gt;document.title" =="" &#039;xss&#039;&lt;="" script&gt;="" placeholder="Rechercher des sujets..." aria-label="Rechercher">

< et > sont devenus &lt; et &gt;
value={{ ... }} — l'attribut n'est pas entre guillemets
XSS réfléchie
il suffit de donner un lien à une victime avec une query custom qui contient un script


Exo 4

#ref=ScarlettGarcia5
non pas un param classique c'est #

TOTO pense que cette catégorie va te plaire !
non la valeur n'apparait pas, c'est traité coté navigateur (front) en js
c'est injecté via une balise script dans le head

4. dans #ref-banner
5. <script> ne s'exécute pas car pas interprété, on interprete du html via innerHTML
6. <img src="image.gif" onerror="alert(1)">
7. XSS DOM-based passe pas par le serveur js côté client
8. l'envoi d'un lien ex https://localhost:8443/1#ref=<img src=x onerror=alert(1)> 
9. remplacement de innerHTML par un textContent
10. pas de correctif back car pas d'utilisation de la variable coté back

Exo 5

2. oui
3. ça a bien changé
4. on peux accéder aussi
5. on peux accéder aussi
7. utilisation Voter

Exo 6

1. bloque l'exécution, réduit l'impact. (A Content Security Policy (CSP) is an HTTP response header that helps protect websites from security threats like cross-site scripting (XSS) and clickjacking)
4. bloqués, console affiche erreur CSP.
6. non corrigé, c'est un contournement CSP 
7. ça complète ne remplace jamais correctifs source
8. nécessite HTTPS actif partout / si le cache expire et est trop long, les utilisateurs peuvent 
   etre bloqués
9. iframe fonctionnait avant, X-Frame-Options bloque apres

Exo 7

1. présence token : '{"token":"ce2cf6","route":"app_comment_delete","method":"GET","controller":
{"class":"App\\\\Controller\\\\CommentController","method":"delete","file":"\\/var\\/www\\/html\\/src\\/Controller\\/CommentController.php","line":16},"status_code":302,"status_text":"Found"}'
2. la suppression apelle un get et non un delete
3. public/exo7-test-delete.html
4. comm supprimé
5. comm supprimé aussi, pas de sécurité sur un utilisateur connecté
6. il manque un token et un form et un voter
7. via une image dans un wysiwig ou un lien dans un mail
9. bloqué via voter et csrf

Exo 8

1.
{
"iat": 1790078090,
"exp": 1790081690,
"roles": [
    "ROLE_USER"
],
"username": "scarlett.garcia5@example.com"
}

2. roles donnée sensible + adresse mail idem
3. ça n'accepte pas le token modifié
4. role
5. rien il ne donne pas d'info
6. user_id_claim + JWTPayloadListener
7. non il est lié à l'utilisateur en bdd, pas besoin de l'envoyer

Exo 9

1. non
2. non
3. brute force / ddos / probleme spam
6. 3 tentatives / HTTP 401
7. on est bon
8. non il faut attendre 12m, le blocage agit avant la vérification du mdp
9. login_throttling sur tous les firewall api_login/api + rate limiter

Exo 10

oui 
429

Exo 11

1. on recoit un mail
2. Cette adresse e-mail est déjà utilisée
3. message présent révèle compte existant
4. cartographie identités, phishing ciblé possible
5. non, ralentit sans empêcher vraiment
6. UniqueEntity, message explicite sur doublon
7. email différencie discrètement information

Exo 12.1

1. via onglet réseau http
2. il peut adapter les attaques en fonction de la version php et des CVE
3. curl -sk "https://localhost:8443/recherche?q[]=x&q[]=y" → page "Symfony Exception" complète : stack trace entière,
   chemins absolus du serveur (/var/www/html/vendor/...), extraits de code source, noms de classes internes, version des composants Symfony. Bien plus qu'une simple erreur — un dump quasi complet de l'architecture interne de l'app.
4. Ce qu'il en reste côté serveur — l'exception reste journalisée en clair dans var/log/dev.log (via Monolog), bien après que la page a été fermée :
5. Audit de la configuration PHP effective (php -i, aucun php.ini chargé — Loaded Configuration File => (none), uniquement les valeurs par défaut du binaire + les conf.d/*.ini du Dockerfile) :
   - X-Powered-By a disparu des réponses (expose_php = Off)
   - 200 OK sur la page d'accueil, comportement inchangé
   - Oui, toujours — le même déclencheur (/recherche?q[]=x&q[]=y) renvoie encore la page "Symfony Exception" complète
   - Oui, déjà avant et toujours après — var/log/dev.log contient bien l'entrée (4 occurrences désormais)

Exo 12.2


