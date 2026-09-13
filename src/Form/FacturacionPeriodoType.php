<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class FacturacionPeriodoType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder->add('periodo', TextType::class, [
            'label' => 'Período de facturación',
            'attr' => [
                'placeholder' => '2026-03',
                'maxlength' => 7,
            ],
            'constraints' => [
                new NotBlank(message: 'Debe ingresar un período.'),
                new Regex(
                    pattern: '/^\d{4}-(0[1-9]|1[0-2])$/',
                    message: 'Use el formato YYYY-MM.',
                ),
            ],
        ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
        ]);
    }
}
