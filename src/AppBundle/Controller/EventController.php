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
use AppBundle\Entity\Event;
use AppBundle\Entity\MonitorizableEvent;
use AppBundle\Forms\MonitorizableEventForm;
use AppBundle\Forms\EventForm;
use DateTime;
use DateInterval;

/**
 * Description of MonitorizableEventController
 *
 * @author ibilbao

/**
* @Route("/{_locale}/admin/event")
*/
class EventController extends Controller {

    
    /**
     * @Route("/{event}/mevent/new", name="admin_event_new_mevent", options={"expose" = true})
     */
    public function newAction (Request $request, Event $event){
	$em = $this->getDoctrine()->getManager();
	$mevent = new MonitorizableEvent();
	$mevent->setFilterFromEvent($event);
	$form = $this->createForm(MonitorizableEventForm::class, $mevent);
	
	$form->handleRequest($request);
	if ( $form->isSubmitted() && $form->isValid() ) {
	    $mevent = $form->getData();
	    $em->persist($mevent);
	    $em->flush();
	    $this->addFlash('success', 'messages.saved');
	    return $this->redirectToRoute('admin_mevent_try',[
		'event' => $event->getId(),
		'id' => $mevent->getId(),
	    ]);
	}
	
	return $this->render('mevent/new.html.twig', [
	    'form' => $form->createView(),
	    'event' => $event,
	]);
    }

     /**
     * @Route("/", name="admin_event_list", options={"expose" = true})
     */
    public function listAction (Request $request){
	$em = $this->getDoctrine()->getManager();
	$date = new \DateTime();
	$to = $date;
	$from = date_sub(new \DateTime(), date_interval_create_from_date_string('15 day'));
	
	$events = $em->getRepository(Event::class)->findAllFromTo([],$from,$to);
//	dump($from,$to,$events);die;
	return $this->render('event/list.html.twig', [
//	    'form' => $form->createView(),
	    'events' => $events,
	]);
    }

     /**
     * @Route("/unmatched", name="admin_event_list_unmatched", options={"expose" = true})
     */
    public function listUnmatchedAction (Request $request){
	$em = $this->getDoctrine()->getManager();
	$date = new \DateTime();
	$to = $date;
	$from = date_sub(new \DateTime(), date_interval_create_from_date_string('15 day'));
	
	$events = $em->getRepository(Event::class)->findUnmatchedEventsFromTo([],$from,$to);
//	dump($from,$to,$events);die;
	return $this->render('event/list.html.twig', [
//	    'form' => $form->createView(),
	    'events' => $events,
	]);
    }

}
