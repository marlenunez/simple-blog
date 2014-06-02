<?php
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

// turn on the debug mode to ease debugging
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(
        __DIR__ . '/../views',
        __DIR__.'/../vendor/twitter/bootstrap/dist/css'
        )
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbhost' => 'localhost',
        'dbname' => 'blog',
        'user' => 'root',
        'password' => '',
    ),
));

// global for 5 recent posts
$recent_posts = $app['db']->fetchAll("SELECT * FROM posts LIMIT 5");
$app['twig']->addGlobal("recent_posts", $recent_posts);

// global for archive
$archived_posts = $app['db']->fetchAll("SELECT DATE_FORMAT(creationdate, '%b %Y') AS 'date', COUNT(id) as count FROM posts GROUP BY YEAR(creationdate), MONTH(creationdate)");
$app['twig']->addGlobal("archived_posts", $archived_posts);


use Symfony\Component\HttpFoundation\Request;

$app->get( '/', function() use ($app) {

    $posts = $app['db']->fetchAll("SELECT * FROM posts LIMIT 10");

    return $app['twig']->render('blog_home.twig', array(
        'posts' => $posts
    ));
});

$app->get('/post/{id}', function ($id) use ($app) {

    $sql = "SELECT * FROM posts WHERE id = ?";
    $posts = $app['db']->fetchAll($sql, array((int) $id));

    return $app['twig']->render('blog_post.twig', array(
        'posts' => $posts
    ));
});

$app->get('/archive/{year}/{month}', function ($year, $month) use ($app) {

    $sql = "SELECT * FROM posts WHERE creationdate = ?";
    $posts = $app['db']->fetchAll($sql, array((timestamp) $creationdate));

    return $app['twig']->render('blog_archive.twig', array(
        'year' => $year, 
        'month' => $month
    ));
});

$app->run();
