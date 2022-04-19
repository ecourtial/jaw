<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\UserRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Security;

class PostType extends AbstractType
{
    // Form types are services, so you can inject other services in them if needed
    public function __construct(
        private SluggerInterface $slugger,
        private Security $security,
        private UserRepository $userRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true, 'class' => 'form-control'],
                'label' => 'post.title',
            ])
            ->add(
                'category',
                EntityType::class,
                [
                    'class' => Category::class,
                    'label' => 'category.label',
                    'attr' => ['class' => 'form-control'],
                    'choice_label' => function (Category $category) {
                        return sprintf('(%d) %s', $category->getId(), $category->getTitle());
                    }
                ]
            )
            ->add(
                'online',
                ChoiceType::class,
                [
                    'label' => 'post.is_online',
                    'attr' => ['class' => 'form-control'],
                    'choices' => [
                        'yes' => true,
                        'no' => false
                    ]
                ]
            )
            ->add(
                'toppost',
                ChoiceType::class,
                [
                    'label' => 'post.is_top_post',
                    'attr' => ['class' => 'form-control'],
                    'choices' => [
                        'yes' => true,
                        'no' => false
                    ]
                ]
            )
            ->add(
                'language',
                ChoiceType::class,
                [
                    'label' => 'language',
                    'attr' => ['class' => 'form-control'],
                    'choices' => [
                        'french' => 'fr',
                        'english' => 'en',
                    ]
                ]
            )
            ->add('summary', TextareaType::class, [
                'label' => 'post.summary',
                'attr' => ['class' => 'form-control',
                ]
            ])
            ->add('content', CKEditorType::class, [
                'attr' => ['rows' => 20, 'class' => 'form-control'],
                'label' => 'post.content',
            ])
            // form events let you modify information or fields at different steps
            // of the form handling process.
            // See https://symfony.com/doc/current/form/events.html
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Post */
                $post = $event->getData();

                if (null !== $postTitle = $post->getTitle()) {
                    $post->setSlug($this->slugger->slug($postTitle)->lower());
                }

                $post->setPublishedAt(new \DateTime());

                $post->setAuthor(
                    // @phpstan-ignore-next-line
                    $this->userRepository->findByUsername($this->security->getUser()->getUserIdentifier())[0]
                );
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
