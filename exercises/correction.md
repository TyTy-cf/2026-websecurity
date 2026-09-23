# Exercice 1 — Security Misconfiguration (cookie de session)

## Question 1 — Le cookie actuel

Je me connecte et je regarde l'onglet Network. La réponse pose le cookie comme ça :

```
Set-Cookie: PHPSESSID=40dd29f230c54a1576c0ddad75a9162b; path=/
```

Le seul attribut présent c'est `path=/`. Il manque `secure`, `httponly` et `samesite`.
Je le vois aussi dans la console : `document.cookie` m'affiche bien le PHPSESSID, donc
n'importe quel script peut le lire.

## Question 2 — La config par défaut de Symfony

Par défaut Symfony met :

- `cookie_secure: auto`
- `cookie_httponly: true`
- `cookie_samesite: lax`

Dans le projet les trois avaient été mis à `false` / `null`. Donc les trois protections
avaient été désactivées exprès.

## Question 3 — À quoi sert chaque attribut

`httponly` : quand il est à false, je peux lire le cookie en JS avec `document.cookie`.
Du coup si j'ai une faille XSS (exo 2), je peux voler le cookie et me connecter à la place
de la victime sans son mot de passe.

`secure` : quand il est à false, le cookie part aussi en HTTP (pas chiffré). Quelqu'un sur
le même réseau peut le récupérer.

`samesite` : quand il est à null, le cookie est envoyé même quand la requête vient d'un
autre site. Ça permet le CSRF : un site pourri peut faire une action sur Fakeddit avec ma
session.

## Question 4 — Remettre une config propre

J'ai remis les valeurs par défaut dans `config/packages/framework.yaml` :

```yaml
session:
    cookie_secure: auto
    cookie_httponly: true
    cookie_samesite: lax
```

Après ça, le cookie devient :

```
Set-Cookie: PHPSESSID=...; path=/; secure; httponly; samesite=lax
```

Et dans la console `document.cookie` ne montre plus le PHPSESSID. C'est une faille de type
Security Misconfiguration : c'est pas un bug de code, juste une mauvaise config.


# Exercice 2 — XSS

## Question 1 — Le point d'injection

J'écris ce commentaire sur un sujet :

```html
<script>document.title = 'XSS'</script>
```

Je recharge, et le titre de l'onglet devient "XSS". Donc mon commentaire est exécuté comme
du code au lieu d'être affiché comme du texte.

## Question 2 — Est-ce que ça reste ?

J'ouvre la même page en navigation privée (donc pas ma session), et le script s'exécute
quand même. Ça veut dire que le payload est enregistré en base, pas juste dans ma session.
Si je supprime le commentaire ça disparait, si je le remets ça revient.

## Question 3 — Un vrai impact

`alert(1)` ça prouve juste que ça marche, mais c'est pas dangereux. Le vrai danger c'est
que le code tourne dans la session de la victime. Par exemple un script qui poste un
commentaire tout seul à sa place quand elle charge la page :

```html
<script>
fetch(location.pathname, {
  method: 'POST',
  credentials: 'include',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: new URLSearchParams({'comment[content]': 'posté à mon insu'})
})
</script>
```

La victime a rien cliqué, juste ouvert la page.

## Question 4 — Le correctif

Le problème est dans `templates/front/topic/show.html.twig` ligne 38 : le commentaire était
affiché avec `|raw`, qui désactive la protection de Twig.

Avant :
```twig
<div>{{ comment.content|raw }}</div>
```

J'ai enlevé le `|raw` :
```twig
<div>{{ comment.content }}</div>
```

Maintenant Twig échappe tout seul : `<script>` devient `&lt;script&gt;` et ça s'affiche
comme du texte au lieu de s'exécuter. La règle c'est de jamais mettre `|raw` sur une donnée
qui vient d'un utilisateur.

## Question 5 — Quel type de XSS

