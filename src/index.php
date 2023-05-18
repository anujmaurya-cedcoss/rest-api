<?php

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Collection\Manager;

define("BASE_PATH", (__DIR__));
require_once(BASE_PATH . '/vendor/autoload.php');


// Use Loader() to autoload our model
$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client(
            'mongodb+srv://root:9SoCvPuQHy0SMXn1@cluster0.nwpyx9q.mongodb.net/?retryWrites=true&w=majority'
        );
        return $mongo->rest_api;
    },
    true
);
$container->set(
    'collectionManager',
    function () {
        return new Manager();
    }
);
$app = new Micro($container);
// Define the routes here

// Retrieves all movies
$app->get(
    '/api/movies',
    function () {
        $collection = $this->mongo->movies;
        $movieList = $collection->find();
        $data = [];

        foreach ($movieList as $movie) {
            $data[] = [
                'id' => $movie['id'],
                'name' => $movie['name'],
            ];
        }
        echo json_encode($data);
    }
);

// Searches for movies with $name in their name
$app->get(
    '/api/movies/search/{name}',
    function ($name) {
        $collection = $this->mongo->movies;
        $movieList = $collection->find(["name" => $name]);

        $data = [];

        foreach ($movieList as $movie) {
            $data[] = [
                'id' => $movie['id'],
                'name' => $movie['name'],
            ];
        }
        echo json_encode($data);
    }
);

// Retrieves robots based on key
$app->get(
    '/api/movies?{id:[0-9]+}',
    function ($id) {
        // echo $id; die;
        $collection = $this->mongo->movies;
        $movieList = $collection->findOne(["id" => $id]);
        echo "<pre>";
        print_r($movieList);
        die;
        $data = [];

        foreach ($movieList as $movie) {
            $data[] = [
                'id' => $movie['id'],
                'name' => $movie['name'],
            ];
            print_r($movie);
            die;
        }
        echo json_encode($data);
    }
);

// Adds a new movie
$app->post(
    '/api/movies',
    function () use ($app) {
        $movie = $app->request->getJsonRawBody();
        $collection = $this->mongo->movies;
        $arr = [
            "name" => $movie->name,
            "genre" => $movie->genre,
            "id" => $movie->id
        ];
        $status = $collection->insertOne($arr);
        return var_dump($status);
    }
);

// update the movie
$app->put(
    '/api/movies/{id:[0-9]+}',
    function ($id) use ($app) {
        $movie = $app->request->getJsonRawBody();
        $response = $this->mongo->movies->updateOne(['id' => (int) $id], ['$set' => ['name' => $movie->name]]);
        return $response;
    }
);

// Deletes robots based on primary key
$app->delete(
    '/api/movies/{id:[0-9]+}',
    function ($id) use ($app) {
        $response = $this->mongo->movies->deleteOne(["id" => (int) $id]);
        return $response;
    }
);

$app->handle($_SERVER['REQUEST_URI']);