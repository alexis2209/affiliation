<?php
namespace AppBundle\Routing;

use AppBundle\Services\Wordpress;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ExtraLoader extends Loader
{
    private $loaded = false;

    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        $cat = [];

        $categorys = $this->container->get(Wordpress::class)->getCategs();
        if (!empty($categorys)){
            foreach ($categorys as $category){
                $defaults = array(
                    '_controller' => 'AppBundle:Blog:categorie',
                    'categoryId'=>$category->getId()
                );
                $requirements = [];
                $route = new Route('/' . $category->getTerm()->getSlug(), $defaults, $requirements, [], $this->container->getParameter('site_host'));

                // add the new route to the route collection
                $routeName = 'category_' . $category->getId();
                $routes->add($routeName, $route);


                $articles = $this->container->get(Wordpress::class)->getPostsByCateg($category->getId());
                if (!empty($articles)){
                    foreach ($articles as $article){
                        $defaults = array(
                            '_controller' => 'AppBundle:Blog:article',
                            'postId'=>$article->getId()
                        );
                        $requirements = [];
                        $route = new Route('/' . $category->getTerm()->getSlug() . '/' . $article->getSlug(), $defaults, $requirements, [], $this->container->getParameter('site_host'));

                        // add the new route to the route collection
                        $routeName = 'article_' . $article->getId();
                        $routes->add($routeName, $route);
                    }
                }
            }
        }

        /*$defaults = array(
            '_controller' => 'BlogBundle:Default:index',
            'categoryId'=>1
        );
        $requirements = [];
        $route = new Route('/ici-test', $defaults, $requirements);

        $routeName = 'category_';
        $routes->add($routeName, $route);*/

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }
}