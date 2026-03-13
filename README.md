Announcementpopupformtype · PHP

<?php

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

final class AnnouncementPopupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'       => 'Titre (EN)',
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire')],
                'attr'        => ['placeholder' => 'Title in English'],
            ])
            ->add('titleFr', TextType::class, [
                'label'    => 'Titre (FR)',
                'required' => false,
                'attr'     => ['placeholder' => 'Titre en français'],
            ])
            ->add('content', CKEditorType::class, [
                'label'       => 'Contenu (EN)',
                'constraints' => [new NotBlank(message: 'Le contenu est obligatoire')],
            ])
            ->add('contentFr', CKEditorType::class, [
                'label'    => 'Contenu (FR)',
                'required' => false,
            ])
            ->add('imageUrl', FileType::class, [
                'label'       => 'Image (EN)',
                'required'    => false,
                'mapped'      => false,
                'constraints' => [new Image(['maxSize' => '2M'])],
            ])
            ->add('imageUrlFr', FileType::class, [
                'label'       => 'Image (FR)',
                'required'    => false,
                'mapped'      => false,
                'constraints' => [new Image(['maxSize' => '2M'])],
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Actif',
                'required' => false,
            ])
            ->add('priority', IntegerType::class, [
                'label'       => 'Priorité',
                'data'        => 0,
                'constraints' => [new PositiveOrZero(message: 'La priorité doit être positive ou nulle')],
                'help'        => 'Plus le chiffre est petit, plus le popup apparaît en premier (0 = premier)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => true]);
    }
}





===


Announcementpopupadmincontroller


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
                imageUrl: null,
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?: null,
                contentFr: $data['contentFr'] ?: null,
                imageUrlFr: null,
            );

            if ($data['isActive']) {
                $popup->activate();
            }

            $this->handleImageUpload($form, $popup);
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
            'title'      => $popup->getTitle(),
            'titleFr'    => $popup->getTitleFr(),
            'content'    => $popup->getContent(),
            'contentFr'  => $popup->getContentFr(),
            'imageUrl'   => $popup->getImageUrl(),
            'imageUrlFr' => $popup->getImageUrlFr(),
            'isActive'   => $popup->isActive(),
            'priority'   => $popup->getPriority(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $popup->update(
                title: $data['title'],
                content: $data['content'],
                imageUrl: $popup->getImageUrl(),
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?: null,
                contentFr: $data['contentFr'] ?: null,
                imageUrlFr: $popup->getImageUrlFr(),
            );

            $data['isActive'] ? $popup->activate() : $popup->deactivate();

            $this->handleImageUpload($form, $popup);
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


========
Announcementpopup · PHP

<?php

namespace App\Domain\Announcement;

final class AnnouncementPopup
{
    private function __construct(
        private readonly string $id,
        private string $title,
        private ?string $titleFr,
        private string $content,
        private ?string $contentFr,
        private ?string $imageUrl,
        private ?string $imageUrlFr,
        private bool $isActive,
        private int $priority,
    ) {
    }

    public static function create(
        string $id,
        string $title,
        string $content,
        ?string $imageUrl,
        int $priority,
        ?string $titleFr = null,
        ?string $contentFr = null,
        ?string $imageUrlFr = null,
    ): self {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        if (trim($content) === '') {
            throw new \InvalidArgumentException('Content cannot be empty');
        }
        if ($priority < 0) {
            throw new \InvalidArgumentException('Priority must be a positive integer');
        }

        return new self(
            id: $id,
            title: $title,
            titleFr: $titleFr,
            content: $content,
            contentFr: $contentFr,
            imageUrl: $imageUrl,
            imageUrlFr: $imageUrlFr,
            isActive: false,
            priority: $priority,
        );
    }

    public static function reconstitute(
        string $id,
        string $title,
        ?string $titleFr,
        string $content,
        ?string $contentFr,
        ?string $imageUrl,
        ?string $imageUrlFr,
        bool $isActive,
        int $priority,
    ): self {
        return new self(
            id: $id,
            title: $title,
            titleFr: $titleFr,
            content: $content,
            contentFr: $contentFr,
            imageUrl: $imageUrl,
            imageUrlFr: $imageUrlFr,
            isActive: $isActive,
            priority: $priority,
        );
    }

    public function activate(): void { $this->isActive = true; }
    public function deactivate(): void { $this->isActive = false; }

    public function update(
        string $title,
        string $content,
        ?string $imageUrl,
        int $priority,
        ?string $titleFr = null,
        ?string $contentFr = null,
        ?string $imageUrlFr = null,
    ): void {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        if (trim($content) === '') {
            throw new \InvalidArgumentException('Content cannot be empty');
        }
        if ($priority < 0) {
            throw new \InvalidArgumentException('Priority must be a positive integer');
        }

        $this->title = $title;
        $this->titleFr = $titleFr;
        $this->content = $content;
        $this->contentFr = $contentFr;
        $this->imageUrl = $imageUrl;
        $this->imageUrlFr = $imageUrlFr;
        $this->priority = $priority;
    }

    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
    public function setImageUrlFr(?string $imageUrlFr): void { $this->imageUrlFr = $imageUrlFr; }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getTitleFr(): ?string { return $this->titleFr; }
    public function getContent(): string { return $this->content; }
    public function getContentFr(): ?string { return $this->contentFr; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getImageUrlFr(): ?string { return $this->imageUrlFr; }
    public function isActive(): bool { return $this->isActive; }
    public function getPriority(): int { return $this->priority; }
}

====
