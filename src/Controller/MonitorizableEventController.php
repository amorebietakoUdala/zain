<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Forms\MonitorizableEventForm;
use App\Entity\MonitorizableEvent;
use App\Forms\EventSearchForm;
use App\Forms\EventTryForm;
use App\Entity\Event;
use App\Forms\EventForm;
use App\Repository\EventRepository;
use App\Repository\MonitorizableEventRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of MonitorizableEventController.
 *
 * @author ibilbao
 */

/**
 * @Route("/{_locale}/admin/mevent")
 */
class MonitorizableEventController extends AbstractController
{
    private EntityManagerInterface $em;
    private EventRepository $eventRepo;
    private MonitorizableEventRepository $meventsRepo;

    public function __construct(EntityManagerInterface $em, EventRepository $eventRepo, MonitorizableEventRepository $meventsRepo)
    {
        $this->em = $em;
        $this->eventRepo = $eventRepo;
        $this->meventsRepo = $meventsRepo;
    }

    /**
     * @Route("/", name="admin_mevent_list", options={"expose" = true})
     */
    public function listAction(Request $request)
    {
        $mevents = $this->meventsRepo->findAll();

        return $this->render('/mevent/list.html.twig', [
            'mevents' => $mevents,
        ]);
    }

    /**
     * @Route("/dashboard", name="admin_mevent_dashboard", options={"expose" = true})
     */
    public function dashBoardAction(Request $request)
    {
        $mevents = $this->meventsRepo->findAll();
        $counters = $this->__countStatusTypes($mevents);

        return $this->render('/mevent/dashboard.html.twig', [
            'mevents' => $mevents,
            'counters' => $counters,
        ]);
    }

    /**
     * @Route("/new", name="admin_mevent_new", options={"expose" = true})
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(MonitorizableEventForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mevent = $form->getData();

            $this->em->persist($mevent);
            $this->em->flush();

            $this->addFlash('success', 'messages.saved');

            return $this->redirectToRoute('admin_mevent_list');
        }

        return $this->render('mevent/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_mevent_edit", options={"expose" = true})
     */
    public function editAction(Request $request, MonitorizableEvent $mevent)
    {
        $form = $this->createForm(MonitorizableEventForm::class, $mevent);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mevent = $form->getData();
            $this->em->persist($mevent);
            $this->em->flush();
            $this->addFlash('success', 'messages.saved');
            return $this->redirectToRoute('admin_mevent_list');
        }

        return $this->render('mevent/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="admin_mevent_delete", options={"expose" = true})
     */
    public function deleteAction(Request $request, MonitorizableEvent $mevent)
    {
        $events = $mevent->getEvents();
        foreach ($events as $event) {
            $event->setMonitorizableEvent(null);
            $this->em->persist($event);
        }
        $this->em->remove($mevent);
        $this->em->flush();
        $this->addFlash('success', 'messages.deleted');

        return $this->redirectToRoute('admin_mevent_list');
    }

    /**
     * @Route("/{id}/try/{event}/save", name="admin_mevent_try_save", options={"expose" = true})
     */
    public function tryEventSaveAction(Request $request, MonitorizableEvent $mevent, Event $event)
    {
        $form = $this->createForm(EventTryForm::class, $mevent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($mevent);
            $this->em->flush();

            $this->addFlash('success', 'messages.saved');

            return $this->redirectToRoute('admin_mevent_try', [
                'id' => $mevent->getId(),
                'event' => $event->getId(),
            ]);
        }

        return $this->render('mevent/_tryEvent.html.twig', [
            'form' => $form->createView(),
            'mevent' => $mevent,
            'event' => $event,
        ]);
    }

    /**
     * @Route("/{id}/try/{event}", name="admin_mevent_try", options={"expose" = true})
     */
    public function tryEventAction(Request $request, MonitorizableEvent $mevent, Event $event)
    {
        // TODO try Monitorizable Events and Events
        $form = $this->createForm(EventTryForm::class, $mevent);
        //	dump($mevent, $event);die;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $patternSuccess = $this->__buildRexEpr($criteria->getSuccessCondition());
            $patternFailure = $this->__buildRexEpr($criteria->getFailureCondition());
            $target = $event->getSubject() . $event->getDetails();
            $findSuccess = 0;
            if (null != $criteria->getSuccessCondition()) {
                $findSuccess = preg_match($patternSuccess, $target, $matchSuccess);
            }
            $findFailure = 0;
            if (null != $criteria->getFailureCondition()) {
                $findFailure = preg_match($patternFailure, $target, $matchFailure);
            }

            return $this->render('mevent/_tryEvent.html.twig', [
                'form' => $form->createView(),
                'mevent' => $criteria,
                'event' => $event,
                'findSuccess' => $findSuccess,
                'findFailure' => $findFailure,
            ]);
        }

        return $this->render('mevent/_tryEvent.html.twig', [
            'form' => $form->createView(),
            'mevent' => $mevent,
            'event' => $event,
        ]);
    }

