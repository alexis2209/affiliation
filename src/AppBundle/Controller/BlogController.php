<?php

namespace AppBundle\Controller;

use AppBundle\Services\Wordpress;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        //$posts = $this->get(Wordpress::class)->getAllPosts();



        
        // replace this example code with whatever you need
        return $this->render('AppBundle:Mondedesjouets/Blog:index.html.twig');
    }


    /**
     *
     */
    public function articleAction($postId)
    {
        $post = $this->get(Wordpress::class)->getArticle($postId);
        // replace this example code with whatever you need
        return $this->render('AppBundle:Mondedesjouets/Blog:article.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
            'post' => $post
        ));
    }



    /**
     *
     */
    public function categorieAction($categoryId)
    {
        $category = $this->get(Wordpress::class)->getCateg($categoryId);
        // replace this example code with whatever you need
        return $this->render('AppBundle:Mondedesjouets/Blog:category.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
            'category' => $category,
            'posts' => $category->getPosts()
        ));
    }

}
