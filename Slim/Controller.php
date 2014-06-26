<?php
namespace Slim;

class Controller
{
    /**
     * @var Slim $app
     */
    public $app;

    public function __construct()
    {
        // Set the current mode
        $app = new \Slim\Slim(array(
            'mode' => 'production'
        ));

        // Define twig resource
        $app->container->singleton(
            'twig',
            function () {
                return new \Twig_Environment(
                    new \Twig_Loader_Filesystem('./resources/templates')
                );
            }
        );

        // Define model resource
        $app->container->singleton(
            'model',
            function () {
                return new Model(
                    include 'config/db.php'
                );
            }
        );

        $this->app = $app;
        $this->route();
    }

    /**
     * Routes for this controller
     */
    private function route(){
        $this->app->map('/login', array($this, 'login'))->via('GET', 'POST');
        $this->app->get('/logout', array($this, 'logout'));
        $this->app->get('/', array($this, 'guestBook'));
        $this->app->map('/ajaxpost', array($this, 'ajaxPost'))->via('POST', 'GET');
        $this->app->map('/post', array($this, 'post'))->via('GET', 'POST');
        $this->app->map('/account', array($this, 'account'))->via('POST', 'GET');
    }

    /**
     * Login
     * ToDo: use sha256 with salt, not md5
     */
    public function login()
    {
        if (!empty($_SESSION['user'])) {
            $this->app->redirect("/");
        }
        $msg = "";
        if ($this->app->request->isPost()) {
            parse_str($this->app->request->getBody(), $arr);
            $arr['password'] = $arr["password"];
            $user = $this->app->model->loginCheck($arr["username"], $arr["password"]);
            if ($user) {
                $_SESSION['user'] = $user;
                $this->app->redirect("/");
            } else {
                $msg = "Wrong login and password";
            }
        }
        echo $this->app->twig->render('login.twig', array('msg' => $msg));
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_destroy();
        $this->app->redirect('/login');
    }

    /**
     * Guest book
     */
    public function guestBook()
    {
        if (empty($_SESSION['user'])) {
            $this->app->redirect("/login");
        }
        $user = $_SESSION['user'];
        $params = $this->app->request->params();
        if(empty($params['page'])) {
            $page = 1;
        } else {
            $page = $params["page"];
        }
        list($messages, $pages) = $this->app->model->getMessages($page);
        echo $this->app->twig->render('list.twig',
            array(
                'messages' => $messages,
                "user" => $user,
                "pages" => $pages,
                "page"  => $page
            ));
    }

    /**
     *
     * @return array
     */
    public function ajaxPost()
    {
        if (empty($_SESSION['user'])) {
            return array();
        }
        $user = $_SESSION['user'];
        parse_str($this->app->request->getBody(), $data);
        $res = array("status" => $this->app->model->post($data['text'], $user->id));

        return json_encode($res);
    }

    /**
     * You can use this action instead of Ajax post
     */
    public function post()
    {
        if (empty($_SESSION['user'])) {
            $this->app->redirect("/login");
        }
        $user = $_SESSION['user'];
        $msg = '';
        if ($this->app->request->isPost()) {
            parse_str($this->app->request->getBody(), $data);
            if (!empty($data['text'])) {
                $this->app->model->post($data['text'], $user->id);
                $this->app->redirect("/");
            } else {
                $msg = "You can not publish empty message!";
            }
        }
        echo $this->app->twig->render('post.twig', array('user' => $user, 'msg' => $msg));
    }

    /**
     * Update account data
     */
    public function account(){
        if (empty($_SESSION['user'])) {
            $this->app->redirect("/login");
        }
        $msg = "";
        $status = "success";
        $user = $_SESSION['user'];
        if ($this->app->request->isPost()) {
            parse_str($this->app->request->getBody(), $data);
            $city_id = $this->app->model->getCityId($data['city'], $data['country']);
            if($city_id) {
                $data['city_id'] = $city_id;
                unset($data['country']);
                unset($data['city']);
                $flag = false;
                foreach ($data as $key => $value) {
                    if (property_exists($user, $key)) {
                        if($user->$key != $value) {
                            $user->$key = $value;
                            $flag = true;
                        }
                    }
                }
                if($flag) {
                    $res = $this->app->model->updateUserData($user);
                    if ($res == true) {
                        $msg = "Success!";
                        $status = "success";
                    } else {
                        $status = "error";
                        $msg = "Error!";
                    }
                }
            } else {
                $status = "error";
                $msg = "Error!";
            }
        }
        echo $this->app->twig->render('account.twig',
            array(
                'user' => $user,
                'msg' => $msg,
                'status' => $status,
                'countries' => $this->app->model->getCountries(),
                'location' => $this->app->model->getLocation($user->city_id)
            )
        );
    }

    /**
     * Run application
     */
    public function run(){
        $this->app->run();
    }
}
