<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class BenchmarkRunFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modelA', TextType::class, [
                'label' => 'form.model_a',
                'constraints' => [new NotBlank()],
            ])
            ->add('modelB', TextType::class, [
                'label' => 'form.model_b',
                'constraints' => [new NotBlank()],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'benchmark.run_suite',
            ]);

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::POST_SUBMIT, function (\Symfony\Component\Form\FormEvent $event): void {
            $data = $event->getData();
            if (!\is_array($data)) {
                return;
            }
            $a = trim((string) ($data['modelA'] ?? ''));
            $b = trim((string) ($data['modelB'] ?? ''));
            if ($a !== '' && $b !== '' && strcasecmp($a, $b) === 0) {
                $event->getForm()->addError(new \Symfony\Component\Form\FormError('validation.models_must_differ'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
        ]);
    }
}
