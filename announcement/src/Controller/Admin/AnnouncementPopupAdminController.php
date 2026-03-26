<?php

namespace App\Controller\Admin;

use App\Domain\Announcement\AnnouncementPopup;
use App\Domain\Announcement\AnnouncementPopupRepositoryInterface;
use App\Form\AnnouncementPopupFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/admin/announcement-popups', name: 'admin_announcement_popup_')]
final class AnnouncementPopupAdminController extends AbstractController
{
    public function __construct(
        private readonly AnnouncementPopupRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/announcement_popup/index.html.twig', [
            'popups' => $this->repository->findAllOrderedByPriority(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(AnnouncementPopupFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $popup = AnnouncementPopup::create(
                id: Uuid::v4()->toRfc4122(),
                title: $data['title'],
                content: $data['content'],
                imageUrl: $data['imageUrl'] ?: null,
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?: null,
                contentFr: $data['contentFr'] ?: null,
                imageUrlFr: $data['imageUrlFr'] ?: null,
                recurrenceSeconds: $data['recurrenceSeconds'] ?: null,
            );

            if ($data['isActive']) {
                $popup->activate();
            }

            $this->repository->save($popup);
            $this->addFlash('success', 'Popup créé avec succès.');

            return $this->redirectToRoute('admin_announcement_popup_index');
        }

        return $this->render('admin/announcement_popup/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(string $id, Request $request): Response
    {
        $popup = $this->repository->findById($id);

        if (!$popup) {
            throw $this->createNotFoundException('Popup introuvable.');
        }

        $form = $this->createForm(AnnouncementPopupFormType::class, [
            'title'              => $popup->getTitle(),
            'titleFr'            => $popup->getTitleFr(),
            'content'            => $popup->getContent(),
            'contentFr'          => $popup->getContentFr(),
            'imageUrl'           => $popup->getImageUrl(),
            'imageUrlFr'         => $popup->getImageUrlFr(),
            'isActive'           => $popup->isActive(),
            'priority'           => $popup->getPriority(),
            'recurrenceSeconds'  => $popup->getRecurrenceSeconds(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $popup->update(
                title: $data['title'],
                content: $data['content'],
                imageUrl: $data['imageUrl'] ?: null,
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?: null,
                contentFr: $data['contentFr'] ?: null,
                imageUrlFr: $data['imageUrlFr'] ?: null,
                recurrenceSeconds: $data['recurrenceSeconds'] ?: null,
            );

            $data['isActive'] ? $popup->activate() : $popup->deactivate();

            $this->repository->save($popup);
            $this->addFlash('success', 'Popup mis à jour.');

            return $this->redirectToRoute('admin_announcement_popup_index');
        }

        return $this->render('admin/announcement_popup/edit.html.twig', [
            'popup' => $popup,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_announcement_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->repository->delete($id);
        $this->addFlash('success', 'Popup supprimé.');

        return $this->redirectToRoute('admin_announcement_popup_index');
    }

    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'])]
    public function toggle(string $id): Response
    {
        $popup = $this->repository->findById($id);

        if (!$popup) {
            throw $this->createNotFoundException('Popup introuvable.');
        }

        $popup->isActive() ? $popup->deactivate() : $popup->activate();
        $this->repository->save($popup);

        return $this->json(['isActive' => $popup->isActive()]);
    }

    #[Route('/{id}/force-reset', name: 'force_reset', methods: ['POST'])]
    public function forceReset(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('force_reset_announcement_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $popup = $this->repository->findById($id);

        if (!$popup) {
            throw $this->createNotFoundException('Popup introuvable.');
        }

        $popup->forceReset();
        $this->repository->save($popup);
        $this->addFlash('success', 'Réaffichage forcé : les utilisateurs reverront cette popup.');

        return $this->redirectToRoute('admin_announcement_popup_index');
    }
}
