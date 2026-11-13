<?php
declare(strict_types=1);
namespace Diablo\Engine\Assets;
/** 256-color palette loader. Derived from palette loading (see NOTICE.md) — modified/translated. */
final class Palette
{
    /** @var list<array{0:int,1:int,2:int}> RGB entries */
    public array $colors = [];

    public function __construct()
    {
        $this->colors = array_fill(0, 256, [0, 0, 0]);
    }

    public static function fromBytes(string $data): self
    {
        if (strlen($data) < 768) {
            throw new \RuntimeException('Palette too small');
        }
        $p = new self();
        for ($i = 0; $i < 256; $i++) {
            $r = ord($data[$i * 3]) * 4;
            $g = ord($data[$i * 3 + 1]) * 4;
            $b = ord($data[$i * 3 + 2]) * 4;
            $p->colors[$i] = [min(255, $r), min(255, $g), min(255, $b)];
        }

        return $p;
    }

    /** @return array{0:int,1:int,2:int} */
    public function rgb(int $index): array
    {
        return $this->colors[$index & 0xFF];
    }
}
