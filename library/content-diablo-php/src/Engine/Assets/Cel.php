<?php
declare(strict_types=1);
namespace Diablo\Engine\Assets;
/**
 * CEL frame decode to RGBA.
 * See NOTICE.md for attribution.
 */
final class Cel
{
    public int $numFrames;
    /** @var list<int> */
    public array $offsets;
    public string $data;

    public static function parse(string $data): self
    {
        $c = new self();
        $c->data = $data;
        $size = strlen($data);
        $c->numFrames = unpack("V", substr($data, 0, 4))[1];
        // Grouped CEL: if offset after frames != size, treat as groups — use first group only for now
        if ($c->numFrames > 0 && ($c->numFrames * 4 + 4) < $size) {
            $last = unpack("V", substr($data, $c->numFrames * 4 + 4, 4))[1];
            if ($last !== $size) {
                // groups header: first dword is groupsHeaderSize
                $groupsHeaderSize = $c->numFrames;
                $data = substr($data, $groupsHeaderSize);
                $c->data = $data;
                $c->numFrames = unpack("V", substr($data, 0, 4))[1];
            }
        }
        $c->offsets = [];
        for ($i = 0; $i <= $c->numFrames; $i++) {
            $c->offsets[] = unpack("V", substr($c->data, 4 + $i * 4, 4))[1];
        }
        return $c;
    }

    /**
     * @param list<int>|null $lightTable LightTables row; null = fullbright
     * @return array{w:int,h:int,rgba:string}
     */
    public function decodeFrame(int $frameIndex, int $width, Palette $pal, ?array $lightTable = null): array
    {
        if ($frameIndex < 0 || $frameIndex >= $this->numFrames) {
            throw new \RuntimeException("CEL frame OOB");
        }
        $start = $this->offsets[$frameIndex];
        $end = $this->offsets[$frameIndex + 1];
        $src = substr($this->data, $start, $end - $start);
        $pos = 0;
        if (strlen($src) >= 2 && unpack("v", substr($src, 0, 2))[1] === 10) {
            $pos = 10; // skip CEL frame header
        }
        $pixels = []; // row-major bottom-up then flip
        $row = [];
        $rows = [];
        $x = 0;
        $len = strlen($src);
        while ($pos < $len) {
            $val = ord($src[$pos++]);
            if ($val >= 0x80) {
                $n = (256 - $val) & 0xFF;
                if ($n === 0) $n = $val; // safety
                // transparent run: signed interpretation (-(int8)val)
                $n = -$this->toInt8($val);
                for ($i = 0; $i < $n; $i++) {
                    $row[] = null;
                    $x++;
                    if ($x >= $width) { $rows[] = $row; $row = []; $x = 0; }
                }
            } else {
                for ($i = 0; $i < $val; $i++) {
                    if ($pos >= $len) break 2;
                    $idx = ord($src[$pos++]);
                    $row[] = $idx;
                    $x++;
                    if ($x >= $width) { $rows[] = $row; $row = []; $x = 0; }
                }
            }
        }
        if ($row !== []) $rows[] = $row;
        $h = count($rows);
        // CEL is stored bottom-up
        $rows = array_reverse($rows);
        $rgba = "";
        foreach ($rows as $r) {
            for ($i = 0; $i < $width; $i++) {
                $idx = $r[$i] ?? null;
                if ($idx === null || $idx === 0) {
                    $rgba .= "\x00\x00\x00\x00";
                } else {
                    $mapped = $idx;
                    if ($lightTable !== null) {
                        $mapped = $lightTable[$idx] ?? $idx;
                    }
                    [$rr,$gg,$bb] = $pal->rgb($mapped);
                    $rgba .= chr($rr).chr($gg).chr($bb)."\xFF";
                }
            }
        }
        return ["w" => $width, "h" => $h, "rgba" => $rgba];
    }

    private function toInt8(int $v): int
    {
        return $v > 127 ? $v - 256 : $v;
    }
}
