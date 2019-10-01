<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of DefaultController.
 *
 * @author ibilbao
 **/
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage", options={"expose" = true})
     */
    public function homeAction(Request $request)
    {
        return $this->redirectToRoute('admin_mevent_dashboard', ['_locale' => 'eu']);
    }

    /**
     * @Route("/{_locale}", name="languageHome", options={"expose" = true})
     */
    public function languageHomeAction(Request $request)
    {
        return $this->redirectToRoute('admin_mevent_dashboard', ['_locale' => $request->getLocale()]);
    }
}