C'est une XSS stockée. Le payload est enregistré en base et renvoyé à tous les visiteurs
(c'est ce que j'ai vu à la question 2, il s'exécute même pour quelqu'un qui l'a pas écrit).


# Exercice 3 — XSS (dans un attribut)

## Question 2 — Comment mon terme est affiché

Après une recherche, mon terme reste dans la barre. Dans le code source je vois qu'il est
mis dans l'attribut `value` de l'input, et surtout que `value=` est écrit **sans
guillemets** (`templates/front/common/_header.html.twig`).

## Question 3 — Le payload évident

Je cherche `<script>document.title = 'XSS'</script>` et il se passe rien. Dans le code
source les `<` et `>` sont devenus `&lt;` et `&gt;`. Twig échappe bien ces caractères, donc
je peux pas ouvrir une balise `<script>` ici.

## Question 4 — Le caractère jamais échappé

Twig échappe `< > " '` mais pas l'espace. Et comme `value=` est sans guillemets, un espace
termine la valeur. Du coup je peux pas mettre une balise, mais je peux sortir du `value` et
ajouter d'autres attributs HTML sur l'input.

## Question 5 — S'exécuter sans clic

`onclick` ça suffit pas parce qu'il faut cliquer. Il faut combiner deux attributs : un qui
donne le focus tout seul à l'input au chargement, et un gestionnaire d'événement qui se
déclenche sur ce focus. Comme le focus se fait automatiquement, le JS part tout seul, sans
balise `<script>`.

## Question 6 — Le scénario

L'attaquant peut pas taper à ma place, mais le terme est dans l'URL (`/recherche?q=...` en
GET). Donc il fabrique une URL avec son payload dans `q` et il l'envoie à la victime (mail,
lien piégé). Quand elle clique, ça s'exécute chez elle.

## Question 7 — Le correctif

Le problème c'est le `value=` sans guillemets. J'ai mis des guillemets et simplifié avec
`|default('')` :

Avant :
```twig
{% if app.request.query.get('q') is not null %}value={{ app.request.query.get('q') }}{% endif %}
```

Après :
```twig
value="{{ app.request.query.get('q')|default('') }}"
```

Avec les guillemets, l'espace reste dans la valeur. Pour sortir il faudrait un `"`, mais
Twig le transforme en `&quot;`. Donc on peut plus injecter d'attribut.

## Question 8 — Quel type de XSS

C'est une XSS réfléchie. Le payload est pas stocké, il passe dans l'URL et il est renvoyé
direct dans la réponse. Différence avec l'exo 2 : là il faut piéger chaque victime avec un
lien.


# Exercice 4 — XSS (DOM)

## Question 2 — Où est le nom dans l'URL

Le lien de partage ressemble à :

```
https://localhost:8443/categories/12#ref=carter.davis1
```

Le nom est après le `#` (`#ref=...`). C'est le fragment, pas un paramètre `?ref=...` normal.

## Question 3 — Ce que voit le serveur

Je mets une valeur de test dans l'URL et je regarde le code source (view-source) : ma
valeur apparait nulle part. C'est parce que le navigateur envoie jamais le `#` au serveur.
Donc c'est pas le serveur qui traite ça, c'est du JavaScript côté navigateur.

## Question 4 — Où c'est affiché

Dans l'onglet Éléments (le DOM), ma valeur est dans `<div id="ref-banner">`. Le code
responsable est dans `assets/scripts/app.ts` :

```js
const ref = hashParams.get('ref');
if (ref) {
    banner.innerHTML = `<strong>${ref}</strong> pense que cette catégorie va te plaire !`;
}
```

Il lit le `#ref=` et il l'injecte avec `innerHTML`.

## Question 5 — Pourquoi `<script>` marche pas

Je mets `<script>alert(document.domain)</script>` et rien. C'est une règle du navigateur :
un `<script>` inséré avec `innerHTML` s'exécute jamais.

## Question 6 — Ce qui marche

Il faut une balise avec un attribut qui déclenche du JS tout seul. Le classique c'est une
balise `<img>` avec une source qui existe pas et un `onerror`. Comme l'image charge pas, le
`onerror` se déclenche direct et exécute le JS. `innerHTML` accepte ce genre de balise,
contrairement à `<script>`.

## Question 7 — Quel type de XSS

C'est une XSS DOM. La différence avec les exos 2 et 3 : ici le serveur voit jamais le
payload (il est dans le `#`). Tout se passe côté navigateur, c'est le JS qui lit
`location.hash` et l'écrit dans `innerHTML`.

