<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
    	$mocksms = "something.funny Hi! We have a meeting tomorrow at 6pm. Thanks. Ignore this message";
    	$data = [];
    	$safeMocksms = str_replace(" ", "+", $mocksms);
    	$data['mocksms'] = $safeMocksms;
        return $this->render('default/index.html.twig', $data);
    }
}
