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