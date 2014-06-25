<?php
require_once 'vendor/autoload.php';

//session_cache_limiter(false);
//session_start();

\Slim\Slim::registerAutoloader();

// Set the current mode
$app = new \Slim\Slim(array(
    'mode' => 'production'
));

// Define twig resource
$app->container->singleton('twig', function () {
        return new Twig_Environment(
            new Twig_Loader_Filesystem('./resources/templates')
        );
    });

// Define model resource
$app->container->singleton('model', function () {
        return new Model(
                include 'config/db.php'
            );
    });

$app->get('/', function() use ($app) {
        $users = $app->model->listSubscribers();
        echo $app->twig->render('list.twig', array('users' => $users));
    });

$app->post('/subscribe', function() use ($app) {
        $user = json_decode($app->request->getBody());
        $app->model->addSubscriber($user->name, $user->email);
        echo $app->twig->render('subscribe.twig');
});


class Model {

    /** @var \PDO Database handler */
    private $db;

    public function __construct($config)
    {
        $this->db = new PDO(
            sprintf("mysql:host=%s;dbname=%s",
                $config['host'],
                $config['name']),
            $config['user'],
            $config['pass']
        );
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Add user
     *
     * @param string $name
     * @param string $email
     */
    public function addUser($name, $email){
        $sql = "INSERT INTO `users` (`name`, `email`)
                VALUES (:name, :email)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("name", $name);
            $stmt->bindParam("email", $email);
            $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    /**
     * List users
     *
     * @return array
     */
    public function listSubscribers(){
        $sql = "SELECT * FROM `users` ORDER BY `name`";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return array();
        }
    }
}

$app->run();
