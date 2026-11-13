<?php

declare(strict_types=1);

namespace App\TokenFlow\Fixture;

final class FlowFixtureProvider
{
    /** @var array<string, string> */
    private const FIXTURES = [
        'flow-lexicon' => <<<'TEXT'
Flow Lexicon Comparison — Token Discipline vs Word Shortening
=============================================================

Many teams try to optimize context by abbreviating vocabulary:
  flow -> flw
  stream -> strm
  pipeline -> pln
  waterfall -> wf
  source -> src
  sink -> snk
  buffer -> buf
  current -> cur
  cascade -> csc
  delta -> dlt
  signal -> sig
  noise -> ns

This approach treats tokens as a spelling problem.

Stream discipline treats tokens as a movement problem:
  - How much data enters the source?
  - How many chunks cross each node?
  - What noise rides the current?
  - Is the buffer bounded?
  - Does the sink receive only the signal?

Word-level shortening saves a few characters per term but destroys readability
for humans and agents alike. A pipeline that strips ANSI noise, removes debug
traces, chunks at 300 characters, and applies a token budget removes thousands
of characters without mangling domain vocabulary.

Compare:
  "wf" vs a compressed log line that keeps "waterfall" but drops 47 repeated
  [DEBUG] timestamps.

The river does not shrink by renaming its banks.
The river shrinks by removing eddies, backwater, and redundant waves.

Semantic field reference:
  flow, stream, pipe, pipeline, source, sink, buffer, current, channel,
  waterfall, river, delta, wave, cascade, node, edge, chunk, token, signal,
  noise, context, budget
TEXT,
        'flow-engine-log' => <<<'TEXT'
\x1b[32m[2026-06-21 08:14:02.441]\x1b[0m \x1b[1m[INFO]\x1b[0m  FlowEngine boot — node=graph-main edge=entry
\x1b[36m[2026-06-21 08:14:02.442]\x1b[0m \x1b[1m[DEBUG]\x1b[0m Loading pipeline manifest: source -> chunk -> transform -> budget -> sink
\x1b[36m[2026-06-21 08:14:02.442]\x1b[0m \x1b[1m[DEBUG]\x1b[0m Loading pipeline manifest: source -> chunk -> transform -> budget -> sink
\x1b[36m[2026-06-21 08:14:02.443]\x1b[0m \x1b[1m[DEBUG]\x1b[0m Registering node "StreamSource" at depth=0
\x1b[36m[2026-06-21 08:14:02.443]\x1b[0m \x1b[1m[DEBUG]\x1b[0m Registering node "StreamSource" at depth=0
\x1b[36m[2026-06-21 08:14:02.444]\x1b[0m \x1b[1m[DEBUG]\x1b[0m Registering node "ChunkText" at depth=1
\x1b[36m[2026-06-21 08:14:02.444]\x1b[0m \x1b[1m[DEBUG]\x1b[0m Registering node "ApplyBudget" at depth=4
\x1b[33m[2026-06-21 08:14:02.445]\x1b[0m \x1b[1m[WARN]\x1b[0m  Buffer high-water mark: 8192 bytes in channel=stdout
\x1b[32m[2026-06-21 08:14:02.446]\x1b[0m \x1b[1m[INFO]\x1b[0m  Executing cascade wave=1 nodes=6
\x1b[36m[2026-06-21 08:14:02.446]\x1b[0m \x1b[1m[DEBUG]\x1b[0m   at FlowEngine.runNode(StreamSource.php:42)
\x1b[36m[2026-06-21 08:14:02.446]\x1b[0m \x1b[1m[DEBUG]\x1b[0m   at FlowEngine.runNode(StreamSource.php:42)
\x1b[36m[2026-06-21 08:14:02.446]\x1b[0m \x1b[1m[DEBUG]\x1b[0m   at PipelineGraph.traverse(edge=source|chunk)
\x1b[36m[2026-06-21 08:14:02.447]\x1b[0m \x1b[1m[TRACE]\x1b[0m  Ip pushed id=0001 payload=PipelineContext stream_len=4200
\x1b[36m[2026-06-21 08:14:02.447]\x1b[0m \x1b[1m[TRACE]\x1b[0m  Ip pushed id=0002 payload=PipelineContext stream_len=4200
\x1b[36m[2026-06-21 08:14:02.448]\x1b[0m \x1b[1m[DEBUG]\x1b[0m ChunkText split stream into 14 chunks size=300
\x1b[36m[2026-06-21 08:14:02.448]\x1b[0m \x1b[1m[DEBUG]\x1b[0m ChunkText split stream into 14 chunks size=300
\x1b[32m[2026-06-21 08:14:02.449]\x1b[0m \x1b[1m[INFO]\x1b[0m  StripAnsi removed 128 escape sequences
\x1b[36m[2026-06-21 08:14:02.450]\x1b[0m \x1b[1m[DEBUG]\x1b[0m RemoveNoise dropped 22 duplicate lines
\x1b[36m[2026-06-21 08:14:02.450]\x1b[0m \x1b[1m[DEBUG]\x1b[0m RemoveNoise dropped 22 duplicate lines
\x1b[32m[2026-06-21 08:14:02.451]\x1b[0m \x1b[1m[INFO]\x1b[0m  ApplyBudget limit=1000 estimated_before=3105 estimated_after=820
\x1b[36m[2026-06-21 08:14:02.452]\x1b[0m \x1b[1m[DEBUG]\x1b[0m StreamSink flushed 8 chunks to channel=stdout
\x1b[36m[2026-06-21 08:14:02.452]\x1b[0m \x1b[1m[DEBUG]\x1b[0m StreamSink flushed 8 chunks to channel=stdout
\x1b[32m[2026-06-21 08:14:02.453]\x1b[0m \x1b[1m[INFO]\x1b[0m  FlowEngine complete — delta_tokens=-2285 reduction=73.6%
\x1b[36m[2026-06-21 08:14:02.454]\x1b[0m \x1b[1m[TRACE]\x1b[0m  at FlowEngine.shutdown() in engine-loop.php:88
\x1b[36m[2026-06-21 08:14:02.454]\x1b[0m \x1b[1m[TRACE]\x1b[0m  at FlowEngine.shutdown() in engine-loop.php:88
TEXT,
        'stream-processing-trace' => <<<'TEXT'
=== stream-processing-trace (captured via process pipe) ===
pid=88421 pipe=stdout mode=blocking

[source]     opening stream from fixture://flow-engine-log
[source]     read 12420 bytes into buffer
[source]     stream ready — current offset=0

[pipe]       fd[0] -> fd[1] connected (machine-to-machine)
[pipe]       chunk boundary detector armed size=300

[chunk]      chunk[0] 300 bytes  tokens~75
[chunk]      chunk[1] 300 bytes  tokens~75
[chunk]      chunk[2] 300 bytes  tokens~75
[chunk]      chunk[3] 300 bytes  tokens~75
[chunk]      chunk[4] 300 bytes  tokens~75
[chunk]      chunk[5] 300 bytes  tokens~75
[chunk]      chunk[6] 120 bytes  tokens~30

[transform]  strip_ansi     removed=128 sequences
[transform]  remove_noise   dropped=22 lines
[transform]  normalize_ws   collapsed=15 blank runs

[budget]     limit=1000 accumulated=820 ACCEPT
[sink]       writing 8 chunks to stdout
[sink]       stream closed — total_out=3280 bytes

=== end trace ===
[DEBUG] redundant footer: stream-processing-trace v0.1.0 build=20260621
[DEBUG] redundant footer: stream-processing-trace v0.1.0 build=20260621
[TRACE] at PipeReader.readLoop() line 44
[TRACE] at PipeReader.readLoop() line 44
TEXT,
        'rag-context-dump' => <<<'TEXT'
RAG Context Dump — Flow Stream Orchestration
=============================================
retrieval_id=rag-7f3a2c  chunks=12  budget=1000

--- SECTION: Stream discipline ---
Flow orchestration moves data as a pipe, a stream, and a sequence of chunks.
Each node transforms the current; each edge routes signal and filters noise.
Budgeted context means the sink receives only what fits the token limit.

--- SECTION: Pipeline nodes ---
Source nodes emit raw stream data.
Transform nodes strip ANSI, remove debug noise, normalize whitespace.
Chunk nodes split the river into bounded segments.
Budget nodes enforce token limits before the sink.

--- SECTION: Useless boilerplate ---
This document was automatically generated by the retrieval subsystem.
Copyright (c) 2026 Flow Documentation Generator v3.1.4
All rights reserved. Redistribution prohibited without written consent.
Disclaimer: This content is provided for informational purposes only.
Disclaimer: This content is provided for informational purposes only.
Metadata: {"generator":"rag-dump","version":"3.1.4","checksum":"deadbeef"}

--- SECTION: Noise examples ---
[DEBUG] index rebuild complete — 847 documents scanned
[DEBUG] index rebuild complete — 847 documents scanned
[TRACE] at Retriever.scan() in indexer.php:112
[TRACE] at Retriever.scan() in indexer.php:112

--- SECTION: Actionable summary ---
Token optimization is stream discipline: smaller tool outputs, cleaner logs,
chunked data, measurable context, budgeted context, better routing, shorter
feedback loops. Do not shorten domain words — compress the stream.

--- SECTION: Footer ---
End of RAG context dump. Total sections=6. Retrieved at 2026-06-21T08:00:00Z.
End of RAG context dump. Total sections=6. Retrieved at 2026-06-21T08:00:00Z.
TEXT,
    ];

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys(self::FIXTURES);
    }

    public function get(string $key): string
    {
        if (!isset(self::FIXTURES[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown fixture "%s". Available: %s',
                $key,
                implode(', ', $this->keys()),
            ));
        }

        $content = self::FIXTURES[$key];

        if ($key === 'flow-engine-log') {
            $content = $this->expandEngineLog($content);
        }

        return $content;
    }

    private function expandEngineLog(string $base): string
    {
        $padding = '';
        for ($wave = 2; $wave <= 40; ++$wave) {
            $padding .= sprintf(
                "\n\\x1b[36m[2026-06-21 08:14:02.%03d]\\x1b[0m \\x1b[1m[DEBUG]\\x1b[0m Cascade wave=%d node=ChunkText edge=chunk|transform buffer=4096",
                $wave % 1000,
                $wave,
            );
            $padding .= sprintf(
                "\n\\x1b[36m[2026-06-21 08:14:02.%03d]\\x1b[0m \\x1b[1m[DEBUG]\\x1b[0m Cascade wave=%d node=ChunkText edge=chunk|transform buffer=4096",
                $wave % 1000,
                $wave,
            );
            $padding .= sprintf(
                "\n\\x1b[36m[2026-06-21 08:14:02.%03d]\\x1b[0m \\x1b[1m[TRACE]\\x1b[0m  at PipelineGraph.traverse(wave=%d) in engine-loop.php:%d",
                $wave % 1000,
                $wave,
                40 + $wave,
            );
        }

        return $base.$padding;
    }
}
