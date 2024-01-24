<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of DefaultController.
 *
 * @author ibilbao
 **/
class DefaultController extends BaseController
{
    #[Route(path: '/', name: 'homepage', options: ['expose' => true])]
    public function home()
    {
        return $this->redirectToRoute('admin_mevent_dashboard', ['_locale' => 'eu']);
    }

    #[Route(path: '/{_locale}', name: 'languageHome', options: ['expose' => true])]
    public function languageHome(Request $request)
    {
        return $this->redirectToRoute('admin_mevent_dashboard', ['_locale' => $request->getLocale()]);
    }
}
