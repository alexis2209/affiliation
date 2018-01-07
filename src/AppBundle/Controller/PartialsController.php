<?php

namespace AppBundle\Controller;

use AppBundle\Services\Wordpress;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PartialsController extends Controller
{
    public function headerAction()
    {
        $menu = $this->get(Wordpress::class)->getHeadMenu();
        return $this->render('AppBundle:Mondedesjouets:header.html.twig', array(
            'menu' => $menu
        ));
    }

    public function footerAction()
    {
        return $this->render('AppBundle:Mondedesjouets:footer.html.twig', array());
    }

    public function sidebarAction()
    {
        return $this->render('AppBundle:Mondedesjouets:sidebar.html.twig', array());
    }

}
