#!/usr/bin/env python3
"""A linear listing of a CODE routine (capstone 68020) that decodes CODE 1's switch helpers' inline
case tables and resumes after them, where a plain disassembly stops or misreads the table as code.
The table formats, from the helpers' own bytes (the same in 1.0.3 and Leyte Gulf):
- $2a (CODE 1 +0x96, sparse, word values): count.w, then count (offset.w, value.w) pairs, then the
  default offset.w; each target is its offset word's address plus the offset (0: loops on itself).
- $32 (CODE 1 +0xaa, sparse, long values): the same with value.l.
- $3a (CODE 1 +0xbe, dense): low.w, high.w, the default offset.w, then one offset.w per case.
A read-only evidence tool; nothing is written.

    python3 tools/leyte/switch_listing.py BIN START END
"""
import struct
import sys

import capstone

b = open(sys.argv[1], 'rb').read()
start, end = int(sys.argv[2], 16), int(sys.argv[3], 16)
md = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_BIG_ENDIAN | capstone.CS_MODE_M68K_020)
w = lambda a: struct.unpack('>h', b[a:a + 2])[0]
l = lambda a: struct.unpack('>i', b[a:a + 4])[0]
pc = start
while pc < end:
    ins = next(md.disasm(b[pc:pc + 10], pc), None)
    if ins is None:
        print(hex(pc), 'dc.w', hex(w(pc) & 0xffff))
        pc += 2
        continue
    print(hex(ins.address), ins.mnemonic, ins.op_str)
    pc += ins.size
    if ins.mnemonic == 'jsr' and ins.op_str in ('$2a(a5)', '$32(a5)', '$3a(a5)'):
        t = pc
        if ins.op_str == '$3a(a5)':
            lo, hi = w(t), w(t + 2)
            d = t + 4
            print('    dense %d..%d default -> %s' % (lo, hi, hex(d + w(d)) if w(d) else 'loop'))
            for k in range(hi - lo + 1):
                at = d + 2 + 2 * k
                print('      case %d -> %s' % (lo + k, hex(at + w(at)) if w(at) else 'default'))
            pc = d + 2 + 2 * (hi - lo + 1)
        else:
            size = 2 if ins.op_str == '$2a(a5)' else 4
            n = w(t)
            a = t + 2
            for k in range(n):
                off = w(a)
                val = w(a + 2) if size == 2 else l(a + 2)
                print('      case %r -> %s' % (chr(val) if 32 <= val < 127 else val, hex(a + off)))
                a += 2 + size
            print('      default -> %s' % (hex(a + w(a)) if w(a) else 'loop'))
            pc = a + 2
    if ins.mnemonic == 'rts' and pc >= end:
        break
