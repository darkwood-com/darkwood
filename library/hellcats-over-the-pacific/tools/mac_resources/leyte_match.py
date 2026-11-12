#!/usr/bin/env python3
"""Leyte Gulf against Hellcats 1.0.3: how many of 1.0.3's decompiled functions appear in the Leyte Gulf
application (sources/mac-os-9/LeyteGulf.dsk) byte for byte, or once operands that move with the image
are masked (A5- and PC-relative displacements; for the overlay, absolute long operands too). A first
estimate for docs/topics/LEYTE_GULF.md: the masks are opcode lists, not the relocation tables, so a
"missing" function may still be the same code.

Usage: python3 tools/mac_resources/leyte_match.py
"""
import sys, json, re, struct
from pathlib import Path as _P
R=str(_P(__file__).resolve().parents[2])
sys.path.insert(0,R); sys.path.insert(0,R+'/tools/mac_resources')
from pathlib import Path
import hfs
from tools import resedit
ley=hfs.find(Path(R+'/sources/mac-os-9/LeyteGulf.dsk'),'Leyte Gulf:Leyte Gulf')
L={(e['type'],e['id']):e['payload'] for e in resedit.parse_fork(ley.rsrc)}
inv=json.load(open(R+'/docs/DECOMPILATION_INVENTORY.json'))['functions']
MASK={0x4EAD,0x4EBA,0x6100,0x41FA,0x43FA,0x45FA,0x47FA,0x49FA,0x4BFA,0x487A,0x4EFA,0x2F2D,0x486D,0x41ED,0x43ED,0x45ED,0x47ED,0x49ED,0x4BED,0x3B6D,0x2B6D,0x202D,0x222D,0x206D,0x226D,0x302D,0x322D,0x4A6D,0x426D,0x42AD,0x3F2D,0x1B6D,0x4AAD,0x2B40,0x3B40,0x2B41,0x3B41,0x2B47,0x3B47}
def pattern(code):
    out=b'';i=0
    while i<len(code):
        w=int.from_bytes(code[i:i+2],'big'); out+=re.escape(code[i:i+2]); i+=2
        if w in MASK and i+2<=len(code): out+=b'..'; i+=2
    return re.compile(out,re.S)
tot={'exact':0,'masked':0,'missing':0}; per={}; host_missing=[]
for seg in ['ID01','ID02','ID03','ID04','ID05','ID06','ID07','ID08','ID09']:
    n=int(seg[2:]); a=open(R+f'/sources/resedit-1-0-3/CODE/{seg}.bin','rb').read(); b=L[('CODE',n)]
    fs=sorted((int(f['original_address'],16),f['semantic_name'] or f['original_function']) for f in inv if f['segment']==seg)
    c={'exact':0,'masked':0,'missing':0}; miss=[]
    for k,(ad,name) in enumerate(fs):
        end=fs[k+1][0] if k+1<len(fs) else len(a); code=a[ad:end]
        if len(code)<6: continue
        if b.find(code)>=0: c['exact']+=1
        elif pattern(code).search(b): c['masked']+=1
        else: c['missing']+=1; miss.append(name); host_missing.append((code, b))
    per[seg]=(len(a),len(b),c,miss[:6])
    for k in c: tot[k]+=c[k]
for s,v in per.items(): print(s,v[0],'->',v[1],v[2],v[3])
print('total',tot)
print('data fork',len(ley.data), ley.data[:8].hex(), 'code size', hex(struct.unpack('>I',ley.data[4:8])[0]))
# overlay: Pacific Conflict functions (runtime address R = file byte R + 0x20) in the Leyte data fork,
# every long the overlay's first relocation table names (offset, flags; flag bit 7 = not relocated)
# masked in both images, and PC-relative displacements (BSR / JSR / LEA / PEA (d16,PC))
def overlay(img):
    code_size, data_size = struct.unpack('>II', img[4:12])
    first = struct.unpack('>I', img[24:28])[0] // 8
    at = 0x20 + code_size + data_size
    relocated = set()
    for i in range(first):
        off, flags = struct.unpack('>II', img[at + 8 * i:at + 8 * i + 8])
        if not (flags >> 7) & 1:
            relocated.update(range(off, off + 4))
    return img[0x20:0x20 + code_size], relocated
pcode, prel = overlay(open(R + '/sources/mac-os-9/Pacific Conflict', 'rb').read())
lcode, lrel = overlay(ley.data)
lmasked = bytes(0 if i in lrel else b for i, b in enumerate(lcode))
PCREL = {0x6100, 0x4EBA, 0x41FA, 0x43FA, 0x45FA, 0x47FA, 0x49FA, 0x4BFA, 0x487A, 0x4EFA}
def rpattern(start, code):
    out = b''; i = 0
    while i < len(code):
        if start + i in prel:                     # a relocated long: zeroed in Leyte's copy too
            out += b'\x00\x00'; i += 2; continue
        w = int.from_bytes(code[i:i + 2], 'big'); out += re.escape(code[i:i + 2]); i += 2
        if w in PCREL and i + 2 <= len(code) and start + i not in prel:
            out += b'..'; i += 2
    return re.compile(out, re.S)
fs = sorted((int(f['original_address'], 16), f['semantic_name'] or f['original_function']) for f in inv if f['segment'] == 'Pacific Conflict')
c = {'exact': 0, 'relocation-masked': 0, 'missing': 0}
for k, (ad, name) in enumerate(fs):
    end = fs[k + 1][0] if k + 1 < len(fs) else len(pcode); code = pcode[ad:end]
    if len(code) < 6: continue
    if lcode.find(code) >= 0: c['exact'] += 1
    elif rpattern(ad, code).search(lmasked): c['relocation-masked'] += 1
    else: c['missing'] += 1
print('overlay', len(pcode), '->', len(lcode), c, 'of', len(fs), 'relocations', len(prel) // 4, '->', len(lrel) // 4)

# The overlay functions still not found: is the instruction sequence (mnemonics, operands ignored)
# in Leyte's overlay? Same sequence = same code with other offsets or constants; none = rewritten.
try:
    import capstone
except ImportError:
    capstone = None
if capstone:
    md = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
    md.skipdata = True
    def mnemonics(code, base=0):
        return ' '.join(i.mnemonic for i in md.disasm(code, base)) + ' '
    leyte_stream = mnemonics(lcode)
    same_shape = rewritten = 0
    for k, (ad, name) in enumerate(fs):
        end = fs[k + 1][0] if k + 1 < len(fs) else len(pcode); code = pcode[ad:end]
        if len(code) < 6 or lcode.find(code) >= 0 or rpattern(ad, code).search(lmasked):
            continue
        if mnemonics(code) in leyte_stream: same_shape += 1
        else: rewritten += 1
    print('overlay not found:', same_shape, 'with the same instruction sequence,', rewritten, 'without')
    streams = {}
    host_same = host_new = 0
    for code, seg in host_missing:
        if id(seg) not in streams:
            streams[id(seg)] = mnemonics(seg)
        if mnemonics(code) in streams[id(seg)]: host_same += 1
        else: host_new += 1
    print('host not found:', host_same, 'with the same instruction sequence,', host_new, 'without')
