# ToDoAndCo - Gestionnaire de tâches

Amélioration et documentation d'un projet existant ToDo & Co.

--- 

## Prérequis

- PHP 8.0 ou supérieur
- Composer
- MySQL 8.0.32 ou supérieur
- Extension PHP : 
  - PDO-MySQL
  - XDebug (pour les tests)
- Symfony CLI (optionnel, pour le serveur de développement)

## Installation

1. Clonez le repository GitHub dans le dossier voulu :
git clone https://github.com/LudGold/P8_ToDoAndCo_Optimisation.git

cd P8_ToDoAndCo_Optimisation

Configurez vos variables d'environnement :

Copiez le fichier .env en .env.local
Modifiez les valeurs dans .env.local selon votre environnement
Pour les tests, configurez également .env.test si nécessaire


Installez les dépendances :

composer install

Créez la base de données :

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

(Optionnel) Chargez les fixtures pour avoir des données de démonstration :

php bin/console doctrine:fixtures:load
Environnement de développement
Lancez le serveur de développement :
symfony serve

L'application est maintenant accessible à l'adresse : http://localhost:8000
Tests
Préparation de l'environnement de test

Créez la base de données de test :

php bin/console doctrine:database:create --env=test

Créez le schéma de la base de test :

php bin/console doctrine:migrations:migrate --env=test

Chargez les fixtures de test :

php bin/console doctrine:fixtures:load --env=test
Exécution des tests

Lancer la suite de tests :

php bin/phpunit

Générer un rapport de couverture de code :

php bin/phpunit --coverage-html var/coverage

Voilà, vous avez tous les éléments pour pouvoir profiter de To Do & Co!
