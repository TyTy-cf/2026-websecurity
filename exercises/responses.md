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

