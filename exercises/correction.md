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



