<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PromptCompareFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modelA', TextType::class, [
                'label' => 'form.model_a',
                'constraints' => [new NotBlank()],
                'attr' => ['data-test' => 'model-a'],
            ])
            ->add('modelB', TextType::class, [
                'label' => 'form.model_b',
                'constraints' => [new NotBlank()],
                'attr' => ['data-test' => 'model-b'],
            ])
            ->add('prompt', TextareaType::class, [
                'label' => 'form.prompt',
                'constraints' => [new NotBlank()],
                'attr' => ['rows' => 8],
            ])
            ->add('system', TextareaType::class, [
                'label' => 'form.system',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('temperature', NumberType::class, [
                'label' => 'form.temperature',
                'html5' => true,
                'scale' => 2,
                'data' => 0.7,
            ])
            ->add('topP', NumberType::class, [
                'label' => 'form.top_p',
                'html5' => true,
                'scale' => 2,
                'data' => 0.9,
            ])
            ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => 'form.run_compare',
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