    /**
     * @Route("/{id}/match/{event}", name="admin_mevent_match", options={"expose" = true})
     */
    public function matchAction(Request $request, MonitorizableEvent $mevent, Event $event)
    {
        $event->setMonitorizableEvent($mevent);
        $this->em->persist($event);
        $this->em->flush();

        $this->addFlash('success', 'messages.eventMatched');

        return $this->redirectToRoute('admin_mevent_search', [
            'id' => $mevent->getId(),
        ]);
    }

    /**
     * @Route("/{id}/search/save", name="admin_mevent_save_filter", options={"expose" = true})
     */
    public function saveFilterAction(Request $request, MonitorizableEvent $mevent)
    {
        $form = $this->createForm(EventSearchForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $mevent->setFilterCondition(http_build_query($criteria));
            $this->em->persist($mevent);
            $this->em->flush();
            $this->addFlash('success', 'messages.saved');
            return $this->redirectToRoute('admin_mevent_list');
        }

        return $this->render('mevent/_searchEvents.html.twig', [
            'form' => $form->createView(),
            'mevent' => $mevent,
        ]);
    }

    /**
     * @Route("/{id}/search", name="admin_mevent_search", options={"expose" = true})
     */
    public function searchAction(Request $request, MonitorizableEvent $mevent)
    {
        parse_str($mevent->getFilterCondition(), $criteria);
        $form = $this->createForm(EventSearchForm::class, $criteria);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $from = array_key_exists('dateFrom', $criteria) && !empty($criteria['dateFrom']) ? $criteria['dateFrom'] : null;
            $to = array_key_exists('dateFrom', $criteria) && !empty($criteria['dateFrom']) ? $criteria['dateTo'] : null;
            $criteria_without_blanks = $this->_remove_from_to($criteria);
            $events = $this->eventRepo->findAllFromTo($criteria_without_blanks, $from, $to);

            return $this->render('mevent/_searchEvents.html.twig', [
                'form' => $form->createView(),
                'events' => $events,
                'mevent' => $mevent,
            ]);
        }

        parse_str($mevent->getFilterCondition(), $criteria);
        $date = new \DateTime();
        // Only last 5 days
        $from = array_key_exists('dateFrom', $criteria) && !empty($criteria['dateFrom']) ? $criteria['dateFrom'] : date_sub(new \DateTime(), date_interval_create_from_date_string('5 days'));
        $to = array_key_exists('dateTo', $criteria) && !empty($criteria['dateTo']) ? $criteria['dateTo'] : $date;
        $criteria_without_blanks = $this->_remove_from_to($criteria);
        $events = $this->eventRepo->findAllFromTo($criteria_without_blanks, $from, $to);

        $this->addFlash('success', 'messages.only_last_5_days');

        return $this->render('mevent/_searchEvents.html.twig', [
            'form' => $form->createView(),
            'mevent' => $mevent,
            'events' => $events,
        ]);
    }

    /**
     * @Route("/{id}/lastMatchedEvent", name="admin_mevent_lastMatchedEvent", options={"expose" = true})
     */
    public function lastMatchedEventAction(MonitorizableEvent $mevent)
    {
        $event = $this->eventRepo->findLastMatchedEvent($mevent);
        $form = $this->createForm(EventForm::class, $event);

        return $this->render('event/show.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_mevent_show", options={"expose" = true})
     */
    public function showAction(MonitorizableEvent $mevent)
    {
        $form = $this->createForm(MonitorizableEventForm::class, $mevent);

        return $this->render('mevent/show.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function _remove_from_to($criteria)
    {
        $from = null;
        $to = null;
        if (array_key_exists('dateFrom', $criteria)) {
            $from = $criteria['dateFrom'];
            $criteria['dateFrom'] = null;
        }
        if (array_key_exists('dateTo', $criteria)) {
            $to = $criteria['dateTo'];
            $criteria['dateTo'] = null;
        }
        $new_criteria = $this->_remove_blank_filters($criteria);

        return $new_criteria;
    }

    private function _remove_blank_filters($criteria)
    {
        $new_criteria = [];
        foreach ($criteria as $key => $value) {
            if (!empty($value)) {
                $new_criteria[$key] = $value;
            }
        }

        return $new_criteria;
    }

    private function __buildRexEpr($string)
    {
        $reg_exp = '/^(.*)';
        $string_space = preg_replace("/\ /", "\s", preg_quote($string));

        return $reg_exp . $string_space . '(.*)/iAs';
    }

    /**
     * @param array $mevents
     *
     * @return array $counters
     */
    private function __countStatusTypes(array $mevents)
    {
        $counters = [
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_SUCCESS] => 0,
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_FAILURE] => 0,
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_UNDEFINED] => 0,
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_EXPIRED] => 0,
        ];
        foreach ($mevents as $mevent) {
            $lastStatus = $mevent->testLastStatus();
            if (MonitorizableEvent::STATUS_SUCCESS == $lastStatus) {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_SUCCESS]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_SUCCESS]] + 1;
            } elseif (MonitorizableEvent::STATUS_FAILURE == $lastStatus) {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_FAILURE]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_FAILURE]] + 1;
            } elseif (MonitorizableEvent::STATUS_UNDEFINED == $lastStatus) {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_UNDEFINED]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_UNDEFINED]] + 1;
            } else {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_EXPIRED]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_EXPIRED]] + 1;
            }
        }

        return $counters;
    }
}
