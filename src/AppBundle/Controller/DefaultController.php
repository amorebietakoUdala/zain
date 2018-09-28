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
        try {
//            $isConnectable = $this->get('secit.imap')->testConnection('office365');
            $mailbox = $this->get('secit.imap')->get('office365');
            $mailsIds = $mailbox->searchMailbox('ALL');

	    $mail1 = new IncomingMailHeader();
	    $mail1->date = '2018-09-24';
	    $mail1->fromName = 'Iker Bilbao';
	    $mail1->subject = 'Prueba!!!!';
	    $mail = new IncomingMail();
	    $mail->setHeader($mail1);
	    
            // Get the first message and save its attachment(s) to disk:
	    $mails = [];
	    $mails[] = $mail;
	    
//            foreach ($mailsIds as $mailId) {
//                $mails[] = $mailbox->getMail($mailId);
//            }
            return $this->render('/mails/list.html.twig', [
                'mails' => $mails
            ]);
        } catch (\Exception $exception) {
            dump($exception);die;
            }
    }


}