## Question 8 — Le scénario

L'attaquant fabrique une URL de catégorie normale avec son payload dans le `#ref=`, et il
l'envoie. Comme le début de l'URL c'est le vrai site, ça a l'air normal. La victime clique
et ça s'exécute chez elle.

## Question 9 — Le correctif

Le problème c'est `innerHTML` qui interprète le HTML. J'ai changé pour insérer du texte
avec `textContent` (dans `assets/scripts/app.ts`) :

```js
if (ref) {
    const name = document.createElement('strong');
    name.textContent = ref;
    banner.replaceChildren(name, document.createTextNode(' pense que cette catégorie va te plaire !'));
}
```

Avec `textContent`, une balise `<img ...>` s'affiche comme du texte au lieu de s'exécuter.
Je garde le `<strong>` en le créant proprement. Après il faut recompiler avec `npm run dev`
parce que l'appli utilise le fichier compilé `public/build/scripts.js`.

## Question 10 — Est-ce qu'on peut corriger côté serveur ?

Non. Le serveur reçoit jamais le `#` (voir question 3), donc Symfony/Twig peuvent rien
échapper. Une XSS DOM ça se corrige forcément côté client, dans le JS, en utilisant
`textContent` au lieu de `innerHTML`.

# Exercice 5 — Broken Access Control

## Questions 2 à 4 — Accéder au sujet d'un autre

Connecté avec `isabella.young62@example.com`, je tape à la main l'URL
`/sujets/{id}/modifier` avec l'id d'un sujet qui appartient pas à ce compte. Le formulaire
s'affiche quand même. Je modifie, j'enregistre, et en me reconnectant avec l'autre compte
je vois que son sujet a bien changé. Ça marche avec plusieurs sujets, c'est pas un cas
particulier.

## Question 5 — Sans être connecté

Même sans être connecté du tout, l'URL `/sujets/{id}/modifier` me donne accès au formulaire
et je peux enregistrer. L'action demande aucune connexion.

## Le problème

Dans `src/Controller/TopicController.php`, l'action `edit()` faisait aucune vérification :
elle chargeait le sujet par son id et traitait le formulaire, c'est tout. Le bouton "Edit"
caché dans le template protège rien : il cache juste le lien, mais l'URL reste accessible.

## Question 6 — Le correctif

J'ai ajouté deux protections dans `TopicController` :

```php
#[Route('/sujets/{id}/modifier', name: 'app_topic_edit')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function edit(Topic $topic, Request $request, EntityManagerInterface $entityManager): Response
{
    if ($topic->getAuthor() !== $this->getUser()) {
        throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres sujets.');
    }
    // ...
}
```

D'abord `#[IsGranted]` pour obliger à être connecté (sinon redirigé vers la connexion).
Ensuite je vérifie que le sujet appartient bien à l'utilisateur, sinon erreur 403. Comme ça
on peut plus modifier le sujet d'un autre, même en changeant l'id dans l'URL.

Je mets la protection dans le contrôleur (la vraie ressource), pas dans le template.

## Quel type de faille

C'est du Broken Access Control, plus précisément un IDOR : on peut changer l'id dans l'URL
et rien vérifie qu'on a le droit d'agir sur ce sujet-là.



# Exercice 6 — En-têtes HTTP de sécurité

## Question 1 — C'est quoi une CSP

La Content-Security-Policy c'est un en-tête HTTP que le serveur envoie au navigateur. Il dit
au navigateur d'où il a le droit de charger et d'exécuter des ressources (JS, CSS, images...).
Avec `script-src 'self'`, le navigateur exécute seulement le JavaScript qui vient de mon site
et refuse tout le reste.

Ça limite les XSS même sans corriger le bug : le payload est toujours injecté dans la page,
mais le navigateur refuse de l'exécuter. C'est une deuxième ligne de défense.

## Question 2 — Où la configurer

