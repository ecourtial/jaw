<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/category')]
class CategoryController extends AbstractAdminController
{
    #[Route('/list', name: 'category_list')]
    public function getList(CategoryRepository $categoryRepository): Response
    {
        return $this->generateView(
            'admin/categories/list.html.twig',
            $this->translator->trans('categories.list'),
            $this->translator->trans('categories.list'),
            ['categories' => $categoryRepository->findAll()]
        );
    }

    #[Route('/add', methods: ['GET', 'POST'], name: 'category_add')]
    public function create(EntityManagerInterface $entityManager): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category)
            ->add('saveAndCreateNew', SubmitType::class, [
                'label' => 'button.save_and_create_new',
                'attr' => ['class' => 'btn btn-primary']
            ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            /** @var \Symfony\Component\Form\SubmitButton $saveAndCreateNewButton */
            $saveAndCreateNewButton = $form->get('saveAndCreateNew');

            if ($saveAndCreateNewButton->isClicked()) {
                $this->addFlash('success', 'category.successfully_created');

                return $this->redirectToRoute('category_add');
            }

            return $this->redirectToRoute('category_list');
        }

        return $this->generateView(
            'admin/categories/form.html.twig',
            $this->translator->trans('category.creation_form_title'),
            $this->translator->trans('category.creation_form_title'),
            ['form' => $form->createView()]
        );
    }

    #[Route('/{id<\d+>}/edit', methods: ['GET', 'POST'], name: 'category_edit')]
    public function edit(Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'category.successfully_updated');

            return $this->redirectToRoute('category_edit', ['id' => $category->getId()]);
        }

        return $this->generateView(
            'admin/categories/form.html.twig',
            $this->translator->trans('category.edition_form_title'),
            $this->translator->trans('category.edition_form_title') . ': ' . $category->getTitle(),
            ['category' => $category, 'form' => $form->createView(), 'showDeleteForm' => true]
        );
    }

    #[Route('/{id<\d+>}/delete', methods: ['POST'], name: 'category_delete')]
    public function delete(Category $category, EntityManagerInterface $entityManager): Response
    {
        // @phpstan-ignore-next-line
        if (true === $this->isCsrfTokenValid('delete', $this->request->request->get('token'))) {
            if ($category->getPosts()->isEmpty()) {
                $entityManager->remove($category);
                $entityManager->flush();

                $this->addFlash('success', 'category.deleted_successfully');
            } else {
                $this->addFlash('alert', 'category.deletion_error_has_posts');
            }
        }

        return $this->redirectToRoute('category_list');
    }

    #[Route('/{id<\d+>}', methods: ['GET'], name: 'category_get')]
    public function details(Category $category): Response
    {
        return $this->generateView(
            'admin/categories/details.html.twig',
            $this->translator->trans('category.label'),
            $this->translator->trans('category.label') . ': ' . $category->getTitle(),
            ['category' => $category]
        );
    }
}
