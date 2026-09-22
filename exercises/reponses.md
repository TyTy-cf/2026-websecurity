Exercice 1 — Security Misconfiguration (cookie de session)


1.  avant => pma_lang=fr; phpMyAdmin=ce7cfd00128390026d58f7cd50cd21d2; main_auth_profile_token=947e55; PHPSESSID=130983edd8b06b508b0326baf2e6a83a
    après => main_auth_profile_token=9bd92f; PHPSESSID=0ccb6ac1c41317c67b131db6aadbf8b2

2. framework:
    secret: '%env(APP_SECRET)%'
        session:
            cookie_secure: true
            cookie_httponly: true
            cookie_samesite: lax

3.  cookie_secure transmet les cooki uniquement au url https => empéche de récupérer les info dans les cookies
    cookie_httponly empéche l'accés au cookie via des scripts autre que ceux de l'application => empéche les injection Javascript
    cookie_samesite cookie valide uniquement sur l'application  => empéche les faille de type CSRF

4. enléve pma_lang et phpMyAdmin des cookies ne sont plus visible 


Exercice 2 — Cross-Site Scripting (XSS)

1. Oui le titre de l'onglet a changé 

2.  Le payload s'exécute-t-il toujours pour un visiteur qui ne l'a pas soumis lui-même ? => oui
    Qu'est-ce que cela vous apprend sur l'endroit où elle réside entre deux requêtes ? => le script est persisté en bdd et jouer au chargement de la page

4. enlever le **|raw** dans les fichiers twig et ajouter symfony/html-sanitizer avec composer 

5. Type Stocké

Exercice 3 — Cross-Site Scripting (XSS)

2. la recherche est injecté dans value du champs input 

3.  Que se passe-t-il ? => rien  
    Le titre de l'onglet change-t-il ? => non 
    Regardez à nouveau le code source à l'endroit où votre terme de recherche apparaît : que sont devenus les caractères < et > ? une parti de la recher est hors de la value et deviens un attribut du chmapsinput

7. il suffit de rajouter les " autour des balise twig pour que l'entiéreté de la chaine de caracté soit pris en compte dans le value 

8. Type de XSS réfléchi

Exercice 4 — Cross-Site Scripting (XSS)

2.  Quelle partie contient le nom de la personne ? => #ref=...
    Est-ce un paramètre classique (?...) ou autre chose ? non c'est  un href passé directement d'en l'url

3.  Votre valeur de test apparaît-elle quelque part dans ce code source ? => non
    Que pouvez-vous en déduire sur qui traite réellement cette donnée : le serveur, ou autre chose ? => elle est ajouter par un script js 

5.  Est-ce que ça s'exécute ? => non
    Si non, à votre avis pourquoi une balise <script> insérée de cette façon ne se déclenche-t-elle pas, contrairement à ce que vous aviez observé aux exercices précédents ? elle est exécuté dans le par le js qui n'a pas de balise

6. Sans utiliser de balise <script>, trouvez une balise HTML qui déclenche du JavaScript dès qu'elle est insérée dans la page =>  il faut utiliser la balise img et mettre le script dans l'attribu onerror <img src="" onerror="alert(1)">

7.  En vous basant sur ce que vous avez observé à l'étape 3, comment qualifieriez-vous ce type de XSS ? Type DOM-based
    En quoi est-il différent des exercices 2 et 3, alors que le résultat (exécution de JavaScript arbitraire) est similaire ? cette fois on passe par l'url, l'erreur peux donc est envoyé facilement a quelqu'un sans être detecté car elle n'est persisté null part.

8. mail fishing, arnaque sms et autre 

10. Ce correctif peut-il être fait côté serveur (Symfony/Twig) ? non
    Pourquoi ? car il faut modifier le DOM du client
    (if (ref) {
    let strong = document.createElement("<strong>${ref}</strong>");
    banner.append(strong + ` pense que cette catégorie va te plaire !`);
    })

Exercice 5 — Broken Access Control

2. Vous accéder bien à la ressource ? => oui 

5. Que se passe t'il avec un utilisateur non connecté ? => le contenu est bien modifié 

6. Voter ajouté => \\wsl.localhost\Ubuntu-22.04\root\projects\Formation\2026-websecurity\src\Security\TopicVoter.php