Le projet utilise le bundle NelmioSecurityBundle. La config est dans
`config/packages/nelmio_security.yaml`. C'est ce fichier qui décide des en-têtes, pas du code
PHP. Au début le paquet était pas installé (l'appli plantait avec "Attempted to load class
NelmioSecurityBundle"), il a fallu faire `composer install` pour l'installer.

## Question 3 — Mettre une politique

Dans `nelmio_security.yaml` la CSP est en strict :

```yaml
csp:
    enforce:
        default-src: [ "'self'" ]
        script-src: [ "'self'" ]
        style-src: [ "'self'" ]
        img-src: [ "'self'", 'https://picsum.photos', 'https://fastly.picsum.photos' ]
```

J'ai vérifié dans l'onglet Network que l'en-tête `content-security-policy` est bien présent
sur les réponses.

## Question 4 — Rejouer les payloads

J'ai reposté le payload de l'exo 2 dans un commentaire :

```html
<script>document.title = 'XSS'</script>
```

Le titre de l'onglet change plus, et dans la console j'ai ce message :

```
Content-Security-Policy : Les paramètres de la page ont empêché l'exécution d'un script
intégré (script-src-elem) car il enfreint la directive : « script-src 'self' ... ».
```

Donc le script est toujours dans la page (le bug est pas corrigé) mais le navigateur refuse
de l'exécuter. La CSP a bloqué l'attaque.

Note : en mode dev, la barre de debug Symfony ajoute `'unsafe-inline'` et un `nonce` à la CSP.
Mais comme il y a un nonce, le navigateur ignore le `'unsafe-inline'` : seuls les scripts qui
ont le bon nonce (ceux du site) s'exécutent, un script injecté n'a pas de nonce donc il est
bloqué quand même.

## Question 5 — Dégât collatéral

La CSP stricte casse une fonctionnalité qui utilisait du JS inline. Sur la page catégorie, le
champ du lien de partage avait un `onclick="this.select()"` (dans
`templates/front/category/show.html.twig`). Comme la CSP interdit le JS inline, le `onclick`
marche plus.

Correctif sans réintroduire de faille : enlever le `onclick` du gabarit et mettre le
comportement dans le JS externe (`assets/scripts/app.ts`), qui lui vient du site donc autorisé
par la CSP.

## Question 6 — Prendre du recul

La CSP a corrigé aucune faille des exos précédents. Le code vulnérable est toujours là (le
`|raw`, le `value=` sans guillemets, le `innerHTML`). Si un attaquant trouve un moyen de
contourner la CSP (par exemple une directive mal configurée, ou un `'unsafe-inline'` laissé
par erreur), alors toutes les failles redeviennent exploitables.

## Question 7 — La CSP remplace ou complète ?

Elle complète, elle remplace pas. La CSP c'est un filet de sécurité en plus, au cas où une
faille passe. Mais il faut quand même corriger les bugs (échapper les sorties, etc.), parce
que la CSP peut être contournée ou mal configurée. Les deux ensemble = défense en profondeur.

## Question 8 — HSTS

L'en-tête `Strict-Transport-Security` force le navigateur à revenir en HTTPS pour les
prochaines visites. Condition pour qu'il soit pris en compte : la page doit déjà être servie
en HTTPS (un HSTS envoyé en HTTP est ignoré).

Piège du `max-age` trop long : une fois que le navigateur a mémorisé le HSTS, il refuse le
HTTP pendant toute la durée du max-age. Si on met un an et qu'on a un problème de certificat,
on peut plus revenir en arrière facilement. Donc on commence avec un max-age court, et on
l'augmente une fois qu'on est sûr que tout marche.

## Question 9 — Clickjacking (iframe)

Le clickjacking c'est quand un site pirate affiche mon site dans une `<iframe>` invisible pour
piéger la victime (elle croit cliquer sur le site pirate mais elle clique sur le mien).

Test : je crée une page HTML avec `<iframe src="https://localhost:8443/">`. Avec l'en-tête
`X-Frame-Options: DENY` (posé par Nelmio, section clickjacking du yaml), le navigateur refuse
d'afficher le site dans l'iframe. La page piège reste vide.


# Exercice 7 — CSRF

## Question 1 — Utiliser la fonctionnalité normalement

Avant de cliquer sur Supprimer j'ouvre l'onglet Network. Quand je clique, je vois que la
requête part en GET vers `/commentaires/{id}/supprimer`, et il y a aucun jeton dedans. C'est
juste un lien.

## Question 2 — Comparer avec le formulaire de commentaire

Le formulaire pour publier un commentaire, lui, c'est un vrai formulaire Symfony : il est en
POST et il contient un champ caché `_token` (le jeton CSRF ajouté automatiquement par Symfony).
Le bouton Supprimer c'est juste un lien `<a href>`, il a rien de tout ça.

## Questions 3-4 — La page piège

J'ai créé un fichier HTML tout simple, en dehors du projet, avec dedans :

```html
<img src="https://localhost:8443/commentaires/1/supprimer">
```

Le `<img>` sert pas à afficher une image, il force le navigateur à charger l'URL tout seul, au
chargement de la page. En ouvrant cette page (connecté à Reddit-Ish dans le même navigateur), le
commentaire n°1 est supprimé, sans que j'aie cliqué sur quoi que ce soit.

## Question 5 — Ce qui est nécessaire

Si je me déconnecte de Reddit-Ish et que je rouvre la page piège, le commentaire est plus
supprimé. Donc l'attaque dépend du fait que la victime soit connectée : c'est son cookie de
session, envoyé automatiquement par le navigateur, qui fait passer la requête pour légitime.

## Question 6 — Ce qui manque

Ce qui manque, c'est le jeton CSRF. Un vrai formulaire Symfony (FormType) l'aurait ajouté tout
seul. Cette route, qui passe pas par un formulaire, a rien pour vérifier que la requête vient
bien de notre site.

## Question 7 — Scénario de diffusion

L'attaquant peut pas ouvrir la page à ma place, mais il peut m'envoyer le lien (mail, message,
faux lien attirant genre "regarde cette photo"). Si je l'ouvre pendant que je suis connecté à
Reddit-Ish, l'attaque part toute seule.

## Question 8 — Le correctif

Comme c'est pas un FormType, je mets la protection CSRF moi-même. Deux changements :

Dans `CommentController` : la route passe en POST au lieu de GET, et je vérifie le jeton avant
de supprimer.

```php
#[Route('/commentaires/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
public function delete(string $id, Request $request, ...): Response
{
    // ...
    if (!$this->isCsrfTokenValid('delete_comment_' . $comment->getId(), $request->request->get('_token'))) {
        throw $this->createAccessDeniedException('Jeton CSRF invalide.');
    }
    // ...
}
```

Dans le template (`show.html.twig`) : je remplace le lien par un petit formulaire POST avec le
jeton.

```twig
<form method="post" action="{{ path('app_comment_delete', { id: comment.id }) }}">
    <input type="hidden" name="_token" value="{{ csrf_token('delete_comment_' ~ comment.id) }}">
    <button class="btn btn-sm btn-outline-danger">{{ 'common.delete'|trans }}</button>
</form>
```

Le nom du jeton (`delete_comment_ID`) doit être le même des deux côtés.

## Question 9 — Revalider

Je rejoue la page piège après le correctif. La requête `<img>` part en GET, mais la route
accepte plus que le POST : le serveur répond 405 et le commentaire est pas supprimé. Je l'ai
vérifié en ligne de commande : code 405, commentaire n°1 toujours là. L'attaque marche plus.

Double barrière : même si l'attaquant envoyait un POST, il connaît pas le jeton
`delete_comment_1`, donc ça serait refusé quand même (403).


# Exercice 8 — Intégrité du JWT

## Question 1 — Décoder le jeton

Un JWT a 3 parties séparées par des points : `entête.payload.signature`. En le décodant (sur
jwt.io ou en base64), je lis le payload. Chez nous il contient :

```json
{
  "iat": 1790079152,
  "exp": 1790082752,
  "roles": ["ROLE_USER"],
  "username": "carter.davis1@example.com"
}
```

Important : le JWT est signé mais PAS chiffré. Le payload est juste du base64, donc n'importe
qui peut le lire.

## Question 2 — Ce qui est exposé

- `username` = l'email de l'utilisateur → donnée personnelle
- `roles` = les rôles de l'utilisateur
- `iat` / `exp` = dates techniques (création / expiration), pas sensibles

Donc si quelqu'un intercepte le token, il voit l'email en clair.

## Question 3 — Ce que la signature protège

J'ai modifié une valeur dans le payload (par exemple me mettre ROLE_ADMIN) et regénéré un
token avec ça, puis je l'ai présenté à /api/user/me. Résultat : 401. La signature ne
correspond plus.

Conclusion : la signature protège l'intégrité (on peut pas trafiquer le contenu sans casser
le token), mais elle protège pas la confidentialité (tout le monde peut lire le contenu).

