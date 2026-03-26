<?php

namespace App\Controller\Admin;

use App\Domain\Announcement\AnnouncementPopup;
use App\Domain\Announcement\AnnouncementPopupRepositoryInterface;
use App\Form\AnnouncementPopupFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/admin/announcement-popups', name: 'admin_announcement_popup_')]
final class AnnouncementPopupAdminController extends AbstractController
{
    public function __construct(
        private readonly AnnouncementPopupRepositoryInterface $repository,
    ) {}

    /*
     * INDEX – liste des pop-ups
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/announcement_popup/index.html.twig', [
            'popups' => $this->repository->findAllOrderedByPriority(),
        ]);
    }

    /*
     * NEW – création d’un pop-up
     */
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
                imageUrl: null,
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?? null,
                contentFr: $data['contentFr'] ?? null,
                imageUrlFr: null,
                recurrenceSeconds: $data['recurrenceSeconds'] ?? null,
            );

            if ($data['isActive']) {
                $popup->activate();
            }

            $this->handleImageUpload($form, $popup);

            $this->repository->save($popup);
            $this->addFlash('success', 'Popup créé avec succès.');

            return $this->redirectToRoute('admin', [
                'routeName' => 'admin_announcement_popup_index',
            ]);
        }

        return $this->render('admin/announcement_popup/new.html.twig', [
            'form' => $form->createView(),
            'locale' => $request->getLocale(),
        ]);
    }

    /*
     * EDIT – modification
     */
    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $id = $request->query->get('id');

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
                imageUrl: $popup->getImageUrl(),
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?? null,
                contentFr: $data['contentFr'] ?? null,
                imageUrlFr: $popup->getImageUrlFr(),
                recurrenceSeconds: $data['recurrenceSeconds'] ?? null,
            );

            $data['isActive'] ? $popup->activate() : $popup->deactivate();

            $this->handleImageUpload($form, $popup);

            $this->repository->save($popup);
            $this->addFlash('success', 'Popup mis à jour.');

            return $this->redirectToRoute('admin', [
                'routeName' => 'admin_announcement_popup_index',
            ]);
        }

        return $this->render('admin/announcement_popup/edit.html.twig', [
            'popup' => $popup,
            'form' => $form->createView(),
            'locale' => $request->getLocale(),
        ]);
    }

    /*
     * DELETE
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_announcement_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->repository->delete($id);
        $this->addFlash('success', 'Popup supprimé.');

        return $this->redirectToRoute('admin', [
            'routeName' => 'admin_announcement_popup_index',
        ]);
    }

    /*
     * TOGGLE activation
     */
    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'])]
    public function toggle(string $id): Response
    {
        $popup = $this->repository->findById($id);
        if (!$popup) {
            throw $this->createNotFoundException('Popup introuvable.');
        }

        $popup->isActive() ? $popup->deactivate() : $popup->activate();

        $this->repository->save($popup);

        return $this->redirectToRoute('admin', [
            'routeName' => 'admin_announcement_popup_index',
        ]);
    }

    /*
     * UPDATE PRIORITY (AJAX)
     */
    #[Route('/{id}/priority', name: 'priority', methods: ['POST'])]
    public function updatePriority(string $id, Request $request): Response
    {
        $csrfToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('priority_' . $id, $csrfToken)) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], Response::HTTP_FORBIDDEN);
        }

        $newPriority = $request->request->get('priority');

        if ($newPriority === null || !is_numeric($newPriority)) {
            return $this->json([
                'success' => false,
                'message' => 'Valeur de priorité invalide.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $newPriority = (int) $newPriority;

        $popup = $this->repository->findById($id);
        if (!$popup) {
            return $this->json([
                'success' => false,
                'message' => 'Popup introuvable.',
            ], Response::HTTP_NOT_FOUND);
        }

        $popup->setPriority($newPriority);
        $this->repository->save($popup);

        return $this->json([
            'success' => true,
            'current' => $newPriority,
        ]);
    }

    /*
     * FORCE RESET – forcer le réaffichage pour tous les utilisateurs
     */
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

        return $this->redirectToRoute('admin', [
            'routeName' => 'admin_announcement_popup_index',
        ]);
    }

    /*
     * Upload image (EN / FR)
     */
    private function handleImageUpload($form, AnnouncementPopup $popup): void
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/announcements/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        /** @var UploadedFile|null $fileEn */
        $fileEn = $form->get('imageUrl')->getData();
        if ($fileEn) {
            $filename = uniqid('ann_') . '.' . $fileEn->guessExtension();
            $fileEn->move($uploadDir, $filename);
            $popup->setImageUrl('/images/announcements/' . $filename);
        }

        /** @var UploadedFile|null $fileFr */
        $fileFr = $form->get('imageUrlFr')->getData();
        if ($fileFr) {
            $filename = uniqid('ann_fr_') . '.' . $fileFr->guessExtension();
            $fileFr->move($uploadDir, $filename);
            $popup->setImageUrlFr('/images/announcements/' . $filename);
        }
    }
}