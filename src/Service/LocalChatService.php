<?php

namespace App\Service;

class LocalChatService
{
    private array $responses = [
        // Symfony questions
        'symfony' => 'Symfony est un framework PHP puissant et flexible pour développer des applications web modernes. Il fournit une structure MVC, un système de routage avancé, et de nombreux composants réutilisables.',
        'route' => 'Les routes dans Symfony définissent les URL de votre application. Vous pouvez les définir dans config/routes.yaml ou directement dans les contrôleurs avec les attributs #[Route].',
        'controller' => 'Un contrôleur est une classe PHP qui gère la logique applicative. Il reçoit les requêtes, traite les données, et retourne une réponse (HTML, JSON, etc).',
        'twig' => 'Twig est le moteur de templates de Symfony. Il permet de créer des templates HTML dynamiques avec une syntaxe claire et sécurisée.',
        'doctrine' => 'Doctrine est un ORM (Object Relational Mapping) qui permet de manipuler les données de la base de données en utilisant des objets PHP au lieu d\'écrire du SQL.',
        'entity' => 'Une entité est une classe PHP qui représente une table de votre base de données. Chaque propriété de la classe correspond à une colonne de la table.',
        'service' => 'Un service est une classe réutilisable qui contient la logique métier de votre application. Les services sont enregistrés dans le conteneur de dépendances.',
        'injection' => 'L\'injection de dépendances est un pattern où les dépendances d\'une classe sont passées en paramètres au lieu d\'être créées à l\'intérieur de la classe.',
        'middleware' => 'Les middlewares (événements) dans Symfony permettent d\'intervenir à différentes étapes du cycle de vie d\'une requête.',
        'validation' => 'La validation dans Symfony permet de vérifier que les données reçues respectent certaines contraintes avant d\'être traitées.',
        
        // OOP/PHP concepts
        'class' => 'Une classe est un modèle pour créer des objets. Elle définit les propriétés et les méthodes que les objets auront.',
        'objet' => 'Un objet est une instance d\'une classe. C\'est une entité dotée de propriétés (data) et de méthodes (actions).',
        'heritage' => 'L\'héritage permet à une classe d\'hériter des propriétés et méthodes d\'une autre classe (classe parent).',
        'interface' => 'Une interface définit un contrat que les classes doivent respecter. Elle déclare quelles méthodes les classes doivent implémenter.',
        'trait' => 'Un trait est un mécanisme de réutilisation de code qui permet de partager des méthodes entre plusieurs classes indépendantes.',
        'polymorphisme' => 'Le polymorphisme permet à des objets de différentes classes d\'être traités de la même manière si elles implémentent la même interface ou héritent de la même classe.',
        'abstrait' => 'Une classe abstraite ne peut pas être instantiée directement. Elle sert de modèle pour d\'autres classes qui doivent l\'étendre.',
        'static' => 'Une propriété ou méthode statique appartient à la classe elle-même, pas à une instance. Elle est accessible sans créer d\'objet.',
        
        // Database
        'sql' => 'SQL (Structured Query Language) est le langage standard pour interroger et manipuler les données dans une base de données relationnelle.',
        'mysql' => 'MySQL est un système de gestion de base de données relationnelle open-source très populaire pour les applications web.',
        'migration' => 'Une migration est un fichier PHP qui décrit les modifications à apporter à la structure de la base de données.',
        'schema' => 'Le schéma de base de données définit la structure : tables, colonnes, types de données, clés primaires et étrangères.',
        
        // Web concepts
        'http' => 'HTTP est le protocole fondamental du web. Il définit comment les requêtes et réponses sont transmises entre le client (navigateur) et le serveur.',
        'get' => 'GET est une méthode HTTP utilisée pour récupérer des données. Les paramètres sont visibles dans l\'URL.',
        'post' => 'POST est une méthode HTTP utilisée pour envoyer des données au serveur. Les données sont envoyées dans le corps de la requête.',
        'json' => 'JSON (JavaScript Object Notation) est un format léger pour l\'échange de données. Il utilise des paires clé-valeur.',
        'rest' => 'REST (Representational State Transfer) est un style d\'architecture pour créer des APIs en utilisant les méthodes HTTP standard.',
        'api' => 'Une API (Application Programming Interface) permet à des applications de communiquer entre elles en échangeant des données.',
        'cookie' => 'Un cookie est une petite données stockée sur le navigateur du client et envoyée à chaque requête au serveur.',
        'session' => 'Une session est un mécanisme pour maintenir l\'état de l\'utilisateur sur plusieurs requêtes HTTP.',
        
        // Security
        'auth' => 'L\'authentification est le processus de vérifier l\'identité d\'un utilisateur (connexion). L\'autorisation détermine ce qu\'il peut faire.',
        'password' => 'Les mots de passe doivent toujours être hachés avant d\'être stockés en base de données pour des raisons de sécurité.',
        'csrf' => 'CSRF (Cross-Site Request Forgery) est une attaque web. Symfony fournit une protection avec des tokens CSRF automatiques.',
        'cors' => 'CORS (Cross-Origin Resource Sharing) permet à une application web d\'accéder à des ressources depuis un domaine différent.',
        'ssl' => 'SSL/TLS est un protocole de chiffrement qui sécurise les communications entre le client et le serveur (HTTPS).',
        
        // Testing
        'test' => 'Les tests permettent de vérifier que votre code fonctionne correctement. Il existe des tests unitaires, d\'intégration et fonctionnels.',
        'phpunit' => 'PHPUnit est le framework de test le plus populaire en PHP. Symfony l\'intègre par défaut.',
        'mock' => 'Un mock est un objet simulé utilisé dans les tests pour remplacer les vraies dépendances.',
        
        // Default fallback responses
        'default' => 'Je suis un assistant de quiz intelligent. Posez-moi des questions sur : Symfony, PHP, OOP, bases de données, concepts web, sécurité, et testing. Comment puis-je vous aider?',
    ];

    public function chat(string $message): string
    {
        $message = strtolower(trim($message));
        
        // Remove common prefixes
        $cleaned = preg_replace('/^(c\'est quoi|qu\'est-ce que|expliquez|parlez-moi de|dis-moi sur|c quoi|koi|what is|what\'s)\s+/i', '', $message);
        
        // Search for keywords
        foreach (array_keys($this->responses) as $keyword) {
            if (stripos($cleaned, $keyword) !== false) {
                return $this->responses[$keyword];
            }
        }
        
        // If no keyword matches, return a helpful default response
        return $this->responses['default'];
    }
}
