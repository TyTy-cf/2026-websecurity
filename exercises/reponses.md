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

