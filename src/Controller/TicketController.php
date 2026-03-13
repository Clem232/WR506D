<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tickets', name: 'api_tickets_')]
class TicketController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        TicketRepository $repo,
        #[CurrentUser] User $user
    ): Response {
        $criteria = [];
        
        // Filtre custom: utilisateurs classiques ne voient que leurs tickets
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $criteria['assignee'] = $user;
        }

        // Filtres
        if ($status = $request->query->get('status')) {
            $criteria['status'] = $status;
        }
        if ($priority = $request->query->get('priority')) {
            $criteria['priority'] = $priority;
        }
        if ($categoryId = $request->query->get('category')) {
            $criteria['category'] = $categoryId;
        }

        $tickets = $repo->findBy($criteria);

        return $this->json($tickets, Response::HTTP_OK, [], ['groups' => 'ticket:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(
        Ticket $ticket,
        #[CurrentUser] User $user
    ): Response {
        // Vérification d'accès
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $ticket->getAssignee()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($ticket, Response::HTTP_OK, [], ['groups' => 'ticket:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        TicketRepository $repo,
        ValidatorInterface $validator,
        #[CurrentUser] User $user
    ): Response {
        $ticket = new Ticket();
        $ticket->setTitle($request->request->get('title'));
        $ticket->setDescription($request->request->get('description'));
        $ticket->setStatus('open');
        $ticket->setPriority($request->request->get('priority'));
        $ticket->setCategory($request->request->get('category'));
        $ticket->setAssignee($user);

        $errors = $validator->validate($ticket);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $repo->save($ticket);

        return $this->json(['message' => 'Ticket created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Ticket $ticket,
        Request $request,
        ValidatorInterface $validator,
        #[CurrentUser] User $user
    ): Response {
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => 'Only admins can update tickets'], Response::HTTP_FORBIDDEN);
        }

        $ticket->setTitle($request->request->get('title'));
        $ticket->setDescription($request->request->get('description'));
        $ticket->setStatus($request->request->get('status'));
        $ticket->setPriority($request->request->get('priority'));
        $ticket->setCategory($request->request->get('category'));

        $errors = $validator->validate($ticket);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $repo->save($ticket);

        return $this->json(['message' => 'Ticket updated']);
    }
}