## Question 4 — Impact concret

La seule info qui cause un dommage au-delà de Reddit-Ish, c'est l'email. Les rôles et les
dates servent à rien en dehors du site. L'email par contre est réutilisé partout : avec lui un
attaquant peut faire du phishing ciblé, ou tenter les mêmes identifiants sur d'autres sites
(credential stuffing). Le mal dépasse donc Reddit-Ish.

## Question 5 — Comparer avec PHPSESSID

Si j'intercepte le cookie PHPSESSID, je lis juste une suite de caractères au hasard
(`40dd29...`), ça m'apprend rien sur l'utilisateur. C'est un identifiant opaque : juste une clé
qui pointe vers des données stockées côté serveur.

Le JWT, lui, transporte les données directement dans le token. C'est ça la différence de fond :
la session cache l'info côté serveur, le JWT la balade côté client.

## Question 6 — Le correctif

On peut pas juste supprimer l'identité du token, il faut savoir quel utilisateur il représente.
Mais l'email c'est personnel, et l'id (1, 2, 3...) c'est devinable. La bonne valeur c'est un
UUID : pas personnel, pas devinable, unique par utilisateur.

Ce que j'ai fait :
1. `composer require symfony/uid`
2. Ajouté un champ `uuid` à l'entité User (unique, généré à la création)
3. Migration + remplissage des utilisateurs existants avec un UUID
4. Config Lexik `user_id_claim: uuid` pour que le token identifie par l'uuid
5. Un provider `app_user_uuid_provider` (property: uuid) sur le firewall api, pour recharger
   l'utilisateur par son uuid. Le login reste par email.
