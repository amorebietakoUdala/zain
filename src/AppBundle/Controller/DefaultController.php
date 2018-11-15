<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SecIT\ImapBundle\Service\Imap;
use PhpImap\Mailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailHeader;

/**
 * Description of DefaultController
 *
 * @author ibilbao
**/

class DefaultController extends Controller {

    /**
    * @Route("/", name="homepage", options={"expose" = true})
    */
    public function homeAction (Request $request){
	return $this->redirectToRoute('admin_mevent_dashboard',['_locale' => 'eu']);
    }

//    /**
//    * @Route("/{url}", name="remove_trailing_slash",
//    *     requirements={"url" = ".+\/$"})
//    */
//    public function removeTrailingSlashAction(Request $request)
//    {
//        $pathInfo = $request->getPathInfo();
//        $requestUri = $request->getRequestUri();
//
//        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
//
//        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
//        // that it does not allow changing the request method (e.g. from POST to GET)
//        return $this->redirect($url, 308);
//    }

    /**
    * @Route("/{_locale}", name="languageHome", options={"expose" = true})
    */
    public function languageHomeAction (Request $request){
	return $this->redirectToRoute('admin_mevent_dashboard',['_locale' => $request->getLocale()]);
    }
}
