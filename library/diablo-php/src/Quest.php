<?php

declare(strict_types=1);

namespace Diablo;

final class Quest
{
    public string $id = 'slay_marked';
    public string $title = 'Clear the Marked Beast';
    public string $status = 'inactive'; // inactive, active, complete, turned_in
    public ?int $targetMonsterId = null;
    public int $rewardGold = 200;
    public int $rewardXp = 150;

    /** @return array<string, mixed> */
    public function toSave(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'targetMonsterId' => $this->targetMonsterId,
            'rewardGold' => $this->rewardGold,
            'rewardXp' => $this->rewardXp,
        ];
    }

    public static function fromSave(array $d): self
    {
        $q = new self();
        $q->id = (string) ($d['id'] ?? $q->id);
        $q->title = (string) ($d['title'] ?? $q->title);
        $q->status = (string) ($d['status'] ?? $q->status);
        $q->targetMonsterId = isset($d['targetMonsterId']) ? (int) $d['targetMonsterId'] : null;
        $q->rewardGold = (int) ($d['rewardGold'] ?? 200);
        $q->rewardXp = (int) ($d['rewardXp'] ?? 150);

        return $q;
    }
}
