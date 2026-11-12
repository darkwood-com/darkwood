/* Hellcats 1.0.4: semantic reconstruction of the code its updater appends to CODE 2 (0x4e1a..0x6100,
 * sources/hellcats-1-0-4/CODE/ID02.bin; docs/reference/RESOURCE_ANALYSIS.md "Hellcats 1.0.4").
 * Every other ID02 function is 1.0.3's, byte for byte (sources/resedit-1-0-3/CODE/ID02.decomp.c).
 *
 * There is no Ghidra export for 1.0.4: each function below is reconstructed from the capstone
 * disassembly of the rebuilt segment, and its header names the byte range it covers. The code reads
 * as hand-written assembly (an early exit leaves registers unrestored, see is_channel_playing), so the
 * C follows the instructions, not a compiler's idiom. A function marked VERIFIED has been run on the
 * 68020 interpreter against the bytes over the cases of tools/original/check_1_0_4_sound.py (in QA);
 * the others are UNVERIFIED.
 *
 * The driver's variables live inside the segment, addressed from A4 = 0x4e1a (LEA 0x4e1a(PC),A4):
 *   driver + 0x81c  short   active: set by init, cleared by shutdown; every entry does nothing without it
 *   driver + 0x81e  ulong   integer step   (rate >> 16)
 *   driver + 0x822  ulong   fraction step  (rate << 16)
 *   driver + 0x826  ptr     the silence buffer every idle channel plays
 *   driver + 0x82a  4 x 0x2a8 bytes: the channel records (each: head, free head, then 24 nodes of
 *                   0x1c bytes from record + 0x08, linked into a ring: node 23's link, record + 0x2a0,
 *                   is node 0)
 *     record + 0x00 ptr     the current (head) node; node + 0x08 its sample buffer
 *     record + 0x04 ptr     the free-list head (nodes linked by +0x14), as in 1.0.3
 *   driver + 0x12ca ptr    the Sound Manager channel (SndNewChannel)
 *   driver + 0x12ce        the SndDoubleBufferHeader given to SndPlayDoubleBuffer, so
 *   driver + 0x12da / 0x12de  its dbhBufferPtr[0] / [1] (NewPtr), driver + 0x12e2 its dbhDoubleBack
 * Nodes keep 1.0.3's layout (SOUND_SYSTEM.md): +0x14 the next node in the list, +0x18 the continuation.
 * Channels are 0..3 here (1.0.3 had two).
 */

#define DRIVER        0x4e1a
#define ACTIVE        (*(short *)(DRIVER + 0x81c))
#define STEP_INT      (*(unsigned long *)(DRIVER + 0x81e))
#define STEP_FRAC     (*(unsigned long *)(DRIVER + 0x822))
#define SILENCE       (*(char **)(DRIVER + 0x826))
#define RECORD(ch)    ((char *)(DRIVER + 0x82a + (ch) * 0x2a8))
#define RECORD_HEAD(ch) (*(char **)(RECORD(ch) + 0x00))
#define RECORD_FREE(ch) (*(char **)(RECORD(ch) + 0x04))
#define SILENCE_LENGTH 0xe74   /* 3700 bytes: ten 370-byte frames */
#define CHANNEL       (*(SndChannelPtr *)(DRIVER + 0x12ca))
#define HEADER        (*(SndDoubleBufferHeader *)(DRIVER + 0x12ce))
#define BUFFER_0      (*(char **)(DRIVER + 0x12da))
#define BUFFER_1      (*(char **)(DRIVER + 0x12de))

/* 0x4fa6..0x5004: alloc_sound_buffer_node (1.0.4) [VERIFIED], called by the routines below
 * (JSR 0x4fa6(PC)), never through the jump table. 1.0.3's 0x3d50 on the new records: pops the head
 * of the channel's free list (record + 0x04 = node + 0x14) and fills it: +0x00 = start (cursor),
 * +0x04 = 0, +0x08 = buffer, +0x0c = start, +0x10 = end, +0x18 = advance ? node + 0x14 : node
 * (0 loops on itself). No empty-list check. It has no LEA of its own: A4 is the caller's driver
 * base. Returns the node. */
