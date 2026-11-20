<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Context\Domain\ManagingPromotionsContext as SyliusContext;
use Sylius\Component\Promotion\Model\PromotionInterface;

/** Adapts Sylius 2.3.3 step signatures to Behat 4's capture validation. */
final class ManagingPromotionsContext implements Context
{
    public function __construct(private readonly SyliusContext $sylius)
    {
    }

    #[When('/^I delete a ("[^"]+" promotion)$/')]
    public function deletePromotion(PromotionInterface $promotion): void
    {
        $this->sylius->iDeletePromotion($promotion);
    }

    #[When('/^I try to delete a ("[^"]+" promotion)$/')]
    public function tryToDeletePromotion(PromotionInterface $promotion): void
    {
        $this->sylius->iTryToDeletePromotion($promotion);
    }

    #[When('I archive the :promotion promotion')]
    public function archivePromotion(PromotionInterface $promotion): void
    {
        $this->sylius->iArchiveThePromotion($promotion);
    }

    #[Then('/^(this promotion) should no longer exist in the promotion registry$/')]
    public function promotionIsGone(PromotionInterface $promotion): void
    {
        $this->sylius->promotionShouldNotExistInTheRegistry($promotion);
    }

    #[Then('promotion :promotion should still exist in the registry')]
    public function promotionStillExists(PromotionInterface $promotion): void
    {
        $this->sylius->promotionShouldStillExistInTheRegistry($promotion);
    }

    #[Then('I should be notified that it is in use and cannot be deleted')]
    public function promotionIsInUse(): void
    {
        $this->sylius->iShouldBeNotifiedOfFailure();
    }

    #[Then('the promotion :promotion should still exist in the registry')]
    public function thePromotionStillExists(PromotionInterface $promotion): void
    {
        $this->sylius->thePromotionShouldStillExistInTheRegistry($promotion);
    }
}
