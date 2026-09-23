## Exercices 13

1. Composer audit : Found 9 security vulnerability advisories affecting 4 packages:
3. composer update "symfony/*" --with-all-dependencies
4. Les packets abandonnés posent problème uniquement cas de montée de version.
5. Intégrer le composer audit dans le ci/cd et bloquer le déploiement en cas de problème.
6. Il vaut mieux utiliser des packages utilisés massivement. Vérifier régulièrement les failles avec l'audit, et faire
   régulièrement les montées de version.

## Exercices 14

1. Un caractère, l'espace fonctionne
2. Stockage sécurisé : réalisé dans le contrôleur avec le PasswordHasher
3. Lettres minuscules
   Lettres majuscules
   Chiffres
   Caractères spéciaux
   Pas de limitation (clavier AZERTY standard)
   Limité à caractères spéciaux.
4. Le formulaire de connexion n'a aucune contrainte de saisie. En théorie il y aurait aussi un page de réinitialisation
   de mot de passe ou modification de compte.
7. Double authentification, changement régulier du mdp. 
