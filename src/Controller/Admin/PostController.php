<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/post')]
class PostController extends AbstractAdminController
{
    #[Route('/add', methods: ['GET', 'POST'], name: 'post_add')]
    public function create(PostRepository $postRepository): Response
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post)
            ->add('saveAndCreateNew', SubmitType::class, [
                'label' => 'button.save_and_create_new',
                'attr' => ['class' => 'btn btn-primary']
            ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postRepository->save($post);

            /** @var \Symfony\Component\Form\SubmitButton $saveAndCreateNewButton */
            $saveAndCreateNewButton = $form->get('saveAndCreateNew');

            $this->addFlash('success', 'post.successfully_created');

            if ($saveAndCreateNewButton->isClicked()) {
                return $this->redirectToRoute('post_add');
            }

            return $this->redirectToRoute('post_edit', ['id' => $post->getId()]);
        }

        return $this->generateView(
            'admin/posts/form.html.twig',
            $this->translator->trans('post.creation_form_title'),
            $this->translator->trans('post.creation_form_title'),
            ['form' => $form->createView()]
        );
    }

    #[Route('/{id<\d+>}/edit', methods: ['GET', 'POST'], name: 'post_edit')]
    public function edit(Post $post, PostRepository $postRepository): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postRepository->save($post);
            $this->addFlash('success', 'post.successfully_updated');

            return $this->redirectToRoute('post_edit', ['id' => $post->getId()]);
        }

        return $this->generateView(
            'admin/posts/form.html.twig',
            $this->translator->trans('post.edition_form_title'),
            $this->translator->trans('post.edition_form_title') . ': ' . $post->getTitle(),
            ['post' => $post, 'form' => $form->createView(), 'showDeleteForm' => true]
        );
    }

    #[Route('/{id<\d+>}/delete', methods: ['POST'], name: 'post_delete')]
    public function delete(Post $post, PostRepository $postRepository): Response
    {
        // @phpstan-ignore-next-line
        if (true === $this->isCsrfTokenValid('delete', $this->request->request->get('token'))) {
            $postRepository->delete($post);
            $this->addFlash('success', 'post.deleted_successfully');
        }

        return $this->redirectToRoute('category_list');
    }
}
