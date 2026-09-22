## Exercice 1
### Sécurité inactive:

HttpOnly:false

SameSite:""

Secure:false

### Sécurité active:

HttpOnly:true

SameSite:"lax"

Secure:true



## Exercice 2
XSS Stocké
Pour sécuriser l'exécution du script, il faut retirer le |raw du twig. Afin de protéger l'insértion du code en base de données, il faut faire un sanitize dans la fonction post

## Exercice 3

3. le titre de l'onglet ne change pas. le </script est remplacé par <%2Fscript

4. / 5. Le problème vient du fait qu'il n'y a pas de "" pour la prop value=""

6. On peut injecter un script permettant de récupérer les cookies et les envoyer à un serveur externe.

8. Réfléchi,

## Exercice 4

2. ce n'est pas un param ? mais #ref
3. / 4. La ref est traité côté JS dans app.ts
5. Non ça ne se déclenche pas.
6. Il faut utiliser une balise img avec la prop onerror pour que le script s'exécute. Ex : #ref=<img src="x" onerror='alert(document.domain)' />
Il faut modifier app.ts car il utilise innerHTML pour injecter le script. Il faut recréer un objet dans le DOM pour intégrer le html proprement.

    if (ref) {
        const strong = document.createElement('strong');
        strong.textContent= ref;
        //banner.innerHTML = `<strong>${ref}</strong> pense que cette catégorie va te plaire !`;
        banner.append(strong, ' pense que cette catégorie va te plaire !');
    }
7. DOM-based. Toute données saisie par un utilisateur qui est passée en URL et apprait dans le contenu de la page par la suite ne doit pas être affiché en innerHTML
8. Un site de phishing pour rediriger un utilisateur vers un site malveillant.
9. cf ci-dessus
10. Le html doit être généré (ou nettoyé) coté serveur et renvoyé au front

## Exercice 5

2. Oui on peut modifier le contenu de la page en mettant /modifier sur les url d'articles
3. Oui le contenu est modifié
5. On peut modifier même sans compte connecté
6. On met en place un voter pour sécuriser l'accès

## Exercice 6

1. Une Content Security Policy (CSP) est un en-tête HTTP qui permet aux sites web de dire aux navigateurs quels fichiers et scripts ont le droit d'être chargés et exécutés. Elle limite l'impact d'une faille XSS en bloquant l'exécution du code injecté (comme les scripts non autorisés ou les balises inline), même si le site laisse passer l'injection.
2. config/packages/nelmio_security.yaml 
4. Le script injecté ne s'exécute plus. La boîte d'alerte ou le code de l'attaquant reste inerte dans le code HTML.
6. La CSP n'a fait que neutraliser l'exploitation de la faille côté client.
7. La CSP complète les correctifs, elle ne les remplace pas.


## Exercice 7

1. dans le post il y a le comment[_token]	"csrf-token" qui est passé dans la request. Rien dans la suppression.
2. Le token CSRF est envoyé dans le formulaire mais pas dans la requête de suppression. Il faut le passer dans la requête de suppression pour que la suppression soit autorisée.
On peut supprimer un article en mettant /supprimer/1 dans l'url. Aucun contrôle sur l'utilisateur
3. On peut faire une page html avec une balise img et un src qui pointe vers l'url de suppression.
4. Le commentaire est supprimé
5. Le commentaire est supprimé, il n'y a pas de vérification sur l'utilisateur connecté.
6. Il manque une vérification sur l'utilisateur connecté avant de supprimer le commentaire et une jeton CSRF.
9. Plus possible de supprimer un commentaire sans être connecté et sans avoir le bon token CSRF.

## Exercice 8
1. {
  "iat": 1790078274,
  "exp": 1790081874,
  "roles": [
    "ROLE_USER"
  ],
  "username": "carter.davis1@example.com"
}

2. Le role est ROLE_USER mais est juste une info. Le mail est plus gênant.
3. Le changement de role ne change rien et le token n'est pas valide si on le réencode
4. Si le rôle pouvait être modifié et qu'il était pris en compte pour affecter le role à l'utilisateur, cela pourrait être dangereux.
5. Le phpsessionId ne comporte pas de données utilisateur. En revanche si on l'intercepte on peut se connecter à la place de l'utilisateur.
6. Un uid
7. Non, le rôle n'a pas d'intérêt dans le json. Le role est déterminé une fois l'utilisateur connecté et retourné par le getMe

## Exercice 9

