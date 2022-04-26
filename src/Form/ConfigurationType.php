<?php


/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

namespace App\Form;

use App\Entity\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('blogTitle', TextType::class, [
                'label' => 'configuration.blog_title',
                'attr' => ['class' => 'form-control']
            ])
            ->add('blogDescription', TextType::class, [
                'label' => 'configuration.blog_description',
                'attr' => ['class' => 'form-control']
            ])
            ->add('copyrightMessage', TextType::class, [
                'label' => 'configuration.copyright_message',
                'attr' => ['class' => 'form-control']
            ])
            ->add('copyrightExtraMessage', TextType::class, [
                'label' => 'configuration.copyright_extra_message',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('linkedinUsername', TextType::class, [
                'label' => 'configuration.linkedin_username',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('githubUsername', TextType::class, [
                'label' => 'configuration.github_username',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('googleAnalyticsId', TextType::class, [
                'label' => 'configuration.google_analytics_id',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('callbackUrl', TextType::class, [
                'label' => 'configuration.callback_url',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('currentPassword', PasswordType::class, [
                'constraints' => [
                    new UserPassword(),
                ],
                'label' => 'user.label.current_password',
                'mapped' => false, // Otherwise error 'cause this field doesn't exist in the Entity
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
        ]);
    }
}
