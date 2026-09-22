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

