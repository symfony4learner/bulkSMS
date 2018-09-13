<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\Contact1Type;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contact")
 */
class ContactController extends Controller
{
    /**
     * @Route("/", name="contact_index", methods="GET")
     */
    public function index(Request $request, ContactRepository $contactRepository): Response
    {
        $grp = $request->query->get('grp');
        $data = [];
        $grp = $this->find("Grp", "One", "Title", $grp);
        $data['grp'] = $grp;
        $contacts = $contactRepository->findByGrp($grp);
        $data['contacts'] = $contacts;
        return $this->render('contact/index.html.twig', $data );
    }

    /**
     * @Route("/new", name="contact_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $grp_id = $request->query->get('grp');
        $group = $this->em()->getRepository('App:Grp')->find($grp_id);
        $contact = new Contact();
        $contact->setGrp($group);
        $form = $this->createForm(Contact1Type::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            return $this->redirectToRoute('choose_group');
        }

        return $this->render('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="contact_show", methods="GET")
     */
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', ['contact' => $contact]);
    }

    /**
     * @Route("/{id}/edit", name="contact_edit", methods="GET|POST")
     */
    public function edit(Request $request, Contact $contact): Response
    {
        $form = $this->createForm(Contact1Type::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contact_edit', ['id' => $contact->getId()]);
        }

        return $this->render('contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="contact_delete", methods="DELETE")
     */
    public function delete(Request $request, Contact $contact): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($contact);
            $em->flush();
        }

        return $this->redirectToRoute('choose_group');
    }

    /**
     * @Route("clear_all", name="contact_clear")
     */
    public function clear(Request $request): Response
    {
        $grp = $request->query->get('grp');
        $group = $this->em()->getRepository('App:Grp')->find($grp);
        $contacts = $this->em()->getRepository('App:Contact')->findByGrp($group);
        foreach($contacts as $contact){
            $em = $this->getDoctrine()->getManager();
            $em->remove($contact);
            $em->flush();
        }
        return $this->redirectToRoute('contact_index', ['grp' => $group->getTitle()]);
    }

    private function em(){
        $em = $this->getDoctrine()->getManager();
        return $em;
    }

    private function find($entity, $qty, $by, $value){
        if($qty == "All"){
            $query_string = "findAll";
        } else {
            $query_string = "find".$qty."By".$by;
        }
        
        $entity = $this->em()->getRepository("App:$entity")->$query_string($value);
        return $entity;
    }

}