char *new_sound_node_104(long channel, char *buffer, long start, long end, short advance);
long shutdown_sound_104(void);
pascal void sound_double_back_104(SndChannelPtr channel, SndDoubleBufferPtr buffer);

#define FRAME 0x73a   /* 1,850 samples a buffer half */

/* Channels 1..3 at the start of a buffer (0x4e30..0x4ea0, one block each): their samples are read
 * from where the head node's cursor is, FRAME bytes straight, with no end check inside the frame; the
 * cursor moves FRAME on and, once at or past the node's end, the continuation (+0x18) becomes the
 * head with its cursor at its loop start (+0x0c). A sound ending inside a frame is read on past its
 * end, up to FRAME bytes: the padding the 1.0.4 sounds carry. Returns where to read from. */
static unsigned char *advance_fixed_channel_104(long channel)
{
  char *node = RECORD_HEAD(channel);
  unsigned long cursor = *(unsigned long *)(node + 0x00);
  unsigned char *from = *(unsigned char **)(node + 0x08) + cursor;

  cursor += FRAME;
  if ((long)cursor >= *(long *)(node + 0x10)) {
    node = *(char **)(node + 0x18);
    cursor = *(unsigned long *)(node + 0x0c);
    RECORD_HEAD(channel) = node;
  }
  *(unsigned long *)(node + 0x00) = cursor;
  return from;
}

/* 0x4e1a..0x4fa4: the double-back procedure [VERIFIED] (SndPlayDoubleBuffer calls it for each
 * buffer that played out; init_sound_system calls it to prime both). All registers saved; A4 = the
 * driver. FRAME samples into buffer->dbSoundData (+0x10), each the unsigned sum of the four channels'
 * bytes minus 0x180, its low byte kept (no clipping). Channels 1..3 as above. Channel 0 (the engine)
 * is resampled: its sample at the 32.32 position (head cursor . +0x04 fraction) advanced by the step
 * (driver + 0x81e . 0x822) each sample (ADD.L / ADDX.L). With more than 0xe74 bytes left in its node
 * (end - cursor, signed) the frame is mixed without end checks, two samples a pass; otherwise one at
 * a time, and a cursor at or past the end moves to the continuation's loop start (fraction 0, which
 * becomes the head) before the sample is read. The position goes back into the head node. Then
 * dbFlags |= dbBufferReady (ORI.W #1 on its low word) and dbNumFrames = FRAME. RTD #8.
 * Every register comes back but A0, which holds the buffer (loaded after the MOVEM restore).
 * 0x4f54..0x4f84 (a buffer filled with 0x80) is never reached. */
pascal void sound_double_back_104(SndChannelPtr channel, SndDoubleBufferPtr buffer)
{
  unsigned char *out = (unsigned char *)buffer + 0x10;
  unsigned char *ch1 = advance_fixed_channel_104(1);
  unsigned char *ch2 = advance_fixed_channel_104(2);
  unsigned char *ch3 = advance_fixed_channel_104(3);
  char *node = RECORD_HEAD(0);
  unsigned long position = *(unsigned long *)(node + 0x00);
  unsigned long fraction = *(unsigned long *)(node + 0x04);
  unsigned long end = *(unsigned long *)(node + 0x10);
  unsigned char *samples = *(unsigned char **)(node + 0x08);
  short n;
  /* decided once a buffer (0x4ebc..0x4ec6): the unchecked path can run past the end at rates > 2 */
  int checked = (long)(end - position) <= 0xe74;

  for (n = 0; n < FRAME; n++) {
    if (checked && (long)end <= (long)position) {
      node = *(char **)(node + 0x18);
      samples = *(unsigned char **)(node + 0x08);
      position = *(unsigned long *)(node + 0x0c);
      fraction = 0;
      end = *(unsigned long *)(node + 0x10);
      RECORD_HEAD(0) = node;
    }
    *out++ = (unsigned char)(samples[position] + *ch1++ + *ch2++ + *ch3++ - 0x180);
    /* the 64-bit position . fraction += step */
    if (fraction + STEP_FRAC < fraction)
      position += STEP_INT + 1;
    else
      position += STEP_INT;
    fraction += STEP_FRAC;
  }
  *(unsigned long *)(node + 0x00) = position;
  *(unsigned long *)(node + 0x04) = fraction;
  buffer->dbFlags |= dbBufferReady;
  buffer->dbNumFrames = FRAME;
}