6. Un listener sur la création du JWT qui met l'uuid dans le payload et retire l'email et les
   rôles.

Après ça, le token contient plus que :

```json
{ "iat": ..., "exp": ..., "uuid": "cdee7430-b5f9-11f1-87fd-02d7a263c2a0" }
```

Plus d'email, plus de rôles. Et /api/user/me marche toujours (le serveur retrouve
l'utilisateur par son uuid).

## Question 7 — Le rôle dans le token ?

Non, le rôle a pas besoin d'être dans le token. À chaque requête, Symfony recharge
l'utilisateur depuis la base et lit ses rôles à jour. Je l'ai vérifié : même après avoir retiré
les rôles du token, /api/user/me renvoie bien `"roles":["ROLE_USER"]`, parce que ça vient de la
base, pas du token. En plus c'est mieux : si on change le rôle d'un user en base, un rôle
gravé dans le token serait resté obsolète.





# Exercice 9 — Login Throttling

## Question 1 — Aucune limite sur /connexion

Je boucle sur le formulaire de connexion avec un mauvais mot de passe. À la 5e tentative,
à la 10e, à la 20e : toujours le même message "Identifiants invalides", le même temps de
réponse, et je peux continuer. Rien ne me ralentit et rien ne me bloque.

## Question 2 — Pareil sur l'API

Je refais le test sur `POST /api/login_check` avec curl. Même chose : un 401 "Identifiants
invalides." à chaque fois, autant de fois que je veux. Les deux routes se comportent pareil,
aucune des deux est protégée.

## Question 3 — Le risque

Sans limite, on peut essayer les mots de passe en boucle : c'est du brute-force. Et si
l'attaquant a récupéré une liste d'identifiants sur un autre site piraté, il peut tous les
tester ici pour voir lesquels marchent (credential stuffing).

J'ai chronométré une tentative sur l'API : environ 50 ms. Donc 10 000 mots de passe ça fait
10 000 × 0,05 = 500 secondes, soit moins de 9 minutes en tapant une requête après l'autre. Et
en lançant plusieurs requêtes en parallèle, ça descend sous la minute. Un mot de passe faible
tient pas.

