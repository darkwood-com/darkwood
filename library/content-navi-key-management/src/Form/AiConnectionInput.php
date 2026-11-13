<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Attribute\AsFormType;
use Symfony\Component\Form\Attribute\FormField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints as Assert;

#[AsFormType]
final class AiConnectionInput
{
    #[FormField(TextType::class, ['label' => 'Label'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $label = '';

    #[FormField(ChoiceType::class, [
        'label' => 'Provider',
        'choices' => [
            'Anthropic' => 'anthropic',
            'OpenAI' => 'openai',
        ],
    ])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['anthropic', 'openai'])]
    public string $provider = 'anthropic';

    #[FormField(TextType::class, ['label' => 'Model'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    public string $model = 'claude-sonnet-4-5';

    #[FormField(UrlType::class, [
        'label' => 'Endpoint (optional)',
        'required' => false,
        'help' => 'Leave empty for the official API. Anthropic-compatible base URL only — no trailing /v1/messages.',
    ])]
    #[Assert\Length(max: 255)]
    public ?string $endpoint = null;

    #[FormField(PasswordType::class, [
        'label' => 'API key',
        'required' => false,
        'always_empty' => true,
        'attr' => ['autocomplete' => 'new-password'],
        'help' => 'Never stored in plaintext. Leave blank when editing to keep the existing encrypted key.',
    ])]
    #[Assert\NotBlank(groups: ['create'])]
    public ?string $apiKey = null;
}
