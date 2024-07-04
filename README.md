# API Authentification

Une API d'authentification sécurisée construite avec Symfony 6.4 et PHP 8.2, utilisant des tokens JWT pour l'authentification et des tokens de rafraîchissement pour la gestion des tokens.

## Table des matières

1. [Aperçu](#aperçu)
2. [Fonctionnalités](#fonctionnalités)
3. [Prérequis](#prérequis)
4. [Installation](#installation)
5. [Utilisation](#utilisation)
6. [Exemples Postman](#exemples-postman)
7. [Contribuer](#contribuer)
8. [Licence](#licence)
9. [À propos de nous](#à-propos-de-nous)

## Aperçu

Ce projet fournit une API d'authentification sécurisée, stateless, permettant aux utilisateurs de s'enregistrer, de se connecter, de gérer leurs comptes, et de protéger l'API contre les attaques par force brute grâce à une gestion des tentatives et l'identification des adresses IP.

## Fonctionnalités

- Création d'un compte utilisateur
- Récupération d'un compte utilisateur
- Modification d'un compte utilisateur
- Création d'un access token à partir d'un refresh token
- Création d'un token de connection
- Confirme la validité d'un access token

## Prérequis

Avant de commencer, assurez-vous que vous avez les éléments suivants installés :

- PHP 8.2 ou supérieur
- Composer 2.7.1
- Symfony CLI (optionnel mais recommandé)
- Un serveur web (Apache 2.4.52, Nginx, etc.)
- MariaDB Ver 15.1 Distrib 10.6.18

## Installation

Pour installer et exécuter ce projet localement, suivez les étapes ci-dessous :

1. Clonez le dépôt

   ```bash
   git clone https://github.com/votre-utilisateur/api-auth.git
   cd api-auth
   ```

2. Installez les dépendances

   ```bash
   composer install
   ```

3. Configurez votre base de données dans le fichier `.env`

   ```dotenv
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.6.18"
   ```

4. Exécutez les migrations de base de données

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. Exécutez les fixtures pour pré-remplir la base de données

   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. Démarrez le serveur Symfony

   ```bash
   symfony server:start
   ```

## Utilisation

L'API est stateless, ce qui signifie qu'elle ne maintient pas d'état de session. Elle utilise des tokens JWT pour l'authentification et l'autorisation. Voici comment utiliser l'API :

### Création d'un compte utilisateur

```http
POST /api/account
Content-Type: application/json
Authorization: Bearer {your_jwt_token}

{
    "login": "test",
    "password": "test",
    "roles": ["ROLE_ADMIN"],
    "status": "open"
}
```

### Récupération d'un compte utilisateur

```http
GET /api/account/{uid}
Authorization: Bearer {your_jwt_token}
```

### Modification d'un compte utilisateur

```http
PUT /api/account/{uid}
Content-Type: application/json
Authorization: Bearer {your_jwt_token}

{
    "login": "admin",
    "password": "admin",
    "roles": ["ROLE_ADMIN"],
    "status": "closed"
}
```

### Création d'un access token à partir du refresh token

```http
POST /api/refresh-token/{refreshToken}/token
```

### Création d'un access token de connection

```http
POST /api/login
Content-Type: application/json

{
    "login": "admin",
    "password": "password"
}
```

### Confirmer la validité d'un access token

```http
GET /api/validate/{accessToken}
```

## Exemples Postman

Pour faciliter l'utilisation de cette API, nous avons inclus une collection Postman nommée `Auth.postman_collection.json` dans le dossier racine du projet. Cette collection contient des requêtes préparées pour toutes les fonctionnalités de l'API.

### Utilisation de la collection Postman

1. Importez la collection dans votre client Postman.
2. Assurez-vous que votre serveur local Symfony est en cours d'exécution.
3. Configurez les environnements nécessaires (par exemple, les variables d'environnement comme l'URL de l'API, les tokens JWT, etc.).
4. Exécutez les requêtes et observez les réponses pour comprendre le fonctionnement de l'API.

## Contribuer

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Poussez la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## À propos de nous

Ce projet a été développé dans le cadre du master à l'École Ynov par :

- Philippe LEK
- Jérémy DEJOUX
