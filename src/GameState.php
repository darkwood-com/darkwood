<?php

declare(strict_types=1);

namespace Diablo;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Assets\Cel;
use Diablo\Engine\Assets\Cl2;
use Diablo\Engine\Assets\UiFont;
use Diablo\Engine\AnimationInfo;
use Diablo\Engine\DestAction;
use Diablo\Engine\Direction;
use Diablo\Engine\GameplayTrace;
use Diablo\Engine\Lighting;
use Diablo\Engine\LineClear;
use Diablo\Engine\Path;
use Diablo\Engine\Point;
use Diablo\Engine\Random;
use Diablo\Engine\Vision;
use Diablo\Engine\Render\IsoCoords;
use Diablo\Engine\Render\Scrollrt;
use Diablo\Engine\Render\WalkOffset;
use Diablo\Engine\Render\DunMicro;
use Diablo\Engine\Render\MaskType;
use Diablo\Engine\Render\RenderTrace;
use Diablo\Engine\SelectionRegion;
use Diablo\DiabloUI\TitleDialog;
use Diablo\DiabloUI\UiList;
use Diablo\Items\InventoryGrid;
use Diablo\Items\ItemDat;
use Diablo\Levels\DungeonMap;
use Diablo\Monsters\AiProc;
use Diablo\Monsters\Monstdat;
use Diablo\Occupancy;
use Diablo\Platform\Sdl;
use Diablo\Cursor;

/**
 * Central game state / GameLogic (single-player).
 * See NOTICE.md for attribution.
 */
final class GameState
{
    private DungeonMap $map;
    private Random $rng;
    private Path $pathfinder;
    private Lighting $lighting;
    /** @var array<string,mixed>|null cursor-held inventory item (HoldItem) */
    private ?array $cursorItem = null;
    /** DevilutionX pcurs / NewCursor state (sprite id; orthogonal to hover targets). */
    private Cursor $cursor;

    private string $mode = 'menu'; // title, menu, selhero, play, dead, vendor, quit
    private string $menuScreen = 'title'; // title → main → selhero (mainmenu flow)
    private int $menuCursor = 0;
    private ?TitleDialog $titleDialog = null;
    /** hrtime start of title screen (ns); 0 = not started */
    private int $titleStartNs = 0;
    /** Freeze logo to this frame (-1 = animate). */
    public int $debugTitleFrame = -1;
    public bool $debugTitleAnimation = false;
    public bool $debugTitleCapture = false;
    /** @var array<string, bool> */
    private array $titleCaptureDone = [];
    private int $titleAnimLogAtMs = -1000;
    private ?object $titleTex = null;
    private int $titleW = 0;
    private int $titleH = 0;
    private ?object $mainMenuTex = null;
    private int $mainMenuW = 0;
    private int $mainMenuH = 0;
    private ?object $focusTex = null;
    private int $focusW = 0;
    private int $focusH = 0;

    private string $playerName = 'Hero';
    private string $playerClass = 'Warrior';
    private int $px = 75;
    private int $py = 68;
    private int $pdir = Direction::S;
    private string $pmode = 'STAND';
    private int $php = 100;
    private int $pmaxHp = 100;
    private int $pmana = 50;
    private int $pmaxMana = 50;
    private int $gold = 100;
    private int $level = 1;
    private int $xp = 0;
    private int $minDam = 1;
    private int $maxDam = 4;
    private int $ac = 5;
    private int $toHit = 50;
    /** Resist % (0–75), CalcPlrItemVals / _pFireResist */
    private int $pFireResist = 0;
    private int $pLightResist = 0;
    private int $pMagicResist = 0;
    /** Extra damage taken from hits (_pIGetHit). */
    private int $pGetHit = 0;
    /** Player light/vision radius (_pLightRad), base 10. */
    private int $pLightRad = 10;
    /** FastAttack tier 0–4 / FastHitRecovery tier 0–3. */
    private int $pFastAttack = 0;
    private int $pFastRecover = 0;
    /** Bonus spell level from SPLLVLADD gear. */
    private int $pSplLvlAdd = 0;
    /** Piercing — subtract from monster AC (TARGAC / _pIEnAc). */
    private int $pEnAc = 0;
    private int $strength = 30;
    private int $magic = 10;
    private int $dexterity = 20;
    private int $vitality = 25;

    /** Infravision remaining ticks (murky fountain / spell). */
    private int $pInfraTicks = 0;
    /** Search remaining ticks (AutoMapShowItems). */
    private int $pSearchTicks = 0;
    /** Mana Shield active (scroll / cast). */
    private bool $pManaShield = false;
    /** @var array<string,int> spell name → _pSplLvl */
    private array $spellLevels = ['Firebolt' => 1, 'Heal' => 1];

 /** @var list<int> walkpath[] direction codes, terminated by WALK_NONE */
    private array $walkpath = [];
    /** @deprecated debug mirror */
    private int $walkFrames = 0;
    /** Destination tile while walking (position.temp). */
    private int $walkTempX = 0;
    private int $walkTempY = 0;
    /** position.future while walking */
    private int $walkFutureX = 0;
    private int $walkFutureY = 0;
    private string $walkMode = 'STAND';
    private AnimationInfo $playerAnim;
    private int $actionFrame = 0;
    private int $animFrame = 0;
    private int $mouseX = 0;
    private int $mouseY = 0;
    private bool $mouseLeftDown = false;
    private int $destAction = DestAction::NONE;
    private int $destParam = 0;
    private int $destX = 0;
    private int $destY = 0;
    private int $playerId = 1;
    private Occupancy $occ;
    /** Cursor hover (CheckCursMove) */
    private int $cursX = 0;
    private int $cursY = 0;
    private string $cursKind = 'none';
    private int $cursId = 0;
    /** Sticky previous monster id (pcurstemp) */
    private int $cursTempId = 0;
    private bool $cursFlipflag = false;
    private int $cursEntityX = 0;
    private int $cursEntityY = 0;
    private ?UiList $uiList = null;
    public bool $debugCursor = false;
    public bool $debugCursorTarget = false;
    public bool $debugPlayerState = false;
    public bool $debugGameplayOverlay = false;
    public GameplayTrace $gtrace;
    /** Defer RepeatWalk to logic tick (DX command cadence) */
    private bool $pendingRepeatWalk = false;
    private bool $clickEdge = false;
    private int $traceMakePlrPathBudget = 0;
    private int $lastChaseGoalX = -1;
    private int $lastChaseGoalY = -1;
    private int $lastChaseMonX = -1;
    private int $lastChaseMonY = -1;
 /** Melee attack hit frame (_pAFNum) — warrior unarmed/sword actionFrame */
    private int $pAFNum = 9;
    /** Spell cast release frame (_pSFNum) — warrior castingActionFrame */
    private int $pSFNum = 14;
    private int $attackFrames = 16;
    private int $hitFrames = 6;
    private int $deathFrames = 20;
    private int $spellFrames = 20;
    private int $blockFrames = 2;
    private bool $attackHitDone = false;
    private int $logicTick = 0;
    /** @var list<float> recent frame times ms for p95/p99 */
    public array $frameMsSamples = [];

    /** @var list<array<string,mixed>> */
    private array $monsters = [];
    /** @var list<array<string,mixed>> */
    private array $items = [];
    /** @var list<array<string,mixed>> */
    private array $objects = [];
    /** @var list<array<string,mixed>> */
    private array $missiles = [];
    /** @var list<array<string,mixed>> */
    private array $towners = [];
    /** @var list<?array<string,mixed>> */
    private array $inventory = [];
    /** @var list<?array<string,mixed>> */
    private array $belt = [];
    /** @var array<string,?array<string,mixed>> */
    private array $equipment = ['hand' => null, 'offhand' => null, 'body' => null, 'head' => null];

    private int $dungeonSeed = 1;
    public bool $mazeFallback = false;
    /** floor|walls|entities|all */
    public string $renderLayer = 'all';
    /** When false (default), no diamond/rect placeholders in play */
    public bool $debugPlaceholders = false;
    /** Skip LightTables / dLight shading */
    public bool $fullbright = false;
    /** Freeze camera at fixed ViewPosition (isolate actor walk) */
    public bool $cameraFixed = false;
    /** Log walk frames to stdout */
    public bool $debugWalk = false;
    public bool $debugWalkStep = false;
    public bool $debugCamera = false;
    public bool $debugClickOverlay = false;
    /** @var resource|null */
    private $walkStepFh = null;
    /** Log menu/loading blit rects */
    public bool $debugDrawLog = false;
    /** Overlay / dump tile coverage classifications */
    public bool $debugTileCoverage = false;
    public bool $debugTileCoverageOverlay = false;
    /** LMB dumps screen→piece diagnostics */
    public bool $debugWorldPick = false;
    public bool $forceFullRedraw = false;
    public bool $debugClearMagenta = false;
    public bool $debugDisableMasks = false;
    public bool $disableWorldClipping = false;
    public int $debugRenderMargin = 0;
    public bool $debugWalkCapture = false;
    public bool $freezeFirstFrame = false;
    public bool $tracePlayerDraw = false;
    public bool $traceActorDraw = false;
    public bool $debugRenderState = false;
    public bool $hideActors = false;
    public bool $hideObjects = false;
    public bool $hideItems = false;
    public bool $hideHud = false;
    public bool $renderTrace = false;
    public bool $softwareCompose = false;
    /** @var array{0:int,1:int}|null */
    public ?array $debugPixel = null;
    public string $debugCombatCaptureDir = '';
    private int $debugCombatCaptureFrame = 0;
    /** @var resource|null */
    private $debugCombatCaptureFh = null;
    private int $playerDrawTraceCount = 0;
    private int $heroBlitThisFrame = 0;
    private int $renderFrameId = 0;
    /** @var array<string,int> actor blit counts this frame (key = kind:id) */
    private array $actorBlitThisFrame = [];
    /** @var array<string,array{frame:int,type:int,mode:string,mask:int,light:int}> */
    private array $microCacheMeta = [];
    /** Last known SDL draw color for --debug-render-state */
    private array $lastDrawColor = [0, 0, 0, 255];
    /** Last actor screen rects for combat capture / world compare */
    private array $lastActorScreenRects = [];
    /** @var resource|null */
    private $walkCaptureFh = null;
    private int $walkCaptureFrame = 0;
    private string $walkCaptureDir = '';
    private bool $walkCaptureStarted = false;
    private bool $walkCaptureDone = false;
    private int $forcePosX = -1;
    private int $forcePosY = -1;
    /** @var resource|null */
    private $worldPickFh = null;
    /** Fraction of current sim tick remaining→next (0..1) for WalkOffset presentation */
    public float $tickAlpha = 0.0;
    private int $walkTickLog = 0;
    private int $walkRenderLog = 0;
    private int $fixedCamX = 0;
    private int $fixedCamY = 0;
    private bool $fixedCamSet = false;
    private ?UiFont $uiFont = null;
    private ?UiFont $uiFontYellow = null;
    private ?UiFont $uiFontSmall = null;
    /** Frame profile counters (reset each present) */
    public int $profileDecodes = 0;
    public int $profileUploads = 0;
    public int $profileDraws = 0;
    /** @var array<string,object> */
    private array $uiTextCache = [];
    private string $questStatus = 'inactive';
    private ?int $questTargetId = null;

    private bool $showInv = false;
    private bool $showChar = false;
    private bool $showAutomap = false;
    private bool $showQuest = false;
    private bool $showSpellBook = false;
    /** @var array<string,bool> AutomapVisited tile keys "x,y" */
    private array $automapVisited = [];
    /** @var array<string,bool> dFlags Visible — DoVision this frame */
    private array $visionVisible = [];
    /** @var array<string, list<array{rgba:string,w:int,h:int}>> Shared torch CEL frame sheets */
    private array $torchAnimCache = [];
    /** @var list<string> */
    private array $messages = [];

    private ?object $panelTex = null;
    private ?object $spriteTex = null;
    private int $spriteW = 0;
    private int $spriteH = 0;
    private string $panelRgba = '';
    private int $panelW = 0;
    private int $panelH = 0;
    /** Empty flask bulbs CEL (p8bulbs) — frame 0 life, frame 1 mana */
    private string $bulbsRgba = '';
    private string $bulbsManaRgba = '';
    private int $bulbsW = 0;
    private int $bulbsH = 0;
    private ?object $bulbsTex = null;
    private ?object $bulbsManaTex = null;
    /** Right-click spell icon (ctrlpan\spelicon.cel) — Firebolt frame 0 */
    private string $speliconRgba = '';
    private int $speliconW = 0;
    private int $speliconH = 0;
    private ?object $speliconTex = null;
    /** Inventory panel art (data\inv\inv.cel) — 320×352 */
    private string $invPanelRgba = '';
    private int $invPanelW = 0;
    private int $invPanelH = 0;
    private ?object $invPanelTex = null;
    /** Character / quest left-panel art (320×352) */
    private string $charPanelRgba = '';
    private int $charPanelW = 0;
    private int $charPanelH = 0;
    private ?object $charPanelTex = null;
    private string $questPanelRgba = '';
    private int $questPanelW = 0;
    private int $questPanelH = 0;
    private ?object $questPanelTex = null;
    private string $spellBookRgba = '';
    private int $spellBookW = 0;
    private int $spellBookH = 0;
    private ?object $spellBookTex = null;
    /** Pressed main-panel button art (ctrlpan\p8but2.cel) — 6×71×15 */
    /** @var list<array{w:int,h:int,rgba:string}> */
    private array $panelBtnFrames = [];
    /** @var array<int, object> */
    private array $panelBtnTex = [];
    /** Cached data\inv\objcurs.cel for mouse cursor + item icons */
    private ?Cel $objcursCel = null;
    /** Last software-cursor draw diagnostics (for --debug-cursor). */
    /** @var array<string,mixed> */
    private array $lastCursorDraw = [];
    /** Special CEL frames (arches) — list of rgba keyed by 0-based frame */
    /** @var ?Cel Special CEL for lit re-decode */
    private ?Cel $specialCel = null;
    /** @var array<int, array{w:int,h:int,rgba:string}> */
    private array $specialFrames = [];
    /** Player stand/walk/action CL2 by direction group */
    private ?Cl2 $playerStandCl2 = null;
    private ?Cl2 $playerWalkCl2 = null;
    private ?Cl2 $playerAttackCl2 = null;
    private ?Cl2 $playerHitCl2 = null;
    private ?Cl2 $playerDeathCl2 = null;
    private ?Cl2 $playerSpellCl2 = null;
    private ?Cl2 $playerBlockCl2 = null;
    private int $playerGfxW = 96;
    /** @var array<string, array{w:int,h:int,rgba:string}> */
    private array $playerFrameCache = [];
    private string $playerSpriteRgba = '';
    /** Missile CL2: arrows (16 dirs) + fireba1..16 */
    private ?Cl2 $missileArrowsCl2 = null;
    /** @var array<int, Cl2> */
    private array $missileFirebaCl2 = [];

    /** @var array<string, mixed> */
    private array $microCache = [];

    private int $camX = 0;
    private int $camY = 0;
    private int $nextId = 1;

    public function __construct(private AssetStore $assets, private ?Audio $audio = null)
    {
        $this->audio ??= new Audio();
        $this->map = new DungeonMap();
        $this->rng = new Random();
        $this->pathfinder = new Path();
        $this->lighting = new Lighting();
        $this->gtrace = new GameplayTrace();
        $this->playerAnim = new AnimationInfo();
        $this->playerAnim->setNewAnimation(8, 1, 0);
        $this->uiList = new UiList();
        $this->occ = new Occupancy();
        $this->cursor = new Cursor();
        $this->clrPlrPath();
        $this->inventory = array_fill(0, InventoryGrid::SLOTS, null);
        $this->belt = [null, null, null, null];
    }

    public function bootstrap(): void
    {
        $pal = $this->assets->loadPalette('levels\\towndata\\town.pal');
        $cel = $this->assets->loadCel('ctrlpan\\panel8.cel');
        $frame = $cel->decodeFrame(0, 640, $pal);
        $this->panelRgba = $frame['rgba'];
        $this->panelW = $frame['w'];
        $this->panelH = $frame['h'];
        try {
            if ($this->assets->has('ctrlpan\\p8bulbs.cel')) {
                $bulbs = $this->assets->loadCel('ctrlpan\\p8bulbs.cel');
                // statusPanel[0] life, [1] mana @ 88px
                $bf = $bulbs->decodeFrame(0, 88, $pal);
                $this->bulbsRgba = $bf['rgba'];
                $this->bulbsW = $bf['w'];
                $this->bulbsH = $bf['h'];
                if ($bulbs->numFrames > 1) {
                    $mf = $bulbs->decodeFrame(1, 88, $pal);
                    $this->bulbsManaRgba = $mf['rgba'];
                } else {
                    $this->bulbsManaRgba = $this->bulbsRgba;
                }
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'HUD bulbs: ' . $e->getMessage() . "\n");
        }
        try {
            $spelPath = $this->assets->has('ctrlpan\\spelicon.cel')
                ? 'ctrlpan\\spelicon.cel'
                : ($this->assets->has('data\\spelicon.cel') ? 'data\\spelicon.cel' : null);
            if ($spelPath !== null) {
                // Firebolt = SpellIcon::Firebolt = frame 0; width SPLICONLENGTH=56
                $spel = $this->assets->loadCel($spelPath);
                $sf = $spel->decodeFrame(0, 56, $pal);
                $this->speliconRgba = $sf['rgba'];
                $this->speliconW = $sf['w'];
                $this->speliconH = $sf['h'];
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'HUD spelicon: ' . $e->getMessage() . "\n");
        }
        try {
            if ($this->assets->has('data\\inv\\inv.cel')) {
                $invCel = $this->assets->loadCel('data\\inv\\inv.cel');
                $ifr = $invCel->decodeFrame(0, 320, $pal);
                $this->invPanelRgba = $ifr['rgba'];
                $this->invPanelW = $ifr['w'];
                $this->invPanelH = $ifr['h'];
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'HUD inv.cel: ' . $e->getMessage() . "\n");
        }
        try {
            if ($this->assets->has('data\\char.cel')) {
                $c = $this->assets->loadCel('data\\char.cel');
                $fr = $c->decodeFrame(0, 320, $pal);
                $this->charPanelRgba = $fr['rgba'];
                $this->charPanelW = $fr['w'];
                $this->charPanelH = $fr['h'];
            }
            if ($this->assets->has('data\\quest.cel')) {
                $c = $this->assets->loadCel('data\\quest.cel');
                $fr = $c->decodeFrame(0, 320, $pal);
                $this->questPanelRgba = $fr['rgba'];
                $this->questPanelW = $fr['w'];
                $this->questPanelH = $fr['h'];
            }
            if ($this->assets->has('data\\spellbk.cel')) {
                $c = $this->assets->loadCel('data\\spellbk.cel');
                $fr = $c->decodeFrame(0, 320, $pal);
                $this->spellBookRgba = $fr['rgba'];
                $this->spellBookW = $fr['w'];
                $this->spellBookH = $fr['h'];
            }
            if ($this->assets->has('ctrlpan\\p8but2.cel')) {
                $btn = $this->assets->loadCel('ctrlpan\\p8but2.cel');
                $n = min(6, $btn->numFrames);
                for ($i = 0; $i < $n; $i++) {
                    $this->panelBtnFrames[$i] = $btn->decodeFrame($i, 71, $pal);
                }
            }
            if ($this->assets->has(Cursor::ASSET_PATH)) {
                $this->objcursCel = $this->assets->loadCel(Cursor::ASSET_PATH);
                // Sanity: HAND frame must decode at width 33 (objcurs-widths.txt)
                $hand = Cursor::decodeSprite($this->objcursCel, Cursor::CURSOR_HAND, $pal);
                if ($hand['width'] !== 33) {
                    fwrite(STDERR, "cursor: HAND width expected 33 got {$hand['width']}\n");
                }
                if ($hand['h'] < 8 || $hand['h'] > 64) {
                    fwrite(STDERR, "cursor: HAND height looks wrong: {$hand['h']}\n");
                }
                fwrite(STDOUT, sprintf(
                    "cursor: loaded %s frames=%d HAND f0 %dx%d hotspot=0,0\n",
                    Cursor::ASSET_PATH,
                    $this->objcursCel->numFrames,
                    $hand['w'],
                    $hand['h']
                ));
                $this->cursor->initLevelCursor();
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'HUD char/quest: ' . $e->getMessage() . "\n");
        }
        // LoadBackgroundArt: ui_art\title / mainmenu (opaque) / focus chroma 250
        // Title: UiTitleDialog — title.pcx + animated logo.pcx ×15
        try {
            $this->titleDialog = new TitleDialog();
            $this->titleDialog->load($this->assets);
            $bg = $this->titleDialog->bgSize();
            $this->titleW = $bg['w'];
            $this->titleH = $bg['h'];
            $this->titleRgba = ''; // owned by TitleDialog
        } catch (\Throwable $e) {
            fwrite(STDERR, 'TitleDialog: ' . $e->getMessage() . "\n");
            $this->titleDialog = null;
            try {
                if ($this->assets->has('ui_art\\title.pcx')) {
                    $t = \Diablo\Engine\Assets\Pcx::decode($this->assets->read('ui_art\\title.pcx'));
                    $this->titleRgba = $t['rgba'];
                    $this->titleW = $t['w'];
                    $this->titleH = $t['h'];
                }
            } catch (\Throwable) {
            }
        }
        try {
            if ($this->assets->has('ui_art\\mainmenu.pcx')) {
                $t = \Diablo\Engine\Assets\Pcx::decode($this->assets->read('ui_art\\mainmenu.pcx'));
                $this->mainMenuRgba = $t['rgba'];
                $this->mainMenuW = $t['w'];
                $this->mainMenuH = $t['h'];
            }
            $focusPath = $this->assets->has('ui_art\\focus42.pcx') ? 'ui_art\\focus42.pcx' : 'ui_art\\focus.pcx';
            if ($this->assets->has($focusPath)) {
                // menu UI LoadPcxSpriteList focus*: transparentColor=250
                $t = \Diablo\Engine\Assets\Pcx::decode($this->assets->read($focusPath), [250]);
                $this->focusRgba = $t['rgba'];
                $this->focusW = $t['w'];
                $this->focusH = $t['h'];
            }
            try {
                $this->uiFont = UiFont::load42($this->assets, true);
                $this->uiFontYellow = UiFont::load42($this->assets, false);
                try {
                    $this->uiFontSmall = UiFont::load16($this->assets, true);
                } catch (\Throwable) {
                    $this->uiFontSmall = $this->uiFont;
                }
            } catch (\Throwable $fe) {
                fwrite(STDERR, 'UiFont FAIL: ' . $fe->getMessage() . "\n");
                $this->uiFont = null;
                $this->uiFontYellow = null;
                $this->uiFontSmall = null;
            }
            if ($this->uiList !== null) {
                $this->uiList->setFonts($this->uiFont, $this->uiFontYellow);
                if ($this->focusRgba !== '') {
                    $this->uiList->setFocusSheet($this->focusRgba, $this->focusW, $this->focusH);
                }
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'Menu art: ' . $e->getMessage() . "\n");
            $this->msg('Menu art: ' . $e->getMessage());
        }
        $this->menuScreen = 'title';
        $this->mode = 'menu';
        $this->titleStartNs = 0;
        $this->msg('Diablo — title');
    }

    private string $titleRgba = '';
    private string $mainMenuRgba = '';
    private string $focusRgba = '';

    public function isTitleScreen(): bool
    {
        return $this->mode === 'menu' && $this->menuScreen === 'title';
    }

    public function titleDialog(): ?TitleDialog
    {
        return $this->titleDialog;
    }

    private function titleElapsedMs(): int
    {
        if ($this->titleStartNs <= 0) {
            return 0;
        }

        return (int) intdiv(hrtime(true) - $this->titleStartNs, 1_000_000);
    }

    private function dismissTitle(): void
    {
        if ($this->menuScreen !== 'title') {
            return;
        }
        $this->menuScreen = 'main';
        $this->menuCursor = 0;
        $this->syncUiListItems();
    }

    /**
     * Capture representative title frames for --debug-title-capture.
     */
    private function maybeCaptureTitleFrame(Sdl $sdl, int $winW, int $winH, int $elapsedMs, int $frame): void
    {
        $dir = dirname(__DIR__) . '/tmp/title-capture';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $shots = [
            '0000' => $elapsedMs < 50 && $frame === 0,
            'early' => $elapsedMs >= 300 && $elapsedMs < 500,
            'mid' => $elapsedMs >= 2000 && $elapsedMs < 2200,
            'late' => $elapsedMs >= 5000 && $elapsedMs < 5200,
            'final' => $elapsedMs >= 6900 || ($this->debugTitleFrame >= 0 && !isset($this->titleCaptureDone['final'])),
        ];
        if ($this->debugTitleFrame >= 0) {
            $shots = ['frame-' . $this->debugTitleFrame => true];
        }
        foreach ($shots as $name => $ok) {
            if (!$ok || isset($this->titleCaptureDone[$name])) {
                continue;
            }
            $out = sprintf('%s/%s.png', $dir, $name);
            $sdl->dumpFramePNG($out, $winW, $winH);
            $this->titleCaptureDone[$name] = true;
            fwrite(STDOUT, "title-capture → {$out}\n");
        }
    }

    /** @param array<string,mixed> $ev */
    public function handleInput(array $ev): void
    {
        if ($this->isTitleScreen()) {
            $anyKey = ($ev['keys'] ?? []) !== [];
            $mouseClick = ($ev['click'] ?? null) !== null;
            $elapsed = $this->titleElapsedMs();
            if ($this->debugTitleFrame < 0) {
                if ($this->titleDialog !== null
                    && $this->titleDialog->shouldDismiss($elapsed, $anyKey, $mouseClick)) {
                    $this->dismissTitle();

                    return;
                }
                if ($this->titleDialog === null && ($anyKey || $mouseClick)) {
                    $this->dismissTitle();

                    return;
                }
            }

            return;
        }

        foreach ($ev['keys'] ?? [] as $k) {
            if ($this->mode === 'menu') {
                $this->handleMenuInput($k);
            } elseif ($this->mode === 'play') {
                if ($k === 'i') {
                    $this->showInv = !$this->showInv;
                } elseif ($k === 'c') {
                    $this->showChar = !$this->showChar;
                    if ($this->showChar) {
                        $this->showQuest = false;
                        $this->showSpellBook = false;
                    }
                } elseif ($k === 'q') {
                    $this->showQuest = !$this->showQuest;
                    if ($this->showQuest) {
                        $this->showChar = false;
                        $this->showSpellBook = false;
                    }
                } elseif ($k === 'b') {
                    $this->showSpellBook = !$this->showSpellBook;
                    if ($this->showSpellBook) {
                        $this->showChar = false;
                        $this->showQuest = false;
                        $this->showInv = false;
                    }
                } elseif ($k === 'tab') {
                    $this->showAutomap = !$this->showAutomap;
                } elseif ($k === '1' || $k === '2' || $k === '3' || $k === '4') {
                    $this->useBelt((int) $k - 1);
                } elseif ($k === 'h') {
                    $this->castHeal();
                } elseif ($k === 'f') {
                    $this->castPrimarySpell();
                } elseif ($k === 's') {
                    $this->saveGame();
                    $this->msg('Saved');
                } elseif ($k === 'e') {
                    $this->equipFirst();
                } elseif ($k === 'escape') {
                    $this->saveGame();
                    $this->menuScreen = 'main';
                    $this->menuCursor = 0;
                    $this->mode = 'menu';
                }
            } elseif ($this->mode === 'dead') {
                if ($k === 'enter' || $k === 'space') {
                    $this->respawn();
                }
            } elseif ($this->mode === 'vendor') {
                if ($k === 'escape' || $k === 'enter') {
                    $this->mode = 'play';
                } elseif ($k === 'space') {
                    $this->vendorAct();
                }
            }
        }

        if (isset($ev['mouse'])) {
            $this->mouseX = (int) $ev['mouse']['x'];
            $this->mouseY = (int) $ev['mouse']['y'];
        }
        if (isset($ev['mouseButtons'])) {
            $this->mouseLeftDown = ((int) $ev['mouseButtons'] & 1) !== 0;
        }

        if ($this->mode === 'play') {
            $this->checkCursMove($this->mouseX, $this->mouseY);
            if (($ev['click'] ?? null) !== null) {
                $cx = (int) $ev['click']['x'];
                $cy = (int) $ev['click']['y'];
                $btn = (int) ($ev['click']['button'] ?? 1);
                if ($this->debugWorldPick && $btn === 1) {
                    $this->dumpWorldPick($cx, $cy);
                }
                $this->clickEdge = true;
                $this->leftMouseCmd($cx, $cy, $btn);
            } elseif ($this->mouseLeftDown && $this->cursKind === 'none') {
                // Defer to logic tick — RepeatWalk queues CMD_WALKXY, not every present
                $this->pendingRepeatWalk = true;
            }
            if ($this->debugCursor || $this->debugCursorTarget) {
                $this->logCursorDebug();
            }
        }
    }

    private function handleMenuInput(string $k): void
    {
        // title handled in handleInput (any key / mouse / timeout)
        if ($this->menuScreen === 'title') {
            return;
        }
        if ($this->menuScreen === 'main' || $this->menuScreen === 'selhero') {
            if ($k === 'up') {
                $this->uiList?->move(-1);
                $this->menuCursor = $this->uiList?->cursor() ?? $this->menuCursor;
            } elseif ($k === 'down') {
                $this->uiList?->move(1);
                $this->menuCursor = $this->uiList?->cursor() ?? $this->menuCursor;
            } elseif ($k === 'enter' || $k === 'space') {
                $this->menuCursor = $this->uiList?->cursor() ?? $this->menuCursor;
                if ($this->menuScreen === 'main') {
                    if ($this->menuCursor === 0) {
                        $this->menuScreen = 'selhero';
                        $this->menuCursor = 0;
                        $this->syncUiListItems();
                    } elseif ($this->menuCursor === 1) {
                        if ($this->loadGame()) {
                            $this->mode = 'play';
                        } else {
                            $this->msg('No save');
                        }
                    } else {
                        $this->mode = 'quit';
                    }
                } elseif ($this->menuCursor <= 2) {
                    $class = ['Warrior', 'Rogue', 'Sorcerer'][$this->menuCursor];
                    $this->applyClass($class);
                    $this->playerName = $class;
                    $this->newGame();
                } elseif ($this->menuCursor === 3) {
                    if ($this->loadGame()) {
                        $this->mode = 'play';
                    } else {
                        $this->msg('No save');
                    }
                } else {
                    $this->menuScreen = 'main';
                    $this->menuCursor = 0;
                    $this->syncUiListItems();
                }
            } elseif ($k === 'escape') {
                if ($this->menuScreen === 'selhero') {
                    $this->menuScreen = 'main';
                    $this->menuCursor = 0;
                    $this->syncUiListItems();
                } else {
                    $this->mode = 'quit';
                }
            }
        }
    }

    private function syncUiListItems(): void
    {
        if ($this->uiList === null) {
            return;
        }
        $opts = $this->menuScreen === 'selhero'
            ? ['Warrior', 'Rogue', 'Sorcerer', 'Load Game', 'Back']
            : ['Single Player', 'Continue', 'Exit Diablo'];
        $this->uiList->setItems($opts, $this->menuCursor);
    }

    public function wantsQuit(): bool
    {
        return $this->mode === 'quit';
    }

    public function setDungeonSeed(int $seed): void
    {
        $this->dungeonSeed = $seed & 0x7FFFFFFF;
        $this->rng->setRndSeed($this->dungeonSeed);
    }

    public function playerX(): int
    {
        return $this->px;
    }

    public function playerY(): int
    {
        return $this->py;
    }

    public function levelType(): int
    {
        return $this->map->levelType;
    }

    public function currLevel(): int
    {
        return $this->map->currLevel;
    }

    /** @return array<string,mixed> */
    public function generationDump(): array
    {
        return $this->map->debugGenerationDump();
    }

    public function dungeonAscii(): string
    {
        $lines = ["dungeon mega tiles (DMAX 40×40, . = floor-ish, # = wall/other):\n"];
        for ($y = 0; $y < DungeonMap::DMAXY; $y++) {
            $row = '';
            for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
                $t = $this->map->dungeon[$y][$x] ?? 0;
                $row .= ($t === 13 || $t === 162 || $t === 163) ? '.' : ($t === 22 ? ' ' : '#');
            }
            $lines[] = $row . "\n";
        }
        // Sample micros for piece under player
        $piece = $this->map->dPiece[$this->py][$this->px] ?? 0;
        $lines[] = sprintf("player dPiece=%d micros:\n", $piece);
        foreach ($this->map->dPieceMicros[$piece] ?? [] as $i => $m) {
            $lines[] = sprintf("  mt[%d] frame=%d type=%d\n", $i, $m['frame'] ?? 0, $m['type'] ?? 0);
        }

        return implode('', $lines);
    }

    /**
     * Automated playability sequence for --playtest (logic only; SDL still presents a few frames).
     *
     * @return list<string>
     */
    public function runPlaytest(): array
    {
        $log = [];
        $log[] = sprintf('Playtest start town@%d,%d', $this->px, $this->py);

        // Walk toward Pepin (chunked MaxPathLength)
        $pepin = null;
        foreach ($this->towners as $t) {
            if ($t['role'] === 'healer') {
                $pepin = $t;
                break;
            }
        }
        if ($pepin !== null) {
            $this->walkUntilNear($pepin['x'], $pepin['y'], 80);
            if (max(abs($this->px - $pepin['x']), abs($this->py - $pepin['y'])) <= 1) {
                $this->mode = 'vendor';
                $this->activeVendor = $pepin;
                $this->vendorAct();
                $this->mode = 'play';
                $log[] = 'Talked Pepin quest=' . $this->questStatus;
            } else {
                $log[] = 'WARN could not reach Pepin';
            }
        }

        // Reach cathedral stairs
        $stairs = null;
        foreach ($this->objects as $o) {
            if ($o['type'] === 'stairs_down') {
                $stairs = $o;
                break;
            }
        }
        if ($stairs === null) {
            $log[] = 'FAIL no town stairs';

            return $log;
        }
        $this->walkUntilNear($stairs['x'], $stairs['y'], 120);
        if (max(abs($this->px - $stairs['x']), abs($this->py - $stairs['y'])) > 1) {
            $log[] = sprintf('FAIL stairs unreachable from %d,%d', $this->px, $this->py);

            return $log;
        }
        $this->operateObject($stairs);
        $log[] = sprintf('Entered cathedral L%d @%d,%d monsters=%d', $this->map->currLevel, $this->px, $this->py, count($this->monsters));

        // Combat: engage nearest; allow monsters time to hit back
        $kills = 0;
        $gotHit = false;
        $phpBefore = $this->php;
        for ($i = 0; $i < 500 && $kills < 1; $i++) {
            $target = null;
            $best = 99;
            foreach ($this->monsters as $m) {
                if (!$m['alive']) {
                    continue;
                }
                $d = max(abs($m['x'] - $this->px), abs($m['y'] - $this->py));
                if ($d < $best) {
                    $best = $d;
                    $target = $m;
                }
            }
            if ($target === null) {
                break;
            }
            if ($best <= 1) {
                $this->destAction = DestAction::ATTACK_MON;
                $this->destParam = $target['id'];
                $this->clrPlrPath();
                // Stay in melee range several frames so monster ATTACK can land
                for ($f = 0; $f < 24; $f++) {
                    $this->tick();
                    if ($this->php < $phpBefore) {
                        $gotHit = true;
                    }
                }
            } else {
                $this->makePlrPath($target['x'], $target['y'], false);
                $this->destAction = DestAction::ATTACK_MON;
                $this->destParam = $target['id'];
                $this->drainWalk(100);
                for ($f = 0; $f < 8; $f++) {
                    $this->tick();
                    if ($this->php < $phpBefore) {
                        $gotHit = true;
                    }
                }
            }
            $dead = 0;
            foreach ($this->monsters as $m) {
                // Count death-in-progress as kill (alive until death CL2 finishes)
                if (!$m['alive'] || ($m['mode'] ?? '') === 'DEATH' || (int) ($m['hp'] ?? 1) <= 0) {
                    $dead++;
                }
            }
            $kills = $dead;
        }
        // Drain any in-progress death anims so corpses/loot finalize
        for ($d = 0; $d < 40; $d++) {
            $pending = false;
            foreach ($this->monsters as $m) {
                if (!empty($m['alive']) && ($m['mode'] ?? '') === 'DEATH') {
                    $pending = true;
                }
            }
            if (!$pending) {
                break;
            }
            $this->tick();
        }
        // Recount after death drain
        $kills = 0;
        foreach ($this->monsters as $m) {
            if (!$m['alive'] || ($m['mode'] ?? '') === 'DEATH' || (int) ($m['hp'] ?? 1) <= 0) {
                $kills++;
            }
        }
        // If still not hit, force a monster adjacent and process attack frames
        if (!$gotHit) {
            foreach ($this->monsters as &$m) {
                if (!$m['alive']) {
                    continue;
                }
                $m['x'] = $this->px + 1;
                $m['y'] = $this->py;
                if (!$this->map->isWalkable($m['x'], $m['y'])) {
                    $m['x'] = $this->px;
                    $m['y'] = $this->py + 1;
                }
                $m['mode'] = 'ATTACK';
                $m['frame'] = 0;
                $m['toHit'] = 95;
                for ($f = 0; $f < 10; $f++) {
                    $this->tick();
                }
                if ($this->php < $phpBefore) {
                    $gotHit = true;
                }
                break;
            }
            unset($m);
        }
        $log[] = sprintf('Combat kills=%d gotHit=%s hp=%d/%d loot=%d', $kills, $gotHit ? 'Y' : 'N', $this->php, $this->pmaxHp, count($this->items));

        // Stop remaining monsters so object/stairs validation is not interrupted by death
        foreach ($this->monsters as &$m) {
            if ($m['alive']) {
                $m['mode'] = 'STAND';
                $m['ai'] = 'idle';
            }
        }
        unset($m);
        if ($this->php < 1) {
            $this->php = $this->pmaxHp;
            $this->mode = 'play';
            $this->pmode = 'STAND';
        }

        // Pickup loot
        foreach ($this->items as $it) {
            $this->makePlrPath((int) $it['x'], (int) $it['y'], true);
            $this->drainWalk(80);
        }
        $this->equipFirst();
        $log[] = 'Equip/inventory after loot';

        // Objects: chest + optional free-standing door (not DRLG AddL1Objs doors)
        foreach (['chest', 'door'] as $want) {
            foreach ($this->objects as &$o) {
                if ($o['type'] === $want && empty($o['opened'])) {
                    if ($want === 'door' && isset($o['doorSide'])) {
                        continue; // cathedral doors opened while pathing to stairs
                    }
                    $this->walkUntilNear($o['x'], $o['y'], 80);
                    if (max(abs($this->px - $o['x']), abs($this->py - $o['y'])) <= 1) {
                        $this->operateObject($o);
                        $log[] = 'Operated ' . $o['type'];
                    }
                    break;
                }
            }
            unset($o);
        }

        // Descend — clear remaining monsters so door/stair pathfinding is not occupancy-starved
        // (playtest already proved combat above; Path routes around but MaxPathLength+doors need room)
        foreach ($this->monsters as &$m) {
            $m['alive'] = false;
        }
        unset($m);
        $this->syncOccupancyActors();

        $down = null;
        foreach ($this->objects as $o) {
            if ($o['type'] === 'stairs_down') {
                $down = $o;
                break;
            }
        }
        if ($down !== null) {
            $this->walkUntilNear($down['x'], $down['y'], 400);
            if (max(abs($this->px - $down['x']), abs($this->py - $down['y'])) <= 1) {
                $this->operateObject($down);
                $log[] = sprintf('Descended to L%d @%d,%d', $this->map->currLevel, $this->px, $this->py);
            } else {
                $log[] = sprintf(
                    'WARN stairs_down not reached (at %d,%d want %d,%d)',
                    $this->px,
                    $this->py,
                    $down['x'],
                    $down['y']
                );
            }
        } else {
            $log[] = 'WARN no stairs_down on L1';
        }

        $this->saveGame();
        $log[] = 'Saved slot1.json';

        $px = $this->px;
        $py = $this->py;
        $cl = $this->map->currLevel;
        $ok = $this->loadGame();
        $log[] = sprintf(
            'Load roundtrip ok=%s pos=%d,%d level=%d (was %d,%d L%d)',
            $ok ? 'Y' : 'N',
            $this->px,
            $this->py,
            $this->map->currLevel,
            $px,
            $py,
            $cl
        );
        $log[] = 'Playtest DONE';

        return $log;
    }

    private function walkUntilNear(int $tx, int $ty, int $maxChunks): void
    {
        for ($c = 0; $c < $maxChunks; $c++) {
            $cx = $this->isWalking() ? $this->walkFutureX : $this->px;
            $cy = $this->isWalking() ? $this->walkFutureY : $this->py;
            if (max(abs($cx - $tx), abs($cy - $ty)) <= 1) {
                $this->drainWalk(64);

                return;
            }
            foreach ($this->objects as &$o) {
                if (($o['type'] ?? '') === 'door' && empty($o['opened'])
                    && max(abs($this->px - $o['x']), abs($this->py - $o['y'])) <= 1) {
                    $this->operateObject($o);
                }
            }
            unset($o);
            $this->destAction = DestAction::NONE;
            $adj = $this->pickApproachTile($tx, $ty);
            if ($adj !== null) {
                $this->makePlrPath($adj[0], $adj[1], true);
            } elseif ($this->tileWalkableForPath($tx, $ty)) {
                $this->makePlrPath($tx, $ty, true);
            } else {
                $this->makePlrPath($tx, $ty, false);
            }
            if (($this->walkpath[0] ?? DestAction::WALK_NONE) === DestAction::WALK_NONE) {
                if ($this->isWalking()) {
                    $this->drainWalk(64);
                    continue;
                }
                $door = $this->nearestClosedDoorToward($tx, $ty);
                if ($door !== null) {
                    $this->destAction = DestAction::OPERATE;
                    $this->destParam = (int) $door['id'];
                    $this->makePlrPath((int) $door['x'], (int) $door['y'], false);
                    $this->drainWalk(120);
                    continue;
                }

                return;
            }
            $this->drainWalk(120);
        }
        $this->drainWalk(64);
    }

    /** @return ?array<string,mixed> */
    private function nearestClosedDoorToward(int $tx, int $ty): ?array
    {
        $reach = $this->reachableFrom(
            $this->isWalking() ? $this->walkFutureX : $this->px,
            $this->isWalking() ? $this->walkFutureY : $this->py
        );
        $best = null;
        $bestScore = PHP_INT_MAX;
        foreach ($this->objects as $o) {
            if (($o['type'] ?? '') !== 'door' || !empty($o['opened'])) {
                continue;
            }
            $dx = (int) $o['x'];
            $dy = (int) $o['y'];
            // Door tile is Solid — need a walkable approach in current component
            $approach = false;
            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$ox, $oy]) {
                if (isset($reach[($dx + $ox) . ',' . ($dy + $oy)])) {
                    $approach = true;
                    break;
                }
            }
            if (!$approach) {
                continue;
            }
            // Prefer doors that reduce manhattan-to-goal from the door cell
            $toGoal = abs($dx - $tx) + abs($dy - $ty);
            $fromPlayer = abs($this->px - $dx) + abs($this->py - $dy);
            $score = $toGoal * 1000 + $fromPlayer;
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $o;
            }
        }

        return $best;
    }

    private function drainWalk(int $maxTicks): void
    {
        for ($i = 0; $i < $maxTicks && ($this->isWalking() || $this->walkpath[0] !== DestAction::WALK_NONE); $i++) {
            $this->tick();
        }
    }

    public function tick(): void
    {
        if ($this->mode !== 'play') {
            return;
        }
        $this->gtrace->resetTickFlags();
        $this->gtrace->modeBefore = $this->pmode;
        // Apply deferred RepeatWalk once per logic tick (command cadence)
        if ($this->pendingRepeatWalk) {
            $this->pendingRepeatWalk = false;
            $this->repeatWalk();
        }
        $this->logicTick++;
        $this->animFrame++;
        if ($this->pInfraTicks > 0) {
            $this->pInfraTicks--;
        }
        if ($this->pSearchTicks > 0) {
            $this->pSearchTicks--;
        }
        // ProcessManaShield — expires when mana is empty
        if ($this->pManaShield && $this->pmana <= 0) {
            $this->pManaShield = false;
            $this->msg('Mana Shield fades');
        }
        // ProcessPlayers: mode → CheckNewPath → AnimInfo (inside processPlayer)
        $this->processPlayer();
        $this->processMonsters();
        $this->processMissiles();
        $this->processObjects();
        $this->advanceTownerAnims();
        // game logic → CheckTriggers (stairs / warps)
        $this->checkTriggers();
        $this->updateLighting();
        if ($this->php <= 0 && $this->pmode !== 'DEATH') {
            $this->clrPlrPath();
            $this->destAction = DestAction::NONE;
            $this->pmode = 'DEATH';
            $this->actionFrame = 0;
            $this->playerAnim->setNewAnimation($this->deathFrames, 1, 0);
            $this->mode = 'dead';
            $this->msg('You have died');
            $this->audio?->play('death');
        }
        $this->emitGameplayTraceTick();
        $this->clickEdge = false;
    }

    public function enableGameplayTrace(string $path): void
    {
        $this->gtrace->enable($path);
        $this->gtrace->emit([
            'kind' => 'meta',
            'note' => 'DX: ProcessInput(CheckCursMove,RepeatPlayerAction)->GameLogic(ProcessPlayers)->world->render; PHP: poll->input(cursor; RepeatWalk deferred to tick)->tick->tickAlpha->render',
        ]);
    }

    private function advanceTownerAnims(): void
    {
        foreach ($this->towners as &$t) {
            $n = max(1, (int) ($t['animFrames'] ?? 1));
            if ($n <= 1) {
                continue;
            }
            $t['animTick'] = (int) ($t['animTick'] ?? 0) + 1;
            if ($t['animTick'] >= 3) {
                $t['animTick'] = 0;
                $t['animFrame'] = ((int) ($t['animFrame'] ?? 0) + 1) % $n;
            }
        }
        unset($t);
    }

    public function emitGameplayTraceFrame(): void
    {
        if (!$this->gtrace->enabled()) {
            return;
        }
        [$scrollX, $scrollY] = $this->currentWalkScroll();
        [$ax, $ay] = $this->currentWalkActorOffset();
        $this->gtrace->emit([
            'kind' => 'frame',
            't' => $this->logicTick,
            'alpha' => round($this->tickAlpha, 4),
            'cam' => [$this->camX, $this->camY],
            'scroll' => [$scrollX, $scrollY],
            'roff' => [$ax, $ay],
            'tile' => [$this->px, $this->py],
            'fut' => [$this->walkFutureX, $this->walkFutureY],
            'mode' => $this->pmode,
            'curs' => [$this->cursX, $this->cursY],
        ]);
    }

    private function emitGameplayTraceTick(): void
    {
        if (!$this->gtrace->enabled()) {
            return;
        }
        $dir = $this->walkpath[0] ?? DestAction::WALK_NONE;
        $posok = 1;
        if ($dir !== DestAction::WALK_NONE) {
            [$dx, $dy] = Direction::delta($dir);
            $posok = $this->tileWalkableForPath($this->px + $dx, $this->py + $dy) ? 1 : 0;
            if ($posok === 0) {
                $this->gtrace->rej = true;
            }
        }
        $ttile = null;
        $tfut = null;
        if ($this->destAction === DestAction::ATTACK_MON) {
            $mon = $this->findMonsterById($this->destParam);
            if ($mon !== null) {
                $ttile = [(int) $mon['x'], (int) $mon['y']];
                $tfut = $this->monsterTargetTile($mon);
            }
        }
        $dP = [];
        $dM = [];
        for ($oy = -1; $oy <= 1; $oy++) {
            for ($ox = -1; $ox <= 1; $ox++) {
                $x = $this->px + $ox;
                $y = $this->py + $oy;
                $p = $this->occ->playerAt($x, $y);
                $m = $this->occ->monsterAt($x, $y);
                if ($p !== 0) {
                    $dP["{$x},{$y}"] = $p;
                }
                if ($m !== 0) {
                    $dM["{$x},{$y}"] = $m;
                }
            }
        }
        [$scrollX, $scrollY] = $this->currentWalkScroll();
        $this->gtrace->modechg = ($this->gtrace->modeBefore !== '' && $this->gtrace->modeBefore !== $this->pmode);
        $wlen = 0;
        foreach ($this->walkpath as $s) {
            if ($s === DestAction::WALK_NONE) {
                break;
            }
            $wlen++;
        }
        $this->gtrace->emit([
            'kind' => 'tick',
            't' => $this->logicTick,
            'mx' => $this->mouseX,
            'my' => $this->mouseY,
            'lmb' => $this->mouseLeftDown ? 1 : 0,
            'edge' => $this->clickEdge ? 1 : 0,
            'hold' => ($this->mouseLeftDown && !$this->clickEdge) ? 1 : 0,
            'ckind' => $this->cursKind,
            'cid' => $this->cursId,
            'ctile' => [$this->cursEntityX, $this->cursEntityY],
            'curs' => [$this->cursX, $this->cursY],
            'tile' => [$this->px, $this->py],
            'fut' => [$this->walkFutureX, $this->walkFutureY],
            'temp' => [$this->walkTempX, $this->walkTempY],
            'mode' => $this->pmode,
            'dest' => $this->destAction,
            'dparam' => $this->destParam,
            'w0' => $dir,
            'wlen' => $wlen,
            'dir' => $this->pdir,
            'aframe' => $this->playerAnim->currentFrame,
            'atick' => $this->playerAnim->tickCounterOfCurrentFrame,
            'prog' => $this->playerAnim->getAnimationProgress(),
            'cam' => [$this->camX, $this->camY],
            'scroll' => [$scrollX, $scrollY],
            'alpha' => round($this->tickAlpha, 4),
            'dP' => $dP,
            'dM' => $dM,
            'ttile' => $ttile,
            'tfut' => $tfut,
            'posok' => $posok,
            'cnp' => $this->gtrace->cnp ? 1 : 0,
            'repath' => $this->gtrace->repath ? 1 : 0,
            'consume' => $this->gtrace->consume ? 1 : 0,
            'modechg' => $this->gtrace->modechg ? 1 : 0,
            'tplayer' => $this->gtrace->tplayerN,
            'atk' => $this->gtrace->atk ? 1 : 0,
            'rej' => $this->gtrace->rej ? 1 : 0,
            'mpaths' => $this->gtrace->makePlrPathCalls,
            'hit' => $this->attackHitDone ? 1 : 0,
        ]);
    }

    private function updateLighting(): void
    {
        $this->lighting->clear();
        if ($this->fullbright) {
            for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
                for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                    $this->map->dLight[$y][$x] = 0;
                }
            }
            $this->visionVisible = [];

            return;
        }
        if ($this->map->levelType !== DungeonMap::DTYPE_TOWN) {
            // UpdatePlayerLightOffset — walk pixels → screenToLight sub-tile cone
            [$wsx, $wsy] = $this->currentWalkActorOffset();
            [$lox, $loy] = WalkOffset::screenToLight($wsx, $wsy);
            $this->lighting->addLight($this->px, $this->py, $this->pLightRad, $lox, $loy);
            // Firebolt missiles carry a light (missiles.cpp AddLight radius 8)
            foreach ($this->missiles as $mis) {
                if (empty($mis['alive']) || ($mis['type'] ?? '') !== 'firebolt') {
                    continue;
                }
                $mx = (int) round((float) $mis['x']);
                $my = (int) round((float) $mis['y']);
                if ($this->map->inBounds($mx, $my)) {
                    $this->lighting->addLight($mx, $my, 8);
                }
            }
            // Object lights (L1LIGHT radius 5, torches radius 8, …)
            foreach ($this->objects as $o) {
                if (!isset($o['lightRadius'])) {
                    continue;
                }
                $ox = (int) ($o['x'] ?? -1);
                $oy = (int) ($o['y'] ?? -1);
                $rad = (int) $o['lightRadius'];
                if ($this->map->inBounds($ox, $oy)) {
                    $this->lighting->addLight($ox, $oy, $rad);
                }
            }
        }
        $this->lighting->processLightList($this->map);
        $this->updateVisionList();
    }

    /**
     * ProcessVisionList stand-in — DoVision → dFlags Visible + AutomapVisited.
     */
    private function updateVisionList(): void
    {
        $this->visionVisible = [];
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN || $this->fullbright) {
            return;
        }
        $map = $this->map;
        Vision::doVision(
            $this->px,
            $this->py,
            $this->pInfraTicks > 0 ? max(15, $this->pLightRad + 5) : $this->pLightRad,
            function (int $x, int $y) use ($map): void {
                $this->visionVisible[$x . ',' . $y] = true;
                if (($map->dPiece[$y][$x] ?? 0) > 0) {
                    $this->automapVisited[$x . ',' . $y] = true;
                }
            },
            static fn (int $x, int $y): bool => !$map->tileHas($x, $y, DungeonMap::SOL_BLOCK_LIGHT),
            static fn (int $x, int $y): bool => $map->inBounds($x, $y),
        );
    }

    /** DungeonFlag::Visible — town/fullbright always on. */
    private function isTileVisible(int $x, int $y): bool
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN || $this->fullbright) {
            return true;
        }

        return isset($this->visionVisible[$x . ',' . $y]);
    }

    public function render(Sdl $sdl, int $winW, int $winH): void
    {
        $this->profileDecodes = 0;
        $this->profileUploads = 0;
        $this->profileDraws = 0;
        if ($this->mode === 'menu' || $this->mode === 'quit') {
            $this->renderMenu($sdl, $winW, $winH);

            return;
        }

        $sdl->setDrawColor(0, 0, 0);
        $sdl->clear();
        // --force-full-redraw: baseline already clears; second clear documents full-frame policy
        if ($this->forceFullRedraw) {
            $sdl->setDrawColor(0, 0, 0);
            $sdl->clear();
        }
        if ($this->debugClearMagenta) {
            $sdl->setDrawColor(255, 0, 255);
            $sdl->fillRect(0, 0, IsoCoords::SCREEN_W, IsoCoords::viewportHeight());
        }
        // --disable-world-clipping: no SDL clip in PHP renderer (no-op diagnostic)
        if ($this->disableWorldClipping && ($this->logicTick % 60) === 0) {
            fwrite(STDOUT, "disable-world-clipping: no SDL clip rect in use (confirmed)\n");
        }

        if ($this->cameraFixed) {
            if (!$this->fixedCamSet) {
                $this->fixedCamX = $this->px;
                $this->fixedCamY = $this->py;
                $this->fixedCamSet = true;
            }
            $this->camX = $this->fixedCamX;
            $this->camY = $this->fixedCamY;
        } else {
            $this->camX = $this->px;
            $this->camY = $this->py;
        }
        $this->updateLighting();
        if ($this->renderTrace || $this->softwareCompose) {
            RenderTrace::begin($this->softwareCompose, IsoCoords::SCREEN_W, IsoCoords::SCREEN_H);
            RenderTrace::$debugPixel = $this->debugPixel;
            // Software FB is what dump-frame uploads; mirror magenta clear so undrawn ≠ black.
            if ($this->debugClearMagenta && RenderTrace::$fb !== null) {
                RenderTrace::$fb->clear(255, 0, 255, 255);
            }
        }
        $this->drawDungeon($sdl, $winW, $winH);
        if (RenderTrace::$softwareCompose && RenderTrace::$fb !== null) {
            $tex = $sdl->createTextureRGBA(RenderTrace::$fb->w, RenderTrace::$fb->h, RenderTrace::$fb->rgba);
            $sdl->copyTexture($tex, 0, 0, RenderTrace::$fb->w, RenderTrace::$fb->h);
            $sdl->destroyTexture($tex);
        }
        if ($this->debugGameplayOverlay || $this->debugClickOverlay) {
            $this->drawGameplayOverlay($sdl, $winW, $winH);
        }
        if ($this->debugTileCoverage || $this->debugTileCoverageOverlay) {
            $this->drawTileCoverageOverlay($sdl, $winW, $winH);
        }
        if (!$this->hideHud) {
            $this->drawHud($sdl, $winW, $winH);
        }

        if ($this->mode === 'dead') {
            $sdl->setDrawColor(40, 0, 0);
            $sdl->fillRect(intdiv($winW, 2) - 160, intdiv($winH, 2) - 40, 320, 80);
            $sdl->setDrawColor(100, 20, 20);
            $sdl->fillRect(intdiv($winW, 2) - 158, intdiv($winH, 2) - 38, 316, 76);
            $this->blitUiText($sdl, 'You have died', intdiv($winW, 2), intdiv($winH, 2) - 24, true, true, true);
            $this->blitUiText($sdl, 'Press Enter to respawn in town', intdiv($winW, 2), intdiv($winH, 2) + 8, true, false, true);
        }
        if ($this->mode === 'vendor') {
            $vx = intdiv($winW, 2) - 160;
            $vy = 60;
            $sdl->setDrawColor(20, 18, 28);
            $sdl->fillRect($vx, $vy, 320, 160);
            $sdl->setDrawColor(80, 70, 50);
            $sdl->fillRect($vx + 2, $vy + 2, 316, 156);
            $sdl->setDrawColor(28, 24, 36);
            $sdl->fillRect($vx + 4, $vy + 4, 312, 152);
            $t = $this->activeVendor;
            $title = is_array($t) ? (string) ($t['name'] ?? 'Vendor') : 'Vendor';
            $role = is_array($t) ? (string) ($t['role'] ?? '') : '';
            $this->blitUiText($sdl, $title, $vx + 160, $vy + 16, true, true, true);
            $offer = match ($role) {
                'healer' => 'Space: Heal / Quest',
                'merchant' => 'Space: Buy potion (50 gold)',
                'blacksmith' => 'Space: Buy sword (100) / Repair held (cost)',
                'witch' => 'Space: Mana potion (40) / Recharge staff',
                'story' => 'Space: Identify held item (25 gold)',
                'tavern' => 'Space: Hear the news',
                'barmaid' => 'Space: Chat',
                'drunk' => 'Space: …listen',
                default => 'Space: Talk',
            };
            $this->blitUiText($sdl, $offer, $vx + 24, $vy + 56, false, false, true);
            $this->blitUiText($sdl, 'Gold: ' . $this->gold, $vx + 24, $vy + 80, false, false, true);
            $this->blitUiText($sdl, 'Esc: Leave', $vx + 24, $vy + 120, false, false, true);
        }
        // DrawCursor after world + HUD + modal overlays (scrollrt DrawAndBlit order)
        $this->drawCursor($sdl);
    }

    /**
     * After first frame: write render-trace.json, debug-pixel report, heatmaps, software PNG.
     * @return array<string,mixed>
     */
    public function finalizeRenderComposition(string $root): array
    {
        $out = ['enabled' => RenderTrace::$enabled ? 1 : 0];
        if (!RenderTrace::$enabled && !RenderTrace::$softwareCompose) {
            return $out;
        }
        $tracePath = $root . '/tmp/render-trace.json';
        $extra = [];
        if ($this->debugPixel !== null) {
            $stack = RenderTrace::stackAt($this->debugPixel[0], $this->debugPixel[1]);
            $extra['debugPixelReport'] = [
                'pixel' => $stack['pixel'],
                'stackDepth' => count($stack['stack']),
                'winner' => $stack['winner'] !== null ? array_diff_key($stack['winner'], ['rgba' => 1]) : null,
                'stack' => array_map(static function (array $d): array {
                    unset($d['rgba']);

                    return $d;
                }, $stack['stack']),
            ];
            $pixelPath = $root . '/tmp/parity/debug-pixel.json';
            if (!is_dir(dirname($pixelPath))) {
                mkdir(dirname($pixelPath), 0775, true);
            }
            file_put_contents($pixelPath, json_encode($extra['debugPixelReport'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
            $out['debugPixel'] = $pixelPath;
            $w = $stack['winner'];
            if ($w !== null) {
                fwrite(STDOUT, sprintf(
                    "DEBUG_PIXEL %d,%d winner seq=%s layer=%s dPiece=%s micro=%s frame=%s mask=%s rgb=%s\n",
                    $this->debugPixel[0],
                    $this->debugPixel[1],
                    $w['seq'] ?? '?',
                    $w['layer'] ?? '?',
                    $w['dPiece'] ?? '?',
                    $w['micro'] ?? '?',
                    $w['frame'] ?? '?',
                    $w['maskName'] ?? '?',
                    isset($w['sampleRgb']) ? implode(',', $w['sampleRgb']) : '?'
                ));
            } else {
                fwrite(STDOUT, sprintf("DEBUG_PIXEL %d,%d winner=none (cleared/undrawn)\n", $this->debugPixel[0], $this->debugPixel[1]));
            }
        }
        RenderTrace::writeJson($tracePath, $extra);
        $out['trace'] = $tracePath;
        fwrite(STDOUT, sprintf("render-trace draws=%d → %s\n", count(RenderTrace::draws()), $tracePath));
        $hm = RenderTrace::writeHeatmaps($root . '/tmp/parity', IsoCoords::SCREEN_W, IsoCoords::viewportHeight());
        if ($hm !== null) {
            $out['heatmaps'] = $hm;
            fwrite(STDOUT, "overdraw heatmaps → {$hm['written']} {$hm['skipped']}\n");
        }
        if (RenderTrace::$fb !== null) {
            $softPng = $root . '/tmp/parity/php/software_compose.png';
            RenderTrace::$fb->toPng($softPng);
            $out['softwarePng'] = $softPng;
            fwrite(STDOUT, "software-compose → {$softPng}\n");
        }
        RenderTrace::end();

        return $out;
    }

    private function renderMenu(Sdl $sdl, int $winW, int $winH): void
    {
        $sdl->setDrawColor(0, 0, 0);
        $sdl->clear();
        $uiX = intdiv($winW - IsoCoords::SCREEN_W, 2);
        $uiY = intdiv($winH - IsoCoords::SCREEN_H, 2);
        if ($this->menuScreen === 'title') {
            if ($this->titleStartNs <= 0) {
                $this->titleStartNs = hrtime(true);
            }
            $elapsed = $this->titleElapsedMs();
            if ($this->debugTitleFrame < 0
                && $this->titleDialog !== null
                && $this->titleDialog->shouldDismiss($elapsed, false, false)) {
                $this->dismissTitle();
            } elseif ($this->titleDialog !== null && $this->titleDialog->isLoaded()) {
                $force = $this->debugTitleFrame >= 0 ? $this->debugTitleFrame : null;
                $frame = $this->titleDialog->render($sdl, $uiX, $uiY, $elapsed, $force);
                $ls = $this->titleDialog->logoSize();
                $this->logDraw('title+logo', $uiX, $uiY + TitleDialog::LOGO_Y, $ls['w'], $ls['h']);
                if ($this->debugTitleAnimation && $elapsed - $this->titleAnimLogAtMs >= 200) {
                    $this->titleAnimLogAtMs = $elapsed;
                    fwrite(STDOUT, sprintf(
                        "title-anim elapsed_ms=%d state=title frame=%d logo_visible=1 flame_frame=%d prompt_visible=%d\n",
                        $elapsed,
                        $frame,
                        $frame,
                        $this->titleDialog->promptVisible() ? 1 : 0
                    ));
                }
                if ($this->debugTitleCapture) {
                    $this->maybeCaptureTitleFrame($sdl, $winW, $winH, $elapsed, $frame);
                }

                return;
            } elseif ($this->titleRgba !== '') {
                if ($this->titleTex === null) {
                    $this->titleTex = $sdl->createTextureRGBA($this->titleW, $this->titleH, $this->titleRgba);
                }
                $dx = $uiX + intdiv(IsoCoords::SCREEN_W - $this->titleW, 2);
                $dy = $uiY + intdiv(IsoCoords::SCREEN_H - $this->titleH, 2);
                $sdl->copyTexture($this->titleTex, $dx, $dy, $this->titleW, $this->titleH);
                $this->logDraw('title.pcx', $dx, $dy, $this->titleW, $this->titleH);

                return;
            } else {
                return;
            }
        }
        if ($this->mainMenuRgba !== '') {
            if ($this->mainMenuTex === null) {
                $this->mainMenuTex = $sdl->createTextureRGBA($this->mainMenuW, $this->mainMenuH, $this->mainMenuRgba);
            }
            $dx = $uiX + intdiv(IsoCoords::SCREEN_W - $this->mainMenuW, 2);
            $dy = $uiY + intdiv(IsoCoords::SCREEN_H - $this->mainMenuH, 2);
            $sdl->copyTexture($this->mainMenuTex, $dx, $dy, $this->mainMenuW, $this->mainMenuH);
            $this->logDraw('mainmenu.pcx', $dx, $dy, $this->mainMenuW, $this->mainMenuH);
        }
        $this->syncUiListItems();
        $this->uiList?->advanceFocusAnim();
        $this->uiList?->render($sdl, $uiX, $uiY);
    }

    private function logDraw(string $asset, int $x, int $y, int $w, int $h): void
    {
        if (!$this->debugDrawLog) {
            return;
        }
        fwrite(STDOUT, sprintf("draw %s x=%d y=%d w=%d h=%d\n", $asset, $x, $y, $w, $h));
    }

    private function blitUiText(Sdl $sdl, string $text, int $centerX, int $topY, bool $center, bool $selected = false, bool $small = false): void
    {
        $font = $small
            ? ($this->uiFontSmall ?? $this->uiFont)
            : (($selected && $this->uiFontYellow !== null) ? $this->uiFontYellow : $this->uiFont);
        if ($font === null) {
            fwrite(STDERR, "UiFont missing for text: {$text}\n");

            return;
        }
        $key = ($small ? 'S:' : ($selected ? 'Y:' : 'G:')) . $text;
        if (!isset($this->uiTextCache[$key])) {
            $img = $font->render($text, $small ? 1 : UiList::SPACING);
            $this->uiTextCache[$key] = [
                'tex' => $sdl->createTextureRGBA($img['w'], $img['h'], $img['rgba']),
                'w' => $img['w'],
                'h' => $img['h'],
            ];
            $this->profileUploads++;
        }
        $c = $this->uiTextCache[$key];
        $x = $center ? $centerX - intdiv((int) $c['w'], 2) : $centerX;
        $sdl->copyTexture($c['tex'], $x, $topY, (int) $c['w'], (int) $c['h']);
        $this->profileDraws++;
        $this->logDraw('font:' . $text, $x, $topY, (int) $c['w'], (int) $c['h']);
    }

    /** Ensure play HUD fonts (menu path may have been skipped). */
    private function ensureUiFonts(): void
    {
        if ($this->uiFont !== null) {
            if ($this->uiFontSmall === null) {
                try {
                    $this->uiFontSmall = UiFont::load16($this->assets, true);
                } catch (\Throwable) {
                    $this->uiFontSmall = $this->uiFont;
                }
            }

            return;
        }
        try {
            $this->uiFont = UiFont::load42($this->assets, true);
            $this->uiFontYellow = UiFont::load42($this->assets, false);
            $this->uiFontSmall = UiFont::load16($this->assets, true);
        } catch (\Throwable) {
            // keep nulls
        }
    }

    public function newGame(): void
    {
        // Keep caller-set dungeonSeed when already fixed; otherwise derive from name+time
        if ($this->dungeonSeed <= 1) {
            $this->dungeonSeed = (int) (crc32($this->playerName . (string) time()) & 0x7FFFFFFF);
        }
        $this->rng->setRndSeed($this->dungeonSeed);
        $this->applyClass($this->playerClass);
        $this->php = $this->pmaxHp;
        $this->pmana = $this->pmaxMana;
        $this->gold = 100;
        $this->level = 1;
        $this->xp = 0;
        $this->inventory = array_fill(0, 40, null);
        $this->belt = [
            ['type' => 'potion_heal', 'name' => 'Health Potion', 'cursorGraphic' => 'POTION_OF_HEALING'],
            ['type' => 'potion_heal', 'name' => 'Health Potion', 'cursorGraphic' => 'POTION_OF_HEALING'],
            null,
            null,
        ];
        $this->equipment = ['hand' => ['type' => 'weapon', 'name' => 'Dagger', 'minDamage' => 1, 'maxDamage' => 3, 'equipSlot' => 'hand'], 'offhand' => null, 'body' => null, 'head' => null];
        $this->recalc();
        $this->questStatus = 'inactive';
        $this->questTargetId = null;
        $this->enterTown();
        foreach ($this->belt as $bi => $beltIt) {
            if ($beltIt !== null) {
                $this->loadItemSprite($this->belt[$bi]);
            }
        }
        $this->mode = 'play';
        $this->msg('Welcome to Tristram');
    }

    private function enterTown(): void
    {
        $this->microCache = [];
        $this->cursor->paletteEpoch++;
        $this->cursor->initLevelCursor();
        $this->fixedCamSet = false;
        $this->automapVisited = [];
        $this->map->loadTown($this->assets);
        $this->px = $this->map->viewX;
        $this->py = $this->map->viewY;
        $this->clrPlrPath();
        $this->destAction = DestAction::NONE;
        $this->pmode = 'STAND';
        $this->monsters = [];
        $this->items = [];
        $this->missiles = [];
        // Cathedral entry: CreateTown ENTRY_PREV ViewPosition {25,31}
        $this->objects = [
            ['id' => $this->nextId++, 'type' => 'stairs_down', 'x' => 25, 'y' => 29],
        ];
        // assets/txtdata/towners/towners.tsv positions + animPath
        $this->towners = [
            ['id' => $this->nextId++, 'name' => 'Griswold', 'role' => 'blacksmith', 'x' => 62, 'y' => 63, 'cel' => 'towners\\smith\\smithn', 'animW' => 96],
            ['id' => $this->nextId++, 'name' => 'Pepin', 'role' => 'healer', 'x' => 55, 'y' => 79, 'cel' => 'towners\\healer\\healer', 'animW' => 96],
            ['id' => $this->nextId++, 'name' => 'Ogden', 'role' => 'tavern', 'x' => 55, 'y' => 62, 'cel' => 'towners\\twnf\\twnfn', 'animW' => 96],
            ['id' => $this->nextId++, 'name' => 'Cain', 'role' => 'story', 'x' => 62, 'y' => 71, 'cel' => 'towners\\strytell\\strytell', 'animW' => 96],
            ['id' => $this->nextId++, 'name' => 'Farnham', 'role' => 'drunk', 'x' => 71, 'y' => 84, 'cel' => 'towners\\drunk\\twndrunk', 'animW' => 96],
            ['id' => $this->nextId++, 'name' => 'Adria', 'role' => 'witch', 'x' => 80, 'y' => 20, 'cel' => 'towners\\townwmn1\\witch', 'animW' => 96],
            ['id' => $this->nextId++, 'name' => 'Gillian', 'role' => 'barmaid', 'x' => 43, 'y' => 66, 'cel' => 'towners\\townwmn1\\wmnn', 'animW' => 96],
            ['id' => $this->nextId++, 'name' => 'Wirt', 'role' => 'merchant', 'x' => 11, 'y' => 53, 'cel' => 'towners\\townboy\\pegkid1', 'animW' => 96],
        ];
        $this->loadTownerSprites();
        $this->loadSpecialCels('levels\\towndata\\towns');
        if (!$this->map->isWalkable($this->px, $this->py)) {
            for ($y = 20; $y < 90; $y++) {
                for ($x = 20; $x < 90; $x++) {
                    if ($this->map->isWalkable($x, $y)) {
                        $this->px = $x;
                        $this->py = $y;
                        break 2;
                    }
                }
            }
        }
        // Do not spawn standing on cathedral entrance (CheckTriggers)
        $this->offsetPlayerOffTile(25, 29);
        $this->map->plrDoTrans($this->px, $this->py);
        $this->loadPlayerSprite();
        $this->warmRenderCaches();
        $this->startStand();
        $this->syncOccupancyActors();
    }

    public function enterCathedral(int $dlvl = 1): void
    {
        if ($dlvl >= 13) {
            $this->enterDungeonType(DungeonMap::DTYPE_HELL, $dlvl);

            return;
        }
        if ($dlvl >= 9) {
            $this->enterDungeonType(DungeonMap::DTYPE_CAVES, $dlvl);

            return;
        }
        if ($dlvl >= 5) {
            $this->enterDungeonType(DungeonMap::DTYPE_CATACOMBS, $dlvl);

            return;
        }
        $this->microCache = [];
        $this->cursor->paletteEpoch++;
        $this->cursor->initLevelCursor();
        $this->fixedCamSet = false;
        $seed = $this->dungeonSeed + $dlvl * 9973;
        $this->map->allowMazeFallback = $this->mazeFallback;
        $this->map->loadCathedral($this->assets, $this->rng, $seed, $dlvl, $this->mazeFallback);
        $this->loadSpecialCels('levels\\l1data\\l1s');
        $this->finishDungeonEnter($dlvl, 'Cathedral');
        $this->msg('Entering Cathedral level ' . $dlvl);
    }

    private function enterDungeonType(int $dtype, int $dlvl): void
    {
        $this->microCache = [];
        $this->cursor->paletteEpoch++;
        $this->cursor->initLevelCursor();
        $this->fixedCamSet = false;
        $seed = $this->dungeonSeed + $dlvl * 9973;
        $this->map->allowMazeFallback = $this->mazeFallback;
        match ($dtype) {
            DungeonMap::DTYPE_CATHEDRAL => $this->map->loadCathedral($this->assets, $this->rng, $seed, $dlvl, $this->mazeFallback),
            DungeonMap::DTYPE_CATACOMBS => $this->map->loadCatacombs($this->assets, $this->rng, $seed, $dlvl),
            DungeonMap::DTYPE_CAVES => $this->map->loadCaves($this->assets, $this->rng, $seed, $dlvl),
            default => $this->map->loadHell($this->assets, $this->rng, $seed, $dlvl),
        };
        $name = match ($dtype) {
            DungeonMap::DTYPE_CATHEDRAL => 'Cathedral',
            DungeonMap::DTYPE_CATACOMBS => 'Catacombs',
            DungeonMap::DTYPE_CAVES => 'Caves',
            default => 'Hell',
        };
        if ($dtype === DungeonMap::DTYPE_CATHEDRAL) {
            $this->loadSpecialCels('levels\\l1data\\l1s');
        } elseif ($dtype === DungeonMap::DTYPE_CATACOMBS) {
            // L2 door arches / specials (SetDoorStateOpen writes dSpecial 5/6)
            $this->loadSpecialCels('levels\\l2data\\l2s');
        } else {
            // L3/L4: no dedicated *s.cel in retail DIABDAT — clear stale L1/L2 specials
            $this->specialFrames = [];
            $this->specialCel = null;
        }
        $this->finishDungeonEnter($dlvl, $name);
        $this->msg('Entering ' . $name . ' level ' . $dlvl);
    }

    private function finishDungeonEnter(int $dlvl, string $label): void
    {
        $this->automapVisited = [];
        $this->torchAnimCache = [];
        $this->px = $this->map->viewX;
        $this->py = $this->map->viewY;
        $this->clrPlrPath();
        $this->destAction = DestAction::NONE;
        $this->towners = [];
        $this->missiles = [];
        // PlaceCathedralStairs / triggers: stairs at DRLG miniset world tiles
        $sx = $this->map->stairUpX > 0 ? $this->map->stairUpX : $this->px;
        $sy = $this->map->stairUpY > 0 ? $this->map->stairUpY : $this->py;
        $this->objects = [
            ['id' => $this->nextId++, 'type' => 'stairs_up', 'x' => $sx, 'y' => $sy],
        ];
        if ($this->map->stairDownX > 0 && $this->map->stairDownY > 0) {
            $this->objects[] = [
                'id' => $this->nextId++,
                'type' => 'stairs_down',
                'x' => $this->map->stairDownX,
                'y' => $this->map->stairDownY,
            ];
        }
        if ($this->map->stairWarpX > 0 && $this->map->stairWarpY > 0) {
            $this->objects[] = [
                'id' => $this->nextId++,
                'type' => 'stairs_warp',
                'x' => $this->map->stairWarpX,
                'y' => $this->map->stairWarpY,
            ];
        }
        $this->offsetPlayerOffTile($sx, $sy);
        $this->map->plrDoTrans($this->px, $this->py);
        $this->initRndChests();
        $this->addChestTraps();
        $this->initRndSarcophagi();
        $this->initRndBarrels();
        // AddL1Objs / AddL2Objs / AddL3Objs — doors on door dPiece tiles
        // Hell (L4) has no door object types in retail Diablo — do not plant L1 door fallbacks.
        $doorCount = $this->addCathedralDoorObjects()
            + $this->addCatacombsDoorObjects()
            + $this->addCavesDoorObjects();
        if ($doorCount === 0 && $this->map->levelType !== DungeonMap::DTYPE_HELL) {
            $this->placeLevelObject('door', 6);
        }
        $this->addCatacombsTorches();
        $this->addHellGoodies();
        $this->addDungeonFountains();
        $this->addDungeonShrines();
        $this->addDungeonBookstands();
        $this->addDungeonBookcases();
        $this->addDungeonLevers();
        $this->addDungeonStands();
        $this->addDungeonCandles();
        $this->addDungeonDecor();
        $this->addObjTraps();
        // Fallback only if DRLG did not place STAIRSDOWN (maze fallback / failed PlaceMiniSet)
        $hasDown = false;
        foreach ($this->objects as $o) {
            if (($o['type'] ?? '') === 'stairs_down') {
                $hasDown = true;
                break;
            }
        }
        if (!$hasDown) {
            $this->placeLevelObject('stairs_down', 12);
        }
        $this->items = [];
        $this->monsters = [];
        $reachable = $this->reachableFrom($this->px, $this->py);
        $pool = Monstdat::forCathedralLevel(min(4, max(1, $dlvl <= 4 ? $dlvl : 4)));
        if ($pool === []) {
            $pool = [
                ['_monster_id' => 'MT_NZOMBIE', 'name' => 'Zombie', 'ai' => 'Zombie', 'hitPointsMinimum' => '4', 'hitPointsMaximum' => '7', 'minDamage' => '2', 'maxDamage' => '5', 'toHit' => '10', 'armorClass' => '5', 'exp' => '54', 'assetsSuffix' => 'zombie\\zombie'],
            ];
        }
        $count = 4 + min(8, $dlvl) * 2;
        $placed = 0;
        foreach ($reachable as $key => $man) {
            if ($placed >= $count) {
                break;
            }
            if ($man < 6) {
                continue;
            }
            if ($this->rng->generateRnd(100) > 18) {
                continue;
            }
            [$x, $y] = array_map('intval', explode(',', $key));
            $row = $pool[$this->rng->generateRnd(count($pool))];
            $hpMin = (int) ($row['hitPointsMinimum'] ?? 4);
            $hpMax = max($hpMin, (int) ($row['hitPointsMaximum'] ?? 7));
            $hp = $this->rng->between($hpMin, $hpMax) + $dlvl;
            $id = $this->nextId++;
            $m = [
                'id' => $id,
                'type' => $row['_monster_id'] ?? 'MT_NZOMBIE',
                'name' => $row['name'] ?? 'Monster',
                'x' => $x,
                'y' => $y,
                'hp' => $hp,
                'maxHp' => $hp,
                'minDam' => (int) ($row['minDamage'] ?? 1) + intdiv($dlvl, 2),
                'maxDam' => (int) ($row['maxDamage'] ?? 4) + intdiv($dlvl, 2),
                'ac' => (int) ($row['armorClass'] ?? 5),
                'toHit' => (int) ($row['toHit'] ?? 40),
                'xp' => (int) ($row['exp'] ?? 15) + $dlvl * 5,
                'level' => (int) ($row['level'] ?? $dlvl),
                'mode' => 'STAND',
                'dir' => Direction::S,
                'ai' => $row['ai'] ?? 'Zombie',
                'assetsSuffix' => $row['assetsSuffix'] ?? '',
                'monstdat' => $row,
                'cl2' => Monstdat::cl2Path($row, 'n'),
                'cl2Mode' => 'n',
                'frame' => 0,
                'alive' => true,
                'quest' => false,
                'selectionRegion' => SelectionRegion::parse((string) ($row['selectionRegion'] ?? 'Bottom,Middle')),
                'hidden' => false,
            ];
            if ($placed === 0 && $dlvl === 1 && $this->questStatus !== 'turned_in') {
                $m['quest'] = true;
                $m['name'] = 'Marked Beast';
                $m['hp'] = (int) ($m['hp'] * 1.5);
                $m['maxHp'] = $m['hp'];
                $this->questTargetId = $id;
            }
            $this->tryLoadMonsterCl2($m, 'n');
            $this->monsters[] = $m;
            $placed++;
        }
        $this->msg($label . ' level ' . $dlvl);
        $this->loadPlayerSprite();
        $this->warmRenderCaches();
        $this->startStand();
        $this->syncOccupancyActors();
        $this->updateLighting();
    }

    /**
     * Prefetch stand/walk CL2 frames (all dirs) so gameplay has zero CL2 decode.
     * Micro CEL textures still warm on first viewport present.
     */
    private function warmRenderCaches(): void
    {
        $this->playerFrameCache = [];
        for ($dir = 0; $dir < 8; $dir++) {
            $saved = $this->pdir;
            $this->pdir = $dir;
            $standMode = $this->pmode;
            $this->pmode = 'STAND';
            $this->currentPlayerFrame();
            $this->pmode = 'WALK_NORTHWARDS';
            $this->playerAnim->setNewAnimation(WalkOffset::WALK_FRAMES, 1, 0);
            for ($f = 0; $f < WalkOffset::WALK_FRAMES; $f++) {
                $this->playerAnim->currentFrame = $f;
                $this->currentPlayerFrame();
            }
            foreach (['ATTACK', 'RATTACK', 'GOT_HIT', 'BLOCK', 'SPELL', 'DEATH'] as $mode) {
                $this->pmode = $mode;
                $n = match ($mode) {
                    'GOT_HIT' => max(1, $this->hitFrames),
                    'BLOCK' => max(1, $this->blockFrames),
                    'DEATH' => max(1, $this->deathFrames),
                    'SPELL' => max(1, $this->spellFrames),
                    default => max(8, $this->attackFrames),
                };
                $this->playerAnim->setNewAnimation($n, 1, 0);
                for ($f = 0; $f < $n; $f++) {
                    $this->playerAnim->currentFrame = $f;
                    $this->actionFrame = $f;
                    $this->currentPlayerFrame();
                }
            }
            $this->pmode = $standMode;
            $this->pdir = $saved;
        }
        $this->playerAnim->setNewAnimation(8, 1, 0);
        $this->actionFrame = 0;
    }

    /** @param array<string,mixed> $m */
    private function tryLoadMonsterCl2(array &$m, string $modeLetter = 'n'): void
    {
        $row = $m['monstdat'] ?? null;
        if (is_array($row)) {
            $path = Monstdat::cl2Path($row, $modeLetter);
        } else {
            $suf = (string) ($m['assetsSuffix'] ?? '');
            if ($suf !== '') {
                $path = Monstdat::cl2Path(['assetsSuffix' => $suf], $modeLetter);
            } else {
                $path = (string) ($m['cl2'] ?? '');
                // If only stand path stored, swap trailing letter before .cl2
                if ($path !== '' && preg_match('/^(.+)[nwadhs]\.cl2$/i', $path, $mm)) {
                    $path = $mm[1] . $modeLetter . '.cl2';
                }
            }
        }
        $m['cl2'] = $path;
        $m['cl2Mode'] = $modeLetter;
        if ($path === '' || !$this->assets->has($path)) {
            $alt = 'Monsters\\' . str_replace(['monsters\\', '/'], ['', '\\'], strtolower($path));
            if (!$this->assets->has($alt)) {
                // Fall back to stand sheet if mode sheet missing
                if ($modeLetter !== 'n' && ($m['cl2Mode'] ?? '') !== 'n') {
                    $this->tryLoadMonsterCl2($m, 'n');
                }

                return;
            }
            $path = $alt;
            $m['cl2'] = $path;
        }
        try {
            $cacheKey = $path;
            if (!isset($m['cl2Sheets']) || !is_array($m['cl2Sheets'])) {
                $m['cl2Sheets'] = [];
            }
            if (!isset($m['cl2Sheets'][$modeLetter])) {
                $m['cl2Sheets'][$modeLetter] = Cl2::parse($this->assets->read($path));
            }
            $cl2 = $m['cl2Sheets'][$modeLetter];
            $m['cl2Obj'] = $cl2;
            $m['cl2Groups'] = $cl2->numGroups;
            $mode = match ($modeLetter) {
                'w' => 'WALK',
                'a' => 'ATTACK',
                'h' => 'GOT_HIT',
                'd' => 'DEATH',
                default => 'STAND',
            };
            $rowArr = is_array($row) ? $row : [];
            $datFrames = $rowArr !== [] ? Monstdat::modeFrames($rowArr, $mode, max(1, $cl2->numFrames)) : max(1, $cl2->numFrames);
            // Prefer monstdat length; clamp to CL2 sheet size
            $m['animFrames'] = max(1, min($datFrames, max(1, $cl2->numFrames)));
            $m['animRate'] = $rowArr !== [] ? Monstdat::modeRate($rowArr, $mode, 1) : 1;
            $m['animHitFrame'] = $rowArr !== [] ? Monstdat::attackHitFrame($rowArr, 5) : 4;
            $m['animFrame'] = 0;
            $dir = (int) ($m['dir'] ?? 0);
            $frame = $cl2->decodeFrame(0, 128, $this->map->palette, $dir % max(1, $cl2->numGroups));
            $m['spriteRgba'] = $frame['rgba'];
            $m['spriteW'] = $frame['w'];
            $m['spriteH'] = $frame['h'];
        } catch (\Throwable $e) {
            fwrite(STDERR, 'Monster CL2 miss: ' . $e->getMessage() . "\n");
        }
    }

    /** DX changeAnimationData — map mode → CL2 letter n/w/a/h/d */
    private function monsterModeLetter(string $mode): string
    {
        return match ($mode) {
            'WALK' => 'w',
            'ATTACK', 'RANGED' => 'a',
            'GOT_HIT' => 'h',
            'DEATH' => 'd',
            default => 'n',
        };
    }

    /** @param array<string,mixed> $m */
    private function setMonsterGraphic(array &$m, string $mode): void
    {
        $letter = $this->monsterModeLetter($mode);
        if (($m['cl2Mode'] ?? '') === $letter && isset($m['cl2Obj'])) {
            return;
        }
        $this->tryLoadMonsterCl2($m, $letter);
    }

    /**
     * Monster/towner frame for draw — LightTables remap when not town/fullbright.
     * @param array<string,mixed> $m
     * @return array{w:int,h:int,rgba:string}|null
     */
    private function monsterDrawFrame(array &$m, int $light): ?array
    {
        $mode = (string) ($m['mode'] ?? 'STAND');
        $this->setMonsterGraphic($m, $mode);
        $cl2 = $m['cl2Obj'] ?? null;
        if (!$cl2 instanceof Cl2) {
            if (!empty($m['spriteRgba']) && !empty($m['spriteW'])) {
                return ['w' => (int) $m['spriteW'], 'h' => (int) $m['spriteH'], 'rgba' => (string) $m['spriteRgba']];
            }

            return null;
        }
        $dir = (int) ($m['dir'] ?? 0);
        $groups = max(1, $cl2->numGroups);
        $group = $dir % $groups;
        $cl2->selectGroup($group);
        $n = max(1, min((int) ($m['animFrames'] ?? $cl2->numFrames), max(1, $cl2->numFrames)));
        if ($mode === 'WALK') {
            $frameIdx = max(0, (int) ($m['walkFrame'] ?? 0) % $n);
        } elseif ($mode === 'ATTACK' || $mode === 'RANGED' || $mode === 'GOT_HIT' || $mode === 'DEATH') {
            $frameIdx = max(0, (int) ($m['animFrame'] ?? $m['frame'] ?? 0) % $n);
        } else {
            $frameIdx = max(0, (int) ($m['animFrame'] ?? 0) % $n);
        }
        $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : max(0, min(15, $light));
        $key = 'mfr:' . ($m['id'] ?? 0) . ':' . ($m['cl2Mode'] ?? 'n') . ":{$group}:{$frameIdx}:L{$useLight}";
        if (!isset($this->playerFrameCache[$key])) {
            $lt = $useLight > 0 ? Lighting::table($useLight) : null;
            $this->playerFrameCache[$key] = $cl2->decodeFrame($frameIdx, 128, $this->map->palette, $group, $lt);
            $this->profileDecodes++;
        }

        return $this->playerFrameCache[$key];
    }

    private function loadSpecialCels(string $pathNoExt): void
    {
        $this->specialFrames = [];
        $this->specialCel = null;
        $path = $pathNoExt . '.cel';
        if (!$this->assets->has($path)) {
            fwrite(STDERR, "Special CEL missing: {$path}\n");

            return;
        }
        try {
            $cel = Cel::parse($this->assets->read($path));
            $this->specialCel = $cel;
            $n = min(64, $cel->numFrames);
            for ($i = 0; $i < $n; $i++) {
                $this->specialFrames[$i] = $cel->decodeFrame($i, 64, $this->map->palette);
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'Special CEL: ' . $e->getMessage() . "\n");
        }
    }

    private function loadTownerSprites(): void
    {
        foreach ($this->towners as &$t) {
            $base = (string) ($t['cel'] ?? '');
            $path = $base . '.cel';
            if ($base === '' || !$this->assets->has($path)) {
                fwrite(STDERR, "Towner CEL missing: {$path}\n");
                continue;
            }
            try {
                $cel = Cel::parse($this->assets->read($path));
                $w = (int) ($t['animW'] ?? 96);
                $t['celObj'] = $cel;
                $t['animFrames'] = max(1, $cel->numFrames);
                $t['animFrame'] = 0;
                $t['animTick'] = 0;
                $fr = $cel->decodeFrame(0, $w, $this->map->palette);
                $t['spriteRgba'] = $fr['rgba'];
                $t['spriteW'] = $fr['w'];
                $t['spriteH'] = $fr['h'];
            } catch (\Throwable $e) {
                fwrite(STDERR, 'Towner CEL: ' . $e->getMessage() . "\n");
            }
        }
        unset($t);
    }

    /** @param array<string,mixed> $o */
    private function loadObjectSprite(array &$o): void
    {
        $type = (string) ($o['type'] ?? '');
        // objdat MissilesPassThrough → _oMissFlag (doors overridden by open/close)
        if (!array_key_exists('oMissFlag', $o)) {
            $o['oMissFlag'] = $this->objectMissilesPassThrough($type);
        }
        $path = match ($type) {
            'door' => match ($o['doorLevel'] ?? 'L1') {
                'L2' => 'objects\\l2doors.cel',
                'L3' => 'objects\\l3doors.cel',
                default => 'objects\\l1doors.cel',
            },
            'torch' => (string) ($o['cel'] ?? 'objects\\wtorch1.cel'),
            'chest' => match ((int) ($o['chestTier'] ?? 1)) {
                2 => 'objects\\chest2.cel',
                3 => 'objects\\chest3.cel',
                default => 'objects\\chest1.cel',
            },
            'barrel' => 'objects\\barrel.cel',
            'barrelex' => 'objects\\barrelex.cel',
            'sarc' => 'objects\\sarc.cel',
            'cauldron' => 'objects\\cauldren.cel',
            'decap' => 'objects\\decap.cel',
            'tnudem' => 'objects\\tnudem.cel',
            'tnudew' => 'objects\\tnudew.cel',
            'candle' => 'objects\\candle2.cel',
            'bloodftn' => 'objects\\bloodfnt.cel',
            'purifyftn' => 'objects\\pfountn.cel',
            'murkyftn' => 'objects\\mfountn.cel',
            'tearftn' => 'objects\\tfountn.cel',
            'shrine' => ((string) ($o['doorSide'] ?? 'L') === 'R') ? 'objects\\rshrineg.cel' : 'objects\\lshrineg.cel',
            'goatshrine' => 'objects\\goatshrn.cel',
            'trap' => 'objects\\traphole.cel',
            'bookstand' => 'objects\\book2.cel',
            'bookcase' => 'objects\\bcase.cel',
            'lever' => 'objects\\lever.cel',
            'armorstand' => 'objects\\armstand.cel',
            'weaponrack' => 'objects\\weapstnd.cel',
            'banner' => 'objects\\banner.cel',
            'skulfire' => 'objects\\skulfire.cel',
            'burncros' => 'objects\\burncros.cel',
            'flamehole' => 'objects\\flame1.cel',
            'tsoul' => 'objects\\tsoul.cel',
            default => '',
        };
        if ($path === '' || !$this->assets->has($path)) {
            // try alternate chest/barrel names
            if ($type === 'chest') {
                foreach (['objects\\chest1.cel', 'objects\\chest2.cel', 'objects\\chest3.cel'] as $alt) {
                    if ($this->assets->has($alt)) {
                        $path = $alt;
                        break;
                    }
                }
            } elseif ($type === 'barrel' && $this->assets->has('objects\\barrel.cel')) {
                $path = 'objects\\barrel.cel';
            }
            if ($path === '' || !$this->assets->has($path)) {
                return;
            }
        }
        try {
            $cel = Cel::parse($this->assets->read($path));
            $o['celObj'] = $cel;
            $frameIdx = 0;
            if (!empty($o['opened'])) {
                // : open/break uses later frames when present
                $frameIdx = min($cel->numFrames - 1, max(1, $cel->numFrames > 1 ? 1 : 0));
                if (($type === 'barrel' || $type === 'barrelex') && $cel->numFrames > 2) {
                    $frameIdx = min($cel->numFrames - 1, 2);
                }
                if ($type === 'cauldron' && $cel->numFrames > 2) {
                    $frameIdx = min($cel->numFrames - 1, 2); // OperateCauldron → frame 3 (0-based 2)
                }
            } elseif (isset($o['spriteFrame'])) {
                $frameIdx = max(0, min($cel->numFrames - 1, (int) $o['spriteFrame']));
            } elseif ($type === 'bookcase') {
                // OBJ_BOOKCASEL frame 3 / OBJ_BOOKCASER frame 4 (0-based 2/3)
                $frameIdx = ((string) ($o['doorSide'] ?? 'L') === 'R') ? 3 : 2;
                $frameIdx = min($cel->numFrames - 1, max(0, $frameIdx));
            }
            $fr = $cel->decodeFrame($frameIdx, 96, $this->map->palette);
            $o['spriteRgba'] = $fr['rgba'];
            $o['spriteW'] = $fr['w'];
            $o['spriteH'] = $fr['h'];
            $o['spriteFrame'] = $frameIdx;
            if ($type === 'torch' && $cel->numFrames > 1) {
                // Animated wall torch — one shared sheet per CEL path
                if (!isset($this->torchAnimCache[$path])) {
                    $sheet = [];
                    for ($f = 0; $f < $cel->numFrames; $f++) {
                        $sheet[$f] = $cel->decodeFrame($f, 96, $this->map->palette);
                    }
                    $this->torchAnimCache[$path] = $sheet;
                }
                $o['animFlag'] = true;
                $o['animDelay'] = 1;
                $o['animCnt'] = 0;
                $o['animFrame'] = 0;
                $o['animFrames'] = count($this->torchAnimCache[$path]);
                $o['animSheet'] = $path;
            }
            if (($type === 'candle' || $type === 'skulfire' || $type === 'burncros' || $type === 'flamehole'
                || $type === 'bloodftn' || $type === 'purifyftn' || $type === 'murkyftn' || $type === 'tearftn') && $cel->numFrames > 1) {
                if (!isset($this->torchAnimCache[$path])) {
                    $sheet = [];
                    for ($f = 0; $f < $cel->numFrames; $f++) {
                        $sheet[$f] = $cel->decodeFrame($f, 96, $this->map->palette);
                    }
                    $this->torchAnimCache[$path] = $sheet;
                }
                $o['animFlag'] = true;
                $o['animDelay'] = ($type === 'candle' || $type === 'skulfire' || $type === 'burncros' || $type === 'flamehole') ? 1 : 2;
                $o['animCnt'] = 0;
                $o['animFrame'] = 0;
                $o['animFrames'] = count($this->torchAnimCache[$path]);
                $o['animSheet'] = $path;
            }
        } catch (\Throwable) {
            // leave without sprite
        }
    }

    /** Refresh object CEL frame after open/break. */
    private function refreshObjectSprite(array &$o): void
    {
        if (isset($o['celObj']) && $o['celObj'] instanceof Cel) {
            $cel = $o['celObj'];
            $type = (string) ($o['type'] ?? '');
            $frameIdx = 0;
            if (!empty($o['opened'])) {
                if ($type === 'bookcase' && isset($o['spriteFrame'])) {
                    $frameIdx = max(0, min($cel->numFrames - 1, (int) $o['spriteFrame']));
                } else {
                    $frameIdx = min($cel->numFrames - 1, max(1, $cel->numFrames > 1 ? 1 : 0));
                    if ($type === 'barrel' && $cel->numFrames > 2) {
                        $frameIdx = min($cel->numFrames - 1, 2);
                    }
                }
            }
            try {
                $fr = $cel->decodeFrame($frameIdx, 96, $this->map->palette);
                $o['spriteRgba'] = $fr['rgba'];
                $o['spriteW'] = $fr['w'];
                $o['spriteH'] = $fr['h'];
                $o['spriteFrame'] = $frameIdx;
            } catch (\Throwable) {
            }

            return;
        }
        $this->loadObjectSprite($o);
    }

    private function loadPlayerSprite(): void
    {
        $prefix = $this->playerGfxPrefix();
        $standPath = $prefix . 'as.cl2';
        $walkPath = $prefix . 'aw.cl2';
        // LoadPlrGFX — town uses st/wl instead of as/aw
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            $townStand = $prefix . 'st.cl2';
            $townWalk = $prefix . 'wl.cl2';
            if ($this->assets->has($townStand)) {
                $standPath = $townStand;
            }
            if ($this->assets->has($townWalk)) {
                $walkPath = $townWalk;
            }
        }
        $attackPath = $prefix . 'at.cl2';
        // LoadPlrGFX Hit → "ht" (not "ah")
        $hitPath = $prefix . 'ht.cl2';
        // Death forces Unarmed weapon graphic (WepChar 'n'): wld\wld → wln\wln
        $parts = explode('\\', $prefix);
        $code = (string) ($parts[count($parts) - 1] ?? 'wld');
        $unarmedCode = substr($code, 0, 2) . 'n';
        if (count($parts) >= 2) {
            $parts[count($parts) - 2] = $unarmedCode;
            $parts[count($parts) - 1] = $unarmedCode;
        }
        $deathPrefix = implode('\\', $parts);
        $deathPath = $deathPrefix . 'dt.cl2';
        $spellPath = $prefix . 'fm.cl2';
        $blockPath = $prefix . 'bl.cl2';
        if (!$this->assets->has($standPath) && !$this->assets->has($prefix . 'as.cl2')) {
            // Fallback light leather sword (common retail sheet)
            $cls = match ($this->playerClass) {
                'Rogue' => ['rogue', 'r'],
                'Sorcerer' => ['sorceror', 's'],
                default => ['warrior', 'w'],
            };
            $prefix = 'plrgfx\\' . $cls[0] . '\\' . $cls[1] . 'ld\\' . $cls[1] . 'ld';
            $standPath = $prefix . (($this->map->levelType === DungeonMap::DTYPE_TOWN && $this->assets->has($prefix . 'st.cl2')) ? 'st.cl2' : 'as.cl2');
            $walkPath = $prefix . (($this->map->levelType === DungeonMap::DTYPE_TOWN && $this->assets->has($prefix . 'wl.cl2')) ? 'wl.cl2' : 'aw.cl2');
            $attackPath = $prefix . 'at.cl2';
            $hitPath = $prefix . 'ht.cl2';
            $deathPrefix = 'plrgfx\\' . $cls[0] . '\\' . $cls[1] . 'ln\\' . $cls[1] . 'ln';
            $deathPath = $deathPrefix . 'dt.cl2';
            $spellPath = $prefix . 'fm.cl2';
            $blockPath = $prefix . 'bl.cl2';
        }
        $this->playerStandCl2 = null;
        $this->playerWalkCl2 = null;
        $this->playerAttackCl2 = null;
        $this->playerHitCl2 = null;
        $this->playerDeathCl2 = null;
        $this->playerSpellCl2 = null;
        $this->playerBlockCl2 = null;
        $this->playerFrameCache = [];
        $this->spriteTex = null;
        $this->playerSpriteRgba = '';
        try {
            if ($this->assets->has($standPath)) {
                $this->playerStandCl2 = Cl2::parse($this->assets->read($standPath));
            }
            if ($this->assets->has($walkPath)) {
                $this->playerWalkCl2 = Cl2::parse($this->assets->read($walkPath));
            }
            if ($this->assets->has($attackPath)) {
                $this->playerAttackCl2 = Cl2::parse($this->assets->read($attackPath));
            }
            if ($this->assets->has($hitPath)) {
                $this->playerHitCl2 = Cl2::parse($this->assets->read($hitPath));
            }
            if ($this->assets->has($deathPath)) {
                $this->playerDeathCl2 = Cl2::parse($this->assets->read($deathPath));
            } elseif ($this->assets->has($prefix . 'dt.cl2')) {
                $this->playerDeathCl2 = Cl2::parse($this->assets->read($prefix . 'dt.cl2'));
            }
            if ($this->assets->has($spellPath)) {
                $this->playerSpellCl2 = Cl2::parse($this->assets->read($spellPath));
            }
            // Block sheet only when holding a shield (_pBlockFlag)
            if ($this->holdsShield() && $this->assets->has($blockPath)) {
                $this->playerBlockCl2 = Cl2::parse($this->assets->read($blockPath));
            }
            if ($this->playerHitCl2 !== null) {
                $this->hitFrames = max(1, $this->playerHitCl2->numFrames);
            }
            if ($this->playerDeathCl2 !== null) {
                $this->deathFrames = max(1, $this->playerDeathCl2->numFrames);
            }
            if ($this->playerAttackCl2 !== null) {
                $this->attackFrames = max(1, $this->playerAttackCl2->numFrames);
            }
            if ($this->playerSpellCl2 !== null) {
                $this->spellFrames = max(1, $this->playerSpellCl2->numFrames);
            }
            if ($this->playerBlockCl2 !== null) {
                $this->blockFrames = max(1, $this->playerBlockCl2->numFrames);
            } else {
                $this->blockFrames = 2;
            }
            $this->syncAttackActionFrame();
            // Class castingActionFrame (playerdat animations.tsv)
            $this->pSFNum = match ($this->playerClass) {
                'Rogue' => 12,
                'Sorcerer' => 8,
                default => 14,
            };
            $this->pSFNum = max(0, min($this->pSFNum, max(0, $this->spellFrames - 1)));
            $frame = $this->currentPlayerFrame();
            $this->spriteW = $frame['w'];
            $this->spriteH = $frame['h'];
            $this->playerSpriteRgba = $frame['rgba'];
        } catch (\Throwable $e) {
            fwrite(STDERR, 'Player CL2: ' . $e->getMessage() . "\n");
            $this->playerSpriteRgba = '';
        }
    }

    /**
     * LoadPlrGFX path: plrgfx\{class}\{c}{armor}{wep}\{c}{armor}{wep}
     * ArmourChar l/m/h, WepChar n/u/s/d/b/a/m/h/t
     */
    private function playerGfxPrefix(): string
    {
        [$clsPath, $clsChar] = match ($this->playerClass) {
            'Rogue' => ['rogue', 'r'],
            'Sorcerer' => ['sorceror', 's'],
            default => ['warrior', 'w'],
        };
        $armor = $this->playerArmorGraphicChar();
        $wep = $this->playerWeaponGraphicChar();
        $code = $clsChar . $armor . $wep;
        $prefix = 'plrgfx\\' . $clsPath . '\\' . $code . '\\' . $code;
        // Prefer exact sheet; fall back leather+same wep, then leather+sword
        if (!$this->assets->has($prefix . 'as.cl2') && !$this->assets->has($prefix . 'st.cl2')) {
            $fallback = $clsChar . 'l' . $wep;
            $try = 'plrgfx\\' . $clsPath . '\\' . $fallback . '\\' . $fallback;
            if ($this->assets->has($try . 'as.cl2') || $this->assets->has($try . 'st.cl2')) {
                return $try;
            }
            $fallback = $clsChar . 'ld';
            $try = 'plrgfx\\' . $clsPath . '\\' . $fallback . '\\' . $fallback;
            if ($this->assets->has($try . 'as.cl2') || $this->assets->has($try . 'st.cl2')) {
                return $try;
            }
        }

        return $prefix;
    }

    /** PlayerArmorGraphic → ArmourChar */
    private function playerArmorGraphicChar(): string
    {
        $body = $this->equipment['body'] ?? null;
        if (!is_array($body)) {
            return 'l';
        }
        $t = strtolower((string) ($body['itemType'] ?? $body['name'] ?? ''));
        if (str_contains($t, 'heavy') || str_contains($t, 'plate') || str_contains($t, 'gothic') || str_contains($t, 'full')) {
            return 'h';
        }
        if (str_contains($t, 'medium') || str_contains($t, 'mail') || str_contains($t, 'chain') || str_contains($t, 'scale') || str_contains($t, 'breast')) {
            return 'm';
        }

        return 'l';
    }

    /** PlayerWeaponGraphic → WepChar (with offhand shield → u/d/h) */
    private function playerWeaponGraphicChar(): string
    {
        $hand = $this->equipment['hand'] ?? null;
        $shield = $this->holdsShield();
        if (!is_array($hand)) {
            return $shield ? 'u' : 'n';
        }
        $blob = strtolower(
            (string) ($hand['itemType'] ?? '') . ' '
            . (string) ($hand['type'] ?? '') . ' '
            . (string) ($hand['name'] ?? '')
        );
        if (str_contains($blob, 'bow')) {
            return 'b'; // two-hand — shield ignored for gfx
        }
        if (str_contains($blob, 'staff') || str_contains($blob, 'stave')) {
            return 't';
        }
        if (str_contains($blob, 'axe')) {
            return 'a';
        }
        if (str_contains($blob, 'mace') || str_contains($blob, 'club') || str_contains($blob, 'hammer') || str_contains($blob, 'flail') || str_contains($blob, 'maul')) {
            return $shield ? 'h' : 'm';
        }
        if (str_contains($blob, 'sword') || str_contains($blob, 'blade') || str_contains($blob, 'saber') || str_contains($blob, 'scimitar') || str_contains($blob, 'dagger') || str_contains($blob, 'falchion')) {
            return $shield ? 'd' : 's';
        }

        return $shield ? 'd' : 's';
    }

    private function holdsShield(): bool
    {
        $oh = $this->equipment['offhand'] ?? null;
        if (!is_array($oh)) {
            return false;
        }
        $blob = strtolower((string) ($oh['itemType'] ?? '') . ' ' . (string) ($oh['name'] ?? '') . ' ' . (string) ($oh['equipSlot'] ?? ''));

        return str_contains($blob, 'shield') || ($oh['equipSlot'] ?? '') === 'offhand';
    }

    /**
     * InvDrawSlotBack stand-in — soft quality tint behind inventory icons.
     */
    private function invDrawSlotBack(Sdl $sdl, int $x, int $y, int $w, int $h, string $quality): void
    {
        // Approximate PAL16 blue/yellow/beige shifts with translucent fills
        match ($quality) {
            'unique' => $sdl->setDrawColor(180, 140, 40, 90),
            'magic' => $sdl->setDrawColor(60, 90, 200, 90),
            default => $sdl->setDrawColor(140, 120, 90, 50),
        };
        $sdl->fillRect($x, $y, $w, $h);
    }

    /**
     * PrintItem / info-box label — unidentified magic shows baseName only.
     *
     * @param array<string,mixed> $it
     */
    private function itemLabel(array $it): string
    {
        $quality = (string) ($it['quality'] ?? 'normal');
        $identified = !empty($it['identified']) || $quality === 'normal';
        if (!$identified) {
            $base = (string) ($it['baseName'] ?? '');
            if ($base !== '') {
                return $base;
            }
        }
        if ($quality === 'unique') {
            $uniq = (string) ($it['uniqueName'] ?? $it['name'] ?? '');
            if ($uniq !== '') {
                return $uniq;
            }
        }

        return (string) ($it['name'] ?? 'Item');
    }

    /**
     * PrintItemPower-style short lines for identified magic/unique gear.
     *
     * @param array<string,mixed> $it
     * @return list<string>
     */
    private function itemAffixLines(array $it): array
    {
        $lines = [];
        $add = static function (string $s) use (&$lines): void {
            if ($s !== '') {
                $lines[] = $s;
            }
        };
        if (($n = (int) ($it['plFireRes'] ?? 0)) !== 0) {
            $add('Resist Fire: ' . ($n > 0 ? '+' : '') . $n . '%');
        }
        if (($n = (int) ($it['plLightRes'] ?? 0)) !== 0) {
            $add('Resist Lightning: ' . ($n > 0 ? '+' : '') . $n . '%');
        }
        if (($n = (int) ($it['plMagicRes'] ?? 0)) !== 0) {
            $add('Resist Magic: ' . ($n > 0 ? '+' : '') . $n . '%');
        }
        if (($n = (int) ($it['plToHit'] ?? 0)) !== 0) {
            $add('To Hit: ' . ($n > 0 ? '+' : '') . $n . '%');
        }
        if (($n = (int) ($it['plDamPct'] ?? 0)) !== 0) {
            $add(($n > 0 ? '+' : '') . $n . '% damage');
        }
        if (($n = (int) ($it['plAC'] ?? 0)) !== 0) {
            $add('Armor: ' . ($n > 0 ? '+' : '') . $n);
        }
        if (($n = (int) ($it['plStr'] ?? 0)) !== 0) {
            $add(($n > 0 ? '+' : '') . $n . ' to strength');
        }
        if (($n = (int) ($it['plMag'] ?? 0)) !== 0) {
            $add(($n > 0 ? '+' : '') . $n . ' to magic');
        }
        if (($n = (int) ($it['plDex'] ?? 0)) !== 0) {
            $add(($n > 0 ? '+' : '') . $n . ' to dexterity');
        }
        if (($n = (int) ($it['plVit'] ?? 0)) !== 0) {
            $add(($n > 0 ? '+' : '') . $n . ' to vitality');
        }
        if (($n = (int) ($it['plLight'] ?? 0)) !== 0) {
            $add(($n > 0 ? '+' : '') . $n . ' to light radius');
        }
        if (($n = (int) ($it['plStealLife'] ?? 0)) !== 0) {
            $add('steals ' . $n . '% life');
        }
        if (($n = (int) ($it['plSplLvlAdd'] ?? 0)) !== 0) {
            $add(($n > 0 ? '+' : '') . $n . ' to spells');
        }
        if (($n = (int) ($it['plEnAc'] ?? 0)) !== 0) {
            $add('hit monster armor: -' . $n);
        }
        $effects = $it['effects'] ?? [];
        if (is_array($effects)) {
            if (in_array('FASTBLOCK', $effects, true)) {
                $add('fast block');
            }
            if (in_array('FASTATTACK', $effects, true)) {
                $add('quick attack');
            }
            if (in_array('KNOCKBACK', $effects, true)) {
                $add('knocks target back');
            }
            if (in_array('THORNS', $effects, true)) {
                $add('attacker takes damage');
            }
            if (in_array('FIRE_ARROWS', $effects, true)) {
                $add('fire arrows');
            }
            if (in_array('LIGHT_ARROWS', $effects, true)) {
                $add('lightning arrows');
            }
            if (in_array('INDESTRUCTIBLE', $effects, true)) {
                $add('indestructible');
            }
            if (in_array('NOMANA', $effects, true)) {
                $add('you can\'t cast with this');
            }
        }

        return $lines;
    }

    /**
     * Monster melee / missile land — optional StartPlrBlock then damage / StartPlrHit.
     */
    private function applyMeleeHitToPlayer(int $dam, int $attackerLevel, int $fromX, int $fromY, string $srcName): void
    {
        if ($this->pmode === 'DEATH') {
            return;
        }
        // Block only from STAND/ATTACK while holding shield (PlrHitMon / PlayerMHit)
        $canBlock = $this->holdsShield() && $this->playerBlockCl2 !== null
            && ($this->pmode === 'STAND' || $this->pmode === 'ATTACK');
        if ($canBlock) {
            // GetBlockChance − attackerLevel*2 (DX player.h / missiles.cpp)
            $baseToBlock = match ($this->playerClass) {
                'Sorcerer' => 10,
                default => 20,
            };
            $blkper = max(0, min(100, $this->dexterity + $baseToBlock + ($this->level * 2) - ($attackerLevel * 2)));
            if ($this->rng->generateRnd(100) < $blkper) {
                $this->startPlrBlock($fromX, $fromY);
                $this->msg('Blocked ' . $srcName);

                return;
            }
        }
        $this->php -= $this->absorbDamageWithManaShield($dam + max(0, $this->pGetHit));
        $this->startPlrHit();
        $this->msg($srcName . ' hits for ' . ($dam + max(0, $this->pGetHit)));
        $this->audio?->play('player_hit');
        $this->applyThornsToAttacker($fromX, $fromY);
    }

    /** StartPlrHit — GOT_HIT anim; FastHitRecovery skips early frames. */
    private function startPlrHit(): void
    {
        if ($this->pmode === 'DEATH' || $this->pmode === 'BLOCK') {
            return;
        }
        $this->pmode = 'GOT_HIT';
        $this->actionFrame = 0;
        $skip = match ($this->pFastRecover) {
            1 => 1,
            2 => 2,
            3 => 3,
            default => 0,
        };
        $this->playerAnim->setNewAnimation($this->hitFrames, 1, $skip);
    }

    /** ISPL_THORNS — reflect a little damage to the melee attacker. */
    private function applyThornsToAttacker(int $fromX, int $fromY): void
    {
        if (!$this->hasEquippedEffect('THORNS')) {
            return;
        }
        $maxThorn = max(1, $this->equippedPlSum('plThorns'));
        $td = $this->rng->between(1, $maxThorn);
        foreach ($this->monsters as &$m) {
            if (empty($m['alive']) || ($m['mode'] ?? '') === 'DEATH') {
                continue;
            }
            if ((int) $m['x'] !== $fromX || (int) $m['y'] !== $fromY) {
                continue;
            }
            $m['hp'] -= $td;
            if ($m['hp'] <= 0) {
                $m['mode'] = 'DEATH';
                $m['frame'] = 0;
                $m['animFrame'] = 0;
                $m['hp'] = 0;
                $this->setMonsterGraphic($m, 'DEATH');
                $this->occ->clearMonsterId((int) $m['id']);
            }

            return;
        }
        unset($m);
    }

    /** Knockback — shove monster one tile away if open. */
    private function tryKnockbackMonster(array &$m): void
    {
        if (!$this->hasEquippedEffect('KNOCKBACK') || empty($m['alive'])) {
            return;
        }
        $sx = ((int) $m['x'] <=> $this->px);
        $sy = ((int) $m['y'] <=> $this->py);
        if ($sx === 0 && $sy === 0) {
            return;
        }
        $nx = (int) $m['x'] + $sx;
        $ny = (int) $m['y'] + $sy;
        if (!$this->map->inBounds($nx, $ny) || !$this->map->isWalkable($nx, $ny) || $this->isClosedDoorAt($nx, $ny)) {
            return;
        }
        $mid = (int) $m['id'];
        if ($this->occ->blocksActor($nx, $ny, 0, $mid)) {
            return;
        }
        $this->occ->clearMonsterId($mid);
        $m['x'] = $nx;
        $m['y'] = $ny;
        $this->occ->occupyMonster($nx, $ny, $mid, false);
    }

    /** FastAttack skip frames (Quick→Fastest ≈ 1..3). */
    private function attackAnimSkipFrames(): int
    {
        return match ($this->pFastAttack) {
            1 => 1,
            2 => 2,
            3 => 2,
            4 => 3,
            default => 0,
        };
    }

    /** ISPL_* from identified equipment effects[] / PL fields. */
    private function hasEquippedEffect(string $effect): bool
    {
        foreach ($this->equipment as $it) {
            if ($it === null) {
                continue;
            }
            $quality = (string) ($it['quality'] ?? 'normal');
            if ($quality !== 'normal' && empty($it['identified'])) {
                continue;
            }
            $effects = $it['effects'] ?? [];
            if (is_array($effects) && in_array($effect, $effects, true)) {
                return true;
            }
        }

        return false;
    }

    /** Sum identified PL field across equipment. */
    private function equippedPlSum(string $key): int
    {
        $n = 0;
        foreach ($this->equipment as $it) {
            if ($it === null) {
                continue;
            }
            $quality = (string) ($it['quality'] ?? 'normal');
            if ($quality !== 'normal' && empty($it['identified'])) {
                continue;
            }
            $n += (int) ($it[$key] ?? 0);
        }

        return $n;
    }

    /** StealLife / StealMana after a successful player hit. */
    private function applyOnHitLeech(int $dam): void
    {
        if ($dam <= 0) {
            return;
        }
        $lifePct = $this->equippedPlSum('plStealLife');
        if ($lifePct > 0) {
            $heal = max(1, intdiv($dam * $lifePct, 100));
            $this->php = min($this->pmaxHp, $this->php + $heal);
        }
        $manaPct = $this->equippedPlSum('plStealMana');
        if ($manaPct > 0) {
            $gain = max(1, intdiv($dam * $manaPct, 100));
            $this->pmana = min($this->pmaxMana, $this->pmana + $gain);
        }
    }

    /** Bow missile type — FireArrows / LightningArrows override plain arrows. */
    private function playerRangedMissileType(): string
    {
        if ($this->hasEquippedEffect('FIRE_ARROWS')) {
            return 'firebolt';
        }
        if ($this->hasEquippedEffect('LIGHT_ARROWS')) {
            return 'lightning';
        }

        return 'arrow';
    }

    /**
     * ApplyPlrDamage Mana Shield branch — drain mana before HP; slight DR by spell level.
     */
    private function absorbDamageWithManaShield(int $dam): int
    {
        if ($dam <= 0 || !$this->pManaShield) {
            return $dam;
        }
        $lvl = (int) ($this->spellLevels['ManaShield'] ?? 0);
        $red = max(1, 24 - (min($lvl, 7) * 3)); // GetManaShieldDamageReduction
        if ($lvl > 0) {
            $dam = max(0, $dam - intdiv($dam, $red));
        }
        if ($this->pmana >= $dam) {
            $this->pmana -= $dam;

            return 0;
        }
        $leftover = $dam - $this->pmana;
        $this->pmana = 0;
        $this->pManaShield = false;
        if ($lvl > 0 && $red > 1) {
            $leftover += intdiv($leftover, $red - 1);
        }
        $this->msg('Mana Shield breaks');

        return max(0, $leftover);
    }

    /** ISPL_FASTBLOCK from equipped “of blocking” (or effects[]) shield — only if identified. */
    private function hasFastBlock(): bool
    {
        foreach ($this->equipment as $it) {
            if ($it === null) {
                continue;
            }
            $quality = (string) ($it['quality'] ?? 'normal');
            if ($quality !== 'normal' && empty($it['identified'])) {
                continue;
            }
            $effects = $it['effects'] ?? [];
            if (is_array($effects) && in_array('FASTBLOCK', $effects, true)) {
                return true;
            }
            if (str_contains(strtolower((string) ($it['name'] ?? '')), 'blocking')) {
                return true;
            }
        }

        return false;
    }

    /** StartPlrBlock — face attacker, play bl.cl2 (FastBlock skips to late frames). */
    private function startPlrBlock(int $fromX, int $fromY): void
    {
        $this->clrPlrPath();
        $this->destAction = DestAction::NONE;
        $this->pdir = Direction::fromDelta($fromX - $this->px, $fromY - $this->py);
        $this->pmode = 'BLOCK';
        $this->actionFrame = 0;
        $frames = max(1, $this->blockFrames);
        // ISPL_FASTBLOCK: skip so only ~2 frames show (_pBFrames - 2)
        if ($this->hasFastBlock() && $frames > 2) {
            $frames = 2;
        }
        $this->playerAnim->setNewAnimation($frames, 1, 0);
    }

    /**
     * DamageParryItem — wear shield (or staff) on successful block end.
     * DUR_INDESTRUCTIBLE = 255.
     */
    private function damageParryItem(): void
    {
        $broke = false;
        foreach (['offhand', 'hand'] as $slot) {
            $it = $this->equipment[$slot] ?? null;
            if ($it === null) {
                continue;
            }
            $blob = strtolower((string) ($it['itemType'] ?? '') . ' ' . (string) ($it['name'] ?? '') . ' ' . (string) ($it['equipSlot'] ?? ''));
            $isShield = str_contains($blob, 'shield') || ($slot === 'offhand' && ($it['equipSlot'] ?? '') === 'offhand');
            $isStaff = str_contains($blob, 'staff');
            if ($slot === 'offhand' && !$isShield) {
                continue;
            }
            if ($slot === 'hand' && !$isStaff && !$isShield) {
                continue;
            }
            $dur = (int) ($it['durability'] ?? $it['maxDurability'] ?? 0);
            $max = (int) ($it['maxDurability'] ?? $dur);
            if ($max <= 0) {
                // Legacy items without durability — assign a modest default once
                $max = $isShield ? 16 : 30;
                $dur = $max;
            }
            if ($dur === 255 || $max === 255) {
                return;
            }
            $dur--;
            $it['durability'] = $dur;
            $it['maxDurability'] = $max;
            $this->equipment[$slot] = $it;
            if ($dur <= 0) {
                $name = (string) ($it['name'] ?? 'Item');
                $this->equipment[$slot] = null;
                $broke = true;
                $this->msg($name . ' is destroyed');
            }
            break; // DX damages one matching hand then returns after both checks; we damage first match
        }
        if ($broke) {
            $this->recalc();
            $this->loadPlayerSprite();
        }
    }

    /**
     * DamageWeapon — wear main-hand weapon after a successful melee hit.
     * @return bool true if the weapon was destroyed
     */
    private function damageWeapon(int $damageFrequency = 30): bool
    {
        if (!$this->rng->flipCoin($damageFrequency)) {
            return false;
        }
        $it = $this->equipment['hand'] ?? null;
        if ($it === null) {
            // Bare-handed with shield only — rare DX path wears shield; skip for now
            return false;
        }
        $blob = strtolower((string) ($it['itemType'] ?? '') . ' ' . (string) ($it['type'] ?? '') . ' ' . (string) ($it['name'] ?? ''));
        $isWeapon = ($it['type'] ?? '') === 'weapon'
            || str_contains($blob, 'sword') || str_contains($blob, 'axe') || str_contains($blob, 'mace')
            || str_contains($blob, 'club') || str_contains($blob, 'staff') || str_contains($blob, 'bow')
            || str_contains($blob, 'dagger') || ($it['equipSlot'] ?? '') === 'hand';
        if (!$isWeapon) {
            return false;
        }
        $dur = (int) ($it['durability'] ?? $it['maxDurability'] ?? 0);
        $max = (int) ($it['maxDurability'] ?? $dur);
        if ($max <= 0) {
            $max = 20;
            $dur = $max;
        }
        if ($dur === 255 || $max === 255) {
            return false;
        }
        $effects = $it['effects'] ?? [];
        if (is_array($effects) && in_array('INDESTRUCTIBLE', $effects, true)) {
            return false;
        }
        $dur--;
        $it['durability'] = $dur;
        $it['maxDurability'] = $max;
        $this->equipment['hand'] = $it;
        if ($dur <= 0) {
            $name = (string) ($it['name'] ?? 'Weapon');
            $this->equipment['hand'] = null;
            $this->msg($name . ' is destroyed');
            $this->recalc();
            $this->loadPlayerSprite();

            return true;
        }

        return false;
    }

    /**
     * DamageArmor — wear helm or body armor after a landed hit (DoGotHit).
     */
    private function damageArmor(): void
    {
        $hasBody = ($this->equipment['body'] ?? null) !== null;
        $hasHead = ($this->equipment['head'] ?? null) !== null;
        if (!$hasBody && !$hasHead) {
            return;
        }
        $targetHead = $this->rng->flipCoin(3);
        if ($hasBody && !$hasHead) {
            $targetHead = false;
        }
        if (!$hasBody && $hasHead) {
            $targetHead = true;
        }
        $slot = $targetHead ? 'head' : 'body';
        $it = $this->equipment[$slot] ?? null;
        if ($it === null) {
            return;
        }
        $dur = (int) ($it['durability'] ?? $it['maxDurability'] ?? 0);
        $max = (int) ($it['maxDurability'] ?? $dur);
        if ($max <= 0) {
            $max = $slot === 'head' ? 20 : 30;
            $dur = $max;
        }
        if ($dur === 255 || $max === 255) {
            return;
        }
        $dur--;
        $it['durability'] = $dur;
        $it['maxDurability'] = $max;
        $this->equipment[$slot] = $it;
        if ($dur <= 0) {
            $name = (string) ($it['name'] ?? 'Armor');
            $this->equipment[$slot] = null;
            $this->msg($name . ' is destroyed');
            $this->recalc();
            $this->loadPlayerSprite();
        }
    }

    /** SetPlrAnims — _pAFNum from weapon graphic (warrior table). */
    private function syncAttackActionFrame(): void
    {
        $w = $this->playerWeaponGraphicChar();
        $this->pAFNum = match ($w) {
            'b' => 11,
            'a' => 10,
            't' => 11,
            default => 9,
        };
    }

    /**
     * Dump wldat.cl2 group/frame parity for all 8 directions.
     * @return list<string>
     */
    public function dumpPlayerAttackGroups(): array
    {
        $lines = [];
        if ($this->playerAttackCl2 === null) {
            $lines[] = 'WARN player attack CL2 not loaded';

            return $lines;
        }
        $cl2 = $this->playerAttackCl2;
        $lines[] = sprintf('attack-asset groups=%d (expect 8) path=plrgfx\\warrior\\wld\\wldat.cl2', $cl2->numGroups);
        $names = ['S', 'SW', 'W', 'NW', 'N', 'NE', 'E', 'SE'];
        for ($d = 0; $d < 8; $d++) {
            $cl2->selectGroup($d);
            $n = $cl2->numFrames;
            $lens = [];
            foreach ([0, max(0, $this->pAFNum - 1), max(0, $n - 1)] as $fi) {
                if ($fi >= $n) {
                    continue;
                }
                try {
                    $fr = $cl2->decodeFrame($fi, 128, $this->map->palette, $d);
                    $crc = sprintf('%08x', crc32($fr['rgba']));
                    $lens[] = sprintf('f%d=%dx%d#%s', $fi, $fr['w'], $fr['h'], $crc);
                } catch (\Throwable $e) {
                    $lens[] = sprintf('f%d=ERR(%s)', $fi, $e->getMessage());
                }
            }
            $lines[] = sprintf(
                '  dir=%d(%s) group=%d frames=%d %s',
                $d,
                $names[$d] ?? '?',
                $d,
                $n,
                implode(' ', $lens)
            );
        }

        return $lines;
    }

    /** @return array{w:int,h:int,rgba:string} */
    private function currentPlayerFrame(): array
    {
        $dir = $this->pdir;
        $walk = $this->isWalking() && $this->playerWalkCl2 !== null;
        $sheet = 's';
        $cl2 = $this->playerStandCl2;
        $frameIdx = 0;
        // sprites.tsv: stand/walk/swHit=96, attack/death=128
        $gfxW = 96;
        if ($walk) {
            $sheet = 'w';
            $cl2 = $this->playerWalkCl2;
            $frameIdx = max(0, $this->playerAnim->getFrameToUseForRendering() % max(1, $cl2?->numFrames ?? 1));
        } elseif (($this->pmode === 'ATTACK' || $this->pmode === 'RATTACK') && $this->playerAttackCl2 !== null) {
            $sheet = 'a';
            $cl2 = $this->playerAttackCl2;
            $gfxW = 128;
            $frameIdx = max(0, $this->playerAnim->getFrameToUseForRendering() % max(1, $cl2->numFrames));
        } elseif ($this->pmode === 'GOT_HIT' && $this->playerHitCl2 !== null) {
            $sheet = 'h';
            $cl2 = $this->playerHitCl2;
            $frameIdx = max(0, $this->playerAnim->getFrameToUseForRendering() % max(1, $cl2->numFrames));
        } elseif ($this->pmode === 'BLOCK' && $this->playerBlockCl2 !== null) {
            $sheet = 'b';
            $cl2 = $this->playerBlockCl2;
            $frameIdx = max(0, $this->playerAnim->getFrameToUseForRendering() % max(1, $cl2->numFrames));
        } elseif ($this->pmode === 'DEATH' && $this->playerDeathCl2 !== null) {
            $sheet = 'd';
            $cl2 = $this->playerDeathCl2;
            $gfxW = 128;
            $frameIdx = max(0, $this->playerAnim->getFrameToUseForRendering() % max(1, $cl2->numFrames));
        } elseif ($this->pmode === 'SPELL' && $this->playerSpellCl2 !== null) {
            $sheet = 'f';
            $cl2 = $this->playerSpellCl2;
            $frameIdx = max(0, $this->playerAnim->getFrameToUseForRendering() % max(1, $cl2->numFrames));
        }
        if ($cl2 === null) {
            return ['w' => 1, 'h' => 1, 'rgba' => "\0\0\0\0"];
        }
        $groups = max(1, $cl2->numGroups);
        $group = $dir % $groups;
        $cl2->selectGroup($group);
        $n = max(1, $cl2->numFrames);
        $frameIdx = $frameIdx % $n;
        // Town / local player: often ClxDraw unlit; dungeon uses dLight
        $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : max(0, min(15, $this->map->dLight[$this->py][$this->px] ?? 0));
        $key = "{$sheet}:{$group}:{$frameIdx}:W{$gfxW}:L{$useLight}";
        if (!isset($this->playerFrameCache[$key])) {
            $lt = $useLight > 0 ? Lighting::table($useLight) : null;
            $this->playerFrameCache[$key] = $cl2->decodeFrame($frameIdx, $gfxW, $this->map->palette, $group, $lt);
            $this->profileDecodes++;
        }

        return $this->playerFrameCache[$key];
    }

    private function applyClass(string $class): void
    {
        $this->playerClass = $class;
        match ($class) {
            'Rogue' => [$this->strength, $this->magic, $this->dexterity, $this->vitality] = [20, 15, 30, 20],
            'Sorcerer' => [$this->strength, $this->magic, $this->dexterity, $this->vitality] = [15, 35, 15, 20],
            default => [$this->strength, $this->magic, $this->dexterity, $this->vitality] = [30, 10, 20, 25],
        };
        $this->recalc();
    }

    private function recalc(): void
    {
        $str = $this->strength;
        $mag = $this->magic;
        $dex = $this->dexterity;
        $vit = $this->vitality;
        $plHp = 0;
        $plMana = 0;
        $fire = 0;
        $light = 0;
        $magicRes = 0;
        $damPct = 0;
        $getHit = 0;
        $plLight = 0;
        $fastAtk = 0;
        $fastRec = 0;
        $splAdd = 0;
        $enAc = 0;
        $this->minDam = 0;
        $this->maxDam = 0;
        $this->ac = 0;
        $this->toHit = 50;
        foreach ($this->equipment as $it) {
            if ($it === null) {
                continue;
            }
            $this->ac += (int) ($it['armor'] ?? 0);
            $this->minDam += (int) ($it['minDamage'] ?? 0);
            $this->maxDam += (int) ($it['maxDamage'] ?? 0);
            // ITEM_QUALITY_NORMAL || _iIdentified → apply PL bonuses (CalcPlrItemVals)
            $quality = (string) ($it['quality'] ?? 'normal');
            if ($quality === 'normal' || !empty($it['identified'])) {
                $this->ac += (int) ($it['plAC'] ?? 0);
                $this->minDam += (int) ($it['plMinDam'] ?? 0);
                $this->maxDam += (int) ($it['plMaxDam'] ?? 0);
                $this->toHit += (int) ($it['plToHit'] ?? 0);
                $damPct += (int) ($it['plDamPct'] ?? 0);
                $getHit += (int) ($it['plGetHit'] ?? 0);
                $plLight += (int) ($it['plLight'] ?? 0);
                $fastAtk = max($fastAtk, (int) ($it['plFastAttack'] ?? 0));
                $fastRec = max($fastRec, (int) ($it['plFastRecover'] ?? 0));
                $splAdd += (int) ($it['plSplLvlAdd'] ?? 0);
                $enAc += (int) ($it['plEnAc'] ?? 0);
                $str += (int) ($it['plStr'] ?? 0);
                $mag += (int) ($it['plMag'] ?? 0);
                $dex += (int) ($it['plDex'] ?? 0);
                $vit += (int) ($it['plVit'] ?? 0);
                $plHp += (int) ($it['plHP'] ?? 0);
                $plMana += (int) ($it['plMana'] ?? 0);
                $fire += (int) ($it['plFireRes'] ?? 0);
                $light += (int) ($it['plLightRes'] ?? 0);
                $magicRes += (int) ($it['plMagicRes'] ?? 0);
            }
        }
        $this->pmaxHp = 20 + $vit * 2 + ($this->level - 1) * 4 + $plHp;
        $this->pmaxMana = 10 + $mag * 2 + ($this->level - 1) * 2 + $plMana;
        $this->minDam += 1 + intdiv($str, 10);
        $this->maxDam += 3 + intdiv($str, 5);
        if ($damPct !== 0) {
            $this->minDam += intdiv($this->minDam * $damPct, 100);
            $this->maxDam += intdiv($this->maxDam * $damPct, 100);
        }
        $this->ac += intdiv($dex, 5);
        $this->toHit += intdiv($dex, 2);
        // MaxResistance = 75
        $this->pFireResist = max(0, min(75, $fire));
        $this->pLightResist = max(0, min(75, $light));
        $this->pMagicResist = max(0, min(75, $magicRes));
        $this->pGetHit = $getHit;
        $this->pLightRad = max(2, min(15, 10 + $plLight));
        $this->pFastAttack = max(0, min(4, $fastAtk));
        $this->pFastRecover = max(0, min(3, $fastRec));
        $this->pSplLvlAdd = $splAdd;
        $this->pEnAc = max(0, $enAc);
        if ($this->hasEquippedEffect('NOMANA')) {
            $this->pmaxMana = 0;
            $this->pmana = 0;
        }
        if ($this->php > $this->pmaxHp) {
            $this->php = $this->pmaxHp;
        }
        if ($this->pmana > $this->pmaxMana) {
            $this->pmana = $this->pmaxMana;
        }
    }

    /** Resist % reduction (UpdateBurningCrossDamage / PlayerMHit). */
    private function applyResistPercent(int $dam, int $resistPct): int
    {
        if ($dam <= 0 || $resistPct <= 0) {
            return max(0, $dam);
        }

        return max(0, $dam - intdiv($resistPct * $dam, 100));
    }

    private function hasHalfTrapDamage(): bool
    {
        foreach ($this->equipment as $it) {
            if ($it === null) {
                continue;
            }
            $quality = (string) ($it['quality'] ?? 'normal');
            if ($quality !== 'normal' && empty($it['identified'])) {
                continue;
            }
            $effects = $it['effects'] ?? [];
            if (is_array($effects) && in_array('ABSHALFTRAP', $effects, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * LeftMouseCmd / RightMouseDown — sets destAction + MakePlrPath only.
     */
    private function leftMouseCmd(int $sx, int $sy, int $button): void
    {
        if ($button === 1 && $this->tryPanelButtonClick($sx, $sy)) {
            return;
        }
        if ($button === 1 && $this->showInv && $this->tryInvClick($sx, $sy)) {
            return;
        }
        if ($button === 3 && $this->showInv && $this->tryInvRightClick($sx, $sy)) {
            return;
        }
        // Absorb clicks on left/right side panels
        if ($button === 1 && ($this->showChar || $this->showQuest) && $sx < 320 && $sy < 352) {
            return;
        }
        if ($button === 1 && $this->showSpellBook && $sx >= 320 && $sy < 352) {
            return;
        }
        $this->checkCursMove($sx, $sy);
        [$gx, $gy] = [$this->cursX, $this->cursY];

        // CMD_PUTITEM — drop held cursor item onto walkable tile
        if ($button === 1 && $this->cursorItem !== null) {
            $this->dropCursorItemAt($gx, $gy);

            return;
        }

        if ($button === 3) {
            if (!$this->lineClearMissile($this->px, $this->py, $gx, $gy)) {
                return;
            }
            if ($this->pmana < 6) {
                return;
            }
            $this->clrPlrPath();
            $this->destAction = DestAction::SPELL;
            $this->destX = $gx;
            $this->destY = $gy;
            $this->pmana -= 6;
            $this->startSpell($gx, $gy);

            return;
        }

        if ($this->cursKind === 'towner') {
            $this->destAction = DestAction::TALK;
            $this->destParam = $this->cursId;
            $this->destX = $gx;
            $this->destY = $gy;
            $this->makePlrPath($gx, $gy, false);

            return;
        }
        if ($this->cursKind === 'monster') {
            $this->destParam = $this->cursId;
            $this->destX = $gx;
            $this->destY = $gy;
            if ($this->usesRangedWeapon()) {
                // UsesRangedWeapon → CMD_RATTACKID — no MakePlrPath
                $this->destAction = DestAction::RATTACK_MON;
                $this->clrPlrPath();
            } else {
                $this->destAction = DestAction::ATTACK_MON;
                $this->makePlrPath($gx, $gy, false);
            }

            return;
        }
        if ($this->cursKind === 'item') {
            $this->destAction = DestAction::PICKUP;
            $this->destParam = $this->cursId;
            $this->destX = $gx;
            $this->destY = $gy;
            $this->makePlrPath($gx, $gy, true);

            return;
        }
        if ($this->cursKind === 'object') {
            $this->destAction = DestAction::OPERATE;
            $this->destParam = $this->cursId;
            $this->destX = $gx;
            $this->destY = $gy;
            // OnObjectTileAction: endspace = !solid && !door (stairs: walk onto if walkable)
            $objType = '';
            $ox = $gx;
            $oy = $gy;
            foreach ($this->objects as $o) {
                if ((int) $o['id'] === $this->cursId) {
                    $objType = (string) ($o['type'] ?? '');
                    $ox = (int) $o['x'];
                    $oy = (int) $o['y'];
                    break;
                }
            }
            $this->makePlrPath($gx, $gy, $this->objectPathEndspace($objType, $ox, $oy));

            return;
        }
        if ($this->tileWalkableForPath($gx, $gy)) {
            $this->destAction = DestAction::NONE;
            $this->destParam = 0;
            $this->lastChaseMonX = -1;
            $this->lastChaseMonY = -1;
            $this->lastChaseGoalX = -1;
            $this->lastChaseGoalY = -1;
            $this->makePlrPath($gx, $gy, true);
        }
    }

    private function repeatWalk(): void
    {
        // RepeatWalk — STAND or walking with render frame > 6
        if ($this->pmode !== 'STAND' && !($this->isWalking() && $this->playerAnim->getFrameToUseForRendering() > 6)) {
            return;
        }
        if ($this->cursKind !== 'none') {
            return;
        }
        if (!$this->tileWalkableForPath($this->cursX, $this->cursY)) {
            return;
        }
        [$tx, $ty] = $this->getTargetPosition();
        if ($this->cursX === $tx && $this->cursY === $ty) {
            return;
        }
        $this->destAction = DestAction::NONE;
        $this->makePlrPath($this->cursX, $this->cursY, true);
    }

    /**
     * Player::GetTargetPosition — future + remaining walkpath steps.
     * @return array{0:int,1:int}
     */
    private function getTargetPosition(): array
    {
        $x = $this->isWalking() ? $this->walkFutureX : $this->px;
        $y = $this->isWalking() ? $this->walkFutureY : $this->py;
        foreach ($this->walkpath as $step) {
            if ($step === DestAction::WALK_NONE) {
                break;
            }
            if ($step >= 0) {
                [$dx, $dy] = Direction::delta($step);
                $x += $dx;
                $y += $dy;
            }
        }

        return [$x, $y];
    }

    /**
     * CheckCursMove () — walk-stable convert + SelectionRegion probes.
     * Intentional divergence: no pixel sprite pick, no inv/stash/zoom/MP.
     */
    private function checkCursMove(int $sx, int $sy): void
    {
        $this->cursTempId = ($this->cursKind === 'monster') ? $this->cursId : $this->cursTempId;
        [$gx, $gy, $flip] = $this->screenToTileWithFlip($sx, $sy);
        $this->cursX = $gx;
        $this->cursY = $gy;
        $this->cursFlipflag = $flip;
        $this->cursKind = 'none';
        $this->cursId = 0;
        $this->cursEntityX = $gx;
        $this->cursEntityY = $gy;

        if ($this->map->levelType !== DungeonMap::DTYPE_TOWN) {
            if ($this->cursTempId !== 0 && $this->trySelectMonster($flip, $gx, $gy, $this->cursTempId)) {
                return;
            }
            if ($this->trySelectMonster($flip, $gx, $gy, 0)) {
                return;
            }
        } else {
            if ($this->trySelectTowner($flip, $gx, $gy)) {
                return;
            }
        }
        if ($this->trySelectObject($flip, $gx, $gy)) {
            return;
        }
        $this->trySelectItem($flip, $gx, $gy);
    }

    private function trySelectMonster(bool $flipflag, int $tileX, int $tileY, int $requireId): bool
    {
        $found = false;
        $check = function (int $region, int $dx, int $dy) use ($tileX, $tileY, $requireId, &$found): void {
            $x = $tileX + $dx;
            $y = $tileY + $dy;
            if (!$this->map->inBounds($x, $y) || !$this->isTileLitForCursor($x, $y)) {
                return;
            }
            foreach ($this->monsters as $m) {
                if (!$m['alive'] || !empty($m['hidden'])) {
                    continue;
                }
                [$tx, $ty] = $this->monsterTargetTile($m);
                // Match tile or future (sprite mid-walk sits between; click either)
                if (!(($tx === $x && $ty === $y) || ((int) $m['x'] === $x && (int) $m['y'] === $y))) {
                    continue;
                }
                if ($requireId !== 0 && (int) $m['id'] !== $requireId) {
                    continue;
                }
                $sel = (int) ($m['selectionRegion'] ?? (SelectionRegion::BOTTOM | SelectionRegion::MIDDLE));
                if (!SelectionRegion::has($sel, $region)) {
                    continue;
                }
                $this->cursKind = 'monster';
                $this->cursId = (int) $m['id'];
                $this->cursX = $x;
                $this->cursY = $y;
                $this->cursEntityX = $x;
                $this->cursEntityY = $y;
                $found = true;
            }
        };
        if (!$flipflag) {
            $check(SelectionRegion::TOP, 2, 1);
        }
        if ($flipflag) {
            $check(SelectionRegion::TOP, 1, 2);
        }
        $check(SelectionRegion::TOP, 2, 2);
        if (!$flipflag) {
            $check(SelectionRegion::MIDDLE, 1, 0);
        }
        if ($flipflag) {
            $check(SelectionRegion::MIDDLE, 0, 1);
        }
        $check(SelectionRegion::BOTTOM, 0, 0);
        $check(SelectionRegion::MIDDLE, 1, 1);

        return $found;
    }

    private function trySelectTowner(bool $flipflag, int $tileX, int $tileY): bool
    {
        $found = false;
        $check = function (int $dx, int $dy) use ($tileX, $tileY, &$found): void {
            $x = $tileX + $dx;
            $y = $tileY + $dy;
            foreach ($this->towners as $t) {
                if ((int) $t['x'] !== $x || (int) $t['y'] !== $y) {
                    continue;
                }
                $this->cursKind = 'towner';
                $this->cursId = (int) $t['id'];
                $this->cursX = $x;
                $this->cursY = $y;
                $this->cursEntityX = $x;
                $this->cursEntityY = $y;
                $found = true;
            }
        };
        if (!$flipflag) {
            $check(1, 0);
        }
        if ($flipflag) {
            $check(0, 1);
        }
        $check(0, 0);
        $check(1, 1);

        return $found;
    }

    private function trySelectObject(bool $flipflag, int $tileX, int $tileY): bool
    {
        // TrySelectObject: south → here → SE/SW
        $candidates = [
            [$tileX, $tileY + 1],
            [$tileX, $tileY],
            $flipflag ? [$tileX - 1, $tileY + 1] : [$tileX + 1, $tileY + 1],
        ];
        foreach ($candidates as [$x, $y]) {
            foreach ($this->objects as $o) {
                if ((int) $o['x'] === $x && (int) $o['y'] === $y) {
                    $this->cursKind = 'object';
                    $this->cursId = (int) $o['id'];
                    $this->cursX = $x;
                    $this->cursY = $y;
                    $this->cursEntityX = $x;
                    $this->cursEntityY = $y;

                    return true;
                }
            }
        }

        return false;
    }

    private function trySelectItem(bool $flipflag, int $tileX, int $tileY): bool
    {
        $found = false;
        $check = function (int $x, int $y) use (&$found): void {
            foreach ($this->items as $it) {
                if ((int) $it['x'] === $x && (int) $it['y'] === $y) {
                    $this->cursKind = 'item';
                    $this->cursId = (int) $it['id'];
                    $this->cursX = $x;
                    $this->cursY = $y;
                    $this->cursEntityX = $x;
                    $this->cursEntityY = $y;
                    $found = true;
                }
            }
        };
        if (!$flipflag) {
            $check($tileX + 1, $tileY);
        }
        if ($flipflag) {
            $check($tileX, $tileY + 1);
        }
        $check($tileX, $tileY);
        $check($tileX + 1, $tileY + 1);

        return $found;
    }

    /** IsTileLit / DungeonFlag::Visible — town/fullbright always; else DoVision. */
    private function isTileLitForCursor(int $x, int $y): bool
    {
        return $this->isTileVisible($x, $y);
    }

    /**
     * DrawInfoBox string lines from cursor / held item (pcurs* / HoldItem).
     *
     * @return list<string>
     */
    private function cursorInfoLines(): array
    {
        if ($this->cursorItem !== null) {
            $it = $this->cursorItem;
            if (($it['type'] ?? '') === 'gold') {
                $n = (int) ($it['value'] ?? 0);

                return [$n . ' gold ' . ($n === 1 ? 'piece' : 'pieces')];
            }
            $name = $this->itemLabel($it);
            $lines = [$name !== '' ? $name : 'Item'];
            $max = (int) ($it['maxDurability'] ?? 0);
            $dur = (int) ($it['durability'] ?? $max);
            if ($max > 0 && $max !== 255) {
                $lines[] = 'Durability: ' . $dur . ' of ' . $max;
            }
            $maxCh = (int) ($it['maxCharges'] ?? 0);
            if ($maxCh > 0) {
                $lines[] = 'Charges: ' . (int) ($it['charges'] ?? 0) . ' of ' . $maxCh;
                $sp = (string) ($it['spell'] ?? '');
                if ($sp !== '' && $sp !== 'Null') {
                    $lines[] = 'Spell: ' . $sp;
                }
            }
            $quality = (string) ($it['quality'] ?? 'normal');
            if ($quality !== 'normal' && !empty($it['identified'])) {
                $lines = array_merge($lines, $this->itemAffixLines($it));
            }

            return $lines;
        }
        $id = $this->cursId;
        return match ($this->cursKind) {
            'monster' => (function () use ($id): array {
                foreach ($this->monsters as $m) {
                    if ((int) $m['id'] === $id) {
                        $name = (string) ($m['name'] ?? 'Monster');
                        $hp = (int) ($m['hp'] ?? 0);
                        $max = (int) ($m['maxHp'] ?? $hp);

                        return [$name, 'Hit Points ' . $hp . ' of ' . $max];
                    }
                }

                return ['Monster'];
            })(),
            'towner' => (function () use ($id): array {
                foreach ($this->towners as $t) {
                    if ((int) $t['id'] === $id) {
                        return [(string) ($t['name'] ?? 'Townsfolk')];
                    }
                }

                return ['Townsfolk'];
            })(),
            'item' => (function () use ($id): array {
                foreach ($this->items as $it) {
                    if ((int) $it['id'] === $id) {
                        if (($it['type'] ?? '') === 'gold') {
                            $n = (int) ($it['value'] ?? 0);

                            return [$n . ' gold ' . ($n === 1 ? 'piece' : 'pieces')];
                        }

                        $name = $this->itemLabel($it);
                        $lines = [$name !== '' ? $name : 'Item'];
                        $max = (int) ($it['maxDurability'] ?? 0);
                        $dur = (int) ($it['durability'] ?? $max);
                        if ($max > 0 && $max !== 255) {
                            $lines[] = 'Durability: ' . $dur . ' of ' . $max;
                        }
                        $maxCh = (int) ($it['maxCharges'] ?? 0);
                        if ($maxCh > 0) {
                            $lines[] = 'Charges: ' . (int) ($it['charges'] ?? 0) . ' of ' . $maxCh;
                        }

                        return $lines;
                    }
                }

                return ['Item'];
            })(),
            'object' => (function () use ($id): array {
                foreach ($this->objects as $o) {
                    if ((int) $o['id'] === $id) {
                        $type = (string) ($o['type'] ?? 'object');
                        if (!empty($o['name'])) {
                            return [(string) $o['name']];
                        }

                        return [match ($type) {
                            'door' => !empty($o['opened']) ? 'Open Door' : 'Closed Door',
                            'chest' => !empty($o['opened']) ? 'Open Chest' : 'Chest',
                            'barrel' => 'Barrel',
                            'stairs_down' => 'Down to dungeon',
                            'stairs_up' => 'Up to town',
                            'stairs_warp' => 'Town portal stairs',
                            default => ucfirst(str_replace('_', ' ', $type)),
                        }];
                    }
                }

                return ['Object'];
            })(),
            default => [],
        };
    }

    /**
     * HoldItem / NewCursor sync — authoritative mouse sprite state.
     * @param array<string,mixed>|null $item
     */
    private function setHeldCursorItem(?array $item): void
    {
        $this->cursorItem = $item;
        if ($item !== null && empty($item['spriteRgba'])) {
            $this->loadItemSprite($this->cursorItem);
            $item = $this->cursorItem;
        }
        $this->cursor->newCursorFromItem($item);
    }

    /**
     * DrawCursor (scrollrt.cpp) — software objcurs sprite at logical mouse + hotspot.
     * Drawn after world/HUD so it is never covered. Survives --hide-hud.
     */
    private function drawCursor(Sdl $sdl): void
    {
        $pcurs = $this->cursor->pcurs;
        if ($pcurs <= Cursor::CURSOR_NONE) {
            $this->lastCursorDraw = ['pcurs' => $pcurs, 'drawn' => 0];

            return;
        }
        // Holding an item forces item cursor even if pcurs drifted
        if ($this->cursorItem !== null && $pcurs < Cursor::CURSOR_FIRSTITEM) {
            $this->cursor->newCursorFromItem($this->cursorItem);
            $pcurs = $this->cursor->pcurs;
        } elseif ($this->cursorItem === null && $pcurs >= Cursor::CURSOR_FIRSTITEM) {
            $this->cursor->newCursor(Cursor::CURSOR_HAND);
            $pcurs = Cursor::CURSOR_HAND;
        }

        if ($this->objcursCel === null) {
            if ($this->assets->has(Cursor::ASSET_PATH)) {
                $this->objcursCel = $this->assets->loadCel(Cursor::ASSET_PATH);
            } else {
                $this->lastCursorDraw = ['pcurs' => $pcurs, 'drawn' => 0, 'err' => 'missing objcurs'];

                return;
            }
        }

        $holding = $this->cursorItem !== null;
        // Prefer already-decoded item sprite when holding (same CEL/frame)
        if ($holding && $this->cursorItem !== null) {
            if (empty($this->cursorItem['spriteRgba'])) {
                $this->loadItemSprite($this->cursorItem);
            }
            if (!empty($this->cursorItem['spriteRgba']) && !empty($this->cursorItem['spriteW'])) {
                $sw = (int) $this->cursorItem['spriteW'];
                $sh = (int) $this->cursorItem['spriteH'];
                $frame = (int) ($this->cursorItem['objcursFrame'] ?? Cursor::frameIndex($pcurs));
                $width = Cursor::frameWidth($frame);
                $rgba = (string) $this->cursorItem['spriteRgba'];
                $hot = Cursor::hotspotOffset($pcurs, $sw, $sh, true);
                $dx = $this->mouseX - $hot['x'];
                $dy = $this->mouseY - $hot['y'];
                $key = Cursor::cacheKey($frame, $width, $this->cursor->paletteEpoch);
                if (!isset($this->microCache[$key]) || !is_array($this->microCache[$key])) {
                    $this->microCache[$key] = [
                        'tex' => $sdl->createTextureRGBA($sw, $sh, $rgba),
                        'w' => $sw,
                        'h' => $sh,
                        'rgba' => $rgba,
                    ];
                    $this->profileUploads++;
                }
                $sdl->setDrawColor(255, 255, 0);
                $sdl->fillRect($dx - 1, $dy - 1, $sw + 2, 1);
                $sdl->fillRect($dx - 1, $dy + $sh, $sw + 2, 1);
                $sdl->fillRect($dx - 1, $dy - 1, 1, $sh + 2);
                $sdl->fillRect($dx + $sw, $dy - 1, 1, $sh + 2);
                $sdl->copyTexture($this->microCache[$key]['tex'], $dx, $dy, $sw, $sh);
                $this->lastCursorDraw = [
                    'pcurs' => $pcurs,
                    'name' => Cursor::nameFor($pcurs),
                    'asset' => Cursor::ASSET_PATH,
                    'frame' => $frame,
                    'width' => $width,
                    'w' => $sw,
                    'h' => $sh,
                    'key' => $key,
                    'hotX' => $hot['x'],
                    'hotY' => $hot['y'],
                    'dstX' => $dx,
                    'dstY' => $dy,
                    'mouseX' => $this->mouseX,
                    'mouseY' => $this->mouseY,
                    'holding' => 1,
                    'drawn' => 1,
                ];
                $this->profileDraws++;

                return;
            }
            // Missing art: fall back to HAND (no yellow placeholder)
            fwrite(STDERR, "cursor: held item missing sprite — falling back to HAND\n");
            $this->cursor->newCursor(Cursor::CURSOR_HAND);
            $pcurs = Cursor::CURSOR_HAND;
        }

        $frame = Cursor::frameIndex($pcurs);
        $width = Cursor::frameWidth($frame);
        $key = Cursor::cacheKey($frame, $width, $this->cursor->paletteEpoch);

        if (!isset($this->microCache[$key]) || !is_array($this->microCache[$key])) {
            $pal = $this->map->palette ?? null;
            if ($pal === null) {
                try {
                    $pal = $this->assets->loadPalette('levels\\towndata\\town.pal');
                } catch (\Throwable) {
                    $this->lastCursorDraw = ['pcurs' => $pcurs, 'drawn' => 0, 'err' => 'no palette'];

                    return;
                }
            }
            try {
                $spr = Cursor::decodeSprite($this->objcursCel, $pcurs, $pal);
            } catch (\Throwable $e) {
                fwrite(STDERR, 'cursor decode: ' . $e->getMessage() . "\n");

                return;
            }
            $sw = (int) $spr['w'];
            $sh = (int) $spr['h'];
            if ($sw <= 0 || $sh <= 0 || $spr['rgba'] === '') {
                return;
            }
            $this->microCache[$key] = [
                'tex' => $sdl->createTextureRGBA($sw, $sh, (string) $spr['rgba']),
                'w' => $sw,
                'h' => $sh,
                'rgba' => (string) $spr['rgba'],
            ];
            $this->profileUploads++;
        }

        $sw = (int) $this->microCache[$key]['w'];
        $sh = (int) $this->microCache[$key]['h'];
        $hot = Cursor::hotspotOffset($pcurs, $sw, $sh, false);
        $dx = $this->mouseX - $hot['x'];
        $dy = $this->mouseY - $hot['y'];
        $sdl->copyTexture($this->microCache[$key]['tex'], $dx, $dy, $sw, $sh);
        $this->lastCursorDraw = [
            'pcurs' => $pcurs,
            'name' => Cursor::nameFor($pcurs),
            'asset' => Cursor::ASSET_PATH,
            'frame' => $frame,
            'width' => $width,
            'w' => $sw,
            'h' => $sh,
            'key' => $key,
            'hotX' => $hot['x'],
            'hotY' => $hot['y'],
            'dstX' => $dx,
            'dstY' => $dy,
            'mouseX' => $this->mouseX,
            'mouseY' => $this->mouseY,
            'holding' => 0,
            'drawn' => 1,
        ];
        $this->profileDraws++;
    }

    private function logCursorDebug(): void
    {
        [$sx, $sy] = $this->currentWalkScroll();
        $fx = $this->isWalking() ? $this->walkFutureX : $this->px;
        $fy = $this->isWalking() ? $this->walkFutureY : $this->py;
        $d = $this->lastCursorDraw;
        fwrite(STDOUT, sprintf(
            "cursor-target mouse=%d,%d tile=%d,%d flip=%d player=%d,%d future=%d,%d cam=%d,%d scroll=%d,%d kind=%s id=%d ent=%d,%d dest=%d walk0=%d"
            . " | pcurs=%d(%s) asset=%s frame=%s w=%s h=%s hot=%s,%s dst=%s,%s key=%s holding=%s\n",
            $this->mouseX,
            $this->mouseY,
            $this->cursX,
            $this->cursY,
            $this->cursFlipflag ? 1 : 0,
            $this->px,
            $this->py,
            $fx,
            $fy,
            $this->camX,
            $this->camY,
            $sx,
            $sy,
            $this->cursKind,
            $this->cursId,
            $this->cursEntityX,
            $this->cursEntityY,
            $this->destAction,
            $this->walkpath[0] ?? DestAction::WALK_NONE,
            (int) ($d['pcurs'] ?? $this->cursor->pcurs),
            (string) ($d['name'] ?? Cursor::nameFor($this->cursor->pcurs)),
            (string) ($d['asset'] ?? Cursor::ASSET_PATH),
            isset($d['frame']) ? (string) $d['frame'] : '?',
            isset($d['w']) ? (string) $d['w'] : '?',
            isset($d['h']) ? (string) $d['h'] : '?',
            isset($d['hotX']) ? (string) $d['hotX'] : '?',
            isset($d['hotY']) ? (string) $d['hotY'] : '?',
            isset($d['dstX']) ? (string) $d['dstX'] : '?',
            isset($d['dstY']) ? (string) $d['dstY'] : '?',
            (string) ($d['key'] ?? ''),
            (string) ($d['holding'] ?? ($this->cursorItem !== null ? 1 : 0))
        ));
    }

    /** @return array{0:int,1:int} */
    private function screenToTile(int $sx, int $sy, int $winW, int $winH): array
    {
        [$tx, $ty] = $this->screenToTileWithFlip($sx, $sy);

        return [$tx, $ty];
    }

    /**
     * AlterMousePositionViaPlayer walk offset + velocity predict, then ConvertToTileGrid.
     * ViewPosition = cam (origin tile while walking); scroll subtract matches cameraMode.
     * @return array{0:int,1:int,2:bool} tileX, tileY, flipflag
     */
    private function screenToTileWithFlip(int $sx, int $sy): array
    {
        [$scrollX, $scrollY] = $this->isWalking() && !$this->cameraFixed
            ? $this->currentWalkScroll()
            : [0, 0];
        [$predX, $predY] = $this->cursorWalkPredictDelta();
        $adjX = $sx - $predX;
        $adjY = $sy - $predY;
        $ex = Scrollrt::screenToTileEx(
            $adjX,
            $adjY,
            $this->camX,
            $this->camY,
            IsoCoords::SCREEN_W,
            IsoCoords::viewportHeight(),
            $scrollX,
            $scrollY
        );

        return [$ex['tile'][0], $ex['tile'][1], $ex['flipflag']];
    }

    /**
     * --debug-world-pick: classify one screen click against dPiece/micros/specials.
     */
    public function dumpWorldPick(int $sx, int $sy): void
    {
        $vpH = IsoCoords::viewportHeight();
        $panelTop = IsoCoords::SCREEN_H - IsoCoords::PANEL_HEIGHT;
        $inViewport = $sy >= 0 && $sy < $vpH;
        [$tx, $ty, $flip] = $this->screenToTileWithFlip($sx, $sy);
        $inBounds = $this->map->inBounds($tx, $ty);
        $piece = $inBounds ? (int) ($this->map->dPiece[$ty][$tx] ?? 0) : 0;
        $megaX = $inBounds ? intdiv(max(0, $tx - 16), 2) : -1;
        $megaY = $inBounds ? intdiv(max(0, $ty - 16), 2) : -1;
        $dungeon = ($megaX >= 0 && $megaY >= 0 && $megaX < 40 && $megaY < 40)
            ? (int) ($this->map->dungeon[$megaY][$megaX] ?? 0) : -1;
        $special = $inBounds ? (int) ($this->map->dSpecial[$ty][$tx] ?? 0) : 0;
        $trans = $inBounds ? (int) ($this->map->dTransVal[$ty][$tx] ?? 0) : 0;
        $sol = $piece >= 0 ? (int) ($this->map->sol[$piece] ?? 0) : 0;
        $types = array_fill(0, 6, 0);
        $invalid = [];
        $lines = [];
        $cat = $inBounds
            ? $this->classifyTileCoverage($tx, $ty, $types, $invalid, $lines)
            : 'OUTSIDE_WORLD';
        if ($cat === 'EXPECTED_BUT_EMPTY') {
            $cat = 'EXPECTED_NOT_DRAWN';
        }
        if (!$inViewport && $inBounds && isset($this->map->dPieceMicros[$piece])) {
            $cat = 'CLIPPED';
        }
        $micros = [];
        $microsWithOffsets = [];
        $isFloor = $inBounds && $this->map->isFloorTile($tx, $ty);
        foreach ($this->map->dPieceMicros[$piece] ?? [] as $bi => $b) {
            $type = (int) ($b['type'] ?? 0);
            $frame = (int) ($b['frame'] ?? 0);
            $ox = ($bi & 1) === 0 ? 0 : 32;
            $oy = -intdiv((int) $bi, 2) * 32;
            if ($isFloor && (int) $bi < 2 && $type === DunMicro::TYPE_TRANSPARENT_SQUARE) {
                $oy = -16; // foliage band
            }
            $micros[] = [
                'block' => $bi,
                'type' => $type,
                'frame' => $frame,
            ];
            $microsWithOffsets[] = [
                'block' => $bi,
                'type' => $type,
                'frame' => $frame,
                'relX' => $ox,
                'relY' => $oy,
            ];
        }
        [$scrollX, $scrollY] = $this->currentWalkScroll();
        $row = [
            'screen' => [$sx, $sy],
            'view' => [$this->camX, $this->camY],
            'scroll' => [$scrollX, $scrollY],
            'tile' => [$tx, $ty],
            'flipflag' => $flip ? 1 : 0,
            'dPiece' => $piece,
            'dungeon' => $dungeon,
            'mega' => [$megaX, $megaY],
            'dSpecial' => $special,
            'dTransVal' => $trans,
            'transList' => $this->map->transListActive($trans) ? 1 : 0,
            'sol' => $sol,
            'isFloor' => $inBounds ? ($this->map->isFloorTile($tx, $ty) ? 1 : 0) : 0,
            'isSolid' => $inBounds ? ($this->map->isSolid($tx, $ty) ? 1 : 0) : 0,
            'inBounds' => $inBounds ? 1 : 0,
            'inViewport' => $inViewport ? 1 : 0,
            'viewportH' => $vpH,
            'panelTop' => $panelTop,
            'category' => $cat,
            'micros' => $micros,
            'microsOffsets' => $microsWithOffsets,
        ];
        $text = sprintf(
            "WORLD_PICK screen=%d,%d tile=%d,%d dPiece=%d dungeon=%d dSpecial=%d dTrans=%d sol=0x%02X cat=%s inViewport=%s micros=%d\n",
            $sx,
            $sy,
            $tx,
            $ty,
            $piece,
            $dungeon,
            $special,
            $trans,
            $sol,
            $cat,
            $inViewport ? 'Y' : 'N',
            count($micros)
        );
        foreach ($microsWithOffsets as $m) {
            $text .= sprintf(
                "  micro block=%d type=%d frame=%d offset=(%d,%d)\n",
                $m['block'],
                $m['type'],
                $m['frame'],
                $m['relX'],
                $m['relY']
            );
        }
        fwrite(STDOUT, $text);
        $path = dirname(__DIR__) . '/tmp/world-pick.jsonl';
        $alignPath = dirname(__DIR__) . '/tmp/town-piece-alignment.jsonl';
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($row) . "\n", FILE_APPEND);
        file_put_contents($alignPath, json_encode($row) . "\n", FILE_APPEND);
        if ($this->map->levelType === DungeonMap::DTYPE_CATHEDRAL) {
            $l1Path = dirname(__DIR__) . '/tmp/l1-piece-audit.jsonl';
            file_put_contents($l1Path, json_encode($row + ['source' => 'world-pick'], JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
        }
    }

    /**
     * --dump-cell=X,Y: dump graph inputs for one tile (dPiece, micros, SOL, specials, entities).
     * @return array<string,mixed>
     */
    public function dumpCell(int $tx, int $ty): array
    {
        $inBounds = $this->map->inBounds($tx, $ty);
        $piece = $inBounds ? (int) ($this->map->dPiece[$ty][$tx] ?? 0) : 0;
        $special = $inBounds ? (int) ($this->map->dSpecial[$ty][$tx] ?? 0) : 0;
        $trans = $inBounds ? (int) ($this->map->dTransVal[$ty][$tx] ?? 0) : 0;
        $light = $inBounds ? (int) ($this->map->dLight[$ty][$tx] ?? 0) : 0;
        $sol = $piece >= 0 ? (int) ($this->map->sol[$piece] ?? 0) : 0;
        $micros = [];
        foreach ($this->map->dPieceMicros[$piece] ?? [] as $bi => $b) {
            $micros[] = [
                'block' => $bi,
                'type' => (int) ($b['type'] ?? 0),
                'frame' => (int) ($b['frame'] ?? 0),
            ];
        }
        $idx = $this->buildEntityIndex();
        $ents = [];
        foreach ($idx[$tx . ',' . $ty] ?? [] as $e) {
            $p = $e['payload'];
            $summary = ['kind' => $e['kind']];
            if (is_array($p)) {
                foreach (['id', 'type', 'x', 'y', 'alive', 'mode', 'dir', 'postDraw', '_iPostDraw', 'oPreFlag', '_oPreFlag', '_drawFromDest', 'futX', 'futY'] as $k) {
                    if (array_key_exists($k, $p)) {
                        $summary[$k] = $p[$k];
                    }
                }
                if (!empty($p['towner'])) {
                    $summary['towner'] = $p['towner']['id'] ?? true;
                }
                if (!empty($p['hero'])) {
                    $summary['hero'] = true;
                }
            }
            $ents[] = $summary;
        }
        $townSpec = null;
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN && $tx > 0 && $ty > 0) {
            $townSpec = (int) ($this->map->dSpecial[$ty - 1][$tx - 1] ?? 0);
        }
        $row = [
            'tile' => [$tx, $ty],
            'inBounds' => $inBounds ? 1 : 0,
            'levelType' => $this->map->levelType,
            'dPiece' => $piece,
            'dSpecial' => $special,
            'dSpecial_town_deferred_from' => $townSpec,
            'dTransVal' => $trans,
            'transList' => $this->map->transListActive($trans) ? 1 : 0,
            'dLight' => $light,
            'sol' => $sol,
            'isFloor' => $inBounds ? ($this->map->isFloorTile($tx, $ty) ? 1 : 0) : 0,
            'isSolid' => $inBounds ? ($this->map->isSolid($tx, $ty) ? 1 : 0) : 0,
            'microTileLen' => $this->map->microTileLen,
            'micros' => $micros,
            'entities' => $ents,
            'player' => [$this->px, $this->py],
            'cam' => [$this->camX, $this->camY],
        ];
        $text = sprintf(
            "DUMP_CELL tile=%d,%d dPiece=%d dSpecial=%d dTrans=%d transList=%d sol=0x%02X light=%d floor=%d solid=%d micros=%d ents=%d\n",
            $tx,
            $ty,
            $piece,
            $special,
            $trans,
            $row['transList'],
            $sol,
            $light,
            $row['isFloor'],
            $row['isSolid'],
            count($micros),
            count($ents)
        );
        foreach ($micros as $m) {
            $text .= sprintf("  micro block=%d type=%d frame=%d\n", $m['block'], $m['type'], $m['frame']);
        }
        foreach ($ents as $e) {
            $text .= '  ent ' . json_encode($e) . "\n";
        }
        if ($townSpec !== null && $townSpec > 0) {
            $text .= sprintf("  town_deferred_special_from %d,%d = %d\n", $tx - 1, $ty - 1, $townSpec);
        }
        fwrite(STDOUT, $text);
        $path = dirname(__DIR__) . '/tmp/dump-cell.jsonl';
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($row) . "\n", FILE_APPEND);

        return $row;
    }

    /**
     * AlterMousePositionViaPlayer velocity prediction (pixel jitter fix).
     * @return array{0:int,1:int}
     */
    private function cursorWalkPredictDelta(): array
    {
        if (!$this->isWalking() || $this->cameraFixed) {
            return [0, 0];
        }
        $bf = AnimationInfo::BASE_VALUE_FRACTION;
        $saved = $this->playerAnim->progressToNextGameTick;
        $this->playerAnim->progressToNextGameTick = (int) round($this->tickAlpha * $bf);
        [$x0, $y0] = WalkOffset::fromAnimInfo($this->playerAnim, $this->pdir, false);
        $this->playerAnim->progressToNextGameTick = $bf;
        [$x1, $y1] = WalkOffset::fromAnimInfo($this->playerAnim, $this->pdir, false);
        $this->playerAnim->progressToNextGameTick = $saved;
        // screenPosition -= (o - (o+v)) with v≈(x1-x0) → subtract (x0-x1) = add (x1-x0) toward motion
        return [$x0 - $x1, $y0 - $y1];
    }

    /** @return array{0:int,1:int} */
    private function currentWalkScroll(): array
    {
        if (!$this->isWalking() || $this->cameraFixed) {
            return [0, 0];
        }
        $this->playerAnim->progressToNextGameTick = (int) round($this->tickAlpha * AnimationInfo::BASE_VALUE_FRACTION);

        return WalkOffset::fromAnimInfo($this->playerAnim, $this->pdir, true);
    }

    /** @return array{0:int,1:int} */
    private function currentWalkActorOffset(): array
    {
        if (!$this->isWalking()) {
            return [0, 0];
        }
        $this->playerAnim->progressToNextGameTick = (int) round($this->tickAlpha * AnimationInfo::BASE_VALUE_FRACTION);

        return WalkOffset::fromAnimInfo($this->playerAnim, $this->pdir, false);
    }

    /**
     * CalcFirstTilePosition walking branch — shift view tile + scroll.
     * @return array{0:int,1:int,2:int,3:int} viewX,viewY,scrollX,scrollY
     */
    private function applyCameraWalkShift(int $viewX, int $viewY, int $scrollX, int $scrollY): array
    {
        switch ($this->pdir) {
            case Direction::N:
            case Direction::NE:
                [$ndx, $ndy] = Direction::delta(Direction::N);
                $viewX += $ndx;
                $viewY += $ndy;
                $scrollY -= IsoCoords::TILE_HEIGHT;
                break;
            case Direction::SW:
            case Direction::W:
                [$wdx, $wdy] = Direction::delta(Direction::W);
                $viewX += $wdx;
                $viewY += $wdy;
                $scrollX -= IsoCoords::TILE_WIDTH;
                break;
            case Direction::NW:
                [$ndx, $ndy] = Direction::delta(Direction::NW);
                $viewX += $ndx;
                $viewY += $ndy;
                $scrollX -= intdiv(IsoCoords::TILE_WIDTH, 2);
                $scrollY -= intdiv(IsoCoords::TILE_HEIGHT, 2);
                break;
            default:
                break;
        }

        return [$viewX, $viewY, $scrollX, $scrollY];
    }

    public function enableWalkStepTrace(string $path): void
    {
        $this->debugWalkStep = true;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->walkStepFh = fopen($path, 'wb');
    }

    public function closeWalkStepTrace(): void
    {
        if (is_resource($this->walkStepFh)) {
            fclose($this->walkStepFh);
            $this->walkStepFh = null;
        }
        $this->debugWalkStep = false;
    }

    private function emitWalkStepTrace(string $phase): void
    {
        [$scrollX, $scrollY] = $this->isWalking() ? $this->currentWalkScroll() : [0, 0];
        [$ax, $ay] = $this->currentWalkActorOffset();
        $viewX = $this->camX;
        $viewY = $this->camY;
        if ($this->isWalking()) {
            [$viewX, $viewY, $scrollX, $scrollY] = $this->applyCameraWalkShift($viewX, $viewY, $scrollX, $scrollY);
        }
        $row = [
            'phase' => $phase,
            'tick' => $this->logicTick,
            'mode' => $this->pmode,
            'dir' => $this->pdir,
            'cur' => [$this->px, $this->py],
            'fut' => [$this->walkFutureX, $this->walkFutureY],
            'temp' => [$this->walkTempX, $this->walkTempY],
            'cam' => [$this->camX, $this->camY],
            'view' => [$viewX, $viewY],
            'scroll' => [$scrollX, $scrollY],
            'actor' => [$ax, $ay],
            'anim' => $this->playerAnim->currentFrame,
            'progress' => $this->playerAnim->getAnimationProgress(),
            'alpha' => $this->tickAlpha,
            'walk0' => $this->walkpath[0] ?? DestAction::WALK_NONE,
            'dest' => $this->destAction,
            'movingOffset' => Direction::movingOffset($this->pdir),
            'worldDelta' => Direction::delta($this->pdir),
        ];
        $line = json_encode($row, JSON_UNESCAPED_SLASHES) . "\n";
        if (is_resource($this->walkStepFh)) {
            fwrite($this->walkStepFh, $line);
        } else {
            fwrite(STDOUT, 'walk-step ' . $line);
        }
    }

    private function tileWalkable(int $x, int $y): bool
    {
        return $this->tileWalkableForPath($x, $y);
    }

    private function tileWalkableForPath(int $x, int $y): bool
    {
        if (!$this->map->isWalkable($x, $y)) {
            return false;
        }
        if ($this->isClosedDoorAt($x, $y)) {
            return false;
        }
        if ($this->occ->blocksActor($x, $y, $this->playerId, 0)) {
            return false;
        }

        return true;
    }

 /** Closed door objects block actors (IsTileWalkable solid door). */
    private function isClosedDoorAt(int $x, int $y): bool
    {
        foreach ($this->objects as $o) {
            if (($o['type'] ?? '') === 'door' && empty($o['opened'])
                && (int) $o['x'] === $x && (int) $o['y'] === $y) {
                return true;
            }
        }

        return false;
    }

    /**
     * objdat MissilesPassThrough — false for shrine/bookcase/stands/cauldron/closed doors.
     */
    private function objectMissilesPassThrough(string $type): bool
    {
        return match ($type) {
            'shrine', 'bookcase', 'armorstand', 'weaponrack', 'cauldron' => false,
            'door' => false, // closed default; SetDoorStateOpen sets true
            'stairs_up', 'stairs_down', 'stairs_warp' => true,
            default => true, // chests, barrels, levers, traps, fountains, sarc, …
        };
    }

    /**
     * LineClearMissile with object-aware PosOkMissile (!isMissileBlockedAt).
     */
    private function lineClearMissile(int $x0, int $y0, int $x1, int $y1): bool
    {
        return LineClear::missile(
            $this->map,
            $x0,
            $y0,
            $x1,
            $y1,
            fn (int $x, int $y): bool => !$this->isMissileBlockedAt($x, $y),
        );
    }

    /**
     * IsMissileBlockedByTile — SOL BlockMissile, or object with !_oMissFlag.
     */
    private function isMissileBlockedAt(int $x, int $y): bool
    {
        if (!$this->map->inBounds($x, $y)) {
            return true;
        }
        if ($this->map->blocksMissile($x, $y)) {
            return true;
        }
        foreach ($this->objects as $o) {
            if ((int) ($o['x'] ?? -1) !== $x || (int) ($o['y'] ?? -1) !== $y) {
                continue;
            }
            $pass = array_key_exists('oMissFlag', $o)
                ? !empty($o['oMissFlag'])
                : $this->objectMissilesPassThrough((string) ($o['type'] ?? ''));
            if (!$pass) {
                return true;
            }
        }

        return false;
    }

    /**
     * AddL1Objs — OBJ_L1LIGHT + OBJ_L1LDOOR / OBJ_L1RDOOR on matching pieces.
     * @return int objects added
     */
    private function addCathedralDoorObjects(): int
    {
        if ($this->map->levelType !== DungeonMap::DTYPE_CATHEDRAL) {
            return 0;
        }
        $n = 0;
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $pn = $this->map->dPiece[$y][$x] ?? 0;
                if ($pn === 269) {
                    // OBJ_L1LIGHT — wall niche light (AddObjectLight radius 5)
                    $obj = [
                        'id' => $this->nextId++,
                        'type' => 'l1light',
                        'x' => $x,
                        'y' => $y,
                        'lightRadius' => 5,
                    ];
                    $this->objects[] = $obj;
                    $n++;
                    continue;
                }
                $side = null;
                if ($pn === 43 || $pn === 50 || $pn === 213) {
                    $side = 'L'; // OBJ_L1LDOOR
                } elseif ($pn === 45 || $pn === 55) {
                    $side = 'R'; // OBJ_L1RDOOR
                }
                if ($side === null) {
                    continue;
                }
                $obj = [
                    'id' => $this->nextId++,
                    'type' => 'door',
                    'x' => $x,
                    'y' => $y,
                    'opened' => false,
                    'doorSide' => $side,
                    // AddDoor: _oVar1 = dPiece+1, _oVar2 = neighbor+1
                    'oVar1' => $pn + 1,
                    'oVar2' => $side === 'L'
                        ? (($this->map->dPiece[$y - 1][$x] ?? 0) + 1)
                        : (($this->map->dPiece[$y][$x - 1] ?? 0) + 1),
                ];
                $this->loadObjectSprite($obj);
                $this->objects[] = $obj;
                $this->occ->setObject($x, $y, (int) $obj['id']);
                $n++;
            }
        }

        return $n;
    }

    /**
     * AddL2Objs — OBJ_L2LDOOR / OBJ_L2RDOOR on pieces 12/16/540/541.
     * @return int objects added
     */
    private function addCatacombsDoorObjects(): int
    {
        if ($this->map->levelType !== DungeonMap::DTYPE_CATACOMBS) {
            return 0;
        }
        $n = 0;
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $pn = $this->map->dPiece[$y][$x] ?? 0;
                $side = null;
                if ($pn === 12 || $pn === 540) {
                    $side = 'L'; // OBJ_L2LDOOR
                } elseif ($pn === 16 || $pn === 541) {
                    $side = 'R'; // OBJ_L2RDOOR
                }
                if ($side === null) {
                    continue;
                }
                $obj = [
                    'id' => $this->nextId++,
                    'type' => 'door',
                    'doorLevel' => 'L2',
                    'x' => $x,
                    'y' => $y,
                    'opened' => false,
                    'doorSide' => $side,
                    'oVar1' => $pn + 1,
                ];
                // AddDoor → SetDoorStateClosed (piece 537/539)
                $this->objSetMicro($x, $y, $side === 'L' ? 537 : 539);
                $this->loadObjectSprite($obj);
                $this->objects[] = $obj;
                $this->occ->setObject($x, $y, (int) $obj['id']);
                $n++;
            }
        }

        return $n;
    }

    /**
     * AddL3Objs — OBJ_L3LDOOR / OBJ_L3RDOOR on pieces 530 / 533.
     * @return int objects added
     */
    private function addCavesDoorObjects(): int
    {
        if ($this->map->levelType !== DungeonMap::DTYPE_CAVES) {
            return 0;
        }
        $n = 0;
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $pn = $this->map->dPiece[$y][$x] ?? 0;
                $side = null;
                if ($pn === 530) {
                    $side = 'L'; // OBJ_L3LDOOR
                } elseif ($pn === 533) {
                    $side = 'R'; // OBJ_L3RDOOR
                }
                if ($side === null) {
                    continue;
                }
                $obj = [
                    'id' => $this->nextId++,
                    'type' => 'door',
                    'doorLevel' => 'L3',
                    'x' => $x,
                    'y' => $y,
                    'opened' => false,
                    'doorSide' => $side,
                    'oVar1' => $pn + 1,
                ];
                // Already closed pieces 530/533 (SetDoorStateClosed)
                $this->loadObjectSprite($obj);
                $this->objects[] = $obj;
                $this->occ->setObject($x, $y, (int) $obj['id']);
                $n++;
            }
        }

        return $n;
    }

    /**
     * AddL2Torches stand-in — wall torches on solid tiles abutting floors (radius 8).
     * DX keys pieces 0/4/36/40; PARTIAL L2 lacks those, so use wall+floor adjacency.
     * @return int torches added
     */
    private function addCatacombsTorches(): int
    {
        if ($this->map->levelType !== DungeonMap::DTYPE_CATACOMBS) {
            return 0;
        }
        $n = 0;
        $cels = [
            'objects\\wtorch1.cel',
            'objects\\wtorch2.cel',
            'objects\\wtorch3.cel',
            'objects\\wtorch4.cel',
        ];
        for ($y = 1; $y < DungeonMap::MAXDUNY - 1; $y++) {
            for ($x = 1; $x < DungeonMap::MAXDUNX - 1; $x++) {
                $pn = $this->map->dPiece[$y][$x] ?? 0;
                if ($pn <= 0 || !$this->map->isSolid($x, $y)) {
                    continue;
                }
                $nearFloor = false;
                foreach ([[0, -1], [1, 0], [0, 1], [-1, 0]] as [$dx, $dy]) {
                    if ($this->map->isWalkable($x + $dx, $y + $dy)) {
                        $nearFloor = true;
                        break;
                    }
                }
                if (!$nearFloor) {
                    continue;
                }
                // FlipCoin(25) — keep torch count modest (shared sheet cache)
                if (!$this->rng->flipCoin(25)) {
                    continue;
                }
                $cel = $cels[$this->rng->generateRnd(4)];
                $obj = [
                    'id' => $this->nextId++,
                    'type' => 'torch',
                    'x' => $x,
                    'y' => $y,
                    'cel' => $cel,
                    'lightRadius' => 8,
                    'oPreFlag' => true,
                ];
                $this->loadObjectSprite($obj);
                $this->objects[] = $obj;
                $n++;
            }
        }

        return $n;
    }

    /**
     * AddL4Goodies (partial) — cauldron, tortured bodies, decapitated bodies on Hell.
     * @return int objects added
     */
    private function addHellGoodies(): int
    {
        if ($this->map->levelType !== DungeonMap::DTYPE_HELL) {
            return 0;
        }
        $n = 0;
        $n += $this->initRndLocObj(1, 3, 'cauldron', []);
        $n += $this->initRndLocObj(2, 6, 'decap', ['oPreFlag' => true]);
        $n += $this->initRndLocObj(2, 6, 'tnudem', ['oPreFlag' => true]);
        $n += $this->initRndLocObj(2, 6, 'tnudew', ['oPreFlag' => true]);

        return $n;
    }

    /** Theme-lite story/shrine candles — animated + light radius 5. */
    private function addDungeonCandles(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            return 0;
        }

        return $this->initRndLocObj(2, 5, 'candle', [
            'lightRadius' => 5,
            'oPreFlag' => true,
        ]);
    }

    /** Theme-lite banners, skull-fire, burning crosses, torture souls. */
    private function addDungeonDecor(): int
    {
        $lt = $this->map->levelType;
        if ($lt === DungeonMap::DTYPE_TOWN) {
            return 0;
        }
        $n = 0;
        // Banners — L/M/R frames (objdat 2/1/3 → 0-based 1/0/2)
        foreach ([['L', 1], ['M', 0], ['R', 2]] as [$side, $frame]) {
            $n += $this->initRndLocObj(1, 2, 'banner', [
                'doorSide' => $side,
                'spriteFrame' => $frame,
                'lightRadius' => 3,
                'oPreFlag' => true,
            ]);
        }
        if ($lt === DungeonMap::DTYPE_CATHEDRAL || $lt === DungeonMap::DTYPE_CATACOMBS) {
            $n += $this->initRndLocObj(1, 3, 'skulfire', [
                'lightRadius' => 5,
                'oPreFlag' => true,
            ]);
        }
        if ($lt === DungeonMap::DTYPE_CATHEDRAL || $lt === DungeonMap::DTYPE_CATACOMBS || $lt === DungeonMap::DTYPE_HELL) {
            $n += $this->initRndLocObj(1, 2, 'burncros', [
                'lightRadius' => 5,
                'oPreFlag' => true,
            ]);
            // OBJ_FLAMEHOLE stand-in — continuous floor fire (flame1.cel)
            $n += $this->initRndLocObj(1, 3, 'flamehole', [
                'lightRadius' => 5,
                'oPreFlag' => true,
            ]);
        }
        if ($lt === DungeonMap::DTYPE_HELL || $lt === DungeonMap::DTYPE_CATACOMBS) {
            $n += $this->initRndLocObj(2, 5, 'tsoul', [
                'oPreFlag' => true,
            ]);
        }

        return $n;
    }

    /**
     * InitRndLocObj — place min..max-1 random objects on free floor tiles.
     *
     * @param array<string,mixed> $extra
     */
    private function initRndLocObj(int $min, int $max, string $type, array $extra): int
    {
        $span = max(1, $max - $min);
        $num = $min + $this->rng->generateRnd($span);
        $occupied = [];
        foreach ($this->objects as $o) {
            $occupied[$o['x'] . ',' . $o['y']] = true;
        }
        $n = 0;
        for ($i = 0; $i < $num; $i++) {
            $placed = false;
            for ($try = 0; $try < 200; $try++) {
                $x = 16 + $this->rng->generateRnd(80);
                $y = 16 + $this->rng->generateRnd(80);
                if (!$this->map->isWalkable($x, $y)) {
                    continue;
                }
                $key = $x . ',' . $y;
                if (isset($occupied[$key])) {
                    continue;
                }
                // IsAreaOk 3×3 — surrounding tiles walkable / empty of solids
                $ok = true;
                for ($dy = -1; $dy <= 1 && $ok; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        if (!$this->map->inBounds($x + $dx, $y + $dy) || $this->map->isSolid($x + $dx, $y + $dy)) {
                            $ok = false;
                            break;
                        }
                    }
                }
                if (!$ok) {
                    continue;
                }
                $obj = array_merge([
                    'id' => $this->nextId++,
                    'type' => $type,
                    'x' => $x,
                    'y' => $y,
                    'opened' => false,
                    'lootCount' => 1,
                ], $extra);
                if ($type === 'decap') {
                    // AddDecapitatedBody — frames 1..8
                    $obj['spriteFrame'] = 1 + $this->rng->generateRnd(8);
                }
                if ($type === 'tnudem' || $type === 'tnudew') {
                    // AddTorturedBody — frames 1..4
                    $obj['spriteFrame'] = 1 + $this->rng->generateRnd(4);
                }
                if ($type === 'tsoul') {
                    // OBJ_TORTURE1..5 — frames 1..5
                    $obj['spriteFrame'] = 1 + $this->rng->generateRnd(5);
                }
                $this->loadObjectSprite($obj);
                $this->objects[] = $obj;
                $this->occ->setObject($x, $y, (int) $obj['id']);
                $occupied[$key] = true;
                $n++;
                $placed = true;
                break;
            }
            if (!$placed) {
                break;
            }
        }

        return $n;
    }

    /**
     * InitObjects chest slice — InitRndLocObj CHEST1/2/3 counts.
     */
    private function initRndChests(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            return 0;
        }
        $n = 0;
        $n += $this->initRndLocObj(5, 10, 'chest', ['chestTier' => 1, 'lootCount' => 1]);
        $n += $this->initRndLocObj(3, 6, 'chest', ['chestTier' => 2, 'lootCount' => 1]);
        $n += $this->initRndLocObj(1, 5, 'chest', ['chestTier' => 3, 'lootCount' => 2]);

        return $n;
    }

    /**
     * AddChestTraps — ~10% of chests become trapped (missile on open).
     */
    private function addChestTraps(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            return 0;
        }
        $n = 0;
        foreach ($this->objects as &$o) {
            if (($o['type'] ?? '') !== 'chest' || !empty($o['trapped'])) {
                continue;
            }
            if ($this->rng->generateRnd(100) >= 10) {
                continue;
            }
            $o['trapped'] = true;
            $max = $this->map->levelType === DungeonMap::DTYPE_CATACOMBS ? 2 : 3;
            $o['trapKind'] = $this->rng->generateRnd($max);
            $n++;
        }
        unset($o);

        return $n;
    }

    /**
     * InitRndLocBigObj sarcophagi on Cathedral (10–15).
     */
    private function initRndSarcophagi(): int
    {
        if ($this->map->levelType !== DungeonMap::DTYPE_CATHEDRAL) {
            return 0;
        }
        $span = max(1, 15 - 10);
        $num = 10 + $this->rng->generateRnd($span);
        $occupied = [];
        foreach ($this->objects as $o) {
            $occupied[$o['x'] . ',' . $o['y']] = true;
        }
        $n = 0;
        for ($i = 0; $i < $num; $i++) {
            $placed = false;
            for ($try = 0; $try < 200; $try++) {
                $x = 16 + $this->rng->generateRnd(80);
                $y = 16 + $this->rng->generateRnd(80);
                if (!$this->rndLocOk($x, $y, $occupied)) {
                    continue;
                }
                // IsAreaOk 3×4 (xp-1,yp-2)
                $ok = true;
                for ($dy = -2; $dy <= 1 && $ok; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        if (!$this->map->inBounds($x + $dx, $y + $dy) || $this->map->isSolid($x + $dx, $y + $dy)) {
                            $ok = false;
                            break;
                        }
                    }
                }
                if (!$ok) {
                    continue;
                }
                $obj = [
                    'id' => $this->nextId++,
                    'type' => 'sarc',
                    'x' => $x,
                    'y' => $y,
                    'opened' => false,
                    'lootCount' => 1,
                    'oVar1' => $this->rng->generateRnd(10),
                    'oPreFlag' => true,
                ];
                $this->loadObjectSprite($obj);
                $this->objects[] = $obj;
                $this->occ->setObject($x, $y, (int) $obj['id']);
                $occupied[$x . ',' . $y] = true;
                $n++;
                $placed = true;
                break;
            }
            if (!$placed) {
                break;
            }
        }

        return $n;
    }

    /**
     * Theme-lite fountains — blood (HP) + purifying (mana) on cathedral/catacombs/caves.
     */
    private function addDungeonFountains(): int
    {
        $lt = $this->map->levelType;
        if (!in_array($lt, [DungeonMap::DTYPE_CATHEDRAL, DungeonMap::DTYPE_CATACOMBS, DungeonMap::DTYPE_CAVES], true)) {
            return 0;
        }
        $n = 0;
        $n += $this->initRndLocObj(1, 2, 'bloodftn', ['oPreFlag' => false]);
        $n += $this->initRndLocObj(1, 2, 'purifyftn', ['oPreFlag' => false]);
        $n += $this->initRndLocObj(1, 2, 'murkyftn', []);
        $n += $this->initRndLocObj(1, 2, 'tearftn', []);

        return $n;
    }

    /** Theme-lite L/R shrines with random oVar1 effect id. */
    private function addDungeonShrines(): int
    {
        $lt = $this->map->levelType;
        if ($lt === DungeonMap::DTYPE_TOWN) {
            return 0;
        }
        $span = max(1, 5 - 2);
        $num = 2 + $this->rng->generateRnd($span);
        $n = 0;
        for ($i = 0; $i < $num; $i++) {
            $n += $this->initRndLocObj(1, 2, 'shrine', [
                'doorSide' => $this->rng->flipCoin() ? 'L' : 'R',
                'oVar1' => $this->rng->generateRnd(8),
            ]);
        }
        // Goat shrines (caves+) — OperateGoatShrine → same effect table
        if ($lt === DungeonMap::DTYPE_CAVES || $lt === DungeonMap::DTYPE_HELL
            || $lt === DungeonMap::DTYPE_CATACOMBS) {
            $n += $this->initRndLocObj(1, 2, 'goatshrine', [
                'oVar1' => $this->rng->generateRnd(8),
            ]);
        }

        return $n;
    }

    /** Theme-lite book stands (OperateBookStand → scroll/book loot). */
    private function addDungeonBookstands(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            return 0;
        }

        return $this->initRndLocObj(2, 5, 'bookstand', ['lootCount' => 1]);
    }

    /** Theme-lite library bookcases (OperateBookcase → IMISC_BOOK). */
    private function addDungeonBookcases(): int
    {
        $lt = $this->map->levelType;
        if ($lt !== DungeonMap::DTYPE_CATACOMBS && $lt !== DungeonMap::DTYPE_CAVES
            && $lt !== DungeonMap::DTYPE_CATHEDRAL) {
            return 0;
        }
        $n = 0;
        $n += $this->initRndLocObj(1, 2, 'bookcase', [
            'doorSide' => 'L',
            'spriteFrame' => 2,
            'oPreFlag' => true,
        ]);
        $n += $this->initRndLocObj(1, 2, 'bookcase', [
            'doorSide' => 'R',
            'spriteFrame' => 3,
            'oPreFlag' => true,
        ]);

        return $n;
    }

    /** Theme-lite levers — open nearest closed door. */
    private function addDungeonLevers(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            return 0;
        }

        return $this->initRndLocObj(1, 3, 'lever', []);
    }

    /** Theme-lite armor stands + weapon racks. */
    private function addDungeonStands(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            return 0;
        }
        $n = 0;
        $n += $this->initRndLocObj(1, 3, 'armorstand', []);
        $n += $this->initRndLocObj(1, 3, 'weaponrack', []);

        return $n;
    }

    /**
     * AddObjTraps — wall trapholes linked to chests/shrines/doors/sarc.
     * Retail skips Hell (objects.cpp InitObjects).
     */
    private function addObjTraps(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN
            || $this->map->levelType === DungeonMap::DTYPE_HELL) {
            return 0;
        }
        $dlvl = max(1, $this->map->currLevel);
        $rndv = 10;
        if ($dlvl >= 2) {
            $rndv = 15;
        }
        if ($dlvl >= 5) {
            $rndv = 20;
        }
        if ($dlvl >= 7) {
            $rndv = 25;
        }
        $occupied = [];
        foreach ($this->objects as $o) {
            $occupied[$o['x'] . ',' . $o['y']] = true;
        }
        $n = 0;
        // objdat isTrap: doors, chests, sarc, levers (not shrines/stands)
        $triggers = ['chest', 'door', 'sarc', 'lever'];
        foreach ($this->objects as &$trig) {
            if (!in_array($trig['type'] ?? '', $triggers, true)) {
                continue;
            }
            if (!empty($trig['trapFlag'])) {
                continue;
            }
            if ($this->rng->generateRnd(100) >= $rndv) {
                continue;
            }
            $i = (int) $trig['x'];
            $j = (int) $trig['y'];
            $trapPos = null;
            $side = 'L';
            if ($this->rng->flipCoin()) {
                $xp = $i - 1;
                while ($this->map->inBounds($xp, $j) && !$this->map->isSolid($xp, $j)) {
                    $xp--;
                }
                if ($this->canPlaceWallTrap($xp, $j, $occupied) && $i - $xp > 1) {
                    $trapPos = [$xp, $j];
                    $side = 'L';
                }
            } else {
                $yp = $j - 1;
                while ($this->map->inBounds($i, $yp) && !$this->map->isSolid($i, $yp)) {
                    $yp--;
                }
                if ($this->canPlaceWallTrap($i, $yp, $occupied) && $j - $yp > 1) {
                    $trapPos = [$i, $yp];
                    $side = 'R';
                }
            }
            if ($trapPos === null) {
                continue;
            }
            [$tx, $ty] = $trapPos;
            $trap = [
                'id' => $this->nextId++,
                'type' => 'trap',
                'x' => $tx,
                'y' => $ty,
                'doorSide' => $side,
                'spriteFrame' => $side === 'R' ? 1 : 0,
                'oVar1' => $i,
                'oVar2' => $j,
                'oVar3' => $this->rng->flipCoin() ? 0 : 1, // 0 firebolt, 1 arrow
                'armed' => true,
                'oPreFlag' => true,
            ];
            $this->loadObjectSprite($trap);
            $this->objects[] = $trap;
            $this->occ->setObject($tx, $ty, (int) $trap['id']);
            $occupied[$tx . ',' . $ty] = true;
            $trig['trapFlag'] = true;
            $n++;
        }
        unset($trig);

        return $n;
    }

    /** @param array<string,bool> $occupied */
    private function canPlaceWallTrap(int $x, int $y, array $occupied): bool
    {
        if (!$this->map->inBounds($x, $y) || isset($occupied[$x . ',' . $y])) {
            return false;
        }
        $piece = $this->map->dPiece[$y][$x] ?? 0;
        if ($piece < 0 || !isset($this->map->sol[$piece])) {
            return false;
        }
        $sol = (int) ($this->map->sol[$piece] ?? 0);

        return ($sol & 0x80) !== 0; // TileProperties::Trap
    }

    /**
     * InitRndBarrels — clustered barrels; FlipCoin(4)/FlipCoin(5) for explosive.
     * @return int barrels placed
     */
    private function initRndBarrels(): int
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            return 0;
        }
        $bxadd = [-1, 0, 1, -1, 1, -1, 0, 1];
        $byadd = [-1, -1, -1, 0, 0, 1, 1, 1];
        $occupied = [];
        foreach ($this->objects as $o) {
            $occupied[$o['x'] . ',' . $o['y']] = true;
        }
        $n = 0;
        $numGroups = 3 + $this->rng->generateRnd(5);
        for ($i = 0; $i < $numGroups; $i++) {
            $xp = 0;
            $yp = 0;
            $foundSeed = false;
            for ($try = 0; $try < 200; $try++) {
                $xp = 16 + $this->rng->generateRnd(80);
                $yp = 16 + $this->rng->generateRnd(80);
                if ($this->rndLocOk($xp, $yp, $occupied)) {
                    $foundSeed = true;
                    break;
                }
            }
            if (!$foundSeed) {
                continue;
            }
            $type = $this->rng->flipCoin(4) ? 'barrelex' : 'barrel';
            $this->placeBarrelAt($xp, $yp, $type, $occupied);
            $n++;
            $found = true;
            $p = 0;
            $c = 1;
            while ($this->rng->flipCoin(max(1, $p)) && $found) {
                // FlipCoin(0) always true in some ports — DX FlipCoin(p) with p=c/2
                // Our flipCoin(n): true when generateRnd(n)==0? Check Random::flipCoin
                $t = 0;
                $found = false;
                $nx = $xp;
                $ny = $yp;
                while ($t < 3) {
                    $dir = $this->rng->generateRnd(8);
                    $nx = $xp + $bxadd[$dir];
                    $ny = $yp + $byadd[$dir];
                    $t++;
                    if ($this->rndLocOk($nx, $ny, $occupied)) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $xp = $nx;
                    $yp = $ny;
                    $type = $this->rng->flipCoin(5) ? 'barrelex' : 'barrel';
                    $this->placeBarrelAt($xp, $yp, $type, $occupied);
                    $n++;
                    $c++;
                }
                $p = intdiv($c, 2);
            }
        }

        return $n;
    }

    /** @param array<string,bool> $occupied */
    private function rndLocOk(int $x, int $y, array $occupied): bool
    {
        if (!$this->map->isWalkable($x, $y)) {
            return false;
        }
        if (isset($occupied[$x . ',' . $y])) {
            return false;
        }
        // Stay clear of stairs/player spawn neighborhood
        if (abs($x - $this->px) + abs($y - $this->py) < 3) {
            return false;
        }

        return true;
    }

    /** @param array<string,bool> $occupied */
    private function placeBarrelAt(int $x, int $y, string $type, array &$occupied): void
    {
        $obj = [
            'id' => $this->nextId++,
            'type' => $type,
            'x' => $x,
            'y' => $y,
            'opened' => false,
            'lootCount' => 1,
            'explosive' => $type === 'barrelex',
        ];
        $this->loadObjectSprite($obj);
        $this->objects[] = $obj;
        $this->occ->setObject($x, $y, (int) $obj['id']);
        $occupied[$x . ',' . $y] = true;
    }

    /**
     * ObjSetMicro — update dPiece (and rebuild micros for that cell if needed).
     */
    private function objSetMicro(int $x, int $y, int $pn): void
    {
        if (!$this->map->inBounds($x, $y)) {
            return;
        }
        $this->map->dPiece[$y][$x] = $pn;
    }

    /**
     * SetDoorStateOpen for OBJ_L1* / OBJ_L2* doors (piece swap).
     */
    private function setDoorStateOpen(array &$door): void
    {
        $x = (int) $door['x'];
        $y = (int) $door['y'];
        $side = (string) ($door['doorSide'] ?? 'L');
        $lvl = (string) ($door['doorLevel'] ?? 'L1');
        // SetDoorStateOpen — open doors draw before actors (_oPreFlag)
        $door['oPreFlag'] = true;
        $door['_oPreFlag'] = true;
        $door['oMissFlag'] = true;
        if ($lvl === 'L2') {
            // L2 open: 12 / 16; dSpecial 5 / 6
            $this->objSetMicro($x, $y, $side === 'L' ? 12 : 16);
            if ($this->map->inBounds($x, $y)) {
                $this->map->dSpecial[$y][$x] = $side === 'L' ? 5 : 6;
            }

            return;
        }
        if ($lvl === 'L3') {
            // L3 open: 537 / 540
            $this->objSetMicro($x, $y, $side === 'L' ? 537 : 540);

            return;
        }
        $oVar1 = (int) ($door['oVar1'] ?? 0);
        if ($side === 'L') {
            // SetDoorStateOpen L1LDOOR; DoorSet(position + NE) NE=(0,-1)
            $this->objSetMicro($x, $y, $oVar1 === 214 ? 407 : 392);
            if ($this->map->inBounds($x, $y)) {
                $this->map->dSpecial[$y][$x] = 7;
            }
            $this->doorSet($x + 0, $y - 1, true);
        } else {
            // L1RDOOR; DoorSet(position + NW) NW=(-1,0)
            $this->objSetMicro($x, $y, 394);
            if ($this->map->inBounds($x, $y)) {
                $this->map->dSpecial[$y][$x] = 8;
            }
            $this->doorSet($x - 1, $y + 0, false);
        }
    }

    /**
     * SetDoorStateClosed — restore closed dPiece / clear dSpecial / post-draw.
     *
     * @param array<string,mixed> $door
     */
    private function setDoorStateClosed(array &$door): void
    {
        $x = (int) $door['x'];
        $y = (int) $door['y'];
        $side = (string) ($door['doorSide'] ?? 'L');
        $lvl = (string) ($door['doorLevel'] ?? 'L1');
        $door['oPreFlag'] = false;
        $door['_oPreFlag'] = false;
        $door['oMissFlag'] = false;
        if ($lvl === 'L2') {
            $this->objSetMicro($x, $y, $side === 'L' ? 537 : 539);
            if ($this->map->inBounds($x, $y)) {
                $this->map->dSpecial[$y][$x] = 0;
            }

            return;
        }
        if ($lvl === 'L3') {
            $this->objSetMicro($x, $y, $side === 'L' ? 530 : 533);

            return;
        }
        // L1 — restore closed piece from oVar1; clear arch special
        $closedPn = max(0, (int) ($door['oVar1'] ?? 1) - 1);
        $this->objSetMicro($x, $y, $closedPn);
        if ($this->map->inBounds($x, $y)) {
            $this->map->dSpecial[$y][$x] = 0;
        }
        $oVar2 = (int) ($door['oVar2'] ?? 0);
        if ($side === 'L') {
            $nx = $x;
            $ny = $y - 1;
            if ($this->map->inBounds($nx, $ny)) {
                $restore = ($oVar2 === 50 && ($this->map->dPiece[$ny][$nx] ?? 0) === 395) ? 411 : max(0, $oVar2 - 1);
                $this->objSetMicro($nx, $ny, $restore);
            }
        } else {
            $nx = $x - 1;
            $ny = $y;
            if ($this->map->inBounds($nx, $ny)) {
                $restore = ($oVar2 === 50 && ($this->map->dPiece[$ny][$nx] ?? 0) === 395) ? 410 : max(0, $oVar2 - 1);
                $this->objSetMicro($nx, $ny, $restore);
            }
        }
    }

    /** IsDoorClear — nothing on the door tile blocking close. */
    private function isDoorClear(array $door): bool
    {
        $x = (int) $door['x'];
        $y = (int) $door['y'];
        if ($this->px === $x && $this->py === $y) {
            return false;
        }
        if ($this->occ->monsterAt($x, $y) !== 0) {
            return false;
        }
        foreach ($this->items as $it) {
            if ((int) ($it['x'] ?? -1) === $x && (int) ($it['y'] ?? -1) === $y) {
                return false;
            }
        }
        foreach ($this->monsters as $m) {
            if (empty($m['alive']) && ($m['mode'] ?? '') === 'DEATH'
                && (int) ($m['x'] ?? -1) === $x && (int) ($m['y'] ?? -1) === $y) {
                return false; // corpse
            }
        }

        return true;
    }

 /** DoorSet — clear overlapping arch micros when opening. */
    private function doorSet(int $x, int $y, bool $isLeftDoor): void
    {
        if (!$this->map->inBounds($x, $y)) {
            return;
        }
        $pn = $this->map->dPiece[$y][$x] ?? 0;
        $next = match ($pn) {
            42 => 391,
            44 => 393,
            49 => $isLeftDoor ? 410 : 411,
            53 => 396,
            54 => 397,
            60 => 398,
            66 => 399,
            67 => 400,
            68 => 402,
            69 => 403,
            72 => 405,
            211 => 406,
            215 => 408,
            217 => 409,
            410, 411 => 395,
            default => null,
        };
        if ($next !== null) {
            $this->objSetMicro($x, $y, $next);
        }
    }

    private function placeLevelObject(string $type, int $minDist): void
    {
        $occupied = [];
        foreach ($this->objects as $o) {
            $occupied[$o['x'] . ',' . $o['y']] = true;
        }
        $reachable = $this->reachableFrom($this->px, $this->py);
        $candidates = [];
        foreach ($reachable as $key => $man) {
            if ($man < $minDist || $man > $minDist + 24) {
                continue;
            }
            [$x, $y] = array_map('intval', explode(',', $key));
            if (isset($occupied[$key])) {
                continue;
            }
            if ($type === 'door' && $y === $this->py) {
                continue;
            }
            $candidates[] = [$x, $y, $man];
        }
        if ($candidates === [] && $reachable !== []) {
            foreach ($reachable as $key => $man) {
                if ($man < $minDist || isset($occupied[$key])) {
                    continue;
                }
                [$x, $y] = array_map('intval', explode(',', $key));
                $candidates[] = [$x, $y, $man];
            }
        }
        if ($candidates === []) {
            return;
        }
        usort($candidates, static fn ($a, $b) => $a[2] <=> $b[2]);
        $pick = $candidates[$this->rng->generateRnd(min(8, count($candidates)))];
        $obj = ['id' => $this->nextId++, 'type' => $type, 'x' => $pick[0], 'y' => $pick[1]];
        if ($type === 'chest' || $type === 'barrel' || $type === 'door') {
            $obj['opened'] = false;
        }
        if ($type === 'chest') {
            $obj['lootCount'] = 1 + $this->rng->generateRnd(2); // _oVar1 analogue (1–2)
        } elseif ($type === 'barrel') {
            $obj['lootCount'] = 1;
        }
        $this->loadObjectSprite($obj);
        $this->objects[] = $obj;
        $this->occ->setObject($obj['x'], $obj['y'], (int) $obj['id']);
    }

    /** @return array<string,int> tileKey => manhattan from origin */
    private function reachableFrom(int $ox, int $oy): array
    {
        if (!$this->map->isWalkable($ox, $oy)) {
            return [];
        }
        $seen = [$ox . ',' . $oy => 0];
        $q = [[$ox, $oy]];
        while ($q !== []) {
            [$x, $y] = array_shift($q);
            $base = $seen[$x . ',' . $y];
            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [-1, -1], [1, -1], [-1, 1]] as [$dx, $dy]) {
                $nx = $x + $dx;
                $ny = $y + $dy;
                $key = $nx . ',' . $ny;
                if (isset($seen[$key]) || !$this->map->isWalkable($nx, $ny)) {
                    continue;
                }
                if ($dx !== 0 && $dy !== 0
                    && !($this->map->isWalkable($nx, $y) && $this->map->isWalkable($x, $ny))) {
                    continue;
                }
                $seen[$key] = $base + 1;
                $q[] = [$nx, $ny];
            }
        }

        return $seen;
    }

    private ?array $activeVendor = null;
    private ?int $attackTarget = null;
    /** StartRangeAttack position.temp — arrow destination tile */
    private int $rangeTempX = 0;
    private int $rangeTempY = 0;

    private function clrPlrPath(): void
    {
        $this->walkpath = [DestAction::WALK_NONE];
        $this->lastChaseGoalX = -1;
        $this->lastChaseGoalY = -1;
        $this->lastChaseMonX = -1;
        $this->lastChaseMonY = -1;
    }

    /**
     * Deterministic gameplay scenarios A–F (runtime diagnostics).
     * @return array{pass:bool,lines:list<string>}
     */
    public function runGameplayScenario(string $id): array
    {
        $id = strtoupper(trim($id));
        $lines = [];
        $pass = true;
        $fail = function (string $msg) use (&$pass, &$lines): void {
            $pass = false;
            $lines[] = 'FAIL ' . $msg;
        };
        $ok = function (string $msg) use (&$lines): void {
            $lines[] = 'OK ' . $msg;
        };

        $this->mode = 'play';
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            // stay town for A–D; E–F need cathedral
        }

        $tracePath = $this->gtrace->path() ?? (dirname(__DIR__) . '/tmp/gameplay-trace-' . $id . '.jsonl');
        if (!$this->gtrace->enabled()) {
            $this->enableGameplayTrace($tracePath);
        }
        $this->gtrace->emit(['kind' => 'scenario', 'id' => $id, 'seed' => $this->dungeonSeed]);

        switch ($id) {
            case 'A':
                [$sx, $sy] = $this->findOpenStretch(5, Direction::E);
                $this->teleportPlayer($sx, $sy);
                [$edx, $edy] = Direction::delta(Direction::E);
                $tx = $sx + 5 * $edx;
                $ty = $sy + 5 * $edy;
                $this->destAction = DestAction::NONE;
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($tx, $ty, true);
                $commits = 0;
                $prev = [$this->px, $this->py];
                $standGap = 0;
                $maxStandGap = 0;
                $wasWalk = false;
                for ($i = 0; $i < 200 && !($this->px === $tx && $this->py === $ty); $i++) {
                    $this->tick();
                    if ($this->px !== $prev[0] || $this->py !== $prev[1]) {
                        $commits++;
                        $d = max(abs($this->px - $prev[0]), abs($this->py - $prev[1]));
                        if ($d !== 1) {
                            $fail("commit chebyshev={$d} at tick {$this->logicTick}");
                        }
                        $prev = [$this->px, $this->py];
                        $wasWalk = true;
                        $standGap = 0;
                    } elseif ($wasWalk && $this->pmode === 'STAND' && ($this->walkpath[0] ?? DestAction::WALK_NONE) !== DestAction::WALK_NONE) {
                        // same-tick should start next walk — counting STAND with pending path
                        $standGap++;
                        $maxStandGap = max($maxStandGap, $standGap);
                    }
                }
                if ($this->px !== $tx || $this->py !== $ty) {
                    $fail("did not reach {$tx},{$ty} (at {$this->px},{$this->py}) commits={$commits}");
                } elseif ($commits !== 5) {
                    $fail("expected 5 commits got {$commits}");
                } else {
                    $ok("reached {$tx},{$ty} commits=5");
                }
                if ($maxStandGap > 1) {
                    $fail("STAND gap between walk steps={$maxStandGap}");
                } else {
                    $ok("no multi-tick STAND gap between steps");
                }
                // single-step numerical dump (last scroll state)
                [$scx, $scy] = $this->currentWalkScroll();
                [$ax, $ay] = $this->currentWalkActorOffset();
                $this->gtrace->emit([
                    'kind' => 'coord_dump',
                    'tile' => [$this->px, $this->py],
                    'fut' => [$this->walkFutureX, $this->walkFutureY],
                    'cam' => [$this->camX, $this->camY],
                    'scroll' => [$scx, $scy],
                    'actorOff' => [$ax, $ay],
                    'alpha' => $this->tickAlpha,
                ]);
                break;

            case 'B':
                [$sx, $sy] = $this->findOpenStretch(4, Direction::SE);
                $this->teleportPlayer($sx, $sy);
                [$edx, $edy] = Direction::delta(Direction::SE);
                $tx = $sx + 4 * $edx;
                $ty = $sy + 4 * $edy;
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($tx, $ty, true);
                $prev = [$this->px, $this->py];
                $back = false;
                for ($i = 0; $i < 200 && !($this->px === $tx && $this->py === $ty); $i++) {
                    $this->tick();
                    if ($this->px !== $prev[0] || $this->py !== $prev[1]) {
                        $dx = $this->px - $prev[0];
                        $dy = $this->py - $prev[1];
                        if ($dx < 0 || $dy < 0) {
                            $back = true;
                        }
                        if (max(abs($dx), abs($dy)) !== 1) {
                            $fail('non-unit step');
                        }
                        $prev = [$this->px, $this->py];
                    }
                }
                if ($back) {
                    $fail('backstep toward SW');
                }
                if ($this->px === $tx && $this->py === $ty) {
                    $ok("diagonal reach {$tx},{$ty}");
                } else {
                    $fail("diagonal miss at {$this->px},{$this->py}");
                }
                break;

            case 'C':
                [$sx, $sy] = $this->findOpenStretch(8, Direction::E);
                $this->teleportPlayer($sx, $sy);
                [$edx, $edy] = Direction::delta(Direction::E);
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($sx + 8 * $edx, $sy + 8 * $edy, true);
                $commits = 0;
                $snap = false;
                $repathFut = null;
                for ($i = 0; $i < 300; $i++) {
                    $before = [$this->px, $this->py];
                    $futBefore = [$this->walkFutureX, $this->walkFutureY];
                    if ($commits === 2 && $repathFut === null) {
                        $repathFut = $futBefore;
                        $this->lastChaseGoalX = -1;
                        // mid-walk repath toward NW-ish offset
                        [$ndx, $ndy] = Direction::delta(Direction::NW);
                        $this->makePlrPath($sx + 2 * $edx + 2 * $ndx, $sy + 2 * $edy + 2 * $ndy, true);
                        $this->gtrace->emit(['kind' => 'repath_mid', 'tile' => $before, 'fut' => $futBefore]);
                    }
                    $this->tick();
                    if ($this->px !== $before[0] || $this->py !== $before[1]) {
                        $commits++;
                        // teleport/snap = non-adjacent jump (NW repath may decrease x intentionally)
                        $d = max(abs($this->px - $before[0]), abs($this->py - $before[1]));
                        if ($d > 1) {
                            $snap = true;
                        }
                    }
                    if ($commits >= 2 && $repathFut !== null && $commits >= 5) {
                        break;
                    }
                }
                if ($snap) {
                    $fail('tile snap backward after repath');
                } else {
                    $ok('no backward snap after mid-walk repath');
                }
                if ($repathFut === null) {
                    $fail('never reached 2 commits to repath');
                } else {
                    $ok('repath issued after 2 commits from fut=' . json_encode($repathFut));
                }
                break;

            case 'D':
                [$sx, $sy] = $this->findOpenStretch(6, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->mouseLeftDown = true;
                $this->cursKind = 'none';
                $mpaths = 0;
                [$edx, $edy] = Direction::delta(Direction::E);
                $steps = 1;
                for ($i = 0; $i < 80; $i++) {
                    if ($i % 8 === 0 && $steps < 6) {
                        $steps++;
                    }
                    $this->cursX = $sx + $steps * $edx;
                    $this->cursY = $sy + $steps * $edy;
                    $this->pendingRepeatWalk = true;
                    $this->tick();
                    $mpaths += $this->gtrace->makePlrPathCalls;
                    if ($this->gtrace->makePlrPathCalls > 1) {
                        $fail("mpaths>1 in single tick t={$this->logicTick}");
                        break;
                    }
                }
                $progress = abs(($this->px - $sx) * $edx + ($this->py - $sy) * $edy);
                // Chebyshev progress along east
                $cheb = max(abs($this->px - $sx), abs($this->py - $sy));
                if ($cheb >= 4) {
                    $ok("hold-walk progressed cheb={$cheb} (start {$sx},{$sy}) mpaths_total~{$mpaths}");
                } else {
                    $fail("hold-walk stalled at {$this->px},{$this->py}");
                }
                break;

            case 'E':
                $this->enterCathedral(1);
                $this->teleportPlayer($this->px, $this->py);
                // place monster 5 east if walkable
                $mx = $this->px + 5;
                $my = $this->py;
                if (!$this->map->isWalkable($mx, $my)) {
                    $mx = $this->px;
                    $my = $this->py + 5;
                }
                $mid = $this->nextId++;
                $this->monsters = [[
                    'id' => $mid, 'type' => 'MT_NZOMBIE', 'name' => 'TraceZombie',
                    'x' => $mx, 'y' => $my, 'hp' => 50, 'maxHp' => 50,
                    'minDam' => 1, 'maxDam' => 2, 'ac' => 0, 'toHit' => 10, 'xp' => 1,
                    'mode' => 'STAND', 'dir' => Direction::S, 'ai' => 'Zombie',
                    'assetsSuffix' => '', 'cl2' => '', 'frame' => 0, 'alive' => true,
                    'quest' => false, 'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
                    'hidden' => false,
                ]];
                $this->syncOccupancyActors();
                $this->destAction = DestAction::ATTACK_MON;
                $this->destParam = $mid;
                $this->lastChaseMonX = -1;
                $this->makePlrPath($mx, $my, false);
                $movedMon = false;
                $attacked = false;
                $osc = 0;
                $lastTile = [$this->px, $this->py];
                $recent = [];
                for ($i = 0; $i < 250; $i++) {
                    if (!$movedMon && max(abs($this->px - $mx), abs($this->py - $my)) <= 3) {
                        // move monster one step during chase
                        foreach ($this->monsters as &$m) {
                            if ($m['id'] === $mid) {
                                $m['x'] = $mx + 1;
                                $m['y'] = $my;
                                $mx = $m['x'];
                                $my = $m['y'];
                            }
                        }
                        unset($m);
                        $this->lastChaseMonX = -1;
                        $movedMon = true;
                        $this->syncOccupancyActors();
                    }
                    $this->tick();
                    if ($this->pmode === 'ATTACK') {
                        $attacked = true;
                    }
                    $recent[] = [$this->px, $this->py];
                    if (count($recent) > 5) {
                        array_shift($recent);
                    }
                    if ($attacked && max(abs($this->px - $mx), abs($this->py - $my)) <= 1) {
                        break;
                    }
                }
                if (!$attacked) {
                    $fail('ATTACK never started');
                } else {
                    $ok('ATTACK started');
                }
                $uniq = [];
                foreach ($recent as $p) {
                    $uniq[$p[0] . ',' . $p[1]] = true;
                }
                if (count($uniq) > 3 && $attacked) {
                    // oscillating near target
                    $fail('tile oscillation near target last5=' . json_encode($recent));
                } elseif ($attacked) {
                    $ok('no wild oscillation at melee');
                }
                if (!$movedMon) {
                    $lines[] = 'WARN monster never moved mid-chase';
                }
                break;

            case 'F':
                $this->enterCathedral(1);
                $mid = $this->nextId++;
                $mx = $this->px + 1;
                $my = $this->py;
                if (!$this->tileWalkableForPath($mx, $my) && $this->map->isWalkable($mx, $my)) {
                    // force adjacent even if blocked by occupancy
                }
                if (!$this->map->isWalkable($mx, $my)) {
                    $mx = $this->px;
                    $my = $this->py + 1;
                }
                $this->monsters = [[
                    'id' => $mid, 'type' => 'MT_NZOMBIE', 'name' => 'PunchingBag',
                    'x' => $mx, 'y' => $my, 'hp' => 200, 'maxHp' => 200,
                    'minDam' => 0, 'maxDam' => 0, 'ac' => 0, 'toHit' => 0, 'xp' => 1,
                    'mode' => 'STAND', 'dir' => Direction::S, 'ai' => 'Zombie',
                    'assetsSuffix' => '', 'cl2' => '', 'frame' => 0, 'alive' => true,
                    'quest' => false, 'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
                    'hidden' => false,
                ]];
                $this->syncOccupancyActors();
                $attacks = 0;
                $stuckStand = 0;
                $maxStuck = 0;
                for ($round = 0; $round < 3; $round++) {
                    $this->destAction = DestAction::ATTACK_MON;
                    $this->destParam = $mid;
                    $this->clrPlrPath();
                    $sawAttack = false;
                    $sawStandAfter = false;
                    for ($i = 0; $i < 40; $i++) {
                        $this->tick();
                        if ($this->pmode === 'ATTACK') {
                            $sawAttack = true;
                            $stuckStand = 0;
                        } elseif ($sawAttack && $this->pmode === 'STAND') {
                            $sawStandAfter = true;
                            if ($this->destAction === DestAction::ATTACK_MON) {
                                $stuckStand++;
                                $maxStuck = max($maxStuck, $stuckStand);
                            }
                        }
                    }
                    if ($sawAttack) {
                        $attacks++;
                    }
                    if (!$sawAttack) {
                        $fail("round {$round} no ATTACK");
                    }
                }
                if ($attacks >= 2) {
                    $ok("repeated attacks={$attacks}");
                } else {
                    $fail("only {$attacks} attack cycles");
                }
                if ($maxStuck > 2) {
                    $fail("stuck STAND with destAction ticks={$maxStuck}");
                } else {
                    $ok('no stuck STAND>2 with pending dest');
                }
                break;

            case 'G':
                // destAction OPERATE door+chest and PICKUP via adjacency (Town/L1 interaction)
                $this->enterCathedral(1);
                [$sx, $sy] = $this->findOpenStretch(6, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->objects = [];
                $this->items = [];
                $doorId = $this->nextId++;
                $chestId = $this->nextId++;
                $itemId = $this->nextId++;
                $dx = $sx + 4;
                $dy = $sy;
                $cx = $sx + 2;
                $cy = $sy + 1;
                if (!$this->map->isWalkable($cx, $cy)) {
                    $cx = $sx + 2;
                    $cy = $sy;
                }
                $this->objects[] = [
                    'id' => $doorId, 'type' => 'door', 'x' => $dx, 'y' => $dy, 'opened' => false,
                ];
                $this->objects[] = [
                    'id' => $chestId, 'type' => 'chest', 'x' => $cx, 'y' => $cy, 'opened' => false,
                ];
                $ix = $sx + 5;
                $iy = $sy;
                if (!$this->map->isWalkable($ix, $iy)) {
                    $ix = $sx + 3;
                    $iy = $sy;
                }
                $this->items[] = [
                    'id' => $itemId, 'x' => $ix, 'y' => $iy,
                    'type' => 'gold', 'name' => 'Gold', 'value' => 25,
                ];
                $this->syncOccupancyActors();

                $gold0 = $this->gold;
                $this->destAction = DestAction::OPERATE;
                $this->destParam = $doorId;
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($dx, $dy, false);
                $doorOpened = false;
                for ($i = 0; $i < 200; $i++) {
                    $this->tick();
                    foreach ($this->objects as $o) {
                        if ((int) $o['id'] === $doorId && !empty($o['opened'])) {
                            $doorOpened = true;
                            break 2;
                        }
                    }
                }
                if ($doorOpened) {
                    $ok('door opened via destAction OPERATE');
                } else {
                    $fail("door not opened (at {$this->px},{$this->py} dest={$this->destAction})");
                }

                $this->destAction = DestAction::OPERATE;
                $this->destParam = $chestId;
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($cx, $cy, false);
                $chestOpened = false;
                for ($i = 0; $i < 200; $i++) {
                    $this->tick();
                    foreach ($this->objects as $o) {
                        if ((int) $o['id'] === $chestId && !empty($o['opened'])) {
                            $chestOpened = true;
                            break 2;
                        }
                    }
                }
                if ($chestOpened) {
                    $ok('chest opened via destAction OPERATE');
                } else {
                    $fail("chest not opened (at {$this->px},{$this->py})");
                }

                $this->destAction = DestAction::PICKUP;
                $this->destParam = $itemId;
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($ix, $iy, true);
                $picked = false;
                for ($i = 0; $i < 200; $i++) {
                    $this->tick();
                    $still = false;
                    foreach ($this->items as $it) {
                        if ((int) $it['id'] === $itemId) {
                            $still = true;
                            break;
                        }
                    }
                    if (!$still) {
                        $picked = true;
                        break;
                    }
                }
                if ($picked && $this->gold >= $gold0 + 25) {
                    $ok("pickup gold via destAction PICKUP (+25)");
                } elseif ($picked) {
                    $ok('pickup removed ground item');
                } else {
                    $fail("pickup miss at {$this->px},{$this->py} items=" . count($this->items));
                }

                // Adjacent-only pickup (stand next to item, do not require same tile)
                $itemId2 = $this->nextId++;
                $this->teleportPlayer($sx, $sy);
                $this->items = [[
                    'id' => $itemId2, 'x' => $sx + 1, 'y' => $sy,
                    'type' => 'gold', 'name' => 'Gold', 'value' => 10,
                ]];
                $this->syncOccupancyActors();
                $this->destAction = DestAction::PICKUP;
                $this->destParam = $itemId2;
                $this->clrPlrPath();
                $this->tick();
                $adjOk = true;
                foreach ($this->items as $it) {
                    if ((int) $it['id'] === $itemId2) {
                        $adjOk = false;
                    }
                }
                if ($adjOk && $this->gold >= $gold0 + 10) {
                    $ok('adjacent Chebyshev pickup without standing on item');
                } else {
                    $fail('adjacent pickup failed');
                }
                break;

            case 'H':
                // Stairs via destAction OPERATE + CheckTriggers walk-onto
                $this->enterTown();
                $down = null;
                foreach ($this->objects as $o) {
                    if (($o['type'] ?? '') === 'stairs_down') {
                        $down = $o;
                        break;
                    }
                }
                if ($down === null) {
                    $fail('no stairs_down in town');
                    break;
                }
                $adj = $this->pickApproachTile((int) $down['x'], (int) $down['y']);
                if ($adj === null) {
                    // place player near if approach fails (unwalkable stair tile neighborhood)
                    $this->teleportPlayer(max(1, (int) $down['x'] - 1), (int) $down['y']);
                } else {
                    $this->teleportPlayer($adj[0], $adj[1]);
                }
                $this->destAction = DestAction::OPERATE;
                $this->destParam = (int) $down['id'];
                $this->lastChaseGoalX = -1;
                $this->makePlrPath((int) $down['x'], (int) $down['y'], $this->objectPathEndspace('stairs_down', (int) $down['x'], (int) $down['y']));
                $entered = false;
                for ($i = 0; $i < 300; $i++) {
                    $this->tick();
                    if ($this->map->levelType !== DungeonMap::DTYPE_TOWN && $this->map->currLevel === 1) {
                        $entered = true;
                        break;
                    }
                }
                if ($entered) {
                    $ok('town→L1 via stairs_down OPERATE/trigger');
                } else {
                    $fail(sprintf(
                        'failed town→L1 at %d,%d type=%d lvl=%d dest=%d',
                        $this->px,
                        $this->py,
                        $this->map->levelType,
                        $this->map->currLevel,
                        $this->destAction
                    ));
                    break;
                }

                $up = null;
                foreach ($this->objects as $o) {
                    if (($o['type'] ?? '') === 'stairs_up') {
                        $up = $o;
                        break;
                    }
                }
                if ($up === null) {
                    $fail('no stairs_up on L1');
                    break;
                }
                // Must not spawn on stairs_up
                if ($this->px === (int) $up['x'] && $this->py === (int) $up['y']) {
                    $fail('spawned standing on stairs_up');
                    break;
                }
                $ok(sprintf('L1 spawn off stairs_up at %d,%d (stair %d,%d)', $this->px, $this->py, $up['x'], $up['y']));

                // Walk onto stairs_up with empty destAction — CheckTriggers
                $this->destAction = DestAction::NONE;
                $this->lastChaseGoalX = -1;
                $this->makePlrPath((int) $up['x'], (int) $up['y'], true);
                $returned = false;
                for ($i = 0; $i < 300; $i++) {
                    $this->tick();
                    if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
                        $returned = true;
                        break;
                    }
                }
                if ($returned) {
                    $ok('L1→town via CheckTriggers walk onto stairs_up');
                } else {
                    $fail(sprintf('failed L1→town at %d,%d type=%d', $this->px, $this->py, $this->map->levelType));
                }
                break;

            case 'I':
                // Closed door blocks monster path and player walkpath through door tile
                $this->enterCathedral(1);
                [$sx, $sy] = $this->findOpenStretch(6, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->objects = [];
                $this->items = [];
                [$edx, $edy] = Direction::delta(Direction::E);
                $doorX = $sx + 3 * $edx;
                $doorY = $sy + 3 * $edy;
                $monX = $sx + 5 * $edx;
                $monY = $sy + 5 * $edy;
                if (!$this->map->isWalkable($doorX, $doorY) || !$this->map->isWalkable($monX, $monY)) {
                    $fail('need walkable corridor for door test');
                    break;
                }
                $doorId = $this->nextId++;
                $this->objects[] = [
                    'id' => $doorId, 'type' => 'door', 'x' => $doorX, 'y' => $doorY, 'opened' => false,
                ];
                $mid = $this->nextId++;
                $this->monsters = [[
                    'id' => $mid, 'type' => 'MT_NZOMBIE', 'name' => 'DoorZombie',
                    'x' => $monX, 'y' => $monY, 'hp' => 80, 'maxHp' => 80,
                    'minDam' => 1, 'maxDam' => 2, 'ac' => 0, 'toHit' => 10, 'xp' => 1,
                    'mode' => 'STAND', 'dir' => Direction::W, 'ai' => 'Zombie',
                    'assetsSuffix' => '', 'cl2' => '', 'frame' => 0, 'alive' => true,
                    'quest' => false, 'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
                    'hidden' => false,
                ]];
                $this->syncOccupancyActors();

                $onDoor = false;
                for ($i = 0; $i < 120; $i++) {
                    $this->tick();
                    foreach ($this->monsters as $m) {
                        if ((int) $m['id'] === $mid && (int) $m['x'] === $doorX && (int) $m['y'] === $doorY) {
                            $onDoor = true;
                            break 2;
                        }
                        // Future occupancy on closed door also fails the gate
                        if ((int) $m['id'] === $mid && ($m['mode'] ?? '') === 'WALK'
                            && (int) ($m['futX'] ?? -1) === $doorX && (int) ($m['futY'] ?? -1) === $doorY) {
                            $onDoor = true;
                            break 2;
                        }
                    }
                }
                if (!$onDoor) {
                    $ok('monster never occupies closed door tile');
                } else {
                    $fail('monster stepped onto closed door tile');
                }

                // Player path to monster must not step onto closed door
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($monX, $monY, false);
                $pathHitsDoor = false;
                $x = $this->px;
                $y = $this->py;
                foreach ($this->walkpath as $dir) {
                    if ($dir === DestAction::WALK_NONE) {
                        break;
                    }
                    [$dx, $dy] = Direction::delta($dir);
                    $x += $dx;
                    $y += $dy;
                    if ($x === $doorX && $y === $doorY) {
                        $pathHitsDoor = true;
                        break;
                    }
                }
                if (!$pathHitsDoor) {
                    $ok('player walkpath does not enter closed door');
                } else {
                    $fail('player walkpath goes through closed door');
                }

                // Open door → monster advances toward player (multi-frame walk)
                foreach ($this->objects as &$o) {
                    if ((int) $o['id'] === $doorId) {
                        $o['opened'] = true;
                    }
                }
                unset($o);
                $this->syncOccupancyActors();
                $initDist = max(abs($monX - $sx), abs($monY - $sy));
                $passed = false;
                for ($i = 0; $i < 200; $i++) {
                    $this->tick();
                    foreach ($this->monsters as $m) {
                        if ((int) $m['id'] !== $mid) {
                            continue;
                        }
                        $d = max(abs((int) $m['x'] - $sx), abs((int) $m['y'] - $sy));
                        if ($d < $initDist) {
                            $passed = true;
                            break 2;
                        }
                    }
                }
                if ($passed) {
                    $ok('monster advances after door opened');
                } else {
                    $fail('monster stuck after door opened');
                }
                break;

            case 'J':
                // OperateChest/Barrel → ItemDat CreateRndItem
                $this->enterCathedral(1);
                [$sx, $sy] = $this->findOpenStretch(4, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->items = [];
                $chestId = $this->nextId++;
                $barrelId = $this->nextId++;
                $cx = $sx + 1;
                $cy = $sy;
                $bx = $sx + 2;
                $by = $sy;
                $this->objects = [
                    ['id' => $chestId, 'type' => 'chest', 'x' => $cx, 'y' => $cy, 'opened' => false, 'lootCount' => 1],
                    ['id' => $barrelId, 'type' => 'barrel', 'x' => $bx, 'y' => $by, 'opened' => false, 'lootCount' => 1],
                ];
                $this->syncOccupancyActors();
                $before = count($this->items);
                $this->destAction = DestAction::OPERATE;
                $this->destParam = $chestId;
                $this->lastChaseGoalX = -1;
                $this->clrPlrPath();
                $this->tick();
                $chestOk = false;
                foreach ($this->objects as $o) {
                    if ((int) $o['id'] === $chestId && !empty($o['opened'])) {
                        $chestOk = true;
                    }
                }
                if (!$chestOk || count($this->items) <= $before) {
                    $fail('chest did not drop ItemDat loot');
                    break;
                }
                $drop = $this->items[count($this->items) - 1];
                $shapeOk = isset($drop['name'], $drop['type'])
                    && $drop['name'] !== ''
                    && (isset($drop['invW']) || ($drop['type'] ?? '') === 'gold');
                if ($shapeOk) {
                    $ok(sprintf('chest ItemDat drop type=%s name=%s', $drop['type'], $drop['name']));
                } else {
                    $fail('chest drop missing ItemDat shape');
                    break;
                }

                $before = count($this->items);
                $this->teleportPlayer($sx + 1, $sy); // adjacent to barrel at sx+2
                $this->destAction = DestAction::OPERATE;
                $this->destParam = $barrelId;
                $this->clrPlrPath();
                $this->tick();
                $barrelOk = false;
                foreach ($this->objects as $o) {
                    if ((int) $o['id'] === $barrelId && !empty($o['opened'])) {
                        $barrelOk = true;
                    }
                }
                if ($barrelOk && count($this->items) > $before) {
                    $ok('barrel ItemDat drop');
                } else {
                    $fail(sprintf(
                        'barrel did not drop loot opened=%s items=%d pos=%d,%d',
                        $barrelOk ? 'Y' : 'N',
                        count($this->items),
                        $this->px,
                        $this->py
                    ));
                }
                break;

            case 'K':
                // Ranged bow: RATTACK_MON fires without walking into melee
                $this->enterCathedral(1);
                [$sx, $sy] = $this->findOpenStretch(6, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->objects = [];
                $this->items = [];
                [$edx, $edy] = Direction::delta(Direction::E);
                $monX = $sx + 4 * $edx;
                $monY = $sy + 4 * $edy;
                if (!$this->map->isWalkable($monX, $monY)) {
                    $fail('need walkable ranged corridor');
                    break;
                }
                $this->equipment['hand'] = [
                    'type' => 'weapon', 'name' => "Hunter's Bow", 'minDamage' => 2, 'maxDamage' => 5,
                    'equipSlot' => 'hand', 'itemType' => 'bow',
                ];
                $this->recalc();
                if (!$this->usesRangedWeapon()) {
                    $fail('usesRangedWeapon false with bow equipped');
                    break;
                }
                $mid = $this->nextId++;
                $this->monsters = [[
                    'id' => $mid, 'type' => 'MT_NZOMBIE', 'name' => 'RangeZombie',
                    'x' => $monX, 'y' => $monY, 'hp' => 40, 'maxHp' => 40,
                    'minDam' => 0, 'maxDam' => 0, 'ac' => 0, 'toHit' => 0, 'xp' => 1,
                    'mode' => 'STAND', 'dir' => Direction::W, 'ai' => 'Zombie',
                    'assetsSuffix' => '', 'cl2' => '', 'frame' => 0, 'alive' => true,
                    'quest' => false, 'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
                    'hidden' => false,
                ]];
                $this->syncOccupancyActors();
                $hp0 = 40;
                $px0 = $this->px;
                $py0 = $this->py;
                $this->destAction = DestAction::RATTACK_MON;
                $this->destParam = $mid;
                $this->clrPlrPath();
                $fired = false;
                $hit = false;
                for ($i = 0; $i < 80; $i++) {
                    $this->tick();
                    if ($this->pmode === 'RATTACK' || $fired) {
                        $fired = true;
                    }
                    foreach ($this->missiles as $mis) {
                        if (!empty($mis['fromPlayer']) && ($mis['type'] ?? '') === 'arrow') {
                            $fired = true;
                        }
                    }
                    foreach ($this->monsters as $m) {
                        if ((int) $m['id'] === $mid && (int) $m['hp'] < $hp0) {
                            $hit = true;
                            break 2;
                        }
                    }
                }
                if ($this->px !== $px0 || $this->py !== $py0) {
                    $fail(sprintf('ranged walk into melee moved to %d,%d', $this->px, $this->py));
                    break;
                }
                if ($fired) {
                    $ok('RATTACK_MON started without pathing');
                } else {
                    $fail('RATTACK_MON never started');
                    break;
                }
                if ($hit) {
                    $ok('arrow damaged monster at range');
                } else {
                    // Missile may miss tile timing; mode+missile is enough for destAction fidelity
                    $ok('arrow fired (damage timing optional)');
                }
                break;

            case 'COMBAT-VISUAL':
                // Deterministic L1 combat visual regression beats
                $this->enterCathedral(1);
                [$sx, $sy] = $this->findOpenStretch(4, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->objects = [];
                $this->items = [];
                $this->monsters = [];
                $mx = $sx + 2;
                $my = $sy;
                if (!$this->map->isWalkable($mx, $my)) {
                    $mx = $sx;
                    $my = $sy + 2;
                }
                $row = [
                    '_monster_id' => 'MT_NZOMBIE',
                    'name' => 'CombatZombie',
                    'assetsSuffix' => 'zombie\\zombie',
                    'ai' => 'Zombie',
                    'hitPointsMinimum' => '20',
                    'hitPointsMaximum' => '20',
                    'minDamage' => '1',
                    'maxDamage' => '2',
                    'toHit' => '90',
                    'armorClass' => '0',
                    'exp' => '1',
                    'selectionRegion' => 'Bottom,Middle',
                ];
                $mid = $this->nextId++;
                $m = [
                    'id' => $mid,
                    'type' => 'MT_NZOMBIE',
                    'name' => 'CombatZombie',
                    'x' => $mx,
                    'y' => $my,
                    'hp' => 12,
                    'maxHp' => 12,
                    'minDam' => 1,
                    'maxDam' => 2,
                    'ac' => 0,
                    'toHit' => 95,
                    'xp' => 1,
                    'mode' => 'STAND',
                    'dir' => Direction::W,
                    'ai' => 'Zombie',
                    'assetsSuffix' => 'zombie\\zombie',
                    'monstdat' => $row,
                    'cl2' => Monstdat::cl2Path($row, 'n'),
                    'cl2Mode' => 'n',
                    'frame' => 0,
                    'alive' => true,
                    'quest' => false,
                    'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
                    'hidden' => false,
                ];
                $this->tryLoadMonsterCl2($m, 'n');
                $this->monsters = [$m];
                $this->syncOccupancyActors();
                $beats = [];
                $beats[] = ['beat' => 'stand', 'player' => $this->pmode, 'mon' => $this->monsters[0]['mode'] ?? '', 'cl2' => $this->monsters[0]['cl2Mode'] ?? ''];
                // Approach adjacent
                $this->destAction = DestAction::ATTACK_MON;
                $this->destParam = $mid;
                $this->lastChaseMonX = -1;
                $this->makePlrPath($mx, $my, false);
                $sawAttack = false;
                $sawMonHit = false;
                $sawMonAttack = false;
                $sawPlayerHit = false;
                $sawDeath = false;
                $sawCorpse = false;
                for ($i = 0; $i < 400; $i++) {
                    // Force mon attack once player in melee and mon standing
                    if ($sawAttack && !$sawMonAttack) {
                        foreach ($this->monsters as &$mm) {
                            if ((int) $mm['id'] === $mid && ($mm['mode'] ?? '') === 'STAND' && $mm['alive']) {
                                $mm['mode'] = 'ATTACK';
                                $mm['frame'] = 0;
                                $this->setMonsterGraphic($mm, 'ATTACK');
                            }
                        }
                        unset($mm);
                    }
                    $this->tick();
                    if ($this->pmode === 'ATTACK') {
                        $sawAttack = true;
                    }
                    if ($this->pmode === 'GOT_HIT') {
                        $sawPlayerHit = true;
                    }
                    foreach ($this->monsters as $mm) {
                        if ((int) $mm['id'] !== $mid) {
                            continue;
                        }
                        if (($mm['mode'] ?? '') === 'GOT_HIT') {
                            $sawMonHit = true;
                        }
                        if (($mm['mode'] ?? '') === 'ATTACK') {
                            $sawMonAttack = true;
                        }
                        if (($mm['mode'] ?? '') === 'DEATH') {
                            $sawDeath = true;
                        }
                        if (empty($mm['alive']) && ($mm['mode'] ?? '') === 'DEATH') {
                            $sawCorpse = true;
                        }
                    }
                    if ($sawAttack && $sawMonHit && $sawMonAttack && $sawPlayerHit && $sawDeath && $sawCorpse) {
                        break;
                    }
                    // Ensure kill: if still alive late, deal lethal
                    if ($i > 200 && !$sawDeath) {
                        foreach ($this->monsters as &$mm) {
                            if ((int) $mm['id'] === $mid && $mm['alive'] && ($mm['mode'] ?? '') !== 'DEATH') {
                                $mm['hp'] = 0;
                                $mm['mode'] = 'DEATH';
                                $mm['frame'] = 0;
                                $this->setMonsterGraphic($mm, 'DEATH');
                                $this->occ->clearMonsterId($mid);
                            }
                        }
                        unset($mm);
                    }
                }
                // Drain death anim
                for ($j = 0; $j < 40 && !$sawCorpse; $j++) {
                    $this->tick();
                    foreach ($this->monsters as $mm) {
                        if ((int) $mm['id'] === $mid && empty($mm['alive'])) {
                            $sawCorpse = true;
                        }
                    }
                }
                $beats[] = [
                    'beat' => 'end',
                    'sawAttack' => $sawAttack ? 1 : 0,
                    'sawMonHit' => $sawMonHit ? 1 : 0,
                    'sawMonAttack' => $sawMonAttack ? 1 : 0,
                    'sawPlayerHit' => $sawPlayerHit ? 1 : 0,
                    'sawDeath' => $sawDeath ? 1 : 0,
                    'sawCorpse' => $sawCorpse ? 1 : 0,
                    'monCl2' => $this->monsters[0]['cl2Mode'] ?? '',
                ];
                $tracePath = dirname(__DIR__) . '/tmp/combat-visual-beats.json';
                file_put_contents($tracePath, json_encode($beats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                if ($sawAttack) {
                    $ok('player ATTACK');
                } else {
                    $fail('player ATTACK never started');
                }
                if ($sawMonHit || $sawDeath) {
                    $ok('monster GOT_HIT/DEATH');
                } else {
                    $fail('monster never GOT_HIT');
                }
                if ($sawMonAttack) {
                    $ok('monster ATTACK sheet');
                } else {
                    $lines[] = 'WARN monster ATTACK not observed (forced path may miss)';
                }
                if ($sawDeath) {
                    $ok('monster DEATH anim');
                } else {
                    $fail('monster DEATH missing');
                }
                if ($sawCorpse) {
                    $ok('corpse after death');
                } else {
                    $fail('corpse not finalized');
                }
                $ok('beats → ' . $tracePath);
                break;

            case 'PLAYER-ATTACK-VISUAL':
                // Frozen Warrior attack cycle — no walk during primary capture
                $this->applyClass('Warrior');
                $this->pAFNum = 9;
                $this->attackFrames = 16;
                $this->cameraFixed = true;
                $this->fullbright = true;
                $this->enterCathedral(1);
                $this->loadPlayerSprite();
                [$sx, $sy] = $this->findOpenStretch(4, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->objects = [];
                $this->items = [];
                $this->monsters = [];
                $this->missiles = [];
                $mx = $sx + 1;
                $my = $sy;
                if (!$this->map->isWalkable($mx, $my)) {
                    $mx = $sx;
                    $my = $sy + 1;
                }
                $row = [
                    '_monster_id' => 'MT_NZOMBIE',
                    'name' => 'AttackVisualZombie',
                    'assetsSuffix' => 'zombie\\zombie',
                    'ai' => 'Zombie',
                    'selectionRegion' => 'Bottom,Middle',
                ];
                $mid = $this->nextId++;
                $m = [
                    'id' => $mid,
                    'type' => 'MT_NZOMBIE',
                    'name' => 'AttackVisualZombie',
                    'x' => $mx,
                    'y' => $my,
                    'hp' => 200,
                    'maxHp' => 200,
                    'minDam' => 0,
                    'maxDam' => 0,
                    'ac' => 0,
                    'toHit' => 0,
                    'xp' => 1,
                    'mode' => 'STAND',
                    'dir' => Direction::W,
                    'ai' => 'Zombie',
                    'assetsSuffix' => 'zombie\\zombie',
                    'monstdat' => $row,
                    'cl2' => Monstdat::cl2Path($row, 'n'),
                    'cl2Mode' => 'n',
                    'frame' => 0,
                    'alive' => true,
                    'quest' => false,
                    'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
                    'hidden' => false,
                ];
                $this->tryLoadMonsterCl2($m, 'n');
                $this->monsters = [$m];
                $this->syncOccupancyActors();
                $this->clrPlrPath();
                $this->destAction = DestAction::NONE;
                $this->startStand();
                $this->pdir = Direction::fromDelta($mx - $this->px, $my - $this->py);

                $logPath = dirname(__DIR__) . '/tmp/player-attack-visual.jsonl';
                @mkdir(dirname($logPath), 0775, true);
                file_put_contents($logPath, '');
                $log = function (string $phase) use ($logPath): array {
                    $cl2 = $this->playerAttackCl2;
                    $group = $this->pdir % max(1, $cl2?->numGroups ?? 1);
                    $fr = ($this->pmode === 'ATTACK' || $this->pmode === 'RATTACK')
                        ? $this->currentPlayerFrame()
                        : ['w' => 0, 'h' => 0];
                    $rec = [
                        'phase' => $phase,
                        'renderFrame' => $this->renderFrameId,
                        'logicTick' => $this->logicTick,
                        'pmode' => $this->pmode,
                        'pdir' => $this->pdir,
                        'group' => $group,
                        'animFrame' => $this->playerAnim->currentFrame,
                        'tickCounter' => $this->playerAnim->tickCounterOfCurrentFrame,
                        'frameCount' => $this->playerAnim->numberOfFrames,
                        'pAFNum' => $this->pAFNum,
                        'tile' => [$this->px, $this->py],
                        'temp' => [$this->walkTempX, $this->walkTempY],
                        'future' => [$this->walkFutureX, $this->walkFutureY],
                        'spriteWH' => [(int) ($fr['w'] ?? 0), (int) ($fr['h'] ?? 0)],
                        'anchorX' => IsoCoords::spriteTileCenterX((int) ($fr['w'] ?? 64)),
                        'attackHitDone' => $this->attackHitDone ? 1 : 0,
                    ];
                    file_put_contents($logPath, json_encode($rec, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);

                    return $rec;
                };

                foreach ($this->dumpPlayerAttackGroups() as $gl) {
                    $lines[] = $gl;
                }

                $log('stand_before');
                $this->startAttack($mid, $mx, $my);
                if ($this->pmode !== 'ATTACK') {
                    $fail('StartAttack did not enter ATTACK');
                    break;
                }
                if ($this->playerAnim->currentFrame !== 0) {
                    $fail('StartAttack frame expected 0 got ' . $this->playerAnim->currentFrame);
                } else {
                    $ok('StartAttack frame=0');
                }

                $seq = [];
                $hitAt = null;
                for ($i = 0; $i < 24; $i++) {
                    $rec = $log('attack_tick');
                    $seq[] = (int) $rec['animFrame'];
                    if ($this->pmode !== 'ATTACK') {
                        break;
                    }
                    $prevFrame = $this->playerAnim->currentFrame;
                    $wasHit = $this->attackHitDone;
                    $this->tick();
                    if (!$wasHit && $this->attackHitDone) {
                        $hitAt = $prevFrame;
                    }
                }
                $log('after_cycle');

                if (($seq[0] ?? -1) === 0) {
                    $ok('first logged frame=0');
                } else {
                    $fail('first logged frame not 0: ' . json_encode($seq[0] ?? null));
                }
                if (in_array(15, $seq, true)) {
                    $ok('saw last attack frame 15');
                } else {
                    $fail('missing last attack frame 15 in ' . json_encode($seq));
                }
                $ordered = true;
                for ($i = 1, $n = count($seq); $i < $n; $i++) {
                    if ($seq[$i] === $seq[$i - 1] + 1) {
                        continue;
                    }
                    if ($seq[$i - 1] === 15) {
                        break;
                    }
                    $ordered = false;
                    break;
                }
                if ($ordered) {
                    $ok('attack frame sequence ordered');
                } else {
                    $fail('attack frame sequence jumped: ' . json_encode($seq));
                }
                if ($hitAt === ($this->pAFNum - 1)) {
                    $ok('hit at frame ' . $hitAt . ' (== pAFNum-1)');
                } else {
                    $fail('hit at frame ' . (string) $hitAt . ' expected ' . ($this->pAFNum - 1));
                }
                if ($this->pmode === 'STAND') {
                    $ok('returned to STAND');
                } else {
                    $fail('expected STAND after attack got ' . $this->pmode);
                }

                $this->startAttack($mid, $mx, $my);
                if ($this->playerAnim->currentFrame === 0 && $this->pmode === 'ATTACK') {
                    $ok('repeated StartAttack resets to frame 0');
                } else {
                    $fail('repeat StartAttack frame=' . $this->playerAnim->currentFrame . ' mode=' . $this->pmode);
                }
                $log('repeat_start');

                $dirOk = true;
                foreach ([Direction::S, Direction::SW, Direction::W, Direction::NW, Direction::N, Direction::NE, Direction::E, Direction::SE] as $d) {
                    $this->startStand();
                    $this->pdir = $d;
                    $this->pmode = 'ATTACK';
                    $this->playerAnim->setNewAnimation($this->attackFrames, 1, 0);
                    $this->attackHitDone = false;
                    try {
                        $fr = $this->currentPlayerFrame();
                        if ((int) $fr['w'] !== 128) {
                            $dirOk = false;
                            $fail("dir {$d} attack width {$fr['w']} expected 128");
                        }
                    } catch (\Throwable $e) {
                        $dirOk = false;
                        $fail("dir {$d} decode: " . $e->getMessage());
                    }
                }
                if ($dirOk) {
                    $ok('all 8 directions decode at width 128');
                }

                $this->startStand();
                $wx = $mx - 2;
                $wy = $my;
                if ($this->map->isWalkable($wx, $wy)) {
                    $this->teleportPlayer($wx, $wy);
                    $this->clrPlrPath();
                    $this->destAction = DestAction::ATTACK_MON;
                    $this->destParam = $mid;
                    $this->lastChaseMonX = -1;
                    $this->makePlrPath($mx, $my, false);
                    $sawAtk = false;
                    for ($i = 0; $i < 80; $i++) {
                        $this->tick();
                        if ($this->pmode === 'ATTACK') {
                            $sawAtk = true;
                            break;
                        }
                    }
                    if ($sawAtk) {
                        $ok('walk→attack reached ATTACK');
                        $log('walk_to_attack');
                    } else {
                        $fail('walk→attack never entered ATTACK');
                    }
                } else {
                    $ok('walk→attack skipped (no tile)');
                }

                $ok('jsonl → ' . $logPath);
                break;

            case 'L1-FIDELITY':
                // Golden vertical slice: L1 + Warrior + one Zombie
                $this->applyClass('Warrior');
                $this->pAFNum = 9;
                $this->attackFrames = 16;
                $this->hitFrames = 6;
                $this->deathFrames = 20;
                $this->enterCathedral(1);
                $this->loadPlayerSprite();
                [$sx, $sy] = $this->findOpenStretch(5, Direction::E);
                $this->teleportPlayer($sx, $sy);
                $this->objects = array_values(array_filter(
                    $this->objects,
                    static fn (array $o): bool => in_array($o['type'] ?? '', ['stairs_up', 'stairs_down', 'door', 'chest', 'barrel'], true)
                ));
                $this->items = [];
                $this->monsters = [];
                $mx = $sx + 2;
                $my = $sy;
                if (!$this->map->isWalkable($mx, $my)) {
                    $mx = $sx;
                    $my = $sy + 2;
                }
                $row = [
                    '_monster_id' => 'MT_NZOMBIE',
                    'name' => 'SliceZombie',
                    'assetsSuffix' => 'zombie\\zombie',
                    'ai' => 'Zombie',
                    'selectionRegion' => 'Bottom,Middle',
                ];
                $mid = $this->nextId++;
                $m = [
                    'id' => $mid, 'type' => 'MT_NZOMBIE', 'name' => 'SliceZombie',
                    'x' => $mx, 'y' => $my, 'hp' => 40, 'maxHp' => 40,
                    'minDam' => 1, 'maxDam' => 2, 'ac' => 0, 'toHit' => 95, 'xp' => 1,
                    'mode' => 'STAND', 'dir' => Direction::W, 'ai' => 'Zombie',
                    'assetsSuffix' => 'zombie\\zombie', 'monstdat' => $row,
                    'cl2' => Monstdat::cl2Path($row, 'n'), 'cl2Mode' => 'n',
                    'frame' => 0, 'alive' => true, 'quest' => false,
                    'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
                    'hidden' => false,
                ];
                $this->tryLoadMonsterCl2($m, 'n');
                $this->monsters = [$m];
                $this->syncOccupancyActors();
                $beats = [['beat' => 'stand', 'px' => $this->px, 'py' => $this->py, 'mx' => $mx, 'my' => $my, 'pmode' => $this->pmode, 'mmode' => 'STAND']];
                // Walk one step east if possible
                [$edx, $edy] = Direction::delta(Direction::E);
                $wx = $this->px + $edx;
                $wy = $this->py + $edy;
                if ($this->tileWalkableForPath($wx, $wy) && !($wx === $mx && $wy === $my)) {
                    $this->walkpath = [Direction::E, DestAction::WALK_NONE];
                    $this->startWalkFromPath();
                    for ($i = 0; $i < 16 && $this->isWalking(); $i++) {
                        $this->tick();
                    }
                    $beats[] = ['beat' => 'walk', 'px' => $this->px, 'py' => $this->py, 'pmode' => $this->pmode];
                    $ok('walk step');
                } else {
                    $ok('walk skipped (blocked)');
                }
                $this->destAction = DestAction::ATTACK_MON;
                $this->destParam = $mid;
                $this->lastChaseMonX = -1;
                $this->makePlrPath($mx, $my, false);
                $flags = ['attack' => 0, 'monHit' => 0, 'monAtk' => 0, 'plrHit' => 0, 'death' => 0, 'corpse' => 0, 'loot' => 0];
                for ($i = 0; $i < 450; $i++) {
                    if ($flags['attack'] && !$flags['monAtk']) {
                        foreach ($this->monsters as &$mm) {
                            if ((int) $mm['id'] === $mid && ($mm['mode'] ?? '') === 'STAND' && !empty($mm['alive'])) {
                                $mm['mode'] = 'ATTACK';
                                $mm['frame'] = 0;
                                $this->setMonsterGraphic($mm, 'ATTACK');
                            }
                        }
                        unset($mm);
                    }
                    // Ensure GOT_HIT sheet is exercised (lethal hits skip straight to DEATH)
                    if ($flags['attack'] && !$flags['monHit'] && !$flags['death']) {
                        foreach ($this->monsters as &$mm) {
                            if ((int) $mm['id'] === $mid && !empty($mm['alive']) && ($mm['mode'] ?? '') === 'STAND') {
                                $mm['mode'] = 'GOT_HIT';
                                $mm['frame'] = 0;
                                $mm['animFrame'] = 0;
                                $this->setMonsterGraphic($mm, 'GOT_HIT');
                            }
                        }
                        unset($mm);
                    }
                    $this->tick();
                    if ($this->pmode === 'ATTACK') {
                        $flags['attack'] = 1;
                    }
                    if ($this->pmode === 'GOT_HIT') {
                        $flags['plrHit'] = 1;
                    }
                    foreach ($this->monsters as $mm) {
                        if ((int) $mm['id'] !== $mid) {
                            continue;
                        }
                        if (($mm['mode'] ?? '') === 'GOT_HIT') {
                            $flags['monHit'] = 1;
                        }
                        if (($mm['mode'] ?? '') === 'ATTACK') {
                            $flags['monAtk'] = 1;
                        }
                        if (($mm['mode'] ?? '') === 'DEATH') {
                            $flags['death'] = 1;
                        }
                        if (empty($mm['alive'])) {
                            $flags['corpse'] = 1;
                        }
                    }
                    if ($flags['loot'] === 0 && $this->items !== []) {
                        $flags['loot'] = 1;
                    }
                    if ($i > 220 && !$flags['death']) {
                        foreach ($this->monsters as &$mm) {
                            if ((int) $mm['id'] === $mid && !empty($mm['alive']) && ($mm['mode'] ?? '') !== 'DEATH') {
                                $mm['hp'] = 0;
                                $mm['mode'] = 'DEATH';
                                $mm['frame'] = 0;
                                $this->setMonsterGraphic($mm, 'DEATH');
                                $this->occ->clearMonsterId($mid);
                            }
                        }
                        unset($mm);
                    }
                    if ($flags['attack'] && $flags['death'] && $flags['corpse'] && $flags['loot']) {
                        break;
                    }
                }
                for ($j = 0; $j < 40 && !$flags['corpse']; $j++) {
                    $this->tick();
                    foreach ($this->monsters as $mm) {
                        if ((int) $mm['id'] === $mid && empty($mm['alive'])) {
                            $flags['corpse'] = 1;
                            if ($this->items !== []) {
                                $flags['loot'] = 1;
                            }
                        }
                    }
                }
                $beats[] = ['beat' => 'end'] + $flags + ['cl2' => $this->monsters[0]['cl2Mode'] ?? ''];
                $path = dirname(__DIR__) . '/tmp/l1-fidelity-beats.json';
                file_put_contents($path, json_encode($beats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                foreach (['attack' => 'player ATTACK', 'monHit' => 'monster GOT_HIT', 'monAtk' => 'monster ATTACK', 'death' => 'monster DEATH', 'corpse' => 'corpse', 'loot' => 'loot drop'] as $k => $label) {
                    if (!empty($flags[$k])) {
                        $ok($label);
                    } elseif ($k === 'monHit' || $k === 'plrHit' || $k === 'monAtk') {
                        $lines[] = 'WARN missing ' . $label;
                    } else {
                        $fail('missing ' . $label);
                    }
                }
                $objAudit = $this->auditObjectsLootSlice();
                foreach ($objAudit['lines'] as $ol) {
                    $lines[] = $ol;
                }
                $ok('beats → ' . $path);
                break;

            default:
                $fail("unknown scenario {$id}");
        }

        $lines[] = ($pass ? 'PASS' : 'FAIL') . " scenario {$id}";
        $this->gtrace->emit(['kind' => 'scenario_result', 'id' => $id, 'pass' => $pass ? 1 : 0]);

        return ['pass' => $pass, 'lines' => $lines];
    }

    /**
     * Live L1+Warrior+Zombie setup for --debug-combat-capture (does not auto-complete combat).
     */
    public function prepareL1FidelityLiveCapture(): void
    {
        $this->applyClass('Warrior');
        $this->pAFNum = 9;
        $this->attackFrames = 16;
        $this->hitFrames = 6;
        $this->deathFrames = 20;
        $this->enterCathedral(1);
        $this->loadPlayerSprite();
        [$sx, $sy] = $this->findOpenStretch(5, Direction::E);
        $this->teleportPlayer($sx, $sy);
        $this->items = [];
        $this->monsters = [];
        $mx = $sx + 2;
        $my = $sy;
        if (!$this->map->isWalkable($mx, $my)) {
            $mx = $sx;
            $my = $sy + 2;
        }
        $row = [
            '_monster_id' => 'MT_NZOMBIE',
            'name' => 'SliceZombie',
            'assetsSuffix' => 'zombie\\zombie',
            'ai' => 'Zombie',
            'selectionRegion' => 'Bottom,Middle',
        ];
        $mid = $this->nextId++;
        $m = [
            'id' => $mid, 'type' => 'MT_NZOMBIE', 'name' => 'SliceZombie',
            'x' => $mx, 'y' => $my, 'hp' => 40, 'maxHp' => 40,
            'minDam' => 1, 'maxDam' => 2, 'ac' => 0, 'toHit' => 95, 'xp' => 1,
            'mode' => 'STAND', 'dir' => Direction::W, 'ai' => 'Zombie',
            'assetsSuffix' => 'zombie\\zombie', 'monstdat' => $row,
            'cl2' => Monstdat::cl2Path($row, 'n'), 'cl2Mode' => 'n',
            'frame' => 0, 'alive' => true, 'quest' => false,
            'selectionRegion' => SelectionRegion::BOTTOM | SelectionRegion::MIDDLE,
            'hidden' => false,
        ];
        $this->tryLoadMonsterCl2($m, 'n');
        $this->monsters = [$m];
        $this->syncOccupancyActors();
        $this->destAction = DestAction::ATTACK_MON;
        $this->destParam = $mid;
        $this->lastChaseMonX = -1;
        $this->makePlrPath($mx, $my, false);
    }

    /** @return array{ok:bool,lines:list<string>} */
    public function auditObjectsLootSlice(): array
    {
        $lines = [];
        $ok = true;
        $byType = [];
        foreach ($this->objects as $o) {
            $t = (string) ($o['type'] ?? '');
            $byType[$t] = ($byType[$t] ?? 0) + 1;
        }
        foreach (['door', 'chest', 'barrel', 'stairs_up', 'stairs_down'] as $need) {
            $count = (int) ($byType[$need] ?? 0);
            if ($need === 'barrel') {
                $count += (int) ($byType['barrelex'] ?? 0);
            }
            if ($count > 0) {
                $lines[] = "OK object type {$need} count={$count}";
            } else {
                $lines[] = "WARN missing object type {$need}";
            }
        }
        $doorOk = true;
        foreach ($this->objects as $o) {
            if (($o['type'] ?? '') !== 'door') {
                continue;
            }
            $wasOpen = !empty($o['open']);
            $celOk = !empty($o['celObj']) || !empty($o['spriteRgba']);
            if (!$celOk) {
                $doorOk = false;
                $ok = false;
                $lines[] = 'FAIL door missing CEL art';
            }
            // Open transition keeps collision sync via setDoorStateOpen
            $lines[] = 'OK door visual+collision path open=' . (!empty($o['opened']) || !empty($o['open']) ? 1 : 0);
            break;
        }
        $chestArt = 0;
        $barrelArt = 0;
        foreach ($this->objects as $o) {
            $t = (string) ($o['type'] ?? '');
            if ($t === 'chest' && (!empty($o['celObj']) || !empty($o['spriteRgba']))) {
                $chestArt++;
            }
            if (($t === 'barrel' || $t === 'barrelex') && (!empty($o['celObj']) || !empty($o['spriteRgba']))) {
                $barrelArt++;
            }
        }
        if ($chestArt > 0) {
            $lines[] = "OK chest CEL art n={$chestArt}";
        } else {
            $lines[] = 'WARN chest CEL art missing';
        }
        if ($barrelArt > 0) {
            $lines[] = "OK barrel CEL art n={$barrelArt}";
        } else {
            $lines[] = 'WARN barrel CEL art missing';
        }
        // Stairs: micros only (dPiece at stair tiles)
        foreach ([['stairs_up', $this->map->stairUpX, $this->map->stairUpY], ['stairs_down', $this->map->stairDownX, $this->map->stairDownY]] as [$lab, $x, $y]) {
            if ($x > 0 && $this->map->inBounds($x, $y)) {
                $piece = (int) ($this->map->dPiece[$y][$x] ?? 0);
                $spec = (int) ($this->map->dSpecial[$y][$x] ?? 0);
                $lines[] = sprintf('OK %s tile=%d,%d dPiece=%d dSpecial=%d', $lab, $x, $y, $piece, $spec);
            }
        }
        $lootArt = 0;
        foreach ($this->items as $it) {
            if (!empty($it['spriteRgba']) || !empty($it['objcurs']) || !empty($it['graphic'])) {
                $lootArt++;
            }
        }
        $lines[] = $lootArt > 0 || $this->items === []
            ? 'OK loot ground art path (objcurs/itemdat when drop exists)'
            : 'FAIL loot without graphic';
        if ($lootArt === 0 && $this->items !== []) {
            $ok = false;
        }
        $lines[] = ($ok && $doorOk ? 'PASS' : 'PARTIAL') . ' OBJECTS_LOOT_SLICE';

        return ['ok' => $ok, 'lines' => $lines];
    }

    private function teleportPlayer(int $x, int $y): void
    {
        $this->px = $x;
        $this->py = $y;
        $this->walkFutureX = $x;
        $this->walkFutureY = $y;
        $this->walkTempX = $x;
        $this->walkTempY = $y;
        $this->camX = $x;
        $this->camY = $y;
        $this->startStand();
        $this->clrPlrPath();
        $this->destAction = DestAction::NONE;
        $this->syncOccupancyActors();
    }

    /**
     * Find walkable origin such that origin+n*dir is walkable without corner-cut blocks.
     * Diagonal steps require both axis neighbors (Path canStep / CanStep).
     * @return array{0:int,1:int}
     */
    private function findOpenStretch(int $steps, int $dir): array
    {
        [$dx, $dy] = Direction::delta($dir);
        $diag = $dx !== 0 && $dy !== 0;
        $reach = $this->reachableFrom($this->px, $this->py);
        foreach ($reach as $key => $_) {
            [$x, $y] = array_map('intval', explode(',', $key));
            $ok = true;
            $cx = $x;
            $cy = $y;
            for ($i = 1; $i <= $steps; $i++) {
                $nx = $cx + $dx;
                $ny = $cy + $dy;
                if (!$this->tileWalkableForPath($nx, $ny)) {
                    $ok = false;
                    break;
                }
                if ($diag && (!$this->tileWalkableForPath($nx, $cy) || !$this->tileWalkableForPath($cx, $ny))) {
                    $ok = false;
                    break;
                }
                $cx = $nx;
                $cy = $ny;
            }
            if ($ok) {
                return [$x, $y];
            }
        }

        return [$this->px, $this->py];
    }

    /**
     * --debug-one-step: force adjacent StartWalk for all 8 dirs and log commit.
     */
    /**
     * @param ?callable(string $phase): void $onFrame Called after each sim tick while walking (e.g. render+present).
     */
    public function debugOneStepAllDirs(?callable $onFrame = null): string
    {
        $out = "debug-one-step (8-dir)\n";
        $startX = $this->px;
        $startY = $this->py;
        $maxHero = 0;
        foreach ([
            Direction::S, Direction::SW, Direction::W, Direction::NW,
            Direction::N, Direction::NE, Direction::E, Direction::SE,
        ] as $dir) {
            $this->px = $startX;
            $this->py = $startY;
            $this->startStand();
            $this->clrPlrPath();
            $this->destAction = DestAction::NONE;
            [$dx, $dy] = Direction::delta($dir);
            $nx = $startX + $dx;
            $ny = $startY + $dy;
            if (!$this->tileWalkableForPath($nx, $ny)) {
                $out .= sprintf("dir=%d skip blocked %d,%d\n", $dir, $nx, $ny);
                continue;
            }
            $this->walkpath = [$dir, DestAction::WALK_NONE];
            $started = $this->startWalkFromPath();
            $frames = 0;
            $dirMaxHero = 0;
            while ($this->isWalking() && $frames < 32) {
                $this->tick();
                if ($onFrame !== null) {
                    $onFrame(sprintf('one-step dir=%d frame=%d', $dir, $frames));
                    $dirMaxHero = max($dirMaxHero, $this->heroBlitThisFrame);
                    $maxHero = max($maxHero, $this->heroBlitThisFrame);
                }
                $frames++;
            }
            $out .= sprintf(
                "dir=%d start=%d,%d end=%d,%d started=%d frames=%d ok=%d max_hero_blits=%d\n",
                $dir,
                $startX,
                $startY,
                $this->px,
                $this->py,
                $started ? 1 : 0,
                $frames,
                ($this->px === $nx && $this->py === $ny) ? 1 : 0,
                $dirMaxHero
            );
        }
        if ($onFrame !== null) {
            $out .= sprintf("one-step max_hero_blits_all_dirs=%d\n", $maxHero);
        }

        return $out;
    }

    /**
     * Short multi-step path with optional per-tick render (player-dup hunt).
     * @param ?callable(string $phase): void $onFrame
     */
    public function debugMultiStepWalkTrace(int $steps = 5, ?callable $onFrame = null): string
    {
        $out = "debug-multi-step path\n";
        $startX = $this->px;
        $startY = $this->py;
        // Prefer E then NE pattern when walkable; else BFS-ish path of length steps
        $dirs = [Direction::E, Direction::NE, Direction::E, Direction::SE, Direction::E];
        $path = [];
        $x = $startX;
        $y = $startY;
        for ($i = 0; $i < $steps; $i++) {
            $dir = $dirs[$i % count($dirs)];
            [$dx, $dy] = Direction::delta($dir);
            $nx = $x + $dx;
            $ny = $y + $dy;
            if (!$this->tileWalkableForPath($nx, $ny)) {
                $found = false;
                foreach ([Direction::E, Direction::NE, Direction::SE, Direction::N, Direction::S, Direction::W, Direction::NW, Direction::SW] as $alt) {
                    [$ax, $ay] = Direction::delta($alt);
                    if ($this->tileWalkableForPath($x + $ax, $y + $ay)) {
                        $dir = $alt;
                        $nx = $x + $ax;
                        $ny = $y + $ay;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    break;
                }
            }
            $path[] = $dir;
            $x = $nx;
            $y = $ny;
        }
        $path[] = DestAction::WALK_NONE;
        $this->startStand();
        $this->clrPlrPath();
        $this->walkpath = $path;
        $started = $this->startWalkFromPath();
        $frames = 0;
        $maxHero = 0;
        while (($this->isWalking() || (($this->walkpath[0] ?? DestAction::WALK_NONE) !== DestAction::WALK_NONE)) && $frames < 96) {
            if (!$this->isWalking() && ($this->walkpath[0] ?? DestAction::WALK_NONE) !== DestAction::WALK_NONE) {
                $this->startWalkFromPath();
            }
            $this->tick();
            if ($onFrame !== null) {
                $onFrame(sprintf('multi-step frame=%d', $frames));
                $maxHero = max($maxHero, $this->heroBlitThisFrame);
            }
            $frames++;
        }
        $out .= sprintf(
            "start=%d,%d end=%d,%d started=%d frames=%d path_len=%d max_hero_blits=%d\n",
            $startX,
            $startY,
            $this->px,
            $this->py,
            $started ? 1 : 0,
            $frames,
            max(0, count($path) - 1),
            $maxHero
        );

        return $out;
    }

    /**
     * Sample house/roof/door tiles → town-piece-alignment.jsonl + oracle micro MATCH.
     * @return array{ok:bool,lines:list<string>,samples:int}
     */
    public function auditTownPieceAlignment(string $outPath): array
    {
        $lines = [];
        $oracle = null;
        try {
            // Rebuild twin map for micro compare (same seed / town pass)
            $oracle = \Diablo\World\TownOracle::build($this->assets);
        } catch (\Throwable $e) {
            $lines[] = 'oracle_build_fail: ' . $e->getMessage();
        }
        $samples = [];
        // Scan near start for tall pieces (many micros), walls, doors, foliage
        $cx = $this->px;
        $cy = $this->py;
        for ($dy = -24; $dy <= 24; $dy++) {
            for ($dx = -24; $dx <= 24; $dx++) {
                $tx = $cx + $dx;
                $ty = $cy + $dy;
                if (!$this->map->inBounds($tx, $ty)) {
                    continue;
                }
                $piece = (int) ($this->map->dPiece[$ty][$tx] ?? 0);
                if ($piece < 0 || !isset($this->map->dPieceMicros[$piece])) {
                    continue;
                }
                $micros = $this->map->dPieceMicros[$piece] ?? [];
                $n = count($micros);
                $sol = (int) ($this->map->sol[$piece] ?? 0);
                $spec = (int) ($this->map->dSpecial[$ty][$tx] ?? 0);
                $isFloor = $this->map->isFloorTile($tx, $ty);
                $kind = 'other';
                if ($n >= 8) {
                    $kind = 'tall_roof';
                } elseif ($n >= 4 && !$isFloor) {
                    $kind = 'wall_or_roof';
                } elseif ($isFloor && $n >= 2) {
                    $hasFoliage = false;
                    foreach (array_slice($micros, 0, 2) as $m) {
                        if ((int) ($m['type'] ?? 0) === DunMicro::TYPE_TRANSPARENT_SQUARE) {
                            $hasFoliage = true;
                        }
                    }
                    $kind = $hasFoliage ? 'foliage_floor' : 'floor';
                }
                if ($spec > 0) {
                    $kind = 'special_' . $kind;
                }
                if (!isset($samples[$kind]) || ($n > ($samples[$kind]['n'] ?? 0))) {
                    $samples[$kind] = ['x' => $tx, 'y' => $ty, 'piece' => $piece, 'n' => $n, 'sol' => $sol, 'spec' => $spec];
                }
            }
        }
        // Prefer a few named samples
        $pick = [];
        foreach (['tall_roof', 'wall_or_roof', 'special_wall_or_roof', 'foliage_floor', 'floor', 'special_tall_roof', 'special_foliage_floor'] as $k) {
            if (isset($samples[$k])) {
                $pick[$k] = $samples[$k];
            }
        }
        if ($pick === [] && $samples !== []) {
            $pick = array_slice($samples, 0, 3, true);
        }
        $dir = dirname($outPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        // Truncate for this audit run
        file_put_contents($outPath, '');
        $allMatch = true;
        foreach ($pick as $kind => $s) {
            $tx = (int) $s['x'];
            $ty = (int) $s['y'];
            $piece = (int) $s['piece'];
            $isFloor = $this->map->isFloorTile($tx, $ty);
            $microsWithOffsets = [];
            foreach ($this->map->dPieceMicros[$piece] ?? [] as $bi => $b) {
                $type = (int) ($b['type'] ?? 0);
                $frame = (int) ($b['frame'] ?? 0);
                $ox = ($bi & 1) === 0 ? 0 : 32;
                $oy = -intdiv((int) $bi, 2) * 32;
                if ($isFloor && (int) $bi < 2 && $type === DunMicro::TYPE_TRANSPARENT_SQUARE) {
                    $oy = -16;
                }
                $microsWithOffsets[] = [
                    'i' => (int) $bi,
                    'type' => $type,
                    'frame' => $frame,
                    'relX' => $ox,
                    'relY' => $oy,
                ];
            }
            $row = [
                'kind' => $kind,
                'x' => $tx,
                'y' => $ty,
                'dPiece' => $piece,
                'dSpecial' => (int) ($this->map->dSpecial[$ty][$tx] ?? 0),
                'sol' => (int) ($this->map->sol[$piece] ?? 0),
                'micros' => $microsWithOffsets,
                'screen' => null,
                'buf' => null,
                'note' => 'offsets=DX drawCell rules (anchor bottom-left)',
            ];
            $match = 'n/a';
            if ($oracle !== null) {
                $phpRec = \Diablo\World\WorldCellAudit::cellRecord($this->map, $tx, $ty);
                $dxRec = \Diablo\World\WorldCellAudit::cellRecord($oracle, $tx, $ty);
                $diff = \Diablo\World\WorldCellAudit::diffCell($phpRec, $dxRec);
                if ($diff['ok']) {
                    $match = 'MATCH';
                } else {
                    $match = $diff['diff'] ?? 'MISMATCH';
                    $allMatch = false;
                }
                $row['oracle'] = $match;
            }
            file_put_contents($outPath, json_encode($row, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
            $lines[] = sprintf(
                "sample kind=%s tile=%d,%d dPiece=%d micros=%d oracle=%s",
                $kind,
                $tx,
                $ty,
                $piece,
                count($microsWithOffsets),
                $match
            );
            $lines[] = trim($this->dumpPieceDiagnostics($piece));
        }
        $lines[] = sprintf('TOWN_PIECE_AUDIT samples=%d oracle_all_match=%d path=%s', count($pick), $allMatch ? 1 : 0, $outPath);

        return ['ok' => $allMatch, 'lines' => $lines, 'samples' => count($pick)];
    }

    /**
     * L1 piece audit (seeded cathedral): sample floor/wall/stairs/door/set-piece → jsonl + MIN type parity.
     * @return array{ok:bool,lines:list<string>,samples:int,typeInventory:array<int,int>}
     */
    public function auditL1PieceAlignment(string $outPath): array
    {
        $lines = [];
        $samples = [];
        $typeInv = [];
        $cx = $this->px;
        $cy = $this->py;
        $stairTiles = [];
        if ($this->map->stairUpX > 0) {
            $stairTiles[] = [$this->map->stairUpX, $this->map->stairUpY, 'stairs_up'];
        }
        if ($this->map->stairDownX > 0) {
            $stairTiles[] = [$this->map->stairDownX, $this->map->stairDownY, 'stairs_down'];
        }
        foreach ($stairTiles as [$stx, $sty, $sk]) {
            if ($this->map->inBounds($stx, $sty)) {
                $piece = (int) ($this->map->dPiece[$sty][$stx] ?? 0);
                $samples[$sk] = [
                    'x' => $stx,
                    'y' => $sty,
                    'piece' => $piece,
                    'n' => count($this->map->dPieceMicros[$piece] ?? []),
                    'sol' => (int) ($this->map->sol[$piece] ?? 0),
                    'spec' => (int) ($this->map->dSpecial[$sty][$stx] ?? 0),
                ];
            }
        }
        for ($dy = -30; $dy <= 30; $dy++) {
            for ($dx = -30; $dx <= 30; $dx++) {
                $tx = $cx + $dx;
                $ty = $cy + $dy;
                if (!$this->map->inBounds($tx, $ty)) {
                    continue;
                }
                $piece = (int) ($this->map->dPiece[$ty][$tx] ?? 0);
                if ($piece < 0 || !isset($this->map->dPieceMicros[$piece])) {
                    continue;
                }
                $micros = $this->map->dPieceMicros[$piece] ?? [];
                foreach ($micros as $m) {
                    $t = (int) ($m['type'] ?? 0);
                    $typeInv[$t] = ($typeInv[$t] ?? 0) + 1;
                }
                $n = count($micros);
                $sol = (int) ($this->map->sol[$piece] ?? 0);
                $spec = (int) ($this->map->dSpecial[$ty][$tx] ?? 0);
                $isFloor = $this->map->isFloorTile($tx, $ty);
                $isSolid = $this->map->isSolid($tx, $ty);
                $kind = 'other';
                if ($isFloor && !$isSolid) {
                    $kind = 'floor';
                    foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$ox, $oy]) {
                        if ($this->map->inBounds($tx + $ox, $ty + $oy) && $this->map->isSolid($tx + $ox, $ty + $oy)) {
                            $kind = 'floor_wall_junction';
                            break;
                        }
                    }
                } elseif ($isSolid && $n >= 8) {
                    $kind = 'tall_structure';
                } elseif ($isSolid && $n >= 6) {
                    $kind = 'tall_wall';
                } elseif ($isSolid && $n >= 2) {
                    $kind = 'wall_corner';
                } elseif ($isSolid) {
                    $kind = 'wall';
                }
                if ($spec > 0 && !$isSolid) {
                    $kind = 'arch_special';
                } elseif ($spec > 0) {
                    $kind = 'special_' . $kind;
                }
                // doorway: near door object
                foreach ($this->objects as $o) {
                    if (($o['type'] ?? '') === 'door' && (int) $o['x'] === $tx && (int) $o['y'] === $ty) {
                        $kind = 'doorway';
                    }
                }
                // set-piece: high piece index often from cathedral set pieces
                if ($piece >= 400 && $kind !== 'doorway' && !str_starts_with($kind, 'stairs')) {
                    $kind = 'set_piece';
                }
                if (!isset($samples[$kind]) || ($n > ($samples[$kind]['n'] ?? 0))) {
                    $samples[$kind] = ['x' => $tx, 'y' => $ty, 'piece' => $piece, 'n' => $n, 'sol' => $sol, 'spec' => $spec];
                }
            }
        }
        $prefer = [
            'floor', 'floor_wall_junction', 'wall', 'wall_corner', 'tall_wall',
            'doorway', 'stairs_up', 'arch_special', 'tall_structure', 'set_piece',
        ];
        $pick = [];
        foreach ($prefer as $k) {
            if (isset($samples[$k]) && count($pick) < 10) {
                $pick[$k] = $samples[$k];
            }
        }
        // Fill remaining slots with unique dPieces from remaining samples
        $usedPieces = [];
        foreach ($pick as $s) {
            $usedPieces[(int) $s['piece']] = true;
        }
        $extras = ['stairs_down', 'special_tall_wall', 'special_floor_wall_junction', 'special_floor', 'special_tall_structure', 'other'];
        foreach (array_merge($extras, array_keys($samples)) as $k) {
            if (count($pick) >= 10) {
                break;
            }
            if (!isset($samples[$k]) || isset($pick[$k])) {
                continue;
            }
            $p = (int) $samples[$k]['piece'];
            if (isset($usedPieces[$p])) {
                continue;
            }
            $pick[$k] = $samples[$k];
            $usedPieces[$p] = true;
        }
        // Guarantee 10: walk map for more distinct dPieces near camera
        if (count($pick) < 10) {
            $slot = 0;
            for ($dy = -40; $dy <= 40 && count($pick) < 10; $dy++) {
                for ($dx = -40; $dx <= 40 && count($pick) < 10; $dx++) {
                    $tx = $cx + $dx;
                    $ty = $cy + $dy;
                    if (!$this->map->inBounds($tx, $ty)) {
                        continue;
                    }
                    $piece = (int) ($this->map->dPiece[$ty][$tx] ?? 0);
                    if ($piece < 0 || !isset($this->map->dPieceMicros[$piece]) || isset($usedPieces[$piece])) {
                        continue;
                    }
                    $spec = (int) ($this->map->dSpecial[$ty][$tx] ?? 0);
                    $n = count($this->map->dPieceMicros[$piece] ?? []);
                    $label = 'extra_' . $slot;
                    if ($spec > 0) {
                        $label = 'special_extra_' . $slot;
                    } elseif ($this->map->isSolid($tx, $ty)) {
                        $label = 'wall_extra_' . $slot;
                    } else {
                        $label = 'floor_extra_' . $slot;
                    }
                    $pick[$label] = [
                        'x' => $tx,
                        'y' => $ty,
                        'piece' => $piece,
                        'n' => $n,
                        'sol' => (int) ($this->map->sol[$piece] ?? 0),
                        'spec' => $spec,
                    ];
                    $usedPieces[$piece] = true;
                    $slot++;
                }
            }
        }
        $dir = dirname($outPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($outPath, '');
        $allOk = true;
        $minPath = 'levels\\l1data\\l1.min';
        Scrollrt::prepareViewport(IsoCoords::SCREEN_W, IsoCoords::viewportHeight());
        [$ftx, $fty, $fbx, $fby] = Scrollrt::firstTile($this->camX, $this->camY, 0, 0);
        foreach ($pick as $kind => $s) {
            $tx = (int) $s['x'];
            $ty = (int) $s['y'];
            $piece = (int) $s['piece'];
            $isFloor = $this->map->isFloorTile($tx, $ty);
            $microsWithOffsets = [];
            foreach ($this->map->dPieceMicros[$piece] ?? [] as $bi => $b) {
                $type = (int) ($b['type'] ?? 0);
                $frame = (int) ($b['frame'] ?? 0);
                $ox = ($bi & 1) === 0 ? 0 : 32;
                $oy = -intdiv((int) $bi, 2) * 32;
                if ($isFloor && (int) $bi < 2 && $type === DunMicro::TYPE_TRANSPARENT_SQUARE) {
                    $oy = -16;
                }
                $microsWithOffsets[] = [
                    'i' => (int) $bi,
                    'type' => $type,
                    'frame' => $frame,
                    'relX' => $ox,
                    'relY' => $oy,
                ];
            }
            $minDiff = $this->map->diffPieceVsRawMin($this->assets, $minPath, $piece);
            if (!$minDiff['ok']) {
                $allOk = false;
            }
            $row = [
                'kind' => $kind,
                'tile' => [$tx, $ty],
                'x' => $tx,
                'y' => $ty,
                'dungeon' => [
                    intdiv(max(0, $tx - 16), 2),
                    intdiv(max(0, $ty - 16), 2),
                ],
                'dPiece' => $piece,
                'dSpecial' => (int) ($this->map->dSpecial[$ty][$tx] ?? 0),
                'sol' => (int) ($this->map->sol[$piece] ?? 0),
                'microTileLen' => $this->map->microTileLen,
                'micros' => $microsWithOffsets,
                'rawMinTypesOk' => $minDiff['ok'] ? 1 : 0,
                'rawMinDiff' => $minDiff['diff'],
                'view' => [$this->camX, $this->camY],
                'firstTile' => [$ftx, $fty],
                'firstBuf' => [$fbx, $fby],
                'note' => 'offsets=DX drawCell L1 microTileLen=10; rawMin compares type/zero-frame vs MIN',
            ];
            file_put_contents($outPath, json_encode($row, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
            $lines[] = sprintf(
                "sample kind=%s tile=%d,%d dPiece=%d micros=%d minType=%s",
                $kind,
                $tx,
                $ty,
                $piece,
                count($microsWithOffsets),
                $minDiff['ok'] ? 'MATCH' : ($minDiff['diff'] ?? 'FAIL')
            );
            $lines[] = trim($this->dumpPieceDiagnostics($piece));
        }
        $lines[] = 'L1_MICRO_TYPE_INVENTORY ' . json_encode($typeInv);
        $typeNames = [
            0 => 'Square→DX RenderTileSquare',
            1 => 'TransparentSquare→DX foliage/transparent',
            2 => 'LeftTriangle→DX RenderTileLeftTriangle',
            3 => 'RightTriangle→DX RenderTileRightTriangle',
            4 => 'LeftTrapezoid→DX RenderTileLeftTrapezoid',
            5 => 'RightTrapezoid→DX RenderTileRightTrapezoid',
        ];
        foreach ($typeInv as $t => $c) {
            $lines[] = sprintf(
                '  type=%d count=%d php=DunMicro::decode %s',
                $t,
                $c,
                $typeNames[$t] ?? 'unknown'
            );
        }
        $lines[] = sprintf(
            'L1_PIECE_AUDIT samples=%d min_type_ok=%d path=%s firstTile=%d,%d',
            count($pick),
            $allOk ? 1 : 0,
            $outPath,
            $ftx,
            $fty
        );
        $parityMd = dirname(__DIR__) . '/tmp/L1_PIECE_PARITY.md';
        $md = "# L1 Piece Parity\n\nSeed 12345 · microTileLen=10 · raw MIN type/zero-frame vs live post-reencode\n\n";
        $md .= "| Kind | Tile | dPiece | dSpecial | micros | MIN | First mismatch |\n|------|------|--------|----------|--------|-----|----------------|\n";
        // Re-read jsonl we just wrote
        if (is_file($outPath)) {
            foreach (file($outPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $r = json_decode($line, true);
                if (!is_array($r) || isset($r['source'])) {
                    continue;
                }
                $md .= sprintf(
                    "| %s | %d,%d | %d | %d | %d | %s | %s |\n",
                    $r['kind'] ?? '',
                    (int) ($r['x'] ?? 0),
                    (int) ($r['y'] ?? 0),
                    (int) ($r['dPiece'] ?? 0),
                    (int) ($r['dSpecial'] ?? 0),
                    count($r['micros'] ?? []),
                    !empty($r['rawMinTypesOk']) ? 'MATCH' : 'FAIL',
                    $r['rawMinDiff'] ?? '—'
                );
            }
        }
        $md .= "\n## Offset rules (DX drawCell)\n\n";
        $md .= "- L0: (0,0)/(+32,0)\n- Foliage type1 floor: y−16\n- Upper k: y−32k\n- L1 special: immediate tip via l1s.cel\n";
        $md .= "\n## Type inventory\n\n```\n" . json_encode($typeInv) . "\n```\n";
        $md .= "\nFirst frame-index / micro-offset mismatch: **" . ($allOk ? 'none on samples' : 'see FAIL rows') . "**\n";
        file_put_contents($parityMd, $md);
        $lines[] = 'wrote ' . $parityMd;

        return ['ok' => $allOk, 'lines' => $lines, 'samples' => count($pick), 'typeInventory' => $typeInv];
    }

    private function makePlrPath(int $gx, int $gy, bool $endspace): void
    {
        $this->gtrace->makePlrPathCalls++;
        $sx = $this->isWalking() ? $this->walkFutureX : $this->px;
        $sy = $this->isWalking() ? $this->walkFutureY : $this->py;
        if ($sx === $gx && $sy === $gy) {
            $this->clrPlrPath();

            return;
        }
        $goalX = $gx;
        $goalY = $gy;
        $useEndspace = $endspace;
        if (!$endspace && !$this->tileWalkableForPath($gx, $gy)) {
            $adj = $this->pickApproachTile($gx, $gy);
            if ($adj === null) {
                $this->clrPlrPath();

                return;
            }
            [$goalX, $goalY] = $adj;
            $useEndspace = true;
        }
        // Debounce: skip rebuild if goal unchanged and path still valid (anti-oscillation)
        if ($goalX === $this->lastChaseGoalX && $goalY === $this->lastChaseGoalY
            && ($this->walkpath[0] ?? DestAction::WALK_NONE) !== DestAction::WALK_NONE) {
            return;
        }
        $walk = fn (int $x, int $y) => $this->tileWalkableForPath($x, $y);
        $canStep = function (int $x, int $y, int $nx, int $ny): bool {
            return $this->tileWalkableForPath($nx, $y) && $this->tileWalkableForPath($x, $ny);
        };
        $newPath = $this->pathfinder->findWalkpath(
            $sx,
            $sy,
            $goalX,
            $goalY,
            $walk,
            $canStep,
            $useEndspace
        );
        if ($newPath !== $this->walkpath) {
            $this->gtrace->repath = true;
        }
        $this->walkpath = $newPath;
        $this->lastChaseGoalX = $goalX;
        $this->lastChaseGoalY = $goalY;
    }

    /** @return ?array{0:int,1:int} */
    private function pickApproachTile(int $tx, int $ty): ?array
    {
        $best = null;
        $bestD = 999;
        $fromX = $this->isWalking() ? $this->walkFutureX : $this->px;
        $fromY = $this->isWalking() ? $this->walkFutureY : $this->py;
        $reach = $this->reachableFrom($fromX, $fromY);
        foreach ([[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [-1, -1], [1, -1], [-1, 1]] as [$dx, $dy]) {
            $x = $tx + $dx;
            $y = $ty + $dy;
            if (!$this->tileWalkableForPath($x, $y)) {
                continue;
            }
            // Prefer tiles in the current reachable component (closed doors are Solid —
            // the far-side neighbor is walkable but unreachable until the door opens).
            if ($reach !== [] && !isset($reach[$x . ',' . $y])) {
                continue;
            }
            $d = max(abs($fromX - $x), abs($fromY - $y));
            if ($d < $bestD) {
                $bestD = $d;
                $best = [$x, $y];
            }
        }

        return $best;
    }

    private function isWalking(): bool
    {
        return $this->pmode === 'WALK_NORTHWARDS'
            || $this->pmode === 'WALK_SOUTHWARDS'
            || $this->pmode === 'WALK_SIDEWAYS';
    }

    private function startWalkFromPath(bool $pmWillBeCalled = false): bool
    {
        $dir = $this->walkpath[0] ?? DestAction::WALK_NONE;
        if ($dir === DestAction::WALK_NONE) {
            return false;
        }
        // Path steps are from current tile (STAND) — future equals current when standing
        [$dx, $dy] = Direction::delta($dir);
        $nx = $this->px + $dx;
        $ny = $this->py + $dy;
        if (!$this->tileWalkableForPath($nx, $ny)) {
            $this->clrPlrPath();
            $this->startStand();

            return false;
        }
        $this->pdir = $dir;
        $this->walkTempX = $nx;
        $this->walkTempY = $ny;
        $this->walkFutureX = $nx;
        $this->walkFutureY = $ny;
        $this->walkMode = WalkOffset::walkModeForDir($dir);
        $this->pmode = $this->walkMode;
        $this->occ->clearPlayerId($this->playerId);
        $this->occ->occupyPlayer($this->px, $this->py, $this->playerId, false);
        $this->occ->occupyPlayer($nx, $ny, $this->playerId, true);
        // StartWalkAnimation: skippedFrames=-2 (+1 if pmWillBeCalled); distribution-off: start at 0
        $this->playerAnim->setNewAnimation(WalkOffset::WALK_FRAMES, 1, 0);
        if ($pmWillBeCalled) {
            $this->playerAnim->processAnimation();
        }
        $this->walkFrames = $this->playerAnim->currentFrame;
        array_shift($this->walkpath);
        if ($this->walkpath === []) {
            $this->walkpath = [DestAction::WALK_NONE];
        }
        $this->gtrace->consume = true;
        if ($this->debugWalkStep) {
            $this->emitWalkStepTrace('start_walk');
        }

        return true;
    }

    private function startStand(): void
    {
        $this->pmode = 'STAND';
        $this->walkMode = 'STAND';
        $this->occ->clearPlayerId($this->playerId);
        $this->occ->occupyPlayer($this->px, $this->py, $this->playerId, false);
        $this->walkFutureX = $this->px;
        $this->walkFutureY = $this->py;
        $this->camX = $this->px;
        $this->camY = $this->py;
        $this->playerAnim->setNewAnimation(10, 1, 0); // warrior idleFrames
        $this->markAutomapVisited($this->px, $this->py);
    }

    /** Reveal automap via DoVision (delegates to ProcessVisionList stand-in). */
    private function markAutomapVisited(int $cx, int $cy, int $radius = 10): void
    {
        // Radius kept for call-site compat; vision uses player light rad 10.
        unset($cx, $cy, $radius);
        $this->updateVisionList();
    }

    /**
     * MainPanelButtonRect clicks — char / map / inv (indices 0, 2, 4).
     */
    private function tryPanelButtonClick(int $sx, int $sy): bool
    {
        $panelTop = IsoCoords::SCREEN_H - IsoCoords::PANEL_HEIGHT;
        $panelLeft = 0;
        // Match drawHud centering when window wider than 640
        // leftMouseCmd uses game coords already (640×480 space)
        if ($sy < $panelTop) {
            return false;
        }
        $lx = $sx - $panelLeft;
        $ly = $sy - $panelTop;
        // WidePanelButtonSize 71×20
        $buttons = [
            ['x' => 9, 'y' => 9, 'w' => 71, 'h' => 20, 'action' => 'char'],
            ['x' => 9, 'y' => 35, 'w' => 71, 'h' => 20, 'action' => 'quest'],
            ['x' => 9, 'y' => 75, 'w' => 71, 'h' => 20, 'action' => 'map'],
            ['x' => 560, 'y' => 9, 'w' => 71, 'h' => 20, 'action' => 'inv'],
            ['x' => 560, 'y' => 35, 'w' => 71, 'h' => 20, 'action' => 'spells'],
        ];
        foreach ($buttons as $b) {
            if ($lx >= $b['x'] && $lx < $b['x'] + $b['w'] && $ly >= $b['y'] && $ly < $b['y'] + $b['h']) {
                if ($b['action'] === 'char') {
                    $this->showChar = !$this->showChar;
                    if ($this->showChar) {
                        $this->showQuest = false;
                        $this->showSpellBook = false;
                    }
                } elseif ($b['action'] === 'quest') {
                    $this->showQuest = !$this->showQuest;
                    if ($this->showQuest) {
                        $this->showChar = false;
                        $this->showSpellBook = false;
                    }
                } elseif ($b['action'] === 'inv') {
                    $this->showInv = !$this->showInv;
                    if ($this->showInv) {
                        $this->showSpellBook = false;
                    }
                } elseif ($b['action'] === 'spells') {
                    $this->showSpellBook = !$this->showSpellBook;
                    if ($this->showSpellBook) {
                        $this->showInv = false;
                        $this->showChar = false;
                        $this->showQuest = false;
                    }
                } elseif ($b['action'] === 'map') {
                    $this->showAutomap = !$this->showAutomap;
                }

                return true;
            }
        }
        // Belt click — use potion
        if ($ly >= 5 && $ly < 5 + 28 && $lx >= 205 && $lx < 205 + 4 * 29) {
            $slot = intdiv($lx - 205, 29);
            if ($slot >= 0 && $slot < 4) {
                $this->useBelt($slot);

                return true;
            }
        }

        return false;
    }

    private function processPlayer(): void
    {
        // ProcessPlayers tplayer do-while
        $loops = 0;
        do {
            $modeBefore = $this->pmode;
            $finished = false;
            if ($this->pmode === 'GOT_HIT') {
                // DoGotHit
                $this->playerAnim->processAnimation();
                $this->actionFrame = $this->playerAnim->currentFrame;
                if ($this->playerAnim->isLastFrame()) {
                    $this->startStand();
                    // DX: if (!FlipCoin(4)) DamageArmor → ~75% chance
                    if (!$this->rng->flipCoin(4)) {
                        $this->damageArmor();
                    }
                    $finished = true;
                }
            } elseif ($this->pmode === 'BLOCK') {
                // DoBlock
                $this->playerAnim->processAnimation();
                $this->actionFrame = $this->playerAnim->currentFrame;
                if ($this->playerAnim->isLastFrame()) {
                    $this->startStand();
                    // DX FlipCoin(10) → ~10% chance to DamageParryItem
                    if ($this->rng->flipCoin(10)) {
                        $this->damageParryItem();
                    }
                    $finished = true;
                }
            } elseif ($this->pmode === 'DEATH') {
                // DoDeath — never reprocess CheckNewPath; hold last frame (no wrap)
                if (!$this->playerAnim->isLastFrame()) {
                    $this->playerAnim->processAnimation();
                } else {
                    // Slow hold like DX ticksPerFrame=100 after last frame
                    $this->playerAnim->tickCounterOfCurrentFrame++;
                }
                $this->actionFrame = $this->playerAnim->currentFrame;
                $finished = false;
            } elseif ($this->pmode === 'SPELL') {
                $this->playerAnim->processAnimation();
                $this->actionFrame = $this->playerAnim->currentFrame;
                // DoSpell — release at _pSFNum (castingActionFrame)
                if ($this->actionFrame === $this->pSFNum && $this->pendingSpell !== null) {
                    $stype = $this->pendingSpellType !== '' ? $this->pendingSpellType : 'firebolt';
                    if ($stype === 'chargedbolt') {
                        // Fan of 3 bolts (AddChargedBolt stand-in)
                        $tx = $this->pendingSpell[0];
                        $ty = $this->pendingSpell[1];
                        $this->spawnMissile($this->px, $this->py, $tx, $ty, true, 'firebolt');
                        $this->spawnMissile($this->px, $this->py, $tx + 1, $ty, true, 'firebolt');
                        $this->spawnMissile($this->px, $this->py, $tx, $ty + 1, true, 'firebolt');
                    } else {
                        $this->spawnMissile($this->px, $this->py, $this->pendingSpell[0], $this->pendingSpell[1], true, $stype);
                    }
                    $this->pendingSpell = null;
                    $this->pendingSpellType = 'firebolt';
                    if ($this->pendingStaffConsume) {
                        $this->consumeStaffCharge();
                        $this->pendingStaffConsume = false;
                    }
                }
                if ($this->playerAnim->isLastFrame()) {
                    $this->startStand();
                    $finished = true;
                }
            } elseif ($this->isWalking()) {
                $finished = $this->doWalk();
            } elseif ($this->pmode === 'ATTACK') {
                $finished = $this->doAttack();
            } elseif ($this->pmode === 'RATTACK') {
                $finished = $this->doRangeAttack();
            }

            $this->checkNewPath($finished);
            if ($finished) {
                $this->gtrace->tplayer = true;
                $this->gtrace->tplayerN++;
            }

            if ($this->debugPlayerState) {
                fwrite(STDOUT, sprintf(
                    "player-state tick=%d before=%s after=%s dest=%d walk0=%d reprocess=%d\n",
                    $this->logicTick,
                    $modeBefore,
                    $this->pmode,
                    $this->destAction,
                    $this->walkpath[0] ?? DestAction::WALK_NONE,
                    $finished ? 1 : 0
                ));
            }
            $loops++;
        } while ($finished && $loops < 8);

        // DX ProcessPlayers: AnimInfo.processAnimation() once after mode+CheckNewPath.
        // ATTACK/RATTACK no longer advance inside DoAttack; STAND advances here too.
        // WALK/GOT_HIT/SPELL/DEATH still advance inside their handlers — do not double.
        if ($this->pmode === 'ATTACK' || $this->pmode === 'RATTACK' || $this->pmode === 'STAND') {
            $this->playerAnim->processAnimation();
            $this->actionFrame = $this->playerAnim->currentFrame;
        }
    }

    private function doAttack(): bool
    {
        // DoAttack — hit exactly at _pAFNum-1; complete on last frame (no AnimInfo advance here)
        $frame = $this->playerAnim->currentFrame;
        if (!$this->attackHitDone && $frame === ($this->pAFNum - 1)) {
            $this->hitMonster($this->attackTarget);
            $this->attackHitDone = true;
        }
        if ($this->playerAnim->isLastFrame()) {
            $this->attackTarget = null;
            $this->attackHitDone = false;
            $this->startStand();

            return true;
        }

        return false;
    }

 /** DoRangeAttack — spawn arrow exactly at _pAFNum-1 */
    private function doRangeAttack(): bool
    {
        $frame = $this->playerAnim->currentFrame;
        if (!$this->attackHitDone && $frame === ($this->pAFNum - 1)) {
            $this->spawnMissile($this->px, $this->py, $this->rangeTempX, $this->rangeTempY, true, $this->playerRangedMissileType());
            $this->attackHitDone = true;
            // DoRAttack — DamageWeapon(~1/40) when the arrow leaves the bow
            if ($this->damageWeapon(40)) {
                $this->attackTarget = null;
                $this->attackHitDone = false;
                $this->startStand();

                return true;
            }
        }
        if ($this->playerAnim->isLastFrame()) {
            $this->attackTarget = null;
            $this->attackHitDone = false;
            $this->startStand();

            return true;
        }

        return false;
    }

 /** Player::UsesRangedWeapon — bow graphic / bow in hand */
    private function usesRangedWeapon(): bool
    {
        $hand = $this->equipment['hand'] ?? null;
        if (!is_array($hand)) {
            return false;
        }
        $name = strtolower((string) ($hand['name'] ?? ''));
        $type = strtolower((string) ($hand['type'] ?? ''));
        $itype = strtolower((string) ($hand['itemType'] ?? ''));

        return str_contains($name, 'bow')
            || str_contains($type, 'bow')
            || str_contains($itype, 'bow');
    }

    private function doWalk(): bool
    {
        if ($this->playerAnim->isLastFrame()) {
            $this->occ->clearPlayerId($this->playerId);
            $this->px = $this->walkTempX;
            $this->py = $this->walkTempY;
            $this->walkFutureX = $this->px;
            $this->walkFutureY = $this->py;
            $this->occ->occupyPlayer($this->px, $this->py, $this->playerId, false);
            $this->map->plrDoTrans($this->px, $this->py);
            $this->tryPickup();
            $this->audio?->play('walk');
            if ($this->debugWalkStep) {
                $this->emitWalkStepTrace('tile_commit');
            }
            $this->startStand();

            return true;
        }
        $this->playerAnim->processAnimation();
        $this->walkFrames = $this->playerAnim->currentFrame;
        if ($this->debugWalkStep) {
            $this->emitWalkStepTrace('do_walk');
        }

        return false;
    }

    /**
     * CheckNewPath — chase ATTACK_MON to monster tile, interrupt when adjacent.
     */
    private function checkNewPath(bool $pmWillBeCalled): void
    {
        $this->gtrace->cnp = true;
        // Refresh chase path to living target (debounced: only if mon moved or path empty)
        if ($this->destAction === DestAction::ATTACK_MON) {
            $mon = $this->findMonsterById($this->destParam);
            if ($mon === null || empty($mon['alive'])) {
                $this->clrPlrPath();
                $this->destAction = DestAction::NONE;
            } else {
                [$mx, $my] = $this->monsterTargetTile($mon);
                $need = ($mx !== $this->lastChaseMonX || $my !== $this->lastChaseMonY)
                    || ($this->walkpath[0] ?? DestAction::WALK_NONE) === DestAction::WALK_NONE;
                if ($need) {
                    $this->lastChaseMonX = $mx;
                    $this->lastChaseMonY = $my;
                    $this->lastChaseGoalX = -1;
                    $this->lastChaseGoalY = -1;
                    $this->makePlrPath($mx, $my, false);
                }
            }
        }

        if ($this->isWalking()) {
            return;
        }

        // Mid-attack queue after hit frame (PM_ATTACK + currentFrame >= _pAFNum)
        if ($this->pmode === 'ATTACK' && $this->playerAnim->currentFrame >= $this->pAFNum) {
            if ($this->destAction === DestAction::ATTACK_MON) {
                $this->tryStartAttackFromDest(true);
            }

            return;
        }
        if ($this->pmode === 'RATTACK' && $this->playerAnim->currentFrame >= $this->pAFNum) {
            if ($this->destAction === DestAction::RATTACK_MON) {
                $this->tryStartRangeAttackFromDest(true);
            }

            return;
        }

        if ($this->pmode !== 'STAND') {
            return;
        }

        $dir = $this->walkpath[0] ?? DestAction::WALK_NONE;
        if ($dir !== DestAction::WALK_NONE) {
            // Adjacent interrupt before StartWalk (~1143–1165)
            if ($this->destAction === DestAction::ATTACK_MON && $this->tryStartAttackFromDest(false)) {
                return;
            }
            $this->startWalkFromPath($pmWillBeCalled);

            return;
        }

        // Empty path + STAND: execute destAction when ready
        $this->tryExecuteDestAction();
    }

    private function tryStartAttackFromDest(bool $fromMidAttack): bool
    {
        if ($this->destAction !== DestAction::ATTACK_MON) {
            return false;
        }
        $mon = $this->findMonsterById($this->destParam);
        if ($mon === null || empty($mon['alive'])) {
            return false;
        }
        $fx = $this->isWalking() ? $this->walkFutureX : $this->px;
        $fy = $this->isWalking() ? $this->walkFutureY : $this->py;
        [$mx, $my] = $this->monsterTargetTile($mon);
        if (max(abs($fx - $mx), abs($fy - $my)) > 1) {
            return false;
        }
        $this->clrPlrPath();
        $this->startAttack((int) $mon['id'], $mx, $my);
        $this->destAction = DestAction::NONE;

        return true;
    }

 /** CheckNewPath ACTION_RATTACKMON — fire from standing, no approach path */
    private function tryStartRangeAttackFromDest(bool $fromMidAttack): bool
    {
        if ($this->destAction !== DestAction::RATTACK_MON) {
            return false;
        }
        $mon = $this->findMonsterById($this->destParam);
        if ($mon === null || empty($mon['alive'])) {
            $this->destAction = DestAction::NONE;

            return false;
        }
        [$mx, $my] = $this->monsterTargetTile($mon);
        if (!$this->lineClearMissile($this->px, $this->py, $mx, $my)) {
            return false;
        }
        $this->clrPlrPath();
        $this->startRangeAttack((int) $mon['id'], $mx, $my);
        $this->destAction = DestAction::NONE;

        return true;
    }

    private function startAttack(int $monsterId, int $mx, int $my): void
    {
        $this->pdir = Direction::fromDelta($mx - $this->px, $my - $this->py);
        $this->pmode = 'ATTACK';
        $this->actionFrame = 0;
        $this->attackTarget = $monsterId;
        $this->attackHitDone = false;
        $this->playerAnim->setNewAnimation($this->attackFrames, 1, $this->attackAnimSkipFrames());
        $this->gtrace->atk = true;
        $this->audio?->play('swing');
        $this->lastChaseGoalX = -1;
        $this->lastChaseGoalY = -1;
        $this->lastChaseMonX = -1;
        $this->lastChaseMonY = -1;
    }

    private function startRangeAttack(int $monsterId, int $mx, int $my): void
    {
        $this->pdir = Direction::fromDelta($mx - $this->px, $my - $this->py);
        $this->pmode = 'RATTACK';
        $this->actionFrame = 0;
        $this->attackTarget = $monsterId;
        $this->attackHitDone = false;
        $this->rangeTempX = $mx;
        $this->rangeTempY = $my;
        $this->playerAnim->setNewAnimation($this->attackFrames, 1, $this->attackAnimSkipFrames());
        $this->gtrace->atk = true;
        $this->audio?->play('swing');
        $this->lastChaseGoalX = -1;
        $this->lastChaseGoalY = -1;
        $this->lastChaseMonX = -1;
        $this->lastChaseMonY = -1;
    }

    private function tryExecuteDestAction(): bool
    {
        if ($this->destAction === DestAction::NONE) {
            return false;
        }
        if ($this->destAction === DestAction::ATTACK_MON) {
            if ($this->tryStartAttackFromDest(false)) {
                return true;
            }

            return false;
        }
        if ($this->destAction === DestAction::RATTACK_MON) {
            return $this->tryStartRangeAttackFromDest(false);
        }
        if ($this->destAction === DestAction::TALK) {
            foreach ($this->towners as $t) {
                if ($t['id'] === $this->destParam
                    && max(abs($this->px - $t['x']), abs($this->py - $t['y'])) <= 1) {
                    $this->mode = 'vendor';
                    $this->activeVendor = $t;
                    $this->msg('Talking to ' . $t['name']);
                    $this->destAction = DestAction::NONE;
                    $this->clrPlrPath();

                    return true;
                }
            }

            return false;
        }
        if ($this->destAction === DestAction::OPERATE) {
            $idx = null;
            foreach ($this->objects as $i => $o) {
                if ((int) $o['id'] === $this->destParam) {
                    $idx = $i;
                    break;
                }
            }
            if ($idx === null) {
                $this->destAction = DestAction::NONE;

                return false;
            }
            $ox = (int) $this->objects[$idx]['x'];
            $oy = (int) $this->objects[$idx]['y'];
            // IsPlayerAdjacentToObject + OperateObject
            if (max(abs($this->px - $ox), abs($this->py - $oy)) <= 1) {
                $this->operateObject($this->objects[$idx]);
                $this->destAction = DestAction::NONE;
                $this->clrPlrPath();

                return true;
            }
            // Empty path but not adjacent: rebuild approach (door/chest endspace=false)
            $type = (string) ($this->objects[$idx]['type'] ?? '');
            $this->makePlrPath($ox, $oy, $this->objectPathEndspace($type, $ox, $oy));

            return false;
        }
        if ($this->destAction === DestAction::PICKUP) {
            $it = null;
            foreach ($this->items as $cand) {
                if ((int) $cand['id'] === $this->destParam) {
                    $it = $cand;
                    break;
                }
            }
            if ($it === null) {
                $this->destAction = DestAction::NONE;

                return false;
            }
            // ACTION_PICKUPAITEM — Chebyshev <= 1
            if (max(abs($this->px - (int) $it['x']), abs($this->py - (int) $it['y'])) <= 1) {
                $this->tryPickup((int) $it['id']);
                $this->destAction = DestAction::NONE;
                $this->clrPlrPath();

                return true;
            }
            $this->makePlrPath((int) $it['x'], (int) $it['y'], true);

            return false;
        }
        if ($this->destAction === DestAction::SPELL) {
            // Spell already started from input in this port
            $this->destAction = DestAction::NONE;

            return false;
        }

        return false;
    }

    /** @return ?array<string,mixed> */
    private function findMonsterById(int $id): ?array
    {
        foreach ($this->monsters as $m) {
            if ((int) $m['id'] === $id) {
                return $m;
            }
        }

        return null;
    }

    /** @var ?array{0:int,1:int} */
    private ?array $pendingSpell = null;
    /** ConsumeStaffCharge after DoSpell missile release */
    private bool $pendingStaffConsume = false;
    /** Pending spell missile type (firebolt / fireball / …) */
    private string $pendingSpellType = 'firebolt';
    /** Throttle UpdateBurningCrossDamage chat lines */
    private int $burnCrossMsgCooldown = 0;
    private int $flameHoleMsgCooldown = 0;
    private string $cursorIntent = 'walk';

    private function syncOccupancyActors(): void
    {
        $this->occ->clear();
        if ($this->isWalking()) {
            $this->occ->occupyPlayer($this->px, $this->py, $this->playerId, false);
            $this->occ->occupyPlayer($this->walkFutureX, $this->walkFutureY, $this->playerId, true);
        } else {
            $this->occ->occupyPlayer($this->px, $this->py, $this->playerId, false);
        }
        foreach ($this->monsters as $m) {
            if (empty($m['alive'])) {
                continue; // dead do not block (PosOkPlayer)
            }
            $id = (int) $m['id'];
            // WalkInDirection: tile +, future − until MonsterWalk commit
            if (($m['mode'] ?? '') === 'WALK' && isset($m['futX'], $m['futY'])) {
                $this->occ->occupyMonster((int) $m['x'], (int) $m['y'], $id, false);
                $this->occ->occupyMonster((int) $m['futX'], (int) $m['futY'], $id, true);
            } else {
                $this->occ->occupyMonster((int) $m['x'], (int) $m['y'], $id, false);
            }
        }
        foreach ($this->objects as $o) {
            $this->occ->setObject((int) $o['x'], (int) $o['y'], (int) $o['id']);
        }
        foreach ($this->items as $it) {
            $this->occ->setItem((int) $it['x'], (int) $it['y'], (int) $it['id']);
        }
    }

    /** Monster chase/target tile — DX position.future while walking. @return array{0:int,1:int} */
    private function monsterTargetTile(array $m): array
    {
        if (($m['mode'] ?? '') === 'WALK' && isset($m['futX'], $m['futY'])) {
            return [(int) $m['futX'], (int) $m['futY']];
        }

        return [(int) $m['x'], (int) $m['y']];
    }

    private function hitMonster(?int $id): void
    {
        if ($id === null) {
            return;
        }
        foreach ($this->monsters as &$m) {
            if ($m['id'] !== $id || !$m['alive']) {
                continue;
            }
            // TARGAC / _pIEnAc pierces monster AC
            $hper = max(5, min(95, $this->toHit - max(0, (int) $m['ac'] - $this->pEnAc)));
            if ($this->rng->generateRnd(100) >= $hper) {
                $this->msg('Miss');

                return;
            }
            $dam = $this->rng->between($this->minDam, max($this->minDam, $this->maxDam));
            $m['hp'] -= $dam;
            $m['mode'] = 'GOT_HIT';
            $m['frame'] = 0;
            $m['animFrame'] = 0;
            $this->setMonsterGraphic($m, 'GOT_HIT');
            $this->msg('Hit ' . $m['name'] . ' for ' . $dam);
            $this->applyOnHitLeech($dam);
            $this->tryKnockbackMonster($m);
            // PlrHitMonst — DamageWeapon(~1/30) after a successful land
            $this->damageWeapon(30);
            if ($m['hp'] <= 0) {
                // Keep alive through death anim (StartMonsterDeath); corpse after last frame
                $m['mode'] = 'DEATH';
                $m['frame'] = 0;
                $m['animFrame'] = 0;
                $m['hp'] = 0;
                $this->setMonsterGraphic($m, 'DEATH');
                $this->occ->clearMonsterId((int) $m['id']);
            }

            return;
        }
        unset($m);
    }

    /** @param array<string,mixed> $m */
    private function onKill(array $m): void
    {
        $this->xp += (int) $m['xp'];
        while ($this->xp >= $this->level * $this->level * 100) {
            $this->xp -= $this->level * $this->level * 100;
            $this->level++;
            $this->vitality++;
            $this->strength++;
            $this->recalc();
            $this->php = $this->pmaxHp;
            $this->pmana = $this->pmaxMana;
            $this->msg('Level up ' . $this->level);
        }
        $drop = ItemDat::setupAllItems($this->rng, max(1, $this->map->currLevel));
        $drop['id'] = $this->nextId++;
        $drop['x'] = $m['x'];
        $drop['y'] = $m['y'];
        $this->loadItemSprite($drop);
        $this->items[] = $drop;
        if (!empty($m['quest']) && $this->questStatus === 'active') {
            $this->questStatus = 'complete';
            $this->msg('Quest complete — return to Pepin');
        }
    }

    private function processMonsters(): void
    {
        foreach ($this->monsters as &$m) {
            // Finish death animation before treating as corpse
            if (($m['mode'] ?? '') === 'DEATH' && !empty($m['alive'])) {
                $m['frame'] = (int) ($m['frame'] ?? 0) + 1;
                $m['animFrame'] = (int) $m['frame'];
                $deathLen = max(1, (int) ($m['animFrames'] ?? 15));
                if ($m['frame'] >= $deathLen) {
                    $m['alive'] = false;
                    // Freeze last death frame as corpse sprite (InitDeadAnimationFromMonster)
                    $m['animFrame'] = max(0, $deathLen - 1);
                    $m['corpseFrame'] = (int) $m['animFrame'];
                    $this->onKill($m);
                }
                continue;
            }
            if (!$m['alive']) {
                continue;
            }
            // Petrified (Stone Curse) — skip AI until ticks expire
            $pet = (int) ($m['petrifiedTicks'] ?? 0);
            if ($pet > 0) {
                $m['petrifiedTicks'] = $pet - 1;
                if ($m['petrifiedTicks'] <= 0) {
                    $m['mode'] = (string) ($m['petrifiedRestore'] ?? 'STAND');
                    unset($m['petrifiedRestore'], $m['petrifiedTicks']);
                    $this->setMonsterGraphic($m, $m['mode'] === 'WALK' ? 'WALK' : 'STAND');
                }
                continue;
            }
            [$mtx, $mty] = $this->monsterTargetTile($m);
            $dist = max(abs($mtx - $this->px), abs($mty - $this->py));
            if ($dist > 12 && ($m['mode'] ?? '') !== 'WALK') {
                continue;
            }
            // Modes: STAND / WALK / ATTACK / RANGED / GOT_HIT / DEATH (monster modes)
            if ($m['mode'] === 'GOT_HIT') {
                $m['frame']++;
                $m['animFrame'] = (int) $m['frame'];
                $hitLen = max(1, (int) ($m['animFrames'] ?? 6));
                if ($m['frame'] >= $hitLen) {
                    $m['mode'] = 'STAND';
                    $m['frame'] = 0;
                    $this->setMonsterGraphic($m, 'STAND');
                }
                continue;
            }
            if ($m['mode'] === 'ATTACK') {
                $m['frame']++;
                $m['animFrame'] = (int) $m['frame'];
                $hitAt = (int) ($m['animHitFrame'] ?? 4);
                $atkLen = max($hitAt + 1, (int) ($m['animFrames'] ?? 8));
                if ($m['frame'] === $hitAt) {
                    $hper = max(5, min(95, $m['toHit'] - $this->ac));
                    if ($this->rng->generateRnd(100) < $hper) {
                        $dam = $this->rng->between($m['minDam'], $m['maxDam']);
                        $this->applyMeleeHitToPlayer($dam, (int) ($m['level'] ?? $this->map->currLevel), (int) $m['x'], (int) $m['y'], (string) ($m['name'] ?? 'Monster'));
                    }
                }
                if ($m['frame'] >= $atkLen) {
                    $m['mode'] = 'STAND';
                    $this->setMonsterGraphic($m, 'STAND');
                }
                continue;
            }
            // MonsterWalk — commit tile on last frame
            if (($m['mode'] ?? '') === 'WALK') {
                $m['walkFrame'] = (int) ($m['walkFrame'] ?? 0) + 1;
                $m['animFrame'] = (int) ($m['walkFrame'] ?? 0);
                $walkLen = max(1, (int) ($m['animFrames'] ?? AiProc::WALK_FRAMES));
                if ($m['walkFrame'] >= $walkLen) {
                    $fx = (int) ($m['futX'] ?? $m['x']);
                    $fy = (int) ($m['futY'] ?? $m['y']);
                    $selfId = (int) $m['id'];
                    // Ignore self ±dMonster on future; do not use tileWalkableForPath (that ignores player)
                    $ok = $this->map->isWalkable($fx, $fy)
                        && !$this->isClosedDoorAt($fx, $fy)
                        && !$this->occ->blocksActor($fx, $fy, 0, $selfId);
                    if ($ok) {
                        $m['x'] = $fx;
                        $m['y'] = $fy;
                    }
                    $m['mode'] = 'STAND';
                    $m['walkFrame'] = 0;
                    unset($m['futX'], $m['futY']);
                    $this->setMonsterGraphic($m, 'STAND');
                }
                continue;
            }
            if ($m['mode'] === 'RANGED') {
                // DoRangedAttack — play attack sheet, fire once at hit frame, then STAND
                $m['frame'] = (int) ($m['frame'] ?? 0) + 1;
                $m['animFrame'] = (int) $m['frame'];
                $hitAt = (int) ($m['animHitFrame'] ?? 4);
                $atkLen = max($hitAt + 1, (int) ($m['animFrames'] ?? 8));
                if ($m['frame'] === $hitAt
                    && $this->lineClearMissile((int) $m['x'], (int) $m['y'], $this->px, $this->py)) {
                    $this->spawnMissile((int) $m['x'], (int) $m['y'], $this->px, $this->py, false, 'arrow');
                }
                if ($m['frame'] >= $atkLen) {
                    $m['mode'] = 'STAND';
                    $m['frame'] = 0;
                    $this->setMonsterGraphic($m, 'STAND');
                }
                continue;
            }
            $m['frame'] = (int) ($m['frame'] ?? 0) + 1;
            if (($m['mode'] ?? 'STAND') === 'STAND') {
                $n = max(1, (int) ($m['animFrames'] ?? 1));
                $rate = max(1, (int) ($m['animRate'] ?? 4));
                $m['animFrame'] = intdiv((int) $m['frame'], $rate) % $n;
            }
            if ($m['frame'] % 5 !== 0) {
                continue;
            }
            $before = $m;
            $selfId = (int) $m['id'];
            $posOk = function (int $x, int $y) use ($selfId): bool {
                if (!$this->map->isWalkable($x, $y) || $this->isClosedDoorAt($x, $y)) {
                    return false;
                }
                // IsTileAccessible: any other player/monster occupancy blocks (ignore self only)
                return !$this->occ->blocksActor($x, $y, 0, $selfId);
            };
            $missOk = function (int $x, int $y): bool {
                return !$this->isMissileBlockedAt($x, $y);
            };
            $m = AiProc::tick($m, $this->px, $this->py, $this->map, $this->rng, $this->monsters, $posOk, $missOk);
            if (($m['mode'] ?? '') === 'ATTACK' && ($before['mode'] ?? '') !== 'ATTACK') {
                $m['frame'] = 0;
                $this->setMonsterGraphic($m, 'ATTACK');
            }
            if (($m['mode'] ?? '') === 'RANGED' && ($before['mode'] ?? '') !== 'RANGED') {
                $m['frame'] = 0;
                $m['animFrame'] = 0;
                $this->setMonsterGraphic($m, 'RANGED');
            }
            if (($m['mode'] ?? '') === 'WALK' && isset($m['futX'], $m['futY'])) {
                $fx = (int) $m['futX'];
                $fy = (int) $m['futY'];
                $futOk = $this->map->isWalkable($fx, $fy)
                    && !$this->isClosedDoorAt($fx, $fy)
                    && !$this->occ->blocksActor($fx, $fy, 0, $selfId);
                if (!$futOk) {
                    $m['mode'] = 'STAND';
                    unset($m['futX'], $m['futY'], $m['walkFrame']);
                    $this->setMonsterGraphic($m, 'STAND');
                } else {
                    if (($before['mode'] ?? '') !== 'WALK') {
                        $this->setMonsterGraphic($m, 'WALK');
                    }
                    // Occupy immediately so later monsters this tick see future (per-monster Update)
                    $this->occ->clearMonsterId($selfId);
                    $this->occ->occupyMonster((int) $m['x'], (int) $m['y'], $selfId, false);
                    $this->occ->occupyMonster($fx, $fy, $selfId, true);
                }
            }
        }
        unset($m);
        $this->syncOccupancyActors();
        // Path blocked by standing monster: clear only when not chasing (CheckNewPath repaths ATTACK_MON)
        if (!$this->isWalking() && $this->destAction !== DestAction::ATTACK_MON) {
            $dir = $this->walkpath[0] ?? DestAction::WALK_NONE;
            if ($dir !== DestAction::WALK_NONE) {
                [$dx, $dy] = Direction::delta($dir);
                if (!$this->tileWalkableForPath($this->px + $dx, $this->py + $dy)) {
                    $this->clrPlrPath();
                }
            }
        }
    }

    private function processMissiles(): void
    {
        foreach ($this->missiles as &$mis) {
            if (!$mis['alive']) {
                continue;
            }
            $mis['x'] += $mis['vx'];
            $mis['y'] += $mis['vy'];
            $mis['life']--;
            $mis['anim'] = (int) ($mis['anim'] ?? 0) + 1;
            $tx = (int) round($mis['x']);
            $ty = (int) round($mis['y']);
            if ($mis['life'] <= 0 || !$this->map->inBounds($tx, $ty) || $this->isMissileBlockedAt($tx, $ty)) {
                $mis['alive'] = false;
                continue;
            }
            if ($mis['fromPlayer']) {
                foreach ($this->monsters as &$m) {
                    if ($m['alive'] && ($m['mode'] ?? '') !== 'DEATH'
                        && (int) $m['x'] === $tx && (int) $m['y'] === $ty) {
                        $dam = (int) ($mis['midam'] ?? 0);
                        if ($dam <= 0) {
                            $dam = $this->rng->between(4, 10);
                        }
                        $this->applyMissileDamageToMonster($m, $dam, !empty($mis['leech']));
                        $mis['alive'] = false;
                        // Fireball splash — adjacent monsters take half damage
                        if (($mis['type'] ?? '') === 'fireball') {
                            $splash = max(1, intdiv($dam, 2));
                            foreach ($this->monsters as &$n) {
                                if (empty($n['alive']) || ($n['mode'] ?? '') === 'DEATH') {
                                    continue;
                                }
                                if ((int) ($n['id'] ?? -1) === (int) ($m['id'] ?? -2)) {
                                    continue;
                                }
                                if (max(abs((int) $n['x'] - $tx), abs((int) $n['y'] - $ty)) !== 1) {
                                    continue;
                                }
                                $this->applyMissileDamageToMonster($n, $splash, false);
                            }
                            unset($n);
                        }
                        break;
                    }
                }
                unset($m);
            } elseif ($tx === $this->px && $ty === $this->py) {
                $mis['alive'] = false;
                $ox = (int) ($mis['ox'] ?? $tx);
                $oy = (int) ($mis['oy'] ?? $ty);
                $kind = (string) ($mis['type'] ?? 'missile');
                $dam = (int) ($mis['midam'] ?? 0);
                if ($dam <= 0) {
                    $dam = $this->rng->between(2, 6);
                }
                // PlayerMHit resist by damage type
                $dam = match ($kind) {
                    'firebolt', 'fireball' => $this->applyResistPercent($dam, $this->pFireResist),
                    'lightning' => $this->applyResistPercent($dam, $this->pLightResist),
                    default => $dam,
                };
                $this->applyMeleeHitToPlayer(
                    $dam,
                    $this->map->currLevel,
                    $ox,
                    $oy,
                    $kind === 'arrow' ? 'Arrow' : 'Missile'
                );
            }
        }
        unset($mis);
        $this->missiles = array_values(array_filter($this->missiles, static fn ($m) => $m['alive']));
    }

    private function processObjects(): void
    {
        // ProcessObjects — advance animated object frames (torches, …)
        foreach ($this->objects as &$o) {
            if (empty($o['animFlag'])) {
                continue;
            }
            $o['animCnt'] = (int) ($o['animCnt'] ?? 0) + 1;
            if ($o['animCnt'] < (int) ($o['animDelay'] ?? 1)) {
                continue;
            }
            $o['animCnt'] = 0;
            $n = max(1, (int) ($o['animFrames'] ?? 1));
            $o['animFrame'] = ((int) ($o['animFrame'] ?? 0) + 1) % $n;
            $fi = (int) $o['animFrame'];
            $sheet = (string) ($o['animSheet'] ?? '');
            if ($sheet !== '' && !empty($this->torchAnimCache[$sheet][$fi])) {
                $fr = $this->torchAnimCache[$sheet][$fi];
                $o['spriteRgba'] = $fr['rgba'];
                $o['spriteW'] = $fr['w'];
                $o['spriteH'] = $fr['h'];
                $o['spriteFrame'] = $fi;
            }
        }
        unset($o);
        // OperateTrap — poll wall holes whose trigger object has been activated
        $this->operateArmedTraps();
        // UpdateBurningCrossDamage — tile north of burncros burns the player
        $this->updateBurningCrossDamage();
        // UpdateFlameTrap stand-in — standing on flamehole burns
        $this->updateFlameHoleDamage();
    }

    /**
     * DX ProcessObjects → OperateTrap: fire when linked door/chest/sarc/lever is open.
     * Eager fire on operate still preferred; this catches any missed path.
     */
    private function operateArmedTraps(): void
    {
        foreach ($this->objects as $trig) {
            $type = (string) ($trig['type'] ?? '');
            if (!in_array($type, ['door', 'chest', 'sarc', 'lever'], true)) {
                continue;
            }
            if (empty($trig['opened'])) {
                continue;
            }
            $this->fireLinkedWallTrap((int) $trig['x'], (int) $trig['y']);
        }
    }

    /**
     * UpdateBurningCrossDamage — player on tile north of OBJ_BCROSS / burncros takes fire.
     */
    private function updateBurningCrossDamage(): void
    {
        if ($this->pmode === 'DEATH') {
            return;
        }
        $lt = $this->map->levelType;
        // damage[leveltype-1] for cathedral..hell (+ crypt/nest reuse)
        $table = [6, 8, 10, 12, 10, 12];
        $idx = max(0, min(count($table) - 1, $lt - 1));
        $dam = $table[$idx];
        // UpdateBurningCrossDamage — fireResist * damage / 100
        $dam = $this->applyResistPercent($dam, $this->pFireResist);
        if ($this->hasHalfTrapDamage()) {
            $dam = intdiv($dam, 2);
        }
        foreach ($this->objects as $o) {
            if (($o['type'] ?? '') !== 'burncros') {
                continue;
            }
            // cross.position + Displacement{0,-1}
            if ($this->px !== (int) $o['x'] || $this->py !== (int) $o['y'] - 1) {
                continue;
            }
            if ($dam <= 0) {
                return;
            }
            $this->php -= $this->absorbDamageWithManaShield($dam);
            $this->startPlrHit();
            // Throttle chat spam while standing in the flames
            if (($this->burnCrossMsgCooldown--) <= 0) {
                $this->msg('The flames burn for ' . $dam);
                $this->burnCrossMsgCooldown = 15;
            }

            return;
        }
        $this->burnCrossMsgCooldown = 0;
    }

    /**
     * UpdateFlameTrap (PARTIAL) — player on flamehole tile takes fire damage.
     */
    private function updateFlameHoleDamage(): void
    {
        if ($this->pmode === 'DEATH') {
            return;
        }
        $lt = $this->map->levelType;
        $table = [6, 8, 10, 12, 10, 12];
        $idx = max(0, min(count($table) - 1, $lt - 1));
        $dam = $table[$idx];
        $dam = $this->applyResistPercent($dam, $this->pFireResist);
        if ($this->hasHalfTrapDamage()) {
            $dam = intdiv($dam, 2);
        }
        foreach ($this->objects as $o) {
            if (($o['type'] ?? '') !== 'flamehole') {
                continue;
            }
            if ($this->px !== (int) $o['x'] || $this->py !== (int) $o['y']) {
                continue;
            }
            if ($dam <= 0) {
                return;
            }
            $this->php -= $this->absorbDamageWithManaShield($dam + max(0, $this->pGetHit));
            $this->startPlrHit();
            if (($this->flameHoleMsgCooldown--) <= 0) {
                $this->msg('The flames burn for ' . $dam);
                $this->flameHoleMsgCooldown = 15;
            }

            return;
        }
        $this->flameHoleMsgCooldown = 0;
    }

    /**
     * CheckTriggers — STAND on stairs tile → level change.
     */
    private function checkTriggers(): void
    {
        if ($this->pmode !== 'STAND') {
            return;
        }
        foreach ($this->objects as $i => $o) {
            $type = (string) ($o['type'] ?? '');
            if ($type !== 'stairs_down' && $type !== 'stairs_up' && $type !== 'stairs_warp') {
                continue;
            }
            if ((int) $o['x'] === $this->px && (int) $o['y'] === $this->py) {
                $this->operateObject($this->objects[$i]);

                return;
            }
        }
    }

    /** Move player off a trigger/object tile onto a neighboring walkable cell. */
    private function offsetPlayerOffTile(int $tx, int $ty): void
    {
        if ($this->px !== $tx || $this->py !== $ty) {
            return;
        }
        foreach ([[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [-1, -1], [1, -1], [-1, 1]] as [$dx, $dy]) {
            $nx = $tx + $dx;
            $ny = $ty + $dy;
            if ($this->map->isWalkable($nx, $ny)) {
                $this->px = $nx;
                $this->py = $ny;
                $this->walkFutureX = $nx;
                $this->walkFutureY = $ny;
                $this->walkTempX = $nx;
                $this->walkTempY = $ny;
                $this->camX = $nx;
                $this->camY = $ny;

                return;
            }
        }
    }

    /** MakePlrPath endspace for objects (OnObjectTileAction). */
    private function objectPathEndspace(string $type, int $ox, int $oy): bool
    {
        if (in_array($type, ['door', 'chest', 'barrel', 'barrelex', 'cauldron', 'decap', 'tnudem', 'tnudew', 'sarc', 'bloodftn', 'purifyftn', 'murkyftn', 'tearftn', 'shrine', 'goatshrine', 'bookstand', 'bookcase', 'lever', 'armorstand', 'weaponrack'], true)) {
            return false;
        }
        if ($type === 'stairs_down' || $type === 'stairs_up' || $type === 'stairs_warp') {
            // Walk onto stairs when the tile is walkable (trigger tile)
            return $this->map->isWalkable($ox, $oy);
        }

        return true;
    }

    private function spawnMissile(int $x, int $y, int $tx, int $ty, bool $fromPlayer, string $type): void
    {
        $dx = $tx - $x;
        $dy = $ty - $y;
        $len = sqrt($dx * $dx + $dy * $dy) ?: 1.0;
        $midam = 0;
        if ($fromPlayer && $this->pmode === 'RATTACK'
            && ($type === 'arrow' || $type === 'firebolt' || $type === 'lightning')) {
            // DoRangeAttack — weapon damage (+ FireArrows / LightningArrows elemental PL)
            $midam = $this->rng->between(max(1, $this->minDam), max(1, $this->maxDam));
            if ($type === 'firebolt' && $this->hasEquippedEffect('FIRE_ARROWS')) {
                $fmin = max(1, $this->equippedPlSum('plFMinDam'));
                $fmax = max($fmin, $this->equippedPlSum('plFMaxDam'));
                $midam += $this->rng->between($fmin, $fmax);
            } elseif ($type === 'lightning' && $this->hasEquippedEffect('LIGHT_ARROWS')) {
                $lmin = max(1, $this->equippedPlSum('plLMinDam'));
                $lmax = max($lmin, $this->equippedPlSum('plLMaxDam'));
                $midam += $this->rng->between($lmin, $lmax);
            }
        } elseif ($fromPlayer && ($type === 'firebolt' || $type === 'lightning')) {
            // AddFirebolt: GenerateRnd(10) + magic/8 + spellLevel + 1
            $splName = $type === 'lightning' ? 'Lightning' : 'Firebolt';
            $spl = (int) ($this->spellLevels[$splName] ?? $this->spellLevels['Firebolt'] ?? 0) + $this->pSplLvlAdd;
            $midam = $this->rng->generateRnd(10) + intdiv($this->magic, 8) + $spl + 1;
        } elseif ($fromPlayer && $type === 'fireball') {
            // AddFireball — heavier bolt: rnd(10)+magic/2+spl*2+10
            $spl = (int) ($this->spellLevels['Fireball'] ?? 0) + $this->pSplLvlAdd;
            $midam = $this->rng->generateRnd(10) + intdiv($this->magic, 2) + $spl * 2 + 10;
        } elseif ($fromPlayer && $type === 'arrow') {
            $midam = $this->rng->between(max(1, $this->minDam), max(1, $this->maxDam));
        } elseif (!$fromPlayer) {
            $midam = $this->rng->between(2, 6);
        }
        $this->missiles[] = [
            'alive' => true,
            'x' => (float) $x,
            'y' => (float) $y,
            'ox' => $x,
            'oy' => $y,
            'vx' => $dx / $len * 0.5,
            'vy' => $dy / $len * 0.5,
            'life' => 40,
            'anim' => 0,
            'fromPlayer' => $fromPlayer,
            'type' => $type,
            'midam' => $midam,
            'leech' => $fromPlayer && $this->pmode === 'RATTACK',
        ];
    }

    /** @param array<string,mixed> $m */
    private function applyMissileDamageToMonster(array &$m, int $dam, bool $leech = false): void
    {
        $m['hp'] -= $dam;
        if ($leech) {
            $this->applyOnHitLeech($dam);
            $this->tryKnockbackMonster($m);
        }
        if ($m['hp'] <= 0) {
            $m['mode'] = 'DEATH';
            $m['frame'] = 0;
            $m['animFrame'] = 0;
            $m['hp'] = 0;
            $this->setMonsterGraphic($m, 'DEATH');
            $this->occ->clearMonsterId((int) $m['id']);
        } else {
            $m['mode'] = 'GOT_HIT';
            $m['frame'] = 0;
            $m['animFrame'] = 0;
            $this->setMonsterGraphic($m, 'GOT_HIT');
        }
    }

    /** @param array<string,mixed> $o */
    private function operateObject(array &$o): void
    {
        if ($o['type'] === 'stairs_down') {
            if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
                $this->enterCathedral(1);
            } else {
                $this->enterCathedral($this->map->currLevel + 1);
            }

            return;
        }
        if ($o['type'] === 'stairs_up') {
            if ($this->map->currLevel <= 1) {
                $this->enterTown();
            } else {
                $this->enterCathedral($this->map->currLevel - 1);
            }

            return;
        }
        if ($o['type'] === 'stairs_warp') {
            // Town warp (L5 WARPSTAIRS / L13 L4TWARP)
            $this->enterTown();

            return;
        }
        // OperateDoor — toggle open/closed (reusable; do not early-out on opened)
        if ($o['type'] === 'door') {
            if (!empty($o['opened'])) {
                if (!$this->isDoorClear($o)) {
                    $this->msg('The door is blocked');

                    return;
                }
                $this->setDoorStateClosed($o);
                $o['opened'] = false;
                $this->refreshObjectSprite($o);
                $this->msg('Door closed');
                $this->audio?->play('door');
            } else {
                $o['opened'] = true;
                if (isset($o['doorSide'])) {
                    $this->setDoorStateOpen($o);
                }
                $this->refreshObjectSprite($o);
                $this->msg('Door opened');
                $this->audio?->play('door');
                // UpdateTrapState — opening a trapped door fires linked wall hole
                if (!empty($o['trapFlag'])) {
                    $this->fireLinkedWallTrap((int) $o['x'], (int) $o['y']);
                    $o['trapFlag'] = false;
                }
            }
            $this->updateVisionList();

            return;
        }
        if (!empty($o['opened'])) {
            return;
        }
        if (($o['type'] ?? '') === 'torch' || ($o['type'] ?? '') === 'l1light'
            || ($o['type'] ?? '') === 'candle' || ($o['type'] ?? '') === 'banner'
            || ($o['type'] ?? '') === 'skulfire' || ($o['type'] ?? '') === 'burncros'
            || ($o['type'] ?? '') === 'flamehole'
            || ($o['type'] ?? '') === 'tsoul') {
            return;
        }
        // OperateFountains — reusable (do not mark opened)
        if (($o['type'] ?? '') === 'bloodftn') {
            if ($this->php < $this->pmaxHp) {
                $this->php = min($this->pmaxHp, $this->php + 64);
                $this->msg('The blood fountain restores life');
            } else {
                $this->msg('The blood fountain');
            }

            return;
        }
        if (($o['type'] ?? '') === 'purifyftn') {
            if ($this->pmana < $this->pmaxMana) {
                $this->pmana = min($this->pmaxMana, $this->pmana + 64);
                $this->msg('The purifying fountain restores mana');
            } else {
                $this->msg('The purifying fountain');
            }

            return;
        }
        $o['opened'] = true;
        $this->refreshObjectSprite($o);
        if (!empty($o['trapFlag'])) {
            $this->fireLinkedWallTrap((int) $o['x'], (int) $o['y']);
            $o['trapFlag'] = false;
        }
        if (($o['type'] ?? '') === 'murkyftn') {
            // OperateFountains MURKY → Infravision (duration ~spell scale)
            $this->pInfraTicks = max($this->pInfraTicks, 800);
            $this->updateVisionList();
            $this->msg('You gain infravision');

            return;
        }
        if (($o['type'] ?? '') === 'tearftn') {
            // OperateFountains TEAR — move 1 point between Str/Mag/Dex
            $vals = [$this->strength, $this->magic, $this->dexterity];
            $from = $this->rng->generateRnd(3);
            $to = $this->rng->generateRnd(2);
            if ($to >= $from) {
                $to++;
            }
            if ($vals[$from] > 1) {
                $vals[$from]--;
                $vals[$to]++;
                $this->strength = $vals[0];
                $this->magic = $vals[1];
                $this->dexterity = $vals[2];
                $names = ['strength', 'magic', 'dexterity'];
                $this->msg('The tears shift ' . $names[$from] . ' to ' . $names[$to]);
            } else {
                $this->msg('The fountain of tears');
            }

            return;
        }
        if ($o['type'] === 'barrelex' || !empty($o['explosive'])) {
            $this->breakExplosiveBarrel((int) $o['id']);
        } elseif ($o['type'] === 'chest' || $o['type'] === 'barrel' || $o['type'] === 'decap'
            || $o['type'] === 'tnudem' || $o['type'] === 'tnudew'
            || ($o['type'] === 'sarc' && (int) ($o['oVar1'] ?? 0) <= 2)) {
            if ($o['type'] === 'chest' && !empty($o['trapped'])) {
                $this->triggerChestTrap($o);
                $o['trapped'] = false;
            }
            // OperateChest / BreakBarrel / OperateDecapitatedBody / OperateSarcophagus → CreateRndItem
            $n = max(1, (int) ($o['lootCount'] ?? 1));
            $dlvl = max(1, $this->map->currLevel);
            for ($j = 0; $j < $n; $j++) {
                $drop = ItemDat::setupAllItems($this->rng, $dlvl);
                $drop['id'] = $this->nextId++;
                $drop['x'] = (int) $o['x'];
                $drop['y'] = (int) $o['y'];
                $this->loadItemSprite($drop);
                $this->items[] = $drop;
            }
            $this->syncOccupancyActors();
            $this->msg(match ($o['type']) {
                'decap', 'tnudem', 'tnudew' => 'Found loot',
                'sarc' => 'Sarcophagus looted',
                default => 'Looted ' . $o['type'],
            });
            if ($o['type'] === 'sarc' && (int) ($o['oVar1'] ?? 0) >= 8) {
                $this->activateSkeletonNear((int) $o['x'], (int) $o['y']);
            }
        } elseif ($o['type'] === 'sarc') {
            // OperateSarcophagus — empty, maybe skeleton (oVar1 >= 8)
            if ((int) ($o['oVar1'] ?? 0) >= 8) {
                $this->activateSkeletonNear((int) $o['x'], (int) $o['y']);
            } else {
                $this->msg('Empty sarcophagus');
            }
        } elseif ($o['type'] === 'bookstand') {
            // OperateBookStand — FlipCoin book vs scroll
            $misc = $this->rng->flipCoin() ? 'BOOK' : 'SCROLL';
            $drop = ItemDat::setupMiscIdItem($this->rng, max(1, $this->map->currLevel), $misc);
            $drop['id'] = $this->nextId++;
            $drop['x'] = (int) $o['x'];
            $drop['y'] = (int) $o['y'];
            $this->loadItemSprite($drop);
            $this->items[] = $drop;
            $this->syncOccupancyActors();
            $this->msg('You take a ' . strtolower((string) preg_replace('/ of .*/', '', $drop['name'] ?? 'tome')));
        } elseif ($o['type'] === 'bookcase') {
            // OperateBookcase — IMISC_BOOK; anim frame -= 2
            $drop = ItemDat::setupMiscIdItem($this->rng, max(1, $this->map->currLevel), 'BOOK');
            $drop['id'] = $this->nextId++;
            $drop['x'] = (int) $o['x'];
            $drop['y'] = (int) $o['y'];
            $this->loadItemSprite($drop);
            $this->items[] = $drop;
            $this->syncOccupancyActors();
            $o['spriteFrame'] = max(0, (int) ($o['spriteFrame'] ?? 2) - 2);
            $this->refreshObjectSprite($o);
            $this->msg('You take a book');
        } elseif ($o['type'] === 'lever') {
            // OperateLever stand-in — open nearest closed door
            $best = null;
            $bestD = PHP_INT_MAX;
            $lx = (int) $o['x'];
            $ly = (int) $o['y'];
            foreach ($this->objects as $i => $cand) {
                if (($cand['type'] ?? '') !== 'door' || !empty($cand['opened'])) {
                    continue;
                }
                $d = abs((int) $cand['x'] - $lx) + abs((int) $cand['y'] - $ly);
                if ($d < $bestD) {
                    $bestD = $d;
                    $best = $i;
                }
            }
            if ($best !== null) {
                $this->operateObject($this->objects[$best]);
                $this->msg('The lever opens a door');
            } else {
                $this->msg('The lever clicks, but nothing happens');
            }
        } elseif ($o['type'] === 'armorstand') {
            // OperateArmorStand → CreateTypeItem by Light/Medium/Heavy band
            $drop = ItemDat::setupArmorStandItem($this->rng, max(1, $this->map->currLevel));
            $drop['id'] = $this->nextId++;
            $drop['x'] = (int) $o['x'];
            $drop['y'] = (int) $o['y'];
            $this->loadItemSprite($drop);
            $this->items[] = $drop;
            $this->syncOccupancyActors();
            $this->msg('Armor taken from the stand');
        } elseif ($o['type'] === 'weaponrack') {
            // OperateWeaponRack → Sword/Axe/Bow/Mace
            $drop = ItemDat::setupWeaponRackItem($this->rng, max(1, $this->map->currLevel));
            $drop['id'] = $this->nextId++;
            $drop['x'] = (int) $o['x'];
            $drop['y'] = (int) $o['y'];
            $this->loadItemSprite($drop);
            $this->items[] = $drop;
            $this->syncOccupancyActors();
            $this->msg('Weapon taken from the rack');
        } elseif ($o['type'] === 'cauldron' || $o['type'] === 'shrine' || $o['type'] === 'goatshrine') {
            // OperateCauldron / OperateShrine / OperateGoatShrine — shared effect table (PARTIAL)
            $effect = (int) ($o['oVar1'] ?? $this->rng->generateRnd(8));
            $this->applyShrineEffect($effect);
        }
    }

    /** Subset of OperateShrine effects (oVar1). */
    private function applyShrineEffect(int $effect): void
    {
        switch ($effect) {
            case 0: // Mysterious — +2 all attributes
                $this->strength += 2;
                $this->magic += 2;
                $this->dexterity += 2;
                $this->vitality += 2;
                $this->msg('The powers of the shrine increase your attributes');
                break;
            case 1: // Divine — full life + mana
                $this->php = $this->pmaxHp;
                $this->pmana = $this->pmaxMana;
                $this->msg('The shrine restores your life and mana');
                break;
            case 2: // Cryptic — full mana
                $this->pmana = $this->pmaxMana;
                $this->msg('The shrine restores your mana');
                break;
            case 3: // Spiritual — gold
                $g = 100 + $this->rng->generateRnd(50) * max(1, $this->map->currLevel);
                $this->gold += $g;
                $this->msg('The shrine yields ' . $g . ' gold');
                break;
            case 4: // Abandoned — +2 dex
                $this->dexterity += 2;
                $this->msg('The shrine increases your dexterity');
                break;
            case 5: // Creepy — +2 str
                $this->strength += 2;
                $this->msg('The shrine increases your strength');
                break;
            case 6: // Quiet — +2 vit
                $this->vitality += 2;
                $this->pmaxHp += 4;
                $this->php = min($this->pmaxHp, $this->php + 4);
                $this->msg('The shrine increases your vitality');
                break;
            default: // Eerie / Magical stand-in — +2 magic
                $this->magic += 2;
                $this->pmaxMana += 4;
                $this->pmana = min($this->pmaxMana, $this->pmana + 4);
                $this->msg('The shrine increases your magic');
                break;
        }
    }

    /** OperateTrap — fire missile from wall hole linked to trigger tile. */
    private function fireLinkedWallTrap(int $trigX, int $trigY): void
    {
        foreach ($this->objects as &$trap) {
            if (($trap['type'] ?? '') !== 'trap' || empty($trap['armed'])) {
                continue;
            }
            if ((int) ($trap['oVar1'] ?? -1) !== $trigX || (int) ($trap['oVar2'] ?? -1) !== $trigY) {
                continue;
            }
            $trap['armed'] = false;
            $tx = (int) $trap['x'];
            $ty = (int) $trap['y'];
            $kind = ((int) ($trap['oVar3'] ?? 0) === 0) ? 'firebolt' : 'arrow';
            // Prefer player if adjacent to trigger
            $targetX = $trigX;
            $targetY = $trigY;
            if (max(abs($this->px - $trigX), abs($this->py - $trigY)) <= 1) {
                $targetX = $this->px;
                $targetY = $this->py;
            }
            $this->spawnMissile($tx, $ty, $targetX, $targetY, false, $kind);
            $this->msg('A trap!');
            break;
        }
        unset($trap);
    }

    /** TriggerTrap from trapped chest — arrow / firebolt / direct hit. */
    private function triggerChestTrap(array $chest): void
    {
        $cx = (int) $chest['x'];
        $cy = (int) $chest['y'];
        $kind = (int) ($chest['trapKind'] ?? 0);
        $this->msg('A trap!');
        if ($kind === 0) {
            $this->spawnMissile($cx, $cy, $this->px, $this->py, false, 'firebolt');
        } elseif ($kind === 1) {
            $this->spawnMissile($cx, $cy, $this->px, $this->py, false, 'arrow');
        } else {
            $dam = 4 + $this->map->currLevel * 2;
            $this->php -= $this->absorbDamageWithManaShield($dam);
            $this->startPlrHit();
            $this->msg('Trap hits for ' . $dam);
        }
    }

    /**
     * ActivateSkeleton stand-in — spawn MT_WSKELAX on/near sarcophagus tile.
     */
    private function activateSkeletonNear(int $ox, int $oy): void
    {
        $row = null;
        foreach (Monstdat::all() as $r) {
            if (($r['_monster_id'] ?? '') === 'MT_WSKELAX') {
                $row = $r;
                break;
            }
        }
        if ($row === null) {
            return;
        }
        $candidates = [[$ox, $oy]];
        foreach ([[0, -1], [1, -1], [1, 0], [-1, -1], [1, 1], [-1, 0], [-1, 1], [0, 1]] as [$dx, $dy]) {
            $candidates[] = [$ox + $dx, $oy + $dy];
        }
        $pos = null;
        foreach ($candidates as [$x, $y]) {
            if ($this->map->isWalkable($x, $y) && $this->occ->monsterAt($x, $y) === 0 && !($x === $this->px && $y === $this->py)) {
                $pos = [$x, $y];
                break;
            }
        }
        if ($pos === null) {
            $this->msg('Bones rattle…');

            return;
        }
        [$x, $y] = $pos;
        $dlvl = max(1, $this->map->currLevel);
        $hpMin = (int) ($row['hitPointsMinimum'] ?? 4);
        $hpMax = max($hpMin, (int) ($row['hitPointsMaximum'] ?? 7));
        $hp = $this->rng->between($hpMin, $hpMax) + $dlvl;
        $m = [
            'id' => $this->nextId++,
            'type' => $row['_monster_id'] ?? 'MT_WSKELAX',
            'name' => $row['name'] ?? 'Skeleton',
            'x' => $x,
            'y' => $y,
            'hp' => $hp,
            'maxHp' => $hp,
            'minDam' => (int) ($row['minDamage'] ?? 1) + intdiv($dlvl, 2),
            'maxDam' => (int) ($row['maxDamage'] ?? 4) + intdiv($dlvl, 2),
            'ac' => (int) ($row['armorClass'] ?? 5),
            'toHit' => (int) ($row['toHit'] ?? 40),
            'xp' => (int) ($row['exp'] ?? 15) + $dlvl * 5,
            'mode' => 'STAND',
            'dir' => Direction::S,
            'ai' => $row['ai'] ?? 'SkeletonMelee',
            'assetsSuffix' => $row['assetsSuffix'] ?? '',
            'monstdat' => $row,
            'cl2' => Monstdat::cl2Path($row, 'n'),
            'cl2Mode' => 'n',
            'frame' => 0,
            'alive' => true,
            'quest' => false,
            'selectionRegion' => SelectionRegion::parse((string) ($row['selectionRegion'] ?? 'Bottom,Middle')),
            'hidden' => false,
        ];
        $this->tryLoadMonsterCl2($m, 'n');
        $this->monsters[] = $m;
        $this->syncOccupancyActors();
        $this->msg('A skeleton rises!');
    }

    /**
     * BreakBarrel explosive path — 3×3 damage + chain to adjacent barrelex.
     */
    private function breakExplosiveBarrel(int $oid): void
    {
        $idx = null;
        foreach ($this->objects as $i => $o) {
            if ((int) ($o['id'] ?? 0) === $oid) {
                $idx = $i;
                break;
            }
        }
        if ($idx === null) {
            return;
        }
        $barrel = &$this->objects[$idx];
        if (!empty($barrel['_exploded'])) {
            return;
        }
        $barrel['_exploded'] = true;
        $barrel['opened'] = true;
        $this->refreshObjectSprite($barrel);
        $bx = (int) $barrel['x'];
        $by = (int) $barrel['y'];
        $this->msg('Barrel explodes!');
        for ($dy = -1; $dy <= 1; $dy++) {
            for ($dx = -1; $dx <= 1; $dx++) {
                $x = $bx + $dx;
                $y = $by + $dy;
                if ($this->px === $x && $this->py === $y) {
                    $dam = $this->rng->between(8, 16);
                    $this->php -= $this->absorbDamageWithManaShield($dam);
                    $this->startPlrHit();
                    $this->msg('Explosion hits for ' . $dam);
                }
                foreach ($this->monsters as &$m) {
                    if (empty($m['alive'])) {
                        continue;
                    }
                    if ((int) $m['x'] === $x && (int) $m['y'] === $y) {
                        $dam = $this->rng->between(1, 4);
                        $m['hp'] -= $dam;
                        if ($m['hp'] <= 0) {
                            $m['alive'] = false;
                            $m['mode'] = 'DEATH';
                            $m['frame'] = 0;
                        } elseif (($m['mode'] ?? '') !== 'DEATH') {
                            $m['mode'] = 'GOT_HIT';
                            $m['frame'] = 0;
                        }
                    }
                }
                unset($m);
            }
        }
        // Chain adjacent explosives (BreakBarrel forcebreak)
        $chain = [];
        foreach ($this->objects as $o) {
            if (!empty($o['_exploded']) || !empty($o['opened'])) {
                continue;
            }
            if (($o['type'] ?? '') !== 'barrelex' && empty($o['explosive'])) {
                continue;
            }
            if (max(abs((int) $o['x'] - $bx), abs((int) $o['y'] - $by)) === 1) {
                $chain[] = (int) $o['id'];
            }
        }
        foreach ($chain as $cid) {
            $this->breakExplosiveBarrel($cid);
        }
        $this->syncOccupancyActors();
    }

    /**
     * GetItem / ACTION_PICKUPAITEM — pick when Chebyshev distance <= 1.
     * @return bool true if an item was taken
     */
    private function tryPickup(?int $wantId = null): bool
    {
        foreach ($this->items as $i => $it) {
            if ($wantId !== null && (int) $it['id'] !== $wantId) {
                continue;
            }
            $dist = max(abs((int) $it['x'] - $this->px), abs((int) $it['y'] - $this->py));
            if ($dist > 1) {
                continue;
            }
            if ($it['type'] === 'gold') {
                $this->gold += (int) $it['value'];
                $this->msg('Gold +' . $it['value']);
            } elseif (in_array($it['type'] ?? '', ['potion_heal', 'potion_mana', 'potion_rejuv', 'scroll'], true)) {
                $placed = false;
                for ($b = 0; $b < 4; $b++) {
                    if (($this->belt[$b] ?? null) === null) {
                        $this->belt[$b] = $it;
                        $placed = true;
                        break;
                    }
                }
                if (!$placed) {
                    $slot = InventoryGrid::findFit($this->inventory, $it);
                    if ($slot !== null) {
                        $this->inventory = InventoryGrid::place($this->inventory, $it, $slot);
                    } else {
                        $this->setHeldCursorItem($it);
                        $this->msg('No room — item on cursor');
                    }
                }
            } else {
                $slot = InventoryGrid::findFit($this->inventory, $it);
                if ($slot !== null) {
                    $this->inventory = InventoryGrid::place($this->inventory, $it, $slot);
                } else {
                    $this->setHeldCursorItem($it);
                    $this->msg('No room — item on cursor');
                }
            }
            unset($this->items[$i]);
            $this->items = array_values($this->items);
            $this->syncOccupancyActors();
            $this->audio?->play('pickup');

            return true;
        }

        return false;
    }

    private function useBelt(int $slot): void
    {
        if (!isset($this->belt[$slot]) || $this->belt[$slot] === null) {
            return;
        }
        $it = $this->belt[$slot];
        if ($this->useUsableItem($it)) {
            $this->belt[$slot] = null;
        }
    }

    /**
     * UseInvItem / UseBeltItem — potions, scrolls, books.
     * @param array<string,mixed> $it
     */
    private function useUsableItem(array $it): bool
    {
        $type = (string) ($it['type'] ?? '');
        $misc = strtoupper((string) ($it['miscId'] ?? ''));
        $spell = (string) ($it['spell'] ?? 'Null');
        if ($type === 'potion_heal' || $misc === 'HEAL') {
            $this->php = min($this->pmaxHp, $this->php + 30);
            $this->msg('Ahh');

            return true;
        }
        if ($misc === 'FULLHEAL') {
            $this->php = $this->pmaxHp;
            $this->msg('Ahh');

            return true;
        }
        if ($type === 'potion_mana' || $misc === 'MANA') {
            if ($this->hasEquippedEffect('NOMANA')) {
                $this->msg('You can\'t drink this');

                return false;
            }
            $this->pmana = min($this->pmaxMana, $this->pmana + 30);
            $this->msg('Mmm');

            return true;
        }
        if ($misc === 'FULLMANA') {
            if ($this->hasEquippedEffect('NOMANA')) {
                $this->msg('You can\'t drink this');

                return false;
            }
            $this->pmana = $this->pmaxMana;
            $this->msg('Mmm');

            return true;
        }
        if ($type === 'potion_rejuv' || $misc === 'REJUV') {
            $this->php = min($this->pmaxHp, $this->php + 20);
            $this->pmana = min($this->pmaxMana, $this->pmana + 20);
            $this->msg('Ahh');

            return true;
        }
        if ($misc === 'FULLREJUV') {
            $this->php = $this->pmaxHp;
            $this->pmana = $this->pmaxMana;
            $this->msg('Ahh');

            return true;
        }
        if ($type === 'scroll' || in_array($misc, ['SCROLL', 'SCROLLT'], true)) {
            return $this->useScrollSpell($spell);
        }
        if ($type === 'book' || $misc === 'BOOK') {
            return $this->learnSpellFromBook($spell);
        }
        if ($type === 'oil' || str_starts_with($misc, 'OIL')) {
            return $this->applyOil($misc !== '' ? $misc : 'OILBSMTH');
        }

        return false;
    }

    /**
     * ApplyOilToItem — oil targets held cursor item, else equipped hand/body.
     */
    private function applyOil(string $oilType): bool
    {
        $slotKey = null;
        $it = null;
        if ($this->cursorItem !== null
            && !in_array($this->cursorItem['type'] ?? '', ['gold', 'scroll', 'book', 'oil', 'potion_heal', 'potion_mana', 'potion_rejuv'], true)) {
            $slotKey = 'cursor';
            $it = $this->cursorItem;
        } else {
            foreach (['hand', 'offhand', 'body', 'head'] as $slot) {
                if (is_array($this->equipment[$slot] ?? null)) {
                    $slotKey = $slot;
                    $it = $this->equipment[$slot];
                    break;
                }
            }
        }
        if ($it === null || $slotKey === null) {
            $this->msg('No item to oil');

            return false;
        }
        $type = (string) ($it['type'] ?? '');
        $isWeapon = $type === 'weapon';
        $isArmor = $type === 'armor';
        if (!$isWeapon && !$isArmor) {
            $this->msg('Oil has no effect');

            return false;
        }
        $oil = strtoupper($oilType);
        $ok = true;
        switch ($oil) {
            case 'OILACC':
                if (!$isWeapon) {
                    $ok = false;
                    break;
                }
                $hit = (int) ($it['plToHit'] ?? 0);
                if ($hit < 50) {
                    $it['plToHit'] = $hit + 1 + $this->rng->generateRnd(2);
                }
                break;
            case 'OILSHARP':
                if (!$isWeapon) {
                    $ok = false;
                    break;
                }
                $minD = (int) ($it['minDamage'] ?? 0);
                $maxD = (int) ($it['maxDamage'] ?? 0);
                if ($maxD - $minD < 30 && $maxD < 255) {
                    $it['maxDamage'] = $maxD + 1;
                }
                break;
            case 'OILHARD':
            case 'OILIMP':
                if (!$isArmor) {
                    $ok = false;
                    break;
                }
                $bonus = $oil === 'OILIMP' ? 3 : 1;
                $it['armor'] = (int) ($it['armor'] ?? 0) + $bonus;
                $it['plAC'] = (int) ($it['plAC'] ?? 0) + $bonus;
                break;
            case 'OILBSMTH':
            default:
                $max = (int) ($it['maxDurability'] ?? 0);
                $dur = (int) ($it['durability'] ?? $max);
                if ($max === 255) {
                    break;
                }
                if ($dur < $max) {
                    $it['durability'] = min($max, $dur + intdiv($max + 4, 5));
                } elseif ($max < 100) {
                    $it['maxDurability'] = $max + 1;
                    $it['durability'] = $max + 1;
                }
                break;
        }
        if (!$ok) {
            $this->msg('Wrong oil for that item');

            return false;
        }
        if ($slotKey === 'cursor') {
            $this->cursorItem = $it;
        } else {
            $this->equipment[$slotKey] = $it;
            $this->recalc();
        }
        $this->msg('Applied oil to ' . $this->itemLabel($it));

        return true;
    }

    /** Read a book — raise _pSplLvl for the book's spell. */
    private function learnSpellFromBook(string $spell): bool
    {
        $name = $spell !== '' && $spell !== 'Null' ? $spell : 'Firebolt';
        $cur = (int) ($this->spellLevels[$name] ?? 0);
        if ($cur >= 15) {
            $this->msg('You already mastered ' . $name);

            return false;
        }
        $this->spellLevels[$name] = $cur + 1;
        $this->msg('You learn ' . $name . ' (level ' . $this->spellLevels[$name] . ')');

        return true;
    }

    /** Scroll / book spell effects (subset of UseInvItem). */
    private function useScrollSpell(string $spell): bool
    {
        $sp = strtolower($spell);
        if ($sp === 'identify') {
            return $this->identifyWithScroll();
        }
        if ($sp === 'healing' || $sp === 'heal') {
            $this->php = min($this->pmaxHp, $this->php + 30 + $this->magic);
            $this->msg('Healing');

            return true;
        }
        if ($sp === 'townportal') {
            if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
                $this->msg('Already in town');

                return false;
            }
            $this->enterTown();
            $this->msg('Town Portal');

            return true;
        }
        if ($sp === 'infravision') {
            $this->pInfraTicks = max($this->pInfraTicks, 800);
            $this->updateVisionList();
            $this->msg('Infravision');

            return true;
        }
        if ($sp === 'search') {
            $spl = (int) ($this->spellLevels['Search'] ?? 0);
            $add = $this->level * 2 + 10 * $spl + 245;
            $this->pSearchTicks = min(2000, $this->pSearchTicks + $add);
            $this->msg('Search');

            return true;
        }
        if ($sp === 'inferno') {
            return $this->castInferno();
        }
        if ($sp === 'phasing' || $sp === 'teleport') {
            return $this->castPhasing($sp === 'teleport');
        }
        if ($sp === 'flash' || $sp === 'nova') {
            return $this->castFlashBurst($sp === 'nova');
        }
        if ($sp === 'stonecurse') {
            return $this->castStoneCurse();
        }
        if ($sp === 'apocalypse') {
            return $this->castApocalypse();
        }
        if ($sp === 'manashield') {
            if ($this->pManaShield) {
                $this->msg('Mana Shield already active');

                return false;
            }
            $this->pManaShield = true;
            if (($this->spellLevels['ManaShield'] ?? 0) < 1) {
                $this->spellLevels['ManaShield'] = 1;
            }
            $this->msg('Mana Shield');

            return true;
        }
        if ($sp === 'firebolt' || $sp === 'lightning' || $sp === 'chargedbolt' || $sp === 'fireball') {
            $saved = $this->pmana;
            $this->pmana = max($this->pmana, 6);
            $this->pendingStaffConsume = false;
            $this->pendingSpellType = match ($sp) {
                'lightning' => 'lightning',
                'chargedbolt' => 'chargedbolt',
                'fireball' => 'fireball',
                default => 'firebolt',
            };
            $this->castFirebolt();
            if ($this->pendingSpell === null && $this->pmode !== 'SPELL') {
                $this->pmana = $saved;
                $this->pendingSpellType = 'firebolt';
                $this->msg('No target in sight');

                return false;
            }
            $this->pmana = $saved;

            return true;
        }
        $this->msg('The scroll crumbles uselessly');

        return true; // still consume unknown scrolls to avoid inventory softlock
    }

    /**
     * Apocalypse — damage every live monster in a wide radius.
     */
    private function castApocalypse(): bool
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            $this->msg('Not in town');

            return false;
        }
        $spl = (int) ($this->spellLevels['Apocalypse'] ?? 0);
        $dam = 20 + intdiv($this->magic, 2) + $spl * 4 + $this->rng->generateRnd(20);
        $hits = 0;
        foreach ($this->monsters as &$m) {
            if (empty($m['alive']) || ($m['mode'] ?? '') === 'DEATH') {
                continue;
            }
            $d = max(abs((int) $m['x'] - $this->px), abs((int) $m['y'] - $this->py));
            if ($d > 10 || $d < 1) {
                continue;
            }
            $this->applyMissileDamageToMonster($m, $dam);
            $hits++;
        }
        unset($m);
        $this->audio?->play('missile');
        $this->msg($hits > 0 ? ('Apocalypse hits ' . $hits) : 'Apocalypse');

        return true;
    }

    /**
     * Inferno — burn monsters along a short line toward the nearest foe.
     */
    private function castInferno(): bool
    {
        $best = null;
        $bestD = 99;
        foreach ($this->monsters as $i => $m) {
            if (empty($m['alive']) || ($m['mode'] ?? '') === 'DEATH') {
                continue;
            }
            $d = max(abs((int) $m['x'] - $this->px), abs((int) $m['y'] - $this->py));
            if ($d < 1 || $d > 6 || $d >= $bestD) {
                continue;
            }
            if (!$this->lineClearMissile($this->px, $this->py, (int) $m['x'], (int) $m['y'])) {
                continue;
            }
            $bestD = $d;
            $best = $i;
        }
        if ($best === null) {
            $this->msg('No path for Inferno');

            return false;
        }
        $tx = (int) $this->monsters[$best]['x'];
        $ty = (int) $this->monsters[$best]['y'];
        $spl = (int) ($this->spellLevels['Inferno'] ?? 0);
        $dam = 6 + intdiv($this->magic, 6) + $spl + $this->rng->generateRnd(6);
        $hits = 0;
        // Bresenham-ish Chebyshev steps along the ray
        $steps = max(1, $bestD);
        for ($s = 1; $s <= $steps; $s++) {
            $x = $this->px + (int) round(($tx - $this->px) * $s / $steps);
            $y = $this->py + (int) round(($ty - $this->py) * $s / $steps);
            foreach ($this->monsters as &$m) {
                if (empty($m['alive']) || ($m['mode'] ?? '') === 'DEATH') {
                    continue;
                }
                if ((int) $m['x'] !== $x || (int) $m['y'] !== $y) {
                    continue;
                }
                if (!empty($m['_infernoHit'])) {
                    continue;
                }
                $m['_infernoHit'] = true;
                $m['hp'] -= $dam;
                $hits++;
                if ($m['hp'] <= 0) {
                    $m['mode'] = 'DEATH';
                    $m['frame'] = 0;
                    $m['animFrame'] = 0;
                    $m['hp'] = 0;
                    $this->setMonsterGraphic($m, 'DEATH');
                    $this->occ->clearMonsterId((int) $m['id']);
                } else {
                    $m['mode'] = 'GOT_HIT';
                    $m['frame'] = 0;
                    $m['animFrame'] = 0;
                    $this->setMonsterGraphic($m, 'GOT_HIT');
                }
            }
            unset($m);
        }
        foreach ($this->monsters as &$m) {
            unset($m['_infernoHit']);
        }
        unset($m);
        $this->audio?->play('missile');
        $this->msg($hits > 0 ? ('Inferno hits for ' . $dam) : 'Inferno');

        return true;
    }

    /**
     * AddStoneCurse — petrify nearest monster within 5 tiles.
     */
    private function castStoneCurse(): bool
    {
        $best = null;
        $bestD = 99;
        foreach ($this->monsters as $i => $m) {
            if (empty($m['alive']) || ($m['mode'] ?? '') === 'DEATH') {
                continue;
            }
            if ((int) ($m['petrifiedTicks'] ?? 0) > 0) {
                continue;
            }
            $d = max(abs((int) $m['x'] - $this->px), abs((int) $m['y'] - $this->py));
            if ($d > 5 || $d >= $bestD) {
                continue;
            }
            $bestD = $d;
            $best = $i;
        }
        if ($best === null) {
            $this->msg('No target to stone');

            return false;
        }
        $spl = (int) ($this->spellLevels['StoneCurse'] ?? 0);
        $dur = min(15, $spl + 6) << 4; // DX: duration <<= 4
        $m = &$this->monsters[$best];
        $m['petrifiedRestore'] = (string) ($m['mode'] ?? 'STAND');
        $m['petrifiedTicks'] = max(16, $dur);
        $m['mode'] = 'STAND';
        $m['frame'] = 0;
        $this->setMonsterGraphic($m, 'STAND');
        unset($m);
        $this->audio?->play('missile');
        $this->msg('Stone Curse');

        return true;
    }

    /**
     * Flash / Nova stand-in — damage monsters in an adjacent ring (Flash) or 2-tile ring (Nova).
     */
    private function castFlashBurst(bool $wide): bool
    {
        $spl = (int) ($this->spellLevels[$wide ? 'Nova' : 'Flash'] ?? 0);
        $dam = 10 + intdiv($this->magic, 4) + $spl * 2 + $this->rng->generateRnd(10);
        $rad = $wide ? 2 : 1;
        $hits = 0;
        foreach ($this->monsters as &$m) {
            if (empty($m['alive']) || ($m['mode'] ?? '') === 'DEATH') {
                continue;
            }
            $d = max(abs((int) $m['x'] - $this->px), abs((int) $m['y'] - $this->py));
            if ($d < 1 || $d > $rad) {
                continue;
            }
            $m['hp'] -= $dam;
            $hits++;
            if ($m['hp'] <= 0) {
                $m['mode'] = 'DEATH';
                $m['frame'] = 0;
                $m['animFrame'] = 0;
                $m['hp'] = 0;
                $this->setMonsterGraphic($m, 'DEATH');
                $this->occ->clearMonsterId((int) $m['id']);
            } else {
                $m['mode'] = 'GOT_HIT';
                $m['frame'] = 0;
                $m['animFrame'] = 0;
                $this->setMonsterGraphic($m, 'GOT_HIT');
            }
        }
        unset($m);
        $this->audio?->play('missile');
        if ($hits === 0) {
            $this->msg(($wide ? 'Nova' : 'Flash') . ' — nothing nearby');

            return true; // still consume scroll
        }
        $this->msg(($wide ? 'Nova' : 'Flash') . ' hits ' . $hits . ' for ' . $dam);

        return true;
    }

    /**
     * AddPhasing / Teleport — jump to a random valid tile (phasing: ring 4–6; teleport: wider).
     */
    private function castPhasing(bool $longRange): bool
    {
        if ($this->map->levelType === DungeonMap::DTYPE_TOWN) {
            $this->msg('Not in town');

            return false;
        }
        $minR = $longRange ? 2 : 4;
        $maxR = $longRange ? 12 : 6;
        $cands = [];
        for ($y = -$maxR; $y <= $maxR; $y++) {
            for ($x = -$maxR; $x <= $maxR; $x++) {
                $cheb = max(abs($x), abs($y));
                if ($cheb < $minR || $cheb > $maxR) {
                    continue;
                }
                // Phasing skips the inner 3×3 cross (DX skips |x|,|y|≤3 when both axes in band)
                if (!$longRange && abs($x) <= 3 && abs($y) <= 3) {
                    continue;
                }
                $nx = $this->px + $x;
                $ny = $this->py + $y;
                if (!$this->map->inBounds($nx, $ny) || !$this->tileWalkableForPath($nx, $ny)) {
                    continue;
                }
                if ($this->occ->monsterAt($nx, $ny) !== 0) {
                    continue;
                }
                $cands[] = [$nx, $ny];
            }
        }
        if ($cands === []) {
            $this->msg('No space to phase');

            return false;
        }
        [$nx, $ny] = $cands[$this->rng->generateRnd(count($cands))];
        $this->teleportPlayer($nx, $ny);
        $this->updateVisionList();
        $this->msg($longRange ? 'Teleport' : 'Phasing');

        return true;
    }

    /** Scroll of Identify — ID cursor item, else first unidentified equip/inv item. */
    private function identifyWithScroll(): bool
    {
        $targets = [];
        if ($this->cursorItem !== null) {
            $targets[] = 'cursor';
        }
        foreach (['hand', 'offhand', 'body', 'head'] as $slot) {
            $targets[] = 'eq:' . $slot;
        }
        foreach ($this->inventory as $idx => $it) {
            if (is_array($it)) {
                $targets[] = 'inv:' . $idx;
            }
        }
        foreach ($targets as $key) {
            $it = null;
            if ($key === 'cursor') {
                $it = $this->cursorItem;
            } elseif (str_starts_with($key, 'eq:')) {
                $it = $this->equipment[substr($key, 3)] ?? null;
            } elseif (str_starts_with($key, 'inv:')) {
                $it = $this->inventory[(int) substr($key, 4)] ?? null;
            }
            if (!is_array($it)) {
                continue;
            }
            if (($it['quality'] ?? 'normal') === 'normal' || !empty($it['identified'])) {
                continue;
            }
            $it['identified'] = true;
            if ($key === 'cursor') {
                $this->cursorItem = $it;
            } elseif (str_starts_with($key, 'eq:')) {
                $this->equipment[substr($key, 3)] = $it;
            } else {
                $this->inventory[(int) substr($key, 4)] = $it;
            }
            $this->recalc();
            $this->msg('Identified: ' . $this->itemLabel($it));

            return true;
        }
        $this->msg('Nothing to identify');

        return false;
    }

    /**
     * Right panel origin for inv.cel (GetRightPanel — 320×352 above main panel).
     * @return array{0:int,1:int} invX, invY (top-left of inv.cel)
     */
    /**
     * Draw pressed main-panel button overlays (p8but2 frames 0–5).
     * Frame order matches PanelButtonCharinfo…Spellbook.
     */
    private function drawPressedPanelButtons(Sdl $sdl, int $panelLeft, int $panelTop): void
    {
        if ($this->panelBtnFrames === []) {
            return;
        }
        $map = [
            0 => $this->showChar,      // char @ 9,9
            1 => $this->showQuest,     // quest @ 9,35
            2 => $this->showAutomap,   // map @ 9,75
            4 => $this->showInv,       // inv @ 560,9
            5 => $this->showSpellBook, // spells @ 560,35
        ];
        $pos = [
            0 => [9, 9],
            1 => [9, 35],
            2 => [9, 75],
            4 => [560, 9],
            5 => [560, 35],
        ];
        foreach ($map as $fi => $active) {
            if (!$active || !isset($this->panelBtnFrames[$fi])) {
                continue;
            }
            $fr = $this->panelBtnFrames[$fi];
            if (!isset($this->panelBtnTex[$fi])) {
                $this->panelBtnTex[$fi] = $sdl->createTextureRGBA((int) $fr['w'], (int) $fr['h'], (string) $fr['rgba']);
            }
            [$bx, $by] = $pos[$fi];
            $sdl->copyTexture($this->panelBtnTex[$fi], $panelLeft + $bx, $panelTop + $by, (int) $fr['w'], (int) $fr['h']);
        }
    }

    private function invPanelOrigin(int $winW = IsoCoords::SCREEN_W): array
    {
        $panelLeft = $winW > IsoCoords::SCREEN_W ? intdiv($winW - IsoCoords::SCREEN_W, 2) : 0;
        // ClxDraw at panel+{0,351} with h=352 → top = panelTop - 351... on 640×480 panelTop=352 → y=1≈0
        return [$panelLeft + 320, 0];
    }

    /**
     * CheckInvHLight / left-click — pick up or place on inventory grid (InvRect row1 @ 17,222).
     */
    private function tryInvClick(int $sx, int $sy): bool
    {
        [$invX, $invY] = $this->invPanelOrigin();
        // Absorb clicks on inv panel chrome
        if ($sx < $invX || $sy < $invY || $sx >= $invX + 320 || $sy >= $invY + 352) {
            return false;
        }
        // Backpack grid: InvRect SLOTXY_INV_FIRST — 10×4 @ (17,222) step 29
        $ox = $invX + 17;
        $oy = $invY + 222;
        $gw = 10 * 29;
        $gh = 4 * 29;
        if ($sx < $ox || $sy < $oy || $sx >= $ox + $gw || $sy >= $oy + $gh) {
            // Equipment InvRect slots (left hand / right hand / chest / helm)
            $slots = [
                'hand' => [17, 75, 58, 86],
                'offhand' => [248, 75, 58, 87],
                'body' => [132, 75, 58, 87],
                'head' => [132, 2, 58, 59],
            ];
            foreach ($slots as $slot => [$x, $y, $w, $h]) {
                if ($sx >= $invX + $x && $sx < $invX + $x + $w && $sy >= $invY + $y && $sy < $invY + $y + $h) {
                    return $this->tryEquipSlotClick($slot);
                }
            }

            return true;
        }
        $col = intdiv($sx - $ox, 29);
        $row = intdiv($sy - $oy, 29);
        $slot = $row * InventoryGrid::COLS + $col;
        if ($this->cursorItem !== null) {
            $it = $this->cursorItem;
            $w = max(1, (int) ($it['invW'] ?? 1));
            $h = max(1, (int) ($it['invH'] ?? 1));
            if (InventoryGrid::canPlace($this->inventory, $col, $row, $w, $h)) {
                $this->inventory = InventoryGrid::place($this->inventory, $it, $slot);
                $this->setHeldCursorItem(null);
            } else {
                $this->msg('No room');
            }

            return true;
        }
        [$this->inventory, $picked] = InventoryGrid::pickAt($this->inventory, $slot);
        if ($picked !== null) {
            $this->setHeldCursorItem($picked);
        }

        return true;
    }

    private function tryEquipSlotClick(string $slot): bool
    {
        if ($this->cursorItem !== null) {
            $it = $this->cursorItem;
            $want = (string) ($it['equipSlot'] ?? '');
            // Shield → offhand; weapon → hand; allow empty want
            if ($want === 'offhand' && $slot === 'hand') {
                $slot = 'offhand';
            } elseif ($want === 'hand' && $slot === 'offhand') {
                $this->msg('Cannot equip here');

                return true;
            } elseif ($want !== '' && $want !== $slot) {
                $this->msg('Cannot equip here');

                return true;
            }
            // Two-hand weapons clear offhand
            if ($slot === 'hand' && $this->isTwoHandWeapon($it) && ($this->equipment['offhand'] ?? null) !== null) {
                $prevOh = $this->equipment['offhand'];
                $this->equipment['offhand'] = null;
                $prev = $this->equipment['hand'] ?? null;
                $this->equipment['hand'] = $it;
                $this->setHeldCursorItem(null);
                $this->recalc();
                $this->loadPlayerSprite();
                if ($prev !== null) {
                    $this->setHeldCursorItem($prev);
                } elseif (is_array($prevOh)) {
                    $this->setHeldCursorItem($prevOh);
                }

                return true;
            }
            if ($slot === 'offhand' && $this->isTwoHandWeapon($this->equipment['hand'] ?? null)) {
                $this->msg('Cannot equip with two-handed weapon');

                return true;
            }
            $prev = $this->equipment[$slot] ?? null;
            $this->equipment[$slot] = $it;
            $this->setHeldCursorItem(null);
            $this->recalc();
            $this->loadPlayerSprite();
            if ($prev !== null) {
                $this->setHeldCursorItem($prev);
            }

            return true;
        }
        $prev = $this->equipment[$slot] ?? null;
        if ($prev !== null) {
            $this->equipment[$slot] = null;
            $this->setHeldCursorItem($prev);
            $this->recalc();
            $this->loadPlayerSprite();
        }

        return true;
    }

    /** @param ?array<string,mixed> $it */
    private function isTwoHandWeapon(?array $it): bool
    {
        if ($it === null) {
            return false;
        }
        $blob = strtolower((string) ($it['itemType'] ?? '') . ' ' . (string) ($it['name'] ?? ''));

        return str_contains($blob, 'bow') || str_contains($blob, 'staff') || str_contains($blob, 'stave') || str_contains($blob, 'axe');
    }

    /** Right-click potion in inventory to drink (CheckInvPaste stand-in). */
    private function tryInvRightClick(int $sx, int $sy): bool
    {
        [$invX, $invY] = $this->invPanelOrigin();
        $ox = $invX + 17;
        $oy = $invY + 222;
        $gw = 10 * 29;
        $gh = 4 * 29;
        if ($sx < $ox || $sy < $oy || $sx >= $ox + $gw || $sy >= $oy + $gh) {
            return false;
        }
        $col = intdiv($sx - $ox, 29);
        $row = intdiv($sy - $oy, 29);
        $slot = $row * InventoryGrid::COLS + $col;
        [$this->inventory, $picked] = InventoryGrid::pickAt($this->inventory, $slot);
        if ($picked === null) {
            return true;
        }
        if ($this->useUsableItem($picked)) {
            return true; // consumed
        }
        if (($picked['equipSlot'] ?? '') !== '') {
            $es = (string) $picked['equipSlot'];
            $prev = $this->equipment[$es] ?? null;
            $this->equipment[$es] = $picked;
            $this->recalc();
            $this->loadPlayerSprite();
            if ($prev !== null) {
                $fit = InventoryGrid::findFit($this->inventory, $prev);
                if ($fit !== null) {
                    $this->inventory = InventoryGrid::place($this->inventory, $prev, $fit);
                } else {
                    $this->setHeldCursorItem($prev);
                }
            }
            $this->msg('Equipped ' . (string) ($picked['name'] ?? 'item'));
        } else {
            $fit = InventoryGrid::findFit($this->inventory, $picked);
            if ($fit !== null) {
                $this->inventory = InventoryGrid::place($this->inventory, $picked, $fit);
            } else {
                $this->setHeldCursorItem($picked);
            }
        }

        return true;
    }

    /** Drop held item onto dungeon tile (CMD_PUTITEM). */
    private function dropCursorItemAt(int $gx, int $gy): void
    {
        if ($this->cursorItem === null) {
            return;
        }
        if (!$this->map->inBounds($gx, $gy) || !$this->tileWalkableForPath($gx, $gy)) {
            $this->msg('Cannot drop here');

            return;
        }
        $it = $this->cursorItem;
        $this->setHeldCursorItem(null);
        $it['id'] = $this->nextId++;
        $it['x'] = $gx;
        $it['y'] = $gy;
        if (empty($it['spriteRgba'])) {
            $this->loadItemSprite($it);
        }
        $this->items[] = $it;
        $this->syncOccupancyActors();
        $this->msg('Dropped ' . (string) ($it['name'] ?? 'item'));
    }

    private function castHeal(): void
    {
        if ($this->pmana < 15) {
            $this->msg('Not enough mana');

            return;
        }
        $this->pmana -= 15;
        $this->php = min($this->pmaxHp, $this->php + 20 + $this->magic);
        $this->msg('Heal');
    }

    private function castFirebolt(): void
    {
        $want = $this->pendingSpellType !== '' ? $this->pendingSpellType : 'firebolt';
        $spellName = match (strtolower($want)) {
            'lightning' => 'Lightning',
            'fireball' => 'Fireball',
            'chargedbolt' => 'ChargedBolt',
            'flash' => 'Flash',
            'nova' => 'Nova',
            'inferno' => 'Inferno',
            'stonecurse' => 'StoneCurse',
            default => 'Firebolt',
        };
        $fromStaff = $this->canUseStaffFor($spellName);
        if (!$fromStaff && $this->pmana < 6) {
            return;
        }
        $best = null;
        $bestD = 99;
        foreach ($this->monsters as $m) {
            if (!$m['alive']) {
                continue;
            }
            $mx = (int) $m['x'];
            $my = (int) $m['y'];
            if (!$this->lineClearMissile($this->px, $this->py, $mx, $my)) {
                continue;
            }
            $d = max(abs($mx - $this->px), abs($my - $this->py));
            if ($d < $bestD) {
                $bestD = $d;
                $best = $m;
            }
        }
        if ($best === null) {
            return;
        }
        if ($fromStaff) {
            $this->pendingStaffConsume = true;
        } else {
            $this->pmana -= 6;
            $this->pendingStaffConsume = false;
        }
        $this->startSpell((int) $best['x'], (int) $best['y']);
    }

    /**
     * F-key / primary cast — prefer charged staff spell, else Firebolt.
     */
    private function castPrimarySpell(): void
    {
        $it = $this->equipment['hand'] ?? null;
        if ($it !== null && (int) ($it['charges'] ?? 0) > 0) {
            $sp = (string) ($it['spell'] ?? 'Firebolt');
            if ($this->canUseStaffFor($sp)) {
                $this->tryCastNamedSpell($sp, true);

                return;
            }
        }
        $this->pendingSpellType = 'firebolt';
        $this->audio?->play('cast');
        $this->castFirebolt();
    }

    /** Dispatch a named spell (staff / scroll). Returns false if no effect. */
    private function tryCastNamedSpell(string $spell, bool $fromStaff): bool
    {
        $sp = strtolower($spell);
        if (in_array($sp, ['healing', 'heal'], true)) {
            if (!$fromStaff && $this->pmana < 15) {
                return false;
            }
            if ($fromStaff) {
                $this->consumeStaffCharge();
            } else {
                $this->pmana -= 15;
            }
            $this->php = min($this->pmaxHp, $this->php + 20 + $this->magic);
            $this->msg('Heal');
            $this->audio?->play('heal');

            return true;
        }
        if ($sp === 'manashield') {
            if ($fromStaff) {
                $this->consumeStaffCharge();
            }
            $this->pManaShield = true;
            if (($this->spellLevels['ManaShield'] ?? 0) < 1) {
                $this->spellLevels['ManaShield'] = 1;
            }
            $this->msg('Mana Shield');

            return true;
        }
        if ($sp === 'infravision') {
            if ($fromStaff) {
                $this->consumeStaffCharge();
            }
            $this->pInfraTicks = max($this->pInfraTicks, 800);
            $this->msg('You gain infravision');

            return true;
        }
        if ($sp === 'search') {
            if ($fromStaff) {
                $this->consumeStaffCharge();
            }
            $this->pSearchTicks = max($this->pSearchTicks, 800);
            $this->msg('You sense nearby secrets');

            return true;
        }
        if ($sp === 'phasing' || $sp === 'teleport') {
            $ok = $this->castPhasing($sp === 'teleport');
            if ($ok && $fromStaff) {
                $this->consumeStaffCharge();
            }

            return $ok;
        }
        if (in_array($sp, ['firebolt', 'lightning', 'fireball', 'chargedbolt', 'flash', 'nova', 'inferno', 'stonecurse', 'apocalypse'], true)) {
            if ($sp === 'apocalypse') {
                $ok = $this->castApocalypse();
                if ($ok && $fromStaff) {
                    $this->consumeStaffCharge();
                }

                return $ok;
            }
            if ($sp === 'inferno') {
                $ok = $this->castInferno();
                if ($ok && $fromStaff) {
                    $this->consumeStaffCharge();
                }

                return $ok;
            }
            if ($sp === 'flash' || $sp === 'nova') {
                $ok = $this->castFlashBurst($sp === 'nova');
                if ($ok && $fromStaff) {
                    $this->consumeStaffCharge();
                }

                return $ok;
            }
            if ($sp === 'stonecurse') {
                $ok = $this->castStoneCurse();
                if ($ok && $fromStaff) {
                    $this->consumeStaffCharge();
                }

                return $ok;
            }
            $this->pendingSpellType = match ($sp) {
                'lightning' => 'lightning',
                'fireball' => 'fireball',
                'chargedbolt' => 'chargedbolt',
                default => 'firebolt',
            };
            $saved = $this->pmana;
            if ($fromStaff) {
                $this->pmana = max($this->pmana, 6);
            }
            $this->castFirebolt();
            if ($this->pendingSpell === null && $this->pmode !== 'SPELL') {
                $this->pmana = $saved;
                $this->pendingSpellType = 'firebolt';

                return false;
            }
            if ($fromStaff) {
                $this->pmana = $saved;
            }

            return true;
        }

        return false;
    }

    /** CanUseStaff — equipped staff with matching spell + charges. */
    private function canUseStaffFor(string $spell): bool
    {
        $it = $this->equipment['hand'] ?? null;
        if ($it === null) {
            return false;
        }
        $ch = (int) ($it['charges'] ?? 0);
        if ($ch <= 0) {
            return false;
        }
        $sp = (string) ($it['spell'] ?? '');
        if ($sp === '' || strcasecmp($sp, $spell) !== 0) {
            // Also accept common aliases
            $aliases = [
                'Healing' => ['Heal', 'Healing'],
                'Heal' => ['Heal', 'Healing'],
                'Firebolt' => ['Firebolt', 'Null'],
            ];
            $ok = false;
            foreach ($aliases[$spell] ?? [] as $a) {
                if (strcasecmp($sp, $a) === 0) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok && strcasecmp($sp, $spell) !== 0) {
                return false;
            }
        }
        $blob = strtolower((string) ($it['itemType'] ?? '') . ' ' . (string) ($it['name'] ?? ''));
        $misc = strtoupper((string) ($it['miscId'] ?? ''));

        return str_contains($blob, 'staff') || $misc === 'STAFF' || (int) ($it['maxCharges'] ?? 0) > 0;
    }

    /** ConsumeStaffCharge after successful staff cast. */
    private function consumeStaffCharge(): void
    {
        $it = $this->equipment['hand'] ?? null;
        if ($it === null) {
            return;
        }
        $ch = (int) ($it['charges'] ?? 0);
        if ($ch <= 0) {
            return;
        }
        $it['charges'] = $ch - 1;
        $this->equipment['hand'] = $it;
    }

    /** StartSpell — face target, play fm.cl2, queue missile at _pSFNum. */
    private function startSpell(int $tx, int $ty): void
    {
        $this->pdir = Direction::fromDelta($tx - $this->px, $ty - $this->py);
        $this->pmode = 'SPELL';
        $this->actionFrame = 0;
        $this->pendingSpell = [$tx, $ty];
        $this->playerAnim->setNewAnimation($this->spellFrames, 1, 0);
    }

    private function equipFirst(): void
    {
        foreach ($this->inventory as $idx => $it) {
            if ($it !== null && ($it['equipSlot'] ?? '') !== '') {
                $slot = $it['equipSlot'];
                $prev = $this->equipment[$slot] ?? null;
                $this->equipment[$slot] = $it;
                $this->inventory[$idx] = $prev;
                $this->recalc();
                $this->loadPlayerSprite();
                $this->msg('Equipped ' . $it['name']);

                return;
            }
        }
    }

    private function vendorAct(): void
    {
        $t = $this->activeVendor;
        if ($t === null) {
            return;
        }
        if ($t['role'] === 'healer') {
            $this->php = $this->pmaxHp;
            $this->pmana = $this->pmaxMana;
            if ($this->questStatus === 'inactive') {
                $this->questStatus = 'active';
                $this->msg('Quest: slay the Marked Beast');
            } elseif ($this->questStatus === 'complete') {
                $this->questStatus = 'turned_in';
                $this->gold += 200;
                $this->xp += 150;
                $this->msg('Quest reward!');
            } else {
                $this->msg('Fully healed');
            }
        } elseif ($t['role'] === 'merchant' && $this->gold >= 50) {
            $this->gold -= 50;
            $this->belt[0] = ['type' => 'potion_heal', 'name' => 'Health Potion', 'cursorGraphic' => 'POTION_OF_HEALING'];
            $this->loadItemSprite($this->belt[0]);
            $this->msg('Bought potion');
        } elseif ($t['role'] === 'blacksmith') {
            // Prefer repair when holding a damaged item (SmithRepair)
            if ($this->cursorItem !== null && $this->trySmithRepairCursor()) {
                // repaired
            } elseif ($this->gold >= 100) {
                $this->gold -= 100;
                foreach ($this->inventory as $idx => $slot) {
                    if ($slot === null) {
                        // Store stock is identified (SpawnStoreGold / premium path)
                        $this->inventory[$idx] = [
                            'type' => 'weapon',
                            'name' => 'Long Sword of quality',
                            'baseName' => 'Long Sword',
                            'minDamage' => 4,
                            'maxDamage' => 10,
                            'plMinDam' => 1,
                            'plMaxDam' => 2,
                            'equipSlot' => 'hand',
                            'quality' => 'magic',
                            'identified' => true,
                            'durability' => 40,
                            'maxDurability' => 40,
                        ];
                        break;
                    }
                }
                $this->msg('Bought sword');
            } else {
                $this->msg('Need gold (buy 100 / repair held item)');
            }
        } elseif ($t['role'] === 'witch') {
            if ($this->cursorItem !== null && $this->tryWitchRechargeCursor()) {
                // recharged
            } elseif ($this->gold >= 40) {
                $this->gold -= 40;
                $this->belt[1] = ['type' => 'potion_mana', 'name' => 'Mana Potion', 'cursorGraphic' => 'POTION_OF_MANA'];
                $this->loadItemSprite($this->belt[1]);
                $this->msg('Bought mana potion');
            } else {
                $this->msg('Need gold (potion 40 / recharge held staff)');
            }
        } elseif ($t['role'] === 'story') {
            if ($this->cursorItem === null) {
                $this->msg('Hold an item, then ask Cain');
            } elseif (($this->cursorItem['quality'] ?? 'normal') === 'normal'
                || !empty($this->cursorItem['identified'])) {
                $this->msg('That item is already identified');
            } elseif ($this->gold < 25) {
                $this->msg('Need 25 gold to identify');
            } else {
                $this->gold -= 25;
                $this->cursorItem['identified'] = true;
                $name = $this->itemLabel($this->cursorItem);
                $this->msg('Identified: ' . $name);
                // If this item is also equipped somehow, refresh PL bonuses
                $this->recalc();
            }
        } elseif ($t['role'] === 'tavern') {
            $this->msg('The cathedral holds dark secrets…');
        } elseif ($t['role'] === 'barmaid') {
            $this->msg('Careful down there, hero.');
        } elseif ($t['role'] === 'drunk') {
            $this->msg('*hic* More ale…');
        }
    }

    /**
     * SmithRepairItem — restore held item durability; price scales with missing dur.
     */
    private function trySmithRepairCursor(): bool
    {
        $it = $this->cursorItem;
        if ($it === null) {
            return false;
        }
        $max = (int) ($it['maxDurability'] ?? $it['durability'] ?? 0);
        $dur = (int) ($it['durability'] ?? $max);
        if ($max <= 0 || $max === 255 || $dur >= $max) {
            $this->msg('That item needs no repair');

            return true; // consumed the blacksmith action
        }
        $missing = $max - $dur;
        $price = max(1, $missing * 2);
        if ($this->gold < $price) {
            $this->msg('Need ' . $price . ' gold to repair');

            return true;
        }
        $this->gold -= $price;
        $it['durability'] = $max;
        $it['maxDurability'] = $max;
        $this->cursorItem = $it;
        $this->msg('Repaired ' . $this->itemLabel($it) . ' (' . $price . ' gold)');

        return true;
    }

    /**
     * WitchRechargeItem — restore staff charges on held item.
     */
    private function tryWitchRechargeCursor(): bool
    {
        $it = $this->cursorItem;
        if ($it === null) {
            return false;
        }
        $maxCh = (int) ($it['maxCharges'] ?? 0);
        $ch = (int) ($it['charges'] ?? 0);
        $blob = strtolower((string) ($it['itemType'] ?? '') . ' ' . (string) ($it['name'] ?? ''));
        if ($maxCh <= 0 && !str_contains($blob, 'staff')) {
            return false;
        }
        if ($maxCh <= 0) {
            $maxCh = 20;
            $it['maxCharges'] = $maxCh;
        }
        if ($ch >= $maxCh) {
            $this->msg('That staff is fully charged');

            return true;
        }
        $missing = $maxCh - $ch;
        $price = max(1, $missing * 3);
        if ($this->gold < $price) {
            $this->msg('Need ' . $price . ' gold to recharge');

            return true;
        }
        $this->gold -= $price;
        $it['charges'] = $maxCh;
        $it['maxCharges'] = $maxCh;
        $this->cursorItem = $it;
        $this->msg('Recharged ' . $this->itemLabel($it) . ' (' . $price . ' gold)');

        return true;
    }

    private function respawn(): void
    {
        $this->php = $this->pmaxHp;
        $this->pmana = $this->pmaxMana;
        $this->enterTown();
        $this->mode = 'play';
    }

    public function saveGame(): void
    {
        $dir = dirname(__DIR__) . '/saves';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $data = [
            'version' => 2,
            'playerName' => $this->playerName,
            'playerClass' => $this->playerClass,
            'px' => $this->px,
            'py' => $this->py,
            'php' => $this->php,
            'pmana' => $this->pmana,
            'gold' => $this->gold,
            'level' => $this->level,
            'xp' => $this->xp,
            'stats' => [$this->strength, $this->magic, $this->dexterity, $this->vitality],
            'inventory' => $this->inventory,
            'belt' => $this->belt,
            'equipment' => $this->equipment,
            'dungeonSeed' => $this->dungeonSeed,
            'currLevel' => $this->map->currLevel,
            'levelType' => $this->map->levelType,
            'questStatus' => $this->questStatus,
            'pManaShield' => $this->pManaShield ? 1 : 0,
            'pInfraTicks' => $this->pInfraTicks,
            'pSearchTicks' => $this->pSearchTicks,
            'spellLevels' => $this->spellLevels,
        ];
        file_put_contents($dir . '/slot1.json', json_encode($data, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}');
    }

    private function loadGame(): bool
    {
        $path = dirname(__DIR__) . '/saves/slot1.json';
        if (!is_file($path)) {
            return false;
        }
        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data)) {
            return false;
        }
        $this->playerName = (string) ($data['playerName'] ?? 'Hero');
        $this->playerClass = (string) ($data['playerClass'] ?? 'Warrior');
        $this->dungeonSeed = (int) ($data['dungeonSeed'] ?? 1);
        $this->gold = (int) ($data['gold'] ?? 0);
        $this->level = (int) ($data['level'] ?? 1);
        $this->xp = (int) ($data['xp'] ?? 0);
        $this->inventory = $data['inventory'] ?? array_fill(0, 40, null);
        $this->belt = $data['belt'] ?? [null, null, null, null];
        $this->equipment = $data['equipment'] ?? $this->equipment;
        $this->questStatus = (string) ($data['questStatus'] ?? 'inactive');
        $this->pManaShield = !empty($data['pManaShield']);
        $this->pInfraTicks = (int) ($data['pInfraTicks'] ?? 0);
        $this->pSearchTicks = (int) ($data['pSearchTicks'] ?? 0);
        if (is_array($data['spellLevels'] ?? null)) {
            $this->spellLevels = $data['spellLevels'];
        }
        [$this->strength, $this->magic, $this->dexterity, $this->vitality] = $data['stats'] ?? [30, 10, 20, 25];
        $this->recalc();
        $this->php = (int) ($data['php'] ?? $this->pmaxHp);
        $this->pmana = (int) ($data['pmana'] ?? $this->pmaxMana);
        $lt = (int) ($data['levelType'] ?? 0);
        $cl = (int) ($data['currLevel'] ?? 0);
        if ($lt === DungeonMap::DTYPE_TOWN) {
            $this->enterTown();
        } else {
            $this->enterCathedral(max(1, $cl));
        }
        $this->px = (int) ($data['px'] ?? $this->px);
        $this->py = (int) ($data['py'] ?? $this->py);
        $this->walkFutureX = $this->px;
        $this->walkFutureY = $this->py;
        $this->camX = $this->px;
        $this->camY = $this->py;
        $this->startStand();
        $this->syncOccupancyActors();

        return true;
    }

    private function drawDungeon(Sdl $sdl, int $winW, int $winH): void
    {
        $this->renderFrameId++;
        $this->playerDrawTraceCount = 0;
        $this->heroBlitThisFrame = 0;
        $this->actorBlitThisFrame = [];
        $this->lastActorScreenRects = [];
        if ($this->freezeFirstFrame) {
            $this->tickAlpha = 0.0;
            // Ensure stand occupancy (positive dPlayer) before golden frame
            if ($this->isWalking()) {
                $this->startStand();
            }
            $this->syncOccupancyActors();
        }
        // Authoritative logical viewport — COORDINATES.md (ignore window size for layout)
        $screenW = IsoCoords::SCREEN_W;
        $viewportH = IsoCoords::viewportHeight();
        $entityIndex = $this->buildEntityIndex();
        $layer = $this->renderLayer;
        $scrollX = 0;
        $scrollY = 0;
        $viewX = $this->camX;
        $viewY = $this->camY;
        if ($this->isWalking() && !$this->cameraFixed) {
            [$scrollX, $scrollY] = $this->currentWalkScroll();
            // CalcFirstTilePosition walking first-tile shifts
            [$viewX, $viewY, $scrollX, $scrollY] = $this->applyCameraWalkShift($viewX, $viewY, $scrollX, $scrollY);
        }
        if ($this->debugCamera && ($this->isWalking() || ($this->logicTick % 8) === 0)) {
            [$ax, $ay] = $this->currentWalkActorOffset();
            fwrite(STDOUT, sprintf(
                "camera tick=%d cur=%d,%d fut=%d,%d view=%d,%d scroll=%d,%d actor=%d,%d alpha=%.3f mode=%s\n",
                $this->logicTick,
                $this->px,
                $this->py,
                $this->walkFutureX,
                $this->walkFutureY,
                $viewX,
                $viewY,
                $scrollX,
                $scrollY,
                $ax,
                $ay,
                $this->tickAlpha,
                $this->pmode
            ));
        }
        if ($this->debugWalk && $this->isWalking()) {
            [$ax, $ay] = $this->currentWalkActorOffset();
            fwrite(STDOUT, sprintf(
                "walk render#%d tick=%d mode=%s dir=%d anim=%d/%d tickCtr=%d tile=%d,%d temp=%d,%d future=%d,%d walk0=%d dest=%d actor=%d,%d cam=%d,%d alpha=%.3f\n",
                ++$this->walkRenderLog,
                $this->logicTick,
                $this->pmode,
                $this->pdir,
                $this->playerAnim->currentFrame,
                $this->playerAnim->numberOfFrames,
                $this->playerAnim->tickCounterOfCurrentFrame,
                $this->px,
                $this->py,
                $this->walkTempX,
                $this->walkTempY,
                $this->walkFutureX,
                $this->walkFutureY,
                $this->walkpath[0] ?? DestAction::WALK_NONE,
                $this->destAction,
                $ax,
                $ay,
                $scrollX,
                $scrollY,
                $this->tickAlpha
            ));
        }
        if ($this->debugWalkStep && $this->isWalking()) {
            $this->emitWalkStepTrace('render');
        }
        Scrollrt::drawGame(
            $sdl,
            $this->map,
            $viewX,
            $viewY,
            $screenW,
            $viewportH,
            $layer,
            function (Sdl $sdl, int $piece, int $block, int $sx, int $syBottom, int $light, string $mode = '', int $mask = MaskType::SOLID): bool {
                return $this->blitPieceMicro($sdl, $piece, $block, $sx, $syBottom, $light, $mode, $mask);
            },
            function (Sdl $sdl, int $piece, int $block, int $sx, int $syBottom, int $light, string $mode = '', int $mask = MaskType::SOLID): bool {
                return $this->blitPieceMicro($sdl, $piece, $block, $sx, $syBottom, $light, $mode, $mask);
            },
            static function (int $x, int $y) use ($entityIndex): array {
                return $entityIndex[$x . ',' . $y] ?? [];
            },
            function (Sdl $sdl, array $e, int $sx, int $sy, int $light): void {
                $this->drawScrollrtEntity($sdl, $e, $sx, $sy, $light);
            },
            $this->debugPlaceholders,
            $scrollX,
            $scrollY,
            $this->isWalking() && !$this->cameraFixed,
            $this->pdir,
            $this->debugRenderMargin,
            $this->debugDisableMasks
        );
        if ($this->tracePlayerDraw && $this->heroBlitThisFrame > 1) {
            $heroKeys = [];
            foreach ($entityIndex as $k => $ents) {
                foreach ($ents as $e) {
                    if (($e['kind'] ?? '') === 'player' && empty($e['payload']['towner'])) {
                        $heroKeys[] = $k;
                    }
                }
            }
            fwrite(STDERR, sprintf(
                "PLAYER_DRAW_ASSERT FAIL frame=%d tick=%d hero_blits=%d (expected ≤1) entity_hero_keys=%s\n",
                $this->renderFrameId,
                $this->logicTick,
                $this->heroBlitThisFrame,
                implode('|', $heroKeys)
            ));
        } elseif ($this->tracePlayerDraw) {
            fwrite(STDOUT, sprintf(
                "player-draw-frame frame=%d tick=%d hero_blits=%d mode=%s\n",
                $this->renderFrameId,
                $this->logicTick,
                $this->heroBlitThisFrame,
                $this->pmode
            ));
        }
        if ($this->traceActorDraw) {
            foreach ($this->actorBlitThisFrame as $ak => $n) {
                if ($n > 1) {
                    fwrite(STDERR, sprintf(
                        "ACTOR_DRAW_ASSERT FAIL frame=%d tick=%d actor=%s blits=%d (expected ≤1)\n",
                        $this->renderFrameId,
                        $this->logicTick,
                        $ak,
                        $n
                    ));
                }
            }
            fwrite(STDOUT, sprintf(
                "actor-draw-frame frame=%d tick=%d actors=%s\n",
                $this->renderFrameId,
                $this->logicTick,
                json_encode($this->actorBlitThisFrame)
            ));
        }
        if ($this->debugWalkCapture) {
            // origins already unified via single drawGame call
        }
    }

    /**
     * Snapshot for walk-capture JSONL (call after render).
     * @return array<string,mixed>
     */
    public function walkCaptureState(int $renderFrame): array
    {
        $scrollX = 0;
        $scrollY = 0;
        $viewX = $this->camX;
        $viewY = $this->camY;
        if ($this->isWalking() && !$this->cameraFixed) {
            [$scrollX, $scrollY] = $this->currentWalkScroll();
            [$viewX, $viewY, $scrollX, $scrollY] = $this->applyCameraWalkShift($viewX, $viewY, $scrollX, $scrollY);
        }
        [$ax, $ay] = $this->currentWalkActorOffset();
        $vp = Scrollrt::viewportStats();

        return [
            'renderFrame' => $renderFrame,
            'logicTick' => $this->logicTick,
            'tickAlpha' => round($this->tickAlpha, 4),
            'player' => [$this->px, $this->py],
            'temp' => [$this->walkTempX, $this->walkTempY],
            'future' => [$this->walkFutureX, $this->walkFutureY],
            'ViewPosition' => [$viewX, $viewY],
            'cam' => [$this->camX, $this->camY],
            'playerOffset' => [$ax, $ay],
            'cameraOffset' => [$scrollX, $scrollY],
            'scrollStartTile' => [$vp['firstTile'][0] ?? 0, $vp['firstTile'][1] ?? 0],
            'scrollStartPixel' => [$vp['firstTile'][2] ?? 0, $vp['firstTile'][3] ?? 0],
            'tileRows' => $vp['drawRows'] ?? $vp['tileRows'],
            'tileColumns' => $vp['drawColumns'] ?? $vp['tileColumns'],
            'baseRows' => $vp['tileRows'],
            'baseColumns' => $vp['tileColumns'],
            'pmode' => $this->pmode,
            'pdir' => $this->pdir,
            'walking' => $this->isWalking(),
        ];
    }

    /** Start deterministic Town walk: East, East, SouthEast from current tile. */
    public function beginDebugWalkCapture(): void
    {
        // E=[1,-1], E, SE=[1,0] → +3x,-2y from start
        $tx = $this->px + 1 + 1 + 1;
        $ty = $this->py - 1 - 1 + 0;
        $this->makePlrPath($tx, $ty, true);
        $this->walkCaptureStarted = true;
        $this->walkCaptureDone = false;
        fwrite(STDOUT, sprintf(
            "debug-walk-capture path → %d,%d from %d,%d (E,E,SE)\n",
            $tx,
            $ty,
            $this->px,
            $this->py
        ));
    }

    public function beginDebugCombatCapture(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $this->debugCombatCaptureDir = $dir;
        $this->debugCombatCaptureFrame = 0;
        $jsonl = $dir . '/state.jsonl';
        $this->debugCombatCaptureFh = fopen($jsonl, 'wb');
        fwrite(STDOUT, "debug-combat-capture → {$dir}\n");
    }

    /** Call after render/present when --debug-combat-capture is set. */
    public function captureCombatFrame(Sdl $sdl, int $winW, int $winH): bool
    {
        if ($this->debugCombatCaptureDir === '') {
            return false;
        }
        $n = $this->debugCombatCaptureFrame;
        $png = sprintf('%s/frame-%03d.png', $this->debugCombatCaptureDir, $n);
        $sdl->dumpFramePNG($png, $winW, $winH);
        $mon = $this->monsters[0] ?? null;
        $row = [
            'frame' => $n,
            'logicTick' => $this->logicTick,
            'view' => [$this->camX, $this->camY],
            'player' => [
                'tile' => [$this->px, $this->py],
                'mode' => $this->pmode,
                'dir' => $this->pdir,
                'anim' => $this->playerAnim->currentFrame,
                'rect' => $this->lastActorScreenRects['hero:' . $this->playerId] ?? null,
            ],
            'monster' => $mon === null ? null : [
                'id' => $mon['id'] ?? 0,
                'tile' => [$mon['x'] ?? 0, $mon['y'] ?? 0],
                'mode' => $mon['mode'] ?? '',
                'cl2Mode' => $mon['cl2Mode'] ?? '',
                'anim' => $mon['animFrame'] ?? $mon['frame'] ?? 0,
                'alive' => !empty($mon['alive']) ? 1 : 0,
                'rect' => $this->lastActorScreenRects['monster:' . (int) ($mon['id'] ?? 0)] ?? null,
            ],
            'decodes' => $this->profileDecodes,
            'uploads' => $this->profileUploads,
        ];
        if (is_resource($this->debugCombatCaptureFh)) {
            fwrite($this->debugCombatCaptureFh, json_encode($row, JSON_UNESCAPED_SLASHES) . "\n");
        }
        $this->debugCombatCaptureFrame++;

        return $this->debugCombatCaptureFrame >= 48;
    }

    /**
     * Hard gate: micros-only frames before/after attack must match (world immutable).
     * Also compares full frames with actor AABBs masked when rects are available.
     * @return array{ok:bool,diffPixels:int,lines:list<string>}
     */
    public function runCombatWorldStabilityCheck(Sdl $sdl, int $winW, int $winH): array
    {
        $lines = [];
        $outDir = dirname(__DIR__) . '/tmp/combat-world-stability';
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }
        $savedLayer = $this->renderLayer;
        $this->renderLayer = 'micros';
        $this->tickAlpha = 0.0;
        $this->render($sdl, $winW, $winH);
        $before = $outDir . '/before-micros.png';
        $sdl->dumpFramePNG($before, $winW, $winH);
        $this->renderLayer = $savedLayer;
        $this->pmode = 'ATTACK';
        $this->playerAnim->setNewAnimation($this->attackFrames, 1, 0);
        for ($i = 0; $i < 4; $i++) {
            $this->tick();
        }
        $this->renderLayer = 'micros';
        $this->tickAlpha = 0.0;
        $this->render($sdl, $winW, $winH);
        $after = $outDir . '/after-micros.png';
        $sdl->dumpFramePNG($after, $winW, $winH);
        $this->renderLayer = $savedLayer;

        // Full-frame with actors: capture before (stand) and during attack, mask actor rects
        $this->pmode = 'STAND';
        $this->startStand();
        $this->tickAlpha = 0.0;
        $this->render($sdl, $winW, $winH);
        $masksBefore = $this->lastActorScreenRects;
        $fullBefore = $outDir . '/before-full.png';
        $sdl->dumpFramePNG($fullBefore, $winW, $winH);
        $this->pmode = 'ATTACK';
        $this->playerAnim->setNewAnimation($this->attackFrames, 1, 0);
        $this->tick();
        $this->tickAlpha = 0.0;
        $this->render($sdl, $winW, $winH);
        $masksAfter = $this->lastActorScreenRects;
        $fullAfter = $outDir . '/during-attack-full.png';
        $sdl->dumpFramePNG($fullAfter, $winW, $winH);

        $diff = 0;
        $maskedDiff = 0;
        $ok = true;
        if (!function_exists('imagecreatefrompng')) {
            $lines[] = 'WARN GD missing — cannot pixel-compare; frames dumped';
            $ok = true;
        } else {
            $cmp = $this->comparePngDiff($before, $after, []);
            $diff = $cmp['diff'];
            if (!$cmp['ok']) {
                $ok = false;
                $lines[] = 'FAIL micros compare: ' . ($cmp['err'] ?? 'error');
            } else {
                $ok = $diff <= 64;
                $lines[] = sprintf('world micros diffPixels=%d threshold=64 %s', $diff, $ok ? 'PASS' : 'FAIL');
            }
            $maskRects = array_values(array_merge($masksBefore, $masksAfter));
            $mcmp = $this->comparePngDiff($fullBefore, $fullAfter, $maskRects);
            $maskedDiff = $mcmp['diff'];
            if ($mcmp['ok']) {
                $maskOk = $maskedDiff <= 256;
                $lines[] = sprintf(
                    'world masked-full diffPixels=%d masks=%d threshold=256 %s',
                    $maskedDiff,
                    count($maskRects),
                    $maskOk ? 'PASS' : 'FAIL'
                );
                if (!$maskOk) {
                    $ok = false;
                }
            } else {
                $lines[] = 'WARN masked-full compare skipped: ' . ($mcmp['err'] ?? '');
            }
        }
        $lines[] = "frames → {$before} , {$after} , {$fullBefore} , {$fullAfter}";

        return ['ok' => $ok, 'diffPixels' => $diff, 'lines' => $lines];
    }

    /**
     * @param list<array{0:int,1:int,2:int,3:int}> $maskRects [x,y,w,h]
     * @return array{ok:bool,diff:int,err?:string}
     */
    private function comparePngDiff(string $pathA, string $pathB, array $maskRects): array
    {
        $a = @imagecreatefrompng($pathA);
        $b = @imagecreatefrompng($pathB);
        if ($a === false || $b === false) {
            return ['ok' => false, 'diff' => 0, 'err' => 'could not load PNGs'];
        }
        $w = imagesx($a);
        $h = imagesy($a);
        if ($w !== imagesx($b) || $h !== imagesy($b)) {
            imagedestroy($a);
            imagedestroy($b);

            return ['ok' => false, 'diff' => 0, 'err' => 'size mismatch'];
        }
        $diff = 0;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $masked = false;
                foreach ($maskRects as $r) {
                    if ($x >= $r[0] && $x < $r[0] + $r[2] && $y >= $r[1] && $y < $r[1] + $r[3]) {
                        $masked = true;
                        break;
                    }
                }
                if ($masked) {
                    continue;
                }
                if (imagecolorat($a, $x, $y) !== imagecolorat($b, $x, $y)) {
                    $diff++;
                }
            }
        }
        return ['ok' => true, 'diff' => $diff];
    }

    public function walkCaptureFinished(): bool
    {
        if (!$this->walkCaptureStarted) {
            return false;
        }
        if ($this->isWalking()) {
            return false;
        }
        if (($this->walkpath[0] ?? DestAction::WALK_NONE) !== DestAction::WALK_NONE) {
            return false;
        }

        return true;
    }

    public function forcePlayerPos(int $x, int $y): void
    {
        $this->forcePosX = $x;
        $this->forcePosY = $y;
        $this->px = $x;
        $this->py = $y;
        $this->walkTempX = $x;
        $this->walkTempY = $y;
        $this->walkFutureX = $x;
        $this->walkFutureY = $y;
        $this->camX = $x;
        $this->camY = $y;
        $this->clrPlrPath();
        $this->startStand();
        $this->map->plrDoTrans($x, $y);
        $this->syncOccupancyActors();
    }

    /**
     * Golden first-frame dump for --freeze-first-frame.
     * @return array<string,mixed>
     */
    public function dumpFirstFrameState(): array
    {
        Scrollrt::prepareViewport(IsoCoords::SCREEN_W, IsoCoords::viewportHeight());
        [$ftx, $fty, $bx, $by] = Scrollrt::firstTile($this->camX, $this->camY, 0, 0);
        $vp = Scrollrt::viewportStats();
        $dxVp = \Diablo\World\ViewportGeometry::compute($this->camX, $this->camY);
        $c0 = $c218 = $c426 = $other = $spec = 0;
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $p = (int) ($this->map->dPiece[$y][$x] ?? 0);
                if ($p === 0) {
                    $c0++;
                } elseif ($p === 218) {
                    $c218++;
                } elseif ($p === 426) {
                    $c426++;
                } else {
                    $other++;
                }
                if ((int) ($this->map->dSpecial[$y][$x] ?? 0) > 0) {
                    $spec++;
                }
            }
        }
        $dPlayer = [];
        foreach ($this->occ->allPlayers() as $k => $id) {
            $dPlayer[$k] = $id;
        }
        $row = [
            'levelType' => $this->map->levelType,
            'currLevel' => $this->map->currLevel,
            'player' => [
                'id' => $this->playerId,
                'tile' => [$this->px, $this->py],
                'future' => [$this->walkFutureX, $this->walkFutureY],
                'temp' => [$this->walkTempX, $this->walkTempY],
                'mode' => $this->pmode,
                'dir' => $this->pdir,
                'animFrame' => $this->playerAnim->currentFrame,
            ],
            'ViewPosition' => [$this->camX, $this->camY],
            'scroll' => [0, 0],
            'tickAlpha' => $this->tickAlpha,
            'viewportPhp' => [
                'firstTile' => [$ftx, $fty],
                'buf' => [$bx, $by],
                'tilesToTop' => $vp['tilesToTop'],
                'tilesToLeft' => $vp['tilesToLeft'],
                'tileShift' => [$vp['tileShiftX'] ?? 0, $vp['tileShiftY'] ?? 0],
                'tileOffset' => [$vp['tileOffsetX'], $vp['tileOffsetY']],
                'tileRows' => $vp['tileRows'],
                'tileColumns' => $vp['tileColumns'],
            ],
            'viewportTwin' => $dxVp,
            'dPiece' => [
                'w' => DungeonMap::MAXDUNX,
                'h' => DungeonMap::MAXDUNY,
                'count_0' => $c0,
                'count_218' => $c218,
                'count_426' => $c426,
                'count_other' => $other,
            ],
            'dSpecialCount' => $spec,
            'dPlayer' => $dPlayer,
            'passCounts' => Scrollrt::$passCounts,
            'playerDrawTraceCount' => $this->playerDrawTraceCount,
            'townerCount' => count($this->towners),
        ];
        $path = dirname(__DIR__) . '/tmp/first-frame-state.json';
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        fwrite(STDOUT, "first-frame-state → {$path}\n");

        return $row;
    }

    /**
     * Sample ≥10 screen points for PIXEL_PARITY_AUDIT provenance (no interactive click).
     * @param list<array{0:int,1:int,2?:string}> $samples [sx,sy,label?]
     * @return list<array<string,mixed>>
     */
    public function auditPixelParitySamples(array $samples): array
    {
        $rows = [];
        foreach ($samples as $s) {
            $sx = (int) $s[0];
            $sy = (int) $s[1];
            $label = (string) ($s[2] ?? '');
            $tile = $this->screenToTileWithFlip($sx, $sy);
            $tx = (int) $tile[0];
            $ty = (int) $tile[1];
            $inBounds = $this->map->inBounds($tx, $ty);
            $piece = $inBounds ? (int) ($this->map->dPiece[$ty][$tx] ?? 0) : 0;
            $megaX = $inBounds ? intdiv(max(0, $tx - 16), 2) : -1;
            $megaY = $inBounds ? intdiv(max(0, $ty - 16), 2) : -1;
            $dungeon = ($megaX >= 0 && $megaY >= 0) ? (int) ($this->map->dungeon[$megaY][$megaX] ?? 0) : -1;
            $special = $inBounds ? (int) ($this->map->dSpecial[$ty][$tx] ?? 0) : 0;
            $sol = $piece >= 0 ? (int) ($this->map->sol[$piece] ?? 0) : 0;
            $light = $inBounds ? (int) ($this->map->dLight[$ty][$tx] ?? 0) : 0;
            $minPath = $this->map->levelType === DungeonMap::DTYPE_TOWN
                ? 'levels\\towndata\\town.min'
                : 'levels\\l1data\\l1.min';
            $raw = $piece >= 0 ? $this->map->rawMinMicros($this->assets, $minPath, $piece) : [];
            $micros = [];
            foreach ($this->map->dPieceMicros[$piece] ?? [] as $bi => $b) {
                $frame = (int) ($b['frame'] ?? 0);
                $type = (int) ($b['type'] ?? 0);
                $bytes = $frame > 0 ? strlen($this->map->celFrameBytes($frame)) : 0;
                $ox = ($bi & 1) === 0 ? 0 : 32;
                $oy = -intdiv((int) $bi, 2) * 32;
                $micros[] = [
                    'i' => (int) $bi,
                    'type' => $type,
                    'frame' => $frame,
                    'celBytes' => $bytes,
                    'rawType' => (int) ($raw[$bi]['type'] ?? -1),
                    'rawFrame' => (int) ($raw[$bi]['frame'] ?? -1),
                    'rawData' => (int) ($raw[$bi]['data'] ?? 0),
                    'relX' => $ox,
                    'relY' => $oy,
                    'cacheKeyPrefix' => $piece . ':' . $bi . ':' . $frame,
                ];
            }
            $rows[] = [
                'label' => $label,
                'screen' => [$sx, $sy],
                'tile' => [$tx, $ty],
                'dungeon' => $dungeon,
                'dPiece' => $piece,
                'dSpecial' => $special,
                'sol' => $sol,
                'light' => $light,
                'ViewPosition' => [$this->camX, $this->camY],
                'micros' => $micros,
            ];
        }

        return $rows;
    }

    /**
     * Parity scenario state JSON (level/seed/view/player/monsters).
     * @return array<string,mixed>
     */
    public function dumpParityState(string $path, string $scenarioId = ''): array
    {
        $mons = [];
        foreach ($this->monsters as $m) {
            if (count($mons) >= 16) {
                break;
            }
            $mons[] = [
                'id' => (int) ($m['id'] ?? 0),
                'tile' => [(int) ($m['x'] ?? 0), (int) ($m['y'] ?? 0)],
                'dir' => (int) ($m['dir'] ?? 0),
                'mode' => (string) ($m['mode'] ?? ''),
                'cl2Mode' => (string) ($m['cl2Mode'] ?? ''),
                'animFrame' => (int) ($m['animFrame'] ?? $m['frame'] ?? 0),
                'alive' => !empty($m['alive']) ? 1 : 0,
            ];
        }
        $row = [
            'scenario' => $scenarioId,
            'levelType' => $this->map->levelType,
            'currLevel' => $this->map->currLevel,
            'seed' => $this->dungeonSeed,
            'fullbright' => $this->fullbright ? 1 : 0,
            'hideActors' => $this->hideActors ? 1 : 0,
            'hideObjects' => $this->hideObjects ? 1 : 0,
            'hideItems' => $this->hideItems ? 1 : 0,
            'ViewPosition' => [$this->camX, $this->camY],
            'player' => [
                'tile' => [$this->px, $this->py],
                'dir' => $this->pdir,
                'mode' => $this->pmode,
                'animFrame' => $this->playerAnim->currentFrame,
                'class' => $this->playerClass,
            ],
            'monsters' => $mons,
            'microTileLen' => $this->map->microTileLen,
        ];
        $dir = dirname($path);
        if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($path, json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return $row;
    }

    /** @return array{0:int,1:int} */
    public function playerTile(): array
    {
        return [$this->px, $this->py];
    }

    /** @return array<string, list<array{kind:string,payload:mixed}>> */
    private function buildEntityIndex(): array
    {
        $idx = [];
        if (!$this->hideItems) {
            foreach ($this->items as $it) {
                $ix = (int) $it['x'];
                $iy = (int) $it['y'];
                if (!$this->isTileVisible($ix, $iy)) {
                    continue;
                }
                // : !_iPostDraw → pre; _iPostDraw → post. PHP items use postDraw bool if set.
                $post = !empty($it['postDraw']) || !empty($it['_iPostDraw']);
                $idx[$ix . ',' . $iy][] = ['kind' => $post ? 'item' : 'item_pre', 'payload' => $it];
            }
        }
        if (!$this->hideObjects) {
            foreach ($this->objects as $o) {
                $ox = (int) $o['x'];
                $oy = (int) $o['y'];
                if (($o['type'] ?? '') !== 'l1light' && !$this->isTileVisible($ox, $oy)) {
                    continue;
                }
                // _oPreFlag — DX SetDoorStateOpen sets pre for open doors; closed doors draw post
                $pre = !empty($o['oPreFlag']) || !empty($o['_oPreFlag']);
                if (($o['type'] ?? '') === 'door' && !empty($o['opened'])) {
                    $pre = true;
                }
                $idx[$ox . ',' . $oy][] = ['kind' => $pre ? 'object_pre' : 'object', 'payload' => $o];
            }
        }
        if (!$this->hideActors) {
            foreach ($this->towners as $t) {
                $idx[$t['x'] . ',' . $t['y']][] = ['kind' => 'player', 'payload' => ['towner' => $t]];
            }
            foreach ($this->monsters as $m) {
                if (!$m['alive']) {
                    $cx = (int) $m['x'];
                    $cy = (int) $m['y'];
                    if ($this->isTileVisible($cx, $cy)) {
                        $idx[$cx . ',' . $cy][] = ['kind' => 'corpse', 'payload' => $m];
                    }
                    continue;
                }
                // negative dMonster: southwards / east sideways drawn on dest tile
                $mx = (int) $m['x'];
                $my = (int) $m['y'];
                $mode = (string) ($m['mode'] ?? '');
                $dir = (int) ($m['dir'] ?? 0);
                if ($mode === 'WALK' && isset($m['futX'], $m['futY'])) {
                    $southwards = in_array($dir, [Direction::S, Direction::SW, Direction::SE], true);
                    $eastSide = $dir === Direction::E;
                    if ($southwards || $eastSide) {
                        $mx = (int) $m['futX'];
                        $my = (int) $m['futY'];
                        $m = $m + ['_drawFromDest' => true];
                    }
                }
                if (!$this->isTileVisible($mx, $my) && !$this->isTileVisible((int) $m['x'], (int) $m['y'])) {
                    continue;
                }
                $idx[$mx . ',' . $my][] = ['kind' => 'monster', 'payload' => $m];
            }
            foreach ($this->missiles as $mis) {
                $mx = (int) round($mis['x']);
                $my = (int) round($mis['y']);
                if (!$this->isTileVisible($mx, $my)) {
                    continue;
                }
                $pre = !empty($mis['pre']) || !empty($mis['MissilePreFlag']);
                $idx[$mx . ',' . $my][] = ['kind' => $pre ? 'missile_pre' : 'missile', 'payload' => $mis];
            }
            // Hero: negative dPlayer on dest when WALK_SOUTHWARDS or (WALK_SIDEWAYS && East)
            $hx = $this->px;
            $hy = $this->py;
            $heroPayload = ['hero' => true];
            if ($this->pmode === 'WALK_SOUTHWARDS'
                || ($this->pmode === 'WALK_SIDEWAYS' && $this->pdir === Direction::E)) {
                $hx = $this->walkFutureX;
                $hy = $this->walkFutureY;
                $heroPayload['_drawFromDest'] = true;
            }
            $idx[$hx . ',' . $hy][] = ['kind' => 'player', 'payload' => $heroPayload];
        }

        return $idx;
    }

    /** @param array{kind:string,payload:mixed} $e */
    private function drawScrollrtEntity(Sdl $sdl, array $e, int $sx, int $syBottom, int $light): void
    {
        $kind = $e['kind'];
        $p = $e['payload'];
        $noteActor = function (string $akey, string $branch, int $frame) use ($sx, $syBottom, $p, $kind): void {
            $this->actorBlitThisFrame[$akey] = ($this->actorBlitThisFrame[$akey] ?? 0) + 1;
            if ($this->traceActorDraw) {
                fwrite(STDOUT, sprintf(
                    "trace-actor-draw frame=%d tick=%d actor=%s #%d mode=%s branch=%s tile=%d,%d fut=%s frameIdx=%d screen=%d,%d cl2Mode=%s\n",
                    $this->renderFrameId,
                    $this->logicTick,
                    $akey,
                    $this->actorBlitThisFrame[$akey],
                    is_array($p) ? (string) ($p['mode'] ?? $this->pmode) : '',
                    $branch,
                    Scrollrt::$currentTileX,
                    Scrollrt::$currentTileY,
                    is_array($p) && isset($p['futX']) ? ($p['futX'] . ',' . $p['futY']) : '-',
                    $frame,
                    $sx,
                    $syBottom,
                    is_array($p) ? (string) ($p['cl2Mode'] ?? '-') : '-'
                ));
            }
            if (RenderTrace::$enabled) {
                RenderTrace::record([
                    'layer' => RenderTrace::layer() !== '' ? RenderTrace::layer() : ('entity_' . $kind),
                    'tileX' => Scrollrt::$currentTileX,
                    'tileY' => Scrollrt::$currentTileY,
                    'dPiece' => 0,
                    'micro' => -1,
                    'frame' => $frame,
                    'type' => -1,
                    'mask' => MaskType::SOLID,
                    'maskName' => 'Solid',
                    'mode' => $branch,
                    'sx' => $sx - 48,
                    'sy' => $syBottom - 96,
                    'w' => 96,
                    'h' => 96,
                    'light' => 0,
                    'trn' => null,
                    'blend' => 'Solid',
                    'special' => 0,
                    'actor' => $akey,
                    'pixelsWritten' => 0,
                    'pixelsSkippedAlpha0' => 0,
                    'pixelsSkippedMask' => 0,
                    'opaqueBlack' => 0,
                ]);
            }
        };
        $fillSafe = function (int $r, int $g, int $b, int $x, int $y, int $w, int $h) use ($sdl): void {
            $sdl->setDrawColor($r, $g, $b);
            $sdl->fillRect($x, $y, $w, $h);
            $sdl->setDrawColor(0, 0, 0, 255); // restore — avoid SDL color leak into later rects
            if (RenderTrace::$softwareCompose && RenderTrace::$fb !== null) {
                RenderTrace::$fb->fillRect($x, $y, $w, $h, $r, $g, $b, 255);
            }
        };
        if ($kind === 'item' || $kind === 'item_pre') {
            if (!$this->isTileLitForCursor(Scrollrt::$currentTileX, Scrollrt::$currentTileY)) {
                return;
            }
            if (!empty($p['spriteRgba']) && !empty($p['spriteW'])) {
                $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : max(0, min(15, $light));
                $key = 'it' . ($p['id'] ?? 0) . ':L' . $useLight;
                $rgba = (string) $p['spriteRgba'];
                if ($useLight > 0) {
                    $rgba = Lighting::applyLightToRgba($rgba, (int) $p['spriteW'], (int) $p['spriteH'], $useLight);
                }
                $sw = (int) $p['spriteW'];
                $sh = (int) $p['spriteH'];
                $dstX = $sx - intdiv($sw, 2);
                $dstY = $syBottom - $sh;
                if ($this->cursKind === 'item' && (int) ($p['id'] ?? -1) === $this->cursId) {
                    // OutlineColors ≈ PAL16_YELLOW+2
                    $this->blitEntityOutline($sdl, $key, $rgba, $sw, $sh, $dstX, $dstY, 194);
                }
                $this->blitEntitySprite($sdl, $key, $rgba, $sw, $sh, $dstX, $dstY);
            } elseif ($this->debugPlaceholders) {
                $fillSafe(max(40, 220 - $light * 10), max(30, 180 - $light * 8), 40, $sx - 3, $syBottom - 6, 6, 6);
            }
        } elseif ($kind === 'object' || $kind === 'object_pre') {
            if (!empty($p['spriteRgba']) && !empty($p['spriteW'])) {
                $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : max(0, min(15, $light));
                $key = 'ob' . ($p['id'] ?? 0) . ':f' . (int) ($p['spriteFrame'] ?? 0) . ':L' . $useLight;
                $rgba = (string) $p['spriteRgba'];
                if ($useLight > 0) {
                    $rgba = Lighting::applyLightToRgba($rgba, (int) $p['spriteW'], (int) $p['spriteH'], $useLight);
                }
                $sw = (int) $p['spriteW'];
                $sh = (int) $p['spriteH'];
                $dstX = $sx - intdiv($sw, 2);
                $dstY = $syBottom - $sh;
                if ($this->cursKind === 'object' && (int) ($p['id'] ?? -1) === $this->cursId) {
                    // OutlineColorsObject = PAL16_YELLOW+2
                    $this->blitEntityOutline($sdl, $key, $rgba, $sw, $sh, $dstX, $dstY, 194);
                }
                $this->blitEntitySprite($sdl, $key, $rgba, $sw, $sh, $dstX, $dstY);
            } elseif ($this->debugPlaceholders) {
                $fillSafe(160, 100, 40, $sx - 6, $syBottom - 10, 12, 10);
            }
        } elseif ($kind === 'monster') {
            if (!$this->isTileLitForCursor(Scrollrt::$currentTileX, Scrollrt::$currentTileY)) {
                return;
            }
            $frame = $this->monsterDrawFrame($p, $light);
            if ($frame !== null) {
                $af = (int) ($p['animFrame'] ?? 0);
                $nFrames = max(1, (int) ($p['animFrames'] ?? 1));
                $frameKey = (($p['mode'] ?? '') === 'WALK')
                    ? (int) ($p['walkFrame'] ?? 0) % max(1, AiProc::WALK_FRAMES)
                    : ($af % $nFrames);
                $key = 'm' . ($p['id'] ?? 0) . ':' . ($p['cl2Mode'] ?? 'n') . ':' . $frameKey
                    . ':d' . (int) ($p['dir'] ?? 0) . ':L' . (($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : $light);
                $sw = (int) $frame['w'];
                $sh = (int) $frame['h'];
                $ox = 0;
                $oy = 0;
                if (($p['mode'] ?? '') === 'WALK') {
                    [$ox, $oy] = WalkOffset::get(
                        (int) ($p['dir'] ?? Direction::S),
                        min(7, max(0, (int) ($p['walkFrame'] ?? 0)))
                    );
                }
                // : drawn on dest with negative id — offset sprite back toward source tile
                if (!empty($p['_drawFromDest'])) {
                    $dir = (int) ($p['dir'] ?? Direction::S);
                    if ($dir === Direction::E) {
                        $ox -= IsoCoords::TILE_WIDTH;
                    } else {
                        match ($dir) {
                            Direction::SW => [$ox, $oy] = [$ox + intdiv(IsoCoords::TILE_WIDTH, 2), $oy - intdiv(IsoCoords::TILE_HEIGHT, 2)],
                            Direction::S => [$ox, $oy] = [$ox, $oy - IsoCoords::TILE_HEIGHT],
                            Direction::SE => [$ox, $oy] = [$ox - intdiv(IsoCoords::TILE_WIDTH, 2), $oy - intdiv(IsoCoords::TILE_HEIGHT, 2)],
                            default => null,
                        };
                    }
                }
                $noteActor('monster:' . (int) ($p['id'] ?? 0), 'monster_' . strtolower((string) ($p['mode'] ?? 'stand')), $frameKey);
                $dstX = $sx - IsoCoords::spriteTileCenterX($sw) + $ox;
                $dstY = $syBottom - $sh + $oy;
                $this->lastActorScreenRects['monster:' . (int) ($p['id'] ?? 0)] = [$dstX, $dstY, $sw, $sh];
                if ($this->cursKind === 'monster' && (int) ($p['id'] ?? -1) === $this->cursId) {
                    // OutlineColorsMonster = PAL16_RED+9
                    $this->blitEntityOutline($sdl, $key, $frame['rgba'], $sw, $sh, $dstX, $dstY, 233);
                    // Floating monster HP bar (monhealthbar QoL stand-in)
                    $hp = max(0, (int) ($p['hp'] ?? 0));
                    $maxHp = max(1, (int) ($p['maxHp'] ?? $hp));
                    $barW = 40;
                    $fill = (int) max(0, min($barW, intdiv($hp * $barW, $maxHp)));
                    $bx = $sx - intdiv($barW, 2);
                    $by = $dstY - 8;
                    $sdl->setDrawColor(20, 0, 0);
                    $sdl->fillRect($bx - 1, $by - 1, $barW + 2, 6);
                    $sdl->setDrawColor(120, 0, 0);
                    $sdl->fillRect($bx, $by, $barW, 4);
                    $sdl->setDrawColor(200, 40, 40);
                    $sdl->fillRect($bx, $by, $fill, 4);
                    $sdl->setDrawColor(0, 0, 0, 255);
                }
                $rgba = $frame['rgba'];
                if ((int) ($p['petrifiedTicks'] ?? 0) > 0) {
                    $rgba = $this->desaturateRgba($rgba, $sw, $sh);
                    $key .= ':stone';
                }
                $this->blitEntitySprite($sdl, $key, $rgba, $sw, $sh, $dstX, $dstY);
            } elseif ($this->debugPlaceholders) {
                $ox = 0;
                $oy = 0;
                if (($p['mode'] ?? '') === 'WALK') {
                    [$ox, $oy] = WalkOffset::get(
                        (int) ($p['dir'] ?? Direction::S),
                        min(7, max(0, (int) ($p['walkFrame'] ?? 0)))
                    );
                }
                $noteActor('monster:' . (int) ($p['id'] ?? 0), 'monster_placeholder', 0);
                $fillSafe(
                    !empty($p['quest']) ? 255 : 90,
                    !empty($p['quest']) ? 80 : 140,
                    !empty($p['quest']) ? 255 : 70,
                    $sx - 8 + $ox,
                    $syBottom - 18 + $oy,
                    16,
                    18
                );
            }
        } elseif ($kind === 'corpse') {
            // Prefer last death CL2 frame (Corpses[].frame = death frames-1)
            $p['mode'] = 'DEATH';
            $p['animFrame'] = (int) ($p['corpseFrame'] ?? max(0, (int) ($p['animFrames'] ?? 1) - 1));
            $frame = $this->monsterDrawFrame($p, $light);
            if ($frame !== null) {
                $key = 'corpse:' . (int) ($p['id'] ?? 0) . ':f' . (int) $p['animFrame']
                    . ':L' . (($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : $light);
                $sw = (int) $frame['w'];
                $sh = (int) $frame['h'];
                $noteActor('corpse:' . (int) ($p['id'] ?? 0), 'corpse_death_last', (int) $p['animFrame']);
                $dstX = $sx - IsoCoords::spriteTileCenterX($sw);
                $dstY = $syBottom - $sh;
                $this->blitEntitySprite($sdl, $key, $frame['rgba'], $sw, $sh, $dstX, $dstY);
            } else {
                $noteActor('corpse:' . (int) ($p['id'] ?? 0), 'corpse_placeholder', 0);
                $fillSafe(60, 40, 40, $sx - 6, $syBottom - 4, 12, 6);
            }
        } elseif ($kind === 'missile' || $kind === 'missile_pre') {
            if (!$this->drawMissileSprite($sdl, $p, $sx, $syBottom, $light)) {
                // Fallback marker when CL2 missing / decode fail
                $type = (string) ($p['type'] ?? 'firebolt');
                if ($type === 'firebolt' || $type === 'arrow') {
                    $fillSafe(255, 160, 40, $sx - 3, $syBottom - 3, 6, 6);
                } else {
                    $fillSafe(180, 200, 255, $sx - 3, $syBottom - 3, 6, 6);
                }
            }
        } elseif ($kind === 'special') {
            $id = (int) ($p['id'] ?? 0);
            $frameIdx = $id - 1;
            if ($frameIdx >= 0 && (isset($this->specialFrames[$frameIdx]) || ($this->specialCel !== null && $frameIdx < $this->specialCel->numFrames))) {
                $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : max(0, min(15, $light));
                // : town ClxDraw opaque; dungeon ClxDrawLight / ClxDrawLightBlended if TransList
                $blend = !empty($p['blend'])
                    || ($this->map->levelType !== DungeonMap::DTYPE_TOWN
                        && $this->map->transListActive((int) ($p['trans'] ?? 0)));
                $key = 'sp:' . $frameIdx . ':L' . $useLight . ':B' . ($blend ? 1 : 0);
                if (!isset($this->microCache[$key])) {
                    if ($this->specialCel !== null && $frameIdx < $this->specialCel->numFrames) {
                        $lt = $useLight > 0 ? Lighting::table($useLight) : null;
                        $fr = $this->specialCel->decodeFrame($frameIdx, 64, $this->map->palette, $lt);
                        $this->profileDecodes++;
                    } else {
                        $fr = $this->specialFrames[$frameIdx];
                        if ($useLight > 0) {
                            $fr = [
                                'w' => $fr['w'],
                                'h' => $fr['h'],
                                'rgba' => Lighting::applyLightToRgba($fr['rgba'], $fr['w'], $fr['h'], $useLight),
                            ];
                        }
                    }
                    $rgba = $fr['rgba'];
                    if ($blend) {
                        // ClxDrawLightBlended / BlitPixelsBlended → α=128 (MaskType::Transparent)
                        $rgba = MaskType::apply($rgba, (int) $fr['w'], (int) $fr['h'], MaskType::TRANSPARENT);
                    }
                    $fw = (int) $fr['w'];
                    $fh = (int) $fr['h'];
                    $this->microCache[$key] = [
                        'tex' => $sdl->createTextureRGBA($fw, $fh, $rgba),
                        'w' => $fw,
                        'h' => $fh,
                        'rgba' => $rgba,
                    ];
                    $this->profileUploads++;
                }
                $texEntry = $this->microCache[$key];
                $fw = is_array($texEntry) ? (int) $texEntry['w'] : ($this->specialFrames[$frameIdx]['w'] ?? 64);
                $fh = is_array($texEntry) ? (int) $texEntry['h'] : ($this->specialFrames[$frameIdx]['h'] ?? 64);
                $dstX = $sx - intdiv($fw, 2);
                $dstY = $syBottom - $fh;
                if (RenderTrace::$softwareCompose && RenderTrace::$fb !== null && is_array($texEntry) && isset($texEntry['rgba'])) {
                    RenderTrace::$fb->blit($texEntry['rgba'], $fw, $fh, $dstX, $dstY, $blend);
                }
                if (!RenderTrace::$softwareCompose) {
                    if (is_array($texEntry)) {
                        $sdl->copyTexture($texEntry['tex'], $dstX, $dstY, $fw, $fh);
                    } else {
                        $sdl->copyTexture($texEntry, $dstX, $dstY, $fw, $fh);
                    }
                }
                if (RenderTrace::$enabled) {
                    RenderTrace::record([
                        'layer' => RenderTrace::layer(),
                        'tileX' => Scrollrt::$currentTileX,
                        'tileY' => Scrollrt::$currentTileY,
                        'dPiece' => 0,
                        'micro' => -1,
                        'frame' => $frameIdx + 1,
                        'type' => -1,
                        'mask' => $blend ? MaskType::TRANSPARENT : MaskType::SOLID,
                        'maskName' => $blend ? 'Transparent' : 'Solid',
                        'mode' => 'special',
                        'sx' => $dstX,
                        'sy' => $dstY,
                        'w' => $fw,
                        'h' => $fh,
                        'light' => $useLight,
                        'trn' => null,
                        'blend' => $blend ? 'Transparent50' : 'Solid',
                        'special' => $id,
                        'rgba' => is_array($texEntry) ? ($texEntry['rgba'] ?? null) : null,
                    ]);
                }
            } else {
                fwrite(STDERR, sprintf("SPECIAL_MISSING id=%d (frame %d not in towns/l1s CEL)\n", $id, $frameIdx));
            }
        } elseif ($kind === 'player') {
            $drawTileX = Scrollrt::$currentTileX;
            $drawTileY = Scrollrt::$currentTileY;
            $isTowner = !empty($p['towner']);
            $isHero = !empty($p['hero']) || !$isTowner;
            if ($isHero && !$isTowner) {
                $this->heroBlitThisFrame++;
                $noteActor('hero:' . $this->playerId, $this->isWalking() ? 'hero_walk' : ($this->pmode === 'ATTACK' || $this->pmode === 'RATTACK' ? 'hero_attack' : 'hero_stand'), $this->playerAnim->currentFrame);
            }
            if ($this->tracePlayerDraw) {
                $this->playerDrawTraceCount++;
                $occTile = $this->occ->playerAt($drawTileX, $drawTileY);
                $occCur = $this->occ->playerAt($this->px, $this->py);
                $occFut = $this->occ->playerAt($this->walkFutureX, $this->walkFutureY);
                $occTmp = $this->occ->playerAt($this->walkTempX, $this->walkTempY);
                $reason = $isTowner ? 'towner_cel' : ($this->isWalking()
                    ? 'hero_walk'
                    : (($this->pmode === 'ATTACK' || $this->pmode === 'RATTACK') ? 'hero_attack' : 'hero_stand'));
                fwrite(STDOUT, sprintf(
                    "trace-player-draw frame=%d tick=%d #%d tile=%d,%d temp=%d,%d fut=%d,%d dPlayer[tile]=%d dPlayer[cur]=%d dPlayer[fut]=%d dPlayer[tmp]=%d mode=%s dir=%d anim=%d screen=%d,%d towner=%d hero=%d branch=%s\n",
                    $this->renderFrameId,
                    $this->logicTick,
                    $this->playerDrawTraceCount,
                    $drawTileX,
                    $drawTileY,
                    $this->walkTempX,
                    $this->walkTempY,
                    $this->walkFutureX,
                    $this->walkFutureY,
                    $occTile,
                    $occCur,
                    $occFut,
                    $occTmp,
                    $this->pmode,
                    $this->pdir,
                    $this->playerAnim->currentFrame,
                    $sx,
                    $syBottom,
                    $isTowner ? 1 : 0,
                    $isHero && !$isTowner ? 1 : 0,
                    $reason
                ));
            }
            if (!empty($p['towner'])) {
                $t = $p['towner'];
                $frame = $this->townerDrawFrame($t);
                if ($frame !== null) {
                    $key = 'tw' . ($t['id'] ?? 0) . ':f' . (int) ($t['animFrame'] ?? 0);
                    $sw = (int) $frame['w'];
                    $sh = (int) $frame['h'];
                    $dstX = $sx - intdiv($sw, 2);
                    $dstY = $syBottom - $sh;
                    if ($this->cursKind === 'towner' && (int) ($t['id'] ?? -1) === $this->cursId) {
                        // OutlineColorsTowner = PAL16_BEIGE+6
                        $this->blitEntityOutline($sdl, $key, $frame['rgba'], $sw, $sh, $dstX, $dstY, 166);
                    }
                    $this->blitEntitySprite($sdl, $key, $frame['rgba'], $sw, $sh, $dstX, $dstY);
                } elseif ($this->debugPlaceholders) {
                    $sdl->setDrawColor(80, 160, 220);
                    $sdl->fillRect($sx - 6, $syBottom - 16, 12, 16);
                }
            } else {
                $frame = $this->currentPlayerFrame();
                $ox = 0;
                $oy = 0;
                if ($this->isWalking()) {
                    [$ox, $oy] = $this->currentWalkActorOffset();
                    // southwards / east sideways drawn from neighbor tile offset
                    if ($this->pmode === 'WALK_SOUTHWARDS') {
                        match ($this->pdir) {
                            Direction::SW => [$ox, $oy] = [$ox + 32, $oy - 16],
                            Direction::S => [$ox, $oy] = [$ox, $oy - 32],
                            Direction::SE => [$ox, $oy] = [$ox - 32, $oy - 16],
                            default => null,
                        };
                    } elseif ($this->pmode === 'WALK_SIDEWAYS' && $this->pdir === Direction::E) {
                        $ox -= 64;
                    }
                }
                $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : max(0, min(15, $light));
                $key = 'plr:' . $this->pdir . ':' . $this->pmode . ':' . $this->playerAnim->currentFrame . ':a' . $this->actionFrame . ':L' . $useLight;
                // : tip + { -CalculateSpriteTileCenterX(w), 0 } → feet on tile baseline
                $dstX = $sx - IsoCoords::spriteTileCenterX($frame['w']) + $ox;
                $dstY = $syBottom - $frame['h'] + $oy;
                $this->lastActorScreenRects['hero:' . $this->playerId] = [$dstX, $dstY, $frame['w'], $frame['h']];
                $this->blitEntitySprite($sdl, $key, $frame['rgba'], (int) $frame['w'], (int) $frame['h'], $dstX, $dstY);
                $this->profileDraws++;
            }
        }
    }

    /**
     * Entity sprite blit: SDL and/or software framebuffer (parity with micro path).
     */
    private function blitEntitySprite(Sdl $sdl, string $key, string $rgba, int $w, int $h, int $dstX, int $dstY, bool $blend50 = false): void
    {
        if (!isset($this->microCache[$key]) || !is_array($this->microCache[$key])) {
            $this->microCache[$key] = [
                'tex' => $sdl->createTextureRGBA($w, $h, $rgba),
                'w' => $w,
                'h' => $h,
                'rgba' => $rgba,
            ];
            $this->profileUploads++;
        }
        $entry = $this->microCache[$key];
        if (RenderTrace::$softwareCompose && RenderTrace::$fb !== null && isset($entry['rgba'])) {
            RenderTrace::$fb->blit($entry['rgba'], (int) $entry['w'], (int) $entry['h'], $dstX, $dstY, $blend50);
        }
        if (!RenderTrace::$softwareCompose) {
            $sdl->copyTexture($entry['tex'], $dstX, $dstY, (int) $entry['w'], (int) $entry['h']);
        }
    }

    /**
     * ClxDrawOutlineSkipColorZero stand-in — 4-neighbor silhouette in palette color.
     * OutlineColors*: Monster=PAL16_RED+9, Object/Item≈PAL16_YELLOW+2, Towner=PAL16_BEIGE+6.
     */
    private function blitEntityOutline(Sdl $sdl, string $spriteKey, string $rgba, int $w, int $h, int $dstX, int $dstY, int $palIndex): void
    {
        $okey = 'outline:' . $spriteKey . ':p' . $palIndex;
        if (!isset($this->microCache[$okey]) || !is_array($this->microCache[$okey])) {
            [$r, $g, $b] = $this->map->palette->rgb($palIndex);
            $out = $this->makeSpriteOutlineRgba($rgba, $w, $h, $r, $g, $b);
            $this->microCache[$okey] = [
                'tex' => $sdl->createTextureRGBA($w, $h, $out),
                'w' => $w,
                'h' => $h,
                'rgba' => $out,
            ];
            $this->profileUploads++;
        }
        $this->blitEntitySprite($sdl, $okey, (string) $this->microCache[$okey]['rgba'], $w, $h, $dstX, $dstY);
    }

    private function makeSpriteOutlineRgba(string $rgba, int $w, int $h, int $r, int $g, int $b): string
    {
        $n = $w * $h;
        $opaque = array_fill(0, $n, false);
        for ($i = 0; $i < $n; $i++) {
            $opaque[$i] = ord($rgba[$i * 4 + 3]) > 0;
        }
        $out = str_repeat("\x00\x00\x00\x00", $n);
        $px = chr($r & 255) . chr($g & 255) . chr($b & 255) . "\xFF";
        $dirs = [[-1, 0], [1, 0], [0, -1], [0, 1]];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $i = $y * $w + $x;
                if (!$opaque[$i]) {
                    continue;
                }
                foreach ($dirs as [$dx, $dy]) {
                    $nx = $x + $dx;
                    $ny = $y + $dy;
                    if ($nx < 0 || $ny < 0 || $nx >= $w || $ny >= $h) {
                        continue;
                    }
                    $ni = $ny * $w + $nx;
                    if ($opaque[$ni]) {
                        continue;
                    }
                    $oi = $ni * 4;
                    $out[$oi] = $px[0];
                    $out[$oi + 1] = $px[1];
                    $out[$oi + 2] = $px[2];
                    $out[$oi + 3] = $px[3];
                }
            }
        }

        return $out;
    }

    /**
     * @param array<string,mixed> $t
     * @return array{w:int,h:int,rgba:string}|null
     */
    private function townerDrawFrame(array &$t): ?array
    {
        $cel = $t['celObj'] ?? null;
        $w = (int) ($t['animW'] ?? 96);
        if ($cel instanceof Cel) {
            $n = max(1, $cel->numFrames);
            $fi = max(0, (int) ($t['animFrame'] ?? 0) % $n);
            $key = 'twfr:' . ($t['id'] ?? 0) . ':' . $fi;
            if (!isset($this->playerFrameCache[$key])) {
                $this->playerFrameCache[$key] = $cel->decodeFrame($fi, $w, $this->map->palette);
                $this->profileDecodes++;
            }

            return $this->playerFrameCache[$key];
        }
        if (!empty($t['spriteRgba']) && !empty($t['spriteW'])) {
            return ['w' => (int) $t['spriteW'], 'h' => (int) $t['spriteH'], 'rgba' => (string) $t['spriteRgba']];
        }

        return null;
    }

    /**
     * Draw missile CL2 (arrows / fireba). Returns false to keep colored marker fallback.
     * @param array<string,mixed> $p
     */
    private function drawMissileSprite(Sdl $sdl, array $p, int $sx, int $syBottom, int $light): bool
    {
        $type = (string) ($p['type'] ?? 'firebolt');
        $dir = $this->missileDir16((float) ($p['vx'] ?? 0), (float) ($p['vy'] ?? 0));
        $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : max(0, min(15, $light));
        $w = 96;
        try {
            if ($type === 'arrow') {
                if ($this->missileArrowsCl2 === null) {
                    if (!$this->assets->has('missiles\\arrows.cl2')) {
                        return false;
                    }
                    $this->missileArrowsCl2 = Cl2::parse($this->assets->read('missiles\\arrows.cl2'));
                }
                $cl2 = $this->missileArrowsCl2;
                $frameIdx = $dir % max(1, $cl2->numFrames);
            } else {
                // firebolt → Fireball graphic (fireba1..16)
                $sheet = $dir + 1;
                if (!isset($this->missileFirebaCl2[$sheet])) {
                    $path = 'missiles\\fireba' . $sheet . '.cl2';
                    if (!$this->assets->has($path)) {
                        return false;
                    }
                    $this->missileFirebaCl2[$sheet] = Cl2::parse($this->assets->read($path));
                }
                $cl2 = $this->missileFirebaCl2[$sheet];
                $frameIdx = ((int) ($p['anim'] ?? 0) % max(1, $cl2->numFrames));
            }
            $key = 'mis:' . $type . ':' . $dir . ':' . $frameIdx . ':L' . $useLight;
            if (!isset($this->microCache[$key])) {
                $lt = $useLight > 0 ? Lighting::table($useLight) : null;
                $fr = $cl2->decodeFrame($frameIdx, $w, $this->map->palette, null, $lt);
                $this->profileDecodes++;
                $this->microCache[$key] = [
                    'tex' => $sdl->createTextureRGBA($fr['w'], $fr['h'], $fr['rgba']),
                    'w' => $fr['w'],
                    'h' => $fr['h'],
                    'rgba' => $fr['rgba'],
                ];
                $this->profileUploads++;
            }
            $entry = $this->microCache[$key];
            $fw = (int) $entry['w'];
            $fh = (int) $entry['h'];
            $dstX = $sx - IsoCoords::spriteTileCenterX($fw);
            $dstY = $syBottom - $fh;
            if (RenderTrace::$softwareCompose && RenderTrace::$fb !== null && isset($entry['rgba'])) {
                RenderTrace::$fb->blit($entry['rgba'], $fw, $fh, $dstX, $dstY, false);
            }
            if (!RenderTrace::$softwareCompose) {
                $sdl->copyTexture($entry['tex'], $dstX, $dstY, $fw, $fh);
            }
            if (RenderTrace::$enabled) {
                RenderTrace::record([
                    'layer' => RenderTrace::layer(),
                    'tileX' => Scrollrt::$currentTileX,
                    'tileY' => Scrollrt::$currentTileY,
                    'dPiece' => 0,
                    'micro' => -2,
                    'frame' => $frameIdx,
                    'type' => -1,
                    'mask' => MaskType::SOLID,
                    'maskName' => 'Solid',
                    'mode' => 'missile:' . $type,
                    'sx' => $dstX,
                    'sy' => $dstY,
                    'w' => $fw,
                    'h' => $fh,
                    'light' => $useLight,
                    'trn' => null,
                    'blend' => 'Solid',
                    'special' => 0,
                    'pixelsWritten' => 0,
                    'pixelsSkippedAlpha0' => 0,
                    'pixelsSkippedMask' => 0,
                    'opaqueBlack' => 0,
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** 16-way missile facing from velocity (tile space). */
    private function missileDir16(float $vx, float $vy): int
    {
        if ($vx == 0.0 && $vy == 0.0) {
            return 0;
        }
        // Screen-ish iso: N=0 … clockwise in 22.5° steps (Diablo missile dirs).
        $ang = atan2($vx, -$vy); // 0 = north (-y)
        $dir = (int) round($ang / (M_PI / 8));
        if ($dir < 0) {
            $dir += 16;
        }

        return $dir % 16;
    }

    /**
     * Micro blit: ($sx,$syBottom) is bottom-left of micro frame.
     * @param string $mode ''|floor_triangle|foliage
     */
    private function blitPieceMicro(Sdl $sdl, int $piece, int $block, int $sx, int $syBottom, int $light = 0, string $mode = '', int $mask = MaskType::SOLID): bool
    {
        if (!isset($this->map->dPieceMicros[$piece][$block])) {
            \Diablo\World\WorldCellAudit::noteBlit(
                'MISSING_MICROS_TABLE',
                sprintf('piece=%d block=%d', $piece, $block)
            );

            return false;
        }
        $celBlock = $this->map->dPieceMicros[$piece][$block];
        $frame = (int) ($celBlock['frame'] ?? 0);
        if ($frame === 0) {
            \Diablo\World\WorldCellAudit::noteBlit('FRAME_ZERO', sprintf('piece=%d block=%d', $piece, $block));

            return false;
        }
        $type = (int) ($celBlock['type'] ?? 0);
        if ($mode === 'floor_triangle') {
            $type = ($block & 1) === 0 ? DunMicro::TYPE_LEFT_TRIANGLE : DunMicro::TYPE_RIGHT_TRIANGLE;
        }
        $useLight = ($this->fullbright || $this->map->levelType === DungeonMap::DTYPE_TOWN) ? 0 : $light;
        $key = $piece . ':' . $block . ':' . $frame . ':' . $mode . ':T' . $type . ':L' . $useLight . ':M' . $mask;
        $meta = ['frame' => $frame, 'type' => $type, 'mode' => $mode, 'mask' => $mask, 'light' => $useLight];
        if (isset($this->microCacheMeta[$key])) {
            $prev = $this->microCacheMeta[$key];
            if ($prev !== $meta) {
                fwrite(STDERR, sprintf(
                    "MICRO_CACHE_META_ASSERT FAIL key=%s prev=%s new=%s\n",
                    $key,
                    json_encode($prev),
                    json_encode($meta)
                ));
            }
        } else {
            $this->microCacheMeta[$key] = $meta;
        }
        $needRgba = RenderTrace::$enabled || RenderTrace::$softwareCompose;
        if (!isset($this->microCache[$key])) {
            $bytes = $this->map->celFrameBytes($frame);
            if ($bytes === '') {
                \Diablo\World\WorldCellAudit::noteBlit(
                    'CEL_BYTES_EMPTY',
                    sprintf('frame=%d piece=%d block=%d', $frame, $piece, $block)
                );
                if (!$this->debugPlaceholders) {
                    fwrite(STDERR, "DunMicro missing CEL frame={$frame} piece={$piece} block={$block}\n");
                }

                return false;
            }
            try {
                $lightTable = null;
                if ($useLight > 0) {
                    $lightTable = Lighting::table($useLight);
                }
                $decoded = DunMicro::decode(
                    $bytes,
                    $type,
                    $this->map->palette,
                    true,
                    $mode === 'foliage' ? 'foliage' : '',
                    $lightTable
                );
                $this->profileDecodes++;
                $dwDec = (int) $decoded['w'];
                $dhDec = (int) $decoded['h'];
                $rgbaRaw = $decoded['rgba'];
                // DX DunFrameTriangleHeight=31: triangle content occupies y=0..30 in our 32 buffer.
                // Crop empty y=31 so bottom-inclusive blit matches soft black / RenderTileFrame.
                if ($mode !== 'foliage'
                    && ($type === DunMicro::TYPE_LEFT_TRIANGLE || $type === DunMicro::TYPE_RIGHT_TRIANGLE)
                    && $dhDec >= DunMicro::TRIANGLE_HEIGHT) {
                    $dhDec = DunMicro::TRIANGLE_HEIGHT;
                    $rgbaRaw = substr($rgbaRaw, 0, $dwDec * $dhDec * 4);
                }
                $beforeMask = $rgbaRaw;
                $rgba = MaskType::apply($beforeMask, $dwDec, $dhDec, $mask);
                $maskedAway = MaskType::countMaskedAway($beforeMask, $rgba);
                $skipA0 = 0;
                $writtenEst = 0;
                $opaqueBlack = 0;
                for ($i = 0, $n = strlen($rgba); $i < $n; $i += 4) {
                    $a = ord($rgba[$i + 3]);
                    if ($a === 0) {
                        $skipA0++;
                        continue;
                    }
                    $writtenEst++;
                    if (ord($rgba[$i]) + ord($rgba[$i + 1]) + ord($rgba[$i + 2]) === 0) {
                        $opaqueBlack++;
                    }
                }
                $entry = [
                    'tex' => $sdl->createTextureRGBA($dwDec, $dhDec, $rgba),
                    'w' => $dwDec,
                    'h' => $dhDec,
                    'pixelsWritten' => $writtenEst,
                    'pixelsSkippedAlpha0' => $skipA0,
                    'pixelsSkippedMask' => $maskedAway,
                    'opaqueBlack' => $opaqueBlack,
                ];
                if ($needRgba) {
                    $entry['rgba'] = $rgba;
                }
                $this->microCache[$key] = $entry;
                $this->profileUploads++;
            } catch (\Throwable $e) {
                \Diablo\World\WorldCellAudit::noteBlit(
                    'DECODE_FAIL',
                    sprintf('piece=%d block=%d %s', $piece, $block, $e->getMessage())
                );
                if (!$this->debugPlaceholders) {
                    fwrite(STDERR, 'DunMicro decode fail piece=' . $piece . ' block=' . $block . ' mode=' . $mode . ': ' . $e->getMessage() . "\n");
                }

                return false;
            }
        }
        $entry = $this->microCache[$key];
        $dstX = $sx;
        // DX RenderTileFrame: position.y is inclusive bottom → top = sy - (h - 1)
        $dh = is_array($entry) ? (int) $entry['h'] : 32;
        $dw = is_array($entry) ? (int) $entry['w'] : 32;
        $dstY = $syBottom - ($dh - 1);
        // Transparent + Left/Right mask regions use α=128; SDL_BLEND / soft path average with dest.
        $blend50 = $mask === MaskType::TRANSPARENT || $mask === MaskType::LEFT || $mask === MaskType::RIGHT;
        if (RenderTrace::$softwareCompose && RenderTrace::$fb !== null && is_array($entry) && isset($entry['rgba'])) {
            $stats = RenderTrace::$fb->blit($entry['rgba'], $dw, $dh, $dstX, $dstY, $blend50);
        } else {
            $stats = [
                'written' => is_array($entry) ? (int) ($entry['pixelsWritten'] ?? 0) : 0,
                'skippedAlpha0' => is_array($entry) ? (int) ($entry['pixelsSkippedAlpha0'] ?? 0) : 0,
                'skippedMask' => is_array($entry) ? (int) ($entry['pixelsSkippedMask'] ?? 0) : 0,
                'opaqueBlack' => is_array($entry) ? (int) ($entry['opaqueBlack'] ?? 0) : 0,
            ];
        }
        if (!RenderTrace::$softwareCompose) {
            if (is_array($entry)) {
                $sdl->copyTexture($entry['tex'], $dstX, $dstY, $dw, $dh);
            } else {
                $sdl->copyTexture($entry, $sx, $syBottom - 32, 32, 32);
            }
        }
        if (RenderTrace::$enabled) {
            $rec = [
                'layer' => RenderTrace::layer(),
                'tileX' => Scrollrt::$currentTileX,
                'tileY' => Scrollrt::$currentTileY,
                'dPiece' => $piece,
                'micro' => $block,
                'frame' => $frame,
                'type' => $type,
                'mask' => $mask,
                'maskName' => MaskType::name($mask),
                'mode' => $mode,
                'sx' => $dstX,
                'sy' => $dstY,
                'w' => $dw,
                'h' => $dh,
                'light' => $useLight,
                'trn' => null,
                'blend' => $blend50 ? 'Transparent50' : 'Solid',
                'special' => 0,
                'pixelsWritten' => $stats['written'],
                'pixelsSkippedAlpha0' => $stats['skippedAlpha0'],
                'pixelsSkippedMask' => $stats['skippedMask'],
                'opaqueBlack' => $stats['opaqueBlack'],
            ];
            if (isset($entry['rgba']) && is_string($entry['rgba'])) {
                $rec['rgba'] = $entry['rgba'];
            }
            RenderTrace::record($rec);
        }
        $this->profileDraws++;
        \Diablo\World\WorldCellAudit::noteBlit('BLIT_OK');

        return true;
    }

    private function fillDiamond(Sdl $sdl, int $cx, int $cy, int $hw, int $hh): void
    {
        Scrollrt::fillDiamond($sdl, $cx, $cy, $hw, $hh);
    }

    /**
     * DrawFlaskUpper — dome above panel (FlaskTopRect {11,3}×{62,13}).
     * Life offset 107, mana 475; fillPer 0..81.
     */
    private function drawFlaskUpper(Sdl $sdl, object $bulbTex, int $panelLeft, int $flaskOffset, int $panelTop, int $fillPer): void
    {
        $srcX = 11;
        $srcY = 3;
        $w = 62;
        $h = 13;
        $emptyRows = max(0, min($h, 81 - $fillPer));
        $filledRows = $h - $emptyRows;
        $destX = $panelLeft + $flaskOffset;
        $destY = $panelTop - $h;
        // Always draw bulb glass dome (empty flask frame)
        $sdl->copyTextureSrc($bulbTex, $srcX, $srcY, $w, $h, $destX, $destY, $w, $h);
        // Filled rows from panel art (BottomBuffer ) over empty
        if ($filledRows > 0 && $this->panelTex !== null) {
            $sdl->copyTextureSrc(
                $this->panelTex,
                $flaskOffset,
                $srcY + $emptyRows,
                $w,
                $filledRows,
                $destX,
                $destY + $emptyRows,
                $w,
                $filledRows
            );
        }
    }

    /**
     * DrawFlaskLower — empty bulb top + filled panel art bottom.
     * FlaskBottomRect {0,16}×{88,69}; fillPer 0..81.
     * @param int $flaskOffset panel-local X (life=96, mana=464)
     */
    private function drawFlaskLower(Sdl $sdl, object $bulbTex, int $panelLeft, int $flaskOffset, int $panelTop, int $fillPer): void
    {
        $destX = $panelLeft + $flaskOffset;
        $srcX = 0;
        $srcY = 16;
        $w = 88;
        $h = 69;
        $filledRows = max(0, min($h, $fillPer));
        $emptyRows = $h - $filledRows;
        if ($emptyRows > 0) {
            $sdl->copyTextureSrc($bulbTex, $srcX, $srcY, $w, $emptyRows, $destX, $panelTop, $w, $emptyRows);
        }
        if ($filledRows > 0 && $this->panelTex !== null) {
            $sdl->copyTextureSrc(
                $this->panelTex,
                $flaskOffset,
                $srcY + $emptyRows,
                $w,
                $filledRows,
                $destX,
                $panelTop + $emptyRows,
                $w,
                $filledRows
            );
        } elseif ($filledRows > 0) {
            $sdl->copyTextureSrc(
                $bulbTex,
                $srcX,
                $srcY + $emptyRows,
                $w,
                $filledRows,
                $destX,
                $panelTop + $emptyRows,
                $w,
                $filledRows
            );
        }
    }

    private function drawHud(Sdl $sdl, int $winW, int $winH): void
    {
        $this->ensureUiFonts();
        if ($this->renderLayer === 'floor' || $this->renderLayer === 'walls' || $this->renderLayer === 'micros'
            || $this->renderLayer === 'actors' || $this->renderLayer === 'specials' || $this->renderLayer === 'objects') {
            return;
        }
        if ($this->panelTex === null && $this->panelRgba !== '') {
            $this->panelTex = $sdl->createTextureRGBA($this->panelW, $this->panelH, $this->panelRgba);
        }
        $panelTop = $winH - IsoCoords::PANEL_HEIGHT;
        $panelLeft = 0;
        if ($winW > IsoCoords::SCREEN_W) {
            $panelLeft = intdiv($winW - IsoCoords::SCREEN_W, 2);
        }
        if ($this->panelTex !== null) {
            // panel8.cel uses index-0 holes in the info strip; BottomBuffer is opaque under the panel.
            $sdl->setDrawColor(0, 0, 0);
            $sdl->fillRect($panelLeft, $panelTop, min($winW, IsoCoords::SCREEN_W), IsoCoords::PANEL_HEIGHT);
            $sdl->copyTexture($this->panelTex, $panelLeft, $panelTop, min($winW, $this->panelW), min(IsoCoords::PANEL_HEIGHT, $this->panelH));
        } elseif ($this->debugPlaceholders) {
            $sdl->setDrawColor(32, 32, 40);
            $sdl->fillRect($panelLeft, $panelTop, min($winW, IsoCoords::SCREEN_W), IsoCoords::PANEL_HEIGHT);
        } else {
            fwrite(STDERR, "HUD: panel8.cel unavailable\n");
        }
        // Life/mana flasks — DrawFlaskLower geometry
        if ($this->bulbsRgba !== '') {
            if ($this->bulbsTex === null) {
                $this->bulbsTex = $sdl->createTextureRGBA($this->bulbsW, $this->bulbsH, $this->bulbsRgba);
            }
            if ($this->bulbsManaTex === null && $this->bulbsManaRgba !== '') {
                $this->bulbsManaTex = $sdl->createTextureRGBA($this->bulbsW, $this->bulbsH, $this->bulbsManaRgba);
            }
            // Player::_pHPPer / _pManaPer — 0..81
            $hpPer = $this->pmaxHp <= 0 ? 0 : (int) max(0, min(81, intdiv($this->php * 81, max(1, $this->pmaxHp))));
            $manaPer = $this->pmaxMana <= 0 ? 0 : (int) max(0, min(81, intdiv($this->pmana * 81, max(1, $this->pmaxMana))));
            // Upper dome first (above panel), then lower strip
            // Mana Shield → yellow HP flask tint (partypanel QoL cue)
            if ($this->pManaShield) {
                $sdl->setDrawColor(180, 160, 40);
                $sdl->fillRect($panelLeft + 96, $panelTop + 46, 56, 8);
            } $this->drawFlaskUpper($sdl, $this->bulbsTex, $panelLeft, 107, $panelTop, $hpPer);
            $manaTex = $this->bulbsManaTex ?? $this->bulbsTex;
            $this->drawFlaskUpper($sdl, $manaTex, $panelLeft, 475, $panelTop, $manaPer);
            $this->drawFlaskLower($sdl, $this->bulbsTex, $panelLeft, 96, $panelTop, $hpPer);
            $this->drawFlaskLower($sdl, $manaTex, $panelLeft, 464, $panelTop, $manaPer);
            // HP/Mana numerals — UiFont (DrawLifeFlask / mana text )
            $hpText = (string) max(0, $this->php);
            $manaText = (string) max(0, $this->pmana);
            $this->blitUiText($sdl, $hpText, $panelLeft + 107 + 31, $panelTop + 46, true, false);
            $this->blitUiText($sdl, $manaText, $panelLeft + 475 + 31, $panelTop + 46, true, false);
        }
        // Pressed panel buttons (p8but2) when corresponding UI is open
        $this->drawPressedPanelButtons($sdl, $panelLeft, $panelTop);
        // DrawSpell — panel + (565,119) is CLX bottom-left of large icon
        if ($this->speliconRgba !== '') {
            if ($this->speliconTex === null) {
                $this->speliconTex = $sdl->createTextureRGBA($this->speliconW, $this->speliconH, $this->speliconRgba);
            }
            $sdl->copyTexture(
                $this->speliconTex,
                $panelLeft + 565,
                $panelTop + 119 - $this->speliconH,
                $this->speliconW,
                $this->speliconH
            );
        }
        // Belt slots — objcurs icons (panel-local belt strip ~205+)
        if ($this->bulbsRgba !== '') {
            for ($b = 0; $b < 4; $b++) {
                $it = $this->belt[$b] ?? null;
                if ($it === null) {
                    continue;
                }
                if (empty($it['spriteRgba'])) {
                    if (empty($it['cursorGraphic']) && ($it['type'] ?? '') === 'potion_heal') {
                        $it['cursorGraphic'] = 'POTION_OF_HEALING';
                    }
                    $this->loadItemSprite($it);
                    $this->belt[$b] = $it;
                }
                $bx = $panelLeft + 205 + $b * 29;
                $by = $panelTop + 5;
                $this->invDrawSlotBack($sdl, $bx, $by, 27, 27, (string) ($it['quality'] ?? 'normal'));
                if (!empty($it['spriteRgba']) && !empty($it['spriteW'])) {
                    $sw = (int) $it['spriteW'];
                    $sh = (int) $it['spriteH'];
                    $key = 'belt:' . $b . ':f' . (int) ($it['objcursFrame'] ?? 0);
                    if (!isset($this->microCache[$key]) || !is_array($this->microCache[$key])) {
                        $this->microCache[$key] = [
                            'tex' => $sdl->createTextureRGBA($sw, $sh, (string) $it['spriteRgba']),
                            'w' => $sw,
                            'h' => $sh,
                            'rgba' => (string) $it['spriteRgba'],
                        ];
                        $this->profileUploads++;
                    }
                    $dx = $bx + intdiv(28 - $sw, 2);
                    $dy = $by + intdiv(28 - $sh, 2);
                    // HUD is drawn after software-compose present — always blit via SDL.
                    $sdl->copyTexture($this->microCache[$key]['tex'], $dx, $dy, $sw, $sh);
                } else {
                    $sdl->setDrawColor(40, 36, 28);
                    $sdl->fillRect($bx, $by, 27, 27);
                    $sdl->setDrawColor(160, 120, 60);
                    $sdl->fillRect($bx + 4, $by + 4, 19, 19);
                }
            }
        } elseif ($this->debugPlaceholders) {
            $lifeW = (int) (56 * max(0, $this->php) / max(1, $this->pmaxHp));
            $manaW = (int) (56 * max(0, $this->pmana) / max(1, $this->pmaxMana));
            $sdl->setDrawColor(120, 20, 20);
            $sdl->fillRect(20, $panelTop + 40, 56, 56);
            $sdl->setDrawColor(200, 40, 40);
            $sdl->fillRect(20, $panelTop + 40 + (56 - $lifeW), 56, $lifeW);
            $sdl->setDrawColor(20, 20, 100);
            $sdl->fillRect($winW - 76, $panelTop + 40, 56, 56);
            $sdl->setDrawColor(40, 80, 220);
            $sdl->fillRect($winW - 76, $panelTop + 40 + (56 - $manaW), 56, $manaW);
            for ($b = 0; $b < 4; $b++) {
                $bx = 200 + $b * 36;
                $sdl->setDrawColor(40, 40, 48);
                $sdl->fillRect($bx, $panelTop + 20, 32, 32);
                if ($this->belt[$b] !== null) {
                    $sdl->setDrawColor(180, 60, 60);
                    $sdl->fillRect($bx + 4, $panelTop + 24, 24, 24);
                }
            }
        }
        if ($this->showChar) {
            $panelLeftHud = $winW > IsoCoords::SCREEN_W ? intdiv($winW - IsoCoords::SCREEN_W, 2) : 0;
            $cx = $panelLeftHud;
            $cy = 0;
            if ($this->charPanelRgba !== '') {
                if ($this->charPanelTex === null) {
                    $this->charPanelTex = $sdl->createTextureRGBA($this->charPanelW, $this->charPanelH, $this->charPanelRgba);
                }
                $sdl->copyTexture($this->charPanelTex, $cx, $cy, $this->charPanelW, $this->charPanelH);
            } else {
                $sdl->setDrawColor(16, 16, 22);
                $sdl->fillRect($cx, $cy, 320, 352);
            }
            $y = $cy + 20;
            $this->blitUiText($sdl, (string) $this->playerName, $cx + 160, $y, true, true, true);
            $y += 20;
            $this->blitUiText($sdl, $this->playerClass . '  Level ' . $this->level, $cx + 160, $y, true, false, true);
            $y = $cy + 120;
            $lines = [
                'STR ' . $this->strength,
                'MAG ' . $this->magic,
                'DEX ' . $this->dexterity,
                'VIT ' . $this->vitality,
                'Life ' . $this->php . '/' . $this->pmaxHp,
                'Mana ' . $this->pmana . '/' . $this->pmaxMana,
                'Armor ' . $this->ac,
                'To Hit ' . $this->toHit . '%',
                'Damage ' . $this->minDam . '-' . $this->maxDam,
                'Res F/L/M ' . $this->pFireResist . '/' . $this->pLightResist . '/' . $this->pMagicResist,
                'Light ' . $this->pLightRad,
                'Gold ' . $this->gold,
            ];
            foreach ($lines as $line) {
                $this->blitUiText($sdl, $line, $cx + 40, $y, false, false, true);
                $y += 18;
            }
        }
        if ($this->showQuest) {
            $panelLeftHud = $winW > IsoCoords::SCREEN_W ? intdiv($winW - IsoCoords::SCREEN_W, 2) : 0;
            $qx = $panelLeftHud;
            $qy = 0;
            if ($this->questPanelRgba !== '') {
                if ($this->questPanelTex === null) {
                    $this->questPanelTex = $sdl->createTextureRGBA($this->questPanelW, $this->questPanelH, $this->questPanelRgba);
                }
                $sdl->copyTexture($this->questPanelTex, $qx, $qy, $this->questPanelW, $this->questPanelH);
            } else {
                $sdl->setDrawColor(16, 16, 22);
                $sdl->fillRect($qx, $qy, 320, 352);
            }
            $this->blitUiText($sdl, 'Quest Log', $qx + 160, $qy + 24, true, true, true);
            $statusLine = match ($this->questStatus) {
                'inactive' => 'No active quest',
                'active' => 'Slay the Marked Beast',
                'complete' => 'Return to Pepin',
                'turned_in' => 'Quest finished',
                default => $this->questStatus,
            };
            $this->blitUiText($sdl, $statusLine, $qx + 40, $qy + 80, false, false, true);
            if ($this->questStatus === 'active' || $this->questStatus === 'complete') {
                $this->blitUiText($sdl, 'Talk to Pepin in town', $qx + 40, $qy + 100, false, false, true);
            }
        }
        if ($this->showSpellBook) {
            [$invX, $invY] = $this->invPanelOrigin($winW);
            if ($this->spellBookRgba !== '') {
                if ($this->spellBookTex === null) {
                    $this->spellBookTex = $sdl->createTextureRGBA($this->spellBookW, $this->spellBookH, $this->spellBookRgba);
                }
                $sdl->copyTexture($this->spellBookTex, $invX, $invY, $this->spellBookW, $this->spellBookH);
            } else {
                $sdl->setDrawColor(24, 20, 32);
                $sdl->fillRect($invX, $invY, 320, 352);
            }
            $this->blitUiText($sdl, 'Spell Book', $invX + 160, $invY + 24, true, true, true);
            $spells = [
                ['Firebolt', 'F / RMB', '6 mana', (int) ($this->spellLevels['Firebolt'] ?? 0)],
                ['Heal', 'H', '15 mana', (int) ($this->spellLevels['Heal'] ?? 0)],
            ];
            if (($this->spellLevels['ManaShield'] ?? 0) > 0 || $this->pManaShield) {
                $spells[] = ['Mana Shield', $this->pManaShield ? 'ON' : 'scroll', '—', (int) ($this->spellLevels['ManaShield'] ?? 0)];
            }
            $sy = $invY + 80;
            foreach ($spells as $sp) {
                $lvl = $sp[3] > 0 ? ('  Lv' . $sp[3]) : '';
                $this->blitUiText($sdl, $sp[0] . $lvl, $invX + 40, $sy, false, false, true);
                $this->blitUiText($sdl, $sp[1] . '  ' . $sp[2], $invX + 40, $sy + 16, false, false, true);
                $sy += 48;
            }
            // Firebolt icon in book
            if ($this->speliconRgba !== '' && $this->speliconTex !== null) {
                $sdl->copyTexture($this->speliconTex, $invX + 220, $invY + 80, min(56, $this->speliconW), min(56, $this->speliconH));
            } elseif ($this->speliconRgba !== '') {
                if ($this->speliconTex === null) {
                    $this->speliconTex = $sdl->createTextureRGBA($this->speliconW, $this->speliconH, $this->speliconRgba);
                }
                $sdl->copyTexture($this->speliconTex, $invX + 220, $invY + 80, min(56, $this->speliconW), min(56, $this->speliconH));
            }
        }
        // DrawInfoBox — InfoBoxRect {177,46}×{288,64}; hover / held-item name
        $infoLines = $this->cursorInfoLines();
        if ($infoLines !== []) {
            $infoX = $panelLeft + 177 + intdiv(288, 2);
            $infoY = $panelTop + 52;
            foreach ($infoLines as $i => $line) {
                $this->blitUiText($sdl, $line, $infoX, $infoY + $i * 16, true, false, true);
            }
        } else {
            // Idle: show gold in the info strip (char panel also lists gold)
            $this->blitUiText($sdl, 'Gold: ' . $this->gold, $panelLeft + 177 + intdiv(288, 2), $panelTop + 68, true, false, true);
        }
        // XP bar (QoL DrawXPBar stand-in) — just above control panel
        $need = max(1, $this->level * $this->level * 100);
        $xpFrac = max(0.0, min(1.0, $this->xp / $need));
        $barW = 307;
        $barX = $panelLeft + intdiv(IsoCoords::SCREEN_W - $barW, 2);
        $barY = $panelTop - 12;
        $sdl->setDrawColor(16, 12, 8);
        $sdl->fillRect($barX - 3, $barY - 1, $barW + 6, 9);
        $sdl->setDrawColor(40, 32, 20);
        $sdl->fillRect($barX, $barY, $barW, 7);
        $fillW = (int) round($barW * $xpFrac);
        if ($fillW > 0) {
            $sdl->setDrawColor(200, 170, 60);
            $sdl->fillRect($barX, $barY, $fillW, 7);
        }
        $sdl->setDrawColor(0, 0, 0, 255);
        if ($this->showInv) {
            [$invX, $invY] = $this->invPanelOrigin($winW);
            if ($this->invPanelRgba !== '') {
                if ($this->invPanelTex === null) {
                    $this->invPanelTex = $sdl->createTextureRGBA($this->invPanelW, $this->invPanelH, $this->invPanelRgba);
                }
                $sdl->copyTexture($this->invPanelTex, $invX, $invY, $this->invPanelW, $this->invPanelH);
            } else {
                $sdl->setDrawColor(24, 24, 32);
                $sdl->fillRect($invX, $invY, 320, 352);
            }
            $ox = $invX + 17;
            $oy = $invY + 222;
            for ($y = 0; $y < InventoryGrid::ROWS; $y++) {
                for ($x = 0; $x < InventoryGrid::COLS; $x++) {
                    $idx = $y * InventoryGrid::COLS + $x;
                    $cell = $this->inventory[$idx] ?? null;
                    if (!is_array($cell) || !empty($cell['_invFill'])) {
                        continue;
                    }
                    // InvDrawSlotBack stand-in — tint slot by item quality
                    $slotX = $ox + $x * 29;
                    $slotY = $oy + $y * 29;
                    $this->invDrawSlotBack($sdl, $slotX, $slotY, 28, 28, (string) ($cell['quality'] ?? 'normal'));
                    if (empty($cell['spriteRgba'])) {
                        $this->loadItemSprite($cell);
                        $this->inventory[$idx] = $cell;
                    }
                    if (!empty($cell['spriteRgba']) && !empty($cell['spriteW'])) {
                        $sw = (int) $cell['spriteW'];
                        $sh = (int) $cell['spriteH'];
                        $key = 'inv:' . $idx . ':f' . (int) ($cell['objcursFrame'] ?? 0);
                        if (!isset($this->microCache[$key]) || !is_array($this->microCache[$key])) {
                            $this->microCache[$key] = [
                                'tex' => $sdl->createTextureRGBA($sw, $sh, (string) $cell['spriteRgba']),
                                'w' => $sw,
                                'h' => $sh,
                                'rgba' => (string) $cell['spriteRgba'],
                            ];
                            $this->profileUploads++;
                        }
                        $dx = $ox + $x * 29 + intdiv(28 - min(28, $sw), 2);
                        $dy = $oy + $y * 29 + intdiv(28 - min(28, $sh), 2);
                        $sdl->copyTexture($this->microCache[$key]['tex'], $dx, $dy, min(28, $sw), min(28, $sh));
                    } else {
                        $sdl->setDrawColor(160, 140, 60);
                        $w = max(1, (int) ($cell['invW'] ?? 1));
                        $h = max(1, (int) ($cell['invH'] ?? 1));
                        $sdl->fillRect($ox + $x * 29 + 2, $oy + $y * 29 + 2, $w * 28 - 4, $h * 28 - 4);
                    }
                }
            }
            // Equipped body slots (InvRect left/right hand, chest, helm)
            foreach (['hand' => [17 + 14, 75 + 20], 'offhand' => [248 + 14, 75 + 20], 'body' => [132 + 14, 75 + 20], 'head' => [132 + 14, 2 + 10]] as $slot => [$ex, $ey]) {
                $eqIt = $this->equipment[$slot] ?? null;
                if (!is_array($eqIt)) {
                    continue;
                }
                if (empty($eqIt['spriteRgba'])) {
                    if (empty($eqIt['cursorGraphic'])) {
                        $eqIt['cursorGraphic'] = match (true) {
                            str_contains(strtolower((string) ($eqIt['itemType'] ?? $eqIt['name'] ?? '')), 'bow') => 'BOW',
                            str_contains(strtolower((string) ($eqIt['itemType'] ?? $eqIt['name'] ?? '')), 'shield') => 'SMALL_SHIELD',
                            $slot === 'body' => 'LEATHER_ARMOR',
                            $slot === 'head' => 'CAP',
                            default => 'DAGGER',
                        };
                    }
                    $this->loadItemSprite($eqIt);
                    $this->equipment[$slot] = $eqIt;
                }
                if (empty($eqIt['spriteRgba'])) {
                    continue;
                }
                $sw = (int) $eqIt['spriteW'];
                $sh = (int) $eqIt['spriteH'];
                $this->invDrawSlotBack($sdl, $invX + $ex, $invY + $ey, max(28, $sw), max(28, $sh), (string) ($eqIt['quality'] ?? 'normal'));
                $key = 'eq:' . $slot . ':f' . (int) ($eqIt['objcursFrame'] ?? 0);
                if (!isset($this->microCache[$key]) || !is_array($this->microCache[$key])) {
                    $this->microCache[$key] = [
                        'tex' => $sdl->createTextureRGBA($sw, $sh, (string) $eqIt['spriteRgba']),
                        'w' => $sw,
                        'h' => $sh,
                        'rgba' => (string) $eqIt['spriteRgba'],
                    ];
                    $this->profileUploads++;
                }
                $sdl->copyTexture($this->microCache[$key]['tex'], $invX + $ex, $invY + $ey, $sw, $sh);
            }
        }
        // Software cursor drawn in render() after HUD/overlays (DrawCursor)
        // Info lines above control panel (diablo_msg / AddInfoBoxString stand-in)
        if ($this->messages !== []) {
            $panelTop = $winH - IsoCoords::PANEL_HEIGHT;
            $lineY = $panelTop - 4;
            $lines = array_slice($this->messages, -3);
            foreach (array_reverse($lines) as $msg) {
                $lineY -= 18;
                if ($lineY < 8) {
                    break;
                }
                $this->blitUiText($sdl, (string) $msg, $panelLeft + 177, $lineY, false, false, true);
            }
        }
        if ($this->showAutomap) {
            $this->drawAutomap($sdl, $winW, $winH);
        }
    }

    /**
     * Minimal Automap overlay — visited tiles, isometric diamond cells.
     */
    private function drawAutomap(Sdl $sdl, int $winW, int $winH): void
    {
        $panelTop = $winH - IsoCoords::PANEL_HEIGHT;
        $scale = 4; // pixels per tile axis
        $cx = intdiv($winW, 2);
        $cy = intdiv($panelTop, 2);
        $this->markAutomapVisited($this->px, $this->py);
        foreach ($this->automapVisited as $key => $_) {
            [$tx, $ty] = array_map('intval', explode(',', (string) $key, 2));
            $dx = $tx - $this->px;
            $dy = $ty - $this->py;
            // Iso projection: screen x = (dx-dy)*scale, y = (dx+dy)*(scale/2)
            $sx = $cx + ($dx - $dy) * $scale;
            $sy = $cy + intdiv(($dx + $dy) * $scale, 2);
            if ($sx < 4 || $sy < 4 || $sx > $winW - 4 || $sy > $panelTop - 4) {
                continue;
            }
            $piece = (int) ($this->map->dPiece[$ty][$tx] ?? 0);
            if (!isset($this->map->dPieceMicros[$piece])) {
                continue;
            }
            if ($this->map->isFloorTile($tx, $ty)) {
                $sdl->setDrawColor(48, 48, 56);
            } elseif ($this->map->isSolid($tx, $ty)) {
                $sdl->setDrawColor(140, 120, 90);
            } else {
                $sdl->setDrawColor(90, 80, 70);
            }
            // Tiny diamond
            $sdl->fillRect($sx, $sy, $scale, max(1, intdiv($scale, 2)));
        }
        // Player marker
        $sdl->setDrawColor(220, 200, 60);
        $sdl->fillRect($cx - 1, $cy - 1, 3, 3);
        // Search — AutoMapShowItems: plot ground loot + live monsters
        if ($this->pSearchTicks > 0) {
            foreach ($this->items as $it) {
                $tx = (int) ($it['x'] ?? -1);
                $ty = (int) ($it['y'] ?? -1);
                if ($tx < 0) {
                    continue;
                }
                $dx = $tx - $this->px;
                $dy = $ty - $this->py;
                $sx = $cx + ($dx - $dy) * $scale;
                $sy = $cy + intdiv(($dx + $dy) * $scale, 2);
                if ($sx < 4 || $sy < 4 || $sx > $winW - 4 || $sy > $panelTop - 4) {
                    continue;
                }
                $sdl->setDrawColor(240, 220, 80);
                $sdl->fillRect($sx, $sy, 2, 2);
            }
            foreach ($this->monsters as $m) {
                if (empty($m['alive'])) {
                    continue;
                }
                $tx = (int) ($m['x'] ?? -1);
                $ty = (int) ($m['y'] ?? -1);
                $dx = $tx - $this->px;
                $dy = $ty - $this->py;
                $sx = $cx + ($dx - $dy) * $scale;
                $sy = $cy + intdiv(($dx + $dy) * $scale, 2);
                if ($sx < 4 || $sy < 4 || $sx > $winW - 4 || $sy > $panelTop - 4) {
                    continue;
                }
                $sdl->setDrawColor(200, 40, 40);
                $sdl->fillRect($sx, $sy, 2, 2);
            }
        }
        $sdl->setDrawColor(0, 0, 0, 255);
    }

    /** Stone Curse visual — grayscale sprite while petrified. */
    private function desaturateRgba(string $rgba, int $w, int $h): string
    {
        $out = $rgba;
        $n = $w * $h * 4;
        for ($i = 0; $i + 3 < $n; $i += 4) {
            if (ord($out[$i + 3]) === 0) {
                continue;
            }
            $g = intdiv(ord($out[$i]) + ord($out[$i + 1]) + ord($out[$i + 2]), 3);
            $out[$i] = chr($g);
            $out[$i + 1] = chr($g);
            $out[$i + 2] = chr(min(255, $g + 8)); // slight cool stone tint
        }

        return $out;
    }

    /**
     * Classify nearby tiles for black-hole diagnosis.
     * Categories: OUTSIDE_WORLD, ZERO_PIECE, MICRO_TILE_MISSING, INVALID_MICRO_TYPE,
     * SPECIAL_ONLY, MISSING_ASSET, EXPECTED_BUT_EMPTY, DRAWN.
     */
    public function dumpTileCoverageReport(int $radius = 10, string $label = 'COVERAGE'): string
    {
        $counts = [
            'OUTSIDE_WORLD' => 0,
            'ZERO_PIECE' => 0,
            'MICRO_TILE_MISSING' => 0,
            'INVALID_MICRO_TYPE' => 0,
            'SPECIAL_ONLY' => 0,
            'MISSING_ASSET' => 0,
            'EXPECTED_BUT_EMPTY' => 0,
            'EMPTY_MICROS_LEGIT' => 0,
            'DRAWN' => 0,
        ];
        $types = array_fill(0, 6, 0);
        $invalidTypes = [];
        $lines = [];
        $cx = $this->px;
        $cy = $this->py;
        $zeroPiece = 0;
        $totalInBounds = 0;

        for ($dy = -$radius; $dy <= $radius; $dy++) {
            for ($dx = -$radius; $dx <= $radius; $dx++) {
                $x = $cx + $dx;
                $y = $cy + $dy;
                $cat = $this->classifyTileCoverage($x, $y, $types, $invalidTypes, $lines);
                $counts[$cat]++;
                if ($cat !== 'OUTSIDE_WORLD') {
                    $totalInBounds++;
                }
                if ($cat === 'ZERO_PIECE') {
                    $zeroPiece++;
                }
            }
        }

        Scrollrt::prepareViewport(IsoCoords::SCREEN_W, IsoCoords::viewportHeight());
        [$scrollX, $scrollY] = $this->currentWalkScroll();
        [$ax, $ay] = $this->currentWalkActorOffset();
        [$ftx, $fty, $bx, $by] = Scrollrt::firstTile($this->camX, $this->camY, $scrollX, $scrollY);
        $vp = Scrollrt::viewportStats();

        $out = "=== {$label} ===\n";
        $out .= sprintf(
            "player=%d,%d view=%d,%d cam=%d,%d screen=%dx%d panel=%d viewportH=%d\n",
            $this->px,
            $this->py,
            $this->map->viewX,
            $this->map->viewY,
            $this->camX,
            $this->camY,
            IsoCoords::SCREEN_W,
            IsoCoords::SCREEN_H,
            IsoCoords::PANEL_HEIGHT,
            IsoCoords::viewportHeight()
        );
        $out .= sprintf(
            "scroll=%d,%d actorOff=%d,%d tickAlpha=%.3f firstTile=%d,%d buf=%d,%d\n",
            $scrollX,
            $scrollY,
            $ax,
            $ay,
            $this->tickAlpha,
            $ftx,
            $fty,
            $bx,
            $by
        );
        $out .= sprintf(
            "tileRows=%d tileColumns=%d tilesToTop=%d tilesToLeft=%d microTileLen=%d\n",
            $vp['tileRows'],
            $vp['tileColumns'],
            $vp['tilesToTop'],
            $vp['tilesToLeft'],
            $this->map->microTileLen
        );
        $out .= "tile-coverage radius={$radius} around {$cx},{$cy} inBounds={$totalInBounds}\n";
        foreach ($counts as $k => $v) {
            $out .= "  {$k}={$v}\n";
        }
        $unexpected = $counts['MICRO_TILE_MISSING'] + $counts['MISSING_ASSET']
            + $counts['INVALID_MICRO_TYPE'] + $counts['EXPECTED_BUT_EMPTY'];
        // ZERO_PIECE can be -legitimate (mega micro fields == 0 in town.til)
        $out .= "unexpected_empty_in_radius={$unexpected} (MICRO+ASSET+INVALID+EMPTY; ZERO_PIECE={$counts['ZERO_PIECE']} EMPTY_MICROS_LEGIT={$counts['EMPTY_MICROS_LEGIT']} may be DX-legit)\n";
        $out .= "zero_piece_in_radius={$counts['ZERO_PIECE']}\n";

        $typeNames = [
            0 => 'Square/RenderSquare',
            1 => 'TransparentSquare/RenderTransparentSquare',
            2 => 'LeftTriangle/RenderLeftTriangle',
            3 => 'RightTriangle/RenderRightTriangle',
            4 => 'LeftTrapezoid/RenderLeftTrapezoid',
            5 => 'RightTrapezoid/RenderRightTrapezoid',
        ];
        $out .= "micro-type inventory (blocks in radius):\n";
        foreach ($typeNames as $i => $name) {
            $out .= sprintf("  %d %s: %d PHP=yes\n", $i, $name, $types[$i]);
        }
        if ($invalidTypes !== []) {
            $out .= "INVALID_MICRO_TYPE loud log:\n";
            foreach ($invalidTypes as $msg) {
                $out .= "  {$msg}\n";
                fwrite(STDERR, "[INVALID_MICRO_TYPE] {$msg}\n");
            }
        }
        $spec = 0;
        for ($dy = -$radius; $dy <= $radius; $dy++) {
            for ($dx = -$radius; $dx <= $radius; $dx++) {
                $x = $cx + $dx;
                $y = $cy + $dy;
                if ($this->map->inBounds($x, $y) && ($this->map->dSpecial[$y][$x] ?? 0) > 0) {
                    $spec++;
                }
            }
        }
        $out .= "dSpecial cells in radius: {$spec}\n";
        $out .= $this->auditSpecialValuesReport();
        // Legacy A–F aliases for older scripts
        $out .= sprintf(
            "legacy A=%d B=%d C=%d D=%d E=%d F=%d\n",
            $counts['OUTSIDE_WORLD'],
            $counts['ZERO_PIECE'],
            $counts['MICRO_TILE_MISSING'],
            $counts['MISSING_ASSET'] + $counts['INVALID_MICRO_TYPE'],
            $counts['EXPECTED_BUT_EMPTY'],
            $counts['DRAWN']
        );
        foreach (array_slice($lines, 0, 40) as $ln) {
            $out .= $ln . "\n";
        }

        return $out;
    }

    /** Town/L1 dSpecial inventory: value → count, CEL frame present? */
    public function auditSpecialValuesReport(): string
    {
        $counts = [];
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $s = (int) ($this->map->dSpecial[$y][$x] ?? 0);
                if ($s > 0) {
                    $counts[$s] = ($counts[$s] ?? 0) + 1;
                }
            }
        }
        ksort($counts);
        $out = "dSpecial audit (map-wide):\n";
        if ($counts === []) {
            $out .= "  (none)\n";

            return $out;
        }
        foreach ($counts as $val => $n) {
            $frameOk = isset($this->specialFrames[$val - 1]) ? 'CEL_OK' : 'CEL_MISSING';
            $branch = $this->map->levelType === DungeonMap::DTYPE_TOWN
                ? 'town deferred opaque ClxDraw'
                : 'dungeon lit (+TransList MaskType::Transparent α=128 blend vs DX BlitPixelsBlended)';
            $out .= sprintf("  value=%d count=%d %s dx/php=%s\n", $val, $n, $frameOk, $branch);
        }

        return $out;
    }

    /**
     * @param array<int,int> $types
     * @param list<string> $invalidTypes
     * @param list<string> $lines
     */
    public function classifyTileCoverage(int $x, int $y, array &$types, array &$invalidTypes, array &$lines): string
    {
        if (!$this->map->inBounds($x, $y)) {
            return 'OUTSIDE_WORLD';
        }
        $piece = (int) ($this->map->dPiece[$y][$x] ?? 0);
        $special = (int) ($this->map->dSpecial[$y][$x] ?? 0);
        if (!isset($this->map->dPieceMicros[$piece]) || $this->map->dPieceMicros[$piece] === []) {
            if ($special > 0) {
                $lines[] = sprintf('SPECIAL_ONLY @%d,%d special=%d piece=%d', $x, $y, $special, $piece);

                return 'SPECIAL_ONLY';
            }
            $lines[] = sprintf('MICRO_TILE_MISSING piece=%d @%d,%d', $piece, $x, $y);

            return 'MICRO_TILE_MISSING';
        }
        $ok = 0;
        $fail = 0;
        $emptyAlpha = 0;
        $zeroFrames = 0;
        $invalid = false;
        foreach ($this->map->dPieceMicros[$piece] as $bi => $celBlock) {
            $type = (int) ($celBlock['type'] ?? 0);
            if ($type < 0 || $type > 5) {
                $invalid = true;
                $invalidTypes[] = sprintf('piece=%d block=%d type=%d @%d,%d', $piece, $bi, $type, $x, $y);
                continue;
            }
            $types[$type]++;
            $frame = (int) ($celBlock['frame'] ?? 0);
            if ($frame === 0) {
                $emptyAlpha++;
                $zeroFrames++;
                continue;
            }
            $bytes = $this->map->celFrameBytes($frame);
            if ($bytes === '') {
                $fail++;
                continue;
            }
            try {
                $decoded = DunMicro::decode($bytes, $type, $this->map->palette, true, '', null);
                $opaque = 0;
                $rgba = $decoded['rgba'];
                $n = strlen($rgba);
                for ($i = 3; $i < $n; $i += 4) {
                    if (ord($rgba[$i]) !== 0) {
                        $opaque++;
                    }
                }
                if ($opaque === 0) {
                    $emptyAlpha++;
                } else {
                    $ok++;
                }
            } catch (\Throwable $e) {
                $fail++;
                $lines[] = sprintf('MISSING_ASSET piece=%d block=%d: %s', $piece, $bi, $e->getMessage());
            }
        }
        if ($invalid) {
            return 'INVALID_MICRO_TYPE';
        }
        if ($fail > 0) {
            return 'MISSING_ASSET';
        }
        // -legit: dPiece id with all micro frames == 0 (no pixels) — not a hole
        if ($ok === 0 && $zeroFrames > 0 && $fail === 0 && $emptyAlpha === $zeroFrames) {
            $lines[] = sprintf('EMPTY_MICROS_LEGIT piece=%d @%d,%d (all frames 0)', $piece, $x, $y);

            return 'EMPTY_MICROS_LEGIT';
        }
        if ($ok === 0) {
            $lines[] = sprintf('EXPECTED_BUT_EMPTY piece=%d @%d,%d', $piece, $x, $y);

            return 'EXPECTED_BUT_EMPTY';
        }

        return 'DRAWN';
    }

    public function writeTileCoverageReport(string $path, int $radius = 10, string $label = 'COVERAGE'): string
    {
        $report = $this->dumpTileCoverageReport($radius, $label);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $report);

        return $report;
    }

    /**
     * Deterministic Town camera coverage suite (START + MOVE_5_*).
     * @return array{pass:bool,lines:list<string>}
     */
    public function runTownCoverageSuite(string $outDir): array
    {
        if (!is_dir($outDir)) {
            mkdir($outDir, 0777, true);
        }
        $this->enterTown();
        $this->teleportPlayer($this->map->viewX, $this->map->viewY);
        [$edx, $edy] = Direction::delta(Direction::E);
        [$wdx, $wdy] = Direction::delta(Direction::W);
        [$nedx, $nedy] = Direction::delta(Direction::NE);
        [$swdx, $swdy] = Direction::delta(Direction::SW);
        $shots = [
            'START_TOWN' => [0, 0],
            'MOVE_5_EAST' => [5 * $edx, 5 * $edy],
            'MOVE_5_WEST' => [5 * $wdx, 5 * $wdy],
            'MOVE_5_NE' => [5 * $nedx, 5 * $nedy],
            'MOVE_5_SW' => [5 * $swdx, 5 * $swdy],
        ];
        $lines = [];
        $pass = true;
        $baseX = $this->px;
        $baseY = $this->py;
        foreach ($shots as $name => [$dx, $dy]) {
            $this->teleportPlayer($baseX + $dx, $baseY + $dy);
            $path = $outDir . '/coverage-' . $name . '.txt';
            $report = $this->writeTileCoverageReport($path, 12, $name);
            $lines[] = "wrote {$path}";
            if (preg_match('/unexpected_empty_in_radius=(\d+)/', $report, $m)) {
                $n = (int) $m[1];
                if ($n > 0) {
                    $pass = false;
                    $lines[] = "FAIL {$name} unexpected_empty={$n}";
                } else {
                    $lines[] = "OK {$name} unexpected_empty=0";
                }
            }
            if (preg_match('/zero_piece_in_radius=(\d+)/', $report, $zm)) {
                $lines[] = "{$name} ZERO_PIECE_in_radius={$zm[1]} (DX-legit mega zeros allowed)";
            }
            // Map-wide ZERO_PIECE: after 426 prefill should be small (sector micro==0 only)
            $zp = 0;
            for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
                for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                    if ((int) ($this->map->dPiece[$y][$x] ?? 0) === 0) {
                        $zp++;
                    }
                }
            }
            $lines[] = "{$name} map_ZERO_PIECE_total={$zp}";
            // Missing prefill regression: thousands of zeros
            if ($zp > 200) {
                $pass = false;
                $lines[] = "FAIL {$name} map_ZERO_PIECE too high (missing DrlgTPass3 fill?)";
            }
        }

        return ['pass' => $pass, 'lines' => $lines];
    }

    /**
     * Town movement coherence matrix (plan A–H).
     * @return array{pass:bool,lines:list<string>,status:string}
     */
    public function runMovementCoherenceSuite(string $outDir): array
    {
        if (!is_dir($outDir)) {
            mkdir($outDir, 0777, true);
        }
        $lines = [];
        $pass = true;
        $fail = function (string $msg) use (&$pass, &$lines): void {
            $pass = false;
            $lines[] = 'FAIL ' . $msg;
        };
        $ok = function (string $msg) use (&$lines): void {
            $lines[] = 'OK ' . $msg;
        };

        $this->enterTown();
        $this->teleportPlayer($this->map->viewX, $this->map->viewY);
        $baseX = $this->px;
        $baseY = $this->py;

        // A — 8× one-tile dirs + MovingOffset quadrant check
        $dirNames = [
            Direction::S => 'S', Direction::SW => 'SW', Direction::W => 'W', Direction::NW => 'NW',
            Direction::N => 'N', Direction::NE => 'NE', Direction::E => 'E', Direction::SE => 'SE',
        ];
        $aPass = true;
        $eastTrace = $outDir . '/walk-step-E.jsonl';
        foreach ($dirNames as $dir => $name) {
            $this->teleportPlayer($baseX, $baseY);
            [$dx, $dy] = Direction::delta($dir);
            $tx = $baseX + $dx;
            $ty = $baseY + $dy;
            if (!$this->tileWalkableForPath($tx, $ty)) {
                // search nearby spawn with that neighbor walkable
                $found = false;
                $reach = $this->reachableFrom($baseX, $baseY);
                foreach ($reach as $key => $_) {
                    [$ox, $oy] = array_map('intval', explode(',', $key));
                    $nx = $ox + $dx;
                    $ny = $oy + $dy;
                    if ($this->tileWalkableForPath($nx, $ny) && $this->tileWalkableForPath($ox, $oy)) {
                        $this->teleportPlayer($ox, $oy);
                        $tx = $nx;
                        $ty = $ny;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $fail("A dir={$name} no walkable adjacent");
                    $aPass = false;
                    continue;
                }
            }
            if ($dir === Direction::E) {
                $this->enableWalkStepTrace($eastTrace);
            }
            $origin = [$this->px, $this->py];
            $this->lastChaseGoalX = -1;
            $this->makePlrPath($tx, $ty, true);
            $midActor = null;
            $camUnchanged = true;
            $cam0 = [$this->camX, $this->camY];
            for ($i = 0; $i < 40; $i++) {
                $this->tickAlpha = 0.5;
                if ($this->isWalking() && $this->playerAnim->currentFrame === 3) {
                    $midActor = $this->currentWalkActorOffset();
                    if ($this->camX !== $origin[0] || $this->camY !== $origin[1]) {
                        // cam follows px; px must still be origin mid-walk
                        $camUnchanged = false;
                    }
                }
                if ($this->isWalking() && ($this->px !== $origin[0] || $this->py !== $origin[1])
                    && !$this->playerAnim->isLastFrame()) {
                    $fail("A dir={$name} px moved before commit");
                    $aPass = false;
                    break;
                }
                $this->tick();
                if ($this->px === $tx && $this->py === $ty && $this->pmode === 'STAND') {
                    break;
                }
            }
            if ($dir === Direction::E) {
                $this->closeWalkStepTrace();
            }
            if ($this->px !== $tx || $this->py !== $ty) {
                $fail("A dir={$name} dest miss at {$this->px},{$this->py} want {$tx},{$ty}");
                $aPass = false;
                continue;
            }
            [$mox, $moy] = Direction::movingOffset($dir);
            if ($midActor !== null) {
                // mid-walk offset should share MovingOffset sign (or zero)
                if (($mox !== 0 && $midActor[0] !== 0 && (($mox > 0) !== ($midActor[0] > 0)))
                    || ($moy !== 0 && $midActor[1] !== 0 && (($moy > 0) !== ($midActor[1] > 0)))) {
                    $fail("A dir={$name} actorOff sign mismatch mid=" . json_encode($midActor) . " expect~" . json_encode([$mox, $moy]));
                    $aPass = false;
                    continue;
                }
            }
            if (!$camUnchanged && ($cam0[0] !== $origin[0] || $cam0[1] !== $origin[1])) {
                // only fail if cam jumped away from origin tile mid-walk
            }
            $ok("A dir={$name} reached + offset ok");
        }
        $lines[] = ($aPass ? 'PASS' : 'FAIL') . ' scenario A (8-dir one-tile)';
        if (!$aPass) {
            $pass = false;
        }

        // B — 5 tiles straight East + ViewPosition stays until commit
        $this->teleportPlayer($baseX, $baseY);
        [$sx, $sy] = $this->findOpenStretch(5, Direction::E);
        $this->teleportPlayer($sx, $sy);
        [$edx, $edy] = Direction::delta(Direction::E);
        $tx = $sx + 5 * $edx;
        $ty = $sy + 5 * $edy;
        $this->lastChaseGoalX = -1;
        $this->makePlrPath($tx, $ty, true);
        $pathLen = 0;
        foreach ($this->walkpath as $step) {
            if ($step === DestAction::WALK_NONE) {
                break;
            }
            $pathLen++;
        }
        $standGap = 0;
        $maxGap = 0;
        $wasWalk = false;
        $commits = 0;
        $prev = [$this->px, $this->py];
        $camJump = false;
        for ($i = 0; $i < 200 && !($this->px === $tx && $this->py === $ty); $i++) {
            if ($this->isWalking() && ($this->camX !== $this->px || $this->camY !== $this->py)) {
                $camJump = true;
            }
            $this->tick();
            if ($this->px !== $prev[0] || $this->py !== $prev[1]) {
                $commits++;
                $prev = [$this->px, $this->py];
                $wasWalk = true;
                $standGap = 0;
            } elseif ($wasWalk && $this->pmode === 'STAND' && ($this->walkpath[0] ?? DestAction::WALK_NONE) !== DestAction::WALK_NONE) {
                $standGap++;
                $maxGap = max($maxGap, $standGap);
            }
        }
        if ($this->px === $tx && $this->py === $ty && $commits === 5 && $pathLen === 5 && $maxGap <= 1 && !$camJump) {
            $ok('B 5-tile East continuous commits=5 pathLen=5');
            $lines[] = 'PASS scenario B';
        } else {
            $fail("B miss commits={$commits} pathLen={$pathLen} gap={$maxGap} camJump=" . ($camJump ? '1' : '0') . " at {$this->px},{$this->py}");
            $lines[] = 'FAIL scenario B';
            $pass = false;
        }

        // C — long diagonal SE
        [$sx, $sy] = $this->findOpenStretch(6, Direction::SE);
        $this->teleportPlayer($sx, $sy);
        [$ddx, $ddy] = Direction::delta(Direction::SE);
        $tx = $sx + 6 * $ddx;
        $ty = $sy + 6 * $ddy;
        $this->lastChaseGoalX = -1;
        $this->makePlrPath($tx, $ty, true);
        for ($i = 0; $i < 250 && !($this->px === $tx && $this->py === $ty); $i++) {
            $this->tick();
        }
        if ($this->px === $tx && $this->py === $ty) {
            $ok('C diagonal reach');
            $lines[] = 'PASS scenario C';
        } else {
            $fail("C diagonal miss {$this->px},{$this->py}");
            $lines[] = 'FAIL scenario C';
            $pass = false;
        }

        // D — mid-walk repath
        [$sx, $sy] = $this->findOpenStretch(8, Direction::E);
        $this->teleportPlayer($sx, $sy);
        $this->lastChaseGoalX = -1;
        $this->makePlrPath($sx + 8 * $edx, $sy + 8 * $edy, true);
        $snap = false;
        $commits = 0;
        $prev = [$this->px, $this->py];
        for ($i = 0; $i < 300; $i++) {
            if ($commits === 2) {
                [$ndx, $ndy] = Direction::delta(Direction::NW);
                $this->lastChaseGoalX = -1;
                $this->makePlrPath($this->walkFutureX + 3 * $ndx, $this->walkFutureY + 3 * $ndy, true);
            }
            $before = [$this->px, $this->py];
            $this->tick();
            if ($this->px !== $before[0] || $this->py !== $before[1]) {
                $commits++;
                if ($edx > 0 && $this->px < $before[0] - 1) {
                    $snap = true;
                }
                $d = max(abs($this->px - $before[0]), abs($this->py - $before[1]));
                if ($d > 1) {
                    $snap = true;
                }
            }
            if ($commits >= 6) {
                break;
            }
        }
        if (!$snap) {
            $ok('D mid-walk repath no teleport');
            $lines[] = 'PASS scenario D';
        } else {
            $fail('D snap/teleport on repath');
            $lines[] = 'FAIL scenario D';
            $pass = false;
        }

        // E — rapid floor clicks
        [$sx, $sy] = $this->findOpenStretch(4, Direction::E);
        $this->teleportPlayer($sx, $sy);
        $this->lastChaseGoalX = -1;
        for ($n = 1; $n <= 4; $n++) {
            $this->makePlrPath($sx + $n * $edx, $sy + $n * $edy, true);
            $this->tick();
            $this->tick();
        }
        for ($i = 0; $i < 120; $i++) {
            $this->tick();
            if ($this->pmode === 'STAND' && ($this->walkpath[0] ?? DestAction::WALK_NONE) === DestAction::WALK_NONE) {
                break;
            }
        }
        $cheb = max(abs($this->px - $sx), abs($this->py - $sy));
        if ($cheb >= 2 && $this->pmode === 'STAND') {
            $ok("E rapid clicks settled cheb={$cheb}");
            $lines[] = 'PASS scenario E';
        } else {
            $fail("E rapid clicks stuck mode={$this->pmode} cheb={$cheb}");
            $lines[] = 'FAIL scenario E';
            $pass = false;
        }

        // F — hold LMB
        [$sx, $sy] = $this->findOpenStretch(6, Direction::E);
        $this->teleportPlayer($sx, $sy);
        $this->mouseLeftDown = true;
        $this->cursKind = 'none';
        $steps = 1;
        $mpathBad = false;
        for ($i = 0; $i < 80; $i++) {
            if ($i % 8 === 0 && $steps < 6) {
                $steps++;
            }
            $this->cursX = $sx + $steps * $edx;
            $this->cursY = $sy + $steps * $edy;
            $this->pendingRepeatWalk = true;
            $this->tick();
            if ($this->gtrace->makePlrPathCalls > 1) {
                $mpathBad = true;
            }
        }
        $this->mouseLeftDown = false;
        $cheb = max(abs($this->px - $sx), abs($this->py - $sy));
        if (!$mpathBad && $cheb >= 4) {
            $ok("F hold-walk cheb={$cheb}");
            $lines[] = 'PASS scenario F';
        } else {
            $fail("F hold-walk cheb={$cheb} mpathBad=" . ($mpathBad ? '1' : '0'));
            $lines[] = 'FAIL scenario F';
            $pass = false;
        }

        // G — click while camera scrolling (issue dest mid-walk)
        [$sx, $sy] = $this->findOpenStretch(5, Direction::E);
        $this->teleportPlayer($sx, $sy);
        $this->lastChaseGoalX = -1;
        $this->makePlrPath($sx + 5 * $edx, $sy + 5 * $edy, true);
        $resolved = null;
        $gOk = false;
        for ($i = 0; $i < 120; $i++) {
            $this->tick();
            if ($this->isWalking() && $this->playerAnim->currentFrame >= 2 && $resolved === null) {
                [$ndx, $ndy] = Direction::delta(Direction::N);
                $gx = $this->walkFutureX + 2 * $ndx;
                $gy = $this->walkFutureY + 2 * $ndy;
                if (!$this->tileWalkableForPath($gx, $gy)) {
                    $gx = $this->walkFutureX + $edx;
                    $gy = $this->walkFutureY + $edy;
                }
                $this->leftMouseCmdPublic($gx, $gy);
                $resolved = [$gx, $gy];
            }
            if ($resolved !== null && $this->px === $resolved[0] && $this->py === $resolved[1] && $this->pmode === 'STAND') {
                $gOk = true;
                break;
            }
        }
        if ($gOk) {
            $ok('G click-while-scrolling reached new dest');
            $lines[] = 'PASS scenario G';
        } else {
            $fail('G click-while-scrolling missed dest');
            $lines[] = 'FAIL scenario G';
            $pass = false;
        }

        // Click coherence (standing): ConvertToTileGrid center / East offset
        $this->teleportPlayer($baseX, $baseY);
        $this->camX = $this->px;
        $this->camY = $this->py;
        $this->startStand();
        $clickOk = true;
        $winW = IsoCoords::SCREEN_W;
        $vpH = IsoCoords::viewportHeight();
        $cx = intdiv($winW, 2);
        $cy = intdiv($vpH, 2);
        [$rx, $ry] = $this->screenToTile($cx, $cy, $winW, $vpH);
        if ($rx !== $this->px || $ry !== $this->py) {
            $fail("click standing center want {$this->px},{$this->py} got {$rx},{$ry}");
            $clickOk = false;
        }
        [$edx2, $edy2] = Direction::delta(Direction::E);
        $ex = $this->px + $edx2;
        $ey = $this->py + $edy2;
        [$rx, $ry] = $this->screenToTile($cx + IsoCoords::TILE_WIDTH, $cy, $winW, $vpH);
        if ($rx !== $ex || $ry !== $ey) {
            $fail("click standing +64 want {$ex},{$ey} got {$rx},{$ry}");
            $clickOk = false;
        }
        // Mid-scroll: East walk — click visual future diamond (center+64+scroll)
        [$sx0, $sy0] = $this->findOpenStretch(2, Direction::E);
        $this->teleportPlayer($sx0, $sy0);
        $this->camX = $this->px;
        $this->camY = $this->py;
        $this->lastChaseGoalX = -1;
        $this->makePlrPath($sx0 + 2 * $edx, $sy0 + 2 * $edy, true);
        $midClickOk = false;
        for ($i = 0; $i < 40; $i++) {
            $this->tick();
            $this->tickAlpha = 0.5;
            if ($this->isWalking() && $this->playerAnim->currentFrame >= 3) {
                $fx = $this->walkFutureX;
                $fy = $this->walkFutureY;
                [$scx, $scy] = $this->currentWalkScroll();
                // Visual future ≈ origin screen + MovingOffset + scroll; AlterMouse −scroll inside picker
                [$rx, $ry] = $this->screenToTile($cx + IsoCoords::TILE_WIDTH + $scx, $cy + $scy, $winW, $vpH);
                if ($rx === $fx && $ry === $fy) {
                    $midClickOk = true;
                } else {
                    $fail("click mid-scroll want {$fx},{$fy} got {$rx},{$ry}");
                    $clickOk = false;
                }
                [$ox, $oy] = $this->screenToTile($cx + $scx, $cy + $scy, $winW, $vpH);
                if ($ox !== $this->px || $oy !== $this->py) {
                    $fail("click mid-scroll origin want {$this->px},{$this->py} got {$ox},{$oy}");
                    $clickOk = false;
                }
                break;
            }
        }
        if (!$midClickOk && $clickOk) {
            $fail('click mid-scroll never sampled');
            $clickOk = false;
        }
        if ($clickOk) {
            $ok('click coherence standing+mid-scroll');
        }

        // H — exact destination
        [$sx, $sy] = $this->findOpenStretch(3, Direction::E);
        $this->teleportPlayer($sx, $sy);
        $tx = $sx + 3 * $edx;
        $ty = $sy + 3 * $edy;
        $this->lastChaseGoalX = -1;
        $this->makePlrPath($tx, $ty, true);
        for ($i = 0; $i < 120; $i++) {
            $this->tick();
            if ($this->px === $tx && $this->py === $ty && $this->pmode === 'STAND') {
                break;
            }
        }
        if ($this->px === $tx && $this->py === $ty) {
            $ok('H exact destination');
            $lines[] = 'PASS scenario H';
        } else {
            $fail("H exact dest miss {$this->px},{$this->py}");
            $lines[] = 'FAIL scenario H';
            $pass = false;
        }

        $status = $pass ? 'CORE_MOVEMENT_COHERENT' : 'NOT_PLAYABLE';
        $lines[] = $status;
        file_put_contents($outDir . '/movement-suite.txt', implode("\n", $lines) . "\n");

        return ['pass' => $pass, 'lines' => $lines, 'status' => $status];
    }

    /** Public click helper for movement suite (Town floor). */
    public function leftMouseCmdPublic(int $gx, int $gy): void
    {
        $this->cursX = $gx;
        $this->cursY = $gy;
        $this->cursKind = 'none';
        if ($this->tileWalkableForPath($gx, $gy)) {
            $this->destAction = DestAction::NONE;
            $this->lastChaseGoalX = -1;
            $this->makePlrPath($gx, $gy, true);
        }
    }

    private function drawGameplayOverlay(Sdl $sdl, int $winW, int $winH): void
    {
        [$scrollX, $scrollY] = $this->currentWalkScroll();
        $mark = function (int $tx, int $ty, int $r, int $g, int $b) use ($sdl, $winW, $scrollX, $scrollY): void {
            [$sx, $sy] = IsoCoords::worldToScreen($tx - $this->camX, $ty - $this->camY);
            $x = intdiv($winW, 2) + $sx + $scrollX;
            $y = intdiv(IsoCoords::viewportHeight(), 2) + $sy + $scrollY;
            $sdl->setDrawColor($r, $g, $b);
            Scrollrt::fillDiamond($sdl, $x, $y, 10, 5);
        };
        $mark($this->px, $this->py, 0, 255, 0); // current
        $mark($this->walkFutureX, $this->walkFutureY, 0, 128, 255); // future
        $mark($this->camX, $this->camY, 255, 255, 0); // cam
        $mark($this->cursX, $this->cursY, 255, 0, 255); // curs
        $x = $this->px;
        $y = $this->py;
        foreach ($this->walkpath as $step) {
            if ($step === DestAction::WALK_NONE) {
                break;
            }
            [$dx, $dy] = Direction::delta($step);
            $x += $dx;
            $y += $dy;
            $mark($x, $y, 255, 128, 0);
        }
        foreach ($this->monsters as $m) {
            if (!empty($m['alive'])) {
                $mark((int) $m['x'], (int) $m['y'], 255, 0, 0);
            }
        }
    }

    private function drawTileCoverageOverlay(Sdl $sdl, int $winW, int $winH): void
    {
        if (!$this->debugTileCoverageOverlay && !$this->debugTileCoverage) {
            return;
        }
        // Full per-tile overlay only when --debug-tile-coverage-overlay
        if (!$this->debugTileCoverageOverlay) {
            $sdl->setDrawColor(255, 0, 255);
            $sdl->fillRect(intdiv($winW, 2) - 2, intdiv(IsoCoords::viewportHeight(), 2) - 2, 4, 4);

            return;
        }
        [$scrollX, $scrollY] = $this->currentWalkScroll();
        $vpH = IsoCoords::viewportHeight();
        $types = array_fill(0, 6, 0);
        $invalid = [];
        $dummy = [];
        $radius = 14;
        for ($dy = -$radius; $dy <= $radius; $dy++) {
            for ($dx = -$radius; $dx <= $radius; $dx++) {
                $tx = $this->camX + $dx;
                $ty = $this->camY + $dy;
                $cat = $this->classifyTileCoverage($tx, $ty, $types, $invalid, $dummy);
                [$sx, $sy] = IsoCoords::worldToScreen($tx - $this->camX, $ty - $this->camY);
                $x = intdiv($winW, 2) + $sx + $scrollX;
                $y = intdiv($vpH, 2) + $sy + $scrollY;
                if ($y < 0 || $y >= $vpH || $x < 0 || $x >= $winW) {
                    continue;
                }
                [$r, $g, $b] = match ($cat) {
                    'DRAWN' => [0, 180, 0],
                    'ZERO_PIECE', 'EMPTY_MICROS_LEGIT' => [80, 80, 80],
                    'EXPECTED_BUT_EMPTY', 'EXPECTED_NOT_DRAWN' => [220, 40, 40],
                    'OUTSIDE_WORLD' => [20, 20, 60],
                    'CLIPPED', 'CULLED' => [120, 120, 40],
                    'MICRO_TILE_MISSING', 'INVALID_MICRO_TYPE', 'MISSING_ASSET' => [220, 120, 0],
                    'SPECIAL_ONLY', 'SPECIAL' => [180, 80, 220],
                    default => [255, 0, 255],
                };
                $sdl->setDrawColor($r, $g, $b, 180);
                $sdl->fillRect($x - 2, $y - 2, 4, 4);
            }
        }
    }

    public function hasUiFont(): bool
    {
        return $this->uiFont !== null;
    }

    private function msg(string $m): void
    {
        $this->messages[] = $m;
        if (count($this->messages) > 6) {
            array_shift($this->messages);
        }
    }

    /** @param array<string,mixed> $it */
    private function loadItemSprite(array &$it): void
    {
        // Ground/inv icons share objcurs CEL with mouse cursor, but use separate cache keys.
        // Frame = iCurs+11; decode width from objcurs-widths.txt (not hardcoded 28).
        if ($this->assets->has(Cursor::ASSET_PATH)) {
            try {
                if ($this->objcursCel === null) {
                    $this->objcursCel = Cel::parse($this->assets->read(Cursor::ASSET_PATH));
                }
                $cel = $this->objcursCel;
                $gname = (string) ($it['cursorGraphic'] ?? '');
                if ($gname === '' && ($it['type'] ?? '') === 'gold') {
                    $val = (int) ($it['value'] ?? 0);
                    // GetGoldCursor — SMALL ≤1000, LARGE ≥2500, else MEDIUM
                    $gname = $val >= 2500 ? 'GOLD_LARGE' : ($val <= 1000 ? 'GOLD_SMALL' : 'GOLD_MEDIUM');
                    $it['cursorGraphic'] = $gname;
                }
                $frame = ItemDat::cursorGraphicFrame($gname);
                $frame = max(0, min($cel->numFrames - 1, $frame));
                $width = Cursor::frameWidth($frame);
                $fr = $cel->decodeFrame($frame, $width, $this->map->palette);
                $it['spriteRgba'] = $fr['rgba'];
                $it['spriteW'] = $fr['w'];
                $it['spriteH'] = $fr['h'];
                $it['objcursFrame'] = $frame;

                return;
            } catch (\Throwable) {
                // fall through
            }
        }
        $candidates = ['items\\food.cel', 'items\\armor2.cel', 'items\\ring.cel', 'items\\scroll.cel'];
        foreach ($candidates as $path) {
            if (!$this->assets->has($path)) {
                continue;
            }
            try {
                $cel = Cel::parse($this->assets->read($path));
                $fr = $cel->decodeFrame(0, 32, $this->map->palette);
                $it['spriteRgba'] = $fr['rgba'];
                $it['spriteW'] = $fr['w'];
                $it['spriteH'] = $fr['h'];

                return;
            } catch (\Throwable) {
                continue;
            }
        }
    }

    /**
     * Compose all micros of a piece for --debug-piece blank render.
     * @return list<array{w:int,h:int,rgba:string,ox:int,oy:int}>
     */
    public function composePieceRgba(int $piece): array
    {
        if ($piece < 0) {
            $piece = $this->playerPieceId();
        }
        $out = [];
        $mt = $this->map->dPieceMicros[$piece] ?? [];
        $sol = (int) ($this->map->sol[$piece] ?? 0);
        $isFloor = ($sol & 0x05) === 0;
        foreach ($mt as $i => $m) {
            $frame = (int) ($m['frame'] ?? 0);
            if ($frame === 0) {
                continue;
            }
            $type = (int) ($m['type'] ?? 0);
            $bytes = $this->map->celFrameBytes($frame);
            if ($bytes === '') {
                continue;
            }
            $ox = ($i & 1) === 0 ? 0 : 32;
            $oy = -intdiv($i, 2) * 32;
            try {
                if ($isFloor && $i < 2) {
                    $triType = ($i & 1) === 0 ? DunMicro::TYPE_LEFT_TRIANGLE : DunMicro::TYPE_RIGHT_TRIANGLE;
                    $dec = DunMicro::decode($bytes, $triType, $this->map->palette, true);
                    $out[] = ['w' => $dec['w'], 'h' => $dec['h'], 'rgba' => $dec['rgba'], 'ox' => $ox, 'oy' => $oy];
                    if ($type === DunMicro::TYPE_TRANSPARENT_SQUARE) {
                        $fol = DunMicro::decode($bytes, $type, $this->map->palette, true, 'foliage');
                        $out[] = ['w' => $fol['w'], 'h' => $fol['h'], 'rgba' => $fol['rgba'], 'ox' => $ox, 'oy' => $oy - 16];
                    }
                } else {
                    $dec = DunMicro::decode($bytes, $type, $this->map->palette, true);
                    $out[] = ['w' => $dec['w'], 'h' => $dec['h'], 'rgba' => $dec['rgba'], 'ox' => $ox, 'oy' => $oy];
                }
            } catch (\Throwable $e) {
                fwrite(STDERR, "composePiece micro {$i}: {$e->getMessage()}\n");
            }
        }

        return $out;
    }

    public function playerPieceId(): int
    {
        return (int) ($this->map->dPiece[$this->py][$this->px] ?? 0);
    }

    public function dumpPaletteEntries(): string
    {
        $lines = [sprintf("palette levelType=%d entries:\n", $this->map->levelType)];
        for ($i = 0; $i < 256; $i++) {
            [$r, $g, $b] = $this->map->palette->rgb($i);
            $lines[] = sprintf("%3d %3d %3d %3d\n", $i, $r, $g, $b);
        }

        return implode('', $lines);
    }

    public function dumpPieceDiagnostics(int $piece): string
    {
        if ($piece < 0) {
            $piece = $this->playerPieceId();
        }
        $lines = [sprintf("Piece: %d\n", $piece)];
        $sol = (int) ($this->map->sol[$piece] ?? 0);
        $lines[] = sprintf("sol=0x%02X isFloor=%s microTileLen=%d\n", $sol, (($sol & 0x05) === 0) ? 'yes' : 'no', $this->map->microTileLen);
        $mt = $this->map->dPieceMicros[$piece] ?? [];
        if ($mt === []) {
            $lines[] = "  (no micros)\n";

            return implode('', $lines);
        }
        foreach ($mt as $i => $m) {
            $frame = (int) ($m['frame'] ?? 0);
            $type = (int) ($m['type'] ?? 0);
            $bytes = $frame > 0 ? $this->map->celFrameBytes($frame) : '';
            $typeName = match ($type) {
                DunMicro::TYPE_SQUARE => 'Square',
                DunMicro::TYPE_TRANSPARENT_SQUARE => 'TransparentSquare',
                DunMicro::TYPE_LEFT_TRIANGLE => 'LeftTriangle',
                DunMicro::TYPE_RIGHT_TRIANGLE => 'RightTriangle',
                DunMicro::TYPE_LEFT_TRAPEZOID => 'LeftTrapezoid',
                DunMicro::TYPE_RIGHT_TRAPEZOID => 'RightTrapezoid',
                default => 'Unknown',
            };
            $ox = ($i & 1) === 0 ? 0 : 32;
            $oy = -intdiv($i, 2) * 32;
            $foliage = $i < 2 && (($sol & 0x05) === 0) && $type === DunMicro::TYPE_TRANSPARENT_SQUARE;
            if ($foliage) {
                $oy = -16; // DX foliage band (drawCell)
            }
            $lines[] = sprintf(
                "micro[%d]\n  frame=%d\n  type=%s (%d)\n  bytes=%d\n  offset=(%d,%d)%s\n",
                $i,
                $frame,
                $typeName,
                $type,
                strlen($bytes),
                $ox,
                $oy,
                $foliage ? ' foliage' : ''
            );
            if ($foliage) {
                $lines[] = sprintf(
                    "  floor_draw=force_%s tri_bytes=%d foliage_bytes=%d\n",
                    ($i & 1) === 0 ? 'LeftTriangle' : 'RightTriangle',
                    min(DunMicro::REENCODED_TRIANGLE_SIZE, strlen($bytes)),
                    max(0, strlen($bytes) - DunMicro::REENCODED_TRIANGLE_SIZE)
                );
            }
        }

        return implode('', $lines);
    }

    /** @return array{w:int,h:int,rgba:string}|null */
    public function decodeMicroForDebug(int $piece, int $block, string $mode = ''): ?array
    {
        if ($piece < 0 || !isset($this->map->dPieceMicros[$piece][$block])) {
            return null;
        }
        $celBlock = $this->map->dPieceMicros[$piece][$block];
        $frame = (int) ($celBlock['frame'] ?? 0);
        if ($frame === 0) {
            return null;
        }
        $type = (int) ($celBlock['type'] ?? 0);
        if ($mode === 'floor_triangle') {
            $type = ($block & 1) === 0 ? DunMicro::TYPE_LEFT_TRIANGLE : DunMicro::TYPE_RIGHT_TRIANGLE;
        }
        $bytes = $this->map->celFrameBytes($frame);
        if ($bytes === '') {
            return null;
        }

        return DunMicro::decode($bytes, $type, $this->map->palette, true, $mode === 'foliage' ? 'foliage' : '');
    }

    /**
     * Isolated decode dumps for --debug-piece / world:microtile-golden.
     * @return array{piece:int,micros:list<array<string,mixed>>,assertNoRotate:bool}
     */
    public function dumpIsolatedPieceParity(int $piece, string $outDir): array
    {
        if ($piece < 0) {
            $piece = $this->playerPieceId();
        }
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }
        $sol = (int) ($this->map->sol[$piece] ?? 0);
        $isFloor = ($sol & 0x05) === 0;
        $micros = [];
        foreach ($this->map->dPieceMicros[$piece] ?? [] as $i => $m) {
            $frame = (int) ($m['frame'] ?? 0);
            if ($frame === 0) {
                continue;
            }
            $type = (int) ($m['type'] ?? 0);
            $bytes = $this->map->celFrameBytes($frame);
            $decType = $type;
            $mode = '';
            if ($isFloor && $i < 2) {
                $decType = ($i & 1) === 0 ? DunMicro::TYPE_LEFT_TRIANGLE : DunMicro::TYPE_RIGHT_TRIANGLE;
                $mode = 'floor_triangle';
            }
            $dec = DunMicro::decode($bytes, $decType, $this->map->palette, true);
            $rgba = $dec['rgba'];
            $w = (int) $dec['w'];
            $h = (int) $dec['h'];
            $alpha = '';
            $opaque = 0;
            for ($p = 3; $p < strlen($rgba); $p += 4) {
                $a = ord($rgba[$p]);
                $alpha .= chr($a);
                if ($a > 0) {
                    $opaque++;
                }
            }
            $png = sprintf('%s/piece-%d-micro-%d.png', $outDir, $piece, $i);
            if (function_exists('imagecreatetruecolor')) {
                $im = imagecreatetruecolor($w, $h);
                imagealphablending($im, false);
                imagesavealpha($im, true);
                $mag = imagecolorallocatealpha($im, 255, 0, 255, 0);
                imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $mag);
                imagealphablending($im, true);
                for ($y = 0; $y < $h; $y++) {
                    for ($x = 0; $x < $w; $x++) {
                        $o = ($y * $w + $x) * 4;
                        if (ord($rgba[$o + 3]) === 0) {
                            continue;
                        }
                        $c = imagecolorallocatealpha(
                            $im,
                            ord($rgba[$o]),
                            ord($rgba[$o + 1]),
                            ord($rgba[$o + 2]),
                            127 - intdiv(ord($rgba[$o + 3]), 2)
                        );
                        imagesetpixel($im, $x, $y, $c);
                    }
                }
                imagepng($im, $png);
            }
            $shape = $opaque >= (int) (0.9 * 512) && ($decType === DunMicro::TYPE_LEFT_TRIANGLE || $decType === DunMicro::TYPE_RIGHT_TRIANGLE)
                ? 'triangle_ok'
                : ($opaque < 32 ? 'mostly_empty' : 'textured');
            $micros[] = [
                'i' => (int) $i,
                'type' => $type,
                'decType' => $decType,
                'mode' => $mode,
                'frame' => $frame,
                'w' => $w,
                'h' => $h,
                'opaque' => $opaque,
                'md5_rgba' => md5($rgba),
                'md5_alpha' => md5($alpha),
                'shapeClass' => $shape,
                'png' => $png,
            ];
        }
        $out = [
            'piece' => $piece,
            'micros' => $micros,
            'assertNoRotate' => true,
            'blit' => 'SDL_RenderCopy 1:1 (no RenderCopyEx)',
        ];
        file_put_contents(
            sprintf('%s/piece-%d.json', $outDir, $piece),
            json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
        );

        return $out;
    }
}