char *new_sound_node_104(long channel, char *buffer, long start, long end, short advance)
{
  char *node = RECORD_FREE(channel);

  RECORD_FREE(channel) = *(char **)(node + 0x14);
  *(long *)(node + 0x00) = start;
  *(long *)(node + 0x04) = 0;
  *(char **)(node + 0x08) = buffer;
  *(long *)(node + 0x0c) = start;
  *(long *)(node + 0x10) = end;
  *(char **)(node + 0x18) = advance != 0 ? *(char **)(node + 0x14) : node;
  return node;
}

/* The node chain both entries below queue for a sample (`end` already shortened):
 * loop_end - loop_start < 370 (CMPI.L #$172 / BCC, unsigned): the sample, then looping silence;
 * otherwise the attack (start..loop_start), the sustain looping on itself (loop_start..loop_end)
 * and the release (loop_end..end). Returns the first node. */
static char *queue_sample_104(long channel, char *buffer, long start, long end, long loop_start, long loop_end)
{
  char *first;

  if ((unsigned long)(loop_end - loop_start) < 0x172) {
    first = new_sound_node_104(channel, buffer, start, end, 1);
    new_sound_node_104(channel, SILENCE, 0, SILENCE_LENGTH, 0);
  } else {
    first = new_sound_node_104(channel, buffer, start, loop_start, 1);
    new_sound_node_104(channel, buffer, loop_start, loop_end, 0);
    new_sound_node_104(channel, buffer, loop_end, end, 1);
  }
  return first;
}

/* 0x51fe..0x52f2: play_sample_on_channel (1.0.4) [VERIFIED], entered from 1.0.3's 0x3f54.
 * Guard as below (returns 0). Every channel but 0 (the engine) drops the last 0x73a = 1,850 bytes
 * (SUBI.L #$73a): exactly what each of the eight sounds the updater replaces gained over its 1.0.3
 * version, so 1.0.4 plays the same length. The queued chain (queue_sample_104, inline in the bytes)
 * becomes the channel's head. Returns 1. */
long play_sample_on_channel_104(long channel, char *buffer, long start, long end, long loop_start, long loop_end)
{
  if (ACTIVE == 0 || channel < 0 || channel >= 4)
    return 0;
  if (channel != 0)
    end -= 0x73a;
  RECORD_HEAD(channel) = queue_sample_104(channel, buffer, start, end, loop_start, loop_end);
  return 1;
}

/* 0x52f4..0x541c: enqueue_sample_on_channel (1.0.4) [VERIFIED], entered from 1.0.3's 0x4050.
 * The same chain, queued behind what plays: from the head, every node until the one whose next
 * (+0x14) is the free-list head makes its continuation (+0x18) that next node, so the loops end and
 * playback runs into the new nodes. The head is unchanged. Returns 1 (0 from the guard). */
long enqueue_sample_on_channel_104(long channel, char *buffer, long start, long end, long loop_start, long loop_end)
{
  char *free_head;
  char *node;

  if (ACTIVE == 0 || channel < 0 || channel >= 4)
    return 0;
  if (channel != 0)
    end -= 0x73a;
  queue_sample_104(channel, buffer, start, end, loop_start, loop_end);
  free_head = RECORD_FREE(channel);
  for (node = RECORD_HEAD(channel); *(char **)(node + 0x14) != free_head; node = *(char **)(node + 0x14))
    *(char **)(node + 0x18) = *(char **)(node + 0x14);
  return 1;
}

