<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Entity\Event;
use App\Entity\MonitorizableEvent;
use App\Forms\MonitorizableEventForm;
use DateTime;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of MonitorizableEventController.
 *
 * @author ibilbao
*/

 /**
 * @Route("/{_locale}/admin/event")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/{event}/mevent/new", name="admin_event_new_mevent", options={"expose" = true})
     */
    public function newAction(Request $request, Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $mevent = new MonitorizableEvent();
        $mevent->setFilterFromEvent($event);
        $form = $this->createForm(MonitorizableEventForm::class, $mevent);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mevent = $form->getData();
            $em->persist($mevent);
            $em->flush();
            $this->addFlash('success', 'messages.saved');

            return $this->redirectToRoute('admin_mevent_try', [
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
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $date = new DateTime();
        $to = $date;
        $from = date_sub(new DateTime(), date_interval_create_from_date_string('15 day'));

        $events = $em->getRepository(Event::class)->findAllFromTo([], $from, $to);

        return $this->render('event/list.html.twig', [
            'events' => $events,
    ]);
    }

    /**
     * @Route("/unmatched", name="admin_event_list_unmatched", options={"expose" = true})
     */
    public function listUnmatchedAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $date = new DateTime();
        $to = $date;
        $from = date_sub(new DateTime(), date_interval_create_from_date_string('15 day'));

        $events = $em->getRepository(Event::class)->findUnmatchedEventsFromTo([], $from, $to);

        return $this->render('event/list.html.twig', [
        'events' => $events,
    ]);
    }

    /**
     * @Route("/{event}" ,name="admin_event_get", options={"expose" = true} )
     */
    public function getEvent(Event $event, SerializerInterface $serializer)
    {
        $json = $serializer->serialize($event, 'json');

        return new Response(
            $json,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

}
