<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\App;
use App\Form\Admin\PageType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

final class AdminAppContentFormTest extends TestCase
{
    public function testExistingContentsCannotBeDeletedByAnIncompleteSubmission(): void
    {
        $listener = null;
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();
        $builder->expects(self::once())
            ->method('addEventListener')
            ->with(FormEvents::POST_SET_DATA, self::callback(static function (callable $callback) use (&$listener): bool {
                $listener = $callback;

                return true;
            }))
            ->willReturnSelf()
        ;

        (new PageType())->buildForm($builder, ['locale' => 'en']);

        self::assertIsCallable($listener);

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::exactly(3))
            ->method('add')
            ->willReturnCallback(static function (string $name, ?string $type = null, array $options = []) use ($form): FormInterface {
                if ('contents' === $name) {
                    self::assertSame(CollectionType::class, $type);
                    self::assertFalse($options['allow_delete']);
                }

                return $form;
            })
        ;

        $listener(new FormEvent($form, new App()));
    }
}
