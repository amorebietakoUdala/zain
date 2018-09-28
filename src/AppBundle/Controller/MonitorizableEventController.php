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
use AppBundle\Forms\MonitorizableEventForm;
use AppBundle\Entity\MonitorizableEvent;

/**
 * Description of MonitorizableEventController
 *
 * @author ibilbao

/**
* @Route("/{_locale}/admin/mevent")
*/

class MonitorizableEventController extends Controller {

     /**
     * @Route("/", name="admin_mevent_list", options={"expose" = true})
     */
    public function listAction (Request $request){
//	$user = $this->get('security.token_storage')->getToken()->getUser();
	$em = $this->getDoctrine()->getManager();
//	$bilatzaileaForm = $this->createForm(HabitanteBilatzaileaForm::class, [
////	    'role' => $user->getRoles(),
////	    'locale' => $request->getLocale(),
//	]);
//	$bilatzaileaForm->handleRequest($request);
//	if ( $bilatzaileaForm->isSubmitted() && $bilatzaileaForm->isValid() ) {
//	    $consulta_habitante = $this->_remove_blank_filters($bilatzaileaForm->getData());
	    $mevents = $em->getRepository("AppBundle:MonitorizableEvent")->findAll();
	    return $this->render('/mevent/list.html.twig', [
		'mevents' => $mevents,
	    ]);
//	}
//	return $this->render('/habitantes/search.html.twig', [
//	    'bilatzaileaForm' => $bilatzaileaForm->createView()
//	]);
    }

    /**
     * @Route("/new", name="admin_mevent_new")
     */
    public function newAction (Request $request){
	$form = $this->createForm(MonitorizableEventForm::class);
	
	$form->handleRequest($request);
	if ( $form->isSubmitted() && $form->isValid() ) {
	    $mevent = $form->getData();
	    
	    $em = $this->getDoctrine()->getManager();
	    $em->persist($mevent);
	    $em->flush();
	    
	    $this->addFlash('success', 'messages.mevent.saved');
	    
	    return $this->redirectToRoute('admin_mevent_list');
	}
	
	return $this->render('mevent/new.html.twig', [
	    'form' => $form->createView()
	]);
    }

    /**
     * @Route("/{id}/edit", name="admin_mevent_edit")
     */
    public function editAction (Request $request, MonitorizableEvent $mevent){
	$form = $this->createForm(MonitorizableEventForm::class, $mevent);
	
	$form->handleRequest($request);
	if ( $form->isSubmitted() && $form->isValid() ) {
	    $mevent = $form->getData();
	    
	    $em = $this->getDoctrine()->getManager();
	    $em->persist($mevent);
	    $em->flush();
	    
	    $this->addFlash('success', 'messages.saved');
	    
	    return $this->redirectToRoute('admin_mevent_list');
	}
	
	return $this->render('mevent/edit.html.twig', [
	    'form' => $form->createView()
	]);
		
    }


    /**
     * @Route("/{id}/delete", name="admin_mevent_delete")
     */
    public function deleteAction (Request $request){


    }

    /**
     * @Route("/{id}", name="admin_mevent_show")
     */
    public function showAction (MonitorizableEvent $mevent){
	$form = $this->createForm(MonitorizableEventForm::class, $mevent);
	return $this->render('mevent/show.html.twig', [
	    'form' => $form->createView()
	]);
    }

    private function _remove_blank_filters ($criteria) {
	$new_criteria = [];
	foreach ($criteria as $key => $value) {
	    if (!empty($value))
		$new_criteria[$key] = $value;
	}
	return $new_criteria;
    }

}