## Question 4 — La fonctionnalité Symfony

Symfony a ça en natif : `login_throttling`, qui se configure directement sur le firewall dans
`security.yaml`. Ça s'appuie sur le composant Rate Limiter, mais y'a pas de code à écrire,
c'est juste de la config.

## Question 5 — Mettre en place la protection

Je l'ai mis sur les deux firewalls qui authentifient, `main` (le site) et `api_login` (l'API),
dans `config/packages/security.yaml` :

```yaml
login_throttling:
    max_attempts: 5          # 5 échecs autorisés
    interval: '15 minutes'   # sur une fenêtre de 15 minutes
```

5 tentatives sur 15 minutes, ça laisse largement la place à quelqu'un qui se trompe, mais ça
casse complètement une attaque automatisée.

## Question 6 — Vérifier le blocage

Je rejoue les deux tests. Sur les deux routes, le blocage arrive à partir de la **6e**
tentative (les 5 premières passent, la 6e est refusée).

Sur l'API :
```
essai 5 -> HTTP 401 | Identifiants invalides.
essai 6 -> HTTP 401 | Trop de tentatives de connexion échouées, veuillez réessayer dans 15 minutes.
```

Sur `/connexion` c'est le même seuil et le même message, mais l'enveloppe change : au lieu
d'un 401 JSON, j'ai une redirection 302 vers le formulaire avec le message en flash. C'est
normal, c'est juste la façon dont chaque firewall répond (`json_login` vs `form_login`), le
comptage est identique.

## Question 7 — Et l'utilisateur légitime ?

Avec le bon mot de passe du premier coup, ça marche toujours. Et si je me trompe deux fois
avant de taper le bon (donc sous le seuil de 5), la connexion passe quand même : la
redirection m'envoie sur `/` au lieu de me renvoyer sur `/connexion`. Un utilisateur normal
est pas pénalisé.

J'ai aussi vérifié que le blocage d'un compte bloque pas tout le monde : après avoir bloqué
`carter.davis1`, je me connecte sans problème avec `isabella.young62` depuis la même machine.
C'est parce que Symfony crée deux compteurs par firewall (je les vois avec
`php bin/console debug:container limiter`) :

- `limiter._login_local_main` : compte par couple IP + identifiant
- `limiter._login_global_main` : compte par IP seule, avec un seuil plus haut

Le local empêche de s'acharner sur un compte précis, le global empêche de balayer plein de
comptes différents depuis la même machine.

## Question 8 — Le bon mot de passe juste après un blocage

Une fois le compte bloqué, je retente avec le **bon** mot de passe : refusé quand même, avec
le message "Trop de tentatives".

Ça m'apprend que le throttling agit **avant** la vérification du mot de passe, pas après. Le
compteur est consulté à l'entrée de l'authentification : si le seuil est dépassé, la requête
est rejetée sans même regarder les identifiants. C'est ce qu'on veut, parce que si le contrôle
se faisait après, l'attaquant saurait quand il tombe juste — et surtout le serveur continuerait
à faire le calcul coûteux du hash à chaque tentative.

## Question 9 — Documenter le correctif

Un seul fichier modifié, `config/packages/security.yaml`, avec le même bloc `login_throttling`
(`max_attempts: 5`, `interval: '15 minutes'`) sur deux firewalls : `main` et `api_login`.

Il fallait bien le mettre sur les deux : ils ont chacun leurs compteurs séparés, donc protéger
uniquement le formulaire aurait laissé `/api/login_check` grand ouvert, et l'attaquant serait
juste passé par là.


# Exercice 10 — Rate Limiter

## Question 1 — Aucune limite sur /inscription

J'ai un script (`span_register.sh`) qui poste le formulaire en boucle avec un email différent
à chaque tour, en récupérant bien le jeton CSRF. Sans protection, il déroule jusqu'au bout :
chaque requête renvoie un 302 et un compte est créé. Autant de comptes que de tentatives,
aucun blocage.

## Question 2 — Aucune limite sur l'API