/* 0x541e..0x547a: set_sound_playback_rate (1.0.4) [VERIFIED], entered from 1.0.3's 0x412e.
 * D7 = rate; CMPI.L #$8000 / BCC, CMPI.L #$30000 / BLS clamp it (unsigned) to 0.5 .. 3.0;
 * driver + 0x822 = rate LSL #16, driver + 0x81e = rate LSR #16. 1.0.3 also stored rate * 370 >> 16
 * (samples per VBL); the double buffer has no VBL frame, so 1.0.4 does not. Returns 0. */
long set_sound_playback_rate_104(unsigned long rate)
{
  if (ACTIVE == 0)
    return 0;
  if (rate < 0x8000)
    rate = 0x8000;
  else if (rate > 0x30000)
    rate = 0x30000;
  STEP_FRAC = rate << 16;
  STEP_INT = rate >> 16;
  return 0;
}

/* 0x547c..0x5504: release_channel_loop (1.0.4) [VERIFIED], entered from 1.0.3's 0x41a6.
 * Ignored unless active and 0 <= channel < 4 (TST.L / BLT, MOVEQ #4 / CMP / BGT). Queues a silence
 * node (0x4fa6 with the silence buffer, 0, SILENCE_LENGTH, 0), stores that node's link (+0x14) as the
 * free-list head again (0x4fa6 already did: the store is redundant), then walks from the head to the new node making each node's continuation (+0x18)
 * its next node (+0x14): the sustain loops end and the channel runs into the silence. Returns 0. */
long release_channel_loop_104(long channel)
{
  char *silence_node;
  char *node;

  if (ACTIVE == 0 || channel < 0 || channel >= 4)
    return 0;
  silence_node = new_sound_node_104(channel, SILENCE, 0, SILENCE_LENGTH, 0);
  RECORD_FREE(channel) = *(char **)(silence_node + 0x14);
  for (node = RECORD_HEAD(channel); node != silence_node; node = *(char **)(node + 0x14))
    *(char **)(node + 0x18) = *(char **)(node + 0x14);
  return 0;
}

/* 0x5506..0x5566: silence_sound_channel (1.0.4) [VERIFIED], entered from 1.0.3's 0x4242.
 * Same guard; the channel's head becomes a new silence node (0x4fa6 as above). Returns 0. */
long silence_sound_channel_104(long channel)
{
  if (ACTIVE == 0 || channel < 0 || channel >= 4)
    return 0;
  RECORD_HEAD(channel) = new_sound_node_104(channel, SILENCE, 0, SILENCE_LENGTH, 0);
  return 0;
}

/* 0x5568..0x55b8: is_channel_playing (1.0.4) [VERIFIED], entered from 1.0.3's 0x42b0.
 * 1 when the channel's head node plays something other than the silence buffer
 * (MOVE.L ([$82a,A4,D0.L],$8) / CMP.L $826(A4) / SNE / NEG.B / EXTB.L), else 0.
 * The guard's exit (0x5588: MOVEQ #0 / BRA to UNLK) skips the MOVEM that restores D2/A4/A5, so the
 * caller gets A4 = 0x4e1a back on that path; UNLK still restores A7. Returns 0 or 1. */
long is_channel_playing_104(long channel)
{
  if (ACTIVE == 0 || channel < 0 || channel >= 4)
    return 0;
  return *(char **)(RECORD_HEAD(channel) + 0x08) != SILENCE ? 1 : 0;
}

