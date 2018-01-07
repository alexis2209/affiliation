<?php
namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Kayue\WordpressBundle\Entity\Comment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class Wordpress
{

    public function __construct(ContainerInterface $container, Router $router, EntityManager $entityManager) {
        $this->container = $container;
        $this->router = $router;
        $this->em = $entityManager;
        $this->kayueManager = $this->container->get('kayue_wordpress')->getManager($container->getParameter('site_id'));
    }

    public function getCateg($id){
        $category = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id' => $id]);
        if (!is_null($category)) {
            return $category;
        }
        return false;
    }

    public function getAllPosts(){
        $articles = [];
        $posts = $this->kayueManager->getRepository('KayueWordpressBundle:Post')->findAll();
        if (!is_null($posts)) {
            foreach ($posts as $post){
                if ($post->getStatus() == 'publish'){
                    $thumbail = $this->kayueManager->getRepository('KayueWordpressBundle:Post')->findAttachmentsByPost($post);
                    if ($thumbail){
                        $image = current($thumbail)->getGuid();
                    }else{
                        $image = false;
                    }
                    $taxonomies = $post->getTaxonomies();
                    foreach ($post->getTaxonomies() as $taxonomy){
                        //var_dump($taxonomy);
                    }
                    $articles[] = [
                        'name'=>$post->getTitle(),
                        'slug'=>$this->getUrlPost($post),
                        'content'=>$post->getContent(),
                        'image'=>$image,
                        'taxonomies'=>$taxonomies,
                    ];
                }
            }
        }
        return $articles;
    }

    private function getUrlPost($post)
    {
        $url = '';
        $taxonomies = $post->getTaxonomies();
        if (count(current(current($taxonomies))) == 1) {
            $taxonomy = current(current($taxonomies));
            if ($taxonomy){
                $url .= $taxonomy->getTerm()->getSlug() . '/';
                if ($taxonomy->getParent() && $taxonomy->getParent() > 0){
                    $taxonomy1 = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id' => $taxonomy->getParent()]);
                    $url .= $taxonomy1->getTerm()->getSlug() . '/';
                    if ($taxonomy1->getParent() && $taxonomy1->getParent() > 0){
                        $taxonomy2 = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id' => $taxonomy1->getParent()]);
                        $url .= $taxonomy2->getTerm()->getSlug() . '/';
                        if ($taxonomy2->getParent() && $taxonomy2->getParent() > 0){
                            $taxonomy3 = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id' => $taxonomy2->getParent()]);
                            $url .= $taxonomy3->getTerm()->getSlug() . '/';
                        }
                    }
                }
            }
        }
        $url .= $post->getSlug();
        return $url;
    }

    public function getArticle($id){
        $article = $this->kayueManager->getRepository('KayueWordpressBundle:Post')->findOneBy(['id' => $id]);
        if (!is_null($article)) {
            return $article;
        }
        return false;
    }


    public function getThumbnailByPost($post){
        $thumbnail = $this->container->get('ekino.wordpress.manager.post')->getThumbnailPath($post);
        if ($thumbnail){
            return $thumbnail;
        }
        return false;
    }


    public function getCategs(){
        $returnCateg = [];
        $categorys = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findAll();
        if (!is_null($categorys)) {
            foreach ($categorys as $category) {
                $url = '';
                if ($category->getName() == 'category' && $category->getTerm()->getSlug() != 'non-classe') {
                    if ($category->getParent() && $category->getParent() > 0) {
                        $taxonomy1 = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id' => $category->getParent()]);
                        $url .= $taxonomy1->getTerm()->getSlug() . '/';
                        if ($taxonomy1->getParent() && $taxonomy1->getParent() > 0) {
                            $taxonomy2 = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id' => $taxonomy1->getParent()]);
                            $url .= $taxonomy2->getTerm()->getSlug() . '/';
                            if ($taxonomy2->getParent() && $taxonomy2->getParent() > 0) {
                                $taxonomy3 = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id' => $taxonomy2->getParent()]);
                                $url = $taxonomy3->getTerm()->getSlug() . '/';
                            }
                        }
                    }
                    $category->getTerm()->setSlug($url . $category->getTerm()->getSlug());
                    $returnCateg[] = $category;
                }
            }
        }
        return $returnCateg;
    }


    public function addcomment(){
        $comment = new Comment();
        $comment->setAuthor('test');
        $this->em->persist($comment);
        //$this->em->flush();
    }


    public function getPostsByCateg($id){
        $returnPosts = [];
        $category = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneById($id);;
        $posts = $category->getPosts();
        foreach ($posts as $post) {
            if ($post->getStatus() == 'publish') {
                $post->setSlug($this->getUrlPost($post));
                $returnPosts[] = $post;
            }
        }
        return $returnPosts;
    }


    public function getHeadMenu(){
        $menu = [];
        $lastOrdre = 1;
        $articlesMenu = $this->kayueManager->getRepository('KayueWordpressBundle:Post')->findBy(['type'=>'nav_menu_item'], ['menuOrder' => 'ASC']);
        if (!empty($articlesMenu)){
            $order = 0;
            $i = 0;
            foreach ($articlesMenu as $articleMenu){
                $postsMeta = $this->kayueManager->getRepository('KayueWordpressBundle:PostMeta')->findBy(['post'=>$articleMenu]);
                $metas = [];
                if (!empty($postsMeta)){
                    foreach ($postsMeta as $postMeta){
                        $metas[$postMeta->getKey()] = $postMeta->getValue();
                    }

                    if ($metas['_menu_item_object'] == 'page') {
                        $article = $this->kayueManager->getRepository('KayueWordpressBundle:Post')->findOneBy(['id' => $metas['_menu_item_object_id']]);
                        $titre = $article->getTitle();
                        $slug = $article->getTitle();
                    }elseif ($metas['_menu_item_object'] == 'post'){
                        $article = $this->kayueManager->getRepository('KayueWordpressBundle:Post')->findOneBy(['id' => $metas['_menu_item_object_id']]);
                        $titre = $article->getTitle();
                        $slug = $this->router->generate('article_'.$article->getId());
                    }elseif ($metas['_menu_item_object'] == 'category'){
                        $category = $this->kayueManager->getRepository('KayueWordpressBundle:Taxonomy')->findOneBy(['id'=>$metas['_menu_item_object_id']]);
                        $titre = $category->getTerm()->getName();
                        $slug = $this->router->generate('category_'.$category->getId());
                    }elseif ($metas['_menu_item_object'] == 'custom') {
                        $titre = $articleMenu->getTitle();
                        $slug = $metas['_menu_item_url'];
                    }


                    if ($metas['_menu_item_menu_item_parent'] > 0){
                        $menu[$lastOrdre]['ssmenu'][] = [
                            'titre' => $titre,
                            'slug' => $slug
                        ];
                    }else{
                        $lastOrdre = $articleMenu->getMenuOrder();
                        $menu[$articleMenu->getMenuOrder()] = [
                            'titre' => $titre,
                            'slug' => $slug
                        ];
                    }


                }
                $i++;
            }
        }
        return $menu;
    }


}