<?php
namespace Slim;

class Model
{
    // Number of messages per page
    const PER_PAGE = 5;

    /** @var \PDO Database handler */
    private $db;

    public function __construct($config)
    {
        $this->db = new \PDO(
            sprintf(
                "mysql:host=%s;dbname=%s",
                $config['host'],
                $config['name']
            ),
            $config['user'],
            $config['pass']
        );
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param string $username
     * @param string $password
     * @return object|null
     */
    public function loginCheck($username, $password)
    {
        $sql = "SELECT *
                FROM `users`
                WHERE `username` = :username
                AND `password` = MD5(:password)
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("username", $username);
        $stmt->bindParam("password", $password);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Add message to guest book
     *
     * @param string $text
     * @param int $user_id
     * @return bool
     */
    public function post($text, $user_id)
    {
        if (empty($text)) {
            return false;
        }
        $sql = "INSERT INTO `messages` (`user_id`, `text`, `created`)
                VALUES (:user_id, :text, NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("user_id", $user_id);
            $stmt->bindParam("text", $text);
            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            error_log($e->getMessage());

            return false;
        }
    }

    /**
     * Update user
     *
     * @param object $user
     * @return bool
     */
    public function updateUserData($user)
    {
        $sql = "UPDATE `users` SET
                `firstname` = :firstname,
                `lastname` = :lastname,
                `username` = :username,
                `email` = :email
                WHERE `id` = :id
                ";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("firstname", $user->firstname);
            $stmt->bindParam("lastname", $user->lastname);
            $stmt->bindParam("username", $user->username);
            $stmt->bindParam("email", $user->email);
            $stmt->bindParam("id", $user->id);
            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            error_log($e->getMessage());

            return false;
        }
    }

    /**
     * List users
     *
     * @param int $page Page number
     * @return array
     */
    public function getMessages($page = 1)
    {
        if(!$page) {
            $page = 1;
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS *, DATE_FORMAT(`created`, '%H:%i %d/%m') AS `created`
        FROM `messages`, `users`
        WHERE  `messages`.`user_id` = `users`.`id`
        ORDER BY `created` DESC LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', self::PER_PAGE, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', ($page - 1)*self::PER_PAGE, \PDO::PARAM_INT);
            $stmt->execute();
            $messages = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $total = $this->db->query('SELECT FOUND_ROWS();')->fetch(\PDO::FETCH_COLUMN);
            $numPages = $total ? ceil($total/ self::PER_PAGE) : 0;
            return array(
                $messages,
                $numPages
            );
        } catch (\PDOException $e) {
            error_log($e->getMessage());

            return array();
        }
    }

    /**
     * Get countries list
     *
     * @return array
     */
    public function getCountries(){
        $sql = "SELECT * FROM `countries` ORDER BY `name`";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log($e->getMessage());

            return array();
        }
    }

    /**
     * @param string $city        City name
     * @param int    $country_id  Country ID
     * @return int
     */
    public function getCityId($city, $country_id){
        try {
            $sql = "SELECT `id` FROM `cities` WHERE `name` = :name AND `country_id` = :country_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("name", $city);
            $stmt->bindParam("country_id", $country_id);
            $stmt->execute();
            $obj = $stmt->fetch(\PDO::FETCH_OBJ);
            if(!$obj) {
                $sql = "INSERT INTO `cities` (`name`, `country_id`) VALUES (:name, :country_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("name", $city);
                $stmt->bindParam("country_id", $country_id);
                $stmt->execute();
                return $this->db->lastInsertId();
            } else {
                return $obj->id;
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    /**
     * @param int $city_id
     * @return object|null
     */
    public function getLocation($city_id) {
        try {
            $sql = "SELECT `name` AS `cityName`, `country_id` FROM `cities` WHERE `id` = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("id", $city_id);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log($e->getMessage());

            return null;
        }
    }
}