/* 0x5006..0x51fc: init_sound_system (1.0.4) [VERIFIED], entered from 1.0.3's 0x3dd0.
 * Shuts an active driver down first (JSR 0x55ba). Each of the four records gets its 24 nodes linked
 * by +0x14 into a ring (node 23's link, record + 0x2a0, stored first, is node 0), with head and free
 * head at node 0.
 * Step 1.0. The silence buffer: NewPtrClear(0xe74) (trap $A31E), filled with 0x80; then a looping
 * silence node on each channel (0x4fa6, which pops node 0: the head). The SndDoubleBufferHeader:
 * mono, 8-bit, no compression, 22254.545 Hz (0x56ee8ba3), double-back = 0x4e1a. SndNewChannel(
 * &channel, sampledSynth 5, init 0x8c, no callback); two NewPtrClear(0x74c) buffers, each filled once
 * by the double-back procedure; SndPlayDoubleBuffer (_SoundDispatch 0x00200008). Any failure returns
 * 0 at once (what was allocated stays); success sets active and returns 1. */
long init_sound_system_104(void)
{
  long ch;
  short i;
  char *nodes;
  char *byte;
  short n;

  if (ACTIVE != 0)
    shutdown_sound_104();
  for (ch = 0; ch < 4; ch++) {
    nodes = RECORD(ch) + 0x08;
    *(char **)(RECORD(ch) + 0x2a0) = nodes;
    RECORD_FREE(ch) = nodes;
    RECORD_HEAD(ch) = nodes;
    for (i = 0; i < 0x17; i++)
      *(char **)(nodes + i * 0x1c + 0x14) = nodes + (i + 1) * 0x1c;
  }
  STEP_INT = 1;
  STEP_FRAC = 0;
  SILENCE = NewPtrClear(SILENCE_LENGTH);
  if (SILENCE == 0)
    return 0;
  byte = SILENCE;
  for (n = SILENCE_LENGTH; --n >= 0; )
    *byte++ = 0x80;
  for (ch = 0; ch < 4; ch++)
    new_sound_node_104(ch, SILENCE, 0, SILENCE_LENGTH, 0);
  HEADER.dbhNumChannels = 1;
  HEADER.dbhSampleSize = 8;
  HEADER.dbhCompressionID = 0;
  HEADER.dbhPacketSize = 0;
  HEADER.dbhSampleRate = 0x56ee8ba3;
  HEADER.dbhDoubleBack = (SndDoubleBackUPP)DRIVER;
  if (SndNewChannel(&CHANNEL, 5, 0x8c, 0) != 0)
    return 0;
  BUFFER_0 = NewPtrClear(0x74c);
  if (BUFFER_0 == 0)
    return 0;
  sound_double_back_104(CHANNEL, BUFFER_0);
  BUFFER_1 = NewPtrClear(0x74c);
  if (BUFFER_1 == 0)
    return 0;
  sound_double_back_104(CHANNEL, BUFFER_1);
  if (SndPlayDoubleBuffer(CHANNEL, &HEADER) != 0)
    return 0;
  ACTIVE = 1;
  return 1;
}

/* 0x55ba..0x5632: shutdown_sound (1.0.4) [VERIFIED], entered from 1.0.3's 0x4318.
 * Nothing unless active. Silences channels 0, 1 and 2 (JSR 0x5506; channel 3 is left as it is),
 * disposes of the Sound Manager channel at once (SndDisposeChannel, quietNow true) and clears it,
 * disposes of each double buffer that is set and of the silence buffer (DisposePtr), clearing each,
 * and clears the active word. Returns 0. */
long shutdown_sound_104(void)
{
  if (ACTIVE == 0)
    return 0;
  silence_sound_channel_104(0);
  silence_sound_channel_104(1);
  silence_sound_channel_104(2);
  SndDisposeChannel(CHANNEL, 1);
  CHANNEL = 0;
  if (BUFFER_0 != 0) {
    DisposePtr(BUFFER_0);
    BUFFER_0 = 0;
  }
  if (BUFFER_1 != 0) {
    DisposePtr(BUFFER_1);
    BUFFER_1 = 0;
  }
  DisposePtr(SILENCE);
  SILENCE = 0;
  ACTIVE = 0;
  return 0;
}

/* 0x5634: an RTS nothing calls (padding before the variables at 0x5636). */
