<?php

namespace App\Controller;

use App\Entity\Grp;
use App\Form\GrpType;
use App\Repository\GrpRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SendMessage;

/**
 * @Route("/grp")
 */
class GrpController extends Controller
{
    /**
     * @Route("/", name="grp_index", methods="GET")
     */
    public function index(GrpRepository $grpRepository): Response
    {
        return $this->render('grp/index.html.twig', ['grps' => $grpRepository->findAll()]);
    }

    /**
     * @Route("/choose", name="choose_group", methods="GET")
     */
    public function choose(GrpRepository $grpRepository): Response
    {
        return $this->render('grp/choose.html.twig', ['groups' => $grpRepository->findAll()]);
    }

    /**
     * @Route("/new", name="grp_new", methods="GET|POST")
     */
    public function new(Request $request, SendMessage $sendMessage): Response
    {
        $grp = new Grp();
        $form = $this->createForm(GrpType::class, $grp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $form_data = $form->getData();
            $title = $form_data->getTitle();
            $admins = $form_data->getAdmins();
            $lowercase_title = strtolower($title);
            $connected_title = str_replace(" ", ".", $lowercase_title);
            $grp->setTitle($connected_title);
            $concatenated_admins = str_replace(",", "+", $admins);

            $em = $this->getDoctrine()->getManager();
            $em->persist($grp);
            $em->flush();

            $message = "Myle-Post bulk messaging. Your group: $connected_title is ready. After adding members, just type the group name '$connected_title' as the first word in your message.";
            $concat_message = str_replace(" ", "+", $message);
            $send_to_group = $sendMessage->sendMessage($concatenated_admins, $concat_message);

            return $this->redirectToRoute('grp_index');
        }

        return $this->render('grp/new.html.twig', [
            'grp' => $grp,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="grp_show", methods="GET")
     */
    public function show(Grp $grp): Response
    {
        return $this->render('grp/show.html.twig', ['grp' => $grp]);
    }

    /**
     * @Route("/{id}/edit", name="grp_edit", methods="GET|POST")
     */
    public function edit(Request $request, Grp $grp): Response
    {
        $form = $this->createForm(GrpType::class, $grp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('grp_edit', ['id' => $grp->getId()]);
        }

        return $this->render('grp/edit.html.twig', [
            'grp' => $grp,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="grp_delete", methods="DELETE")
     */
    public function delete(Request $request, Grp $grp): Response
    {
        if ($this->isCsrfTokenValid('delete'.$grp->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($grp);
            $em->flush();
        }

        return $this->redirectToRoute('grp_index');
    }
}
