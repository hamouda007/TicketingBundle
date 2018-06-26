<?php

namespace Maps_red\TicketingBundle\Controller;

use Maps_red\TicketingBundle\Entity\TicketComment;
use Maps_red\TicketingBundle\Form\TicketCloseForm;
use Maps_red\TicketingBundle\Form\TicketCommentForm;
use Maps_red\TicketingBundle\Manager\TicketCommentManager;
use Maps_red\TicketingBundle\Manager\TicketStatusManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Maps_red\TicketingBundle\Entity\Ticket;
use Maps_red\TicketingBundle\Form\TicketForm;
use Maps_red\TicketingBundle\Manager\TicketManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TicketingController extends Controller
{
    /**
     * @Route("/perso", name="ticketing_perso")
     * @param TicketStatusManager $ticketStatusManager
     * @return Response
     */
    public function persoTicketsAction(TicketStatusManager $ticketStatusManager)
    {
        return $this->render("@Ticketing/ticketing/personal_page.html.twig", [
            'status_list' => $ticketStatusManager->getRepository()->findAll()
        ]);
    }

    /**
     * @Route("/all", name="ticketing_all")
     */
    public function index()
    {
        return $this->render('@Ticketing/ticketing/index.html.twig', [
        ]);
    }

    /**
     * @Route("/new", name="ticketing_new", methods="GET|POST")
     * @param Request $request
     * @param TicketManager $ticketManager
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addTicket(Request $request, TicketManager $ticketManager): Response
    {
        $ticket = $ticketManager->newClass();
        $user = $this->getUser();
        $ticketForm = $this->createForm(TicketForm::class, $ticket);
        $ticketForm->handleRequest($request);

        if ($ticketForm->isSubmitted() && $ticketForm->isValid()) {
            $ticketManager->createTicket($user, $ticket);
            $this->addFlash('success', 'The ticket is online !');

            return $this->redirectToRoute('ticketing_all');
        }

        return $this->render('@Ticketing/ticketing/new.html.twig', [
            'form' => $ticketForm->createView(),
        ]);
    }

    /**
     * @Route("/detail/{id}", name="ticketing_detail", options={"expose": "true"})
     * @param Request $request
     * @param Ticket $ticket
     * @param TicketManager $ticketManager
     * @param TicketCommentManager $ticketCommentManager
     * @return RedirectResponse|Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function detail(Request $request, Ticket $ticket, TicketManager $ticketManager, TicketCommentManager $ticketCommentManager)
    {
        if (!$ticketManager->isTicketGranted($ticket, $this->getUser())) {
            $this->addFlash("warning", "Ce ticket appartient à une catégorie restreinte, vous ne disposez pas des 
            permissions suffisantes pour l'afficher");

            return $this->redirectToRoute("ticketing_perso");
        }

        $comment = new TicketComment();
        $commentForm = $this->createForm(TicketCommentForm::class, $comment);
        $closeForm = $this->createForm(TicketCloseForm::class, $ticket);

        $commentForm->handleRequest($request);
        $closeForm->handleRequest($request);

        $route = $this->redirectToRoute("ticketing_detail", ['id' => $ticket->getId()]);
        $isAuthorOrGranted = $ticketManager->isAuthorOrGranted($ticket, $this->getUser());
        $isGranted = $ticketManager->isPrivateTicketAuthorized();

        if ($request->request->has("public") && $isAuthorOrGranted) {
            $isPublic = (int)$request->request->get("public");
            $ticketManager->handlePublicStatusAction($ticket, $this->getUser(), $isPublic);
            $this->addFlash('success', sprintf('Le ticket a bien été passé en %s', $isPublic ? "public" : "privé"));

            return $route;
        }



        //Current user add a comment to this ticket
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $ticketManager->handleCommentAction($ticket, $this->getUser(), $comment);
            $this->addFlash('success', 'Le commentaire a bien été ajouté');

            return $route;
        }

        //Current use close the ticket
        if ($closeForm->isSubmitted() && $closeForm->isValid() && $isAuthorOrGranted) {
            $ticketManager->handleCloseAction($ticket, $this->getUser());
            $this->addFlash('success', 'Le ticket a bien été fermé');

            return $route;
        }


        return $this->render("@Ticketing/ticketing/detail_page.html.twig", [
            'ticket' => $ticket,
            'form' => $commentForm->createView(),
            'close_form' => $closeForm->createView(),
            'isAuthorOrGranted' => $isAuthorOrGranted,
            'isGranted' => $isGranted,
        ]);
    }
}