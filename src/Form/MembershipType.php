<?php

namespace App\Form;

use App\Entity\Membership;
use App\Entity\User;
use App\Entity\Wishlist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $userId = $options['userId'];
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'placeholder' => 'Sélectionnez un email',
            ])
            ->add('wishlist', EntityType::class, [
                'class' => Wishlist::class,
                'choice_label' => 'title',
                'placeholder' => 'Sélectionnez une wishlist',
                'query_builder' => function ($repository) use ($userId) {
                    return $repository->createQueryBuilder('w')
                        ->where('w.user = :userId')
                        ->setParameter('userId', $userId);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Membership::class,
        ]);
        $resolver->setRequired('userId');
    }
}
