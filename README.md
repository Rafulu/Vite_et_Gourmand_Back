Vite & Gourmand — Déploiement local
Prérequis

Docker installé et démarré
Docker Compose (inclus avec Docker Desktop)
Git


Installation
1. Cloner le dépôt
bashgit clone https://github.com/Rafulu/Vite_et_Gourmand_Back.git
cd Vite_et_Gourmand_Back
2. Installer les dépendances PHP
bashcomposer install
3. Lancer les conteneurs Docker
Depuis la racine du projet (là où se trouve le docker-compose.yml) :
bashdocker-compose up -d
Cela démarre trois services :

app — PHP 8.2 + Apache (port 8000)
mariadb — Base de données relationnelle (port 3306)
mongodb — Base de données NoSQL (port 27017)

4. Initialiser la base de données
Importer les fichiers SQL dans cet ordre via phpMyAdmin (http://localhost:8080) ou en ligne de commande :
bashdocker exec -i mariadb mysql -u jose -padmin vite-et-gourmand < sql/schema.sql
docker exec -i mariadb mysql -u jose -padmin vite-et-gourmand < sql/data.sql
5. Accéder à l'application
ServiceURLApplicationhttp://localhost:8000phpMyAdminhttp://localhost:8080

Comptes de test
RôleEmailMot de passeAdministrateuradmin@test.com(voir copie)Managermanager@test.compasswordCuisiniercook@test.compasswordLivreurdriver@test.compasswordClientclient@test.compassword

Structure du projet
back/
├── config/
│   ├── database.php        ← Connexion MariaDB
│   └── mongodb.php         ← Connexion MongoDB
├── public/
│   └── index.php           ← Point d'entrée unique (routeur)
├── src/
│   ├── controllers/        ← AuthController, MenuController, OrderController...
│   ├── models/             ← UserModel, MenuModel, OrderModel...
│   ├── helpers/            ← SecurityHelper, MongoDBHelper
│   └── views/
│       ├── partials/       ← navbar, footer, head, scripts
│       ├── client/         ← login, register, account, order-form...
│       ├── employee/       ← dashboard, orders-management, order-detail...
│       └── admin/          ← dashboard-admin, employees-management...
├── sql/
│   ├── schema.sql          ← Structure de la base de données
│   └── data.sql            ← Données de test
├── Dockerfile
└── composer.json

Variables d'environnement
Les variables de connexion sont définies dans config/database.php et config/mongodb.php. En production, elles doivent être passées via des variables d'environnement :
DB_HOST
DB_NAME
DB_USER
DB_PASSWORD
MONGODB_URI
MONGODB_DB