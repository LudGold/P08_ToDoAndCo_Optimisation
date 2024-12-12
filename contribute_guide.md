# Guide pour contribuer au projet ToDoAndCo

## Objectif
Ce guide décrit les règles et bonnes pratiques pour collaborer efficacement sur le projet **ToDo & Co**

- Comment contribuer au projet via GitHub.
- Les étapes à suivre pour garantir la qualité du code.
- Les règles de collaboration pour une équipe structurée.

---

## 1. Collaboration via GitHub

### 1.1 Structure des branches
Le projet utilise une structure de branches claire pour organiser le développement :

#### Branches principales
- **`main`** : Branche de production. Elle contient le code validé et prêt pour la mise en production.
  - ⚠️ **Ne pas pousser directement sur `main`.**
- **`develop`** : Branche pour l’intégration des nouvelles fonctionnalités en cours de développement.

#### Branches secondaires
- **`feature/`** : Une branche par fonctionnalité ou amélioration.
  - Exemple : `feature/authentication`
- **`bugfix/`** : Une branche par correction de bug.
  - Exemple : `bugfix/fix-login-error`
- **`hotfix/`** : Une branche pour les correctifs critiques en production.

### 1.2 Politique de commits
Les commits doivent être :

- **Descriptifs** : Expliquez clairement ce que vous avez modifié.
- **Structurés** :
  - Exemple : `fix: correction du bug de connexion`
  - Exemple : `feat: ajout de la fonctionnalité de réinitialisation de mot de passe`

### Exemple de workflow GitHub
1. Créer une nouvelle branche à partir de `develop` :
   ```bash
   git checkout develop
   git checkout -b feature/ma-nouvelle-fonctionnalite
   ```
2. Développer votre fonctionnalité ou correction.
3. Faire un commit et pousser la branche :
   ```bash
   git add .
   git commit -m "feat: description de votre fonctionnalité"
   git push origin feature/ma-nouvelle-fonctionnalite
   ```
4. Créer une **Pull Request (PR)** vers `develop`.

---

## 2. Processus pour contribuer

### 2.1 Création d’une nouvelle fonctionnalité
1. Créez une branche à partir de `develop`.
2. Implémentez la fonctionnalité.
3. Écrivez des **tests unitaires** et/ou **fonctionnels** pour valider votre code.
4. Passez les vérifications de code (linting, tests).
5. Ouvrez une Pull Request vers `develop` et attendez la revue.

### 2.2 Revue de code
#### Processus :
- Chaque PR doit être revue par **au moins un autre développeur senior**.
- Les critères de validation :
  - Qualité du code.
  - Respect des standards de codage.
  - Couverture des tests suffisante.
  - Performances et sécurité.

#### Fusion :
- Après validation, la branche est fusionnée dans `develop`.

---

## 3. Standards de qualité

### 3.1 Conventions de codage
- Respectez les standards **PSR** pour PHP.
- Utilisez **PHP_CodeSniffer** pour vérifier les standards.
- Configuration via `.php-cs-fixer.php`.
- Indentation : 4 espaces.

### 3.2 Outils de qualité
- **PHPStan** : Analyse statique du code.
- **PHPUnit** : Tests unitaires.
- **Couverture des tests minimale** : 70%.
- **Codacy** : Outil pour surveiller la qualité du code.

### 3.3 Documentation du code
- Utilisez des commentaires PHPDoc pour toutes les classes et méthodes.
- Maintenez le fichier `README.md` à jour dans chaque module.

---

## 4. Gestion des dépendances
- Mettez régulièrement à jour les dépendances via **Composer**.
- Assurez-vous que les dépendances n’introduisent pas de vulnérabilités.

---

## 5. Communication et outils

### Outils recommandés
- **Jira** : Gestion des tickets et des tâches.
- **Slack** : Communication directe.
- **GitHub** : Gestion du code et des PR.
- **Copilot/ChatGPT** : Assistance au développement.

---

## Notes importantes
- Respectez les deadlines et communiquez avec l’équipe en cas de blocage.
- Documentez chaque nouvelle fonctionnalité ou mise à jour majeure.

En suivant ces étapes, vous contribuerez efficacement au projet **ToDoAndCo** tout en garantissant la qualité et la pérennité du code.