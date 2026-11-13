<?php
declare(strict_types=1);
namespace Diablo\Engine\Assets;
final class AssetStore
{
    /** @var array<string,string> */
    private array $cache = [];
    public function __construct(private MpqArchive $mpq) {}
    public function mpq(): MpqArchive { return $this->mpq; }
    public function read(string $path): string
    {
        $key = strtolower(str_replace('/', '\\', $path));
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->mpq->readFile($path);
        }
        return $this->cache[$key];
    }
    public function has(string $path): bool { return $this->mpq->hasFile($path); }
    public function loadPalette(string $path): Palette { return Palette::fromBytes($this->read($path)); }
    public function loadCel(string $path): Cel { return Cel::parse($this->read($path)); }
}
