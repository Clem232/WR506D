# TechSupport360 - API de Gestion de Tickets

API REST construite avec **Symfony 7.3** et **API Platform** pour la gestion de tickets de support technique.

## Prérequis

- Docker & Docker Compose
- PHP 8.2+ (pour le développement local sans Docker)

## Installation

### Avec Docker (recommandé)

```bash
# Cloner le projet
git clone <repository-url>
cd WR506D

# Lancer les conteneurs
docker compose -f docker-compose.yml up -d

# Ou avec Make
make start

# Se connecter au conteneur PHP
make connect

# Installer les dépendances et configurer la base
composer install
php bin/console doctrine:migrations:migrate --no-interaction
```

### Sans Docker

```bash
composer install
# Configurer la base de données dans .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console server:start
```

## 📖 Documentation de l'API

La documentation interactive Swagger/OpenAPI est disponible à l'adresse :

**➡️ [http://localhost/api](http://localhost/api)**

Elle permet de :
- Visualiser tous les endpoints disponibles
- Tester les requêtes directement depuis le navigateur
- Voir les schémas de données attendus

## 🔑 Authentification

L'API utilise un système d'authentification par **token**.

### 1. Inscription

```bash
POST /api/register
Content-Type: application/ld+json

{
  "email": "client@example.com",
  "password": "password123"
}
```

### 2. Connexion

```bash
POST /api/login
Content-Type: application/json

{
  "email": "client@example.com",
  "password": "password123"
}
```

**Réponse :**
```json
{
  "token": "eyJ...",
  "user": {
    "id": "uuid-...",
    "email": "client@example.com",
    "name": "Client",
    "role": "ROLE_CLIENT"
  }
}
```

### 3. Utiliser le token

Ajoutez le token dans le header `Authorization` de chaque requête :

```bash
Authorization: <votre-token>
```

## 👥 Rôles utilisateurs

| Rôle | Description |
|------|-------------|
| `ROLE_CLIENT` | Crée des tickets, commente ses tickets, suit l'avancement |
| `ROLE_AGENT` | Voit les tickets assignés, change leur statut, commente |
| `ROLE_ADMIN` | Gère les utilisateurs, catégories, tickets (vue globale) |
| `ROLE_SUPER_ADMIN` | Tous les droits, y compris la suppression |

## 📋 Endpoints principaux

| Méthode | Endpoint | Description | Rôle requis |
|---------|----------|-------------|-------------|
| `GET` | `/api/tickets` | Lister les tickets | Authentifié |
| `POST` | `/api/tickets` | Créer un ticket | Authentifié |
| `GET` | `/api/tickets/{id}` | Voir un ticket | Propriétaire/Admin |
| `PATCH` | `/api/tickets/{id}` | Modifier un ticket | Propriétaire/Admin |
| `DELETE` | `/api/tickets/{id}` | Supprimer un ticket | Super Admin |
| `GET` | `/api/categories` | Lister les catégories | Authentifié |
| `POST` | `/api/categories` | Créer une catégorie | Admin |
| `GET` | `/api/comments` | Lister les commentaires | Authentifié |
| `POST` | `/api/comments` | Ajouter un commentaire | Authentifié |
| `GET` | `/api/users` | Lister les utilisateurs | Authentifié |
| `GET` | `/api/version` | Version de l'API | Public |
| `POST` | `/api/register` | Inscription | Public |
| `POST` | `/api/login` | Connexion | Public |

## 🔍 Filtres et tri

### Tickets
- `?status=OPEN` — Filtrer par statut (`OPEN`, `IN_PROGRESS`, `RESOLVED`, `CLOSED`)
- `?priority=HIGH` — Filtrer par priorité (`LOW`, `MEDIUM`, `HIGH`)
- `?category.name=Bug` — Recherche partielle par nom de catégorie
- `?order[createdAt]=desc` — Tri par date de création

### Catégories
- `?onlyWithTodo=true` — N'affiche que les catégories ayant des tickets ouverts ou en cours

## 🧪 Tests

```bash
# Installer les dépendances de test
composer require --dev phpunit/phpunit symfony/browser-kit symfony/css-selector

# Lancer les tests
php bin/phpunit
```

## 🏗️ Architecture technique

- **Symfony 7.3** — Framework PHP
- **API Platform 4** — Exposition REST automatique avec Swagger
- **Doctrine ORM** — Mapping objet-relationnel avec UUID
- **MySQL 8.0** — Base de données
- **Docker** — Conteneurisation
- **PHP 8.2+** — Langage

## 📁 Structure du projet

```
src/
├── ApiResource/     # DTOs pour les opérations custom (Register, Version...)
├── Controller/      # Contrôleurs (Login, etc.)
├── Doctrine/
│   └── Extension/   # Extension Doctrine pour filtrer les tickets par utilisateur
├── Entity/          # Entités Doctrine (User, Ticket, Category, Comment)
│   └── Trait/       # Traits PHP (UuidTrait, TimestampTrait)
├── Filter/          # Filtres API Platform custom (OnlyWithTodoFilter)
├── Repository/      # Repositories Doctrine
├── Security/        # Authentification (Tokens, TokenAuthenticator)
├── State/
│   ├── Processor/   # Processors API Platform (création ticket, user, catégorie)
│   └── Provider/    # Providers API Platform (VersionProvider)
└── Validator/       # Contraintes de validation custom (MaxOpenTickets)
```

## 📝 Variables d'environnement

| Variable | Description | Défaut |
|----------|-------------|--------|
| `DATABASE_URL` | URL de connexion BDD | `mysql://root:root@mysql:3306/wr506d` |
| `APP_SECRET` | Clé secrète Symfony | — |
| `VERSION` | Numéro de version de l'API | `1.0.0` |
