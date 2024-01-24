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
use App\Repository\EventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/{_locale}/admin/event')]
class EventController extends BaseController
{

    public function __construct(
        private readonly EntityManagerInterface $em, 
        private readonly EventRepository $repo
    )
    {
    }

    #[Route(path: '/{event}/mevent/new', name: 'admin_event_new_mevent', options: ['expose' => true])]
    public function new(Request $request, Event $event)
    {
        $this->loadQueryParameters($request);
        $mevent = new MonitorizableEvent();
        $mevent->setFilterFromEvent($event);
        $form = $this->createForm(MonitorizableEventForm::class, $mevent);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mevent = $form->getData();
            $this->em->persist($mevent);
            $this->em->flush();
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

    #[Route(path: '/', name: 'admin_event_list', options: ['expose' => true])]
    public function list(Request $request)
    {
        $this->loadQueryParameters($request);
        $date = new DateTime();
        $to = $date;
        $from = date_sub(new DateTime(), date_interval_create_from_date_string('15 day'));
        $events = $this->repo->findAllFromTo([], $from, $to);
        $this->addFlash('warning', 'messages.onlyEventsOfLast15Days');
        return $this->render('event/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route(path: '/unmatched', name: 'admin_event_list_unmatched', options: ['expose' => true])]
    public function listUnmatched(Request $request)
    {
        $this->loadQueryParameters($request);
        $date = new DateTime();
        $to = $date;
        $from = date_sub(new DateTime(), date_interval_create_from_date_string('15 day'));
        $events = $this->repo->findUnmatchedEventsFromTo([], $from, $to);
        return $this->render('event/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route(path: '/{event}', name: 'admin_event_get', options: ['expose' => true])]
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