Je boucle sur `GET /api/topic` : 100 requêtes, 100 réponses en HTTP 200, aucun blocage. Même
constat que sur l'inscription.

## Question 3 — Installer le composant

`composer require symfony/rate-limiter` (il était déjà dans le `composer.json` du projet).

## Question 4 — Protéger /inscription

D'abord je déclare les limiteurs dans `config/packages/rate_limiter.yaml` :

```yaml
framework:
    rate_limiter:
        registration:
            policy: 'sliding_window'
            limit: 5                 # 5 inscriptions autorisées
            interval: '1 hour'       # sur une fenêtre glissante d'une heure
```

Puis je consomme un jeton dans `SecurityController::register()`, en identifiant par l'IP :

```php
// Exercice 10 : on compte que les envois du formulaire, pas les affichages.
if ($request->isMethod('POST')) {
    $limit = $registrationLimiter->create($request->getClientIp())->consume();

    if (!$limit->isAccepted()) {
        throw new TooManyRequestsHttpException(
            $limit->getRetryAfter()->getTimestamp() - time(),
            'Trop d\'inscriptions depuis cette adresse. Réessayez plus tard.',
        );
    }
}
```

Deux détails qui comptent :

- le `if ($request->isMethod('POST'))` : sans ça, chaque simple affichage de la page
  consommerait un jeton, et quelqu'un qui ouvre le formulaire 5 fois sans rien envoyer serait
  bloqué pour rien. On compte les soumissions, pas les visites.
- le service s'injecte par le nom de l'argument : `RateLimiterFactoryInterface
  $registrationLimiter` correspond au limiteur `registration` du yaml. Si je renomme
  l'argument, Symfony trouve plus le service.

## Question 5 — Protéger GET /api/topic

Là c'est différent : c'est pas une action précise mais toute une famille de routes générées
par API Platform (la collection, un sujet précis, etc.). Je peux pas aller mettre trois lignes
dans chaque contrôleur, vu qu'il y en a pas.

La solution c'est d'attraper la requête plus tôt, avec un listener sur `kernel.request`, et de
filtrer sur l'URL au lieu du contrôleur (`src/EventSubscriber/ApiRateLimitSubscriber.php`) :

```php
#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final readonly class ApiRateLimitSubscriber
{
    public function __construct(
        private RateLimiterFactoryInterface $apiTopicLimiter,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !str_starts_with($request->getPathInfo(), '/api/topic')) {
            return;
        }

        $limit = $this->apiTopicLimiter->create($request->getClientIp())->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(...);
        }
    }
}
```

Avec le limiteur correspondant dans le yaml :

```yaml
api_topic:
    policy: 'sliding_window'
    limit: 60                # 60 requêtes autorisées
    interval: '1 minute'     # sur une fenêtre glissante d'une minute
```

Le `isMainRequest()` sert à pas décompter deux fois quand Symfony fait une sous-requête
interne. Et comme le test porte sur le chemin, toute nouvelle route sous `/api/topic` est
protégée automatiquement, sans que j'aie à y penser.

## Question 6 — Vérifier les deux protections

Je relance les deux tests :

- `/inscription` : 5 comptes créés, puis la 6e tentative renvoie **HTTP 429** et le script
  s'arrête.
  ```
    5  HTTP 302  spam1790087975.5@example.com   compte créé
    6  HTTP 429  spam1790087975.6@example.com   BLOQUÉ (rate limit)
  ```
- `/api/topic` : les requêtes passent normalement puis la 60e renvoie **HTTP 429**.

Dans les deux cas c'est bien le code 429 (Too Many Requests), et une utilisation normale
(m'inscrire une fois, consulter quelques sujets) marche toujours sans rien voir.

## Ce que j'en retiens

Les deux exercices utilisent le même composant Rate Limiter dessous, mais pas de la même
façon :

- l'exo 9 c'est de la **config pure** : `login_throttling` est déjà branché par Symfony sur
  l'authentification, j'ai rien à écrire.
- l'exo 10 c'est **à moi de décider** où et quoi compter, parce que Symfony sait pas ce qui
  mérite d'être limité dans mon appli. D'où les deux approches : dans le contrôleur pour une
  action précise, dans un listener pour une famille de routes.
