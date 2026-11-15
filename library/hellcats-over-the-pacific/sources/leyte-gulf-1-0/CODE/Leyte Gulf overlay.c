// Leyte Gulf overlay (Leyte Gulf:Leyte Gulf data fork), Ghidra 12.2 headless: Motorola 68000 BE, raw binary at base 0 (file offsets), tools/leyte/ghidra.

// FUN_00000020 @ 0x20

void FUN_00000020(undefined2 param_1)

{
  DAT_0001f7d0 = param_1;
  DAT_0001f874 = param_1;
  DAT_0001f8c6 = param_1;
  return;
}



// FUN_00000036 @ 0x36

undefined4 FUN_00000036(undefined4 param_1,undefined4 param_2,undefined4 param_3)

{
  FUN_0001f63a(param_2,param_1,param_3);
  return param_1;
}



// FUN_00000058 @ 0x58

void FUN_00000058(void)

{
  undefined4 uVar1;
  uint uVar2;
  char *pcVar3;
  undefined2 *puVar4;
  short *psVar5;
  
  uVar2 = 0;
  psVar5 = (short *)(s_GRAPHIC_SIMULATIONS_XXXXXXXXXXXX_0001f778 + 0x14);
  pcVar3 = s_GRAPHIC_SIMULATIONS_XXXXXXXXXXXX_0001f778 + 0x14;
  do {
    uVar1 = FUN_000020a0(0x52,pcVar3,0x52);
    uVar1 = FUN_00000036(uVar1);
    *(undefined4 *)(&DAT_0002074c + *psVar5 * 4) = uVar1;
    psVar5 = psVar5 + 0x29;
    pcVar3 = pcVar3 + 0x52;
    uVar2 = uVar2 + 1;
  } while (uVar2 < 7);
  uVar1 = FUN_00002080(0x1a,&DAT_0001f9ca,0x1a);
  DAT_0002076c = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001f9e4,0x1a);
  DAT_00020770 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001f9fe,0x1a);
  DAT_00020774 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fa18,0x1a);
  DAT_00020780 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fa32,0x1a);
  DAT_00020784 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fa4c,0x1a);
  DAT_00020778 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fa66,0x1a);
  DAT_0002077c = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fa80,0x1a);
  DAT_000207a8 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fd7a,0x1a);
  DAT_00020788 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x20,&DAT_0001fa9a,0x20);
  DAT_0002078c = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001faba,0x1a);
  DAT_00020798 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x20,&DAT_0001fad4,0x20);
  DAT_00020794 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x20,&DAT_0001faf4,0x20);
  DAT_00020790 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fb14,0x1a);
  DAT_0002079c = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fb2e,0x1a);
  DAT_000207a0 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fb48,0x1a);
  DAT_000207a4 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fb62,0x1a);
  DAT_000207ac = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fb7c,0x1a);
  DAT_000207b0 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fb96,0x1a);
  DAT_000207b4 = FUN_00000036(uVar1);
  uVar2 = 0;
  psVar5 = &DAT_0001fbb0;
  puVar4 = &DAT_0001fbb0;
  do {
    uVar1 = FUN_000020a0(0x1c,puVar4,0x1c);
    uVar1 = FUN_00000036(uVar1);
    *(undefined4 *)(&DAT_0002074c + *psVar5 * 4) = uVar1;
    psVar5 = psVar5 + 0xe;
    puVar4 = puVar4 + 0xe;
    uVar2 = uVar2 + 1;
  } while (uVar2 < 8);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fc90,0x1a);
  DAT_000207d8 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fcaa,0x1a);
  DAT_000207dc = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fcde,0x1a);
  DAT_000207e4 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fcf8,0x1a);
  DAT_000207e8 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fd12,0x1a);
  DAT_000207ec = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fd2c,0x1a);
  DAT_000207f0 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fcc4,0x1a);
  DAT_000207e0 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fd46,0x1a);
  DAT_000207f4 = FUN_00000036(uVar1);
  uVar1 = FUN_00002080(0x1a,&DAT_0001fd60,0x1a);
  DAT_000207f8 = FUN_00000036(uVar1);
  return;
}



// FUN_00000408 @ 0x408

undefined4 FUN_00000408(void)

{
  short *psVar1;
  
  psVar1 = (short *)(SUB_000207fc + 0xbe);
  do {
    if (*psVar1 != 0) {
      return 1;
    }
    psVar1 = psVar1 + 1;
  } while ((int)psVar1 <= SUB_000207fc + 0xd4);
  return 0;
}



// FUN_0000042c @ 0x42c

undefined2 FUN_0000042c(int param_1)

{
  int *piVar1;
  short sVar2;
  int iVar3;
  undefined4 *puVar4;
  short sVar5;
  int iVar6;
  undefined2 uVar7;
  undefined4 *puVar8;
  
  uVar7 = 0;
  iVar3 = 0x7fffffff;
  puVar4 = &DAT_00020804;
  sVar5 = DAT_00020b24._2_2_;
joined_r0x0000044e:
  do {
    do {
      while( true ) {
        do {
          sVar5 = sVar5 + -1;
          if (sVar5 == -1) {
            return uVar7;
          }
          puVar8 = puVar4 + 1;
          piVar1 = (int *)*puVar4;
          puVar4 = puVar8;
        } while (*piVar1 == 0);
        iVar6 = *piVar1;
        sVar2 = **(short **)(iVar6 + 4);
        if (sVar2 == 0x1b) break;
        if ((sVar2 != 0x1c) && (sVar2 != 0x20)) goto LAB_000004f4;
        iVar6 = FUN_000048a6(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
                             *(undefined4 *)(param_1 + 0xc),piVar1[1],piVar1[2],piVar1[3]);
        if ((iVar6 < 0x6720) && (iVar6 < iVar3)) {
          uVar7 = 0x100;
          iVar3 = iVar6;
        }
      }
    } while ((*(short *)(iVar6 + 0x698) != -1) && (*(short *)(iVar6 + 0x69a) == -1));
    iVar6 = FUN_000048a6(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
                         *(undefined4 *)(param_1 + 0xc),piVar1[1],piVar1[2],piVar1[3]);
  } while ((0x671f < iVar6) || (iVar3 <= iVar6));
  goto LAB_0000053a;
LAB_000004f4:
  if ((sVar2 == 0x19) || (sVar2 == 0x18)) {
    iVar6 = FUN_000048a6(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
                         *(undefined4 *)(param_1 + 0xc),piVar1[1],piVar1[2],piVar1[3]);
    if ((iVar6 < piVar1[6]) && (iVar6 < iVar3)) {
      if (sVar2 == 0x18) {
        uVar7 = 0x100;
        iVar3 = iVar6;
      }
      else {
LAB_0000053a:
        uVar7 = 0x200;
        iVar3 = iVar6;
      }
    }
  }
  goto joined_r0x0000044e;
}



// FUN_0000054c @ 0x54c

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000054c(void)

{
  int iVar1;
  int iVar2;
  
  iVar2 = ((int)*(short *)(*(int *)(SUB_00020b28 + 4) + 0x1c) * (int)*(short *)(SUB_000207fc + 0xb6)
          ) / 100;
  if (iVar2 < 0) {
    iVar2 = iVar2 + 1;
  }
  *(char *)(SUB_00020b28 + 0x691) = (char)(iVar2 >> 1);
  iVar2 = SUB_00020b28;
  *(undefined1 *)(SUB_00020b28 + 0x692) = *(undefined1 *)(SUB_00020b28 + 0x691);
  *(undefined2 *)(iVar2 + 0x694) = 0;
  if ((*(char *)(iVar2 + 0x691) == '\0') && (*(char *)(iVar2 + 0x692) == '\0')) {
    *(undefined1 *)(iVar2 + 0x691) = 1;
  }
  iVar1 = SUB_00020b28;
  iVar2 = SUB_000207fc;
  *(undefined1 *)(SUB_00020b28 + 0x68d) = *(undefined1 *)(SUB_000207fc + 0xb9);
  *(undefined1 *)(iVar1 + 0x690) = *(undefined1 *)(iVar2 + 0xbd);
  *(undefined1 *)(iVar1 + 0x68e) = *(undefined1 *)(iVar2 + 0xbb);
  *(short *)(iVar1 + 0x642) =
       *(char *)(iVar1 + 0x68e) * 5 +
       *(short *)(*(int *)(iVar1 + 4) + 0x1a) + *(char *)(iVar1 + 0x68d) * 0x10;
  *(undefined2 *)(iVar1 + 0x696) = 0x2a3;
  *(undefined1 *)(iVar1 + 0x684) = 0;
  *(undefined1 *)(iVar1 + 0x687) = 0;
  *(ushort *)(iVar1 + 0x682) = *(ushort *)(iVar1 + 0x682) & 0xfffc;
  *(ushort *)(iVar1 + 0x698) = *(ushort *)(iVar1 + 0x698) & 0xfc01;
  _DAT_00020b2c = 1;
  return;
}



// FUN_00000602 @ 0x602

void FUN_00000602(int *param_1)

{
  short sVar1;
  short *psVar2;
  int iVar3;
  
  if (-1 < *(short *)((int)param_1 + 0x52)) {
    iVar3 = FUN_0000042c(param_1);
    if (iVar3 != 0x200) {
      if (*(short *)((int)param_1 + 0x52) == 0) {
        return;
      }
      psVar2 = (short *)(*(short *)((int)param_1 + 0x52) * 2 + SUB_000207fc + 0xbc);
      *psVar2 = *psVar2 + 1;
      return;
    }
    if (*param_1 != 0) {
      sVar1 = **(short **)(*param_1 + 4);
      if (sVar1 == 0x10) {
        return;
      }
      if (sVar1 == 0x11) {
        return;
      }
      if (sVar1 == 0x12) {
        return;
      }
      if (sVar1 == 0x15) {
        return;
      }
    }
  }
  *(short *)(SUB_000207fc + 0xd6) = *(short *)(SUB_000207fc + 0xd6) + 1;
  return;
}



// FUN_00000696 @ 0x696

void FUN_00000696(int *param_1,short param_2,int param_3,undefined4 *param_4,undefined4 *param_5,
                 undefined4 *param_6,int param_7)

{
  int iVar1;
  uint uVar2;
  int iVar3;
  int *local_26;
  undefined1 auStack_22 [18];
  undefined4 local_10;
  undefined4 local_c;
  undefined4 local_8;
  
  local_26 = param_1;
  local_10 = *(undefined4 *)(param_3 + 2);
  local_c = *(undefined4 *)(param_3 + 6);
  local_8 = *(undefined4 *)(param_3 + 10);
  iVar3 = param_3 + 0x22;
  param_2 = param_2 + -1;
  if (param_2 != -1) {
    do {
      local_26 = local_26 + -1;
      iVar1 = *local_26;
      FUN_00002d02((int)*(short *)(iVar1 + 2),(int)*(short *)(iVar1 + 4),(int)*(short *)(iVar1 + 6),
                   auStack_22);
      FUN_00003418(1,&local_10,&local_10,auStack_22);
      (*(code *)&LAB_00002272_2)
                (1,&local_10,&local_10,*(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc),
                 *(undefined4 *)(iVar1 + 0x10));
      if (param_7 != 0) {
        FUN_00003418((int)*(short *)(param_3 + 0x1a),iVar3,param_7,auStack_22);
        uVar2 = (uint)*(short *)(param_3 + 0x18);
        (*(code *)&LAB_00002272_2)
                  ((int)*(short *)(param_3 + 0x1a),param_7,param_7,
                   *(int *)(iVar1 + 8) << (uVar2 & 0x3f),*(int *)(iVar1 + 0xc) << (uVar2 & 0x3f),
                   *(int *)(iVar1 + 0x10) << (uVar2 & 0x3f));
        iVar3 = param_7;
      }
      param_2 = param_2 + -1;
    } while (param_2 != -1);
  }
  *param_4 = local_10;
  *param_5 = local_c;
  *param_6 = local_8;
  return;
}



// FUN_0000079e @ 0x79e

void FUN_0000079e(code *param_1,undefined4 param_2,int *param_3,int param_4,int param_5)

{
  int iVar1;
  short sVar2;
  short sVar3;
  char *pcVar4;
  char *pcVar5;
  
  sVar3 = 0;
  if (0 < *(short *)(param_5 + 0x18)) {
    do {
      iVar1 = param_5 + 0x1a + sVar3 * 0x18;
      *param_3 = iVar1;
      pcVar4 = (char *)(*(int *)(SUB_000207fc + 0x8c) + *(int *)(iVar1 + 0x14));
      if (*pcVar4 == '\x03') {
        FUN_0000079e(param_1,param_2,param_3 + 1,param_4 + 1,pcVar4);
      }
      else {
        pcVar5 = pcVar4 + *(short *)(pcVar4 + 0x1e) * 0xc + *(short *)(pcVar4 + 0x1a) * 0xc + 0x22;
        sVar2 = 0;
        if (0 < *(short *)(pcVar4 + 0x1e)) {
          do {
            (*param_1)(param_2,pcVar4,pcVar5,param_3 + 1,param_4 + 1);
            pcVar5 = pcVar5 + (uint)(byte)pcVar5[8] * 2 + 0xe;
            sVar2 = sVar2 + 1;
          } while (sVar2 < *(short *)(pcVar4 + 0x1e));
        }
      }
      sVar3 = sVar3 + 1;
    } while (sVar3 < *(short *)(param_5 + 0x18));
  }
  return;
}



// FUN_00000848 @ 0x848

void FUN_00000848(void)

{
  return;
}



// FUN_0000084a @ 0x84a

void FUN_0000084a(undefined4 param_1,undefined4 param_2)

{
  undefined1 auStack_2c [40];
  
  FUN_0000079e(param_1,param_2,auStack_2c,0,
               *(int *)(SUB_000207fc + 0x8c) +
               *(int *)(*(int *)(SUB_000207fc + 0x8c) + *(short *)(SUB_000207fc + 0x90) * 0x18 +
                       0x8e));
  return;
}



// FUN_00000890 @ 0x890

void FUN_00000890(int *param_1,int param_2,undefined4 *param_3,undefined4 param_4,short param_5)

{
  int *piVar1;
  short *psVar2;
  undefined2 uVar3;
  short sVar4;
  int iVar5;
  int aiStackY_6070c [32488];
  int aiStackY_40b6c [65231];
  int local_fd8;
  int local_fd4;
  int local_fd0;
  undefined1 auStack_fcc [1120];
  int local_b6c [280];
  int local_70c [450];
  
  iVar5 = *SUB_00020b28;
  if (((*(byte *)(param_2 + 0x16) & 0x10) == 0) || ((*(ushort *)(param_2 + 0x16) & 0x240) != 0)) {
    if ((*(byte *)((int)param_3 + 9) & 0xf0) == 0x70) {
      FUN_00000696(param_4,(int)param_5,param_2,&local_fd0,&local_fd4,&local_fd8,local_70c);
      piVar1 = param_1 + 0x2d6;
      param_1 = param_1 + *(short *)piVar1 * 3 + 1;
      *(short *)piVar1 = *(short *)piVar1 + 1;
      *param_1 = local_fd0;
      param_1[1] = local_70c[*(short *)((int)param_3 + 0xe) * 3 + 1];
    }
    else {
      if ((*(char *)(param_2 + 1) != '\x19') && (*(char *)(param_2 + 1) != '\x18')) {
        return;
      }
      FUN_00000696(param_4,(int)param_5,param_2,&local_fd0,&local_fd4,&local_fd8,0);
      param_1[*(short *)((int)param_1 + 0xb5a) + 0x272] = *(int *)(param_2 + 0xe);
      *(undefined1 *)((int)param_1 + *(short *)((int)param_1 + 0xb5a) + 0x964) =
           *(undefined1 *)(param_2 + 1);
      psVar2 = (short *)((int)param_1 + 0xb5a);
      param_1 = param_1 + *psVar2 * 3 + 0x12d;
      *psVar2 = *psVar2 + 1;
      *param_1 = local_fd0;
      param_1[1] = local_fd4;
    }
    param_1[2] = local_fd8;
  }
  else {
    FUN_00000696(param_4,(int)param_5,param_2,&local_fd0,&local_fd4,&local_fd8,local_70c);
    FUN_00002294((int)*(short *)(param_2 + 0x1a),local_70c,local_70c,-*(int *)(iVar5 + 4),0,
                 -*(int *)(iVar5 + 0xc));
    sVar4 = 0;
    if (0 < *(short *)(param_2 + 0x1a)) {
      do {
        iVar5 = (int)sVar4;
        local_70c[iVar5 * 3] = local_70c[iVar5 * 3] / *param_1;
        local_70c[iVar5 * 3 + 2] = local_70c[iVar5 * 3 + 2] / *param_1;
        sVar4 = sVar4 + 1;
      } while (sVar4 < *(short *)(param_2 + 0x1a));
    }
    sVar4 = 0;
    if (*(char *)(param_3 + 2) != '\0') {
      do {
        iVar5 = (int)sVar4;
        local_b6c[iVar5 * 2] = local_70c[*(short *)((int)param_3 + iVar5 * 2 + 0xe) * 3];
        local_b6c[iVar5 * 2 + 1] = local_70c[*(short *)((int)param_3 + iVar5 * 2 + 0xe) * 3 + 2];
        sVar4 = sVar4 + 1;
      } while (sVar4 < (short)(ushort)*(byte *)(param_3 + 2));
    }
    sVar4 = FUN_0000c478(local_b6c,*(undefined1 *)(param_3 + 2),auStack_fcc);
    if (((sVar4 != 0) && (sVar4 = FUN_0000c52a(auStack_fcc,(int)sVar4,local_b6c), sVar4 != 0)) &&
       (sVar4 = FUN_0000c5dc(local_b6c,(int)sVar4,auStack_fcc), uVar3 = DAT_00020b2e,
       DAT_00020b2e = uVar3, sVar4 != 0)) {
      DAT_00020b2e = DAT_00020b30;
      sVar4 = FUN_0000c692(auStack_fcc,(int)sVar4,local_b6c);
      DAT_00020b2e = uVar3;
      if (sVar4 != 0) {
        (**(code **)(SUB_000207fc + 0x2ca))(local_b6c,(int)sVar4,*param_3);
      }
    }
  }
  return;
}



// FUN_00000b02 @ 0xb02

void FUN_00000b02(int param_1,code *param_2)

{
  int *piVar1;
  int iVar2;
  int iVar3;
  short sVar4;
  char cVar5;
  int iVar6;
  short sVar7;
  int iVar8;
  int iVar9;
  int aiStackY_60b5c [300];
  undefined4 auStackY_606ac [65861];
  int aiStackY_20198 [24551];
  char acStackY_81fc [30296];
  int local_b64;
  int local_b60;
  int local_b5c [300];
  undefined4 local_6ac [300];
  char local_1fc [100];
  int local_198 [100];
  short local_8;
  short local_6;
  
  iVar2 = *SUB_00020b28;
  local_b60 = param_1;
  local_6 = 0;
  local_8 = 0;
  FUN_0000084a(0x890,&local_b60);
  sVar7 = 0;
  if (0 < local_8) {
    do {
      iVar8 = (int)sVar7;
      piVar1 = local_b5c + iVar8 * 3;
      cVar5 = 'A';
      iVar6 = 1000000000;
      sVar4 = 0;
      if (0 < local_6) {
        do {
          iVar9 = (int)sVar4;
          iVar3 = FUN_000048a6(*piVar1,local_b5c[iVar8 * 3 + 1],local_b5c[iVar8 * 3 + 2],
                               local_6ac[iVar9 * 3],local_6ac[iVar9 * 3 + 1],
                               local_6ac[iVar9 * 3 + 2]);
          if ((iVar3 < local_198[iVar9]) && (iVar3 < iVar6)) {
            iVar6 = iVar3;
            if (local_b5c[iVar8 * 3 + 1] < 1) {
              if (local_1fc[sVar4] == '\x19') {
                cVar5 = 'B';
              }
              else if (local_1fc[sVar4] == '\x18') {
                cVar5 = '\0';
              }
            }
            else {
              cVar5 = 'C';
            }
          }
          sVar4 = sVar4 + 1;
        } while (sVar4 < local_6);
      }
      if (cVar5 != '\0') {
        sVar4 = 0;
        if (0 < sVar7) {
          do {
            iVar6 = (int)sVar4;
            iVar6 = FUN_000048a6(*piVar1,local_b5c[iVar8 * 3 + 1],local_b5c[iVar8 * 3 + 2],
                                 local_b5c[iVar6 * 3],local_b5c[iVar6 * 3 + 1],
                                 local_b5c[iVar6 * 3 + 2]);
            if (iVar6 < 0xce40) {
              cVar5 = '\0';
              break;
            }
            sVar4 = sVar4 + 1;
          } while (sVar4 < sVar7);
        }
        if (cVar5 != '\0') {
          (*param_2)(cVar5,(*piVar1 - *(int *)(iVar2 + 4)) / param_1,
                     (*(int *)(iVar2 + 0xc) - local_b5c[iVar8 * 3 + 2]) / param_1);
        }
      }
      sVar7 = sVar7 + 1;
    } while (sVar7 < local_8);
  }
  iVar6 = 1000000000;
  switch(*(undefined2 *)(SUB_000207fc + 0x90)) {
  case 2:
    iVar6 = 0x4991d4;
    local_b64 = -0x1d324c;
    break;
  case 3:
    iVar6 = 0x5cf903;
    local_b64 = -0x3e0e;
    break;
  case 6:
    iVar6 = 0x58dd38;
    local_b64 = -0x14f794;
    break;
  case 7:
    iVar6 = 0x768a2f;
    local_b64 = -0xa58cc;
    break;
  case 9:
    iVar6 = 0x590fe9;
    local_b64 = -0x1720c1;
  }
  if (iVar6 != 1000000000) {
    (*param_2)(0x54,(iVar6 - *(int *)(iVar2 + 4)) / param_1,
               (*(int *)(iVar2 + 0xc) - local_b64) / param_1);
  }
  return;
}



// FUN_00000d00 @ 0xd00

void FUN_00000d00(undefined4 param_1,int param_2,int param_3,undefined4 param_4,short param_5)

{
  int iVar1;
  short sVar2;
  int iVar3;
  int *piVar4;
  int aiStackY_6070c [98285];
  undefined1 auStack_724 [4];
  undefined1 auStack_720 [4];
  undefined1 auStack_71c [4];
  int local_718;
  int local_714;
  int local_710;
  int aiStack_70c [450];
  
  if (((*(byte *)(param_3 + 9) & 0xf0) == 0x80) && (*(char *)(param_2 + 1) == '\0')) {
    FUN_00000696(param_4,(int)param_5,param_2,auStack_71c,auStack_720,auStack_724,aiStack_70c);
    iVar1 = param_3 + 0xe;
    local_710 = 0;
    local_714 = 0;
    local_718 = 0;
    sVar2 = 0;
    if (*(char *)(param_3 + 8) != '\0') {
      do {
        iVar3 = (int)sVar2;
        local_718 = (aiStack_70c[*(short *)(iVar1 + iVar3 * 2) * 3] >>
                    (*(ushort *)(param_2 + 0x18) & 0x3f)) + local_718;
        local_714 = (aiStack_70c[*(short *)(iVar1 + iVar3 * 2) * 3 + 1] >>
                    (*(ushort *)(param_2 + 0x18) & 0x3f)) + local_714;
        local_710 = (aiStack_70c[*(short *)(iVar1 + iVar3 * 2) * 3 + 2] >>
                    (*(ushort *)(param_2 + 0x18) & 0x3f)) + local_710;
        sVar2 = sVar2 + 1;
      } while (sVar2 < (short)(ushort)*(byte *)(param_3 + 8));
    }
    local_718 = local_718 / (int)(uint)*(byte *)(param_3 + 8);
    local_714 = local_714 / (int)(uint)*(byte *)(param_3 + 8) + 100;
    local_710 = local_710 / (int)(uint)*(byte *)(param_3 + 8);
    if (DAT_00020b32 == DAT_00020b34) {
      DAT_00020b32 = DAT_00020b32 + 10;
      DAT_00020b38 = FUN_000021e0(DAT_00020b38,DAT_00020b32 * 0xc);
    }
    piVar4 = (int *)(DAT_00020b34 * 0xc + DAT_00020b38);
    *piVar4 = local_718;
    piVar4[1] = local_714;
    piVar4[2] = local_710;
    DAT_00020b34 = DAT_00020b34 + 1;
  }
  return;
}



// FUN_00000e72 @ 0xe72

undefined4 FUN_00000e72(int *param_1,int *param_2)

{
  short sVar1;
  int iVar2;
  short *psVar3;
  int *piVar4;
  
  if ((*(byte *)((int)param_1 + 0x17) & 2) == 0) {
    if (*param_1 != 0) {
      iVar2 = *param_1;
      if (((0x1a < **(short **)(iVar2 + 4)) && (**(short **)(iVar2 + 4) < 0x23)) &&
         (*(char *)(iVar2 + 0x619) != '\0')) {
        FUN_00017dac(iVar2);
      }
    }
    iVar2 = *(int *)((int)param_1 + 0x4a);
    sVar1 = 0;
    if (0 < *(short *)(param_1 + 0xf)) {
      psVar3 = (short *)(iVar2 + 0x14);
      piVar4 = (int *)(iVar2 + 0x18);
      do {
        if (*(char *)(iVar2 + 0x16) == -0x80) {
          if ((*(byte *)(iVar2 + 0x13) & 2) != 0) {
            return 0;
          }
          param_2[2] = 0;
          param_2[1] = 0;
          *param_2 = 0;
          sVar1 = 0;
          if (0 < *psVar3) {
            do {
              iVar2 = (int)sVar1;
              *param_2 = (*(int *)(*(int *)((int)param_1 + 0x46) +
                                  *(short *)(*piVar4 + iVar2 * 2) * 0xc) >>
                         (*(ushort *)((int)param_1 + 0x36) & 0x3f)) + *param_2;
              param_2[1] = (*(int *)(*(int *)((int)param_1 + 0x46) + 4 +
                                    *(short *)(*piVar4 + iVar2 * 2) * 0xc) >>
                           (*(ushort *)((int)param_1 + 0x36) & 0x3f)) + param_2[1];
              param_2[2] = (*(int *)(*(int *)((int)param_1 + 0x46) + 8 +
                                    *(short *)(*piVar4 + iVar2 * 2) * 0xc) >>
                           (*(ushort *)((int)param_1 + 0x36) & 0x3f)) + param_2[2];
              sVar1 = sVar1 + 1;
            } while (sVar1 < *psVar3);
          }
          if (param_2[1] < 1) {
            return 0;
          }
          *param_2 = *param_2 / (int)*psVar3;
          param_2[1] = param_2[1] / (int)*psVar3 + 100;
          param_2[2] = param_2[2] / (int)*psVar3;
          return 1;
        }
        psVar3 = psVar3 + 0x10;
        piVar4 = piVar4 + 8;
        iVar2 = iVar2 + 0x20;
        sVar1 = sVar1 + 1;
      } while (sVar1 < *(short *)(param_1 + 0xf));
    }
  }
  return 0;
}



// FUN_00000f9c @ 0xf9c

bool FUN_00000f9c(undefined4 *param_1)

{
  undefined4 uVar1;
  int iVar2;
  short sVar3;
  int iVar4;
  undefined4 *puVar5;
  undefined4 local_10;
  undefined4 local_c;
  undefined4 local_8;
  
  if (DAT_00020b38 == (undefined4 *)0x0) {
    DAT_00020b32 = 10;
    DAT_00020b38 = (undefined4 *)FUN_000020a0(0x78);
    FUN_0000084a(0xd00,param_1);
  }
  iVar4 = 1000000000;
  sVar3 = 0;
  puVar5 = DAT_00020b38;
  if (0 < DAT_00020b34) {
    do {
      iVar2 = FUN_0000483e(*puVar5,puVar5[1],puVar5[2]);
      if (iVar2 < iVar4) {
        *param_1 = *puVar5;
        param_1[1] = puVar5[1];
        param_1[2] = puVar5[2];
        iVar4 = iVar2;
      }
      puVar5 = puVar5 + 3;
      sVar3 = sVar3 + 1;
    } while (sVar3 < DAT_00020b34);
  }
  DAT_00020b3c = 0;
  sVar3 = 0;
  if (0 < DAT_00020b24) {
    puVar5 = &DAT_00020804;
    do {
      uVar1 = *puVar5;
      iVar2 = FUN_00000e72(uVar1,&local_10);
      if ((iVar2 != 0) && (iVar2 = FUN_0000483e(local_10,local_c,local_8), iVar2 < iVar4)) {
        *param_1 = local_10;
        param_1[1] = local_c;
        param_1[2] = local_8;
        iVar4 = iVar2;
        DAT_00020b3c = uVar1;
      }
      sVar3 = sVar3 + 1;
      puVar5 = puVar5 + 1;
    } while (sVar3 < DAT_00020b24);
  }
  return iVar4 < 0x40740;
}



// FUN_000010ae @ 0x10ae

void FUN_000010ae(int *param_1,int param_2,int param_3,int param_4,short param_5)

{
  int *piVar1;
  short *psVar2;
  int iVar3;
  int aiStackY_60708 [98285];
  int local_724;
  int local_720;
  int local_71c [4];
  undefined1 auStack_70c [4];
  int aiStack_708 [449];
  
  if (*(short *)((int)param_1 + 0x1e) == 0) {
    if ((*(char *)(param_2 + 1) == '\x01') && (iVar3 = (int)param_5, iVar3 < param_1[8])) {
      param_1[8] = iVar3;
      FUN_00000696(param_4,iVar3,param_2,local_71c,&local_720,&local_724,0);
      *(int *)*param_1 = local_71c[0];
      *(int *)(*param_1 + 4) = local_720;
      *(int *)(*param_1 + 8) = local_724;
      iVar3 = *(int *)(param_4 + -4);
      *(int *)(*param_1 + 0xc) = (int)*(short *)(iVar3 + 2);
      *(int *)(*param_1 + 0x10) = (int)*(short *)(iVar3 + 4);
      *(int *)(*param_1 + 0x14) = (int)*(short *)(iVar3 + 6);
      *(undefined2 *)(*param_1 + 0x18) = 1;
    }
  }
  else if ((*(byte *)(param_3 + 9) & 0xf0) == 0x70) {
    FUN_00000696(param_4,(int)param_5,param_2,local_71c,&local_720,&local_724,auStack_70c);
    if (aiStack_708[*(short *)(param_3 + 0xe) * 3] < 1) {
      piVar1 = param_1 + 0x2de;
      param_1 = param_1 + *(short *)piVar1 * 3 + 9;
      *(short *)piVar1 = *(short *)piVar1 + 1;
      *param_1 = local_71c[0];
      param_1[1] = 0x43;
      param_1[2] = local_724;
    }
  }
  else if ((*(char *)(param_2 + 1) == '\x19') || (*(char *)(param_2 + 1) == '\x18')) {
    FUN_00000696(param_4,(int)param_5,param_2,local_71c,&local_720,&local_724,0);
    param_1[*(short *)((int)param_1 + 0xb7a) + 0x27a] = *(int *)(param_2 + 0xe);
    *(undefined1 *)((int)param_1 + *(short *)((int)param_1 + 0xb7a) + 0x984) =
         *(undefined1 *)(param_2 + 1);
    psVar2 = (short *)((int)param_1 + 0xb7a);
    param_1 = param_1 + *psVar2 * 3 + 0x135;
    *psVar2 = *psVar2 + 1;
    *param_1 = local_71c[0];
    param_1[1] = local_720;
    param_1[2] = local_724;
  }
  return;
}



// FUN_0000121a @ 0x121a

void FUN_0000121a(undefined4 *param_1,int param_2)

{
  undefined4 *puVar1;
  int iVar2;
  short sVar3;
  int iVar4;
  short sVar5;
  short unaff_D5w;
  int iVar6;
  int iVar7;
  undefined4 auStackY_60b5c [300];
  undefined4 auStackY_606ac [65861];
  int aiStackY_20198 [24551];
  char acStackY_81fc [30276];
  undefined4 *local_b80;
  undefined4 local_b7c;
  undefined4 local_b78;
  undefined4 local_b74;
  undefined4 uStack_b70;
  undefined4 uStack_b6c;
  undefined4 uStack_b68;
  undefined2 local_b64;
  undefined2 local_b62;
  int local_b60;
  undefined4 local_b5c [300];
  undefined4 local_6ac [300];
  char local_1fc [100];
  int local_198 [100];
  short local_8;
  short local_6;
  
  local_b62 = (undefined2)param_2;
  local_b60 = 1000000000;
  local_b80 = param_1;
  local_b7c = *param_1;
  local_b78 = param_1[1];
  local_b74 = param_1[2];
  uStack_b70 = param_1[3];
  uStack_b6c = param_1[4];
  uStack_b68 = param_1[5];
  local_b64 = *(undefined2 *)(param_1 + 6);
  local_8 = 0;
  local_6 = 0;
  FUN_0000084a(FUN_000010ae,&local_b80);
  if ((param_2 != 0) && (sVar5 = 0, 0 < local_8)) {
    do {
      iVar4 = 1000000000;
      iVar6 = (int)sVar5;
      puVar1 = local_b5c + iVar6 * 3;
      sVar3 = 0;
      if (0 < local_6) {
        do {
          iVar7 = (int)sVar3;
          iVar2 = FUN_000048a6(*puVar1,local_b5c[iVar6 * 3 + 1],local_b5c[iVar6 * 3 + 2],
                               local_6ac[iVar7 * 3],local_6ac[iVar7 * 3 + 1],
                               local_6ac[iVar7 * 3 + 2]);
          if ((iVar2 < local_198[iVar7]) && (iVar2 < iVar4)) {
            unaff_D5w = (short)local_1fc[iVar7];
            iVar4 = iVar2;
          }
          sVar3 = sVar3 + 1;
        } while (sVar3 < local_6);
      }
      iVar4 = FUN_000048a6(*puVar1,local_b5c[iVar6 * 3 + 1],local_b5c[iVar6 * 3 + 2],local_b7c,
                           local_b78,local_b74);
      if ((iVar4 < local_b60) && (unaff_D5w != 0x18)) {
        local_b60 = iVar4;
        *local_b80 = *puVar1;
        local_b80[1] = local_b5c[iVar6 * 3 + 1];
        local_b80[2] = local_b5c[iVar6 * 3 + 2];
        *(undefined2 *)(local_b80 + 6) = 1;
      }
      sVar5 = sVar5 + 1;
    } while (sVar5 < local_8);
  }
  return;
}



// FUN_00001350 @ 0x1350

int FUN_00001350(int param_1,int param_2)

{
  short sVar1;
  short sVar2;
  int iVar3;
  int iVar4;
  undefined4 *puVar5;
  short *psVar6;
  
  if ((*(char *)(param_1 + 0x28) == '\x02') && (iVar3 = FUN_0000042c(param_1), iVar3 == 0x200)) {
    iVar3 = *(int *)(param_1 + 0x4a);
    sVar1 = *(short *)(param_1 + 0x3c);
    while (sVar1 = sVar1 + -1, sVar1 != -1) {
      if (*(char *)(iVar3 + 0x16) == 'p') {
        psVar6 = *(short **)(iVar3 + 0x18);
        sVar2 = *(short *)(iVar3 + 0x14);
        while (sVar2 = sVar2 + -1, sVar2 != -1) {
          puVar5 = (undefined4 *)(*(int *)(param_1 + 0x4e) + *psVar6 * 0xc);
          iVar4 = FUN_000048a6(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
                               *(undefined4 *)(param_1 + 0xc),*puVar5,puVar5[1],puVar5[2]);
          if (iVar4 < param_2) {
            param_2 = iVar4;
          }
          psVar6 = psVar6 + 1;
        }
      }
      iVar3 = iVar3 + 0x20;
    }
  }
  return param_2;
}



// FUN_000013e8 @ 0x13e8

void FUN_000013e8(void)

{
  undefined4 *puVar1;
  short sVar2;
  undefined4 uVar3;
  
  uVar3 = 0x7fffffff;
  puVar1 = &DAT_00020804;
  sVar2 = DAT_00020b24._2_2_;
  while (sVar2 = sVar2 + -1, sVar2 != -1) {
    uVar3 = FUN_00001350(*puVar1,uVar3);
    puVar1 = puVar1 + 1;
  }
  puVar1 = &DAT_00020b44;
  sVar2 = DAT_00020f2c._2_2_;
  while (sVar2 = sVar2 + -1, sVar2 != -1) {
    uVar3 = FUN_00001350(*puVar1,uVar3);
    puVar1 = puVar1 + 1;
  }
  return;
}



// FUN_0000143c @ 0x143c

bool FUN_0000143c(int *param_1,undefined4 param_2)

{
  int iVar1;
  int iVar2;
  bool bVar3;
  
  iVar1 = *param_1;
  if (iVar1 == 0) {
    iVar2 = (**(code **)(SUB_000207fc + 0x302))(param_2);
    *param_1 = iVar2;
  }
  bVar3 = *(int *)*param_1 == 0;
  if (bVar3) {
    (**(code **)(SUB_000207fc + 0x30a))((int *)*param_1,param_2);
  }
  (**(code **)(SUB_000207fc + 0x31a))(*param_1);
  return bVar3 || iVar1 == 0;
}



// FUN_00001496 @ 0x1496

void FUN_00001496(undefined4 param_1)

{
  (**(code **)(SUB_000207fc + 0x30e))(param_1);
  (**(code **)(SUB_000207fc + 0x31a))(param_1);
  return;
}



// FUN_000014be @ 0x14be

void FUN_000014be(undefined4 param_1)

{
  (**(code **)(SUB_000207fc + 0x312))(param_1);
  (**(code **)(SUB_000207fc + 0x316))(param_1);
  return;
}



// FUN_000014e6 @ 0x14e6

void FUN_000014e6(void)

{
  (**(code **)(SUB_000207fc + 0x30e))(*(undefined4 *)(SUB_000207fc + 0x196));
  (**(code **)(SUB_000207fc + 0x30e))(*(undefined4 *)(SUB_000207fc + 0x1b0));
  (**(code **)(SUB_000207fc + 0x30e))(*(undefined4 *)(SUB_000207fc + 0x1b8));
  (**(code **)(SUB_000207fc + 0x30e))(*(undefined4 *)(SUB_000207fc + 0xdc));
  return;
}



// FUN_0000152c @ 0x152c

void FUN_0000152c(void)

{
  int iVar1;
  short sVar2;
  
  PTR_FUN_00027184 = (undefined *)0x0;
  if (*SUB_000207fc != '\0') {
    FUN_0000143c(&DAT_00020f30,0x8340);
  }
  if (*(short *)(SUB_000207fc + 0xe) != 1) {
    sVar2 = 1;
    do {
      if ((sVar2 == 1) || (*(short *)(SUB_000207fc + 0xb4) != 0)) {
        iVar1 = FUN_0000143c(&DAT_00020f34 + sVar2 * 4,*(short *)(SUB_000207fc + 0xc) * 0x78);
        if (iVar1 != 0) {
          PTR_FUN_00027184 = (undefined *)0x1;
        }
      }
      if ((sVar2 == 1) || (*(short *)(SUB_000207fc + 0xb4) != 0)) {
        iVar1 = FUN_0000143c(&DAT_00020f44 + sVar2 * 4,*(short *)(SUB_000207fc + 0xc) * 0x78);
        if (iVar1 != 0) {
          PTR_FUN_00027184 = (undefined *)0x1;
        }
      }
      sVar2 = sVar2 + 1;
    } while (sVar2 < 4);
  }
  return;
}



// FUN_000015fa @ 0x15fa

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_000015fa(void)

{
  short sVar1;
  int iVar2;
  
  if (*SUB_000207fc != '\0') {
    FUN_00001496(DAT_00020f30);
    _DAT_00020f54 = *DAT_00020f30;
    if (*(short *)(SUB_000207fc + 0x76) != 0) {
      _DAT_00020f54 = _DAT_00020f54 & 0xffffff;
    }
  }
  if (*(short *)(SUB_000207fc + 0xe) != 1) {
    sVar1 = 1;
    do {
      if ((sVar1 == 1) || (*(short *)(SUB_000207fc + 0xb4) != 0)) {
        iVar2 = (int)sVar1;
        FUN_00001496(*(undefined4 *)(&DAT_00020f34 + iVar2 * 4));
        *(undefined4 *)(&DAT_00020f5c + iVar2 * 4) = **(undefined4 **)(&DAT_00020f34 + iVar2 * 4);
        if (*(short *)(SUB_000207fc + 0x76) != 0) {
          *(uint *)(&DAT_00020f5c + iVar2 * 4) = *(uint *)(&DAT_00020f5c + iVar2 * 4) & 0xffffff;
        }
      }
      if ((sVar1 == 1) || (*(short *)(SUB_000207fc + 0xb4) != 0)) {
        iVar2 = (int)sVar1;
        FUN_00001496(*(undefined4 *)(&DAT_00020f44 + iVar2 * 4));
        *(undefined4 *)(&DAT_00020f6c + iVar2 * 4) = **(undefined4 **)(&DAT_00020f44 + iVar2 * 4);
        if (*(short *)(SUB_000207fc + 0x76) != 0) {
          *(uint *)(&DAT_00020f6c + iVar2 * 4) = *(uint *)(&DAT_00020f6c + iVar2 * 4) & 0xffffff;
        }
      }
      sVar1 = sVar1 + 1;
    } while (sVar1 < 4);
    DAT_00020f7c = DAT_00020f70;
    DAT_00020f80 = DAT_00020f60;
  }
  FUN_00007f32();
  return;
}



// FUN_000016fc @ 0x16fc

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_000016fc(void)

{
  short sVar1;
  int iVar2;
  
  if (DAT_00020f30 != 0) {
    FUN_000014be(DAT_00020f30);
    _DAT_00020f54 = 0;
  }
  sVar1 = 1;
  do {
    iVar2 = (int)sVar1;
    if (*(int *)(&DAT_00020f34 + iVar2 * 4) != 0) {
      FUN_000014be(*(undefined4 *)(&DAT_00020f34 + iVar2 * 4));
      *(undefined4 *)(&DAT_00020f5c + iVar2 * 4) = 0;
    }
    iVar2 = (int)sVar1;
    if (*(int *)(&DAT_00020f44 + iVar2 * 4) != 0) {
      FUN_000014be(*(undefined4 *)(&DAT_00020f44 + iVar2 * 4));
      *(undefined4 *)(&DAT_00020f6c + iVar2 * 4) = 0;
    }
    sVar1 = sVar1 + 1;
  } while (sVar1 < 4);
  DAT_00020f7c = 0;
  DAT_00020f80 = 0;
  return;
}



// FUN_0000177a @ 0x177a

void FUN_0000177a(int param_1,undefined4 param_2,undefined4 param_3)

{
  int iVar1;
  
  SUB_000207fc = param_1;
  FUN_00001f10(param_3);
  iVar1 = SUB_000207fc;
  *(undefined2 *)(SUB_000207fc + 0x92) = 9;
  *(undefined4 *)(iVar1 + 0x322) = 0x8032;
  *(undefined4 *)(iVar1 + 0x326) = 0x7f12;
  *(undefined4 *)(iVar1 + 0x32a) = 0x46e2;
  *(undefined4 *)(iVar1 + 0x32e) = 0x4e68;
  *(undefined4 *)(iVar1 + 0x332) = 0x5192;
  *(undefined4 *)(iVar1 + 0x336) = 0x52c;
  *(undefined4 *)(iVar1 + 0x33a) = 0x43d4;
  *(undefined4 *)(iVar1 + 0x33e) = 0x64e;
  *(undefined4 *)(iVar1 + 0x342) = 0x150c;
  *(undefined4 *)(iVar1 + 0x346) = 0x15da;
  *(undefined4 *)(iVar1 + 0x34a) = 0x741e;
  *(undefined4 *)(iVar1 + 0x34e) = 0x16dc;
  *(undefined4 *)(iVar1 + 0x352) = 0xe61e;
  *(undefined4 *)(iVar1 + 0x356) = 0x834c;
  *(undefined4 *)(iVar1 + 0x35a) = 0x844e;
  *(code **)(iVar1 + 0x35e) = FUN_000023a4;
  *(code **)(iVar1 + 0x362) = FUN_00002406;
  *(undefined4 *)(iVar1 + 0x366) = 0x31ca;
  *(code **)(iVar1 + 0x36a) = FUN_0000f7c0;
  *(undefined4 *)(iVar1 + 0x36e) = 0x2554;
  *(undefined4 *)(iVar1 + 0x372) = 0xae2;
  *(undefined4 *)(iVar1 + 0x376) = 0x728c;
  return;
}



// FUN_0000184e @ 0x184e

undefined1 * FUN_0000184e(char *param_1)

{
  char *pcVar1;
  
  pcVar1 = &DAT_00027084;
  while (*param_1 != '\0') {
    if ((*param_1 == '%') && (param_1[1] == 'd')) {
      *pcVar1 = '%';
      pcVar1[1] = 'l';
      pcVar1[2] = 'd';
      pcVar1 = pcVar1 + 3;
      param_1 = param_1 + 2;
    }
    else if ((*param_1 == '%') && (param_1[1] == 'x')) {
      *pcVar1 = '%';
      pcVar1[1] = 'l';
      pcVar1[2] = 'x';
      pcVar1 = pcVar1 + 3;
      param_1 = param_1 + 2;
    }
    else {
      *pcVar1 = *param_1;
      pcVar1 = pcVar1 + 1;
      param_1 = param_1 + 1;
    }
  }
  *pcVar1 = '\0';
  return &DAT_00027084;
}



// FUN_000018bc @ 0x18bc

void FUN_000018bc(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5,undefined4 param_6)

{
  int iVar1;
  undefined4 uVar2;
  
  iVar1 = SUB_000207fc;
  uVar2 = FUN_0000184e(param_1,param_2,param_3,param_4,param_5,param_6);
  (**(code **)(iVar1 + 0x29a))(uVar2);
  return;
}



// FUN_000018fc @ 0x18fc

void FUN_000018fc(void)

{
  undefined4 uVar1;
  int iVar2;
  int *piVar3;
  undefined4 *puVar4;
  
  iVar2 = 1;
  piVar3 = &DAT_00023fa0;
  do {
    if (*piVar3 != 0) {
      FUN_00002008(*piVar3);
      *piVar3 = 0;
    }
    piVar3 = piVar3 + 1;
    iVar2 = iVar2 + 1;
  } while (iVar2 < 4);
  iVar2 = 1;
  puVar4 = &DAT_00023fa0;
  do {
    if ((iVar2 == 1) || (*(short *)(SUB_000207fc + 0xb4) != 0)) {
      uVar1 = FUN_000021a4(0x400);
      *puVar4 = uVar1;
    }
    puVar4 = puVar4 + 1;
    iVar2 = iVar2 + 1;
  } while (iVar2 < 4);
  DAT_00023fac = DAT_00023fa0;
  return;
}



// FUN_00001968 @ 0x1968

void FUN_00001968(void)

{
  FUN_000018fc();
  DAT_00023fba = 0;
  DAT_00023fd0 = *(undefined2 *)(*(int *)(SUB_000207fc + 0x34) + 0x1a0);
  return;
}



// FUN_00001984 @ 0x1984

void FUN_00001984(void)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  int iVar6;
  int iVar7;
  uint local_8;
  
  FUN_000018fc();
  iVar7 = 1;
  do {
    iVar2 = *(int *)(&DAT_00023f9c + iVar7 * 4);
    iVar1 = *(int *)(iVar7 * 0x16 + SUB_000207fc + 0x1e);
    if ((iVar1 != 0) && (iVar2 != 0)) {
      iVar6 = 0;
      iVar5 = 0;
      do {
        iVar4 = 0;
        do {
          iVar3 = 0;
          do {
            local_8 = (uint)(byte)-(3 < iVar5);
            *(int *)(iVar2 + iVar6 * 4) =
                 (int)*(short *)(iVar1 + (((byte)-(2 < iVar3) & 1) +
                                         ((byte)-(2 < iVar5) & 1) * 4 + ((byte)-(2 < iVar4) & 1) * 2
                                         ) * 2) |
                 (int)*(short *)(iVar1 + (((byte)-(0 < iVar3) & 1) +
                                         ((byte)-(0 < iVar5) & 1) * 4 + ((byte)-(0 < iVar4) & 1) * 2
                                         ) * 2) << 4 |
                 (int)*(short *)(iVar1 + (((byte)-(1 < iVar3) & 1) +
                                         ((byte)-(1 < iVar5) & 1) * 4 + ((byte)-(1 < iVar4) & 1) * 2
                                         ) * 2) << 8 |
                 (int)*(short *)(iVar1 + (((byte)-(3 < iVar3) & 1) +
                                         (local_8 & 1) * 4 + ((byte)-(3 < iVar4) & 1) * 2) * 2) <<
                 0xc;
            iVar6 = iVar6 + 1;
            iVar3 = iVar3 + 1;
          } while (iVar3 < 5);
          iVar4 = iVar4 + 1;
        } while (iVar4 < 5);
        iVar5 = iVar5 + 1;
      } while (iVar5 < 5);
    }
    iVar7 = iVar7 + 1;
  } while (iVar7 < 4);
  DAT_00023fac = DAT_00023fa0;
  DAT_00023fba = 0;
  DAT_00023fd0 = *(undefined2 *)(*(int *)(SUB_000207fc + 0x34) + 0x10);
  return;
}



// FUN_00001ae2 @ 0x1ae2

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00001ae2(int param_1)

{
  byte bVar1;
  short *psVar2;
  int iVar3;
  short sVar4;
  uint uVar5;
  short *psVar6;
  
  bVar1 = *(byte *)(param_1 + 0xf);
  *(int *)(DAT_00023fac + (uint)bVar1 * 4) = param_1;
  if (_DAT_00023d8a == 0) {
    sVar4 = *(short *)(DAT_00023d8c + (uint)bVar1 * 2);
    *(int *)(param_1 + 4) = (int)sVar4 | (int)sVar4 << 8;
    psVar6 = (short *)(*(int *)(SUB_000207fc + 0x72) + 6);
    psVar2 = (short *)(*(int *)(SUB_000207fc + 0x72) + *psVar6 * 8 + 8);
    *psVar6 = *psVar6 + 1;
    *psVar2 = sVar4;
    iVar3 = (uint)*(byte *)(param_1 + 0xc) * 0x100 + -1;
    if (iVar3 < 0) {
      sVar4 = 0;
    }
    else {
      sVar4 = (short)iVar3;
    }
    psVar2[1] = sVar4;
    iVar3 = (uint)*(byte *)(param_1 + 0xd) * 0x100 + -1;
    if (iVar3 < 0) {
      sVar4 = 0;
    }
    else {
      sVar4 = (short)iVar3;
    }
    psVar2[2] = sVar4;
    iVar3 = (uint)*(byte *)(param_1 + 0xe) * 0x100 + -1;
    if (iVar3 < 0) {
      sVar4 = 0;
    }
    else {
      sVar4 = (short)iVar3;
    }
    psVar2[3] = sVar4;
  }
  else {
    uVar5 = (uint)(int)(short)(ushort)*(byte *)(param_1 + 0xe) / 3 +
            (uint)*(byte *)(param_1 + 0xd) + (uint)*(byte *)(param_1 + 0xc);
    if (uVar5 == 0) {
      uVar5 = 1;
    }
    *(uint *)(param_1 + 4) = uVar5 | uVar5 << 8;
  }
  return;
}



// FUN_00001bb0 @ 0x1bb0

void FUN_00001bb0(int param_1)

{
  int *piVar1;
  
  FUN_00001ae2(param_1);
  if (*(short *)(SUB_000207fc + 0xb4) != 0) {
    piVar1 = &DAT_00023fa0;
    do {
      if ((*piVar1 != 0) && (DAT_00023fac != *piVar1)) {
        *(int *)(*piVar1 + (uint)*(byte *)(param_1 + 0xf) * 4) = -param_1;
      }
      piVar1 = piVar1 + 1;
    } while ((int)piVar1 < 0x23fa9);
  }
  return;
}



// FUN_00001c06 @ 0x1c06

/* WARNING: Removing unreachable block (ram,0x00001c42) */
/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

undefined4 FUN_00001c06(int param_1)

{
  int iVar1;
  
  if (DAT_0002100e == 1) {
    iVar1 = ((((int)((uint)*(byte *)(param_1 + 0xe) * 7 +
                    (uint)*(byte *)(param_1 + 0xd) * 0x26 + (uint)*(byte *)(param_1 + 0xc) * 0x13)
              >> 6) + -0x80) * 0x10) / 0xc + 0x80;
    if (iVar1 < 0x100) {
      if (iVar1 < 0) {
        iVar1 = 0;
      }
    }
    else {
      iVar1 = 0xff;
    }
    *(int *)(param_1 + 4) = (int)*(short *)(*(int *)(SUB_000207fc + 0x88) + iVar1 * 2);
  }
  else {
    if (DAT_0002100e != 4) {
      if (-*(int *)(DAT_00023fac + (uint)*(byte *)(param_1 + 0xf) * 4) == param_1) {
        FUN_00001ae2(param_1);
      }
      else {
        iVar1 = 0;
        while ((*(char *)(DAT_00023d90 + DAT_00021568) != '\0' ||
               (*(char *)(DAT_00023d94 + DAT_00021568) != '\0'))) {
          DAT_00021568 = DAT_00021568 + 1;
          if (DAT_00021568 == 200) {
            DAT_00021568 = 0;
          }
          iVar1 = iVar1 + 1;
          if (199 < iVar1) {
            return 1;
          }
        }
        *(undefined1 *)(param_1 + 0xf) = (undefined1)DAT_00021568;
        FUN_00001bb0(param_1);
        _DAT_00023fd2 = 1;
      }
      return 0;
    }
    *(undefined4 *)(param_1 + 4) =
         *(undefined4 *)
          (DAT_00023fac +
          ((uint)*(byte *)(param_1 + 0xe) +
          (uint)*(byte *)(param_1 + 0xd) * 5 + (uint)*(byte *)(param_1 + 0xc) * 0x19) * 4);
  }
  *(undefined1 *)(param_1 + 0xf) = 0xff;
  return 0;
}



// FUN_00001d4a @ 0x1d4a

void FUN_00001d4a(void)

{
  int *piVar1;
  int iVar2;
  undefined4 uVar3;
  int iVar4;
  int iVar5;
  int iVar6;
  undefined4 *puVar7;
  undefined4 *puVar8;
  
  FUN_00000058();
  iVar2 = FUN_00005428(*(undefined4 *)(SUB_000207fc + 0x8c));
  puVar8 = (undefined4 *)(*(int *)(iVar2 + 0x2a) + 0x1a);
  DAT_00023c2c = iVar2;
  uVar3 = FUN_000020a0(*(short *)(iVar2 + 0x30) * 0x18);
  *(undefined4 *)(iVar2 + 0x32) = uVar3;
  iVar6 = 0;
  if (0 < *(short *)(iVar2 + 0x30)) {
    iVar4 = 0;
    do {
      puVar7 = (undefined4 *)(iVar4 + *(int *)(iVar2 + 0x32));
      *puVar7 = *puVar8;
      puVar7[1] = puVar8[1];
      puVar7[2] = puVar8[2];
      puVar7[3] = puVar8[3];
      puVar7[4] = puVar8[4];
      puVar7[5] = puVar8[5];
      puVar8 = puVar8 + 6;
      piVar1 = (int *)(*(int *)(iVar2 + 0x32) + 0x14 + iVar4);
      *piVar1 = *(int *)(SUB_000207fc + 0x8c) + *piVar1;
      iVar4 = iVar4 + 0x18;
      iVar6 = iVar6 + 1;
    } while (iVar6 < *(short *)(iVar2 + 0x30));
  }
  iVar6 = 0;
  if (0 < *(short *)(iVar2 + 0x30)) {
    iVar4 = 0;
    do {
      iVar5 = FUN_00005428(*(undefined4 *)(*(int *)(iVar2 + 0x32) + 0x14 + iVar4));
      *(int *)(*(int *)(iVar2 + 0x32) + 0x14 + iVar4) = iVar5;
      *(int *)(iVar5 + 0x24) = iVar2;
      *(short *)(iVar5 + 0x10) = (short)iVar6;
      *(short *)(iVar5 + 0x12) = (short)iVar6;
      iVar4 = iVar4 + 0x18;
      iVar6 = iVar6 + 1;
    } while (iVar6 < *(short *)(iVar2 + 0x30));
  }
  DAT_0002159c = *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + 0x14);
  FUN_00005c98(DAT_0002159c);
  FUN_0000221c(DAT_0002159c);
  DAT_00021598 = *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + 0x2c);
  FUN_00005c98(DAT_00021598);
  FUN_0000221c(DAT_0002159c);
  DAT_00021594 = *(int *)(*(int *)(DAT_00023c2c + 0x32) + 0x44);
  FUN_00005c98(DAT_00021594);
  DAT_00021590 = *(undefined4 *)(DAT_00021594 + 0x4a);
  DAT_0002158c = *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + 0x74);
  FUN_00005c98(DAT_0002158c);
  FUN_0000221c(DAT_0002158c);
  DAT_00020b24 = 0;
  iVar6 = (int)*(short *)(DAT_00023c2c + 0x2e);
  DAT_0002107a = (short)(iVar6 + -1);
  DAT_00021410 = *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + 0x14 + (iVar6 + -1) * 0x18);
  DAT_0002141c = *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + 0x14 + iVar6 * 0x18);
  DAT_00021420 = *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + 0x14 + (iVar6 + 1) * 0x18);
  DAT_00021078 = *(short *)(DAT_00023c2c + 0x2e) + 1;
  return;
}



// FUN_00001f10 @ 0x1f10

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00001f10(undefined4 param_1)

{
  FUN_0001361e(param_1);
  DAT_00023d94 = &DAT_00023e9c;
  DAT_00023d90 = 0x23d9c;
  FUN_00004234();
  FUN_00004232();
  FUN_00004232();
  FUN_00003d66();
  FUN_00003f12();
  FUN_000040b4();
  DAT_00021574 = 0xd983;
  DAT_00021572 = 0x69d8;
  DAT_00021570 = 0xc330;
  _DAT_0002156c = 1;
  DAT_00021568 = 0;
  DAT_00021020 = 0x4c;
  FUN_0001b8bc(0xc15c0);
  return;
}



// FUN_00001f90 @ 0x1f90

int FUN_00001f90(int param_1)

{
  int iVar1;
  
  iVar1 = DAT_00027188 + *(int *)(SUB_000207fc + 900);
  DAT_00027188 = param_1 + DAT_00027188;
  return iVar1;
}



// FUN_00001fac @ 0x1fac

undefined1 * FUN_00001fac(uint param_1)

{
  undefined1 *puVar1;
  
  puVar1 = (undefined1 *)FUN_00001f90(1 << (param_1 & 0x3f));
  if (puVar1 == (undefined1 *)0xffffffff) {
    puVar1 = (undefined1 *)0xffffffff;
  }
  else {
    *puVar1 = (char)param_1;
    puVar1 = puVar1 + 4;
  }
  return puVar1;
}



// FUN_00001fd2 @ 0x1fd2

undefined4 FUN_00001fd2(int param_1)

{
  int *piVar1;
  undefined4 uVar2;
  
  piVar1 = (int *)FUN_00001f90(param_1 << 3);
  if (piVar1 == (int *)0xffffffff) {
    uVar2 = 0;
  }
  else {
    *piVar1 = param_1;
    *(undefined1 *)(piVar1 + 1) = 0xff;
    FUN_00002008(piVar1 + 2);
    uVar2 = DAT_00027198;
  }
  return uVar2;
}



// FUN_00001fe8 @ 0x1fe8

undefined4 FUN_00001fe8(void)

{
  undefined4 uVar1;
  undefined4 unaff_D2;
  undefined4 *in_A0;
  bool in_ZF;
  
  if (in_ZF) {
    uVar1 = 0;
  }
  else {
    *in_A0 = unaff_D2;
    *(undefined1 *)(in_A0 + 1) = 0xff;
    FUN_00002008(in_A0 + 2);
    uVar1 = DAT_00027198;
  }
  return uVar1;
}



// FUN_00002008 @ 0x2008

void FUN_00002008(int param_1)

{
  byte bVar1;
  int *piVar2;
  byte *pbVar3;
  int *piVar4;
  
  if (*(char *)(param_1 + -4) == -1) {
    piVar2 = (int *)(param_1 + -8);
    for (piVar4 = DAT_00027198;
        ((piVar2 <= piVar4 || ((int *)piVar4[1] <= piVar2)) &&
        ((piVar4 < (int *)piVar4[1] || ((piVar2 <= piVar4 && ((int *)piVar4[1] <= piVar2))))));
        piVar4 = (int *)piVar4[1]) {
    }
    if (piVar2 + *piVar2 * 2 == (int *)piVar4[1]) {
      *piVar2 = *(int *)piVar4[1] + *piVar2;
      *(undefined4 *)(param_1 + -4) = *(undefined4 *)(piVar4[1] + 4);
    }
    else {
      *(int *)(param_1 + -4) = piVar4[1];
    }
    if (piVar2 == piVar4 + *piVar4 * 2) {
      *piVar4 = *piVar2 + *piVar4;
      piVar4[1] = *(int *)(param_1 + -4);
      FUN_00002080();
      return;
    }
    piVar4[1] = (int)piVar2;
    DAT_00027198 = piVar4;
  }
  else {
    pbVar3 = (byte *)(param_1 + -4);
    bVar1 = *pbVar3;
    *(undefined4 *)pbVar3 = *(undefined4 *)(&DAT_0002719c + (uint)bVar1 * 4);
    *(byte **)(&DAT_0002719c + (uint)bVar1 * 4) = pbVar3;
  }
  return;
}



// FUN_00002080 @ 0x2080

void FUN_00002080(void)

{
  undefined4 in_A1;
  
  DAT_00027198 = in_A1;
  return;
}



// FUN_000020a0 @ 0x20a0

uint * FUN_000020a0(int param_1)

{
  undefined1 *puVar1;
  uint uVar2;
  uint *puVar3;
  uint *puVar4;
  uint uVar5;
  
  uVar5 = 2;
  for (uVar2 = (param_1 + 7U & 0xfffffffc) - 1 >> 2; uVar2 != 0; uVar2 = uVar2 >> 1) {
    uVar5 = uVar5 + 1;
  }
  if (uVar5 < 6) {
    puVar1 = *(undefined1 **)(&DAT_0002719c + uVar5 * 4);
    if (puVar1 == (undefined1 *)0x0) {
      puVar3 = (uint *)FUN_00001fac(uVar5);
    }
    else {
      *(undefined4 *)(&DAT_0002719c + uVar5 * 4) = **(undefined4 **)(&DAT_0002719c + uVar5 * 4);
      *puVar1 = (char)uVar5;
      puVar3 = (uint *)(puVar1 + 4);
    }
  }
  else {
    uVar5 = (param_1 + 7U >> 3) + 1;
    if (DAT_00027198 == (uint *)0x0) {
      DAT_00027198 = &DAT_00027190;
      DAT_00027194 = &DAT_00027190;
      DAT_00027190 = 0;
    }
    puVar3 = (uint *)DAT_00027198[1];
    puVar4 = DAT_00027198;
    while (*puVar3 < uVar5) {
      puVar4 = puVar3;
      if ((puVar3 == DAT_00027198) && (puVar4 = (uint *)FUN_00001fd2(uVar5), puVar4 == (uint *)0x0))
      {
        return (uint *)0x0;
      }
      puVar3 = (uint *)puVar4[1];
    }
    if (uVar5 == *puVar3) {
      puVar4[1] = puVar3[1];
    }
    else {
      *puVar3 = *puVar3 - uVar5;
      puVar3 = puVar3 + *puVar3 * 2;
      *puVar3 = uVar5;
    }
    DAT_00027198 = puVar4;
    *(undefined1 *)(puVar3 + 1) = 0xff;
    puVar3 = puVar3 + 2;
  }
  return puVar3;
}



// FUN_00002170 @ 0x2170

void FUN_00002170(int param_1)

{
  int iVar1;
  
  if (*(byte *)(param_1 + -4) == 0xff) {
    iVar1 = (*(int *)(param_1 + -8) + -1) * 8;
  }
  else {
    iVar1 = (1 << (*(byte *)(param_1 + -4) & 0x3f)) + -4;
  }
  FUN_0001f712(param_1,iVar1);
  return;
}



// FUN_000021a4 @ 0x21a4

undefined4 FUN_000021a4(undefined4 param_1)

{
  undefined4 uVar1;
  
  uVar1 = FUN_000020a0(param_1);
  FUN_00002170(uVar1);
  return uVar1;
}



// FUN_000021bc @ 0x21bc

int FUN_000021bc(int param_1,int param_2)

{
  int iVar1;
  
  iVar1 = FUN_000020a0(param_2 * param_1);
  if (iVar1 != 0) {
    FUN_00002170(iVar1);
  }
  return iVar1;
}



// FUN_000021e0 @ 0x21e0

int FUN_000021e0(int param_1,uint param_2)

{
  int iVar1;
  uint uVar2;
  
  if (*(char *)(param_1 + -4) == -1) {
    uVar2 = (*(int *)(param_1 + -8) + -1) * 8;
  }
  else {
    uVar2 = (1 << (*(byte *)(param_1 + -4) & 0x3f)) - 4;
  }
  if (uVar2 < param_2) {
    iVar1 = FUN_000021bc(param_2,1);
    FUN_0001f63a(param_1,iVar1,uVar2);
    FUN_00002008(param_1);
  }
  else {
    iVar1 = param_1;
  }
  return iVar1;
}



// FUN_0000221c @ 0x221c

void FUN_0000221c(void)

{
  FUN_0001f63a();
  FUN_00002008();
  return;
}



// FUN_0000223c @ 0x223c

void FUN_0000223c(int param_1)

{
  int iVar1;
  int iVar2;
  
  iVar2 = (int)*(short *)(param_1 + 0x3c) + (int)*(short *)(param_1 + 0x38);
  if (0 < iVar2) {
    iVar1 = 0;
    do {
      *(undefined4 *)(*(int *)(param_1 + 0x4e) + iVar1) =
           *(undefined4 *)(*(int *)(param_1 + 0x46) + iVar1);
      *(undefined4 *)(*(int *)(param_1 + 0x4e) + 4 + iVar1) =
           *(undefined4 *)(*(int *)(param_1 + 0x46) + 4 + iVar1);
      *(undefined4 *)(*(int *)(param_1 + 0x4e) + 8 + iVar1) =
           *(undefined4 *)(*(int *)(param_1 + 0x46) + 8 + iVar1);
      iVar1 = iVar1 + 0xc;
    } while (iVar1 < iVar2 * 0xc);
  }
  return;
}



// FUN_00002294 @ 0x2294

void FUN_00002294(int param_1,int *param_2,int *param_3,int param_4,int param_5,int param_6)

{
  ushort uVar1;
  short sVar2;
  
  param_1 = param_1 + -1;
  if (param_1 != -1) {
    do {
      do {
        *param_3 = *param_2 + param_4;
        param_3[1] = param_2[1] + param_5;
        param_3[2] = param_2[2] + param_6;
        param_3 = param_3 + 3;
        param_2 = param_2 + 3;
        uVar1 = (ushort)((uint)param_1 >> 0x10);
        sVar2 = (short)param_1 + -1;
        param_1 = CONCAT22(uVar1,sVar2);
      } while (sVar2 != -1);
      param_1 = (uint)uVar1 * 0x10000 + -1;
    } while (uVar1 != 0);
  }
  return;
}



// FUN_000022ea @ 0x22ea

void FUN_000022ea(int param_1,int *param_2,int *param_3,int param_4,int param_5,int param_6)

{
  ushort uVar1;
  short sVar2;
  
  param_1 = param_1 + -1;
  if (param_1 != -1) {
    do {
      do {
        *param_3 = *param_2 * param_4;
        param_3[1] = param_2[1] * param_5;
        param_3[2] = param_2[2] * param_6;
        param_3 = param_3 + 3;
        param_2 = param_2 + 3;
        uVar1 = (ushort)((uint)param_1 >> 0x10);
        sVar2 = (short)param_1 + -1;
        param_1 = CONCAT22(uVar1,sVar2);
      } while (sVar2 != -1);
      param_1 = (uint)uVar1 * 0x10000 + -1;
    } while (uVar1 != 0);
  }
  return;
}



// FUN_00002346 @ 0x2346

void FUN_00002346(int param_1,short *param_2,int param_3)

{
  int iVar1;
  undefined2 *puVar2;
  undefined2 *puVar3;
  short *psVar4;
  short *psVar5;
  short *psVar6;
  int iVar7;
  
  iVar7 = 0;
  do {
    iVar1 = iVar7 * 6;
    puVar2 = (undefined2 *)(iVar1 + param_3);
    psVar4 = param_2;
    psVar5 = param_2 + 3;
    psVar6 = param_2 + 6;
    do {
      puVar3 = puVar2 + 1;
      *puVar2 = (short)((int)*(short *)(iVar1 + param_1 + 4) * (int)*psVar6 +
                        (int)*(short *)(iVar1 + param_1 + 2) * (int)*psVar5 +
                        (int)*(short *)(param_1 + iVar1) * (int)*psVar4 >> 0xc);
      puVar2 = puVar3;
      psVar4 = psVar4 + 1;
      psVar5 = psVar5 + 1;
      psVar6 = psVar6 + 1;
    } while ((int)puVar3 <= (int)((undefined2 *)(iVar1 + param_3) + 2));
    iVar7 = iVar7 + 1;
  } while (iVar7 < 3);
  return;
}



// FUN_000023a4 @ 0x23a4

void FUN_000023a4(void)

{
  int in_D0;
  short in_D1w;
  int unaff_D2;
  undefined2 *unaff_D3;
  short *unaff_D4;
  int unaff_D5;
  int unaff_D6;
  undefined2 *puVar1;
  undefined2 *in_A0;
  short *psVar2;
  short *in_A1;
  short *unaff_A2;
  short *psVar3;
  short *unaff_A3;
  int unaff_A4;
  int unaff_A6;
  
  do {
    psVar3 = unaff_A3 + 1;
    puVar1 = in_A0 + 1;
    *in_A0 = (short)((int)in_D1w * (int)*unaff_A3 + in_D0 >> 0xc);
    psVar2 = in_A1;
    if ((int)unaff_D3 < (int)puVar1) {
      unaff_A4 = unaff_A4 + 1;
      if (2 < unaff_A4) {
        return;
      }
      unaff_D2 = unaff_A4 * 6;
      puVar1 = (undefined2 *)(unaff_D2 + unaff_D6);
      *(int *)(unaff_A6 + -8) = unaff_D2 + unaff_D5;
      *(int *)(unaff_A6 + -4) = unaff_D2 + unaff_D5 + 4;
      psVar3 = unaff_D4 + 6;
      unaff_A2 = unaff_D4 + 3;
      unaff_D3 = puVar1 + 2;
      psVar2 = unaff_D4;
    }
    in_A1 = psVar2 + 1;
    in_D0 = (int)*(short *)(*(int *)(unaff_A6 + -8) + 2) * (int)*unaff_A2 +
            (int)*(short *)(unaff_D5 + unaff_D2) * (int)*psVar2;
    in_D1w = **(short **)(unaff_A6 + -4);
    in_A0 = puVar1;
    unaff_A2 = unaff_A2 + 1;
    unaff_A3 = psVar3;
  } while( true );
}



// FUN_000023c4 @ 0x23c4

int FUN_000023c4(uint param_1)

{
  uint uVar1;
  uint uVar2;
  uint uVar3;
  int iVar4;
  
  uVar1 = param_1;
  if ((int)param_1 < 0) {
    uVar1 = param_1 + 0x3f;
  }
  uVar2 = param_1;
  if ((int)param_1 < 0) {
    uVar2 = param_1 + 0x3f;
  }
  uVar3 = param_1;
  if ((int)param_1 < 0) {
    uVar3 = param_1 + 0x3f;
  }
  iVar4 = ((int)*(short *)(&DAT_0001fd94 + (((int)uVar2 >> 6) + 1U & 0xff) * 2) -
          (int)*(short *)(&DAT_0001fd94 + ((int)uVar1 >> 6) * 2)) *
          -((uVar3 & 0xffffffc0) - param_1);
  if (iVar4 < 0) {
    iVar4 = iVar4 + 0x3f;
  }
  return (int)(short)(*(short *)(&DAT_0001fd94 + ((int)uVar1 >> 6) * 2) + (short)(iVar4 >> 6));
}



// FUN_00002406 @ 0x2406

int FUN_00002406(void)

{
  int in_D0;
  int iVar1;
  int in_D1;
  int unaff_D2;
  short unaff_D3w;
  int unaff_D4;
  
  iVar1 = unaff_D2 * -((unaff_D4 + in_D0 & 0xffffffc0U) - in_D1);
  if (iVar1 < 0) {
    iVar1 = iVar1 + 0x3f;
  }
  return (int)(short)(unaff_D3w + (short)(iVar1 >> 6));
}



// FUN_00002426 @ 0x2426

int FUN_00002426(uint param_1)

{
  uint uVar1;
  uint uVar2;
  uint uVar3;
  int iVar4;
  
  uVar1 = param_1;
  if ((int)param_1 < 0) {
    uVar1 = param_1 + 0x3f;
  }
  uVar2 = param_1;
  if ((int)param_1 < 0) {
    uVar2 = param_1 + 0x3f;
  }
  uVar3 = param_1;
  if ((int)param_1 < 0) {
    uVar3 = param_1 + 0x3f;
  }
  iVar4 = ((int)*(short *)(&DAT_0001ff94 + (((int)uVar2 >> 6) + 1U & 0xff) * 2) -
          (int)*(short *)(&DAT_0001ff94 + ((int)uVar1 >> 6) * 2)) *
          -((uVar3 & 0xffffffc0) - param_1);
  if (iVar4 < 0) {
    iVar4 = iVar4 + 0x3f;
  }
  return (int)(short)(*(short *)(&DAT_0001ff94 + ((int)uVar1 >> 6) * 2) + (short)(iVar4 >> 6));
}



// FUN_00002488 @ 0x2488

int FUN_00002488(int param_1)

{
  short sVar1;
  short sVar3;
  int iVar2;
  short sVar4;
  
  if (0x1000 < param_1) {
    param_1 = 0x1000;
  }
  if (param_1 < -0x1000) {
    param_1 = -0x1000;
  }
  sVar4 = 0;
  sVar3 = 0x80;
  do {
    iVar2 = (int)sVar3 + (int)sVar4;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 1;
    }
    sVar1 = (short)(iVar2 >> 1);
    if (*(short *)(&DAT_0001ff94 + sVar1 * 2) < param_1) {
      sVar3 = sVar1;
    }
    else {
      sVar4 = sVar1;
    }
  } while ((int)sVar4 < sVar3 + -1);
  iVar2 = (int)sVar4;
  return (int)(short)((short)(((param_1 - *(short *)(&DAT_0001ff94 + iVar2 * 2)) * -0x40) /
                             ((int)*(short *)(&DAT_0001ff94 + iVar2 * 2) -
                             (int)*(short *)(iVar2 * 2 + 0x1ff96))) + (short)(iVar2 << 6));
}



// FUN_0000250c @ 0x250c

ushort FUN_0000250c(int param_1)

{
  short sVar1;
  short sVar2;
  int iVar3;
  short sVar4;
  
  sVar4 = 0;
  sVar2 = 0x7f;
  do {
    iVar3 = (int)sVar2 + (int)sVar4;
    if (iVar3 < 0) {
      iVar3 = iVar3 + 1;
    }
    sVar1 = (short)(iVar3 >> 1);
    if (param_1 < *(int *)(&DAT_00020194 + sVar1 * 4)) {
      sVar2 = sVar1;
    }
    else {
      sVar4 = sVar1;
    }
  } while ((int)sVar4 < sVar2 + -1);
  iVar3 = (int)sVar4;
  return (short)(((*(int *)(&DAT_00020194 + iVar3 * 4) - param_1) * 0x40) /
                (*(int *)(&DAT_00020194 + iVar3 * 4) - *(int *)(iVar3 * 4 + 0x20198))) +
         (short)((iVar3 + -0x3f) * 0x40) & 0x3fff;
}



// FUN_00002574 @ 0x2574

void FUN_00002574(short param_1,short param_2,short param_3,int param_4,int param_5,ushort *param_6,
                 ushort *param_7)

{
  short sVar2;
  short sVar3;
  short sVar4;
  int iVar1;
  ushort uVar5;
  
  sVar2 = FUN_000038e8((int)param_1);
  sVar3 = FUN_000038e8((int)param_3);
  if (sVar3 < sVar2) {
    sVar4 = FUN_0000250c(((int)param_3 << 0xc) / (int)sVar2);
    uVar5 = 0x1000 - sVar4;
    if (0 < param_1) {
      uVar5 = -uVar5;
    }
LAB_000025fc:
    uVar5 = uVar5 & 0x3fff;
  }
  else {
    if (param_3 == 0) goto LAB_00002602;
    uVar5 = FUN_0000250c((param_1 * -0x1000) / (int)sVar3);
    if (param_3 < 0) {
      uVar5 = 0x2000 - uVar5;
      goto LAB_000025fc;
    }
  }
  *param_7 = uVar5;
LAB_00002602:
  if (param_4 != 0) {
    iVar1 = FUN_00003908((int)param_2);
    if ((int)sVar3 + (int)sVar2 < iVar1) {
      sVar2 = FUN_0000f7e0(param_5 - (int)param_2 * (int)param_2);
      uVar5 = FUN_00002488(((int)sVar2 << 0xc) / param_4);
      if (param_2 < 0) {
        uVar5 = -uVar5 & 0x3fff;
      }
    }
    else {
      sVar2 = FUN_00002488(((int)param_2 << 0xc) / param_4);
      uVar5 = 0x1000U - sVar2 & 0x3fff;
    }
    *param_6 = uVar5;
  }
  return;
}



// FUN_0000267c @ 0x267c

void FUN_0000267c(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4)

{
  DAT_00023d2a = FUN_00002426(param_1);
  DAT_00023d2c = FUN_000023c4(param_1);
  DAT_00023d26 = FUN_00002426(param_2);
  DAT_00023d28 = FUN_000023c4(param_2);
  DAT_00023d22 = FUN_00002426(param_3);
  DAT_00023d24 = FUN_000023c4(param_3);
  DAT_00023d1e = FUN_00002426(param_4);
  DAT_00023d20 = FUN_000023c4(param_4);
  return;
}



// FUN_000026f2 @ 0x26f2

void FUN_000026f2(undefined4 param_1,undefined4 param_2)

{
  DAT_00023d1a = FUN_00002426(param_1);
  DAT_00023d1c = FUN_000023c4(param_1);
  DAT_00023d16 = FUN_00002426(param_2);
  DAT_00023d18 = FUN_000023c4(param_2);
  return;
}



// FUN_00002738 @ 0x2738

void FUN_00002738(void)

{
  DAT_00023d04 = 0x1000;
  DAT_00023d06 = 0;
  DAT_00023d08 = 0;
  DAT_00023d0a = 0;
  DAT_00023d0c = DAT_00023d2a;
  DAT_00023d0e = -DAT_00023d2c;
  DAT_00023d10 = 0;
  DAT_00023d12 = DAT_00023d2c;
  DAT_00023d14 = DAT_00023d2a;
  DAT_00023cec = DAT_00023d26;
  DAT_00023cee = 0;
  DAT_00023cf0 = DAT_00023d28;
  DAT_00023cf2 = 0;
  DAT_00023cf4 = 0x1000;
  DAT_00023cf6 = 0;
  DAT_00023cf8 = -DAT_00023d28;
  DAT_00023cfa = 0;
  DAT_00023cfc = DAT_00023d26;
  DAT_00023cd4 = DAT_00023d22;
  DAT_00023cd6 = DAT_00023d24;
  DAT_00023cd8 = 0;
  DAT_00023cda = -DAT_00023d24;
  DAT_00023cdc = DAT_00023d22;
  DAT_00023cde = 0;
  DAT_00023ce0 = 0;
  DAT_00023ce2 = 0;
  DAT_00023ce4 = 0x1000;
  return;
}



// FUN_000027f2 @ 0x27f2

void FUN_000027f2(void)

{
  DAT_00023cbc = DAT_00023d1e;
  DAT_00023cbe = 0;
  DAT_00023cc0 = DAT_00023d20;
  DAT_00023cc2 = 0;
  DAT_00023cc4 = 0x1000;
  DAT_00023cc6 = 0;
  DAT_00023cc8 = -DAT_00023d20;
  DAT_00023cca = 0;
  DAT_00023ccc = DAT_00023d1e;
  return;
}



// FUN_00002832 @ 0x2832

void FUN_00002832(void)

{
  DAT_00023cbc = 0x1000;
  DAT_00023cbe = 0;
  DAT_00023cc0 = 0;
  DAT_00023cc2 = 0;
  DAT_00023cc4 = DAT_00023d1e;
  DAT_00023cc6 = -DAT_00023d20;
  DAT_00023cc8 = 0;
  DAT_00023cca = DAT_00023d20;
  DAT_00023ccc = DAT_00023d1e;
  return;
}



// FUN_00002872 @ 0x2872

void FUN_00002872(void)

{
  DAT_00023ca4 = 0x1000;
  DAT_00023ca6 = 0;
  DAT_00023ca8 = 0;
  DAT_00023caa = 0;
  DAT_00023cac = DAT_00023d1a;
  DAT_00023cae = -DAT_00023d1c;
  DAT_00023cb0 = 0;
  DAT_00023cb2 = DAT_00023d1c;
  DAT_00023cb4 = DAT_00023d1a;
  DAT_00023c8c = DAT_00023d16;
  DAT_00023c8e = 0;
  DAT_00023c90 = DAT_00023d18;
  DAT_00023c92 = 0;
  DAT_00023c94 = 0x1000;
  DAT_00023c96 = 0;
  DAT_00023c98 = -DAT_00023d18;
  DAT_00023c9a = 0;
  DAT_00023c9c = DAT_00023d16;
  return;
}



// FUN_000028f0 @ 0x28f0

void FUN_000028f0(int param_1,int param_2,int param_3,undefined4 param_4,undefined4 param_5,
                 int param_6)

{
  short sVar1;
  int iVar2;
  undefined1 auStack_4c [18];
  undefined2 local_3a;
  short local_38;
  undefined2 local_36;
  short local_34;
  undefined2 local_32;
  undefined2 local_30;
  undefined2 local_2e;
  undefined2 local_2c;
  undefined2 local_2a;
  undefined2 local_28;
  undefined2 local_26;
  short local_24;
  undefined2 local_22;
  undefined2 local_20;
  undefined2 local_1e;
  short local_1c;
  undefined2 local_1a;
  undefined2 local_18;
  undefined2 local_16;
  undefined2 local_14;
  undefined2 local_12;
  undefined2 local_10;
  undefined2 local_e;
  short local_c;
  undefined2 local_a;
  short local_8;
  undefined2 local_6;
  
  sVar1 = FUN_0000f7c0(param_1 * param_1 + param_3 * param_3);
  iVar2 = (int)sVar1;
  sVar1 = FUN_0000f7c0(param_2 * param_2 + param_1 * param_1 + param_3 * param_3);
  DAT_00023d2a = (undefined2)((iVar2 << 0xc) / (int)sVar1);
  DAT_00023d2c = (short)((param_2 * -0x1000) / (int)sVar1);
  if (param_6 == 0) {
    DAT_00023d26 = (undefined2)((param_3 << 0xc) / iVar2);
    DAT_00023d28 = (short)((param_1 << 0xc) / iVar2);
  }
  else {
    DAT_00023d26 = 0x1000;
    DAT_00023d28 = 0;
  }
  DAT_00023d22 = FUN_00002426(param_4);
  DAT_00023d24 = FUN_000023c4(param_4);
  local_16 = 0x1000;
  local_14 = 0;
  local_12 = 0;
  local_10 = 0;
  local_e = DAT_00023d2a;
  local_c = -DAT_00023d2c;
  local_a = 0;
  local_8 = DAT_00023d2c;
  local_6 = DAT_00023d2a;
  local_28 = DAT_00023d26;
  local_26 = 0;
  local_24 = DAT_00023d28;
  local_22 = 0;
  local_20 = 0x1000;
  local_1e = 0;
  local_1c = -DAT_00023d28;
  local_1a = 0;
  local_18 = DAT_00023d26;
  local_3a = DAT_00023d22;
  local_36 = 0;
  local_34 = -DAT_00023d24;
  local_32 = DAT_00023d22;
  local_30 = 0;
  local_2e = 0;
  local_2c = 0;
  local_2a = 0x1000;
  local_38 = DAT_00023d24;
  FUN_00002346(&local_28,&local_16,auStack_4c);
  FUN_00002346(auStack_4c,&local_3a,param_5);
  return;
}



// FUN_00002a7a @ 0x2a7a

void FUN_00002a7a(undefined2 *param_1)

{
  int iVar1;
  int iVar2;
  short sVar3;
  
  iVar1 = (int)DAT_00023d2c;
  iVar2 = (int)DAT_00023d26;
  sVar3 = (short)((int)DAT_00023d2c * (int)DAT_00023d28 >> 0xc);
  *param_1 = (short)((int)DAT_00023d26 * (int)DAT_00023d22 - (int)sVar3 * (int)DAT_00023d24 >> 0xc);
  param_1[1] = (short)((int)DAT_00023d26 * (int)DAT_00023d24 + (int)sVar3 * (int)DAT_00023d22 >> 0xc
                      );
  param_1[2] = (short)((int)DAT_00023d2a * (int)DAT_00023d28 >> 0xc);
  param_1[3] = (short)(-((int)DAT_00023d2a * (int)DAT_00023d24) >> 0xc);
  param_1[4] = (short)((int)DAT_00023d2a * (int)DAT_00023d22 >> 0xc);
  param_1[5] = -DAT_00023d2c;
  sVar3 = (short)(iVar1 * iVar2 >> 0xc);
  param_1[6] = (short)(-((int)sVar3 * (int)DAT_00023d24) - (int)DAT_00023d22 * (int)DAT_00023d28 >>
                      0xc);
  param_1[7] = (short)((int)sVar3 * (int)DAT_00023d22 - (int)DAT_00023d28 * (int)DAT_00023d24 >> 0xc
                      );
  param_1[8] = (short)((int)DAT_00023d2a * (int)DAT_00023d26 >> 0xc);
  return;
}



// FUN_00002b52 @ 0x2b52

void FUN_00002b52(void)

{
  undefined2 in_D0w;
  uint unaff_D4;
  int in_A0;
  
  *(undefined2 *)(in_A0 + 0xe) = in_D0w;
  *(short *)(in_A0 + 0x10) = (short)((int)DAT_00023d2a * (int)DAT_00023d26 >> (unaff_D4 & 0x3f));
  return;
}



// FUN_00002b72 @ 0x2b72

void FUN_00002b72(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4)

{
  FUN_0000267c(param_1,param_2,param_3,0);
  FUN_00002a7a(param_4);
  return;
}



// FUN_00002b98 @ 0x2b98

void FUN_00002b98(undefined4 param_1)

{
  undefined1 auStack_16 [18];
  
  FUN_00002738();
  FUN_00002832();
  FUN_00002346(&DAT_00023cec,&DAT_00023d04,param_1);
  FUN_00002346(param_1,&DAT_00023cd4,auStack_16);
  FUN_00002346(auStack_16,&DAT_00023cbc,param_1);
  return;
}



// FUN_00002be6 @ 0x2be6

void FUN_00002be6(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5)

{
  FUN_0000267c(param_1,param_2,param_3,param_4);
  FUN_00002b98(param_5);
  return;
}



// FUN_00002c08 @ 0x2c08

void FUN_00002c08(undefined2 *param_1)

{
  short sVar1;
  short sVar3;
  int iVar2;
  int iVar4;
  
  iVar2 = (int)DAT_00023d2c;
  iVar4 = (int)DAT_00023d26;
  sVar3 = (short)((int)DAT_00023d2c * (int)DAT_00023d28 >> 0xc);
  *param_1 = (short)((int)sVar3 * (int)DAT_00023d24 + (int)DAT_00023d22 * (int)DAT_00023d26 >> 0xc);
  param_1[1] = (short)((int)DAT_00023d24 * (int)DAT_00023d2a >> 0xc);
  sVar1 = (short)(iVar2 * iVar4 >> 0xc);
  param_1[2] = (short)((int)DAT_00023d22 * (int)DAT_00023d28 - (int)sVar1 * (int)DAT_00023d24 >> 0xc
                      );
  iVar4 = (int)DAT_00023d24;
  param_1[3] = (short)((int)sVar3 * (int)DAT_00023d22 + -iVar4 * (int)DAT_00023d26 >> 0xc);
  param_1[4] = (short)((int)DAT_00023d22 * (int)DAT_00023d2a >> 0xc);
  iVar2 = (int)DAT_00023d28;
  param_1[5] = (short)(iVar2 * -iVar4 - (int)sVar1 * (int)DAT_00023d22 >> 0xc);
  param_1[6] = (short)(iVar2 * -(int)DAT_00023d2a >> 0xc);
  param_1[7] = DAT_00023d2c;
  param_1[8] = (short)((int)DAT_00023d2a * (int)DAT_00023d26 >> 0xc);
  return;
}



// FUN_00002d02 @ 0x2d02

void FUN_00002d02(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4)

{
  FUN_0000267c(param_1,param_2,param_3,0);
  FUN_00002c08(param_4);
  return;
}



// FUN_00002d28 @ 0x2d28

void FUN_00002d28(undefined2 *param_1)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  short sVar5;
  short sVar6;
  short sVar7;
  short sVar8;
  short sVar9;
  short sStack_20;
  short sStack_1a;
  
  sStack_1a = (short)((int)DAT_00023d20 * (int)DAT_00023d28 >> 0xc);
  iVar1 = (int)DAT_00023d2c;
  iVar3 = (int)DAT_00023d24;
  iVar2 = (int)DAT_00023d1e;
  iVar4 = (int)DAT_00023d2a;
  sStack_20 = (short)((int)DAT_00023d20 * (int)DAT_00023d24 >> 0xc);
  sVar5 = (short)((int)DAT_00023d1e * (int)DAT_00023d26 >> 0xc);
  sVar6 = (short)((int)DAT_00023d2c * (int)DAT_00023d28 >> 0xc);
  *param_1 = (short)((int)(short)((int)DAT_00023d1e * (int)DAT_00023d24 >> 0xc) * (int)sVar6 +
                     ((int)sVar5 * (int)DAT_00023d22 - (int)sStack_1a * (int)DAT_00023d2a) >> 0xc);
  sVar8 = (short)(iVar2 * iVar4 >> 0xc);
  param_1[1] = (short)((int)sVar8 * (int)DAT_00023d24 + (int)DAT_00023d20 * (int)DAT_00023d2c >> 0xc
                      );
  sVar7 = (short)(iVar1 * iVar3 >> 0xc);
  param_1[2] = (short)((int)(short)((int)DAT_00023d1e * (int)DAT_00023d22 >> 0xc) *
                       (int)DAT_00023d28 +
                       ((int)(short)((int)DAT_00023d2a * (int)DAT_00023d26 >> 0xc) *
                        (int)DAT_00023d20 - (int)sVar5 * (int)sVar7) >> 0xc);
  param_1[3] = (short)((int)sVar6 * (int)DAT_00023d22 - (int)DAT_00023d26 * (int)DAT_00023d24 >> 0xc
                      );
  param_1[4] = (short)((int)DAT_00023d2a * (int)DAT_00023d22 >> 0xc);
  sVar9 = (short)((int)DAT_00023d26 * (int)DAT_00023d22 >> 0xc);
  param_1[5] = (short)(-((int)DAT_00023d28 * (int)DAT_00023d24) - (int)sVar9 * (int)DAT_00023d2c >>
                      0xc);
  param_1[6] = (short)((-((int)sVar9 * (int)DAT_00023d20) - (int)sVar6 * (int)sStack_20) -
                       (int)sVar8 * (int)DAT_00023d28 >> 0xc);
  param_1[7] = (short)((int)DAT_00023d1e * (int)DAT_00023d2c - (int)sStack_20 * (int)DAT_00023d2a >>
                      0xc);
  param_1[8] = (short)((int)(short)((int)sVar7 * (int)DAT_00023d26 >> 0xc) * (int)DAT_00023d20 +
                       ((int)sVar5 * (int)DAT_00023d2a - (int)sStack_1a * (int)DAT_00023d22) >> 0xc)
  ;
  return;
}



// FUN_00002ee4 @ 0x2ee4

void FUN_00002ee4(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5)

{
  FUN_0000267c(param_2,param_3,param_4,param_1);
  FUN_00002d28(param_5);
  return;
}



// FUN_00002f0c @ 0x2f0c

void FUN_00002f0c(undefined4 param_1)

{
  undefined1 auStack_16 [18];
  
  DAT_0002721c = 0x2b78;
  FUN_00002738();
  FUN_00002832();
  FUN_00002346(&DAT_00023cbc,&DAT_00023cd4,param_1);
  FUN_00002346(param_1,&DAT_00023d04,auStack_16);
  FUN_00002346(auStack_16,&DAT_00023cec,param_1);
  return;
}



// FUN_00002f64 @ 0x2f64

void FUN_00002f64(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5)

{
  FUN_0000267c(param_2,param_3,param_4,param_1);
  FUN_00002f0c(param_5);
  return;
}



// FUN_00002f8a @ 0x2f8a

void FUN_00002f8a(undefined4 param_1)

{
  undefined1 auStack_16 [18];
  
  DAT_0002721c = 0x3016;
  FUN_00002738();
  FUN_00002832();
  FUN_00002872();
  FUN_00002346(&DAT_00023ca4,&DAT_00023c8c,param_1);
  FUN_00002346(param_1,&DAT_00023cbc,auStack_16);
  FUN_00002346(auStack_16,&DAT_00023cd4,param_1);
  FUN_00002346(param_1,&DAT_00023d04,auStack_16);
  FUN_00002346(auStack_16,&DAT_00023cec,param_1);
  return;
}



// FUN_00003002 @ 0x3002

void FUN_00003002(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5,undefined4 param_6,undefined4 param_7)

{
  FUN_0000267c(param_1,param_2,param_3,param_4);
  FUN_000026f2(param_5,param_6);
  FUN_00002f8a(param_7);
  return;
}



// FUN_00003036 @ 0x3036

void FUN_00003036(undefined4 param_1)

{
  undefined1 auStack_16 [18];
  
  FUN_00002738();
  FUN_00002832();
  FUN_00002872();
  FUN_00002346(&DAT_00023cec,&DAT_00023d04,param_1);
  FUN_00002346(param_1,&DAT_00023cd4,auStack_16);
  FUN_00002346(auStack_16,&DAT_00023cbc,param_1);
  FUN_00002346(param_1,&DAT_00023c8c,auStack_16);
  FUN_00002346(auStack_16,&DAT_00023ca4,param_1);
  return;
}



// FUN_000030a4 @ 0x30a4

void FUN_000030a4(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5,undefined4 param_6,undefined4 param_7)

{
  FUN_0000267c(param_1,param_2,param_3,param_4);
  FUN_000026f2(param_5,param_6);
  FUN_00003036(param_7);
  return;
}



// FUN_000030d8 @ 0x30d8

void FUN_000030d8(int param_1)

{
  undefined2 uVar1;
  
  uVar1 = *(undefined2 *)(param_1 + 6);
  *(undefined2 *)(param_1 + 6) = *(undefined2 *)(param_1 + 2);
  *(undefined2 *)(param_1 + 2) = uVar1;
  uVar1 = *(undefined2 *)(param_1 + 0xc);
  *(undefined2 *)(param_1 + 0xc) = *(undefined2 *)(param_1 + 4);
  *(undefined2 *)(param_1 + 4) = uVar1;
  uVar1 = *(undefined2 *)(param_1 + 0xe);
  *(undefined2 *)(param_1 + 0xe) = *(undefined2 *)(param_1 + 10);
  *(undefined2 *)(param_1 + 10) = uVar1;
  return;
}



// FUN_00003108 @ 0x3108

void FUN_00003108(undefined4 param_1)

{
  DAT_00023d2c = -DAT_00023d2c;
  DAT_00023d28 = -DAT_00023d28;
  DAT_00023d24 = -DAT_00023d24;
  DAT_00023d20 = -DAT_00023d20;
  DAT_00023d1c = -DAT_00023d1c;
  DAT_00023d18 = -DAT_00023d18;
  (*DAT_0002721c)(param_1);
  return;
}



// FUN_0000313e @ 0x313e

void FUN_0000313e(int param_1,int *param_2,int *param_3,uint param_4)

{
  short sVar1;
  short sVar2;
  ushort uVar3;
  short sVar4;
  
  param_1 = param_1 + -1;
  if (param_1 != -1) {
    do {
      do {
        sVar4 = (short)(*param_2 >> (param_4 & 0x3f));
        sVar1 = (short)(param_2[1] >> (param_4 & 0x3f));
        sVar2 = (short)(param_2[2] >> (param_4 & 0x3f));
        *param_3 = (int)sVar2 * (int)DAT_00023c80 +
                   (int)sVar1 * (int)DAT_00023c7a + (int)sVar4 * (int)DAT_00023c74 + 0x800 >> 0xc;
        param_3[1] = (int)sVar2 * (int)DAT_00023c82 +
                     (int)sVar1 * (int)DAT_00023c7c + (int)sVar4 * (int)DAT_00023c76 + 0x800 >> 0xc;
        param_3[2] = (int)sVar2 * (int)DAT_00023c84 +
                     (int)sVar1 * (int)DAT_00023c7e + (int)sVar4 * (int)DAT_00023c78 + 0x800 >> 0xc;
        param_2 = param_2 + 3;
        param_3 = param_3 + 3;
        uVar3 = (ushort)((uint)param_1 >> 0x10);
        sVar4 = (short)param_1 + -1;
        param_1 = CONCAT22(uVar3,sVar4);
      } while (sVar4 != -1);
      param_1 = (uint)uVar3 * 0x10000 + -1;
    } while (uVar3 != 0);
  }
  return;
}



// FUN_000031ea @ 0x31ea

void FUN_000031ea(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5,undefined4 param_6)

{
  undefined1 auStack_16 [18];
  
  FUN_00002d02(param_1,param_2,param_3,auStack_16);
  FUN_00003438(param_4,param_5,param_6,auStack_16);
  return;
}



// FUN_00003220 @ 0x3220

void FUN_00003220(int param_1,int param_2,int param_3,short *param_4)

{
  short sVar1;
  short sVar2;
  ushort uVar3;
  short sVar4;
  
  param_1 = param_1 + -1;
  if (param_1 != -1) {
    do {
      do {
        sVar4 = *(short *)(param_2 + 2);
        sVar1 = *(short *)(param_2 + 6);
        sVar2 = *(short *)(param_2 + 10);
        *(short *)(param_3 + 2) =
             (short)((int)sVar2 * (int)param_4[6] +
                     (int)sVar1 * (int)param_4[3] + (int)sVar4 * (int)*param_4 + 0x800 >> 0xc);
        *(short *)(param_3 + 6) =
             (short)((int)sVar2 * (int)param_4[7] +
                     (int)sVar1 * (int)param_4[4] + (int)sVar4 * (int)param_4[1] + 0x800 >> 0xc);
        *(short *)(param_3 + 10) =
             (short)((int)sVar2 * (int)param_4[8] +
                     (int)sVar1 * (int)param_4[5] + (int)sVar4 * (int)param_4[2] + 0x800 >> 0xc);
        param_2 = param_2 + 0xc;
        param_3 = param_3 + 0xc;
        uVar3 = (ushort)((uint)param_1 >> 0x10);
        sVar4 = (short)param_1 + -1;
        param_1 = CONCAT22(uVar3,sVar4);
      } while (sVar4 != -1);
      param_1 = (uint)uVar3 * 0x10000 + -1;
    } while (uVar3 != 0);
  }
  return;
}



// FUN_000032c0 @ 0x32c0

void FUN_000032c0(int param_1,int param_2,int *param_3,short *param_4)

{
  short sVar1;
  short sVar2;
  ushort uVar3;
  short sVar4;
  
  param_1 = param_1 + -1;
  if (param_1 != -1) {
    do {
      do {
        sVar4 = *(short *)(param_2 + 2);
        sVar1 = *(short *)(param_2 + 6);
        sVar2 = *(short *)(param_2 + 10);
        *param_3 = (int)sVar2 * (int)param_4[6] +
                   (int)sVar1 * (int)param_4[3] + (int)sVar4 * (int)*param_4 + 0x800 >> 0xc;
        param_3[1] = (int)sVar2 * (int)param_4[7] +
                     (int)sVar1 * (int)param_4[4] + (int)sVar4 * (int)param_4[1] + 0x800 >> 0xc;
        param_3[2] = (int)sVar2 * (int)param_4[8] +
                     (int)sVar1 * (int)param_4[5] + (int)sVar4 * (int)param_4[2] + 0x800 >> 0xc;
        param_2 = param_2 + 0xc;
        param_3 = param_3 + 3;
        uVar3 = (ushort)((uint)param_1 >> 0x10);
        sVar4 = (short)param_1 + -1;
        param_1 = CONCAT22(uVar3,sVar4);
      } while (sVar4 != -1);
      param_1 = (uint)uVar3 * 0x10000 + -1;
    } while (uVar3 != 0);
  }
  return;
}



// FUN_0000335e @ 0x335e

void FUN_0000335e(void)

{
  int iVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  short sVar5;
  uint uVar6;
  int *piVar7;
  int *piVar8;
  int *piVar9;
  int iVar10;
  
  piVar9 = &DAT_00022994 + DAT_000215a0;
  iVar10 = ((int)DAT_000219bc._2_2_ - (int)DAT_000215a0._2_2_) + -1;
  if ((short)iVar10 != -1) {
    do {
      iVar1 = *piVar9;
      piVar7 = *(int **)(iVar1 + 0x4e);
      sVar5 = *(short *)(iVar1 + 0x38) + -1;
      if (sVar5 != -1) {
        uVar6 = (uint)*(short *)(iVar1 + 0x34);
        piVar8 = piVar7;
        do {
          sVar2 = (short)(*piVar8 >> (uVar6 & 0x3f));
          sVar3 = (short)(piVar8[1] >> (uVar6 & 0x3f));
          sVar4 = (short)(piVar8[2] >> (uVar6 & 0x3f));
          *(short *)((int)piVar7 + 2) =
               (short)((int)sVar4 * (int)DAT_00023c80 +
                       (int)sVar3 * (int)DAT_00023c7a + (int)sVar2 * (int)DAT_00023c74 + 0x800 >>
                      0xc);
          *(short *)((int)piVar7 + 6) =
               (short)((int)sVar4 * (int)DAT_00023c82 +
                       (int)sVar3 * (int)DAT_00023c7c + (int)sVar2 * (int)DAT_00023c76 + 0x800 >>
                      0xc);
          *(short *)((int)piVar7 + 10) =
               (short)((int)sVar4 * (int)DAT_00023c84 +
                       (int)sVar3 * (int)DAT_00023c7e + (int)sVar2 * (int)DAT_00023c78 + 0x800 >>
                      0xc);
          piVar8 = piVar8 + 3;
          piVar7 = piVar7 + 3;
          sVar5 = sVar5 + -1;
        } while (sVar5 != -1);
      }
      iVar10 = iVar10 + -1;
      piVar9 = piVar9 + 1;
    } while (iVar10 != -1);
  }
  return;
}



// FUN_00003418 @ 0x3418

void FUN_00003418(void)

{
  int iVar1;
  short sVar2;
  int in_D0;
  short sVar3;
  short sVar4;
  short unaff_D5w;
  uint unaff_D6;
  int *in_A1;
  int *unaff_A2;
  short *unaff_A3;
  int *unaff_A4;
  int *piVar5;
  short *unaff_A5;
  int unaff_A6;
  
  do {
    *(short *)((int)in_A1 + 10) = (short)in_D0;
    in_A1 = in_A1 + 3;
    unaff_D5w = unaff_D5w + -1;
    unaff_A2 = unaff_A2 + 3;
    piVar5 = unaff_A4;
    if (unaff_D5w == -1) {
      do {
        unaff_A6 = unaff_A6 + -1;
        if (unaff_A6 == -1) {
          return;
        }
        unaff_A4 = piVar5 + 1;
        iVar1 = *piVar5;
        in_A1 = *(int **)(iVar1 + 0x4e);
        unaff_D5w = *(short *)(iVar1 + 0x38) + -1;
        piVar5 = unaff_A4;
      } while (unaff_D5w == -1);
      unaff_D6 = (uint)*(short *)(iVar1 + 0x34);
      unaff_A2 = in_A1;
    }
    sVar2 = (short)(*unaff_A2 >> (unaff_D6 & 0x3f));
    sVar3 = (short)(unaff_A2[1] >> (unaff_D6 & 0x3f));
    sVar4 = (short)(unaff_A2[2] >> (unaff_D6 & 0x3f));
    *(short *)((int)in_A1 + 2) =
         (short)((int)sVar4 * (int)unaff_A3[6] +
                 (int)sVar3 * (int)unaff_A3[3] + (int)sVar2 * (int)*unaff_A3 + 0x800 >> 0xc);
    *(short *)((int)in_A1 + 6) =
         (short)((int)sVar4 * (int)unaff_A3[7] +
                 (int)sVar3 * (int)unaff_A3[4] + (int)sVar2 * (int)unaff_A3[1] + 0x800 >> 0xc);
    in_D0 = (int)sVar4 * (int)*unaff_A5 +
            (int)sVar3 * (int)unaff_A3[5] + (int)sVar2 * (int)unaff_A3[2] + 0x800 >> 0xc;
  } while( true );
}



// FUN_00003438 @ 0x3438

void FUN_00003438(int param_1,int *param_2,uint *param_3,int param_4)

{
  int iVar1;
  int iVar2;
  longlong lVar3;
  short *psVar4;
  int *piVar5;
  int *piVar6;
  int iVar7;
  bool bVar8;
  int local_2c;
  int local_28 [4];
  int local_18;
  int local_14;
  int local_10;
  int local_c;
  int local_8;
  
  iVar7 = 0;
  do {
    psVar4 = (short *)(param_4 + iVar7 * 6);
    piVar5 = local_28 + iVar7 * 3;
    do {
      piVar6 = piVar5 + 1;
      *piVar5 = (int)*psVar4;
      psVar4 = psVar4 + 1;
      piVar5 = piVar6;
    } while ((int)piVar6 <= (int)(local_28 + iVar7 * 3 + 2));
    iVar7 = iVar7 + 1;
  } while (iVar7 < 3);
  local_2c = param_1 + -1;
  if (local_2c != -1) {
    do {
      iVar7 = *param_2;
      iVar1 = param_2[1];
      iVar2 = param_2[2];
      lVar3 = (longlong)local_28[0] * (longlong)iVar7 + (longlong)local_28[3] * (longlong)iVar1 +
              (longlong)local_10 * (longlong)iVar2;
      *param_3 = (int)((ulonglong)lVar3 >> 0x20) * 0x100000 | (uint)lVar3 >> 0xc;
      lVar3 = (longlong)local_28[1] * (longlong)iVar7 + (longlong)local_18 * (longlong)iVar1 +
              (longlong)local_c * (longlong)iVar2;
      param_3[1] = (int)((ulonglong)lVar3 >> 0x20) * 0x100000 | (uint)lVar3 >> 0xc;
      lVar3 = (longlong)local_28[2] * (longlong)iVar7 + (longlong)local_14 * (longlong)iVar1 +
              (longlong)local_8 * (longlong)iVar2;
      param_3[2] = (int)((ulonglong)lVar3 >> 0x20) * 0x100000 | (uint)lVar3 >> 0xc;
      param_2 = param_2 + 3;
      param_3 = param_3 + 3;
      bVar8 = local_2c != 0;
      local_2c = local_2c + -1;
    } while (bVar8);
  }
  return;
}



// FUN_00003524 @ 0x3524

void FUN_00003524(void)

{
  int iVar1;
  int iVar2;
  int iVar3;
  longlong lVar4;
  uint in_D0;
  uint in_D1;
  uint *unaff_A4;
  int *unaff_A5;
  int unaff_A6;
  
  while( true ) {
    unaff_A4[2] = in_D1 | in_D0;
    iVar3 = *(int *)(unaff_A6 + -0x28);
    *(int *)(unaff_A6 + -0x28) = iVar3 + -1;
    if (iVar3 == 0) break;
    iVar3 = unaff_A5[3];
    iVar1 = unaff_A5[4];
    iVar2 = unaff_A5[5];
    lVar4 = (longlong)*(int *)(unaff_A6 + -0x24) * (longlong)iVar3 +
            (longlong)*(int *)(unaff_A6 + -0x18) * (longlong)iVar1 +
            (longlong)*(int *)(unaff_A6 + -0xc) * (longlong)iVar2;
    unaff_A4[3] = (int)((ulonglong)lVar4 >> 0x20) * 0x100000 | (uint)lVar4 >> 0xc;
    lVar4 = (longlong)*(int *)(unaff_A6 + -0x20) * (longlong)iVar3 +
            (longlong)*(int *)(unaff_A6 + -0x14) * (longlong)iVar1 +
            (longlong)*(int *)(unaff_A6 + -8) * (longlong)iVar2;
    unaff_A4[4] = (int)((ulonglong)lVar4 >> 0x20) * 0x100000 | (uint)lVar4 >> 0xc;
    lVar4 = (longlong)*(int *)(unaff_A6 + -0x1c) * (longlong)iVar3 +
            (longlong)*(int *)(unaff_A6 + -0x10) * (longlong)iVar1 +
            (longlong)*(int *)(unaff_A6 + -4) * (longlong)iVar2;
    in_D0 = (uint)lVar4 >> 0xc;
    in_D1 = (int)((ulonglong)lVar4 >> 0x20) * 0x100000;
    unaff_A4 = unaff_A4 + 3;
    unaff_A5 = unaff_A5 + 3;
  }
  return;
}



// FUN_00003544 @ 0x3544

void FUN_00003544(int param_1,undefined4 param_2,int param_3,int param_4)

{
  int iVar1;
  uint uVar2;
  undefined4 local_2e;
  undefined4 local_2a;
  undefined4 local_26;
  undefined1 auStack_22 [18];
  int local_10;
  int local_c;
  int local_8;
  
  if ((param_3 == 0) || (param_4 == 0)) {
    iVar1 = FUN_0000371a(param_1,auStack_22);
    local_2e = *(undefined4 *)(param_1 + 4);
    local_2a = *(undefined4 *)(param_1 + 8);
    local_26 = *(undefined4 *)(param_1 + 0xc);
    if (iVar1 != 0) {
      FUN_00003438(1,&local_2e,&local_2e,auStack_22);
    }
    FUN_00002294(1,&local_2e,&local_2e,local_10,local_c,local_8);
    *(undefined4 *)(param_1 + 4) = local_2e;
    *(undefined4 *)(param_1 + 8) = local_2a;
    *(undefined4 *)(param_1 + 0xc) = local_26;
  }
  else {
    iVar1 = FUN_0000371a(param_1,auStack_22);
    if (iVar1 != 0) {
      FUN_00003438(param_2,param_3,param_4,auStack_22);
      param_3 = param_4;
    }
    uVar2 = (uint)*(short *)(param_1 + 0x36);
    FUN_00002294(param_2,param_3,param_4,local_10 << (uVar2 & 0x3f),local_c << (uVar2 & 0x3f),
                 local_8 << (uVar2 & 0x3f));
  }
  return;
}



// FUN_00003624 @ 0x3624

void FUN_00003624(int param_1,undefined4 param_2,undefined4 *param_3,undefined4 *param_4)

{
  ushort uVar1;
  int iVar2;
  int iVar3;
  undefined4 uVar4;
  undefined4 *puVar5;
  undefined4 uVar6;
  undefined4 uVar7;
  undefined4 uVar8;
  undefined4 local_22;
  undefined4 local_1e;
  undefined4 local_1a;
  undefined1 auStack_16 [18];
  
  local_22 = *(undefined4 *)(param_1 + 4);
  local_1e = *(undefined4 *)(param_1 + 8);
  local_1a = *(undefined4 *)(param_1 + 0xc);
  iVar3 = *(int *)(param_1 + 0x24);
  uVar1 = *(ushort *)(param_1 + 0x12);
  if (iVar3 != 0) {
    do {
      iVar2 = *(int *)(iVar3 + 0x32) + (uint)uVar1 * 0x18;
      FUN_00002d02((int)*(short *)(iVar2 + 2),(int)*(short *)(iVar2 + 4),(int)*(short *)(iVar2 + 6),
                   auStack_16);
      if ((param_3 == (undefined4 *)0x0) || (param_4 == (undefined4 *)0x0)) {
        FUN_00003438(1,&local_22,&local_22,auStack_16);
        uVar8 = *(undefined4 *)(iVar2 + 0x10);
        uVar7 = *(undefined4 *)(iVar2 + 0xc);
        uVar6 = *(undefined4 *)(iVar2 + 8);
        uVar4 = 1;
        puVar5 = &local_22;
      }
      else {
        FUN_00003438(param_2,param_3,param_4,auStack_16);
        uVar8 = *(undefined4 *)(iVar2 + 0x10);
        uVar7 = *(undefined4 *)(iVar2 + 0xc);
        uVar6 = *(undefined4 *)(iVar2 + 8);
        param_3 = param_4;
        uVar4 = param_2;
        puVar5 = param_4;
      }
      FUN_00002294(uVar4,puVar5,puVar5,uVar6,uVar7,uVar8);
      uVar1 = *(ushort *)(iVar3 + 0x12);
      iVar3 = *(int *)(iVar3 + 0x24);
    } while (iVar3 != 0);
  }
  if ((param_3 == (undefined4 *)0x0) && (param_4 == (undefined4 *)0x0)) {
    *(undefined4 *)(param_1 + 4) = local_22;
    *(undefined4 *)(param_1 + 8) = local_1e;
    *(undefined4 *)(param_1 + 0xc) = local_1a;
  }
  return;
}



// FUN_0000371a @ 0x371a

int FUN_0000371a(int param_1,undefined2 *param_2)

{
  int iVar1;
  int iVar2;
  int iVar3;
  ushort uVar4;
  short sVar5;
  undefined2 *puVar6;
  undefined2 *puVar7;
  int iVar8;
  int *piVar9;
  int *piVar10;
  short *psVar11;
  int *piVar12;
  int local_80;
  int *local_7c;
  int local_78;
  undefined2 *local_74;
  int local_70;
  int local_6c;
  int local_68;
  int local_64;
  int local_60;
  int local_5c;
  int local_58;
  int local_54;
  int local_50;
  int local_4c [9];
  undefined2 auStack_28 [9];
  undefined1 auStack_16 [12];
  short local_a [3];
  
  local_78 = 0;
  local_74 = auStack_28;
  piVar9 = (int *)(param_2 + 9);
  local_7c = local_4c;
  FUN_0001f712(piVar9,0xc);
  FUN_0001f712(param_2,0x12);
  param_2[8] = 0x1000;
  param_2[4] = 0x1000;
  *param_2 = 0x1000;
  local_80 = *(int *)(param_1 + 0x24);
  uVar4 = *(ushort *)(param_1 + 0x12);
  puVar7 = param_2;
  if (local_80 != 0) {
    piVar12 = piVar9;
    do {
      iVar8 = *(int *)(local_80 + 0x32) + (uint)uVar4 * 0x18;
      iVar1 = *(int *)(iVar8 + 8);
      iVar2 = *(int *)(iVar8 + 0xc);
      iVar3 = *(int *)(iVar8 + 0x10);
      if (((*(short *)(iVar8 + 2) == 0) && (*(short *)(iVar8 + 4) == 0)) &&
         (*(short *)(iVar8 + 6) == 0)) {
        *piVar12 = iVar1 + *piVar12;
        piVar12[1] = iVar2 + piVar12[1];
        piVar12[2] = iVar3 + piVar12[2];
        piVar9 = piVar12;
      }
      else {
        FUN_00002d02((int)*(short *)(iVar8 + 2),(int)*(short *)(iVar8 + 4),
                     (int)*(short *)(iVar8 + 6),auStack_16);
        if (local_78 == 0) {
          FUN_0001f63a(auStack_16,puVar7,0x12);
          puVar6 = puVar7;
        }
        else {
          FUN_00002346(puVar7,auStack_16,local_74);
          puVar6 = local_74;
          local_74 = puVar7;
        }
        iVar8 = 2;
        piVar9 = &local_58;
        piVar10 = piVar12 + 2;
        psVar11 = local_a;
        do {
          if (*piVar10 == 0) {
            piVar9[2] = 0;
            piVar9[1] = 0;
            *piVar9 = 0;
          }
          else {
            *piVar9 = *piVar10 * (int)*psVar11;
            piVar9[1] = *piVar10 * (int)psVar11[1];
            piVar9[2] = *piVar10 * (int)psVar11[2];
          }
          piVar9 = piVar9 + -3;
          piVar10 = piVar10 + -1;
          psVar11 = psVar11 + -3;
          uVar4 = (ushort)((uint)iVar8 >> 0x10);
          sVar5 = (short)iVar8 + -1;
          iVar8 = CONCAT22(uVar4,sVar5);
        } while ((sVar5 != -1) || (iVar8 = (uint)uVar4 * 0x10000 + -1, uVar4 != 0));
        *local_7c = (local_58 + local_64 + local_70 >> 0xc) + iVar1;
        local_7c[1] = (local_54 + local_60 + local_6c >> 0xc) + iVar2;
        local_7c[2] = (local_50 + local_5c + local_68 >> 0xc) + iVar3;
        local_78 = 1;
        puVar7 = puVar6;
        piVar9 = local_7c;
        local_7c = piVar12;
      }
      uVar4 = *(ushort *)(local_80 + 0x12);
      local_80 = *(int *)(local_80 + 0x24);
      piVar12 = piVar9;
    } while (local_80 != 0);
  }
  if (puVar7 != param_2) {
    FUN_0001f63a(puVar7,param_2,0x12);
  }
  *(int *)(param_2 + 9) = *piVar9;
  *(int *)(param_2 + 0xb) = piVar9[1];
  *(int *)(param_2 + 0xd) = piVar9[2];
  return local_78;
}



// FUN_000038e8 @ 0x38e8

undefined4 FUN_000038e8(void)

{
  undefined4 *unaff_A4;
  int unaff_A5;
  int unaff_A6;
  
  *(undefined4 *)(unaff_A5 + 0x12) = *unaff_A4;
  *(undefined4 *)(unaff_A5 + 0x16) = unaff_A4[1];
  *(undefined4 *)(unaff_A5 + 0x1a) = unaff_A4[2];
  return *(undefined4 *)(unaff_A6 + -0x74);
}



// FUN_00003908 @ 0x3908

int FUN_00003908(int param_1)

{
  if (param_1 < 0) {
    param_1 = -param_1;
  }
  return param_1;
}



// FUN_00003912 @ 0x3912

void FUN_00003912(void)

{
  return;
}



// FUN_00003914 @ 0x3914

void FUN_00003914(void)

{
  ushort ****ppppuVar1;
  byte bVar3;
  ushort uVar2;
  ushort ****ppppuVar4;
  uint uVar5;
  uint uVar6;
  undefined2 uVar7;
  int iVar8;
  ushort uVar9;
  ushort *****pppppuVar10;
  ushort uVar11;
  ushort uVar12;
  ushort ******ppppppuVar13;
  short sVar14;
  short sVar15;
  ushort uVar16;
  ushort *****pppppuVar17;
  ushort *****pppppuVar18;
  ushort ******ppppppuVar19;
  ushort ******ppppppuVar20;
  ushort ******ppppppuVar21;
  ushort ******unaff_A3;
  ushort ******ppppppuVar22;
  ushort ******ppppppuVar23;
  ushort ******unaff_A5;
  ushort ******ppppppuVar24;
  ushort ******ppppppuVar25;
  bool bVar26;
  int local_4a8;
  ushort ******local_468;
  ushort ******local_464 [7];
  ushort ******local_448;
  ushort ******local_444;
  ushort *****local_440 [128];
  ushort *****local_240;
  undefined4 local_23c;
  ushort ******local_38;
  ushort ******local_34;
  ushort ******local_30;
  undefined4 local_2c;
  short *local_24;
  ushort ******local_20;
  
  local_444 = &local_240;
  local_448 = local_440;
  local_20 = (ushort ******)&DAT_00024fdc;
  DAT_0002039c = (int)DAT_00021012 << 0x10 | 0x8000;
  PTR_PTR_000203b0 = (undefined *)((int)DAT_00023fd0 | 0xffff0000);
  local_240 = (ushort *****)&DAT_0002039c;
  local_23c = 0;
  local_464[0] = (ushort ******)0x1;
  local_4a8 = (int)DAT_00027012;
  if (DAT_0002101c != 0) {
    local_4a8 = local_4a8 >> 1;
  }
  local_24 = &DAT_00023fd4;
  (&DAT_00023fd4)[local_4a8] = 1;
  local_2c = DAT_00020f80;
  local_30 = DAT_00020f7c;
  local_38 = DAT_00020f7c;
  uVar5 = 0;
  iVar8 = 0;
  ppppppuVar25 = DAT_00020f7c;
  do {
    ppppppuVar23 = local_20;
    ppppppuVar19 = local_444;
    ppppppuVar21 = local_448;
    local_34 = ppppppuVar25;
    uVar9 = (ushort)iVar8;
    if ((short)uVar5 == 0) {
      *local_24 = 0;
      ppppppuVar13 = local_20 + 1;
      do {
        ppppppuVar20 = ppppppuVar13;
        local_24 = local_24 + 1;
        if (*ppppppuVar20 != (ushort *****)0x0) break;
        ppppppuVar13 = ppppppuVar20 + 1;
      } while (*local_24 == 0);
      uVar5 = (uint)-((int)(local_20 + 1) - (int)(ppppppuVar20 + 1)) >> 2;
      local_20 = ppppppuVar20;
      local_444 = local_448;
      local_448 = ppppppuVar19;
      ppppppuVar13 = ppppppuVar21;
      if (*ppppppuVar23 == (ushort *****)0x0) goto LAB_00003a66;
      pppppuVar10 = *ppppppuVar23 + 3;
      ppppuVar4 = *pppppuVar10;
      while( true ) {
        ppppppuVar20 = ppppppuVar19;
        pppppuVar18 = *ppppppuVar20;
        if (pppppuVar18 == (ushort *****)0x0) break;
        ppppppuVar19 = ppppppuVar20 + 1;
        if (uVar9 != *(ushort *)((int)pppppuVar18 + 10)) {
          pppppuVar17 = pppppuVar10;
          if ((int)ppppuVar4 < (int)*pppppuVar18) {
            if (pppppuVar10[3] == (ushort ****)0x0) {
              do {
                *ppppppuVar13 = pppppuVar10;
                ppppppuVar19 = ppppppuVar20;
                ppppppuVar13 = ppppppuVar13 + 1;
LAB_00003a66:
                do {
                  ppppppuVar20 = ppppppuVar19 + 1;
                  pppppuVar10 = *ppppppuVar19;
                  if (pppppuVar10 == (ushort *****)0x0) goto LAB_00003a6c;
                  ppppppuVar19 = ppppppuVar20;
                } while (uVar9 == *(ushort *)((int)pppppuVar10 + 10));
              } while( true );
            }
            pppppuVar17 = (ushort *****)(pppppuVar10[3] + 3);
            ppppuVar4 = *pppppuVar17;
            ppppppuVar19 = ppppppuVar20;
            pppppuVar18 = pppppuVar10;
          }
          *ppppppuVar13 = pppppuVar18;
          pppppuVar10 = pppppuVar17;
          ppppppuVar13 = ppppppuVar13 + 1;
        }
      }
LAB_00003a6c:
      *ppppppuVar23 = (ushort *****)0x0;
      *ppppppuVar13 = (ushort *****)0x0;
      unaff_A3 = ppppppuVar21;
      unaff_A5 = ppppppuVar23;
    }
    pppppuVar10 = (ushort *****)0xffffffff;
    uVar5 = uVar5 << 0x10;
    ppppuVar4 = (ushort ****)0xffffffff;
    ppppppuVar25 = ppppppuVar25 + 1;
    ppppppuVar21 = local_444;
    while( true ) {
      ppppppuVar19 = ppppppuVar21 + 1;
      pppppuVar18 = *ppppppuVar21;
      if (pppppuVar18 == (ushort *****)0x0) break;
      bVar3 = 0;
      ppppppuVar23 = (ushort ******)pppppuVar18[-3];
      ppppppuVar13 = (ushort ******)(pppppuVar18 + -2);
      do {
        uVar12 = *(ushort *)ppppppuVar23;
        *(ushort *)ppppppuVar23 = uVar12 ^ 0x8000;
        if ((uVar12 ^ 0x8000) == 0) {
          if (unaff_A5 == ppppppuVar23) {
            bVar3 = 0xff;
          }
        }
        else {
          if (ppppppuVar23[2] == (ushort *****)0x0) {
            ppppppuVar23[2] = (ushort *****)local_464[0];
            local_464[0] = ppppppuVar23;
          }
          if ((short)uVar5 < (short)*ppppppuVar23) {
            uVar5 = CONCAT22((short)(uVar5 >> 0x10),(short)*ppppppuVar23);
            bVar3 = bVar3 | 1;
            unaff_A5 = ppppppuVar23;
          }
        }
        unaff_A3 = ppppppuVar13 + 1;
        ppppppuVar23 = (ushort ******)*ppppppuVar13;
        ppppppuVar13 = unaff_A3;
      } while (ppppppuVar23 != (ushort ******)0x0);
      ppppuVar1 = *pppppuVar18;
      ppppppuVar23 = ppppppuVar25;
      if (bVar3 != 0) {
        if ((char)bVar3 < '\x01') {
          uVar5 = uVar5 & 0xffff0000;
          unaff_A3 = (ushort ******)local_464;
          ppppppuVar13 = local_464[0];
          do {
            if ((int)*ppppppuVar13 < 0) {
              sVar15 = (short)*ppppppuVar13;
              if ((short)uVar5 < sVar15) {
                uVar5 = CONCAT22((short)(uVar5 >> 0x10),sVar15);
                unaff_A5 = ppppppuVar13;
              }
              unaff_A3 = ppppppuVar13 + 2;
            }
            else {
              *unaff_A3 = ppppppuVar13[2];
              ppppppuVar13[2] = (ushort *****)0x0;
            }
            ppppppuVar13 = (ushort ******)*unaff_A3;
          } while (ppppppuVar13 != (ushort ******)0x1);
        }
        pppppuVar17 = unaff_A5[1];
        if (pppppuVar10 != pppppuVar17) {
          ppppppuVar23 = ppppppuVar25 + 1;
          *ppppppuVar25 =
               (ushort *****)CONCAT22((short)((uint)ppppuVar1 >> 0x10),(short)pppppuVar10);
          pppppuVar10 = pppppuVar17;
        }
      }
      ppppuVar1 = (ushort ****)((int)pppppuVar18[1] + (int)ppppuVar1);
      *pppppuVar18 = ppppuVar1;
      *(short *)(pppppuVar18 + 2) = (short)pppppuVar10;
      ppppppuVar25 = ppppppuVar23;
      if ((int)ppppuVar1 < (int)ppppuVar4) {
        if ((ushort)(*(short *)((int)pppppuVar18 + 10) - uVar9) == 1) {
          *pppppuVar18 = (ushort ****)0x7fff0000;
          ppppppuVar21 = ppppppuVar19;
        }
        else {
          ppppppuVar21 = ppppppuVar21 + -1;
          do {
            unaff_A3 = ppppppuVar21;
            unaff_A3[1] = *unaff_A3;
            ppppppuVar21 = unaff_A3 + -1;
          } while ((int)ppppuVar1 < (int)*unaff_A3[-1]);
          *unaff_A3 = pppppuVar18;
          uVar6 = uVar5 << 0x10 | uVar5 >> 0x10;
          sVar15 = (short)(uVar5 >> 0x10);
          if (0 < sVar15) {
            uVar6 = CONCAT22((short)uVar5,2 - sVar15);
          }
          uVar5 = uVar6 << 0x10 | uVar6 >> 0x10;
          ppppppuVar21 = ppppppuVar19;
        }
      }
      else {
        ppppuVar4 = ppppuVar1;
        ppppppuVar21 = ppppppuVar19;
      }
    }
    *local_34 = (ushort *****)(((uint)((int)ppppppuVar25 - (int)local_34) >> 2) - 1);
    for (ppppppuVar21 = local_464[0]; ppppppuVar21 != (ushort ******)0x1;
        ppppppuVar21 = (ushort ******)ppppppuVar21[2]) {
      *(ushort *)ppppppuVar21 = 0;
    }
    iVar8 = iVar8 + 1;
    sVar15 = (short)(uVar5 >> 0x10);
    uVar9 = sVar15 - 1;
    uVar5 = (uint)uVar9;
    bVar26 = (int)((uint)uVar9 << 0x10) < 0;
    if (uVar9 == 0 || SBORROW2(sVar15,1) != bVar26) {
      if (SBORROW2(sVar15,1) != bVar26) {
        uVar5 = (uint)(ushort)-uVar9;
      }
    }
    else {
      ppppppuVar21 = local_444;
      do {
        ppppppuVar19 = ppppppuVar21;
        ppppppuVar21 = ppppppuVar19 + 1;
      } while ((*ppppppuVar19)[1] == (ushort ****)0x0 && (uint)**ppppppuVar19 >> 0x10 == 0);
      uVar12 = *(ushort *)(ppppppuVar19[-1] + 2);
      sVar14 = (short)iVar8;
      uVar7 = (undefined2)((uint)iVar8 >> 0x10);
      if ((short)uVar9 < 4) {
LAB_00003be4:
        sVar15 = sVar15 + -2;
        ppppppuVar23 = ppppppuVar25;
        uVar16 = uVar9;
        do {
          ppppppuVar13 = (ushort ******)0xffffffff;
          pppppuVar10 = *ppppppuVar19;
          ppppppuVar20 = ppppppuVar21;
          ppppppuVar24 = ppppppuVar23 + 1;
          uVar11 = uVar12;
          do {
            unaff_A3 = (ushort ******)((int)pppppuVar10[1] + (int)*pppppuVar10);
            ppppppuVar25 = ppppppuVar24 + 1;
            *ppppppuVar24 = (ushort *****)CONCAT22((short)((uint)*pppppuVar10 >> 0x10),uVar11);
            uVar11 = *(ushort *)(pppppuVar10 + 2);
            *pppppuVar10 = (ushort ****)unaff_A3;
            if ((int)unaff_A3 < (int)ppppppuVar13) {
              uVar16 = uVar16 - sVar15;
              if ((short)(*(short *)((int)pppppuVar10 + 10) - sVar14) == 1) {
                *pppppuVar10 = (ushort ****)0x7fff0000;
                sVar15 = 0;
              }
              else {
                ppppppuVar24 = ppppppuVar20 + -2;
                do {
                  ppppppuVar22 = ppppppuVar24;
                  ppppppuVar22[1] = *ppppppuVar22;
                  ppppppuVar24 = ppppppuVar22 + -1;
                  if ((int)ppppppuVar24 < (int)ppppppuVar19) break;
                } while ((int)unaff_A3 < (int)**ppppppuVar24);
                *ppppppuVar22 = pppppuVar10;
                sVar15 = 0;
                unaff_A3 = ppppppuVar22;
              }
            }
            else {
              ppppppuVar13 = unaff_A3;
            }
            pppppuVar10 = *ppppppuVar20;
            ppppppuVar20 = ppppppuVar20 + 1;
            ppppppuVar24 = ppppppuVar25;
          } while (pppppuVar10 != (ushort *****)0x0);
          *ppppppuVar23 = (ushort *****)(((uint)((int)ppppppuVar25 - (int)ppppppuVar23) >> 2) - 1);
          sVar15 = sVar15 + -1;
          ppppppuVar23 = ppppppuVar25;
        } while (sVar15 != -1);
        iVar8 = CONCAT22(uVar7,uVar16 + sVar14);
        uVar5 = (uint)(ushort)(uVar9 - uVar16);
        unaff_A5 = ppppppuVar25;
      }
      else {
        pppppuVar18 = (ushort *****)0x0;
        pppppuVar10 = *ppppppuVar19;
        ppppuVar4 = (ushort ****)0x80000000;
        ppppppuVar23 = ppppppuVar21;
        uVar16 = uVar12;
        uVar11 = uVar9;
        do {
          ppppppuVar13 = (ushort ******)(pppppuVar10 + 2);
          ppppuVar1 = pppppuVar10[1];
          uVar2 = *(ushort *)ppppppuVar13;
          if (uVar16 != uVar2) {
            pppppuVar18 = (ushort *****)((int)pppppuVar18 + 1);
            uVar16 = uVar2;
          }
          if ((((int)ppppuVar1 < (int)ppppuVar4) &&
              (uVar2 = (ushort)((uint)((int)*pppppuVar10 - (int)unaff_A3[-2]) /
                               (uint)((int)ppppuVar4 - (int)ppppuVar1)),
              (short)uVar2 < (short)uVar11)) && (uVar11 = uVar2, (short)uVar2 < 2))
          goto LAB_00003be4;
          pppppuVar10 = *ppppppuVar23;
          ppppuVar4 = ppppuVar1;
          ppppppuVar23 = ppppppuVar23 + 1;
          unaff_A3 = ppppppuVar13;
        } while (pppppuVar10 != (ushort *****)0x0);
        uVar5 = (uint)(ushort)(uVar9 - uVar11);
        iVar8 = CONCAT22(uVar7,uVar11 + sVar14);
        sVar14 = (short)pppppuVar18 << 2;
        sVar15 = uVar11 - 1;
        ppppppuVar23 = ppppppuVar25;
        do {
          *ppppppuVar23 = pppppuVar18;
          ppppppuVar23 = (ushort ******)((int)ppppppuVar23 + sVar14 + 4);
          sVar15 = sVar15 + -1;
        } while (sVar15 != -1);
        unaff_A3 = ppppppuVar25 + 1;
        pppppuVar10 = *ppppppuVar19;
        do {
          ppppuVar4 = *pppppuVar10;
          ppppuVar1 = pppppuVar10[1];
          uVar9 = *(ushort *)(pppppuVar10 + 2);
          if (uVar12 == uVar9) {
            *pppppuVar10 = (ushort ****)((int)ppppuVar1 * (int)(short)uVar11 + (int)ppppuVar4);
          }
          else {
            uVar6 = (int)(short)uVar11 - 1;
            ppppppuVar25 = unaff_A3;
            do {
              *ppppppuVar25 = (ushort *****)CONCAT22((short)((uint)ppppuVar4 >> 0x10),uVar12);
              ppppuVar4 = (ushort ****)((int)ppppuVar1 + (int)ppppuVar4);
              ppppppuVar25 = (ushort ******)((int)ppppppuVar25 + sVar14 + 4);
              uVar16 = (short)uVar6 - 1;
              uVar6 = (uint)uVar16;
            } while (uVar16 != 0xffff);
            *pppppuVar10 = ppppuVar4;
            unaff_A3 = unaff_A3 + 1;
            uVar12 = uVar9;
          }
          pppppuVar10 = *ppppppuVar21;
          ppppppuVar21 = ppppppuVar21 + 1;
        } while (pppppuVar10 != (ushort *****)0x0);
        ppppppuVar25 = (ushort ******)((int)ppppppuVar25 - (int)sVar14);
        unaff_A5 = ppppppuVar25;
      }
    }
    if (local_4a8 <= iVar8) {
      local_468 = local_464[0];
      while (local_468 != (ushort ******)0x1) {
        *(ushort *)local_468 = 0;
        ppppppuVar25 = (ushort ******)local_468[2];
        local_468[2] = (ushort *****)0x0;
        local_468 = ppppppuVar25;
      }
      DAT_00026fe0 = 0;
      return;
    }
  } while( true );
}



// FUN_00003d66 @ 0x3d66

void FUN_00003d66(void)

{
  DAT_00003d42 = 0x3e54;
  DAT_00003d46 = &DAT_00003e5a;
  DAT_00003d4a = &DAT_00003e6c;
  DAT_00003d4e = &DAT_00003e74;
  DAT_00003d52 = &DAT_00003e78;
  DAT_00003d56 = &DAT_00003ea4;
  DAT_00003d5a = &DAT_00003ea4;
  DAT_00003d5e = &DAT_00003ea4;
  DAT_00003d62 = &DAT_00003ea4;
  return;
}



// FUN_00003dba @ 0x3dba

void FUN_00003dba(int *param_1,int *param_2,int param_3,byte *param_4,int param_5)

{
  ushort uVar4;
  undefined4 uVar1;
  uint uVar2;
  undefined4 uVar3;
  uint uVar5;
  ushort uVar7;
  uint uVar6;
  uint uVar8;
  undefined2 uVar9;
  uint uVar10;
  short sVar11;
  undefined4 *puVar12;
  uint *puVar13;
  uint *puVar14;
  byte *pbVar15;
  bool bVar16;
  
  puVar12 = (undefined4 *)*param_1 + 1;
  uVar3 = *(undefined4 *)*param_1;
  puVar14 = (uint *)*param_2 + 1;
  uVar6 = *(uint *)*param_2;
  uVar10 = 0;
  do {
    sVar11 = (short)uVar6 + (short)uVar3 + -1;
    uVar8 = 0;
    uVar1 = *puVar12;
    uVar5 = *puVar14;
    puVar12 = puVar12 + 1;
    puVar13 = puVar14 + 1;
    do {
      while( true ) {
        uVar4 = (ushort)uVar1;
        uVar7 = (ushort)uVar5;
        bVar16 = uVar7 == uVar4;
        uVar2 = CONCAT22((short)((uint)uVar1 >> 0x10),uVar7);
        if ((int)uVar5 < (int)uVar2) {
          uVar7 = (ushort)(uVar5 - uVar8 >> 0x10);
          puVar14 = puVar13 + 1;
          uVar6 = *puVar13;
          uVar3 = uVar1;
        }
        else if (uVar5 == uVar2) {
          uVar7 = (ushort)(uVar2 - uVar8 >> 0x10);
          puVar14 = puVar13 + 1;
          uVar6 = *puVar13;
          sVar11 = sVar11 + -1;
          uVar3 = *puVar12;
          uVar5 = uVar2;
          puVar12 = puVar12 + 1;
        }
        else {
          uVar7 = (ushort)(uVar2 - uVar8 >> 0x10);
          uVar3 = *puVar12;
          uVar6 = uVar5;
          uVar5 = uVar2;
          puVar12 = puVar12 + 1;
          puVar14 = puVar13;
        }
        uVar8 = uVar5 & 0xffff0000;
        if ((int)uVar10 < 0) {
          uVar4 = (ushort)((uint)uVar1 >> 8) & 0xff;
        }
        uVar2 = uVar10 & 1;
        uVar10 = uVar10 & 0xffff0001;
        uVar9 = (undefined2)(uVar10 >> 0x10);
        uVar1 = uVar3;
        uVar5 = uVar6;
        puVar13 = puVar14;
        pbVar15 = param_4;
        if (uVar2 != 0) break;
        uVar10 = CONCAT22(uVar9,uVar7 + (short)uVar10);
        if (!bVar16) {
LAB_00003e2c:
          if (8 < (short)uVar7) {
            uVar4 = uVar4 & 0xff | uVar4 << 8;
            sVar11 = (uVar7 >> 3) - 1;
            do {
              *(uint *)pbVar15 = CONCAT22(uVar4,uVar4);
              sVar11 = sVar11 + -1;
              pbVar15 = pbVar15 + 4;
            } while (sVar11 != -1);
            uVar7 = uVar7 & 7;
          }
                    /* WARNING: Could not recover jumptable at 0x00003e36. Too many branches */
                    /* WARNING: Treating indirect jump as call */
          (*(code *)(&DAT_00003d42)[(short)uVar7])();
          return;
        }
LAB_00003ebe:
        pbVar15 = param_4 + (short)(uVar7 >> 1);
        sVar11 = sVar11 + -1;
        param_4 = pbVar15;
        if (sVar11 == -1) goto LAB_00003ec6;
      }
      if (uVar7 != 0) {
        uVar10 = CONCAT22(uVar9,uVar7 + (short)uVar10);
        if (bVar16) {
          uVar7 = uVar7 + 1;
          goto LAB_00003ebe;
        }
        pbVar15 = param_4 + 1;
        *param_4 = (byte)uVar4 ^ ((byte)uVar4 ^ *param_4) & 0xf0;
        bVar16 = SBORROW2(uVar7,1);
        uVar7 = uVar7 - 1;
        if (uVar7 != 0 && bVar16 == (int)((uint)uVar7 << 0x10) < 0) goto LAB_00003e2c;
      }
      sVar11 = sVar11 + -1;
      param_4 = pbVar15;
    } while (sVar11 != -1);
LAB_00003ec6:
    param_4 = pbVar15 + param_5;
    param_3 = param_3 + -1;
    if (param_3 == 0) {
      *param_1 = (int)(puVar12 + -1);
      *param_2 = (int)(puVar14 + -1);
      return;
    }
    uVar10 = -uVar10 & 0xffff0000;
  } while( true );
}



// FUN_00003f12 @ 0x3f12

void FUN_00003f12(void)

{
  DAT_00003eee = 0x3ff6;
  DAT_00003ef2 = 0x3ffc;
  DAT_00003ef6 = &DAT_0000400e;
  DAT_00003efa = &DAT_00004016;
  DAT_00003efe = &DAT_0000401a;
  DAT_00003f02 = &DAT_00004046;
  DAT_00003f06 = &DAT_00004046;
  DAT_00003f0a = &DAT_00004046;
  DAT_00003f0e = &DAT_00004046;
  return;
}



// FUN_00003f66 @ 0x3f66

void FUN_00003f66(undefined4 *param_1,uint *param_2,int param_3,byte *param_4,int param_5)

{
  ushort uVar1;
  ushort uVar5;
  undefined4 uVar2;
  uint uVar3;
  undefined4 uVar4;
  uint uVar6;
  ushort uVar8;
  uint uVar7;
  uint uVar9;
  ushort uVar10;
  ushort uVar11;
  ushort uVar12;
  short sVar13;
  undefined4 *puVar14;
  uint *puVar15;
  uint *puVar16;
  byte *pbVar17;
  bool bVar18;
  
  puVar14 = param_1 + 1;
  uVar4 = *param_1;
  puVar16 = param_2 + 1;
  uVar7 = *param_2;
  do {
    uVar12 = 0;
    sVar13 = (short)uVar7 + (short)uVar4 + -1;
    uVar9 = 0;
    uVar2 = *puVar14;
    uVar6 = *puVar16;
    puVar14 = puVar14 + 1;
    puVar15 = puVar16 + 1;
    do {
      while( true ) {
        uVar5 = (ushort)uVar2;
        uVar8 = (ushort)uVar6;
        uVar3 = CONCAT22((short)((uint)uVar2 >> 0x10),uVar8);
        if ((int)uVar6 < (int)uVar3) {
          uVar10 = (ushort)(uVar6 - uVar9 >> 0x10);
          puVar16 = puVar15 + 1;
          uVar7 = *puVar15;
          uVar4 = uVar2;
        }
        else if (uVar6 == uVar3) {
          uVar10 = (ushort)(uVar3 - uVar9 >> 0x10);
          puVar16 = puVar15 + 1;
          uVar7 = *puVar15;
          sVar13 = sVar13 + -1;
          uVar4 = *puVar14;
          uVar6 = uVar3;
          puVar14 = puVar14 + 1;
        }
        else {
          uVar10 = (ushort)(uVar3 - uVar9 >> 0x10);
          uVar4 = *puVar14;
          uVar7 = uVar6;
          uVar6 = uVar3;
          puVar14 = puVar14 + 1;
          puVar16 = puVar15;
        }
        uVar9 = uVar6 & 0xffff0000;
        uVar1 = uVar12 & 1;
        uVar6 = uVar7;
        puVar15 = puVar16;
        pbVar17 = param_4;
        if ((uVar12 & 1) != 0) break;
        uVar11 = uVar10;
        if (uVar8 != uVar5) {
LAB_00003fce:
          if (8 < (short)uVar10) {
            uVar12 = uVar5 & 0xff | uVar5 << 8;
            sVar13 = (uVar10 >> 3) - 1;
            do {
              *(uint *)pbVar17 = CONCAT22(uVar12,uVar12);
              sVar13 = sVar13 + -1;
              pbVar17 = pbVar17 + 4;
            } while (sVar13 != -1);
            uVar10 = uVar10 & 7;
          }
                    /* WARNING: Could not recover jumptable at 0x00003fd8. Too many branches */
                    /* WARNING: Treating indirect jump as call */
          (*(code *)(&DAT_00003eee)[(short)uVar10])();
          return;
        }
LAB_00004060:
        uVar12 = uVar10 + uVar1;
        pbVar17 = param_4 + (short)(uVar11 >> 1);
        sVar13 = sVar13 + -1;
        uVar2 = uVar4;
        param_4 = pbVar17;
        if (sVar13 == -1) goto LAB_00004068;
      }
      uVar12 = uVar1;
      if (uVar10 != 0) {
        uVar12 = uVar10 + uVar1;
        if (uVar8 == uVar5) {
          uVar11 = uVar10 + 1;
          goto LAB_00004060;
        }
        pbVar17 = param_4 + 1;
        *param_4 = (byte)uVar2 ^ ((byte)uVar2 ^ *param_4) & 0xf0;
        bVar18 = SBORROW2(uVar10,1);
        uVar10 = uVar10 - 1;
        if (uVar10 != 0 && bVar18 == (int)((uint)uVar10 << 0x10) < 0) goto LAB_00003fce;
      }
      sVar13 = sVar13 + -1;
      uVar2 = uVar4;
      param_4 = pbVar17;
    } while (sVar13 != -1);
LAB_00004068:
    param_4 = pbVar17 + param_5;
    param_3 = param_3 + -1;
    if (param_3 == 0) {
      *param_1 = puVar14 + -1;
      *param_2 = (uint)(puVar16 + -1);
      return;
    }
  } while( true );
}



// FUN_000040b4 @ 0x40b4

void FUN_000040b4(void)

{
  DAT_00004090 = 0x4198;
  DAT_00004094 = &DAT_0000419e;
  DAT_00004098 = &DAT_000041b0;
  DAT_0000409c = &DAT_000041b8;
  DAT_000040a0 = &DAT_000041bc;
  DAT_000040a4 = &DAT_000041e8;
  DAT_000040a8 = &DAT_000041e8;
  DAT_000040ac = &DAT_000041e8;
  DAT_000040b0 = &DAT_000041e8;
  return;
}



// FUN_00004108 @ 0x4108

void FUN_00004108(undefined4 *param_1,uint *param_2,int param_3,byte *param_4,int param_5)

{
  ushort uVar1;
  ushort uVar5;
  undefined4 uVar2;
  uint uVar3;
  undefined4 uVar4;
  uint uVar6;
  ushort uVar8;
  uint uVar7;
  uint uVar9;
  ushort uVar10;
  ushort uVar11;
  ushort uVar12;
  short sVar13;
  undefined4 *puVar14;
  uint *puVar15;
  uint *puVar16;
  byte *pbVar17;
  bool bVar18;
  
  puVar14 = param_1 + 1;
  uVar4 = *param_1;
  puVar16 = param_2 + 1;
  uVar7 = *param_2;
  do {
    uVar12 = 0;
    sVar13 = (short)uVar7 + (short)uVar4 + -1;
    uVar9 = 0;
    uVar2 = *puVar14;
    uVar6 = *puVar16;
    puVar14 = puVar14 + 1;
    puVar15 = puVar16 + 1;
    do {
      while( true ) {
        uVar5 = (ushort)uVar2;
        uVar8 = (ushort)uVar6;
        uVar3 = CONCAT22((short)((uint)uVar2 >> 0x10),uVar8);
        if ((int)uVar6 < (int)uVar3) {
          uVar10 = (ushort)(uVar6 - uVar9 >> 0x10);
          puVar16 = puVar15 + 1;
          uVar7 = *puVar15;
          uVar4 = uVar2;
        }
        else if (uVar6 == uVar3) {
          uVar10 = (ushort)(uVar3 - uVar9 >> 0x10);
          puVar16 = puVar15 + 1;
          uVar7 = *puVar15;
          sVar13 = sVar13 + -1;
          uVar4 = *puVar14;
          uVar6 = uVar3;
          puVar14 = puVar14 + 1;
        }
        else {
          uVar10 = (ushort)(uVar3 - uVar9 >> 0x10);
          uVar4 = *puVar14;
          uVar7 = uVar6;
          uVar6 = uVar3;
          puVar14 = puVar14 + 1;
          puVar16 = puVar15;
        }
        uVar9 = uVar6 & 0xffff0000;
        uVar1 = uVar12 & 1;
        uVar6 = uVar7;
        puVar15 = puVar16;
        pbVar17 = param_4;
        if ((uVar12 & 1) != 0) break;
        uVar11 = uVar10;
        if (uVar8 != uVar5) {
LAB_00004170:
          if (8 < (short)uVar10) {
            uVar12 = uVar5 & 0xff | uVar5 << 8;
            sVar13 = (uVar10 >> 3) - 1;
            do {
              *(uint *)pbVar17 = CONCAT22(uVar12,uVar12);
              sVar13 = sVar13 + -1;
              pbVar17 = pbVar17 + 4;
            } while (sVar13 != -1);
            uVar10 = uVar10 & 7;
          }
                    /* WARNING: Could not recover jumptable at 0x0000417a. Too many branches */
                    /* WARNING: Treating indirect jump as call */
          (*(code *)(&DAT_00004090)[(short)uVar10])();
          return;
        }
LAB_00004202:
        uVar12 = uVar10 + uVar1;
        pbVar17 = param_4 + (short)(uVar11 >> 1);
        sVar13 = sVar13 + -1;
        uVar2 = uVar4;
        param_4 = pbVar17;
        if (sVar13 == -1) goto LAB_0000420a;
      }
      uVar12 = uVar1;
      if (uVar10 != 0) {
        uVar12 = uVar10 + uVar1;
        if (uVar8 == uVar5) {
          uVar11 = uVar10 + 1;
          goto LAB_00004202;
        }
        pbVar17 = param_4 + 1;
        *param_4 = (byte)uVar2 ^ ((byte)uVar2 ^ *param_4) & 0xf0;
        bVar18 = SBORROW2(uVar10,1);
        uVar10 = uVar10 - 1;
        if (uVar10 != 0 && bVar18 == (int)((uint)uVar10 << 0x10) < 0) goto LAB_00004170;
      }
      sVar13 = sVar13 + -1;
      uVar2 = uVar4;
      param_4 = pbVar17;
    } while (sVar13 != -1);
LAB_0000420a:
    param_4 = pbVar17 + param_5;
    param_3 = param_3 + -1;
    if (param_3 == 0) {
      *param_1 = puVar14 + -1;
      *param_2 = (uint)(puVar16 + -1);
      return;
    }
  } while( true );
}



// FUN_00004232 @ 0x4232

void FUN_00004232(void)

{
  return;
}



// FUN_00004234 @ 0x4234

void FUN_00004234(void)

{
  short sVar1;
  undefined4 *puVar2;
  
  DAT_00027224 = &DAT_00004336;
  DAT_00027228 = &DAT_00004342;
  DAT_0002722c = &DAT_00004350;
  DAT_00027230 = &DAT_00004340;
  DAT_00027234 = &DAT_0000434e;
  DAT_00027238 = &DAT_0000433e;
  sVar1 = 0x7f9;
  puVar2 = &DAT_0002723c;
  do {
    *puVar2 = &DAT_00004358;
    sVar1 = sVar1 + -1;
    puVar2 = puVar2 + 1;
  } while (sVar1 != -1);
  DAT_00029224 = &DAT_00004336;
  DAT_00029228 = &DAT_00004342;
  DAT_0002922c = &DAT_00004382;
  DAT_00029230 = &DAT_0000438c;
  DAT_00029234 = &DAT_0000439c;
  DAT_00029238 = &DAT_000043a8;
  DAT_0002923c = &DAT_00004398;
  sVar1 = 0x7f8;
  puVar2 = &DAT_00029240;
  do {
    *puVar2 = &DAT_000043b6;
    sVar1 = sVar1 + -1;
    puVar2 = puVar2 + 1;
  } while (sVar1 != -1);
  return;
}



// FUN_000042b4 @ 0x42b4

void FUN_000042b4(int *param_1,int *param_2,int param_3)

{
  short sVar1;
  short sVar2;
  undefined4 uVar3;
  short sVar5;
  int iVar4;
  short sVar6;
  ushort uVar7;
  short sVar8;
  undefined4 *puVar9;
  int *piVar10;
  int *piVar11;
  undefined4 *puVar12;
  undefined4 *puVar13;
  undefined4 *puVar14;
  
  puVar12 = &DAT_00027224;
  puVar14 = &DAT_00029224;
  puVar9 = (undefined4 *)*param_1 + 1;
  uVar3 = *(undefined4 *)*param_1;
  piVar11 = (int *)*param_2 + 1;
  iVar4 = *(int *)*param_2;
  do {
    sVar8 = (short)iVar4 + (short)uVar3 + -1;
    sVar6 = 0;
    uVar3 = *puVar9;
    iVar4 = *piVar11;
    puVar9 = puVar9 + 1;
    piVar10 = piVar11 + 1;
    puVar13 = puVar12;
    do {
      sVar1 = (short)uVar3;
      sVar5 = (short)iVar4;
      sVar2 = (short)((uint)uVar3 >> 0x10);
      if (iVar4 < CONCAT22(sVar2,sVar5)) {
        uVar7 = (short)((uint)iVar4 >> 0x10) - sVar6;
        if (sVar5 != sVar1) {
                    /* WARNING: Could not recover jumptable at 0x0000431a. Too many branches */
                    /* WARNING: Treating indirect jump as call */
          (*(code *)puVar13[(short)uVar7])();
          return;
        }
LAB_000043c0:
        piVar11 = piVar10 + 1;
        iVar4 = *piVar10;
      }
      else {
        if (iVar4 == CONCAT22(sVar2,sVar5)) {
          uVar7 = sVar2 - sVar6;
          uVar3 = *puVar9;
          if (sVar5 != sVar1) {
                    /* WARNING: Could not recover jumptable at 0x00004334. Too many branches */
                    /* WARNING: Treating indirect jump as call */
            (*(code *)puVar13[(short)uVar7])();
            return;
          }
          sVar8 = sVar8 + -1;
          puVar9 = puVar9 + 1;
          goto LAB_000043c0;
        }
        uVar7 = sVar2 - sVar6;
        uVar3 = *puVar9;
        puVar9 = puVar9 + 1;
        piVar11 = piVar10;
        if (sVar5 != sVar1) {
                    /* WARNING: Could not recover jumptable at 0x00004302. Too many branches */
                    /* WARNING: Treating indirect jump as call */
          (*(code *)puVar13[(short)uVar7])();
          return;
        }
      }
      sVar6 = uVar7 + sVar6;
      puVar12 = puVar13;
      if ((uVar7 & 1) != 0) {
        puVar12 = puVar14;
        puVar14 = puVar13;
      }
      sVar8 = sVar8 + -1;
      piVar10 = piVar11;
      puVar13 = puVar12;
    } while (sVar8 != -1);
    param_3 = param_3 + -1;
    if (param_3 == 0) {
      *param_1 = (int)(puVar9 + -1);
      *param_2 = (int)(piVar11 + -1);
      return;
    }
  } while( true );
}



// FUN_000043f4 @ 0x43f4

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_000043f4(void)

{
  int iVar1;
  int iVar2;
  uint *puVar3;
  int *piVar4;
  int *piVar5;
  short local_56 [2];
  undefined1 auStack_52 [30];
  undefined1 auStack_34 [18];
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  if ((SUB_00020b28 != (int *)0x0) && (iVar1 = *SUB_00020b28, iVar1 != 0)) {
    if (DAT_00023d84 == 0) {
      FUN_0000121a(&DAT_00023d6c,1);
    }
    else if (DAT_00023d84 == 2) {
      iVar2 = *DAT_0002104c;
      local_e = 0;
      local_a = 0x2b4;
      local_6 = -0xed7;
      FUN_00002d02(0,(int)*(short *)(DAT_0002104c + 0x195),0,auStack_22);
      FUN_0000371a(iVar2,auStack_52);
      FUN_00002346(auStack_52,auStack_22,auStack_34);
      FUN_00003220(1,auStack_10,auStack_10,auStack_34);
      DAT_00023d6c = *(int *)(iVar2 + 4) + (int)local_e;
      DAT_00023d70 = *(int *)(iVar2 + 8) + (int)local_a;
      DAT_00023d74 = *(int *)(iVar2 + 0xc) + (int)local_6;
      local_e = 0;
      local_a = 0;
      local_6 = 0x1000;
      FUN_00003220(1,auStack_10,auStack_10,auStack_34);
      local_56[1] = 0;
      local_56[0] = 0;
      FUN_00002574((int)local_e,(int)local_a,(int)local_6,0x1000,0x1000000,local_56 + 1,local_56);
      DAT_00023d7c = (int)local_56[0];
    }
    *(int *)(iVar1 + 4) = DAT_00023d6c;
    *(int *)(iVar1 + 8) = DAT_00023d70;
    *(int *)(iVar1 + 0xc) = DAT_00023d74;
    piVar4 = SUB_00020b28;
    *(undefined2 *)(SUB_00020b28 + 8) = DAT_00023d7a;
    *(undefined2 *)((int)piVar4 + 0x22) = DAT_00023d7c._2_2_;
    *(undefined2 *)(piVar4 + 9) = DAT_00023d82;
    *(undefined2 *)((int)piVar4 + 0x626) = *(undefined2 *)(piVar4[1] + 0x44);
    FUN_0000f380((int)SUB_00020b28 + 0x36,(int)*(short *)(piVar4 + 8),
                 (int)*(short *)((int)piVar4 + 0x22),(int)*(short *)(piVar4 + 9));
    FUN_0000f346((int)SUB_00020b28 + 0x36,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                 *(undefined4 *)(iVar1 + 0xc));
    FUN_000083fe();
    piVar4 = SUB_00020b28;
    *(undefined1 *)((int)SUB_00020b28 + 0x68a) = 0x2d;
    *(byte *)((int)piVar4 + 0x68b) = *(byte *)((int)piVar4 + 0x68b) | 2;
    *(undefined1 *)(piVar4 + 0x1a3) = 0;
    *(undefined1 *)(piVar4 + 0x193) = 1;
    *(int *)((int)piVar4 + 0x2e) = *(int *)(iVar1 + 8) - (int)*(short *)(piVar4[1] + 0x42);
    *(undefined1 *)(piVar4 + 0xb) = 0x30;
    *(undefined1 *)((int)piVar4 + 0x64e) = 0;
    *(undefined1 *)((int)piVar4 + 0x64f) = 0;
    *(undefined1 *)(piVar4 + 0x1a1) = 0;
    *(undefined1 *)((int)piVar4 + 0x687) = 0;
    *(undefined2 *)((int)piVar4 + 0x682) = 0;
    *(undefined1 *)(piVar4 + 0x1a2) = 0;
    piVar4[4] = 0;
    piVar4[3] = 0;
    piVar4[2] = 0;
    *(undefined2 *)(piVar4 + 0x1a6) = 0x4000;
    FUN_0000f3ba((int)SUB_00020b28 + 0x36,(int)*(short *)(piVar4 + 0x1a6));
    piVar4 = SUB_00020b28;
    *(undefined1 *)((int)SUB_00020b28 + 0x676) = 0;
    piVar4[0x19f] = 0;
    piVar4[0x19e] = 0;
    *(undefined4 *)((int)piVar4 + 0x62a) = 0;
    *(undefined4 *)((int)piVar4 + 0x62e) = 0;
    piVar5 = DAT_0002104c;
    *(int *)((int)piVar4 + 0x632) = DAT_0002104c[0x19d];
    *(undefined2 *)((int)piVar4 + 0x636) = *(undefined2 *)((int)piVar5 + 10);
    *(undefined2 *)(piVar4 + 0x18e) = *(undefined2 *)((int)piVar5 + 0x12);
    *(ushort *)((int)piVar4 + 0x63a) =
         *(short *)((int)piVar5 + 0x22) - *(short *)(piVar5 + 10) & 0x3fff;
    *(undefined2 *)(piVar4 + 400) = 0;
    *(undefined2 *)((int)piVar4 + 0x63e) = 0;
    *(undefined2 *)(piVar4 + 399) = 0;
    *(undefined2 *)(piVar4 + 0x191) = 0;
    *(undefined2 *)((int)piVar4 + 0x646) = 0;
    DAT_00021050 = 0;
    *(undefined2 *)(SUB_000207fc + 0xda) = 0;
    _DAT_0002105c = 0;
    _DAT_0002105a = 0;
    DAT_00021054 = 0;
    if ((DAT_00021064 != (int *)0x0) && (*DAT_00021064 != 0)) {
      FUN_000060fc(*DAT_00021064);
      DAT_00021064 = (int *)0x0;
    }
    *(uint *)(iVar1 + 0x14) = *(uint *)(iVar1 + 0x14) & 0xfffffffd;
    *(undefined4 *)(iVar1 + 0x3e) = 0;
    DAT_00021020 = DAT_00021020 + 0x4b;
    DAT_00023d88 = 1;
    if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
      puVar3 = (uint *)(*SUB_00020b28 + 0x14);
      *puVar3 = *puVar3 | 0x40;
    }
    FUN_00016136(*SUB_00020b28);
    FUN_00008014();
  }
  return;
}



// FUN_00004702 @ 0x4702

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00004702(int param_1)

{
  if (DAT_00023c30 != 0) {
    FUN_00006150(DAT_00023c30);
  }
  DAT_00023c30 = *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + param_1 * 0x18 + 0x74);
  FUN_0000121a(&DAT_00023d6c,0);
  DAT_00023d68 = DAT_00023d6c;
  DAT_00023d64 = DAT_00023d70;
  DAT_00023d60 = DAT_00023d74;
  DAT_00023d88 = 1;
  _DAT_00023d86 = 1;
  _DAT_00020b2c = 1;
  DAT_00023d54 = 1000000000;
  FUN_00008014();
  FUN_00005cb8(DAT_00023c30);
  DAT_00021020 = DAT_00021020 + 0x4b;
  FUN_0000f380(SUB_00020b28 + 0x36,(int)*(short *)(SUB_00020b28 + 0x20),
               (int)*(short *)(SUB_00020b28 + 0x22),(int)*(short *)(SUB_00020b28 + 0x24));
  FUN_000083fe();
  FUN_000072ac();
  FUN_0000a6c4();
  return;
}



// FUN_000047d6 @ 0x47d6

void FUN_000047d6(int param_1)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  
  iVar1 = FUN_000038e8(*(int *)(param_1 + 4) - DAT_00023d68);
  iVar2 = FUN_000038e8(*(int *)(param_1 + 8) - DAT_00023d64);
  iVar3 = FUN_000038e8(*(int *)(param_1 + 0xc) - DAT_00023d60);
  iVar4 = iVar1;
  if (iVar1 < iVar2) {
    iVar4 = iVar2;
    iVar2 = iVar1;
  }
  iVar1 = iVar3;
  if (iVar4 < iVar3) {
    iVar1 = iVar4;
    iVar4 = iVar3;
  }
  *(int *)(param_1 + 0x1c) = iVar4 + (iVar1 + iVar2 >> 2);
  return;
}



// FUN_0000483e @ 0x483e

int FUN_0000483e(int param_1,int param_2,int param_3)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  
  iVar1 = FUN_000038e8(param_1 - DAT_00023d68);
  iVar2 = FUN_000038e8(param_2 - DAT_00023d64);
  iVar3 = FUN_000038e8(param_3 - DAT_00023d60);
  iVar4 = iVar1;
  if (iVar1 < iVar2) {
    iVar4 = iVar2;
    iVar2 = iVar1;
  }
  iVar1 = iVar3;
  if (iVar4 < iVar3) {
    iVar1 = iVar4;
    iVar4 = iVar3;
  }
  return iVar4 + (iVar1 + iVar2 >> 2);
}



// FUN_00004886 @ 0x4886

int FUN_00004886(void)

{
  int in_D0;
  int in_D1;
  int iVar1;
  int unaff_D3;
  
  iVar1 = in_D1;
  if (unaff_D3 < in_D1) {
    iVar1 = unaff_D3;
    unaff_D3 = in_D1;
  }
  return unaff_D3 + (iVar1 + in_D0 >> 2);
}



// FUN_000048a6 @ 0x48a6

int FUN_000048a6(int param_1,int param_2,int param_3,int param_4,int param_5,int param_6)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  
  iVar1 = FUN_000038e8(param_4 - param_1);
  iVar2 = FUN_000038e8(param_5 - param_2);
  iVar3 = FUN_000038e8(param_6 - param_3);
  iVar4 = iVar1;
  if (iVar1 < iVar2) {
    iVar4 = iVar2;
    iVar2 = iVar1;
  }
  iVar1 = iVar3;
  if (iVar4 < iVar3) {
    iVar1 = iVar4;
    iVar4 = iVar3;
  }
  return iVar4 + (iVar1 + iVar2 >> 2);
}



// FUN_00004908 @ 0x4908

int FUN_00004908(int param_1,int param_2,int param_3,short param_4,short param_5,short param_6,
                undefined4 param_7,undefined4 param_8,undefined4 param_9)

{
  int iVar1;
  int iVar2;
  short sVar3;
  short sVar4;
  short sVar5;
  short sVar6;
  int iVar7;
  int iVar8;
  int iVar9;
  int local_10;
  int local_c;
  int local_8;
  
  local_8 = param_1;
  local_10 = param_2;
  local_c = param_4 + param_1;
  iVar9 = param_5 + param_2;
  iVar7 = param_6 + param_3;
  sVar3 = FUN_000048a6(param_1,param_2,param_3,param_7,param_8,param_9);
  sVar4 = FUN_000048a6(local_c,iVar9,iVar7,param_7,param_8,param_9);
  sVar6 = 3;
  do {
    iVar1 = local_c + local_8 >> 1;
    iVar2 = iVar9 + local_10 >> 1;
    iVar8 = iVar7 + param_3 >> 1;
    sVar5 = FUN_000048a6(iVar1,iVar2,iVar8,param_7,param_8,param_9);
    if (sVar4 < sVar3) {
      local_10 = iVar2;
      local_8 = iVar1;
      sVar3 = sVar5;
    }
    else {
      iVar7 = iVar8;
      iVar8 = param_3;
      iVar9 = iVar2;
      local_c = iVar1;
      sVar4 = sVar5;
    }
    sVar6 = sVar6 + -1;
    param_3 = iVar8;
  } while (sVar6 != -1);
  if (sVar3 < sVar4) {
    sVar4 = sVar3;
  }
  return (int)sVar4;
}



// FUN_000049ec @ 0x49ec

undefined4 FUN_000049ec(int param_1,int param_2)

{
  int iVar1;
  short sVar2;
  
  sVar2 = *(short *)(param_1 + 0x32);
  if (param_2 < *(int *)(&DAT_000203b8 + sVar2 * 4)) {
    if (*(int *)(&DAT_000203b4 + sVar2 * 4) <= param_2) {
      return 0;
    }
    sVar2 = sVar2 + -2;
    iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    while (param_2 < iVar1) {
      sVar2 = sVar2 + -1;
      iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    }
    sVar2 = sVar2 + 1;
  }
  else {
    sVar2 = sVar2 + 1;
    iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    while (iVar1 <= param_2) {
      sVar2 = sVar2 + 1;
      iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    }
  }
  *(short *)(param_1 + 0x32) = sVar2;
  return 1;
}



// FUN_00004a5a @ 0x4a5a

undefined4 FUN_00004a5a(int param_1,int param_2)

{
  int iVar1;
  short sVar2;
  
  sVar2 = *(short *)(param_1 + 4);
  if (param_2 < *(int *)(&DAT_000203b8 + sVar2 * 4)) {
    if (*(int *)(&DAT_000203b4 + sVar2 * 4) <= param_2) {
      return 0;
    }
    sVar2 = sVar2 + -2;
    iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    while (param_2 < iVar1) {
      sVar2 = sVar2 + -1;
      iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    }
    sVar2 = sVar2 + 1;
  }
  else {
    sVar2 = sVar2 + 1;
    iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    while (iVar1 <= param_2) {
      sVar2 = sVar2 + 1;
      iVar1 = *(int *)(&DAT_000203b8 + sVar2 * 4);
    }
  }
  *(short *)(param_1 + 4) = sVar2;
  return 1;
}



// FUN_00004ac8 @ 0x4ac8

void FUN_00004ac8(int param_1)

{
  int iVar1;
  undefined4 *puVar2;
  int iVar3;
  
  iVar3 = *(int *)(param_1 + 0x4a);
  puVar2 = (undefined4 *)
           (*(int *)(param_1 + 0x4a) + *(short *)(param_1 + 0x3c) * 0x20 +
           *(short *)(param_1 + 0x3a) * 2);
  for (iVar1 = 0; iVar1 < *(short *)(param_1 + 0x3c); iVar1 = iVar1 + 1) {
    *puVar2 = *(undefined4 *)(iVar3 + 0xc);
    *(undefined2 *)(puVar2 + 1) = 0;
    iVar3 = iVar3 + 0x20;
    puVar2 = (undefined4 *)((int)puVar2 + 6);
  }
  return;
}



// FUN_00004b0c @ 0x4b0c

/* WARNING: Removing unreachable block (ram,0x00004d0e) */
/* WARNING: Removing unreachable block (ram,0x00004d30) */
/* WARNING: Removing unreachable block (ram,0x00004d52) */

void FUN_00004b0c(int param_1)

{
  undefined4 uVar1;
  undefined4 uVar2;
  undefined4 uVar3;
  undefined4 *puVar4;
  int iVar5;
  short sVar6;
  short sVar7;
  short sVar8;
  undefined1 uVar11;
  int iVar9;
  int iVar10;
  byte bVar12;
  byte bVar13;
  byte bVar14;
  short in_D1w;
  ushort uVar15;
  uint uVar16;
  int iVar17;
  byte *pbVar18;
  int iVar19;
  int local_42;
  int local_38;
  int local_34;
  int local_30;
  short local_2a;
  short local_1a;
  short local_16;
  short sStack_12;
  short local_e;
  short local_a;
  short sStack_6;
  
  if (((*(byte *)(param_1 + 0x17) & 2) == 0) && (*(char *)(param_1 + 0x28) == '\x02')) {
    sVar6 = *(short *)(SUB_000207fc + 0xaa);
    local_2a = sVar6;
    if (sVar6 != 0) {
      in_D1w = *(short *)(param_1 + 0x32);
      local_30 = in_D1w * 0x7d;
      local_34 = in_D1w * 0x84;
      local_38 = in_D1w * 0xa8;
      if ((*(uint *)(param_1 + 0x14) & 0x1280) != 0) {
        local_2a = 0;
      }
    }
    sVar7 = *(short *)(SUB_000207fc + 0xe);
    puVar4 = *(undefined4 **)(param_1 + 0x46);
    uVar1 = *puVar4;
    uVar3 = puVar4[1];
    uVar2 = puVar4[2];
    sVar8 = *(short *)(param_1 + 0x38);
    iVar5 = *(int *)(param_1 + 0x46);
    iVar17 = *(int *)(SUB_000207fc + 0x8c) +
             *(int *)(*(int *)(*(int *)(param_1 + 0x24) + 0x2a) + 0x2e +
                     (uint)*(ushort *)(param_1 + 0x10) * 0x18);
    pbVar18 = (byte *)(iVar17 + 0x22 + *(short *)(iVar17 + 0x1a) * 0xc +
                      *(short *)(iVar17 + 0x1e) * 0xc);
    iVar17 = *(int *)(param_1 + 0x4a);
    local_42 = 0;
    if (0 < *(short *)(param_1 + 0x3c)) {
      iVar19 = 0x20 - in_D1w;
      do {
        if ((*(byte *)(iVar17 + 0x13) & 4) == 0) {
          puVar4 = (undefined4 *)(iVar5 + sVar8 * 0xc + local_42 * 0xc);
          local_1a = (short)*puVar4;
          local_e = (short)uVar1;
          local_16 = (short)puVar4[1];
          local_a = (short)uVar3;
          sStack_12 = (short)puVar4[2];
          sStack_6 = (short)uVar2;
          iVar9 = (int)(short)(sStack_12 - sStack_6) * (int)DAT_00021570 +
                  (int)(short)(local_16 - local_a) * (int)DAT_00021572 +
                  (int)(short)(local_1a - local_e) * (int)DAT_00021574 >> 0xd;
        }
        else {
          iVar9 = 0xffff;
        }
        if (sVar7 == 1) {
          if (iVar9 < 0) {
            iVar10 = iVar9 + 0x10000;
            if (iVar10 < 0) {
              iVar10 = iVar9 + 0x10003;
            }
            uVar15 = (short)(iVar10 >> 2) + 0x4000;
          }
          else {
            if (iVar9 < 0) {
              iVar9 = iVar9 + 1;
            }
            uVar15 = (short)(iVar9 >> 1) + 0x8000;
          }
          uVar16 = (uint)uVar15;
          *(char *)(iVar17 + 0xc) = (char)(*pbVar18 * uVar16 >> 0x10);
          *(char *)(iVar17 + 0xd) = (char)(pbVar18[1] * uVar16 >> 0x10);
          uVar11 = (undefined1)(pbVar18[2] * uVar16 >> 0x10);
LAB_00004d60:
          *(undefined1 *)(iVar17 + 0xe) = uVar11;
          FUN_00001c06(iVar17);
        }
        else {
          if (sVar7 == 4) {
            if (iVar9 < 0) {
              iVar10 = iVar9 + 0x10000;
              if (iVar10 < 0) {
                iVar10 = iVar9 + 0x10003;
              }
              uVar15 = (short)(iVar10 >> 2) + 0x4000;
            }
            else {
              if (iVar9 < 0) {
                iVar9 = iVar9 + 1;
              }
              uVar15 = (short)(iVar9 >> 1) + 0x8000;
            }
            *(char *)(iVar17 + 0xc) =
                 (char)((uint)(((int)((uint)*pbVar18 * (uint)uVar15) >> 6) + 0x2000) >> 0x10);
            *(char *)(iVar17 + 0xd) =
                 (char)((uint)(((int)((uint)pbVar18[1] * (uint)uVar15) >> 6) + 0x2000) >> 0x10);
            uVar11 = (undefined1)
                     ((uint)(((int)((uint)pbVar18[2] * (uint)uVar15) >> 6) + 0x2000) >> 0x10);
            goto LAB_00004d60;
          }
          if (iVar9 < 0) {
            iVar10 = iVar9 + 0x10000;
            if (iVar10 < 0) {
              iVar10 = iVar9 + 0x10003;
            }
            uVar15 = (short)(iVar10 >> 2) + 0x4000;
          }
          else {
            if (iVar9 < 0) {
              iVar9 = iVar9 + 1;
            }
            uVar15 = (short)(iVar9 >> 1) + 0x8000;
          }
          uVar16 = (uint)uVar15;
          bVar12 = (byte)(*pbVar18 * uVar16 >> 0x10);
          bVar13 = (byte)(pbVar18[1] * uVar16 >> 0x10);
          bVar14 = (byte)(pbVar18[2] * uVar16 >> 0x10);
          if (local_2a != 0) {
            bVar12 = (byte)(local_30 + iVar19 * (uint)bVar12 >> 5);
            bVar13 = (byte)(local_34 + iVar19 * (uint)bVar13 >> 5);
            bVar14 = (byte)(local_38 + iVar19 * (uint)bVar14 >> 5);
          }
          *(byte *)(iVar17 + 0xc) = bVar12;
          *(byte *)(iVar17 + 0xd) = bVar13;
          *(byte *)(iVar17 + 0xe) = bVar14;
          if (iVar17 == *(int *)(DAT_00023fac + (uint)*(byte *)(iVar17 + 0xf) * 4)) {
            FUN_00001bb0(iVar17);
          }
          else {
            *(undefined1 *)(iVar17 + 0xf) = 0;
          }
        }
        pbVar18 = pbVar18 + (uint)pbVar18[8] * 2 + 0xe;
        iVar17 = iVar17 + 0x20;
        local_42 = local_42 + 1;
      } while (local_42 < *(short *)(param_1 + 0x3c));
    }
    if ((sVar6 != 0) && ((*(byte *)(param_1 + 0x16) & 2) != 0)) {
      FUN_00004ac8(param_1);
    }
  }
  return;
}



// FUN_00004e62 @ 0x4e62

void FUN_00004e62(int param_1)

{
  if (*(char *)(param_1 + 0x28) == '\x02') {
    FUN_000049ec(param_1,*(undefined4 *)(param_1 + 0x1c));
    FUN_00004b0c(param_1);
  }
  return;
}



// FUN_00004e88 @ 0x4e88

void FUN_00004e88(void)

{
  int iVar1;
  int iVar2;
  undefined1 *puVar3;
  int *piVar4;
  undefined4 *puVar5;
  
  if (DAT_0002100e == 4) {
    FUN_00001984();
  }
  iVar2 = 0;
  puVar3 = &DAT_00023e9c;
  puVar5 = DAT_00023fac;
  do {
    *puVar3 = 0;
    *puVar3 = 0;
    if (DAT_0002100e == 8) {
      *puVar5 = 0;
    }
    iVar1 = SUB_000207fc;
    puVar5 = puVar5 + 1;
    puVar3 = puVar3 + 1;
    iVar2 = iVar2 + 1;
  } while (iVar2 < 0x100);
  if (DAT_0002100e == 1) {
    *(int *)(*(int *)(DAT_0002159c + 0x4a) + 4) =
         (int)*(short *)(*(int *)(SUB_000207fc + 0x88) + 0x180);
    *(int *)(*(int *)(DAT_00021594 + 0x4a) + 4) = (int)*(short *)(*(int *)(iVar1 + 0x88) + 0x80);
  }
  else {
    FUN_00004e62(DAT_0002159c);
    FUN_00004e62(DAT_00021594);
  }
  FUN_00004e62(DAT_00021598);
  FUN_00004e62(DAT_0002158c);
  iVar2 = 0;
  if (0 < DAT_00020f2c) {
    piVar4 = &DAT_00020b44;
    do {
      if ((DAT_0002100e == 1) && ((*(byte *)(*piVar4 + 0x16) & 0x10) != 0)) {
        FUN_00005758(*piVar4,0x80);
      }
      else {
        FUN_00004e62(*piVar4);
      }
      piVar4 = piVar4 + 1;
      iVar2 = iVar2 + 1;
    } while (iVar2 < DAT_00020f2c);
  }
  iVar2 = 0;
  if (0 < DAT_00020b24) {
    puVar5 = &DAT_00020804;
    do {
      iVar1 = *(int *)*puVar5;
      if (((iVar1 != 0) && (0x1a < **(short **)(iVar1 + 4))) && (**(short **)(iVar1 + 4) < 0x23)) {
        FUN_00017dac(iVar1);
      }
      if (((DAT_0002100e == 1) && (iVar1 != 0)) &&
         ((4 < **(short **)(iVar1 + 4) && (**(short **)(iVar1 + 4) < 8)))) {
        FUN_00005758(*puVar5,0);
      }
      else {
        FUN_00004e62(*puVar5);
      }
      puVar5 = puVar5 + 1;
      iVar2 = iVar2 + 1;
    } while (iVar2 < DAT_00020b24);
  }
  return;
}



// FUN_00004fee @ 0x4fee

void FUN_00004fee(int *param_1,int param_2)

{
  bool bVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  int *piVar6;
  
  if ((((*(byte *)((int)param_1 + 0x17) & 2) == 0) && (0 < *(short *)(SUB_000207fc + 0xa8))) &&
     (*(short *)(SUB_000207fc + 0xa8) < 6)) {
    iVar5 = *(int *)(SUB_000207fc + 0x8c) +
            *(int *)(*(int *)(param_1[9] + 0x2a) + 0x2e + (uint)*(ushort *)(param_1 + 4) * 0x18);
    if ((*(byte *)((int)param_1 + 0x15) & 2) == 0) {
      iVar4 = *(short *)(SUB_000207fc + 0xa8) + -1;
    }
    else {
      iVar4 = 1;
    }
    if (param_1[6] != 1000000000) {
      if (iVar4 == 0) {
        iVar3 = *(int *)(iVar5 + 0xe);
        if (iVar3 < 0) {
          iVar3 = iVar3 + 1;
        }
        param_1[6] = iVar3 >> 1;
      }
      else {
        param_1[6] = *(int *)(iVar5 + 0xe) * iVar4;
      }
      if ((param_1[6] < 0) || (999999999 < param_1[6])) {
        param_1[6] = 999999999;
      }
      if (((*(byte *)((int)param_1 + 0x16) & 0x10) != 0) && (param_1[6] < param_1[8] + 0xce40)) {
        param_1[6] = param_1[8] + 0xce40;
      }
      if (((*(char *)(param_1 + 10) == '\x02') && (0x42b209 < param_1[6])) &&
         ((*(byte *)((int)param_1 + 0x16) & 0x10) == 0)) {
        param_1[6] = 0x42b209;
      }
    }
    if ((*(char *)(param_1 + 10) == '\x02') && (param_2 == 0)) {
      iVar5 = iVar5 + 0x22 + *(short *)(iVar5 + 0x1a) * 0xc + *(short *)(iVar5 + 0x1e) * 0xc;
      bVar1 = false;
      if ((*param_1 != 0) && (SUB_00020b28 == *param_1)) {
        bVar1 = true;
      }
      iVar3 = 0;
      if (0 < *(short *)(param_1 + 0xf)) {
        piVar6 = (int *)(*(int *)((int)param_1 + 0x4a) + 0x1c);
        do {
          if (bVar1) {
            *piVar6 = *(int *)(iVar5 + 10) + *(int *)(iVar5 + 10) * 4;
          }
          else if (iVar4 == 0) {
            iVar2 = *(int *)(iVar5 + 10);
            if (iVar2 < 0) {
              iVar2 = iVar2 + 1;
            }
            *piVar6 = iVar2 >> 1;
          }
          else {
            *piVar6 = *(int *)(iVar5 + 10) * iVar4;
          }
          iVar5 = iVar5 + 0xe + (uint)*(byte *)(iVar5 + 8) * 2;
          piVar6 = piVar6 + 8;
          iVar3 = iVar3 + 1;
        } while (iVar3 < *(short *)(param_1 + 0xf));
      }
    }
  }
  return;
}



// FUN_00005172 @ 0x5172

void FUN_00005172(int *param_1,undefined4 param_2)

{
  FUN_00004fee(param_1,param_2);
  if ((*param_1 != 0) && (**(short **)(*param_1 + 4) == 8)) {
    if (*(short *)(SUB_000207fc + 0xb0) == 0) {
      param_1[5] = param_1[5] | 0x40;
    }
    else {
      param_1[5] = param_1[5] & 0xffffffbf;
    }
  }
  return;
}



// FUN_000051b2 @ 0x51b2

void FUN_000051b2(void)

{
  int iVar1;
  undefined4 *puVar2;
  
  if (*(short *)(SUB_000207fc + 0xac) == 0) {
    FUN_00006c8a();
  }
  if ((0 < *(short *)(SUB_000207fc + 0xa8)) && (*(short *)(SUB_000207fc + 0xa8) < 6)) {
    iVar1 = 0;
    if (0 < DAT_00020f2c) {
      puVar2 = &DAT_00020b44;
      do {
        FUN_00005172(*puVar2,0);
        iVar1 = iVar1 + 1;
        puVar2 = puVar2 + 1;
      } while (iVar1 < DAT_00020f2c);
    }
    iVar1 = 0;
    if (0 < DAT_00020b24) {
      puVar2 = &DAT_00020804;
      do {
        FUN_00005172(*puVar2,0);
        iVar1 = iVar1 + 1;
        puVar2 = puVar2 + 1;
      } while (iVar1 < DAT_00020b24);
    }
    iVar1 = 0;
    if (0 < DAT_000219c4) {
      puVar2 = &DAT_000233bc;
      do {
        FUN_00005172(*puVar2,1);
        iVar1 = iVar1 + 1;
        puVar2 = puVar2 + 1;
      } while (iVar1 < DAT_000219c4);
    }
    iVar1 = 0;
    if (0 < DAT_000219c0) {
      puVar2 = &DAT_00022d2c;
      do {
        FUN_00005172(*puVar2,1);
        iVar1 = iVar1 + 1;
        puVar2 = puVar2 + 1;
      } while (iVar1 < DAT_000219c0);
    }
    FUN_00008014();
  }
  return;
}



// FUN_0000527c @ 0x527c

void FUN_0000527c(void)

{
  int iVar1;
  short sVar2;
  short sVar3;
  int *piVar4;
  int *piVar5;
  
  sVar3 = 0;
  piVar5 = &DAT_00021bd4;
  do {
    if (DAT_000219b4 <= sVar3) {
      return;
    }
    iVar1 = *piVar5;
    if ((**(short **)(iVar1 + 4) == 8) && (*(int *)(iVar1 + 0x32) != 0)) {
      piVar4 = piVar5 + 1;
      sVar2 = sVar3;
      while (sVar2 = sVar2 + 1, sVar2 < DAT_000219b4) {
        if (*piVar4 == **(int **)(iVar1 + 0x32)) {
          *piVar5 = *piVar4;
          *piVar4 = iVar1;
          break;
        }
        piVar4 = piVar4 + 1;
      }
    }
    sVar3 = sVar3 + 1;
    piVar5 = piVar5 + 1;
  } while( true );
}



// FUN_000052d6 @ 0x52d6

void FUN_000052d6(void)

{
  int *piVar1;
  undefined4 *puVar2;
  undefined4 *puVar3;
  undefined4 *puVar4;
  short sVar5;
  undefined4 auStack_194 [100];
  
  if (0 < DAT_00020f2c) {
    puVar2 = &DAT_00020b44;
    puVar3 = &DAT_00020b44;
    puVar4 = auStack_194;
    sVar5 = DAT_00020f2c._2_2_;
    while (sVar5 = sVar5 + -1, sVar5 != -1) {
      piVar1 = (int *)*puVar3;
      if (((*(char *)(piVar1 + 10) == '\x02') && (*piVar1 != 0)) &&
         (**(short **)(*piVar1 + 4) == 0xf)) {
        *puVar4 = piVar1;
        puVar3 = puVar3 + 1;
        puVar4 = puVar4 + 1;
      }
      else {
        *puVar2 = piVar1;
        puVar2 = puVar2 + 1;
        puVar3 = puVar3 + 1;
      }
    }
    sVar5 = (short)((int)puVar4 - (int)auStack_194 >> 2);
    puVar3 = auStack_194;
    while (sVar5 = sVar5 + -1, sVar5 != -1) {
      *puVar2 = *puVar3;
      puVar3 = puVar3 + 1;
      puVar2 = puVar2 + 1;
    }
  }
  return;
}



// FUN_0000534e @ 0x534e

int FUN_0000534e(char *param_1)

{
  short sVar1;
  int in_D1;
  
  if (*param_1 == '\x02') {
    in_D1 = FUN_000021a4(0x54);
    *(char *)(in_D1 + 0x28) = *param_1;
    *(uint *)(in_D1 + 0x14) = (uint)*(ushort *)(param_1 + 0x16);
    if ((*(byte *)(in_D1 + 0x16) & 0x20) != 0) {
      *(byte *)(in_D1 + 0x15) = *(byte *)(in_D1 + 0x15) | 0x10;
    }
    *(undefined4 *)(in_D1 + 4) = *(undefined4 *)(param_1 + 2);
    *(undefined4 *)(in_D1 + 8) = *(undefined4 *)(param_1 + 6);
    *(undefined4 *)(in_D1 + 0xc) = *(undefined4 *)(param_1 + 10);
    *(undefined4 *)(in_D1 + 0x18) = *(undefined4 *)(param_1 + 0xe);
    *(undefined4 *)(in_D1 + 0x20) = *(undefined4 *)(param_1 + 0x12);
    *(undefined2 *)(in_D1 + 0x36) = *(undefined2 *)(param_1 + 0x18);
    *(undefined2 *)(in_D1 + 0x38) = *(undefined2 *)(param_1 + 0x1a);
    sVar1 = *(short *)(param_1 + 0x1c);
    *(short *)(in_D1 + 0x3a) = sVar1;
    if (sVar1 == 0) {
      *(undefined2 *)(in_D1 + 0x3a) = 1;
    }
    *(undefined2 *)(in_D1 + 0x3c) = *(undefined2 *)(param_1 + 0x1e);
    *(undefined2 *)(in_D1 + 0x52) = *(undefined2 *)(param_1 + 0x20);
    *(undefined4 *)(in_D1 + 0x42) = DAT_00021020;
  }
  else if (*param_1 == '\x03') {
    in_D1 = FUN_000021a4(0x36);
    *(char *)(in_D1 + 0x28) = *param_1;
    *(undefined4 *)(in_D1 + 4) = *(undefined4 *)(param_1 + 2);
    *(undefined4 *)(in_D1 + 8) = *(undefined4 *)(param_1 + 6);
    *(undefined4 *)(in_D1 + 0xc) = *(undefined4 *)(param_1 + 10);
    *(undefined4 *)(in_D1 + 0x18) = *(undefined4 *)(param_1 + 0xe);
    *(undefined4 *)(in_D1 + 0x20) = *(undefined4 *)(param_1 + 0x12);
    *(undefined2 *)(in_D1 + 0x2e) = *(undefined2 *)(param_1 + 0x16);
    *(char **)(in_D1 + 0x2a) = param_1;
    *(undefined2 *)(in_D1 + 0x30) = *(undefined2 *)(param_1 + 0x18);
  }
  return in_D1;
}



// FUN_00005428 @ 0x5428

undefined4 * FUN_00005428(int param_1)

{
  undefined4 *puVar1;
  undefined4 *puVar2;
  
  puVar1 = (undefined4 *)FUN_0000534e(param_1);
  if (*(char *)(param_1 + 1) != '\0') {
    puVar2 = (undefined4 *)
             (**(code **)(*(int *)(&DAT_0002074c + *(char *)(param_1 + 1) * 4) + 6))
                       (puVar1,(int)*(char *)(param_1 + 1),0);
    *puVar1 = puVar2;
    if (puVar2 != (undefined4 *)0x0) {
      *puVar2 = 0;
    }
  }
  return puVar1;
}



// FUN_00005470 @ 0x5470

void FUN_00005470(int param_1,int param_2,int param_3)

{
  int *piVar1;
  undefined4 *puVar2;
  int iVar3;
  undefined2 local_14;
  undefined2 local_12;
  int local_10;
  int local_c;
  int local_8;
  
  iVar3 = *(int *)(SUB_000207fc + 0x8c) + *(int *)(*(int *)(param_1 + 0x2a) + param_2 * 0x18 + 0x2e)
  ;
  piVar1 = (int *)FUN_0000534e(iVar3);
  *(int **)(*(int *)(param_1 + 0x32) + 0x14 + param_3 * 0x18) = piVar1;
  piVar1[9] = param_1;
  *(short *)(piVar1 + 4) = (short)param_2;
  *(short *)((int)piVar1 + 0x12) = (short)param_3;
  if (param_3 == param_2) {
    *(char *)((int)piVar1 + 0x29) = *(char *)((int)piVar1 + 0x29) + '\x01';
  }
  if (param_2 < *(short *)(param_1 + 0x2e)) {
    piVar1[5] = piVar1[5] | 1;
  }
  FUN_00003524(piVar1,1,0,0);
  if (*(char *)(iVar3 + 1) != '\0') {
    puVar2 = (undefined4 *)
             (**(code **)(*(int *)(&DAT_0002074c + *(char *)(iVar3 + 1) * 4) + 6))
                       (piVar1,(int)*(char *)(iVar3 + 1),
                        (int)*(short *)(*(int *)(param_1 + 0x32) + param_3 * 0x18));
    *piVar1 = (int)puVar2;
    if (puVar2 != (undefined4 *)0x0) {
      *puVar2 = 0;
      local_10 = 0;
      local_c = 0;
      local_8 = 0x1000 << (*(ushort *)((int)piVar1 + 0x36) & 0x3f);
      FUN_00003524(piVar1,1,&local_10,&local_10);
      local_10 = (local_10 >> (*(ushort *)((int)piVar1 + 0x36) & 0x3f)) - piVar1[1];
      local_c = (local_c >> (*(ushort *)((int)piVar1 + 0x36) & 0x3f)) - piVar1[2];
      local_8 = (local_8 >> (*(ushort *)((int)piVar1 + 0x36) & 0x3f)) - piVar1[3];
      local_14 = 0;
      FUN_00002574(local_10,local_c,local_8,0x1000,0x1000000,&local_12,&local_14);
      *(int *)(*piVar1 + 0x14) = piVar1[1];
      *(int *)(*piVar1 + 0x18) = piVar1[2];
      *(int *)(*piVar1 + 0x1c) = piVar1[3];
      *(undefined2 *)(*piVar1 + 0x20) = local_12;
      *(undefined2 *)(*piVar1 + 0x22) = local_14;
    }
  }
  FUN_00004fee(piVar1,1);
  iVar3 = FUN_000047d6(piVar1);
  if (iVar3 - piVar1[6] < 0x19c80) {
    FUN_00005cb8(piVar1);
  }
  else {
    (&DAT_000233bc)[DAT_000219c4] = piVar1;
    DAT_000219c4 = DAT_000219c4 + 1;
  }
  return;
}



// FUN_0000563c @ 0x563c

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000563c(int param_1,undefined4 *param_2)

{
  int iVar1;
  undefined4 uVar2;
  int iVar3;
  int iVar4;
  undefined2 *puVar5;
  ushort *puVar6;
  
  iVar4 = *(short *)(param_1 + 0x3c) * 0x20;
  if ((*(byte *)(param_1 + 0x16) & 2) != 0) {
    iVar4 = *(short *)(param_1 + 0x3c) * 6 + *(short *)(param_1 + 0x3a) * 2 + iVar4;
  }
  iVar1 = FUN_000020a0(iVar4);
  *(int *)(param_1 + 0x4a) = iVar1;
  iVar4 = 0;
  if (0 < *(short *)(param_1 + 0x3c)) {
    puVar6 = (ushort *)(iVar1 + 0x14);
    do {
      *(undefined4 *)(iVar1 + 8) = 0;
      *(undefined4 *)(iVar1 + 0xc) = *param_2;
      *(undefined1 *)(iVar1 + 0x10) = *(undefined1 *)(param_2 + 1);
      *(undefined1 *)(iVar1 + 0x11) = *(undefined1 *)((int)param_2 + 5);
      *(undefined1 *)(iVar1 + 0x12) = *(undefined1 *)((int)param_2 + 6);
      *(undefined1 *)(iVar1 + 0x13) = *(undefined1 *)((int)param_2 + 7);
      *puVar6 = (ushort)*(byte *)(param_2 + 2);
      *(byte *)(iVar1 + 0x16) = *(byte *)((int)param_2 + 9) & 0xf0;
      *(byte *)(iVar1 + 0x17) = *(byte *)((int)param_2 + 9) & 0xf;
      *(undefined4 *)(iVar1 + 0x1c) = *(undefined4 *)((int)param_2 + 10);
      uVar2 = FUN_000020a0((int)(short)*puVar6 << 1);
      *(undefined4 *)(iVar1 + 0x18) = uVar2;
      iVar3 = 0;
      if (0 < (short)*puVar6) {
        puVar5 = (undefined2 *)((int)param_2 + 0xe);
        do {
          *(undefined2 *)(*(int *)(iVar1 + 0x18) + iVar3 * 2) = *puVar5;
          iVar3 = iVar3 + 1;
          puVar5 = puVar5 + 1;
        } while (iVar3 < (short)*puVar6);
      }
      param_2 = (undefined4 *)((undefined2 *)((int)param_2 + 0xe) + (short)*puVar6);
      puVar6 = puVar6 + 0x10;
      iVar1 = iVar1 + 0x20;
      iVar4 = iVar4 + 1;
    } while (iVar4 < *(short *)(param_1 + 0x3c));
  }
  if ((*(byte *)(param_1 + 0x16) & 2) != 0) {
    puVar5 = (undefined2 *)(*(int *)(param_1 + 0x4a) + *(short *)(param_1 + 0x3c) * 0x20);
    for (iVar4 = 0; iVar4 < *(short *)(param_1 + 0x3a); iVar4 = iVar4 + 1) {
      *puVar5 = (short)iVar4;
      puVar5 = puVar5 + 1;
    }
  }
  _DAT_00023fd2 = 1;
  return;
}



// FUN_00005758 @ 0x5758

void FUN_00005758(int param_1,int param_2)

{
  int iVar1;
  short sVar2;
  int iVar3;
  
  iVar1 = SUB_000207fc;
  iVar3 = *(int *)(param_1 + 0x4a);
  sVar2 = 0;
  if (0 < *(short *)(param_1 + 0x3c)) {
    do {
      *(int *)(iVar3 + 4) = (int)*(short *)(*(int *)(iVar1 + 0x88) + param_2 * 2);
      *(undefined1 *)(iVar3 + 0xf) = 0xff;
      iVar3 = iVar3 + 0x20;
      sVar2 = sVar2 + 1;
    } while (sVar2 < *(short *)(param_1 + 0x3c));
  }
  return;
}



// FUN_0000579a @ 0x579a

void FUN_0000579a(int param_1,undefined4 param_2,short param_3,short param_4)

{
  undefined2 uVar1;
  undefined2 uVar2;
  short sVar4;
  short sVar5;
  short sVar6;
  undefined4 uVar3;
  uint uVar7;
  short sVar8;
  short sVar9;
  short sVar10;
  int iVar11;
  int iVar12;
  int iVar13;
  int *piVar14;
  int *piVar15;
  int iVar16;
  int aiStackY_61cd4 [834];
  int aiStackY_60fcc [33498];
  int aiStackY_40464 [63940];
  int local_1cec;
  int local_1ce8;
  int local_1ce0;
  int local_1cd4 [384];
  int aiStack_16d4 [450];
  int local_fcc [450];
  undefined1 auStack_8c4 [1120];
  int local_464 [280];
  
  iVar16 = *(int *)(SUB_000207fc + 0x8c) +
           *(int *)(*(int *)(*(int *)(param_1 + 0x24) + 0x2a) + 0x2e +
                   (uint)*(ushort *)(param_1 + 0x10) * 0x18);
  sVar8 = 0;
  if (0 < *(short *)(param_1 + 0x3c)) {
    do {
      iVar13 = (int)sVar8;
      piVar15 = local_1cd4 + iVar13 * 3;
      piVar14 = (int *)((iVar13 + *(short *)(param_1 + 0x38)) * 0xc + *(int *)(param_1 + 0x46));
      *piVar15 = *piVar14;
      local_1cd4[iVar13 * 3 + 1] = piVar14[1];
      local_1cd4[iVar13 * 3 + 2] = piVar14[2];
      *piVar15 = *piVar15 - **(int **)(param_1 + 0x46);
      local_1cd4[iVar13 * 3 + 1] =
           local_1cd4[iVar13 * 3 + 1] - *(int *)(*(int *)(param_1 + 0x46) + 4);
      local_1cd4[iVar13 * 3 + 2] =
           local_1cd4[iVar13 * 3 + 2] - *(int *)(*(int *)(param_1 + 0x46) + 8);
      sVar8 = sVar8 + 1;
    } while (sVar8 < *(short *)(param_1 + 0x3c));
  }
  local_1ce0 = iVar16 + 0x22 + *(short *)(iVar16 + 0x1a) * 0xc + *(short *)(iVar16 + 0x1e) * 0xc;
  FUN_00003544(param_1,(int)*(short *)(iVar16 + 0x1a),iVar16 + 0x22,aiStack_16d4);
  sVar10 = 0;
  sVar8 = *(short *)(SUB_000207fc + 0xa8);
  sVar9 = 0;
  if (0 < *(short *)(param_1 + 0x3c)) {
    uVar7 = (uint)param_3;
    do {
      piVar15 = local_464;
      iVar16 = 0;
      for (sVar5 = 0; uVar2 = DAT_0002701a, sVar5 < (short)(ushort)*(byte *)(local_1ce0 + 8);
          sVar5 = sVar5 + 1) {
        iVar11 = (int)*(short *)(local_1ce0 + 0xe + sVar5 * 2);
        iVar13 = aiStack_16d4[iVar11 * 3];
        iVar11 = aiStack_16d4[iVar11 * 3 + 2];
        if (sVar5 == 0) {
LAB_00005930:
          iVar16 = 0;
          *piVar15 = iVar13 - DAT_00023c54 >> (uVar7 & 0x3f);
          piVar15[1] = iVar11 - DAT_00023c4c >> (uVar7 & 0x3f);
          piVar15 = piVar15 + 2;
          local_1cec = iVar11;
          local_1ce8 = iVar13;
        }
        else {
          iVar12 = FUN_000048a6(local_1ce8,0,local_1cec,iVar13,0,iVar11);
          iVar16 = iVar12 + iVar16;
          iVar12 = FUN_000048a6(DAT_00023d68,0,DAT_00023d60,iVar13,0,iVar11);
          if (iVar12 / (int)(short)(sVar8 + 3) <= iVar16) goto LAB_00005930;
        }
      }
      sVar5 = (short)((int)piVar15 - (int)local_464 >> 3);
      if (sVar5 < 3) {
        sVar5 = 0;
        DAT_0002701a = uVar2;
      }
      else {
        DAT_0002701a = -param_4;
        sVar4 = FUN_0000c478(local_464,(int)sVar5,auStack_8c4);
        uVar1 = DAT_00027018;
        sVar5 = 0;
        DAT_00027018 = uVar1;
        DAT_0002701a = uVar2;
        if (sVar4 != 0) {
          DAT_00027018 = param_4;
          sVar4 = FUN_0000c52a(auStack_8c4,(int)sVar4,local_464);
          uVar2 = DAT_00027016;
          sVar5 = 0;
          DAT_00027016 = uVar2;
          DAT_00027018 = uVar1;
          if (sVar4 != 0) {
            DAT_00027016 = -param_4;
            sVar4 = FUN_0000c5dc(local_464,(int)sVar4,auStack_8c4);
            uVar1 = DAT_00020b2e;
            sVar5 = 0;
            DAT_00020b2e = uVar1;
            DAT_00027016 = uVar2;
            if (sVar4 != 0) {
              DAT_00020b2e = param_4;
              sVar5 = FUN_0000c692(auStack_8c4,(int)sVar4,local_464);
              DAT_00020b2e = uVar1;
            }
          }
        }
      }
      iVar16 = *(int *)(param_1 + 0x4a) + sVar9 * 0x20;
      if (sVar5 == 0) {
        *(byte *)(iVar16 + 0x13) = *(byte *)(iVar16 + 0x13) | 2;
      }
      else {
        *(short *)(iVar16 + 0x14) = sVar5;
        *(byte *)(iVar16 + 0x13) = *(byte *)(iVar16 + 0x13) & 0xfd;
        uVar3 = FUN_000021e0(*(undefined4 *)(iVar16 + 0x18),(int)sVar5 << 1);
        *(undefined4 *)(iVar16 + 0x18) = uVar3;
        sVar4 = 0;
        if (0 < sVar5) {
          do {
            iVar13 = DAT_00023c54 + (local_464[sVar4 * 2] << (uVar7 & 0x3f));
            iVar11 = DAT_00023c4c + (local_464[sVar4 * 2 + 1] << (uVar7 & 0x3f));
            piVar15 = local_fcc;
            sVar6 = sVar10 + -1;
            if (sVar6 != -1) {
              do {
                if ((iVar13 == *piVar15) && (iVar11 == piVar15[2])) {
                  *(short *)(*(int *)(iVar16 + 0x18) + sVar4 * 2) = (sVar10 - sVar6) + -1;
                  goto LAB_00005b02;
                }
                piVar15 = piVar15 + 3;
                sVar6 = sVar6 + -1;
              } while (sVar6 != -1);
            }
            iVar12 = (int)sVar10;
            local_fcc[iVar12 * 3] = iVar13;
            local_fcc[iVar12 * 3 + 1] = 0;
            local_fcc[iVar12 * 3 + 2] = iVar11;
            *(short *)(*(int *)(iVar16 + 0x18) + sVar4 * 2) = sVar10;
            sVar10 = sVar10 + 1;
LAB_00005b02:
            sVar4 = sVar4 + 1;
          } while (sVar4 < sVar5);
        }
      }
      local_1ce0 = local_1ce0 + 0xe + (uint)*(byte *)(local_1ce0 + 8) * 2;
      sVar9 = sVar9 + 1;
    } while (sVar9 < *(short *)(param_1 + 0x3c));
  }
  if (sVar10 == 0) {
    *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x40;
  }
  else {
    *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) & 0xffffffbf;
    *(short *)(param_1 + 0x38) = sVar10;
    iVar16 = (short)(*(short *)(param_1 + 0x3c) + sVar10) * 0xc;
    uVar3 = (*(code *)&LAB_000021be_2)(*(undefined4 *)(param_1 + 0x4e),iVar16);
    *(undefined4 *)(param_1 + 0x4e) = uVar3;
    uVar3 = (*(code *)&LAB_000021be_2)(*(undefined4 *)(param_1 + 0x46),iVar16);
    *(undefined4 *)(param_1 + 0x46) = uVar3;
    sVar8 = 0;
    if (0 < sVar10) {
      do {
        iVar16 = (int)sVar8;
        piVar15 = (int *)(iVar16 * 0xc + *(int *)(param_1 + 0x46));
        *piVar15 = local_fcc[iVar16 * 3];
        piVar15[1] = local_fcc[iVar16 * 3 + 1];
        piVar15[2] = local_fcc[iVar16 * 3 + 2];
        sVar8 = sVar8 + 1;
      } while (sVar8 < sVar10);
    }
    sVar8 = 0;
    if (0 < *(short *)(param_1 + 0x3c)) {
      do {
        iVar16 = (int)sVar8;
        piVar15 = local_1cd4 + iVar16 * 3;
        *piVar15 = **(int **)(param_1 + 0x46) + *piVar15;
        local_1cd4[iVar16 * 3 + 1] =
             *(int *)(*(int *)(param_1 + 0x46) + 4) + local_1cd4[iVar16 * 3 + 1];
        local_1cd4[iVar16 * 3 + 2] =
             *(int *)(*(int *)(param_1 + 0x46) + 8) + local_1cd4[iVar16 * 3 + 2];
        piVar14 = (int *)((iVar16 + *(short *)(param_1 + 0x38)) * 0xc + *(int *)(param_1 + 0x46));
        *piVar14 = *piVar15;
        piVar14[1] = local_1cd4[iVar16 * 3 + 1];
        piVar14[2] = local_1cd4[iVar16 * 3 + 2];
        sVar8 = sVar8 + 1;
      } while (sVar8 < *(short *)(param_1 + 0x3c));
    }
  }
  *(int *)(param_1 + 4) = DAT_00023c54;
  *(int *)(param_1 + 0xc) = DAT_00023c4c;
  *(undefined4 *)(param_1 + 0x20) = param_2;
  FUN_000047d6(param_1);
  sVar8 = 0;
  for (uVar7 = *(int *)(param_1 + 0x20) + *(int *)(param_1 + 0x1c) + (*(int *)(param_1 + 0x1c) >> 2)
               << (*(ushort *)(param_1 + 0x36) & 0x3f); (uVar7 & 0xffff8000) != 0;
      uVar7 = (int)uVar7 >> 1) {
    sVar8 = sVar8 + 1;
  }
  *(short *)(param_1 + 0x34) = sVar8;
  return;
}



// FUN_00005c50 @ 0x5c50

void FUN_00005c50(int param_1)

{
  int iVar1;
  
  if ((*(byte *)(param_1 + 0x17) & 2) == 0) {
    iVar1 = *(int *)(SUB_000207fc + 0x8c) +
            *(int *)(*(int *)(*(int *)(param_1 + 0x24) + 0x2a) + 0x2e +
                    (uint)*(ushort *)(param_1 + 0x10) * 0x18);
    *(undefined4 *)(param_1 + 4) = *(undefined4 *)(iVar1 + 2);
    *(undefined4 *)(param_1 + 8) = *(undefined4 *)(iVar1 + 6);
    *(undefined4 *)(param_1 + 0xc) = *(undefined4 *)(iVar1 + 10);
    *(undefined4 *)(param_1 + 0x20) = *(undefined4 *)(iVar1 + 0x12);
    FUN_00003544(param_1,1,0,0);
  }
  return;
}



// FUN_00005c98 @ 0x5c98

void FUN_00005c98(void)

{
  int in_A0;
  int unaff_A3;
  
  *(undefined4 *)(unaff_A3 + 0x20) = *(undefined4 *)(in_A0 + 0x12);
  FUN_00003544();
  return;
}



// FUN_00005cb8 @ 0x5cb8

void FUN_00005cb8(int *param_1)

{
  int *piVar1;
  char cVar2;
  undefined4 uVar3;
  int iVar4;
  uint uVar5;
  short sVar7;
  int iVar6;
  undefined4 *puVar8;
  undefined4 *puVar9;
  int iVar10;
  
  cVar2 = *(char *)(param_1 + 10);
  if (cVar2 != '\x02') {
    if (('\x02' < cVar2) && (cVar2 == '\x03')) {
      (&DAT_00020b44)[DAT_00020f2c] = param_1;
      DAT_00020f2c = DAT_00020f2c + 1;
      puVar9 = (undefined4 *)(*(int *)((int)param_1 + 0x2a) + 0x1a);
      uVar3 = FUN_000020a0(*(short *)(param_1 + 0xc) * 0x18);
      *(undefined4 *)((int)param_1 + 0x32) = uVar3;
      iVar6 = 0;
      if (0 < *(short *)(param_1 + 0xc)) {
        iVar4 = 0;
        do {
          puVar8 = (undefined4 *)(iVar4 + *(int *)((int)param_1 + 0x32));
          *puVar8 = *puVar9;
          puVar8[1] = puVar9[1];
          puVar8[2] = puVar9[2];
          puVar8[3] = puVar9[3];
          puVar8[4] = puVar9[4];
          puVar8[5] = puVar9[5];
          puVar9 = puVar9 + 6;
          piVar1 = (int *)(*(int *)((int)param_1 + 0x32) + 0x14 + iVar4);
          *piVar1 = *(int *)(SUB_000207fc + 0x8c) + *piVar1;
          iVar4 = iVar4 + 0x18;
          iVar6 = iVar6 + 1;
        } while (iVar6 < *(short *)(param_1 + 0xc));
      }
      for (iVar6 = 0; iVar6 < *(short *)(param_1 + 0xc); iVar6 = iVar6 + 1) {
        FUN_00005470(param_1,iVar6,iVar6);
      }
    }
    goto LAB_00005f76;
  }
  if ((*(byte *)((int)param_1 + 0x17) & 1) == 0) {
    (&DAT_00020804)[DAT_00020b24] = param_1;
    DAT_00020b24 = DAT_00020b24 + 1;
  }
  else {
    (&DAT_00020b44)[DAT_00020f2c] = param_1;
    DAT_00020f2c = DAT_00020f2c + 1;
  }
  sVar7 = 0;
  for (uVar5 = param_1[8] + param_1[7] + (param_1[7] >> 2) <<
               (*(ushort *)((int)param_1 + 0x36) & 0x3f); (uVar5 & 0xffff8000) != 0;
      uVar5 = (int)uVar5 >> 1) {
    sVar7 = sVar7 + 1;
  }
  *(short *)(param_1 + 0xd) = sVar7;
  sVar7 = *(short *)(param_1 + 0xf) + *(short *)(param_1 + 0xe);
  iVar4 = sVar7 * 0xc;
  uVar3 = FUN_00002080(iVar4);
  *(undefined4 *)((int)param_1 + 0x4e) = uVar3;
  uVar3 = FUN_00002080(iVar4);
  *(undefined4 *)((int)param_1 + 0x46) = uVar3;
  iVar6 = *(int *)(SUB_000207fc + 0x8c) +
          *(int *)(*(int *)(param_1[9] + 0x2a) + 0x2e + (uint)*(ushort *)(param_1 + 4) * 0x18) +
          0x22;
  FUN_0000563c(param_1,iVar6 + iVar4);
  if (*param_1 != 0) {
    *(int **)*param_1 = param_1;
    (&DAT_00021bd4)[DAT_000219b4] = *param_1;
    DAT_000219b4 = DAT_000219b4 + 1;
  }
  if ((*(byte *)((int)param_1 + 0x16) & 0x10) == 0) {
    if ((*(byte *)((int)param_1 + 0x17) & 0x10) == 0) {
      if ((*(byte *)((int)param_1 + 0x15) & 0x10) == 0) {
        FUN_00003544(param_1,(int)sVar7,iVar6,*(undefined4 *)((int)param_1 + 0x46));
        FUN_00004fee(param_1,0);
        FUN_000049ec(param_1,param_1[7]);
        goto LAB_00005ebe;
      }
      FUN_00002294((int)sVar7,iVar6,*(undefined4 *)((int)param_1 + 0x46),0,0,0);
      FUN_00004fee(param_1,0);
      FUN_000049ec(param_1,param_1[7]);
      FUN_00004b0c(param_1);
    }
    else {
      FUN_00004fee(param_1,0);
      FUN_000049ec(param_1,param_1[7]);
      iVar6 = *param_1;
      if (((iVar6 == 0) || (**(short **)(iVar6 + 4) < 0x1b)) || (0x22 < **(short **)(iVar6 + 4))) {
        iVar4 = 0;
        iVar10 = 0;
        iVar6 = 0;
      }
      else {
        iVar4 = (int)*(short *)(iVar6 + 0x65c);
        iVar10 = (int)*(short *)(iVar6 + 0x65a);
        iVar6 = (int)*(short *)(iVar6 + 0x658);
      }
      FUN_00019b52(param_1,iVar6,iVar10,iVar4);
    }
  }
  else {
    FUN_0000579a(param_1,0xb4e1,0,0x7fe4);
    if (*(short *)(SUB_000207fc + 0xe) == 1) {
      FUN_00005758(param_1,0x80);
    }
    else {
LAB_00005ebe:
      FUN_00004b0c(param_1);
    }
  }
  if ((*(byte *)((int)param_1 + 0x16) & 2) == 0) {
    *(ushort *)((int)param_1 + 0x16) = *(ushort *)((int)param_1 + 0x16) | 0x800;
  }
LAB_00005f76:
  FUN_000047d6(param_1);
  return;
}



// FUN_00005f84 @ 0x5f84

int FUN_00005f84(int param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                undefined2 param_5,undefined2 param_6,undefined2 param_7,undefined2 param_8)

{
  int iVar1;
  int *piVar2;
  undefined2 *puVar3;
  int iVar4;
  int iVar5;
  
  iVar1 = *(int *)(DAT_00023c2c + 0x32);
  iVar5 = (int)*(short *)(DAT_00023c2c + 0x30);
  iVar4 = 0;
  if (0 < iVar5) {
    piVar2 = (int *)(iVar1 + 0x14);
    do {
      if (*piVar2 == 0) break;
      piVar2 = piVar2 + 6;
      iVar4 = iVar4 + 1;
    } while (iVar4 < iVar5);
  }
  if (iVar5 == iVar4) {
    iVar1 = FUN_000021e0(iVar1,(iVar5 + 10) * 0x18);
    iVar5 = DAT_00023c2c;
    *(int *)(DAT_00023c2c + 0x32) = iVar1;
    *(short *)(iVar5 + 0x30) = *(short *)(iVar5 + 0x30) + 10;
  }
  puVar3 = (undefined2 *)(iVar4 * 0x18 + iVar1);
  *puVar3 = param_8;
  *(undefined4 *)(puVar3 + 4) = param_2;
  *(undefined4 *)(puVar3 + 6) = param_3;
  *(undefined4 *)(puVar3 + 8) = param_4;
  puVar3[1] = param_5;
  puVar3[2] = param_6;
  puVar3[3] = param_7;
  FUN_00005470(DAT_00023c2c,*(undefined2 *)(param_1 + 0x10),iVar4);
  iVar1 = *(int *)(*(int *)(DAT_00023c2c + 0x32) + iVar4 * 0x18 + 0x14);
  if (*(int *)(iVar1 + 0x46) == 0) {
    FUN_0000605a(iVar1);
    iVar1 = 0;
  }
  return iVar1;
}



// FUN_0000605a @ 0x605a

void FUN_0000605a(int *param_1)

{
  int iVar1;
  short sVar2;
  
  if (*param_1 == DAT_00021068) {
    DAT_00021068 = 0;
  }
  if (*(short *)((int)param_1 + 0x12) != *(short *)(param_1 + 4)) {
    iVar1 = (uint)*(ushort *)((int)param_1 + 0x12) * 0x18;
    if (param_1 == *(int **)(*(int *)(DAT_00023c2c + 0x32) + 0x14 + iVar1)) {
      *(undefined4 *)(*(int *)(DAT_00023c2c + 0x32) + 0x14 + iVar1) = 0;
    }
    if (((int)DAT_0002107a <= (int)(uint)*(ushort *)(param_1 + 4)) &&
       ((int)(uint)*(ushort *)(param_1 + 4) <= (int)DAT_00021078)) {
      sVar2 = 0;
      do {
        if (param_1 == (int *)(&DAT_0002144c)[sVar2]) {
          (&DAT_0002144c)[sVar2] = 0;
        }
        sVar2 = sVar2 + 1;
      } while (sVar2 < 7);
    }
  }
  param_1[1] = 1000000000;
  param_1[5] = param_1[5] | 2;
  *(undefined4 *)((int)param_1 + 0x3e) = 0;
  if (*param_1 != 0) {
    *(undefined4 *)*param_1 = 0;
  }
  DAT_0002b22c = DAT_0002b22c + 1;
  return;
}



// FUN_000060fc @ 0x60fc

void FUN_000060fc(int param_1)

{
  if ((*(byte *)(param_1 + 0x17) & 2) == 0) {
    if ((((*(int *)(param_1 + 0x24) == DAT_00023c2c) &&
         ((int)DAT_0002107a <= (int)(uint)*(ushort *)(param_1 + 0x10))) &&
        ((int)(uint)*(ushort *)(param_1 + 0x10) <= (int)DAT_00021078)) ||
       (*(char *)(param_1 + 0x28) != '\x02')) {
      FUN_0000605a(param_1);
    }
    else {
      *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x42;
      *(undefined4 *)(param_1 + 0x3e) = DAT_00021020;
    }
  }
  return;
}



// FUN_00006150 @ 0x6150

void FUN_00006150(int *param_1)

{
  int *piVar1;
  char cVar2;
  int iVar3;
  int iVar4;
  
  cVar2 = *(char *)(param_1 + 10);
  if (cVar2 == '\x02') {
    if ((*(byte *)((int)param_1 + 0x16) & 0x10) != 0) {
      FUN_00005c50(param_1);
    }
    FUN_00001fe8(*(undefined4 *)((int)param_1 + 0x4e));
    FUN_00001fe8(*(undefined4 *)((int)param_1 + 0x46));
    *(undefined4 *)((int)param_1 + 0x46) = 0;
    iVar3 = 0;
    if (0 < *(short *)(param_1 + 0xf)) {
      iVar4 = 0;
      do {
        FUN_00002008(*(undefined4 *)(*(int *)((int)param_1 + 0x4a) + 0x18 + iVar4));
        iVar4 = iVar4 + 0x20;
        iVar3 = iVar3 + 1;
      } while (iVar3 < *(short *)(param_1 + 0xf));
    }
    FUN_00002008(*(undefined4 *)((int)param_1 + 0x4a));
    if (*param_1 != 0) {
      *(undefined4 *)*param_1 = 0;
    }
    if (*(short *)((int)param_1 + 0x12) != *(short *)(param_1 + 4)) {
      FUN_000060fc(param_1);
    }
    if (*(int *)((int)param_1 + 0x2e) != 0) {
      FUN_0001748c(param_1);
    }
    param_1[5] = param_1[5] & 0xfff7ffff;
  }
  else if (('\x02' < cVar2) && (cVar2 == '\x03')) {
    iVar3 = 0;
    if (0 < *(short *)(param_1 + 0xc)) {
      iVar4 = 0;
      do {
        piVar1 = *(int **)(*(int *)((int)param_1 + 0x32) + 0x14 + iVar4);
        if ((*(char *)(piVar1 + 10) == '\x02') && (*(int *)((int)piVar1 + 0x2e) != 0)) {
          FUN_0001748c(piVar1);
        }
        piVar1[5] = piVar1[5] | 2;
        *(char *)((int)piVar1 + 0x29) = *(char *)((int)piVar1 + 0x29) + -1;
        if (*piVar1 != 0) {
          *(undefined4 *)*piVar1 = 0;
        }
        iVar4 = iVar4 + 0x18;
        iVar3 = iVar3 + 1;
      } while (iVar3 < *(short *)(param_1 + 0xc));
    }
    FUN_00002008(*(undefined4 *)((int)param_1 + 0x32));
    *(undefined4 *)((int)param_1 + 0x32) = 0;
    if (*param_1 != 0) {
      *(undefined4 *)*param_1 = 0;
    }
  }
  return;
}



// FUN_00006270 @ 0x6270

void FUN_00006270(int *param_1,int *param_2)

{
  int iVar1;
  short sVar2;
  int *piVar3;
  int *piVar4;
  uint uVar5;
  short sVar6;
  int *piVar7;
  
  piVar7 = &DAT_00021f44 + DAT_000219b8 * 3;
  sVar2 = (short)*param_2;
  piVar3 = param_1;
  piVar4 = param_1;
  do {
    while( true ) {
      sVar2 = sVar2 + -1;
      if (sVar2 == -1) {
        DAT_000219b8 = (int)(piVar7 + -0x87d1) / 0xc;
        *param_2 = (int)piVar4 - (int)param_1 >> 2;
        return;
      }
      iVar1 = *piVar3;
      if ((*(int *)(iVar1 + 0x18) < *(int *)(iVar1 + 0x1c)) || ((*(byte *)(iVar1 + 0x17) & 2) != 0))
      break;
      if ((*(char *)(iVar1 + 0x28) == '\x02') && ((*(uint *)(iVar1 + 0x14) & 0x40) == 0)) {
        sVar6 = 0;
        for (uVar5 = *(int *)(iVar1 + 0x20) + *(int *)(iVar1 + 0x1c) + (*(int *)(iVar1 + 0x1c) >> 2)
                     << (*(ushort *)(iVar1 + 0x36) & 0x3f); (uVar5 & 0xffff8000) != 0;
            uVar5 = (int)uVar5 >> 1) {
          sVar6 = sVar6 + 1;
        }
        *(short *)(iVar1 + 0x34) = sVar6;
        if ((*(uint *)(iVar1 + 0x14) & 4) == 0) {
          uVar5 = (uint)sVar6;
          *piVar7 = *(int *)(iVar1 + 4) - DAT_00023d68 >> (uVar5 & 0x3f);
          piVar7[1] = *(int *)(iVar1 + 8) - DAT_00023d64 >> (uVar5 & 0x3f);
          piVar7[2] = *(int *)(iVar1 + 0xc) - DAT_00023d60 >> (uVar5 & 0x3f);
          piVar7 = piVar7 + 3;
        }
        (&DAT_00022994)[DAT_000219bc] = iVar1;
        DAT_000219bc = DAT_000219bc + 1;
      }
LAB_000063b2:
      *piVar4 = iVar1;
      piVar3 = piVar3 + 1;
      piVar4 = piVar4 + 1;
    }
    if ((*(int *)(iVar1 + 0x1c) - *(int *)(iVar1 + 0x18) < 0x19c80) &&
       ((*(byte *)(iVar1 + 0x17) & 2) == 0)) goto LAB_000063b2;
    if (*(char *)(iVar1 + 0x28) == '\x02') {
      if (DAT_00021020 - *(int *)(iVar1 + 0x3e) < 0x4c) goto LAB_000063b2;
      if (*(int *)(iVar1 + 0x3e) != 0) {
        FUN_0000605a(iVar1);
      }
    }
    FUN_00006150(iVar1);
    (&DAT_000233bc)[DAT_000219c4] = iVar1;
    DAT_000219c4 = DAT_000219c4 + 1;
    piVar3 = piVar3 + 1;
  } while( true );
}



// FUN_000063da @ 0x63da

void FUN_000063da(int *param_1,int *param_2)

{
  int iVar1;
  int iVar2;
  uint uVar3;
  int iVar4;
  short sVar5;
  int *piVar6;
  int *piVar7;
  uint uVar8;
  short sVar9;
  int iVar10;
  int *piVar11;
  int *piVar12;
  short local_37e;
  int *local_37c;
  int local_374 [220];
  
  piVar12 = &DAT_00021f44 + DAT_000219b8 * 3;
  iVar10 = 0x7fffffff;
  local_37c = local_374;
  local_37e = 0;
  sVar5 = (short)*param_2;
  piVar6 = param_1;
  piVar7 = param_1;
  do {
    while( true ) {
      sVar5 = sVar5 + -1;
      if (sVar5 == -1) {
        DAT_000219b8 = (int)(piVar12 + -0x87d1) / 0xc;
        *param_2 = (int)piVar7 - (int)param_1 >> 2;
        return;
      }
      iVar1 = *piVar6;
      uVar3 = *(uint *)(iVar1 + 0x14);
      if ((*(int *)(iVar1 + 0x18) < *(int *)(iVar1 + 0x1c)) || ((uVar3 & 2) != 0)) break;
      if ((uVar3 & 0x40) == 0) {
        sVar9 = 0;
        for (uVar8 = *(int *)(iVar1 + 0x20) + *(int *)(iVar1 + 0x1c) + (*(int *)(iVar1 + 0x1c) >> 2)
                     << (*(ushort *)(iVar1 + 0x36) & 0x3f); (uVar8 & 0xffff8000) != 0;
            uVar8 = (int)uVar8 >> 1) {
          sVar9 = sVar9 + 1;
        }
        *(short *)(iVar1 + 0x34) = sVar9;
        if ((uVar3 & 4) == 0) {
          uVar8 = (uint)sVar9;
          *piVar12 = *(int *)(iVar1 + 4) - DAT_00023d68 >> (uVar8 & 0x3f);
          piVar12[1] = *(int *)(iVar1 + 8) - DAT_00023d64 >> (uVar8 & 0x3f);
          piVar12[2] = *(int *)(iVar1 + 0xc) - DAT_00023d60 >> (uVar8 & 0x3f);
          piVar12 = piVar12 + 3;
        }
        (&DAT_00022994)[DAT_000219bc] = iVar1;
        DAT_000219bc = DAT_000219bc + 1;
      }
LAB_00006530:
      if ((short)uVar3 < 0) {
        *(int *)(&DAT_000219cc + DAT_000219ac * 4) = iVar1;
        DAT_000219ac = DAT_000219ac + 1;
      }
      if ((uVar3 & 0x2800) == 0) {
        iVar4 = *(int *)(iVar1 + 0x20) + *(int *)(iVar1 + 0x1c);
        piVar11 = local_37c;
        sVar9 = local_37e;
        if (iVar10 < iVar4) {
          do {
            while( true ) {
              sVar9 = sVar9 + -1;
              if (sVar9 == -1) goto LAB_00006682;
              piVar11 = piVar11 + -1;
              iVar2 = *piVar11;
              if (iVar4 <= *(int *)(iVar2 + 0x1c) - *(int *)(iVar2 + 0x20)) break;
              if ((uVar3 & 0x100000) == 0) {
                if ((*(byte *)(iVar2 + 0x17) & 0x40) == 0) {
                  FUN_00016832(iVar2,iVar1);
                }
                else {
                  FUN_00016c02(iVar2,iVar1);
                }
              }
              else if ((*(byte *)(iVar2 + 0x17) & 0x40) == 0) {
                FUN_000167a4(iVar2,iVar1);
              }
              else {
                FUN_00016804(iVar2,iVar1);
              }
            }
          } while (*(int *)(iVar2 + 0x2a) <= iVar4);
        }
      }
      else {
        *(ushort *)(iVar1 + 0x16) = *(ushort *)(iVar1 + 0x16) & 0xf7ff;
        iVar4 = *(int *)(iVar1 + 0x20) + *(int *)(iVar1 + 0x1c);
        if ((iVar10 < iVar4) && (sVar9 = (short)((int)piVar7 - (int)param_1 >> 2) + -1, sVar9 != -1)
           ) {
          piVar11 = piVar7;
          do {
            piVar11 = piVar11 + -1;
            iVar2 = *piVar11;
            if ((*(byte *)(iVar2 + 0x17) & 2) == 0) {
              if (*(int *)(iVar2 + 0x1c) - *(int *)(iVar2 + 0x20) < iVar4) {
                if ((*(byte *)(iVar2 + 0x15) & 0x10) == 0) {
                  if ((uVar3 & 0x40) == 0) {
                    FUN_00016832(iVar1,iVar2);
                  }
                  else {
                    FUN_00016c02(iVar1,iVar2);
                  }
                }
                else if ((uVar3 & 0x40) == 0) {
                  FUN_000167a4(iVar1,iVar2);
                }
                else {
                  FUN_00016804(iVar1,iVar2);
                }
              }
              else if (iVar4 < *(int *)(iVar2 + 0x2a)) break;
            }
            sVar9 = sVar9 + -1;
          } while (sVar9 != -1);
        }
        *local_37c = iVar1;
        local_37e = local_37e + 1;
        local_37c = local_37c + 1;
      }
LAB_00006682:
      iVar4 = *(int *)(iVar1 + 0x1c) - *(int *)(iVar1 + 0x20);
      if (iVar4 < iVar10) {
        iVar10 = iVar4;
      }
      *(int *)(iVar1 + 0x2a) = iVar10;
LAB_00006694:
      *piVar7 = iVar1;
      piVar6 = piVar6 + 1;
      piVar7 = piVar7 + 1;
    }
    if ((*(int *)(iVar1 + 0x1c) - *(int *)(iVar1 + 0x18) < 0x19c80) && ((uVar3 & 2) == 0))
    goto LAB_00006530;
    if (DAT_00021020 - *(int *)(iVar1 + 0x3e) < 0x4c) goto LAB_00006694;
    if (*(int *)(iVar1 + 0x3e) != 0) {
      FUN_0000605a(iVar1);
    }
    FUN_00006150(iVar1);
    (&DAT_000233bc)[DAT_000219c4] = iVar1;
    DAT_000219c4 = DAT_000219c4 + 1;
    piVar6 = piVar6 + 1;
  } while( true );
}



// FUN_000066c6 @ 0x66c6

void FUN_000066c6(undefined4 *param_1,int param_2)

{
  int *piVar1;
  int *piVar2;
  code *pcVar3;
  short sVar4;
  int iVar5;
  short sVar6;
  int iVar7;
  undefined4 *puVar8;
  undefined4 *puVar9;
  
  iVar7 = 0x7fffffff;
  sVar4 = *(short *)(param_2 + 2);
  puVar8 = param_1;
  do {
    sVar4 = sVar4 + -1;
    if (sVar4 == -1) {
      return;
    }
    puVar9 = puVar8 + 1;
    piVar1 = (int *)*puVar8;
    iVar5 = piVar1[6] + piVar1[7];
    if (iVar7 < iVar5) {
      sVar6 = (short)((int)puVar8 - (int)param_1 >> 2);
      do {
        while( true ) {
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) goto LAB_00006758;
          puVar8 = puVar8 + -1;
          piVar2 = (int *)*puVar8;
          if (iVar5 <= piVar2[7] - piVar2[6]) break;
          if ((*piVar1 != 0) &&
             (pcVar3 = *(code **)(*(int *)(*piVar1 + 4) + 0x16), pcVar3 != (code *)0x0)) {
            (*pcVar3)(piVar1,piVar2);
          }
          if ((*piVar2 != 0) &&
             (pcVar3 = *(code **)(*(int *)(*piVar2 + 4) + 0x16), pcVar3 != (code *)0x0)) {
            (*pcVar3)(piVar2,piVar1);
          }
        }
      } while (*(int *)((int)piVar2 + 0x2a) <= iVar5);
    }
LAB_00006758:
    if (piVar1[7] - piVar1[6] < iVar7) {
      iVar7 = piVar1[7] - piVar1[6];
    }
    *(int *)((int)piVar1 + 0x2a) = iVar7;
    puVar8 = puVar9;
  } while( true );
}



// FUN_00006774 @ 0x6774

void FUN_00006774(void)

{
  int iVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  short sVar5;
  short sVar6;
  undefined4 *puVar7;
  int iVar8;
  int *piVar9;
  int *piVar10;
  int *piVar11;
  int *piVar12;
  int *local_8;
  
  FUN_0000313e(DAT_000219b8,&DAT_00021f44,&DAT_00021f44,0);
  piVar10 = &DAT_00022994;
  local_8 = &DAT_00022994;
  puVar7 = &DAT_00021f44;
  sVar6 = 0;
  do {
    if (sVar6 == 0) {
      sVar5 = DAT_000219aa;
    }
    else {
      sVar5 = DAT_000219bc._2_2_ - DAT_000219aa;
      DAT_000219aa = (short)((int)(piVar10 + -0x8a65) >> 2);
    }
    sVar5 = sVar5 + -1;
    if (sVar5 != -1) {
      piVar12 = puVar7 + 2;
      piVar11 = piVar10;
      do {
        piVar9 = local_8 + 1;
        iVar1 = *local_8;
        if ((*(byte *)(iVar1 + 0x17) & 4) == 0) {
          if (-((*(int *)(iVar1 + 0x1c) >> 2) - *(int *)(iVar1 + 0x1c)) < *(int *)(iVar1 + 0x20)) {
            *piVar11 = iVar1;
            *(uint *)(iVar1 + 0x14) = *(uint *)(iVar1 + 0x14) | 8;
            piVar10 = piVar11 + 1;
          }
          else {
            piVar10 = piVar11;
            if (0 < *piVar12) {
              sVar3 = FUN_00003908(*puVar7);
              sVar4 = FUN_00003908(puVar7[1]);
              sVar2 = (short)(*(int *)(iVar1 + 0x20) >> (*(ushort *)(iVar1 + 0x34) & 0x3f));
              if (sVar4 < sVar3) {
                iVar8 = (int)((short)(sVar3 - sVar2) >> 1) + (int)(short)(sVar3 - sVar2);
              }
              else {
                iVar8 = (int)((short)(sVar4 - sVar2) >> 1) + (int)(short)(sVar4 - sVar2);
              }
              if (iVar8 < *piVar12 + (int)sVar2) {
                piVar10 = piVar11 + 1;
                *piVar11 = iVar1;
              }
            }
          }
          piVar12 = piVar12 + 3;
          puVar7 = puVar7 + 3;
        }
        else {
          piVar10 = piVar11 + 1;
          *piVar11 = iVar1;
        }
        sVar5 = sVar5 + -1;
        piVar11 = piVar10;
        local_8 = piVar9;
      } while (sVar5 != -1);
    }
    sVar6 = sVar6 + 1;
  } while (sVar6 < 2);
  DAT_000219bc = (int)(piVar10 + -0x8a65) >> 2;
  DAT_000219b8 = 0;
  return;
}



// FUN_000068a2 @ 0x68a2

void FUN_000068a2(void)

{
  int *piVar1;
  int iVar2;
  bool bVar3;
  int iVar4;
  int iVar5;
  ushort uVar6;
  ushort uVar7;
  short sVar9;
  int iVar8;
  int iVar10;
  int iVar11;
  undefined4 *puVar12;
  undefined4 *puVar13;
  int *piVar14;
  undefined4 *puVar16;
  undefined4 *puVar15;
  int *piVar17;
  
  iVar5 = DAT_00023c3c;
  iVar4 = DAT_00021594;
  bVar3 = false;
  if ((DAT_00023d64 < DAT_00023c3c) || (DAT_00023c3c * 2 <= DAT_00023d64)) {
    iVar10 = 0;
    for (iVar11 = DAT_00023d64; iVar11 != 0; iVar11 = iVar11 >> 1) {
      iVar10 = iVar10 + 1;
    }
    if (iVar10 == 0) {
      DAT_00023c3c = 0;
      DAT_00023c34 = 0;
    }
    else {
      DAT_00023c3c = 1 << (iVar10 - 1U & 0x3f);
      DAT_00023c34 = (short)iVar10 + -1;
    }
    if ((0xff < DAT_00023c3c) || (0xff < iVar5)) {
      bVar3 = true;
    }
  }
  iVar5 = (&DAT_0002043c)[DAT_00023c34];
  if (DAT_00023c3c != 0) {
    iVar5 = (iVar5 * (DAT_00023d64 - DAT_00023c3c) >> ((int)DAT_00023c34 & 0x3fU)) + iVar5;
  }
  iVar11 = 0;
  iVar10 = 0;
  do {
    *(int *)(*(int *)(iVar4 + 0x46) + 4 + iVar10) = iVar5;
    iVar8 = DAT_00021598;
    iVar10 = iVar10 + 0xc;
    iVar11 = iVar11 + 1;
  } while (iVar11 < 4);
  *(int *)(*(int *)(DAT_00021598 + 0x46) + 100) = iVar5;
  *(int *)(*(int *)(iVar8 + 0x46) + 0x70) = iVar5;
  iVar4 = FUN_0000483e(DAT_00023c54,DAT_00023c50,DAT_00023c4c);
  iVar4 = (iVar4 >> 3) + iVar4;
  if (0xce40 < iVar4) {
    bVar3 = true;
    DAT_00023c54 = DAT_00023d68;
    DAT_00023c50 = DAT_00023d64;
    DAT_00023c4c = DAT_00023d60;
  }
  iVar5 = FUN_0000483e(DAT_00023c48,DAT_00023c44,DAT_00023c40);
  iVar5 = (iVar5 >> 3) + iVar5;
  if (0x80e80 < iVar5) {
    bVar3 = true;
    DAT_00023c54 = DAT_00023d68;
    DAT_00023c48 = DAT_00023d68;
    DAT_00023c50 = DAT_00023d64;
    DAT_00023c44 = DAT_00023d64;
    DAT_00023c4c = DAT_00023d60;
    DAT_00023c40 = DAT_00023d60;
  }
  if (bVar3) {
    uVar7 = 0;
    for (iVar10 = *(int *)(&DAT_0002048c + DAT_00023c34 * 4); 0x7fff < iVar10; iVar10 = iVar10 >> 1)
    {
      uVar7 = uVar7 + 1;
    }
    iVar11 = *(int *)(&DAT_0002048c + DAT_00023c34 * 4) >> (uVar7 & 0x3f);
    iVar10 = iVar11 * 0x16a1;
    if (iVar10 < 0) {
      iVar10 = iVar10 + 0xfff;
    }
    iVar8 = DAT_00020f2c + -1;
    piVar17 = &DAT_00020b44;
    if (iVar8 != -1) {
      do {
        piVar14 = piVar17 + 1;
        iVar2 = *piVar17;
        if ((*(byte *)(iVar2 + 0x16) & 0x10) != 0) {
          FUN_00005c50(iVar2);
          FUN_0000579a(iVar2,(iVar10 >> 0xc) << (uVar7 & 0x3f),(int)(short)uVar7,
                       (int)(short)((iVar11 * 9) / 10));
        }
        uVar6 = (ushort)((uint)iVar8 >> 0x10);
        sVar9 = (short)iVar8 + -1;
        iVar8 = CONCAT22(uVar6,sVar9);
        piVar17 = piVar14;
      } while ((sVar9 != -1) || (iVar8 = (uint)uVar6 * 0x10000 + -1, uVar6 != 0));
    }
  }
  if (0x80e80 < iVar5 || 0xce40 < iVar4) {
    FUN_0000527c();
    bVar3 = false;
    if (0x80e80 < iVar5) {
      piVar17 = &DAT_00022d2c;
      piVar14 = &DAT_00022d2c;
      iVar5 = 0;
      iVar4 = DAT_000219c4;
      if (0 < DAT_000219c0) {
        do {
          piVar1 = (int *)*piVar14;
          if ((*(byte *)((int)piVar1 + 0x17) & 2) == 0) {
            iVar10 = FUN_000047d6(*piVar14);
            if (iVar10 - *(int *)(*piVar14 + 0x18) < 0x80e80) {
              (&DAT_000233bc)[iVar4] = *piVar14;
              iVar4 = iVar4 + 1;
            }
            else {
              *piVar17 = *piVar14;
              piVar17 = piVar17 + 1;
            }
          }
          else if (*(char *)((int)piVar1 + 0x29) == '\0') {
            if (*piVar1 != 0) {
              puVar12 = (undefined4 *)*piVar1;
              *puVar12 = 0;
              if (*(int *)(puVar12[1] + 10) != 0) {
                (**(code **)(puVar12[1] + 10))(puVar12);
              }
              FUN_00002008(puVar12);
              bVar3 = true;
            }
            FUN_00002008(*piVar14);
          }
          else {
            *piVar17 = *piVar14;
            piVar17 = piVar17 + 1;
          }
          piVar14 = piVar14 + 1;
          iVar5 = iVar5 + 1;
        } while (iVar5 < DAT_000219c0);
      }
      DAT_000219c0 = (int)(piVar17 + -0x8b4b) >> 2;
      DAT_000219c4 = iVar4;
    }
    piVar17 = &DAT_000233bc;
    piVar14 = &DAT_000233bc;
    iVar4 = 0;
    if (0 < DAT_000219c4) {
      do {
        piVar1 = (int *)*piVar14;
        if ((*(byte *)((int)piVar1 + 0x17) & 2) == 0) {
          iVar5 = FUN_000047d6(*piVar14);
          iVar5 = iVar5 - *(int *)(*piVar14 + 0x18);
          if (iVar5 < 0x80e80) {
            if (0xce3f < iVar5) goto LAB_00006c1c;
            FUN_00005cb8(*piVar14);
            goto LAB_00006c26;
          }
          (&DAT_00022d2c)[DAT_000219c0] = *piVar14;
          DAT_000219c0 = DAT_000219c0 + 1;
        }
        else if (*(char *)((int)piVar1 + 0x29) == '\0') {
          if (*piVar1 != 0) {
            puVar12 = (undefined4 *)*piVar1;
            *puVar12 = 0;
            if (*(int *)(puVar12[1] + 10) != 0) {
              (**(code **)(puVar12[1] + 10))(puVar12);
            }
            FUN_00002008(puVar12);
            bVar3 = true;
          }
          FUN_00002008(*piVar14);
LAB_00006c26:
        }
        else {
LAB_00006c1c:
          *piVar17 = *piVar14;
          piVar17 = piVar17 + 1;
        }
        piVar14 = piVar14 + 1;
        iVar4 = iVar4 + 1;
      } while (iVar4 < DAT_000219c4);
    }
    DAT_000219c4 = (int)(piVar17 + -0x8cef) >> 2;
    if (bVar3) {
      puVar12 = &DAT_00021bd4;
      iVar4 = DAT_000219b4 + -1;
      puVar13 = puVar12;
      puVar15 = &DAT_00021bd4;
      if (iVar4 != -1) {
        do {
          puVar16 = puVar15 + 1;
          puVar12 = puVar13;
          if (*(int *)*puVar15 != 0) {
            puVar12 = puVar13 + 1;
            *puVar13 = (int *)*puVar15;
          }
          uVar7 = (ushort)((uint)iVar4 >> 0x10);
          sVar9 = (short)iVar4 + -1;
          iVar4 = CONCAT22(uVar7,sVar9);
          puVar13 = puVar12;
          puVar15 = puVar16;
        } while ((sVar9 != -1) || (iVar4 = (uint)uVar7 * 0x10000 + -1, uVar7 != 0));
      }
      DAT_000219b4 = (int)(puVar12 + -0x86f5) >> 2;
    }
    FUN_000052d6();
  }
  return;
}



// FUN_00006c8a @ 0x6c8a

void FUN_00006c8a(void)

{
  short sVar1;
  
  sVar1 = 0;
  do {
    if ((&DAT_0002144c)[sVar1] != 0) {
      FUN_000060fc((&DAT_0002144c)[sVar1]);
      (&DAT_0002144c)[sVar1] = 0;
    }
    sVar1 = sVar1 + 1;
  } while (sVar1 < 7);
  return;
}



// FUN_00006cba @ 0x6cba

/* WARNING: Removing unreachable block (ram,0x00006e46) */
/* WARNING: Removing unreachable block (ram,0x00006de2) */
/* WARNING: Removing unreachable block (ram,0x00006e12) */
/* WARNING: Removing unreachable block (ram,0x00007126) */
/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00006cba(void)

{
  uint *puVar1;
  int iVar2;
  int iVar3;
  ushort uVar6;
  short sVar7;
  short sVar8;
  int iVar4;
  ushort uVar9;
  undefined4 uVar5;
  undefined2 uVar10;
  short *psVar11;
  int iVar12;
  undefined1 *puVar13;
  undefined4 *puVar14;
  int *piVar15;
  short sVar16;
  short asStackY_10384 [32724];
  int *local_388;
  short local_384 [64];
  undefined1 auStack_304 [10];
  short local_2fa [379];
  
  if (*(short *)(SUB_000207fc + 0xac) != 0) {
    if (DAT_00023d88 < 6) {
      iVar4 = DAT_00023d64 - *(int *)(SUB_00020b28 + 0x2e);
    }
    else {
      iVar4 = DAT_00023d64;
    }
    if (iVar4 < 0x26ad) {
      if (_DAT_0002b228 == 0) {
        _DAT_00023d86 = 1;
        _DAT_0002b228 = 1;
      }
      if (_DAT_00023d86 != 0) {
        puVar14 = &DAT_0002110c;
        sVar16 = 0;
        do {
          *puVar14 = 1000000000;
          puVar14[2] = 1000000000;
          puVar14 = puVar14 + 3;
          sVar16 = sVar16 + 1;
        } while (sVar16 < 0x40);
        sVar16 = 0;
        do {
          if ((&DAT_0002144c)[sVar16] != 0) {
            puVar1 = (uint *)((&DAT_0002144c)[sVar16] + 0x14);
            *puVar1 = *puVar1 & 0xfffffffb;
          }
          sVar16 = sVar16 + 1;
        } while (sVar16 < 7);
      }
      iVar2 = *(int *)(DAT_00023c2c + 0x2a);
      piVar15 = &DAT_0002110c;
      sVar16 = 0;
      do {
        iVar3 = FUN_000038e8(*piVar15 - DAT_00023d68);
        if ((0x4d58 < iVar3) || (iVar3 = FUN_000038e8(piVar15[2] - DAT_00023d60), 0x4d58 < iVar3)) {
          if ((DAT_00023d88 < 6) && (_DAT_00023d86 == 0)) {
            uVar6 = FUN_00013672();
            uVar6 = (*(short *)(SUB_00020b28 + 0x22) +
                    (short)((int)((uint)(uVar6 & 0xfff) * 0xc00) >> 0xc)) - 0x600U & 0x3fff;
            sVar7 = FUN_000023c4(uVar6);
            sVar8 = FUN_00002426(uVar6);
            *piVar15 = DAT_00023d68 + (sVar7 * -0x4d58 >> 0xc);
            piVar15[1] = 0;
            piVar15[2] = DAT_00023d60 + (sVar8 * 0x4d58 >> 0xc);
          }
          else {
            uVar6 = FUN_00013672();
            *piVar15 = DAT_00023d68 + -0x4d58 + ((int)((uint)(uVar6 & 0xfff) * 0x9ab0) >> 0xc);
            piVar15[1] = 0;
            uVar6 = FUN_00013672();
            piVar15[2] = DAT_00023d60 + -0x4d58 + ((int)((uint)(uVar6 & 0xfff) * 0x9ab0) >> 0xc);
          }
          if (((((&DAT_000210cc)[sVar16] & 0xc0) == 0xc0) &&
              (((&DAT_000210cc)[sVar16] & 0x3f) != 0x3f)) &&
             ((&DAT_0002144c)[(short)(ushort)((&DAT_000210cc)[sVar16] & 0x3f)] != 0)) {
            puVar1 = (uint *)((&DAT_0002144c)[(short)(ushort)((&DAT_000210cc)[sVar16] & 0x3f)] +
                             0x14);
            *puVar1 = *puVar1 & 0xfffffffb;
          }
          (&DAT_000210cc)[sVar16] = 0x3f;
        }
        piVar15 = piVar15 + 3;
        sVar16 = sVar16 + 1;
      } while (sVar16 < 0x40);
      puVar13 = auStack_304;
      FUN_00002294(0x40,&DAT_0002110c,puVar13,-DAT_00023d68,-iVar4,-DAT_00023d60);
      FUN_00003220(0x40,puVar13,puVar13,&DAT_00023c74);
      sVar16 = 0;
      sVar7 = 0;
      psVar11 = local_2fa;
      do {
        if (*psVar11 < 5) {
LAB_00006fc0:
          (&DAT_000210cc)[sVar7] = (&DAT_000210cc)[sVar7] & 0x7f;
        }
        else {
          iVar3 = ((int)*(short *)(puVar13 + 2) * (int)DAT_00027050) / (int)*psVar11;
          iVar4 = ((int)DAT_0002704e * -(int)*(short *)(puVar13 + 6)) / (int)*psVar11;
          if (((iVar3 < DAT_0002701a) || (DAT_00027018 < iVar3)) ||
             ((iVar4 < DAT_00027016 || (DAT_00020b2e < iVar4)))) goto LAB_00006fc0;
          if (-1 < (char)(&DAT_000210cc)[sVar7]) {
            (&DAT_000210cc)[sVar7] = (&DAT_000210cc)[sVar7] | 0x80;
            local_384[sVar16] = sVar7;
            sVar16 = sVar16 + 1;
          }
        }
        psVar11 = psVar11 + 6;
        puVar13 = puVar13 + 0xc;
        sVar7 = sVar7 + 1;
      } while (sVar7 < 0x40);
      local_388 = &DAT_0002144c;
      uVar6 = 0;
      do {
        iVar4 = *local_388;
        if (iVar4 == 0) {
          if (sVar16 != 0) {
joined_r0x0000705c:
            do {
              if (sVar16 == 0) goto LAB_00007290;
              sVar16 = sVar16 + -1;
              sVar7 = local_384[sVar16];
              iVar3 = (int)sVar7;
              piVar15 = &DAT_0002110c + iVar3 * 3;
              if (((&DAT_000210cc)[iVar3] & 0x40) == 0) {
                uVar9 = FUN_00015c4c(*piVar15,0,(&PTR_FUN_00021114)[iVar3 * 3]);
                (&DAT_0002108c)[sVar7] = (char)uVar9;
                (&DAT_000210cc)[sVar7] = (&DAT_000210cc)[sVar7] | 0x40;
              }
              else {
                uVar9 = (ushort)(byte)(&DAT_0002108c)[iVar3];
              }
              iVar12 = *(int *)(&DAT_0002140c + ((short)uVar9 >> 4) * 4);
            } while (iVar12 == 0);
            (&DAT_000210cc)[sVar7] = (byte)uVar6 | (&DAT_000210cc)[sVar7] & 0xc0;
            (&DAT_0002107c)[(short)uVar6] = sVar7;
            if (uVar9 == 0x10) {
              uVar10 = 0x540;
            }
            else {
              uVar9 = FUN_00013672();
              uVar10 = (undefined2)((int)((uint)(uVar9 & 0xfff) * 0x40) >> 4);
            }
            if (iVar4 == 0) {
              uVar5 = FUN_00005f84(iVar12,*piVar15,(&DAT_00021110)[iVar3 * 3],
                                   (&PTR_FUN_00021114)[iVar3 * 3],0,uVar10,0,0);
              (&DAT_0002144c)[(short)uVar6] = uVar5;
            }
            else if ((*(short *)(iVar12 + 0x10) == *(short *)(iVar4 + 0x10)) &&
                    (*(int *)(iVar4 + 0x46) != 0)) {
              if (*(int *)(iVar4 + 0x2e) != 0) {
                FUN_0001748c(iVar4);
              }
              *(ushort *)(iVar4 + 0x16) = *(ushort *)(iVar4 + 0x16) | 0x800;
              iVar12 = *(int *)(DAT_00023c2c + 0x32) + (uint)*(ushort *)(iVar4 + 0x12) * 0x18;
              *(int *)(iVar12 + 8) = *piVar15;
              *(undefined4 *)(iVar12 + 0xc) = 0;
              *(undefined **)(iVar12 + 0x10) = (&PTR_FUN_00021114)[iVar3 * 3];
              *(undefined2 *)(iVar12 + 4) = uVar10;
              iVar12 = *(int *)(SUB_000207fc + 0x8c) +
                       *(int *)(iVar2 + 0x2e + (uint)*(ushort *)(iVar4 + 0x10) * 0x18);
              *(int *)(iVar4 + 4) = *(int *)(iVar12 + 2) + *piVar15;
              *(undefined4 *)(iVar4 + 8) = *(undefined4 *)(iVar12 + 6);
              *(undefined **)(iVar4 + 0xc) = (&PTR_FUN_00021114)[iVar3 * 3] + *(int *)(iVar12 + 10);
              if ((*(byte *)(iVar4 + 0x15) & 0x10) == 0) {
                FUN_00003544(iVar4,(int)*(short *)(iVar4 + 0x3c) + (int)*(short *)(iVar4 + 0x38),
                             iVar12 + 0x22,*(undefined4 *)(iVar4 + 0x46));
              }
              else {
                FUN_00002294((int)*(short *)(iVar4 + 0x3c) + (int)*(short *)(iVar4 + 0x38),
                             iVar12 + 0x22,*(undefined4 *)(iVar4 + 0x46),0,0,0);
              }
              FUN_000047d6(iVar4);
              FUN_000049ec(iVar4,*(undefined4 *)(iVar4 + 0x1c));
              FUN_00004b0c(iVar4);
            }
            else {
              FUN_000060fc(iVar4);
              uVar5 = FUN_00005f84(iVar12,*piVar15,(&DAT_00021110)[iVar3 * 3],
                                   (&PTR_FUN_00021114)[iVar3 * 3],0,uVar10,0,0);
              (&DAT_0002144c)[(short)uVar6] = uVar5;
            }
          }
        }
        else if ((*(uint *)(iVar4 + 0x14) & 6) == 0) {
          sVar7 = (&DAT_0002107c)[(short)uVar6];
          if ((uVar6 | 0xc0) != (ushort)(byte)(&DAT_000210cc)[sVar7]) {
            if ((int)(short)uVar6 == (uint)((&DAT_000210cc)[sVar7] & 0x3f)) {
              (&DAT_000210cc)[sVar7] = (&DAT_000210cc)[sVar7] | 0x3f;
            }
            if (sVar16 != 0) {
              *(uint *)(iVar4 + 0x14) = *(uint *)(iVar4 + 0x14) & 0xffffffbf;
              goto joined_r0x0000705c;
            }
            *(uint *)(iVar4 + 0x14) = *(uint *)(iVar4 + 0x14) | 0x40;
          }
        }
LAB_00007290:
        uVar6 = uVar6 + 1;
        local_388 = local_388 + 1;
        if (6 < (short)uVar6) {
          return;
        }
      } while( true );
    }
    if (_DAT_0002b228 == 0) {
      return;
    }
    FUN_00006c8a();
  }
  _DAT_0002b228 = 0;
  return;
}



// FUN_000072ac @ 0x72ac

void FUN_000072ac(void)

{
  ushort uVar3;
  ushort uVar4;
  short sVar5;
  int iVar1;
  uint uVar2;
  short sVar6;
  short sVar7;
  short sVar8;
  short sVar9;
  short asStackY_10018 [32738];
  short local_18 [10];
  
  if (*(short *)(SUB_000207fc + 0xae) == 0) {
    sVar8 = 0;
    if (0 < DAT_00020f2c) {
      do {
        iVar1 = *(int *)(&DAT_00020b44)[sVar8];
        if ((iVar1 != 0) && (**(short **)(iVar1 + 4) == 0x2a)) {
          FUN_0000605a((int *)(&DAT_00020b44)[sVar8]);
        }
        sVar8 = sVar8 + 1;
      } while (sVar8 < DAT_00020f2c);
    }
  }
  else {
    sVar8 = 0;
    do {
      uVar3 = FUN_00013672();
      uVar3 = uVar3 % 5;
      do {
        uVar4 = FUN_00013672();
        sVar9 = (uVar4 & 0xff) << 6;
        sVar7 = 0;
        if (0 < sVar8) {
          do {
            sVar5 = FUN_0000f87c((int)sVar9 - (int)local_18[sVar7]);
            iVar1 = FUN_00003908((int)sVar5);
            if (iVar1 < 0x280) break;
            sVar7 = sVar7 + 1;
          } while (sVar7 < sVar8);
        }
      } while (sVar8 != sVar7);
      local_18[sVar8] = sVar9;
      if (uVar3 == 4) {
        uVar2 = FUN_00013652();
        sVar7 = (short)((uVar2 & 0xffff) % 10) + 0xf;
        uVar4 = FUN_00013652();
        iVar1 = (uint)(uVar4 & 0xfff) * 10 + 300000;
      }
      else {
        uVar2 = FUN_00013652();
        sVar7 = (short)((uVar2 & 0xffff) % 10) + 0x23;
        uVar2 = FUN_00013652();
        iVar1 = (uVar2 & 0xffff) % 1000 + 0xcc4c;
      }
      sVar5 = FUN_000023c4(sVar9);
      sVar6 = FUN_00002426(sVar9);
      if (uVar3 == 4) {
        sVar9 = 0;
      }
      FUN_0001dc22(uVar3,(int)sVar7 * sVar5 * 0x339 >> 6,iVar1,sVar6 * 0x339 * (int)sVar7 >> 6,0,
                   0x4000U - (int)sVar9 & 0x3fff,0);
      sVar8 = sVar8 + 1;
    } while (sVar8 < 10);
  }
  return;
}



// FUN_0000743e @ 0x743e

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000743e(int param_1,int param_2)

{
  undefined4 uVar1;
  undefined4 *puVar2;
  ushort uVar3;
  short sVar7;
  short sVar8;
  short sVar9;
  int iVar4;
  int iVar5;
  uint uVar6;
  int *piVar10;
  short unaff_D6w;
  undefined2 unaff_D7w;
  undefined4 *puVar11;
  int iVar12;
  int *piVar13;
  int iVar14;
  bool bVar15;
  int *local_3c;
  short local_38;
  undefined4 local_36;
  short local_32;
  short local_30;
  short local_2e;
  undefined2 local_2c;
  undefined4 local_2a;
  undefined4 local_26;
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  undefined2 local_e;
  undefined2 local_a;
  undefined2 local_6;
  
  puVar2 = DAT_00023d94;
  puVar11 = DAT_00023d90;
  sVar7 = 0;
  if ((*(short *)(SUB_000207fc + 0x76) != 0) && (*(short *)(SUB_000207fc + 0x9e) == 0)) {
    sVar7 = 1;
  }
  if (param_2 == 1) {
    DAT_00023d94 = DAT_00023d90;
    DAT_00023d90 = puVar2;
    iVar14 = 0x3f;
    do {
      *puVar11 = 0;
      iVar14 = iVar14 + -1;
      puVar11 = puVar11 + 1;
    } while (iVar14 != -1);
    *(char *)((int)DAT_00023fba + (int)DAT_00023d94) =
         *(char *)((int)DAT_00023fba + (int)DAT_00023d94) + '\x01';
  }
  if (0x13 < DAT_0002b22c) {
    FUN_00008014();
    DAT_0002b22c = 0;
  }
  FUN_000068a2();
  sVar9 = DAT_00027014;
  if (param_2 != 1) {
    local_38 = DAT_00020b2e;
    local_2c = DAT_00027018;
    local_2e = DAT_00027016;
    local_32 = DAT_00027012;
    local_30 = DAT_00023d88;
    local_36 = DAT_00021590;
    DAT_00021590 = 0;
    DAT_00027014 = DAT_00020b30;
    DAT_00020b2e = DAT_00020b30;
    DAT_00027012 = DAT_00021010;
    DAT_00023d88 = 2;
    if (param_2 == 2) {
      uVar6 = DAT_00023d30 - 0xd43;
    }
    else {
      uVar6 = DAT_00023d30 + 0xd43;
    }
    DAT_00023d30 = uVar6 & 0x3fff;
    unaff_D7w = DAT_0002701a;
    unaff_D6w = sVar9;
  }
  DAT_00023fac = *(undefined4 *)(&DAT_00023f9c + param_2 * 4);
  DAT_00020f7c = *(undefined4 *)(&DAT_00020f6c + param_2 * 4);
  DAT_00020f80 = *(undefined4 *)(&DAT_00020f5c + param_2 * 4);
  iVar14 = SUB_000207fc + param_2 * 0x16;
  DAT_00023d8c = *(int *)(iVar14 + 0x1e);
  _DAT_00023d8a = *(undefined2 *)(iVar14 + 0x22);
  if (DAT_0002100e == 8) {
    DAT_00023fd0 = *(undefined2 *)(DAT_00023d8c + 0x1a0);
  }
  else if (DAT_0002100e == 4) {
    DAT_00023fd0 = *(undefined2 *)(DAT_00023d8c + 0x10);
  }
  if (DAT_00023d88 == 2) {
    FUN_00003002(-(int)DAT_00023d40 & 0x3fff,0,-(int)DAT_00023d3c & 0x3fff,-DAT_00023d38 & 0x3fff,
                 -DAT_00023d34 & 0x3fff,-DAT_00023d30 & 0x3fff,auStack_22);
    local_a = 0;
    local_e = 0;
    local_6 = 0x1000;
    FUN_00003220(1,auStack_10,auStack_10,auStack_22);
    DAT_00023d28 = local_e;
    DAT_00023d26 = local_6;
    FUN_00003108(&DAT_00023c74);
  }
  else {
    sVar9 = FUN_0000f87c(DAT_00023d38);
    sVar8 = FUN_000023c4((int)DAT_00023d3c);
    FUN_00002be6((int)DAT_00023d40,(int)sVar9 * (int)sVar8 >> 0xc & 0x3fff,(int)DAT_00023d3c,
                 DAT_00023d38,&DAT_00023c74);
  }
  if (*(short *)(SUB_000207fc + 0xaa) == 0) {
    (&DAT_00022994)[DAT_000219bc] = DAT_0002159c;
    DAT_000219bc = DAT_000219bc + 1;
    *(uint *)(DAT_0002159c + 0x14) = *(uint *)(DAT_0002159c + 0x14) | 4;
    (&DAT_00022994)[DAT_000219bc] = DAT_00021594;
  }
  else {
    (&DAT_00022994)[DAT_000219bc] = DAT_00021598;
    iVar14 = DAT_00021598;
    DAT_000219bc = DAT_000219bc + 1;
    *(uint *)(DAT_00021598 + 0x14) = *(uint *)(DAT_00021598 + 0x14) | 4;
    FUN_00003220((int)*(short *)(iVar14 + 0x38),*(undefined4 *)(iVar14 + 0x46),
                 *(undefined4 *)(iVar14 + 0x4e),&DAT_00023c74);
    iVar14 = FUN_000049ec(DAT_00021594,DAT_00023d64 << 1);
    if (iVar14 != 0) {
      FUN_00004b0c(DAT_00021594);
    }
    (&DAT_00022994)[DAT_000219bc] = DAT_00021594;
  }
  iVar14 = DAT_00021594;
  DAT_000219bc = DAT_000219bc + 1;
  *(uint *)(DAT_00021594 + 0x14) = *(uint *)(DAT_00021594 + 0x14) | 4;
  FUN_00003220((int)*(short *)(iVar14 + 0x38),*(undefined4 *)(iVar14 + 0x46),
               *(undefined4 *)(iVar14 + 0x4e),&DAT_00023c74);
  if (DAT_00023d88 == 2) {
    FUN_000030a4((int)DAT_00023d40,(int)DAT_00023d3e,(int)DAT_00023d3c,DAT_00023d38,DAT_00023d34,
                 DAT_00023d30,&DAT_00023c74);
  }
  else {
    FUN_00002be6((int)DAT_00023d40,(int)DAT_00023d3e,(int)DAT_00023d3c,DAT_00023d38,&DAT_00023c74);
  }
  puVar11 = &DAT_00020b44;
  iVar14 = DAT_00020f2c;
  while (iVar14 = iVar14 + -1, iVar14 != -1) {
    FUN_000047d6(*puVar11);
    puVar11 = puVar11 + 1;
  }
  FUN_00006cba();
  DAT_000215a0 = DAT_000219bc;
  if (DAT_00021046 == 0) {
    iVar14 = 0;
    if (0 < DAT_00020b24) {
      do {
        uVar1 = (&DAT_00020804)[iVar14];
        iVar4 = FUN_000047d6(uVar1);
        iVar5 = iVar14 + -1;
        if (-1 < iVar5) {
          piVar10 = &DAT_00020808 + iVar5;
          piVar13 = &DAT_00020804 + iVar5;
          do {
            if (iVar4 <= *(int *)(*piVar13 + 0x1c)) break;
            *piVar10 = *piVar13;
            piVar10 = piVar10 + -1;
            piVar13 = piVar13 + -1;
            uVar3 = (ushort)((uint)iVar5 >> 0x10);
            sVar9 = (short)iVar5 + -1;
            iVar5 = CONCAT22(uVar3,sVar9);
          } while ((sVar9 != -1) || (iVar5 = (uint)uVar3 * 0x10000 + -1, uVar3 != 0));
        }
        (&DAT_00020808)[iVar5] = uVar1;
        iVar14 = iVar14 + 1;
      } while (iVar14 < DAT_00020b24);
    }
  }
  else {
    iVar14 = 0;
    if (0 < DAT_00020b24) {
      puVar11 = &DAT_00020804;
      do {
        FUN_000047d6(*puVar11);
        iVar14 = iVar14 + 1;
        puVar11 = puVar11 + 1;
      } while (iVar14 < DAT_00020b24);
    }
    iVar14 = (int)DAT_00021046;
    piVar10 = DAT_00021048;
    while (iVar14 = iVar14 + -1, iVar14 != -1) {
      piVar13 = (int *)*piVar10;
      iVar4 = piVar10[1];
      iVar5 = *piVar13;
      *(undefined4 *)(iVar4 + 0x2a) = *(undefined4 *)(iVar4 + 0x1c);
      iVar12 = (int)*(short *)(iVar5 + 0x696);
      if (iVar12 < DAT_00023d64) {
        if (*(int *)(iVar4 + 8) + 100 < iVar12) {
LAB_0000790a:
          *(int *)(iVar4 + 0x1c) = piVar13[8] + *(int *)(iVar4 + 0x1c);
        }
        else {
          *(int *)(iVar4 + 0x1c) = *(int *)(iVar4 + 0x1c) - piVar13[8];
        }
      }
      else if ((int)*(short *)(iVar5 + 0x696) <= *(int *)(iVar4 + 8) + 100) goto LAB_0000790a;
      piVar10 = piVar10 + 2;
    }
    iVar14 = 0;
    if (0 < DAT_00020b24) {
      piVar10 = &DAT_00020804;
      do {
        iVar5 = *piVar10;
        iVar4 = *(int *)(iVar5 + 0x1c);
        iVar12 = iVar14 + -1;
        if (-1 < iVar12) {
          local_3c = &DAT_00020808 + iVar12;
          piVar13 = &DAT_00020804 + iVar12;
          do {
            if (iVar4 <= *(int *)(*piVar13 + 0x1c)) break;
            *local_3c = *piVar13;
            local_3c = local_3c + -1;
            piVar13 = piVar13 + -1;
            uVar3 = (ushort)((uint)iVar12 >> 0x10);
            sVar9 = (short)iVar12 + -1;
            iVar12 = CONCAT22(uVar3,sVar9);
          } while ((sVar9 != -1) || (iVar12 = (uint)uVar3 * 0x10000 + -1, uVar3 != 0));
        }
        (&DAT_00020808)[iVar12] = iVar5;
        piVar10 = piVar10 + 1;
        iVar14 = iVar14 + 1;
      } while (iVar14 < DAT_00020b24);
    }
    iVar14 = (int)DAT_00021046;
    piVar10 = DAT_00021048;
    while (iVar14 = iVar14 + -1, iVar14 != -1) {
      *(undefined4 *)(piVar10[1] + 0x1c) = *(undefined4 *)(piVar10[1] + 0x2a);
      piVar10 = piVar10 + 2;
    }
    DAT_00021046 = 0;
  }
  FUN_00006270(&DAT_00020b44,&DAT_00020f2c);
  DAT_000219ac = 0;
  DAT_000219aa = DAT_000219bc._2_2_;
  FUN_000063da(&DAT_00020804,&DAT_00020b24);
  if ((DAT_00021020 & 0xf) == 0) {
    _DAT_000219a4 = 1;
  }
  if (_DAT_000219a4 != 0) {
    FUN_000066c6(&DAT_000219cc,&DAT_000219ac);
    _DAT_000219a4 = 0;
  }
  FUN_000160d0();
  FUN_00006774();
  if (DAT_0002100e == 1) {
    sVar9 = DAT_00020b2e;
    if (_DAT_00027010 != 0) {
      sVar9 = DAT_00027014;
    }
    (**(code **)(SUB_000207fc + 0x84))((int)sVar9 - (int)DAT_00027016);
  }
  FUN_0000bf3e();
  if (_DAT_00023fd2 != 0) {
    FUN_00003912();
    _DAT_00023fd2 = 0;
  }
  if (DAT_0002100e != 1) {
    FUN_00003914();
  }
  DAT_000219bc = 0;
  _DAT_000219a6 = 0;
  _DAT_00023d86 = 0;
  if (*(short *)(*(int *)(SUB_000207fc + 0x72) + 6) != 0) {
    (**(code **)(SUB_000207fc + 0x29e))();
  }
  if (sVar7 != 0) {
    (**(code **)(SUB_000207fc + 0x2fe))();
  }
  if (param_2 == 1) {
    FUN_0001a2b6();
  }
  if (DAT_0002100e == 1) {
    if (*(short *)(SUB_000207fc + 0x9e) == 0) {
      (**(code **)(SUB_000207fc + 0x80))(0);
    }
    else {
      FUN_0000e734(param_2);
    }
    (**(code **)(SUB_000207fc + 0x78))();
  }
  else {
    iVar14 = (int)*(short *)(SUB_000207fc + 0x10) -
             (int)(short)(DAT_00021012 / (short)(8 / (int)DAT_0002100e));
    if (DAT_0002101c != 0) {
      iVar14 = *(short *)(SUB_000207fc + 0x10) + iVar14;
    }
    if (*(short *)(SUB_000207fc + 0x9e) == 0) {
      local_26 = DAT_00020f7c;
      local_2a = DAT_00020f80;
      (*DAT_00026ff0)(&local_26,&local_2a,(int)DAT_00027012,*(undefined4 *)(SUB_000207fc + 6),iVar14
                     );
    }
    else {
      FUN_0000e680(DAT_00020f7c,DAT_00020f80,(int)DAT_00027012,*(undefined4 *)(SUB_000207fc + 6),
                   iVar14,param_2);
    }
    uVar1 = DAT_00020f7c;
    DAT_00020f7c = DAT_00020f80;
    DAT_00020f80 = uVar1;
  }
  if ((_DAT_00027010 == 0) && (DAT_00023d88 == 1)) {
    FUN_0000c786();
    DAT_00020b2e = (short)((DAT_0002704e * 0x78) / 0x1e0);
    DAT_00027014 = (short)((DAT_0002704e * 0x43) / 0x1e0);
    sVar9 = DAT_00021010;
    if (DAT_00021010 < 0) {
      sVar9 = DAT_00021010 + 1;
    }
    DAT_00027012 = DAT_00020b2e + (sVar9 >> 1);
    bVar15 = false;
    if (param_1 == 0) {
      if (sVar7 != 0) {
        (**(code **)(SUB_000207fc + 0x2fe))();
      }
      FUN_000016fc();
      _DAT_00027010 = 1;
      (**(code **)(SUB_000207fc + 0x2a2))();
      bVar15 = _DAT_00027010 == 0;
      _DAT_00027010 = 0;
      FUN_0000152c();
      FUN_000015fa();
      FUN_000014e6();
      if (sVar7 != 0) {
        (**(code **)(SUB_000207fc + 0x2fe))();
      }
    }
    iVar14 = SUB_000207fc;
    if (param_2 == 1) {
      if (*(short *)(SUB_000207fc + 0x76) == 0) {
        *(undefined4 *)(SUB_000207fc + 0x19a) = **(undefined4 **)(SUB_000207fc + 0x196);
        *(undefined4 *)(iVar14 + 0x1b4) = **(undefined4 **)(iVar14 + 0x1b0);
        *(undefined4 *)(iVar14 + 0x1bc) = **(undefined4 **)(iVar14 + 0x1b8);
        *(undefined4 *)(iVar14 + 0xe0) = **(undefined4 **)(iVar14 + 0xdc);
      }
      else {
        *(uint *)(SUB_000207fc + 0x19a) = **(uint **)(SUB_000207fc + 0x196) & 0xffffff;
        *(uint *)(iVar14 + 0x1b4) = **(uint **)(iVar14 + 0x1b0) & 0xffffff;
        *(uint *)(iVar14 + 0x1bc) = **(uint **)(iVar14 + 0x1b8) & 0xffffff;
        *(uint *)(iVar14 + 0xe0) = **(uint **)(iVar14 + 0xdc) & 0xffffff;
      }
    }
    FUN_0000c79e(sVar7);
    FUN_0000d4d2(sVar7);
    if (sVar7 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
    (**(code **)(SUB_000207fc + 0x2a6))();
    FUN_0001bc2e();
    if (sVar7 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
    if (!bVar15) {
      _DAT_00027010 = 1;
    }
  }
  if (param_1 == 0) {
    if (sVar7 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
  }
  else if (_DAT_00027010 == 0) {
    if (sVar7 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
    FUN_0000e3ea(0);
  }
  else {
    if (DAT_0002100e == 1) {
      if (sVar7 != 0) {
        (**(code **)(SUB_000207fc + 0x2fe))();
      }
      FUN_0000e3ea(0);
    }
    else {
      FUN_0000e3ea(sVar7);
      if (sVar7 != 0) {
        (**(code **)(SUB_000207fc + 0x2fe))();
      }
    }
    FUN_0000df40();
    FUN_0001bc2e();
    FUN_0000de06();
    FUN_0000dea6();
    FUN_0000e664();
  }
  if (param_2 != 1) {
    DAT_00020b2e = local_38;
    DAT_00027018 = local_2c;
    DAT_00027016 = local_2e;
    DAT_00023d88 = local_30;
    DAT_00027012 = local_32;
    DAT_00021590 = local_36;
    if (param_2 == 2) {
      uVar6 = DAT_00023d30 + 0xd43;
    }
    else {
      uVar6 = DAT_00023d30 - 0xd43;
    }
    DAT_00023d30 = uVar6 & 0x3fff;
    DAT_00027014 = unaff_D6w;
    DAT_0002701a = unaff_D7w;
  }
  *(undefined4 *)(&DAT_00020f6c + param_2 * 4) = DAT_00020f7c;
  *(undefined4 *)(&DAT_00020f5c + param_2 * 4) = DAT_00020f80;
  return;
}



// FUN_00007ecc @ 0x7ecc

void FUN_00007ecc(int param_1)

{
  int iVar1;
  undefined2 *puVar2;
  undefined4 *puVar3;
  
  puVar3 = &DAT_00020f60;
  do {
    puVar2 = (undefined2 *)*puVar3;
    if ((puVar2 != (undefined2 *)0x0) &&
       ((param_1 == 0 || (puVar3 == (undefined4 *)(&DAT_00020f5c + param_1 * 4))))) {
      for (iVar1 = 0; iVar1 < DAT_00021010; iVar1 = iVar1 + 1) {
        *puVar2 = 0;
        puVar2[1] = 1;
        puVar2[2] = DAT_00021012;
        puVar2[3] = DAT_00023fd0;
        puVar2 = puVar2 + 4;
      }
    }
    puVar3 = puVar3 + 1;
  } while ((int)puVar3 < 0x20f69);
  return;
}



// FUN_00007f2a @ 0x7f2a

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00007f2a(void)

{
  _DAT_00027010 = 0;
  return;
}



// FUN_00007f32 @ 0x7f32

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00007f32(void)

{
  short sVar1;
  
  FUN_00007ecc(0);
  _DAT_00023fd2 = 1;
  DAT_00023fbc = 0;
  sVar1 = DAT_00021010;
  if (DAT_00021010 < 0) {
    sVar1 = DAT_00021010 + 1;
  }
  DAT_00027014 = sVar1 >> 1;
  DAT_00027012 = DAT_00021010;
  if (((DAT_00023d88 == 1) || (DAT_00023d88 == 8)) || (*(short *)(SUB_000207fc + 0xa2) == 0)) {
    DAT_0002700e = 0;
  }
  else {
    DAT_00027014 = DAT_00027014 + -0x14;
    DAT_00027012 = DAT_00021010 + -0x14;
    DAT_0002700e = 4;
  }
  _DAT_00027010 = 0;
  _DAT_0002700c = 0;
  if ((DAT_00023d88 == 6) && (DAT_00027020 != 0)) {
    DAT_00027050 = (&DAT_0002703c)[DAT_00027020];
    DAT_0002704e = (&DAT_00027024)[DAT_00027020];
  }
  else {
    DAT_00027050 = DAT_0002703c;
    DAT_0002704e = DAT_00027024;
  }
  DAT_00023fba = 0;
  DAT_00020b2e = DAT_00027014;
  return;
}



// FUN_00008014 @ 0x8014

void FUN_00008014(void)

{
  DAT_00023c48 = 100000000;
  DAT_00023c54 = 100000000;
  DAT_00023c44 = 100000000;
  DAT_00023c50 = 100000000;
  DAT_00023c40 = 100000000;
  DAT_00023c4c = 100000000;
  return;
}



// FUN_00008052 @ 0x8052

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00008052(void)

{
  byte *pbVar1;
  int iVar2;
  short sVar3;
  int iVar4;
  int iVar5;
  
  DAT_00021010 = *(short *)(SUB_000207fc + 0x18);
  DAT_00021012 = *(short *)(SUB_000207fc + 0x16);
  _DAT_00021016 = *(undefined2 *)(SUB_000207fc + 0x12);
  _DAT_00021014 = *(undefined2 *)(SUB_000207fc + 0x14);
  DAT_00027054 = DAT_00021010 >> 1;
  DAT_00027052 = *(short *)(SUB_000207fc + 0x16) >> 1;
  DAT_0002701c = (DAT_00027052 * 2 + -1) * 0x10000 | 0x8000;
  sVar3 = *(short *)(SUB_000207fc + 0x16);
  if (sVar3 < 0) {
    sVar3 = sVar3 + 1;
  }
  iVar5 = (int)(sVar3 >> 1) + (int)*(short *)(SUB_000207fc + 0x16);
  if (iVar5 < 0) {
    iVar5 = iVar5 + 1;
  }
  DAT_00027024 = (undefined2)(iVar5 >> 1);
  DAT_00027012 = DAT_00021010;
  DAT_0002703c = DAT_00027024;
  DAT_0002704e = DAT_00027024;
  DAT_00027050 = DAT_00027024;
  if ((DAT_0002100e != *(short *)(SUB_000207fc + 0xe)) ||
     (DAT_0002100c != *(short *)(SUB_000207fc + 0xb4))) {
    if (*(short *)(SUB_000207fc + 0xe) == 8) {
      FUN_00001968();
      DAT_00026ff0 = 0x4294;
      _DAT_00026fec = 0x4294;
      _DAT_00026fe8 = 0x4294;
      DAT_00023fcc = 0xe784;
      DAT_00023fc8 = 0xece2;
      DAT_00023fb0 = 0x19f12;
      DAT_00023fb4 = 0x19e8c;
      DAT_00023fc4 = 0x1b992;
      DAT_00023fc0 = 0x1b902;
      DAT_00026fe4 = 0xb3d0;
    }
    if (*(short *)(SUB_000207fc + 0xe) == 4) {
      FUN_00001984();
      DAT_00026ff0 = 0x3d9a;
      _DAT_00026fec = 0x3f46;
      _DAT_00026fe8 = 0x40e8;
      DAT_00023fcc = 0xe978;
      DAT_00023fc8 = 0xef28;
      DAT_00023fb0 = 0x1a104;
      DAT_00023fb4 = 0x19f8c;
      DAT_00023fc4 = 0x1baca;
      DAT_00023fc0 = 0x1b9ea;
      DAT_00026fe4 = 0xb3d0;
    }
    if (*(short *)(SUB_000207fc + 0xe) == 1) {
      DAT_00026fe4 = 0xb482;
      DAT_00023fcc = *(undefined4 *)(SUB_000207fc + 0x2ae);
      DAT_00023fc8 = *(undefined4 *)(SUB_000207fc + 0x2ae);
      DAT_00023fb0 = 0x1a20c;
      DAT_00023fb4 = 0x1a204;
      DAT_00023fc4 = 0x1bbe2;
      DAT_00023fc0 = 0x1bbb6;
    }
    DAT_0002100e = *(short *)(SUB_000207fc + 0xe);
    DAT_0002100c = *(short *)(SUB_000207fc + 0xb4);
    DAT_00023d8c = *(undefined4 *)(SUB_000207fc + 0x34);
    _DAT_00023d8a = *(undefined2 *)(SUB_000207fc + 0x38);
    if (DAT_0002159c == 0) {
      FUN_00001d4a(*(undefined4 *)(SUB_000207fc + 0x8c));
    }
    if (*(short *)(SUB_000207fc + 0xe) == 1) {
      FUN_00000020(0x195);
      iVar2 = DAT_0002158c;
      iVar4 = 1;
      iVar5 = 0x20;
      do {
        pbVar1 = (byte *)(*(int *)(iVar2 + 0x4a) + 0x13 + iVar5);
        *pbVar1 = *pbVar1 & 0xfd;
        iVar5 = iVar5 + 0x20;
        iVar4 = iVar4 + 1;
      } while (iVar4 < 5);
    }
    else {
      FUN_00000020(0x1d5);
      iVar2 = DAT_0002158c;
      iVar4 = 1;
      iVar5 = 0x20;
      do {
        pbVar1 = (byte *)(*(int *)(iVar2 + 0x4a) + 0x13 + iVar5);
        *pbVar1 = *pbVar1 | 2;
        iVar5 = iVar5 + 0x20;
        iVar4 = iVar4 + 1;
      } while (iVar4 < 5);
    }
  }
  DAT_00023d8c = *(undefined4 *)(SUB_000207fc + 0x34);
  _DAT_00023d8a = *(undefined2 *)(SUB_000207fc + 0x38);
  iVar5 = -(int)DAT_00021012;
  if (iVar5 < 0) {
    iVar5 = iVar5 + 1;
  }
  DAT_0002701a = (undefined2)(iVar5 >> 1);
  sVar3 = DAT_00021012;
  if (DAT_00021012 < 0) {
    sVar3 = DAT_00021012 + 1;
  }
  DAT_00027018 = (sVar3 >> 1) + -1;
  sVar3 = DAT_00021010;
  if (DAT_00021010 < 0) {
    sVar3 = DAT_00021010 + 1;
  }
  DAT_00020b2e = sVar3 >> 1;
  iVar5 = -(int)DAT_00021010;
  if (iVar5 < 0) {
    iVar5 = iVar5 + 1;
  }
  DAT_00027016 = (undefined2)(iVar5 >> 1);
  DAT_00020b30 = DAT_00020b2e;
  DAT_00027014 = DAT_00020b2e;
  FUN_00007f32();
  return;
}



// FUN_0000836c @ 0x836c

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000836c(int param_1)

{
  short sVar1;
  
  DAT_00021060 = *(undefined2 *)(&DAT_000204dc + param_1 * 2);
  sVar1 = *(short *)(&DAT_000204dc + param_1 * 2);
  if (sVar1 < 0) {
    sVar1 = sVar1 + 1;
  }
  DAT_0002105e = sVar1 >> 1;
  if (*(short *)(SUB_000207fc + 0xa4) == 0) {
    *(undefined4 *)(SUB_00020b28 + 0x32) = 0xfcf6;
  }
  if (*(short *)(SUB_000207fc + 0xe) == 1) {
    FUN_00004e88();
    DAT_00023fb8 = **(undefined2 **)(SUB_000207fc + 0x88);
  }
  _DAT_0002b268 = 0;
  _DAT_0002b26a = 0;
  DAT_00021042 = 0;
  if (_DAT_0002b270 != 0) {
    DAT_0002b26e = *(undefined2 *)(SUB_000207fc + 0x37a);
    DAT_0002b26c = *(undefined2 *)(SUB_000207fc + 0x37c);
  }
  return;
}



// FUN_000083fe @ 0x83fe

void FUN_000083fe(void)

{
  DAT_00023d68 = *(undefined4 *)(*SUB_00020b28 + 4);
  DAT_00023d64 = *(undefined4 *)(*SUB_00020b28 + 8);
  DAT_00023d60 = *(undefined4 *)(*SUB_00020b28 + 0xc);
  DAT_00023d40 = -*(short *)(SUB_00020b28 + 8) & 0x3fff;
  DAT_00023d3e = -*(short *)((int)SUB_00020b28 + 0x22) & 0x3fff;
  DAT_00023d3c = -*(short *)(SUB_00020b28 + 9) & 0x3fff;
  DAT_00023d38 = (int)*(short *)(SUB_00020b28[1] + 0x46) -
                 (int)*(short *)((int)SUB_00020b28 + 0x626) & 0x3fff;
  return;
}



// FUN_0000846e @ 0x846e

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_0000846e(int param_1,int param_2)

{
  uint *puVar1;
  int iVar2;
  short sVar3;
  int iVar4;
  uint uVar5;
  uint uVar6;
  ushort uVar9;
  int iVar7;
  int iVar8;
  short sVar10;
  short sVar11;
  short sVar13;
  uint uVar12;
  int *piVar14;
  int unaff_A5;
  ushort uStack_26;
  ushort uStack_24;
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  sVar3 = DAT_00023d88;
  piVar14 = SUB_00020b28;
  if (DAT_00021058 == 0) {
LAB_000084b0:
    if (DAT_00023d88 == 8) {
      if (DAT_00021064 == 0) {
        DAT_00023d88 = 1;
        if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
          puVar1 = (uint *)(*SUB_00020b28 + 0x14);
          *puVar1 = *puVar1 | 0x40;
          FUN_00016136(*piVar14);
        }
      }
      else {
        DAT_00023d88 = 9;
      }
      FUN_00007f32();
      DAT_0002106c = 0;
    }
  }
  else {
    if (DAT_00021056 == 0) {
      DAT_00021058 = 0;
      goto LAB_000084b0;
    }
    if (DAT_00021058 == 1) {
      DAT_00021058 = 2;
    }
  }
  if (*(short *)(SUB_000207fc + 0x98) != 0) {
    if ((*(short *)(SUB_000207fc + 0x98) == 2) && (DAT_00021058 == 0)) {
      if ((*(int *)(SUB_000207fc + 0x296) != 0) &&
         (iVar4 = (**(code **)(SUB_000207fc + 0x2f2))(0), iVar4 != 0)) {
        (**(code **)(SUB_000207fc + 0x2fa))(0);
      }
      FUN_00007f32();
      DAT_00023d88 = 8;
      DAT_00021058 = 3;
      DAT_00021056 = 0x1e;
      local_a = 0x183;
      local_e = -0x2ee;
      local_6 = -0x513;
      FUN_00002d02(0,(int)*(short *)((int)SUB_00020b28 + 0x22),0,auStack_22);
      FUN_00003220(1,auStack_10,auStack_10,auStack_22);
      DAT_00023d68 = *(int *)(*SUB_00020b28 + 4) + (int)local_e;
      DAT_00023d64 = *(int *)(*SUB_00020b28 + 8) + (int)local_a;
      DAT_00023d60 = *(int *)(*SUB_00020b28 + 0xc) + (int)local_6;
    }
    if ((DAT_00021054 == 0) && (DAT_00021056 == 0)) {
      FUN_000098c6(*(char *)(SUB_00020b28 + 0x193) == '\0');
    }
  }
  if ((DAT_00021054 != 0) && (DAT_00021056 == 0)) {
    FUN_0000a136();
  }
  sVar10 = DAT_00027052;
  piVar14 = SUB_00020b28;
  iVar4 = SUB_000207fc;
  if ((DAT_00023d88 == 7) && ((DAT_00021070 == 0 || (*(char *)(SUB_00020b28 + 0x193) != '\0'))))
  goto switchD_000086e8_caseD_56;
  if ((DAT_00023d88 == 8) || (DAT_00023d88 == 9)) {
    if ((param_2 == 3) && (((param_1 == 0x22 || (param_1 == 0x60)) || (param_1 == 0x1b)))) {
switchD_000086e8_caseD_1b:
      uVar5 = 3;
      goto switchD_00009430_caseD_3;
    }
    goto LAB_00009098;
  }
  if ((param_2 != 3) &&
     ((((((param_2 != 4 || (param_1 != 0x20)) &&
         ((param_1 != 0x61 && ((param_1 != 0x41 && (param_1 != 0x2b)))))) && (param_1 != 0x2d)) &&
       ((((((param_1 != 0x3d && (param_1 != 0x5f)) && (param_1 != 0x32)) &&
          ((param_1 != 0x34 && (param_1 != 0x36)))) && (param_1 != 0x38)) &&
        ((param_1 != 0x5a && (param_1 != 0x7a)))))) &&
      (((param_1 != 0x3e && (((param_1 != 0x2e && (param_1 != 0x58)) && (param_1 != 0x78)))) &&
       ((param_1 != 0x3f && (param_1 != 0x2f)))))))) goto switchD_000086e8_caseD_a;
  switch(param_1) {
  case 9:
    FUN_0001b8d8();
    break;
  case 0x13:
    *(undefined1 *)((int)SUB_00020b28 + 0x676) = 0;
    *(undefined1 *)((int)piVar14 + 0x689) = 0;
    *(undefined4 *)((int)piVar14 + 0x62a) = 0;
    if (*(int *)((int)piVar14 + 0x32) == 0xfcf6) {
      *(undefined4 *)((int)piVar14 + 0x32) = 0x112a4;
    }
    else {
LAB_00008dde:
      *(undefined4 *)((int)SUB_00020b28 + 0x32) = 0xfcf6;
    }
    break;
  case 0x18:
    if (DAT_00021070 != 0) {
      *(ushort *)(DAT_00021070 + 0x698) = *(ushort *)(DAT_00021070 + 0x698) | 0x100;
    }
    break;
  case 0x1b:
  case 0x60:
  case 0x7e:
    goto switchD_000086e8_caseD_1b;
  case 0x1c:
    if (DAT_00023d88 < 3) {
      uVar5 = DAT_0002b238 - 0x800;
LAB_00008b7a:
      DAT_0002b238 = uVar5 & 0x3fff;
    }
    break;
  case 0x1d:
    if (DAT_00023d88 < 3) {
      uVar5 = DAT_0002b238 + 0x800;
      goto LAB_00008b7a;
    }
    break;
  case 0x1e:
    if ((DAT_00023d88 < 3) && (DAT_0002b234 != 0x3580)) {
      uVar5 = DAT_0002b234 - 0x540;
LAB_00008b40:
      DAT_0002b234 = uVar5 & 0x3fff;
    }
    break;
  case 0x1f:
    if ((DAT_00023d88 < 3) && (DAT_0002b234 != 0xa80)) {
      uVar5 = DAT_0002b234 + 0x540;
      goto LAB_00008b40;
    }
    break;
  case 0x20:
    if (_DAT_0002105c == 0) {
      if (*(char *)(SUB_00020b28 + 0x193) == '\0') {
        if (_DAT_0002b270 != 0) {
          if (param_2 == 3) {
            _DAT_0002b230 = 1;
          }
          else {
            _DAT_0002b230 = 0;
          }
        }
      }
      else if (param_2 == 3) {
        *(undefined1 *)((int)SUB_00020b28 + 0x64f) = 1;
      }
      else {
        *(undefined1 *)((int)SUB_00020b28 + 0x64f) = 0;
      }
    }
    break;
  case 0x2b:
  case 0x3d:
    if ((DAT_00023d88 == 6) && (DAT_00027020 < 8)) {
      if (param_2 == 3) {
        iVar4 = (int)DAT_00027020;
        (&DAT_0002703c)[iVar4] = DAT_00027050;
        (&DAT_00027024)[iVar4] = DAT_0002704e;
        DAT_00027050 = DAT_00027050 + (DAT_00027050 >> 1);
        sVar10 = DAT_0002704e + (DAT_0002704e >> 1);
        sVar13 = DAT_00027020 + 1;
        DAT_00027020 = sVar13;
        DAT_0002704e = sVar10;
        (&DAT_0002703c)[sVar13] = DAT_00027050;
        (&DAT_00027024)[sVar13] = sVar10;
      }
    }
    else if ((DAT_00023d88 < 6) && (_DAT_0002105c == 0)) {
      if (param_2 == 3) {
        DAT_0002b232 = 1;
      }
      else {
LAB_000088ee:
        DAT_0002b232 = 0;
      }
    }
    break;
  case 0x2d:
  case 0x5f:
    if ((DAT_00023d88 == 6) && (DAT_00027020 != 0)) {
      if (param_2 == 3) {
        DAT_00027020 = DAT_00027020 + -1;
        DAT_00027050 = (&DAT_0002703c)[DAT_00027020];
        DAT_0002704e = (&DAT_00027024)[DAT_00027020];
      }
    }
    else if ((DAT_00023d88 < 6) && (_DAT_0002105c == 0)) {
      if (param_2 != 3) goto LAB_000088ee;
      DAT_0002b232 = -1;
    }
    break;
  case 0x2e:
  case 0x3e:
  case 0x5a:
  case 0x7a:
    if ((param_2 == 3) && ((param_1 == 0x5a || (param_1 == 0x3e)))) {
      DAT_00021042 = 0x40;
    }
    else {
LAB_00008c76:
      DAT_00021042 = 0;
    }
    break;
  case 0x2f:
  case 0x3f:
  case 0x58:
  case 0x78:
    if ((param_2 != 3) || ((param_1 != 0x58 && (param_1 != 0x3f)))) goto LAB_00008c76;
    DAT_00021042 = 0xffc0;
    break;
  case 0x30:
  case 0x31:
  case 0x33:
  case 0x37:
  case 0x39:
    if (*(int *)((int)SUB_00020b28 + 0x32) == 0x112a4) goto LAB_000089f6;
    break;
  case 0x32:
    if (*(int *)((int)SUB_00020b28 + 0x32) == 0x112a4) goto LAB_000089f6;
    if (param_2 == 3) {
      _DAT_0002b268 = 1;
    }
    else {
      _DAT_0002b268 = 0;
    }
LAB_000089d8:
    _DAT_0002b270 = 1;
    break;
  case 0x34:
    if (*(int *)((int)SUB_00020b28 + 0x32) != 0x112a4) {
      if (param_2 != 3) goto LAB_000089d2;
      _DAT_0002b26a = -1;
      goto LAB_000089d8;
    }
    goto LAB_000089f6;
  case 0x35:
    if (*(int *)((int)SUB_00020b28 + 0x32) != 0x112a4) {
      *(short *)(SUB_000207fc + 0x37a) = DAT_00027052;
      DAT_0002b26c = DAT_00027054;
      DAT_0002b26e = sVar10;
      *(short *)(iVar4 + 0x37c) = DAT_00027054;
      _DAT_0002b268 = 0;
LAB_000089d2:
      _DAT_0002b26a = 0;
      goto LAB_000089d8;
    }
    goto LAB_000089f6;
  case 0x36:
    if (*(int *)((int)SUB_00020b28 + 0x32) != 0x112a4) {
      if (param_2 != 3) goto LAB_000089d2;
      _DAT_0002b26a = 1;
      goto LAB_000089d8;
    }
    goto LAB_000089f6;
  case 0x38:
    if (*(int *)((int)SUB_00020b28 + 0x32) != 0x112a4) {
      if (param_2 == 3) {
        _DAT_0002b268 = -1;
      }
      else {
        _DAT_0002b268 = 0;
      }
      goto LAB_000089d8;
    }
LAB_000089f6:
    _DAT_0002b270 = 0;
    *(char *)((int)SUB_00020b28 + 0x676) = (char)param_1 + -0x30;
    *(ushort *)(piVar14 + 0x1a6) = *(ushort *)(piVar14 + 0x1a6) & 0xbfff;
    break;
  case 0x41:
  case 0x61:
    if ((DAT_00021070 == 0) || (param_2 != 3)) {
      _DAT_0002105a = 0;
    }
    else {
      _DAT_0002105a = 1;
    }
    break;
  case 0x42:
  case 0x62:
    if ((_DAT_0002105c == 0) &&
       (0x43 < *(int *)(*SUB_00020b28 + 8) - *(int *)((int)SUB_00020b28 + 0x2e))) {
      *(undefined1 *)(SUB_00020b28 + 0x1a1) = 1;
    }
    break;
  case 0x43:
  case 99:
    if (DAT_00023d88 == 5) goto switchD_000086e8_caseD_56;
    DAT_00023d88 = 5;
LAB_00008bf2:
    piVar14 = SUB_00020b28;
    if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) != 0) {
      puVar1 = (uint *)(*SUB_00020b28 + 0x14);
      *puVar1 = *puVar1 & 0xffffffbf;
      goto LAB_00008c02;
    }
    goto LAB_00008c0c;
  case 0x45:
  case 0x65:
    if (((DAT_00021070 != 0) && (DAT_00023d88 != 7)) && (*(char *)(SUB_00020b28 + 0x193) == '\0')) {
      DAT_00023d88 = 7;
      goto LAB_00008bf2;
    }
  case 0x56:
  case 0x76:
switchD_000086e8_caseD_56:
    DAT_00023d88 = 1;
    DAT_0002b234 = 0;
    DAT_00023d34 = 0;
    DAT_0002b238 = 0;
    DAT_00023d30 = 0;
    DAT_00023d68 = *(int *)(*SUB_00020b28 + 4);
    DAT_00023d64 = *(int *)(*SUB_00020b28 + 8);
    DAT_00023d60 = *(int *)(*SUB_00020b28 + 0xc);
    if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
      puVar1 = (uint *)(*SUB_00020b28 + 0x14);
      *puVar1 = *puVar1 | 0x40;
LAB_00008c02:
      FUN_00016136(*piVar14);
    }
LAB_00008c0c:
    FUN_00007f32();
    break;
  case 0x46:
  case 0x66:
    if (_DAT_0002105c == 0) {
      if (*(char *)(SUB_00020b28 + 0x1a3) == '\0') {
        *(undefined1 *)(SUB_00020b28 + 0x1a3) = 1;
      }
      else {
        *(char *)(SUB_00020b28 + 0x1a3) = -*(char *)(SUB_00020b28 + 0x1a3);
      }
    }
    break;
  case 0x47:
  case 0x67:
    if ((_DAT_0002105c == 0) && (*(char *)(SUB_00020b28 + 0x193) == '\0')) {
      if (*(char *)((int)SUB_00020b28 + 0x68a) == '\0') {
        *(undefined1 *)((int)SUB_00020b28 + 0x68a) = 1;
      }
      else {
        *(char *)((int)SUB_00020b28 + 0x68a) = -*(char *)((int)SUB_00020b28 + 0x68a);
      }
      if ((*(char *)((int)SUB_00020b28 + 0x68a) < '\x01') || (*(short *)(SUB_000207fc + 0xa4) == 0))
      goto LAB_00008dde;
      iVar4 = FUN_0001e152(SUB_00020b28);
      if (iVar4 == 0) {
        _DAT_00021026 = 1;
      }
      else {
        _DAT_00021024 = 1;
      }
    }
    break;
  case 0x4a:
  case 0x6a:
    if (((_DAT_0002105c == 0) && (*(char *)(SUB_00020b28 + 0x193) == '\0')) &&
       (*(char *)(SUB_00020b28 + 0x1a2) == '\0')) {
      *(undefined1 *)(SUB_00020b28 + 0x1a2) = 1;
    }
    break;
  case 0x4d:
  case 0x6d:
    *(undefined1 *)((int)SUB_00020b28 + 0x676) = 0;
    *(ushort *)(piVar14 + 0x1a6) = *(ushort *)(piVar14 + 0x1a6) | 0x4000;
    break;
  case 0x4e:
  case 0x6e:
    if ((_DAT_0002105c == 0) && ((*(ushort *)((int)SUB_00020b28 + 0x682) & 3) < 3)) {
      *(short *)((int)SUB_00020b28 + 0x682) = *(short *)((int)SUB_00020b28 + 0x682) + 1;
    }
    break;
  case 0x50:
  case 0x70:
    uVar5 = 4;
    goto switchD_00009430_caseD_3;
  case 0x52:
  case 0x72:
    if (DAT_00023d88 < 3) {
      uVar5 = DAT_0002b238 + 0x2000;
      goto LAB_00008b7a;
    }
    DAT_0002b238 = 0x2000;
    DAT_00023d30 = 0x2000;
    DAT_00023d88 = 1;
    break;
  case 0x54:
  case 0x74:
    if (DAT_00023d88 == 6) goto switchD_000086e8_caseD_56;
    iVar4 = FUN_00000f9c(&DAT_00023d54);
    if (iVar4 != 0) {
      DAT_00023d88 = 6;
      DAT_00023d68 = DAT_00023d54;
      DAT_00023d64 = DAT_00023d58;
      DAT_00023d60 = DAT_00023d5c;
      goto LAB_00008bf2;
    }
  }
switchD_000086e8_caseD_a:
  if (DAT_0002b232 != 0) {
    iVar4 = FUN_00003908((int)DAT_0002b232);
    piVar14 = SUB_00020b28;
    if (iVar4 == 1) {
      if ((*(byte *)(SUB_00020b28 + 0x1a6) & 0x40) == 0) {
        *(char *)((int)SUB_00020b28 + 0x676) =
             (char)DAT_0002b232 + *(char *)((int)SUB_00020b28 + 0x676);
        if ('\t' < *(char *)((int)piVar14 + 0x676)) {
          *(undefined1 *)((int)piVar14 + 0x676) = 9;
          DAT_0002b232 = 0;
        }
        if (*(char *)((int)SUB_00020b28 + 0x676) < '\0') {
          *(undefined1 *)((int)SUB_00020b28 + 0x676) = 0;
          DAT_0002b232 = 0;
        }
      }
      else {
        *(undefined1 *)((int)SUB_00020b28 + 0x676) = 0;
        *(ushort *)(piVar14 + 0x1a6) = *(ushort *)(piVar14 + 0x1a6) & 0xbfff;
      }
      DAT_0002b232 = DAT_0002b232 << 1;
    }
    else if (DAT_0002b232 < 1) {
      DAT_0002b232 = DAT_0002b232 + 1;
    }
    else {
      DAT_0002b232 = DAT_0002b232 + -1;
    }
  }
  if ((DAT_0002b26e != *(short *)(SUB_000207fc + 0x37a)) ||
     (DAT_0002b26c != *(short *)(SUB_000207fc + 0x37c))) {
    _DAT_0002b270 = 0;
  }
  if (_DAT_0002b270 == 0) {
    _DAT_0002b230 = *(short *)(SUB_000207fc + 0x37e);
  }
  else {
    sVar10 = DAT_00027052 - *(short *)(SUB_000207fc + 0x37a);
    sVar13 = *(short *)(SUB_000207fc + 0x37c) - DAT_00027054;
    if (_DAT_0002b26a == 0) {
      iVar4 = FUN_00003908((int)sVar10);
      if (0x1e < iVar4) {
        if (0 < sVar10) goto LAB_00008f50;
        goto LAB_00008f54;
      }
      sVar11 = sVar10;
    }
    else if (_DAT_0002b26a < 0) {
LAB_00008f54:
      sVar11 = -0x1e;
    }
    else {
LAB_00008f50:
      sVar11 = 0x1e;
    }
    iVar4 = FUN_00003908((int)sVar10);
    if (0xd5 < iVar4) {
      if (sVar10 < 1) {
        sVar10 = DAT_00027052 + 0xd5;
      }
      else {
        sVar10 = DAT_00027052 + -0xd5;
      }
      *(short *)(SUB_000207fc + 0x37a) = sVar10;
    }
    DAT_0002b26e = *(short *)(SUB_000207fc + 0x37a) + sVar11;
    *(short *)(SUB_000207fc + 0x37a) = DAT_0002b26e;
    if (_DAT_0002b268 == 0) {
      iVar4 = FUN_00003908((int)sVar13);
      if (0x18 < iVar4) {
        if (0 < sVar13) goto LAB_00008fd2;
        goto LAB_00008fb8;
      }
      sVar10 = -sVar13;
    }
    else if (_DAT_0002b268 < 0) {
LAB_00008fd2:
      sVar10 = -0x18;
    }
    else {
LAB_00008fb8:
      sVar10 = 0x18;
    }
    iVar4 = FUN_00003908((int)sVar13);
    if (0xab < iVar4) {
      if (sVar13 < 1) {
        sVar13 = DAT_00027054 + -0xab;
      }
      else {
        sVar13 = DAT_00027054 + 0xab;
      }
      *(short *)(SUB_000207fc + 0x37c) = sVar13;
    }
    DAT_0002b26c = *(short *)(SUB_000207fc + 0x37c) + sVar10;
    *(short *)(SUB_000207fc + 0x37c) = DAT_0002b26c;
  }
  if ((((_DAT_0002b230 != 0) && (1 < *(short *)((int)SUB_00020b28 + 0x696))) &&
      ((*(byte *)((int)SUB_00020b28 + 0x699) & 0x10) == 0)) && (_DAT_0002105c == 0)) {
    if (*(char *)((int)SUB_00020b28 + 0x687) == '\0') {
      *(undefined1 *)((int)SUB_00020b28 + 0x687) = 1;
    }
    _DAT_0002103c = 1;
  }
LAB_00009098:
  FUN_0000a380();
  iVar4 = DAT_00021070;
  sVar10 = *(short *)(SUB_00020b28 + 0xc);
  if (DAT_00021058 == 3) {
    FUN_0000a63a();
  }
  else {
    FUN_0000a426();
  }
  unaff_A5 = DAT_00021070;
  if (DAT_00021070 == 0) {
    DAT_00021070 = iVar4;
  }
  FUN_0000a90a();
  if (((7 < DAT_00023d88) || (*(char *)(SUB_00020b28 + 0x193) == '\0')) ||
     ((*(int *)((int)SUB_00020b28 + 0x62a) != 0 &&
      ((*(int *)((int)SUB_00020b28 + 0x62e) != 0 || (sVar10 < 1)))))) goto LAB_0000941a;
  uVar5 = FUN_0000042c(*SUB_00020b28);
  if (*(short *)(SUB_000207fc + 0xd8) == 1) {
    uVar5 = uVar5 | 1;
    goto switchD_00009430_caseD_3;
  }
  if ((*(byte *)(SUB_00020b28 + 0x1a6) & 0x10) != 0) {
    if (((*(ushort *)(SUB_00020b28 + 0x1a6) & 0x3fe) == 0) &&
       ((*(char *)((int)SUB_00020b28 + 0x691) != '\0' ||
        (*(char *)((int)SUB_00020b28 + 0x692) != '\0')))) {
      *(undefined2 *)(SUB_000207fc + 0xd8) = 4;
    }
    else if ((uVar5 & 0x100) == 0) {
      if ((uVar5 & 0x200) != 0) {
        uVar9 = FUN_00013672();
        uVar9 = uVar9 & 7;
        goto joined_r0x0000918e;
      }
      uVar9 = FUN_00013672();
      if ((uVar9 & 3) != 0) {
        uVar9 = FUN_00013672();
        if ((uVar9 & 3) == 0) goto LAB_000091b8;
        goto LAB_000091c4;
      }
LAB_0000919e:
      *(undefined2 *)(SUB_000207fc + 0xd8) = 1;
    }
    else {
      uVar6 = FUN_00013672();
      if ((uVar6 & 1) == 0) {
LAB_000091b8:
        *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
      }
      else {
        uVar9 = FUN_00013672();
        uVar9 = uVar9 & 3;
joined_r0x0000918e:
        if (uVar9 == 0) goto LAB_0000919e;
      }
    }
LAB_000091c4:
    uVar5 = uVar5 | 6;
    goto switchD_00009430_caseD_3;
  }
  if (*(short *)(SUB_00020b28 + 0x1a0) == 0) {
    if (DAT_00021050 == 2) {
      DAT_00021050 = 0;
      _DAT_00020b2c = 0;
      if (((0 < *(short *)(SUB_000207fc + 0xd2)) && (uVar5 == 0x200)) &&
         (((*(char *)(SUB_00020b28 + 0xb) == '0' ||
           ((*(char *)(SUB_00020b28 + 0xb) == '`' || (*(char *)(SUB_00020b28 + 0xb) == 'p')))) &&
          (_DAT_00021052 == 0)))) {
        _DAT_00021052 = 1;
        uVar5 = 0x201;
        goto switchD_00009430_caseD_3;
      }
      if ((((*(char *)(SUB_00020b28 + 0xb) == '0') || (*(char *)(SUB_00020b28 + 0xb) == '`')) ||
          (*(char *)(SUB_00020b28 + 0xb) == 'p')) && (uVar5 == 0x100)) {
        *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
        uVar5 = 0x10a;
        goto switchD_00009430_caseD_3;
      }
      if (((*(char *)(SUB_00020b28 + 0xb) != '0') && (*(char *)(SUB_00020b28 + 0xb) != '`')) &&
         (*(char *)(SUB_00020b28 + 0xb) != 'p')) {
        if (((*(ushort *)(SUB_00020b28 + 0x1a6) & 0x3fe) != 0) ||
           ((*(char *)((int)SUB_00020b28 + 0x691) == '\0' &&
            (*(char *)((int)SUB_00020b28 + 0x692) == '\0')))) {
          if ((uVar5 & 0x100) == 0) {
            if ((uVar5 & 0x200) == 0) {
              uVar9 = FUN_00013672();
              if ((uVar9 & 3) == 0) goto LAB_0000931c;
              uVar9 = FUN_00013672();
              if ((uVar9 & 3) == 0) goto LAB_00009336;
            }
            else {
              iVar4 = FUN_000013e8(*SUB_00020b28);
              if (iVar4 == 0) {
                iVar4 = 1;
              }
              uVar9 = FUN_00013672();
              if (0x339000 / iVar4 < (int)(uint)(uVar9 & 0x7f)) {
LAB_0000931c:
                *(undefined2 *)(SUB_000207fc + 0xd8) = 5;
              }
            }
          }
          else {
            uVar6 = FUN_00013672();
            if ((uVar6 & 1) == 0) {
LAB_00009336:
              *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
            }
            else {
              uVar9 = FUN_00013672();
              if ((uVar9 & 3) == 0) goto LAB_0000931c;
            }
          }
          uVar5 = uVar5 | 8;
          goto switchD_00009430_caseD_3;
        }
        if (uVar5 == 0x100) {
          uVar9 = FUN_00013672();
          if ((uVar9 & 3) == 0) {
            *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
          }
        }
        else {
          iVar4 = FUN_00000408();
          if (iVar4 == 0) goto LAB_0000941a;
        }
      }
      uVar5 = uVar5 | 9;
      goto switchD_00009430_caseD_3;
    }
    if ((((*(char *)(SUB_00020b28 + 0xb) == '0') || (*(char *)(SUB_00020b28 + 0xb) == '`')) ||
        (*(char *)(SUB_00020b28 + 0xb) == 'p')) &&
       ((_DAT_00020b2c == 0 && ((*(byte *)(SUB_00020b28 + 0x1a6) & 0x40) != 0)))) {
      *(short *)(SUB_000207fc + 0xb6) =
           (short)((((int)*(char *)((int)SUB_00020b28 + 0x691) +
                    (int)*(char *)((int)SUB_00020b28 + 0x692)) * 100) /
                  (int)*(short *)(SUB_00020b28[1] + 0x1c));
      *(short *)(SUB_000207fc + 0xb8) = (short)*(char *)((int)SUB_00020b28 + 0x68d);
      _DAT_00020b2c = 1;
      uVar5 = uVar5 | 2;
      goto switchD_00009430_caseD_3;
    }
  }
LAB_0000941a:
  uVar5 = 0;
  switch(DAT_00023d88) {
  case 1:
  case 2:
    FUN_000083fe();
    piVar14 = SUB_00020b28;
    if ((DAT_00023d88 == 1) && ((DAT_0002b234 != 0 || (DAT_0002b238 != 0)))) {
      DAT_00023d88 = 2;
      if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
        puVar1 = (uint *)(*SUB_00020b28 + 0x14);
        *puVar1 = *puVar1 | 0x40;
        FUN_00016136(*piVar14);
      }
      FUN_00007f32();
    }
    if (DAT_00023d88 == 2) {
      if (*(short *)(SUB_000207fc + 0xa0) == 0) {
        DAT_00023d34 = DAT_0002b234;
        DAT_00023d30 = DAT_0002b238;
      }
      else {
        sVar10 = FUN_0000f87c(DAT_0002b234 - DAT_00023d34);
        if (sVar10 < 0) {
          sVar10 = sVar10 + 3;
        }
        DAT_00023d34 = DAT_00023d34 + (int)(sVar10 >> 2) & 0x3fff;
        sVar10 = FUN_0000f87c(DAT_0002b238 - DAT_00023d30);
        if (sVar10 < 0) {
          sVar10 = sVar10 + 3;
        }
        DAT_00023d30 = DAT_00023d30 + (int)(sVar10 >> 2) & 0x3fff;
      }
      piVar14 = SUB_00020b28;
      if ((((DAT_0002b234 == 0) && (DAT_0002b238 == 0)) &&
          (((int)DAT_00023d34 < 0x21 || (0x3fdf < (int)DAT_00023d34)))) &&
         (((int)DAT_00023d30 < 0x21 || (0x3fdf < (int)DAT_00023d30)))) {
        DAT_00023d88 = 1;
        if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
          puVar1 = (uint *)(*SUB_00020b28 + 0x14);
          *puVar1 = *puVar1 | 0x40;
          FUN_00016136(*piVar14);
        }
        FUN_00007f32();
      }
    }
  default:
    goto switchD_00009430_caseD_3;
  case 5:
    iVar4 = *(short *)((int)SUB_00020b28 + 0x612) + -7;
    if (iVar4 < 0) {
      iVar4 = *(short *)((int)SUB_00020b28 + 0x612) + 0x44;
    }
    piVar14 = (int *)((int)SUB_00020b28 + iVar4 * 0x14 + 0x36);
    DAT_00023d68 = *piVar14;
    DAT_00023d64 = piVar14[1];
    DAT_00023d60 = piVar14[2];
    DAT_00023d40 = -*(short *)(piVar14 + 3) & 0x3fff;
    DAT_00023d3e = -*(short *)((int)piVar14 + 0xe) & 0x3fff;
    DAT_00023d3c = -*(short *)(piVar14 + 4) & 0x3fff;
    break;
  case 6:
    iVar4 = *SUB_00020b28;
    if (DAT_00020b3c != 0) {
      iVar7 = FUN_00000e72(DAT_00020b3c,&DAT_00023d54);
      if (iVar7 == 0) {
        DAT_00020b3c = 0;
      }
      else {
        DAT_00023d68 = DAT_00023d54;
        DAT_00023d64 = DAT_00023d58;
        DAT_00023d60 = DAT_00023d5c;
      }
    }
    iVar8 = FUN_000047d6(iVar4);
    iVar7 = 0;
    for (uVar6 = *(int *)(iVar4 + 0x20) + iVar8; (uVar6 & 0xffff8000) != 0; uVar6 = (int)uVar6 >> 1)
    {
      iVar7 = iVar7 + 1;
    }
    uVar6 = iVar7 + 1;
    iVar7 = *(int *)(iVar4 + 4) - DAT_00023d68 >> (uVar6 & 0x3f);
    iVar8 = *(int *)(iVar4 + 8) - DAT_00023d64 >> (uVar6 & 0x3f);
    iVar2 = *(int *)(iVar4 + 0xc) - DAT_00023d60 >> (uVar6 & 0x3f);
    iVar4 = iVar2 * iVar2 + iVar8 * iVar8 + iVar7 * iVar7;
    sVar10 = FUN_0000f7e0(iVar4);
    FUN_00002574(iVar7,iVar8,iVar2,(int)sVar10,iVar4,&uStack_24,&uStack_26);
    sVar10 = (*(code *)&LAB_0000f858_4)((int)(short)uStack_24 - DAT_0002b23c);
    iVar4 = (int)sVar10;
    sVar10 = (*(code *)&LAB_0000f858_4)((int)(short)uStack_26 - DAT_0002b240);
    iVar7 = (int)sVar10;
    iVar8 = FUN_000038e8(iVar4);
    if ((iVar8 < 0x1001) && (iVar8 = FUN_000038e8(iVar7), iVar8 < 0x1001)) {
      if (iVar4 < 0) {
        iVar4 = iVar4 + 3;
      }
      uVar6 = DAT_0002b23c + (iVar4 >> 2);
      if (iVar7 < 0) {
        iVar7 = iVar7 + 3;
      }
      uVar12 = DAT_0002b240 + (iVar7 >> 2);
    }
    else {
      uVar6 = (uint)uStack_24;
      uVar12 = (uint)uStack_26;
    }
    DAT_0002b240 = uVar12 & 0x3fff;
    DAT_0002b23c = uVar6 & 0x3fff;
    DAT_00023d40 = -DAT_0002b23c._2_2_ & 0x3fff;
    DAT_00023d3e = -DAT_0002b240._2_2_ & 0x3fff;
    DAT_00023d3c = 0;
    break;
  case 7:
    iVar4 = *(short *)(DAT_00021070 + 0x612) + -7;
    if (iVar4 < 0) {
      iVar4 = *(short *)(DAT_00021070 + 0x612) + 0x44;
    }
    piVar14 = (int *)(DAT_00021070 + iVar4 * 0x14 + 0x36);
    DAT_00023d68 = *piVar14;
    DAT_00023d64 = piVar14[1];
    DAT_00023d60 = piVar14[2];
    DAT_00023d40 = -*(short *)(piVar14 + 3) & 0x3fff;
    DAT_00023d3e = -*(short *)((int)piVar14 + 0xe) & 0x3fff;
    DAT_00023d3c = -*(short *)(piVar14 + 4) & 0x3fff;
    break;
  case 8:
    uVar6 = FUN_00009d72();
    goto LAB_0000968e;
  case 9:
    uVar6 = FUN_00009ab4();
LAB_0000968e:
    if (uVar6 != 0) {
      uVar5 = uVar6;
    }
    goto switchD_00009430_caseD_3;
  }
  DAT_00023d38 = 0;
switchD_00009430_caseD_3:
  if ((0x1000 < (short)DAT_00023d40) && ((short)DAT_00023d40 < 0x3000)) {
    if ((short)DAT_00023d40 < 0x2001) {
      sVar10 = 0x2000;
    }
    else {
      sVar10 = 0x6000;
    }
    DAT_00023d40 = sVar10 - DAT_00023d40;
    DAT_00023d3e = DAT_00023d3e + 0x2000 & 0x3fff;
  }
  if (uVar5 != 0) {
    *(undefined1 *)((int)SUB_00020b28 + 0x64f) = 0;
    _DAT_0002105a = 0;
    DAT_0002b232 = 0;
  }
  if (DAT_00023d88 != sVar3) {
    _DAT_00023d86 = 1;
  }
  DAT_00021070 = unaff_A5;
  return uVar5;
}



// FUN_00009090 @ 0x9090

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_00009090(void)

{
  uint *puVar1;
  int iVar2;
  uint uVar3;
  uint uVar4;
  ushort uVar8;
  int iVar5;
  int iVar6;
  int iVar7;
  short sVar9;
  uint unaff_D6;
  int *piVar10;
  short unaff_A3w;
  int unaff_A5;
  int unaff_A6;
  bool in_ZF;
  
  if (in_ZF) {
    unaff_D6 = 3;
    goto switchD_00009430_caseD_3;
  }
  FUN_0000a380();
  iVar5 = DAT_00021070;
  sVar9 = *(short *)(SUB_00020b28 + 0xc);
  if (DAT_00021058 == 3) {
    FUN_0000a63a();
  }
  else {
    FUN_0000a426();
  }
  unaff_A5 = DAT_00021070;
  if (DAT_00021070 == 0) {
    DAT_00021070 = iVar5;
  }
  FUN_0000a90a();
  if (((DAT_00023d88 < 8) && (*(char *)(SUB_00020b28 + 0x193) != '\0')) &&
     ((*(int *)((int)SUB_00020b28 + 0x62a) == 0 ||
      ((*(int *)((int)SUB_00020b28 + 0x62e) == 0 && (0 < sVar9)))))) {
    uVar3 = FUN_0000042c(*SUB_00020b28);
    if (*(short *)(SUB_000207fc + 0xd8) == 1) {
      unaff_D6 = uVar3 | 1;
      goto switchD_00009430_caseD_3;
    }
    if ((*(byte *)(SUB_00020b28 + 0x1a6) & 0x10) != 0) {
      if (((*(ushort *)(SUB_00020b28 + 0x1a6) & 0x3fe) == 0) &&
         ((*(char *)((int)SUB_00020b28 + 0x691) != '\0' ||
          (*(char *)((int)SUB_00020b28 + 0x692) != '\0')))) {
        *(undefined2 *)(SUB_000207fc + 0xd8) = 4;
      }
      else if ((uVar3 & 0x100) == 0) {
        if ((uVar3 & 0x200) != 0) {
          uVar8 = FUN_00013672();
          uVar8 = uVar8 & 7;
          goto joined_r0x0000918e;
        }
        uVar8 = FUN_00013672();
        if ((uVar8 & 3) != 0) {
          uVar8 = FUN_00013672();
          if ((uVar8 & 3) == 0) goto LAB_000091b8;
          goto LAB_000091c4;
        }
LAB_0000919e:
        *(undefined2 *)(SUB_000207fc + 0xd8) = 1;
      }
      else {
        uVar4 = FUN_00013672();
        if ((uVar4 & 1) == 0) {
LAB_000091b8:
          *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
        }
        else {
          uVar8 = FUN_00013672();
          uVar8 = uVar8 & 3;
joined_r0x0000918e:
          if (uVar8 == 0) goto LAB_0000919e;
        }
      }
LAB_000091c4:
      unaff_D6 = uVar3 | 6;
      goto switchD_00009430_caseD_3;
    }
    if (*(short *)(SUB_00020b28 + 0x1a0) == 0) {
      if (DAT_00021050 == 2) {
        DAT_00021050 = 0;
        _DAT_00020b2c = 0;
        if (((0 < *(short *)(SUB_000207fc + 0xd2)) && (uVar3 == 0x200)) &&
           (((*(char *)(SUB_00020b28 + 0xb) == '0' ||
             ((*(char *)(SUB_00020b28 + 0xb) == '`' || (*(char *)(SUB_00020b28 + 0xb) == 'p')))) &&
            (_DAT_00021052 == 0)))) {
          _DAT_00021052 = 1;
          unaff_D6 = 0x201;
          goto switchD_00009430_caseD_3;
        }
        if ((((*(char *)(SUB_00020b28 + 0xb) == '0') || (*(char *)(SUB_00020b28 + 0xb) == '`')) ||
            (*(char *)(SUB_00020b28 + 0xb) == 'p')) && (uVar3 == 0x100)) {
          *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
          unaff_D6 = 0x10a;
          goto switchD_00009430_caseD_3;
        }
        if (((*(char *)(SUB_00020b28 + 0xb) != '0') && (*(char *)(SUB_00020b28 + 0xb) != '`')) &&
           (*(char *)(SUB_00020b28 + 0xb) != 'p')) {
          if (((*(ushort *)(SUB_00020b28 + 0x1a6) & 0x3fe) != 0) ||
             ((*(char *)((int)SUB_00020b28 + 0x691) == '\0' &&
              (*(char *)((int)SUB_00020b28 + 0x692) == '\0')))) {
            if ((uVar3 & 0x100) == 0) {
              if ((uVar3 & 0x200) == 0) {
                uVar8 = FUN_00013672();
                if ((uVar8 & 3) == 0) goto LAB_0000931c;
                uVar8 = FUN_00013672();
                if ((uVar8 & 3) == 0) goto LAB_00009336;
              }
              else {
                iVar5 = FUN_000013e8(*SUB_00020b28);
                if (iVar5 == 0) {
                  iVar5 = 1;
                }
                uVar8 = FUN_00013672();
                if (0x339000 / iVar5 < (int)(uint)(uVar8 & 0x7f)) {
LAB_0000931c:
                  *(undefined2 *)(SUB_000207fc + 0xd8) = 5;
                }
              }
            }
            else {
              uVar4 = FUN_00013672();
              if ((uVar4 & 1) == 0) {
LAB_00009336:
                *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
              }
              else {
                uVar8 = FUN_00013672();
                if ((uVar8 & 3) == 0) goto LAB_0000931c;
              }
            }
            unaff_D6 = uVar3 | 8;
            goto switchD_00009430_caseD_3;
          }
          if (uVar3 == 0x100) {
            uVar8 = FUN_00013672();
            if ((uVar8 & 3) == 0) {
              *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
            }
          }
          else {
            iVar5 = FUN_00000408();
            if (iVar5 == 0) goto LAB_00009418;
          }
        }
        unaff_D6 = uVar3 | 9;
        goto switchD_00009430_caseD_3;
      }
      if ((((*(char *)(SUB_00020b28 + 0xb) == '0') || (*(char *)(SUB_00020b28 + 0xb) == '`')) ||
          (*(char *)(SUB_00020b28 + 0xb) == 'p')) &&
         ((_DAT_00020b2c == 0 && ((*(byte *)(SUB_00020b28 + 0x1a6) & 0x40) != 0)))) {
        *(short *)(SUB_000207fc + 0xb6) =
             (short)((((int)*(char *)((int)SUB_00020b28 + 0x691) +
                      (int)*(char *)((int)SUB_00020b28 + 0x692)) * 100) /
                    (int)*(short *)(SUB_00020b28[1] + 0x1c));
        *(short *)(SUB_000207fc + 0xb8) = (short)*(char *)((int)SUB_00020b28 + 0x68d);
        _DAT_00020b2c = 1;
        unaff_D6 = uVar3 | 2;
        goto switchD_00009430_caseD_3;
      }
    }
LAB_00009418:
    unaff_D6 = 0;
  }
  switch(DAT_00023d88) {
  case 1:
  case 2:
    FUN_000083fe();
    piVar10 = SUB_00020b28;
    if ((DAT_00023d88 == 1) && ((DAT_0002b234 != 0 || (DAT_0002b238 != 0)))) {
      DAT_00023d88 = 2;
      if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
        puVar1 = (uint *)(*SUB_00020b28 + 0x14);
        *puVar1 = *puVar1 | 0x40;
        FUN_00016136(*piVar10);
      }
      FUN_00007f32();
    }
    if (DAT_00023d88 == 2) {
      if (*(short *)(SUB_000207fc + 0xa0) == 0) {
        DAT_00023d34 = DAT_0002b234;
        DAT_00023d30 = DAT_0002b238;
      }
      else {
        sVar9 = FUN_0000f87c(DAT_0002b234 - DAT_00023d34);
        if (sVar9 < 0) {
          sVar9 = sVar9 + 3;
        }
        DAT_00023d34 = DAT_00023d34 + (int)(sVar9 >> 2) & 0x3fff;
        sVar9 = FUN_0000f87c(DAT_0002b238 - DAT_00023d30);
        if (sVar9 < 0) {
          sVar9 = sVar9 + 3;
        }
        DAT_00023d30 = DAT_00023d30 + (int)(sVar9 >> 2) & 0x3fff;
      }
      piVar10 = SUB_00020b28;
      if ((((DAT_0002b234 == 0) && (DAT_0002b238 == 0)) &&
          (((int)DAT_00023d34 < 0x21 || (0x3fdf < (int)DAT_00023d34)))) &&
         (((int)DAT_00023d30 < 0x21 || (0x3fdf < (int)DAT_00023d30)))) {
        DAT_00023d88 = 1;
        if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
          puVar1 = (uint *)(*SUB_00020b28 + 0x14);
          *puVar1 = *puVar1 | 0x40;
          FUN_00016136(*piVar10);
        }
        FUN_00007f32();
      }
    }
  default:
    goto switchD_00009430_caseD_3;
  case 5:
    iVar5 = *(short *)((int)SUB_00020b28 + 0x612) + -7;
    if (iVar5 < 0) {
      iVar5 = *(short *)((int)SUB_00020b28 + 0x612) + 0x44;
    }
    piVar10 = (int *)((int)SUB_00020b28 + iVar5 * 0x14 + 0x36);
    DAT_00023d68 = *piVar10;
    DAT_00023d64 = piVar10[1];
    DAT_00023d60 = piVar10[2];
    DAT_00023d40 = -*(short *)(piVar10 + 3) & 0x3fff;
    DAT_00023d3e = -*(short *)((int)piVar10 + 0xe) & 0x3fff;
    DAT_00023d3c = -*(short *)(piVar10 + 4) & 0x3fff;
    break;
  case 6:
    iVar5 = *SUB_00020b28;
    if (DAT_00020b3c != 0) {
      iVar6 = FUN_00000e72(DAT_00020b3c,&DAT_00023d54);
      if (iVar6 == 0) {
        DAT_00020b3c = 0;
      }
      else {
        DAT_00023d68 = DAT_00023d54;
        DAT_00023d64 = DAT_00023d58;
        DAT_00023d60 = DAT_00023d5c;
      }
    }
    iVar7 = FUN_000047d6(iVar5);
    iVar6 = 0;
    for (uVar3 = *(int *)(iVar5 + 0x20) + iVar7; (uVar3 & 0xffff8000) != 0; uVar3 = (int)uVar3 >> 1)
    {
      iVar6 = iVar6 + 1;
    }
    uVar3 = iVar6 + 1;
    iVar6 = *(int *)(iVar5 + 4) - DAT_00023d68 >> (uVar3 & 0x3f);
    iVar7 = *(int *)(iVar5 + 8) - DAT_00023d64 >> (uVar3 & 0x3f);
    iVar2 = *(int *)(iVar5 + 0xc) - DAT_00023d60 >> (uVar3 & 0x3f);
    iVar5 = iVar2 * iVar2 + iVar7 * iVar7 + iVar6 * iVar6;
    sVar9 = FUN_0000f7e0(iVar5);
    FUN_00002574(iVar6,iVar7,iVar2,(int)sVar9,iVar5,unaff_A6 + -0x20,unaff_A6 + -0x22);
    sVar9 = (*(code *)&LAB_0000f858_4)((int)*(short *)(unaff_A6 + -0x20) - DAT_0002b23c);
    iVar5 = (int)sVar9;
    sVar9 = (*(code *)&LAB_0000f858_4)((int)*(short *)(unaff_A6 + -0x22) - DAT_0002b240);
    iVar6 = (int)sVar9;
    iVar7 = FUN_000038e8(iVar5);
    if ((iVar7 < 0x1001) && (iVar7 = FUN_000038e8(iVar6), iVar7 < 0x1001)) {
      if (iVar5 < 0) {
        iVar5 = iVar5 + 3;
      }
      uVar3 = DAT_0002b23c + (iVar5 >> 2);
      if (iVar6 < 0) {
        iVar6 = iVar6 + 3;
      }
      uVar4 = DAT_0002b240 + (iVar6 >> 2);
    }
    else {
      uVar3 = (uint)*(ushort *)(unaff_A6 + -0x20);
      uVar4 = (uint)*(ushort *)(unaff_A6 + -0x22);
    }
    DAT_0002b240 = uVar4 & 0x3fff;
    DAT_0002b23c = uVar3 & 0x3fff;
    DAT_00023d40 = -DAT_0002b23c._2_2_ & 0x3fff;
    DAT_00023d3e = -DAT_0002b240._2_2_ & 0x3fff;
    DAT_00023d3c = 0;
    break;
  case 7:
    iVar5 = *(short *)(DAT_00021070 + 0x612) + -7;
    if (iVar5 < 0) {
      iVar5 = *(short *)(DAT_00021070 + 0x612) + 0x44;
    }
    piVar10 = (int *)(DAT_00021070 + iVar5 * 0x14 + 0x36);
    DAT_00023d68 = *piVar10;
    DAT_00023d64 = piVar10[1];
    DAT_00023d60 = piVar10[2];
    DAT_00023d40 = -*(short *)(piVar10 + 3) & 0x3fff;
    DAT_00023d3e = -*(short *)((int)piVar10 + 0xe) & 0x3fff;
    DAT_00023d3c = -*(short *)(piVar10 + 4) & 0x3fff;
    break;
  case 8:
    uVar3 = FUN_00009d72();
    goto LAB_0000968e;
  case 9:
    uVar3 = FUN_00009ab4();
LAB_0000968e:
    if (uVar3 != 0) {
      unaff_D6 = uVar3;
    }
    goto switchD_00009430_caseD_3;
  }
  DAT_00023d38 = 0;
switchD_00009430_caseD_3:
  if ((0x1000 < (short)DAT_00023d40) && ((short)DAT_00023d40 < 0x3000)) {
    if ((short)DAT_00023d40 < 0x2001) {
      sVar9 = 0x2000;
    }
    else {
      sVar9 = 0x6000;
    }
    DAT_00023d40 = sVar9 - DAT_00023d40;
    DAT_00023d3e = DAT_00023d3e + 0x2000 & 0x3fff;
  }
  if (unaff_D6 != 0) {
    *(undefined1 *)((int)SUB_00020b28 + 0x64f) = 0;
    _DAT_0002105a = 0;
    DAT_0002b232 = 0;
  }
  if (DAT_00023d88 != unaff_A3w) {
    _DAT_00023d86 = 1;
  }
  DAT_00021070 = unaff_A5;
  return unaff_D6;
}



// FUN_000090a2 @ 0x90a2

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_000090a2(void)

{
  uint *puVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  uint uVar5;
  ushort uVar9;
  int iVar6;
  int iVar7;
  int iVar8;
  short sVar10;
  uint unaff_D6;
  int in_A0;
  int *piVar11;
  short unaff_A3w;
  int unaff_A6;
  
  iVar6 = DAT_00021070;
  sVar10 = *(short *)(in_A0 + 0x30);
  if (DAT_00021058 == 3) {
    FUN_0000a63a();
  }
  else {
    FUN_0000a426();
  }
  iVar3 = DAT_00021070;
  if (DAT_00021070 == 0) {
    DAT_00021070 = iVar6;
  }
  FUN_0000a90a();
  if (((DAT_00023d88 < 8) && (*(char *)(SUB_00020b28 + 0x193) != '\0')) &&
     ((*(int *)((int)SUB_00020b28 + 0x62a) == 0 ||
      ((*(int *)((int)SUB_00020b28 + 0x62e) == 0 && (0 < sVar10)))))) {
    uVar4 = FUN_0000042c(*SUB_00020b28);
    if (*(short *)(SUB_000207fc + 0xd8) == 1) {
      unaff_D6 = uVar4 | 1;
      goto switchD_00009430_caseD_3;
    }
    if ((*(byte *)(SUB_00020b28 + 0x1a6) & 0x10) != 0) {
      if (((*(ushort *)(SUB_00020b28 + 0x1a6) & 0x3fe) == 0) &&
         ((*(char *)((int)SUB_00020b28 + 0x691) != '\0' ||
          (*(char *)((int)SUB_00020b28 + 0x692) != '\0')))) {
        *(undefined2 *)(SUB_000207fc + 0xd8) = 4;
      }
      else if ((uVar4 & 0x100) == 0) {
        if ((uVar4 & 0x200) != 0) {
          uVar9 = FUN_00013672();
          uVar9 = uVar9 & 7;
          goto joined_r0x0000918e;
        }
        uVar9 = FUN_00013672();
        if ((uVar9 & 3) != 0) {
          uVar9 = FUN_00013672();
          if ((uVar9 & 3) == 0) goto LAB_000091b8;
          goto LAB_000091c4;
        }
LAB_0000919e:
        *(undefined2 *)(SUB_000207fc + 0xd8) = 1;
      }
      else {
        uVar5 = FUN_00013672();
        if ((uVar5 & 1) == 0) {
LAB_000091b8:
          *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
        }
        else {
          uVar9 = FUN_00013672();
          uVar9 = uVar9 & 3;
joined_r0x0000918e:
          if (uVar9 == 0) goto LAB_0000919e;
        }
      }
LAB_000091c4:
      unaff_D6 = uVar4 | 6;
      goto switchD_00009430_caseD_3;
    }
    if (*(short *)(SUB_00020b28 + 0x1a0) == 0) {
      if (DAT_00021050 == 2) {
        DAT_00021050 = 0;
        _DAT_00020b2c = 0;
        if (((0 < *(short *)(SUB_000207fc + 0xd2)) && (uVar4 == 0x200)) &&
           (((*(char *)(SUB_00020b28 + 0xb) == '0' ||
             ((*(char *)(SUB_00020b28 + 0xb) == '`' || (*(char *)(SUB_00020b28 + 0xb) == 'p')))) &&
            (_DAT_00021052 == 0)))) {
          _DAT_00021052 = 1;
          unaff_D6 = 0x201;
          goto switchD_00009430_caseD_3;
        }
        if ((((*(char *)(SUB_00020b28 + 0xb) == '0') || (*(char *)(SUB_00020b28 + 0xb) == '`')) ||
            (*(char *)(SUB_00020b28 + 0xb) == 'p')) && (uVar4 == 0x100)) {
          *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
          unaff_D6 = 0x10a;
          goto switchD_00009430_caseD_3;
        }
        if (((*(char *)(SUB_00020b28 + 0xb) != '0') && (*(char *)(SUB_00020b28 + 0xb) != '`')) &&
           (*(char *)(SUB_00020b28 + 0xb) != 'p')) {
          if (((*(ushort *)(SUB_00020b28 + 0x1a6) & 0x3fe) != 0) ||
             ((*(char *)((int)SUB_00020b28 + 0x691) == '\0' &&
              (*(char *)((int)SUB_00020b28 + 0x692) == '\0')))) {
            if ((uVar4 & 0x100) == 0) {
              if ((uVar4 & 0x200) == 0) {
                uVar9 = FUN_00013672();
                if ((uVar9 & 3) == 0) goto LAB_0000931c;
                uVar9 = FUN_00013672();
                if ((uVar9 & 3) == 0) goto LAB_00009336;
              }
              else {
                iVar6 = FUN_000013e8(*SUB_00020b28);
                if (iVar6 == 0) {
                  iVar6 = 1;
                }
                uVar9 = FUN_00013672();
                if (0x339000 / iVar6 < (int)(uint)(uVar9 & 0x7f)) {
LAB_0000931c:
                  *(undefined2 *)(SUB_000207fc + 0xd8) = 5;
                }
              }
            }
            else {
              uVar5 = FUN_00013672();
              if ((uVar5 & 1) == 0) {
LAB_00009336:
                *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
              }
              else {
                uVar9 = FUN_00013672();
                if ((uVar9 & 3) == 0) goto LAB_0000931c;
              }
            }
            unaff_D6 = uVar4 | 8;
            goto switchD_00009430_caseD_3;
          }
          if (uVar4 == 0x100) {
            uVar9 = FUN_00013672();
            if ((uVar9 & 3) == 0) {
              *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
            }
          }
          else {
            iVar6 = FUN_00000408();
            if (iVar6 == 0) goto LAB_00009418;
          }
        }
        unaff_D6 = uVar4 | 9;
        goto switchD_00009430_caseD_3;
      }
      if ((((*(char *)(SUB_00020b28 + 0xb) == '0') || (*(char *)(SUB_00020b28 + 0xb) == '`')) ||
          (*(char *)(SUB_00020b28 + 0xb) == 'p')) &&
         ((_DAT_00020b2c == 0 && ((*(byte *)(SUB_00020b28 + 0x1a6) & 0x40) != 0)))) {
        *(short *)(SUB_000207fc + 0xb6) =
             (short)((((int)*(char *)((int)SUB_00020b28 + 0x691) +
                      (int)*(char *)((int)SUB_00020b28 + 0x692)) * 100) /
                    (int)*(short *)(SUB_00020b28[1] + 0x1c));
        *(short *)(SUB_000207fc + 0xb8) = (short)*(char *)((int)SUB_00020b28 + 0x68d);
        _DAT_00020b2c = 1;
        unaff_D6 = uVar4 | 2;
        goto switchD_00009430_caseD_3;
      }
    }
LAB_00009418:
    unaff_D6 = 0;
  }
  switch(DAT_00023d88) {
  case 1:
  case 2:
    FUN_000083fe();
    piVar11 = SUB_00020b28;
    if ((DAT_00023d88 == 1) && ((DAT_0002b234 != 0 || (DAT_0002b238 != 0)))) {
      DAT_00023d88 = 2;
      if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
        puVar1 = (uint *)(*SUB_00020b28 + 0x14);
        *puVar1 = *puVar1 | 0x40;
        FUN_00016136(*piVar11);
      }
      FUN_00007f32();
    }
    if (DAT_00023d88 == 2) {
      if (*(short *)(SUB_000207fc + 0xa0) == 0) {
        DAT_00023d34 = DAT_0002b234;
        DAT_00023d30 = DAT_0002b238;
      }
      else {
        sVar10 = FUN_0000f87c(DAT_0002b234 - DAT_00023d34);
        if (sVar10 < 0) {
          sVar10 = sVar10 + 3;
        }
        DAT_00023d34 = DAT_00023d34 + (int)(sVar10 >> 2) & 0x3fff;
        sVar10 = FUN_0000f87c(DAT_0002b238 - DAT_00023d30);
        if (sVar10 < 0) {
          sVar10 = sVar10 + 3;
        }
        DAT_00023d30 = DAT_00023d30 + (int)(sVar10 >> 2) & 0x3fff;
      }
      piVar11 = SUB_00020b28;
      if ((((DAT_0002b234 == 0) && (DAT_0002b238 == 0)) &&
          (((int)DAT_00023d34 < 0x21 || (0x3fdf < (int)DAT_00023d34)))) &&
         (((int)DAT_00023d30 < 0x21 || (0x3fdf < (int)DAT_00023d30)))) {
        DAT_00023d88 = 1;
        if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) == 0) {
          puVar1 = (uint *)(*SUB_00020b28 + 0x14);
          *puVar1 = *puVar1 | 0x40;
          FUN_00016136(*piVar11);
        }
        FUN_00007f32();
      }
    }
  default:
    goto switchD_00009430_caseD_3;
  case 5:
    iVar6 = *(short *)((int)SUB_00020b28 + 0x612) + -7;
    if (iVar6 < 0) {
      iVar6 = *(short *)((int)SUB_00020b28 + 0x612) + 0x44;
    }
    piVar11 = (int *)((int)SUB_00020b28 + iVar6 * 0x14 + 0x36);
    DAT_00023d68 = *piVar11;
    DAT_00023d64 = piVar11[1];
    DAT_00023d60 = piVar11[2];
    DAT_00023d40 = -*(short *)(piVar11 + 3) & 0x3fff;
    DAT_00023d3e = -*(short *)((int)piVar11 + 0xe) & 0x3fff;
    DAT_00023d3c = -*(short *)(piVar11 + 4) & 0x3fff;
    break;
  case 6:
    iVar6 = *SUB_00020b28;
    if (DAT_00020b3c != 0) {
      iVar7 = FUN_00000e72(DAT_00020b3c,&DAT_00023d54);
      if (iVar7 == 0) {
        DAT_00020b3c = 0;
      }
      else {
        DAT_00023d68 = DAT_00023d54;
        DAT_00023d64 = DAT_00023d58;
        DAT_00023d60 = DAT_00023d5c;
      }
    }
    iVar8 = FUN_000047d6(iVar6);
    iVar7 = 0;
    for (uVar4 = *(int *)(iVar6 + 0x20) + iVar8; (uVar4 & 0xffff8000) != 0; uVar4 = (int)uVar4 >> 1)
    {
      iVar7 = iVar7 + 1;
    }
    uVar4 = iVar7 + 1;
    iVar7 = *(int *)(iVar6 + 4) - DAT_00023d68 >> (uVar4 & 0x3f);
    iVar8 = *(int *)(iVar6 + 8) - DAT_00023d64 >> (uVar4 & 0x3f);
    iVar2 = *(int *)(iVar6 + 0xc) - DAT_00023d60 >> (uVar4 & 0x3f);
    iVar6 = iVar2 * iVar2 + iVar8 * iVar8 + iVar7 * iVar7;
    sVar10 = FUN_0000f7e0(iVar6);
    FUN_00002574(iVar7,iVar8,iVar2,(int)sVar10,iVar6,unaff_A6 + -0x20,unaff_A6 + -0x22);
    sVar10 = (*(code *)&LAB_0000f858_4)((int)*(short *)(unaff_A6 + -0x20) - DAT_0002b23c);
    iVar6 = (int)sVar10;
    sVar10 = (*(code *)&LAB_0000f858_4)((int)*(short *)(unaff_A6 + -0x22) - DAT_0002b240);
    iVar7 = (int)sVar10;
    iVar8 = FUN_000038e8(iVar6);
    if ((iVar8 < 0x1001) && (iVar8 = FUN_000038e8(iVar7), iVar8 < 0x1001)) {
      if (iVar6 < 0) {
        iVar6 = iVar6 + 3;
      }
      uVar4 = DAT_0002b23c + (iVar6 >> 2);
      if (iVar7 < 0) {
        iVar7 = iVar7 + 3;
      }
      uVar5 = DAT_0002b240 + (iVar7 >> 2);
    }
    else {
      uVar4 = (uint)*(ushort *)(unaff_A6 + -0x20);
      uVar5 = (uint)*(ushort *)(unaff_A6 + -0x22);
    }
    DAT_0002b240 = uVar5 & 0x3fff;
    DAT_0002b23c = uVar4 & 0x3fff;
    DAT_00023d40 = -DAT_0002b23c._2_2_ & 0x3fff;
    DAT_00023d3e = -DAT_0002b240._2_2_ & 0x3fff;
    DAT_00023d3c = 0;
    break;
  case 7:
    iVar6 = *(short *)(DAT_00021070 + 0x612) + -7;
    if (iVar6 < 0) {
      iVar6 = *(short *)(DAT_00021070 + 0x612) + 0x44;
    }
    piVar11 = (int *)(DAT_00021070 + iVar6 * 0x14 + 0x36);
    DAT_00023d68 = *piVar11;
    DAT_00023d64 = piVar11[1];
    DAT_00023d60 = piVar11[2];
    DAT_00023d40 = -*(short *)(piVar11 + 3) & 0x3fff;
    DAT_00023d3e = -*(short *)((int)piVar11 + 0xe) & 0x3fff;
    DAT_00023d3c = -*(short *)(piVar11 + 4) & 0x3fff;
    break;
  case 8:
    uVar4 = FUN_00009d72();
    goto LAB_0000968e;
  case 9:
    uVar4 = FUN_00009ab4();
LAB_0000968e:
    if (uVar4 != 0) {
      unaff_D6 = uVar4;
    }
    goto switchD_00009430_caseD_3;
  }
  DAT_00023d38 = 0;
switchD_00009430_caseD_3:
  if ((0x1000 < (short)DAT_00023d40) && ((short)DAT_00023d40 < 0x3000)) {
    if ((short)DAT_00023d40 < 0x2001) {
      sVar10 = 0x2000;
    }
    else {
      sVar10 = 0x6000;
    }
    DAT_00023d40 = sVar10 - DAT_00023d40;
    DAT_00023d3e = DAT_00023d3e + 0x2000 & 0x3fff;
  }
  if (unaff_D6 != 0) {
    *(undefined1 *)((int)SUB_00020b28 + 0x64f) = 0;
    _DAT_0002105a = 0;
    DAT_0002b232 = 0;
  }
  if (DAT_00023d88 != unaff_A3w) {
    _DAT_00023d86 = 1;
  }
  DAT_00021070 = iVar3;
  return unaff_D6;
}



// FUN_000098c6 @ 0x98c6

void FUN_000098c6(int param_1)

{
  if (param_1 == 0) {
    DAT_00021054 = 2;
  }
  else {
    DAT_00021054 = 1;
  }
  return;
}



// FUN_000098e0 @ 0x98e0

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_000098e0(void)

{
  int iVar1;
  uint uVar2;
  uint uVar3;
  
  uVar2 = FUN_0000042c(*SUB_00020b28);
  iVar1 = SUB_000207fc;
  uVar3 = uVar2 | 7;
  *(undefined2 *)(SUB_000207fc + 0xda) = 1;
  if (((DAT_00021064 != (int *)0x0) && (*(int *)(*DAT_00021064 + 8) < 0x373)) &&
     (0x5a0 < *(short *)(DAT_00021064 + 0x185))) {
    *(undefined2 *)(iVar1 + 0xd8) = 1;
    uVar3 = CONCAT22((short)(uVar2 >> 0x10),(short)uVar3) & 0xffffff00 | 1;
  }
  if (*(short *)(SUB_000207fc + 0xd8) != 0) goto LAB_000099a8;
  if ((uVar3 & 0x100) == 0) {
    if ((uVar3 & 0x200) != 0) {
      uVar2 = FUN_00013672();
      uVar2 = uVar2 & 7;
      goto joined_r0x00009972;
    }
    uVar2 = FUN_00013672();
    if ((uVar2 & 3) != 0) {
      uVar2 = FUN_00013672();
      if ((uVar2 & 3) != 0) goto LAB_000099a8;
      goto LAB_0000999c;
    }
  }
  else {
    uVar2 = FUN_00013672();
    if ((uVar2 & 1) == 0) {
LAB_0000999c:
      *(undefined2 *)(SUB_000207fc + 0xd8) = 3;
      goto LAB_000099a8;
    }
    uVar2 = FUN_00013672();
    uVar2 = uVar2 & 3;
joined_r0x00009972:
    if (uVar2 != 0) goto LAB_000099a8;
  }
  *(undefined2 *)(SUB_000207fc + 0xd8) = 1;
LAB_000099a8:
  if (DAT_00021064 != (int *)0x0) {
    FUN_000060fc(*DAT_00021064);
  }
  if ((_DAT_00021062 != 0) && (*(short *)(SUB_000207fc + 0xd8) == 0)) {
    *(undefined2 *)(SUB_000207fc + 0xd8) = 4;
  }
  return uVar3;
}



// FUN_000099e2 @ 0x99e2

void FUN_000099e2(int param_1,int param_2,int param_3,int param_4,short param_5)

{
  uint uVar1;
  short sVar3;
  int iVar2;
  short sVar4;
  short sVar5;
  short sVar6;
  
  uVar1 = (uint)param_5;
  sVar4 = (short)(*(int *)(param_1 + 4) - param_2 >> (uVar1 & 0x3f));
  sVar5 = (short)(*(int *)(param_1 + 8) - param_3 >> (uVar1 & 0x3f));
  sVar6 = (short)(*(int *)(param_1 + 0xc) - param_4 >> (uVar1 & 0x3f));
  sVar3 = FUN_0000f7e0((int)sVar5 * (int)sVar5 + (int)sVar4 * (int)sVar4 + (int)sVar6 * (int)sVar6);
  if (sVar3 == 0) {
    DAT_00023d68 = param_2;
    DAT_00023d64 = param_3;
    DAT_00023d60 = param_4;
  }
  else {
    iVar2 = (int)sVar3;
    DAT_00023d68 = *(int *)(param_1 + 4) + (sVar4 * 1000) / iVar2 + -0xfa;
    DAT_00023d64 = *(int *)(param_1 + 8) + (sVar5 * 1000) / iVar2 + 0xfa;
    DAT_00023d60 = *(int *)(param_1 + 0xc) + (sVar6 * 1000) / iVar2;
  }
  if (DAT_00023d64 < 0xfa) {
    DAT_00023d64 = 0xfa;
  }
  return;
}



// FUN_00009ab4 @ 0x9ab4

undefined4 FUN_00009ab4(void)

{
  int iVar1;
  undefined4 uVar2;
  uint uVar3;
  short sVar4;
  ushort uVar5;
  int iVar6;
  int iVar7;
  int iVar8;
  undefined4 *puVar9;
  
  if (((DAT_00021064 == (int *)0x0) || ((*(byte *)(*DAT_00021064 + 0x17) & 2) != 0)) ||
     ((*(short *)((int)DAT_00021064 + 0x61e) != 0 && (0x5a0 < *(short *)(DAT_00021064 + 0x185))))) {
    uVar2 = FUN_000098e0();
    return uVar2;
  }
  iVar1 = *DAT_00021064;
  if ((SUB_00020b28 != (int *)0x0) &&
     ((DAT_00021020 + -0x4b <= *(int *)(iVar1 + 0x42) ||
      (0x373 < *(int *)(iVar1 + 8) - *(int *)((int)DAT_00021064 + 0x2e))))) {
    iVar6 = *SUB_00020b28;
    uVar3 = FUN_000048a6(*(undefined4 *)(iVar6 + 4),*(undefined4 *)(iVar6 + 8),
                         *(undefined4 *)(iVar6 + 0xc),*(undefined4 *)(iVar1 + 4),
                         *(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc));
    if ((int)uVar3 < 0x9ab1) {
      iVar8 = *(int *)(iVar1 + 4) + *(int *)(iVar6 + 4);
      if (iVar8 < 0) {
        iVar8 = iVar8 + 1;
      }
      iVar8 = iVar8 >> 1;
      iVar7 = *(int *)(iVar1 + 8) + *(int *)(iVar6 + 8);
      if (iVar7 < 0) {
        iVar7 = iVar7 + 1;
      }
      iVar7 = iVar7 >> 1;
      iVar6 = *(int *)(iVar1 + 0xc) + *(int *)(iVar6 + 0xc);
      if (iVar6 < 0) {
        iVar6 = iVar6 + 1;
      }
      iVar6 = iVar6 >> 1;
      sVar4 = 0;
      for (; (uVar3 & 0xffff8000) != 0; uVar3 = (int)uVar3 >> 1) {
        sVar4 = sVar4 + 1;
      }
      FUN_000099e2(iVar1,iVar8,iVar7,iVar6,(int)sVar4);
      goto LAB_00009bec;
    }
  }
  iVar8 = *(int *)(iVar1 + 4);
  iVar7 = *(int *)(iVar1 + 8);
  iVar6 = *(int *)(iVar1 + 0xc);
  uVar5 = *(short *)((int)DAT_00021064 + 0x60e) + 1;
  if (0x4a < uVar5) {
    uVar5 = *(short *)((int)DAT_00021064 + 0x60e) - 0x4a;
  }
  puVar9 = (undefined4 *)((int)DAT_00021064 + (short)uVar5 * 0x14 + 0x32);
  DAT_00023d68 = *puVar9;
  DAT_00023d64 = puVar9[1];
  DAT_00023d60 = puVar9[2];
LAB_00009bec:
  FUN_00009c00(iVar8,iVar7,iVar6);
  return 0;
}



// FUN_00009c00 @ 0x9c00

void FUN_00009c00(int param_1,int param_2,int param_3)

{
  int iVar1;
  uint uVar2;
  short sVar4;
  int iVar3;
  int iVar5;
  int iVar6;
  ushort local_8;
  ushort local_6;
  
  uVar2 = FUN_0000483e(param_1,param_2,param_3);
  sVar4 = 0;
  for (; (uVar2 & 0xffff8000) != 0; uVar2 = (int)uVar2 >> 1) {
    sVar4 = sVar4 + 1;
  }
  uVar2 = (uint)(short)(sVar4 + 1);
  iVar6 = param_1 - DAT_00023d68 >> (uVar2 & 0x3f);
  iVar3 = param_2 - DAT_00023d64 >> (uVar2 & 0x3f);
  iVar1 = param_3 - DAT_00023d60 >> (uVar2 & 0x3f);
  iVar5 = iVar1 * iVar1 + iVar3 * iVar3 + iVar6 * iVar6;
  sVar4 = FUN_0000f7e0(iVar5);
  FUN_00002574(iVar6,iVar3,iVar1,(int)sVar4,iVar5,&local_6,&local_8);
  sVar4 = (*(code *)&LAB_0000f858_4)((int)(short)local_6 - (int)(short)DAT_0002b256);
  iVar5 = (int)sVar4;
  sVar4 = (*(code *)&LAB_0000f858_4)((int)(short)local_8 - (int)(short)DAT_0002b254);
  iVar6 = (int)sVar4;
  iVar3 = FUN_000038e8(iVar5);
  if ((iVar3 < 0x1001) && (iVar3 = FUN_000038e8(iVar6), iVar3 < 0x1001)) {
    if (iVar5 < 0) {
      iVar5 = iVar5 + 3;
    }
    local_6 = DAT_0002b256 + (short)(iVar5 >> 2);
    if (iVar6 < 0) {
      iVar6 = iVar6 + 3;
    }
    local_8 = DAT_0002b254 + (short)(iVar6 >> 2);
  }
  DAT_0002b256 = local_6 & 0x3fff;
  DAT_0002b254 = local_8 & 0x3fff;
  DAT_00023d40 = -DAT_0002b256 & 0x3fff;
  DAT_00023d3e = -DAT_0002b254 & 0x3fff;
  DAT_00023d3c = 0;
  DAT_00023d38 = 0;
  return;
}



// FUN_00009d72 @ 0x9d72

int FUN_00009d72(void)

{
  uint uVar1;
  short sVar4;
  int iVar2;
  int iVar3;
  short sVar5;
  int iVar6;
  int iVar7;
  int iVar8;
  int iVar9;
  int local_28;
  short local_24;
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  local_24 = 0;
  if ((DAT_00021068 == (int *)0x0) ||
     (iVar8 = *DAT_00021068, DAT_00021020 <= *(int *)(iVar8 + 0x42))) {
    if (((DAT_00021070 != (int *)0x0) || (DAT_0002b258 != 0)) && (DAT_00021058 != 3)) {
      if (DAT_0002b258 == 0) {
        DAT_0002106c = DAT_00021070;
        DAT_0002b258 = 0x14;
      }
      if (DAT_0002106c != (int *)0x0) {
        iVar8 = *SUB_00020b28;
        iVar9 = *DAT_0002106c;
        if ((iVar9 != 0) &&
           (uVar1 = FUN_000048a6(*(undefined4 *)(iVar8 + 4),*(undefined4 *)(iVar8 + 8),
                                 *(undefined4 *)(iVar8 + 0xc),*(undefined4 *)(iVar9 + 4),
                                 *(undefined4 *)(iVar9 + 8),*(undefined4 *)(iVar9 + 0xc)),
           (int)uVar1 < 0x9ab1)) {
          local_28 = *(int *)(iVar8 + 4) + *(int *)(iVar9 + 4);
          if (local_28 < 0) {
            local_28 = local_28 + 1;
          }
          local_28 = local_28 >> 1;
          iVar7 = *(int *)(iVar8 + 8) + *(int *)(iVar9 + 8);
          if (iVar7 < 0) {
            iVar7 = iVar7 + 1;
          }
          iVar7 = iVar7 >> 1;
          iVar6 = *(int *)(iVar8 + 0xc) + *(int *)(iVar9 + 0xc);
          if (iVar6 < 0) {
            iVar6 = iVar6 + 1;
          }
          iVar6 = iVar6 >> 1;
          sVar5 = 0;
          for (; (uVar1 & 0xffff8000) != 0; uVar1 = (int)uVar1 >> 1) {
            sVar5 = sVar5 + 1;
          }
          sVar4 = FUN_0000f87c((int)*(short *)((int)DAT_0002106c + 0x22) -
                               (int)*(short *)((int)SUB_00020b28 + 0x22));
          if ((DAT_00021070 == (int *)0x0) || (iVar2 = FUN_00003908((int)sVar4), 0x1000 < iVar2)) {
            iVar9 = iVar8;
          }
          else {
            local_e = 0;
            local_a = 0;
            local_6 = 0x4000;
            FUN_00002d02((int)*(short *)(SUB_00020b28 + 8),(int)*(short *)((int)SUB_00020b28 + 0x22)
                         ,(int)*(short *)(SUB_00020b28 + 9),auStack_22);
            (*(code *)&LAB_000031fe_2)(1,auStack_10,auStack_10,auStack_22);
            iVar2 = FUN_00004886(*(int *)(iVar8 + 4) + (int)local_e,
                                 *(int *)(iVar8 + 8) + (int)local_a,
                                 *(int *)(iVar8 + 0xc) + (int)local_6,*(undefined4 *)(iVar9 + 4),
                                 *(undefined4 *)(iVar9 + 8),*(undefined4 *)(iVar9 + 0xc));
            local_e = 0;
            local_a = 0;
            local_6 = 0x4000;
            FUN_00002d02((int)*(short *)(DAT_0002106c + 8),(int)*(short *)((int)DAT_0002106c + 0x22)
                         ,(int)*(short *)(DAT_0002106c + 9),auStack_22);
            (*(code *)&LAB_000031fe_2)(1,auStack_10,auStack_10,auStack_22);
            iVar3 = FUN_00004886(*(int *)(iVar9 + 4) + (int)local_e,
                                 *(int *)(iVar9 + 8) + (int)local_a,
                                 *(int *)(iVar9 + 0xc) + (int)local_6,*(undefined4 *)(iVar8 + 4),
                                 *(undefined4 *)(iVar8 + 8),*(undefined4 *)(iVar8 + 0xc));
            if (iVar2 <= iVar3) {
              iVar9 = iVar8;
            }
          }
          FUN_000099e2(iVar9,local_28,iVar7,iVar6,(int)sVar5);
          if (DAT_00021070 != DAT_0002106c) {
            DAT_0002b258 = DAT_0002b258 + -1;
          }
          goto LAB_0000a0ae;
        }
      }
    }
    iVar8 = *SUB_00020b28;
  }
  local_28 = *(int *)(iVar8 + 4);
  iVar7 = *(int *)(iVar8 + 8);
  iVar6 = *(int *)(iVar8 + 0xc);
  if (DAT_00021058 != 3) {
    if (DAT_0002106c != (int *)0x0) {
      iVar8 = DAT_00021056 + -0x1e;
      DAT_00023d4c = (DAT_0002b264 - DAT_00023d68) / iVar8;
      DAT_00023d48 = (DAT_0002b260 - DAT_00023d64) / iVar8;
      DAT_00023d44 = (DAT_0002b25c - DAT_00023d60) / iVar8;
      DAT_0002106c = (int *)0x0;
      DAT_0002b258 = 0;
    }
    DAT_00023d68 = DAT_00023d4c + DAT_00023d68;
    DAT_00023d64 = DAT_00023d48 + DAT_00023d64;
    DAT_00023d60 = DAT_00023d44 + DAT_00023d60;
  }
LAB_0000a0ae:
  DAT_00021056 = DAT_00021056 + -1;
  if ((DAT_00021056 < 0x1f) && (DAT_00021058 = 3, DAT_00021056 == 0)) {
    if (*(short *)(SUB_000207fc + 0x98) != 2) {
      if ((*(byte *)(SUB_00020b28 + 0x1a6) & 4) == 0) {
        local_24 = -1;
      }
      else if (DAT_00021064 == 0) {
        *(undefined2 *)(SUB_000207fc + 0xd8) = 1;
        local_24 = 1;
      }
      else {
        local_24 = FUN_000098e0();
      }
    }
    *(undefined2 *)(SUB_000207fc + 0x98) = 0;
  }
  FUN_00009c00(local_28,iVar7,iVar6);
  return (int)local_24;
}



// FUN_0000a136 @ 0xa136

void FUN_0000a136(void)

{
  uint *puVar1;
  int iVar2;
  ushort uVar3;
  int *piVar4;
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  piVar4 = SUB_00020b28;
  DAT_00023d88 = 8;
  if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) != 0) {
    puVar1 = (uint *)(*SUB_00020b28 + 0x14);
    *puVar1 = *puVar1 & 0xffffffbf;
    FUN_00016136(*piVar4);
  }
  FUN_00007f32();
  piVar4 = (int *)((int)SUB_00020b28 + *(short *)((int)SUB_00020b28 + 0x612) * 0x14 + 0x36);
  local_a = 0;
  local_e = 0;
  local_6 = -1000;
  FUN_00002d02(0,(int)*(short *)((int)piVar4 + 0xe),0,auStack_22);
  FUN_00003220(1,auStack_10,auStack_10,auStack_22);
  DAT_00023d68 = *piVar4 + (int)local_e;
  DAT_00023d64 = piVar4[1] + (int)local_a;
  DAT_00023d60 = piVar4[2] + (int)local_6;
  uVar3 = *(short *)((int)SUB_00020b28 + 0x612) - 1;
  if ((int)((uint)uVar3 << 0x10) < 0) {
    uVar3 = 0x4a;
  }
  piVar4 = (int *)((int)SUB_00020b28 + (short)uVar3 * 0x14 + 0x36);
  if (DAT_00021054 == 1) {
    local_6 = -0x513;
  }
  else {
    local_6 = 0x513;
  }
  local_a = 0x183;
  local_e = -0x2ee;
  FUN_00002d02(0,(int)*(short *)((int)piVar4 + 0xe),0,auStack_22);
  FUN_00003220(1,auStack_10,auStack_10,auStack_22);
  DAT_0002b264 = *piVar4 + (int)local_e;
  DAT_0002b25c = piVar4[2] + (int)local_6;
  iVar2 = *(int *)((int)SUB_00020b28 + 0x2e) + 0x280;
  DAT_0002b260 = piVar4[1] + (int)local_a;
  if (piVar4[1] + (int)local_a < iVar2) {
    DAT_0002b260 = iVar2;
  }
  DAT_00023d4c = (DAT_0002b264 - DAT_00023d68) / 0x4b;
  DAT_00023d48 = (DAT_0002b260 - DAT_00023d64) / 0x4b;
  DAT_00023d44 = (DAT_0002b25c - DAT_00023d60) / 0x4b;
  *(undefined2 *)(SUB_00020b28 + 0x1a7) = 0;
  DAT_00023d88 = 8;
  DAT_0002b254 = 0xc001;
  DAT_0002b256 = 0xc001;
  DAT_00021054 = 0;
  DAT_00021056 = 0x69;
  DAT_00021058 = 1;
  *(undefined2 *)(SUB_000207fc + 0x98) = 1;
  if ((DAT_0002106c != (int *)0x0) && (*DAT_0002106c != 0)) {
    DAT_00021070 = DAT_0002106c;
  }
  DAT_0002106c = (int *)0x0;
  DAT_0002b258 = 0;
  FUN_0000a6c4();
  return;
}



// FUN_0000a380 @ 0xa380

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000a380(void)

{
  int iVar1;
  
  if ((*(byte *)(SUB_00020b28 + 0x699) & 1) == 0) {
    if ((_DAT_0002105a == 0) || (DAT_00021070 == (int *)0x0)) {
      DAT_0002b26e = *(undefined2 *)(SUB_000207fc + 0x37a);
      DAT_0002b26c = *(undefined2 *)(SUB_000207fc + 0x37c);
      iVar1 = ((int)DAT_00027052 - (int)*(short *)(SUB_000207fc + 0x37a)) * 3;
      if (iVar1 < 0) {
        iVar1 = iVar1 + 1;
      }
      FUN_000123de(SUB_00020b28,(int)DAT_00021042,
                   (int)(short)((*(short *)(SUB_000207fc + 0x37c) - DAT_00027054) * 3),
                   (int)(short)(iVar1 >> 1));
    }
    else {
      iVar1 = *DAT_00021070;
      FUN_000129b4(SUB_00020b28,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                   *(undefined4 *)(iVar1 + 0xc));
    }
  }
  return;
}



// FUN_0000a426 @ 0xa426

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000a426(void)

{
  int iVar1;
  int *piVar2;
  short sVar3;
  short sVar4;
  undefined4 *puVar5;
  uint uVar6;
  int *piVar7;
  undefined4 *puVar8;
  undefined4 *puVar9;
  
  iVar1 = DAT_00021020 + 1;
  if (DAT_00021058 == 1) {
    DAT_00021020 = DAT_00021020 + -0x4a;
    FUN_00013d54();
    iVar1 = DAT_00021020;
  }
  DAT_00021020 = iVar1;
  sVar3 = DAT_000219b4._2_2_;
  if (DAT_00021058 != 0) {
    piVar2 = &DAT_00020804;
    sVar4 = DAT_00020b24._2_2_;
    while (sVar4 = sVar4 + -1, sVar4 != -1) {
      piVar7 = piVar2 + 1;
      iVar1 = *piVar2;
      piVar2 = piVar7;
      if ((DAT_00021020 < *(int *)(iVar1 + 0x42)) ||
         ((*(int *)(iVar1 + 0x3e) != 0 && (*(int *)(iVar1 + 0x3e) < DAT_00021020)))) {
        if ((*(int *)(iVar1 + 0x3e) == 0) || (DAT_00021020 <= *(int *)(iVar1 + 0x3e))) {
          uVar6 = 0x40;
        }
        else {
          uVar6 = 0x42;
        }
        *(uint *)(iVar1 + 0x14) = uVar6 | *(uint *)(iVar1 + 0x14);
      }
      else if ((*(byte *)(iVar1 + 0x17) & 2) == 0) {
        if (*(int *)(iVar1 + 0x42) == DAT_00021020) {
          *(uint *)(iVar1 + 0x14) = *(uint *)(iVar1 + 0x14) & 0xffffffbf;
        }
      }
      else {
        *(uint *)(iVar1 + 0x14) = *(uint *)(iVar1 + 0x14) & 0xffffffbd;
      }
    }
    piVar2 = &DAT_00020b44;
    sVar4 = DAT_00020f2c._2_2_;
    while (sVar4 = sVar4 + -1, sVar4 != -1) {
      piVar7 = piVar2 + 1;
      iVar1 = *piVar2;
      piVar2 = piVar7;
      if (*(char *)(iVar1 + 0x28) == '\x02') {
        if ((DAT_00021020 < *(int *)(iVar1 + 0x42)) ||
           ((*(int *)(iVar1 + 0x3e) != 0 && (*(int *)(iVar1 + 0x3e) < DAT_00021020)))) {
          if ((*(int *)(iVar1 + 0x3e) == 0) || (DAT_00021020 <= *(int *)(iVar1 + 0x3e))) {
            uVar6 = 0x40;
          }
          else {
            uVar6 = 0x42;
          }
          *(uint *)(iVar1 + 0x14) = uVar6 | *(uint *)(iVar1 + 0x14);
        }
        else if ((*(byte *)(iVar1 + 0x17) & 2) == 0) {
          if (*(int *)(iVar1 + 0x42) == DAT_00021020) {
            *(uint *)(iVar1 + 0x14) = *(uint *)(iVar1 + 0x14) & 0xffffffbf;
          }
        }
        else {
          *(uint *)(iVar1 + 0x14) = *(uint *)(iVar1 + 0x14) & 0xffffffbd;
        }
      }
    }
    FUN_00013cbe();
  }
  DAT_00021070 = 0;
  puVar8 = &DAT_00021bd4;
  puVar5 = &DAT_00021bd4;
  sVar4 = sVar3;
  while (sVar4 = sVar4 + -1, sVar4 != -1) {
    puVar9 = puVar5 + 1;
    piVar2 = (int *)*puVar5;
    puVar5 = puVar9;
    if (*piVar2 != 0) {
      puVar9 = puVar8 + 1;
      *puVar8 = piVar2;
      puVar8 = puVar9;
      if (*(int *)(piVar2[1] + 2) != 0) {
        (**(code **)(piVar2[1] + 2))(piVar2);
      }
    }
  }
  sVar4 = (short)((int)(puVar8 + -0x86f5) >> 2);
  if (sVar4 < sVar3) {
    if (sVar3 < DAT_000219b4) {
      sVar3 = DAT_000219b4._2_2_ - sVar3;
      while (sVar3 = sVar3 + -1, sVar3 != -1) {
        *puVar8 = *puVar5;
        puVar8 = puVar8 + 1;
        puVar5 = puVar5 + 1;
      }
    }
    DAT_000219b4 = (int)(puVar8 + -0x86f5) >> 2;
  }
  _DAT_000219b0 = (int)sVar4;
  FUN_0000a5de();
  return;
}



// FUN_0000a5de @ 0xa5de

void FUN_0000a5de(void)

{
  int iVar1;
  int *piVar2;
  undefined4 *puVar3;
  short sVar4;
  undefined4 *puVar5;
  
  puVar3 = &DAT_00021bd4;
  sVar4 = DAT_000219b2;
  while (sVar4 = sVar4 + -1, sVar4 != -1) {
    puVar5 = puVar3 + 1;
    piVar2 = (int *)*puVar3;
    iVar1 = *piVar2;
    puVar3 = puVar5;
    if ((iVar1 != 0) && ((*(byte *)(iVar1 + 0x16) & 0x20) != 0)) {
      piVar2[5] = *(int *)(iVar1 + 4);
      piVar2[6] = *(int *)(iVar1 + 8);
      piVar2[7] = *(int *)(iVar1 + 0xc);
      *(undefined4 *)((int)piVar2 + 0x2e) = 0;
      *(int *)(iVar1 + 4) = piVar2[2] + *(int *)(iVar1 + 4);
      *(int *)(iVar1 + 8) = piVar2[3] + *(int *)(iVar1 + 8);
      *(int *)(iVar1 + 0xc) = piVar2[4] + *(int *)(iVar1 + 0xc);
    }
  }
  return;
}



// FUN_0000a63a @ 0xa63a

void FUN_0000a63a(void)

{
  int iVar1;
  int *piVar2;
  short sVar3;
  undefined4 *puVar4;
  short sVar5;
  undefined4 *puVar6;
  undefined4 *puVar7;
  
  sVar3 = DAT_000219b4._2_2_;
  puVar6 = &DAT_00021bd4;
  puVar4 = &DAT_00021bd4;
  sVar5 = DAT_000219b4._2_2_;
  while (sVar5 = sVar5 + -1, sVar5 != -1) {
    puVar7 = puVar4 + 1;
    piVar2 = (int *)*puVar4;
    iVar1 = *piVar2;
    puVar4 = puVar7;
    if (iVar1 != 0) {
      puVar7 = puVar6 + 1;
      *puVar6 = piVar2;
      puVar6 = puVar7;
      if (((*(byte *)(iVar1 + 0x15) & 4) != 0) && (*(int *)(piVar2[1] + 2) != 0)) {
        (**(code **)(piVar2[1] + 2))(piVar2);
      }
    }
  }
  if ((short)((int)(puVar6 + -0x86f5) >> 2) < sVar3) {
    if (sVar3 < DAT_000219b4) {
      sVar3 = DAT_000219b4._2_2_ - sVar3;
      while (sVar3 = sVar3 + -1, sVar3 != -1) {
        *puVar6 = *puVar4;
        puVar6 = puVar6 + 1;
        puVar4 = puVar4 + 1;
      }
    }
    DAT_000219b4 = (int)(puVar6 + -0x86f5) >> 2;
  }
  return;
}



// FUN_0000a6c4 @ 0xa6c4

void FUN_0000a6c4(void)

{
  int iVar1;
  
  if (*(int *)(SUB_000207fc + 0x296) != 0) {
    iVar1 = (**(code **)(SUB_000207fc + 0x2f2))(0);
    if (iVar1 != 0) {
      (**(code **)(SUB_000207fc + 0x2fa))(0);
    }
    iVar1 = (**(code **)(SUB_000207fc + 0x2f2))(1);
    if (iVar1 != 0) {
      (**(code **)(SUB_000207fc + 0x2fa))(1);
    }
  }
  DAT_0002b250 = 0xffffffff;
  return;
}



// FUN_0000a720 @ 0xa720

void FUN_0000a720(int param_1)

{
  code *pcVar1;
  int in_A1;
  int iVar2;
  undefined4 uVar3;
  int iVar4;
  undefined4 uVar5;
  undefined4 uVar6;
  
  if ((DAT_0002b24c != 0) && (DAT_0002b24c <= param_1)) {
    return;
  }
  if ((param_1 < 100) &&
     (in_A1 = *(int *)(*(int *)(SUB_000207fc + 0x296) + param_1 * 4), in_A1 == 0)) {
    return;
  }
  switch(param_1) {
  case 1:
  case 2:
  case 3:
  case 4:
  case 6:
  case 8:
  case 9:
  case 0xc:
  case 0xe:
    uVar6 = 0;
    uVar5 = 0;
    iVar4 = *(int *)(in_A1 + 4) + -0x172;
    uVar3 = 0;
    iVar2 = in_A1 + 0x16;
    pcVar1 = *(code **)(SUB_000207fc + 0x2e6);
    break;
  default:
    uVar6 = *(undefined4 *)(in_A1 + 0x10);
    uVar5 = *(undefined4 *)(in_A1 + 0xc);
    iVar4 = *(int *)(in_A1 + 4) + -0x172;
    uVar3 = 0;
    iVar2 = in_A1 + 0x16;
    pcVar1 = *(code **)(SUB_000207fc + 0x2e6);
    break;
  case 100:
    iVar2 = *(int *)(*(int *)(SUB_000207fc + 0x296) + 0x1c);
    if (iVar2 == 0) {
      DAT_0002b24c = param_1;
      return;
    }
    uVar6 = 0;
    uVar5 = 0;
    iVar4 = *(int *)(iVar2 + 4) + -0x172;
    uVar3 = 0x48c0;
    iVar2 = iVar2 + 0x16;
    pcVar1 = *(code **)(SUB_000207fc + 0x2e6);
  }
  (*pcVar1)(1,iVar2,uVar3,iVar4,uVar5,uVar6);
  DAT_0002b24c = param_1;
  return;
}



// FUN_0000a8c4 @ 0xa8c4

void FUN_0000a8c4(int param_1)

{
  if (DAT_0002b24c == param_1) {
    DAT_0002b24c = 0;
    (**(code **)(SUB_000207fc + 0x2f6))(1);
  }
  return;
}



// FUN_0000a90a @ 0xa90a

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000a90a(void)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  code *pcVar5;
  
  if (*(short *)(SUB_000207fc + 0x9a) == 0) goto LAB_0000acf2;
  if ((((2 < DAT_00023d88) && (DAT_00023d88 != 5)) && (DAT_00023d88 != 7)) && (DAT_00023d88 != 8)) {
    FUN_0000a6c4();
    goto LAB_0000acf2;
  }
  iVar1 = (**(code **)(SUB_000207fc + 0x2f2))(1);
  iVar3 = SUB_00020b28;
  if (iVar1 == 0) {
    DAT_0002b24c = 0;
  }
  if ((DAT_00023d88 == 8) || (DAT_00023d88 == 9)) {
    if (DAT_00021034 != 0) {
      FUN_0000a720(8);
      DAT_00021034 = 0;
    }
    if (DAT_00021032 != 0) {
      FUN_0000a720(9);
      DAT_00021032 = 0;
    }
    if (_DAT_0002102e != 0) {
      FUN_0000a720(0xe);
    }
    goto LAB_0000acf2;
  }
  if (DAT_00023d88 == 7) {
    iVar3 = DAT_00021070;
  }
  else {
    if (DAT_00021034 != 0) {
      DAT_00021032 = DAT_00021034;
      DAT_00021034 = 0;
    }
    iVar1 = FUN_00003908((int)*(char *)(SUB_00020b28 + 0x68a));
    _DAT_00021038 = 0;
    if (((iVar1 != 0) && (iVar1 != 0x2d)) && ((*(byte *)(iVar3 + 0x699) & 0x20) == 0)) {
      _DAT_00021038 = 1;
    }
    iVar1 = FUN_00003908((int)*(char *)(iVar3 + 0x68c));
    _DAT_00021036 = 0;
    if ((iVar1 != 0) && (iVar1 != 0xf)) {
      _DAT_00021036 = 1;
    }
  }
  if (_DAT_0002103c == 0) {
    FUN_0000a8c4(5);
  }
  else {
    FUN_0000a720(5);
  }
  if (_DAT_00021040 != 0) {
    FUN_0000a720(6);
  }
  if ((_DAT_00021038 == 0) && (_DAT_00021036 == 0)) {
    FUN_0000a8c4(7);
  }
  else {
    FUN_0000a720(7);
  }
  if (_DAT_0002103a == 0) {
    FUN_0000a8c4(0xb);
  }
  else {
    FUN_0000a720(0xb);
  }
  if (_DAT_0002103e == 0) {
    FUN_0000a8c4(0xc);
  }
  else {
    FUN_0000a720(0xc);
  }
  if (DAT_00021034 != 0) {
    FUN_0000a720(8);
    DAT_00021034 = 0;
  }
  if (DAT_00021032 != 0) {
    FUN_0000a720(9);
    DAT_00021032 = 0;
  }
  if (_DAT_00021030 != 0) {
    FUN_0000a720(100);
  }
  if (_DAT_0002102e != 0) {
    if ((DAT_0002b24c != 0) && (DAT_0002b24c == 0xe)) {
      DAT_0002b24c = 0;
    }
    FUN_0000a720(0xe);
  }
  if (_DAT_0002102c != 0) {
    FUN_0000a720(1);
  }
  if (_DAT_0002102a != 0) {
    FUN_0000a720(2);
  }
  if (_DAT_00021024 != 0) {
    FUN_0000a720(3);
  }
  if (_DAT_00021026 != 0) {
    FUN_0000a720(4);
  }
  if (((DAT_0002b24c != 0) && (((DAT_0002b24c == 1 || (DAT_0002b24c == 3)) || (DAT_0002b24c == 4))))
     || (*(short *)(SUB_000207fc + 0x9c) == 0)) {
    iVar3 = (**(code **)(SUB_000207fc + 0x2f2))(0);
    if (iVar3 != 0) {
      (**(code **)(SUB_000207fc + 0x2fa))(0);
    }
    DAT_0002b250 = -1;
    goto LAB_0000acf2;
  }
  iVar3 = *(int *)(iVar3 + 0x678);
  if (((DAT_00023d88 == 7) && (*(char *)(DAT_00021070 + 0x677) != '\0')) ||
     ((DAT_00023d88 != 7 && (*(char *)(SUB_00020b28 + 0x677) != '\0')))) {
    iVar4 = 0;
    iVar1 = **(int **)(SUB_000207fc + 0x296);
    iVar3 = iVar3 + 0x10000;
  }
  else if (iVar3 < 0x2711) {
    iVar1 = 0;
    iVar4 = -1;
  }
  else {
    iVar4 = 10;
    iVar1 = *(int *)(*(int *)(SUB_000207fc + 0x296) + 0x28);
    iVar3 = (iVar3 << 2) / 3 + 0x4000;
  }
  if (iVar1 == 0) {
    iVar3 = (**(code **)(SUB_000207fc + 0x2f2))(0);
    if (iVar3 != 0) {
      iVar3 = 0;
      pcVar5 = *(code **)(SUB_000207fc + 0x2fa);
      goto LAB_0000acb4;
    }
  }
  else {
    iVar2 = (**(code **)(SUB_000207fc + 0x2f2))(0);
    if ((iVar2 == 0) || (iVar4 != DAT_0002b250)) {
      (**(code **)(SUB_000207fc + 0x2e6))
                (0,iVar1 + 0x16,0,*(undefined4 *)(iVar1 + 4),0,*(undefined4 *)(iVar1 + 4));
    }
    pcVar5 = *(code **)(SUB_000207fc + 0x2ee);
LAB_0000acb4:
    (*pcVar5)(iVar3);
  }
  DAT_0002b250 = iVar4;
LAB_0000acf2:
  _DAT_00021040 = 0;
  _DAT_0002103c = 0;
  _DAT_0002103a = 0;
  _DAT_0002103e = 0;
  if (DAT_00021032 != 0) {
    DAT_00021032 = DAT_00021032 + -1;
  }
  if (DAT_00021034 != 0) {
    DAT_00021034 = DAT_00021034 + -1;
  }
  _DAT_00021030 = 0;
  _DAT_0002102e = 0;
  _DAT_0002102c = 0;
  _DAT_0002102a = 0;
  _DAT_00021026 = 0;
  _DAT_00021024 = 0;
  return;
}



// FUN_0000ad50 @ 0xad50

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

byte FUN_0000ad50(int param_1,int param_2)

{
  int *piVar1;
  uint uVar2;
  int iVar3;
  
  iVar3 = _DAT_0002b286 + param_1 * 0xc;
  piVar1 = (int *)(*(int *)(DAT_0002c0ba + 0x46) + **(short **)(param_2 + 0x18) * 0xc);
  uVar2 = (uint)DAT_0002b296;
  return -((int)(short)(*(short *)(iVar3 + 10) - DAT_0002b292._2_2_) *
           (int)(short)(DAT_0002b298 - (short)(piVar1[2] >> (uVar2 & 0x3f))) +
           (int)(short)(*(short *)(iVar3 + 6) - DAT_0002b28e._2_2_) *
           (int)(short)(DAT_0002b29a - (short)(piVar1[1] >> (uVar2 & 0x3f))) +
           (int)(short)(*(short *)(iVar3 + 2) - DAT_0002b28a._2_2_) *
           (int)(short)(DAT_0002b29c - (short)(*piVar1 >> (uVar2 & 0x3f))) < 1) & 1;
}



// FUN_0000adfc @ 0xadfc

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000adfc(undefined2 *param_1)

{
  short sVar1;
  int *piVar2;
  short sVar3;
  int iVar4;
  int iVar5;
  uint uVar6;
  int iVar7;
  int *piVar8;
  undefined4 *puVar9;
  int iVar10;
  undefined4 *puVar11;
  
  sVar3 = DAT_0002c0b0;
  *param_1 = 0;
  uVar6 = (uint)*(byte *)((int)param_1 + 0xf);
  if (uVar6 != 0xff) {
    if (param_1 == *(undefined2 **)(DAT_00023fac + uVar6 * 4)) {
      *(undefined1 *)(DAT_00023d94 + uVar6) = 1;
    }
    else {
      iVar4 = FUN_00001c06(param_1);
      if (iVar4 != 0) {
        return;
      }
      *(undefined1 *)(DAT_00023d94 + (uint)*(byte *)((int)param_1 + 0xf)) = 1;
    }
  }
  piVar8 = &DAT_0002b338;
  for (sVar1 = sVar3; sVar1 != 0; sVar1 = sVar1 + -1) {
    *piVar8 = *piVar8 * 0x10000 + 0x8000;
    piVar8 = piVar8 + 2;
  }
  piVar8 = &DAT_0002b338;
joined_r0x0000ae80:
  do {
    do {
      piVar2 = piVar8;
      sVar3 = sVar3 + -1;
      if (sVar3 == -1) {
        return;
      }
      iVar4 = (int)sVar3;
      piVar8 = &DAT_0002b338 + iVar4 * 2;
    } while (piVar2[1] == (&DAT_0002b33c)[iVar4 * 2]);
    puVar9 = (undefined4 *)(_DAT_00020f54 + DAT_00026fe0 * 0x1c);
    *puVar9 = param_1;
    puVar9[1] = 0;
    if ((int)(&DAT_0002b33c)[iVar4 * 2] < piVar2[1]) {
      *(short *)((int)puVar9 + 0x16) = DAT_00027054 + *(short *)((int)piVar2 + 6);
      iVar7 = *piVar8;
      iVar10 = (&DAT_0002b33c)[iVar4 * 2];
      iVar5 = *piVar2 - iVar7;
      iVar4 = piVar2[1];
    }
    else {
      *(short *)((int)puVar9 + 0x16) = DAT_00027054 + *(short *)((int)&DAT_0002b33c + iVar4 * 8 + 2)
      ;
      iVar7 = *piVar2;
      iVar10 = piVar2[1];
      iVar5 = *piVar8 - iVar7;
      iVar4 = (&DAT_0002b33c)[iVar4 * 2];
    }
    puVar9[4] = iVar5 / (iVar4 - iVar10);
    iVar4 = (int)DAT_00027054;
    puVar9[3] = DAT_00027052 * 0x10000 + iVar7;
    DAT_00026fe0 = DAT_00026fe0 + 1;
  } while ((puVar9[3] == DAT_0002701c) && (puVar9[4] == 0));
  iVar7 = (&DAT_00024fdc)[iVar4 + iVar10];
  puVar11 = &DAT_00024fdc + iVar4 + iVar10;
  do {
    if ((iVar7 == 0) || ((int)puVar9[3] < *(int *)(iVar7 + 0xc))) break;
    if (puVar9[3] == *(int *)(iVar7 + 0xc)) {
      if ((int)puVar9[4] < *(int *)(iVar7 + 0x10)) break;
      if (((puVar9[4] == *(int *)(iVar7 + 0x10)) &&
          (*(short *)(iVar7 + 0x16) == *(short *)((int)puVar9 + 0x16))) &&
         (*(int *)(iVar7 + 4) == 0)) {
        *(undefined2 **)(iVar7 + 4) = param_1;
        goto joined_r0x0000ae80;
      }
    }
    puVar11 = (undefined4 *)(iVar7 + 0x18);
    iVar7 = *(int *)(iVar7 + 0x18);
  } while( true );
  puVar9[6] = *puVar11;
  *puVar11 = puVar9;
  (&DAT_00023fd4)[*(short *)((int)puVar9 + 0x16)] = 1;
  goto joined_r0x0000ae80;
}



// FUN_0000afaa @ 0xafaa

void FUN_0000afaa(short param_1,int param_2)

{
  int iVar1;
  int iVar2;
  
  do {
    iVar2 = DAT_0002c4c6 + param_1 * 0x20;
    if ((param_2 <= *(int *)(iVar2 + 0x1c)) && ((*(byte *)(iVar2 + 0x13) & 2) == 0)) {
      if (((*(byte *)(iVar2 + 0x13) & 1) == 0) &&
         (iVar1 = FUN_0000ad50((int)param_1,iVar2), iVar1 != 0)) {
        return;
      }
      *(short *)(iVar2 + 2) = DAT_000219a8;
      DAT_000219a8 = DAT_000219a8 + 1;
      *DAT_0002c2c2 = iVar2;
      DAT_0002c2c2 = DAT_0002c2c2 + 1;
    }
    param_1 = (short)*(char *)(iVar2 + 0x12);
    if (param_1 < 0) {
      return;
    }
  } while( true );
}



// FUN_0000b01c @ 0xb01c

void FUN_0000b01c(short param_1)

{
  int iVar1;
  byte bVar2;
  bool bVar3;
  short sVar4;
  char cVar5;
  int iVar6;
  
  iVar1 = *(int *)(DAT_0002c0ba + 0x1c);
  iVar6 = DAT_0002c4c6 + param_1 * 0x20;
  bVar3 = false;
  bVar2 = *(byte *)(iVar6 + 0x13) & 1;
  if (DAT_0002c0ac == 0) {
    sVar4 = FUN_0000ad50((int)param_1,iVar6);
    if ((sVar4 != 0) && (bVar3 = true, bVar2 == 0)) goto LAB_0000b0cc;
  }
  else {
    sVar4 = FUN_0000ad50((int)param_1,iVar6);
    if (sVar4 == 0) {
      *(byte *)(iVar6 + 0x13) = *(byte *)(iVar6 + 0x13) & 0xef;
    }
    else {
      bVar3 = true;
      if (bVar2 == 0) {
        *DAT_0002c0be = iVar6;
        DAT_0002c0be = DAT_0002c0be + 1;
        goto LAB_0000b0cc;
      }
      *(byte *)(iVar6 + 0x13) = *(byte *)(iVar6 + 0x13) | 0x10;
    }
  }
  if ((*(int *)(iVar6 + 0x1c) < iVar1) || ((*(byte *)(iVar6 + 0x13) & 2) != 0)) {
    bVar3 = true;
  }
  else {
    *(short *)(iVar6 + 2) = DAT_000219a8;
    DAT_000219a8 = DAT_000219a8 + 1;
    *DAT_0002c2c2 = iVar6;
    DAT_0002c2c2 = DAT_0002c2c2 + 1;
  }
LAB_0000b0cc:
  if (sVar4 == 0) {
    if (-1 < *(char *)(iVar6 + 0x11)) {
      FUN_0000b01c((int)*(char *)(iVar6 + 0x11));
    }
    *(short *)(iVar6 + 2) = DAT_000219a8;
    DAT_000219a8 = DAT_000219a8 + 1;
    if ((!bVar3) && (-1 < *(char *)(iVar6 + 0x12))) {
      FUN_0000afaa((int)*(char *)(iVar6 + 0x12),iVar1);
    }
    if (*(char *)(iVar6 + 0x10) < '\0') {
      return;
    }
    cVar5 = *(char *)(iVar6 + 0x10);
  }
  else {
    if (-1 < *(char *)(iVar6 + 0x10)) {
      FUN_0000b01c((int)*(char *)(iVar6 + 0x10));
    }
    *(short *)(iVar6 + 2) = DAT_000219a8;
    DAT_000219a8 = DAT_000219a8 + 1;
    if ((!bVar3) && (-1 < *(char *)(iVar6 + 0x12))) {
      FUN_0000afaa((int)*(char *)(iVar6 + 0x12),iVar1);
    }
    if (*(char *)(iVar6 + 0x11) < '\0') {
      return;
    }
    cVar5 = *(char *)(iVar6 + 0x11);
  }
  FUN_0000b01c((int)cVar5);
  return;
}



// FUN_0000b16e @ 0xb16e

void FUN_0000b16e(void)

{
  int iVar1;
  byte bVar2;
  short sVar3;
  int iVar4;
  short sVar5;
  short sVar6;
  int iVar7;
  
  iVar1 = *(int *)(DAT_0002c0ba + 0x1c);
  sVar6 = 0;
  sVar3 = DAT_0002c0ae;
  do {
    sVar3 = sVar3 + -1;
    if (sVar3 == -1) {
      return;
    }
    sVar5 = sVar6;
    do {
      iVar7 = DAT_0002c4c6 + sVar5 * 0x20;
      if ((*(int *)(iVar7 + 0x1c) < iVar1) || ((*(byte *)(iVar7 + 0x13) & 2) != 0)) break;
      bVar2 = *(byte *)(iVar7 + 0x13) & 1;
      if (DAT_0002c0ac == 0) {
        if ((bVar2 == 0) && (iVar4 = FUN_0000ad50((int)sVar5,iVar7), iVar4 != 0)) break;
      }
      else {
        iVar4 = FUN_0000ad50((int)sVar5,iVar7);
        if (iVar4 == 0) {
          *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) & 0xef;
        }
        else {
          if (bVar2 == 0) {
            *DAT_0002c0be = iVar7;
            DAT_0002c0be = DAT_0002c0be + 1;
            break;
          }
          *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) | 0x10;
        }
      }
      *(short *)(iVar7 + 2) = DAT_000219a8;
      DAT_000219a8 = DAT_000219a8 + 1;
      *DAT_0002c2c2 = iVar7;
      DAT_0002c2c2 = DAT_0002c2c2 + 1;
      sVar5 = (short)*(char *)(iVar7 + 0x12);
    } while (-1 < sVar5);
    sVar6 = sVar6 + 1;
  } while( true );
}



// FUN_0000b240 @ 0xb240

void FUN_0000b240(void)

{
  int iVar1;
  byte bVar2;
  short sVar3;
  int iVar4;
  short sVar5;
  short sVar6;
  int iVar7;
  
  iVar1 = *(int *)(DAT_0002c0ba + 0x1c);
  sVar6 = 0;
  sVar3 = DAT_0002c0ae;
  do {
    sVar3 = sVar3 + -1;
    if (sVar3 == -1) {
      return;
    }
    sVar5 = sVar6;
    do {
      iVar7 = DAT_0002c4c6 + sVar5 * 0x20;
      if ((*(int *)(iVar7 + 0x1c) < iVar1) || ((*(byte *)(iVar7 + 0x13) & 2) != 0)) break;
      bVar2 = *(byte *)(iVar7 + 0x13) & 1;
      if (DAT_0002c0ac == 0) {
        if ((bVar2 == 0) && (iVar4 = FUN_0000ad50((int)sVar5,iVar7), iVar4 != 0)) break;
      }
      else {
        iVar4 = FUN_0000ad50((int)sVar5,iVar7);
        if (iVar4 == 0) {
          *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) & 0xef;
        }
        else {
          if (bVar2 == 0) {
            *DAT_0002c0be = iVar7;
            DAT_0002c0be = DAT_0002c0be + 1;
            break;
          }
          *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) | 0x10;
        }
      }
      *DAT_0002c2c2 = iVar7;
      DAT_0002c2c2 = DAT_0002c2c2 + 1;
      sVar5 = (short)*(char *)(iVar7 + 0x12);
    } while (-1 < sVar5);
    sVar6 = sVar6 + 1;
  } while( true );
}



// FUN_0000b300 @ 0xb300

undefined4 FUN_0000b300(undefined4 param_1)

{
  undefined2 uVar1;
  int iVar2;
  undefined4 uVar3;
  uint uVar4;
  int *piVar5;
  int *piVar6;
  int *piVar7;
  int iVar8;
  int iVar9;
  
  uVar1 = DAT_00020b2e;
  DAT_00020b2e = DAT_00020b30;
  iVar2 = FUN_0000c692(&DAT_0002b798,param_1,&DAT_0002b338);
  DAT_00020b2e = uVar1;
  if (iVar2 == 0) {
    uVar3 = 0;
  }
  else {
    if (iVar2 != 0) {
      iVar9 = (int)DAT_0002701a;
      iVar8 = (int)DAT_00027018;
      piVar6 = &DAT_0002b33c;
      piVar5 = (int *)(&DAT_0002b330 + iVar2 * 8);
      piVar7 = &DAT_0002b338;
      do {
        if ((int)DAT_00027016 == piVar5[1]) {
          if (iVar9 == *piVar5) {
            uVar4 = 1;
          }
          else {
            if (iVar8 != *piVar5) goto LAB_0000b3a6;
            uVar4 = 2;
          }
LAB_0000b3a0:
          DAT_00021578 = uVar4 | DAT_00021578;
        }
        else if ((int)DAT_00020b30 == piVar5[1]) {
          if (iVar9 == *piVar5) {
            uVar4 = 4;
          }
          else {
            if (iVar8 != *piVar5) goto LAB_0000b3a6;
            uVar4 = 8;
          }
          goto LAB_0000b3a0;
        }
LAB_0000b3a6:
        if (((piVar5[1] != *piVar6) ||
            (((int)DAT_00020b30 != *piVar6 && ((int)DAT_00027016 != *piVar6)))) &&
           ((*piVar5 != *piVar7 || ((iVar9 != *piVar7 && (iVar8 != *piVar7)))))) {
          DAT_00021588 = *piVar5;
          DAT_00021584 = piVar5[1];
          DAT_00021580 = *piVar7;
          DAT_0002157c = *piVar6;
        }
        iVar2 = iVar2 + -1;
        piVar6 = piVar6 + 2;
        piVar5 = piVar7;
        piVar7 = piVar7 + 2;
      } while (iVar2 != 0);
    }
    uVar3 = 1;
  }
  return uVar3;
}



// FUN_0000b3f0 @ 0xb3f0

undefined4 FUN_0000b3f0(int param_1)

{
  short sVar2;
  int iVar1;
  
  sVar2 = FUN_0000c478(&DAT_0002b338,(int)DAT_0002c0b0,&DAT_0002b798);
  if ((((sVar2 == 0) || (sVar2 = FUN_0000c52a(&DAT_0002b798,(int)sVar2,&DAT_0002b338), sVar2 == 0))
      || (sVar2 = FUN_0000c5dc(&DAT_0002b338,(int)sVar2,&DAT_0002b798), sVar2 == 0)) ||
     (((param_1 == DAT_00021590 && (iVar1 = FUN_0000b300((int)sVar2), iVar1 == 0)) ||
      (DAT_0002c0b0 = FUN_0000c692(&DAT_0002b798,(int)sVar2,&DAT_0002b338), DAT_0002c0b0 == 0)))) {
    return 0;
  }
  FUN_0000adfc(param_1);
  return 1;
}



// FUN_0000b4a2 @ 0xb4a2

undefined4 FUN_0000b4a2(int param_1)

{
  short sVar2;
  int iVar1;
  
  sVar2 = FUN_0000c478(&DAT_0002b338,(int)DAT_0002c0b0,&DAT_0002b798);
  if ((((sVar2 == 0) || (sVar2 = FUN_0000c52a(&DAT_0002b798,(int)sVar2,&DAT_0002b338), sVar2 == 0))
      || (sVar2 = FUN_0000c5dc(&DAT_0002b338,(int)sVar2,&DAT_0002b798), sVar2 == 0)) ||
     (((param_1 == DAT_00021590 && (iVar1 = FUN_0000b300((int)sVar2), iVar1 == 0)) ||
      (DAT_0002c0b0 = FUN_0000c692(&DAT_0002b798,(int)sVar2,&DAT_0002b338), DAT_0002c0b0 == 0)))) {
    return 0;
  }
  (**(code **)(SUB_000207fc + 0x7c))
            ((int)DAT_0002c0b0,*(undefined4 *)(param_1 + 4),(int)*(short *)(param_1 + 2),
             &DAT_0002b338);
  return 1;
}



// FUN_0000b574 @ 0xb574

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000b574(void)

{
  short sVar1;
  bool bVar2;
  short sVar3;
  short *psVar4;
  short sVar5;
  int iVar6;
  bool bVar8;
  int iVar7;
  int *piVar9;
  int *piVar10;
  bool bVar11;
  
  bVar2 = false;
  sVar3 = (short)(DAT_0002c2c2 + -0x2c2c6 >> 2);
  piVar9 = &DAT_0002c2c6;
  do {
    sVar3 = sVar3 + -1;
    if (sVar3 == -1) {
      if (DAT_0002c0ac != 0) {
        sVar3 = (short)(DAT_0002c0be + -0x2c0c2 >> 2);
        piVar9 = &DAT_0002c0c2;
        while (sVar3 = sVar3 + -1, sVar3 != -1) {
          iVar7 = *piVar9;
          if (((DAT_0002c0ac != 2) && ((*(byte *)(iVar7 + 0x13) & 2) == 0)) &&
             ((*(byte *)(iVar7 + 0x13) & 8) == 0)) {
            if ((*(byte *)(iVar7 + 0x13) & 0x60) != 0x60) {
              FUN_0000b7b6(iVar7);
            }
            iVar6 = FUN_0001583a(0,0,DAT_0002c0b6,(int)*(short *)(iVar7 + 0x14),
                                 *(undefined4 *)(iVar7 + 0x18));
            if (iVar6 != 0) {
              FUN_00016caa(*SUB_00020b28,DAT_0002c0ba);
            }
          }
          *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) | 8;
          piVar9 = piVar9 + 1;
        }
      }
      if (bVar2) {
        *(uint *)(DAT_0002c0ba + 0x14) = *(uint *)(DAT_0002c0ba + 0x14) | 4;
      }
      else {
        *(uint *)(DAT_0002c0ba + 0x14) = *(uint *)(DAT_0002c0ba + 0x14) & 0xfffffffb;
      }
      if (_DAT_0002c0a8 == 0) {
        *(uint *)(DAT_0002c0ba + 0x14) = *(uint *)(DAT_0002c0ba + 0x14) & 0xfffffff7;
      }
      return;
    }
    piVar10 = piVar9 + 1;
    iVar7 = *piVar9;
    if ((*(byte *)(iVar7 + 0x13) & 0x60) != 0x60) {
      *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) | 0x20;
      FUN_0000b7b6(iVar7);
    }
    *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) & 0xbf;
    DAT_0002c0b0 = *(short *)(iVar7 + 0x14);
    if (_DAT_0002c0aa == 0) {
      piVar9 = &DAT_0002b338;
      psVar4 = *(short **)(iVar7 + 0x18);
      sVar5 = DAT_0002c0b0;
      while (sVar5 = sVar5 + -1, sVar5 != -1) {
        sVar1 = *psVar4;
        piVar9[1] = (&DAT_0002bbfc)[sVar1 * 2];
        iVar6 = (&DAT_0002bbf8)[sVar1 * 2];
        *piVar9 = iVar6;
        if (iVar6 == -0x80000000) goto LAB_0000b606;
        piVar9 = piVar9 + 2;
        psVar4 = psVar4 + 1;
      }
    }
    else {
LAB_0000b606:
      iVar6 = FUN_0000c310(&DAT_0002c0b0,DAT_0002c0b6,*(undefined4 *)(iVar7 + 0x18),&DAT_0002bbf8,
                           &DAT_0002b338);
      if (iVar6 != 0) {
        _DAT_0002c0a8 = 1;
      }
    }
    if ((DAT_0002c0ac != 0) && ((*(byte *)(iVar7 + 0x13) & 2) == 0)) {
      bVar11 = (*(byte *)(iVar7 + 0x13) & 0x10) != 0;
      if (DAT_0002c0ac != 2) {
        bVar8 = bVar11;
        if ((*(byte *)(iVar7 + 0x13) & 8) != 0) {
          bVar8 = !bVar11;
        }
        if ((bVar8) &&
           (iVar6 = FUN_0001583a(0,0,DAT_0002c0b6,(int)*(short *)(iVar7 + 0x14),
                                 *(undefined4 *)(iVar7 + 0x18)), iVar6 != 0)) {
          FUN_00016caa(*SUB_00020b28,DAT_0002c0ba);
        }
      }
      if (bVar11) {
        *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) | 8;
      }
      else {
        *(byte *)(iVar7 + 0x13) = *(byte *)(iVar7 + 0x13) & 0xf7;
      }
    }
    piVar9 = piVar10;
    if ((DAT_0002c0b0 != 0) && (iVar7 = (*DAT_00026fe4)(iVar7), iVar7 != 0)) {
      bVar2 = true;
    }
  } while( true );
}



// FUN_0000b7b6 @ 0xb7b6

void FUN_0000b7b6(int param_1)

{
  int iVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  short sVar5;
  short sVar6;
  short sVar7;
  short *psVar8;
  int iVar9;
  short sVar10;
  uint uVar11;
  int *piVar12;
  int iVar13;
  int iVar14;
  short *local_16;
  
  iVar9 = DAT_0002c0b6;
  psVar8 = DAT_0002b29e;
  sVar7 = DAT_0002b29c;
  sVar6 = DAT_0002b29a;
  sVar5 = DAT_0002b298;
  local_16 = *(short **)(param_1 + 0x18);
  iVar1 = *(int *)(DAT_0002c0ba + 0x46);
  sVar10 = *(short *)(param_1 + 0x14) + -1;
  if (sVar10 != -1) {
    uVar11 = (uint)DAT_0002b296;
    do {
      iVar13 = (int)*local_16;
      if (*(char *)((int)&DAT_0002b2a2 + iVar13) == '\0') {
        iVar14 = iVar13 * 0xc + iVar9;
        piVar12 = (int *)(iVar13 * 0xc + iVar1);
        *(undefined1 *)((int)&DAT_0002b2a2 + iVar13) = 1;
        sVar2 = (short)(*piVar12 >> (uVar11 & 0x3f)) - sVar7;
        sVar3 = (short)(piVar12[1] >> (uVar11 & 0x3f)) - sVar6;
        sVar4 = (short)(piVar12[2] >> (uVar11 & 0x3f)) - sVar5;
        *(short *)(iVar14 + 2) =
             (short)((int)sVar4 * (int)psVar8[6] +
                     (int)sVar3 * (int)psVar8[3] + (int)sVar2 * (int)*psVar8 + 0x800 >> 0xc);
        *(short *)(iVar14 + 6) =
             (short)((int)sVar4 * (int)psVar8[7] +
                     (int)sVar3 * (int)psVar8[4] + (int)sVar2 * (int)psVar8[1] + 0x800 >> 0xc);
        *(short *)(iVar14 + 10) =
             (short)((int)sVar4 * (int)psVar8[8] +
                     (int)sVar3 * (int)psVar8[5] + (int)sVar2 * (int)psVar8[2] + 0x800 >> 0xc);
        if (*(short *)(iVar14 + 10) < 5) {
          (&DAT_0002bbf8)[iVar13 * 2] = -0x80000000;
        }
        else {
          (&DAT_0002bbf8)[iVar13 * 2] =
               ((int)*(short *)(iVar14 + 2) * (int)DAT_00027050) / (int)*(short *)(iVar14 + 10);
          (&DAT_0002bbfc)[iVar13 * 2] =
               ((int)DAT_0002704e * -(int)*(short *)(iVar14 + 6)) / (int)*(short *)(iVar14 + 10);
        }
      }
      local_16 = local_16 + 1;
      sVar10 = sVar10 + -1;
    } while (sVar10 != -1);
  }
  return;
}



// FUN_0000b922 @ 0xb922

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000b922(void)

{
  undefined4 *puVar1;
  short sVar2;
  short *psVar3;
  byte *pbVar4;
  int *piVar5;
  int iVar6;
  
  iVar6 = *(int *)(DAT_0002c0ba + 0x4e);
  DAT_0002c4c6 = *(int *)(DAT_0002c0ba + 0x4a);
  DAT_0002c0ae = *(undefined2 *)(DAT_0002c0ba + 0x3a);
  DAT_0002b296 = *(undefined2 *)(DAT_0002c0ba + 0x34);
  _DAT_0002b286 = *(int *)(DAT_0002c0ba + 0x46) + *(short *)(DAT_0002c0ba + 0x38) * 0xc;
  puVar1 = *(undefined4 **)(DAT_0002c0ba + 0x46);
  DAT_0002b28a = *puVar1;
  DAT_0002b28e = puVar1[1];
  DAT_0002b292 = puVar1[2];
  piVar5 = &DAT_0002bbf8;
  sVar2 = *(short *)(DAT_0002c0ba + 0x38) + -1;
  DAT_0002c0b6 = iVar6;
  if (sVar2 != -1) {
    psVar3 = (short *)(iVar6 + 10);
    do {
      if (*psVar3 < 5) {
        *piVar5 = -0x80000000;
      }
      else {
        *piVar5 = ((int)*(short *)(iVar6 + 2) * (int)DAT_00027050) / (int)*psVar3;
        piVar5[1] = ((int)DAT_0002704e * -(int)*(short *)(iVar6 + 6)) / (int)*psVar3;
      }
      psVar3 = psVar3 + 6;
      iVar6 = iVar6 + 0xc;
      piVar5 = piVar5 + 2;
      sVar2 = sVar2 + -1;
    } while (sVar2 != -1);
  }
  sVar2 = *(short *)(DAT_0002c0ba + 0x3c) + -1;
  if (sVar2 != -1) {
    pbVar4 = (byte *)(DAT_0002c4c6 + 0x13);
    do {
      *pbVar4 = *pbVar4 | 0x60;
      pbVar4 = pbVar4 + 0x20;
      sVar2 = sVar2 + -1;
    } while (sVar2 != -1);
  }
  return;
}



// FUN_0000ba18 @ 0xba18

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

undefined4 FUN_0000ba18(void)

{
  undefined4 *puVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  undefined2 uVar5;
  undefined2 uVar6;
  undefined2 uVar7;
  short sVar8;
  short sVar9;
  uint uVar10;
  uint uVar11;
  ushort uVar12;
  short sVar14;
  int iVar13;
  short sVar15;
  short *psVar16;
  short *psVar17;
  int iVar18;
  short *psVar19;
  byte *pbVar20;
  int *piVar21;
  int iVar22;
  short *psVar23;
  int *local_36;
  char *local_30;
  undefined1 auStack_28 [2];
  short local_26;
  short local_22;
  short local_1e;
  undefined2 local_1a;
  undefined2 local_16;
  undefined2 local_12;
  
  DAT_0002b29e = &DAT_00023c74;
  sVar15 = *(short *)(SUB_000207fc + 0xaa);
  sVar14 = *(short *)(SUB_000207fc + 0xb2);
  DAT_0002b296 = *(short *)(DAT_0002c0ba + 0xd);
  uVar12 = *(short *)((int)DAT_0002c0ba + 0x36) - DAT_0002b296;
  if ((int)((uint)uVar12 << 0x10) < 0) {
    uVar10 = -(int)(short)uVar12;
    DAT_0002b29c = (short)(DAT_00023d68 >> (uVar10 & 0x3f));
    DAT_0002b29a = (short)(DAT_00023d64 >> (uVar10 & 0x3f));
    DAT_0002b298 = (short)(DAT_00023d60 >> (uVar10 & 0x3f));
  }
  else {
    uVar10 = (uint)(short)uVar12;
    DAT_0002b29c = (short)(DAT_00023d68 << (uVar10 & 0x3f));
    DAT_0002b29a = (short)(DAT_00023d64 << (uVar10 & 0x3f));
    DAT_0002b298 = (short)(DAT_00023d60 << (uVar10 & 0x3f));
  }
  piVar21 = (int *)*DAT_0002c0ba;
  if (piVar21 == (int *)0x0) {
    if ((*(byte *)((int)DAT_0002c0ba + 0x16) & 2) != 0) goto LAB_0000bd16;
  }
  else {
    if (*piVar21 == 0) {
      return 4;
    }
    if (*(int *)(piVar21[1] + 0xe) == 0) {
      uVar10 = 0;
    }
    else {
      uVar10 = (**(code **)(piVar21[1] + 0xe))(piVar21,1);
      if ((uVar10 & 4) != 0) {
        return 4;
      }
    }
    if ((*(byte *)((int)DAT_0002c0ba + 0x15) & 0x10) != 0) {
      if ((uVar10 & 1) == 0) {
        FUN_00002d02((int)*(short *)(piVar21 + 8),(int)*(short *)((int)piVar21 + 0x22),
                     (int)*(short *)(piVar21 + 9),&DAT_00023c5c);
      }
      FUN_00002346(&DAT_00023c5c,&DAT_00023c74,&DAT_0002b274);
      DAT_0002b29e = &DAT_0002b274;
      FUN_000030d8(&DAT_00023c5c);
      uVar7 = DAT_00021574;
      uVar6 = DAT_00021572;
      uVar5 = DAT_00021570;
      if ((short)uVar12 < 0) {
        uVar11 = -(int)(short)uVar12;
        local_26 = (short)(DAT_00023d68 - DAT_0002c0ba[1] >> (uVar11 & 0x3f));
        local_22 = (short)(DAT_00023d64 - DAT_0002c0ba[2] >> (uVar11 & 0x3f));
        local_1e = (short)(DAT_00023d60 - DAT_0002c0ba[3] >> (uVar11 & 0x3f));
      }
      else {
        uVar11 = (uint)(short)uVar12;
        local_26 = (short)(DAT_00023d68 - DAT_0002c0ba[1] << (uVar11 & 0x3f));
        local_22 = (short)(DAT_00023d64 - DAT_0002c0ba[2] << (uVar11 & 0x3f));
        local_1e = (short)(DAT_00023d60 - DAT_0002c0ba[3] << (uVar11 & 0x3f));
      }
      if (((*(byte *)((int)DAT_0002c0ba + 0x16) & 1) == 0) || (sVar14 == 0)) {
        FUN_00003220(1,auStack_28,auStack_28,&DAT_00023c5c);
        if ((sVar15 != 0) && (iVar22 = FUN_000049ec(DAT_0002c0ba,DAT_0002c0ba[7]), iVar22 != 0)) {
          FUN_00004b0c(DAT_0002c0ba);
        }
      }
      else {
        local_1a = DAT_00021574;
        local_16 = DAT_00021572;
        local_12 = DAT_00021570;
        FUN_00003220(2,auStack_28,auStack_28,&DAT_00023c5c);
        DAT_00021574 = local_1a;
        DAT_00021572 = local_16;
        DAT_00021570 = local_12;
        if (sVar15 != 0) {
          FUN_000049ec(DAT_0002c0ba,DAT_0002c0ba[7]);
        }
        FUN_00004b0c(DAT_0002c0ba);
        DAT_00021570 = uVar5;
        DAT_00021572 = uVar6;
        DAT_00021574 = uVar7;
      }
      DAT_0002b29c = local_26;
      DAT_0002b29a = local_22;
      DAT_0002b298 = local_1e;
      if ((uVar10 & 2) != 0) {
        (**(code **)(piVar21[1] + 0xe))(piVar21,2);
      }
      goto LAB_0000bd16;
    }
  }
  if ((sVar15 != 0) && (iVar22 = FUN_000049ec(DAT_0002c0ba,DAT_0002c0ba[7]), iVar22 != 0)) {
    FUN_00004b0c(DAT_0002c0ba);
  }
LAB_0000bd16:
  DAT_0002c0b6 = *(int *)((int)DAT_0002c0ba + 0x4e);
  DAT_0002c0ae = *(undefined2 *)((int)DAT_0002c0ba + 0x3a);
  _DAT_0002b286 = *(int *)((int)DAT_0002c0ba + 0x46) + *(short *)(DAT_0002c0ba + 0xe) * 0xc;
  puVar1 = *(undefined4 **)((int)DAT_0002c0ba + 0x46);
  DAT_0002b28a = *puVar1;
  DAT_0002b28e = puVar1[1];
  DAT_0002b292 = puVar1[2];
  sVar15 = *(short *)(DAT_0002c0ba + 0xe);
  sVar14 = sVar15;
  if (sVar15 < 0) {
    sVar14 = sVar15 + 3;
  }
  iVar22 = *(int *)((int)DAT_0002c0ba + 0x4a);
  puVar1 = &DAT_0002b2a2;
  for (iVar18 = (int)(sVar14 >> 2); DAT_0002c4c6 = iVar22, iVar18 != -1; iVar18 = iVar18 + -1) {
    *puVar1 = 0;
    iVar22 = DAT_0002c4c6;
    puVar1 = puVar1 + 1;
  }
  iVar18 = *(short *)(DAT_0002c0ba + 0xf) + -1;
  if (iVar18 != -1) {
    pbVar20 = (byte *)(iVar22 + 0x13);
    do {
      if ((*pbVar20 & 0x60) == 0x20) {
        *pbVar20 = *pbVar20 | 0x40;
        iVar13 = *(short *)(iVar22 + 0x14) + -1;
        psVar16 = *(short **)(iVar22 + 0x18);
        if (iVar13 != -1) {
          do {
            do {
              psVar17 = psVar16 + 1;
              *(undefined1 *)((int)&DAT_0002b2a2 + (int)*psVar16) = 1;
              uVar12 = (ushort)((uint)iVar13 >> 0x10);
              sVar14 = (short)iVar13 + -1;
              iVar13 = CONCAT22(uVar12,sVar14);
              psVar16 = psVar17;
            } while (sVar14 != -1);
            iVar13 = (uint)uVar12 * 0x10000 + -1;
          } while (uVar12 != 0);
        }
      }
      else {
        *pbVar20 = *pbVar20 & 0xdf;
      }
      pbVar20 = pbVar20 + 0x20;
      iVar22 = iVar22 + 0x20;
      iVar18 = iVar18 + -1;
    } while (iVar18 != -1);
  }
  psVar16 = DAT_0002b29e;
  sVar9 = DAT_0002b29c;
  sVar8 = DAT_0002b29a;
  sVar14 = DAT_0002b298;
  local_36 = *(int **)((int)DAT_0002c0ba + 0x46);
  piVar21 = &DAT_0002bbf8;
  local_30 = (char *)&DAT_0002b2a2;
  sVar15 = sVar15 + -1;
  if (sVar15 != -1) {
    uVar10 = (uint)DAT_0002b296;
    psVar17 = (short *)(DAT_0002c0b6 + 10);
    psVar23 = (short *)(DAT_0002c0b6 + 6);
    psVar19 = (short *)(DAT_0002c0b6 + 2);
    do {
      if (*local_30 != '\0') {
        sVar2 = (short)(*local_36 >> (uVar10 & 0x3f)) - sVar9;
        sVar3 = (short)(local_36[1] >> (uVar10 & 0x3f)) - sVar8;
        sVar4 = (short)(local_36[2] >> (uVar10 & 0x3f)) - sVar14;
        *psVar19 = (short)((int)sVar4 * (int)psVar16[6] +
                           (int)sVar3 * (int)psVar16[3] + (int)sVar2 * (int)*psVar16 + 0x800 >> 0xc)
        ;
        *psVar23 = (short)((int)sVar4 * (int)psVar16[7] +
                           (int)sVar3 * (int)psVar16[4] + (int)sVar2 * (int)psVar16[1] + 0x800 >>
                          0xc);
        *psVar17 = (short)((int)sVar4 * (int)psVar16[8] +
                           (int)sVar3 * (int)psVar16[5] + (int)sVar2 * (int)psVar16[2] + 0x800 >>
                          0xc);
        if (*psVar17 < 5) {
          *piVar21 = -0x80000000;
        }
        else {
          *piVar21 = ((int)*psVar19 * (int)DAT_00027050) / (int)*psVar17;
          piVar21[1] = ((int)-*psVar23 * (int)DAT_0002704e) / (int)*psVar17;
        }
      }
      psVar17 = psVar17 + 6;
      psVar23 = psVar23 + 6;
      psVar19 = psVar19 + 6;
      local_36 = local_36 + 3;
      piVar21 = piVar21 + 2;
      sVar15 = sVar15 + -1;
      local_30 = local_30 + 1;
    } while (sVar15 != -1);
  }
  return 0;
}



// FUN_0000bf3e @ 0xbf3e

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000bf3e(void)

{
  bool bVar1;
  bool bVar2;
  undefined2 uVar3;
  int iVar4;
  int iVar5;
  ushort uVar6;
  short sVar8;
  int iVar7;
  byte *pbVar9;
  int *piVar10;
  int *piVar11;
  int *piVar12;
  bool bVar13;
  bool bVar14;
  int aiStack_374 [220];
  
  bVar13 = _DAT_00023d86 != 0;
  bVar14 = DAT_00023d88 != 1;
  piVar12 = aiStack_374;
  DAT_00026fe0 = 0;
  DAT_000219a8 = 1;
  DAT_0002c0ac = 0;
  bVar2 = false;
  iVar7 = DAT_000215a0 + -1;
  piVar11 = &DAT_00022994;
  if (iVar7 != -1) {
    do {
      piVar10 = piVar11 + 1;
      DAT_0002c0ba = *piVar11;
      FUN_0000b922();
      _DAT_0002c0aa = *(ushort *)(DAT_0002c0ba + 0x16) & 8;
      _DAT_0002c0a8 = 0;
      DAT_0002c2c2 = &DAT_0002c2c6;
      DAT_0002c0be = &DAT_0002c0c2;
      if ((*(byte *)(DAT_0002c0ba + 0x17) & 0x20) == 0) {
        FUN_0000b16e();
      }
      else {
        FUN_0000b01c(0);
      }
      FUN_0000b574();
      uVar6 = (ushort)((uint)iVar7 >> 0x10);
      sVar8 = (short)iVar7 + -1;
      iVar7 = CONCAT22(uVar6,sVar8);
      piVar11 = piVar10;
    } while ((sVar8 != -1) || (iVar7 = (uint)uVar6 * 0x10000 + -1, uVar6 != 0));
  }
  DAT_000219aa = DAT_000219bc._2_2_ - DAT_000219aa;
  iVar7 = (DAT_000219bc - DAT_000215a0) + -1;
  piVar11 = &DAT_00022994 + DAT_000215a0;
  if (iVar7 != -1) {
    do {
      piVar10 = piVar11 + 1;
      DAT_0002c0ba = *piVar11;
      if ((*(byte *)(DAT_0002c0ba + 0x16) & 2) == 0) {
        if (*(int *)(DAT_0002c0ba + 0x2e) == 0) goto LAB_0000c046;
        *piVar12 = DAT_0002c0ba;
        piVar12 = piVar12 + 1;
      }
      else {
        bVar2 = true;
LAB_0000c046:
        iVar4 = FUN_0000ba18();
        if (iVar4 != 4) {
          if (iVar7 < DAT_000219aa) {
            if (bVar13 || bVar14) {
              DAT_0002c0ac = 0;
            }
            else {
              bVar1 = *(int *)(DAT_0002c0ba + 0x1c) <= *(int *)(DAT_0002c0ba + 0x20);
              DAT_0002c0ac = (byte)-bVar1 & 1;
              if (bVar1) {
                if ((*(byte *)(DAT_0002c0ba + 0x15) & 0x20) == 0) {
                  iVar4 = *(short *)(DAT_0002c0ba + 0x3c) + -1;
                  if (iVar4 != -1) {
                    pbVar9 = (byte *)(DAT_0002c4c6 + 0x13);
                    do {
                      *pbVar9 = *pbVar9 & 0xf7;
                      pbVar9 = pbVar9 + 0x20;
                      iVar4 = iVar4 + -1;
                    } while (iVar4 != -1);
                  }
                  *(byte *)(DAT_0002c0ba + 0x15) = *(byte *)(DAT_0002c0ba + 0x15) | 0x20;
                  DAT_0002c0ac = 2;
                }
                goto LAB_0000c0de;
              }
            }
            *(uint *)(DAT_0002c0ba + 0x14) = *(uint *)(DAT_0002c0ba + 0x14) & 0xffdfffff;
          }
          else {
            DAT_0002c0ac = 0;
          }
LAB_0000c0de:
          if (bVar2) {
            FUN_00016d9a(DAT_0002c0ba);
            bVar2 = false;
            _DAT_0002c0aa = *(ushort *)(DAT_0002c0ba + 0x16) & 8;
            _DAT_0002c0a8 = 0;
            DAT_0002c2c2 = &DAT_0002c2c6;
            DAT_0002c0be = &DAT_0002c0c2;
            FUN_0000b240();
            FUN_0000b574();
          }
          else {
            _DAT_0002c0aa = *(ushort *)(DAT_0002c0ba + 0x16) & 8;
            _DAT_0002c0a8 = 0;
            DAT_0002c2c2 = &DAT_0002c2c6;
            DAT_0002c0be = &DAT_0002c0c2;
            if ((*(byte *)(DAT_0002c0ba + 0x17) & 0x20) == 0) {
              FUN_0000b16e();
            }
            else {
              FUN_0000b01c(0);
            }
            FUN_0000b574();
          }
        }
      }
      uVar6 = (ushort)((uint)iVar7 >> 0x10);
      sVar8 = (short)iVar7 + -1;
      iVar7 = CONCAT22(uVar6,sVar8);
      piVar11 = piVar10;
    } while ((sVar8 != -1) || (iVar7 = (uint)uVar6 * 0x10000 + -1, uVar6 != 0));
  }
  uVar3 = DAT_000219a8;
  iVar7 = ((int)piVar12 - (int)aiStack_374 >> 2) + -1;
  piVar12 = aiStack_374;
  if (iVar7 != -1) {
    do {
      piVar11 = piVar12 + 1;
      DAT_0002c0ba = *piVar12;
      iVar4 = *(int *)(DAT_0002c0ba + 0x2e);
      DAT_000219a8 = *(undefined2 *)(iVar4 + 2);
      iVar5 = FUN_0000ba18();
      if (iVar5 != 4) {
        if (bVar13 || bVar14) {
          DAT_0002c0ac = 0;
LAB_0000c22e:
          *(uint *)(DAT_0002c0ba + 0x14) = *(uint *)(DAT_0002c0ba + 0x14) & 0xffdfffff;
        }
        else {
          bVar2 = *(int *)(DAT_0002c0ba + 0x1c) <= *(int *)(DAT_0002c0ba + 0x20);
          DAT_0002c0ac = (byte)-bVar2 & 1;
          if (!bVar2) goto LAB_0000c22e;
          if ((*(byte *)(DAT_0002c0ba + 0x15) & 0x20) == 0) {
            iVar5 = *(short *)(DAT_0002c0ba + 0x3c) + -1;
            if (iVar5 != -1) {
              pbVar9 = (byte *)(DAT_0002c4c6 + 0x13);
              do {
                *pbVar9 = *pbVar9 & 0xf7;
                pbVar9 = pbVar9 + 0x20;
                iVar5 = iVar5 + -1;
              } while (iVar5 != -1);
            }
            *(byte *)(DAT_0002c0ba + 0x15) = *(byte *)(DAT_0002c0ba + 0x15) | 0x20;
            DAT_0002c0ac = 2;
          }
        }
        _DAT_0002c0aa = *(ushort *)(DAT_0002c0ba + 0x16) & 8;
        _DAT_0002c0a8 = 0;
        DAT_0002c2c2 = &DAT_0002c2c6;
        DAT_0002c0be = &DAT_0002c0c2;
        if ((*(byte *)(DAT_0002c0ba + 0x17) & 0x20) == 0) {
          FUN_0000b16e();
        }
        else {
          FUN_0000b01c(0);
        }
        FUN_0000b574();
      }
      *(undefined2 *)(iVar4 + 2) = DAT_000219a8;
      uVar6 = (ushort)((uint)iVar7 >> 0x10);
      sVar8 = (short)iVar7 + -1;
      iVar7 = CONCAT22(uVar6,sVar8);
      piVar12 = piVar11;
    } while ((sVar8 != -1) || (iVar7 = (uint)uVar6 * 0x10000 + -1, uVar6 != 0));
  }
  iVar7 = DAT_0002158c;
  DAT_000219a8 = uVar3;
  if (DAT_00023d88 == 1) {
    *(uint *)(DAT_0002158c + 0x14) = *(uint *)(DAT_0002158c + 0x14) | 4;
    DAT_0002c0ba = iVar7;
    FUN_0000b922();
    _DAT_0002c0aa = *(ushort *)(DAT_0002c0ba + 0x16) & 8;
    _DAT_0002c0a8 = 0;
    DAT_0002c2c2 = &DAT_0002c2c6;
    DAT_0002c0be = &DAT_0002c0c2;
    if ((*(byte *)(DAT_0002c0ba + 0x17) & 0x20) == 0) {
      FUN_0000b16e();
    }
    else {
      FUN_0000b01c(0);
    }
    FUN_0000b574();
  }
  return;
}



// FUN_0000c310 @ 0xc310

undefined2 FUN_0000c310(short *param_1,int param_2,short *param_3,int param_4,int *param_5)

{
  int iVar1;
  int *piVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  undefined2 uVar6;
  int iVar7;
  int *piVar8;
  
  uVar6 = 0;
  iVar7 = (int)*param_1;
  iVar5 = param_2 + param_3[iVar7 + -1] * 0xc;
  piVar8 = param_5;
  do {
    if (iVar7 == 0) {
      *param_1 = (short)((int)piVar8 - (int)param_5 >> 3);
      return uVar6;
    }
    iVar1 = param_2 + *param_3 * 0xc;
    piVar2 = (int *)(param_4 + *param_3 * 8);
    if (*(short *)(iVar1 + 10) < 5) {
      uVar6 = 1;
      if (4 < *(short *)(iVar5 + 10)) {
        iVar3 = (int)*(short *)(iVar5 + 10) - (int)*(short *)(iVar1 + 10);
        iVar4 = 5 - *(short *)(iVar1 + 10);
        *piVar8 = ((int)(short)(*(short *)(iVar1 + 2) +
                               (short)((iVar4 * ((int)*(short *)(iVar5 + 2) -
                                                (int)*(short *)(iVar1 + 2))) / iVar3)) *
                  (int)DAT_00027050) / 5;
        piVar8[1] = ((int)(short)((short)((iVar4 * ((int)*(short *)(iVar1 + 6) -
                                                   (int)*(short *)(iVar5 + 6))) / iVar3) -
                                 *(short *)(iVar1 + 6)) * (int)DAT_0002704e) / 5;
        goto LAB_0000c44e;
      }
    }
    else {
      if (*(short *)(iVar5 + 10) < 5) {
        uVar6 = 1;
        iVar3 = (int)*(short *)(iVar1 + 10) - (int)*(short *)(iVar5 + 10);
        iVar4 = 5 - *(short *)(iVar5 + 10);
        *piVar8 = ((int)(short)(*(short *)(iVar5 + 2) +
                               (short)((iVar4 * ((int)*(short *)(iVar1 + 2) -
                                                (int)*(short *)(iVar5 + 2))) / iVar3)) *
                  (int)DAT_00027050) / 5;
        piVar8[1] = ((int)(short)((short)((iVar4 * ((int)*(short *)(iVar5 + 6) -
                                                   (int)*(short *)(iVar1 + 6))) / iVar3) -
                                 *(short *)(iVar5 + 6)) * (int)DAT_0002704e) / 5;
        piVar8[2] = *piVar2;
        piVar8[3] = piVar2[1];
        piVar8 = piVar8 + 2;
      }
      else {
        *piVar8 = *piVar2;
        piVar8[1] = piVar2[1];
      }
LAB_0000c44e:
      piVar8 = piVar8 + 2;
    }
    iVar7 = iVar7 + -1;
    param_3 = param_3 + 1;
    iVar5 = iVar1;
  } while( true );
}



// FUN_0000c478 @ 0xc478

int FUN_0000c478(int *param_1,int param_2,int *param_3)

{
  int iVar1;
  int iVar2;
  longlong lVar3;
  int iVar4;
  int iVar5;
  int *piVar6;
  int *piVar7;
  int *piVar8;
  
  piVar6 = param_3;
  if (param_2 != 0) {
    piVar8 = param_1 + 1;
    piVar7 = param_1 + param_2 * 2 + -2;
    do {
      if (*param_1 < (int)DAT_0002701a) {
        iVar5 = (int)DAT_0002701a;
        if (iVar5 <= *piVar7) {
          iVar1 = *piVar7;
          iVar2 = *param_1;
          iVar4 = *param_1;
          *piVar6 = iVar5;
          lVar3 = (longlong)(piVar7[1] - *piVar8) * (longlong)(iVar5 - iVar4);
          piVar6[1] = *piVar8 + (int)(((longlong)(int)((ulonglong)lVar3 >> 0x20) << 0x20 |
                                      (longlong)(int)lVar3) / (longlong)(iVar1 - iVar2));
          goto LAB_0000c510;
        }
      }
      else {
        if (*piVar7 < (int)DAT_0002701a) {
          iVar5 = *param_1;
          iVar1 = *piVar7;
          iVar4 = (int)DAT_0002701a;
          iVar2 = *piVar7;
          *piVar6 = iVar4;
          lVar3 = (longlong)(*piVar8 - piVar7[1]) * (longlong)(iVar4 - iVar2);
          piVar6[1] = piVar7[1] +
                      (int)(((longlong)(int)((ulonglong)lVar3 >> 0x20) << 0x20 |
                            (longlong)(int)lVar3) / (longlong)(iVar5 - iVar1));
          piVar6[2] = *param_1;
          piVar6[3] = *piVar8;
          piVar6 = piVar6 + 2;
        }
        else {
          *piVar6 = *param_1;
          piVar6[1] = *piVar8;
        }
LAB_0000c510:
        piVar6 = piVar6 + 2;
      }
      param_2 = param_2 + -1;
      piVar8 = piVar8 + 2;
      piVar7 = param_1;
      param_1 = param_1 + 2;
    } while (param_2 != 0);
  }
  return (int)piVar6 - (int)param_3 >> 3;
}



// FUN_0000c52a @ 0xc52a

int FUN_0000c52a(int *param_1,int param_2,int *param_3)

{
  int iVar1;
  int iVar2;
  longlong lVar3;
  int iVar4;
  int iVar5;
  int *piVar6;
  int *piVar7;
  int *piVar8;
  
  piVar6 = param_3;
  if (param_2 != 0) {
    piVar8 = param_1 + 1;
    piVar7 = param_1 + param_2 * 2 + -2;
    do {
      if ((int)DAT_00027018 < *param_1) {
        iVar5 = (int)DAT_00027018;
        if (*piVar7 <= iVar5) {
          iVar1 = *piVar7;
          iVar2 = *param_1;
          iVar4 = *param_1;
          *piVar6 = iVar5;
          lVar3 = (longlong)(piVar7[1] - *piVar8) * (longlong)(iVar5 - iVar4);
          piVar6[1] = *piVar8 + (int)(((longlong)(int)((ulonglong)lVar3 >> 0x20) << 0x20 |
                                      (longlong)(int)lVar3) / (longlong)(iVar1 - iVar2));
          goto LAB_0000c5c2;
        }
      }
      else {
        if ((int)DAT_00027018 < *piVar7) {
          iVar5 = *param_1;
          iVar1 = *piVar7;
          iVar4 = (int)DAT_00027018;
          iVar2 = *piVar7;
          *piVar6 = iVar4;
          lVar3 = (longlong)(*piVar8 - piVar7[1]) * (longlong)(iVar4 - iVar2);
          piVar6[1] = piVar7[1] +
                      (int)(((longlong)(int)((ulonglong)lVar3 >> 0x20) << 0x20 |
                            (longlong)(int)lVar3) / (longlong)(iVar5 - iVar1));
          piVar6[2] = *param_1;
          piVar6[3] = *piVar8;
          piVar6 = piVar6 + 2;
        }
        else {
          *piVar6 = *param_1;
          piVar6[1] = *piVar8;
        }
LAB_0000c5c2:
        piVar6 = piVar6 + 2;
      }
      param_2 = param_2 + -1;
      piVar8 = piVar8 + 2;
      piVar7 = param_1;
      param_1 = param_1 + 2;
    } while (param_2 != 0);
  }
  return (int)piVar6 - (int)param_3 >> 3;
}



// FUN_0000c5dc @ 0xc5dc

int FUN_0000c5dc(int *param_1,int param_2,int *param_3)

{
  longlong lVar1;
  int iVar2;
  int *piVar3;
  int *piVar4;
  int *piVar5;
  
  piVar3 = param_3;
  if (param_2 != 0) {
    piVar5 = param_1 + 1;
    piVar4 = param_1 + param_2 * 2 + -2;
    do {
      if (*piVar5 < (int)DAT_00027016) {
        iVar2 = (int)DAT_00027016;
        if (iVar2 <= piVar4[1]) {
          lVar1 = (longlong)(*piVar4 - *param_1) * (longlong)(iVar2 - *piVar5);
          *piVar3 = *param_1 +
                    (int)(((longlong)(int)((ulonglong)lVar1 >> 0x20) << 0x20 | (longlong)(int)lVar1)
                         / (longlong)(piVar4[1] - *piVar5));
          piVar3[1] = iVar2;
          goto LAB_0000c678;
        }
      }
      else {
        if (piVar4[1] < (int)DAT_00027016) {
          iVar2 = (int)DAT_00027016;
          lVar1 = (longlong)(*param_1 - *piVar4) * (longlong)(iVar2 - piVar4[1]);
          *piVar3 = *piVar4 + (int)(((longlong)(int)((ulonglong)lVar1 >> 0x20) << 0x20 |
                                    (longlong)(int)lVar1) / (longlong)(*piVar5 - piVar4[1]));
          piVar3[1] = iVar2;
          piVar3[2] = *param_1;
          piVar3[3] = *piVar5;
          piVar3 = piVar3 + 2;
        }
        else {
          *piVar3 = *param_1;
          piVar3[1] = *piVar5;
        }
LAB_0000c678:
        piVar3 = piVar3 + 2;
      }
      param_2 = param_2 + -1;
      piVar5 = piVar5 + 2;
      piVar4 = param_1;
      param_1 = param_1 + 2;
    } while (param_2 != 0);
  }
  return (int)piVar3 - (int)param_3 >> 3;
}



// FUN_0000c692 @ 0xc692

int FUN_0000c692(int *param_1,int param_2,int *param_3)

{
  longlong lVar1;
  int iVar2;
  int *piVar3;
  int *piVar4;
  int *piVar5;
  
  piVar3 = param_3;
  if (param_2 != 0) {
    piVar5 = param_1 + 1;
    piVar4 = param_1 + param_2 * 2 + -2;
    do {
      if ((int)DAT_00020b2e < *piVar5) {
        iVar2 = (int)DAT_00020b2e;
        if (piVar4[1] <= iVar2) {
          lVar1 = (longlong)(*piVar4 - *param_1) * (longlong)(iVar2 - *piVar5);
          *piVar3 = *param_1 +
                    (int)(((longlong)(int)((ulonglong)lVar1 >> 0x20) << 0x20 | (longlong)(int)lVar1)
                         / (longlong)(piVar4[1] - *piVar5));
          piVar3[1] = iVar2;
          goto LAB_0000c72e;
        }
      }
      else {
        if ((int)DAT_00020b2e < piVar4[1]) {
          iVar2 = (int)DAT_00020b2e;
          lVar1 = (longlong)(*param_1 - *piVar4) * (longlong)(iVar2 - piVar4[1]);
          *piVar3 = *piVar4 + (int)(((longlong)(int)((ulonglong)lVar1 >> 0x20) << 0x20 |
                                    (longlong)(int)lVar1) / (longlong)(*piVar5 - piVar4[1]));
          piVar3[1] = iVar2;
          piVar3[2] = *param_1;
          piVar3[3] = *piVar5;
          piVar3 = piVar3 + 2;
        }
        else {
          *piVar3 = *param_1;
          piVar3[1] = *piVar5;
        }
LAB_0000c72e:
        piVar3 = piVar3 + 2;
      }
      param_2 = param_2 + -1;
      piVar5 = piVar5 + 2;
      piVar4 = param_1;
      param_1 = param_1 + 2;
    } while (param_2 != 0);
  }
  return (int)piVar3 - (int)param_3 >> 3;
}



// FUN_0000c748 @ 0xc748

void FUN_0000c748(void)

{
  int iVar1;
  undefined2 *puVar2;
  
  if (DAT_00027004 != (undefined2 *)0x0) {
    puVar2 = DAT_00027004;
    for (iVar1 = 0; iVar1 < *(short *)(SUB_000207fc + 400); iVar1 = iVar1 + 1) {
      *puVar2 = 0;
      puVar2[1] = 1;
      puVar2[2] = DAT_00021012;
      puVar2[3] = DAT_00023fd0;
      puVar2 = puVar2 + 4;
    }
  }
  return;
}



// FUN_0000c786 @ 0xc786

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000c786(void)

{
  FUN_0000c748();
  FUN_0001b90c();
  DAT_0002c598 = 1;
  _DAT_0002c596 = 0;
  return;
}



// FUN_0000c79e @ 0xc79e

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_0000c79e(int param_1)

{
  short sVar1;
  int iVar2;
  uint uVar3;
  int iVar4;
  int iVar5;
  short sVar6;
  short sVar7;
  short sVar8;
  uint unaff_D4;
  short unaff_D6w;
  short sVar9;
  short *psVar10;
  short *psVar11;
  uint unaff_A4;
  short local_16;
  short local_14;
  short local_12;
  uint local_10;
  short *local_c;
  short *local_8;
  
  if ((((DAT_00027008 == (short *)0x0) || (*(short *)(SUB_000207fc + 0x18a) != DAT_0002c4cc._0_2_))
      || (*(short *)(SUB_000207fc + 0x18c) != DAT_0002c4cc._2_2_)) ||
     (((*(short *)(SUB_000207fc + 0x18e) != DAT_0002c4d0._0_2_ ||
       (*(short *)(SUB_000207fc + 400) != DAT_0002c4d0._2_2_)) ||
      (DAT_0002c4dc != *(int *)(SUB_000207fc + 0x19a))))) {
    DAT_0002c4cc = *(undefined4 *)(SUB_000207fc + 0x18a);
    DAT_0002c4d0 = *(undefined4 *)(SUB_000207fc + 0x18e);
    DAT_0002c4d4 = *(undefined4 *)(SUB_000207fc + 0x192);
    DAT_0002c4d8 = *(undefined4 *)(SUB_000207fc + 0x196);
    DAT_0002c4dc = *(int *)(SUB_000207fc + 0x19a);
    if (DAT_00027008 != (short *)0x0) {
      FUN_00002008(DAT_00027008);
      DAT_00027008 = (short *)0x0;
    }
    if (DAT_00027004 != (short *)0x0) {
      FUN_00002008(DAT_00027004);
      DAT_00027004 = (short *)0x0;
    }
    if (DAT_0002100e != 1) {
      DAT_00027008 = (short *)FUN_00002080(DAT_0002c4d0._2_2_ * 0x14);
      DAT_00027004 = (short *)FUN_00002080(DAT_0002c4d0._2_2_ * 0x14);
    }
    FUN_0000c748();
  }
  iVar4 = DAT_0002c4dc;
  iVar2 = (int)DAT_00021010;
  iVar5 = ((int)DAT_0002c4d0._2_2_ * (DAT_00021584 + DAT_00027054)) / iVar2;
  DAT_0002157c = ((int)DAT_0002c4d0._2_2_ * (DAT_0002157c + DAT_00027054)) / iVar2;
  DAT_00021588 = ((int)DAT_0002c4cc._2_2_ +
                 (int)((int)DAT_0002c4d0._2_2_ * (DAT_00021588 + (int)DAT_00027052)) / iVar2) *
                 0x10000 + 0x8000;
  DAT_00021580 = ((int)DAT_0002c4cc._2_2_ +
                 (int)((int)DAT_0002c4d0._2_2_ * (DAT_00021580 + (int)DAT_00027052)) / iVar2) *
                 0x10000 + 0x8000;
  DAT_00021584._2_2_ = (short)iVar5;
  sVar8 = DAT_00021584._2_2_;
  if (iVar5 == DAT_0002157c) {
    uVar3 = DAT_00021580;
  }
  else if (DAT_0002157c < iVar5) {
    uVar3 = (int)(DAT_00021588 - DAT_00021580) / (iVar5 - DAT_0002157c);
    unaff_D6w = 1;
    unaff_D4 = DAT_00021580;
    unaff_A4 = uVar3;
    DAT_00021584._2_2_ = DAT_0002157c._2_2_;
    local_10 = DAT_00021588;
  }
  else {
    uVar3 = (int)(DAT_00021580 - DAT_00021588) / (DAT_0002157c - iVar5);
    unaff_D6w = 0;
    unaff_D4 = DAT_00021588;
    unaff_A4 = uVar3;
    sVar8 = DAT_0002157c._2_2_;
    local_10 = DAT_00021580;
  }
  if (DAT_00021578 == 0xf) {
    local_12 = *(short *)(SUB_000207fc + 0x194);
    DAT_00021584._2_2_ = DAT_0002c4d0._2_2_;
  }
  else if (DAT_00021578 == 0) {
    local_12 = *(short *)(SUB_000207fc + 0x192);
    DAT_00021584._2_2_ = DAT_0002c4d0._2_2_;
  }
  else {
    if ((DAT_00021578 & 3) == 0) {
      local_12 = *(short *)(SUB_000207fc + 0x192);
    }
    else {
      local_12 = *(short *)(SUB_000207fc + 0x194);
    }
    uVar3 = DAT_00021578 & 0xc;
    if (uVar3 == 0) {
      local_16 = *(short *)(SUB_000207fc + 0x192);
    }
    else {
      local_16 = *(short *)(SUB_000207fc + 0x194);
    }
  }
  sVar7 = DAT_00021584._2_2_;
  if (DAT_0002c4d0._2_2_ < DAT_00021584._2_2_) {
    sVar7 = DAT_0002c4d0._2_2_;
  }
  if (DAT_0002c4d0._2_2_ < sVar8) {
    sVar8 = DAT_0002c4d0._2_2_;
  }
  if (unaff_D6w == 0) {
    local_14 = *(short *)(SUB_000207fc + 0x194);
    sVar9 = *(short *)(SUB_000207fc + 0x192);
  }
  else {
    local_14 = *(short *)(SUB_000207fc + 0x192);
    sVar9 = *(short *)(SUB_000207fc + 0x194);
  }
  DAT_00021584 = iVar5;
  if (DAT_0002100e == 1) {
    if (_DAT_00027010 != 0) {
      uVar3 = CONCAT22((short)(uVar3 >> 0x10),DAT_0002c4e0) & 0xffff0001;
      DAT_0002c4e0 = DAT_0002c4e0 + 1;
      if ((short)uVar3 == 0) {
        if (param_1 != 0) {
          (**(code **)(SUB_000207fc + 0x2fe))();
        }
        uVar3 = (**(code **)(SUB_000207fc + 0x2b6))
                          ((int)sVar7,(int)sVar8,(int)DAT_0002c4d0._2_2_,(int)local_12,(int)local_16
                           ,(int)unaff_D4 >> 0x10,(int)local_10 >> 0x10,(int)local_14,(int)sVar9);
        if (param_1 != 0) {
          uVar3 = (**(code **)(SUB_000207fc + 0x2fe))();
        }
      }
    }
  }
  else {
    sVar6 = 0;
    psVar11 = DAT_00027008;
    psVar10 = DAT_00027008;
    if (0 < sVar7) {
      do {
        psVar10[2] = *(short *)(iVar4 + sVar6 * 4);
        psVar10[3] = DAT_00023fd0;
        psVar10[4] = *(short *)(iVar4 + 2 + sVar6 * 4);
        psVar10[5] = local_12;
        psVar10[6] = DAT_00021012;
        psVar10[7] = DAT_00023fd0;
        psVar11 = psVar10 + 8;
        *psVar10 = 0;
        psVar10[1] = (short)((int)psVar11 - (int)psVar10 >> 2) + -1;
        sVar6 = sVar6 + 1;
        psVar10 = psVar11;
      } while (sVar6 < sVar7);
    }
    for (; sVar6 < sVar8; sVar6 = sVar6 + 1) {
      sVar7 = *(short *)(iVar4 + sVar6 * 4);
      sVar1 = *(short *)(iVar4 + 2 + sVar6 * 4);
      psVar11[2] = sVar7;
      psVar11[3] = DAT_00023fd0;
      psVar10 = psVar11 + 4;
      if (sVar7 * 0x10000 < (int)unaff_D4) {
        if ((int)unaff_D4 < sVar1 * 0x10000) {
          *psVar10 = (short)(unaff_D4 >> 0x10);
          psVar11[5] = local_14;
          psVar10 = psVar11 + 6;
          goto LAB_0000cbc2;
        }
        sVar7 = local_14;
      }
      else {
LAB_0000cbc2:
        sVar7 = sVar9;
      }
      *psVar10 = sVar1;
      psVar10[1] = sVar7;
      psVar10[2] = DAT_00021012;
      psVar10[3] = DAT_00023fd0;
      *psVar11 = 0;
      psVar11[1] = (short)((int)(psVar10 + 4) - (int)psVar11 >> 2) + -1;
      unaff_D4 = unaff_A4 + unaff_D4;
      psVar11 = psVar10 + 4;
    }
    if (sVar6 < DAT_0002c4d0._2_2_) {
      do {
        psVar11[2] = *(short *)(iVar4 + sVar6 * 4);
        psVar11[3] = DAT_00023fd0;
        psVar11[4] = *(short *)(iVar4 + 2 + sVar6 * 4);
        psVar11[5] = local_16;
        psVar11[6] = DAT_00021012;
        psVar11[7] = DAT_00023fd0;
        *psVar11 = 0;
        psVar11[1] = (short)((int)(psVar11 + 8) - (int)psVar11 >> 2) + -1;
        sVar6 = sVar6 + 1;
        psVar11 = psVar11 + 8;
      } while (sVar6 < DAT_0002c4d0._2_2_);
    }
    if (_DAT_00027010 == 0) {
      iVar2 = 0;
      iVar4 = *(int *)(SUB_000207fc + 0xe0) +
              ((int)DAT_0002c4cc._0_2_ - (int)*(short *)(SUB_000207fc + 0xe6)) *
              (int)*(short *)(SUB_000207fc + 0xe4);
    }
    else {
      iVar2 = (int)(short)(*(short *)(SUB_000207fc + 0x10) -
                          DAT_00021012 / (short)(8 / (int)DAT_0002100e));
      iVar4 = *(int *)(SUB_000207fc + 6) +
              (int)*(short *)(SUB_000207fc + 0x10) * (int)DAT_0002c4cc._0_2_;
    }
    local_8 = DAT_00027008;
    local_c = DAT_00027004;
    uVar3 = (*DAT_00026ff0)(&local_8,&local_c,(int)DAT_0002c4d0._2_2_,iVar4,iVar2);
    psVar11 = DAT_00027008;
    DAT_00027008 = DAT_00027004;
    DAT_00027004 = psVar11;
  }
  DAT_0002157c = 0;
  DAT_00021584 = 0;
  DAT_00021578 = 0;
  return uVar3;
}



// FUN_0000cd16 @ 0xcd16

void FUN_0000cd16(void)

{
  byte bVar1;
  ushort uVar2;
  ushort uVar3;
  short sVar4;
  short sVar5;
  byte *pbVar6;
  byte *pbVar7;
  ushort *puVar8;
  ushort *puVar9;
  int iVar10;
  byte *pbVar11;
  byte *pbVar12;
  ushort *puVar13;
  ushort *puVar14;
  
  if (*(short *)(SUB_000207fc + 0x9e) == 0) {
    if (DAT_0002100e == 4) {
      sVar5 = *(short *)(SUB_000207fc + 0x10);
      bVar1 = *(byte *)(SUB_000207fc + 0x295) | *(byte *)(SUB_000207fc + 0x295) << 4;
      pbVar6 = *(byte **)(SUB_000207fc + 0x284);
      iVar10 = ((int)*(short *)(SUB_000207fc + 0x272) - (int)*(short *)(SUB_000207fc + 0x270)) + -1;
      if (iVar10 != -1) {
        do {
          do {
            *pbVar6 = bVar1;
            pbVar6 = pbVar6 + sVar5;
            uVar2 = (ushort)((uint)iVar10 >> 0x10);
            sVar4 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar2,sVar4);
          } while (sVar4 != -1);
          iVar10 = (uint)uVar2 * 0x10000 + -1;
        } while (uVar2 != 0);
      }
      pbVar6 = *(byte **)(SUB_000207fc + 0x288);
      iVar10 = ((int)*(short *)(SUB_000207fc + 0x276) - (int)*(short *)(SUB_000207fc + 0x274)) + -1;
      if (iVar10 != -1) {
        do {
          do {
            *pbVar6 = bVar1;
            pbVar6 = pbVar6 + sVar5;
            uVar2 = (ushort)((uint)iVar10 >> 0x10);
            sVar4 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar2,sVar4);
          } while (sVar4 != -1);
          iVar10 = (uint)uVar2 * 0x10000 + -1;
        } while (uVar2 != 0);
      }
      iVar10 = (int)*(short *)(SUB_000207fc + 0x27c) - (int)*(short *)(SUB_000207fc + 0x27a);
      if (iVar10 < 0) {
        iVar10 = iVar10 + 1;
      }
      iVar10 = (iVar10 >> 1) + -1;
      pbVar6 = *(byte **)(SUB_000207fc + 0x28c);
      pbVar12 = *(byte **)(SUB_000207fc + 0x28c) + sVar5;
      if (iVar10 != -1) {
        do {
          do {
            pbVar7 = pbVar6 + 1;
            *pbVar6 = bVar1;
            pbVar11 = pbVar12 + 1;
            *pbVar12 = bVar1;
            uVar2 = (ushort)((uint)iVar10 >> 0x10);
            sVar4 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar2,sVar4);
            pbVar6 = pbVar7;
            pbVar12 = pbVar11;
          } while (sVar4 != -1);
          iVar10 = (uint)uVar2 * 0x10000 + -1;
        } while (uVar2 != 0);
      }
      iVar10 = (int)*(short *)(SUB_000207fc + 0x280) - (int)*(short *)(SUB_000207fc + 0x27e);
      if (iVar10 < 0) {
        iVar10 = iVar10 + 1;
      }
      iVar10 = (iVar10 >> 1) + -1;
      pbVar6 = *(byte **)(SUB_000207fc + 0x290);
      pbVar12 = *(byte **)(SUB_000207fc + 0x290) + sVar5;
      if (iVar10 != -1) {
        do {
          do {
            pbVar7 = pbVar6 + 1;
            *pbVar6 = bVar1;
            pbVar11 = pbVar12 + 1;
            *pbVar12 = bVar1;
            uVar2 = (ushort)((uint)iVar10 >> 0x10);
            sVar5 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar2,sVar5);
            pbVar6 = pbVar7;
            pbVar12 = pbVar11;
          } while (sVar5 != -1);
          iVar10 = (uint)uVar2 * 0x10000 + -1;
        } while (uVar2 != 0);
      }
    }
    else if (DAT_0002100e == 8) {
      sVar5 = *(short *)(SUB_000207fc + 0x10);
      if (sVar5 < 0) {
        sVar5 = sVar5 + 1;
      }
      sVar5 = sVar5 >> 1;
      uVar2 = *(ushort *)(SUB_000207fc + 0x294) | *(short *)(SUB_000207fc + 0x294) << 8;
      puVar9 = *(ushort **)(SUB_000207fc + 0x284);
      iVar10 = ((int)*(short *)(SUB_000207fc + 0x272) - (int)*(short *)(SUB_000207fc + 0x270)) + -1;
      if (iVar10 != -1) {
        do {
          do {
            *puVar9 = uVar2;
            puVar9 = puVar9 + sVar5;
            uVar3 = (ushort)((uint)iVar10 >> 0x10);
            sVar4 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar3,sVar4);
          } while (sVar4 != -1);
          iVar10 = (uint)uVar3 * 0x10000 + -1;
        } while (uVar3 != 0);
      }
      puVar9 = *(ushort **)(SUB_000207fc + 0x288);
      iVar10 = ((int)*(short *)(SUB_000207fc + 0x276) - (int)*(short *)(SUB_000207fc + 0x274)) + -1;
      if (iVar10 != -1) {
        do {
          do {
            *puVar9 = uVar2;
            puVar9 = puVar9 + sVar5;
            uVar3 = (ushort)((uint)iVar10 >> 0x10);
            sVar4 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar3,sVar4);
          } while (sVar4 != -1);
          iVar10 = (uint)uVar3 * 0x10000 + -1;
        } while (uVar3 != 0);
      }
      iVar10 = (int)*(short *)(SUB_000207fc + 0x27c) - (int)*(short *)(SUB_000207fc + 0x27a);
      if (iVar10 < 0) {
        iVar10 = iVar10 + 1;
      }
      iVar10 = (iVar10 >> 1) + -1;
      puVar9 = *(ushort **)(SUB_000207fc + 0x28c);
      puVar14 = *(ushort **)(SUB_000207fc + 0x28c) + sVar5;
      if (iVar10 != -1) {
        do {
          do {
            puVar8 = puVar9 + 1;
            *puVar9 = uVar2;
            puVar13 = puVar14 + 1;
            *puVar14 = uVar2;
            uVar3 = (ushort)((uint)iVar10 >> 0x10);
            sVar4 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar3,sVar4);
            puVar9 = puVar8;
            puVar14 = puVar13;
          } while (sVar4 != -1);
          iVar10 = (uint)uVar3 * 0x10000 + -1;
        } while (uVar3 != 0);
      }
      iVar10 = (int)*(short *)(SUB_000207fc + 0x280) - (int)*(short *)(SUB_000207fc + 0x27e);
      if (iVar10 < 0) {
        iVar10 = iVar10 + 1;
      }
      iVar10 = (iVar10 >> 1) + -1;
      puVar9 = *(ushort **)(SUB_000207fc + 0x290);
      puVar14 = *(ushort **)(SUB_000207fc + 0x290) + sVar5;
      if (iVar10 != -1) {
        do {
          do {
            puVar8 = puVar9 + 1;
            *puVar9 = uVar2;
            puVar13 = puVar14 + 1;
            *puVar14 = uVar2;
            uVar3 = (ushort)((uint)iVar10 >> 0x10);
            sVar5 = (short)iVar10 + -1;
            iVar10 = CONCAT22(uVar3,sVar5);
            puVar9 = puVar8;
            puVar14 = puVar13;
          } while (sVar5 != -1);
          iVar10 = (uint)uVar3 * 0x10000 + -1;
        } while (uVar3 != 0);
      }
    }
  }
  else if (DAT_0002100e != 1) {
    (**(code **)(SUB_000207fc + 0x2aa))
              ((int)*(short *)(SUB_000207fc + 0x294),(int)*(short *)(SUB_000207fc + 0x278),
               (int)*(short *)(SUB_000207fc + 0x270),(int)*(short *)(SUB_000207fc + 0x278),
               *(short *)(SUB_000207fc + 0x272) + -2);
    (**(code **)(SUB_000207fc + 0x2aa))
              ((int)*(short *)(SUB_000207fc + 0x294),(int)*(short *)(SUB_000207fc + 0x278),
               (int)*(short *)(SUB_000207fc + 0x274),(int)*(short *)(SUB_000207fc + 0x278),
               *(short *)(SUB_000207fc + 0x276) + -2);
    (**(code **)(SUB_000207fc + 0x2aa))
              ((int)*(short *)(SUB_000207fc + 0x294),(int)*(short *)(SUB_000207fc + 0x27a),
               (int)*(short *)(SUB_000207fc + 0x282),*(short *)(SUB_000207fc + 0x27c) + -2,
               (int)*(short *)(SUB_000207fc + 0x282));
    (**(code **)(SUB_000207fc + 0x2aa))
              ((int)*(short *)(SUB_000207fc + 0x294),(int)*(short *)(SUB_000207fc + 0x27e),
               (int)*(short *)(SUB_000207fc + 0x282),*(short *)(SUB_000207fc + 0x280) + -2,
               (int)*(short *)(SUB_000207fc + 0x282));
  }
  return;
}



// FUN_0000cfc6 @ 0xcfc6

uint FUN_0000cfc6(uint param_1,uint param_2,uint param_3,short param_4,int param_5,int param_6)

{
  short sVar1;
  byte bVar2;
  uint uVar3;
  ushort uVar4;
  short sVar7;
  byte *pbVar5;
  char *pcVar6;
  short sVar8;
  ushort uVar9;
  ushort *puVar10;
  ushort *puVar11;
  byte *pbVar12;
  ushort *puVar13;
  ushort *puVar14;
  uint local_12;
  ushort local_e;
  undefined4 local_c;
  uint local_8;
  
  param_5 = param_5 >> 1;
  param_6 = param_6 >> 1;
  for (local_c = param_3; (uVar3 = param_1 & 3, uVar3 != 0 && (local_c != 0)); local_c = local_c - 1
      ) {
    if ((param_1 & 1) == 0) {
      if ((param_2 & 1) == 0) {
        pbVar12 = (byte *)(param_1 >> 1);
        pbVar5 = (byte *)(param_2 >> 1);
        sVar8 = param_4 + -1;
        do {
          *pbVar12 = *pbVar5 ^ (*pbVar5 ^ *pbVar12) & 0xf;
          pbVar12 = pbVar12 + param_6;
          pbVar5 = pbVar5 + param_5;
          sVar8 = sVar8 + -1;
        } while (sVar8 != -1);
      }
      else {
        pbVar5 = (byte *)(param_1 >> 1);
        pcVar6 = (char *)(param_2 >> 1);
        sVar8 = param_4 + -1;
        do {
          *pbVar5 = *pcVar6 << 4 | *pbVar5 & 0xf;
          pbVar5 = pbVar5 + param_6;
          pcVar6 = pcVar6 + param_5;
          sVar8 = sVar8 + -1;
        } while (sVar8 != -1);
      }
    }
    else if ((param_2 & 1) == 0) {
      pbVar12 = (byte *)(param_1 >> 1);
      pbVar5 = (byte *)(param_2 >> 1);
      sVar8 = param_4 + -1;
      do {
        *pbVar12 = *pbVar5 >> 4 | *pbVar12 & 0xf0;
        pbVar12 = pbVar12 + param_6;
        pbVar5 = pbVar5 + param_5;
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
    }
    else {
      pbVar12 = (byte *)(param_1 >> 1);
      pbVar5 = (byte *)(param_2 >> 1);
      sVar8 = param_4 + -1;
      do {
        *pbVar12 = *pbVar5 ^ (*pbVar5 ^ *pbVar12) & 0xf0;
        pbVar12 = pbVar12 + param_6;
        pbVar5 = pbVar5 + param_5;
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
    }
    param_1 = param_1 + 1;
    param_2 = param_2 + 1;
  }
  local_e = local_c._2_2_ & 0xfffc;
  local_8 = (int)(short)local_e + param_1;
  local_12 = param_2 + (int)(short)local_e;
  if ((local_c & 0xfffc) != 0) {
    sVar8 = param_4 + -1;
    if ((short)local_e < 0) {
      local_e = local_e + 3;
    }
    sVar1 = ((short)local_e >> 2) + -1;
    puVar11 = (ushort *)(param_1 >> 1);
    puVar10 = (ushort *)(param_2 >> 1);
    switch((ushort)param_2 & 3) {
    case 0:
      do {
        puVar13 = puVar10;
        puVar14 = puVar11;
        sVar7 = sVar1;
        do {
          *puVar14 = *puVar13;
          sVar7 = sVar7 + -1;
          puVar13 = puVar13 + 1;
          puVar14 = puVar14 + 1;
        } while (sVar7 != -1);
        puVar11 = (ushort *)(param_6 + (int)puVar11);
        puVar10 = (ushort *)(param_5 + (int)puVar10);
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
      break;
    case 1:
      do {
        uVar4 = *puVar10;
        puVar14 = puVar11;
        sVar7 = sVar1;
        puVar13 = puVar10;
        do {
          uVar9 = uVar4 << 4;
          puVar13 = puVar13 + 1;
          uVar4 = *puVar13;
          uVar3 = (uint)(uVar4 >> 0xc);
          *puVar14 = uVar4 >> 0xc | uVar9;
          sVar7 = sVar7 + -1;
          puVar14 = puVar14 + 1;
        } while (sVar7 != -1);
        puVar11 = (ushort *)(param_6 + (int)puVar11);
        puVar10 = (ushort *)(param_5 + (int)puVar10);
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
      break;
    case 2:
      do {
        uVar4 = (ushort)*(char *)puVar10;
        puVar13 = (ushort *)((int)puVar10 + 1);
        puVar14 = puVar11;
        sVar7 = sVar1;
        do {
          uVar9 = uVar4 << 8;
          uVar4 = *puVar13;
          uVar3 = (uint)(uVar4 >> 8);
          *puVar14 = uVar4 >> 8 | uVar9;
          sVar7 = sVar7 + -1;
          puVar13 = puVar13 + 1;
          puVar14 = puVar14 + 1;
        } while (sVar7 != -1);
        puVar11 = (ushort *)(param_6 + (int)puVar11);
        puVar10 = (ushort *)(param_5 + (int)puVar10);
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
      break;
    case 3:
      do {
        uVar4 = (ushort)*(char *)puVar10;
        puVar13 = (ushort *)((int)puVar10 + 1);
        puVar14 = puVar11;
        sVar7 = sVar1;
        do {
          uVar9 = uVar4 << 0xc;
          uVar4 = *puVar13;
          uVar3 = (uint)(uVar4 >> 4);
          *puVar14 = uVar4 >> 4 | uVar9;
          sVar7 = sVar7 + -1;
          puVar13 = puVar13 + 1;
          puVar14 = puVar14 + 1;
        } while (sVar7 != -1);
        puVar11 = (ushort *)(param_6 + (int)puVar11);
        puVar10 = (ushort *)(param_5 + (int)puVar10);
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
    }
    local_c = local_c & 3;
  }
  for (; local_c != 0; local_c = local_c - 1) {
    if ((local_8 & 1) == 0) {
      if ((local_12 & 1) == 0) {
        pbVar12 = (byte *)(local_8 >> 1);
        pbVar5 = (byte *)(local_12 >> 1);
        sVar8 = param_4 + -1;
        do {
          uVar3 = CONCAT31((int3)(uVar3 >> 8),*pbVar5 ^ *pbVar12) & 0xffffff0f;
          bVar2 = *pbVar5 ^ (byte)uVar3;
          uVar3 = CONCAT31((int3)(uVar3 >> 8),bVar2);
          *pbVar12 = bVar2;
          pbVar12 = pbVar12 + param_6;
          pbVar5 = pbVar5 + param_5;
          sVar8 = sVar8 + -1;
        } while (sVar8 != -1);
      }
      else {
        pbVar5 = (byte *)(local_8 >> 1);
        pcVar6 = (char *)(local_12 >> 1);
        sVar8 = param_4 + -1;
        do {
          uVar3 = CONCAT31((int3)(uVar3 >> 8),*pbVar5) & 0xffffff0f;
          bVar2 = *pcVar6 << 4 | (byte)uVar3;
          uVar3 = CONCAT31((int3)(uVar3 >> 8),bVar2);
          *pbVar5 = bVar2;
          pbVar5 = pbVar5 + param_6;
          pcVar6 = pcVar6 + param_5;
          sVar8 = sVar8 + -1;
        } while (sVar8 != -1);
      }
    }
    else if ((local_12 & 1) == 0) {
      pbVar12 = (byte *)(local_8 >> 1);
      pbVar5 = (byte *)(local_12 >> 1);
      sVar8 = param_4 + -1;
      do {
        uVar3 = CONCAT31((int3)(uVar3 >> 8),*pbVar12) & 0xfffffff0;
        bVar2 = *pbVar5 >> 4 | (byte)uVar3;
        uVar3 = CONCAT31((int3)(uVar3 >> 8),bVar2);
        *pbVar12 = bVar2;
        pbVar12 = pbVar12 + param_6;
        pbVar5 = pbVar5 + param_5;
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
    }
    else {
      pbVar12 = (byte *)(local_8 >> 1);
      pbVar5 = (byte *)(local_12 >> 1);
      sVar8 = param_4 + -1;
      do {
        uVar3 = CONCAT31((int3)(uVar3 >> 8),*pbVar5 ^ *pbVar12) & 0xfffffff0;
        bVar2 = *pbVar5 ^ (byte)uVar3;
        uVar3 = CONCAT31((int3)(uVar3 >> 8),bVar2);
        *pbVar12 = bVar2;
        pbVar12 = pbVar12 + param_6;
        pbVar5 = pbVar5 + param_5;
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
    }
    local_8 = local_8 + 1;
    local_12 = local_12 + 1;
  }
  return uVar3;
}



// FUN_0000d2ce @ 0xd2ce

uint FUN_0000d2ce(uint *param_1,undefined1 *param_2,uint param_3,short param_4,int param_5,
                 int param_6)

{
  uint uVar1;
  short sVar2;
  uint in_D0;
  short sVar3;
  uint uVar4;
  short sVar5;
  ushort uVar6;
  uint *puVar7;
  undefined1 *puVar8;
  uint *puVar9;
  undefined1 *puVar10;
  uint *puVar11;
  undefined1 *puVar12;
  bool bVar13;
  undefined4 local_e;
  undefined1 *local_a;
  
  for (local_e = param_3;
      (in_D0 = CONCAT22((short)(in_D0 >> 0x10),(short)param_1) & 0xffff0003,
      ((uint)param_1 & 3) != 0 && (local_e != 0)); local_e = local_e - 1) {
    sVar5 = param_4 + -1;
    puVar12 = param_2;
    puVar11 = param_1;
    do {
      *(undefined1 *)puVar11 = *puVar12;
      puVar11 = (uint *)(param_6 + (int)puVar11);
      puVar12 = puVar12 + param_5;
      sVar5 = sVar5 + -1;
    } while (sVar5 != -1);
    param_1 = (uint *)((int)param_1 + 1);
    param_2 = param_2 + 1;
  }
  uVar6 = local_e._2_2_ & 0xfffc;
  local_a = (undefined1 *)((int)(short)uVar6 + (int)param_1);
  puVar12 = param_2 + (short)uVar6;
  if ((local_e & 0xfffc) != 0) {
    sVar5 = param_4 + -1;
    if ((short)uVar6 < 0) {
      uVar6 = uVar6 + 3;
    }
    sVar2 = ((short)uVar6 >> 2) + -1;
    in_D0 = (uint)param_2 & 3;
    puVar11 = (uint *)(param_2 + -in_D0);
    switch(in_D0) {
    case 0:
      do {
        puVar7 = puVar11;
        puVar9 = param_1;
        sVar3 = sVar2;
        do {
          *puVar9 = *puVar7;
          sVar3 = sVar3 + -1;
          puVar7 = puVar7 + 1;
          puVar9 = puVar9 + 1;
        } while (sVar3 != -1);
        param_1 = (uint *)(param_6 + (int)param_1);
        puVar11 = (uint *)(param_5 + (int)puVar11);
        sVar5 = sVar5 + -1;
      } while (sVar5 != -1);
      break;
    case 1:
      do {
        uVar1 = *puVar11;
        puVar9 = param_1;
        sVar3 = sVar2;
        puVar7 = puVar11;
        do {
          uVar4 = uVar1 << 8;
          puVar7 = puVar7 + 1;
          uVar1 = *puVar7;
          in_D0 = uVar1 >> 0x18;
          *puVar9 = in_D0 | uVar4;
          sVar3 = sVar3 + -1;
          puVar9 = puVar9 + 1;
        } while (sVar3 != -1);
        param_1 = (uint *)(param_6 + (int)param_1);
        puVar11 = (uint *)(param_5 + (int)puVar11);
        sVar5 = sVar5 + -1;
      } while (sVar5 != -1);
      break;
    case 2:
      do {
        uVar1 = *puVar11;
        puVar9 = param_1;
        sVar3 = sVar2;
        puVar7 = puVar11;
        do {
          uVar4 = uVar1 << 0x10;
          puVar7 = puVar7 + 1;
          uVar1 = *puVar7;
          in_D0 = uVar1 >> 0x10;
          *puVar9 = in_D0 | uVar4;
          sVar3 = sVar3 + -1;
          puVar9 = puVar9 + 1;
        } while (sVar3 != -1);
        param_1 = (uint *)(param_6 + (int)param_1);
        puVar11 = (uint *)(param_5 + (int)puVar11);
        sVar5 = sVar5 + -1;
      } while (sVar5 != -1);
      break;
    case 3:
      do {
        uVar1 = *puVar11;
        puVar9 = param_1;
        sVar3 = sVar2;
        puVar7 = puVar11;
        do {
          uVar4 = uVar1 << 0x18;
          puVar7 = puVar7 + 1;
          uVar1 = *puVar7;
          in_D0 = uVar1 >> 8;
          *puVar9 = in_D0 | uVar4;
          sVar3 = sVar3 + -1;
          puVar9 = puVar9 + 1;
        } while (sVar3 != -1);
        param_1 = (uint *)(param_6 + (int)param_1);
        puVar11 = (uint *)(param_5 + (int)puVar11);
        sVar5 = sVar5 + -1;
      } while (sVar5 != -1);
    }
    local_e = local_e & 3;
  }
  local_e = local_e - 1;
  if (local_e != -1) {
    do {
      sVar5 = param_4 + -1;
      puVar8 = puVar12;
      puVar10 = local_a;
      do {
        *puVar10 = *puVar8;
        puVar10 = puVar10 + param_6;
        puVar8 = puVar8 + param_5;
        sVar5 = sVar5 + -1;
      } while (sVar5 != -1);
      local_a = local_a + 1;
      puVar12 = puVar12 + 1;
      bVar13 = local_e != 0;
      local_e = local_e + -1;
    } while (bVar13);
  }
  return in_D0;
}



// FUN_0000d446 @ 0xd446

void FUN_0000d446(undefined4 param_1,undefined4 param_2,undefined4 param_3,short param_4)

{
  short local_c [3];
  undefined2 local_6;
  
  FUN_0001f712(param_1,0x6c);
  local_c[1] = 0;
  local_c[0] = param_4;
  local_6 = *(undefined2 *)(SUB_000207fc + 0x16);
  local_c[2] = *(short *)(SUB_000207fc + 0x18) + param_4;
  (**(code **)(SUB_000207fc + 0x2ba))(param_1,param_2,param_3,local_c);
  (**(code **)(SUB_000207fc + 0x2be))(param_1);
  return;
}



// FUN_0000d4a6 @ 0xd4a6

void FUN_0000d4a6(undefined4 param_1,undefined4 param_2)

{
  (**(code **)(SUB_000207fc + 0x2c2))(param_1);
  (**(code **)(SUB_000207fc + 0x2be))(param_2);
  return;
}



// FUN_0000d4d2 @ 0xd4d2

/* WARNING: Removing unreachable block (ram,0x0000d76a) */
/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_0000d4d2(int param_1)

{
  int iVar1;
  byte *pbVar2;
  short sVar3;
  ushort uVar4;
  uint uVar5;
  int iVar6;
  int iVar7;
  short sVar9;
  short sVar10;
  int iVar8;
  int iVar11;
  undefined1 *puVar12;
  int iVar13;
  ushort uVar14;
  int iVar15;
  int iVar16;
  int iVar17;
  short sVar19;
  int iVar18;
  undefined4 unaff_A3;
  int iVar20;
  byte *pbVar21;
  int local_a0;
  byte *local_9c;
  short local_88;
  short local_86;
  short local_84;
  short local_82;
  undefined2 local_80;
  short local_7e;
  undefined2 local_7c;
  short local_7a;
  short local_78;
  short local_76;
  short local_74;
  short local_72;
  undefined1 auStack_70 [108];
  
  iVar1 = *(int *)(SUB_000207fc + 0x1b4);
  pbVar2 = *(byte **)(SUB_000207fc + 0x1bc);
  sVar10 = *(short *)(SUB_000207fc + 0x1a8);
  iVar16 = (int)sVar10;
  sVar3 = *(short *)(SUB_000207fc + 0x1a2);
  iVar15 = (int)sVar3;
  sVar9 = *(short *)(SUB_000207fc + 0x1a4);
  iVar17 = (int)sVar9;
  uVar4 = *(ushort *)(SUB_000207fc + 0x1ae) & 0x1fff;
  if (_DAT_00027010 == 0) {
    local_a0 = *(int *)(SUB_000207fc + 0xe0);
    sVar19 = *(short *)(SUB_000207fc + 0x19e) - *(short *)(SUB_000207fc + 0xe6);
    iVar13 = (int)*(short *)(SUB_000207fc + 0xe4);
    if (param_1 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
    unaff_A3 = FUN_0000d446(auStack_70,iVar13,local_a0,(int)*(short *)(SUB_000207fc + 0xe6));
    local_76 = *(short *)(SUB_000207fc + 0x1a0);
    local_78 = *(short *)(SUB_000207fc + 0x19e);
    local_72 = sVar3 + local_76;
    local_74 = sVar9 + local_78;
    (**(code **)(SUB_000207fc + 0x2c6))(&local_78,(int)*(short *)(SUB_000207fc + 0x294));
    if (DAT_0002100e == 8) {
      FUN_0000d4a6(auStack_70,unaff_A3);
    }
    if (param_1 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
  }
  else {
    local_a0 = *(int *)(SUB_000207fc + 6);
    sVar19 = *(short *)(SUB_000207fc + 0x19e);
    iVar13 = (int)*(short *)(SUB_000207fc + 0x10);
  }
  iVar6 = iVar16 * -(int)DAT_00023d3e;
  if (iVar6 < 0) {
    iVar6 = iVar6 + 0x3fff;
  }
  iVar11 = iVar15;
  if (iVar15 < 0) {
    iVar11 = iVar15 + 1;
  }
  uVar5 = iVar11 >> 1;
  for (iVar6 = (int)*(short *)(SUB_000207fc + 0x1ac) + ((iVar6 >> 0xe) - uVar5); iVar6 < 0;
      iVar6 = iVar16 + iVar6) {
  }
  if (DAT_0002c598 == iVar6) {
    if (_DAT_00027010 != 0) {
      return uVar5;
    }
    if (DAT_0002100e == 8) {
      return uVar5;
    }
    if (param_1 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
  }
  else {
    iVar11 = (int)DAT_0002c598;
    sVar9 = (short)iVar6;
    DAT_0002c598 = sVar9;
    if (DAT_0002100e == 8) {
      pbVar21 = pbVar2 + iVar6;
      local_9c = pbVar2 + iVar11;
      uVar5 = iVar13 * sVar19;
      iVar18 = (int)*(short *)(SUB_000207fc + 0x1a0) + uVar5 + local_a0;
      iVar20 = iVar6 + iVar1;
      iVar7 = 0;
      iVar8 = 0;
      iVar15 = iVar15 + -1;
      if (iVar15 != -1) {
        do {
          iVar6 = iVar6 + 1;
          uVar5 = (uint)(*pbVar21 | *local_9c);
          if ((*pbVar21 | *local_9c) == 0) {
            if (iVar8 != 0) {
              uVar5 = (*(code *)&LAB_0000d2ac_2)
                                (iVar7 + iVar18,iVar7 + iVar20,iVar8,iVar17,uVar4,iVar13);
              iVar18 = iVar8 + iVar7 + iVar18;
              iVar20 = iVar8 + iVar7 + iVar20;
              iVar8 = 0;
              iVar7 = 0;
            }
            iVar7 = iVar7 + 1;
            pbVar21 = pbVar21 + 1;
            if (iVar16 == iVar6) {
              iVar18 = iVar7 + iVar18;
              iVar7 = 0;
              iVar6 = 0;
              iVar20 = iVar1;
              pbVar21 = pbVar2;
            }
          }
          else {
            iVar8 = iVar8 + 1;
            pbVar21 = pbVar21 + 1;
            if (iVar16 == iVar6) {
              uVar5 = (*(code *)&LAB_0000d2ac_2)
                                (iVar7 + iVar18,iVar20 + iVar7,iVar8,iVar17,uVar4,iVar13);
              iVar18 = iVar8 + iVar7 + iVar18;
              iVar8 = 0;
              iVar7 = 0;
              iVar6 = 0;
              iVar20 = iVar1;
              pbVar21 = pbVar2;
            }
          }
          iVar11 = iVar11 + 1;
          local_9c = local_9c + 1;
          if (iVar16 == iVar11) {
            iVar11 = 0;
            local_9c = pbVar2;
          }
          uVar14 = (ushort)((uint)iVar15 >> 0x10);
          sVar10 = (short)iVar15 + -1;
          iVar15 = CONCAT22(uVar14,sVar10);
        } while ((sVar10 != -1) || (iVar15 = (uint)uVar14 * 0x10000 + -1, uVar14 != 0));
      }
      if (iVar8 == 0) {
        return uVar5;
      }
      uVar5 = (*(code *)&LAB_0000d2ac_2)(iVar18 + iVar7,iVar20 + iVar7,iVar8,iVar17,uVar4,iVar13);
      return uVar5;
    }
    if (param_1 != 0) {
      (**(code **)(SUB_000207fc + 0x2fe))();
    }
    if (_DAT_00027010 == 0) {
      puVar12 = auStack_70;
    }
    else {
      puVar12 = (undefined1 *)0x0;
    }
    local_86 = *(short *)(SUB_000207fc + 0x1a0);
    local_88 = *(short *)(SUB_000207fc + 0x19e);
    local_82 = *(short *)(SUB_000207fc + 0x1a2) + local_86;
    local_84 = *(short *)(SUB_000207fc + 0x1a4) + local_88;
    local_80 = 0;
    local_7c = *(undefined2 *)(SUB_000207fc + 0x1aa);
    local_7a = sVar3 + sVar9;
    local_7e = sVar9;
    if (iVar16 < local_7a) {
      sVar10 = local_7a - sVar10;
      local_7a = local_7a - sVar10;
      local_82 = local_82 - sVar10;
      (**(code **)(SUB_000207fc + 0x2d2))
                (*(undefined4 *)(SUB_000207fc + 0x1c0),*(undefined4 *)(SUB_000207fc + 0x1b4),
                 &local_80,puVar12,&local_88);
      local_7e = 0;
      local_86 = local_82;
      local_82 = sVar10 + local_82;
      local_7a = sVar10;
    }
    uVar5 = (**(code **)(SUB_000207fc + 0x2d2))
                      (*(undefined4 *)(SUB_000207fc + 0x1c0),*(undefined4 *)(SUB_000207fc + 0x1b4),
                       &local_80,puVar12,&local_88);
    if (_DAT_00027010 != 0) goto LAB_0000d73a;
  }
  uVar5 = FUN_0000d4a6(auStack_70,unaff_A3);
LAB_0000d73a:
  if (param_1 != 0) {
    uVar5 = (**(code **)(SUB_000207fc + 0x2fe))();
  }
  return uVar5;
}



// FUN_0000d8b2 @ 0xd8b2

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

undefined4 FUN_0000d8b2(int param_1,undefined4 param_2,undefined4 param_3,int param_4,int param_5)

{
  if (*(short *)(SUB_000207fc + 0x9e) == 0) {
    if (_DAT_0002c596 != 0) {
      if ((param_4 == *(int *)(&DAT_0002c4e2 + param_1 * 4)) &&
         (param_5 == *(int *)(&DAT_0002c52a + param_1 * 4))) {
        return 0;
      }
      (*DAT_00023fc8)(param_2,param_3,*(undefined4 *)(&DAT_0002c4e2 + param_1 * 4),
                      *(undefined4 *)(&DAT_0002c52a + param_1 * 4),*(undefined4 *)(SUB_000207fc + 6)
                      ,(int)*(short *)(SUB_000207fc + 0x10),
                      *(int *)(SUB_000207fc + 0xe0) - DAT_00021564,
                      (int)*(short *)(SUB_000207fc + 0xe4));
    }
    (*DAT_00023fcc)(param_2,param_3,param_4,param_5,(int)*(short *)(SUB_000207fc + 0xe8),
                    *(undefined4 *)(SUB_000207fc + 6),(int)*(short *)(SUB_000207fc + 0x10));
  }
  else {
    (**(code **)(SUB_000207fc + 0x2aa))
              ((int)*(short *)(SUB_000207fc + 0xe8),param_2,param_3,param_4,param_5);
  }
  *(int *)(&DAT_0002c4e2 + param_1 * 4) = param_4;
  *(int *)(&DAT_0002c52a + param_1 * 4) = param_5;
  return 1;
}



// FUN_0000d9a4 @ 0xd9a4

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000d9a4(void)

{
  ushort uVar1;
  int iVar2;
  short sVar4;
  short sVar5;
  int iVar3;
  short sVar6;
  int iVar7;
  int iVar8;
  
  iVar7 = *(short *)(SUB_000207fc + 0xf4) + -1;
  iVar8 = *(short *)(SUB_000207fc + 0xf6) + -1;
  uVar1 = (short)((DAT_00023d64 % 10000 << 0xe) / 10000) - 0x1000U & 0x3fff;
  sVar4 = FUN_00002406(uVar1);
  sVar6 = *(short *)(SUB_000207fc + 0xf8);
  sVar5 = FUN_000023a4(uVar1);
  iVar3 = FUN_0000d8b2(0,iVar7,iVar8,iVar7 + ((int)sVar4 * (int)sVar6 >> 0xc),
                       iVar8 + ((int)sVar5 * (int)*(short *)(SUB_000207fc + 0xf8) >> 0xc));
  if (iVar3 != 0) {
    uVar1 = (short)((DAT_00023d64 % 100000 << 0xe) / 100000) - 0x1000U & 0x3fff;
    sVar6 = FUN_00002406(uVar1);
    iVar3 = (int)sVar6 * (((int)*(short *)(SUB_000207fc + 0xf8) << 1) / 3) >> 0xc;
    sVar6 = FUN_000023a4(uVar1);
    iVar2 = (int)sVar6 * (((int)*(short *)(SUB_000207fc + 0xf8) << 1) / 3) >> 0xc;
    if (*(short *)(SUB_000207fc + 0x9e) == 0) {
      if (_DAT_0002c596 != 0) {
        (*DAT_00023fc8)(iVar7,iVar8,DAT_0002c572,DAT_0002c576,*(undefined4 *)(SUB_000207fc + 6),
                        (int)*(short *)(SUB_000207fc + 0x10),
                        *(int *)(SUB_000207fc + 0xe0) - DAT_00021564,
                        (int)*(short *)(SUB_000207fc + 0xe4));
      }
      (*DAT_00023fcc)(iVar7,iVar8,iVar7 + iVar3,iVar8 + iVar2,(int)*(short *)(SUB_000207fc + 0xe8),
                      *(undefined4 *)(SUB_000207fc + 6),(int)*(short *)(SUB_000207fc + 0x10));
    }
    else {
      (**(code **)(SUB_000207fc + 0x2aa))
                ((int)*(short *)(SUB_000207fc + 0xe8),iVar7,iVar8,iVar7 + iVar3,iVar8 + iVar2);
    }
    DAT_0002c572 = iVar3 + iVar7;
    DAT_0002c576 = iVar2 + iVar8;
  }
  return;
}



// FUN_0000db4c @ 0xdb4c

void FUN_0000db4c(void)

{
  ushort uVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  int iVar5;
  int iVar6;
  
  iVar5 = *(int *)(SUB_00020b28 + 0x62a);
  if (0x3843 < iVar5) {
    iVar5 = iVar5 + -0x3843;
  }
  if (iVar5 < 0xb41) {
    sVar2 = (short)((iVar5 << 0xe) / 0xb409);
  }
  else if (iVar5 < 0x10e1) {
    sVar2 = (short)(((iVar5 + -0x5a0) * 0x4000) / 0x5a04);
  }
  else {
    sVar2 = (short)(((iVar5 + -0xb41) * 0x4000) / 0x2d02);
  }
  iVar5 = *(short *)(SUB_000207fc + 0x104) + -1;
  iVar6 = *(short *)(SUB_000207fc + 0x106) + -1;
  uVar1 = sVar2 - 0x1000U & 0x3fff;
  sVar3 = FUN_00002426(uVar1);
  sVar2 = *(short *)(SUB_000207fc + 0x108);
  sVar4 = FUN_000023c4(uVar1);
  FUN_0000d8b2(1,iVar5,iVar6,iVar5 + ((int)sVar3 * (int)sVar2 >> 0xc),
               iVar6 + ((int)sVar4 * (int)*(short *)(SUB_000207fc + 0x108) >> 0xc));
  return;
}



// FUN_0000dc16 @ 0xdc16

void FUN_0000dc16(void)

{
  short sVar1;
  ushort uVar2;
  short sVar3;
  short sVar4;
  int iVar5;
  int iVar6;
  
  iVar5 = *(short *)(SUB_000207fc + 0x154) + -1;
  iVar6 = *(short *)(SUB_000207fc + 0x156) + -1;
  uVar2 = DAT_00023d3e - 0x1000U & 0x3fff;
  sVar3 = FUN_00002426(uVar2);
  sVar1 = *(short *)(SUB_000207fc + 0x158);
  sVar4 = FUN_000023c4(uVar2);
  FUN_0000d8b2(6,iVar5,iVar6,iVar5 + ((int)sVar3 * (int)sVar1 >> 0xc),
               iVar6 + ((int)sVar4 * (int)*(short *)(SUB_000207fc + 0x158) >> 0xc));
  return;
}



// FUN_0000dc88 @ 0xdc88

void FUN_0000dc88(void)

{
  short sVar1;
  uint uVar2;
  short sVar3;
  short sVar4;
  int iVar5;
  int iVar6;
  
  uVar2 = DAT_00021468 / 0x78 - 0x2000U & 0x3fff;
  iVar5 = *(short *)(SUB_000207fc + 0x124) + -1;
  iVar6 = *(short *)(SUB_000207fc + 0x126) + -1;
  sVar3 = FUN_00002426(uVar2);
  sVar1 = *(short *)(SUB_000207fc + 0x128);
  sVar4 = FUN_000023c4(uVar2);
  FUN_0000d8b2(3,iVar5,iVar6,iVar5 + ((int)sVar3 * (int)sVar1 >> 0xc),
               iVar6 + ((int)sVar4 * (int)*(short *)(SUB_000207fc + 0x128) >> 0xc));
  return;
}



// FUN_0000dd02 @ 0xdd02

void FUN_0000dd02(void)

{
  short sVar1;
  uint uVar2;
  short sVar3;
  short sVar4;
  int iVar5;
  int iVar6;
  
  uVar2 = (*(int *)(SUB_00020b28 + 0x678) << 6) / 0x23a + 0x1540U & 0x3fff;
  iVar5 = *(short *)(SUB_000207fc + 0x144) + -1;
  iVar6 = *(short *)(SUB_000207fc + 0x146) + -1;
  sVar3 = FUN_00002426(uVar2);
  sVar1 = *(short *)(SUB_000207fc + 0x148);
  sVar4 = FUN_000023c4(uVar2);
  FUN_0000d8b2(5,iVar5,iVar6,iVar5 + ((int)sVar3 * (int)sVar1 >> 0xc),
               iVar6 + ((int)sVar4 * (int)*(short *)(SUB_000207fc + 0x148) >> 0xc));
  return;
}



// FUN_0000dd84 @ 0xdd84

void FUN_0000dd84(void)

{
  short sVar1;
  uint uVar2;
  short sVar3;
  short sVar4;
  int iVar5;
  int iVar6;
  
  uVar2 = ((int)*(short *)(SUB_00020b28 + 0x680) << 6) / 0x5f - 0x2500U & 0x3fff;
  iVar5 = *(short *)(SUB_000207fc + 0x134) + -1;
  iVar6 = *(short *)(SUB_000207fc + 0x136) + -1;
  sVar3 = FUN_00002426(uVar2);
  sVar1 = *(short *)(SUB_000207fc + 0x138);
  sVar4 = FUN_000023c4(uVar2);
  FUN_0000d8b2(4,iVar5,iVar6,iVar5 + ((int)sVar3 * (int)sVar1 >> 0xc),
               iVar6 + ((int)sVar4 * (int)*(short *)(SUB_000207fc + 0x138) >> 0xc));
  return;
}



// FUN_0000de06 @ 0xde06

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000de06(void)

{
  short sVar1;
  short sVar2;
  uint uVar3;
  
  uVar3 = (byte)-(0x43 < *(int *)(*SUB_00020b28 + 8) - *(int *)((int)SUB_00020b28 + 0x2e)) & 1;
  if (((_DAT_0002c596 == 0) || (*(char *)((int)SUB_00020b28 + 0x68d) != DAT_0002c57a)) ||
     (uVar3 != (int)DAT_0002c57c)) {
    DAT_0002c57a = (short)*(char *)((int)SUB_00020b28 + 0x68d);
    DAT_0002c57c = (short)uVar3;
    sVar1 = *(short *)(SUB_000207fc + 0x200);
    sVar2 = sVar1;
    if (1 < DAT_0002c57a) {
      sVar2 = *(short *)(SUB_000207fc + 0x204);
    }
    if (0 < DAT_0002c57a) {
      sVar1 = *(short *)(SUB_000207fc + 0x204);
    }
    (**(code **)(SUB_000207fc + 0x2d6))
              (uVar3,(int)sVar1,(int)sVar2,(int)*(char *)(SUB_00020b28 + 0x1a4));
  }
  return;
}



// FUN_0000dea6 @ 0xdea6

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000dea6(void)

{
  undefined4 in_D1;
  
  if ((_DAT_0002c596 == 0) || (*(char *)(SUB_00020b28 + 0x68e) != DAT_0002c57e)) {
    DAT_0002c57e = (short)*(char *)(SUB_00020b28 + 0x68e);
    switch(*(undefined1 *)(SUB_00020b28 + 0x68e)) {
    case 0:
      in_D1 = 0;
      break;
    case 1:
      if (*(char *)(SUB_00020b28 + 0x68f) == '\x01') {
        in_D1 = 4;
      }
      else {
        in_D1 = 8;
      }
      break;
    case 2:
      in_D1 = 0xc;
      break;
    case 3:
      if (*(char *)(SUB_00020b28 + 0x68f) == '\x01') {
        in_D1 = 0xe;
      }
      else {
        in_D1 = 0x1c;
      }
      break;
    case 4:
      in_D1 = 0x1e;
      break;
    case 5:
    case 6:
      in_D1 = 0x3f;
    }
    (**(code **)(SUB_000207fc + 0x2da))(in_D1);
  }
  return;
}



// FUN_0000df40 @ 0xdf40

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000df40(void)

{
  short sVar1;
  undefined4 local_c;
  undefined4 local_8;
  
  if ((_DAT_0002c596 == 0) || (*(char *)(SUB_00020b28 + 0x676) != DAT_0002c580)) {
    DAT_0002c580 = (short)*(char *)(SUB_00020b28 + 0x676);
    local_c = *(undefined4 *)(SUB_000207fc + 0x23a);
    local_8._0_2_ = (short)((uint)*(undefined4 *)(SUB_000207fc + 0x23e) >> 0x10);
    sVar1 = local_8._0_2_ -
            (short)(((int)(short)(*(short *)(SUB_000207fc + 0x23e) -
                                 *(short *)(SUB_000207fc + 0x23a)) * (int)DAT_0002c580) / 9);
    local_8 = CONCAT22(sVar1,(short)*(undefined4 *)(SUB_000207fc + 0x23e));
    (**(code **)(SUB_000207fc + 0x2c6))(&local_c,(int)*(short *)(SUB_000207fc + 0x242));
    local_8 = *(undefined4 *)(SUB_000207fc + 0x23e);
    local_c = CONCAT22(sVar1,(short)*(undefined4 *)(SUB_000207fc + 0x23a));
    (**(code **)(SUB_000207fc + 0x2c6))(&local_c,(int)*(short *)(SUB_000207fc + 0x244));
  }
  return;
}



// FUN_0000dff0 @ 0xdff0

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000dff0(void)

{
  short sVar1;
  ushort uVar2;
  int iVar3;
  short sVar4;
  short sVar5;
  int iVar6;
  int iVar7;
  
  if ((_DAT_0002c596 == 0) || (*(char *)(SUB_00020b28 + 0x68c) != DAT_0002c582)) {
    DAT_0002c582 = (short)*(char *)(SUB_00020b28 + 0x68c);
    iVar3 = FUN_00003908((int)DAT_0002c582);
    iVar6 = *(short *)(SUB_000207fc + 0x246) + -1;
    iVar7 = *(short *)(SUB_000207fc + 0x248) + -1;
    uVar2 = (short)((iVar3 * -0xac0) / 0xf) + 0x2000U & 0x3fff;
    sVar4 = FUN_00002426(uVar2);
    sVar1 = *(short *)(SUB_000207fc + 0x24a);
    sVar5 = FUN_000023c4(uVar2);
    FUN_0000d8b2(10,iVar6,iVar7,iVar6 + ((int)sVar4 * (int)sVar1 >> 0xc),
                 iVar7 + ((int)sVar5 * (int)*(short *)(SUB_000207fc + 0x24a) >> 0xc));
  }
  return;
}



// FUN_0000e0a8 @ 0xe0a8

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000e0a8(void)

{
  short sVar1;
  uint uVar2;
  int iVar3;
  short sVar4;
  short sVar5;
  int iVar6;
  int iVar7;
  
  if ((_DAT_0002c596 == 0) || (*(char *)(SUB_00020b28 + 0x68a) != DAT_0002c584)) {
    DAT_0002c584 = (short)*(char *)(SUB_00020b28 + 0x68a);
    iVar3 = FUN_00003908((int)DAT_0002c584);
    iVar7 = *(short *)(SUB_000207fc + 0x24c) + -1;
    iVar6 = *(short *)(SUB_000207fc + 0x24e) + -1;
    uVar2 = (iVar3 << 0xc) / 0x2d & 0x3fff;
    sVar4 = FUN_00002406(uVar2);
    sVar1 = *(short *)(SUB_000207fc + 0x250);
    sVar5 = FUN_000023a4(uVar2);
    FUN_0000d8b2(0xb,iVar7,iVar6,iVar7 + ((int)sVar4 * (int)sVar1 >> 0xc),
                 iVar6 + ((int)sVar5 * (int)*(short *)(SUB_000207fc + 0x250) >> 0xc));
    iVar6 = *(short *)(SUB_000207fc + 0x252) + -1;
    iVar3 = *(short *)(SUB_000207fc + 0x254) + -1;
    sVar4 = FUN_00002406(uVar2);
    sVar1 = *(short *)(SUB_000207fc + 0x256);
    sVar5 = FUN_000023a4(uVar2);
    FUN_0000d8b2(0xc,iVar6,iVar3,iVar6 - ((int)sVar4 * (int)sVar1 >> 0xc),
                 iVar3 + ((int)sVar5 * (int)*(short *)(SUB_000207fc + 0x256) >> 0xc));
    iVar6 = *(short *)(SUB_000207fc + 600) + -1;
    iVar3 = *(short *)(SUB_000207fc + 0x25a) + -1;
    sVar4 = FUN_00002406(uVar2);
    sVar1 = *(short *)(SUB_000207fc + 0x25c);
    sVar5 = FUN_000023a4(uVar2);
    FUN_0000d8b2(0xd,iVar6,iVar3,iVar6 + ((int)sVar4 * (int)sVar1 >> 0xc),
                 iVar3 + ((int)sVar5 * (int)*(short *)(SUB_000207fc + 0x25c) >> 0xc));
  }
  return;
}



// FUN_0000e1fa @ 0xe1fa

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000e1fa(void)

{
  short sVar1;
  short sVar2;
  short sVar3;
  int iVar4;
  int iVar5;
  int iVar6;
  
  if ((_DAT_0002c596 == 0) || (*(short *)(SUB_00020b28 + 0x696) != DAT_0002c586)) {
    DAT_0002c586 = *(short *)(SUB_00020b28 + 0x696);
    iVar4 = *(short *)(SUB_000207fc + 0x26a) + -1;
    iVar5 = *(short *)(SUB_000207fc + 0x26c) + -1;
    iVar6 = (int)(short)((short)((DAT_0002c586 * 0x1555) / 0x2a3) + 0x555);
    sVar2 = FUN_00002426(iVar6);
    sVar1 = *(short *)(SUB_000207fc + 0x26e);
    sVar3 = FUN_000023c4(iVar6);
    FUN_0000d8b2(0xf,iVar4,iVar5,iVar4 - ((int)sVar2 * (int)sVar1 >> 0xc),
                 iVar5 - ((int)sVar3 * (int)*(short *)(SUB_000207fc + 0x26e) >> 0xc));
  }
  return;
}



// FUN_0000e2a2 @ 0xe2a2

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000e2a2(void)

{
  ushort uVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  short sVar5;
  int iVar6;
  int iVar7;
  
  if (((_DAT_0002c596 == 0) || (*(char *)(SUB_00020b28 + 0x691) != DAT_0002c588)) ||
     (*(char *)(SUB_00020b28 + 0x692) != DAT_0002c58a)) {
    DAT_0002c588 = (short)*(char *)(SUB_00020b28 + 0x691);
    DAT_0002c58a = (short)*(char *)(SUB_00020b28 + 0x692);
    sVar5 = *(short *)(*(int *)(SUB_00020b28 + 4) + 0x1c);
    if (sVar5 < 0) {
      sVar5 = sVar5 + 1;
    }
    iVar7 = *(short *)(SUB_000207fc + 0x25e) + -1;
    iVar6 = *(short *)(SUB_000207fc + 0x260) + -1;
    uVar1 = (short)((DAT_0002c588 * 0x1555) / (int)(sVar5 >> 1)) - 0xaabU & 0x3fff;
    sVar2 = FUN_00002406(uVar1);
    sVar4 = *(short *)(SUB_000207fc + 0x262);
    sVar3 = FUN_000023a4(uVar1);
    FUN_0000d8b2(0x10,iVar7,iVar6,iVar7 + ((int)sVar2 * (int)sVar4 >> 0xc),
                 iVar6 - ((int)sVar3 * (int)*(short *)(SUB_000207fc + 0x262) >> 0xc));
    iVar7 = *(short *)(SUB_000207fc + 0x264) + -1;
    iVar6 = *(short *)(SUB_000207fc + 0x266) + -1;
    uVar1 = (short)((DAT_0002c58a * 0x1555) / (int)(sVar5 >> 1)) - 0xaabU & 0x3fff;
    sVar4 = FUN_00002406(uVar1);
    sVar5 = *(short *)(SUB_000207fc + 0x268);
    sVar2 = FUN_000023a4(uVar1);
    FUN_0000d8b2(0x11,iVar7,iVar6,iVar7 - ((int)sVar4 * (int)sVar5 >> 0xc),
                 iVar6 - ((int)sVar2 * (int)*(short *)(SUB_000207fc + 0x268) >> 0xc));
  }
  return;
}



// FUN_0000e3ea @ 0xe3ea

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000e3ea(undefined4 param_1)

{
  uint uVar1;
  bool bVar2;
  int iVar3;
  ushort uVar5;
  undefined4 uVar4;
  uint unaff_D2;
  short sVar6;
  short sVar7;
  int iVar8;
  short sVar9;
  short sVar10;
  int *piVar11;
  char acStack_68 [9];
  undefined1 local_5f;
  undefined1 local_5e;
  
  if (_DAT_00027010 == 0) {
    if (DAT_00023d88 != 7) {
      piVar11 = SUB_00020b28;
    }
    else {
      piVar11 = DAT_00021070;
    }
    bVar2 = DAT_00023d88 == 7;
    _DAT_0002c596 = 0;
    sVar7 = DAT_0002700e;
    while (sVar7 = sVar7 + -1, sVar7 != -1) {
      if (DAT_0002700e == 4) {
        DAT_0002c58c = 0;
        (**(code **)(SUB_000207fc + 0x2b2))(0,0,0,0,1,bVar2);
        DAT_0002700e = 1;
        sVar7 = 0;
        do {
          (&DAT_0002c58e)[sVar7] = 0x7fff;
          sVar7 = sVar7 + 1;
        } while (sVar7 < 4);
      }
      switch(DAT_0002c58c) {
      case 0:
        iVar3 = *(int *)(*piVar11 + 8);
        iVar8 = 10;
        goto LAB_0000e4fc;
      case 1:
        iVar3 = *(int *)((int)piVar11 + 0x62a) / 0x12;
        if (iVar3 < 0) {
          iVar3 = iVar3 + 3;
        }
        uVar5 = (ushort)(iVar3 >> 2);
        goto LAB_0000e500;
      case 2:
        iVar3 = *(short *)((int)piVar11 + 0x22) * 0x168;
        if (iVar3 < 0) {
          iVar3 = iVar3 + 0xff;
        }
        iVar3 = iVar3 >> 8;
        if (iVar3 < 0) {
          iVar3 = iVar3 + 0x3f;
        }
        unaff_D2 = (uint)(ushort)(0x168 - (short)(iVar3 >> 6));
        break;
      case 3:
        iVar3 = (DAT_00021468 / 0x78) * 600;
        if (iVar3 < 0) {
          iVar3 = iVar3 + 0x3f;
        }
        iVar3 = iVar3 >> 6;
        iVar8 = 0x76;
LAB_0000e4fc:
        uVar5 = (ushort)(iVar3 / iVar8);
LAB_0000e500:
        unaff_D2 = (uint)uVar5;
      }
      if (((!bVar2) || (DAT_0002c58c != 3)) &&
         (sVar6 = (short)unaff_D2, sVar6 != (&DAT_0002c58e)[DAT_0002c58c])) {
        if (sVar6 < 0) {
          unaff_D2 = (uint)(ushort)-sVar6;
        }
        (&DAT_0002c58e)[DAT_0002c58c] = (short)unaff_D2;
        iVar3 = SUB_000207fc;
        local_5e = 0;
        sVar10 = 9;
        if (DAT_0002c58c == 2) {
          local_5f = 0xa1;
          sVar10 = 8;
        }
        do {
          sVar9 = sVar10;
          acStack_68[sVar9] = (char)((int)(short)unaff_D2 % 10) + '0';
          sVar10 = sVar9 + -1;
          uVar1 = (int)(short)unaff_D2 / 10;
          unaff_D2 = uVar1 & 0xffff;
        } while (uVar1 != 0);
        if (sVar6 < 0) {
          acStack_68[sVar10] = '-';
          sVar10 = sVar9 + -2;
        }
        uVar4 = FUN_0001f75a(acStack_68 + (short)(sVar10 + 1),0,bVar2);
        (**(code **)(iVar3 + 0x2b2))(acStack_68 + (short)(sVar10 + 1),(int)DAT_0002c58c,0,uVar4);
      }
      DAT_0002c58c = DAT_0002c58c + 1;
      if (DAT_0002c58c == 4) {
        DAT_0002c58c = 0;
      }
    }
  }
  else {
    if (_DAT_0002c596 == 0) {
      DAT_00021564 = (int)*(short *)(SUB_000207fc + 0xe6) * (int)*(short *)(SUB_000207fc + 0xe4);
    }
    if (*(short *)(SUB_000207fc + 0x9e) == 0) {
      FUN_0000c79e(param_1);
      FUN_0000d4d2(param_1);
    }
    FUN_0000cd16();
    FUN_0000d9a4();
    FUN_0000db4c();
    FUN_0000dc16();
    FUN_0000dc88();
    FUN_0000dd02();
    FUN_0000dd84();
    FUN_0001bd0a();
    FUN_0000dff0();
    FUN_0000e1fa();
    FUN_0000e2a2();
    FUN_0000e0a8();
  }
  return;
}



// FUN_0000e664 @ 0xe664

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0000e664(void)

{
  _DAT_0002c596 = 1;
  if (*(short *)(SUB_000207fc + 0x9e) != 0) {
    FUN_00007f2a();
  }
  return;
}



// FUN_0000e680 @ 0xe680

void FUN_0000e680(undefined4 param_1,undefined4 param_2,uint param_3,undefined4 param_4,
                 undefined4 param_5,undefined4 param_6)

{
  uint uVar1;
  undefined4 uVar2;
  undefined4 uVar3;
  uint uVar4;
  uint uVar5;
  uint uVar6;
  
  uVar2 = param_6;
  uVar1 = param_3;
  FUN_00007ecc(param_6);
  uVar3 = FUN_000020a0(0x2000);
  uVar6 = (uint)(0x2000 / (short)(DAT_00021012 / (short)(8 / (int)DAT_0002100e)));
  uVar5 = uVar1;
  if (DAT_0002100e == 4) {
    uVar6 = uVar6 - (uVar6 & 1);
  }
  do {
    if ((int)uVar6 < (int)uVar5) {
      uVar4 = uVar6;
    }
    else {
      uVar4 = uVar5;
    }
    (*DAT_00026ff0)(&param_1,&param_2,uVar4,uVar3,0);
    (**(code **)(SUB_000207fc + 0x2ce))(uVar3,uVar1 - uVar5,uVar4,uVar2);
    uVar5 = uVar5 - uVar4;
  } while (uVar5 != 0);
  FUN_00002008(uVar3);
  return;
}



// FUN_0000e734 @ 0xe734

void FUN_0000e734(undefined4 param_1)

{
  undefined4 uVar1;
  uint uVar2;
  
  uVar1 = FUN_000020a0(0x2000);
  uVar2 = (uint)(short)(0x2000 / (int)(short)(DAT_00021012 / (short)(8 / (int)DAT_0002100e)));
  if (DAT_0002100e == 4) {
    uVar2 = uVar2 - (uVar2 & 1);
  }
  (**(code **)(SUB_000207fc + 0x80))(uVar1,uVar2,param_1);
  FUN_00002008(uVar1);
  return;
}



// FUN_0000e7a4 @ 0xe7a4

void FUN_0000e7a4(int param_1,uint param_2,int param_3,uint param_4,ushort param_5,int param_6,
                 uint param_7)

{
  int iVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  int iVar5;
  ushort uVar6;
  undefined1 uVar7;
  short sVar8;
  ushort *puVar9;
  ushort *puVar10;
  ushort *puVar11;
  ushort *puVar12;
  int iVar13;
  bool bVar14;
  bool bVar15;
  bool bVar16;
  
  uVar6 = param_5 << 8 | param_5;
  iVar5 = param_1 - param_3;
  if (param_1 < param_3) {
    iVar5 = -iVar5;
  }
  iVar2 = param_2 - param_4;
  if ((int)param_2 < (int)param_4) {
    iVar2 = -iVar2;
  }
  uVar7 = (undefined1)param_5;
  if (iVar5 < iVar2) {
    iVar3 = param_1;
    if ((int)param_4 < (int)param_2) {
      iVar3 = param_3;
      param_2 = param_4;
      param_3 = param_1;
    }
    sVar8 = (short)iVar2 + -1;
    iVar13 = param_7 - 1;
    puVar9 = (ushort *)((param_7 & 0xffff) * (param_2 & 0xffff) + iVar3 + param_6);
    iVar1 = (iVar5 - iVar2) * 2;
    iVar5 = iVar5 * 2 - iVar2;
    iVar2 = iVar5 + iVar2;
    if (iVar3 < param_3) {
      if (((uint)puVar9 & 1) == 0) {
        *puVar9 = uVar6;
        puVar9 = (ushort *)(param_7 + (int)puVar9);
        bVar14 = false;
        goto LAB_0000e920;
      }
      *(undefined1 *)puVar9 = uVar7;
      *(undefined1 *)((int)puVar9 + 1) = uVar7;
      puVar9 = (ushort *)((undefined1 *)((int)puVar9 + 1) + iVar13);
      bVar16 = false;
      bVar14 = iVar5 < 0;
      bVar15 = iVar5 == 0;
      do {
        while (!bVar15 && bVar16 == bVar14) {
          *(undefined1 *)puVar9 = uVar7;
          *(ushort *)((int)puVar9 + 1) = uVar6;
          bVar14 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          puVar9 = (ushort *)((int)puVar9 + 1);
          while( true ) {
            sVar8 = sVar8 + -1;
            puVar9 = (ushort *)(param_7 + (int)puVar9);
            if (sVar8 == -1) goto LAB_0000e98e;
LAB_0000e920:
            if (iVar5 != 0 && bVar14 == iVar5 < 0) break;
            *puVar9 = uVar6;
            bVar14 = SCARRY4(iVar2,iVar5);
            iVar5 = iVar2 + iVar5;
          }
          *puVar9 = uVar6;
          *(undefined1 *)(puVar9 + 1) = uVar7;
          puVar9 = (ushort *)(iVar13 + (int)(puVar9 + 1));
          bVar16 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          bVar14 = iVar5 < 0;
          bVar15 = iVar5 == 0;
          sVar8 = sVar8 + -1;
          if (sVar8 == -1) goto LAB_0000e98e;
        }
        *(undefined1 *)puVar9 = uVar7;
        *(undefined1 *)((int)puVar9 + 1) = uVar7;
        puVar9 = (ushort *)((undefined1 *)((int)puVar9 + 1) + iVar13);
        bVar16 = SCARRY4(iVar2,iVar5);
        iVar5 = iVar2 + iVar5;
        bVar14 = iVar5 < 0;
        bVar15 = iVar5 == 0;
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
    }
    else {
      if (((uint)puVar9 & 1) == 0) {
        *puVar9 = uVar6;
        puVar9 = (ushort *)(param_7 + (int)puVar9);
        bVar14 = false;
        goto LAB_0000e974;
      }
      *(undefined1 *)puVar9 = uVar7;
      *(undefined1 *)((int)puVar9 + 1) = uVar7;
      puVar9 = (ushort *)((undefined1 *)((int)puVar9 + 1) + iVar13);
      bVar16 = false;
      bVar14 = iVar5 < 0;
      bVar15 = iVar5 == 0;
      do {
        while (!bVar15 && bVar16 == bVar14) {
          *(undefined1 *)((int)puVar9 + 1) = uVar7;
          puVar9 = (ushort *)((int)puVar9 + -1);
          *puVar9 = uVar6;
          bVar14 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          while( true ) {
            sVar8 = sVar8 + -1;
            puVar9 = (ushort *)(param_7 + (int)puVar9);
            if (sVar8 == -1) goto LAB_0000e98e;
LAB_0000e974:
            if (iVar5 != 0 && bVar14 == iVar5 < 0) break;
            *puVar9 = uVar6;
            bVar14 = SCARRY4(iVar2,iVar5);
            iVar5 = iVar2 + iVar5;
          }
          *puVar9 = uVar6;
          *(undefined1 *)((int)puVar9 + -1) = uVar7;
          puVar9 = (ushort *)((undefined1 *)((int)puVar9 + -1) + param_7);
          bVar16 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          bVar14 = iVar5 < 0;
          bVar15 = iVar5 == 0;
          sVar8 = sVar8 + -1;
          if (sVar8 == -1) goto LAB_0000e98e;
        }
        *(undefined1 *)puVar9 = uVar7;
        *(undefined1 *)((int)puVar9 + 1) = uVar7;
        puVar9 = (ushort *)((undefined1 *)((int)puVar9 + 1) + iVar13);
        bVar16 = SCARRY4(iVar2,iVar5);
        iVar5 = iVar2 + iVar5;
        bVar14 = iVar5 < 0;
        bVar15 = iVar5 == 0;
        sVar8 = sVar8 + -1;
      } while (sVar8 != -1);
    }
LAB_0000e98e:
    *(undefined1 *)puVar9 = uVar7;
    *(undefined1 *)((int)puVar9 + 1) = uVar7;
  }
  else {
    uVar4 = param_2;
    if (param_3 < param_1) {
      param_1 = param_3;
      uVar4 = param_4;
      param_4 = param_2;
    }
    sVar8 = (short)iVar5 + -1;
    puVar9 = (ushort *)((param_7 & 0xffff) * (uVar4 & 0xffff) + param_1 + param_6);
    puVar11 = (ushort *)(param_7 + (int)puVar9);
    iVar3 = (iVar2 - iVar5) * 2;
    iVar2 = iVar2 * 2 - iVar5;
    iVar5 = iVar2 + iVar5;
    if ((int)uVar4 < (int)param_4) {
      if (((uint)puVar9 & 1) == 0) {
        bVar16 = false;
        bVar14 = iVar2 < 0;
        bVar15 = iVar2 == 0;
        goto LAB_0000e81a;
      }
      *(undefined1 *)puVar9 = uVar7;
      *(undefined1 *)puVar11 = uVar7;
      bVar16 = false;
      bVar14 = iVar2 < 0;
      bVar15 = iVar2 == 0;
      puVar9 = (ushort *)((int)puVar9 + 1);
      puVar11 = (ushort *)((int)puVar11 + 1);
      do {
        while( true ) {
          if (bVar15 || bVar16 != bVar14) {
            bVar16 = SCARRY4(iVar5,iVar2);
            iVar2 = iVar5 + iVar2;
            bVar14 = iVar2 < 0;
            bVar15 = iVar2 == 0;
            sVar8 = sVar8 + -1;
            if (sVar8 == -1) {
              puVar10 = (ushort *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = uVar7;
              puVar12 = (ushort *)((int)puVar11 + 1);
              *(undefined1 *)puVar11 = uVar7;
              goto LAB_0000e8b0;
            }
          }
          else {
            *(undefined1 *)puVar9 = uVar7;
            puVar9 = (ushort *)(param_7 + (int)puVar9);
            puVar11 = (ushort *)(param_7 + (int)puVar11);
            bVar16 = SCARRY4(iVar3,iVar2);
            iVar2 = iVar3 + iVar2;
            bVar14 = iVar2 < 0;
            bVar15 = iVar2 == 0;
            sVar8 = sVar8 + -1;
            if (sVar8 == -1) {
              puVar10 = (ushort *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = uVar7;
              puVar12 = (ushort *)((int)puVar11 + 1);
              *(undefined1 *)puVar11 = uVar7;
              goto LAB_0000e8b0;
            }
          }
LAB_0000e81a:
          if (!bVar15 && bVar16 == bVar14) break;
          puVar10 = puVar9 + 1;
          *puVar9 = uVar6;
          puVar12 = puVar11 + 1;
          *puVar11 = uVar6;
          bVar16 = SCARRY4(iVar5,iVar2);
          iVar2 = iVar5 + iVar2;
          bVar14 = iVar2 < 0;
          bVar15 = iVar2 == 0;
          sVar8 = sVar8 + -1;
          puVar9 = puVar10;
          puVar11 = puVar12;
          if (sVar8 == -1) goto LAB_0000e8b0;
        }
        *puVar9 = uVar6;
        *puVar11 = uVar6;
        puVar10 = (ushort *)((int)puVar9 + param_7 + 2);
        puVar12 = (ushort *)((int)puVar11 + param_7 + 2);
        *(undefined1 *)((int)puVar12 + -1) = uVar7;
        bVar16 = SCARRY4(iVar3,iVar2);
        iVar2 = iVar3 + iVar2;
        bVar14 = iVar2 < 0;
        bVar15 = iVar2 == 0;
        sVar8 = sVar8 + -1;
        puVar9 = puVar10;
        puVar11 = puVar12;
      } while (sVar8 != -1);
    }
    else {
      if (((uint)puVar9 & 1) == 0) {
        bVar16 = false;
        bVar14 = iVar2 < 0;
        bVar15 = iVar2 == 0;
        goto LAB_0000e870;
      }
      *(undefined1 *)puVar9 = uVar7;
      *(undefined1 *)puVar11 = uVar7;
      bVar16 = false;
      bVar14 = iVar2 < 0;
      bVar15 = iVar2 == 0;
      puVar9 = (ushort *)((int)puVar9 + 1);
      puVar11 = (ushort *)((int)puVar11 + 1);
      do {
        while( true ) {
          if (bVar15 || bVar16 != bVar14) {
            bVar16 = SCARRY4(iVar5,iVar2);
            iVar2 = iVar5 + iVar2;
            bVar14 = iVar2 < 0;
            bVar15 = iVar2 == 0;
            sVar8 = sVar8 + -1;
            if (sVar8 == -1) {
              puVar10 = (ushort *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = uVar7;
              puVar12 = (ushort *)((int)puVar11 + 1);
              *(undefined1 *)puVar11 = uVar7;
              goto LAB_0000e8b0;
            }
          }
          else {
            *(undefined1 *)puVar11 = uVar7;
            puVar9 = (ushort *)((int)puVar9 - param_7);
            puVar11 = (ushort *)((int)puVar11 - param_7);
            bVar16 = SCARRY4(iVar3,iVar2);
            iVar2 = iVar3 + iVar2;
            bVar14 = iVar2 < 0;
            bVar15 = iVar2 == 0;
            sVar8 = sVar8 + -1;
            if (sVar8 == -1) {
              puVar10 = (ushort *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = uVar7;
              puVar12 = (ushort *)((int)puVar11 + 1);
              *(undefined1 *)puVar11 = uVar7;
              goto LAB_0000e8b0;
            }
          }
LAB_0000e870:
          if (!bVar15 && bVar16 == bVar14) break;
          puVar10 = puVar9 + 1;
          *puVar9 = uVar6;
          puVar12 = puVar11 + 1;
          *puVar11 = uVar6;
          bVar16 = SCARRY4(iVar5,iVar2);
          iVar2 = iVar5 + iVar2;
          bVar14 = iVar2 < 0;
          bVar15 = iVar2 == 0;
          sVar8 = sVar8 + -1;
          puVar9 = puVar10;
          puVar11 = puVar12;
          if (sVar8 == -1) goto LAB_0000e8b0;
        }
        *puVar9 = uVar6;
        *puVar11 = uVar6;
        puVar10 = (ushort *)((int)puVar9 + (2 - param_7));
        puVar12 = (ushort *)((int)puVar11 + (2 - param_7));
        *(undefined1 *)((int)puVar10 + -1) = uVar7;
        bVar16 = SCARRY4(iVar3,iVar2);
        iVar2 = iVar3 + iVar2;
        bVar14 = iVar2 < 0;
        bVar15 = iVar2 == 0;
        sVar8 = sVar8 + -1;
        puVar9 = puVar10;
        puVar11 = puVar12;
      } while (sVar8 != -1);
    }
LAB_0000e8b0:
    *(undefined1 *)puVar10 = uVar7;
    *(undefined1 *)puVar12 = uVar7;
  }
  return;
}



// FUN_0000e998 @ 0xe998

/* WARNING: Type propagation algorithm not settling */

void FUN_0000e998(uint param_1,uint param_2,uint param_3,uint param_4,byte param_5,int param_6,
                 uint param_7)

{
  int iVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  int iVar5;
  short sVar6;
  byte *pbVar7;
  byte *pbVar8;
  int iVar9;
  bool bVar10;
  bool bVar11;
  bool bVar12;
  
  iVar5 = param_1 - param_3;
  if ((int)param_1 < (int)param_3) {
    iVar5 = -iVar5;
  }
  iVar3 = param_2 - param_4;
  if ((int)param_2 < (int)param_4) {
    iVar3 = -iVar3;
  }
  if (iVar5 < iVar3) {
    uVar4 = param_1;
    if ((int)param_4 < (int)param_2) {
      uVar4 = param_3;
      param_2 = param_4;
      param_3 = param_1;
    }
    sVar6 = (short)iVar3 + -1;
    iVar9 = param_7 - 1;
    pbVar7 = (byte *)((param_7 & 0xffff) * (param_2 & 0xffff) + (uVar4 >> 1) + param_6);
    iVar1 = (iVar5 - iVar3) * 2;
    iVar5 = iVar5 * 2 - iVar3;
    iVar3 = iVar5 + iVar3;
    if ((int)uVar4 < (int)param_3) {
      if ((uVar4 & 1) == 0) {
        *pbVar7 = param_5;
        pbVar7 = pbVar7 + param_7;
        bVar11 = false;
        goto LAB_0000ec2a;
      }
      pbVar8 = pbVar7 + 1;
      *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf0;
      *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
      pbVar8 = pbVar8 + iVar9;
      bVar12 = false;
      bVar11 = iVar5 < 0;
      bVar10 = iVar5 == 0;
LAB_0000ebe4:
      do {
        if (!bVar10 && bVar12 == bVar11) {
          pbVar7 = pbVar8 + 1;
          *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf0;
          *pbVar7 = param_5;
          bVar11 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          do {
            sVar6 = sVar6 + -1;
            pbVar7 = pbVar7 + param_7;
            if (sVar6 == -1) {
LAB_0000ecfa:
              *pbVar7 = param_5;
              return;
            }
LAB_0000ec2a:
            if (iVar5 != 0 && bVar11 == iVar5 < 0) goto LAB_0000ec3a;
            *pbVar7 = param_5;
            bVar11 = SCARRY4(iVar3,iVar5);
            iVar5 = iVar3 + iVar5;
          } while( true );
        }
        pbVar7 = pbVar8 + 1;
        *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf0;
        *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
        pbVar8 = pbVar7 + iVar9;
        bVar12 = SCARRY4(iVar3,iVar5);
        iVar5 = iVar3 + iVar5;
        bVar11 = iVar5 < 0;
        bVar10 = iVar5 == 0;
        sVar6 = sVar6 + -1;
      } while (sVar6 != -1);
      goto LAB_0000ece0;
    }
    if ((uVar4 & 1) == 0) {
      *pbVar7 = param_5;
      pbVar7 = pbVar7 + param_7;
      bVar11 = false;
      goto LAB_0000ecbc;
    }
    pbVar8 = pbVar7 + 1;
    *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf0;
    *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
    pbVar8 = pbVar8 + iVar9;
    bVar12 = false;
    bVar11 = iVar5 < 0;
    bVar10 = iVar5 == 0;
    do {
      while (!bVar10 && bVar12 == bVar11) {
        pbVar8[1] = param_5 ^ (param_5 ^ pbVar8[1]) & 0xf;
        *pbVar8 = param_5;
        bVar11 = SCARRY4(iVar1,iVar5);
        iVar5 = iVar1 + iVar5;
        pbVar7 = pbVar8;
        while( true ) {
          sVar6 = sVar6 + -1;
          pbVar7 = pbVar7 + param_7;
          if (sVar6 == -1) goto LAB_0000ecfa;
LAB_0000ecbc:
          if (iVar5 != 0 && bVar11 == iVar5 < 0) break;
          *pbVar7 = param_5;
          bVar11 = SCARRY4(iVar3,iVar5);
          iVar5 = iVar3 + iVar5;
        }
        *pbVar7 = param_5;
        pbVar7 = pbVar7 + -1;
        *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf0;
        pbVar8 = pbVar7 + param_7;
        bVar12 = SCARRY4(iVar1,iVar5);
        iVar5 = iVar1 + iVar5;
        bVar11 = iVar5 < 0;
        bVar10 = iVar5 == 0;
        sVar6 = sVar6 + -1;
        if (sVar6 == -1) goto LAB_0000ece0;
      }
      pbVar7 = pbVar8 + 1;
      *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf0;
      *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
      pbVar8 = pbVar7 + iVar9;
      bVar12 = SCARRY4(iVar3,iVar5);
      iVar5 = iVar3 + iVar5;
      bVar11 = iVar5 < 0;
      bVar10 = iVar5 == 0;
      sVar6 = sVar6 + -1;
    } while (sVar6 != -1);
LAB_0000ece0:
    *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf0;
    pbVar8[1] = param_5 ^ (param_5 ^ pbVar8[1]) & 0xf;
  }
  else {
    uVar4 = param_2;
    if ((int)param_3 < (int)param_1) {
      param_1 = param_3;
      uVar4 = param_4;
      param_4 = param_2;
    }
    sVar6 = (short)iVar5 + -1;
    pbVar7 = (byte *)((param_7 & 0xffff) * (uVar4 & 0xffff) + (param_1 >> 1) + param_6);
    pbVar8 = pbVar7 + param_7;
    iVar1 = (iVar3 - iVar5) * 2;
    iVar3 = iVar3 * 2 - iVar5;
    iVar5 = iVar3 + iVar5;
    if ((int)uVar4 < (int)param_4) {
      if ((param_1 & 1) == 0) {
        bVar12 = false;
        bVar11 = iVar3 < 0;
        bVar10 = iVar3 == 0;
        goto LAB_0000ea26;
      }
      *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf0;
      *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf0;
      bVar11 = false;
      iVar9 = iVar3;
      pbVar7 = pbVar7 + 1;
      pbVar8 = pbVar8 + 1;
      do {
        if (iVar9 == 0 || bVar11 != iVar9 < 0) {
          bVar12 = SCARRY4(iVar5,iVar9);
          iVar3 = iVar5 + iVar9;
          bVar11 = iVar3 < 0;
          bVar10 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
            *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
            goto LAB_0000eb52;
          }
        }
        else {
          *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
          pbVar7 = pbVar7 + param_7;
          pbVar8 = pbVar8 + param_7;
          bVar12 = SCARRY4(iVar1,iVar9);
          iVar3 = iVar1 + iVar9;
          bVar11 = iVar3 < 0;
          bVar10 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
            *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
LAB_0000eb52:
            *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf0;
            *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf0;
            return;
          }
        }
LAB_0000ea26:
        if (bVar10 || bVar12 != bVar11) {
          *pbVar7 = param_5;
          *pbVar8 = param_5;
          pbVar7 = pbVar7 + 1;
          pbVar8 = pbVar8 + 1;
          iVar2 = iVar5;
        }
        else {
          *pbVar7 = param_5;
          *pbVar8 = param_5;
          pbVar7 = pbVar7 + param_7 + 1;
          pbVar8 = pbVar8 + param_7 + 1;
          pbVar8[-1] = param_5 ^ (param_5 ^ pbVar8[-1]) & 0xf0;
          iVar2 = iVar1;
        }
        iVar9 = iVar2 + iVar3;
        sVar6 = sVar6 + -1;
        bVar11 = SCARRY4(iVar2,iVar3);
      } while (sVar6 != -1);
    }
    else {
      if ((param_1 & 1) == 0) {
        bVar12 = false;
        bVar11 = iVar3 < 0;
        bVar10 = iVar3 == 0;
        goto LAB_0000ead2;
      }
      *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf0;
      *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf0;
      bVar11 = false;
      iVar9 = iVar3;
      pbVar7 = pbVar7 + 1;
      pbVar8 = pbVar8 + 1;
      do {
        if (iVar9 == 0 || bVar11 != iVar9 < 0) {
          bVar12 = SCARRY4(iVar5,iVar9);
          iVar3 = iVar5 + iVar9;
          bVar11 = iVar3 < 0;
          bVar10 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
            *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
            goto LAB_0000eb52;
          }
        }
        else {
          *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
          pbVar7 = pbVar7 + -param_7;
          pbVar8 = pbVar8 + -param_7;
          bVar12 = SCARRY4(iVar1,iVar9);
          iVar3 = iVar1 + iVar9;
          bVar11 = iVar3 < 0;
          bVar10 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
            *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
            goto LAB_0000eb52;
          }
        }
LAB_0000ead2:
        if (bVar10 || bVar12 != bVar11) {
          *pbVar7 = param_5;
          *pbVar8 = param_5;
          pbVar7 = pbVar7 + 1;
          pbVar8 = pbVar8 + 1;
          iVar2 = iVar5;
        }
        else {
          *pbVar7 = param_5;
          *pbVar8 = param_5;
          pbVar7 = pbVar7 + (1 - param_7);
          pbVar8 = pbVar8 + (1 - param_7);
          pbVar7[-1] = param_5 ^ (param_5 ^ pbVar7[-1]) & 0xf0;
          iVar2 = iVar1;
        }
        iVar9 = iVar2 + iVar3;
        sVar6 = sVar6 + -1;
        bVar11 = SCARRY4(iVar2,iVar3);
      } while (sVar6 != -1);
    }
    *pbVar7 = param_5 ^ (param_5 ^ *pbVar7) & 0xf;
    *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
  }
  return;
LAB_0000ec3a:
  pbVar8 = pbVar7 + 1;
  *pbVar7 = param_5;
  *pbVar8 = param_5 ^ (param_5 ^ *pbVar8) & 0xf;
  pbVar8 = pbVar8 + iVar9;
  bVar12 = SCARRY4(iVar1,iVar5);
  iVar5 = iVar1 + iVar5;
  bVar11 = iVar5 < 0;
  bVar10 = iVar5 == 0;
  sVar6 = sVar6 + -1;
  if (sVar6 == -1) goto LAB_0000ece0;
  goto LAB_0000ebe4;
}



// FUN_0000ed02 @ 0xed02

void FUN_0000ed02(int param_1,uint param_2,int param_3,uint param_4,int param_5,uint param_6,
                 int param_7,uint param_8)

{
  int iVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  int iVar5;
  short sVar6;
  undefined2 *puVar7;
  undefined2 *puVar8;
  undefined2 *puVar9;
  undefined2 *puVar10;
  int iVar11;
  undefined2 *puVar12;
  undefined2 *puVar13;
  undefined2 *puVar14;
  undefined2 *puVar15;
  int iVar16;
  bool bVar17;
  bool bVar18;
  bool bVar19;
  
  iVar5 = param_1 - param_3;
  if (param_1 < param_3) {
    iVar5 = -iVar5;
  }
  iVar2 = param_2 - param_4;
  if ((int)param_2 < (int)param_4) {
    iVar2 = -iVar2;
  }
  if (iVar5 < iVar2) {
    iVar3 = param_1;
    if ((int)param_4 < (int)param_2) {
      iVar3 = param_3;
      param_2 = param_4;
      param_3 = param_1;
    }
    sVar6 = (short)iVar2 + -1;
    iVar11 = param_6 - 1;
    puVar7 = (undefined2 *)((param_6 & 0xffff) * (param_2 & 0xffff) + iVar3 + param_5);
    iVar16 = param_8 - 1;
    puVar9 = (undefined2 *)((param_8 & 0xffff) * (param_2 & 0xffff) + iVar3 + param_7);
    iVar1 = (iVar5 - iVar2) * 2;
    iVar5 = iVar5 * 2 - iVar2;
    iVar2 = iVar5 + iVar2;
    if (iVar3 < param_3) {
      if (((uint)puVar7 & 1) == 0) {
        *puVar7 = *puVar9;
        puVar7 = (undefined2 *)(param_6 + (int)puVar7);
        puVar9 = (undefined2 *)(param_8 + (int)puVar9);
        bVar17 = false;
        goto LAB_0000eebc;
      }
      *(undefined1 *)puVar7 = *(undefined1 *)puVar9;
      *(undefined1 *)((int)puVar7 + 1) = *(undefined1 *)((int)puVar9 + 1);
      puVar7 = (undefined2 *)((undefined1 *)((int)puVar7 + 1) + iVar11);
      puVar9 = (undefined2 *)((undefined1 *)((int)puVar9 + 1) + iVar16);
      bVar19 = false;
      bVar17 = iVar5 < 0;
      bVar18 = iVar5 == 0;
      do {
        while (!bVar18 && bVar19 == bVar17) {
          *(undefined1 *)puVar7 = *(undefined1 *)puVar9;
          *(undefined2 *)((int)puVar7 + 1) = *(undefined2 *)((int)puVar9 + 1);
          bVar17 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          puVar7 = (undefined2 *)((int)puVar7 + 1);
          puVar9 = (undefined2 *)((int)puVar9 + 1);
          while( true ) {
            sVar6 = sVar6 + -1;
            puVar9 = (undefined2 *)(param_8 + (int)puVar9);
            puVar7 = (undefined2 *)(param_6 + (int)puVar7);
            if (sVar6 == -1) goto LAB_0000ef3e;
LAB_0000eebc:
            if (iVar5 != 0 && bVar17 == iVar5 < 0) break;
            *puVar7 = *puVar9;
            bVar17 = SCARRY4(iVar2,iVar5);
            iVar5 = iVar2 + iVar5;
          }
          *puVar7 = *puVar9;
          *(undefined1 *)(puVar7 + 1) = *(undefined1 *)(puVar9 + 1);
          puVar7 = (undefined2 *)(iVar11 + (int)(puVar7 + 1));
          puVar9 = (undefined2 *)(iVar16 + (int)(puVar9 + 1));
          bVar19 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          bVar17 = iVar5 < 0;
          bVar18 = iVar5 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) goto LAB_0000ef3e;
        }
        *(undefined1 *)puVar7 = *(undefined1 *)puVar9;
        *(undefined1 *)((int)puVar7 + 1) = *(undefined1 *)((int)puVar9 + 1);
        puVar7 = (undefined2 *)((undefined1 *)((int)puVar7 + 1) + iVar11);
        puVar9 = (undefined2 *)((undefined1 *)((int)puVar9 + 1) + iVar16);
        bVar19 = SCARRY4(iVar2,iVar5);
        iVar5 = iVar2 + iVar5;
        bVar17 = iVar5 < 0;
        bVar18 = iVar5 == 0;
        sVar6 = sVar6 + -1;
      } while (sVar6 != -1);
    }
    else {
      if (((uint)puVar7 & 1) == 0) {
        *puVar7 = *puVar9;
        puVar7 = (undefined2 *)(param_6 + (int)puVar7);
        puVar9 = (undefined2 *)(param_8 + (int)puVar9);
        bVar17 = false;
        goto LAB_0000ef20;
      }
      *(undefined1 *)puVar7 = *(undefined1 *)puVar9;
      *(undefined1 *)((int)puVar7 + 1) = *(undefined1 *)((int)puVar9 + 1);
      puVar7 = (undefined2 *)((undefined1 *)((int)puVar7 + 1) + iVar11);
      puVar9 = (undefined2 *)((undefined1 *)((int)puVar9 + 1) + iVar16);
      bVar19 = false;
      bVar17 = iVar5 < 0;
      bVar18 = iVar5 == 0;
      do {
        while (!bVar18 && bVar19 == bVar17) {
          *(undefined1 *)((int)puVar7 + 1) = *(undefined1 *)((int)puVar9 + 1);
          puVar7 = (undefined2 *)((int)puVar7 + -1);
          puVar9 = (undefined2 *)((int)puVar9 + -1);
          *puVar7 = *puVar9;
          bVar17 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          while( true ) {
            sVar6 = sVar6 + -1;
            puVar9 = (undefined2 *)(param_8 + (int)puVar9);
            puVar7 = (undefined2 *)(param_6 + (int)puVar7);
            if (sVar6 == -1) goto LAB_0000ef3e;
LAB_0000ef20:
            if (iVar5 != 0 && bVar17 == iVar5 < 0) break;
            *puVar7 = *puVar9;
            bVar17 = SCARRY4(iVar2,iVar5);
            iVar5 = iVar2 + iVar5;
          }
          *puVar7 = *puVar9;
          *(undefined1 *)((int)puVar7 + -1) = *(undefined1 *)((int)puVar9 + -1);
          puVar7 = (undefined2 *)((undefined1 *)((int)puVar7 + -1) + param_6);
          puVar9 = (undefined2 *)((undefined1 *)((int)puVar9 + -1) + param_8);
          bVar19 = SCARRY4(iVar1,iVar5);
          iVar5 = iVar1 + iVar5;
          bVar17 = iVar5 < 0;
          bVar18 = iVar5 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) goto LAB_0000ef3e;
        }
        *(undefined1 *)puVar7 = *(undefined1 *)puVar9;
        *(undefined1 *)((int)puVar7 + 1) = *(undefined1 *)((int)puVar9 + 1);
        puVar7 = (undefined2 *)((undefined1 *)((int)puVar7 + 1) + iVar11);
        puVar9 = (undefined2 *)((undefined1 *)((int)puVar9 + 1) + iVar16);
        bVar19 = SCARRY4(iVar2,iVar5);
        iVar5 = iVar2 + iVar5;
        bVar17 = iVar5 < 0;
        bVar18 = iVar5 == 0;
        sVar6 = sVar6 + -1;
      } while (sVar6 != -1);
    }
LAB_0000ef3e:
    *(undefined1 *)puVar7 = *(undefined1 *)puVar9;
    *(undefined1 *)((int)puVar7 + 1) = *(undefined1 *)((int)puVar9 + 1);
  }
  else {
    uVar4 = param_2;
    if (param_3 < param_1) {
      param_1 = param_3;
      uVar4 = param_4;
      param_4 = param_2;
    }
    sVar6 = (short)iVar5 + -1;
    puVar7 = (undefined2 *)((param_6 & 0xffff) * (uVar4 & 0xffff) + param_1 + param_5);
    puVar9 = (undefined2 *)(param_6 + (int)puVar7);
    puVar12 = (undefined2 *)((param_8 & 0xffff) * (uVar4 & 0xffff) + param_1 + param_7);
    puVar14 = (undefined2 *)(param_8 + (int)puVar12);
    iVar3 = (iVar2 - iVar5) * 2;
    iVar2 = iVar2 * 2 - iVar5;
    iVar5 = iVar2 + iVar5;
    if ((int)uVar4 < (int)param_4) {
      if (((uint)puVar7 & 1) == 0) {
        bVar19 = false;
        bVar17 = iVar2 < 0;
        bVar18 = iVar2 == 0;
        goto LAB_0000ed82;
      }
      *(undefined1 *)puVar7 = *(undefined1 *)puVar12;
      *(undefined1 *)puVar9 = *(undefined1 *)puVar14;
      bVar19 = false;
      bVar17 = iVar2 < 0;
      bVar18 = iVar2 == 0;
      puVar7 = (undefined2 *)((int)puVar7 + 1);
      puVar9 = (undefined2 *)((int)puVar9 + 1);
      puVar12 = (undefined2 *)((int)puVar12 + 1);
      puVar14 = (undefined2 *)((int)puVar14 + 1);
      do {
        while( true ) {
          if (bVar18 || bVar19 != bVar17) {
            bVar19 = SCARRY4(iVar5,iVar2);
            iVar2 = iVar5 + iVar2;
            bVar17 = iVar2 < 0;
            bVar18 = iVar2 == 0;
            sVar6 = sVar6 + -1;
            if (sVar6 == -1) {
              puVar13 = (undefined2 *)((int)puVar12 + 1);
              puVar8 = (undefined2 *)((int)puVar7 + 1);
              *(undefined1 *)puVar7 = *(undefined1 *)puVar12;
              puVar15 = (undefined2 *)((int)puVar14 + 1);
              puVar10 = (undefined2 *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = *(undefined1 *)puVar14;
              goto LAB_0000ee2e;
            }
          }
          else {
            *(undefined1 *)puVar7 = *(undefined1 *)puVar12;
            puVar7 = (undefined2 *)(param_6 + (int)puVar7);
            puVar9 = (undefined2 *)(param_6 + (int)puVar9);
            puVar12 = (undefined2 *)(param_8 + (int)puVar12);
            puVar14 = (undefined2 *)(param_8 + (int)puVar14);
            bVar19 = SCARRY4(iVar3,iVar2);
            iVar2 = iVar3 + iVar2;
            bVar17 = iVar2 < 0;
            bVar18 = iVar2 == 0;
            sVar6 = sVar6 + -1;
            if (sVar6 == -1) {
              puVar13 = (undefined2 *)((int)puVar12 + 1);
              puVar8 = (undefined2 *)((int)puVar7 + 1);
              *(undefined1 *)puVar7 = *(undefined1 *)puVar12;
              puVar15 = (undefined2 *)((int)puVar14 + 1);
              puVar10 = (undefined2 *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = *(undefined1 *)puVar14;
              goto LAB_0000ee2e;
            }
          }
LAB_0000ed82:
          if (!bVar18 && bVar19 == bVar17) break;
          puVar13 = puVar12 + 1;
          puVar8 = puVar7 + 1;
          *puVar7 = *puVar12;
          puVar15 = puVar14 + 1;
          puVar10 = puVar9 + 1;
          *puVar9 = *puVar14;
          bVar19 = SCARRY4(iVar5,iVar2);
          iVar2 = iVar5 + iVar2;
          bVar17 = iVar2 < 0;
          bVar18 = iVar2 == 0;
          sVar6 = sVar6 + -1;
          puVar7 = puVar8;
          puVar9 = puVar10;
          puVar12 = puVar13;
          puVar14 = puVar15;
          if (sVar6 == -1) goto LAB_0000ee2e;
        }
        *puVar7 = *puVar12;
        *puVar9 = *puVar14;
        puVar8 = (undefined2 *)((int)puVar7 + param_6 + 2);
        puVar10 = (undefined2 *)((int)puVar9 + param_6 + 2);
        puVar13 = (undefined2 *)((int)puVar12 + param_8 + 2);
        puVar15 = (undefined2 *)((int)puVar14 + param_8 + 2);
        *(undefined1 *)((int)puVar10 + -1) = *(undefined1 *)((int)puVar15 + -1);
        bVar19 = SCARRY4(iVar3,iVar2);
        iVar2 = iVar3 + iVar2;
        bVar17 = iVar2 < 0;
        bVar18 = iVar2 == 0;
        sVar6 = sVar6 + -1;
        puVar7 = puVar8;
        puVar9 = puVar10;
        puVar12 = puVar13;
        puVar14 = puVar15;
      } while (sVar6 != -1);
    }
    else {
      if (((uint)puVar7 & 1) == 0) {
        bVar19 = false;
        bVar17 = iVar2 < 0;
        bVar18 = iVar2 == 0;
        goto LAB_0000ede4;
      }
      *(undefined1 *)puVar7 = *(undefined1 *)puVar12;
      *(undefined1 *)puVar9 = *(undefined1 *)puVar14;
      bVar19 = false;
      bVar17 = iVar2 < 0;
      bVar18 = iVar2 == 0;
      puVar7 = (undefined2 *)((int)puVar7 + 1);
      puVar9 = (undefined2 *)((int)puVar9 + 1);
      puVar12 = (undefined2 *)((int)puVar12 + 1);
      puVar14 = (undefined2 *)((int)puVar14 + 1);
      do {
        while( true ) {
          if (bVar18 || bVar19 != bVar17) {
            bVar19 = SCARRY4(iVar5,iVar2);
            iVar2 = iVar5 + iVar2;
            bVar17 = iVar2 < 0;
            bVar18 = iVar2 == 0;
            sVar6 = sVar6 + -1;
            if (sVar6 == -1) {
              puVar13 = (undefined2 *)((int)puVar12 + 1);
              puVar8 = (undefined2 *)((int)puVar7 + 1);
              *(undefined1 *)puVar7 = *(undefined1 *)puVar12;
              puVar15 = (undefined2 *)((int)puVar14 + 1);
              puVar10 = (undefined2 *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = *(undefined1 *)puVar14;
              goto LAB_0000ee2e;
            }
          }
          else {
            *(undefined1 *)puVar9 = *(undefined1 *)puVar14;
            puVar7 = (undefined2 *)((int)puVar7 - param_6);
            puVar9 = (undefined2 *)((int)puVar9 - param_6);
            puVar12 = (undefined2 *)((int)puVar12 - param_8);
            puVar14 = (undefined2 *)((int)puVar14 - param_8);
            bVar19 = SCARRY4(iVar3,iVar2);
            iVar2 = iVar3 + iVar2;
            bVar17 = iVar2 < 0;
            bVar18 = iVar2 == 0;
            sVar6 = sVar6 + -1;
            if (sVar6 == -1) {
              puVar13 = (undefined2 *)((int)puVar12 + 1);
              puVar8 = (undefined2 *)((int)puVar7 + 1);
              *(undefined1 *)puVar7 = *(undefined1 *)puVar12;
              puVar15 = (undefined2 *)((int)puVar14 + 1);
              puVar10 = (undefined2 *)((int)puVar9 + 1);
              *(undefined1 *)puVar9 = *(undefined1 *)puVar14;
              goto LAB_0000ee2e;
            }
          }
LAB_0000ede4:
          if (!bVar18 && bVar19 == bVar17) break;
          puVar13 = puVar12 + 1;
          puVar8 = puVar7 + 1;
          *puVar7 = *puVar12;
          puVar15 = puVar14 + 1;
          puVar10 = puVar9 + 1;
          *puVar9 = *puVar14;
          bVar19 = SCARRY4(iVar5,iVar2);
          iVar2 = iVar5 + iVar2;
          bVar17 = iVar2 < 0;
          bVar18 = iVar2 == 0;
          sVar6 = sVar6 + -1;
          puVar7 = puVar8;
          puVar9 = puVar10;
          puVar12 = puVar13;
          puVar14 = puVar15;
          if (sVar6 == -1) goto LAB_0000ee2e;
        }
        *puVar7 = *puVar12;
        *puVar9 = *puVar14;
        puVar8 = (undefined2 *)((int)puVar7 + (2 - param_6));
        puVar10 = (undefined2 *)((int)puVar9 + (2 - param_6));
        puVar13 = (undefined2 *)((int)puVar12 + (2 - param_8));
        puVar15 = (undefined2 *)((int)puVar14 + (2 - param_8));
        *(undefined1 *)((int)puVar8 + -1) = *(undefined1 *)((int)puVar13 + -1);
        bVar19 = SCARRY4(iVar3,iVar2);
        iVar2 = iVar3 + iVar2;
        bVar17 = iVar2 < 0;
        bVar18 = iVar2 == 0;
        sVar6 = sVar6 + -1;
        puVar7 = puVar8;
        puVar9 = puVar10;
        puVar12 = puVar13;
        puVar14 = puVar15;
      } while (sVar6 != -1);
    }
LAB_0000ee2e:
    *(undefined1 *)puVar8 = *(undefined1 *)puVar13;
    *(undefined1 *)puVar10 = *(undefined1 *)puVar15;
  }
  return;
}



// FUN_0000ef48 @ 0xef48

/* WARNING: Type propagation algorithm not settling */

void FUN_0000ef48(uint param_1,uint param_2,uint param_3,uint param_4,int param_5,uint param_6,
                 int param_7,uint param_8)

{
  byte bVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  int iVar5;
  short sVar6;
  byte *pbVar7;
  byte *pbVar8;
  int iVar9;
  byte *pbVar10;
  byte *pbVar11;
  int iVar12;
  bool bVar13;
  bool bVar14;
  bool bVar15;
  
  iVar5 = param_1 - param_3;
  if ((int)param_1 < (int)param_3) {
    iVar5 = -iVar5;
  }
  iVar3 = param_2 - param_4;
  if ((int)param_2 < (int)param_4) {
    iVar3 = -iVar3;
  }
  if (iVar5 < iVar3) {
    uVar4 = param_1;
    if ((int)param_4 < (int)param_2) {
      uVar4 = param_3;
      param_2 = param_4;
      param_3 = param_1;
    }
    sVar6 = (short)iVar3 + -1;
    iVar9 = param_6 - 1;
    pbVar10 = (byte *)((param_6 & 0xffff) * (param_2 & 0xffff) + (uVar4 >> 1) + param_5);
    iVar12 = param_8 - 1;
    pbVar7 = (byte *)((param_8 & 0xffff) * (param_2 & 0xffff) + (uVar4 >> 1) + param_7);
    iVar2 = (iVar5 - iVar3) * 2;
    iVar5 = iVar5 * 2 - iVar3;
    iVar3 = iVar5 + iVar3;
    if ((int)uVar4 < (int)param_3) {
      if ((uVar4 & 1) == 0) {
        *pbVar10 = *pbVar7;
        pbVar10 = pbVar10 + param_6;
        pbVar7 = pbVar7 + param_8;
        bVar14 = false;
        goto LAB_0000f24a;
      }
      pbVar11 = pbVar10 + 1;
      *pbVar10 = *pbVar7 ^ (*pbVar7 ^ *pbVar10) & 0xf0;
      bVar1 = pbVar7[1];
      *pbVar11 = bVar1 ^ (bVar1 ^ *pbVar11) & 0xf;
      pbVar11 = pbVar11 + iVar9;
      pbVar8 = pbVar7 + 1 + iVar12;
      bVar15 = false;
      bVar14 = iVar5 < 0;
      bVar13 = iVar5 == 0;
LAB_0000f1f8:
      do {
        if (!bVar13 && bVar15 == bVar14) {
          pbVar7 = pbVar8 + 1;
          pbVar10 = pbVar11 + 1;
          *pbVar11 = *pbVar8 ^ (*pbVar8 ^ *pbVar11) & 0xf0;
          *pbVar10 = *pbVar7;
          bVar14 = SCARRY4(iVar2,iVar5);
          iVar5 = iVar2 + iVar5;
          do {
            sVar6 = sVar6 + -1;
            pbVar7 = pbVar7 + param_8;
            pbVar10 = pbVar10 + param_6;
            if (sVar6 == -1) {
LAB_0000f33e:
              *pbVar10 = *pbVar7;
              return;
            }
LAB_0000f24a:
            if (iVar5 != 0 && bVar14 == iVar5 < 0) goto LAB_0000f25c;
            *pbVar10 = *pbVar7;
            bVar14 = SCARRY4(iVar3,iVar5);
            iVar5 = iVar3 + iVar5;
          } while( true );
        }
        pbVar10 = pbVar11 + 1;
        *pbVar11 = *pbVar8 ^ (*pbVar8 ^ *pbVar11) & 0xf0;
        bVar1 = pbVar8[1];
        *pbVar10 = bVar1 ^ (bVar1 ^ *pbVar10) & 0xf;
        pbVar11 = pbVar10 + iVar9;
        pbVar8 = pbVar8 + 1 + iVar12;
        bVar15 = SCARRY4(iVar3,iVar5);
        iVar5 = iVar3 + iVar5;
        bVar14 = iVar5 < 0;
        bVar13 = iVar5 == 0;
        sVar6 = sVar6 + -1;
      } while (sVar6 != -1);
      goto LAB_0000f320;
    }
    if ((uVar4 & 1) == 0) {
      *pbVar10 = *pbVar7;
      pbVar10 = pbVar10 + param_6;
      pbVar7 = pbVar7 + param_8;
      bVar14 = false;
      goto LAB_0000f2f6;
    }
    pbVar11 = pbVar10 + 1;
    *pbVar10 = *pbVar7 ^ (*pbVar7 ^ *pbVar10) & 0xf0;
    bVar1 = pbVar7[1];
    *pbVar11 = bVar1 ^ (bVar1 ^ *pbVar11) & 0xf;
    pbVar11 = pbVar11 + iVar9;
    pbVar8 = pbVar7 + 1 + iVar12;
    bVar15 = false;
    bVar14 = iVar5 < 0;
    bVar13 = iVar5 == 0;
    do {
      while (!bVar13 && bVar15 == bVar14) {
        pbVar11[1] = pbVar8[1] ^ (pbVar8[1] ^ pbVar11[1]) & 0xf;
        *pbVar11 = *pbVar8;
        bVar14 = SCARRY4(iVar2,iVar5);
        iVar5 = iVar2 + iVar5;
        pbVar10 = pbVar11;
        pbVar7 = pbVar8;
        while( true ) {
          sVar6 = sVar6 + -1;
          pbVar7 = pbVar7 + param_8;
          pbVar10 = pbVar10 + param_6;
          if (sVar6 == -1) goto LAB_0000f33e;
LAB_0000f2f6:
          if (iVar5 != 0 && bVar14 == iVar5 < 0) break;
          *pbVar10 = *pbVar7;
          bVar14 = SCARRY4(iVar3,iVar5);
          iVar5 = iVar3 + iVar5;
        }
        *pbVar10 = *pbVar7;
        pbVar10 = pbVar10 + -1;
        bVar1 = pbVar7[-1];
        *pbVar10 = bVar1 ^ (bVar1 ^ *pbVar10) & 0xf0;
        pbVar11 = pbVar10 + param_6;
        pbVar8 = pbVar7 + -1 + param_8;
        bVar15 = SCARRY4(iVar2,iVar5);
        iVar5 = iVar2 + iVar5;
        bVar14 = iVar5 < 0;
        bVar13 = iVar5 == 0;
        sVar6 = sVar6 + -1;
        if (sVar6 == -1) goto LAB_0000f320;
      }
      pbVar10 = pbVar11 + 1;
      *pbVar11 = *pbVar8 ^ (*pbVar8 ^ *pbVar11) & 0xf0;
      bVar1 = pbVar8[1];
      *pbVar10 = bVar1 ^ (bVar1 ^ *pbVar10) & 0xf;
      pbVar11 = pbVar10 + iVar9;
      pbVar8 = pbVar8 + 1 + iVar12;
      bVar15 = SCARRY4(iVar3,iVar5);
      iVar5 = iVar3 + iVar5;
      bVar14 = iVar5 < 0;
      bVar13 = iVar5 == 0;
      sVar6 = sVar6 + -1;
    } while (sVar6 != -1);
LAB_0000f320:
    *pbVar11 = *pbVar8 ^ (*pbVar8 ^ *pbVar11) & 0xf0;
    pbVar11[1] = pbVar8[1] ^ (pbVar8[1] ^ pbVar11[1]) & 0xf;
  }
  else {
    uVar4 = param_2;
    if ((int)param_3 < (int)param_1) {
      param_1 = param_3;
      uVar4 = param_4;
      param_4 = param_2;
    }
    sVar6 = (short)iVar5 + -1;
    pbVar7 = (byte *)((param_6 & 0xffff) * (uVar4 & 0xffff) + (param_1 >> 1) + param_5);
    pbVar8 = pbVar7 + param_6;
    pbVar10 = (byte *)((param_8 & 0xffff) * (uVar4 & 0xffff) + (param_1 >> 1) + param_7);
    pbVar11 = pbVar10 + param_8;
    iVar2 = (iVar3 - iVar5) * 2;
    iVar3 = iVar3 * 2 - iVar5;
    iVar5 = iVar3 + iVar5;
    if ((int)uVar4 < (int)param_4) {
      if ((param_1 & 1) == 0) {
        bVar15 = false;
        bVar14 = iVar3 < 0;
        bVar13 = iVar3 == 0;
        goto LAB_0000efe4;
      }
      *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf0;
      *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf0;
      bVar14 = false;
      iVar9 = iVar3;
      pbVar7 = pbVar7 + 1;
      pbVar8 = pbVar8 + 1;
      pbVar10 = pbVar10 + 1;
      pbVar11 = pbVar11 + 1;
      do {
        if (iVar9 == 0 || bVar14 != iVar9 < 0) {
          bVar15 = SCARRY4(iVar5,iVar9);
          iVar3 = iVar5 + iVar9;
          bVar14 = iVar3 < 0;
          bVar13 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf;
            *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf;
            goto LAB_0000f140;
          }
        }
        else {
          *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf;
          pbVar7 = pbVar7 + param_6;
          pbVar8 = pbVar8 + param_6;
          pbVar10 = pbVar10 + param_8;
          pbVar11 = pbVar11 + param_8;
          bVar15 = SCARRY4(iVar2,iVar9);
          iVar3 = iVar2 + iVar9;
          bVar14 = iVar3 < 0;
          bVar13 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf;
            *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf;
LAB_0000f140:
            *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf0;
            *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf0;
            return;
          }
        }
LAB_0000efe4:
        if (bVar13 || bVar15 != bVar14) {
          *pbVar7 = *pbVar10;
          *pbVar8 = *pbVar11;
          pbVar10 = pbVar10 + 1;
          pbVar7 = pbVar7 + 1;
          pbVar11 = pbVar11 + 1;
          pbVar8 = pbVar8 + 1;
          iVar12 = iVar5;
        }
        else {
          *pbVar7 = *pbVar10;
          *pbVar8 = *pbVar11;
          pbVar7 = pbVar7 + param_6 + 1;
          pbVar8 = pbVar8 + param_6 + 1;
          pbVar10 = pbVar10 + param_8 + 1;
          pbVar11 = pbVar11 + param_8 + 1;
          pbVar8[-1] = pbVar11[-1] ^ (pbVar11[-1] ^ pbVar8[-1]) & 0xf0;
          iVar12 = iVar2;
        }
        iVar9 = iVar12 + iVar3;
        sVar6 = sVar6 + -1;
        bVar14 = SCARRY4(iVar12,iVar3);
      } while (sVar6 != -1);
    }
    else {
      if ((param_1 & 1) == 0) {
        bVar15 = false;
        bVar14 = iVar3 < 0;
        bVar13 = iVar3 == 0;
        goto LAB_0000f0aa;
      }
      *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf0;
      *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf0;
      bVar14 = false;
      iVar9 = iVar3;
      pbVar7 = pbVar7 + 1;
      pbVar8 = pbVar8 + 1;
      pbVar10 = pbVar10 + 1;
      pbVar11 = pbVar11 + 1;
      do {
        if (iVar9 == 0 || bVar14 != iVar9 < 0) {
          bVar15 = SCARRY4(iVar5,iVar9);
          iVar3 = iVar5 + iVar9;
          bVar14 = iVar3 < 0;
          bVar13 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf;
            *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf;
            goto LAB_0000f140;
          }
        }
        else {
          *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf;
          pbVar7 = pbVar7 + -param_6;
          pbVar8 = pbVar8 + -param_6;
          pbVar10 = pbVar10 + -param_8;
          pbVar11 = pbVar11 + -param_8;
          bVar15 = SCARRY4(iVar2,iVar9);
          iVar3 = iVar2 + iVar9;
          bVar14 = iVar3 < 0;
          bVar13 = iVar3 == 0;
          sVar6 = sVar6 + -1;
          if (sVar6 == -1) {
            *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf;
            *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf;
            goto LAB_0000f140;
          }
        }
LAB_0000f0aa:
        if (bVar13 || bVar15 != bVar14) {
          *pbVar7 = *pbVar10;
          *pbVar8 = *pbVar11;
          pbVar10 = pbVar10 + 1;
          pbVar7 = pbVar7 + 1;
          pbVar11 = pbVar11 + 1;
          pbVar8 = pbVar8 + 1;
          iVar12 = iVar5;
        }
        else {
          *pbVar7 = *pbVar10;
          *pbVar8 = *pbVar11;
          pbVar7 = pbVar7 + (1 - param_6);
          pbVar8 = pbVar8 + (1 - param_6);
          pbVar10 = pbVar10 + (1 - param_8);
          pbVar11 = pbVar11 + (1 - param_8);
          pbVar7[-1] = pbVar10[-1] ^ (pbVar10[-1] ^ pbVar7[-1]) & 0xf0;
          iVar12 = iVar2;
        }
        iVar9 = iVar12 + iVar3;
        sVar6 = sVar6 + -1;
        bVar14 = SCARRY4(iVar12,iVar3);
      } while (sVar6 != -1);
    }
    *pbVar7 = *pbVar10 ^ (*pbVar10 ^ *pbVar7) & 0xf;
    *pbVar8 = *pbVar11 ^ (*pbVar11 ^ *pbVar8) & 0xf;
  }
  return;
LAB_0000f25c:
  pbVar11 = pbVar10 + 1;
  *pbVar10 = *pbVar7;
  bVar1 = pbVar7[1];
  *pbVar11 = bVar1 ^ (bVar1 ^ *pbVar11) & 0xf;
  pbVar11 = pbVar11 + iVar9;
  pbVar8 = pbVar7 + 1 + iVar12;
  bVar15 = SCARRY4(iVar2,iVar5);
  iVar5 = iVar2 + iVar5;
  bVar14 = iVar5 < 0;
  bVar13 = iVar5 == 0;
  sVar6 = sVar6 + -1;
  if (sVar6 == -1) goto LAB_0000f320;
  goto LAB_0000f1f8;
}



// FUN_0000f346 @ 0xf346

void FUN_0000f346(int param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4)

{
  int iVar1;
  short sVar2;
  
  sVar2 = 0;
  do {
    iVar1 = sVar2 * 0x14;
    *(undefined4 *)(param_1 + iVar1) = param_2;
    *(undefined4 *)(param_1 + 4 + iVar1) = param_3;
    *(undefined4 *)(param_1 + 8 + iVar1) = param_4;
    sVar2 = sVar2 + 1;
  } while (sVar2 < 0x4b);
  return;
}



// FUN_0000f380 @ 0xf380

void FUN_0000f380(int param_1,undefined2 param_2,undefined2 param_3,undefined2 param_4)

{
  int iVar1;
  short sVar2;
  
  sVar2 = 0;
  do {
    iVar1 = sVar2 * 0x14;
    *(undefined2 *)(param_1 + 0xc + iVar1) = param_2;
    *(undefined2 *)(param_1 + 0xe + iVar1) = param_3;
    *(undefined2 *)(param_1 + 0x10 + iVar1) = param_4;
    sVar2 = sVar2 + 1;
  } while (sVar2 < 0x4b);
  return;
}



// FUN_0000f3ba @ 0xf3ba

void FUN_0000f3ba(int param_1,undefined2 param_2)

{
  short sVar1;
  
  sVar1 = 0;
  do {
    *(undefined2 *)(param_1 + 0x12 + sVar1 * 0x14) = param_2;
    sVar1 = sVar1 + 1;
  } while (sVar1 < 0x4b);
  return;
}



// FUN_0000f3d8 @ 0xf3d8

int * FUN_0000f3d8(int param_1,int param_2,undefined2 param_3)

{
  int *piVar1;
  short sVar2;
  
  piVar1 = (int *)FUN_000021a4(0x6c8);
  *(undefined1 *)((int)piVar1 + 0x68d) = 2;
  *(undefined2 *)((int)piVar1 + 0x696) = 0x2a3;
  *(undefined2 *)(piVar1 + 0x185) = param_3;
  *piVar1 = param_1;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(short *)((int)piVar1 + 0x642) =
       *(short *)(piVar1[1] + 0x1a) + *(char *)((int)piVar1 + 0x68d) * 0x10;
  sVar2 = *(short *)(piVar1[1] + 0x1c);
  if (sVar2 < 0) {
    sVar2 = sVar2 + 1;
  }
  *(char *)((int)piVar1 + 0x691) = (char)(sVar2 >> 1);
  sVar2 = *(short *)(piVar1[1] + 0x1c);
  if (sVar2 < 0) {
    sVar2 = sVar2 + 1;
  }
  *(char *)((int)piVar1 + 0x692) = (char)(sVar2 >> 1);
  *(undefined2 *)((int)piVar1 + 0x626) = *(undefined2 *)(piVar1[1] + 0x44);
  *(undefined2 *)((int)piVar1 + 0x63e) = 0xffa5;
  *(undefined1 *)((int)piVar1 + 0x68a) = 0x2d;
  FUN_0000f346((int)piVar1 + 0x36,*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
               *(undefined4 *)(param_1 + 0xc));
  *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 2;
  return piVar1;
}



// FUN_0000f48c @ 0xf48c

void FUN_0000f48c(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x616) != 0) {
    FUN_00002008(*(undefined4 *)(param_1 + 0x616));
  }
  if (*(int *)(param_1 + 0x6a2) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x6a2) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  if (*(int *)(param_1 + 0x6bc) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x6bc) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  return;
}



// FUN_0000f4c8 @ 0xf4c8

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

int FUN_0000f4c8(int param_1,int param_2,undefined4 param_3)

{
  int iVar1;
  int iVar2;
  char cVar3;
  undefined4 uVar4;
  
  iVar2 = FUN_0000f3d8(param_1,param_2,param_3);
  *(undefined1 *)(iVar2 + 0x64c) = 1;
  *(int *)(iVar2 + 0x2e) = *(int *)(param_1 + 8) - (int)*(short *)(*(int *)(iVar2 + 4) + 0x42);
  if ((SUB_00020b28 == 0) && (param_2 == 1)) {
    SUB_00020b28 = iVar2;
    *(undefined4 *)(iVar2 + 0x32) = 0xfcf6;
    *(ushort *)(iVar2 + 0x698) = *(ushort *)(iVar2 + 0x698) | 0x4000;
    *(short *)(iVar2 + 0x642) = *(short *)(iVar2 + 0x642) + *(char *)(iVar2 + 0x68d) * -0x10;
    iVar1 = SUB_000207fc;
    *(undefined1 *)(iVar2 + 0x68d) = *(undefined1 *)(SUB_000207fc + 0xb9);
    *(undefined1 *)(iVar2 + 0x68e) = *(undefined1 *)(iVar1 + 0xbb);
    *(undefined1 *)(iVar2 + 0x690) = *(undefined1 *)(iVar1 + 0xbd);
    *(short *)(iVar2 + 0x642) =
         *(char *)(iVar2 + 0x68e) * 5 + *(char *)(iVar2 + 0x68d) * 0x10 + *(short *)(iVar2 + 0x642);
    *(char *)(iVar2 + 0x691) =
         (char)(((int)*(short *)(iVar1 + 0xb6) * (int)*(char *)(iVar2 + 0x691)) / 100);
    cVar3 = (char)(((int)*(short *)(iVar1 + 0xb6) * (int)*(char *)(iVar2 + 0x692)) / 100);
    *(char *)(iVar2 + 0x692) = cVar3;
    if ((*(char *)(iVar2 + 0x691) == '\0') && (cVar3 == '\0')) {
      *(undefined1 *)(iVar2 + 0x691) = 1;
    }
    *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x102140;
    *(undefined4 *)(param_1 + 0x18) = 1000000000;
  }
  else {
    *(undefined4 *)(iVar2 + 0x32) = 0x13038;
    *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x102100;
  }
  if (param_2 == 2) {
    uVar4 = 5;
  }
  else {
    if (param_2 < 3) {
      if (param_2 == 1) {
        FUN_0001caa4(*(undefined4 *)(param_1 + 4),0,*(undefined4 *)(param_1 + 0xc),1,param_1);
        return iVar2;
      }
      return iVar2;
    }
    if (param_2 != 3) {
      return iVar2;
    }
    if (_DAT_00021074 == 0) {
      _DAT_00021074 = iVar2;
    }
    uVar4 = 6;
  }
  FUN_0001caa4(*(undefined4 *)(param_1 + 4),0,*(undefined4 *)(param_1 + 0xc),uVar4,param_1);
  *(char *)(iVar2 + 0x68d) = *(char *)(iVar2 + 0x68d) + '\x01';
  *(short *)(iVar2 + 0x642) = *(short *)(iVar2 + 0x642) + 0x10;
  return iVar2;
}



// FUN_0000f636 @ 0xf636

int FUN_0000f636(int param_1,int param_2,undefined4 param_3)

{
  int iVar1;
  
  if ((DAT_0002c5ae == 0) && (param_2 == 7)) {
    DAT_0002c5ae = param_1;
    iVar1 = 0;
  }
  else {
    iVar1 = FUN_0000f3d8(param_1,param_2,param_3);
    *(undefined1 *)(iVar1 + 0x64c) = 1;
    *(int *)(iVar1 + 0x2e) = *(int *)(param_1 + 8) - (int)*(short *)(*(int *)(iVar1 + 4) + 0x42);
    *(undefined4 *)(iVar1 + 0x32) = 0x13038;
    *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x102100;
    FUN_0001caa4(*(undefined4 *)(param_1 + 4),0,*(undefined4 *)(param_1 + 0xc),2,param_1);
  }
  return iVar1;
}



// FUN_0000f6b2 @ 0xf6b2

int FUN_0000f6b2(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                undefined4 param_5,undefined4 param_6,undefined4 param_7)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002c5ae,param_1,param_2,param_3,param_4,param_5,param_6,param_7)
  ;
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(short *)(iVar2 + 0x60a) = (short)param_4;
    *(short *)(iVar2 + 0x60c) = (short)param_5;
    *(short *)(iVar2 + 0x60e) = (short)param_6;
  }
  return iVar2;
}



// FUN_0000f714 @ 0xf714

int FUN_0000f714(int param_1,undefined4 param_2,undefined4 param_3)

{
  int iVar1;
  int *piVar2;
  
  iVar1 = FUN_0000f3d8(param_1,param_2,param_3);
  *(undefined1 *)(iVar1 + 0x64c) = 1;
  *(int *)(iVar1 + 0x2e) = *(int *)(param_1 + 8) - (int)*(short *)(*(int *)(iVar1 + 4) + 0x42);
  *(undefined4 *)(iVar1 + 0x32) = 0x1312c;
  *(undefined2 *)(iVar1 + 0x696) = 0;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x102100;
  *(undefined1 *)(iVar1 + 0x68d) = 8;
  FUN_0001caa4(*(undefined4 *)(param_1 + 4),0,*(undefined4 *)(param_1 + 0xc),4,param_1);
  piVar2 = (int *)FUN_0001d2b8(param_1);
  if (piVar2 != (int *)0x0) {
    *(undefined2 *)(piVar2 + 0x10) = 1;
    *(undefined2 *)((int)piVar2 + 0x3e) = 0x200;
    *(undefined4 *)((int)piVar2 + 0x42) = 25000;
    *(byte *)(*piVar2 + 0x15) = *(byte *)(*piVar2 + 0x15) | 1;
    *(int *)(iVar1 + 0x6a2) = *piVar2;
  }
  return iVar1;
}



// FUN_0000f7b0 @ 0xf7b0

int FUN_0000f7b0(undefined4 param_1,undefined4 param_2,undefined4 param_3)

{
  int iVar1;
  
  iVar1 = FUN_0000f714(param_1,param_2,param_3);
  if (*(int *)(iVar1 + 0x6a2) != 0) {
    *(undefined2 *)(**(int **)(iVar1 + 0x6a2) + 0x3e) = 0x100;
  }
  return iVar1;
}



// FUN_0000f7c0 @ 0xf7c0

int FUN_0000f7c0(void)

{
  int iVar1;
  
  iVar1 = FUN_0000f714();
  if (*(int *)(iVar1 + 0x6a2) != 0) {
    *(undefined2 *)(**(int **)(iVar1 + 0x6a2) + 0x3e) = 0x100;
  }
  return iVar1;
}



// FUN_0000f7e0 @ 0xf7e0

int FUN_0000f7e0(int param_1)

{
  int iVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  
  sVar4 = 0;
  sVar3 = 0x7fff;
  do {
    iVar1 = (int)sVar3 + (int)sVar4;
    if (iVar1 < 0) {
      iVar1 = iVar1 + 1;
    }
    sVar2 = (short)(iVar1 >> 1);
    if (param_1 < (int)sVar2 * (int)sVar2) {
      sVar3 = sVar2;
    }
    else {
      sVar4 = sVar2;
    }
  } while ((int)sVar4 < sVar3 + -1);
  return (int)sVar4;
}



// FUN_0000f822 @ 0xf822

int FUN_0000f822(int param_1,int param_2,int param_3,int param_4,int param_5,int param_6,int param_7
                ,int param_8)

{
  int iVar1;
  short sVar2;
  
  iVar1 = param_8 * param_4 >> 0x10;
  if (iVar1 == 0) {
    iVar1 = 0;
  }
  else {
    sVar2 = FUN_00002488((param_7 * param_3 + param_6 * param_2 + param_5 * param_1 >> 4) / iVar1);
    iVar1 = (int)sVar2;
  }
  return iVar1;
}



// FUN_0000f87c @ 0xf87c

int FUN_0000f87c(short param_1)

{
  if (param_1 < 0x2001) {
    if (param_1 < -0x2000) {
      param_1 = param_1 + 0x4000;
    }
  }
  else {
    param_1 = param_1 + -0x4000;
  }
  return (int)param_1;
}



// FUN_0000f89a @ 0xf89a

int FUN_0000f89a(uint param_1)

{
  int iVar1;
  
  iVar1 = (int)param_1 >> 0x10;
  if (7 < iVar1) {
    iVar1 = 7;
  }
  return (int)(short)(*(short *)(&DAT_000204ea + iVar1 * 4) +
                     (short)((param_1 & 0xffff) *
                             (*(int *)(&DAT_000204ec + iVar1 * 4) -
                             *(int *)(&DAT_000204e8 + iVar1 * 4)) >> 0x10));
}



// FUN_0000f8d8 @ 0xf8d8

int FUN_0000f8d8(int param_1,short param_2)

{
  int iVar1;
  
  if (param_1 < 0x200) {
    iVar1 = -(param_1 + -0x200) * (int)param_2;
    if (iVar1 < 0) {
      iVar1 = iVar1 + 0xfff;
    }
    param_2 = param_2 - (short)(iVar1 >> 0xc);
  }
  return (int)param_2;
}



// FUN_0000f914 @ 0xf914

int FUN_0000f914(short param_1,short param_2,undefined1 *param_3)

{
  short sVar2;
  int iVar1;
  short sVar3;
  
  if (param_2 == 0) {
    sVar3 = *(short *)(DAT_00027074 + 0x28);
    sVar2 = *(short *)(DAT_00027074 + 0x2a);
  }
  else {
    sVar2 = FUN_00003908((int)param_2);
    sVar3 = *(short *)(DAT_00027074 + 0x28) +
            (short)(((int)sVar2 *
                    ((int)*(short *)(DAT_00027074 + 0x2c) - (int)*(short *)(DAT_00027074 + 0x28))) /
                   0xf);
    sVar2 = *(short *)(DAT_00027074 + 0x2a) +
            (short)((((int)*(short *)(DAT_00027074 + 0x2e) - (int)*(short *)(DAT_00027074 + 0x2a)) *
                    (int)sVar2) / 0xf);
  }
  if (*(short *)(DAT_00027074 + 0x36) < param_1) {
    *param_3 = 0xc;
    iVar1 = 0x20 - ((int)param_1 - (int)*(short *)(DAT_00027074 + 0x36));
    if (iVar1 < 0) {
      iVar1 = 0;
    }
    iVar1 = iVar1 * ((int)sVar2 * (int)*(short *)(DAT_00027074 + 0x36) + (int)sVar3);
    if (iVar1 < 0) {
      iVar1 = iVar1 + 0x7ff;
    }
    sVar3 = (short)(iVar1 >> 0xb);
  }
  else {
    iVar1 = (int)sVar2 * (int)param_1;
    if (iVar1 < 0) {
      iVar1 = iVar1 + 0x3f;
    }
    sVar3 = sVar3 + (short)(iVar1 >> 6);
  }
  return (int)sVar3;
}



// FUN_0000f9e0 @ 0xf9e0

void FUN_0000f9e0(short param_1,int *param_2)

{
  *param_2 = (int)(short)(0x551a / (int)(short)(param_1 + 0x105 >> 3)) *
             (*(int *)(DAT_00027074 + 0x3a) >> 1) >> 7;
  return;
}



// FUN_0000fa16 @ 0xfa16

void FUN_0000fa16(int *param_1)

{
  int *piVar1;
  int iVar2;
  undefined2 uVar3;
  short *psVar4;
  char cVar5;
  
  psVar4 = (short *)((int)param_1 + 0x612);
  piVar1 = (int *)((int)param_1 + *psVar4 * 0x14 + 0x36);
  *psVar4 = *psVar4 + 1;
  if (*(short *)((int)param_1 + 0x612) == 0x4b) {
    *psVar4 = 0;
  }
  iVar2 = *param_1;
  if ((((*(byte *)(iVar2 + 0x17) & 2) == 0) && (param_1 != SUB_00020b28)) &&
     ((DAT_00023d88 == 1 || ((DAT_00023d88 == 8 || (DAT_00023d88 == 7)))))) {
    if (1999 < *(int *)(iVar2 + 8) - *(int *)((int)param_1 + 0x2e)) {
      FUN_0001b856(iVar2);
    }
    if ((*(short *)param_1[1] != 1) &&
       ((DAT_00021070 == (int *)0x0 || (*(int *)(iVar2 + 0x1c) < *(int *)(*DAT_00021070 + 0x1c)))))
    {
      DAT_00021070 = param_1;
    }
  }
  if (DAT_00021058 == 0) {
    if (((*(byte *)(param_1 + 0x1a6) & 4) == 0) || ((*(byte *)((int)param_1 + 0x69a) & 4) == 0)) {
      if (param_1[0x1af] == 0) {
        (**(code **)((int)param_1 + 0x32))(param_1);
      }
      else {
        FUN_0000fc7c(param_1);
      }
    }
    else {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
    }
    FUN_000124e0(param_1);
    *piVar1 = param_1[2] + *(int *)(iVar2 + 4);
    piVar1[1] = param_1[3] + *(int *)(iVar2 + 8);
    piVar1[2] = param_1[4] + *(int *)(iVar2 + 0xc);
    *(undefined2 *)(piVar1 + 3) = *(undefined2 *)(param_1 + 8);
    *(undefined2 *)((int)piVar1 + 0xe) = *(undefined2 *)((int)param_1 + 0x22);
    *(undefined2 *)(piVar1 + 4) = *(undefined2 *)(param_1 + 9);
    *(undefined2 *)((int)piVar1 + 0x12) = *(undefined2 *)(param_1 + 0x1a6);
  }
  else if (*(int *)(iVar2 + 0x42) <= DAT_00021020) {
    if ((DAT_00021058 == 1) || (DAT_00021020 == *(int *)(iVar2 + 0x42))) {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
      *(int *)(iVar2 + 4) = *piVar1;
      *(int *)(iVar2 + 8) = piVar1[1];
      *(int *)(iVar2 + 0xc) = piVar1[2];
      *(undefined2 *)(param_1 + 0x1a7) = 0;
      *(ushort *)((int)param_1 + 0x69a) = *(ushort *)((int)piVar1 + 0x12) & 0xeeff;
      *(undefined2 *)(param_1 + 0x1a6) = *(undefined2 *)((int)piVar1 + 0x12);
      if ((*(byte *)(param_1 + 0x1a6) & 0x20) == 0) {
        *(undefined1 *)((int)param_1 + 0x68a) = 0x2d;
      }
      else {
        *(undefined1 *)((int)param_1 + 0x68a) = 0;
      }
      if (((*(ushort *)((int)piVar1 + 0x12) & 0x3c0) != 0) &&
         ((*(byte *)((int)piVar1 + 0x12) & 0x10) == 0)) {
        *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) & 0xfc3f;
        *(ushort *)((int)param_1 + 0x69a) = *(ushort *)((int)param_1 + 0x69a) & 0xfc3f;
        *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 0x40;
      }
    }
    else {
      param_1[2] = *piVar1 - *(int *)(iVar2 + 4);
      param_1[3] = piVar1[1] - *(int *)(iVar2 + 8);
      param_1[4] = piVar1[2] - *(int *)(iVar2 + 0xc);
      *(undefined2 *)(param_1 + 0x1a6) = *(undefined2 *)((int)piVar1 + 0x12);
      if (((*(ushort *)((int)param_1 + 0x69a) ^ *(ushort *)(param_1 + 0x1a6)) & 0x3c0) == 0) {
        *(undefined2 *)(param_1 + 0x1a7) = 1;
      }
      else {
        *(undefined2 *)(param_1 + 0x1a7) = 0;
      }
    }
    *(undefined2 *)(param_1 + 8) = *(undefined2 *)(piVar1 + 3);
    *(undefined2 *)((int)param_1 + 0x22) = *(undefined2 *)((int)piVar1 + 0xe);
    *(undefined2 *)(param_1 + 9) = *(undefined2 *)(piVar1 + 4);
    uVar3 = *(undefined2 *)(param_1 + 0x1a6);
    FUN_000124e0(param_1);
    if (((*(ushort *)((int)param_1 + 0x69a) ^ *(ushort *)(param_1 + 0x1a6)) & 0x2000) != 0) {
      if ((*(byte *)(param_1 + 0x1a6) & 0x20) == 0) {
        cVar5 = FUN_00003908((int)*(char *)((int)SUB_00020b28 + 0x68a));
        *(char *)((int)param_1 + 0x68a) = cVar5;
        if (cVar5 == '\0') {
          *(undefined1 *)((int)param_1 + 0x68a) = 1;
        }
      }
      else {
        cVar5 = FUN_00003908((int)*(char *)((int)SUB_00020b28 + 0x68a));
        *(char *)((int)param_1 + 0x68a) = -cVar5;
      }
    }
    *(undefined2 *)((int)param_1 + 0x69a) = uVar3;
  }
  return;
}



// FUN_0000fc7c @ 0xfc7c

void FUN_0000fc7c(int *param_1)

{
  int iVar1;
  int iVar2;
  
  iVar2 = *param_1;
  iVar1 = param_1[0x1af];
  FUN_000129b4(param_1,-((*(int *)(iVar1 + 4) - *(int *)(iVar2 + 4)) * 8 - *(int *)(iVar2 + 4)),
               *(int *)(iVar2 + 8) + 30000,
               *(int *)(iVar2 + 0xc) + (*(int *)(iVar1 + 0xc) - *(int *)(iVar2 + 0xc)) * -8);
  FUN_0000fd16(param_1);
  iVar2 = FUN_000048a6(*(undefined4 *)(iVar2 + 4),*(undefined4 *)(iVar2 + 8),
                       *(undefined4 *)(iVar2 + 0xc),*(undefined4 *)(iVar1 + 4),
                       *(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc));
  if (param_1[0x1b0] == 0) {
    param_1[0x1b0] = iVar2;
  }
  if ((param_1[0x1b0] < iVar2) && (0x9c4 < iVar2)) {
    *(char *)(param_1[0x1af] + 0x29) = *(char *)(param_1[0x1af] + 0x29) + -1;
    param_1[0x1af] = 0;
    param_1[0x1b0] = 0;
  }
  return;
}



// FUN_0000fd16 @ 0xfd16

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_0000fd16(int *param_1)

{
  int iVar1;
  uint *puVar2;
  byte bVar3;
  int *piVar4;
  ushort *puVar5;
  byte *pbVar6;
  bool bVar7;
  bool bVar8;
  ushort uVar14;
  int iVar9;
  undefined2 uVar15;
  uint uVar10;
  short sVar16;
  short sVar17;
  short sVar18;
  int iVar11;
  int iVar12;
  undefined1 uVar21;
  short sVar19;
  ushort uVar20;
  char cVar22;
  uint uVar13;
  int iVar23;
  int iVar24;
  short sVar25;
  int iVar26;
  int iVar27;
  ushort local_76;
  undefined4 local_74;
  int local_70;
  int local_6c;
  ushort local_5c;
  int local_5a;
  int local_56;
  ushort local_4c;
  ushort local_4a;
  undefined1 auStack_48 [2];
  short local_46;
  short local_42;
  short local_3e;
  undefined1 auStack_3c [18];
  ushort local_2a;
  short local_28;
  int local_26;
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  iVar1 = *param_1;
  bVar8 = false;
  DAT_00027074 = param_1[1];
  local_28 = *(short *)(param_1 + 8);
  local_2a = *(ushort *)((int)param_1 + 0x22);
  sVar18 = *(short *)(param_1 + 9);
  bVar7 = false;
  if ((*(char *)(param_1 + 0x193) != '\0') && (0 < *(int *)((int)param_1 + 0x2e))) {
    bVar7 = true;
  }
  if (bVar7) {
    local_56 = *(int *)((int)param_1 + 0x62e);
    iVar12 = *(int *)((int)param_1 + 0x632) + *(int *)((int)param_1 + 0x62e);
  }
  else {
    local_56 = *(int *)((int)param_1 + 0x62a);
    iVar12 = local_56;
  }
  local_56 = local_56 >> 2;
  iVar24 = iVar12 >> 2;
  if (*(char *)((int)param_1 + 0x68a) == '-') {
    sVar19 = *(short *)(DAT_00027074 + 0x42);
  }
  else {
    sVar19 = *(short *)(DAT_00027074 + 0x42);
    if (sVar19 < 0) {
      sVar19 = sVar19 + 1;
    }
    sVar19 = sVar19 >> 1;
  }
  iVar26 = *(int *)((int)param_1 + 0x2e) + (int)sVar19;
  iVar23 = param_1[0x19e];
  sVar19 = (short)*(char *)((int)param_1 + 0x692) +
           *(short *)((int)param_1 + 0x642) + (short)*(char *)((int)param_1 + 0x691);
  if ((*(byte *)(param_1 + 0x1a6) & 8) != 0) {
    if (*(int *)((int)param_1 + 0x2e) < *(int *)(iVar1 + 8) + -0x16) {
      param_1[3] = -0x16;
    }
    else {
      if ((*(char *)(param_1 + 0xb) == '\x10') &&
         (*(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 0x1000,
         param_1 == SUB_00020b28)) {
        _DAT_0002103a = 1;
      }
      param_1[3] = iVar26 - *(int *)(iVar1 + 8);
    }
    uVar14 = FUN_00013652();
    *(short *)((int)param_1 + 0x61e) = (uVar14 & 0x7f) + *(short *)((int)param_1 + 0x61e);
    *(ushort *)(param_1 + 9) = *(short *)((int)param_1 + 0x61e) + *(short *)(param_1 + 9) & 0x3fff;
    uVar14 = FUN_00013652();
    *(short *)((int)param_1 + 0x61a) = *(short *)((int)param_1 + 0x61a) - (uVar14 & 0x7f);
    uVar10 = (ushort)(*(short *)((int)param_1 + 0x61a) + *(short *)(param_1 + 8)) & 0xffff3fff;
    *(short *)(param_1 + 8) = (short)uVar10;
    *(undefined1 *)((int)param_1 + 0x677) = 0;
    return uVar10;
  }
  if ((*(byte *)((int)param_1 + 0x699) & 4) != 0) {
    uVar14 = FUN_00013672();
    sVar25 = *(short *)(param_1 + 0x19c) + (uVar14 & 0x7f) + -0x40;
    *(short *)(param_1 + 0x19c) = sVar25;
    iVar9 = FUN_00003908((int)sVar25);
    if (0x200 < iVar9) {
      if (*(short *)(param_1 + 0x19c) < 0) {
        uVar15 = 0xfe00;
      }
      else {
        uVar15 = 0x200;
      }
      *(undefined2 *)(param_1 + 0x19c) = uVar15;
    }
  }
  if ((*(byte *)((int)param_1 + 0x699) & 8) != 0) {
    uVar14 = FUN_00013672();
    sVar25 = *(short *)(param_1 + 0x19d) + (uVar14 & 0x7f) + -0x40;
    *(short *)(param_1 + 0x19d) = sVar25;
    iVar9 = FUN_00003908((int)sVar25);
    if (*(short *)(DAT_00027074 + 0x4c) < iVar9) {
      if (*(short *)(param_1 + 0x19d) < 0) {
        sVar25 = -*(short *)(DAT_00027074 + 0x4c);
      }
      else {
        sVar25 = *(short *)(DAT_00027074 + 0x4c);
      }
      *(short *)(param_1 + 0x19d) = sVar25;
    }
  }
  if ((*(byte *)(param_1 + 0x1a6) & 1) != 0) {
    *(undefined2 *)(param_1 + 0x19c) = 0;
    *(undefined2 *)(param_1 + 0x19d) = 0xff80;
  }
  sVar25 = (short)*(char *)((int)param_1 + 0x676);
  *(undefined1 *)((int)param_1 + 0x677) = 1;
  if (((((*(byte *)((int)param_1 + 0x699) & 0x40) == 0) || (*(short *)(param_1 + 0x1a8) != 0)) &&
      ((*(ushort *)(param_1 + 0x1a6) & 0x300) == 0)) &&
     ((*(char *)((int)param_1 + 0x691) != '\0' || (*(char *)((int)param_1 + 0x692) != '\0')))) {
    if (*(char *)((int)param_1 + 0x699) < '\0') {
      if (sVar25 < 0) {
        sVar25 = sVar25 + 1;
      }
      sVar25 = sVar25 >> 1;
    }
  }
  else {
    *(undefined1 *)((int)param_1 + 0x677) = 0;
    sVar25 = 0;
  }
  *(short *)(param_1 + 0x1a5) = sVar25 + *(short *)(param_1 + 0x1a5);
  if (*(int *)(DAT_00027074 + 0x1e) < (int)*(short *)(param_1 + 0x1a5)) {
    *(short *)(param_1 + 0x1a5) = *(short *)(param_1 + 0x1a5) - *(short *)(DAT_00027074 + 0x20);
    uVar10 = FUN_00013672();
    if (((uVar10 & 1) == 0) || (*(char *)((int)param_1 + 0x691) == '\0')) {
      if (*(char *)((int)param_1 + 0x692) != '\0') {
        *(char *)((int)param_1 + 0x692) = *(char *)((int)param_1 + 0x692) + -1;
      }
    }
    else {
      *(char *)((int)param_1 + 0x691) = *(char *)((int)param_1 + 0x691) + -1;
    }
  }
  sVar16 = FUN_0000f89a(*(undefined4 *)(iVar1 + 8));
  sVar17 = FUN_0000f8d8(*(int *)(iVar1 + 8) - *(int *)((int)param_1 + 0x2e),(int)sVar16);
  FUN_0000f9e0((int)sVar16,&local_26);
  if (sVar25 == 0) {
    local_5a = 0;
  }
  else if (*(short *)(DAT_00027074 + 0x40) < iVar24) {
    local_5a = ((local_26 * sVar25) / 9) / iVar24;
  }
  else {
    local_5a = ((int)sVar25 * (int)*(short *)(DAT_00027074 + 0x3e)) / 9;
  }
  if ((param_1 == SUB_00020b28) || ((param_1 == DAT_00021070 && (DAT_00023d88 == 7)))) {
    local_70 = (iVar24 << 0x11) / 0x1c20;
    local_6c = (int)(short)(0x5e50 / (int)(short)(sVar16 + 0xa25 >> 5)) * sVar25 * 0x666 >> 5;
    iVar27 = local_6c - param_1[0x19f];
    iVar9 = iVar27;
    if (iVar27 < 0) {
      iVar9 = iVar27 + 7;
    }
    if (iVar9 >> 3 == 0) {
      param_1[0x19f] = iVar27 + param_1[0x19f];
    }
    else {
      if (iVar27 < 0) {
        iVar27 = iVar27 + 7;
      }
      param_1[0x19f] = (iVar27 >> 3) + param_1[0x19f];
    }
    if ((*(char *)((int)param_1 + 0x677) == '\0') || ((*(ushort *)(param_1 + 0x1a6) & 0x4300) != 0))
    {
      sVar25 = 0;
    }
    else {
      sVar25 = (short)(local_26 >> 9);
    }
    if (*(char *)((int)param_1 + 0x699) < '\0') {
      if (sVar25 < 0) {
        sVar25 = sVar25 + 1;
      }
      sVar25 = sVar25 >> 1;
    }
    iVar27 = (int)sVar25 - (int)*(short *)(param_1 + 0x1a0);
    iVar9 = iVar27;
    if (iVar27 < 0) {
      iVar9 = iVar27 + 7;
    }
    if (iVar9 >> 3 == 0) {
      *(short *)(param_1 + 0x1a0) = (short)iVar27 + *(short *)(param_1 + 0x1a0);
    }
    else {
      if (iVar27 < 0) {
        iVar27 = iVar27 + 7;
      }
      *(short *)(param_1 + 0x1a0) = (short)(iVar27 >> 3) + *(short *)(param_1 + 0x1a0);
    }
    iVar9 = local_70 - local_6c;
    if (iVar9 < 0) {
      iVar9 = iVar9 + 1;
    }
    iVar9 = -(iVar23 - (local_6c + (iVar9 >> 1)));
    iVar23 = iVar9;
    if (iVar9 < 0) {
      iVar23 = iVar9 + 7;
    }
    if (iVar23 >> 3 == 0) {
      param_1[0x19e] = iVar9 + param_1[0x19e];
    }
    else {
      if (iVar9 < 0) {
        iVar9 = iVar9 + 7;
      }
      param_1[0x19e] = (iVar9 >> 3) + param_1[0x19e];
    }
    if (((*(byte *)(param_1 + 0x1a6) & 0x40) != 0) && (param_1[0x19f] == 0)) {
      *(undefined1 *)((int)param_1 + 0x677) = 0;
    }
  }
  if (iVar24 < 0xca8) {
    if (*(short *)(DAT_00027074 + 0x38) < iVar24) {
      iVar23 = (int)*(short *)(param_1 + 0x19c) * (*(short *)(DAT_00027074 + 0x36) + -0x200);
      if (iVar23 < 0) {
        iVar23 = iVar23 + 0x1ff;
      }
      sVar25 = *(short *)(param_1 + 0x19c) +
               (short)(((iVar23 >> 9) * (0xca8 - iVar24)) /
                      (0xca8 - *(short *)(DAT_00027074 + 0x38)));
    }
    else {
      iVar23 = (int)*(short *)(param_1 + 0x19c) * (int)*(short *)(DAT_00027074 + 0x36);
      if (iVar23 < 0) {
        iVar23 = iVar23 + 0x1ff;
      }
      sVar25 = (short)(iVar23 >> 9);
    }
    if (iVar24 < 0) {
      local_74 = 0;
    }
    else {
      local_74 = iVar24 * 0x2a;
      if (local_74 < 0) {
        local_74 = local_74 + 0xfff;
      }
      local_74 = (local_74 >> 0xc) * 0x143a;
      if (local_74 < 0) {
        local_74 = local_74 + 0xfff;
      }
      local_74 = local_74 >> 0xc;
    }
    iVar23 = iVar24 * *(short *)((int)param_1 + 0x672);
    if (iVar23 < 0) {
      iVar23 = iVar23 + 0xfff;
    }
    iVar23 = (iVar23 >> 0xc) * 0x143a;
    if (iVar23 < 0) {
      iVar23 = iVar23 + 0xfff;
    }
    local_76 = (ushort)(iVar23 >> 0xc) & 0x3fff;
    iVar23 = iVar24 * *(short *)(param_1 + 0x19d);
    if (iVar23 < 0) {
      iVar23 = iVar23 + 0xfff;
    }
    iVar23 = (iVar23 >> 0xc) * 0x143a;
    if (iVar23 < 0) {
      iVar23 = iVar23 + 0xfff;
    }
    uVar14 = (ushort)(iVar23 >> 0xc) & 0x3fff;
  }
  else {
    sVar25 = *(short *)(param_1 + 0x19c);
    local_74 = 0x2a;
    local_76 = *(ushort *)((int)param_1 + 0x672);
    uVar14 = *(ushort *)(param_1 + 0x19d);
  }
  local_74 = local_74 *
             ((int)sVar25 -
             ((int)*(short *)((int)param_1 + 0x626) - (int)*(short *)(DAT_00027074 + 0x46)));
  if (local_74 < 0) {
    local_74 = local_74 + 0xff;
  }
  *(short *)((int)param_1 + 0x626) = (short)((uint)local_74 >> 8) + *(short *)((int)param_1 + 0x626)
  ;
  if ((*(char *)(param_1 + 0x193) != '\0') && (*(char *)((int)param_1 + 0x68a) == '-')) {
    if (*(short *)(DAT_00027074 + 0x4e) == 0) {
      if (*(short *)(DAT_00027074 + 0x44) < *(short *)((int)param_1 + 0x626)) {
        *(undefined2 *)((int)param_1 + 0x626) = *(undefined2 *)(DAT_00027074 + 0x44);
      }
      if (iVar24 < *(short *)(DAT_00027074 + 0x4a)) {
        iVar23 = (int)*(short *)(DAT_00027074 + 0x48);
        if (iVar24 <= iVar23) goto LAB_00010356;
        *(short *)((int)param_1 + 0x626) =
             *(short *)(DAT_00027074 + 0x44) +
             (short)((((int)*(short *)((int)param_1 + 0x626) - (int)*(short *)(DAT_00027074 + 0x44))
                     * (iVar24 - iVar23)) / (*(short *)(DAT_00027074 + 0x4a) - iVar23));
      }
    }
    else {
      if (iVar24 < *(short *)(DAT_00027074 + 0x4a)) {
        iVar23 = (int)*(short *)(DAT_00027074 + 0x48);
        if (iVar23 < iVar24) {
          *(short *)((int)param_1 + 0x626) =
               *(short *)(DAT_00027074 + 0x44) +
               (short)((((int)*(short *)((int)param_1 + 0x626) -
                        (int)*(short *)(DAT_00027074 + 0x44)) * (iVar24 - iVar23)) /
                      (*(short *)(DAT_00027074 + 0x4a) - iVar23));
        }
        else {
          *(undefined2 *)((int)param_1 + 0x626) = *(undefined2 *)(DAT_00027074 + 0x44);
        }
      }
      if (*(short *)((int)param_1 + 0x626) < *(short *)(DAT_00027074 + 0x44)) {
LAB_00010356:
        *(undefined2 *)((int)param_1 + 0x626) = *(undefined2 *)(DAT_00027074 + 0x44);
      }
    }
    iVar23 = local_6c + local_70 * 2;
    if (iVar23 < 0) {
      iVar23 = iVar23 + 1;
    }
    if (iVar23 >> 1 < 0x10000) {
      iVar23 = (iVar23 >> 1) * (int)*(short *)(param_1 + 0x19d);
      if (iVar23 < 0) {
        iVar23 = iVar23 + 0xffff;
      }
      local_76 = (ushort)((uint)iVar23 >> 0x10);
    }
    else {
      local_76 = *(ushort *)(param_1 + 0x19d);
    }
    local_76 = (short)local_76 >> 2;
    if (param_1 == SUB_00020b28) {
      DAT_00021042 = 0;
    }
  }
  local_5c = uVar14 + sVar18 & 0x3fff;
  local_76 = local_76 & 0x3fff;
  if ((*(char *)(param_1 + 0x193) != '\0') && (*(char *)((int)param_1 + 0x68a) == '-')) {
    local_2a = local_76 + local_2a & 0x3fff;
  }
  sVar18 = FUN_0000f914((int)*(short *)((int)param_1 + 0x626),(int)*(char *)(param_1 + 0x1a3),
                        (int)param_1 + 0x689);
  iVar23 = (int)sVar18;
  if (*(char *)((int)param_1 + 0x689) != '\0') {
    iVar23 = iVar23 - *(short *)(param_1 + 0x192);
    if (iVar23 < 0) {
      iVar23 = iVar23 + 7;
    }
    iVar23 = (iVar23 >> 3) + (int)*(short *)(param_1 + 0x192);
    *(char *)((int)param_1 + 0x689) = *(char *)((int)param_1 + 0x689) + -1;
  }
  *(short *)(param_1 + 0x192) = (short)iVar23;
  if (iVar24 < 1) {
    iVar23 = 0;
    iVar9 = 0;
  }
  else {
    iVar9 = iVar24 * (((int)*(short *)(DAT_00027074 + 0x24) * (iVar24 * iVar23 >> 8)) / (int)sVar17)
    ;
    if (iVar9 < 0) {
      iVar9 = iVar9 + 7;
    }
    iVar9 = ((iVar9 >> 3) / 0xa0) / (int)sVar19;
    iVar23 = iVar24 * (((int)*(short *)(DAT_00027074 + 0x24) *
                       (iVar24 * ((int)*(short *)(DAT_00027074 + 0x26) * (iVar23 * iVar23 >> 4) >>
                                 0x14) >> 8)) / (int)sVar17);
    if (iVar23 < 0) {
      iVar23 = iVar23 + 7;
    }
    iVar23 = (iVar23 >> 3) / 0xa0;
  }
  sVar18 = *(short *)(DAT_00027074 + 0x22);
  local_74 = 0;
  iVar27 = 0;
  if (*(char *)((int)param_1 + 0x68a) != '\0') {
    iVar27 = FUN_00003908((int)*(char *)((int)param_1 + 0x68a));
    iVar27 = (iVar27 * *(short *)(DAT_00027074 + 0x22)) / 0xb;
  }
  if (*(char *)(param_1 + 0x1a3) != '\0') {
    iVar11 = FUN_00003908((int)*(char *)(param_1 + 0x1a3));
    local_74 = (iVar11 * *(short *)(DAT_00027074 + 0x22)) / 0xf;
  }
  iVar23 = (int)*(short *)(param_1 + 0x191) +
           (iVar24 * ((iVar24 * (local_74 + iVar27 + (int)sVar18) >> 8) / (int)sVar16) * 2) / 0xa0 +
           iVar23;
  iVar24 = iVar23 / (int)sVar19;
  *(short *)(param_1 + 0x191) = (short)(iVar23 % (int)sVar19);
  if (-1 < iVar12) {
    iVar24 = -(iVar23 / (int)sVar19);
  }
  iVar24 = iVar24 + iVar12;
  if (*(char *)(param_1 + 0x193) != '\0') {
    iVar12 = FUN_00003908((int)*(char *)((int)param_1 + 0x68a));
    if (iVar12 == 0x2d) {
      if ((local_56 < *(short *)(DAT_00027074 + 0x48)) || (*(char *)(param_1 + 0xb) == '@')) {
        sVar18 = *(short *)(DAT_00027074 + 0x30);
      }
      else {
        sVar18 = *(short *)(DAT_00027074 + 0x32);
      }
      iVar12 = (int)sVar18;
      if ((*(char *)((int)param_1 + 0x64f) != '\0') || (*(char *)(param_1 + 0xb) == 'P')) {
        iVar12 = *(short *)(DAT_00027074 + 0x34) + iVar12;
      }
    }
    else if ((*(byte *)(param_1 + 0x1a6) & 0x10) == 0) {
      iVar12 = 0;
    }
    else {
      iVar12 = *(short *)(DAT_00027074 + 0x34) * 3;
      if (iVar12 < 0) {
        iVar12 = iVar12 + 1;
      }
      iVar12 = iVar12 >> 1;
      if (param_1 == SUB_00020b28) {
        _DAT_0002103a = 1;
      }
    }
    iVar23 = (int)*(short *)((int)param_1 + 0x646) + iVar12 * (0x5b - iVar9);
    iVar12 = iVar23;
    if (iVar23 < 0) {
      iVar12 = iVar23 + 0xfff;
    }
    *(ushort *)((int)param_1 + 0x646) = -(((ushort)iVar12 & 0xf000) - (short)iVar23);
    if (iVar23 < 0) {
      iVar23 = iVar23 + 0xfff;
    }
    iVar24 = iVar24 - (iVar23 >> 0xc);
  }
  if (*(char *)((int)param_1 + 0x64e) == '\x01') {
    if (local_56 < 0) {
      iVar24 = iVar24 + 0xb6;
    }
    else {
      iVar24 = iVar24 + -0xb6;
    }
  }
  iVar24 = (local_5a << 2) / (int)sVar19 + iVar24;
  if (bVar7) {
    if (iVar24 - *(int *)((int)param_1 + 0x632) < 0) {
      local_56 = -999;
      if (local_6c == 0) {
        local_2a = local_2a - local_76 & 0x3fff;
      }
      *(undefined1 *)((int)param_1 + 0x64e) = 2;
      iVar12 = *(int *)((int)param_1 + 0x62e);
joined_r0x000106d0:
      if (((iVar12 != 0) && (param_1 == SUB_00020b28)) && (DAT_00021050 == 1)) {
        DAT_00021050 = 2;
      }
    }
  }
  else if (iVar24 < 1) {
    iVar24 = 0;
    *(undefined1 *)((int)param_1 + 0x64e) = 2;
    iVar12 = *(int *)((int)param_1 + 0x62a);
    goto joined_r0x000106d0;
  }
  FUN_00002ee4(local_76,(int)local_28,(int)(short)local_2a,local_5c,auStack_22);
  sVar19 = (short)iVar9;
  local_e = 0;
  sVar18 = (short)iVar24;
  local_a = sVar19;
  local_6 = sVar18;
  FUN_00003220(1,auStack_10,auStack_10,auStack_22);
  local_a = *(short *)((int)param_1 + 0x63e) + local_a;
  *(undefined2 *)((int)param_1 + 0x63e) = 0xffa5;
  param_1[2] = (int)(local_e >> 6);
  param_1[3] = (int)(local_a >> 6);
  param_1[4] = (int)(local_6 >> 6);
  if (*(char *)(param_1 + 0x193) == '\0') {
    if ((param_1[3] + *(int *)(iVar1 + 8) < iVar26) && (iVar26 <= *(int *)(iVar1 + 8))) {
      *(undefined1 *)(param_1 + 0x193) = 1;
      param_1[3] = iVar26 - *(int *)(iVar1 + 8);
      uVar10 = *(short *)((int)param_1 + 0xe) * 0x10;
      if ((int)uVar10 < -0x168) {
        *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 0x400;
        if (*(int *)((int)param_1 + 0x2e) == 0) {
          uVar10 = FUN_00015d92(iVar1);
          *(char *)(param_1 + 0xb) = (char)uVar10;
          return uVar10;
        }
        return uVar10;
      }
      if (*(short *)((int)param_1 + 0xe) * 0x10 < -0xd8) {
LAB_000108dc:
        bVar8 = true;
      }
      else {
        sVar25 = FUN_0000f87c(local_5c);
        iVar12 = FUN_00003908((int)sVar25);
        if ((0x200 < iVar12) || (sVar25 = FUN_0000f87c((int)local_28), sVar25 < -0x100))
        goto LAB_000108dc;
        if (param_1 == SUB_00020b28) {
          _DAT_00021040 = 1;
        }
      }
      *(undefined2 *)((int)param_1 + 0x646) = 0;
      if (param_1 == SUB_00020b28) {
        DAT_00021050 = 1;
      }
      if (0 < *(int *)((int)param_1 + 0x2e)) {
        local_e = 0;
        local_6 = sVar18 - *(short *)(param_1 + 0x18d);
        if (local_6 < 0) {
          local_6 = 0;
        }
        local_a = sVar19;
        FUN_00003220(1,auStack_10,auStack_10,auStack_22);
        param_1[2] = (int)(local_e >> 6);
        param_1[4] = (int)(local_6 >> 6);
        param_1[2] = param_1[2] + (int)*(short *)((int)param_1 + 0x636);
        param_1[4] = param_1[4] + (int)*(short *)(param_1 + 0x18e);
        bVar7 = true;
      }
    }
  }
  else {
    if (iVar26 <= param_1[3] + *(int *)(iVar1 + 8)) {
      *(undefined1 *)(param_1 + 0x193) = 0;
      bVar7 = false;
      *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) & 0xefff;
      if (param_1 == SUB_00020b28) {
        DAT_00021050 = 0;
      }
      if (iVar24 < 0) {
        local_6 = 0;
        local_a = 0;
        local_e = 0;
      }
    }
    if (bVar7) {
      if (local_56 == -999) {
        local_6 = 0;
        local_e = 0;
        param_1[4] = 0;
        param_1[2] = 0;
      }
      else {
        local_e = 0;
        local_6 = sVar18 - *(short *)(param_1 + 0x18d);
        if (local_6 < 0) {
          local_6 = 0;
        }
        local_a = sVar19;
        FUN_00003220(1,auStack_10,auStack_10,auStack_22);
        param_1[2] = (int)(local_e >> 6);
        param_1[4] = (int)(local_6 >> 6);
      }
      param_1[2] = param_1[2] + (int)*(short *)((int)param_1 + 0x636);
      param_1[4] = param_1[4] + (int)*(short *)(param_1 + 0x18e);
    }
  }
  if (*(char *)(param_1 + 0x193) == '\0') {
    iVar12 = (int)local_6 * (int)local_6 + (int)local_a * (int)local_a + (int)local_e * (int)local_e
    ;
    sVar18 = FUN_0000f7e0(iVar12);
    iVar24 = (int)sVar18;
    FUN_00002574((int)local_e,(int)local_a,(int)local_6,iVar24,iVar12,&local_28,&local_2a);
    if (*(char *)((int)param_1 + 0x64e) == '\x02') {
      *(undefined1 *)((int)param_1 + 0x64e) = 0;
    }
    goto LAB_00010c5c;
  }
  if (*(int *)((int)param_1 + 0x2e) == 0) {
    uVar21 = FUN_00015d92(iVar1);
    *(undefined1 *)(param_1 + 0xb) = uVar21;
  }
  sVar18 = FUN_0000f7e0((int)local_e * (int)local_e + (int)local_6 * (int)local_6);
  iVar24 = (int)sVar18;
  param_1[3] = iVar26 - *(int *)(iVar1 + 8);
  cVar22 = *(char *)(param_1 + 0xb);
  iVar12 = iVar24 >> 2;
  if (cVar22 == '@') {
    iVar23 = FUN_00003908((int)*(char *)((int)param_1 + 0x68a));
    if (iVar23 != 0x2d) {
      bVar8 = true;
    }
    uVar14 = FUN_00013652();
    uVar14 = uVar14 & 0x7f;
    uVar20 = FUN_00013652();
    local_5c = (uVar20 & 0x7f) - 0x40;
    if (iVar12 < 0x438) {
      local_5c = (ushort)((iVar12 * (short)local_5c) / 0x438);
      uVar14 = (ushort)((iVar12 * (short)uVar14) / 0x438);
    }
    local_5c = local_5c & 0x3fff;
    *(short *)((int)param_1 + 0x63e) = uVar14 + *(short *)((int)param_1 + 0x63e);
    if (param_1 == SUB_00020b28) {
      if (_DAT_00021040 == 0) {
        if (iVar24 < 0x10e1) {
          if (iVar24 < 0x2d1) goto LAB_00010be0;
          uVar14 = FUN_00013672();
          uVar14 = uVar14 & 0x1f;
        }
        else {
          uVar14 = FUN_00013672();
          uVar14 = uVar14 & 0xf;
        }
        if (uVar14 == 0) {
          _DAT_0002103e = 1;
        }
      }
      else {
        _DAT_0002103e = 1;
LAB_00010bda:
        _DAT_00021040 = 0;
      }
    }
  }
  else if (cVar22 < 'A') {
    if (cVar22 == ' ') {
      if ((*(byte *)(param_1 + 0x1a6) & 8) == 0) {
        bVar8 = true;
      }
    }
    else if (cVar22 < '!') {
      if (cVar22 == '\x10') {
        *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 0x1000;
        uVar10 = 0x10;
        if ((*(char *)((int)param_1 + 0x68a) != '\0') ||
           (uVar10 = FUN_0000f87c((int)*(short *)((int)param_1 + 0x626)), (short)uVar10 < -0x80)) {
          *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 0x400;
          return uVar10;
        }
        local_5c = FUN_0000f87c(local_5c);
        if ((short)local_5c < 0) {
          local_5c = local_5c + 1;
        }
        uVar14 = FUN_00013672();
        local_5c = (uVar14 & 0x3f) + ((short)local_5c >> 1) + -0x20;
        if (iVar12 < 0x438) {
          local_5c = (ushort)((iVar12 * (short)local_5c) / 0x438);
        }
        local_5c = local_5c & 0x3fff;
        if (param_1 == SUB_00020b28) goto LAB_00010bda;
      }
    }
    else if (cVar22 == '0') goto LAB_00010b14;
  }
  else if (cVar22 == '`') {
LAB_00010b14:
    iVar12 = FUN_00003908((int)*(char *)((int)param_1 + 0x68a));
    if (iVar12 != 0x2d) {
      bVar8 = true;
    }
LAB_00010b30:
    local_5c = 0;
  }
  else if (cVar22 < 'a') {
    if (cVar22 == 'P') {
      iVar12 = FUN_00003908((int)*(char *)((int)param_1 + 0x68a));
      if (iVar12 != 0x2d) {
        bVar8 = true;
      }
      if ((param_1 == SUB_00020b28) && (_DAT_00021040 != 0)) {
        _DAT_00021040 = 0;
      }
      goto LAB_00010b30;
    }
  }
  else if (cVar22 == 'p') goto LAB_00010b14;
LAB_00010be0:
  if (0x2000 < local_28) {
    local_28 = 0;
  }
  if (((!bVar8) && (*(char *)((int)param_1 + 0x64d) != '\0')) &&
     (*(char *)((int)param_1 + 0x64e) == '\0')) {
    *(undefined1 *)((int)param_1 + 0x64e) = 1;
  }
LAB_00010c5c:
  *(undefined1 *)((int)param_1 + 0x64d) = 0;
  sVar18 = FUN_0000f87c((int)(short)local_2a - (int)*(short *)((int)param_1 + 0x22));
  *(short *)(param_1 + 8) = local_28;
  *(ushort *)((int)param_1 + 0x22) = local_2a;
  sVar19 = FUN_000023c4((int)local_28);
  *(ushort *)(param_1 + 9) = local_5c + (short)((int)sVar18 * (int)sVar19 >> 0xc) & 0x3fff;
  if (bVar7) {
    if (iVar24 < 0) {
      *(undefined4 *)((int)param_1 + 0x62e) = 0;
    }
    else {
      *(int *)((int)param_1 + 0x62e) = iVar24;
    }
    iVar24 = *(int *)((int)param_1 + 0x632) + iVar24;
    *(int *)((int)param_1 + 0x62a) = iVar24;
  }
  else {
    *(int *)((int)param_1 + 0x62a) = iVar24;
  }
  if (param_1 == SUB_00020b28) {
    sVar18 = FUN_000023c4((int)*(short *)(param_1 + 8));
    iVar12 = (iVar24 * (((int)sVar18 << 0xd) >> 0xc)) / 0x1ce;
    if (0x1d80 < iVar12) {
      iVar12 = 0x1d80;
    }
    if (iVar12 < -0x1d80) {
      iVar12 = -0x1d80;
    }
    *(short *)(&DAT_00021474 + DAT_0002146c * 2) = (short)iVar12;
    local_74 = DAT_0002146c + 1;
    if (local_74 == 0x78) {
      local_74 = 0;
    }
    DAT_00021468 = (DAT_00021468 + (short)iVar12) - (int)*(short *)(&DAT_00021474 + local_74 * 2);
    DAT_0002146c = local_74._2_2_;
    FUN_0001f63a(auStack_22,&DAT_0002c59c,0x12);
  }
  if ((('\0' < *(char *)(param_1 + 0x1a3)) && (*(char *)(param_1 + 0x1a3) < '\x0f')) ||
     (*(char *)(param_1 + 0x1a3) < '\0')) {
    *(char *)(param_1 + 0x1a3) = *(char *)(param_1 + 0x1a3) + '\x01';
  }
  if (((*(char *)((int)param_1 + 0x68a) < '\x01') || (',' < *(char *)((int)param_1 + 0x68a))) ||
     ((*(byte *)((int)param_1 + 0x699) & 0x20) != 0)) {
    if (*(char *)((int)param_1 + 0x68a) < '\0') {
      *(char *)((int)param_1 + 0x68a) = *(char *)((int)param_1 + 0x68a) + '\x01';
      *(byte *)((int)param_1 + 0x68b) = *(byte *)((int)param_1 + 0x68b) | 2;
      *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 0x2000;
    }
  }
  else {
    *(char *)((int)param_1 + 0x68a) = *(char *)((int)param_1 + 0x68a) + '\x01';
    *(byte *)((int)param_1 + 0x68b) = *(byte *)((int)param_1 + 0x68b) | 2;
    *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) & 0xdfff;
  }
  if ((*(char *)(param_1 + 0x1a1) != '\0') && (*(char *)((int)param_1 + 0x68d) != '\0')) {
    if (*(char *)(param_1 + 0x1a1) == '\x01') {
      if (*(char *)(param_1 + 0x1a4) == '\0') {
        iVar12 = FUN_000149fa(iVar1,iVar24,0,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                              *(undefined4 *)(iVar1 + 0xc),(int)*(short *)(param_1 + 8),
                              (int)*(short *)((int)param_1 + 0x22),(int)*(short *)(param_1 + 9));
      }
      else {
        iVar12 = FUN_00014b1e(iVar1,iVar24,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                              *(undefined4 *)(iVar1 + 0xc),(int)*(short *)(param_1 + 8),
                              (int)*(short *)((int)param_1 + 0x22),(int)*(short *)(param_1 + 9));
      }
      if ((iVar12 != 0) && (param_1 == SUB_00020b28)) {
        DAT_00021068 = iVar12;
        *(undefined2 *)(iVar12 + 0x626) = 1;
        _DAT_00021030 = 1;
      }
      *(char *)((int)param_1 + 0x68d) = *(char *)((int)param_1 + 0x68d) + -1;
      *(short *)((int)param_1 + 0x642) =
           *(short *)((int)param_1 + 0x642) - *(short *)(*(int *)(iVar12 + 4) + 0x1a);
    }
    piVar4 = param_1 + 0x1a1;
    cVar22 = *(char *)piVar4;
    *(char *)piVar4 = *(char *)piVar4 + '\x01';
    if (cVar22 == '\x04') {
      *(undefined1 *)piVar4 = 0;
    }
  }
  uVar10 = *(ushort *)((int)param_1 + 0x682) & 0xffff0003;
  if (((*(ushort *)((int)param_1 + 0x682) & 3) != 0) && (*(char *)((int)param_1 + 0x68e) != '\0')) {
    uVar20 = *(short *)((int)param_1 + 0x682) >> 2;
    uVar14 = uVar20 & 7;
    if ((uVar20 & 7) == 0) {
      *(ushort *)((int)param_1 + 0x682) = *(ushort *)((int)param_1 + 0x682) & 0xff1f;
      uVar10 = FUN_00013672();
      *(ushort *)((int)param_1 + 0x682) =
           (short)((uVar10 & 0xffff) % 6) << 5 | *(ushort *)((int)param_1 + 0x682);
      do {
        uVar10 = FUN_00013672();
        uVar10 = (uVar10 & 0xffff) % 6;
      } while (((ushort)(*(short *)((int)param_1 + 0x682) >> 5) & 7) == uVar10);
      *(ushort *)((int)param_1 + 0x682) = *(ushort *)((int)param_1 + 0x682) & 0xf8ff;
      *(ushort *)((int)param_1 + 0x682) = (short)uVar10 << 8 | *(ushort *)((int)param_1 + 0x682);
    }
    if (uVar14 == (*(short *)((int)param_1 + 0x682) >> 5 & 7U)) {
      iVar12 = 1;
    }
    else if (uVar14 == (*(short *)((int)param_1 + 0x682) >> 8 & 7U)) {
      iVar12 = 2;
    }
    else {
      iVar12 = 0;
    }
    if (iVar12 != 0) {
      FUN_00002f64((int)*(short *)((int)param_1 + 0x626) - (int)*(short *)(param_1[1] + 0x46) &
                   0x3fff,(int)*(short *)(param_1 + 8),(int)*(short *)((int)param_1 + 0x22),
                   (int)*(short *)(param_1 + 9),auStack_3c);
      local_46 = 0;
      local_42 = 0;
      local_3e = 0x1000;
      FUN_00003220(1,auStack_48,auStack_48,auStack_3c);
      FUN_00002574((int)local_46,(int)local_42,(int)local_3e,0x1000,0x1000000,&local_4a,&local_4c);
      local_42 = -0x28;
      uVar20 = FUN_00013672();
      local_3e = (uVar20 & 0x3f) + (short)(iVar24 >> 6);
      if (iVar12 == 1) {
        cVar22 = *(char *)((int)param_1 + 0x68e);
        if (cVar22 < '\0') {
          cVar22 = cVar22 + '\x01';
        }
        local_46 = -((cVar22 >> 1) * 0xf + 100);
      }
      else {
        cVar22 = *(char *)((int)param_1 + 0x68e);
        if (cVar22 < '\0') {
          cVar22 = cVar22 + '\x01';
        }
        local_46 = (cVar22 >> 1) * 0xf + 100;
      }
      FUN_00003220(1,auStack_48,auStack_48,auStack_3c);
      if (*(char *)((int)param_1 + 0x68e) != '\0') {
        uVar20 = FUN_00013652();
        local_4a = local_4a + (uVar20 & 0x3f) & 0x3fff;
        uVar20 = FUN_00013652();
        local_4c = (local_4c + (uVar20 & 0x3f)) - 0x20 & 0x3fff;
        iVar12 = FUN_00014a78(iVar1,iVar24,*(int *)(iVar1 + 4) + (int)local_46,
                              *(int *)(iVar1 + 8) + (int)local_42,
                              *(int *)(iVar1 + 0xc) + (int)local_3e,(int)(short)local_4a,local_4c,
                              (int)*(short *)(param_1 + 9));
        if ((iVar12 != 0) && (param_1 == SUB_00020b28)) {
          DAT_00021068 = iVar12;
          *(undefined2 *)(iVar12 + 0x626) = 1;
          _DAT_0002102e = 1;
        }
        *(char *)((int)param_1 + 0x68e) = *(char *)((int)param_1 + 0x68e) + -1;
        *(short *)((int)param_1 + 0x642) =
             *(short *)((int)param_1 + 0x642) - *(short *)(*(int *)(iVar12 + 4) + 0x1a);
      }
    }
    *(ushort *)((int)param_1 + 0x682) = *(ushort *)((int)param_1 + 0x682) & 0xffe3;
    uVar20 = (uVar14 + 1 & 7) << 2;
    uVar10 = (uint)uVar20;
    *(ushort *)((int)param_1 + 0x682) = uVar20 | *(ushort *)((int)param_1 + 0x682);
    if (uVar14 == 5) {
      puVar5 = (ushort *)((int)param_1 + 0x682);
      *puVar5 = *puVar5 - 1;
      *puVar5 = *(ushort *)((int)param_1 + 0x682) & 0xffe3;
    }
  }
  if (*(char *)((int)param_1 + 0x687) != '\0') {
    if (((*(char *)((int)param_1 + 0x687) == '\x01') && (1 < *(short *)((int)param_1 + 0x696))) &&
       ((*(byte *)((int)param_1 + 0x699) & 0x10) == 0)) {
      iVar12 = FUN_00019d16(iVar24,1,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                            *(undefined4 *)(iVar1 + 0xc),(int)*(short *)(param_1 + 8),
                            (int)*(short *)((int)param_1 + 0x22),(int)*(short *)(param_1 + 9),
                            (int)*(short *)((int)param_1 + 0x626) -
                            (int)*(short *)(param_1[1] + 0x46) & 0x3fff);
      if ((iVar12 != 0) && (param_1 == SUB_00020b28)) {
        *(undefined2 *)(iVar12 + 0x36) = 1;
      }
      *(short *)((int)param_1 + 0x696) = *(short *)((int)param_1 + 0x696) + -2;
    }
    pbVar6 = (byte *)((int)param_1 + 0x687);
    bVar3 = *pbVar6;
    uVar10 = (uint)bVar3;
    *pbVar6 = *pbVar6 + 1;
    if (bVar3 == 2) {
      *pbVar6 = 0;
    }
  }
  if ((*(char *)(param_1 + 0x1a2) == '\x01') && ((*(byte *)((int)param_1 + 0x699) & 1) == 0)) {
    uVar10 = FUN_0001d5b0(*(undefined4 *)((int)param_1 + 0x62a),*(undefined4 *)(iVar1 + 4),
                          *(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc),
                          (int)*(short *)(param_1 + 8),(int)*(short *)((int)param_1 + 0x22),
                          (int)*(short *)(param_1 + 9));
    *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 1;
    *(undefined1 *)(param_1 + 0x1a2) = 2;
    piVar4 = SUB_00020b28;
    if (param_1 == SUB_00020b28) {
      _DAT_0002105c = 1;
      _DAT_0002105a = 0;
      DAT_00023d88 = 9;
      if (((*(ushort *)(param_1 + 0x1a6) & 0x3fe) == 0) &&
         ((*(char *)((int)param_1 + 0x691) != '\0' || (*(char *)((int)param_1 + 0x692) != '\0')))) {
        _DAT_00021062 = 1;
      }
      DAT_00021064 = uVar10;
      if ((*(byte *)(*SUB_00020b28 + 0x17) & 0x40) != 0) {
        puVar2 = (uint *)(*SUB_00020b28 + 0x14);
        *puVar2 = *puVar2 & 0xffffffbf;
        FUN_00016136(*piVar4);
      }
      uVar10 = FUN_00007f32();
    }
  }
  if ((*(byte *)((int)param_1 + 0x699) & 2) != 0) {
    uVar13 = FUN_00013652();
    uVar10 = uVar13 & 0xffff003f;
    if ((uVar13 & 0x3f) == 0) {
      uVar10 = FUN_00013652();
      if (((uVar10 & 1) == 0) || (*(char *)((int)param_1 + 0x691) == '\0')) {
        if (*(char *)((int)param_1 + 0x692) != '\0') {
          *(char *)((int)param_1 + 0x692) = *(char *)((int)param_1 + 0x692) + -1;
        }
      }
      else {
        *(char *)((int)param_1 + 0x691) = *(char *)((int)param_1 + 0x691) + -1;
      }
    }
  }
  if (bVar8) {
    if ((*(byte *)(param_1 + 0x1a6) & 1) == 0) {
      *(undefined2 *)(param_1 + 0x1a7) = 0;
    }
    else if (0x16 < *(short *)(param_1 + 0x1a7)) {
      *(undefined2 *)(param_1 + 0x1a7) = 0x16;
    }
    *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) & 0x8000;
    *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) | 0x900;
    *(undefined4 *)((int)param_1 + 0x32) = 0xfcf6;
    *(undefined2 *)((int)param_1 + 0x61a) = 0;
    *(undefined2 *)((int)param_1 + 0x61e) = 0;
  }
  return uVar10;
}



// FUN_000112c4 @ 0x112c4

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

ushort FUN_000112c4(int *param_1)

{
  int iVar1;
  byte bVar2;
  int *piVar3;
  ushort *puVar4;
  byte *pbVar5;
  ushort uVar6;
  short sVar9;
  int iVar7;
  uint uVar8;
  ushort uVar10;
  char cVar11;
  undefined4 uVar12;
  char cVar13;
  ushort local_44;
  ushort local_42;
  undefined1 auStack_40 [2];
  short local_3e;
  short local_3a;
  short local_36;
  undefined1 auStack_34 [18];
  undefined1 auStack_22 [18];
  int local_10;
  int local_c;
  int local_8;
  
  iVar1 = *param_1;
  uVar12 = 1000;
  sVar9 = *(short *)(param_1 + 0x19c);
  if (sVar9 < 0) {
    sVar9 = sVar9 + 1;
  }
  *(ushort *)(param_1 + 8) = *(short *)(param_1 + 8) + (sVar9 >> 1) & 0x3fff;
  sVar9 = *(short *)(param_1 + 0x19d);
  if (sVar9 < 0) {
    sVar9 = sVar9 + 1;
  }
  *(ushort *)((int)param_1 + 0x22) = *(short *)((int)param_1 + 0x22) + (sVar9 >> 1) & 0x3fff;
  *(undefined2 *)(param_1 + 9) = 0;
  iVar7 = FUN_00003908(*(int *)((int)param_1 + 0x62a) >> 1);
  if (iVar7 < 1000) {
    iVar7 = 1000;
  }
  if (*(char *)((int)param_1 + 0x676) == '\x05') {
    *(undefined4 *)((int)param_1 + 0x62a) = 0;
    *(undefined1 *)((int)param_1 + 0x689) = 0;
    goto LAB_0001147c;
  }
  if (*(char *)((int)param_1 + 0x676) == '\x04') {
    if (*(char *)((int)param_1 + 0x689) == '\0') {
      *(undefined4 *)((int)param_1 + 0x62a) = 0xfffffc18;
    }
    else if (*(char *)((int)param_1 + 0x689) == '\x01') {
      *(int *)((int)param_1 + 0x62a) = *(int *)((int)param_1 + 0x62a) - iVar7;
    }
    else {
      iVar7 = FUN_00003908(*(undefined4 *)((int)param_1 + 0x62a));
      iVar7 = -iVar7;
LAB_00011392:
      *(int *)((int)param_1 + 0x62a) = iVar7;
    }
  }
  else {
    if (*(char *)((int)param_1 + 0x676) != '\x06') {
      if (*(char *)((int)param_1 + 0x676) == '\x02') {
        if (*(char *)((int)param_1 + 0x689) == '\0') {
          *(undefined4 *)((int)param_1 + 0x62a) = 0xfffffc18;
        }
        else if (*(char *)((int)param_1 + 0x689) == '\x02') {
          *(int *)((int)param_1 + 0x62a) = *(int *)((int)param_1 + 0x62a) - iVar7;
        }
        else {
          iVar7 = FUN_00003908(*(undefined4 *)((int)param_1 + 0x62a));
          iVar7 = -iVar7;
LAB_00011402:
          *(int *)((int)param_1 + 0x62a) = iVar7;
        }
      }
      else {
        if (*(char *)((int)param_1 + 0x676) != '\b') {
          if (*(char *)((int)param_1 + 0x676) == '\x01') {
            if (*(char *)((int)param_1 + 0x689) == '\0') {
              *(undefined4 *)((int)param_1 + 0x62a) = 0xfffffc18;
            }
            else if (*(char *)((int)param_1 + 0x689) == '\x03') {
              *(int *)((int)param_1 + 0x62a) = *(int *)((int)param_1 + 0x62a) - iVar7;
            }
            else {
              iVar7 = FUN_00003908(*(undefined4 *)((int)param_1 + 0x62a));
              iVar7 = -iVar7;
LAB_00011470:
              *(int *)((int)param_1 + 0x62a) = iVar7;
            }
          }
          else {
            if (*(char *)((int)param_1 + 0x676) != '\a') goto LAB_0001147c;
            if (*(char *)((int)param_1 + 0x689) == '\0') {
              *(undefined4 *)((int)param_1 + 0x62a) = 1000;
            }
            else {
              if (*(char *)((int)param_1 + 0x689) != '\x03') {
                iVar7 = FUN_00003908(*(undefined4 *)((int)param_1 + 0x62a));
                goto LAB_00011470;
              }
              *(int *)((int)param_1 + 0x62a) = iVar7 + *(int *)((int)param_1 + 0x62a);
            }
          }
          *(undefined1 *)((int)param_1 + 0x689) = 3;
          goto LAB_0001147c;
        }
        if (*(char *)((int)param_1 + 0x689) == '\0') {
          *(undefined4 *)((int)param_1 + 0x62a) = 1000;
        }
        else {
          if (*(char *)((int)param_1 + 0x689) != '\x02') {
            iVar7 = FUN_00003908(*(undefined4 *)((int)param_1 + 0x62a));
            goto LAB_00011402;
          }
          *(int *)((int)param_1 + 0x62a) = iVar7 + *(int *)((int)param_1 + 0x62a);
        }
      }
      *(undefined1 *)((int)param_1 + 0x689) = 2;
      goto LAB_0001147c;
    }
    if (*(char *)((int)param_1 + 0x689) == '\0') {
      *(undefined4 *)((int)param_1 + 0x62a) = 1000;
    }
    else {
      if (*(char *)((int)param_1 + 0x689) != '\x01') {
        iVar7 = FUN_00003908(*(undefined4 *)((int)param_1 + 0x62a));
        goto LAB_00011392;
      }
      *(int *)((int)param_1 + 0x62a) = iVar7 + *(int *)((int)param_1 + 0x62a);
    }
  }
  *(undefined1 *)((int)param_1 + 0x689) = 1;
LAB_0001147c:
  local_8 = 0;
  local_10 = 0;
  local_c = 0;
  if (*(char *)((int)param_1 + 0x689) != '\0') {
    iVar7 = FUN_00003908(*(undefined4 *)((int)param_1 + 0x62a));
    if (iVar7 < 1000) {
      if (-1 < *(int *)((int)param_1 + 0x62a)) {
        uVar12 = 0xfffffc18;
      }
      *(undefined4 *)((int)param_1 + 0x62a) = uVar12;
    }
    if (*(char *)((int)param_1 + 0x689) == '\x01') {
      local_10 = *(int *)((int)param_1 + 0x62a);
    }
    else if (*(char *)((int)param_1 + 0x689) == '\x02') {
      local_8 = *(int *)((int)param_1 + 0x62a);
    }
    else if (*(char *)((int)param_1 + 0x689) == '\x03') {
      local_c = *(int *)((int)param_1 + 0x62a);
    }
    FUN_00002d02((int)*(short *)(param_1 + 8),(int)*(short *)((int)param_1 + 0x22),
                 (int)*(short *)(param_1 + 9),auStack_22);
    FUN_00003438(1,&local_10,&local_10,auStack_22);
  }
  param_1[2] = local_10 >> 6;
  param_1[3] = local_c >> 6;
  param_1[4] = local_8 >> 6;
  if (param_1[3] + *(int *)(iVar1 + 8) < 0x40) {
    *(undefined4 *)(iVar1 + 8) = 0x40;
    param_1[3] = 0;
  }
  *(undefined1 *)((int)param_1 + 0x676) = 0;
  *(undefined2 *)((int)param_1 + 0x626) = 0;
  if ((*(char *)(param_1 + 0x1a1) != '\0') && (*(char *)((int)param_1 + 0x68d) != '\0')) {
    if (*(char *)(param_1 + 0x1a1) == '\x01') {
      if (*(char *)(param_1 + 0x1a4) == '\0') {
        iVar7 = FUN_000149fa(iVar1,0,0,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                             *(undefined4 *)(iVar1 + 0xc),(int)*(short *)(param_1 + 8),
                             (int)*(short *)((int)param_1 + 0x22),(int)*(short *)(param_1 + 9));
      }
      else {
        iVar7 = FUN_00014b1e(iVar1,0xa8c,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                             *(undefined4 *)(iVar1 + 0xc),0,(int)*(short *)((int)param_1 + 0x22),
                             (int)*(short *)(param_1 + 9));
      }
      if ((iVar7 != 0) && (param_1 == SUB_00020b28)) {
        DAT_00021068 = iVar7;
        *(undefined2 *)(iVar7 + 0x626) = 1;
        _DAT_00021030 = 1;
      }
      *(char *)((int)param_1 + 0x68d) = *(char *)((int)param_1 + 0x68d) + -1;
      *(short *)((int)param_1 + 0x642) =
           *(short *)((int)param_1 + 0x642) - *(short *)(*(int *)(iVar7 + 4) + 0x1a);
    }
    piVar3 = param_1 + 0x1a1;
    cVar13 = *(char *)piVar3;
    *(char *)piVar3 = *(char *)piVar3 + '\x01';
    if (cVar13 == '\x04') {
      *(undefined1 *)piVar3 = 0;
    }
  }
  uVar10 = *(ushort *)((int)param_1 + 0x682) & 3;
  if (((*(ushort *)((int)param_1 + 0x682) & 3) != 0) && (*(char *)((int)param_1 + 0x68e) != '\0')) {
    uVar10 = *(short *)((int)param_1 + 0x682) >> 2;
    uVar6 = uVar10 & 7;
    if ((uVar10 & 7) == 0) {
      *(ushort *)((int)param_1 + 0x682) = *(ushort *)((int)param_1 + 0x682) & 0xff1f;
      uVar8 = FUN_00013672();
      *(ushort *)((int)param_1 + 0x682) =
           (short)((uVar8 & 0xffff) % 6) << 5 | *(ushort *)((int)param_1 + 0x682);
      do {
        uVar8 = FUN_00013672();
        uVar8 = (uVar8 & 0xffff) % 6;
      } while (((ushort)(*(short *)((int)param_1 + 0x682) >> 5) & 7) == uVar8);
      *(ushort *)((int)param_1 + 0x682) = *(ushort *)((int)param_1 + 0x682) & 0xf8ff;
      *(ushort *)((int)param_1 + 0x682) = (short)uVar8 << 8 | *(ushort *)((int)param_1 + 0x682);
    }
    if (uVar6 == (*(short *)((int)param_1 + 0x682) >> 5 & 7U)) {
      cVar13 = '\x01';
    }
    else if (uVar6 == (*(short *)((int)param_1 + 0x682) >> 8 & 7U)) {
      cVar13 = '\x02';
    }
    else {
      cVar13 = '\0';
    }
    if (cVar13 != '\0') {
      FUN_00002f64((int)*(short *)((int)param_1 + 0x626) - (int)*(short *)(param_1[1] + 0x46) &
                   0x3fff,(int)*(short *)(param_1 + 8),(int)*(short *)((int)param_1 + 0x22),
                   (int)*(short *)(param_1 + 9),auStack_34);
      local_3e = 0;
      local_3a = 0;
      local_36 = 0x1000;
      FUN_00003220(1,auStack_40,auStack_40,auStack_34);
      FUN_00002574((int)local_3e,(int)local_3a,(int)local_36,0x1000,0x1000000,&local_42,&local_44);
      local_3a = -0x28;
      uVar10 = FUN_00013672();
      local_36 = (uVar10 & 0x3f) + 0x43;
      if (cVar13 == '\x01') {
        cVar11 = *(char *)((int)param_1 + 0x68e);
        if (cVar11 < '\0') {
          cVar11 = cVar11 + '\x01';
        }
        local_3e = -((cVar11 >> 1) * 0xf + 100);
      }
      else {
        cVar11 = *(char *)((int)param_1 + 0x68e);
        if (cVar11 < '\0') {
          cVar11 = cVar11 + '\x01';
        }
        local_3e = (cVar11 >> 1) * 0xf + 100;
      }
      FUN_00003220(1,auStack_40,auStack_40,auStack_34);
      if (*(char *)((int)param_1 + 0x68e) != '\0') {
        uVar10 = FUN_00013652();
        local_42 = local_42 + (uVar10 & 0x3f) & 0x3fff;
        uVar10 = FUN_00013652();
        local_44 = (local_44 + (uVar10 & 0x3f)) - 0x20 & 0x3fff;
        iVar7 = FUN_00014a78(iVar1,0x438,*(int *)(iVar1 + 4) + (int)local_3e,
                             *(int *)(iVar1 + 8) + (int)local_3a,
                             *(int *)(iVar1 + 0xc) + (int)local_36,(int)(short)local_42,local_44,
                             (int)*(short *)(param_1 + 9));
        if ((iVar7 != 0) && (param_1 == SUB_00020b28)) {
          DAT_00021068 = iVar7;
          *(undefined2 *)(iVar7 + 0x626) = 1;
          _DAT_0002102e = 1;
        }
        *(char *)((int)param_1 + 0x68e) = *(char *)((int)param_1 + 0x68e) + -1;
        *(short *)((int)param_1 + 0x642) =
             *(short *)((int)param_1 + 0x642) - *(short *)(*(int *)(iVar7 + 4) + 0x1a);
        *(char *)((int)param_1 + 0x68f) = cVar13;
      }
    }
    *(ushort *)((int)param_1 + 0x682) = *(ushort *)((int)param_1 + 0x682) & 0xffe3;
    uVar10 = (uVar6 + 1 & 7) << 2;
    *(ushort *)((int)param_1 + 0x682) = uVar10 | *(ushort *)((int)param_1 + 0x682);
    if (uVar6 == 5) {
      puVar4 = (ushort *)((int)param_1 + 0x682);
      *puVar4 = *puVar4 - 1;
      *puVar4 = *(ushort *)((int)param_1 + 0x682) & 0xffe3;
    }
  }
  if (*(char *)((int)param_1 + 0x687) != '\0') {
    if (*(char *)((int)param_1 + 0x687) == '\x01') {
      FUN_00019d16(0,1,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                   *(undefined4 *)(iVar1 + 0xc),(int)*(short *)(param_1 + 8),
                   (int)*(short *)((int)param_1 + 0x22),(int)*(short *)(param_1 + 9),
                   (int)*(short *)((int)param_1 + 0x626) - (int)*(short *)(param_1[1] + 0x46) &
                   0x3fff);
    }
    pbVar5 = (byte *)((int)param_1 + 0x687);
    bVar2 = *pbVar5;
    uVar10 = (ushort)bVar2;
    *pbVar5 = *pbVar5 + 1;
    if (bVar2 == 2) {
      *pbVar5 = 0;
    }
  }
  if (((('\0' < *(char *)((int)param_1 + 0x68a)) && (*(char *)((int)param_1 + 0x68a) < '-')) &&
      ((*(byte *)((int)param_1 + 0x699) & 0x20) == 0)) || (*(char *)((int)param_1 + 0x68a) < '\0'))
  {
    *(char *)((int)param_1 + 0x68a) = *(char *)((int)param_1 + 0x68a) + '\x01';
    *(byte *)((int)param_1 + 0x68b) = *(byte *)((int)param_1 + 0x68b) | 2;
  }
  return uVar10;
}



// FUN_00011902 @ 0x11902

undefined4 FUN_00011902(int *param_1)

{
  int iVar1;
  int *piVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  uint uVar6;
  short sVar7;
  short sVar8;
  short sVar9;
  undefined1 *puVar10;
  byte *pbVar11;
  int *piVar12;
  int *piVar13;
  short *psVar14;
  bool bVar15;
  short sStack_4a;
  short sStack_46;
  undefined1 auStack_34 [18];
  undefined1 auStack_22 [18];
  int local_10;
  int local_c;
  int local_8;
  
  iVar1 = *param_1;
  if (((*(byte *)((int)param_1 + 0x68b) & 2) != 0) ||
     ((*(char *)((int)param_1 + 0x68a) != '-' &&
      (iVar4 = FUN_00003908((int)*(char *)((int)param_1 + 0x68a)), 0 < iVar4)))) {
    bVar15 = *(char *)((int)param_1 + 0x68a) == '\0';
    if ((bVar15) || (*(char *)((int)param_1 + 0x68a) == '-')) {
      *(byte *)((int)param_1 + 0x68b) = *(byte *)((int)param_1 + 0x68b) & 0xfd;
    }
    else {
      *(byte *)((int)param_1 + 0x68b) = *(byte *)((int)param_1 + 0x68b) | 2;
    }
    iVar4 = *(int *)(iVar1 + 0x4a);
    if (bVar15) {
      *(byte *)((int)param_1 + 0x68b) = *(byte *)((int)param_1 + 0x68b) | 1;
      sVar8 = *(short *)(iVar1 + 0x3c) + -1;
      if (sVar8 != -1) {
        pbVar11 = (byte *)(iVar4 + 0x13);
        do {
          if (*(char *)(iVar4 + 0x17) == '\x01') {
            *pbVar11 = *pbVar11 | 2;
          }
          pbVar11 = pbVar11 + 0x20;
          iVar4 = iVar4 + 0x20;
          sVar8 = sVar8 + -1;
        } while (sVar8 != -1);
      }
    }
    else {
      *(byte *)((int)param_1 + 0x68b) = *(byte *)((int)param_1 + 0x68b) & 0xfe;
      iVar5 = FUN_00003908((int)*(char *)((int)param_1 + 0x68a));
      uVar6 = ((0x2d - iVar5) * -0x1000) / 0x2d & 0x3fff;
      FUN_00002b52(uVar6,uVar6,0,auStack_22);
      FUN_00002b52(uVar6,-uVar6 & 0x3fff,0,auStack_34);
      iVar5 = *(int *)(SUB_000207fc + 0x8c) +
              *(int *)(*(int *)(*(int *)(iVar1 + 0x24) + 0x2a) + 0x2e +
                      (uint)*(ushort *)(iVar1 + 0x10) * 0x18);
      piVar2 = (int *)(iVar5 + 0x22);
      local_10 = *piVar2;
      local_c = *(int *)(iVar5 + 0x26);
      local_8 = *(int *)(iVar5 + 0x2a);
      sVar8 = *(short *)(iVar1 + 0x38);
      iVar5 = *(int *)(iVar1 + 0x46);
      sVar9 = 0;
      if (0 < *(short *)(iVar1 + 0x3c)) {
        uVar6 = (uint)*(short *)(iVar1 + 0x36);
        iVar3 = 0x1a << (uVar6 & 0x3f);
        do {
          if (*(char *)(iVar4 + 0x17) == '\x01') {
            *(byte *)(iVar4 + 0x13) = *(byte *)(iVar4 + 0x13) & 0xfd;
            psVar14 = *(short **)(iVar4 + 0x18);
            if (piVar2[*psVar14 * 3] < 1) {
              puVar10 = auStack_34;
              sStack_4a = (short)(-0x46 << (uVar6 & 0x3f));
              sStack_46 = sStack_4a;
            }
            else {
              puVar10 = auStack_22;
              sStack_46 = (short)(0x46 << (uVar6 & 0x3f));
            }
            piVar12 = piVar2 + sVar8 * 3 + sVar9 * 3;
            piVar13 = (int *)(sVar9 * 0xc + iVar5 + sVar8 * 0xc);
            *piVar13 = *piVar12 - local_10;
            piVar13[1] = piVar12[1] - local_c;
            piVar13[2] = piVar12[2] - local_8;
            FUN_000032c0(1,piVar13,piVar13,puVar10);
            *piVar13 = local_10 + *piVar13;
            piVar13[1] = local_c + piVar13[1];
            piVar13[2] = local_8 + piVar13[2];
            sVar7 = *(short *)(iVar4 + 0x14) + -1;
            if (sVar7 != -1) {
              do {
                piVar12 = piVar2 + *psVar14 * 3;
                piVar13 = (int *)(*psVar14 * 0xc + iVar5);
                *piVar13 = *piVar12 - (int)sStack_46;
                piVar13[1] = piVar12[1] + iVar3;
                piVar13[2] = piVar12[2];
                FUN_000032c0(1,piVar13,piVar13,puVar10);
                *piVar13 = (int)sStack_46 + *piVar13;
                piVar13[1] = piVar13[1] - iVar3;
                sVar7 = sVar7 + -1;
                psVar14 = psVar14 + 1;
              } while (sVar7 != -1);
            }
          }
          sVar9 = sVar9 + 1;
          iVar4 = iVar4 + 0x20;
        } while (sVar9 < *(short *)(iVar1 + 0x3c));
      }
    }
  }
  FUN_00002f64((int)*(short *)((int)param_1 + 0x626) - (int)*(short *)(param_1[1] + 0x46) & 0x3fff,
               (int)*(short *)(param_1 + 8),(int)*(short *)((int)param_1 + 0x22),
               (int)*(short *)(param_1 + 9),&DAT_00023c5c);
  return 1;
}



// FUN_00011bf0 @ 0x11bf0

void FUN_00011bf0(int *param_1,int *param_2)

{
  int iVar1;
  short sVar2;
  int iVar3;
  
  iVar1 = *param_1;
  if (DAT_00021058 == 0) {
    if (*param_2 != 0) {
      sVar2 = **(short **)(*param_2 + 4);
      if ((0xf < sVar2) && (sVar2 < 0x13)) {
        FUN_000157aa(param_2,param_1);
        return;
      }
      if (sVar2 < 8) {
        *(ushort *)(*param_2 + 0x698) = *(ushort *)(*param_2 + 0x698) | 0x400;
      }
      else if ((0x1a < sVar2) && (sVar2 < 0x23)) {
        if (*(int *)(iVar1 + 0x2e) < param_1[2]) {
          iVar3 = param_1[2];
        }
        else {
          iVar3 = *(int *)(iVar1 + 0x2e);
        }
        FUN_000151b2(param_1[1],iVar3,param_1[3],10,-(iVar1 == SUB_00020b28) & 1,0,1);
      }
    }
    *(ushort *)(iVar1 + 0x698) = *(ushort *)(iVar1 + 0x698) | 0x400;
  }
  return;
}



// FUN_00011c96 @ 0x11c96

void FUN_00011c96(int *param_1,int *param_2)

{
  int iVar1;
  short sVar2;
  char *pcVar3;
  bool bVar4;
  uint uVar5;
  int iVar6;
  int iVar7;
  ushort uVar8;
  int iVar9;
  
  iVar1 = *param_1;
  if (iVar1 == SUB_00020b28) {
    return;
  }
  sVar2 = **(short **)(*param_2 + 4);
  switch(sVar2) {
  case 1:
  case 2:
  case 3:
  case 4:
    iVar6 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]);
    if ((*(int *)(iVar1 + 0x6bc) == 0) &&
       (uVar8 = FUN_00013672(), iVar6 < (int)((uVar8 & 0x1ff) + 1000))) {
      *(int **)(iVar1 + 0x6bc) = param_2;
      *(char *)((int)param_2 + 0x29) = *(char *)((int)param_2 + 0x29) + '\x01';
    }
    break;
  case 5:
  case 6:
  case 7:
  case 0x1c:
  case 0x1d:
  case 0x1e:
  case 0x1f:
  case 0x20:
  case 0x22:
  case 0x28:
    uVar5 = FUN_00013672();
    if ((uVar5 & 3) != 0) {
      return;
    }
    if (((*(int *)(iVar1 + 0x32) != 0x13038) && (*(int *)(iVar1 + 0x32) != 0x13100)) &&
       (*(int *)(iVar1 + 0x32) != 0x1325c)) {
      return;
    }
    iVar6 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]);
    if (iVar6 == 0) {
      iVar9 = 0x7fffffff;
    }
    else {
      iVar9 = ((param_2[8] * 0x6720) / 500 << 6) / iVar6;
    }
    if ((sVar2 < 0x1b) || (0x22 < sVar2)) {
      if (sVar2 < 8) {
        if (*(int *)(iVar1 + 0x32) == 0x13100) {
          return;
        }
        if (*(short *)(iVar1 + 0x696) < 2) {
          return;
        }
      }
    }
    else {
      iVar7 = FUN_0000042c(param_2);
      if (iVar7 != 0x100) {
        return;
      }
      if (*(char *)(iVar1 + 0x68d) == '\0') {
        return;
      }
    }
    if (*(int *)(iVar1 + 0x6a2) != 0) {
      iVar9 = iVar9 >> 1;
    }
    if (sVar2 == 5) {
      iVar9 = iVar9 << 2;
    }
    uVar8 = FUN_00013672();
    if (iVar9 <= (int)(uint)(uVar8 & 0x7f)) {
      return;
    }
    bVar4 = false;
    if (*(int *)(iVar1 + 0x6a2) == 0) {
      *(int **)(iVar1 + 0x6a2) = param_2;
      *(int *)(iVar1 + 0x6a6) = iVar6;
LAB_00011e2a:
      *(char *)((int)param_2 + 0x29) = *(char *)((int)param_2 + 0x29) + '\x01';
      bVar4 = true;
    }
    else if (iVar6 * 2 < *(int *)(iVar1 + 0x6a6)) {
      *(int *)(iVar1 + 0x6a6) = iVar6;
      pcVar3 = (char *)(*(int *)(iVar1 + 0x6a2) + 0x29);
      *pcVar3 = *pcVar3 + -1;
      *(int **)(iVar1 + 0x6a2) = param_2;
      goto LAB_00011e2a;
    }
    if (bVar4) {
      if (((sVar2 < 0x1b) || (0x22 < sVar2)) && (sVar2 != 0x28)) {
        if (sVar2 < 8) {
          *(undefined4 *)(iVar1 + 0x32) = 0x13038;
          FUN_00012b66(iVar1);
        }
      }
      else {
        FUN_00012b66(iVar1);
        *(undefined4 *)(iVar1 + 0x32) = 0x1325c;
        *(undefined1 *)(iVar1 + 0x686) = 0;
      }
    }
  }
  return;
}



// FUN_00011ec6 @ 0x11ec6

void FUN_00011ec6(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x6a2) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x6a2) + 0x29);
    *pcVar1 = *pcVar1 + -1;
    *(undefined4 *)(param_1 + 0x6a2) = 0;
  }
  return;
}



// FUN_00011ee0 @ 0x11ee0

uint FUN_00011ee0(int *param_1,int *param_2)

{
  int iVar1;
  short sVar2;
  char cVar3;
  char *pcVar4;
  bool bVar5;
  uint uVar6;
  uint uVar7;
  uint uVar8;
  int iVar9;
  
  iVar1 = *param_1;
  sVar2 = **(short **)(*param_2 + 4);
  uVar6 = (int)sVar2 - 1;
  switch(uVar6) {
  case 0:
  case 1:
  case 3:
  case 0x1a:
  case 0x1c:
  case 0x1d:
  case 0x1e:
  case 0x21:
  case 0x26:
    uVar6 = FUN_00013672();
    if ((uVar6 & 3) != 0) {
      return uVar6 & 0xffff0003;
    }
    if ((((*(int *)(iVar1 + 0x32) != 0x13038) && (*(int *)(iVar1 + 0x32) != 0x13100)) &&
        (*(int *)(iVar1 + 0x32) != 0x13158)) && (*(int *)(iVar1 + 0x32) != 0x1325c)) {
      return uVar6 & 0xffff0003;
    }
    uVar7 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]);
    if (uVar7 == 0) {
      iVar9 = 0x7fffffff;
    }
    else {
      iVar9 = ((param_2[8] * 0x6720) / 500 << 6) / (int)uVar7;
    }
    if (*(int *)(iVar1 + 0x32) == 0x13158) {
      if (*(int *)(iVar1 + 0x6a2) != 0) {
        return uVar7;
      }
      if (sVar2 < 0x1b) {
        return uVar7;
      }
      if (0x22 < sVar2) {
        return uVar7;
      }
      uVar6 = FUN_0000042c(param_2);
      if (uVar6 != 0x200) {
        return uVar6;
      }
      uVar8 = FUN_00013672();
      uVar6 = uVar8 & 0xffff007f;
      if (iVar9 <= (int)(uVar8 & 0x7f)) {
        return uVar6;
      }
      *(int **)(iVar1 + 0x6a2) = param_2;
      *(uint *)(iVar1 + 0x6a6) = uVar7;
    }
    else {
      if (*(int *)(iVar1 + 0x6a2) != 0) {
        iVar9 = iVar9 >> 1;
      }
      if (sVar2 == 4) {
        iVar9 = iVar9 << 3;
      }
      uVar8 = FUN_00013672();
      uVar6 = uVar8 & 0xffff007f;
      if (iVar9 <= (int)(uVar8 & 0x7f)) {
        return uVar6;
      }
      bVar5 = false;
      if ((*(int *)(iVar1 + 0x6a2) == 0) ||
         (uVar6 = uVar7 * 2, (int)uVar6 < *(int *)(iVar1 + 0x6a6))) {
        bVar5 = true;
      }
      if (bVar5) {
        if ((sVar2 < 0x1b) || (0x22 < sVar2)) {
          if (sVar2 < 8) {
            if (1 < *(short *)(iVar1 + 0x696)) {
              *(undefined4 *)(iVar1 + 0x32) = 0x13038;
              goto LAB_000120d6;
            }
          }
          else if (sVar2 == 0x27) {
            cVar3 = *(char *)(iVar1 + 0x68d);
            goto joined_r0x000120c8;
          }
        }
        else {
          uVar6 = FUN_0000042c(param_2);
          if (uVar6 == 0x200) {
            uVar6 = *(ushort *)(iVar1 + 0x698) & 0xffff03b2;
            if ((*(ushort *)(iVar1 + 0x698) & 0x3b2) == 0) {
              cVar3 = *(char *)(iVar1 + 0x68d);
joined_r0x000120c8:
              if (cVar3 == '\0') goto LAB_000120e0;
              *(undefined4 *)(iVar1 + 0x32) = 0x1325c;
              *(undefined1 *)(iVar1 + 0x686) = 0;
            }
            else {
              *(undefined4 *)(iVar1 + 0x32) = 0x13158;
            }
LAB_000120d6:
            uVar6 = FUN_00012b66(iVar1);
            goto LAB_000120e2;
          }
        }
LAB_000120e0:
        bVar5 = false;
      }
LAB_000120e2:
      if (!bVar5) {
        return uVar6;
      }
      if (*(int *)(iVar1 + 0x6a2) == 0) {
        *(int **)(iVar1 + 0x6a2) = param_2;
        *(uint *)(iVar1 + 0x6a6) = uVar7;
      }
      else {
        *(uint *)(iVar1 + 0x6a6) = uVar7;
        pcVar4 = (char *)(*(int *)(iVar1 + 0x6a2) + 0x29);
        *pcVar4 = *pcVar4 + -1;
        *(int **)(iVar1 + 0x6a2) = param_2;
      }
    }
    break;
  default:
    goto switchD_00011f08_caseD_2;
  case 5:
  case 6:
    uVar7 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]);
    if (*(int *)(iVar1 + 0x6bc) != 0) {
      return uVar7;
    }
    uVar8 = FUN_00013672();
    uVar6 = uVar8 & 0xffff01ff;
    if ((int)((uVar8 & 0x1ff) + 1000) <= (int)uVar7) {
      return uVar6;
    }
    *(int **)(iVar1 + 0x6bc) = param_2;
  }
  *(char *)((int)param_2 + 0x29) = *(char *)((int)param_2 + 0x29) + '\x01';
switchD_00011f08_caseD_2:
  return uVar6;
}



// FUN_0001215a @ 0x1215a

ushort FUN_0001215a(short param_1,short param_2)

{
  short sVar1;
  short sVar2;
  ushort unaff_D2w;
  
  sVar1 = FUN_000038e8((int)param_1);
  sVar2 = FUN_000038e8((int)param_2);
  if (sVar2 < sVar1) {
    sVar1 = FUN_0000250c(((int)param_2 << 0xc) / (int)sVar1);
    unaff_D2w = 0x1000 - sVar1;
    if (0 < param_1) {
      unaff_D2w = -unaff_D2w;
    }
  }
  else if (param_2 != 0) {
    unaff_D2w = FUN_0000250c((param_1 * -0x1000) / (int)sVar2);
    if (param_2 < 0) {
      unaff_D2w = 0x2000 - unaff_D2w;
    }
  }
  return unaff_D2w & 0x3fff;
}



// FUN_000121e4 @ 0x121e4

undefined4
FUN_000121e4(int *param_1,int param_2,int param_3,int param_4,int *param_5,int param_6,
            undefined2 *param_7,undefined2 *param_8)

{
  int iVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  short sVar5;
  undefined2 uVar6;
  undefined4 uVar7;
  short local_1a;
  short local_18;
  undefined1 auStack_16 [18];
  
  iVar3 = *param_1;
  iVar2 = (*(int *)(iVar3 + 8) + param_1[3] * 0x2d) - *(int *)((int)param_1 + 0x2e);
  *param_5 = iVar2;
  if (iVar2 < 4000) {
    param_3 = (param_3 + 4000) - *param_5;
    if (*param_5 < 0x3e9) {
      FUN_00012b40(param_1);
    }
    uVar7 = 1;
  }
  else {
    uVar7 = 0;
  }
  param_2 = param_2 - *(int *)(iVar3 + 4);
  iVar1 = FUN_000038e8(param_2);
  param_3 = param_3 - *(int *)(iVar3 + 8);
  iVar2 = FUN_000038e8(param_3);
  param_4 = param_4 - *(int *)(iVar3 + 0xc);
  iVar3 = FUN_000038e8(param_4);
  iVar3 = iVar3 + iVar1;
  iVar1 = param_3;
  if (iVar3 < 0x8001) goto LAB_000122ea;
  if (iVar2 < 0x801) {
    iVar1 = iVar3;
    if (iVar3 < 0) {
      iVar1 = iVar3 + 0xff;
    }
    iVar2 = iVar2 * (iVar1 >> 8);
    if (iVar2 < 0) {
      iVar2 = iVar2 + 0x1f;
    }
    iVar1 = iVar2 >> 5;
  }
  else {
    iVar1 = iVar3;
    if (iVar3 < 0) {
      iVar1 = iVar3 + 3;
    }
    iVar1 = iVar1 >> 2;
  }
  if (param_3 < 0) {
    if (iVar3 < 0x10000) {
      iVar1 = -iVar1;
      goto LAB_000122be;
    }
    iVar1 = -iVar1;
  }
  else if (iVar3 < 0x10000) {
LAB_000122be:
    iVar2 = (iVar3 + -0x8000) * (iVar1 - param_3);
    if (iVar2 < 0) {
      iVar2 = iVar2 + 0x7fff;
    }
    iVar1 = (iVar2 >> 0xf) + param_3;
  }
  iVar2 = FUN_00003908(iVar1);
LAB_000122ea:
  sVar5 = 0;
  for (uVar4 = iVar2 + iVar3; (uVar4 & 0xffff8000) != 0; uVar4 = (int)uVar4 >> 1) {
    sVar5 = sVar5 + 1;
  }
  uVar4 = (uint)sVar5;
  *(short *)(param_6 + 2) = (short)(param_2 >> (uVar4 & 0x3f));
  *(short *)(param_6 + 6) = (short)(iVar1 >> (uVar4 & 0x3f));
  *(short *)(param_6 + 10) = (short)(param_4 >> (uVar4 & 0x3f));
  FUN_00002b72(-(int)*(short *)(param_1 + 8) & 0x3fff,-(int)*(short *)((int)param_1 + 0x22) & 0x3fff
               ,-(int)*(short *)(param_1 + 9) & 0x3fff,auStack_16);
  FUN_00003220(1,param_6,param_6,auStack_16);
  iVar3 = (int)*(short *)(param_6 + 10) * (int)*(short *)(param_6 + 10) +
          (int)*(short *)(param_6 + 6) * (int)*(short *)(param_6 + 6) +
          (int)*(short *)(param_6 + 2) * (int)*(short *)(param_6 + 2);
  sVar5 = FUN_0000f7e0(iVar3);
  FUN_00002574((int)*(short *)(param_6 + 2),(int)*(short *)(param_6 + 6),
               (int)*(short *)(param_6 + 10),(int)sVar5,iVar3,&local_18,&local_1a);
  uVar6 = FUN_0000f87c((int)local_18);
  *param_7 = uVar6;
  uVar6 = FUN_0000f87c((int)local_1a);
  *param_8 = uVar6;
  return uVar7;
}



// FUN_000123de @ 0x123de

void FUN_000123de(int param_1,short param_2,short param_3,short param_4)

{
  int iVar1;
  short sVar2;
  short sVar3;
  
  sVar3 = *(short *)(param_1 + 0x650);
  if ((*(byte *)(param_1 + 0x699) & 1) == 0) {
    iVar1 = FUN_00003908((int)param_4);
    if (*(short *)(*(int *)(param_1 + 4) + 0x4c) < iVar1) {
      if (param_4 < 0) {
        param_4 = -*(short *)(*(int *)(param_1 + 4) + 0x4c);
      }
      else {
        param_4 = *(short *)(*(int *)(param_1 + 4) + 0x4c);
      }
    }
    iVar1 = FUN_00003908((int)param_2);
    if (0x40 < iVar1) {
      if (param_2 < 0) {
        param_2 = -0x40;
      }
      else {
        param_2 = 0x40;
      }
    }
    iVar1 = FUN_00003908((int)param_3);
    if (0x200 < iVar1) {
      if (param_3 < 0) {
        param_3 = -0x200;
      }
      else {
        param_3 = 0x200;
      }
    }
    iVar1 = param_1 + sVar3 * 2;
    *(short *)(iVar1 + 0x652) = param_4;
    *(short *)(param_1 + 0x66a) = param_4 + *(short *)(param_1 + 0x66a);
    *(short *)(iVar1 + 0x65a) = param_3;
    *(short *)(param_1 + 0x66c) = param_3 + *(short *)(param_1 + 0x66c);
    *(short *)(iVar1 + 0x662) = param_2;
    *(short *)(param_1 + 0x66e) = param_2 + *(short *)(param_1 + 0x66e);
    sVar3 = sVar3 + 1;
    if (sVar3 == 4) {
      sVar3 = 0;
    }
    iVar1 = param_1 + sVar3 * 2;
    *(short *)(param_1 + 0x66a) = *(short *)(param_1 + 0x66a) - *(short *)(iVar1 + 0x652);
    *(short *)(param_1 + 0x66c) = *(short *)(param_1 + 0x66c) - *(short *)(iVar1 + 0x65a);
    *(short *)(param_1 + 0x66e) = *(short *)(param_1 + 0x66e) - *(short *)(iVar1 + 0x662);
    sVar2 = *(short *)(param_1 + 0x66a);
    if (sVar2 < 0) {
      sVar2 = sVar2 + 3;
    }
    *(short *)(param_1 + 0x674) = sVar2 >> 2;
    sVar2 = *(short *)(param_1 + 0x66c);
    if (sVar2 < 0) {
      sVar2 = sVar2 + 3;
    }
    *(short *)(param_1 + 0x670) = sVar2 >> 2;
    sVar2 = *(short *)(param_1 + 0x66e);
    if (sVar2 < 0) {
      sVar2 = sVar2 + 3;
    }
    *(short *)(param_1 + 0x672) = sVar2 >> 2;
    *(short *)(param_1 + 0x650) = sVar3;
  }
  return;
}



// FUN_000124e0 @ 0x124e0

/* WARNING: Removing unreachable block (ram,0x00012820) */
/* WARNING: Removing unreachable block (ram,0x0001282a) */
/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

ushort FUN_000124e0(int *param_1)

{
  int iVar1;
  short *psVar2;
  short sVar3;
  uint uVar4;
  undefined2 uVar5;
  ushort uVar6;
  ushort uVar7;
  ushort uVar8;
  ushort uVar9;
  short sVar10;
  
  iVar1 = *param_1;
  sVar10 = 0;
  uVar7 = *(ushort *)(param_1 + 0x1a6);
  uVar8 = *(ushort *)((int)param_1 + 0x69a);
  if (*(short *)(param_1 + 0x1a7) != 0) {
    *(short *)(param_1 + 0x1a7) = *(short *)(param_1 + 0x1a7) + -1;
    uVar7 = uVar7 & 0xfc3f;
  }
  do {
    uVar6 = uVar7 & 0x3c0;
    uVar9 = uVar7;
    if ((uVar7 & 0x3c0) != 0) {
      if (uVar6 == 0x80) {
        sVar10 = 0xff;
        if (((char)uVar8 < '\0') && (uVar4 = FUN_00013672(), (uVar4 & 3) == 0)) {
          uVar8 = uVar7 & 0xff7f;
LAB_00012590:
          sVar10 = 0xff;
          uVar9 = uVar8 | 0x200;
        }
      }
      else if (uVar6 < 0x81) {
        if (uVar6 == 0x40) {
          sVar10 = 0xff;
          if ((uVar8 & 0x40) == 0) {
            FUN_0001ac8a(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                         *(undefined4 *)(iVar1 + 0xc),param_1);
          }
          else {
            uVar4 = FUN_00013652();
            if ((uVar4 & 1) != 0) {
              uVar8 = uVar7 & 0xffbf;
              uVar4 = FUN_00013652();
              if ((uVar4 & 1) == 0) goto LAB_00012590;
              uVar9 = uVar8 | 0x80;
            }
          }
        }
      }
      else if (uVar6 == 0x100) {
        if ((*(byte *)(param_1 + 0x1a6) & 8) == 0) {
          sVar10 = 0xff;
        }
        else {
          *(undefined2 *)(param_1 + 0x1a7) = 0x16;
        }
        if ((uVar8 & 0x100) == 0) {
          FUN_00013798(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                       *(undefined4 *)(iVar1 + 0xc),param_1);
        }
        else if (((*(byte *)(param_1 + 0x1a6) & 8) != 0) ||
                (uVar4 = FUN_00013672(), (uVar4 & 1) != 0)) {
          uVar9 = uVar7 & 0xfeff | 0x400;
        }
      }
      else if (uVar6 == 0x200) {
        sVar10 = 0xff;
      }
    }
    uVar6 = uVar9 ^ uVar7;
    *(ushort *)(param_1 + 0x1a6) = ~uVar6 & *(ushort *)(param_1 + 0x1a6);
    *(ushort *)(param_1 + 0x1a6) = uVar9 & uVar6 | *(ushort *)(param_1 + 0x1a6);
    uVar8 = uVar6 & uVar7;
    uVar7 = uVar6 & uVar9;
    if (uVar7 == 0) {
      if (((*(byte *)(param_1 + 0x1a6) & 4) != 0) && ((*(byte *)((int)param_1 + 0x69a) & 4) == 0)) {
        if (param_1 == SUB_00020b28) {
          FUN_000098c6((*(byte *)(param_1 + 0x1a6) & 8) == 0);
          if (DAT_00021058 == 0) {
            *(undefined2 *)(SUB_000207fc + 0x98) = 2;
          }
          DAT_0002106c = DAT_00021070;
          if (DAT_00021058 != 0) {
            if (DAT_00021064 == 0) {
              *(undefined2 *)(SUB_000207fc + 0xd8) = 1;
            }
            else if (_DAT_00021062 != 0) {
              *(undefined2 *)(SUB_000207fc + 0xd8) = 4;
            }
          }
        }
        *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) & 0xfc3f;
        sVar3 = DAT_00021058;
        DAT_00021058 = 0xffff;
        if (*(int *)((int)param_1 + 0x32) == 0x13158) {
          uVar5 = 300;
        }
        else {
          uVar5 = 100;
        }
        FUN_00013ee0(iVar1,uVar5);
        DAT_00021058 = sVar3;
        if (sVar3 == 0) {
          if ((((*(short *)(SUB_000207fc + 0x90) == 5) && (*(short *)param_1[1] == 5)) &&
              (*(short *)(SUB_000207fc + 0xd2) == 0)) &&
             (DAT_0002705e = DAT_0002705e + 1, DAT_0002705e == 2)) {
            *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
          }
          if (((*(short *)(SUB_000207fc + 0x90) == 7) && (*(short *)param_1[1] == 4)) &&
             ((*(short *)(SUB_000207fc + 0xd2) == 0 &&
              (DAT_0002705c = DAT_0002705c + 1, DAT_0002705c == 2)))) {
            *(undefined2 *)(SUB_000207fc + 0xd2) = 0xffff;
          }
          if ((*(short *)(iVar1 + 0x52) != 0) && (*(char *)(param_1 + 0x1a6) < '\0')) {
            if (*(short *)(iVar1 + 0x52) < 1) {
              *(short *)(SUB_000207fc + 0xd6) = *(short *)(SUB_000207fc + 0xd6) + 1;
            }
            else {
              psVar2 = (short *)(*(short *)(iVar1 + 0x52) * 2 + SUB_000207fc + 0xbc);
              *psVar2 = *psVar2 + 1;
            }
          }
        }
        uVar6 = FUN_000060fc(iVar1);
      }
      if (((*(byte *)(param_1 + 0x1a6) & 0x10) != 0) &&
         ((*(byte *)((int)param_1 + 0x69a) & 0x10) == 0)) {
        uVar6 = FUN_0001b2a0(*(undefined4 *)(iVar1 + 4),*(undefined4 *)((int)param_1 + 0x2e),
                             *(undefined4 *)(iVar1 + 0xc),param_1);
      }
      if ((((*(byte *)(param_1 + 0x1a6) & 1) != 0) && (*(char *)(param_1 + 0x1a2) == '\0')) &&
         (param_1 != SUB_00020b28)) {
        uVar7 = FUN_00013672();
        uVar6 = uVar7 & 0x1f;
        if (((uVar7 & 0x1f) == 0) && (*(int *)((int)param_1 + 0x32) != 0x13158)) {
          *(undefined1 *)(param_1 + 0x1a2) = 1;
        }
      }
      if (((param_1 == SUB_00020b28) && ((*(byte *)((int)param_1 + 0x699) & 1) != 0)) &&
         ((*(byte *)((int)param_1 + 0x69b) & 1) == 0)) {
        _DAT_0002105c = 1;
        _DAT_0002105a = 0;
        if (DAT_00021064 == 0) {
          *(undefined2 *)(SUB_000207fc + 0xd8) = 1;
          _DAT_0002102c = 1;
        }
      }
      if (sVar10 != 0) {
        uVar7 = FUN_00013672();
        uVar6 = sVar10 >> 1;
        *(ushort *)(param_1 + 0x1a7) = uVar6 + (short)((int)((uint)uVar7 & (int)sVar10) >> 1);
      }
      *(undefined2 *)((int)param_1 + 0x69a) = *(undefined2 *)(param_1 + 0x1a6);
      return uVar6;
    }
  } while( true );
}



// FUN_00012840 @ 0x12840

void FUN_00012840(int param_1,int param_2,int param_3,short param_4,short param_5)

{
  ushort uVar1;
  int iVar2;
  short sVar3;
  ushort uVar4;
  short sVar5;
  ushort uVar6;
  
  sVar5 = 0;
  iVar2 = FUN_000038e8((int)param_5);
  if (((iVar2 < 0x501) && (iVar2 = FUN_000038e8((int)param_4), iVar2 < 0x501)) && (-1 < param_2)) {
    sVar3 = param_4 * 5 + *(short *)(param_1 + 0x61a) * -4;
    uVar6 = param_5 * 2 - *(short *)(param_1 + 0x61c);
    sVar5 = (short)uVar6 >> 4;
    goto LAB_00012990;
  }
  sVar3 = FUN_0001215a((int)*(short *)(param_3 + 2),(int)*(short *)(param_3 + 6));
  uVar4 = FUN_0000f87c((int)sVar3);
  if (param_2 < 0x5000) {
    uVar6 = *(short *)(param_1 + 0x24) + uVar4 & 0x3fff;
    if (param_2 < 0) {
      uVar4 = 0;
    }
    else if (param_2 < 0x1000) {
      param_2 = param_2 * 0x1000;
      if (param_2 < 0) {
        param_2 = param_2 + 0xfff;
      }
      uVar4 = (ushort)(param_2 >> 0xc);
    }
    else {
      iVar2 = (param_2 + -0x1000) * 0x1000;
      if (iVar2 < 0) {
        iVar2 = iVar2 + 0x3fff;
      }
      uVar4 = (short)(iVar2 >> 0xe) + 0x1000;
    }
    if (uVar6 < 0x2001) {
      uVar1 = uVar4;
      if ((short)uVar4 < (short)uVar6) goto LAB_00012910;
    }
    else {
      uVar1 = 0x4000 - uVar4;
      if ((short)uVar6 < (short)(0x4000 - uVar4)) {
LAB_00012910:
        uVar6 = uVar1;
      }
    }
    uVar4 = FUN_0000f87c((int)(short)uVar6 - (int)*(short *)(param_1 + 0x24) & 0x3fff);
  }
  iVar2 = FUN_00003908((int)(short)uVar4);
  if (iVar2 < 0x281) {
    uVar6 = (ushort)((short)uVar4 * 3 + *(short *)(param_1 + 0x61e) * -2 >> 1);
  }
  else {
    uVar6 = uVar4;
  }
  sVar3 = FUN_00002426(uVar4 & 0x3fff);
  sVar3 = (short)(((int)sVar3 << 9) >> 0xc);
  *(ushort *)(param_1 + 0x61e) = uVar4;
LAB_00012990:
  *(short *)(param_1 + 0x61a) = param_4;
  *(short *)(param_1 + 0x61c) = param_5;
  FUN_000123de(param_1,(int)sVar5,(int)sVar3,(int)(short)uVar6);
  return;
}



// FUN_000129b4 @ 0x129b4

void FUN_000129b4(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4)

{
  short local_18;
  short local_16;
  undefined4 local_14;
  undefined1 auStack_10 [12];
  
  FUN_000121e4(param_1,param_2,param_3,param_4,&local_14,auStack_10,&local_16,&local_18);
  FUN_00012840(param_1,local_14,auStack_10,(int)local_16,(int)local_18);
  return;
}



// FUN_00012a0a @ 0x12a0a

undefined4 FUN_00012a0a(int *param_1)

{
  undefined4 uVar1;
  short sVar3;
  int iVar2;
  short sVar4;
  
  if (*(int *)(*param_1 + 8) - *(int *)((int)param_1 + 0x2e) < 0x7d1) {
    if (*(char *)(param_1 + 0x1a3) == '\0') {
      *(undefined1 *)(param_1 + 0x1a3) = 1;
    }
    if ((200 < *(int *)(*param_1 + 8) - *(int *)((int)param_1 + 0x2e)) &&
       ('\0' < *(char *)((int)param_1 + 0x68a))) {
      *(char *)((int)param_1 + 0x68a) = -*(char *)((int)param_1 + 0x68a);
    }
    *(undefined1 *)((int)param_1 + 0x676) = 9;
    sVar3 = FUN_0000f87c(0x2c0 - *(short *)(param_1 + 8));
    sVar4 = (sVar3 + (sVar3 >> 1)) - *(short *)((int)param_1 + 0x61a);
    *(short *)((int)param_1 + 0x61a) = sVar3;
    iVar2 = FUN_00003908((int)sVar4);
    if (0x200 < iVar2) {
      if (sVar4 < 0) {
        sVar4 = -0x200;
      }
      else {
        sVar4 = 0x200;
      }
    }
    FUN_000123de(param_1,0,(int)sVar4,0);
    uVar1 = 0;
  }
  else {
    if ('\0' < *(char *)(param_1 + 0x1a3)) {
      *(char *)(param_1 + 0x1a3) = -*(char *)(param_1 + 0x1a3);
    }
    uVar1 = 1;
  }
  return uVar1;
}



// FUN_00012aba @ 0x12aba

void FUN_00012aba(int param_1,short param_2)

{
  short sVar1;
  short sVar2;
  
  param_2 = param_2 - (short)(*(int *)(param_1 + 0x62a) >> 2);
  sVar1 = *(short *)(param_1 + 0x620);
  *(short *)(param_1 + 0x620) = param_2;
  sVar1 = param_2 * 0x21 + sVar1 * -0x20 + *(short *)(param_1 + 0x64a);
  *(short *)(param_1 + 0x64a) = sVar1 % 0x100;
  sVar2 = sVar1;
  if (sVar1 < 0) {
    sVar2 = sVar1 + 0xff;
  }
  *(char *)(param_1 + 0x676) = (char)((ushort)sVar2 >> 8) + *(char *)(param_1 + 0x676);
  if (*(char *)(param_1 + 0x676) < '\0') {
    *(undefined1 *)(param_1 + 0x676) = 0;
  }
  else if ('\t' < *(char *)(param_1 + 0x676)) {
    *(undefined1 *)(param_1 + 0x676) = 9;
  }
  if (sVar1 < -0x438) {
    if (*(char *)(param_1 + 0x68a) == '\0') {
      *(undefined1 *)(param_1 + 0x68a) = 1;
    }
  }
  else if (0x1c2 < sVar1) {
    FUN_00012b40(param_1);
  }
  return;
}



// FUN_00012b40 @ 0x12b40

void FUN_00012b40(int *param_1)

{
  *(undefined1 *)((int)param_1 + 0x676) = 9;
  if (('\0' < *(char *)((int)param_1 + 0x68a)) &&
     (100 < *(int *)(*param_1 + 8) - *(int *)((int)param_1 + 0x2e))) {
    *(char *)((int)param_1 + 0x68a) = -*(char *)((int)param_1 + 0x68a);
  }
  return;
}



// FUN_00012b66 @ 0x12b66

void FUN_00012b66(int param_1)

{
  if ('\0' < *(char *)(param_1 + 0x68c)) {
    *(char *)(param_1 + 0x68c) = -*(char *)(param_1 + 0x68c);
  }
  return;
}



// FUN_00012b76 @ 0x12b76

void FUN_00012b76(undefined4 *param_1)

{
  short *psVar1;
  int iVar2;
  undefined4 *puVar3;
  
  psVar1 = *(short **)((int)param_1 + 0x616);
  if (*psVar1 == 0) {
    iVar2 = FUN_00012a0a(param_1);
    if (iVar2 != 0) {
      *psVar1 = *psVar1 + 1;
    }
  }
  else {
    puVar3 = (undefined4 *)FUN_0001aaa4(*param_1,psVar1 + 2);
    FUN_00012aba(param_1,(int)psVar1[1]);
    if (puVar3[1] - *(int *)((int)param_1 + 0x2e) < 0x1000) {
      iVar2 = *(int *)((int)param_1 + 0x2e) + 0x1000;
    }
    else {
      iVar2 = puVar3[1];
    }
    FUN_000129b4(param_1,*puVar3,iVar2,puVar3[2]);
  }
  FUN_0000fd16(param_1);
  return;
}



// FUN_00012bf6 @ 0x12bf6

void FUN_00012bf6(undefined4 *param_1)

{
  int iVar1;
  
  if (*(int *)((int)param_1 + 0x616) == 0) {
    iVar1 = FUN_0001a8c2(*param_1,8,4,&DAT_00020508);
    *(int *)((int)param_1 + 0x616) = iVar1;
    *(undefined2 *)(iVar1 + 2) = 0x9d8;
  }
  FUN_00012b76(param_1);
  return;
}



// FUN_00012c36 @ 0x12c36

void FUN_00012c36(undefined4 *param_1)

{
  int iVar1;
  
  if (*(int *)((int)param_1 + 0x616) == 0) {
    iVar1 = FUN_0001a8c2(*param_1,8,4,&DAT_00020538);
    *(int *)((int)param_1 + 0x616) = iVar1;
    *(undefined2 *)(iVar1 + 2) = 0xd5c;
  }
  FUN_00012b76(param_1);
  return;
}



// FUN_00012c76 @ 0x12c76

void FUN_00012c76(undefined4 *param_1)

{
  int iVar1;
  
  if (*(int *)((int)param_1 + 0x616) == 0) {
    iVar1 = FUN_0001a8c2(*param_1,8,4,&DAT_00020568);
    *(int *)((int)param_1 + 0x616) = iVar1;
    *(undefined2 *)(iVar1 + 2) = 0x9d8;
  }
  FUN_00012b76(param_1);
  return;
}



// FUN_00012cb6 @ 0x12cb6

undefined4 FUN_00012cb6(int *param_1)

{
  int iVar1;
  ushort uVar2;
  
  if (param_1[0x1b1] <= DAT_00021020) {
    if (*(char *)((int)param_1 + 0x68d) == '\0') {
      return 1;
    }
    if ((*(char *)(param_1 + 0x1a1) == '\0') && (uVar2 = FUN_00013652(), (uVar2 & 7) == 0)) {
      iVar1 = *param_1;
      *(undefined1 *)(param_1 + 0x1a1) = 2;
      uVar2 = FUN_00013652((int)*(short *)(param_1 + 9));
      uVar2 = FUN_00013652(*(short *)((int)param_1 + 0x22) + -0x80 + (uVar2 & 0xff) & 0x3fff);
      FUN_000149fa(iVar1,*(undefined4 *)((int)param_1 + 0x62a),0,*(undefined4 *)(iVar1 + 4),
                   *(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc),
                   *(short *)(param_1 + 8) + (uVar2 & 0x7f) & 0x3fff);
      *(char *)((int)param_1 + 0x68d) = *(char *)((int)param_1 + 0x68d) + -1;
      *(short *)((int)param_1 + 0x642) = *(short *)((int)param_1 + 0x642) + -6;
    }
  }
  return 0;
}



// FUN_00012d5e @ 0x12d5e

void FUN_00012d5e(int *param_1)

{
  short *psVar1;
  int iVar2;
  int iVar3;
  
  iVar3 = *param_1;
  FUN_00012c36(param_1);
  psVar1 = *(short **)((int)param_1 + 0x616);
  if (*psVar1 == 1) {
    iVar2 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc),0x5bc72d,0,
                         0xffd634ea);
    if (((iVar2 < 50000) && (20000 < *(int *)(iVar3 + 8))) &&
       ((*(byte *)(param_1 + 0x1a6) & 1) == 0)) {
      *psVar1 = 2;
      param_1[0x1b1] = DAT_00021020 + 0x4b;
      if (*(short *)(SUB_000207fc + 0xd2) == 0) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 0xffff;
      }
    }
  }
  else if ((*psVar1 == 2) && (iVar3 = FUN_00012cb6(param_1), iVar3 != 0)) {
    *psVar1 = 3;
  }
  return;
}



// FUN_00012dfa @ 0x12dfa

void FUN_00012dfa(int *param_1)

{
  short *psVar1;
  int iVar2;
  int iVar3;
  
  iVar3 = *param_1;
  FUN_00012bf6(param_1);
  psVar1 = *(short **)((int)param_1 + 0x616);
  if (*psVar1 == 1) {
    iVar2 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc),0x768c3d,0,
                         0xfff5a942);
    if (((iVar2 < 50000) && (20000 < *(int *)(iVar3 + 8))) &&
       ((*(byte *)(param_1 + 0x1a6) & 1) == 0)) {
      *psVar1 = 2;
      param_1[0x1b1] = DAT_00021020 + 0x4b;
      if (*(short *)(SUB_000207fc + 0xd2) == 0) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
      }
    }
  }
  else if ((*psVar1 == 2) && (iVar3 = FUN_00012cb6(param_1), iVar3 != 0)) {
    *psVar1 = 3;
  }
  return;
}



// FUN_00012e96 @ 0x12e96

void FUN_00012e96(int *param_1)

{
  int iVar1;
  int iVar2;
  int iVar3;
  short *psVar4;
  int iVar5;
  ushort uVar6;
  short sVar7;
  short local_18;
  short local_16;
  undefined4 local_14;
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  iVar5 = *SUB_00020b28;
  iVar1 = *param_1;
  psVar4 = *(short **)((int)param_1 + 0x616);
  if (psVar4 == (short *)0x0) {
    psVar4 = (short *)FUN_000020a0(8);
    *(short **)((int)param_1 + 0x616) = psVar4;
    *psVar4 = 0;
  }
  if (*psVar4 == 0) {
    iVar5 = FUN_00012a0a(param_1);
    if (iVar5 != 0) {
      *psVar4 = 1;
    }
  }
  else if (iVar5 != 0) {
    if (param_1[0x1ac] != -999999) {
      uVar6 = FUN_00013652();
      param_1[0x1ab] = (uVar6 & 0x1fff) - 0xfff;
      uVar6 = FUN_00013652();
      param_1[0x1ad] = (uVar6 & 0x800) + 0x1000;
      param_1[0x1ac] = -999999;
    }
    local_e = *(short *)((int)param_1 + 0x6ae);
    local_a = 0;
    local_6 = *(short *)((int)param_1 + 0x6b6);
    FUN_00003220(1,auStack_10,auStack_10,&DAT_0002c59c);
    iVar2 = *(int *)(iVar5 + 4) + (int)local_e;
    iVar3 = *(int *)(iVar5 + 8) + (int)local_a;
    iVar5 = *(int *)(iVar5 + 0xc) + (int)local_6;
    sVar7 = FUN_000121e4(param_1,iVar2,iVar3,iVar5,&local_14,auStack_10,&local_16,&local_18);
    FUN_00012840(param_1,local_14,auStack_10,(int)local_16,(int)local_18);
    if (sVar7 == 0) {
      iVar5 = FUN_000048a6(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                           *(undefined4 *)(iVar1 + 0xc),iVar2,iVar3,iVar5);
      if (iVar5 < 0x3000) {
        sVar7 = (short)(*(int *)((int)SUB_00020b28 + 0x62a) >> 2);
        if (iVar5 < 0x800) {
          sVar7 = 0;
        }
        if (iVar5 < 0x1000) {
          iVar5 = iVar5 * sVar7;
          if (iVar5 < 0) {
            iVar5 = iVar5 + 0xfff;
          }
          sVar7 = (short)(iVar5 >> 0xc);
        }
        else {
          iVar5 = (iVar5 + -0x2000) * (0x1c21 - sVar7);
          if (iVar5 < 0) {
            iVar5 = iVar5 + 0x1fff;
          }
          sVar7 = (short)(iVar5 >> 0xd) + sVar7;
        }
        FUN_00012aba(param_1,(int)sVar7);
        goto LAB_00013048;
      }
    }
    FUN_00012b40(param_1);
  }
LAB_00013048:
  FUN_0000fd16(param_1);
  return;
}



// FUN_00013058 @ 0x13058

void FUN_00013058(int *param_1)

{
  int iVar1;
  bool bVar2;
  int iVar3;
  undefined4 uVar4;
  
  bVar2 = false;
  if ((*(int *)((int)param_1 + 0x6a2) != 0) && (iVar3 = FUN_00012a0a(param_1), iVar3 != 0)) {
    iVar3 = *(int *)((int)param_1 + 0x6a2);
    if ((*(short *)((int)param_1 + 0x696) < 2) ||
       (((*(byte *)(iVar3 + 0x17) & 2) != 0 || (*(int *)(iVar3 + 0x46) == 0)))) {
      *(char *)(iVar3 + 0x29) = *(char *)(iVar3 + 0x29) + -1;
      *(undefined4 *)((int)param_1 + 0x6a2) = 0;
    }
    else {
      iVar1 = *param_1;
      uVar4 = FUN_000048a6(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                           *(undefined4 *)(iVar1 + 0xc),*(undefined4 *)(iVar3 + 4),
                           *(undefined4 *)(iVar3 + 8),*(undefined4 *)(iVar3 + 0xc));
      *(undefined4 *)((int)param_1 + 0x6a6) = uVar4;
      FUN_0001c2ae(param_1);
      bVar2 = true;
    }
  }
  if (!bVar2) {
    if (*(int *)(&DAT_00020598 + *(short *)(param_1 + 0x185) * 4) == 0) {
      if (((*(char *)(param_1 + 0x193) == '\0') || (*(char *)((int)param_1 + 0x676) != '\0')) ||
         (*(int *)((int)param_1 + 0x62a) != 0)) {
        if ((*(int *)((int)param_1 + 0x6a2) == 0) && (*(short *)param_1[1] < 5)) {
          FUN_00012e96(param_1);
        }
        else {
          FUN_0000fd16(param_1);
        }
      }
    }
    else {
      (**(code **)(&DAT_00020598 + *(short *)(param_1 + 0x185) * 4))(param_1);
    }
  }
  return;
}



// FUN_00013120 @ 0x13120

void FUN_00013120(int param_1)

{
  if (*(int *)(&DAT_00020598 + *(short *)(param_1 + 0x614) * 4) == 0) {
    FUN_0000fd16(param_1);
  }
  else {
    (**(code **)(&DAT_00020598 + *(short *)(param_1 + 0x614) * 4))(param_1);
  }
  return;
}



// FUN_0001314c @ 0x1314c

void FUN_0001314c(int param_1)

{
  if (*(int *)(&DAT_00020598 + *(short *)(param_1 + 0x614) * 4) == 0) {
    FUN_0000fd16(param_1);
  }
  else {
    (**(code **)(&DAT_00020598 + *(short *)(param_1 + 0x614) * 4))(param_1);
  }
  return;
}



// FUN_00013178 @ 0x13178

void FUN_00013178(int *param_1)

{
  int iVar1;
  undefined4 uVar2;
  bool bVar3;
  int iVar4;
  undefined4 uVar5;
  
  bVar3 = false;
  if (((*(int *)((int)param_1 + 0x6a2) == 0) ||
      (iVar1 = *(int *)((int)param_1 + 0x6a2), (*(byte *)(iVar1 + 0x17) & 2) != 0)) ||
     (*(int *)(iVar1 + 0x46) == 0)) {
    FUN_00011ec6(param_1);
    if (*(short *)((int)param_1 + 0x696) < 3) {
      *(undefined4 *)((int)param_1 + 0x32) = 0x13100;
    }
    else {
      *(undefined4 *)((int)param_1 + 0x32) = 0x13038;
    }
  }
  else {
    iVar4 = FUN_00012a0a(param_1);
    if (iVar4 == 0) {
      FUN_0000fd16(param_1);
      bVar3 = true;
    }
    else {
      uVar5 = FUN_000048a6(*(undefined4 *)(*param_1 + 4),0,*(undefined4 *)(*param_1 + 0xc),
                           *(undefined4 *)(iVar1 + 4),0,*(undefined4 *)(iVar1 + 0xc));
      *(undefined4 *)((int)param_1 + 0x6a6) = uVar5;
      if (*(int *)((int)param_1 + 0x6a6) < 0xafc9) {
        uVar5 = *(undefined4 *)(iVar1 + 8);
      }
      else {
        uVar5 = 40000;
      }
      FUN_00012aba(param_1,0xd5c);
      uVar2 = *(undefined4 *)((int)param_1 + 0x2e);
      *(undefined4 *)((int)param_1 + 0x2e) = 0xffff63c0;
      FUN_000129b4(param_1,*(undefined4 *)(iVar1 + 4),uVar5,*(undefined4 *)(iVar1 + 0xc));
      *(undefined4 *)((int)param_1 + 0x2e) = 0;
      FUN_0000fd16(param_1);
      bVar3 = true;
      *(undefined4 *)((int)param_1 + 0x2e) = uVar2;
    }
  }
  if (!bVar3) {
    if (*(int *)(&DAT_00020598 + *(short *)(param_1 + 0x185) * 4) == 0) {
      FUN_0000fd16(param_1);
    }
    else {
      (**(code **)(&DAT_00020598 + *(short *)(param_1 + 0x185) * 4))(param_1);
    }
  }
  return;
}



// FUN_0001327c @ 0x1327c

void FUN_0001327c(int *param_1)

{
  int *piVar1;
  bool bVar2;
  int iVar3;
  short sVar7;
  short sVar8;
  int iVar4;
  int iVar5;
  undefined4 uVar6;
  int iVar9;
  int iVar10;
  int iVar11;
  
  bVar2 = false;
  if ((((*(int *)((int)param_1 + 0x6a2) == 0) || (*(char *)((int)param_1 + 0x68d) == '\0')) ||
      (piVar1 = *(int **)((int)param_1 + 0x6a2), (*(byte *)((int)piVar1 + 0x17) & 2) != 0)) ||
     (*(int *)((int)piVar1 + 0x46) == 0)) {
    FUN_00011ec6(param_1);
    FUN_00012b66(param_1);
    if (*(short *)((int)param_1 + 0x696) < 3) {
      *(undefined4 *)((int)param_1 + 0x32) = 0x13100;
    }
    else {
      *(undefined4 *)((int)param_1 + 0x32) = 0x13038;
    }
    goto LAB_0001356c;
  }
  iVar3 = FUN_00012a0a(param_1);
  if (iVar3 == 0) {
    FUN_0000fd16(param_1);
    bVar2 = true;
    goto LAB_0001356c;
  }
  iVar3 = *param_1;
  switch(*(undefined1 *)((int)param_1 + 0x686)) {
  case 0:
    sVar7 = FUN_00018732(piVar1,*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc));
    sVar8 = FUN_000023c4((int)sVar7);
    param_1[0x1ab] = -(sVar8 * 30000 >> 0xc);
    param_1[0x1ac] = 0;
    sVar7 = FUN_00002426((int)sVar7);
    param_1[0x1ad] = sVar7 * 30000 >> 0xc;
    *(undefined1 *)((int)param_1 + 0x686) = 1;
  case 1:
    iVar4 = piVar1[1];
    iVar10 = param_1[0x1ab];
    iVar11 = piVar1[2];
    iVar9 = piVar1[3];
    iVar5 = param_1[0x1ad];
    uVar6 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc),piVar1[1],0,
                         piVar1[3]);
    *(undefined4 *)((int)param_1 + 0x6a6) = uVar6;
    if (((*(int *)((int)param_1 + 0x6a6) < 0x88b9) && (19999 < *(int *)(iVar3 + 8))) &&
       (29999 < *(int *)((int)param_1 + 0x6a6))) {
      *(undefined1 *)((int)param_1 + 0x686) = 2;
    }
    else {
      iVar11 = 25000;
    }
    FUN_000129b4(param_1,iVar10 + iVar4,iVar11,iVar5 + iVar9);
    FUN_0000fd16(param_1);
    bVar2 = true;
    break;
  case 2:
    uVar6 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc),piVar1[1],0,
                         piVar1[3]);
    *(undefined4 *)((int)param_1 + 0x6a6) = uVar6;
    iVar10 = piVar1[1];
    iVar11 = piVar1[2];
    iVar9 = piVar1[3];
    uVar6 = *(undefined4 *)((int)param_1 + 0x2e);
    *(undefined4 *)((int)param_1 + 0x2e) = 0xffff63c0;
    iVar4 = *piVar1;
    if (iVar4 != 0) {
      if (*(int *)((int)param_1 + 0x62a) == 0) {
        sVar7 = (short)((*(int *)((int)param_1 + 0x6a6) << 6) / 0xd5c);
      }
      else {
        sVar7 = (short)((*(int *)((int)param_1 + 0x6a6) << 6) / *(int *)((int)param_1 + 0x62a));
      }
      iVar10 = (int)*(short *)(iVar4 + 10) * (int)sVar7 + iVar10;
      iVar9 = (int)*(short *)(iVar4 + 0x12) * (int)sVar7 + iVar9;
    }
    if (*(int *)(iVar3 + 8) < 10000) {
      iVar4 = FUN_000038e8(param_1[2]);
      iVar5 = FUN_000038e8(param_1[4]);
      if (iVar5 < iVar4) {
        iVar4 = iVar10 - *(int *)(iVar3 + 4);
        sVar7 = *(short *)((int)param_1 + 10);
LAB_00013458:
        sVar7 = (short)(iVar4 / (int)sVar7);
      }
      else {
        if (param_1[4] != 0) {
          iVar4 = iVar9 - *(int *)(iVar3 + 0xc);
          sVar7 = *(short *)((int)param_1 + 0x12);
          goto LAB_00013458;
        }
        sVar7 = 0xf;
      }
      iVar3 = FUN_00003908((*(int *)(iVar3 + 8) + (int)*(short *)((int)param_1 + 0xe) * (int)sVar7)
                           - (sVar7 * 0x5b * (int)sVar7 >> 7));
      if (iVar3 < 200) {
        *(undefined1 *)(param_1 + 0x1a1) = 1;
        *(undefined1 *)((int)param_1 + 0x686) = 3;
      }
    }
    FUN_000129b4(param_1,iVar10,iVar11,iVar9);
    *(undefined4 *)((int)param_1 + 0x2e) = 0;
    FUN_0000fd16(param_1);
    bVar2 = true;
    *(undefined4 *)((int)param_1 + 0x2e) = uVar6;
    break;
  case 3:
    uVar6 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc),piVar1[1],0,
                         piVar1[3]);
    *(undefined4 *)((int)param_1 + 0x6a6) = uVar6;
    FUN_000129b4(param_1,*(int *)(iVar3 + 4) + param_1[2] * 0x2000,25000,
                 *(int *)(iVar3 + 0xc) + param_1[4] * 0x2000);
    FUN_0000fd16(param_1);
    bVar2 = true;
    if (40000 < *(int *)((int)param_1 + 0x6a6)) {
      *(undefined1 *)((int)param_1 + 0x686) = 0;
      FUN_00012b66(param_1);
      FUN_00011ec6(param_1);
    }
  }
  FUN_00012aba(param_1,0xd5c);
LAB_0001356c:
  if (!bVar2) {
    if (*(int *)(&DAT_00020598 + *(short *)(param_1 + 0x185) * 4) == 0) {
      FUN_0000fd16(param_1);
    }
    else {
      (**(code **)(&DAT_00020598 + *(short *)(param_1 + 0x185) * 4))(param_1);
    }
  }
  return;
}



// FUN_0001359a @ 0x1359a

void FUN_0001359a(undefined4 param_1,int param_2)

{
  undefined4 *puVar1;
  
  puVar1 = (undefined4 *)FUN_000021a4(0x3a);
  *puVar1 = param_1;
  puVar1[1] = *(undefined4 *)(&DAT_0002074c + param_2 * 4);
  *(undefined4 *)((int)puVar1 + 0x36) = 0;
  return;
}



// FUN_000135c8 @ 0x135c8

void FUN_000135c8(int param_1)

{
  *(ushort *)(param_1 + 0x22) = *(short *)(param_1 + 0x22) + 0x40U & 0x3fff;
  return;
}



// FUN_000135de @ 0x135de

int FUN_000135de(uint param_1,uint param_2)

{
  return ((param_2 & 0xffff) * (param_1 >> 0x10) + (param_1 & 0xffff) * (param_2 >> 0x10)) * 0x10000
         + (param_2 & 0xffff) * (param_1 & 0xffff);
}



// FUN_0001361e @ 0x1361e

void FUN_0001361e(undefined4 param_1)

{
  int iVar1;
  int iVar2;
  undefined4 *puVar3;
  int *piVar4;
  
  DAT_0002c5b8 = param_1;
  iVar2 = 1;
  puVar3 = &DAT_0002c5b8;
  piVar4 = &DAT_0002c5bc;
  do {
    iVar1 = FUN_000135de(0x1df5e0d,*puVar3);
    *piVar4 = iVar1 + 1;
    iVar2 = iVar2 + 1;
    puVar3 = puVar3 + 1;
    piVar4 = piVar4 + 1;
  } while (iVar2 < 0x37);
  DAT_0002c5b4 = 0x2c690;
  iVar2 = 0;
  do {
    FUN_00013672();
    iVar2 = iVar2 + 1;
  } while (iVar2 < 10000);
  return;
}



// FUN_00013652 @ 0x13652

void FUN_00013652(void)

{
  int unaff_D2;
  int iVar1;
  undefined4 *unaff_A2;
  int *unaff_A3;
  char in_NF;
  char in_VF;
  
  while (in_VF == in_NF) {
    iVar1 = FUN_000135de(0x1df5e0d,*unaff_A2);
    *unaff_A3 = iVar1 + 1;
    unaff_D2 = unaff_D2 + 1;
    in_NF = 0x36 - unaff_D2 < 0;
    unaff_A2 = unaff_A2 + 1;
    unaff_A3 = unaff_A3 + 1;
    in_VF = SBORROW4(0x36,unaff_D2);
  }
  DAT_0002c5b4 = 0x2c690;
  iVar1 = 0;
  do {
    FUN_00013672();
    iVar1 = iVar1 + 1;
  } while (iVar1 < 10000);
  return;
}



// FUN_00013672 @ 0x13672

uint FUN_00013672(void)

{
  uint uVar1;
  uint uVar2;
  uint *puVar3;
  uint *puVar4;
  uint *puVar5;
  
  puVar4 = (uint *)((int)DAT_0002c5b4 + 4);
  if ((uint *)0x2c693 < puVar4) {
    puVar4 = (uint *)((int)DAT_0002c5b4 + -0xd8);
  }
  puVar5 = puVar4 + 0x17;
  if ((uint *)0x2c693 < puVar5) {
    puVar5 = puVar4 + -0x20;
  }
  puVar3 = puVar4 + 0x36;
  if ((uint *)0x2c693 < puVar3) {
    puVar3 = puVar4 + -1;
  }
  uVar1 = *puVar5;
  uVar2 = *puVar3;
  DAT_0002c5b4 = puVar4;
  *puVar4 = uVar2 + uVar1;
  return uVar2 + uVar1 >> 0x10;
}



// FUN_0001369e @ 0x1369e

uint FUN_0001369e(void)

{
  int iVar1;
  uint uVar2;
  uint *in_D0;
  uint *puVar3;
  uint *in_A1;
  int *unaff_A2;
  
  puVar3 = in_A1 + 0x36;
  if (in_D0 <= puVar3) {
    puVar3 = in_A1 + -1;
  }
  iVar1 = *unaff_A2;
  uVar2 = *puVar3;
  *in_A1 = uVar2 + iVar1;
  return uVar2 + iVar1 >> 0x10;
}



// FUN_000136be @ 0x136be

undefined4 FUN_000136be(void)

{
  undefined2 uVar1;
  undefined2 uVar2;
  
  uVar1 = FUN_00013672();
  uVar2 = FUN_00013672();
  return CONCAT22(uVar2,uVar1);
}



// FUN_000136e2 @ 0x136e2

void FUN_000136e2(int param_1)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0xa2);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x112000;
  piVar1[1] = DAT_00020774;
  return;
}



// FUN_0001370c @ 0x1370c

undefined4 FUN_0001370c(undefined4 param_1,int param_2)

{
  DAT_0002c694 = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x136c2;
  return 0;
}



// FUN_0001372e @ 0x1372e

int FUN_0001372e(int param_1,int param_2,int param_3,undefined2 param_4,undefined4 param_5,
                undefined4 param_6,undefined4 param_7,undefined4 param_8)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002c694,param_6,param_7,param_8,0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(short *)(iVar2 + 0x36) = (short)(param_1 << (*(ushort *)((int)piVar1 + 0x36) & 0x3f));
    *(short *)(iVar2 + 0x34) = (short)(param_2 << (*(ushort *)((int)piVar1 + 0x36) & 0x3f));
    *(undefined2 *)(iVar2 + 0x32) = param_4;
    *(int *)(iVar2 + 0x38) = DAT_00021020 + param_3;
  }
  return iVar2;
}



// FUN_00013798 @ 0x13798

void FUN_00013798(undefined4 param_1,undefined4 param_2,undefined4 param_3,int *param_4)

{
  int *piVar1;
  int iVar2;
  int *piVar3;
  short sVar4;
  
  piVar3 = (int *)FUN_0001372e(0x48,0,0,0,0,param_1,param_2,param_3);
  if (piVar3 != (int *)0x0) {
    iVar2 = *piVar3;
    sVar4 = 0;
    do {
      piVar1 = piVar3 + sVar4 * 3 + 0x10;
      *piVar1 = *(int *)(iVar2 + 4);
      piVar1[1] = *(int *)(iVar2 + 8);
      piVar1[2] = *(int *)(iVar2 + 0xc);
      sVar4 = sVar4 + 1;
    } while (sVar4 < 8);
    *(uint *)(*piVar3 + 0x14) = *(uint *)(*piVar3 + 0x14) & 0xffefffff;
    *(undefined2 *)(*piVar3 + 0x36) = 0;
    piVar3[0xf] = *param_4;
    *(char *)(*param_4 + 0x29) = *(char *)(*param_4 + 0x29) + '\x01';
  }
  return;
}



// FUN_00013812 @ 0x13812

void FUN_00013812(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x3c) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x3c) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  return;
}



// FUN_00013828 @ 0x13828

void FUN_00013828(int *param_1)

{
  int *piVar1;
  int iVar2;
  int *piVar3;
  ushort uVar4;
  
  if (param_1[0xf] == 0) {
    if (param_1[0xe] != DAT_00021020) {
      return;
    }
  }
  else {
    piVar3 = (int *)param_1[0xf];
    if ((((*(byte *)((int)piVar3 + 0x17) & 2) == 0) && ((*(byte *)(*piVar3 + 0x698) & 1) != 0)) &&
       (DAT_00021058 != 1)) {
      uVar4 = *(short *)(param_1 + 0x28) - 1U & 7;
      *(ushort *)(param_1 + 0x28) = uVar4;
      piVar1 = param_1 + (short)uVar4 * 3 + 0x10;
      *piVar1 = piVar3[1];
      piVar1[1] = piVar3[2];
      piVar1[2] = piVar3[3];
      iVar2 = *param_1;
      param_1[2] = piVar3[1] - *(int *)(iVar2 + 4);
      param_1[3] = piVar3[2] - *(int *)(iVar2 + 8);
      param_1[4] = piVar3[3] - *(int *)(iVar2 + 0xc);
      return;
    }
  }
  FUN_0000605a(*param_1);
  return;
}



// FUN_000138c0 @ 0x138c0

undefined4 FUN_000138c0(int *param_1)

{
  int *piVar1;
  ushort uVar4;
  int iVar2;
  short sVar5;
  int iVar3;
  int iVar6;
  
  iVar3 = *param_1;
  piVar1 = *(int **)(iVar3 + 0x46);
  if (*(short *)((int)param_1 + 0x32) != 0) {
    *(ushort *)((int)param_1 + 0x22) = -DAT_00023d3e & 0x3fff;
  }
  if (*(short *)((int)param_1 + 0x36) != 0) {
    iVar6 = (int)*(short *)((int)param_1 + 0x36);
    iVar2 = -iVar6;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 1;
    }
    *piVar1 = iVar2 >> 1;
    iVar2 = iVar6 * -3;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 7;
    }
    piVar1[3] = iVar2 >> 3;
    iVar2 = -iVar6;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 3;
    }
    piVar1[6] = iVar2 >> 2;
    iVar2 = iVar6;
    if (iVar6 < 0) {
      iVar2 = iVar6 + 3;
    }
    piVar1[0xc] = iVar2 >> 2;
    iVar2 = iVar6 * 3;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 7;
    }
    piVar1[0xf] = iVar2 >> 3;
    if (iVar6 < 0) {
      iVar6 = iVar6 + 1;
    }
    piVar1[0x12] = iVar6 >> 1;
    *(undefined2 *)((int)param_1 + 0x36) = 0;
  }
  if (param_1[0xe] != DAT_00021020) {
    uVar4 = FUN_00013672();
    iVar2 = (uint)(uVar4 & 0xfff) * (int)*(short *)(param_1 + 0xd);
    if (iVar2 < 0) {
      iVar2 = iVar2 + 0x1fff;
    }
    piVar1[4] = iVar2 >> 0xd;
    uVar4 = FUN_00013672();
    sVar5 = *(short *)(param_1 + 0xd);
    if (sVar5 < 0) {
      sVar5 = sVar5 + 1;
    }
    iVar2 = (uint)(uVar4 & 0xfff) * (int)*(short *)(param_1 + 0xd);
    if (iVar2 < 0) {
      iVar2 = iVar2 + 0x1fff;
    }
    piVar1[10] = (iVar2 >> 0xd) + (int)(sVar5 >> 1);
    uVar4 = FUN_00013672();
    iVar2 = (uint)(uVar4 & 0xfff) * (int)*(short *)(param_1 + 0xd);
    if (iVar2 < 0) {
      iVar2 = iVar2 + 0x1fff;
    }
    piVar1[0x10] = iVar2 >> 0xd;
    uVar4 = FUN_00013672();
    iVar2 = (uint)(uVar4 & 0xfff) * (int)*(short *)(param_1 + 0xd);
    if (iVar2 < 0) {
      iVar2 = iVar2 + 0x1fff;
    }
    piVar1[0x16] = iVar2 >> 0xd;
    sVar5 = FUN_00003908(DAT_00021020 - *(int *)(iVar3 + 0x42));
    if (sVar5 < 0x2d) {
      iVar3 = (int)sVar5;
      piVar1[4] = (piVar1[4] * iVar3) / 0x2d;
      piVar1[10] = (piVar1[10] * iVar3) / 0x2d;
      piVar1[0x10] = (piVar1[0x10] * iVar3) / 0x2d;
      piVar1[0x16] = (piVar1[0x16] * iVar3) / 0x2d;
    }
  }
  return 0;
}



// FUN_00013a6a @ 0x13a6a

undefined4 FUN_00013a6a(int *param_1)

{
  int *piVar1;
  short sVar2;
  uint uVar3;
  ushort uVar4;
  int iVar5;
  
  piVar1 = *(int **)(*param_1 + 0x46);
  sVar2 = *(short *)(param_1 + 0x28);
  iVar5 = (int)sVar2;
  *piVar1 = param_1[iVar5 * 3 + 0x10];
  piVar1[1] = param_1[iVar5 * 3 + 0x11] + 0x24;
  piVar1[2] = param_1[iVar5 * 3 + 0x12];
  piVar1[0x12] = param_1[iVar5 * 3 + 0x10];
  piVar1[0x13] = param_1[iVar5 * 3 + 0x11] + -0x24;
  piVar1[0x14] = param_1[iVar5 * 3 + 0x12];
  uVar3 = (ushort)(sVar2 + 2) & 7;
  piVar1[6] = param_1[uVar3 * 3 + 0x10];
  piVar1[7] = param_1[uVar3 * 3 + 0x11] + 0x12;
  piVar1[8] = param_1[uVar3 * 3 + 0x12];
  piVar1[0xc] = param_1[uVar3 * 3 + 0x10];
  piVar1[0xd] = param_1[uVar3 * 3 + 0x11] + -0x12;
  piVar1[0xe] = param_1[uVar3 * 3 + 0x12];
  uVar4 = FUN_00013672();
  uVar3 = (ushort)(sVar2 + (uVar4 & 3) + 2) & 7;
  piVar1[3] = param_1[uVar3 * 3 + 0x10];
  piVar1[4] = param_1[uVar3 * 3 + 0x11] + 0x1b;
  piVar1[5] = param_1[uVar3 * 3 + 0x12];
  uVar4 = FUN_00013672();
  uVar3 = (ushort)(sVar2 + (uVar4 & 3) + 2) & 7;
  piVar1[0xf] = param_1[uVar3 * 3 + 0x10];
  piVar1[0x10] = param_1[uVar3 * 3 + 0x11] + -0x1b;
  piVar1[0x11] = param_1[uVar3 * 3 + 0x12];
  uVar4 = FUN_00013672();
  uVar3 = (ushort)(sVar2 + (uVar4 & 3)) & 7;
  piVar1[0x15] = param_1[uVar3 * 3 + 0x10];
  piVar1[0x16] = param_1[uVar3 * 3 + 0x11];
  piVar1[0x17] = param_1[uVar3 * 3 + 0x12];
  uVar4 = FUN_00013672();
  uVar3 = (ushort)(sVar2 + (uVar4 & 3) + 4) & 7;
  piVar1[9] = param_1[uVar3 * 3 + 0x10];
  piVar1[10] = param_1[uVar3 * 3 + 0x11];
  piVar1[0xb] = param_1[uVar3 * 3 + 0x12];
  return 0;
}



// FUN_00013bba @ 0x13bba

void FUN_00013bba(int param_1)

{
  if (*(int *)(param_1 + 0x3c) == 0) {
    FUN_000138c0(param_1);
  }
  else {
    FUN_00013a6a(param_1);
  }
  return;
}



// FUN_00013bd6 @ 0x13bd6

void FUN_00013bd6(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x32);
  *piVar1 = param_1;
  *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 0x10;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_00013c0e @ 0x13c0e

undefined4 FUN_00013c0e(int param_1)

{
  *(ushort *)(param_1 + 0x22) = -DAT_00023d3e & 0x3fff;
  return 0;
}



// FUN_00013c26 @ 0x13c26

int * FUN_00013c26(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0xd6);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x170000;
  if (param_1 == DAT_0002c826) {
    *(ushort *)(param_1 + 0x16) = *(ushort *)(param_1 + 0x16) | 0x2000;
  }
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return piVar1;
}



// FUN_00013c70 @ 0x13c70

undefined4 FUN_00013c70(undefined4 param_1,int param_2)

{
  ushort uVar1;
  
  uVar1 = 0;
  do {
    if ((&DAT_0002c81a)[(short)uVar1] == 0) {
      (&DAT_0002c81a)[(short)uVar1] = param_1;
      break;
    }
    uVar1 = uVar1 + 1;
  } while (uVar1 < 4);
  if (2 < uVar1) {
    *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x13c06;
  }
  return 0;
}



// FUN_00013cbe @ 0x13cbe

void FUN_00013cbe(void)

{
  int iVar1;
  undefined2 uVar2;
  
  uVar2 = DAT_00021058;
  DAT_00021058 = 0xffff;
  while( true ) {
    iVar1 = (short)DAT_0002c698 * 0x18;
    if (DAT_00021020 < *(int *)(&DAT_0002c6ae + iVar1)) break;
    if (*(int *)(&DAT_0002c6ae + iVar1) == DAT_00021020) {
      FUN_00013d80((&DAT_0002c69a)[iVar1],(int)*(short *)(&DAT_0002c69e + iVar1),
                   (int)*(short *)(&DAT_0002c6a0 + iVar1),(int)(char)(&DAT_0002c69b)[iVar1],
                   (int)(char)(&DAT_0002c69c)[iVar1],*(undefined4 *)(&DAT_0002c6a2 + iVar1),
                   *(undefined4 *)(&DAT_0002c6a6 + iVar1),*(undefined4 *)(&DAT_0002c6aa + iVar1));
    }
    DAT_0002c698 = DAT_0002c698 + 1 & 0xf;
  }
  DAT_00021058 = uVar2;
  return;
}



// FUN_00013d54 @ 0x13d54

void FUN_00013d54(void)

{
  *(undefined4 *)(&DAT_0002c6ae + (short)DAT_0002c698 * 0x18) = 0x7fffffff;
  DAT_0002c698 = DAT_0002c698 + 1 & 0xf;
  return;
}



// FUN_00013d80 @ 0x13d80

int FUN_00013d80(int param_1,undefined2 param_2,undefined2 param_3,undefined1 param_4,int param_5,
                undefined4 param_6,undefined4 param_7,undefined4 param_8)

{
  int iVar1;
  int *piVar2;
  short sVar3;
  
  if (DAT_00021058 == 0) {
    iVar1 = (short)DAT_0002c698 * 0x18;
    (&DAT_0002c69a)[iVar1] = (char)param_1;
    *(undefined2 *)(&DAT_0002c69e + iVar1) = param_2;
    *(undefined2 *)(&DAT_0002c6a0 + iVar1) = param_3;
    (&DAT_0002c69c)[iVar1] = (char)param_5;
    (&DAT_0002c69b)[iVar1] = param_4;
    *(undefined4 *)(&DAT_0002c6a2 + iVar1) = param_6;
    *(undefined4 *)(&DAT_0002c6a6 + iVar1) = param_7;
    *(undefined4 *)(&DAT_0002c6aa + iVar1) = param_8;
    *(undefined4 *)(&DAT_0002c6ae + iVar1) = DAT_00021020;
    DAT_0002c698 = DAT_0002c698 + 1 & 0xf;
  }
  else if ((DAT_00021058 != -1) && (param_1 != 1)) {
    return 0;
  }
  if ((param_1 != 1) && ((DAT_00023d88 < 3 || (DAT_00023d88 == 8)))) {
    iVar1 = FUN_0000483e(param_6,param_7,param_8);
    if (iVar1 < 10000) {
      DAT_00021034 = 0x1e;
    }
    else if (iVar1 < 40000) {
      DAT_00021032 = 0xf;
    }
  }
  piVar2 = (int *)FUN_00005f84((&DAT_0002c81a)[param_1],param_6,param_7,param_8,0,0,0,0);
  if (piVar2 == (int *)0x0) {
    return 0;
  }
  iVar1 = *piVar2;
  *(char *)(iVar1 + 0x32) = (char)param_1;
  *(undefined2 *)(iVar1 + 0x34) = param_2;
  *(undefined2 *)(iVar1 + 0x36) = param_3;
  *(char *)(iVar1 + 0x39) = (char)param_5;
  *(undefined1 *)(iVar1 + 0x38) = param_4;
  (**(code **)(&DAT_000205bc + param_1 * 4))(piVar2);
  if (param_5 != 0) {
    sVar3 = 2;
    do {
      FUN_0001b5a2(param_6,param_7,param_8,0);
      sVar3 = sVar3 + -1;
    } while (sVar3 != -1);
  }
  return iVar1;
}



// FUN_00013ee0 @ 0x13ee0

bool FUN_00013ee0(int *param_1,int param_2)

{
  int iVar1;
  short sVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  undefined2 uVar6;
  bool bVar7;
  undefined4 uVar8;
  undefined4 uVar9;
  undefined4 uVar10;
  
  uVar6 = 1;
  iVar1 = *param_1;
  if (iVar1 == 0) {
    bVar7 = true;
    iVar5 = param_1[1];
    iVar3 = 0;
    iVar4 = param_1[3];
    sVar2 = FUN_00015d92(param_1);
  }
  else {
    iVar5 = *(int *)(iVar1 + 8) + param_1[1];
    iVar3 = *(int *)(iVar1 + 0xc) + param_1[2];
    iVar4 = *(int *)(iVar1 + 0x10) + param_1[3];
    bVar7 = iVar3 - *(int *)(iVar1 + 0x2e) < 100;
    sVar2 = (short)*(char *)(iVar1 + 0x2c);
    if (bVar7) {
      if (*(int *)(iVar1 + 0x2e) == 0) {
        sVar2 = FUN_00015d92(param_1);
      }
      if (((sVar2 == 0x30) || (sVar2 == 0x60)) || (sVar2 == 0x70)) {
        iVar3 = *(int *)(iVar1 + 0x2e);
      }
      else {
        iVar3 = 0;
      }
    }
    else {
      sVar2 = 0;
    }
    if (((**(short **)(iVar1 + 4) == 0x24) || (**(short **)(iVar1 + 4) == 0x23)) &&
       ((*(byte *)(*(int *)((int)param_1 + 0x4a) + 0x13) & 2) != 0)) {
      uVar6 = 0;
    }
  }
  if (bVar7 == false) {
    uVar10 = 1;
    uVar6 = 0;
    uVar9 = 5;
    uVar8 = 2;
    goto LAB_00014036;
  }
  if (sVar2 != 0x40) {
    if (sVar2 < 0x41) {
      if (sVar2 != 0x20) {
        if (sVar2 < 0x21) {
          if (sVar2 == 0x10) {
            uVar10 = 0;
            uVar6 = 0;
            uVar9 = 0x4b;
            uVar8 = 0;
            goto LAB_00014036;
          }
        }
        else if (sVar2 == 0x30) goto LAB_00013ffc;
      }
    }
    else if ((sVar2 == 0x60) || ((0x60 < sVar2 && (sVar2 == 0x70)))) {
LAB_00013ffc:
      uVar10 = 0;
      uVar9 = 0x2d;
      param_2 = param_2 * 3;
      if (param_2 < 0) {
        param_2 = param_2 + 3;
      }
      param_2 = param_2 >> 2;
      uVar8 = 3;
      goto LAB_00014036;
    }
  }
  uVar10 = 0;
  uVar9 = 0x2d;
  param_2 = param_2 * 3;
  if (param_2 < 0) {
    param_2 = param_2 + 3;
  }
  param_2 = param_2 >> 2;
  uVar8 = 3;
LAB_00014036:
  FUN_00013d80(uVar8,param_2,uVar9,uVar6,uVar10,iVar5,iVar3,iVar4);
  return bVar7;
}



// FUN_00014048 @ 0x14048

void FUN_00014048(int *param_1)

{
  int *piVar1;
  int iVar2;
  ushort uVar3;
  ushort uVar4;
  short sVar5;
  int iVar6;
  int iVar7;
  int *piVar8;
  int *piVar9;
  
  iVar2 = *param_1;
  iVar6 = ((int)*(short *)(iVar2 + 0x34) << 8) / 100;
  piVar9 = *(int **)((int)param_1 + 0x46);
  uVar3 = FUN_00013672();
  sVar5 = 0;
  piVar8 = piVar9 + 1;
  do {
    *piVar9 = iVar6 * (*piVar9 >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
    *piVar8 = iVar6 * (*piVar8 >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
    if (sVar5 < 6) {
      uVar4 = FUN_00013672();
      iVar7 = (*piVar9 * 2 + -0x3f + (uVar4 & 0xffff007f)) * 0x100;
      if (iVar7 < 0) {
        iVar7 = iVar7 + 1;
      }
      *(int *)(iVar2 + 0x3a + sVar5 * 4) = iVar7 >> 1;
      uVar4 = FUN_00013672();
      iVar7 = (*piVar8 * 2 + -0x3f + (uVar4 & 0xffff007f)) * 0x100;
      if (iVar7 < 0) {
        iVar7 = iVar7 + 1;
      }
      *(int *)(iVar2 + 0x6e + sVar5 * 4) = iVar7 >> 1;
      iVar7 = (int)sVar5;
      if (((short)(uVar3 & 1) + iVar7 & 1U) != 0) {
        piVar1 = (int *)(iVar2 + 0x3a + iVar7 * 4);
        *piVar1 = *(int *)(iVar2 + 0x3a + iVar7 * 4) * 2 + *piVar1;
        piVar1 = (int *)(iVar2 + 0x6e + iVar7 * 4);
        *piVar1 = *(int *)(iVar2 + 0x6e + iVar7 * 4) * 2 + *piVar1;
      }
    }
    else {
      uVar4 = FUN_00013672();
      iVar7 = (*piVar9 * 2 + -0x1f + (uVar4 & 0xffff003f)) * 0x100;
      if (iVar7 < 0) {
        iVar7 = iVar7 + 1;
      }
      *(int *)(iVar2 + 0x3a + sVar5 * 4) = iVar7 >> 1;
      uVar4 = FUN_00013672();
      iVar7 = (*piVar8 * 2 + -0x1f + (uVar4 & 0xffff003f)) * 0x100;
      if (iVar7 < 0) {
        iVar7 = iVar7 + 1;
      }
      *(int *)(iVar2 + 0x6e + sVar5 * 4) = iVar7 >> 1;
    }
    sVar5 = sVar5 + 1;
    piVar8 = piVar8 + 3;
    piVar9 = piVar9 + 3;
  } while (sVar5 < 0xc);
  *(undefined2 *)((int)param_1 + 0x36) = 0;
  param_1[8] = param_1[8] * iVar6 >> 8;
  return;
}



// FUN_00014184 @ 0x14184

void FUN_00014184(int *param_1)

{
  int iVar1;
  int iVar2;
  short sVar3;
  int iVar4;
  int *piVar5;
  int *piVar6;
  
  iVar1 = *param_1;
  iVar4 = ((int)*(short *)(iVar1 + 0x34) << 8) / 100;
  piVar6 = *(int **)((int)param_1 + 0x46);
  sVar3 = 0;
  piVar5 = piVar6 + 2;
  do {
    *piVar6 = iVar4 * (*piVar6 >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
    *piVar5 = iVar4 * (*piVar5 >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
    iVar2 = *piVar6 * 0x200;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 1;
    }
    *(int *)(iVar1 + 0x3a + sVar3 * 4) = iVar2 >> 1;
    iVar2 = *piVar5 * 0x200;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 1;
    }
    *(int *)(iVar1 + 0x6e + sVar3 * 4) = iVar2 >> 1;
    sVar3 = sVar3 + 1;
    piVar5 = piVar5 + 3;
    piVar6 = piVar6 + 3;
  } while (sVar3 < 6);
  *(undefined2 *)((int)param_1 + 0x36) = 0;
  param_1[8] = param_1[8] * iVar4 >> 8;
  return;
}



// FUN_00014212 @ 0x14212

void FUN_00014212(int *param_1)

{
  int iVar1;
  int iVar2;
  int iVar3;
  short sVar4;
  int iVar5;
  int iVar6;
  
  iVar1 = *param_1;
  iVar5 = ((int)*(short *)(iVar1 + 0x34) << 8) / 100;
  iVar2 = *(int *)((int)param_1 + 0x46);
  sVar4 = 0;
  do {
    if (sVar4 != 2) {
      iVar6 = (int)sVar4;
      iVar3 = iVar6 * 0xc;
      *(int *)(iVar2 + iVar3) =
           iVar5 * (*(int *)(iVar2 + iVar3) >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
      *(int *)(iVar2 + 8 + iVar3) =
           iVar5 * (*(int *)(iVar2 + 8 + iVar3) >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
      *(int *)(iVar1 + 0x3a + iVar6 * 4) = (*(int *)(iVar2 + iVar3) << 9) / 3;
      *(int *)(iVar1 + 0x6e + iVar6 * 4) = (*(int *)(iVar2 + 8 + iVar3) << 9) / 3;
    }
    sVar4 = sVar4 + 1;
  } while (sVar4 < 4);
  *(undefined4 *)(iVar2 + 0x1c) = 0;
  *(undefined4 *)(iVar1 + 0x42) = 0;
  *(int *)(iVar1 + 0x76) = iVar5 * 0x500 >> 8;
  *(undefined2 *)((int)param_1 + 0x36) = 0;
  param_1[8] = param_1[8] * iVar5 >> 8;
  return;
}



// FUN_000142c4 @ 0x142c4

void FUN_000142c4(int *param_1)

{
  int iVar1;
  int iVar2;
  short sVar4;
  int iVar3;
  int iVar5;
  int iVar6;
  
  iVar1 = *param_1;
  sVar4 = *(short *)(iVar1 + 0x34);
  if (sVar4 < 0) {
    sVar4 = sVar4 + 3;
  }
  FUN_00013d80(2,(int)(sVar4 >> 2),2,0,0,param_1[1],param_1[2],param_1[3]);
  iVar5 = ((int)*(short *)(iVar1 + 0x34) << 8) / 100;
  iVar2 = *(int *)((int)param_1 + 0x46);
  sVar4 = 0;
  do {
    iVar3 = sVar4 * 0xc;
    *(int *)(iVar2 + iVar3) =
         iVar5 * (*(int *)(iVar2 + iVar3) >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
    *(int *)(iVar2 + 4 + iVar3) =
         iVar5 * (*(int *)(iVar2 + 4 + iVar3) >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
    *(int *)(iVar2 + 8 + iVar3) =
         iVar5 * (*(int *)(iVar2 + 8 + iVar3) >> (*(ushort *)((int)param_1 + 0x36) & 0x3f)) >> 8;
    iVar3 = *(int *)(iVar2 + iVar3) * 0x200;
    if (iVar3 < 0) {
      iVar3 = iVar3 + 1;
    }
    *(int *)(iVar1 + 0x3a + sVar4 * 4) = iVar3 >> 1;
    iVar6 = (int)sVar4;
    *(int *)(iVar1 + 0x6e + iVar6 * 4) = *(int *)(iVar2 + 4 + iVar6 * 0xc) / 6 << 4;
    iVar3 = *(int *)(iVar2 + 8 + iVar6 * 0xc) * 0x200;
    if (iVar3 < 0) {
      iVar3 = iVar3 + 1;
    }
    *(int *)(iVar1 + iVar6 * 4 + 0xa2) = iVar3 >> 1;
    *(undefined4 *)(iVar2 + 4 + sVar4 * 0xc) = 0;
    sVar4 = sVar4 + 1;
  } while (sVar4 < 0xd);
  *(undefined2 *)((int)param_1 + 0x36) = 0;
  param_1[8] = param_1[8] * iVar5 >> 8;
  *(undefined2 *)(iVar1 + 0x36) = 0x7fff;
  if (DAT_00021058 != 0) {
    *(byte *)(iVar1 + 0x33) = *(byte *)(iVar1 + 0x33) | 1;
  }
  return;
}



// FUN_000143da @ 0x143da

/* WARNING: Removing unreachable block (ram,0x000144ca) */
/* WARNING: Removing unreachable block (ram,0x000144b2) */
/* WARNING: Removing unreachable block (ram,0x000144e2) */

undefined4 FUN_000143da(int *param_1)

{
  int iVar1;
  undefined4 uVar2;
  short sVar3;
  int *piVar4;
  int *piVar5;
  int iVar6;
  int *piVar7;
  int *piVar8;
  
  iVar1 = *param_1;
  if ((DAT_00021058 == 1) || (*(short *)((int)param_1 + 0x36) == 0)) {
    if ((*(short *)((int)param_1 + 0x36) == 0) && (*(char *)(param_1 + 0xe) != '\0')) {
      FUN_0001372e(*(short *)(param_1 + 0xd) * 3,*(short *)(param_1 + 0xd) * 6,0x2a3,1,0,
                   *(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                   *(undefined4 *)(iVar1 + 0xc));
    }
    uVar2 = 1;
  }
  else {
    piVar8 = *(int **)(iVar1 + 0x46);
    sVar3 = 0;
    piVar7 = piVar8 + 1;
    piVar4 = (int *)((int)param_1 + 0x6e);
    piVar5 = (int *)((int)param_1 + 0x3a);
    do {
      *piVar8 = (*piVar5 >> 8) + *piVar8;
      *piVar7 = (*piVar4 >> 8) + *piVar7;
      iVar6 = *piVar5;
      if (iVar6 < 0) {
        iVar6 = iVar6 + 1;
      }
      *piVar5 = iVar6 >> 1;
      iVar6 = *piVar4;
      if (iVar6 < 0) {
        iVar6 = iVar6 + 1;
      }
      *piVar4 = iVar6 >> 1;
      sVar3 = sVar3 + 1;
      piVar7 = piVar7 + 3;
      piVar8 = piVar8 + 3;
      piVar4 = piVar4 + 1;
      piVar5 = piVar5 + 1;
    } while (sVar3 < 0xc);
    if (DAT_0002100e == 8) {
      iVar6 = *(int *)(iVar1 + 0x4a);
      sVar3 = *(short *)(iVar1 + 0x3c);
      while (sVar3 = sVar3 + -1, sVar3 != -1) {
        *(char *)(iVar6 + 0xc) = (char)((int)((uint)*(byte *)(iVar6 + 0xc) * 6) >> 3);
        *(char *)(iVar6 + 0xd) = (char)((int)((uint)*(byte *)(iVar6 + 0xd) * 6) >> 3);
        *(char *)(iVar6 + 0xe) = (char)((int)((uint)*(byte *)(iVar6 + 0xe) * 6) >> 3);
        if (iVar6 == *(int *)(DAT_00023fac + (uint)*(byte *)(iVar6 + 0xf) * 4)) {
          FUN_00001bb0(iVar6);
        }
        else {
          *(undefined1 *)(iVar6 + 0xf) = 0;
        }
        iVar6 = iVar6 + 0x20;
      }
    }
    uVar2 = 0;
  }
  return uVar2;
}



// FUN_0001451a @ 0x1451a

undefined4 FUN_0001451a(int *param_1)

{
  undefined4 uVar1;
  int iVar2;
  short sVar3;
  int iVar4;
  int *piVar5;
  int *piVar6;
  
  iVar2 = *param_1;
  if ((DAT_00021058 == 1) || (*(short *)((int)param_1 + 0x36) == 0)) {
    uVar1 = 1;
  }
  else {
    if (*(short *)((int)param_1 + 0x36) == 0x47) {
      FUN_00013d80(1,(int)*(short *)(param_1 + 0xd),(int)*(short *)((int)param_1 + 0x36),0,0,
                   *(undefined4 *)(iVar2 + 4),*(undefined4 *)(iVar2 + 8),
                   *(undefined4 *)(iVar2 + 0xc));
    }
    piVar6 = *(int **)(iVar2 + 0x46);
    sVar3 = 0;
    piVar5 = piVar6 + 2;
    do {
      iVar4 = (int)sVar3;
      *piVar6 = (*(int *)((int)param_1 + iVar4 * 4 + 0x3a) >> 8) + *piVar6;
      *piVar5 = (*(int *)((int)param_1 + iVar4 * 4 + 0x6e) >> 8) + *piVar5;
      iVar2 = *(int *)((int)param_1 + iVar4 * 4 + 0x3a) +
              *(int *)((int)param_1 + iVar4 * 4 + 0x3a) * 2;
      if (iVar2 < 0) {
        iVar2 = iVar2 + 3;
      }
      *(int *)((int)param_1 + iVar4 * 4 + 0x3a) = iVar2 >> 2;
      iVar4 = (int)sVar3;
      iVar2 = *(int *)((int)param_1 + iVar4 * 4 + 0x6e) +
              *(int *)((int)param_1 + iVar4 * 4 + 0x6e) * 2;
      if (iVar2 < 0) {
        iVar2 = iVar2 + 3;
      }
      *(int *)((int)param_1 + iVar4 * 4 + 0x6e) = iVar2 >> 2;
      sVar3 = sVar3 + 1;
      piVar5 = piVar5 + 3;
      piVar6 = piVar6 + 3;
    } while (sVar3 < 6);
    uVar1 = 0;
  }
  return uVar1;
}



// FUN_000145b2 @ 0x145b2

undefined4 FUN_000145b2(void)

{
  int iVar1;
  int in_D0;
  short unaff_D2w;
  int iVar2;
  int in_A0;
  int *in_A1;
  int *unaff_A2;
  int unaff_A3;
  int unaff_A4;
  
  do {
    in_D0 = in_D0 + 3;
    do {
      *(int *)(unaff_A3 + in_A0 * 4) = in_D0 >> 2;
      unaff_D2w = unaff_D2w + 1;
      in_A1 = in_A1 + 3;
      unaff_A2 = unaff_A2 + 3;
      if (5 < unaff_D2w) {
        return 0;
      }
      iVar2 = (int)unaff_D2w;
      *unaff_A2 = (*(int *)(unaff_A4 + iVar2 * 4) >> 8) + *unaff_A2;
      *in_A1 = (*(int *)(unaff_A3 + iVar2 * 4) >> 8) + *in_A1;
      iVar1 = *(int *)(unaff_A4 + iVar2 * 4) + *(int *)(unaff_A4 + iVar2 * 4) * 2;
      if (iVar1 < 0) {
        iVar1 = iVar1 + 3;
      }
      *(int *)(unaff_A4 + iVar2 * 4) = iVar1 >> 2;
      in_A0 = (int)unaff_D2w;
      in_D0 = *(int *)(unaff_A3 + in_A0 * 4) + *(int *)(unaff_A3 + in_A0 * 4) * 2;
    } while (-1 < in_D0);
  } while( true );
}



// FUN_000145d2 @ 0x145d2

undefined4 FUN_000145d2(int *param_1)

{
  int *piVar1;
  int iVar2;
  int iVar3;
  short sVar4;
  int iVar5;
  
  if ((DAT_00021058 != 1) && (*(short *)((int)param_1 + 0x36) != 0)) {
    iVar2 = *(int *)(*param_1 + 0x46);
    *(int *)((int)param_1 + 0x76) = *(int *)((int)param_1 + 0x76) + -0x5b;
    iVar3 = *(int *)(iVar2 + 0x1c) + (*(int *)((int)param_1 + 0x76) >> 4);
    *(int *)(iVar2 + 0x1c) = iVar3;
    if (-1 < iVar3) {
      sVar4 = 0;
      do {
        if (sVar4 != 2) {
          iVar5 = (int)sVar4;
          piVar1 = (int *)(iVar2 + iVar5 * 0xc);
          *piVar1 = (*(int *)((int)param_1 + iVar5 * 4 + 0x3a) >> 8) + *piVar1;
          piVar1 = (int *)(iVar2 + 8 + iVar5 * 0xc);
          *piVar1 = (*(int *)((int)param_1 + iVar5 * 4 + 0x6e) >> 8) + *piVar1;
          iVar3 = *(int *)((int)param_1 + iVar5 * 4 + 0x3a) +
                  *(int *)((int)param_1 + iVar5 * 4 + 0x3a) * 2;
          if (iVar3 < 0) {
            iVar3 = iVar3 + 3;
          }
          *(int *)((int)param_1 + iVar5 * 4 + 0x3a) = iVar3 >> 2;
          iVar5 = (int)sVar4;
          iVar3 = *(int *)((int)param_1 + iVar5 * 4 + 0x6e) +
                  *(int *)((int)param_1 + iVar5 * 4 + 0x6e) * 2;
          if (iVar3 < 0) {
            iVar3 = iVar3 + 3;
          }
          *(int *)((int)param_1 + iVar5 * 4 + 0x6e) = iVar3 >> 2;
        }
        sVar4 = sVar4 + 1;
      } while (sVar4 < 4);
      return 0;
    }
  }
  return 1;
}



// FUN_00014654 @ 0x14654

undefined4 FUN_00014654(void)

{
  int *piVar1;
  int iVar2;
  int in_D0;
  short unaff_D2w;
  int iVar3;
  int in_A0;
  int in_A1;
  int unaff_A2;
  int unaff_A3;
  
  do {
    iVar2 = *(int *)(in_A1 + in_A0 * 4) + in_D0 * 2;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 3;
    }
    *(int *)(in_A1 + in_A0 * 4) = iVar2 >> 2;
    do {
      unaff_D2w = unaff_D2w + 1;
      if (3 < unaff_D2w) {
        return 0;
      }
    } while (unaff_D2w == 2);
    iVar3 = (int)unaff_D2w;
    piVar1 = (int *)(unaff_A2 + iVar3 * 0xc);
    *piVar1 = (*(int *)(unaff_A3 + iVar3 * 4) >> 8) + *piVar1;
    piVar1 = (int *)(unaff_A2 + 8 + iVar3 * 0xc);
    *piVar1 = (*(int *)(in_A1 + iVar3 * 4) >> 8) + *piVar1;
    iVar2 = *(int *)(unaff_A3 + iVar3 * 4) + *(int *)(unaff_A3 + iVar3 * 4) * 2;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 3;
    }
    *(int *)(unaff_A3 + iVar3 * 4) = iVar2 >> 2;
    in_A0 = (int)unaff_D2w;
    in_D0 = *(int *)(in_A1 + in_A0 * 4);
  } while( true );
}



// FUN_00014674 @ 0x14674

/* WARNING: Removing unreachable block (ram,0x0001479a) */
/* WARNING: Removing unreachable block (ram,0x000147ae) */
/* WARNING: Removing unreachable block (ram,0x000147c2) */

undefined4 FUN_00014674(int *param_1)

{
  int *piVar1;
  int iVar2;
  bool bVar3;
  undefined4 uVar4;
  short sVar5;
  short sVar6;
  int iVar7;
  int iVar8;
  int iVar9;
  
  bVar3 = false;
  iVar2 = *param_1;
  sVar5 = *(short *)(SUB_000207fc + 0xe);
  iVar9 = *(int *)(iVar2 + 0x46);
  if ((*(byte *)((int)param_1 + 0x33) & 1) == 0) {
    if (DAT_00021058 == 0) {
      *(uint *)(iVar2 + 0x14) = *(uint *)(iVar2 + 0x14) & 0xffffffbf;
      goto LAB_000146d8;
    }
    *(uint *)(iVar2 + 0x14) = *(uint *)(iVar2 + 0x14) | 0x40;
LAB_0001487e:
    uVar4 = 0;
  }
  else {
    if (DAT_00021058 != 1) {
LAB_000146d8:
      sVar6 = 0;
      do {
        iVar7 = (int)sVar6;
        piVar1 = (int *)((int)param_1 + iVar7 * 4 + 0x6e);
        *piVar1 = *piVar1 + -0x5b;
        iVar8 = iVar7 * 0xc;
        *(int *)(iVar9 + iVar8) =
             (*(int *)((int)param_1 + iVar7 * 4 + 0x3a) >> 8) + *(int *)(iVar9 + iVar8);
        iVar7 = *(int *)(iVar9 + 4 + iVar8) + (*(int *)((int)param_1 + iVar7 * 4 + 0x6e) >> 4);
        *(int *)(iVar9 + 4 + iVar8) = iVar7;
        if (iVar7 < 0) {
          *(undefined4 *)(iVar9 + 4 + iVar8) = 0;
        }
        iVar7 = sVar6 * 0xc;
        piVar1 = (int *)(iVar9 + 8 + iVar7);
        *piVar1 = (*(int *)((int)param_1 + sVar6 * 4 + 0xa2) >> 8) + *piVar1;
        if (0 < *(int *)(iVar9 + 4 + iVar7)) {
          bVar3 = true;
        }
        iVar8 = (int)sVar6;
        iVar7 = *(int *)((int)param_1 + iVar8 * 4 + 0x3a) +
                *(int *)((int)param_1 + iVar8 * 4 + 0x3a) * 2;
        if (iVar7 < 0) {
          iVar7 = iVar7 + 3;
        }
        *(int *)((int)param_1 + iVar8 * 4 + 0x3a) = iVar7 >> 2;
        iVar8 = (int)sVar6;
        iVar7 = *(int *)((int)param_1 + iVar8 * 4 + 0xa2) +
                *(int *)((int)param_1 + iVar8 * 4 + 0xa2) * 2;
        if (iVar7 < 0) {
          iVar7 = iVar7 + 3;
        }
        *(int *)((int)param_1 + iVar8 * 4 + 0xa2) = iVar7 >> 2;
        sVar6 = sVar6 + 1;
      } while (sVar6 < 0xc);
      iVar7 = *(int *)(iVar9 + 0x1c);
      if (iVar7 < 0) {
        iVar7 = iVar7 + 1;
      }
      *(int *)(iVar9 + 0x94) = *(int *)(iVar9 + 0x1c) + (iVar7 >> 1);
      if (sVar5 == 8) {
        iVar7 = *(int *)(iVar2 + 0x4a);
        sVar5 = *(short *)(iVar2 + 0x3c);
        while (sVar5 = sVar5 + -1, sVar5 != -1) {
          *(char *)(iVar7 + 0xc) = (char)((int)((uint)*(byte *)(iVar7 + 0xc) * 7) >> 3);
          *(char *)(iVar7 + 0xd) = (char)((int)((uint)*(byte *)(iVar7 + 0xd) * 7) >> 3);
          *(char *)(iVar7 + 0xe) = (char)((int)((uint)*(byte *)(iVar7 + 0xe) * 7) >> 3);
          if (iVar7 == *(int *)(DAT_00023fac + (uint)*(byte *)(iVar7 + 0xf) * 4)) {
            FUN_00001bb0(iVar7);
          }
          else {
            *(undefined1 *)(iVar7 + 0xf) = 0;
          }
          iVar7 = iVar7 + 0x20;
        }
      }
      if (bVar3) goto LAB_0001487e;
      if ((*(byte *)((int)param_1 + 0x33) & 1) == 0) {
        if (*(int *)(iVar2 + 8) == 0) {
          FUN_0001b782(*(undefined4 *)(iVar9 + 0x14),*(undefined4 *)(iVar2 + 4),0,
                       *(undefined4 *)(iVar2 + 0xc));
        }
        if (*(char *)(param_1 + 0xe) != '\0') {
          iVar7 = *(int *)(iVar9 + 0x14);
          if (iVar7 < 0) {
            iVar7 = iVar7 + 1;
          }
          sVar5 = (short)(iVar7 >> 1) + *(short *)(iVar9 + 0x16);
          iVar9 = (int)sVar5;
          if (sVar5 < 0) {
            sVar5 = sVar5 + 1;
          }
          FUN_0001372e((int)(sVar5 >> 1),iVar9,300,1,0,*(undefined4 *)(iVar2 + 4),
                       *(undefined4 *)(iVar2 + 8),*(undefined4 *)(iVar2 + 0xc));
        }
      }
      else if (DAT_00021058 == 3) goto LAB_0001487e;
    }
    uVar4 = 1;
  }
  return uVar4;
}



// FUN_0001488a @ 0x1488a

undefined4 FUN_0001488a(int param_1)

{
  *(ushort *)(param_1 + 0x22) = -DAT_00023d3e & 0x3fff;
  *(ushort *)(param_1 + 0x20) = -DAT_00023d40 & 0x3fff;
  return 0;
}



// FUN_000148ba @ 0x148ba

void FUN_000148ba(int *param_1)

{
  short sVar1;
  
  sVar1 = (**(code **)(&DAT_000205cc + (uint)*(byte *)((int)param_1 + 0x32) * 4))(param_1);
  if (*(short *)((int)param_1 + 0x36) == 0) {
    *(uint *)(*param_1 + 0x14) = *(uint *)(*param_1 + 0x14) | 0x40;
  }
  else {
    *(short *)((int)param_1 + 0x36) = *(short *)((int)param_1 + 0x36) + -1;
  }
  if (sVar1 != 0) {
    FUN_0000605a(*param_1);
  }
  return;
}



// FUN_000148fc @ 0x148fc

void FUN_000148fc(int param_1)

{
  (**(code **)(&DAT_000205dc + (uint)*(byte *)(param_1 + 0x32) * 4))(param_1);
  return;
}



// FUN_0001491a @ 0x1491a

int * FUN_0001491a(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x63c);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x122000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  FUN_0000f346((int)piVar1 + 0x32,*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
               *(undefined4 *)(param_1 + 0xc));
  return piVar1;
}



// FUN_00014972 @ 0x14972

undefined4 FUN_00014972(undefined4 param_1,int param_2)

{
  DAT_0002c834 = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x148fa;
  return 0;
}



// FUN_00014994 @ 0x14994

undefined4 FUN_00014994(undefined4 param_1,int param_2)

{
  DAT_0002c830 = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x148fa;
  return 0;
}



// FUN_000149b6 @ 0x149b6

undefined4 FUN_000149b6(undefined4 param_1,int param_2)

{
  DAT_0002c82c = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x148fa;
  return 0;
}



// FUN_000149d8 @ 0x149d8

void FUN_000149d8(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x62c) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x62c) + 0x29);
    *pcVar1 = *pcVar1 + -1;
    FUN_000060fc(*(undefined4 *)(param_1 + 0x62c));
  }
  return;
}



// FUN_000149fa @ 0x149fa

int FUN_000149fa(undefined4 param_1,undefined4 param_2,int param_3,undefined4 param_4,
                undefined4 param_5,undefined4 param_6,short param_7,short param_8,short param_9)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002c834,param_4,param_5,param_6,(int)param_7,(int)param_8,
                               (int)param_9,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    if (param_3 != 0) {
      *(ushort *)(iVar2 + 0x638) = *(ushort *)(iVar2 + 0x638) | 0x40;
    }
    *(undefined4 *)(iVar2 + 0x634) = param_1;
    *(undefined4 *)(iVar2 + 0x61e) = param_2;
    *(short *)(iVar2 + 0x606) = param_7;
    *(short *)(iVar2 + 0x608) = param_8;
    *(short *)(iVar2 + 0x60a) = param_9;
  }
  return iVar2;
}



// FUN_00014a78 @ 0x14a78

int FUN_00014a78(undefined4 param_1,int param_2,undefined4 param_3,undefined4 param_4,
                undefined4 param_5,short param_6,short param_7,short param_8)

{
  int *piVar1;
  int iVar2;
  ushort uVar3;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002c830,param_3,param_4,param_5,(int)param_6,(int)param_7,
                               (int)param_8,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(undefined4 *)(iVar2 + 0x634) = param_1;
    if (param_2 < 0) {
      *(undefined4 *)(iVar2 + 0x61e) = 0;
    }
    else {
      *(int *)(iVar2 + 0x61e) = param_2;
    }
    *(short *)(iVar2 + 0x606) = param_6;
    *(short *)(iVar2 + 0x608) = param_7;
    *(short *)(iVar2 + 0x60a) = param_8;
    uVar3 = FUN_00013652();
    *(ushort *)(iVar2 + 0x610) = (uVar3 & 0x7ff) - 0x400;
    uVar3 = FUN_00013652();
    *(ushort *)(iVar2 + 0x624) = (uVar3 & 7) + 0x2d;
    *(undefined1 *)(iVar2 + 0x62b) = 1;
  }
  return iVar2;
}



// FUN_00014b1e @ 0x14b1e

int FUN_00014b1e(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                undefined4 param_5,short param_6,short param_7,short param_8)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002c82c,param_3,param_4,param_5,(int)param_6,(int)param_7,
                               (int)param_8,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(undefined4 *)(iVar2 + 0x634) = param_1;
    *(undefined4 *)(iVar2 + 0x61e) = param_2;
    *(short *)(iVar2 + 0x606) = param_6;
    *(short *)(iVar2 + 0x608) = param_7;
    *(short *)(iVar2 + 0x60a) = param_8;
    *(undefined1 *)(iVar2 + 0x62a) = 1;
  }
  return iVar2;
}



// FUN_00014b92 @ 0x14b92

void FUN_00014b92(int *param_1)

{
  int iVar1;
  ushort uVar2;
  short sVar5;
  int iVar3;
  undefined2 uVar6;
  short sVar7;
  int *piVar4;
  uint uVar8;
  int iVar9;
  uint uVar10;
  ushort local_26;
  short local_24;
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  iVar1 = *param_1;
  if (*(char *)((int)param_1 + 0x639) < '\0') {
    iVar9 = *(int *)((int)param_1 + 0x612) + (int)*(short *)((int)param_1 + 0x61a);
    *(ushort *)((int)param_1 + 0x61a) = (ushort)iVar9 & 0x3f;
    param_1[2] = iVar9 >> 6;
    iVar9 = *(int *)((int)param_1 + 0x616) + (int)*(short *)(param_1 + 0x187);
    *(ushort *)(param_1 + 0x187) = (ushort)iVar9 & 0x3f;
    param_1[4] = iVar9 >> 6;
    param_1[3] = 0;
  }
  else {
    local_24 = *(short *)(param_1 + 8);
    local_26 = *(ushort *)((int)param_1 + 0x22);
    uVar2 = *(short *)(param_1 + 0x184) + *(short *)(param_1 + 9) & 0x3fff;
    uVar8 = *(uint *)((int)param_1 + 0x61e);
    if (*(short *)(param_1 + 0x189) != 0) {
      uVar8 = uVar8 + 0x38e;
      *(short *)(param_1 + 0x189) = *(short *)(param_1 + 0x189) + -1;
    }
    if ((int)uVar8 < 0x1f4001) {
      uVar10 = 0;
      for (; (uVar8 & 0xffff8000) != 0; uVar8 = (int)uVar8 >> 1) {
        uVar10 = uVar10 + 1;
      }
    }
    else {
      uVar8 = 32000;
      uVar10 = 6;
    }
    local_a = 0;
    local_e = 0;
    local_6 = (short)uVar8;
    FUN_00002d02((int)local_24,(int)(short)local_26,uVar2,auStack_22);
    FUN_00003220(1,auStack_10,auStack_10,auStack_22);
    if ((*(short *)(param_1 + 0x189) == 0) || (8 < (int)(DAT_00021020 - *(int *)(iVar1 + 0x42)))) {
      local_a = local_a - (short)(0x5b >> (uVar10 & 0x3f));
    }
    uVar8 = 6 - uVar10;
    param_1[2] = (int)local_e >> (uVar8 & 0x3f);
    param_1[3] = (int)local_a >> (uVar8 & 0x3f);
    param_1[4] = (int)local_6 >> (uVar8 & 0x3f);
    iVar3 = (int)local_6 * (int)local_6 + (int)local_a * (int)local_a + (int)local_e * (int)local_e;
    sVar7 = FUN_0000f7e0(iVar3);
    iVar9 = (int)sVar7;
    FUN_00002574((int)local_e,(int)local_a,(int)local_6,iVar9,iVar3,&local_24,&local_26);
    sVar7 = local_26 - *(short *)((int)param_1 + 0x22);
    if (sVar7 < 0x2001) {
      if (sVar7 < -0x2000) {
        sVar7 = sVar7 + 0x4000;
      }
    }
    else {
      sVar7 = sVar7 + -0x4000;
    }
    *(short *)(param_1 + 8) = local_24;
    *(ushort *)((int)param_1 + 0x22) = local_26 & 0x3fff;
    sVar5 = FUN_000023c4((int)local_24);
    *(ushort *)(param_1 + 9) = uVar2 + (short)((int)sVar5 * (int)sVar7 >> 0xc) & 0x3fff;
    *(int *)((int)param_1 + 0x61e) = iVar9 << (uVar10 & 0x3f);
  }
  if (((param_1[0x18b] != 0) && ((DAT_00021020 & 7) == 0)) &&
     (iVar3 = FUN_00015d92(iVar1), iVar3 != 0x10)) {
    *(ushort *)(param_1 + 0x18e) = *(ushort *)(param_1 + 0x18e) | 0x400;
  }
  if (param_1[3] + *(int *)(iVar1 + 8) <= *(int *)((int)param_1 + 0x2e)) {
    if (param_1[0x18b] == 0) {
      iVar3 = *(int *)((int)param_1 + 0x2e);
    }
    else {
      iVar3 = 0x3c;
    }
    param_1[3] = iVar3 - *(int *)(iVar1 + 8);
    if ((*(int *)((int)param_1 + 0x2e) < 1) ||
       (((*(char *)(param_1 + 0xb) != '0' && (*(char *)(param_1 + 0xb) != '`')) &&
        (*(char *)(param_1 + 0xb) != 'p')))) {
      if (param_1[0x18b] != 0) {
        return;
      }
      if (((*(char *)((int)param_1 + 0x62a) != '\0') && (iVar3 = FUN_00015d92(iVar1), iVar3 == 0x10)
          ) && ((iVar9 < 0x3840 && (sVar7 = FUN_0000f87c((int)local_24), -0x640 < sVar7)))) {
        *(ushort *)(param_1 + 0x18e) = *(ushort *)(param_1 + 0x18e) | 0x80;
        piVar4 = (int *)FUN_0001ec4e(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                                     *(undefined4 *)(iVar1 + 0xc),0,
                                     (int)*(short *)((int)param_1 + 0x22),0,8);
        param_1[0x18b] = *piVar4;
        *(int *)(param_1[0x18b] + 0x20) = *(int *)(param_1[0x18b] + 0x20) << 1;
        *(char *)(param_1[0x18b] + 0x29) = *(char *)(param_1[0x18b] + 0x29) + '\x01';
        *(undefined2 *)(param_1 + 8) = 0;
        *(undefined2 *)(param_1 + 9) = 0;
        *(undefined4 *)((int)param_1 + 0x61e) = 0x10e0;
        sVar7 = FUN_000023c4((int)*(short *)((int)param_1 + 0x22));
        *(int *)((int)param_1 + 0x612) = *(int *)((int)param_1 + 0x61e) * -(int)sVar7 >> 0xc;
        sVar7 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
        *(int *)((int)param_1 + 0x616) = *(int *)((int)param_1 + 0x61e) * (int)sVar7 >> 0xc;
        return;
      }
    }
    else if ((*(char *)((int)param_1 + 0x62a) == '\0') || (param_1[0x18b] != 0)) {
      if (*(char *)((int)param_1 + 0x62b) == '\0') {
        uVar6 = 1;
      }
      else {
        uVar6 = 2;
      }
      *(undefined2 *)(param_1 + 0x18a) = uVar6;
    }
    *(ushort *)(param_1 + 0x18e) = *(ushort *)(param_1 + 0x18e) | 0x400;
  }
  return;
}



// FUN_00014eca @ 0x14eca

void FUN_00014eca(int *param_1)

{
  int *piVar1;
  int iVar2;
  undefined2 uVar3;
  short *psVar4;
  
  iVar2 = *param_1;
  psVar4 = (short *)((int)param_1 + 0x60e);
  piVar1 = (int *)((int)param_1 + *psVar4 * 0x14 + 0x32);
  *psVar4 = *psVar4 + 1;
  if (*(short *)((int)param_1 + 0x60e) == 0x4b) {
    *psVar4 = 0;
  }
  if (DAT_00021058 == 0) {
    if (((*(byte *)(param_1 + 0x18e) & 4) == 0) || ((*(byte *)((int)param_1 + 0x63a) & 4) == 0)) {
      FUN_00014b92(param_1);
    }
    else {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
    }
    FUN_0001546e(param_1);
    *piVar1 = param_1[2] + *(int *)(iVar2 + 4);
    piVar1[1] = param_1[3] + *(int *)(iVar2 + 8);
    piVar1[2] = param_1[4] + *(int *)(iVar2 + 0xc);
    *(undefined2 *)(piVar1 + 3) = *(undefined2 *)(param_1 + 8);
    *(undefined2 *)((int)piVar1 + 0xe) = *(undefined2 *)((int)param_1 + 0x22);
    *(undefined2 *)(piVar1 + 4) = *(undefined2 *)(param_1 + 9);
    *(undefined2 *)((int)piVar1 + 0x12) = *(undefined2 *)(param_1 + 0x18e);
  }
  else if (*(int *)(iVar2 + 0x42) <= DAT_00021020) {
    if ((DAT_00021058 == 1) || (DAT_00021020 == *(int *)(iVar2 + 0x42))) {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
      *(int *)(iVar2 + 4) = *piVar1;
      *(int *)(iVar2 + 8) = piVar1[1];
      *(int *)(iVar2 + 0xc) = piVar1[2];
      if (DAT_00021020 == *(int *)(iVar2 + 0x42)) {
        *(ushort *)((int)param_1 + 0x63a) = *(ushort *)((int)piVar1 + 0x12) & 0xffbf;
      }
    }
    else {
      param_1[2] = *piVar1 - *(int *)(iVar2 + 4);
      param_1[3] = piVar1[1] - *(int *)(iVar2 + 8);
      param_1[4] = piVar1[2] - *(int *)(iVar2 + 0xc);
    }
    *(undefined2 *)(param_1 + 0x18e) = *(undefined2 *)((int)piVar1 + 0x12);
    *(undefined2 *)(param_1 + 8) = *(undefined2 *)(piVar1 + 3);
    *(undefined2 *)((int)param_1 + 0x22) = *(undefined2 *)((int)piVar1 + 0xe);
    *(undefined2 *)(param_1 + 9) = *(undefined2 *)(piVar1 + 4);
    uVar3 = *(undefined2 *)(param_1 + 0x18e);
    FUN_0001546e(param_1);
    *(undefined2 *)((int)param_1 + 0x63a) = uVar3;
  }
  return;
}



// FUN_00015006 @ 0x15006

void FUN_00015006(int param_1,int *param_2)

{
  int iVar1;
  
  if ((DAT_00021058 == 0) && (param_1 != 0)) {
    FUN_00000602(param_2);
  }
  if (*(short *)((int)param_2 + 0x52) == 10) {
    if (*(short *)(SUB_000207fc + 0x90) == 2) {
      iVar1 = FUN_000048a6(param_2[1],0,param_2[3],0x498eb4,0,0xffe2cb5c);
      if (iVar1 < 100) {
        DAT_00027062 = DAT_00027062 + 1;
      }
      iVar1 = FUN_000048a6(param_2[1],0,param_2[3],0x4991d4,0,0xffe2cdb4);
      if (iVar1 < 100) {
        DAT_00027062 = DAT_00027062 + 1;
      }
      iVar1 = FUN_000048a6(param_2[1],0,param_2[3],0x4995ee,0,0xffe2cc24);
      if (iVar1 < 100) {
        DAT_00027062 = DAT_00027062 + 1;
      }
      if ((DAT_00027062 == 3) && (*(short *)(SUB_000207fc + 0xd2) == 0)) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
      }
    }
    if (*(short *)(SUB_000207fc + 0x90) == 3) {
      iVar1 = FUN_000048a6(param_2[1],0,param_2[3],0x5cf903,0,0xffffc1f2);
      if ((iVar1 < 100) && (*(short *)(SUB_000207fc + 0xd2) == 0)) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
      }
    }
  }
  else if ((*param_2 == 0) || (**(short **)(*param_2 + 4) != 0x28)) {
    if ((*param_2 != 0) &&
       ((((**(short **)(*param_2 + 4) == 0x27 && (*(short *)(SUB_000207fc + 0x90) == 6)) &&
         (DAT_0002705a = DAT_0002705a + 1, DAT_0002705a == 4)) &&
        (-1 < *(short *)(SUB_000207fc + 0xd2))))) {
      *(undefined2 *)(SUB_000207fc + 0xd2) = 0xffff;
    }
  }
  else if (((*(short *)(SUB_000207fc + 0x90) == 6) &&
           (DAT_00027058 = DAT_00027058 + 1, DAT_00027058 == 5)) &&
          (*(short *)(SUB_000207fc + 0xd2) == 0)) {
    *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
  }
  FUN_000060fc(param_2);
  return;
}



// FUN_000151b2 @ 0x151b2

void FUN_000151b2(undefined4 param_1,undefined4 param_2,undefined4 param_3,int param_4,int param_5,
                 int param_6,int param_7)

{
  int *piVar1;
  bool bVar2;
  undefined4 *puVar3;
  short sVar4;
  int iVar5;
  ushort uVar7;
  short sVar8;
  int iVar6;
  short unaff_D3w;
  undefined4 *puVar9;
  
  puVar3 = &DAT_00020804;
  sVar4 = DAT_00020b24._2_2_;
joined_r0x000151e0:
  do {
    do {
      do {
        do {
          sVar4 = sVar4 + -1;
          if (sVar4 == -1) {
            return;
          }
          puVar9 = puVar3 + 1;
          piVar1 = (int *)*puVar3;
          if (*piVar1 != 0) {
            unaff_D3w = **(short **)(*piVar1 + 4);
          }
          puVar3 = puVar9;
        } while (((piVar1[5] & 0x10242U) != 0) &&
                (((((*(byte *)((int)piVar1 + 0x15) & 1) == 0 || (*piVar1 == 0)) ||
                  ((unaff_D3w != 0x24 && (unaff_D3w != 0x23)))) &&
                 ((*piVar1 == 0 || (*piVar1 != SUB_00020b28))))));
        if (param_4 < piVar1[8]) goto LAB_0001537c;
        iVar5 = FUN_000048a6(piVar1[1],piVar1[2],piVar1[3],param_1,param_2,param_3);
      } while (1999 < iVar5 - piVar1[8]);
      if (iVar5 < piVar1[8]) {
LAB_00015268:
        bVar2 = true;
      }
      else if ((*piVar1 == 0) || (7 < unaff_D3w)) {
        if ((*piVar1 == 0) || (unaff_D3w != 0x12)) {
          if ((*piVar1 == 0) || ((unaff_D3w != 0x27 && (unaff_D3w != 0x28)))) {
            bVar2 = iVar5 - piVar1[8] < 600;
          }
          else if (param_6 == 0) {
            bVar2 = iVar5 - piVar1[8] < 0x5dc;
          }
          else {
            bVar2 = false;
          }
        }
        else {
          bVar2 = iVar5 - piVar1[8] < 2000;
        }
      }
      else {
        if ((param_6 == 0) &&
           (uVar7 = FUN_00013672(), iVar5 - piVar1[8] <= (int)(uint)(uVar7 & 0x3ff)))
        goto LAB_00015268;
        FUN_0001a72c(*piVar1,param_5);
        bVar2 = false;
      }
    } while (!bVar2);
    if (((*piVar1 != 0) && (0x1a < unaff_D3w)) && (unaff_D3w < 0x23)) goto LAB_000153f8;
    if ((*piVar1 == 0) || (7 < unaff_D3w)) {
      if ((*piVar1 == 0) || (unaff_D3w != 0x12)) {
        if (piVar1[2] == 0) {
          iVar5 = piVar1[8];
          if (iVar5 < 0) {
            iVar5 = iVar5 + 7;
          }
          iVar5 = iVar5 >> 3;
        }
        else {
          iVar5 = piVar1[8];
          if (iVar5 < 0) {
            iVar5 = iVar5 + 3;
          }
          iVar5 = iVar5 >> 2;
        }
        FUN_00013ee0(piVar1,iVar5);
        FUN_00015006(param_5,piVar1);
      }
      else {
        *(ushort *)(*piVar1 + 0x638) = *(ushort *)(*piVar1 + 0x638) | 0x400;
      }
    }
    else {
      if (param_5 != 0) {
        *(ushort *)(*piVar1 + 0x698) = *(ushort *)(*piVar1 + 0x698) | 0x8000;
      }
      *(ushort *)(*piVar1 + 0x698) = *(ushort *)(*piVar1 + 0x698) | 0x400;
    }
  } while( true );
LAB_0001537c:
  if ((((param_7 != 0) && (*piVar1 != 0)) && (0x1a < unaff_D3w)) && (unaff_D3w < 0x23)) {
    sVar8 = (short)param_7;
    if (sVar8 == 2) {
      if (((unaff_D3w == 0x1b) && (*(short *)(*piVar1 + 0x69a) == -1)) ||
         ((unaff_D3w == 0x1d || ((unaff_D3w == 0x20 || (unaff_D3w == 0x21)))))) {
        sVar8 = 1;
      }
      else {
        sVar8 = 0;
      }
    }
    if (sVar8 != 0) {
      iVar6 = FUN_000048a6(piVar1[1],piVar1[2],piVar1[3],param_1,param_2,param_3);
      iVar5 = piVar1[8];
      if (iVar5 < 0) {
        iVar5 = iVar5 + 1;
      }
      if (iVar6 < piVar1[8] + (iVar5 >> 1)) {
LAB_000153f8:
        FUN_00017a4a(*piVar1,param_5);
      }
    }
  }
  goto joined_r0x000151e0;
}



// FUN_00015412 @ 0x15412

void FUN_00015412(int param_1)

{
  short sVar1;
  byte *pbVar2;
  
  if (((*(byte *)(*(int *)(param_1 + 0x4a) + 0x13) & 2) == 0) &&
     (sVar1 = 0, 0 < *(short *)(param_1 + 0x3c))) {
    pbVar2 = (byte *)(*(int *)(param_1 + 0x4a) + 0x13);
    do {
      *pbVar2 = *pbVar2 | 2;
      sVar1 = sVar1 + 1;
      pbVar2 = pbVar2 + 0x20;
    } while (sVar1 < *(short *)(param_1 + 0x3c));
  }
  return;
}



// FUN_00015440 @ 0x15440

void FUN_00015440(int param_1)

{
  short sVar1;
  byte *pbVar2;
  
  if (((*(byte *)(*(int *)(param_1 + 0x4a) + 0x13) & 2) != 0) &&
     (sVar1 = 0, 0 < *(short *)(param_1 + 0x3c))) {
    pbVar2 = (byte *)(*(int *)(param_1 + 0x4a) + 0x13);
    do {
      *pbVar2 = *pbVar2 & 0xfd;
      sVar1 = sVar1 + 1;
      pbVar2 = pbVar2 + 0x20;
    } while (sVar1 < *(short *)(param_1 + 0x3c));
  }
  return;
}



// FUN_0001546e @ 0x1546e

void FUN_0001546e(int *param_1)

{
  int iVar1;
  int *piVar2;
  int iVar3;
  undefined2 uVar4;
  short sVar5;
  short sVar6;
  short sVar7;
  short *psVar8;
  int iVar9;
  undefined4 uVar10;
  bool bVar11;
  undefined4 uVar12;
  undefined4 uVar13;
  
  iVar1 = *param_1;
  if ((*(byte *)(param_1 + 0x18e) & 4) == 0) goto LAB_00015694;
  if ((*(byte *)((int)param_1 + 0x63a) & 4) != 0) {
    if (DAT_00021020 == param_1[0x18c]) {
      uVar4 = 0;
      if ((DAT_00021058 == 0) && (*(short *)((int)param_1 + 0x626) != 0)) {
        uVar4 = 1;
      }
      if (*(char *)((int)param_1 + 0x62b) == '\0') {
        if ((*(char *)((int)param_1 + 0x62a) == '\0') || (param_1[0x18b] != 0)) {
          iVar9 = (int)*(short *)(param_1 + 0x18a);
          uVar12 = 0x8d4;
        }
        else {
          iVar9 = (int)*(short *)(param_1 + 0x18a);
          uVar12 = 600;
        }
      }
      else {
        iVar9 = (int)*(short *)(param_1 + 0x18a);
        uVar12 = 0x640;
      }
      FUN_000151b2(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                   *(undefined4 *)(iVar1 + 0xc),uVar12,uVar4,0,iVar9);
      return;
    }
    goto LAB_00015694;
  }
  sVar5 = 0;
  if ((*(char *)((int)param_1 + 0x62a) == '\0') || (param_1[0x18b] != 0)) {
    uVar4 = 200;
  }
  else {
    uVar4 = 0x32;
  }
  if (*(short *)(param_1 + 0x18a) == 0) {
    sVar5 = FUN_00013ee0(iVar1,uVar4);
  }
  else {
    iVar9 = param_1[3] + *(int *)(iVar1 + 8);
    if (*(char *)((int)param_1 + 0x62a) == '\0') {
      if (*(short *)(param_1 + 0x18a) == 2) {
        bVar11 = false;
      }
      else {
        if (0 < *(int *)((int)param_1 + 0x2e)) {
          FUN_00013ee0(iVar1,uVar4);
          sVar5 = 1;
          goto LAB_000155d4;
        }
        bVar11 = 200 < iVar9;
      }
      uVar13 = 1;
      uVar10 = 5;
      uVar12 = 2;
    }
    else {
      uVar13 = 0;
      bVar11 = false;
      uVar10 = 0x4b;
      uVar12 = 0;
    }
    FUN_00013d80(uVar12,uVar4,uVar10,bVar11,uVar13,param_1[2] + *(int *)(iVar1 + 4),iVar9,
                 param_1[4] + *(int *)(iVar1 + 0xc));
  }
LAB_000155d4:
  if (((((*(char *)((int)param_1 + 0x62a) != '\0') && (*(short *)(SUB_000207fc + 0x90) == 8)) &&
       (*(short *)((int)param_1 + 0x626) == 0)) &&
      ((DAT_00021058 == 0 && (DAT_00027056 = DAT_00027056 + 1, DAT_00027056 == 0xc)))) &&
     (*(short *)(SUB_000207fc + 0xd2) == 0)) {
    *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
  }
  if (sVar5 == 0) {
    sVar5 = 5;
  }
  else {
    sVar5 = 0xf;
    if (*(short *)((int)param_1 + 0x626) != 0) {
      sVar6 = FUN_00015d92(iVar1);
      if (sVar6 == 0x70) {
        sVar7 = FUN_0000042c(iVar1);
        if (sVar7 == 0x100) {
          if (DAT_00021058 == 0) {
            psVar8 = (short *)(SUB_000207fc + 0xd0);
            goto LAB_0001566a;
          }
        }
        else if ((sVar7 == 0x200) && (DAT_00021058 == 0)) {
          psVar8 = (short *)(SUB_000207fc + 0xd6);
LAB_0001566a:
          *psVar8 = *psVar8 + 1;
        }
      }
      if ((sVar6 == 0x10) && (*(char *)((int)param_1 + 0x62b) != '\0')) {
        sVar5 = 1;
      }
    }
  }
  param_1[0x18c] = DAT_00021020 + sVar5;
  FUN_000060fc(iVar1);
LAB_00015694:
  if (((*(ushort *)((int)param_1 + 0x63a) ^ *(ushort *)(param_1 + 0x18e)) & 0x40) != 0) {
    FUN_0001acde(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc),
                 param_1);
  }
  if (*(char *)((int)param_1 + 0x639) < '\0') {
    if (-1 < *(char *)((int)param_1 + 0x63b)) {
      FUN_00013d80(1,0x32,0x3c,0,0,param_1[2] + *(int *)(iVar1 + 4),param_1[3] + *(int *)(iVar1 + 8)
                   ,param_1[4] + *(int *)(iVar1 + 0xc));
    }
    FUN_00015412(iVar1);
  }
  else {
    FUN_00015440(iVar1);
  }
  if (param_1[0x18b] != 0) {
    piVar2 = (int *)param_1[0x18b];
    iVar9 = *piVar2;
    iVar3 = DAT_00021020 - *(int *)((int)piVar2 + 0x42);
    *(undefined2 *)(iVar9 + 0x22) = *(undefined2 *)((int)param_1 + 0x22);
    *(int *)(iVar9 + 8) = (param_1[2] + *(int *)(iVar1 + 4)) - piVar2[1];
    *(int *)(iVar9 + 0xc) = -piVar2[2];
    *(int *)(iVar9 + 0x10) = (param_1[4] + *(int *)(iVar1 + 0xc)) - piVar2[3];
    if (((*(ushort *)((int)param_1 + 0x63a) & *(ushort *)(param_1 + 0x18e) & 0x400) == 0) ||
       (DAT_00021020 - param_1[0x18c] < 0x2e)) {
      *(uint *)(param_1[0x18b] + 0x14) = *(uint *)(param_1[0x18b] + 0x14) & 0xffffffbf;
    }
    else {
      *(uint *)(param_1[0x18b] + 0x14) = *(uint *)(param_1[0x18b] + 0x14) | 0x40;
    }
    if (0x1194 < iVar3) {
      FUN_000060fc(iVar1);
    }
  }
  *(undefined2 *)((int)param_1 + 0x63a) = *(undefined2 *)(param_1 + 0x18e);
  return;
}



// FUN_000157aa @ 0x157aa

void FUN_000157aa(int *param_1,int *param_2)

{
  int iVar1;
  
  iVar1 = *param_1;
  if ((((DAT_00021058 == 0) && (param_2 != *(int **)(iVar1 + 0x634))) &&
      (*(ushort *)(iVar1 + 0x638) = *(ushort *)(iVar1 + 0x638) | 0x400, *param_2 != 0)) &&
     ((0x1a < **(short **)(*param_2 + 4) && (**(short **)(*param_2 + 4) < 0x23)))) {
    if (*(char *)(iVar1 + 0x62b) == '\0') {
      *(undefined2 *)(iVar1 + 0x628) = 1;
    }
    else {
      *(undefined2 *)(iVar1 + 0x628) = 2;
    }
  }
  return;
}



// FUN_000157fa @ 0x157fa

void FUN_000157fa(int param_1,int param_2)

{
  DAT_00027078 = (int *)FUN_000021a4(0x32);
  *DAT_00027078 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x112100;
  DAT_00027078[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_0001583a @ 0x1583a

byte FUN_0001583a(short param_1,short param_2,int param_3,short param_4,short *param_5)

{
  int iVar1;
  int iVar2;
  byte bVar3;
  short *psVar4;
  
  bVar3 = 0;
  iVar1 = param_3 + param_5[param_4 + -1] * 0xc;
joined_r0x00015866:
  do {
    iVar2 = iVar1;
    param_4 = param_4 + -1;
    if (param_4 == -1) {
      return -bVar3 & 1;
    }
    psVar4 = param_5 + 1;
    iVar1 = param_3 + *param_5 * 0xc;
    param_5 = psVar4;
  } while (((*(short *)(iVar2 + 6) <= param_2) || (param_2 < *(short *)(iVar1 + 6))) &&
          ((param_2 < *(short *)(iVar2 + 6) || (*(short *)(iVar1 + 6) <= param_2))));
  if (*(short *)(iVar2 + 2) <= param_1) goto LAB_00015898;
  if (*(short *)(iVar1 + 2) <= param_1) goto LAB_0001589e;
  goto LAB_000158c6;
LAB_00015898:
  if (param_1 < *(short *)(iVar1 + 2)) {
LAB_0001589e:
    if (0 < ((int)(short)(param_2 - *(short *)(iVar2 + 6)) *
            (int)(short)(*(short *)(iVar1 + 2) - *(short *)(iVar2 + 2))) /
            (int)(short)(*(short *)(iVar1 + 6) - *(short *)(iVar2 + 6)) + (int)*(short *)(iVar2 + 2)
       ) {
LAB_000158c6:
      bVar3 = bVar3 ^ 1;
    }
  }
  goto joined_r0x00015866;
}



// FUN_000158de @ 0x158de

byte FUN_000158de(short param_1,short param_2,int param_3,short param_4,short *param_5)

{
  int iVar1;
  int iVar2;
  byte bVar3;
  short *psVar4;
  
  bVar3 = 0;
  iVar1 = param_3 + param_5[param_4 + -1] * 0xc;
joined_r0x0001590a:
  do {
    iVar2 = iVar1;
    param_4 = param_4 + -1;
    if (param_4 == -1) {
      return -bVar3 & 1;
    }
    psVar4 = param_5 + 1;
    iVar1 = param_3 + *param_5 * 0xc;
    param_5 = psVar4;
  } while (((*(short *)(iVar2 + 10) <= param_2) || (param_2 < *(short *)(iVar1 + 10))) &&
          ((param_2 < *(short *)(iVar2 + 10) || (*(short *)(iVar1 + 10) <= param_2))));
  if (*(short *)(iVar2 + 2) <= param_1) goto LAB_0001593c;
  if (*(short *)(iVar1 + 2) <= param_1) goto LAB_00015942;
  goto LAB_0001596a;
LAB_0001593c:
  if (param_1 < *(short *)(iVar1 + 2)) {
LAB_00015942:
    if (0 < ((int)(short)(param_2 - *(short *)(iVar2 + 10)) *
            (int)(short)(*(short *)(iVar1 + 2) - *(short *)(iVar2 + 2))) /
            (int)(short)(*(short *)(iVar1 + 10) - *(short *)(iVar2 + 10)) +
            (int)*(short *)(iVar2 + 2)) {
LAB_0001596a:
      bVar3 = bVar3 ^ 1;
    }
  }
  goto joined_r0x0001590a;
}



// FUN_00015982 @ 0x15982

byte FUN_00015982(short param_1,short param_2,int param_3,short param_4,short *param_5)

{
  int iVar1;
  int iVar2;
  byte bVar3;
  short *psVar4;
  
  bVar3 = 0;
  iVar1 = param_3 + param_5[param_4 + -1] * 0xc;
joined_r0x000159ae:
  do {
    iVar2 = iVar1;
    param_4 = param_4 + -1;
    if (param_4 == -1) {
      return -bVar3 & 1;
    }
    psVar4 = param_5 + 1;
    iVar1 = param_3 + *param_5 * 0xc;
    param_5 = psVar4;
  } while (((*(short *)(iVar2 + 10) <= param_2) || (param_2 < *(short *)(iVar1 + 10))) &&
          ((param_2 < *(short *)(iVar2 + 10) || (*(short *)(iVar1 + 10) <= param_2))));
  if (*(short *)(iVar2 + 6) <= param_1) goto LAB_000159e0;
  if (*(short *)(iVar1 + 6) <= param_1) goto LAB_000159e6;
  goto LAB_00015a0e;
LAB_000159e0:
  if (param_1 < *(short *)(iVar1 + 6)) {
LAB_000159e6:
    if (0 < ((int)(short)(param_2 - *(short *)(iVar2 + 10)) *
            (int)(short)(*(short *)(iVar1 + 6) - *(short *)(iVar2 + 6))) /
            (int)(short)(*(short *)(iVar1 + 10) - *(short *)(iVar2 + 10)) +
            (int)*(short *)(iVar2 + 6)) {
LAB_00015a0e:
      bVar3 = bVar3 ^ 1;
    }
  }
  goto joined_r0x000159ae;
}



// FUN_00015a26 @ 0x15a26

void FUN_00015a26(int param_1,short param_2,int param_3,short param_4,short param_5,int param_6)

{
  int iVar1;
  short sVar2;
  int *piVar3;
  int iVar4;
  short *psVar5;
  
  iVar1 = *(int *)(param_1 + 0x46);
  sVar2 = *(short *)(param_3 + 0x14) + -1;
  if (sVar2 != -1) {
    psVar5 = *(short **)(param_3 + 0x18);
    do {
      piVar3 = (int *)(*psVar5 * 0xc + iVar1);
      iVar4 = *psVar5 * 0xc + param_6;
      *(short *)(iVar4 + 2) = (short)(*piVar3 >> ((int)param_2 & 0x3fU)) - param_4;
      *(short *)(iVar4 + 6) = (short)(piVar3[1] >> ((int)param_2 & 0x3fU)) - param_5;
      sVar2 = sVar2 + -1;
      psVar5 = psVar5 + 1;
    } while (sVar2 != -1);
  }
  return;
}



// FUN_00015a8c @ 0x15a8c

void FUN_00015a8c(int param_1,short param_2,int param_3,short param_4,short param_5,int param_6)

{
  int iVar1;
  short sVar2;
  int *piVar3;
  int iVar4;
  short *psVar5;
  
  iVar1 = *(int *)(param_1 + 0x46);
  sVar2 = *(short *)(param_3 + 0x14) + -1;
  if (sVar2 != -1) {
    psVar5 = *(short **)(param_3 + 0x18);
    do {
      piVar3 = (int *)(*psVar5 * 0xc + iVar1);
      iVar4 = *psVar5 * 0xc + param_6;
      *(short *)(iVar4 + 2) = (short)(*piVar3 >> ((int)param_2 & 0x3fU)) - param_4;
      *(short *)(iVar4 + 10) = (short)(piVar3[2] >> ((int)param_2 & 0x3fU)) - param_5;
      sVar2 = sVar2 + -1;
      psVar5 = psVar5 + 1;
    } while (sVar2 != -1);
  }
  return;
}



// FUN_00015af2 @ 0x15af2

void FUN_00015af2(int param_1,short param_2,int param_3,short param_4,short param_5,int param_6)

{
  int iVar1;
  short sVar2;
  int iVar3;
  int iVar4;
  short *psVar5;
  
  iVar1 = *(int *)(param_1 + 0x46);
  sVar2 = *(short *)(param_3 + 0x14) + -1;
  if (sVar2 != -1) {
    psVar5 = *(short **)(param_3 + 0x18);
    do {
      iVar3 = *psVar5 * 0xc + iVar1;
      iVar4 = *psVar5 * 0xc + param_6;
      *(short *)(iVar4 + 6) = (short)(*(int *)(iVar3 + 4) >> ((int)param_2 & 0x3fU)) - param_4;
      *(short *)(iVar4 + 10) = (short)(*(int *)(iVar3 + 8) >> ((int)param_2 & 0x3fU)) - param_5;
      sVar2 = sVar2 + -1;
      psVar5 = psVar5 + 1;
    } while (sVar2 != -1);
  }
  return;
}



// FUN_00015b5a @ 0x15b5a

void FUN_00015b5a(int param_1,int param_2,int param_3,int param_4)

{
  short sVar1;
  undefined1 auStack_70c [1800];
  
  sVar1 = (*(short *)(param_3 + 0x36) + -1) - *(short *)(param_3 + 0x34);
  if (sVar1 < 0) {
    param_1 = param_1 >> (-(int)sVar1 & 0x3fU);
    param_2 = param_2 >> (-(int)sVar1 & 0x3fU);
  }
  else {
    param_1 = param_1 << ((int)sVar1 & 0x3fU);
    param_2 = param_2 << ((int)sVar1 & 0x3fU);
  }
  FUN_00015a8c(param_3,*(short *)(param_3 + 0x34) + 1,param_4,param_1,param_2,auStack_70c);
  FUN_000158de(0,0,auStack_70c,(int)*(short *)(param_4 + 0x14),*(undefined4 *)(param_4 + 0x18));
  return;
}



// FUN_00015bce @ 0x15bce

void FUN_00015bce(int *param_1,int param_2)

{
  int iVar1;
  int iVar2;
  undefined1 auStack_70c [1800];
  
  iVar2 = *param_1;
  if (*(short *)(param_1 + 3) < 0) {
    iVar1 = *(int *)(iVar2 + 4) >> (-(int)*(short *)(param_1 + 3) & 0x3fU);
    iVar2 = *(int *)(iVar2 + 0xc) >> (-(int)*(short *)(param_1 + 3) & 0x3fU);
  }
  else {
    iVar1 = *(int *)(iVar2 + 4) << ((int)*(short *)(param_1 + 3) & 0x3fU);
    iVar2 = *(int *)(iVar2 + 0xc) << ((int)*(short *)(param_1 + 3) & 0x3fU);
  }
  FUN_00015a8c(param_1[1],(int)*(short *)((int)param_1 + 0xe),param_2,iVar1,iVar2,auStack_70c);
  FUN_000158de(0,0,auStack_70c,(int)*(short *)(param_2 + 0x14),*(undefined4 *)(param_2 + 0x18));
  return;
}



// FUN_00015c4c @ 0x15c4c

int FUN_00015c4c(int param_1,undefined4 param_2,int param_3)

{
  int iVar1;
  short sVar2;
  ushort uVar3;
  int iVar4;
  int iVar5;
  int iVar6;
  int iVar7;
  char cVar8;
  short sVar9;
  int iVar10;
  int *piVar11;
  undefined1 auStack_70c [1800];
  
  sVar9 = DAT_00020f2c._2_2_;
  piVar11 = &DAT_00020b44 + DAT_00020f2c._2_2_;
  iVar5 = FUN_0000483e(param_1,param_2,param_3);
  sVar9 = sVar9 + -1;
  if (sVar9 != -1) {
    do {
      piVar11 = piVar11 + -1;
      iVar1 = *piVar11;
      iVar6 = FUN_00003908(*(int *)(iVar1 + 0x1c) - iVar5);
      if ((((iVar6 <= *(int *)(iVar1 + 0x20)) && ((*(uint *)(iVar1 + 0x14) & 0x2042) == 0)) &&
          (*(char *)(iVar1 + 0x28) == '\x02')) &&
         (iVar6 = FUN_000048a6(param_1,param_2,param_3,*(undefined4 *)(iVar1 + 4),
                               *(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc)),
         iVar6 <= *(int *)(iVar1 + 0x20))) {
        sVar2 = *(short *)(iVar1 + 0x3c);
        iVar6 = *(int *)(iVar1 + 0x4a) + sVar2 * 0x20;
        while (iVar4 = iVar6, sVar2 = sVar2 + -1, sVar2 != -1) {
          iVar6 = iVar4 + -0x20;
          if (*(char *)(iVar4 + -10) != '\0') {
            uVar3 = (*(short *)(iVar1 + 0x36) + -1) - *(short *)(iVar1 + 0x34);
            if ((int)((uint)uVar3 << 0x10) < 0) {
              iVar7 = param_1 >> (-(int)(short)uVar3 & 0x3fU);
              iVar10 = param_3 >> (-(int)(short)uVar3 & 0x3fU);
            }
            else {
              iVar7 = param_1 << ((int)(short)uVar3 & 0x3fU);
              iVar10 = param_3 << ((int)(short)uVar3 & 0x3fU);
            }
            FUN_00015a8c(iVar1,*(short *)(iVar1 + 0x34) + 1,iVar6,iVar7,iVar10,auStack_70c);
            iVar7 = FUN_000158de(0,0,auStack_70c,(int)*(short *)(iVar4 + -0xc),
                                 *(undefined4 *)(iVar4 + -8));
            if (iVar7 != 0) {
              cVar8 = *(char *)(iVar4 + -10);
              goto LAB_00015d86;
            }
          }
        }
      }
      sVar9 = sVar9 + -1;
    } while (sVar9 != -1);
  }
  cVar8 = *(char *)(DAT_00021590 + 0x16);
LAB_00015d86:
  return (int)cVar8;
}



// FUN_00015d92 @ 0x15d92

void FUN_00015d92(int param_1)

{
  FUN_00015c4c(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
               *(undefined4 *)(param_1 + 0xc));
  return;
}



// FUN_00015dac @ 0x15dac

undefined4 FUN_00015dac(int param_1,int param_2,undefined4 param_3,int param_4)

{
  uint uVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  short in_stack_00000026;
  undefined1 auStack_70c [1800];
  
  if (*(short *)(param_1 + 0xc) < 0) {
    uVar1 = -(int)*(short *)(param_1 + 0xc);
    iVar2 = *(int *)(param_2 + 4) >> (uVar1 & 0x3f);
    iVar3 = *(int *)(param_2 + 8) >> (uVar1 & 0x3f);
    iVar4 = *(int *)(param_2 + 0xc) >> (uVar1 & 0x3f);
  }
  else {
    uVar1 = (uint)*(short *)(param_1 + 0xc);
    iVar2 = *(int *)(param_2 + 4) << (uVar1 & 0x3f);
    iVar3 = *(int *)(param_2 + 8) << (uVar1 & 0x3f);
    iVar4 = *(int *)(param_2 + 0xc) << (uVar1 & 0x3f);
  }
  if (in_stack_00000026 == 1) {
    FUN_00015a8c(param_3,(int)*(short *)(param_1 + 0xe),param_4,iVar2,iVar4,auStack_70c);
    iVar2 = FUN_000158de(0,0,auStack_70c,(int)*(short *)(param_4 + 0x14),
                         *(undefined4 *)(param_4 + 0x18));
  }
  else if (in_stack_00000026 < 2) {
    if (in_stack_00000026 != 0) {
      return 0;
    }
    FUN_00015af2(param_3,(int)*(short *)(param_1 + 0xe),param_4,iVar3,iVar4,auStack_70c);
    iVar2 = FUN_00015982(0,0,auStack_70c,(int)*(short *)(param_4 + 0x14),
                         *(undefined4 *)(param_4 + 0x18));
  }
  else {
    if (in_stack_00000026 != 2) {
      return 0;
    }
    FUN_00015a26(param_3,(int)*(short *)(param_1 + 0xe),param_4,iVar2,iVar3,auStack_70c);
    iVar2 = FUN_0001583a(0,0,auStack_70c,(int)*(short *)(param_4 + 0x14),
                         *(undefined4 *)(param_4 + 0x18));
  }
  if (iVar2 == 0) {
    return 0;
  }
  return 1;
}



// FUN_00015ecc @ 0x15ecc

byte FUN_00015ecc(byte param_1,char param_2)

{
  return param_1 ^ param_2 << 2;
}



// FUN_00015ee0 @ 0x15ee0

int * FUN_00015ee0(int param_1,int param_2)

{
  int *piVar1;
  ushort uVar2;
  short sVar3;
  
  uVar2 = FUN_00015ecc(param_1,param_2);
  sVar3 = 0xff;
  while (((piVar1 = (int *)(&DAT_000215a4)[(short)uVar2], piVar1 == (int *)0x0 ||
          (param_1 != *piVar1)) || (param_2 != piVar1[1]))) {
    uVar2 = uVar2 + 1 & 0xff;
    sVar3 = sVar3 + -1;
    if (sVar3 == -1) {
      return (int *)0x0;
    }
  }
  return piVar1;
}



// FUN_00015f2e @ 0x15f2e

int * FUN_00015f2e(int param_1,int param_2)

{
  int *piVar1;
  int *piVar2;
  int *piVar3;
  ushort uVar5;
  int iVar4;
  short sVar6;
  short sVar7;
  short sVar8;
  uint uVar9;
  short sVar10;
  short sVar11;
  int iVar12;
  int *piVar13;
  int *piVar14;
  
  iVar12 = *(int *)(param_2 + 0x4a);
  sVar11 = 0;
  sVar10 = *(short *)(param_2 + 0x3c);
  while (sVar10 = sVar10 + -1, sVar10 != -1) {
    iVar4 = *(int *)(param_2 + 0x1c);
    if (iVar4 < 0) {
      iVar4 = iVar4 + 1;
    }
    if (iVar4 >> 1 <= *(int *)(iVar12 + 0x1c)) {
      sVar11 = sVar11 + 1;
    }
    iVar12 = iVar12 + 0x20;
  }
  piVar3 = (int *)FUN_000021a4(sVar11 * 6 + 0x10);
  *(short *)((int)piVar3 + 10) = sVar11;
  *piVar3 = param_1;
  piVar3[1] = param_2;
  for (uVar5 = FUN_00015ecc(param_1,param_2); (&DAT_000215a4)[(short)uVar5] != 0;
      uVar5 = uVar5 + 1 & 0xff) {
  }
  (&DAT_000215a4)[(short)uVar5] = piVar3;
  sVar10 = 0;
  for (uVar9 = *(int *)(param_1 + 0x20) + *(int *)(param_2 + 0x20) * 2 <<
               (*(ushort *)(param_2 + 0x36) & 0x3f); (uVar9 & 0xffff8000) != 0;
      uVar9 = (int)uVar9 >> 1) {
    sVar10 = sVar10 + 1;
  }
  *(short *)((int)piVar3 + 0xe) = sVar10;
  *(short *)(piVar3 + 3) = *(short *)(param_2 + 0x36) - *(short *)((int)piVar3 + 0xe);
  iVar12 = *(int *)(param_2 + 0x4a);
  sVar11 = 0;
  sVar10 = *(short *)(param_2 + 0x3c);
  piVar13 = piVar3 + 4;
  while (sVar10 = sVar10 + -1, sVar10 != -1) {
    iVar4 = *(int *)(param_2 + 0x1c);
    if (iVar4 < 0) {
      iVar4 = iVar4 + 1;
    }
    piVar14 = piVar13;
    if (iVar4 >> 1 <= *(int *)(iVar12 + 0x1c)) {
      piVar14 = (int *)((int)piVar13 + 2);
      *(short *)piVar13 = sVar11;
    }
    iVar12 = iVar12 + 0x20;
    sVar11 = sVar11 + 1;
    piVar13 = piVar14;
  }
  sVar10 = *(short *)((int)piVar3 + 10);
  piVar13 = piVar3 + 4 + sVar10;
  sVar11 = *(short *)(param_2 + 0x38);
  iVar12 = *(int *)(param_2 + 0x46);
  piVar14 = *(int **)(param_2 + 0x46);
  piVar2 = piVar3 + 4;
  do {
    sVar10 = sVar10 + -1;
    if (sVar10 == -1) {
      return piVar3;
    }
    piVar1 = (int *)(iVar12 + sVar11 * 0xc + *(short *)piVar2 * 0xc);
    sVar6 = FUN_000038e8(*piVar1 - *piVar14);
    sVar7 = FUN_000038e8(piVar1[1] - piVar14[1]);
    sVar8 = FUN_000038e8(piVar1[2] - piVar14[2]);
    if (sVar7 < sVar6) {
      if (sVar8 < sVar6) {
        *(short *)piVar13 = 0;
      }
      else {
LAB_000160ba:
        *(short *)piVar13 = 2;
      }
    }
    else {
      if (sVar7 <= sVar8) goto LAB_000160ba;
      *(short *)piVar13 = 1;
    }
    piVar13 = (int *)((int)piVar13 + 2);
    piVar2 = (int *)((int)piVar2 + 2);
  } while( true );
}



// FUN_000160d0 @ 0x160d0

void FUN_000160d0(void)

{
  int *piVar1;
  int iVar2;
  short sVar3;
  int *piVar4;
  
  piVar4 = &DAT_000215a4;
  sVar3 = 0xff;
  do {
    piVar1 = (int *)*piVar4;
    if (piVar1 != (int *)0x0) {
      if (*(short *)(piVar1 + 2) == 0) {
        if ((((*(byte *)(piVar1[1] + 0x16) & 2) != 0) &&
            (iVar2 = *piVar1, (*(byte *)(iVar2 + 0x16) & 0x20) != 0)) &&
           (*(int *)(iVar2 + 0x2e) != 0)) {
          FUN_0001748c(iVar2);
        }
        FUN_00002008(piVar1);
        *piVar4 = 0;
      }
      else {
        *(short *)(piVar1 + 2) = *(short *)(piVar1 + 2) + -1;
      }
    }
    piVar4 = piVar4 + 1;
    sVar3 = sVar3 + -1;
  } while (sVar3 != -1);
  return;
}



// FUN_00016136 @ 0x16136

void FUN_00016136(int param_1)

{
  int *piVar1;
  int iVar2;
  short sVar3;
  int *piVar4;
  
  piVar4 = &DAT_000215a4;
  sVar3 = 0xff;
  do {
    piVar1 = (int *)*piVar4;
    if ((piVar1 != (int *)0x0) && ((param_1 == *piVar1 || (param_1 == piVar1[1])))) {
      if (((*(byte *)(piVar1[1] + 0x16) & 2) != 0) &&
         ((iVar2 = *piVar1, (*(byte *)(iVar2 + 0x16) & 0x20) != 0 && (*(int *)(iVar2 + 0x2e) != 0)))
         ) {
        FUN_0001748c(iVar2);
      }
      FUN_00002008(piVar1);
      *piVar4 = 0;
    }
    piVar4 = piVar4 + 1;
    sVar3 = sVar3 + -1;
  } while (sVar3 != -1);
  return;
}



// FUN_0001619e @ 0x1619e

int * FUN_0001619e(int param_1,int param_2)

{
  int iVar1;
  short sVar2;
  int *piVar3;
  ushort uVar4;
  short sVar5;
  short sVar6;
  uint uVar7;
  short sVar8;
  
  piVar3 = (int *)FUN_000020a0(0x3e);
  *piVar3 = param_1;
  piVar3[1] = param_2;
  for (uVar4 = FUN_00015ecc(param_1,param_2); (&DAT_000215a4)[(short)uVar4] != 0;
      uVar4 = uVar4 + 1 & 0xff) {
  }
  (&DAT_000215a4)[(short)uVar4] = piVar3;
  sVar8 = 0;
  for (uVar7 = *(int *)(param_1 + 0x20) + *(int *)(param_2 + 0x20) * 2 <<
               (*(ushort *)(param_2 + 0x36) & 0x3f); (uVar7 & 0xffff8000) != 0;
      uVar7 = (int)uVar7 >> 1) {
    sVar8 = sVar8 + 1;
  }
  *(short *)(piVar3 + 3) = sVar8;
  *(short *)((int)piVar3 + 10) = *(short *)(param_2 + 0x36) - sVar8;
  uVar4 = 0;
  sVar8 = *(short *)(param_2 + 0x12);
  for (iVar1 = *(int *)(param_2 + 0x24); iVar1 != 0; iVar1 = *(int *)(iVar1 + 0x24)) {
    uVar4 = *(short *)(*(int *)(iVar1 + 0x32) + sVar8 * 0x18 + 4) + uVar4 & 0x3fff;
    sVar8 = *(short *)(iVar1 + 0x12);
  }
  if (uVar4 == 0) {
    *(undefined2 *)(piVar3 + 5) = 0;
    *(undefined2 *)((int)piVar3 + 0xe) = 0;
  }
  else {
    sVar5 = FUN_00002426(uVar4);
    sVar6 = FUN_000023c4(uVar4);
    sVar2 = -sVar6;
    sVar8 = sVar2;
    if (sVar2 < 0) {
      sVar8 = sVar2 + 1;
    }
    *(short *)((int)piVar3 + 0xe) = sVar5 + (sVar8 >> 1);
    *(short *)(piVar3 + 4) = sVar2;
    sVar8 = sVar5;
    if (sVar5 < 0) {
      sVar8 = sVar5 + 1;
    }
    *(short *)((int)piVar3 + 0x12) = (sVar8 >> 1) + sVar6;
    *(short *)(piVar3 + 5) = sVar5;
  }
  *(short *)((int)piVar3 + 0x16) =
       (short)((0x19c80 << (*(ushort *)(param_2 + 0x36) & 0x3f)) >> (*(ushort *)(piVar3 + 3) & 0x3f)
              );
  *(undefined2 *)(piVar3 + 6) = 1;
  *(undefined2 *)((int)piVar3 + 0x1a) = 0xffff;
  *(undefined2 *)(piVar3 + 7) = 0xffff;
  *(undefined2 *)((int)piVar3 + 0x1e) = 1;
  return piVar3;
}



// FUN_000162d8 @ 0x162d8

int FUN_000162d8(int param_1,short param_2,short param_3,undefined2 *param_4)

{
  int *piVar1;
  undefined2 *puVar2;
  short sVar5;
  short sVar6;
  int iVar3;
  int iVar4;
  int iVar7;
  short sVar8;
  short sVar9;
  short sVar11;
  int iVar10;
  int *piVar12;
  int iVar13;
  int *local_16;
  short local_6;
  
  local_6 = param_3;
  iVar13 = *(int *)(param_1 + 4);
  if ((*(short *)(param_1 + 0xe) == 0) && (*(short *)(param_1 + 0x14) == 0)) {
    sVar5 = param_3;
    if (param_3 < 0) {
      sVar5 = param_3 + 1;
    }
    sVar5 = (sVar5 >> 1) + param_2;
  }
  else {
    local_6 = (short)((int)param_3 * (int)*(short *)(param_1 + 0x14) +
                      (int)param_2 * (int)*(short *)(param_1 + 0x10) >> 0xc);
    sVar5 = (short)((int)param_3 * (int)*(short *)(param_1 + 0x12) +
                    (int)param_2 * (int)*(short *)(param_1 + 0xe) >> 0xc);
  }
  if ((((sVar5 < *(short *)(param_1 + 0x18)) || (*(short *)(param_1 + 0x1a) <= sVar5)) ||
      (local_6 < *(short *)(param_1 + 0x1c))) || (*(short *)(param_1 + 0x1e) <= local_6)) {
    *(undefined2 *)(param_1 + 0x20) = 0xffff;
    sVar9 = *(short *)(param_1 + 0x16);
    sVar11 = sVar9;
    if (sVar9 < 0) {
      sVar11 = sVar9 + 1;
    }
    sVar8 = sVar5 / sVar9;
    if (sVar5 % sVar9 < 0) {
      sVar8 = sVar8 + -1;
    }
    sVar8 = sVar8 * sVar9;
    sVar6 = local_6 / sVar9;
    if (local_6 % sVar9 < 0) {
      sVar6 = sVar6 + -1;
    }
    sVar6 = sVar6 * sVar9;
    *(short *)(param_1 + 0x18) = sVar8;
    *(short *)(param_1 + 0x1a) = sVar9 + sVar8;
    *(short *)(param_1 + 0x1c) = sVar6;
    *(short *)(param_1 + 0x1e) = sVar6 + sVar9;
    iVar10 = (int)(sVar11 >> 1) + (int)sVar6;
    iVar7 = iVar10;
    if (iVar10 < 0) {
      iVar7 = iVar10 + 1;
    }
    iVar7 = ((int)(sVar11 >> 1) + (int)sVar8) - (iVar7 >> 1);
    if (*(short *)(param_1 + 10) < 0) {
      iVar10 = iVar10 << (-(int)*(short *)(param_1 + 10) & 0x3fU);
      iVar7 = iVar7 << (-(int)*(short *)(param_1 + 10) & 0x3fU);
    }
    else {
      iVar7 = iVar7 >> ((int)*(short *)(param_1 + 10) & 0x3fU);
      iVar10 = iVar10 >> ((int)*(short *)(param_1 + 10) & 0x3fU);
    }
    piVar1 = (int *)(*(int *)(SUB_000207fc + 0x8c) +
                     *(int *)(*(int *)(*(int *)(iVar13 + 0x24) + 0x2a) + 0x2e +
                             (uint)*(ushort *)(iVar13 + 0x10) * 0x18) + 0x22 +
                    ((int)*(short *)(iVar13 + 0x38) - (int)*(short *)(iVar13 + 0x3a)) * 0xc);
    DAT_0002707e = 0xffff;
    DAT_0002707c = 0xffff;
    sVar9 = *(short *)(iVar13 + 0x3a) + -1;
    if (sVar9 != -1) {
      local_16 = piVar1 + 2;
      piVar12 = piVar1;
      do {
        iVar3 = FUN_000038e8(*piVar12 - iVar7);
        iVar4 = FUN_000038e8(*local_16 - iVar10);
        if (iVar4 + iVar3 < 0xad40) {
          if (iVar10 < *local_16) {
            DAT_0002707e = (undefined2)(((int)piVar12 - (int)piVar1) / 0xc);
          }
          else {
            DAT_0002707c = (undefined2)(((int)piVar12 - (int)piVar1) / 0xc);
          }
        }
        local_16 = local_16 + 3;
        piVar12 = piVar12 + 3;
        sVar9 = sVar9 + -1;
      } while (sVar9 != -1);
    }
    *(undefined2 *)(param_1 + 0x34) = 0;
    *(undefined2 *)(param_1 + 0x26) = 0;
    iVar10 = *(int *)(iVar13 + 0x46);
    sVar9 = 0;
    do {
      iVar7 = (int)sVar9;
      if ((&DAT_0002707c)[iVar7] != -1) {
        piVar1 = (int *)(iVar10 + **(short **)
                                    (*(int *)(iVar13 + 0x4a) + (short)(&DAT_0002707c)[iVar7] * 0x20
                                    + 0x18) * 0xc);
        iVar3 = iVar10 + *(short *)(iVar13 + 0x38) * 0xc + (short)(&DAT_0002707c)[iVar7] * 0xc;
        puVar2 = (undefined2 *)(param_1 + 0x22 + iVar7 * 0xe);
        *puVar2 = (&DAT_0002707c)[iVar7];
        puVar2[1] = *(short *)(iVar3 + 2) - *(short *)(iVar10 + 2);
        puVar2[2] = *(short *)(iVar3 + 6) - *(short *)(iVar10 + 6);
        puVar2[3] = *(short *)(iVar3 + 10) - *(short *)(iVar10 + 10);
        puVar2[4] = (short)(*piVar1 - *(int *)(iVar13 + 4) >> (*(ushort *)(param_1 + 0xc) & 0x3f));
        puVar2[5] = (short)(piVar1[1] - *(int *)(iVar13 + 8) >> (*(ushort *)(param_1 + 0xc) & 0x3f))
        ;
        puVar2[6] = (short)(piVar1[2] - *(int *)(iVar13 + 0xc) >>
                           (*(ushort *)(param_1 + 0xc) & 0x3f));
      }
      sVar9 = sVar9 + 1;
    } while (sVar9 < 2);
  }
  if ((int)local_6 - (int)*(short *)(param_1 + 0x1c) < (int)sVar5 - (int)*(short *)(param_1 + 0x18))
  {
    DAT_00027080 = param_1 + 0x22;
    if (*(short *)(param_1 + 0x20) != 0) {
      *param_4 = 1;
      *(undefined2 *)(param_1 + 0x20) = 0;
    }
  }
  else {
    DAT_00027080 = param_1 + 0x30;
    if (*(short *)(param_1 + 0x20) != 1) {
      *param_4 = 1;
      *(undefined2 *)(param_1 + 0x20) = 1;
    }
  }
  if ((*(short *)(DAT_00027080 + 4) == 0) ||
     (iVar13 = (int)*(short *)(DAT_00027080 + 10) +
               ((int)(short)(param_3 - *(short *)(DAT_00027080 + 0xc)) *
                -(int)*(short *)(DAT_00027080 + 6) -
               (int)(short)(param_2 - *(short *)(DAT_00027080 + 8)) *
               (int)*(short *)(DAT_00027080 + 2)) / (int)*(short *)(DAT_00027080 + 4), iVar13 < 0))
  {
    iVar13 = 0;
  }
  return iVar13;
}



// FUN_0001663a @ 0x1663a

void FUN_0001663a(int *param_1,int param_2)

{
  int iVar1;
  int iVar2;
  uint uVar3;
  short sVar4;
  short sVar5;
  short sVar6;
  short local_6;
  
  iVar1 = param_1[3] - *(int *)(param_2 + 0xc);
  iVar2 = iVar1;
  if (iVar1 < 0) {
    iVar2 = iVar1 + 1;
  }
  iVar2 = (param_1[1] + (iVar2 >> 1)) - *(int *)(param_2 + 4);
  if ((((-0x33901 < iVar2) && (iVar2 < 0x33901)) && (-0x33901 < iVar1)) && (iVar1 < 0x33901)) {
    iVar2 = FUN_00015ee0(param_1,param_2);
    if (iVar2 == 0) {
      if (((*(byte *)(param_2 + 0x16) & 2) != 0) && (*(int *)((int)param_1 + 0x2e) != 0)) {
        return;
      }
      iVar2 = FUN_0001619e(param_1,param_2);
    }
    *(undefined2 *)(iVar2 + 8) = 1;
    if (*(short *)(iVar2 + 10) < 0) {
      uVar3 = -(int)*(short *)(iVar2 + 10);
      sVar6 = (short)(param_1[1] - *(int *)(param_2 + 4) >> (uVar3 & 0x3f));
      sVar5 = (short)(param_1[2] >> (uVar3 & 0x3f));
      sVar4 = (short)(param_1[3] - *(int *)(param_2 + 0xc) >> (uVar3 & 0x3f));
    }
    else {
      uVar3 = (uint)*(short *)(iVar2 + 10);
      sVar6 = (short)(param_1[1] - *(int *)(param_2 + 4) << (uVar3 & 0x3f));
      sVar5 = (short)(param_1[2] << (uVar3 & 0x3f));
      sVar4 = (short)(param_1[3] - *(int *)(param_2 + 0xc) << (uVar3 & 0x3f));
    }
    local_6 = 0;
    sVar4 = FUN_000162d8(iVar2,(int)sVar6,(int)sVar4,&local_6);
    if ((*param_1 != 0) && (sVar4 != 0)) {
      if (*(short *)(iVar2 + 10) < 0) {
        *(int *)(*param_1 + 0x2e) = (int)sVar4 << (-(int)*(short *)(iVar2 + 10) & 0x3fU);
      }
      else {
        *(int *)(*param_1 + 0x2e) = (int)sVar4 >> (*(ushort *)(iVar2 + 10) & 0x3f);
      }
      if (DAT_00021058 == 0) {
        *(undefined1 *)(*param_1 + 0x2c) = 0;
      }
    }
    if (local_6 != 0) {
      sVar5 = FUN_000171da(iVar2,(int)sVar4,(int)sVar5,param_1,param_2);
    }
    if (sVar5 < sVar4) {
      FUN_00016caa(param_1,param_2);
    }
  }
  return;
}



// FUN_000167a4 @ 0x167a4

void FUN_000167a4(int param_1,int param_2)

{
  int iVar1;
  
  if ((*(uint *)(param_2 + 0x14) & *(uint *)(param_1 + 0x14) & 0x100000) != 0) {
    iVar1 = FUN_000048a6(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
                         *(undefined4 *)(param_1 + 0xc),*(undefined4 *)(param_2 + 4),
                         *(undefined4 *)(param_2 + 8),*(undefined4 *)(param_2 + 0xc));
    iVar1 = (iVar1 >> 2) + (iVar1 >> 1);
    if ((iVar1 < *(int *)(param_1 + 0x20)) || (iVar1 < *(int *)(param_2 + 0x20))) {
      FUN_00016caa(param_1,param_2);
    }
  }
  return;
}



// FUN_00016804 @ 0x16804

void FUN_00016804(int *param_1,int *param_2)

{
  if ((*param_1 == SUB_00020b28) && (**(short **)(*param_2 + 4) == 0x14)) {
    FUN_000167a4(param_1,param_2);
  }
  return;
}



// FUN_00016832 @ 0x16832

int FUN_00016832(int *param_1,int *param_2)

{
  int *piVar1;
  int iVar2;
  int iVar3;
  short sVar4;
  int iVar5;
  int iVar6;
  ushort uVar7;
  ushort uVar8;
  short sVar9;
  short sVar10;
  int iVar11;
  int iVar12;
  int iVar13;
  short sVar14;
  short sVar15;
  uint uVar16;
  short sVar17;
  ushort *puVar18;
  bool bVar19;
  short *local_2e;
  short sStack_20;
  short sStack_1c;
  ushort *local_a;
  
  iVar11 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]);
  iVar11 = iVar11 - (iVar11 >> 2);
  if (iVar11 <= param_2[8] + param_1[8]) {
    if ((*(byte *)((int)param_2 + 0x16) & 2) == 0) {
      if (((((*(byte *)((int)param_2 + 0x16) & 4) == 0) || (*param_1 == 0)) ||
          (**(short **)(*param_1 + 4) != 0x14)) || (DAT_00023d88 == 8)) {
        iVar12 = FUN_00015ee0(param_1,param_2);
        bVar19 = iVar12 != 0;
        if (!bVar19) {
          iVar12 = FUN_00015f2e(param_1,param_2);
        }
        *(undefined2 *)(iVar12 + 8) = 1;
        iVar11 = iVar12;
        if ((*(byte *)((int)param_2 + 0x16) & 0x40) != 0) {
          iVar11 = (**(code **)(*(int *)(*param_2 + 4) + 0x12))(param_2,param_1,iVar12);
        }
        if (((*(byte *)((int)param_1 + 0x15) & 1) == 0) &&
           ((*(byte *)((int)param_2 + 0x15) & 1) == 0)) {
          iVar2 = *(int *)((int)param_2 + 0x4a);
          iVar3 = *(int *)((int)param_2 + 0x46);
          sVar4 = *(short *)(param_2 + 0xe);
          if (*(short *)(iVar12 + 0xc) < 0) {
            uVar16 = -(int)*(short *)(iVar12 + 0xc);
            sStack_1c = (short)(param_1[1] >> (uVar16 & 0x3f));
            sStack_20 = (short)(param_1[2] >> (uVar16 & 0x3f));
            sVar14 = (short)(param_1[3] >> (uVar16 & 0x3f));
          }
          else {
            uVar16 = (uint)*(short *)(iVar12 + 0xc);
            sStack_1c = (short)(param_1[1] << (uVar16 & 0x3f));
            sStack_20 = (short)(param_1[2] << (uVar16 & 0x3f));
            sVar14 = (short)(param_1[3] << (uVar16 & 0x3f));
          }
          local_2e = (short *)(iVar12 + 0x10);
          puVar18 = (ushort *)(local_2e + *(short *)(iVar12 + 10));
          local_a = puVar18 + *(short *)(iVar12 + 10);
          iVar11 = CONCAT22((short)((uint)local_a >> 0x10),*(short *)(iVar12 + 0xe));
          sVar15 = *(short *)(iVar12 + 10) + -1;
          if (sVar15 != -1) {
            uVar16 = (uint)*(short *)(iVar12 + 0xe);
            do {
              iVar13 = iVar2 + *local_2e * 0x20;
              iVar11 = iVar13;
              if ((*(byte *)(iVar13 + 0x13) & 2) == 0) {
                piVar1 = (int *)(iVar3 + **(short **)(iVar13 + 0x18) * 0xc);
                iVar11 = iVar3 + sVar4 * 0xc + *local_2e * 0xc;
                sVar9 = *(short *)(iVar11 + 2) - *(short *)(iVar3 + 2);
                sVar10 = *(short *)(iVar11 + 6) - *(short *)(iVar3 + 6);
                sVar17 = *(short *)(iVar11 + 10) - *(short *)(iVar3 + 10);
                iVar5 = (int)(short)(sStack_20 - (short)(piVar1[1] >> (uVar16 & 0x3f))) *
                        (int)sVar10 +
                        (int)(short)(sStack_1c - (short)(*piVar1 >> (uVar16 & 0x3f))) * (int)sVar9;
                iVar6 = iVar5 + (int)(short)(sVar14 - (short)(piVar1[2] >> (uVar16 & 0x3f))) *
                                (int)sVar17;
                iVar11 = CONCAT31((int3)((uint)iVar5 >> 8),-(iVar6 < 1));
                uVar7 = (ushort)iVar11 & 1;
                if ((bVar19) &&
                   (uVar8 = uVar7 ^ *puVar18, iVar11 = CONCAT22((short)((uint)iVar5 >> 0x10),uVar8),
                   uVar8 != 0)) {
                  iVar13 = FUN_00015dac(iVar12,param_1,param_2,iVar13,iVar6,(int)sVar9,(int)sVar10,
                                        (int)sVar17,(int)(short)*local_a);
                  iVar11 = 0;
                  if (iVar13 != 0) {
                    iVar11 = FUN_00016caa(param_1,param_2);
                    return iVar11;
                  }
                }
                *puVar18 = uVar7;
              }
              local_2e = local_2e + 1;
              puVar18 = puVar18 + 1;
              local_a = local_a + 1;
              sVar15 = sVar15 + -1;
            } while (sVar15 != -1);
          }
        }
      }
      else {
        iVar11 = FUN_0001a66a(param_1,param_2);
      }
    }
    else {
      iVar11 = FUN_0001663a(param_1,param_2);
    }
  }
  return iVar11;
}



// FUN_00016b06 @ 0x16b06

void FUN_00016b06(int *param_1,int param_2)

{
  int iVar1;
  short sVar2;
  short sVar3;
  undefined2 local_6;
  
  iVar1 = FUN_00015ee0(param_1,param_2);
  if (iVar1 == 0) {
    if (((*(byte *)(param_2 + 0x16) & 2) != 0) && (*(int *)((int)param_1 + 0x2e) != 0)) {
      return;
    }
    iVar1 = FUN_0001619e(param_1,param_2);
  }
  *(undefined2 *)(iVar1 + 8) = 1;
  if (*(short *)(iVar1 + 10) < 0) {
    sVar2 = (short)(param_1[1] - *(int *)(param_2 + 4) >> (-(int)*(short *)(iVar1 + 10) & 0x3fU));
    sVar3 = (short)(param_1[3] - *(int *)(param_2 + 0xc) >> (-(int)*(short *)(iVar1 + 10) & 0x3fU));
  }
  else {
    sVar2 = (short)(param_1[1] - *(int *)(param_2 + 4) << ((int)*(short *)(iVar1 + 10) & 0x3fU));
    sVar3 = (short)(param_1[3] - *(int *)(param_2 + 0xc) << ((int)*(short *)(iVar1 + 10) & 0x3fU));
  }
  local_6 = 0;
  sVar3 = FUN_000162d8(iVar1,(int)sVar2,(int)sVar3,&local_6);
  if ((*param_1 != 0) && (sVar3 != 0)) {
    if (*(short *)(iVar1 + 10) < 0) {
      *(int *)(*param_1 + 0x2e) = (int)sVar3 << (-(int)*(short *)(iVar1 + 10) & 0x3fU);
    }
    else {
      *(int *)(*param_1 + 0x2e) = (int)sVar3 >> (*(ushort *)(iVar1 + 10) & 0x3f);
    }
    if (DAT_00021058 == 0) {
      *(undefined1 *)(*param_1 + 0x2c) = 0;
    }
  }
  if (*(int *)((int)param_1 + 0x2e) != 0) {
    FUN_0001748c(param_1);
  }
  *(undefined2 *)(iVar1 + 0x20) = 0xffff;
  return;
}



// FUN_00016c02 @ 0x16c02

void FUN_00016c02(int *param_1,int *param_2)

{
  int iVar1;
  
  if (*param_1 == SUB_00020b28) {
    iVar1 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]);
    if (iVar1 - (iVar1 >> 2) <= param_2[8] + param_1[8]) {
      if ((*(byte *)((int)param_2 + 0x16) & 2) == 0) {
        iVar1 = FUN_00015ee0(param_1,param_2);
        if (iVar1 == 0) {
          iVar1 = FUN_00015f2e(param_1,param_2);
        }
        *(undefined2 *)(iVar1 + 8) = 1;
        if ((*(byte *)((int)param_2 + 0x16) & 0x40) != 0) {
          (**(code **)(*(int *)(*param_2 + 4) + 0x12))(param_2,param_1,iVar1);
        }
      }
      else {
        FUN_00016b06(param_1,param_2);
      }
    }
  }
  return;
}



// FUN_00016caa @ 0x16caa

void FUN_00016caa(int *param_1,int *param_2)

{
  short sVar1;
  short *in_A1;
  short *unaff_A2;
  code *pcVar2;
  code *pcVar3;
  int *piVar4;
  int *piVar5;
  
  pcVar2 = (code *)0x0;
  pcVar3 = (code *)0x0;
  if (DAT_00023d88 == 8) {
    return;
  }
  if ((*(byte *)((int)param_1 + 0x15) & 1) != 0) {
    return;
  }
  if ((*(byte *)((int)param_2 + 0x15) & 1) != 0) {
    return;
  }
  if (*param_1 != 0) {
    in_A1 = *(short **)(*param_1 + 4);
    pcVar2 = *(code **)(in_A1 + 9);
  }
  if (*param_2 != 0) {
    unaff_A2 = *(short **)(*param_2 + 4);
    pcVar3 = *(code **)(unaff_A2 + 9);
  }
  if ((pcVar2 == (code *)0x0) || (pcVar3 == (code *)0x0)) {
    if (pcVar2 != (code *)0x0) {
      (*pcVar2)(param_1,param_2,0);
      return;
    }
    if (pcVar3 == (code *)0x0) {
      return;
    }
  }
  else {
    DAT_0002c838 = &DAT_000205ec;
    sVar1 = 0x10;
    do {
      if ((*DAT_0002c838 == *in_A1) && (DAT_0002c838[1] == *unaff_A2)) {
        piVar4 = param_1;
        piVar5 = param_2;
LAB_00016d48:
        (**(code **)(DAT_0002c838 + 2))(piVar4,piVar5,0);
        break;
      }
      if ((*DAT_0002c838 == *unaff_A2) && (DAT_0002c838[1] == *in_A1)) {
        piVar4 = param_2;
        piVar5 = param_1;
        goto LAB_00016d48;
      }
      DAT_0002c838 = DAT_0002c838 + 4;
      sVar1 = sVar1 + -1;
    } while (sVar1 != -1);
    if (sVar1 != -1) {
      return;
    }
    if ((*(byte *)((int)param_1 + 0x16) & 0x20) != 0) {
      (*pcVar2)(param_1,param_2,0);
      return;
    }
  }
  (*pcVar3)(param_2,param_1,0);
  return;
}



// FUN_00016d9a @ 0x16d9a

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_00016d9a(int param_1)

{
  byte *pbVar1;
  undefined4 *puVar2;
  int iVar3;
  short sVar4;
  undefined2 uVar5;
  ushort uVar6;
  int *piVar7;
  int *piVar8;
  int iVar9;
  undefined4 uVar10;
  int iVar11;
  int iVar12;
  short sVar13;
  ushort uVar14;
  short sVar15;
  int iVar16;
  int *piVar17;
  short *psVar18;
  short *psVar19;
  int iVar20;
  int *local_428;
  short local_41e;
  int local_404 [256];
  
  iVar11 = DAT_00023d68;
  uVar10 = DAT_00023d64;
  iVar9 = DAT_00023d60;
  DAT_00023d68 = DAT_00023d68 << (*(ushort *)(param_1 + 0x36) & 0x3f);
  DAT_00023d64 = 0;
  DAT_00023d60 = DAT_00023d60 << (*(ushort *)(param_1 + 0x36) & 0x3f);
  sVar15 = *(short *)(param_1 + 0x3a);
  iVar20 = *(int *)(param_1 + 0x46) + ((int)*(short *)(param_1 + 0x38) - (int)sVar15) * 0xc;
  iVar3 = *(int *)(param_1 + 0x4a);
  local_428 = local_404;
  psVar18 = (short *)(*(short *)(param_1 + 0x3c) * 0x20 + iVar3);
  if (*(short *)(SUB_000207fc + 0xaa) == 0) {
    sVar13 = 0;
    if (0 < sVar15) {
      do {
        puVar2 = (undefined4 *)(iVar20 + *psVar18 * 0xc);
        iVar12 = FUN_0000483e(*puVar2,0,puVar2[2]);
        *local_428 = iVar12;
        sVar13 = sVar13 + 1;
        psVar18 = psVar18 + 1;
        local_428 = local_428 + 1;
      } while (sVar13 < sVar15);
    }
  }
  else {
    sVar13 = 0;
    psVar19 = psVar18;
    if (0 < sVar15) {
      do {
        iVar16 = (int)*psVar19;
        pbVar1 = (byte *)(psVar18 + (int)sVar15 + iVar16 * 3);
        puVar2 = (undefined4 *)(iVar20 + iVar16 * 0xc);
        iVar12 = FUN_0000483e(*puVar2,0,puVar2[2]);
        *local_428 = iVar12;
        iVar12 = FUN_00004a5a(pbVar1,iVar12);
        if (iVar12 != 0) {
          iVar12 = iVar16 * 0x20 + iVar3;
          sVar4 = *(short *)(pbVar1 + 4);
          iVar16 = 0x20 - sVar4;
          *(char *)(iVar12 + 0xc) = (char)(sVar4 * 0x7d + iVar16 * (uint)*pbVar1 >> 5);
          *(char *)(iVar12 + 0xd) = (char)(sVar4 * 0x84 + iVar16 * (uint)pbVar1[1] >> 5);
          *(char *)(iVar12 + 0xe) = (char)(sVar4 * 0xa8 + (uint)pbVar1[2] * iVar16 >> 5);
          if (iVar12 == *(int *)(DAT_00023fac + (uint)*(byte *)(iVar12 + 0xf) * 4)) {
            FUN_00001bb0(iVar12);
          }
          else {
            *(undefined1 *)(iVar12 + 0xf) = 0;
          }
        }
        sVar13 = sVar13 + 1;
        psVar19 = psVar19 + 1;
        local_428 = local_428 + 1;
      } while (sVar13 < sVar15);
    }
  }
  iVar20 = *(short *)(param_1 + 0x3c) * 0x20 + iVar3;
  uVar14 = 0;
  local_428 = local_404;
  if (0 < sVar15) {
    do {
      uVar5 = *(undefined2 *)(iVar20 + (short)uVar14 * 2);
      iVar12 = *local_428;
      uVar6 = uVar14;
      while ((uVar6 = uVar6 - 1, -1 < (int)((uint)uVar6 << 0x10) &&
             (iVar16 = (int)(short)uVar6, local_404[iVar16] < iVar12))) {
        *(undefined2 *)(iVar20 + 2 + iVar16 * 2) = *(undefined2 *)(iVar20 + iVar16 * 2);
        local_404[iVar16 + 1] = local_404[iVar16];
      }
      *(undefined2 *)(iVar20 + 2 + (short)uVar6 * 2) = uVar5;
      local_404[(short)uVar6 + 1] = iVar12;
      uVar14 = uVar14 + 1;
      local_428 = local_428 + 1;
    } while ((short)uVar14 < sVar15);
  }
  psVar18 = *(short **)(param_1 + 0x2e);
  if (psVar18 != (short *)0x0) {
    local_41e = *psVar18;
    piVar8 = (int *)(psVar18 + 2);
    piVar17 = piVar8;
    piVar7 = piVar8;
    if (_DAT_000219a6 != 0) {
      while (local_41e = local_41e + -1, local_41e != -1) {
        psVar19 = (short *)*piVar17;
        if (*psVar19 == 0) {
          FUN_00002008(psVar19);
          piVar17 = piVar17 + 1;
        }
        else {
          *piVar7 = (int)psVar19;
          piVar17 = piVar17 + 1;
          piVar7 = piVar7 + 1;
        }
      }
      local_41e = (short)((int)piVar7 - (int)piVar8 >> 2);
      *psVar18 = local_41e;
    }
    if (local_41e != 0) {
      uVar14 = 0;
      if (0 < local_41e) {
        do {
          iVar20 = piVar8[(short)uVar14];
          iVar12 = FUN_0000483e(*(undefined4 *)(iVar20 + 4),0,*(undefined4 *)(iVar20 + 8));
          *(int *)(iVar20 + 0xc) = iVar12;
          uVar6 = uVar14;
          while (uVar6 = uVar6 - 1, -1 < (int)((uint)uVar6 << 0x10)) {
            if (iVar12 <= *(int *)(piVar8[(short)uVar6] + 0xc)) break;
            *(int *)(psVar18 + (short)uVar6 * 2 + 4) = piVar8[(short)uVar6];
          }
          *(int *)(psVar18 + (short)uVar6 * 2 + 4) = iVar20;
          uVar14 = uVar14 + 1;
        } while ((short)uVar14 < local_41e);
      }
      piVar17 = (int *)(psVar18 + 4);
      psVar18 = (short *)*piVar8;
      iVar20 = *(int *)(psVar18 + 6);
      local_41e = local_41e + -1;
      psVar19 = (short *)(*(short *)(param_1 + 0x3c) * 0x20 + iVar3);
      piVar8 = local_404;
      do {
        if ((short)(sVar15 + -1) == -1) {
          psVar18[1] = DAT_000219a8;
          DAT_000219a8 = *psVar18 * 0x80 + DAT_000219a8;
          while (local_41e = local_41e + -1, local_41e != -1) {
            psVar18 = (short *)*piVar17;
            psVar18[1] = DAT_000219a8;
            DAT_000219a8 = *psVar18 * 0x80 + DAT_000219a8;
            piVar17 = piVar17 + 1;
          }
          DAT_00023d60 = iVar9;
          DAT_00023d64 = uVar10;
          DAT_00023d68 = iVar11;
          return;
        }
        iVar16 = *psVar19 * 0x20 + iVar3;
        iVar12 = *piVar8;
        while (iVar12 < iVar20) {
          psVar18[1] = DAT_000219a8;
          DAT_000219a8 = DAT_000219a8 + *psVar18 * 0x80;
          local_41e = local_41e + -1;
          if (local_41e == -1) {
            *(short *)(iVar16 + 2) = DAT_000219a8;
            psVar18 = psVar19 + 1;
            for (sVar15 = sVar15 + -2; DAT_000219a8 = DAT_000219a8 + 1, sVar15 != -1;
                sVar15 = sVar15 + -1) {
              *(short *)(iVar3 + 2 + *psVar18 * 0x20) = DAT_000219a8;
              psVar18 = psVar18 + 1;
            }
            DAT_00023d60 = iVar9;
            DAT_00023d64 = uVar10;
            DAT_00023d68 = iVar11;
            return;
          }
          psVar18 = (short *)*piVar17;
          piVar17 = piVar17 + 1;
          iVar20 = *(int *)(psVar18 + 6);
        }
        *(short *)(iVar16 + 2) = DAT_000219a8;
        DAT_000219a8 = DAT_000219a8 + 1;
        psVar19 = psVar19 + 1;
        piVar8 = piVar8 + 1;
        sVar15 = sVar15 + -1;
      } while( true );
    }
  }
  psVar18 = (short *)(*(short *)(param_1 + 0x3c) * 0x20 + iVar3);
  while (sVar15 = sVar15 + -1, sVar15 != -1) {
    *(short *)(iVar3 + 2 + *psVar18 * 0x20) = DAT_000219a8;
    DAT_000219a8 = DAT_000219a8 + 1;
    psVar18 = psVar18 + 1;
  }
  DAT_00023d68 = iVar11;
  DAT_00023d64 = uVar10;
  DAT_00023d60 = iVar9;
  return;
}



// FUN_000171da @ 0x171da

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

int FUN_000171da(int param_1,short param_2,int param_3,int param_4,int param_5)

{
  int iVar1;
  short sVar6;
  int iVar2;
  short sVar7;
  undefined2 *puVar3;
  short *psVar4;
  int *piVar5;
  short sVar8;
  int iVar9;
  int *piVar10;
  
  if ((param_3 < param_2) && (*(int *)(param_4 + 0x2e) == 0)) {
    if (*(short *)(param_1 + 10) < 0) {
      iVar2 = (int)param_2 << (-(int)*(short *)(param_1 + 10) & 0x3fU);
    }
    else {
      iVar2 = (int)param_2 >> (*(ushort *)(param_1 + 10) & 0x3f);
    }
    *(int *)(param_4 + 8) = iVar2 + *(int *)(param_4 + 8);
    if ((*(byte *)(param_4 + 0x15) & 0x10) == 0) {
      FUN_00002294((int)*(short *)(param_4 + 0x3c) + (int)*(short *)(param_4 + 0x38),
                   *(undefined4 *)(param_4 + 0x46),*(undefined4 *)(param_4 + 0x46),0,
                   iVar2 << (*(ushort *)(param_4 + 0x36) & 0x3f),0);
    }
    if ((*(byte *)(param_4 + 0x15) & 8) == 0) {
      iVar1 = FUN_000047d6(param_4);
      *(int *)(param_4 + 0x18) = (iVar1 - *(int *)(param_4 + 0x1c)) + *(int *)(param_4 + 0x18);
      sVar6 = *(short *)(param_4 + 0x3c) + -1;
      if (sVar6 != -1) {
        piVar5 = (int *)(*(int *)(param_4 + 0x4a) + 0x1c);
        do {
          *piVar5 = iVar2 + *piVar5;
          piVar5 = piVar5 + 8;
          sVar6 = sVar6 + -1;
        } while (sVar6 != -1);
      }
    }
    *(byte *)(param_4 + 0x15) = *(byte *)(param_4 + 0x15) | 8;
    param_3 = (int)param_2;
  }
  if (*(short *)(DAT_00027080 + 4) == 0) {
    if (*(short *)(param_1 + 0x20) == 0) {
      iVar1 = (int)*(short *)(param_1 + 0x18) + *(short *)(param_1 + 0x1a) * 2;
      iVar2 = (int)*(short *)(param_1 + 0x1e) + *(short *)(param_1 + 0x1c) * 2;
    }
    else {
      iVar1 = (int)*(short *)(param_1 + 0x1a) + *(short *)(param_1 + 0x18) * 2;
      iVar2 = (int)*(short *)(param_1 + 0x1c) + *(short *)(param_1 + 0x1e) * 2;
    }
    sVar6 = (short)(iVar1 / 3);
    sVar8 = (short)(iVar2 / 3);
    if ((*(short *)(param_1 + 0xe) == 0) && (*(short *)(param_1 + 0x14) == 0)) {
      sVar7 = sVar8;
      if (sVar8 < 0) {
        sVar7 = sVar8 + 1;
      }
      sVar6 = sVar6 - (sVar7 >> 1);
    }
    if (*(short *)(param_1 + 10) < 0) {
      iVar2 = *(int *)(param_5 + 4) + ((int)sVar6 << (-(int)*(short *)(param_1 + 10) & 0x3fU));
      iVar1 = (int)sVar8 << (-(int)*(short *)(param_1 + 10) & 0x3fU);
    }
    else {
      iVar2 = *(int *)(param_5 + 4) + ((int)sVar6 >> ((int)*(short *)(param_1 + 10) & 0x3fU));
      iVar1 = (int)sVar8 >> ((int)*(short *)(param_1 + 10) & 0x3fU);
    }
    iVar1 = *(int *)(param_5 + 0xc) + iVar1;
  }
  else {
    iVar9 = *(int *)(param_5 + 0x46) +
            ((int)*(short *)(param_5 + 0x38) - (int)*(short *)(param_5 + 0x3a)) * 0xc;
    iVar1 = *(short *)(param_1 + 0x22 + *(short *)(param_1 + 0x20) * 0xe) * 0xc;
    iVar2 = *(int *)(iVar9 + iVar1);
    iVar1 = *(int *)(iVar9 + 8 + iVar1);
  }
  if (*(int *)(param_5 + 0x2e) == 0) {
    puVar3 = (undefined2 *)FUN_000020a0(0x1c);
    puVar3[1] = 5;
    *puVar3 = 0;
    *(undefined2 **)(param_5 + 0x2e) = puVar3;
  }
  psVar4 = *(short **)(param_4 + 0x2e);
  if (psVar4 != (short *)0x0) {
    *psVar4 = *psVar4 + -1;
    _DAT_000219a6 = 1;
  }
  piVar5 = *(int **)(param_5 + 0x2e);
  sVar6 = *(short *)piVar5;
  piVar10 = piVar5;
  do {
    sVar6 = sVar6 + -1;
    if (sVar6 == -1) break;
    piVar10 = piVar10 + 1;
    psVar4 = (short *)*piVar10;
  } while ((iVar2 != *(int *)(psVar4 + 2)) || (iVar1 != *(int *)(psVar4 + 4)));
  if (sVar6 == -1) {
    psVar4 = (short *)FUN_000021a4(0x10);
    *(int *)(psVar4 + 2) = iVar2;
    *(int *)(psVar4 + 4) = iVar1;
    if ((short)*piVar5 == *(short *)piVar5) {
      *(short *)((int)piVar5 + 2) = *(short *)((int)piVar5 + 2) + 5;
      piVar5 = (int *)FUN_000021e0(piVar5,(short)*piVar5 * 4 + 8);
      *(int **)(param_5 + 0x2e) = piVar5;
    }
    piVar5[*(short *)piVar5 + 1] = (int)psVar4;
    *(short *)piVar5 = *(short *)piVar5 + 1;
  }
  *psVar4 = *psVar4 + 1;
  *(short **)(param_4 + 0x2e) = psVar4;
  return (int)(short)param_3;
}



// FUN_0001748c @ 0x1748c

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0001748c(int param_1)

{
  short *psVar1;
  short sVar2;
  int *piVar3;
  short sVar4;
  int iVar5;
  int *piVar6;
  int *piVar7;
  
  if (((*(byte *)(param_1 + 0x15) & 8) != 0) && ((*(byte *)(param_1 + 0x15) & 0x10) == 0)) {
    iVar5 = *(int *)(SUB_000207fc + 0x8c) +
            *(int *)(*(int *)(*(int *)(param_1 + 0x24) + 0x2a) + 0x2e +
                    (uint)*(ushort *)(param_1 + 0x10) * 0x18);
    *(undefined4 *)(param_1 + 4) = *(undefined4 *)(iVar5 + 2);
    *(undefined4 *)(param_1 + 8) = *(undefined4 *)(iVar5 + 6);
    *(undefined4 *)(param_1 + 0xc) = *(undefined4 *)(iVar5 + 10);
    FUN_00003544(param_1,1,0,0);
  }
  if ((*(byte *)(param_1 + 0x16) & 2) == 0) {
    sVar2 = **(short **)(param_1 + 0x2e) + -1;
    **(short **)(param_1 + 0x2e) = sVar2;
    if (sVar2 == 0) {
      _DAT_000219a6 = 1;
    }
  }
  else {
    piVar7 = *(int **)(param_1 + 0x2e);
    if (piVar7 != (int *)0x0) {
      sVar2 = *(short *)piVar7;
      while (sVar2 = sVar2 + -1, sVar2 != -1) {
        piVar7 = piVar7 + 1;
        psVar1 = (short *)*piVar7;
        if (*psVar1 != 0) {
          piVar3 = &DAT_00020804;
          sVar4 = DAT_00020b24._2_2_;
          while (sVar4 = sVar4 + -1, sVar4 != -1) {
            piVar6 = piVar3 + 1;
            iVar5 = *piVar3;
            piVar3 = piVar6;
            if (psVar1 == *(short **)(iVar5 + 0x2e)) {
              FUN_0001748c(iVar5);
            }
          }
        }
        if (*psVar1 != 0) {
          FUN_000018bc(s_____Deactivating_Fractal__but_st_00017454);
        }
        FUN_00002008(psVar1);
      }
      FUN_00002008(*(undefined4 *)(param_1 + 0x2e));
    }
  }
  *(undefined4 *)(param_1 + 0x2e) = 0;
  return;
}



// FUN_0001758a @ 0x1758a

uint FUN_0001758a(int *param_1,short param_2,short param_3,short param_4,int param_5)

{
  int iVar1;
  int *piVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  int iVar6;
  ushort uVar7;
  uint uVar8;
  short sVar9;
  undefined4 *puVar10;
  undefined1 auStack_40 [18];
  undefined1 auStack_2e [2];
  short local_2c;
  short local_28;
  short local_24;
  undefined1 auStack_22 [2];
  short local_20;
  short local_1c;
  short local_18;
  undefined1 auStack_16 [18];
  
  iVar1 = *param_1;
  iVar3 = *(int *)(iVar1 + 4);
  iVar4 = param_1[6];
  iVar5 = *(int *)(iVar1 + 0xc);
  uVar8 = FUN_00002ee4(-(int)*(short *)((int)param_1 + 0x22) & 0x3fff,(int)param_2,
                       *(short *)((int)param_1 + 0x22) + param_3 & 0x3fff,(int)param_4,auStack_16);
  sVar9 = DAT_00020b24._2_2_ + -1;
  if (sVar9 != -1) {
    puVar10 = &DAT_00020804;
    do {
      piVar2 = (int *)*puVar10;
      uVar8 = piVar2[5] & 0x202;
      if (((uVar8 == 0) &&
          (uVar8 = FUN_000048a6(piVar2[1],piVar2[2],piVar2[3],iVar3,iVar4,iVar5),
          (int)uVar8 < *(int *)(iVar1 + 0x20))) && (*piVar2 != 0)) {
        uVar7 = **(ushort **)(*piVar2 + 4);
        uVar8 = (uint)uVar7;
        if ((uVar7 == 10) || (uVar7 == 0xb)) {
          if (param_5 == 0) {
            piVar2[3] = 0;
            piVar2[2] = 0;
            piVar2[1] = 0;
            FUN_00003544(piVar2,1,0,0);
            local_20 = *(short *)((int)piVar2 + 6) - (short)iVar3;
            local_1c = *(short *)((int)piVar2 + 10) - (short)iVar4;
            local_18 = *(short *)((int)piVar2 + 0xe) - (short)iVar5;
            uVar8 = FUN_00003220(1,auStack_22,auStack_22,auStack_16);
            piVar2[1] = iVar3 + local_20;
            piVar2[2] = (*(int *)(iVar1 + 8) + (int)local_1c) - iVar4;
            piVar2[3] = iVar5 + local_18;
            if (-1 < piVar2[2]) goto LAB_000176d2;
          }
          uVar8 = FUN_000060fc(piVar2);
        }
      }
LAB_000176d2:
      sVar9 = sVar9 + -1;
      puVar10 = puVar10 + 1;
    } while (sVar9 != -1);
  }
  sVar9 = 0;
  if (0 < *(short *)(param_1 + 0x19e)) {
    do {
      iVar6 = *(int *)((int)param_1 + sVar9 * 4 + 0x67a);
      if (((iVar6 != 0) && (uVar8 = *(uint *)(iVar6 + 0x14) & 0x202, uVar8 == 0)) &&
         (uVar8 = FUN_000048a6(*(undefined4 *)(iVar6 + 4),*(undefined4 *)(iVar6 + 8),
                               *(undefined4 *)(iVar6 + 0xc),iVar3,iVar4,iVar5),
         (int)uVar8 < *(int *)(iVar1 + 0x20))) {
        if (param_5 == 0) {
          *(undefined4 *)(iVar6 + 0xc) = 0;
          *(undefined4 *)(iVar6 + 8) = 0;
          *(undefined4 *)(iVar6 + 4) = 0;
          FUN_00003544(iVar6,1,0,0);
          local_2c = *(short *)(iVar6 + 6) - *(short *)((int)param_1 + 0x16);
          local_28 = *(short *)(iVar6 + 10) - *(short *)((int)param_1 + 0x1a);
          local_24 = *(short *)(iVar6 + 0xe) - *(short *)((int)param_1 + 0x1e);
          FUN_00002d02(0,(int)*(short *)(param_1 + 0x195),0,auStack_40);
          (*(code *)&LAB_000031fe_2)(1,auStack_2e,auStack_2e,auStack_40);
          uVar8 = (*(code *)&LAB_000031fe_2)(1,auStack_2e,auStack_2e,auStack_16);
          *(int *)(iVar6 + 4) = iVar3 + local_2c;
          *(int *)(iVar6 + 8) = (*(int *)(iVar1 + 8) + (int)local_28) - iVar4;
          *(int *)(iVar6 + 0xc) = iVar5 + local_24;
          if (-1 < *(int *)(iVar6 + 8)) goto LAB_000177dc;
        }
        uVar8 = FUN_000060fc(iVar6);
      }
LAB_000177dc:
      sVar9 = sVar9 + 1;
    } while (sVar9 < *(short *)(param_1 + 0x19e));
  }
  return uVar8;
}



// FUN_000177f0 @ 0x177f0

void FUN_000177f0(int *param_1,short param_2,short param_3,short param_4)

{
  int iVar1;
  ushort uVar2;
  
  iVar1 = *param_1;
  *(undefined1 *)(iVar1 + 0x61a) = 1;
  uVar2 = *(short *)(iVar1 + 0x654) + param_3 & 0x3fff;
  if (*(char *)(iVar1 + 0x61b) == '\0') {
    FUN_0000371a(param_1,iVar1 + 0x630);
    *(undefined1 *)(iVar1 + 0x61b) = 1;
    FUN_0001972a(param_1,(int)param_2,uVar2,(int)param_4,iVar1 + 0x630);
  }
  else {
    FUN_0001972a(param_1,(int)param_2,uVar2,(int)param_4,iVar1 + 0x630);
  }
  return;
}



// FUN_00017870 @ 0x17870

void FUN_00017870(int *param_1)

{
  int iVar1;
  short sVar2;
  int iVar3;
  
  if ((param_1[0x19b] <= DAT_00021020) && (iVar1 = *param_1, (*(byte *)(iVar1 + 0x17) & 2) == 0)) {
    *(ushort *)(param_1 + 0x196) =
         *(short *)((int)param_1 + 0x65e) + *(short *)(param_1 + 0x196) & 0x3fff;
    *(ushort *)((int)param_1 + 0x65a) =
         *(short *)(param_1 + 0x198) + *(short *)((int)param_1 + 0x65a) & 0x3fff;
    *(ushort *)(param_1 + 0x197) =
         *(short *)((int)param_1 + 0x662) + *(short *)(param_1 + 0x197) & 0x3fff;
    *(short *)(param_1 + 0x199) = *(short *)((int)param_1 + 0x666) + *(short *)(param_1 + 0x199);
    *(int *)(iVar1 + 8) = *(int *)(iVar1 + 8) - (int)*(short *)((int)param_1 + 0x666);
    if ((0x1a < *(short *)param_1[1]) && (*(short *)param_1[1] < 0x1d)) {
      *(short *)((int)param_1 + 0x696) =
           *(short *)((int)param_1 + 0x696) - *(short *)((int)param_1 + 0x666);
    }
    sVar2 = *(short *)((int)param_1 + 0x66a) + -1;
    *(short *)((int)param_1 + 0x66a) = sVar2;
    if (sVar2 == 0) {
      param_1[0x19b] = 1000000000;
    }
    else {
      param_1[0x19b] = DAT_00021020 + *(short *)(param_1 + 0x19a);
    }
    iVar3 = FUN_000177f0(iVar1,(int)*(short *)(param_1 + 0x196),
                         (int)*(short *)((int)param_1 + 0x65a),(int)*(short *)(param_1 + 0x197));
    if (iVar3 == 0) {
      FUN_0001758a(param_1,0,0,0,1);
      if (*(short *)((int)param_1 + 0x672) != 0) {
        FUN_00000602(iVar1);
      }
      if ((*(short *)param_1[1] == 0x1b) &&
         (((*(short *)(SUB_000207fc + 0x90) == 8 || (*(short *)(SUB_000207fc + 0x90) == 2)) &&
          (-1 < *(short *)(SUB_000207fc + 0xd2))))) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 0xffff;
      }
      if (((*(short *)(SUB_000207fc + 0x90) == 4) && (*(short *)param_1[1] != 0x1b)) &&
         ((DAT_00027060 = DAT_00027060 + 1, DAT_00027060 == 3 &&
          (*(short *)(SUB_000207fc + 0xd2) == 0)))) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
      }
      if (((*(short *)param_1[1] == 0x20) &&
          (DAT_00027056 = *(short *)((int)param_1 + 0x696) + DAT_00027056, DAT_00027056 == 0xc)) &&
         (*(short *)(SUB_000207fc + 0xd2) == 0)) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
      }
      if ((((*(short *)(SUB_000207fc + 0x90) == 9) && (*(short *)param_1[1] == 0x1b)) &&
          (*(short *)((int)param_1 + 0x69a) == -1)) && (*(short *)(SUB_000207fc + 0xd2) == 0)) {
        *(undefined2 *)(SUB_000207fc + 0xd2) = 1;
      }
      FUN_000060fc(iVar1);
    }
    else {
      FUN_0001758a(param_1,(int)*(short *)(param_1 + 0x196),(int)*(short *)((int)param_1 + 0x65a),
                   (int)*(short *)(param_1 + 0x197),0);
    }
    if (*(short *)((int)param_1 + 0x656) == 0) {
      *(short *)((int)param_1 + 0x66a) = *(short *)((int)param_1 + 0x66a) + 1;
    }
  }
  return;
}



// FUN_00017a4a @ 0x17a4a

void FUN_00017a4a(int param_1,int param_2)

{
  uint uVar1;
  short sVar2;
  ushort uVar3;
  short in_D1w;
  short sVar4;
  short sVar5;
  
  if ((param_1 == DAT_0002104c) && (DAT_00023d84 == 2)) {
    DAT_00023d84 = 0;
  }
  if (param_2 != 0) {
    *(undefined2 *)(param_1 + 0x672) = 1;
  }
  if (*(short *)(param_1 + 0x656) == 0) {
    return;
  }
  *(short *)(param_1 + 0x656) = *(short *)(param_1 + 0x656) + -1;
  sVar5 = 0;
  sVar4 = 0;
  sVar2 = *(short *)(param_1 + 0x656);
  if (sVar2 == 1) {
    uVar3 = FUN_00013652();
    sVar4 = (uVar3 & 0x3f) + 0x100;
    uVar1 = FUN_00013652();
    if ((uVar1 & 1) != 0) {
      sVar4 = -sVar4;
    }
    uVar1 = FUN_00013672();
    in_D1w = (short)((uVar1 & 0xffff) % 100) + 100;
    sVar2 = *(short *)(param_1 + 0x670);
joined_r0x00017b2c:
    if (sVar2 != 0) goto LAB_00017b62;
  }
  else {
    if (sVar2 < 2) {
      if (sVar2 != 0) goto LAB_00017b62;
      uVar1 = FUN_00013672();
      sVar4 = (short)((uVar1 & 0xffff) % 0x140) + 0x200;
      sVar2 = FUN_0000f87c((int)*(short *)(param_1 + 0x658));
      if (sVar2 < 0) {
        sVar4 = -sVar4;
      }
      uVar1 = FUN_00013672();
      in_D1w = (short)((uVar1 & 0xffff) % 200) + 1000;
      sVar2 = *(short *)(param_1 + 0x670);
      goto joined_r0x00017b2c;
    }
    if (sVar2 != 2) goto LAB_00017b62;
  }
  uVar3 = FUN_00013652();
  sVar5 = (uVar3 & 0x7f) + 0x200;
  uVar1 = FUN_00013652();
  if ((uVar1 & 1) != 0) {
    sVar5 = -sVar5;
  }
  uVar1 = FUN_00013672();
  in_D1w = (short)((uVar1 & 0xffff) % 100) + 100;
LAB_00017b62:
  if ((**(short **)(param_1 + 4) == 0x1b) && (*(short *)(param_1 + 0x69a) == -1)) {
    if (sVar4 < 0) {
      sVar4 = sVar4 + 3;
    }
    sVar4 = sVar4 >> 2;
  }
  *(undefined2 *)(param_1 + 0x66a) = 0xf;
  *(short *)(param_1 + 0x65e) = sVar4 / *(short *)(param_1 + 0x66a);
  *(short *)(param_1 + 0x660) = (short)(0 / (int)*(short *)(param_1 + 0x66a));
  *(short *)(param_1 + 0x662) = sVar5 / *(short *)(param_1 + 0x66a);
  *(short *)(param_1 + 0x666) = in_D1w / *(short *)(param_1 + 0x66a);
  *(int *)(param_1 + 0x66c) = DAT_00021020 + 0x1e;
  *(undefined2 *)(param_1 + 0x670) = 1;
  return;
}



// FUN_00017bc2 @ 0x17bc2

int * FUN_00017bc2(int param_1,int param_2,int param_3)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_000021a4(0x69c);
  *piVar1 = param_1;
  *(short *)((int)piVar1 + 0x32) = (short)param_3;
  *(ushort *)(param_1 + 0x16) = *(ushort *)(param_1 + 0x16) | 0x4000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(undefined2 *)(piVar1 + 0x1a6) = 0xffff;
  *(undefined2 *)((int)piVar1 + 0x69a) = 0xffff;
  piVar1[0x19b] = 1000000000;
  *(undefined2 *)((int)piVar1 + 0x656) = *(undefined2 *)(piVar1[1] + 0x1a);
  *(undefined2 *)(piVar1 + 0x19a) = 7;
  if ((DAT_0002104c == (int *)0x0) && (DAT_00023d84 != 0)) {
    iVar2 = FUN_000048a6(DAT_00023d6c,DAT_00023d70,DAT_00023d74,*(undefined4 *)(param_1 + 4),
                         *(undefined4 *)(param_1 + 8),*(undefined4 *)(param_1 + 0xc));
    if (iVar2 <= *(int *)(param_1 + 0x20)) {
      DAT_00023d84 = 2;
      DAT_0002104c = piVar1;
    }
  }
  piVar1[0xe] = 0x18e20;
  if (param_3 != 0) {
    *(undefined4 *)(param_1 + 0x18) = 1000000000;
  }
  return piVar1;
}



// FUN_00017c86 @ 0x17c86

void FUN_00017c86(int param_1)

{
  char *pcVar1;
  int iVar2;
  int *piVar3;
  
  iVar2 = 0;
  if (0 < *(short *)(param_1 + 0x678)) {
    piVar3 = (int *)(param_1 + 0x67a);
    do {
      if (*piVar3 != 0) {
        *(char *)(*piVar3 + 0x29) = *(char *)(*piVar3 + 0x29) + -1;
        FUN_000060fc(*piVar3);
      }
      piVar3 = piVar3 + 1;
      iVar2 = iVar2 + 1;
    } while (iVar2 < *(short *)(param_1 + 0x678));
  }
  if (*(int *)(param_1 + 0x68e) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x68e) + 0x29);
    *pcVar1 = *pcVar1 + -1;
    FUN_000060fc(*(undefined4 *)(param_1 + 0x68e));
  }
  if (*(int *)(param_1 + 0x34) != 0) {
    FUN_00002008(*(undefined4 *)(param_1 + 0x34));
  }
  return;
}



// FUN_00017d0e @ 0x17d0e

void FUN_00017d0e(int param_1,int param_2)

{
  short *psVar1;
  
  if (*(short *)(param_1 + 0x678) < 4) {
    psVar1 = (short *)(param_1 + 0x678);
    *(int *)(param_1 + *psVar1 * 4 + 0x67a) = param_2;
    *psVar1 = *psVar1 + 1;
    *(char *)(param_2 + 0x29) = *(char *)(param_2 + 0x29) + '\x01';
  }
  else {
    FUN_000018bc(s_Too_many_AA_guns_on_a_ship_00017cf2);
  }
  return;
}



// FUN_00017d4a @ 0x17d4a

void FUN_00017d4a(int param_1,int param_2,int param_3,int param_4)

{
  undefined4 *puVar1;
  int *piVar2;
  int iVar3;
  
  iVar3 = *(short *)(param_1 + 0x678) + -1;
  if (iVar3 != -1) {
    piVar2 = (int *)(param_1 + 0x67a + iVar3 * 4);
    do {
      if (*piVar2 != 0) {
        puVar1 = (undefined4 *)*piVar2;
        if (((*(byte *)((int)puVar1 + 0x17) & 2) == 0) || (*(int *)*puVar1 != 0)) {
          puVar1[1] = param_2 + puVar1[1];
          puVar1[2] = param_3 + puVar1[2];
          puVar1[3] = param_4 + puVar1[3];
        }
        else {
          *(char *)((int)puVar1 + 0x29) = *(char *)((int)puVar1 + 0x29) + -1;
          *piVar2 = 0;
        }
      }
      piVar2 = piVar2 + -1;
      iVar3 = iVar3 + -1;
    } while (iVar3 != -1);
  }
  return;
}



// FUN_00017dac @ 0x17dac

void FUN_00017dac(int *param_1)

{
  int iVar1;
  
  if (*(char *)((int)param_1 + 0x619) != '\0') {
    iVar1 = *param_1;
    if (param_1[0x18a] == 0) {
      FUN_00002294((int)*(short *)(iVar1 + 0x3c) + (int)*(short *)(iVar1 + 0x38),
                   *(undefined4 *)(iVar1 + 0x46),*(undefined4 *)(iVar1 + 0x46),
                   param_1[0x189] << ((int)*(short *)(iVar1 + 0x36) & 0x3fU),0,
                   param_1[0x18b] << ((int)*(short *)(iVar1 + 0x36) & 0x3fU));
    }
    else {
      FUN_000177f0(iVar1,0,0,0);
    }
    *(undefined1 *)((int)param_1 + 0x619) = 0;
    param_1[0x189] = 0;
    param_1[0x18a] = 0;
    param_1[0x18b] = 0;
  }
  return;
}



// FUN_00017e1a @ 0x17e1a

undefined4 FUN_00017e1a(int param_1)

{
  if (*(char *)(param_1 + 0x619) != '\0') {
    FUN_00017dac(param_1);
  }
  return 0;
}



// FUN_00017e2e @ 0x17e2e

void FUN_00017e2e(int *param_1,int *param_2,int param_3)

{
  if (((*param_2 != 0) && (param_3 != 0)) && (*(char *)(*param_1 + 0x619) != '\0')) {
    FUN_00017dac(*param_1);
  }
  return;
}



// FUN_000180a8 @ 0x180a8

void FUN_000180a8(undefined4 param_1,int param_2)

{
  undefined4 *puVar1;
  
  puVar1 = (undefined4 *)FUN_000021a4(0x772);
  *puVar1 = param_1;
  puVar1[1] = *(undefined4 *)(&DAT_0002074c + param_2 * 4);
  *(undefined2 *)((int)puVar1 + 0x696) = 0x14;
  puVar1[0x19b] = 1000000000;
  *(undefined2 *)(puVar1 + 0x19a) = 7;
  *(undefined2 *)((int)puVar1 + 0x656) = *(undefined2 *)(puVar1[1] + 0x1a);
  return;
}



// FUN_000180f0 @ 0x180f0

undefined2
FUN_000180f0(int param_1,short *param_2,int *param_3,int *param_4,int *param_5,undefined4 param_6,
            undefined4 param_7)

{
  int iVar1;
  int *piVar2;
  int iVar3;
  int iVar4;
  undefined2 uVar5;
  short sVar6;
  uint uVar7;
  int iVar8;
  int iVar9;
  int iVar10;
  short *psVar11;
  short *psVar12;
  int local_c;
  
  uVar5 = 0;
  iVar3 = *(int *)(param_1 + 0x46) + *(short *)(param_1 + 0x38) * 0xc;
  local_c = *(int *)(param_1 + 0x4a) + *param_2 * 0x20;
  if (*param_2 < *(short *)(param_1 + 0x3c)) {
    psVar12 = (short *)(local_c + 0x14);
    do {
      if (*(char *)(local_c + 0x17) == '\x01') {
        iVar10 = 0;
        iVar9 = 0;
        iVar8 = 0;
        sVar6 = *psVar12 + -1;
        if (sVar6 != -1) {
          iVar1 = *(int *)(param_1 + 0x46);
          uVar7 = (uint)*(short *)(param_1 + 0x36);
          psVar11 = *(short **)(local_c + 0x18);
          do {
            iVar4 = *psVar11 * 0xc;
            iVar8 = (*(int *)(iVar1 + iVar4) >> (uVar7 & 0x3f)) + iVar8;
            iVar9 = (*(int *)(iVar1 + 4 + iVar4) >> (uVar7 & 0x3f)) + iVar9;
            iVar10 = (*(int *)(iVar1 + 8 + iVar4) >> (uVar7 & 0x3f)) + iVar10;
            sVar6 = sVar6 + -1;
            psVar11 = psVar11 + 1;
          } while (sVar6 != -1);
        }
        *param_3 = iVar8 / (int)*psVar12;
        *param_4 = iVar9 / (int)*psVar12;
        *param_5 = iVar10 / (int)*psVar12;
        iVar8 = *param_2 * 0xc;
        piVar2 = *(int **)(param_1 + 0x46);
        FUN_00002574(*(int *)(iVar3 + iVar8) - *piVar2,*(int *)(iVar3 + 4 + iVar8) - piVar2[1],
                     *(int *)(iVar3 + 8 + iVar8) - piVar2[2],0x4000,0x10000000,param_6,param_7);
        uVar5 = 1;
      }
      param_2 = param_2 + 1;
      psVar12 = psVar12 + 0x10;
      local_c = local_c + 0x20;
    } while (*param_2 < *(short *)(param_1 + 0x3c));
  }
  return uVar5;
}



// FUN_00018230 @ 0x18230

void FUN_00018230(undefined4 *param_1)

{
  undefined4 *puVar1;
  undefined4 uVar2;
  short sVar3;
  char cVar5;
  ushort uVar4;
  short sVar6;
  int iVar7;
  ushort local_16;
  ushort local_14;
  undefined4 local_12;
  undefined4 local_e;
  undefined4 local_a;
  short local_6;
  
  uVar2 = *param_1;
  if (*(short *)(param_1 + 0x19c) == 0) {
    if (*(short *)(param_1 + 0x1a6) == 0) {
      local_6 = 0;
      sVar6 = 0;
      while( true ) {
        sVar3 = FUN_000180f0(uVar2,&local_6,&local_a,&local_e,&local_12,&local_14,&local_16);
        if (sVar3 == 0) break;
        iVar7 = (int)sVar6;
        *(undefined4 *)((int)param_1 + iVar7 * 0xc + 0x69a) = local_a;
        *(undefined4 *)((int)param_1 + iVar7 * 0xc + 0x69e) = local_e;
        *(undefined4 *)((int)param_1 + iVar7 * 0xc + 0x6a2) = local_12;
        *(ushort *)((int)param_1 + iVar7 * 6 + 0x72a) = local_14;
        *(ushort *)((int)param_1 + iVar7 * 6 + 0x72c) = local_16;
        *(undefined2 *)((int)param_1 + iVar7 * 6 + 0x72e) = 0;
        sVar6 = sVar6 + 1;
      }
      *(short *)(param_1 + 0x1a6) = sVar6;
    }
    if (*(short *)((int)param_1 + 0x696) != 0) {
      cVar5 = FUN_00013652();
      if (cVar5 == '\x1b') {
        *(short *)((int)param_1 + 0x696) = *(short *)((int)param_1 + 0x696) + -1;
        uVar4 = FUN_00013652();
        local_6 = (short)((int)(uint)uVar4 % (int)*(short *)(param_1 + 0x1a6));
        puVar1 = (undefined4 *)((int)param_1 + local_6 * 0xc + 0x69a);
        uVar4 = FUN_00013652();
        local_14 = (*(short *)((int)param_1 + local_6 * 6 + 0x72a) + (uVar4 & 0xff)) - 0x80 & 0x3fff
        ;
        uVar4 = FUN_00013652();
        local_16 = (*(short *)((int)param_1 + local_6 * 6 + 0x72c) + (uVar4 & 0xff)) - 0x80 & 0x3fff
        ;
        if (DAT_00021058 == 0) {
          uVar4 = FUN_00013652(1,*puVar1,puVar1[1] + 10,puVar1[2],(int)(short)local_14,local_16,0);
          FUN_000149fa(uVar2,0x7fff - (uint)(uVar4 & 0xff));
        }
      }
    }
  }
  else {
    FUN_00017870(param_1);
  }
  return;
}



// FUN_00018396 @ 0x18396

void FUN_00018396(int *param_1)

{
  int iVar1;
  int iVar2;
  short sVar5;
  int iVar3;
  ushort uVar6;
  short sVar7;
  int iVar4;
  byte *pbVar8;
  
  iVar3 = *param_1;
  if (*(short *)(param_1 + 0x1a6) == 0) {
    if (*(short *)((int)param_1 + 0x69a) < 1) {
      FUN_00018e40(param_1);
      if (*(short *)((int)param_1 + 0x69a) < 0) {
        iVar2 = FUN_00015c4c(*(int *)(iVar3 + 4) + (int)*(short *)((int)param_1 + 0x64e),0,
                             *(int *)(iVar3 + 0xc) + (int)*(short *)((int)param_1 + 0x652));
        if (iVar2 != 0x10) {
          *(undefined2 *)(param_1 + 0x1a6) = 1;
          if (*(int *)((int)param_1 + 0x68e) != 0) {
            FUN_000060fc(*(undefined4 *)((int)param_1 + 0x68e));
          }
          iVar2 = *(int *)(iVar3 + 0x4a);
          sVar5 = *(short *)(iVar3 + 0x3c) + -1;
          if (sVar5 != -1) {
            pbVar8 = (byte *)(iVar2 + 0x13);
            do {
              if (*(char *)(iVar2 + 0x17) == '\x01') {
                *pbVar8 = *pbVar8 ^ 2;
              }
              pbVar8 = pbVar8 + 0x20;
              iVar2 = iVar2 + 0x20;
              sVar5 = sVar5 + -1;
            } while (sVar5 != -1);
          }
        }
      }
      else {
        iVar3 = FUN_00015c4c(*(int *)(iVar3 + 4) + *(short *)((int)param_1 + 0x64e) * 0x2d,0,
                             *(int *)(iVar3 + 0xc) + *(short *)((int)param_1 + 0x652) * 0x2d);
        if (iVar3 == 0x10) {
          *(undefined2 *)((int)param_1 + 0x69a) = 0x2d;
        }
        else {
          *(undefined2 *)((int)param_1 + 0x69a) = 0xffff;
        }
      }
    }
    else {
      *(short *)((int)param_1 + 0x69a) = *(short *)((int)param_1 + 0x69a) + -1;
      FUN_00018e40(param_1);
    }
  }
  else if (*(short *)(param_1 + 0x19c) == 0) {
    if ((*(short *)((int)param_1 + 0x696) != 0) && (param_1[0x1a7] <= DAT_00021020)) {
      sVar5 = 0;
      do {
        uVar6 = FUN_00013672();
        uVar6 = (*(short *)((int)param_1 + 0x22) + (uVar6 & 0x4ff)) - 0x280 & 0x3fff;
        sVar7 = FUN_000023c4(uVar6);
        iVar1 = *(int *)(iVar3 + 4) - (sVar7 * 100 >> 0xc);
        sVar7 = FUN_00002426(uVar6);
        iVar2 = *(int *)(iVar3 + 0xc) + (sVar7 * 100 >> 0xc);
        iVar4 = FUN_00015c4c(iVar1,0,iVar2);
        if (iVar4 != 0x10) {
          FUN_0001edc0(0x27,uVar6,iVar1,0,iVar2,0,uVar6,0);
          break;
        }
        sVar5 = sVar5 + 1;
      } while (sVar5 < 0x14);
      *(short *)((int)param_1 + 0x696) = *(short *)((int)param_1 + 0x696) + -1;
      uVar6 = FUN_00013672();
      param_1[0x1a7] = DAT_00021020 + (uVar6 & 0x3f) + 0x3c;
    }
  }
  else {
    FUN_00017870(param_1);
    param_1[2] = 0;
    *(undefined2 *)((int)param_1 + 0x64e) = 0;
    param_1[4] = 0;
    *(undefined2 *)((int)param_1 + 0x652) = 0;
    param_1[0x19d] = 0;
  }
  return;
}



// FUN_00018564 @ 0x18564

int * FUN_00018564(int param_1,int param_2,int param_3)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x6a0);
  *piVar1 = param_1;
  *(short *)((int)piVar1 + 0x32) = (short)param_3;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  piVar1[0x19b] = 1000000000;
  *(undefined2 *)(piVar1 + 0x19a) = 3;
  *(undefined2 *)((int)piVar1 + 0x656) = *(undefined2 *)(piVar1[1] + 0x1a);
  piVar1[0xe] = 0x18376;
  *(undefined2 *)((int)piVar1 + 0x696) = 2;
  *(undefined2 *)(piVar1 + 0x1a6) = 0;
  if (param_3 != 0) {
    *(undefined4 *)(param_1 + 0x18) = 1000000000;
  }
  return piVar1;
}



// FUN_000185da @ 0x185da

int * FUN_000185da(int param_1,int param_2,int param_3)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x6a8);
  *piVar1 = param_1;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(short *)((int)piVar1 + 0x32) = (short)param_3;
  piVar1[0x19b] = 1000000000;
  *(undefined2 *)(piVar1 + 0x19a) = 7;
  *(undefined2 *)((int)piVar1 + 0x656) = *(undefined2 *)(piVar1[1] + 0x1a);
  *(undefined2 *)((int)piVar1 + 0x696) = 3;
  *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 2;
  if (param_3 != 0) {
    *(undefined4 *)(param_1 + 0x18) = 1000000000;
  }
  return piVar1;
}



// FUN_0001864a @ 0x1864a

int * FUN_0001864a(int param_1,int param_2,int param_3)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x696);
  *piVar1 = param_1;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(short *)((int)piVar1 + 0x32) = (short)param_3;
  if (param_3 != 0) {
    *(undefined4 *)(param_1 + 0x18) = 1000000000;
    *(ushort *)(param_1 + 0x16) = *(ushort *)(param_1 + 0x16) | 0x4000;
  }
  piVar1[0xe] = 0x18e20;
  piVar1[0x19b] = 1000000000;
  *(undefined2 *)(piVar1 + 0x19a) = 7;
  *(undefined2 *)((int)piVar1 + 0x656) = *(undefined2 *)(piVar1[1] + 0x1a);
  return piVar1;
}



// FUN_000186bc @ 0x186bc

undefined4 FUN_000186bc(int param_1,short *param_2,undefined4 *param_3,short param_4)

{
  short *psVar1;
  int iVar2;
  undefined4 uVar3;
  int iVar4;
  
  psVar1 = param_2 + *param_2 * 6 + 2;
  if (param_4 < 0x871) {
    iVar4 = 0x10e0;
  }
  else {
    iVar4 = 10000;
  }
  iVar2 = FUN_000048a6(*(undefined4 *)(param_1 + 4),0,*(undefined4 *)(param_1 + 0xc),
                       *(undefined4 *)psVar1,0,*(undefined4 *)(psVar1 + 4));
  if (iVar2 < iVar4) {
    *param_2 = *param_2 + 1;
    if (param_2[1] <= *param_2) {
      *param_2 = 0;
    }
    *param_3 = param_2 + *param_2 * 6 + 2;
    uVar3 = 1;
  }
  else {
    *param_3 = psVar1;
    uVar3 = 0;
  }
  return uVar3;
}



// FUN_00018732 @ 0x18732

int FUN_00018732(int param_1,int param_2,undefined4 param_3,int param_4)

{
  int iVar1;
  int iVar2;
  uint uVar3;
  short sVar4;
  short local_6;
  
  param_2 = param_2 - *(int *)(param_1 + 4);
  iVar1 = FUN_000038e8(param_2);
  param_4 = param_4 - *(int *)(param_1 + 0xc);
  iVar2 = FUN_000038e8(param_4);
  sVar4 = 0;
  for (uVar3 = iVar2 + iVar1; (uVar3 & 0xffff8000) != 0; uVar3 = (int)uVar3 >> 1) {
    sVar4 = sVar4 + 1;
  }
  FUN_00002574(param_2 >> ((int)sVar4 & 0x3fU),0,param_4 >> ((int)sVar4 & 0x3fU),0,0,0,&local_6);
  return (int)local_6;
}



// FUN_000187a4 @ 0x187a4

void FUN_000187a4(int *param_1,short param_2)

{
  int iVar1;
  uint uVar2;
  undefined4 uVar3;
  ushort uVar4;
  short sVar5;
  short sVar6;
  int iVar7;
  int iVar8;
  undefined1 auStack_28 [18];
  undefined1 auStack_16 [18];
  
  iVar1 = *param_1;
  sVar6 = *(short *)(param_1[0x188] + 0x1a);
  sVar5 = *(short *)(param_1[0x188] + 0x1e);
  FUN_00002d02(0,(int)param_2,0,auStack_16);
  FUN_00002346(param_1 + 0x18c,auStack_16,auStack_28);
  iVar7 = (int)(short)(sVar5 + sVar6);
  FUN_000032c0(iVar7,param_1[0x188] + 0x22,*(undefined4 *)(iVar1 + 0x46),auStack_28);
  uVar2 = (uint)*(short *)(iVar1 + 0x36);
  FUN_00002294(iVar7,*(undefined4 *)(iVar1 + 0x46),*(undefined4 *)(iVar1 + 0x46),
               *(int *)((int)param_1 + 0x642) + (*(int *)(iVar1 + 4) - param_1[5]) << (uVar2 & 0x3f)
               ,*(int *)((int)param_1 + 0x646) + (*(int *)(iVar1 + 8) - param_1[6]) <<
                (uVar2 & 0x3f),
               *(int *)((int)param_1 + 0x64a) + (*(int *)(iVar1 + 0xc) - param_1[7]) <<
               (uVar2 & 0x3f));
  if (*(char *)((int)param_1 + 0x61a) != '\0') {
    iVar7 = iVar7 * 0xc;
    uVar3 = (*(code *)&LAB_000021be_2)(*(undefined4 *)(iVar1 + 0x4e),iVar7);
    *(undefined4 *)(iVar1 + 0x4e) = uVar3;
    uVar3 = (*(code *)&LAB_000021be_2)(*(undefined4 *)(iVar1 + 0x46),iVar7);
    *(undefined4 *)(iVar1 + 0x46) = uVar3;
    iVar7 = param_1[0x188] + iVar7 + 0x22;
    for (sVar6 = 0; sVar6 < *(short *)(param_1[0x188] + 0x1e); sVar6 = sVar6 + 1) {
      uVar4 = (ushort)*(byte *)(iVar7 + 8);
      iVar8 = *(int *)(iVar1 + 0x4a) + sVar6 * 0x20;
      uVar3 = FUN_000021e0(*(undefined4 *)(iVar8 + 0x18),(int)(short)uVar4 << 1);
      *(undefined4 *)(iVar8 + 0x18) = uVar3;
      *(ushort *)(iVar8 + 0x14) = uVar4;
      sVar5 = 0;
      if (uVar4 != 0) {
        do {
          *(undefined2 *)(*(int *)(iVar8 + 0x18) + sVar5 * 2) =
               *(undefined2 *)(iVar7 + 0xe + (short)uVar4 * 2);
          sVar5 = sVar5 + 1;
        } while (sVar5 < (short)uVar4);
      }
      iVar7 = iVar7 + 0xe + (uint)*(byte *)(iVar7 + 0x16) * 2;
    }
    *(undefined2 *)(iVar1 + 0x38) = *(undefined2 *)(param_1[0x188] + 0x1a);
    *(undefined1 *)((int)param_1 + 0x61a) = 0;
  }
  if (*(short *)(SUB_000207fc + 0xb2) != 0) {
    FUN_00004b0c(iVar1);
  }
  FUN_00016136(iVar1);
  return;
}



// FUN_00018928 @ 0x18928

void FUN_00018928(int *param_1,ushort param_2)

{
  int iVar1;
  int *piVar2;
  int iVar3;
  int iVar4;
  short sVar5;
  ushort uVar6;
  undefined1 auStack_34 [2];
  short local_32;
  short local_2e;
  short local_2a;
  undefined1 auStack_16 [18];
  
  iVar1 = *param_1;
  *(ushort *)(param_1 + 0x195) = *(short *)(param_1 + 0x195) + param_2 & 0x3fff;
  FUN_00002d02(0,param_2 & 0x3fff,0,auStack_16);
  sVar5 = *(short *)(param_1 + 0x19e) + -1;
  if (sVar5 != -1) {
    do {
      if (*(int *)((int)param_1 + sVar5 * 4 + 0x67a) != 0) {
        iVar3 = *(int *)((int)param_1 + sVar5 * 4 + 0x67a);
        local_32 = *(short *)(iVar3 + 6) - *(short *)(iVar1 + 6);
        local_2e = *(short *)(iVar3 + 10) - *(short *)(iVar1 + 10);
        local_2a = *(short *)(iVar3 + 0xe) - *(short *)(iVar1 + 0xe);
        FUN_00003220(1,auStack_34,auStack_34,auStack_16);
        *(int *)(iVar3 + 4) = *(int *)(iVar1 + 4) + (int)local_32;
        *(int *)(iVar3 + 8) = *(int *)(iVar1 + 8) + (int)local_2e;
        *(int *)(iVar3 + 0xc) = *(int *)(iVar1 + 0xc) + (int)local_2a;
      }
      sVar5 = sVar5 + -1;
    } while (sVar5 != -1);
  }
  uVar6 = 0;
  do {
    piVar2 = *(int **)(&DAT_00020674)[(short)uVar6];
    if ((piVar2 != (int *)0x0) && (iVar3 = *piVar2, *(char *)(piVar2 + 0x193) != '\0')) {
      iVar4 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),*(undefined4 *)(iVar3 + 8),
                           *(undefined4 *)(iVar3 + 0xc),*(undefined4 *)(iVar1 + 4),
                           *(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc));
      if (iVar4 < *(int *)(iVar1 + 0x20)) {
        local_32 = *(short *)(iVar3 + 6) - *(short *)(iVar1 + 6);
        local_2a = *(short *)(iVar3 + 0xe) - *(short *)(iVar1 + 0xe);
        FUN_00003220(1,auStack_34,auStack_34,auStack_16);
        *(int *)(iVar3 + 4) = *(int *)(iVar1 + 4) + (int)local_32;
        *(int *)(iVar3 + 0xc) = *(int *)(iVar1 + 0xc) + (int)local_2a;
        *(ushort *)((int)piVar2 + 0x22) = *(short *)((int)piVar2 + 0x22) + param_2 & 0x3fff;
      }
    }
    uVar6 = uVar6 + 1;
  } while (uVar6 < 2);
  if (*(int *)(iVar1 + 8) == 0) {
    FUN_000187a4(param_1,(int)*(short *)(param_1 + 0x195));
  }
  else {
    FUN_000177f0(iVar1,0,0,0);
  }
  FUN_00017d4a(param_1,(int)*(short *)((int)param_1 + 0x64e),(int)*(short *)(param_1 + 0x194),
               (int)*(short *)((int)param_1 + 0x652));
  param_1[0x189] = (int)*(short *)((int)param_1 + 0x64e);
  param_1[0x18a] = (int)*(short *)(param_1 + 0x194);
  param_1[0x18b] = (int)*(short *)((int)param_1 + 0x652);
  *(int *)(iVar1 + 4) = *(int *)(iVar1 + 4) + (int)*(short *)((int)param_1 + 0x64e);
  *(int *)(iVar1 + 8) = *(int *)(iVar1 + 8) + (int)*(short *)(param_1 + 0x194);
  *(int *)(iVar1 + 0xc) = *(int *)(iVar1 + 0xc) + (int)*(short *)((int)param_1 + 0x652);
  uVar6 = *(short *)((int)param_1 + 0x22) + param_2 & 0x3fff;
  *(ushort *)((int)param_1 + 0x22) = uVar6;
  sVar5 = FUN_000023c4(uVar6);
  param_1[2] = (int)sVar5 * -param_1[0x19d] >> 0x12;
  sVar5 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
  param_1[4] = param_1[0x19d] * (int)sVar5 >> 0x12;
  return;
}



// FUN_00018b54 @ 0x18b54

void FUN_00018b54(int *param_1)

{
  short *psVar1;
  short sVar3;
  int iVar2;
  undefined4 *local_8;
  
  iVar2 = *param_1;
  *(undefined2 *)((int)param_1 + 0x64e) = *(undefined2 *)((int)param_1 + 10);
  *(undefined2 *)(param_1 + 0x194) = *(undefined2 *)((int)param_1 + 0xe);
  *(undefined2 *)((int)param_1 + 0x652) = *(undefined2 *)((int)param_1 + 0x12);
  param_1[0x189] = param_1[0x189] + (int)*(short *)((int)param_1 + 0x64e);
  param_1[0x18a] = param_1[0x18a] + (int)*(short *)(param_1 + 0x194);
  param_1[0x18b] = param_1[0x18b] + (int)*(short *)((int)param_1 + 0x652);
  *(undefined1 *)((int)param_1 + 0x619) = 1;
  psVar1 = (short *)param_1[0xd];
  sVar3 = *psVar1;
  if (sVar3 == 2) {
    *(int *)(iVar2 + 4) = *(int *)(iVar2 + 4) + (int)*(short *)((int)param_1 + 0x64e);
    *(int *)(iVar2 + 8) = *(int *)(iVar2 + 8) + (int)*(short *)(param_1 + 0x194);
    *(int *)(iVar2 + 0xc) = *(int *)(iVar2 + 0xc) + (int)*(short *)((int)param_1 + 0x652);
    FUN_00017d4a(param_1,(int)*(short *)((int)param_1 + 0x64e),(int)*(short *)(param_1 + 0x194),
                 (int)*(short *)((int)param_1 + 0x652));
    iVar2 = FUN_000186bc(iVar2,psVar1 + 2,&local_8,param_1[0x19d]);
    if (iVar2 != 0) {
      *psVar1 = 3;
    }
  }
  else if ((sVar3 < 3) && (sVar3 == 0)) {
    if (*(short *)param_1[1] == 0x21) {
      param_1[0x19d] = 0x708;
    }
    else {
      param_1[0x19d] = 0x980;
    }
    sVar3 = FUN_000023c4((int)*(short *)((int)param_1 + 0x22));
    param_1[2] = (int)sVar3 * -param_1[0x19d] >> 0x12;
    sVar3 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
    param_1[4] = param_1[0x19d] * (int)sVar3 >> 0x12;
    FUN_000186bc(iVar2,psVar1 + 2,&local_8,param_1[0x19d]);
    *psVar1 = 3;
  }
  else if (*psVar1 == 3) {
    FUN_000186bc(iVar2,psVar1 + 2,&local_8,param_1[0x19d]);
    sVar3 = FUN_00018732(iVar2,*local_8,local_8[1],local_8[2]);
    sVar3 = FUN_0000f87c((int)sVar3 - (int)*(short *)((int)param_1 + 0x22));
    iVar2 = FUN_00003908((int)sVar3);
    if (iVar2 < 0x41) {
      *psVar1 = 2;
    }
    else {
      if (sVar3 < 0) {
        sVar3 = -0x40;
      }
      else {
        sVar3 = 0x40;
      }
      *psVar1 = 8;
    }
    FUN_00018928(param_1,(int)sVar3);
  }
  else {
    *psVar1 = *psVar1 + -1;
    *(int *)(iVar2 + 4) = *(int *)(iVar2 + 4) + (int)*(short *)((int)param_1 + 0x64e);
    *(int *)(iVar2 + 8) = *(int *)(iVar2 + 8) + (int)*(short *)(param_1 + 0x194);
    *(int *)(iVar2 + 0xc) = *(int *)(iVar2 + 0xc) + (int)*(short *)((int)param_1 + 0x652);
    FUN_00017d4a(param_1,(int)*(short *)((int)param_1 + 0x64e),(int)*(short *)(param_1 + 0x194),
                 (int)*(short *)((int)param_1 + 0x652));
  }
  return;
}



// FUN_00018d3c @ 0x18d3c

void FUN_00018d3c(undefined4 *param_1)

{
  undefined4 uVar1;
  
  if (param_1[0xd] == 0) {
    uVar1 = FUN_0001a8c2(*param_1,8,4,&DAT_0002067c);
    param_1[0xd] = uVar1;
  }
  FUN_00018b54(param_1);
  return;
}



// FUN_00018d74 @ 0x18d74

void FUN_00018d74(int *param_1)

{
  int iVar1;
  int iVar2;
  int *piVar3;
  int iVar4;
  int iVar5;
  
  iVar1 = *param_1;
  iVar5 = 0;
  do {
    if (*(short *)(*(int *)(iVar1 + 0x24) + 0x30) <= iVar5) {
LAB_00018df8:
      iVar5 = *(int *)(*(int *)(iVar1 + 0x24) + 0x2a);
      param_1[0x187] = iVar5 + 0x1a;
      param_1[0x188] =
           *(int *)(SUB_000207fc + 0x8c) +
           *(int *)(iVar5 + 0x2e + (uint)*(ushort *)(iVar1 + 0x10) * 0x18);
      *(undefined1 *)((int)param_1 + 0x61b) = 1;
      FUN_0000371a(iVar1,param_1 + 0x18c);
      return;
    }
    iVar2 = *(int *)(*(int *)(*(int *)(iVar1 + 0x24) + 0x32) + 0x14 + iVar5 * 0x18);
    if ((iVar2 != 0) && (*(char *)(iVar2 + 0x28) == '\x03')) {
      if ((*(int *)(iVar2 + 0x32) != 0) && (iVar5 = 0, 0 < *(short *)(iVar2 + 0x30))) {
        iVar4 = 0;
        do {
          piVar3 = *(int **)(*(int *)(iVar2 + 0x32) + 0x14 + iVar4);
          if (((piVar3 != (int *)0x0) && (*piVar3 != 0)) &&
             ((**(short **)(*piVar3 + 4) == 0x24 || (**(short **)(*piVar3 + 4) == 0x23)))) {
            FUN_00017d0e(param_1,piVar3);
          }
          iVar4 = iVar4 + 0x18;
          iVar5 = iVar5 + 1;
        } while (iVar5 < *(short *)(iVar2 + 0x30));
      }
      goto LAB_00018df8;
    }
    iVar5 = iVar5 + 1;
  } while( true );
}



// FUN_00018e40 @ 0x18e40

void FUN_00018e40(int *param_1)

{
  int iVar1;
  int iVar2;
  undefined2 local_6;
  
  iVar1 = *param_1;
  if (*(short *)(param_1 + 0x19c) == 0) {
    if (*(char *)((int)param_1 + 0x61b) == '\0') {
      FUN_00018d74(param_1);
    }
    if (*(short *)((int)param_1 + 0x32) != 0) {
      if (*(short *)((int)param_1 + 0x32) == 99) {
        if (param_1[0xd] == 0) {
          iVar2 = FUN_0001a956(*param_1,8,&local_6);
          param_1[0xd] = iVar2;
          if (iVar2 == 0) {
            *(undefined2 *)((int)param_1 + 0x32) = 0;
            return;
          }
          *(undefined2 *)(param_1[0xd] + 4) = local_6;
          FUN_0000f380(param_1 + 0xf,0,0,0);
          FUN_0000f346(param_1 + 0xf,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                       *(undefined4 *)(iVar1 + 0xc));
        }
        FUN_00018b54(param_1);
      }
      else if (*(int *)(&DAT_000206ac + *(short *)((int)param_1 + 0x32) * 4) != 0) {
        (**(code **)(&DAT_000206ac + *(short *)((int)param_1 + 0x32) * 4))(param_1);
      }
    }
  }
  else {
    FUN_00017870(param_1);
    param_1[2] = 0;
    *(undefined2 *)((int)param_1 + 0x64e) = 0;
    param_1[4] = 0;
    *(undefined2 *)((int)param_1 + 0x652) = 0;
    param_1[0x19d] = 0;
    if (*(int *)((int)param_1 + 0x68e) != 0) {
      FUN_000060fc(*(undefined4 *)((int)param_1 + 0x68e));
    }
  }
  return;
}



// FUN_00018f28 @ 0x18f28

void FUN_00018f28(int *param_1,short param_2)

{
  int iVar1;
  int iVar2;
  int *piVar3;
  char *pcVar4;
  undefined4 *puVar5;
  
  if (*(short *)((int)param_1 + 0x32) != 0) {
    iVar1 = *param_1;
    if (*(int *)((int)param_1 + 0x68e) == 0) {
      puVar5 = (undefined4 *)
               FUN_0001ec4e(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                            *(undefined4 *)(iVar1 + 0xc),(int)*(short *)(param_1 + 8),(int)param_2,
                            (int)*(short *)(param_1 + 9),*(short *)param_1[1] + -0x1b);
      *(undefined4 *)((int)param_1 + 0x68e) = *puVar5;
      pcVar4 = (char *)(*(int *)((int)param_1 + 0x68e) + 0x29);
      *pcVar4 = *pcVar4 + '\x01';
    }
    else {
      piVar3 = *(int **)((int)param_1 + 0x68e);
      iVar2 = *piVar3;
      if (iVar2 != 0) {
        *(short *)(iVar2 + 0x22) = param_2;
        *(int *)(iVar2 + 8) = *(int *)(iVar1 + 4) - piVar3[1];
        *(undefined4 *)(iVar2 + 0xc) = 0;
        *(int *)(iVar2 + 0x10) = *(int *)(iVar1 + 0xc) - piVar3[3];
      }
    }
  }
  return;
}



// FUN_00018fb4 @ 0x18fb4

void FUN_00018fb4(int *param_1)

{
  int *piVar1;
  int iVar2;
  undefined2 uVar3;
  byte *pbVar4;
  int *piVar5;
  
  iVar2 = *param_1;
  pbVar4 = (byte *)(param_1 + 0x186);
  piVar1 = param_1 + (uint)*pbVar4 * 5 + 0xf;
  *pbVar4 = *pbVar4 + 1;
  if (*(char *)(param_1 + 0x186) == 'K') {
    *pbVar4 = 0;
  }
  if (DAT_00021058 == 0) {
    (*(code *)param_1[0xe])(param_1);
    FUN_00018f28(param_1,(int)*(short *)((int)param_1 + 0x22));
    *(undefined2 *)((int)piVar1 + 0xe) = *(undefined2 *)(param_1 + 0x195);
    *piVar1 = *(int *)(iVar2 + 4);
    piVar1[1] = *(int *)(iVar2 + 8);
    piVar1[2] = *(int *)(iVar2 + 0xc);
    *(undefined2 *)((int)piVar1 + 0x12) = *(undefined2 *)((int)param_1 + 0x692);
  }
  else if (*(short *)(param_1 + 0x19c) == 0) {
    param_1[0x189] = (*piVar1 - *(int *)(iVar2 + 4)) + param_1[0x189];
    param_1[0x18b] = (piVar1[2] - *(int *)(iVar2 + 0xc)) + param_1[0x18b];
    *(undefined1 *)((int)param_1 + 0x619) = 1;
    *(int *)(iVar2 + 4) = *piVar1;
    *(int *)(iVar2 + 0xc) = piVar1[2];
    if (param_1 + 0xf == piVar1) {
      piVar5 = param_1 + 0x181;
    }
    else {
      piVar5 = piVar1 + -5;
    }
    if (*(short *)((int)piVar5 + 0xe) != *(short *)((int)piVar1 + 0xe)) {
      FUN_000187a4(param_1,(int)*(short *)((int)piVar1 + 0xe));
      param_1[0x189] = 0;
      param_1[0x18a] = 0;
      param_1[0x18b] = 0;
      *(undefined1 *)((int)param_1 + 0x619) = 0;
    }
    *(undefined2 *)((int)param_1 + 0x692) = *(undefined2 *)((int)piVar1 + 0x12);
    uVar3 = *(undefined2 *)((int)param_1 + 0x692);
    FUN_00018f28(param_1,(int)*(short *)((int)piVar1 + 0xe) +
                         ((int)*(short *)((int)param_1 + 0x22) - (int)*(short *)(param_1 + 0x195)) &
                         0x3fff);
    *(undefined2 *)(param_1 + 0x1a5) = uVar3;
  }
  return;
}



// FUN_000190b6 @ 0x190b6

void FUN_000190b6(int *param_1,int *param_2)

{
  int iVar1;
  short sVar2;
  char *pcVar3;
  int iVar4;
  uint uVar5;
  int iVar6;
  ushort uVar7;
  
  iVar1 = *param_1;
  sVar2 = **(short **)(*param_2 + 4);
  if ((((sVar2 < 0x1b) || (0x22 < sVar2)) || (iVar4 = FUN_0000042c(param_2), iVar4 != 0x200)) ||
     (sVar2 == 0x20)) {
    if (4 < sVar2) {
      return;
    }
    iVar4 = 0x339000;
  }
  else {
    iVar4 = 0x672000;
  }
  uVar5 = FUN_00013672();
  if (((uVar5 & 3) == 0) &&
     (iVar6 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]),
     iVar6 < 0x33901)) {
    if (iVar6 == 0) {
      iVar4 = 0x7fffffff;
    }
    else {
      iVar4 = iVar4 / iVar6;
    }
    if (*(int *)(iVar1 + 0x698) != 0) {
      iVar4 = iVar4 >> 1;
    }
    uVar7 = FUN_00013672();
    if ((int)(uint)(uVar7 & 0x7f) < iVar4) {
      if (sVar2 < 5) {
        *(ushort *)(iVar1 + 0x6a0) = *(ushort *)(iVar1 + 0x6a0) | 2;
        *(undefined4 *)(iVar1 + 0x6a4) = DAT_00021020;
      }
      else {
        if (*(int *)(iVar1 + 0x698) == 0) {
          *(int **)(iVar1 + 0x698) = param_2;
          *(int *)(iVar1 + 0x69c) = iVar6;
        }
        else {
          if (*(int *)(iVar1 + 0x69c) <= iVar6 * 2) {
            return;
          }
          *(int *)(iVar1 + 0x69c) = iVar6;
          pcVar3 = (char *)(*(int *)(iVar1 + 0x698) + 0x29);
          *pcVar3 = *pcVar3 + -1;
          *(int **)(iVar1 + 0x698) = param_2;
        }
        *(char *)((int)param_2 + 0x29) = *(char *)((int)param_2 + 0x29) + '\x01';
      }
    }
  }
  return;
}



// FUN_000191b6 @ 0x191b6

void FUN_000191b6(int *param_1)

{
  int iVar1;
  int iVar2;
  short sVar5;
  int iVar3;
  uint uVar4;
  
  iVar1 = *param_1;
  if (*(short *)((int)param_1 + 0x66a) == 0) {
    if ((*(byte *)((int)param_1 + 0x6a1) & 2) == 0) {
      if (*(int *)(iVar1 + 8) < 0) {
        param_1[3] = 5;
      }
      else {
        param_1[3] = 0;
      }
    }
    else {
      if (*(int *)(iVar1 + 8) < -399) {
        param_1[3] = 0;
      }
      else {
        param_1[3] = -5;
      }
      if (0x1c2 < DAT_00021020 - param_1[0x1a9]) {
        *(ushort *)(param_1 + 0x1a8) = *(ushort *)(param_1 + 0x1a8) & 0xfffd;
      }
    }
    *(undefined2 *)((int)param_1 + 0x66a) = 5;
  }
  else {
    *(short *)((int)param_1 + 0x66a) = *(short *)((int)param_1 + 0x66a) + -1;
    param_1[3] = 0;
  }
  if (param_1[0x1a6] == 0) {
LAB_000193a8:
    FUN_00018b54(param_1);
  }
  else {
    iVar3 = param_1[0x1a6];
    if (((*(byte *)(iVar3 + 0x17) & 2) == 0) && (*(int *)(iVar3 + 0x46) != 0)) {
      if ((*(byte *)((int)param_1 + 0x6a1) & 1) == 0) {
        FUN_00018b54(param_1);
        if (*(short *)((int)param_1 + 0x696) == 0) {
          return;
        }
        uVar4 = FUN_00013672();
        if ((uVar4 & 0x7ff) == 0) {
          *(ushort *)(param_1 + 0x1a8) = *(ushort *)(param_1 + 0x1a8) | 1;
          return;
        }
        return;
      }
      if (*(short *)((int)param_1 + 0x6a2) != 0) {
        *(short *)((int)param_1 + 0x6a2) = *(short *)((int)param_1 + 0x6a2) + -1;
        *(undefined2 *)param_1[0xd] = 2;
        goto LAB_000193a8;
      }
      iVar2 = FUN_000048a6(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                           *(undefined4 *)(iVar1 + 0xc),*(undefined4 *)(iVar3 + 4),
                           *(undefined4 *)(iVar3 + 8),*(undefined4 *)(iVar3 + 0xc));
      param_1[0x1a7] = iVar2;
      if (iVar2 < 0x33901) {
        sVar5 = FUN_00018732(iVar1,*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc));
        sVar5 = FUN_0000f87c((int)sVar5 - (int)*(short *)((int)param_1 + 0x22));
        iVar3 = FUN_00003908((int)sVar5);
        if (0x40 < iVar3) {
          if (sVar5 < 0) {
            sVar5 = -0x40;
          }
          else {
            sVar5 = 0x40;
          }
        }
        iVar3 = FUN_00003908((int)sVar5);
        if (iVar3 < 0x40) {
          FUN_00014b1e(iVar1,0x438,*(int *)(iVar1 + 4) + param_1[2] * 0x69,
                       *(undefined4 *)(iVar1 + 8),*(int *)(iVar1 + 0xc) + param_1[4] * 0x69,0,
                       (int)*(short *)((int)param_1 + 0x22),0);
          *(ushort *)(param_1 + 0x1a8) = *(ushort *)(param_1 + 0x1a8) & 0xfffe;
          *(undefined2 *)param_1[0xd] = 3;
          *(short *)((int)param_1 + 0x696) = *(short *)((int)param_1 + 0x696) + -1;
        }
        *(undefined2 *)((int)param_1 + 0x64e) = *(undefined2 *)((int)param_1 + 10);
        *(undefined2 *)(param_1 + 0x194) = *(undefined2 *)((int)param_1 + 0xe);
        *(undefined2 *)((int)param_1 + 0x652) = *(undefined2 *)((int)param_1 + 0x12);
        param_1[0x189] = param_1[0x189] + (int)*(short *)((int)param_1 + 0x64e);
        param_1[0x18a] = param_1[0x18a] + (int)*(short *)(param_1 + 0x194);
        param_1[0x18b] = param_1[0x18b] + (int)*(short *)((int)param_1 + 0x652);
        *(undefined1 *)((int)param_1 + 0x619) = 1;
        FUN_00018928(param_1,(int)sVar5);
        *(undefined2 *)((int)param_1 + 0x6a2) = 5;
        return;
      }
    }
    *(char *)(iVar3 + 0x29) = *(char *)(iVar3 + 0x29) + -1;
    param_1[0x1a6] = 0;
    *(undefined2 *)(param_1 + 0x1a8) = 0;
  }
  return;
}



// FUN_000193b6 @ 0x193b6

void FUN_000193b6(int *param_1)

{
  int iVar1;
  undefined2 *puVar2;
  int iVar3;
  short sVar4;
  undefined2 local_6;
  
  iVar1 = *param_1;
  if (*(short *)(param_1 + 0x19c) == 0) {
    if (*(char *)((int)param_1 + 0x61b) == '\0') {
      FUN_00018d74(param_1);
    }
    if (*(short *)((int)param_1 + 0x32) == 99) {
      if (param_1[0xd] == 0) {
        iVar3 = FUN_0001a956(*param_1,8,&local_6);
        param_1[0xd] = iVar3;
        if (iVar3 == 0) {
          *(undefined2 *)((int)param_1 + 0x32) = 0;
          return;
        }
        puVar2 = (undefined2 *)param_1[0xd];
        puVar2[2] = local_6;
        *puVar2 = 2;
        param_1[0x19d] = 0x5a0;
        sVar4 = FUN_000023c4((int)*(short *)((int)param_1 + 0x22));
        param_1[2] = (int)sVar4 * -param_1[0x19d] >> 0x12;
        sVar4 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
        param_1[4] = param_1[0x19d] * (int)sVar4 >> 0x12;
        FUN_0000f380(param_1 + 0xf,0,0,0);
        FUN_0000f346(param_1 + 0xf,*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                     *(undefined4 *)(iVar1 + 0xc));
      }
      FUN_000191b6(param_1);
    }
  }
  else {
    FUN_00017870(param_1);
    param_1[2] = 0;
    *(undefined2 *)((int)param_1 + 0x64e) = 0;
    param_1[4] = 0;
    *(undefined2 *)((int)param_1 + 0x652) = 0;
    param_1[0x19d] = 0;
    if (*(int *)((int)param_1 + 0x68e) != 0) {
      FUN_000060fc(*(undefined4 *)((int)param_1 + 0x68e));
    }
  }
  return;
}



// FUN_000194ca @ 0x194ca

void FUN_000194ca(int *param_1)

{
  int *piVar1;
  int iVar2;
  undefined2 uVar3;
  byte *pbVar4;
  int *piVar5;
  
  iVar2 = *param_1;
  pbVar4 = (byte *)(param_1 + 0x186);
  piVar1 = param_1 + (uint)*pbVar4 * 5 + 0xf;
  *pbVar4 = *pbVar4 + 1;
  if (*(char *)(param_1 + 0x186) == 'K') {
    *pbVar4 = 0;
  }
  if (DAT_00021058 == 0) {
    FUN_000193b6(param_1);
    FUN_00018f28(param_1,(int)*(short *)((int)param_1 + 0x22));
    *(undefined2 *)((int)piVar1 + 0xe) = *(undefined2 *)(param_1 + 0x195);
    *piVar1 = *(int *)(iVar2 + 4);
    piVar1[1] = *(int *)(iVar2 + 8);
    piVar1[2] = *(int *)(iVar2 + 0xc);
    *(undefined2 *)((int)piVar1 + 0x12) = *(undefined2 *)((int)param_1 + 0x692);
  }
  else if (*(short *)(param_1 + 0x19c) == 0) {
    param_1[0x189] = (*piVar1 - *(int *)(iVar2 + 4)) + param_1[0x189];
    param_1[0x18a] = (piVar1[1] - *(int *)(iVar2 + 8)) + param_1[0x18a];
    param_1[0x18b] = (piVar1[2] - *(int *)(iVar2 + 0xc)) + param_1[0x18b];
    *(undefined1 *)((int)param_1 + 0x619) = 1;
    *(int *)(iVar2 + 4) = *piVar1;
    *(int *)(iVar2 + 8) = piVar1[1];
    *(int *)(iVar2 + 0xc) = piVar1[2];
    if (param_1 + 0xf == piVar1) {
      piVar5 = param_1 + 0x181;
    }
    else {
      piVar5 = piVar1 + -5;
    }
    if (*(short *)((int)piVar5 + 0xe) != *(short *)((int)piVar1 + 0xe)) {
      if (*(int *)(iVar2 + 8) == 0) {
        FUN_000187a4(param_1,(int)*(short *)((int)piVar1 + 0xe));
      }
      else {
        FUN_000177f0(iVar2,0,0,0);
      }
      param_1[0x189] = 0;
      param_1[0x18a] = 0;
      param_1[0x18b] = 0;
      *(undefined1 *)((int)param_1 + 0x619) = 0;
    }
    *(undefined2 *)((int)param_1 + 0x692) = *(undefined2 *)((int)piVar1 + 0x12);
    uVar3 = *(undefined2 *)((int)param_1 + 0x692);
    FUN_00018f28(param_1,(int)*(short *)((int)piVar1 + 0xe) +
                         ((int)*(short *)((int)param_1 + 0x22) - (int)*(short *)(param_1 + 0x195)) &
                         0x3fff);
    *(undefined2 *)(param_1 + 0x1a5) = uVar3;
  }
  return;
}



// FUN_000195f6 @ 0x195f6

undefined2 FUN_000195f6(short *param_1,int param_2,short *param_3,int param_4)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  undefined2 uVar5;
  int iVar6;
  int iVar7;
  
  uVar5 = 0;
  iVar6 = (int)*param_1;
  iVar4 = param_2 + param_3[iVar6 + -1] * 0xc;
  iVar7 = param_4;
  do {
    if (iVar6 == 0) {
      *param_1 = (short)((iVar7 - param_4) / 0xc);
      return uVar5;
    }
    iVar1 = param_2 + *param_3 * 0xc;
    if (*(short *)(iVar1 + 6) < 0) {
      uVar5 = 1;
      if (-1 < *(short *)(iVar4 + 6)) {
        iVar2 = (int)*(short *)(iVar4 + 6) - (int)*(short *)(iVar1 + 6);
        iVar3 = -(int)*(short *)(iVar1 + 6);
        *(short *)(iVar7 + 2) =
             *(short *)(iVar1 + 2) +
             (short)((iVar3 * ((int)*(short *)(iVar4 + 2) - (int)*(short *)(iVar1 + 2))) / iVar2);
        *(undefined2 *)(iVar7 + 6) = 0;
        *(short *)(iVar7 + 10) =
             *(short *)(iVar1 + 10) +
             (short)((iVar3 * ((int)*(short *)(iVar4 + 10) - (int)*(short *)(iVar1 + 10))) / iVar2);
        goto LAB_00019706;
      }
    }
    else {
      if (*(short *)(iVar4 + 6) < 0) {
        uVar5 = 1;
        iVar2 = (int)*(short *)(iVar1 + 6) - (int)*(short *)(iVar4 + 6);
        iVar3 = -(int)*(short *)(iVar4 + 6);
        *(short *)(iVar7 + 2) =
             *(short *)(iVar4 + 2) +
             (short)((iVar3 * ((int)*(short *)(iVar1 + 2) - (int)*(short *)(iVar4 + 2))) / iVar2);
        *(undefined2 *)(iVar7 + 6) = 0;
        *(short *)(iVar7 + 10) =
             *(short *)(iVar4 + 10) +
             (short)((iVar3 * ((int)*(short *)(iVar1 + 10) - (int)*(short *)(iVar4 + 10))) / iVar2);
        *(undefined2 *)(iVar7 + 0xe) = *(undefined2 *)(iVar1 + 2);
        *(undefined2 *)(iVar7 + 0x12) = *(undefined2 *)(iVar1 + 6);
        *(undefined2 *)(iVar7 + 0x16) = *(undefined2 *)(iVar1 + 10);
        iVar7 = iVar7 + 0xc;
      }
      else {
        *(undefined2 *)(iVar7 + 2) = *(undefined2 *)(iVar1 + 2);
        *(undefined2 *)(iVar7 + 6) = *(undefined2 *)(iVar1 + 6);
        *(undefined2 *)(iVar7 + 10) = *(undefined2 *)(iVar1 + 10);
      }
LAB_00019706:
      iVar7 = iVar7 + 0xc;
    }
    iVar6 = iVar6 + -1;
    param_3 = param_3 + 1;
    iVar4 = iVar1;
  } while( true );
}



// FUN_0001972a @ 0x1972a

undefined2 FUN_0001972a(int param_1,short param_2,short param_3,short param_4,undefined4 param_5)

{
  int iVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  int iVar5;
  undefined4 uVar6;
  ushort uVar7;
  short sVar8;
  short sVar9;
  int iVar10;
  short *psVar11;
  int iVar12;
  int iVar13;
  int *piVar14;
  int *piVar15;
  int iVar16;
  int iVar17;
  short sVar18;
  int aiStackY_c1f0c [96988];
  int aiStackY_6339c [896];
  short asStackY_6259a [1739];
  int aiStackY_61804 [80538];
  ushort auStackY_12d9c [31924];
  undefined2 local_33c4;
  ushort local_33c2;
  undefined1 auStack_33c0 [18];
  undefined1 auStack_33ae [18];
  int local_339c [384];
  ushort local_2d9c [512];
  short asStack_299c [512];
  undefined1 auStack_259c [2];
  short local_259a [839];
  int local_1f0c;
  int local_1f08;
  short sStack_1f06;
  int local_1f04 [448];
  int aiStack_1804 [1536];
  
  local_33c4 = 0;
  iVar2 = *(int *)(SUB_000207fc + 0x8c) +
          *(int *)(*(int *)(*(int *)(param_1 + 0x24) + 0x2a) + 0x2e +
                  (uint)*(ushort *)(param_1 + 0x10) * 0x18);
  iVar16 = (int)(short)(*(short *)(iVar2 + 0x1e) + *(short *)(iVar2 + 0x1a));
  iVar13 = iVar2 + 0x22 + iVar16 * 0xc;
  FUN_00002d02((int)param_2,(int)param_3,(int)param_4,auStack_33ae);
  FUN_00002346(auStack_33ae,param_5,auStack_33c0);
  FUN_000032c0(iVar16,iVar2 + 0x22,&local_1f0c,auStack_33c0);
  for (sVar8 = 0; sVar8 < *(short *)(iVar2 + 0x1e); sVar8 = sVar8 + 1) {
    iVar16 = (int)sVar8;
    piVar14 = local_339c + iVar16 * 3;
    iVar17 = iVar16 + *(short *)(iVar2 + 0x1a);
    *piVar14 = (&local_1f0c)[iVar17 * 3];
    local_339c[iVar16 * 3 + 1] = local_1f04[iVar17 * 3 + -1];
    local_339c[iVar16 * 3 + 2] = local_1f04[iVar17 * 3];
    *piVar14 = *piVar14 - local_1f0c;
    local_339c[iVar16 * 3 + 1] = local_339c[iVar16 * 3 + 1] - local_1f08;
    local_339c[iVar16 * 3 + 2] = local_339c[iVar16 * 3 + 2] - local_1f04[0];
  }
  uVar4 = (uint)*(short *)(param_1 + 0x36);
  iVar16 = *(int *)(param_1 + 4);
  iVar17 = *(int *)(param_1 + 8);
  iVar1 = *(int *)(param_1 + 0xc);
  sVar8 = *(short *)(iVar2 + 0x1a) + -1;
  if (sVar8 != -1) {
    psVar11 = &sStack_1f06;
    do {
      *psVar11 = (short)(iVar17 << (uVar4 & 0x3f)) + *psVar11;
      psVar11 = psVar11 + 6;
      sVar8 = sVar8 + -1;
    } while (sVar8 != -1);
  }
  sVar18 = 0;
  sVar8 = 0xff;
  psVar11 = asStack_299c;
  do {
    psVar11[0] = -1;
    psVar11[1] = -1;
    sVar8 = sVar8 + -1;
    psVar11 = psVar11 + 2;
  } while (sVar8 != -1);
  sVar8 = 0;
  if (0 < *(short *)(iVar2 + 0x1e)) {
    do {
      local_33c2 = (ushort)*(byte *)(iVar13 + 8);
      FUN_000195f6(&local_33c2,&local_1f0c,iVar13 + 0xe,auStack_259c);
      iVar17 = *(int *)(param_1 + 0x4a) + sVar8 * 0x20;
      if (local_33c2 == 0) {
        *(byte *)(iVar17 + 0x13) = *(byte *)(iVar17 + 0x13) | 2;
      }
      else {
        local_33c4 = 1;
        *(ushort *)(iVar17 + 0x14) = local_33c2;
        if ((*(byte *)(iVar13 + 7) & 2) == 0) {
          *(byte *)(iVar17 + 0x13) = *(byte *)(iVar17 + 0x13) & 0xfd;
        }
        uVar6 = FUN_000021e0(*(undefined4 *)(iVar17 + 0x18),(int)(short)local_33c2 << 1);
        *(undefined4 *)(iVar17 + 0x18) = uVar6;
        sVar9 = 0;
        if (0 < (short)local_33c2) {
          do {
            iVar12 = (int)sVar9;
            iVar3 = (int)local_259a[iVar12 * 6] + (iVar16 << (uVar4 & 0x3f));
            iVar10 = (int)local_259a[iVar12 * 6 + 2];
            iVar12 = (int)local_259a[iVar12 * 6 + 4] + (iVar1 << (uVar4 & 0x3f));
            uVar7 = (ushort)(iVar12 + iVar3 + (iVar10 >> 1) >> 2) & 0xff;
            piVar14 = aiStack_1804 + (short)uVar7 * 3;
            if (-1 < asStack_299c[(short)uVar7]) {
              do {
                if (((iVar3 == *piVar14) && (iVar10 == piVar14[1])) && (piVar14[2] == iVar12)) {
                  *(short *)(*(int *)(iVar17 + 0x18) + sVar9 * 2) = asStack_299c[(short)uVar7];
                  goto LAB_000199fc;
                }
                piVar14 = piVar14 + 3;
                uVar7 = uVar7 + 1;
              } while (-1 < asStack_299c[(short)uVar7]);
            }
            iVar5 = (int)(short)uVar7;
            aiStack_1804[iVar5 * 3] = iVar3;
            aiStack_1804[iVar5 * 3 + 1] = iVar10;
            aiStack_1804[iVar5 * 3 + 2] = iVar12;
            asStack_299c[iVar5] = sVar18;
            local_2d9c[sVar18] = uVar7;
            *(short *)(*(int *)(iVar17 + 0x18) + sVar9 * 2) = sVar18;
            sVar18 = sVar18 + 1;
LAB_000199fc:
            sVar9 = sVar9 + 1;
          } while (sVar9 < (short)local_33c2);
        }
      }
      iVar13 = iVar13 + 0xe + (uint)*(byte *)(iVar13 + 8) * 2;
      sVar8 = sVar8 + 1;
    } while (sVar8 < *(short *)(iVar2 + 0x1e));
  }
  if (sVar18 == 0) {
    *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x40;
  }
  else {
    *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) & 0xffffffbf;
    *(short *)(param_1 + 0x38) = sVar18;
    iVar13 = (short)(*(short *)(param_1 + 0x3c) + sVar18) * 0xc;
    uVar6 = (*(code *)&LAB_000021be_2)(*(undefined4 *)(param_1 + 0x4e),iVar13);
    *(undefined4 *)(param_1 + 0x4e) = uVar6;
    uVar6 = (*(code *)&LAB_000021be_2)(*(undefined4 *)(param_1 + 0x46),iVar13);
    *(undefined4 *)(param_1 + 0x46) = uVar6;
    sVar8 = 0;
    if (0 < sVar18) {
      do {
        iVar13 = (int)(short)local_2d9c[sVar8];
        piVar14 = (int *)(sVar8 * 0xc + *(int *)(param_1 + 0x46));
        *piVar14 = aiStack_1804[iVar13 * 3];
        piVar14[1] = aiStack_1804[iVar13 * 3 + 1];
        piVar14[2] = aiStack_1804[iVar13 * 3 + 2];
        sVar8 = sVar8 + 1;
      } while (sVar8 < sVar18);
    }
    sVar8 = 0;
    if (0 < *(short *)(param_1 + 0x3c)) {
      do {
        iVar13 = (int)sVar8;
        piVar14 = local_339c + iVar13 * 3;
        *piVar14 = **(int **)(param_1 + 0x46) + *piVar14;
        local_339c[iVar13 * 3 + 1] =
             *(int *)(*(int *)(param_1 + 0x46) + 4) + local_339c[iVar13 * 3 + 1];
        local_339c[iVar13 * 3 + 2] =
             *(int *)(*(int *)(param_1 + 0x46) + 8) + local_339c[iVar13 * 3 + 2];
        piVar15 = (int *)((iVar13 + *(short *)(param_1 + 0x38)) * 0xc + *(int *)(param_1 + 0x46));
        *piVar15 = *piVar14;
        piVar15[1] = local_339c[iVar13 * 3 + 1];
        piVar15[2] = local_339c[iVar13 * 3 + 2];
        sVar8 = sVar8 + 1;
      } while (sVar8 < *(short *)(param_1 + 0x3c));
    }
  }
  if (*(short *)(SUB_000207fc + 0xb2) != 0) {
    FUN_00004b0c(param_1);
  }
  FUN_00016136(param_1);
  return local_33c4;
}



// FUN_00019b52 @ 0x19b52

void FUN_00019b52(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4)

{
  undefined1 auStack_22 [30];
  
  FUN_0000371a(param_1,auStack_22);
  FUN_0001972a(param_1,param_2,param_3,param_4,auStack_22);
  return;
}



// FUN_00019b8a @ 0x19b8a

void FUN_00019b8a(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x52);
  *piVar1 = param_1;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x122000;
  *(undefined4 *)(param_1 + 0x20) = 0x9c4;
  return;
}



// FUN_00019bcc @ 0x19bcc

undefined4 FUN_00019bcc(undefined4 param_1,int param_2)

{
  DAT_0002cbfe = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x19b6a;
  return 0;
}



// FUN_00019bee @ 0x19bee

int FUN_00019bee(int param_1,int param_2,int param_3,int param_4,int param_5,undefined4 param_6)

{
  ushort uVar3;
  int *piVar1;
  int iVar2;
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  local_a = 0;
  local_e = 0;
  local_6 = (short)(param_1 >> 4) + 0x2015;
  FUN_00003220(1,auStack_10,auStack_10,param_6);
  uVar3 = FUN_00013652();
  local_e = (uVar3 & 0x3f) + local_e + -0x20;
  uVar3 = FUN_00013652();
  local_a = (uVar3 & 0x3f) + local_a + -0x20;
  uVar3 = FUN_00013652();
  local_6 = (uVar3 & 0x3f) + local_6 + -0x20;
  piVar1 = (int *)FUN_00005f84(DAT_0002cbfe,param_3 + (local_e >> 2),param_4 + (local_a >> 2),
                               param_5 + (local_6 >> 2),0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(int *)(iVar2 + 0x32) = (int)local_a << 4;
    *(int *)(iVar2 + 8) = (int)(local_e >> 2);
    *(int *)(iVar2 + 0xc) = (int)(local_a >> 2);
    *(int *)(iVar2 + 0x10) = (int)(local_6 >> 2);
    *(undefined2 *)(iVar2 + 0x38) = 0;
    if (param_2 == 0) {
      *(undefined2 *)(iVar2 + 0x3a) = 1;
    }
    else {
      *(undefined2 *)(iVar2 + 0x3a) = 2;
    }
    FUN_0001f63a(param_6,iVar2 + 0x40,0x12);
  }
  return iVar2;
}



// FUN_00019d16 @ 0x19d16

void FUN_00019d16(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5,short param_6,short param_7,short param_8,short param_9)

{
  undefined1 auStack_16 [18];
  
  FUN_00002f64((int)param_9,(int)param_6,(int)param_7,(int)param_8,auStack_16);
  FUN_00019bee(param_1,param_2,param_3,param_4,param_5,auStack_16);
  return;
}



// FUN_00019d6e @ 0x19d6e

void FUN_00019d6e(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                 undefined4 param_5,short param_6,short param_7,short param_8)

{
  undefined1 auStack_16 [18];
  
  FUN_00002d02((int)param_6,(int)param_7,(int)param_8,auStack_16);
  FUN_00019bee(param_1,param_2,param_3,param_4,param_5,auStack_16);
  return;
}



// FUN_00019dbe @ 0x19dbe

void FUN_00019dbe(int *param_1)

{
  int iVar1;
  int iVar2;
  int iVar3;
  
  iVar1 = *param_1;
  iVar2 = param_1[0xf];
  if (iVar2 != 0) {
    if (*(int *)(iVar2 + 8) == 0) {
      iVar3 = *(int *)(iVar2 + 0x20);
      if (iVar3 < 0) {
        iVar3 = iVar3 + 7;
      }
      iVar3 = iVar3 >> 3;
    }
    else {
      iVar3 = *(int *)(iVar2 + 0x20);
      if (iVar3 < 0) {
        iVar3 = iVar3 + 3;
      }
      iVar3 = iVar3 >> 2;
    }
    FUN_00013ee0(iVar2,iVar3);
    FUN_00015006((int)*(short *)((int)param_1 + 0x36),iVar2);
    FUN_0000605a(iVar1);
  }
  *(short *)(param_1 + 0xe) = *(short *)(param_1 + 0xe) + 1;
  if (*(short *)(param_1 + 0xe) != 0x1f) {
    if (3 < *(short *)(param_1 + 0xe)) {
      *(int *)((int)param_1 + 0x32) = *(int *)((int)param_1 + 0x32) + -0x5b;
    }
    param_1[3] = *(int *)((int)param_1 + 0x32) >> 6;
    if (*(int *)((int)param_1 + 0x2e) < *(int *)(iVar1 + 8)) {
      return;
    }
  }
  FUN_0000605a(iVar1);
  return;
}



// FUN_00019e50 @ 0x19e50

undefined4 FUN_00019e50(int *param_1)

{
  if (DAT_0002cb84 < 0x1e) {
    if (*(short *)((int)param_1 + 0x3a) < 8) {
      if (*(short *)((int)param_1 + 0x3a) == 2) {
        FUN_000032c0(2,*(undefined4 *)(*param_1 + 0x46),*(undefined4 *)(*param_1 + 0x46),
                     param_1 + 0x10);
      }
      *(short *)((int)param_1 + 0x3a) = *(short *)((int)param_1 + 0x3a) + 8;
    }
    (&DAT_0002cb86)[DAT_0002cb84] = *param_1;
    DAT_0002cb84 = DAT_0002cb84 + 1;
  }
  return 4;
}



// FUN_00019eac @ 0x19eac

void FUN_00019eac(void)

{
  short sVar1;
  short sVar2;
  char cVar3;
  int iVar4;
  char *pcVar5;
  
  sVar1 = *(short *)(SUB_000207fc + 0x10);
  while (DAT_00023fbc = DAT_00023fbc + -1, DAT_00023fbc != -1) {
    iVar4 = DAT_00023fbc * 0xe;
    pcVar5 = *(char **)(&DAT_0002c83c + iVar4);
    cVar3 = (&DAT_0002c844)[iVar4];
    sVar2 = (&DAT_0002c842)[DAT_00023fbc * 7];
    if (cVar3 == *pcVar5) {
      *pcVar5 = (&DAT_0002c845)[iVar4];
    }
    if ((2 < sVar2) && (cVar3 == pcVar5[1])) {
      pcVar5[1] = (&DAT_0002c846)[iVar4];
    }
    pcVar5 = pcVar5 + sVar1;
    if ((1 < sVar2) && (cVar3 == *pcVar5)) {
      *pcVar5 = (&DAT_0002c847)[iVar4];
    }
    if ((3 < sVar2) && (cVar3 == pcVar5[1])) {
      pcVar5[1] = (&DAT_0002c848)[iVar4];
    }
  }
  DAT_00023fbc = 0;
  return;
}



// FUN_00019f32 @ 0x19f32

void FUN_00019f32(int *param_1,int param_2,short param_3,short param_4)

{
  short sVar1;
  undefined2 uVar2;
  undefined1 uVar3;
  undefined1 *puVar4;
  
  uVar2 = DAT_00023fb8;
  sVar1 = *(short *)(SUB_000207fc + 0x10);
  puVar4 = (undefined1 *)
           (DAT_00027052 + param_2 +
           *(int *)(SUB_000207fc + 6) + (int)(short)(DAT_00027054 + param_3) * (int)sVar1);
  *param_1 = (int)puVar4;
  *(undefined1 *)((int)param_1 + 9) = *puVar4;
  uVar3 = (undefined1)uVar2;
  *puVar4 = uVar3;
  if (2 < param_4) {
    *(undefined1 *)((int)param_1 + 10) = puVar4[1];
    puVar4[1] = uVar3;
  }
  puVar4 = puVar4 + sVar1;
  if (1 < param_4) {
    *(undefined1 *)((int)param_1 + 0xb) = *puVar4;
    *puVar4 = uVar3;
  }
  if (3 < param_4) {
    *(undefined1 *)(param_1 + 3) = puVar4[1];
    puVar4[1] = uVar3;
  }
  return;
}



// FUN_00019fac @ 0x19fac

uint FUN_00019fac(void)

{
  ushort uVar1;
  short sVar2;
  byte bVar3;
  byte *pbVar4;
  byte bVar5;
  undefined3 uVar6;
  uint uVar7;
  int iVar8;
  short sVar9;
  byte *pbVar10;
  
  uVar1 = *(ushort *)(SUB_000207fc + 0x10);
  uVar7 = (uint)uVar1;
  sVar9 = DAT_00023fbc + -1;
  if (sVar9 != -1) {
    do {
      iVar8 = sVar9 * 0xe;
      pbVar10 = *(byte **)(&DAT_0002c83c + iVar8);
      pbVar4 = &DAT_0002c845 + iVar8;
      bVar3 = (&DAT_0002c844)[iVar8];
      sVar2 = (&DAT_0002c842)[sVar9 * 7];
      uVar6 = (undefined3)((uint)iVar8 >> 8);
      if (*(short *)(&DAT_0002c840 + iVar8) == 0) {
        uVar7 = CONCAT31(uVar6,bVar3 ^ *pbVar10) & 0xfffffff0;
        if (((bVar3 ^ *pbVar10) & 0xf0) == 0) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),*pbVar4 ^ *pbVar10) & 0xffffff0f;
          bVar5 = *pbVar4 ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar5);
          *pbVar10 = bVar5;
        }
        if ((2 < sVar2) &&
           (uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3 ^ *pbVar10) & 0xffffff0f,
           ((bVar3 ^ *pbVar10) & 0xf) == 0)) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),*pbVar4 ^ *pbVar10) & 0xfffffff0;
          bVar5 = *pbVar4 ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar5);
          *pbVar10 = bVar5;
        }
        pbVar10 = pbVar10 + (short)uVar1;
        if ((1 < sVar2) &&
           (uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3 ^ *pbVar10) & 0xfffffff0,
           ((bVar3 ^ *pbVar10) & 0xf0) == 0)) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),(&DAT_0002c847)[iVar8] ^ *pbVar10) & 0xffffff0f;
          bVar5 = (&DAT_0002c847)[iVar8] ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar5);
          *pbVar10 = bVar5;
        }
        if ((3 < sVar2) &&
           (uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3 ^ *pbVar10) & 0xffffff0f,
           ((bVar3 ^ *pbVar10) & 0xf) == 0)) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),(&DAT_0002c847)[iVar8] ^ *pbVar10) & 0xfffffff0;
          bVar3 = (&DAT_0002c847)[iVar8] ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3);
          *pbVar10 = bVar3;
        }
      }
      else {
        uVar7 = CONCAT31(uVar6,bVar3 ^ *pbVar10) & 0xffffff0f;
        if (((bVar3 ^ *pbVar10) & 0xf) == 0) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),*pbVar4 ^ *pbVar10) & 0xfffffff0;
          bVar5 = *pbVar4 ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar5);
          *pbVar10 = bVar5;
        }
        if ((2 < sVar2) &&
           (uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3 ^ pbVar10[1]) & 0xfffffff0,
           ((bVar3 ^ pbVar10[1]) & 0xf0) == 0)) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),(&DAT_0002c846)[iVar8] ^ pbVar10[1]) & 0xffffff0f;
          bVar5 = (&DAT_0002c846)[iVar8] ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar5);
          pbVar10[1] = bVar5;
        }
        pbVar10 = pbVar10 + (short)uVar1;
        if ((1 < sVar2) &&
           (uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3 ^ *pbVar10) & 0xffffff0f,
           ((bVar3 ^ *pbVar10) & 0xf) == 0)) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),(&DAT_0002c847)[iVar8] ^ *pbVar10) & 0xfffffff0;
          bVar5 = (&DAT_0002c847)[iVar8] ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar5);
          *pbVar10 = bVar5;
        }
        if ((3 < sVar2) &&
           (uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3 ^ pbVar10[1]) & 0xfffffff0,
           ((bVar3 ^ pbVar10[1]) & 0xf0) == 0)) {
          uVar7 = CONCAT31((int3)(uVar7 >> 8),(&DAT_0002c848)[iVar8] ^ pbVar10[1]) & 0xffffff0f;
          bVar3 = (&DAT_0002c848)[iVar8] ^ (byte)uVar7;
          uVar7 = CONCAT31((int3)(uVar7 >> 8),bVar3);
          pbVar10[1] = bVar3;
        }
      }
      sVar9 = sVar9 + -1;
    } while (sVar9 != -1);
  }
  DAT_00023fbc = 0;
  return uVar7;
}



// FUN_0001a124 @ 0x1a124

void FUN_0001a124(undefined4 *param_1,short param_2,short param_3,short param_4)

{
  short sVar1;
  undefined2 uVar2;
  short sVar3;
  byte bVar4;
  byte *pbVar5;
  
  uVar2 = DAT_00023fb8;
  sVar1 = *(short *)(SUB_000207fc + 0x10);
  param_2 = DAT_00027052 + param_2;
  sVar3 = param_2;
  if (param_2 < 0) {
    sVar3 = param_2 + 1;
  }
  pbVar5 = (byte *)((int)(sVar3 >> 1) +
                   (int)(short)(DAT_00027054 + param_3) * (int)sVar1 + *(int *)(SUB_000207fc + 6));
  *(short *)(param_1 + 1) = param_2 % 2;
  *param_1 = pbVar5;
  bVar4 = (byte)uVar2;
  if (*(short *)(param_1 + 1) == 0) {
    *(byte *)((int)param_1 + 9) = *pbVar5;
    if (param_4 < 3) {
      *pbVar5 = bVar4 ^ (bVar4 ^ *pbVar5) & 0xf;
    }
    else {
      *pbVar5 = bVar4;
    }
    pbVar5 = pbVar5 + sVar1;
    *(byte *)((int)param_1 + 0xb) = *pbVar5;
    if (param_4 < 4) {
      if (1 < param_4) {
        *pbVar5 = bVar4 ^ (bVar4 ^ *pbVar5) & 0xf;
      }
    }
    else {
      *pbVar5 = bVar4;
    }
  }
  else {
    *(byte *)((int)param_1 + 9) = *pbVar5;
    *pbVar5 = bVar4 ^ (bVar4 ^ *pbVar5) & 0xf0;
    if (2 < param_4) {
      *(byte *)((int)param_1 + 10) = pbVar5[1];
      pbVar5[1] = bVar4 ^ (bVar4 ^ pbVar5[1]) & 0xf;
    }
    pbVar5 = pbVar5 + sVar1;
    if (1 < param_4) {
      *(byte *)((int)param_1 + 0xb) = *pbVar5;
      *pbVar5 = bVar4 ^ (bVar4 ^ *pbVar5) & 0xf0;
    }
    if (3 < param_4) {
      *(byte *)(param_1 + 3) = pbVar5[1];
      pbVar5[1] = bVar4 ^ (bVar4 ^ pbVar5[1]) & 0xf;
    }
  }
  return;
}



// FUN_0001a22c @ 0x1a22c

void FUN_0001a22c(undefined4 param_1,int param_2,int param_3,short param_4)

{
  int iVar1;
  short sVar2;
  int iVar3;
  int local_24 [8];
  
  sVar2 = 0;
  iVar3 = (short)(param_4 + -1) * 4;
  do {
    iVar1 = (int)sVar2;
    local_24[iVar1 * 2] = *(int *)(&DAT_000206b4 + iVar3 + iVar1 * 0x10) + param_2;
    local_24[iVar1 * 2 + 1] = *(int *)(&DAT_000206f4 + iVar3 + iVar1 * 0x10) + param_3;
    sVar2 = sVar2 + 1;
  } while (sVar2 < 4);
  iVar3 = (int)DAT_000219a8;
  DAT_000219a8 = DAT_000219a8 + 1;
  (**(code **)(SUB_000207fc + 0x7c))(4,(int)DAT_00023fb8,iVar3,local_24);
  return;
}



// FUN_0001a2b6 @ 0x1a2b6

void FUN_0001a2b6(void)

{
  ushort uVar1;
  short sVar2;
  short sVar3;
  int iVar4;
  undefined2 uVar5;
  short sVar6;
  short sVar7;
  int iVar8;
  short sVar9;
  int iVar10;
  uint uVar11;
  int iVar12;
  int *piVar13;
  undefined1 *puVar14;
  bool bVar15;
  undefined *local_32;
  short sStack_2c;
  int local_2a;
  int *local_26;
  short sStack_20;
  short local_1e;
  undefined1 auStack_1c [2];
  undefined2 local_1a;
  undefined2 local_16;
  undefined2 local_12;
  
  if (*(short *)(SUB_000207fc + 0x9e) != 0) {
    DAT_0002cb84 = 0;
    DAT_00023fbc = 0;
  }
  (*DAT_00023fb4)();
  local_26 = &DAT_0002cb86;
  local_32 = &DAT_0002c83c;
  local_1e = DAT_0002cb84 + -1;
  if (local_1e != -1) {
    do {
      piVar13 = (int *)*local_26;
      iVar8 = *piVar13;
      iVar10 = *(int *)((int)piVar13 + 0x4a);
      sVar6 = *(short *)(piVar13 + 0xd);
      if (*(char *)(iVar10 + 0xf) == -1) {
LAB_0001a35c:
        DAT_00023fb8 = *(undefined2 *)(iVar10 + 6);
      }
      else {
        if (DAT_00023fba == 0) {
          iVar12 = FUN_00001c06(iVar10);
          if (iVar12 != 0) {
            return;
          }
          DAT_00023fba = (ushort)*(byte *)(iVar10 + 0xf);
          goto LAB_0001a35c;
        }
        *(undefined1 *)(iVar10 + 0xf) = (undefined1)DAT_00023fba;
      }
      if ((*(byte *)(iVar8 + 0x3b) & 2) == 0) {
        uVar11 = (uint)sVar6;
        sVar6 = (short)(piVar13[1] - DAT_00023d68 >> (uVar11 & 0x3f));
        sVar7 = (short)(piVar13[2] - DAT_00023d64 >> (uVar11 & 0x3f));
        sVar9 = (short)(piVar13[3] - DAT_00023d60 >> (uVar11 & 0x3f));
        local_2a = 5000 >> (uVar11 & 0x3f);
        local_1a = (short)((int)sVar9 * (int)DAT_00023c80 +
                           (int)sVar7 * (int)DAT_00023c7a + (int)sVar6 * (int)DAT_00023c74 + 0x800
                          >> 0xc);
        local_16 = (short)((int)sVar9 * (int)DAT_00023c82 +
                           (int)sVar7 * (int)DAT_00023c7c + (int)sVar6 * (int)DAT_00023c76 + 0x800
                          >> 0xc);
        local_12 = (short)((int)sVar9 * (int)DAT_00023c84 +
                           (int)sVar7 * (int)DAT_00023c7e + (int)sVar6 * (int)DAT_00023c78 + 0x800
                          >> 0xc);
        sVar6 = 1;
      }
      else {
        uVar1 = *(short *)((int)piVar13 + 0x36) - sVar6;
        if ((int)((uint)uVar1 << 0x10) < 0) {
          uVar11 = -(int)(short)uVar1;
          sStack_20 = (short)(DAT_00023d68 - piVar13[1] >> (uVar11 & 0x3f));
          sVar7 = (short)(DAT_00023d64 - piVar13[2] >> (uVar11 & 0x3f));
          sStack_2c = (short)(DAT_00023d60 - piVar13[3] >> (uVar11 & 0x3f));
          local_2a = 5000 >> (uVar11 & 0x3f);
        }
        else {
          uVar11 = (uint)(short)uVar1;
          sStack_20 = (short)(DAT_00023d68 - piVar13[1] << (uVar11 & 0x3f));
          sVar7 = (short)(DAT_00023d64 - piVar13[2] << (uVar11 & 0x3f));
          sStack_2c = (short)(DAT_00023d60 - piVar13[3] << (uVar11 & 0x3f));
          local_2a = 5000 << (uVar11 & 0x3f);
        }
        piVar13 = *(int **)((int)piVar13 + 0x46);
        puVar14 = auStack_1c;
        sVar9 = 1;
        uVar11 = (uint)sVar6;
        do {
          sVar6 = (short)(*piVar13 >> (uVar11 & 0x3f)) - sStack_20;
          sVar2 = (short)(piVar13[1] >> (uVar11 & 0x3f)) - sVar7;
          sVar3 = (short)(piVar13[2] >> (uVar11 & 0x3f)) - sStack_2c;
          *(short *)(puVar14 + 2) =
               (short)((int)sVar3 * (int)DAT_00023c80 +
                       (int)sVar2 * (int)DAT_00023c7a + (int)sVar6 * (int)DAT_00023c74 + 0x800 >>
                      0xc);
          *(short *)(puVar14 + 6) =
               (short)((int)sVar3 * (int)DAT_00023c82 +
                       (int)sVar2 * (int)DAT_00023c7c + (int)sVar6 * (int)DAT_00023c76 + 0x800 >>
                      0xc);
          *(short *)(puVar14 + 10) =
               (short)((int)sVar3 * (int)DAT_00023c84 +
                       (int)sVar2 * (int)DAT_00023c7e + (int)sVar6 * (int)DAT_00023c78 + 0x800 >>
                      0xc);
          piVar13 = piVar13 + 3;
          puVar14 = puVar14 + 0xc;
          sVar9 = sVar9 + -1;
        } while (sVar9 != -1);
        sVar6 = 2;
      }
      puVar14 = auStack_1c;
      while (sVar6 = sVar6 + -1, sVar6 != -1) {
        if (4 < *(short *)(puVar14 + 10)) {
          iVar8 = (int)*(short *)(puVar14 + 10);
          iVar12 = ((int)*(short *)(puVar14 + 2) * (int)DAT_00027050) / iVar8;
          iVar10 = ((int)DAT_0002704e * -(int)*(short *)(puVar14 + 6)) / iVar8;
          if ((((DAT_0002701a <= iVar12) && (iVar12 <= DAT_00027018 + -1)) &&
              (DAT_00027016 <= iVar10)) && (iVar10 <= DAT_00027014 + -1)) {
            if (local_2a < iVar8) {
              iVar4 = local_2a;
              if (local_2a < 0) {
                iVar4 = local_2a + 1;
              }
              if (local_2a + (iVar4 >> 1) < iVar8) {
                if (local_2a * 2 < (int)*(short *)(puVar14 + 10)) {
                  uVar5 = 1;
                }
                else {
                  uVar5 = 2;
                }
              }
              else {
                uVar5 = 3;
              }
            }
            else {
              uVar5 = 4;
            }
            *(undefined2 *)(local_32 + 6) = uVar5;
            local_32[8] = (undefined1)DAT_00023fb8;
            (*DAT_00023fb0)(local_32,iVar12,iVar10,uVar5);
            local_32 = local_32 + 0xe;
          }
        }
        puVar14 = puVar14 + 0xc;
      }
      bVar15 = local_1e != 0;
      local_1e = local_1e + -1;
      local_26 = local_26 + 1;
    } while (bVar15);
  }
  DAT_0002cb84 = 0;
  DAT_00023fbc = (short)((int)(local_32 + -0x2c83c) / 0xe);
  return;
}



// FUN_0001a66a @ 0x1a66a

uint FUN_0001a66a(int *param_1,int *param_2)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  uint uVar5;
  uint uVar6;
  
  iVar1 = *param_1;
  iVar2 = *(int *)(iVar1 + 0x10);
  if (iVar2 < 0) {
    iVar2 = iVar2 + 1;
  }
  iVar3 = *(int *)(iVar1 + 0xc);
  if (iVar3 < 0) {
    iVar3 = iVar3 + 1;
  }
  iVar4 = *(int *)(iVar1 + 8);
  if (iVar4 < 0) {
    iVar4 = iVar4 + 1;
  }
  uVar5 = FUN_00004908(param_1[1] - (iVar4 >> 1),param_1[2] - (iVar3 >> 1),param_1[3] - (iVar2 >> 1)
                       ,*(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc),
                       *(undefined4 *)(iVar1 + 0x10),param_2[1],param_2[2],param_2[3]);
  if ((int)uVar5 <= param_2[8]) {
    if ((*(byte *)((int)param_2 + 0x16) & 4) == 0) {
      if (*param_2 != 0) {
        if (**(short **)(*param_2 + 4) == 0x14) {
          return 0x14;
        }
        if ((**(short **)(*param_2 + 4) == 0x24) && (uVar5 = FUN_00013672(), (uVar5 & 0xf) == 0)) {
          *(undefined2 *)(*param_2 + 0x46) = 1;
        }
      }
      uVar5 = FUN_0000605a(param_1);
    }
    else {
      uVar6 = FUN_00013672();
      uVar5 = uVar6 & 0xffff0007;
      if ((uVar6 & 7) == 0) {
        *(int **)(iVar1 + 0x3c) = param_2;
      }
    }
  }
  return uVar5;
}



// FUN_0001a72c @ 0x1a72c

ushort FUN_0001a72c(int param_1,int param_2)

{
  ushort uVar1;
  
  uVar1 = FUN_00013652();
  if (uVar1 <= *(ushort *)(*(int *)(param_1 + 4) + 0x50)) {
    uVar1 = FUN_00013652();
    uVar1 = uVar1 & 0xff;
    if (uVar1 < 6) {
      *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 1;
    }
    else if (uVar1 < 0x2b) {
      *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 2;
    }
    else if (uVar1 < 0x38) {
      *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 4;
    }
    else if (uVar1 < 0x45) {
      *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 8;
    }
    else if (uVar1 < 0x51) {
      *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 0x10;
    }
    else if (uVar1 < 0x5e) {
      *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 0x20;
    }
    else {
      uVar1 = *(ushort *)(param_1 + 0x698) & 0x3c0;
      if (uVar1 == 0) {
        *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 0x40;
      }
      else {
        if ((*(byte *)(param_1 + 0x698) & 2) != 0) {
          *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) & 0xfdff;
          *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 0x100;
        }
        *(undefined2 *)(param_1 + 0x69c) = 0;
      }
    }
    if (param_2 != 0) {
      *(ushort *)(param_1 + 0x698) = *(ushort *)(param_1 + 0x698) | 0x8000;
    }
  }
  return uVar1;
}



// FUN_0001a7e6 @ 0x1a7e6

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

uint FUN_0001a7e6(int *param_1,int *param_2)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  uint uVar5;
  uint uVar6;
  
  iVar1 = *param_1;
  iVar2 = *(int *)(iVar1 + 0x10);
  if (iVar2 < 0) {
    iVar2 = iVar2 + 1;
  }
  iVar3 = *(int *)(iVar1 + 0xc);
  if (iVar3 < 0) {
    iVar3 = iVar3 + 1;
  }
  iVar4 = *(int *)(iVar1 + 8);
  if (iVar4 < 0) {
    iVar4 = iVar4 + 1;
  }
  uVar5 = FUN_00004908(param_1[1] - (iVar4 >> 1),param_1[2] - (iVar3 >> 1),param_1[3] - (iVar2 >> 1)
                       ,*(undefined4 *)(iVar1 + 8),*(undefined4 *)(iVar1 + 0xc),
                       *(undefined4 *)(iVar1 + 0x10),param_2[1],param_2[2],param_2[3]);
  if ((int)uVar5 <= param_2[8]) {
    iVar2 = *param_2;
    uVar6 = FUN_00013672();
    uVar5 = uVar6 & 0xffff0007;
    if ((uVar6 & 7) == 0) {
      FUN_0001a72c(iVar2,(int)*(short *)(iVar1 + 0x36));
      uVar5 = FUN_0000605a(param_1);
    }
    else if (iVar2 == SUB_00020b28) {
      uVar5 = FUN_00013672();
      if ((uVar5 & 1) != 0) {
        _DAT_0002102a = 1;
      }
    }
  }
  return uVar5;
}



// FUN_0001a89c @ 0x1a89c

ushort FUN_0001a89c(undefined4 param_1,undefined4 param_2)

{
  ushort uVar1;
  
  uVar1 = FUN_00013672();
  uVar1 = uVar1 & 0x1f;
  if (uVar1 == 0) {
    uVar1 = FUN_000157aa(param_2,param_1);
  }
  return uVar1;
}



// FUN_0001a8c2 @ 0x1a8c2

int FUN_0001a8c2(undefined4 param_1,int param_2,short param_3,undefined4 param_4)

{
  int iVar1;
  
  iVar1 = FUN_000021a4(param_2 + param_3 * 0xc);
  *(short *)(param_2 + -2 + iVar1) = param_3;
  FUN_00003624(param_1,(int)param_3,param_4,param_2 + iVar1);
  return iVar1;
}



// FUN_0001a90c @ 0x1a90c

int FUN_0001a90c(undefined4 param_1,int param_2,short param_3,undefined4 param_4)

{
  int iVar1;
  
  iVar1 = FUN_000021a4(param_2 + param_3 * 0xc);
  *(short *)(param_2 + -2 + iVar1) = param_3;
  FUN_0001f63a(param_4,param_2 + iVar1,param_3 * 0xc);
  return iVar1;
}



// FUN_0001a956 @ 0x1a956

int FUN_0001a956(int param_1,int param_2,short *param_3)

{
  int *piVar1;
  short sVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  short sVar8;
  int iVar6;
  int iVar7;
  short sVar9;
  uint uVar10;
  int iVar11;
  int *piVar12;
  
  iVar11 = *(int *)(param_1 + 0x24);
  do {
    if (iVar11 == 0) {
      return 0;
    }
    sVar8 = 0;
    if (0 < *(short *)(iVar11 + 0x30)) {
      do {
        piVar1 = *(int **)(*(int *)(iVar11 + 0x32) + 0x14 + sVar8 * 0x18);
        if ((((piVar1 != (int *)0x0) && (*(char *)(piVar1 + 10) == '\x02')) &&
            (*(short *)(piVar1 + 0xf) == 1)) &&
           (((*piVar1 == 0 && (*(int *)((int)piVar1 + 0x4a) != 0)) &&
            ((*(byte *)(*(int *)((int)piVar1 + 0x4a) + 0x13) & 2) != 0)))) {
          sVar8 = *(short *)(*(int *)((int)piVar1 + 0x4a) + 0x14);
          sVar2 = *(short *)((int)piVar1 + 0x36);
          iVar6 = FUN_000021a4(param_2 + sVar8 * 0xc);
          *(short *)(param_2 + -2 + iVar6) = sVar8;
          iVar11 = 0x7fffffff;
          sVar9 = 0;
          if (0 < sVar8) {
            uVar10 = (uint)sVar2;
            do {
              piVar12 = (int *)(*(short *)(*(int *)(*(int *)((int)piVar1 + 0x4a) + 0x18) + sVar9 * 2
                                          ) * 0xc + *(int *)((int)piVar1 + 0x46));
              iVar3 = *piVar12 >> (uVar10 & 0x3f);
              iVar4 = piVar12[1] >> (uVar10 & 0x3f);
              iVar5 = piVar12[2] >> (uVar10 & 0x3f);
              iVar7 = FUN_000048a6(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
                                   *(undefined4 *)(param_1 + 0xc),iVar3,iVar4,iVar5);
              if (iVar7 < iVar11) {
                *param_3 = sVar9;
                iVar11 = iVar7;
              }
              piVar12 = (int *)(iVar6 + param_2 + sVar9 * 0xc);
              *piVar12 = iVar3;
              piVar12[1] = iVar4;
              piVar12[2] = iVar5;
              sVar9 = sVar9 + 1;
            } while (sVar9 < sVar8);
          }
          return iVar6;
        }
        sVar8 = sVar8 + 1;
      } while (sVar8 < *(short *)(iVar11 + 0x30));
    }
    iVar11 = *(int *)(iVar11 + 0x24);
  } while( true );
}



// FUN_0001aaa4 @ 0x1aaa4

int * FUN_0001aaa4(int param_1,short *param_2)

{
  int iVar1;
  uint uVar2;
  uint uVar3;
  short sVar4;
  int *piVar5;
  
  piVar5 = (int *)(param_2 + *param_2 * 6 + 2);
  iVar1 = FUN_000048a6(*(undefined4 *)(param_1 + 4),0,*(undefined4 *)(param_1 + 0xc),*piVar5,0,
                       piVar5[2]);
  if (iVar1 < 5000) {
    *param_2 = *param_2 + 1;
    if (param_2[1] <= *param_2) {
      *param_2 = 0;
    }
    piVar5 = (int *)(param_2 + *param_2 * 6 + 2);
    uVar2 = FUN_000048a6(*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
                         *(undefined4 *)(param_1 + 0xc),*piVar5,piVar5[1],piVar5[2]);
    sVar4 = 0;
    for (; (uVar2 & 0xffff8000) != 0; uVar2 = (int)uVar2 >> 1) {
      sVar4 = sVar4 + 1;
    }
    uVar3 = (uint)sVar4;
    iVar1 = (int)(short)((int)uVar2 >> (uVar3 & 0x3f));
    *piVar5 = ((short)(*piVar5 - *(int *)(param_1 + 4) >> (uVar3 & 0x3f)) * 5000) / iVar1 + *piVar5;
    piVar5[1] = ((short)(piVar5[1] - *(int *)(param_1 + 8) >> (uVar3 & 0x3f)) * 5000) / iVar1 +
                piVar5[1];
    piVar5[2] = ((short)(piVar5[2] - *(int *)(param_1 + 0xc) >> (uVar3 & 0x3f)) * 5000) / iVar1 +
                piVar5[2];
  }
  return piVar5;
}



// FUN_0001ab7a @ 0x1ab7a

void FUN_0001ab7a(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x5e);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x12000;
  *(undefined2 *)(param_1 + 0x36) = 0;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_0001abb8 @ 0x1abb8

undefined4 FUN_0001abb8(undefined4 param_1,int param_2)

{
  ushort uVar1;
  
  uVar1 = 0;
  do {
    if ((&DAT_0002cc04)[(short)uVar1] == 0) {
      (&DAT_0002cc04)[(short)uVar1] = param_1;
      break;
    }
    uVar1 = uVar1 + 1;
  } while (uVar1 < 2);
  if (uVar1 != 0) {
    *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1ab5a;
  }
  return 0;
}



// FUN_0001ac04 @ 0x1ac04

void FUN_0001ac04(undefined4 param_1,int param_2)

{
  *(undefined2 *)(param_2 + 0x34) = 0x4b;
  *(undefined2 *)(param_2 + 0x36) = 1;
  return;
}



// FUN_0001ac16 @ 0x1ac16

void FUN_0001ac16(undefined4 param_1,int param_2)

{
  *(undefined2 *)(param_2 + 0x34) = 0x4b;
  *(undefined2 *)(param_2 + 0x36) = 1;
  return;
}



// FUN_0001ac28 @ 0x1ac28

int FUN_0001ac28(int param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84((&DAT_0002cc04)[param_1],param_2,param_3,param_4,0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(short *)(iVar2 + 0x32) = (short)param_1;
    (**(code **)(&DAT_00020734 + param_1 * 4))(piVar1,iVar2);
  }
  return iVar2;
}



// FUN_0001ac8a @ 0x1ac8a

int FUN_0001ac8a(undefined4 param_1,undefined4 param_2,undefined4 param_3,int *param_4)

{
  int iVar1;
  
  iVar1 = FUN_0001ac28(0,param_1,param_2,param_3);
  if (iVar1 == 0) {
    iVar1 = 0;
  }
  else {
    *(undefined2 *)(iVar1 + 0x32) = 0;
    *(int *)(iVar1 + 0x58) = *param_4;
    *(undefined2 *)(iVar1 + 0x5c) = 1;
    *(short *)(param_4 + 0x1a8) = *(short *)(param_4 + 0x1a8) + 1;
    *(char *)(*param_4 + 0x29) = *(char *)(*param_4 + 0x29) + '\x01';
    FUN_0001b1ca(iVar1);
  }
  return iVar1;
}



// FUN_0001acde @ 0x1acde

int FUN_0001acde(undefined4 param_1,int param_2,undefined4 param_3,int *param_4)

{
  int iVar1;
  int iVar2;
  
  iVar1 = FUN_0001ac28(1,param_1,param_2,param_3);
  if (iVar1 == 0) {
    iVar1 = 0;
  }
  else {
    *(undefined2 *)(iVar1 + 0x32) = 1;
    *(int *)(iVar1 + 0x58) = *param_4;
    *(undefined2 *)(iVar1 + 0x5c) = 1;
    *(char *)(*param_4 + 0x29) = *(char *)(*param_4 + 0x29) + '\x01';
    FUN_0001b1ca(iVar1);
    iVar2 = FUN_0000483e(param_1,param_2,param_3);
    if (param_2 < 0xc9) {
      if (iVar2 < 10000) {
        DAT_00021032 = 0xf;
      }
      *(undefined2 *)(iVar1 + 0x34) = 10;
      *(undefined2 *)(iVar1 + 0x50) = 0x19;
      *(undefined2 *)(iVar1 + 0x52) = 1;
      *(undefined2 *)(iVar1 + 0x54) = 5;
    }
    else {
      if (iVar2 < 5000) {
        DAT_00021034 = 0x1e;
      }
      else if (iVar2 < 20000) {
        DAT_00021032 = 0xf;
      }
      *(undefined2 *)(iVar1 + 0x50) = 100;
      *(undefined2 *)(iVar1 + 0x52) = 4;
      *(undefined2 *)(iVar1 + 0x54) = 0x3c;
    }
  }
  return iVar1;
}



// FUN_0001adac @ 0x1adac

void FUN_0001adac(int param_1)

{
  char *pcVar1;
  short *psVar2;
  
  if (*(short *)(param_1 + 0x32) == 0) {
    if ((*(int *)(param_1 + 0x58) != 0) &&
       (pcVar1 = (char *)(*(int *)(param_1 + 0x58) + 0x29), *pcVar1 = *pcVar1 + -1,
       *(short *)(param_1 + 0x5c) != 0)) {
      psVar2 = (short *)(**(int **)(param_1 + 0x58) + 0x6a0);
      *psVar2 = *psVar2 + -1;
    }
  }
  else if ((*(short *)(param_1 + 0x32) == 1) && (*(int *)(param_1 + 0x58) != 0)) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x58) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  return;
}



// FUN_0001adf4 @ 0x1adf4

undefined2 FUN_0001adf4(int *param_1)

{
  int iVar1;
  int *piVar2;
  int iVar3;
  int iVar4;
  bool bVar5;
  uint uVar6;
  ushort uVar7;
  short sVar8;
  short sVar9;
  undefined2 uVar10;
  
  bVar5 = false;
  uVar10 = 0;
  piVar2 = (int *)param_1[0x16];
  iVar1 = *piVar2;
  if (((*(byte *)((int)piVar2 + 0x17) & 2) != 0) || ((*(ushort *)(iVar1 + 0x698) & 0x3c0) == 0)) {
    if (*(short *)((int)param_1 + 0x36) == 1) {
      *(undefined2 *)(param_1 + 0xd) = 0;
    }
    bVar5 = true;
    *(undefined2 *)((int)param_1 + 0x36) = 4;
  }
  if ((DAT_00021058 == 1) && (*(short *)((int)param_1 + 0x36) != 1)) {
    bVar5 = true;
    *(undefined2 *)((int)param_1 + 0x36) = 4;
  }
  switch(*(undefined2 *)((int)param_1 + 0x36)) {
  case 1:
    *(undefined2 *)((int)param_1 + 0x36) = 2;
  case 2:
    iVar3 = *(int *)(*param_1 + 0x46);
    uVar7 = *(ushort *)((int)param_1 + 0x56);
    sVar8 = 0;
    do {
      if ((uVar7 & 1) == 0) {
        if ((sVar8 == 1) || (sVar8 == 2)) {
          sVar9 = 0x22;
        }
        else {
          sVar9 = -0x22;
        }
        iVar4 = sVar8 * 0xc;
        *(int *)(iVar3 + iVar4) = piVar2[1];
        *(int *)(iVar3 + 4 + iVar4) = piVar2[2] + (int)sVar9;
        *(int *)(iVar3 + 8 + iVar4) = piVar2[3];
      }
      sVar8 = sVar8 + 1;
      uVar7 = (short)uVar7 >> 1;
    } while (sVar8 < 4);
    uVar7 = *(ushort *)((int)param_1 + 0x56);
    if ((uVar7 == 0) || (uVar6 = FUN_00013672(), (uVar6 & 3) == 0)) {
      if ((uVar7 & 3) == 3) {
        uVar6 = FUN_00013672();
        if ((uVar6 & 1) == 0) {
          uVar7 = uVar7 | 8;
        }
        else {
          uVar7 = uVar7 | 4;
        }
      }
      else {
        uVar6 = FUN_00013672();
        if ((uVar6 & 1) == 0) {
          uVar7 = uVar7 | 2;
        }
        else {
          uVar7 = uVar7 | 1;
        }
      }
    }
    *(ushort *)((int)param_1 + 0x56) = uVar7;
    if ((uVar7 == 0xf) || (*(short *)(param_1 + 0xd) == 0)) {
      bVar5 = true;
      *(undefined2 *)((int)param_1 + 0x36) = 3;
    }
    break;
  case 3:
    if (*(char *)(iVar1 + 0x699) < '\0') {
      uVar7 = FUN_00013672();
      uVar7 = uVar7 & 0x1f;
    }
    else {
      uVar7 = FUN_00013672();
      uVar7 = uVar7 & 3;
    }
    if (uVar7 == 0) {
      FUN_0001ac8a(piVar2[1],piVar2[2],piVar2[3],iVar1);
      *(undefined2 *)((int)param_1 + 0x36) = 4;
    }
    break;
  case 4:
    if ((*(short *)(param_1 + 0xd) == 0) || (DAT_00021058 == 1)) {
      uVar10 = 1;
    }
  }
  if (bVar5) {
    *(short *)(iVar1 + 0x6a0) = *(short *)(iVar1 + 0x6a0) + -1;
    *(undefined2 *)(param_1 + 0x17) = 0;
  }
  return uVar10;
}



// FUN_0001af8e @ 0x1af8e

undefined2 FUN_0001af8e(int *param_1)

{
  int iVar1;
  byte *pbVar2;
  bool bVar3;
  uint uVar4;
  int iVar5;
  short sVar6;
  short sVar8;
  int iVar7;
  ushort uVar10;
  int iVar9;
  int iVar11;
  undefined2 uVar12;
  
  iVar7 = *param_1;
  bVar3 = false;
  uVar12 = 0;
  iVar9 = param_1[0x16];
  iVar1 = *(int *)(iVar7 + 0x46);
  uVar10 = *(ushort *)((int)param_1 + 0x56);
  if ((*(byte *)(iVar9 + 0x17) & 2) != 0) {
    if (*(short *)((int)param_1 + 0x36) == 1) {
      *(undefined2 *)(param_1 + 0xd) = 0;
    }
    bVar3 = true;
    *(undefined2 *)((int)param_1 + 0x36) = 4;
  }
  if ((DAT_00021058 == 1) && (*(short *)((int)param_1 + 0x36) != 1)) {
    bVar3 = true;
    *(undefined2 *)((int)param_1 + 0x36) = 4;
  }
  sVar6 = *(short *)((int)param_1 + 0x36);
  if (sVar6 != 2) {
    if (2 < sVar6) {
      if ((sVar6 == 4) && ((*(short *)(param_1 + 0xd) == 0 || (DAT_00021058 == 1)))) {
        uVar12 = 1;
      }
      goto LAB_0001b0c8;
    }
    if (sVar6 != 1) goto LAB_0001b0c8;
    *(undefined2 *)((int)param_1 + 0x36) = 2;
  }
  sVar6 = 0;
  do {
    if ((uVar10 & 1) == 0) {
      if ((sVar6 == 1) || (sVar6 == 2)) {
        sVar8 = *(short *)(param_1 + 0x14);
      }
      else {
        sVar8 = -*(short *)(param_1 + 0x14);
      }
      iVar11 = sVar6 * 0xc;
      *(undefined4 *)(iVar1 + iVar11) = *(undefined4 *)(iVar9 + 4);
      *(int *)(iVar1 + 4 + iVar11) = *(int *)(iVar9 + 8) + (int)sVar8;
      *(undefined4 *)(iVar1 + 8 + iVar11) = *(undefined4 *)(iVar9 + 0xc);
    }
    sVar6 = sVar6 + 1;
    uVar10 = (short)uVar10 >> 1;
  } while (sVar6 < 4);
  uVar10 = *(ushort *)((int)param_1 + 0x56);
  if ((uVar10 == 0) || (uVar4 = FUN_00013672(), (uVar4 & 1) == 0)) {
    if ((uVar10 & 3) == 3) {
      uVar4 = FUN_00013672();
      if ((uVar4 & 1) == 0) {
        uVar10 = uVar10 | 8;
      }
      else {
        uVar10 = uVar10 | 4;
      }
    }
    else {
      uVar4 = FUN_00013672();
      if ((uVar4 & 1) == 0) {
        uVar10 = uVar10 | 2;
      }
      else {
        uVar10 = uVar10 | 1;
      }
    }
  }
  *(ushort *)((int)param_1 + 0x56) = uVar10;
  if ((uVar10 == 0xf) || (*(short *)(param_1 + 0xd) == 0)) {
    bVar3 = true;
    *(undefined2 *)((int)param_1 + 0x36) = 4;
  }
LAB_0001b0c8:
  sVar6 = 0;
  do {
    if ((uVar10 & 1) != 0) {
      if ((sVar6 == 1) || (sVar6 == 2)) {
        *(int *)(iVar1 + 4 + sVar6 * 0xc) =
             *(int *)(iVar1 + 4 + sVar6 * 0xc) + (int)*(short *)((int)param_1 + 0x52);
      }
      else {
        *(int *)(iVar1 + 4 + sVar6 * 0xc) =
             *(int *)(iVar1 + 4 + sVar6 * 0xc) - (int)*(short *)((int)param_1 + 0x52);
      }
    }
    sVar6 = sVar6 + 1;
    uVar10 = (short)uVar10 >> 1;
  } while (sVar6 < 4);
  if (*(short *)(param_1 + 0xd) < *(short *)(param_1 + 0x15)) {
    pbVar2 = (byte *)(*(int *)(iVar7 + 0x4a) + 0x33);
    *pbVar2 = *pbVar2 | 2;
  }
  else {
    iVar11 = 0;
    iVar9 = 0;
    iVar7 = 0;
    sVar6 = 0;
    do {
      iVar5 = sVar6 * 0xc;
      iVar7 = *(int *)(iVar1 + iVar5) + iVar7;
      iVar9 = *(int *)(iVar1 + 4 + iVar5) + iVar9;
      iVar11 = *(int *)(iVar1 + 8 + iVar5) + iVar11;
      sVar6 = sVar6 + 1;
    } while (sVar6 < 4);
    sVar6 = 0;
    do {
      iVar5 = *(int *)(iVar1 + sVar6 * 0xc) - (iVar7 >> 2);
      if (iVar5 < 0) {
        iVar5 = iVar5 + 1;
      }
      *(int *)(iVar1 + 0x30 + sVar6 * 0xc) = (iVar7 >> 2) + (iVar5 >> 1);
      iVar5 = *(int *)(iVar1 + 4 + sVar6 * 0xc) - (iVar9 >> 2);
      if (iVar5 < 0) {
        iVar5 = iVar5 + 1;
      }
      *(int *)(iVar1 + sVar6 * 0xc + 0x34) = (iVar9 >> 2) + (iVar5 >> 1);
      iVar5 = *(int *)(iVar1 + 8 + sVar6 * 0xc) - (iVar11 >> 2);
      if (iVar5 < 0) {
        iVar5 = iVar5 + 1;
      }
      *(int *)(iVar1 + sVar6 * 0xc + 0x38) = (iVar11 >> 2) + (iVar5 >> 1);
      sVar6 = sVar6 + 1;
    } while (sVar6 < 4);
  }
  if (bVar3) {
    *(undefined2 *)(param_1 + 0x17) = 0;
  }
  return uVar12;
}



// FUN_0001b1ca @ 0x1b1ca

void FUN_0001b1ca(int *param_1)

{
  short sVar1;
  
  sVar1 = (**(code **)(&DAT_0002073c + *(short *)((int)param_1 + 0x32) * 4))(param_1);
  if (*(short *)(param_1 + 0xd) == 0) {
    *(uint *)(*param_1 + 0x14) = *(uint *)(*param_1 + 0x14) | 0x40;
  }
  else {
    *(short *)(param_1 + 0xd) = *(short *)(param_1 + 0xd) + -1;
  }
  if (sVar1 != 0) {
    FUN_0000605a(*param_1);
  }
  return;
}



// FUN_0001b20a @ 0x1b20a

void FUN_0001b20a(int param_1)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x54);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x12040;
  *(undefined2 *)(param_1 + 0x36) = 0;
  piVar1[1] = DAT_00020784;
  return;
}



// FUN_0001b238 @ 0x1b238

undefined4 FUN_0001b238(undefined4 param_1,int param_2)

{
  DAT_0002cc0c = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1b1ea;
  return 0;
}



// FUN_0001b25a @ 0x1b25a

int FUN_0001b25a(undefined4 param_1,undefined4 param_2,undefined4 param_3)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002cc0c,param_1,param_2,param_3,0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(undefined2 *)(iVar2 + 0x34) = 1;
    *(undefined2 *)(iVar2 + 0x32) = 0x2d;
  }
  return iVar2;
}



// FUN_0001b2a0 @ 0x1b2a0

int * FUN_0001b2a0(undefined4 param_1,undefined4 param_2,undefined4 param_3,int *param_4)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_0001b25a(param_1,param_2,param_3);
  if (piVar1 != (int *)0x0) {
    piVar1[0x14] = *param_4;
    *(char *)(*param_4 + 0x29) = *(char *)(*param_4 + 0x29) + '\x01';
    if (*piVar1 != 0) {
      return piVar1;
    }
  }
  return (int *)0x0;
}



// FUN_0001b2e0 @ 0x1b2e0

void FUN_0001b2e0(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x50) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x50) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  return;
}



// FUN_0001b2f6 @ 0x1b2f6

undefined2 FUN_0001b2f6(int *param_1)

{
  int iVar1;
  int iVar2;
  uint *puVar3;
  int *piVar4;
  int iVar5;
  int iVar6;
  short sVar9;
  int iVar7;
  short sVar10;
  uint uVar8;
  short sVar11;
  ushort uVar12;
  int iVar13;
  int local_e;
  
  iVar1 = *param_1;
  piVar4 = (int *)param_1[0x14];
  iVar2 = *piVar4;
  iVar5 = FUN_000038e8(*(undefined4 *)(iVar2 + 8));
  iVar6 = FUN_000038e8(*(undefined4 *)(iVar2 + 0x10));
  iVar6 = iVar6 + iVar5;
  if (((*(byte *)((int)piVar4 + 0x17) & 2) != 0) || (iVar6 == 0)) {
    if (*(short *)(param_1 + 0xd) == 1) {
      *(undefined2 *)((int)param_1 + 0x32) = 0;
    }
    *(undefined2 *)(param_1 + 0xd) = 3;
  }
  if ((DAT_00021058 == 1) && (*(short *)(param_1 + 0xd) != 1)) {
    *(undefined2 *)(param_1 + 0xd) = 3;
  }
  sVar11 = *(short *)(param_1 + 0xd);
  if (sVar11 != 2) {
    if (2 < sVar11) {
      if (sVar11 != 3) {
        return 0;
      }
      if (*(short *)((int)param_1 + 0x32) != 0) {
        *(short *)((int)param_1 + 0x32) = *(short *)((int)param_1 + 0x32) + -1;
      }
      if ((*(short *)((int)param_1 + 0x32) == 0) || (DAT_00021058 == 1)) {
        return 1;
      }
      return 0;
    }
    if (sVar11 != 1) {
      return 0;
    }
    *(undefined2 *)(param_1 + 0xd) = 2;
    puVar3 = (uint *)(iVar1 + 0x14);
    *puVar3 = *puVar3 & 0xffffffbf;
  }
  iVar1 = *(int *)(iVar1 + 0x46);
  uVar12 = *(ushort *)((int)param_1 + 0x4e);
  local_e = 0;
  iVar5 = 0;
  sVar11 = 0;
  do {
    if ((uVar12 & 1) == 0) {
      if ((sVar11 == 2) || (sVar11 == 3)) {
        sVar9 = (short)((*(int *)(iVar2 + 0x10) << 5) / iVar6);
        iVar7 = -*(int *)(iVar2 + 8);
      }
      else {
        sVar9 = (short)((*(int *)(iVar2 + 0x10) * -0x20) / iVar6);
        iVar7 = *(int *)(iVar2 + 8);
      }
      sVar10 = (short)((iVar7 << 5) / iVar6);
      iVar7 = sVar11 * 0xc;
      *(int *)(iVar1 + iVar7) = piVar4[1] + (int)sVar9;
      *(undefined4 *)(iVar1 + 4 + iVar7) = 0;
      *(int *)(iVar1 + 8 + iVar7) = piVar4[3] + (int)sVar10;
      if (sVar9 < 0) {
        sVar9 = sVar9 + 7;
      }
      *(short *)((int)param_1 + sVar11 * 2 + 0x36) = sVar9 >> 3;
      if (sVar10 < 0) {
        sVar10 = sVar10 + 7;
      }
      *(short *)((int)param_1 + sVar11 * 2 + 0x46) = sVar10 >> 3;
    }
    else {
      iVar13 = (int)sVar11;
      iVar7 = iVar13 * 0xc;
      *(int *)(iVar1 + iVar7) =
           (int)*(short *)((int)param_1 + iVar13 * 2 + 0x36) + *(int *)(iVar1 + iVar7);
      *(int *)(iVar1 + 8 + iVar7) =
           *(int *)(iVar1 + 8 + iVar7) + (int)*(short *)((int)param_1 + iVar13 * 2 + 0x46);
    }
    iVar5 = *(int *)(iVar1 + sVar11 * 0xc) + iVar5;
    local_e = *(int *)(iVar1 + 8 + sVar11 * 0xc) + local_e;
    sVar11 = sVar11 + 1;
    uVar12 = (short)uVar12 >> 1;
  } while (sVar11 < 4);
  if (iVar5 < 0) {
    iVar5 = iVar5 + 3;
  }
  *(int *)(iVar1 + 0x30) = iVar5 >> 2;
  *(undefined4 *)(iVar1 + 0x34) = 0;
  if (local_e < 0) {
    local_e = local_e + 3;
  }
  *(int *)(iVar1 + 0x38) = local_e >> 2;
  uVar12 = *(ushort *)((int)param_1 + 0x4e);
  if ((*(ushort *)(iVar2 + 0x698) & 0x1800) == 0) {
    uVar12 = uVar12 | 0xf;
  }
  else if ((uVar12 & 9) != 9) {
    uVar8 = FUN_00013672();
    if ((uVar8 & 1) == 0) {
      uVar12 = uVar12 | 8;
    }
    else {
      uVar12 = uVar12 | 1;
    }
  }
  *(ushort *)((int)param_1 + 0x4e) = uVar12;
  if (uVar12 == 0xf) {
    *(undefined2 *)(param_1 + 0xd) = 3;
  }
  return 0;
}



// FUN_0001b524 @ 0x1b524

void FUN_0001b524(undefined4 *param_1)

{
  short sVar1;
  
  sVar1 = FUN_0001b2f6(param_1);
  if (sVar1 != 0) {
    FUN_0000605a(*param_1);
  }
  return;
}



// FUN_0001b546 @ 0x1b546

void FUN_0001b546(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x38);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x152000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_0001b580 @ 0x1b580

undefined4 FUN_0001b580(undefined4 param_1,int param_2)

{
  DAT_0002cc10 = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1b526;
  return 0;
}



// FUN_0001b5a2 @ 0x1b5a2

int FUN_0001b5a2(undefined4 param_1,undefined4 param_2,undefined4 param_3)

{
  int *piVar1;
  int iVar2;
  ushort uVar3;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002cc10,param_1,param_2,param_3,0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(int *)(iVar2 + 0x32) = DAT_00021020 + 0x2d;
    uVar3 = FUN_00013652();
    *(uint *)(iVar2 + 8) = (uVar3 & 0xff) - 0x7f;
    uVar3 = FUN_00013652();
    *(uint *)(iVar2 + 0xc) = (uVar3 & 0xff) - 0x7f;
    uVar3 = FUN_00013652();
    *(uint *)(iVar2 + 0x10) = (uVar3 & 0xff) - 0x7f;
    *(short *)(iVar2 + 0x36) = *(short *)(iVar2 + 0xe) << 4;
    uVar3 = FUN_00013652();
    *(ushort *)(iVar2 + 0x20) = (uVar3 & 0xff) << 6;
    uVar3 = FUN_00013652();
    *(ushort *)(iVar2 + 0x22) = (uVar3 & 0xff) << 6;
    uVar3 = FUN_00013652();
    *(ushort *)(iVar2 + 0x24) = (uVar3 & 0xff) << 6;
  }
  return iVar2;
}



// FUN_0001b666 @ 0x1b666

void FUN_0001b666(int *param_1)

{
  int iVar1;
  
  iVar1 = *param_1;
  *(ushort *)(param_1 + 8) = *(short *)(param_1 + 8) + 0x400U & 0x3fff;
  *(ushort *)((int)param_1 + 0x22) = *(short *)((int)param_1 + 0x22) + 0x200U & 0x3fff;
  *(ushort *)(param_1 + 9) = *(short *)(param_1 + 9) + 0x100U & 0x3fff;
  *(short *)((int)param_1 + 0x36) = *(short *)((int)param_1 + 0x36) + -0x5b;
  param_1[3] = (int)(*(short *)((int)param_1 + 0x36) >> 4);
  if ((DAT_00021058 == 3) && (iVar1 != 0)) {
    param_1[5] = *(int *)(iVar1 + 4);
    param_1[6] = *(int *)(iVar1 + 8);
    param_1[7] = *(int *)(iVar1 + 0xc);
    *(int *)(iVar1 + 4) = param_1[2] + *(int *)(iVar1 + 4);
    *(int *)(iVar1 + 8) = param_1[3] + *(int *)(iVar1 + 8);
    *(int *)(iVar1 + 0xc) = param_1[4] + *(int *)(iVar1 + 0xc);
  }
  if ((*(int *)((int)param_1 + 0x32) == DAT_00021020) ||
     (*(int *)(iVar1 + 8) <= *(int *)((int)param_1 + 0x2e))) {
    FUN_0000605a(iVar1);
  }
  return;
}



// FUN_0001b70a @ 0x1b70a

void FUN_0001b70a(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x32);
  *piVar1 = param_1;
  *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 2;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_0001b742 @ 0x1b742

undefined4 FUN_0001b742(undefined4 param_1,int param_2)

{
  DAT_0002cc14 = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1b6ea;
  return 0;
}



// FUN_0001b764 @ 0x1b764

void FUN_0001b764(int *param_1)

{
  if (*(int *)(*param_1 + 0x3e) <= DAT_00021020) {
    FUN_0000605a(*param_1);
  }
  return;
}



// FUN_0001b782 @ 0x1b782

undefined4 FUN_0001b782(int param_1,int param_2,int param_3,int param_4)

{
  int iVar1;
  int iVar2;
  undefined4 *puVar3;
  undefined4 uVar4;
  short sVar5;
  int iVar6;
  
  puVar3 = (undefined4 *)FUN_00005f84(DAT_0002cc14,param_2,param_3,param_4,0,0,0,0);
  if (puVar3 == (undefined4 *)0x0) {
    uVar4 = 0;
  }
  else {
    iVar6 = (param_1 << 8) / 100;
    iVar1 = *(int *)((int)puVar3 + 0x46);
    sVar5 = 0;
    do {
      iVar2 = sVar5 * 0xc;
      *(int *)(iVar1 + iVar2) =
           param_2 + (iVar6 * ((*(int *)(iVar1 + iVar2) >> (*(ushort *)((int)puVar3 + 0x36) & 0x3f))
                              - param_2) >> 8);
      *(int *)(iVar1 + 4 + iVar2) =
           param_3 + (iVar6 * ((*(int *)(iVar1 + 4 + iVar2) >>
                               (*(ushort *)((int)puVar3 + 0x36) & 0x3f)) - param_3) >> 8);
      *(int *)(iVar1 + 8 + iVar2) =
           param_4 + (iVar6 * ((*(int *)(iVar1 + 8 + iVar2) >>
                               (*(ushort *)((int)puVar3 + 0x36) & 0x3f)) - param_4) >> 8);
      sVar5 = sVar5 + 1;
    } while (sVar5 < 6);
    *(undefined2 *)((int)puVar3 + 0x36) = 0;
    puVar3[8] = puVar3[8] * iVar6 >> 8;
    *(int *)((int)puVar3 + 0x3e) = DAT_00021020 + 900;
    uVar4 = *puVar3;
  }
  return uVar4;
}



// FUN_0001b856 @ 0x1b856

void FUN_0001b856(int *param_1)

{
  short sVar1;
  short sVar2;
  int *piVar3;
  
  if (DAT_0002cc2a < 8) {
    sVar1 = *(short *)(*param_1 + 10);
    sVar2 = *(short *)(*param_1 + 0x12);
    if (param_1[7] <= DAT_0002cc1a) {
      piVar3 = (int *)(DAT_00020744 + DAT_0002cc2a * 0x14);
      DAT_0002cc2a = DAT_0002cc2a + 1;
      *piVar3 = param_1[1];
      piVar3[1] = param_1[3];
      piVar3[2] = (int)sVar1;
      piVar3[3] = (int)sVar2;
      piVar3[4] = 0;
    }
  }
  return;
}



// FUN_0001b8bc @ 0x1b8bc

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0001b8bc(int param_1)

{
  _DAT_0002cc22 = 1;
  DAT_0002cc1e = param_1;
  DAT_0002cc1a = param_1 << 1;
  return;
}



// FUN_0001b8d8 @ 0x1b8d8

void FUN_0001b8d8(void)

{
  undefined *puVar1;
  
  if (DAT_0002cc1e == 0xc15c0) {
    puVar1 = &DAT_00026ac0;
  }
  else if (DAT_0002cc1e == 0xce40) {
    puVar1 = (undefined *)0xc15c0;
  }
  else {
    puVar1 = (undefined *)0xce40;
  }
  FUN_0001b8bc(puVar1);
  return;
}



// FUN_0001b90c @ 0x1b90c

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0001b90c(void)

{
  DAT_0002cc26 = 0;
  DAT_0002cc2a = 0;
  _DAT_0002cc22 = 1;
  return;
}



// FUN_0001b922 @ 0x1b922

void FUN_0001b922(short param_1,short param_2)

{
  int iVar1;
  int iVar2;
  undefined1 *puVar3;
  undefined1 *puVar4;
  undefined1 *puVar5;
  
  iVar1 = (int)*(short *)(SUB_000207fc + 0x10);
  puVar4 = (undefined1 *)((int)param_1 + *(int *)(SUB_000207fc + 6) + iVar1 * (param_2 + -1));
  iVar2 = (int)*(short *)(SUB_000207fc + 0xe4);
  puVar3 = (undefined1 *)
           ((int)param_1 + iVar2 * (param_2 + -1) + (*(int *)(SUB_000207fc + 0xe0) - DAT_00021564));
  *puVar4 = *puVar3;
  puVar4[1] = puVar3[1];
  puVar4 = puVar4 + iVar1;
  puVar3 = puVar3 + iVar2;
  puVar4[-1] = puVar3[-1];
  *puVar4 = *puVar3;
  puVar4[1] = puVar3[1];
  puVar4[2] = puVar3[2];
  puVar5 = puVar4 + -1 + iVar1;
  puVar4 = puVar3 + -1 + iVar2;
  *puVar5 = *puVar4;
  puVar5[1] = puVar4[1];
  puVar5[2] = puVar4[2];
  puVar5[3] = puVar4[3];
  puVar5[iVar1 + 1] = puVar4[iVar2 + 1];
  (puVar5 + iVar1 + 1)[1] = (puVar4 + iVar2 + 1)[1];
  return;
}



// FUN_0001ba0a @ 0x1ba0a

undefined4 FUN_0001ba0a(short param_1,short param_2)

{
  short sVar1;
  short sVar2;
  int iVar3;
  short sVar5;
  uint uVar4;
  undefined1 *puVar6;
  undefined1 *puVar7;
  undefined1 *puVar8;
  
  sVar1 = *(short *)(SUB_000207fc + 0x10);
  sVar2 = *(short *)(SUB_000207fc + 0xe4);
  sVar5 = param_1;
  if (param_1 < 0) {
    sVar5 = param_1 + 1;
  }
  puVar7 = (undefined1 *)
           ((int)(sVar5 >> 1) + *(int *)(SUB_000207fc + 6) + (int)sVar1 * (param_2 + -1));
  iVar3 = *(int *)(SUB_000207fc + 0xe0) - DAT_00021564;
  sVar5 = param_1;
  if (param_1 < 0) {
    sVar5 = param_1 + 1;
  }
  puVar6 = (undefined1 *)((int)(sVar5 >> 1) + (int)sVar2 * (param_2 + -1) + iVar3);
  sVar5 = param_1;
  if (param_1 < 0) {
    sVar5 = param_1 + 1;
  }
  uVar4 = CONCAT22((short)((uint)iVar3 >> 0x10),sVar5) & 0xfffffffe;
  param_1 = param_1 - (short)uVar4;
  if (param_1 == 0) {
    *puVar7 = *puVar6;
    puVar7 = puVar7 + sVar1;
    puVar6 = puVar6 + sVar2;
    puVar7[-1] = puVar6[-1];
    *puVar7 = *puVar6;
    puVar7[1] = puVar6[1];
    puVar8 = puVar7 + -1 + sVar1;
    puVar7 = puVar6 + -1 + sVar2;
    *puVar8 = *puVar7;
    puVar8[1] = puVar7[1];
    puVar8[2] = puVar7[2];
    puVar8[sVar1 + 1] = puVar7[sVar2 + 1];
  }
  else {
    *puVar7 = *puVar6;
    puVar7[1] = puVar6[1];
    puVar7 = puVar7 + sVar1;
    puVar6 = puVar6 + sVar2;
    *puVar7 = *puVar6;
    puVar7[1] = puVar6[1];
    puVar7 = puVar7 + sVar1;
    puVar6 = puVar6 + sVar2;
    *puVar7 = *puVar6;
    puVar7[1] = puVar6[1];
    puVar7[sVar1] = puVar6[sVar2];
    (puVar7 + sVar1)[1] = (puVar6 + sVar2)[1];
  }
  return CONCAT22((short)(uVar4 >> 0x10),param_1);
}



// FUN_0001baea @ 0x1baea

void FUN_0001baea(ushort param_1,short param_2)

{
  short sVar1;
  ushort uVar2;
  byte bVar3;
  byte *pbVar4;
  byte *pbVar5;
  
  sVar1 = *(short *)(SUB_000207fc + 0x10);
  uVar2 = param_1;
  if ((short)param_1 < 0) {
    uVar2 = param_1 + 1;
  }
  pbVar4 = (byte *)((int)((short)uVar2 >> 1) +
                   *(int *)(SUB_000207fc + 6) + (int)sVar1 * (param_2 + -1));
  uVar2 = param_1;
  if ((short)param_1 < 0) {
    uVar2 = param_1 + 1;
  }
  bVar3 = (byte)*(undefined2 *)(SUB_000207fc + 0x1ea);
  if (param_1 == (uVar2 & 0xfffe)) {
    *pbVar4 = bVar3;
    pbVar4 = pbVar4 + sVar1;
    pbVar5 = pbVar4 + -1;
    *pbVar5 = bVar3 ^ (bVar3 ^ *pbVar5) & 0xf0;
    *pbVar4 = bVar3;
    pbVar4[1] = bVar3 ^ (bVar3 ^ pbVar4[1]) & 0xf;
    pbVar5 = pbVar5 + sVar1;
    *pbVar5 = bVar3 ^ (bVar3 ^ *pbVar5) & 0xf0;
    pbVar5[1] = bVar3;
    pbVar5[2] = bVar3 ^ (bVar3 ^ pbVar5[2]) & 0xf;
    pbVar5[sVar1 + 1] = bVar3;
  }
  else {
    *pbVar4 = bVar3 ^ (bVar3 ^ *pbVar4) & 0xf0;
    pbVar4[1] = bVar3 ^ (bVar3 ^ pbVar4[1]) & 0xf;
    pbVar4 = pbVar4 + sVar1;
    *pbVar4 = bVar3;
    pbVar4[1] = bVar3;
    pbVar4 = pbVar4 + sVar1;
    *pbVar4 = bVar3;
    pbVar4[1] = bVar3;
    pbVar4 = pbVar4 + sVar1;
    *pbVar4 = bVar3 ^ (bVar3 ^ *pbVar4) & 0xf0;
    pbVar4[1] = bVar3 ^ (bVar3 ^ pbVar4[1]) & 0xf;
  }
  return;
}



// FUN_0001bbd6 @ 0x1bbd6

void FUN_0001bbd6(short param_1,short param_2)

{
  (**(code **)(SUB_000207fc + 0x2ae))((int)param_1,(int)param_2,param_1 + 1,param_2 + 1);
  return;
}



// FUN_0001bc02 @ 0x1bc02

void FUN_0001bc02(short param_1,short param_2)

{
  (**(code **)(SUB_000207fc + 0x2ae))((int)param_1,(int)param_2,param_1 + 1,param_2 + 1);
  return;
}



// FUN_0001bc2e @ 0x1bc2e

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0001bc2e(void)

{
  short sVar1;
  int iVar2;
  short sVar3;
  short sVar4;
  
  if (_DAT_0002cc22 != 0) {
    iVar2 = ((int)*(short *)(SUB_000207fc + 0x1e8) - (int)*(short *)(SUB_000207fc + 0x1e4)) * 0x10;
    if (iVar2 < 0) {
      iVar2 = iVar2 + 1;
    }
    DAT_0002cc18 = (undefined2)((int)DAT_0002cc1e / (iVar2 >> 1));
    _DAT_0002cc22 = 0;
    sVar1 = *(short *)(SUB_000207fc + 0x1de);
    sVar4 = *(short *)(SUB_000207fc + 0x1e0);
    if (DAT_0002cc1e == (undefined *)0xce40) {
      sVar3 = sVar1;
    }
    else {
      sVar3 = sVar4;
    }
    (**(code **)(SUB_000207fc + 0x2c6))(SUB_000207fc + 0x1c6,(int)sVar3);
    if (DAT_0002cc1e == &DAT_00026ac0) {
      sVar3 = sVar1;
    }
    else {
      sVar3 = sVar4;
    }
    (**(code **)(SUB_000207fc + 0x2c6))(SUB_000207fc + 0x1ce,(int)sVar3);
    if (DAT_0002cc1e == (undefined *)0xc15c0) {
      sVar4 = sVar1;
    }
    (**(code **)(SUB_000207fc + 0x2c6))(SUB_000207fc + 0x1d6,(int)sVar4);
  }
  return;
}



// FUN_0001bd0a @ 0x1bd0a

void FUN_0001bd0a(void)

{
  int iVar1;
  short sVar2;
  short sVar3;
  short sVar4;
  short sVar5;
  short sVar6;
  int *piVar7;
  short sVar8;
  short sVar9;
  short sVar10;
  short sVar11;
  int iVar12;
  int iVar13;
  undefined4 *puVar14;
  int *piVar15;
  
  iVar1 = *SUB_00020b28;
  sVar2 = *(short *)(SUB_000207fc + 0x1ec);
  sVar3 = *(short *)(SUB_000207fc + 0x1ee);
  sVar8 = FUN_000023c4((int)DAT_00023d3e);
  sVar9 = FUN_00002426((int)DAT_00023d3e);
  if (*(short *)(SUB_000207fc + 0x9e) == 0) {
    puVar14 = DAT_00020748;
    sVar6 = DAT_0002cc26._2_2_;
    while (sVar6 = sVar6 + -1, sVar6 != -1) {
      if (puVar14[4] != 0) {
        (*DAT_00023fc0)(*puVar14,puVar14[1]);
      }
      puVar14 = puVar14 + 5;
    }
  }
  piVar15 = DAT_00020744;
  piVar7 = DAT_00020744;
  sVar6 = DAT_0002cc2a._2_2_;
  while (sVar6 = sVar6 + -1, sVar6 != -1) {
    sVar10 = (short)((*piVar15 - *(int *)(iVar1 + 4)) / (int)DAT_0002cc18);
    sVar11 = (short)((piVar15[1] - *(int *)(iVar1 + 0xc)) / (int)DAT_0002cc18);
    sVar4 = sVar2 + (short)((uint)((int)sVar10 * (int)sVar9 - (int)sVar11 * (int)sVar8) >> 0x10);
    DAT_00020744 = piVar7;
    *piVar15 = (int)sVar4;
    sVar5 = sVar3 - (short)((uint)((int)sVar11 * (int)sVar9 + (int)sVar10 * (int)sVar8) >> 0x10);
    piVar15[1] = (int)sVar5;
    if ((((sVar4 < *(short *)(SUB_000207fc + 0x1e4)) || (*(short *)(SUB_000207fc + 0x1e8) <= sVar4))
        || (sVar5 < *(short *)(SUB_000207fc + 0x1e2))) ||
       (*(short *)(SUB_000207fc + 0x1e6) <= sVar5)) {
      piVar15[4] = 0;
    }
    else {
      if (*(short *)(SUB_000207fc + 0x9e) == 0) {
        (*DAT_00023fc4)((int)sVar4,(int)sVar5);
      }
      else {
        iVar12 = (int)sVar11;
        iVar13 = (int)sVar10;
        (**(code **)(SUB_000207fc + 0x2aa))
                  ((int)*(short *)(SUB_000207fc + 0xe8),iVar13,iVar12 + -1,iVar13,iVar12 + 1);
        (**(code **)(SUB_000207fc + 0x2aa))
                  ((int)*(short *)(SUB_000207fc + 0xe8),iVar13 + -1,iVar12,iVar13 + 1,iVar12);
      }
      piVar15[4] = 1;
    }
    piVar15 = piVar15 + 5;
    piVar7 = DAT_00020744;
  }
  DAT_0002cc26 = (int)DAT_0002cc2a._2_2_;
  DAT_00020744 = DAT_00020748;
  DAT_00020748 = piVar7;
  DAT_0002cc2a = 0;
  return;
}



// FUN_0001bec6 @ 0x1bec6

/* WARNING: Removing unreachable block (ram,0x0001bf3e) */
/* WARNING: Removing unreachable block (ram,0x0001bf50) */

int * FUN_0001bec6(int param_1,int param_2)

{
  int iVar1;
  int *piVar2;
  ushort uVar3;
  ushort uVar4;
  
  piVar2 = (int *)FUN_000021a4(0x48);
  *piVar2 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x30040;
  if (param_2 == 0x18) {
    *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 0x10;
  }
  piVar2[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  uVar3 = FUN_00013672();
  uVar4 = uVar3 & 0xf;
  iVar1 = *(int *)(param_1 + 0x18);
  if (iVar1 == 0x80e80) {
    uVar4 = (uVar4 - (uVar3 & 0xc)) + 1;
  }
  else if (iVar1 < 0x80e81) {
    if (iVar1 == 0x40740) {
      uVar4 = (ushort)((uint)(int)(short)uVar4 % 3);
    }
  }
  else if (iVar1 == 0xc15c0) {
    uVar4 = uVar4 % 4 + 2;
  }
  *(ushort *)((int)piVar2 + 0x32) = uVar4;
  return piVar2;
}



// FUN_0001bf68 @ 0x1bf68

void FUN_0001bf68(int param_1)

{
  int iVar1;
  char *pcVar2;
  short sVar3;
  
  sVar3 = 0;
  do {
    iVar1 = *(int *)(param_1 + 0x34 + sVar3 * 4);
    if (iVar1 != 0) {
      pcVar2 = (char *)(iVar1 + 0x29);
      *pcVar2 = *pcVar2 + -1;
    }
    sVar3 = sVar3 + 1;
  } while (sVar3 < 3);
  return;
}



// FUN_0001bf84 @ 0x1bf84

void FUN_0001bf84(int *param_1)

{
  int iVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  ushort uVar8;
  int iVar5;
  int iVar6;
  short sVar9;
  ushort uVar10;
  int *piVar7;
  short sVar11;
  short sVar12;
  int iVar13;
  int local_a;
  ushort local_6;
  
  if ((*(short *)((int)param_1 + 0x32) != 0) && (DAT_00021058 == 0)) {
    sVar12 = 0;
    sVar9 = 0;
    do {
      piVar7 = (int *)param_1[sVar9 + 0xd];
      if (piVar7 != (int *)0x0) {
        if (((*(byte *)((int)piVar7 + 0x17) & 2) == 0) && (*(int *)*piVar7 != 0)) {
          if ((*(ushort *)(*piVar7 + 0x698) & 0x3fe) == 0) {
            sVar12 = sVar12 + 1;
          }
        }
        else {
          *(char *)((int)piVar7 + 0x29) = *(char *)((int)piVar7 + 0x29) + -1;
          param_1[sVar9 + 0xd] = 0;
        }
      }
      sVar9 = sVar9 + 1;
    } while (sVar9 < 5);
    if ((sVar12 == 0) && (uVar4 = FUN_00013672(), (uVar4 & 0x1ff) == 0)) {
      iVar13 = *param_1;
      iVar1 = *SUB_00020b28;
      if (*(short *)((int)param_1 + 0x32) < 3) {
        sVar12 = *(short *)((int)param_1 + 0x32);
      }
      else {
        sVar12 = 3;
      }
      uVar8 = FUN_00013672();
      sVar12 = (short)((int)(uint)uVar8 % (int)sVar12) + 1;
      iVar2 = *(int *)(iVar13 + 4) - *(int *)(iVar1 + 4);
      iVar3 = *(int *)(iVar13 + 0xc) - *(int *)(iVar1 + 0xc);
      iVar5 = FUN_000038e8(iVar2);
      iVar6 = FUN_000038e8(iVar3);
      sVar9 = 0;
      for (uVar4 = iVar6 + iVar5; (uVar4 & 0xffff8000) != 0; uVar4 = (int)uVar4 >> 1) {
        sVar9 = sVar9 + 1;
      }
      FUN_00002574(iVar2 >> ((int)sVar9 & 0x3fU),0,iVar3 >> ((int)sVar9 & 0x3fU),0,0,0,&local_6);
      uVar8 = FUN_00013652();
      local_6 = local_6 + ((uVar8 & 0x1f) - 0x10) * 0x40 & 0x3fff;
      iVar13 = *(int *)(iVar13 + 0x18);
      sVar9 = FUN_000023c4(local_6);
      sVar11 = (short)((iVar13 << 1) / 3 >> 5);
      local_a = *(int *)(iVar1 + 4) - ((int)sVar9 * (int)sVar11 >> 7);
      uVar8 = FUN_00013652();
      sVar9 = FUN_00002426((int)(short)local_6);
      iVar13 = *(int *)(iVar1 + 0xc) + ((int)sVar11 * (int)sVar9 >> 7);
      uVar10 = FUN_00013652();
      local_6 = local_6 + ((uVar10 & 0x1f) - 0x10) * 0x40 + 0x2000 & 0x3fff;
      sVar9 = 0;
      sVar11 = 0;
      if (0 < sVar12) {
        do {
          while (param_1[sVar9 + 0xd] != 0) {
            sVar9 = sVar9 + 1;
          }
          uVar10 = FUN_00013652();
          local_a = (uVar10 & 0xffff0fff) + local_a + -0x800;
          uVar10 = FUN_00013652();
          iVar13 = (uVar10 & 0xffff0fff) + iVar13 + -0x800;
          piVar7 = (int *)FUN_0000f6b2(local_a,(uint)(uVar8 & 0xfff) * 10 + 30000,iVar13,0,
                                       (int)(short)local_6,0,1);
          if (piVar7 != (int *)0x0) {
            *(short *)((int)param_1 + 0x32) = *(short *)((int)param_1 + 0x32) + -1;
            param_1[sVar9 + 0xd] = *piVar7;
            *(char *)(*piVar7 + 0x29) = *(char *)(*piVar7 + 0x29) + '\x01';
          }
          sVar11 = sVar11 + 1;
        } while (sVar11 < sVar12);
      }
    }
  }
  return;
}



// FUN_0001c1a2 @ 0x1c1a2

void FUN_0001c1a2(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x38);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x130040;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(undefined2 *)((int)piVar1 + 0x32) = 10;
  piVar1[0xd] = DAT_00021020 + 0x546;
  return;
}



// FUN_0001c1f2 @ 0x1c1f2

void FUN_0001c1f2(int param_1)

{
  int iVar1;
  uint uVar2;
  
  if (((*(short *)(param_1 + 0x32) != 0) && (DAT_00021058 == 0)) &&
     (*(int *)(param_1 + 0x34) <= DAT_00021020)) {
    FUN_00013652();
    FUN_00013652();
    iVar1 = FUN_0000f6b2();
    if (iVar1 != 0) {
      *(ushort *)(iVar1 + 0x698) = *(ushort *)(iVar1 + 0x698) | 0x20;
      *(short *)(param_1 + 0x32) = *(short *)(param_1 + 0x32) + -1;
    }
    if (*(short *)(param_1 + 0x32) == 5) {
      iVar1 = 0x1356;
    }
    else {
      iVar1 = 0x546;
    }
    uVar2 = FUN_00013672();
    *(uint *)(param_1 + 0x34) = (uVar2 & 0xffff) % 0x1c2 + DAT_00021020 + iVar1 + -0xe1;
  }
  return;
}



// FUN_0001c2ae @ 0x1c2ae

void FUN_0001c2ae(int *param_1)

{
  int *piVar1;
  int *piVar2;
  int *piVar3;
  int *piVar4;
  char cVar5;
  int iVar6;
  int iVar7;
  short sVar10;
  uint uVar8;
  int iVar9;
  ushort uVar11;
  short sVar12;
  int iVar13;
  int iVar14;
  short local_3a;
  short local_38;
  undefined1 auStack_36 [18];
  undefined1 auStack_24 [2];
  short local_22;
  short local_1e;
  short local_1a;
  short local_18;
  short local_16;
  undefined4 local_14;
  undefined1 auStack_10 [12];
  
  piVar4 = *(int **)((int)param_1 + 0x6a2);
  if (*(int *)((int)param_1 + 0x6a6) < 0x4e21) {
    iVar9 = *param_1;
    piVar3 = (int *)*piVar4;
    if ((*(short *)param_1[1] == 1) || (*(short *)param_1[1] == 2)) {
      sVar10 = DAT_0002105e;
    }
    else if (*(short *)param_1[1] == 6) {
      sVar10 = DAT_00021060;
      if (DAT_00021060 < 0) {
        sVar10 = DAT_00021060 + 1;
      }
      sVar10 = sVar10 >> 1;
    }
    else {
      sVar10 = DAT_00021060;
    }
    iVar14 = (int)*(short *)((int)piVar3 + 0x612) - (int)sVar10;
    if (iVar14 < 0) {
      iVar14 = iVar14 + 0x4b;
    }
    piVar1 = (int *)((int)piVar3 + iVar14 * 0x14 + 0x36);
    iVar14 = iVar14 + 1;
    if (iVar14 == 0x4b) {
      iVar14 = 0;
    }
    piVar2 = (int *)((int)piVar3 + iVar14 * 0x14 + 0x36);
    iVar14 = sVar10 + 4;
    iVar13 = *piVar1 + iVar14 * (*piVar2 - *piVar1);
    iVar6 = piVar1[1] + iVar14 * (piVar2[1] - piVar1[1]);
    iVar14 = piVar1[2] + iVar14 * (piVar2[2] - piVar1[2]);
    iVar7 = FUN_000048a6(*(undefined4 *)(iVar9 + 4),*(undefined4 *)(iVar9 + 8),
                         *(undefined4 *)(iVar9 + 0xc),iVar13,iVar6,iVar14);
    FUN_000121e4(param_1,iVar13,iVar6,iVar14,&local_14,auStack_10,&local_16,&local_18);
    iVar14 = FUN_000038e8((int)local_18);
    if ((0x53f < iVar14) && (*(char *)((int)param_1 + 0x6aa) != '\x04')) {
      sVar10 = FUN_0000f87c((int)*(short *)((int)piVar3 + 0x22) -
                            (int)*(short *)((int)param_1 + 0x22));
      iVar14 = FUN_000038e8((int)sVar10);
      if ((iVar14 < 0x1001) || (uVar8 = FUN_00013672(), (uVar8 & 1) == 0)) {
        FUN_00012b40(param_1);
        do {
          if (*(char *)((int)param_1 + 0x6aa) == '\0') {
            param_1[0x1ab] = *(int *)(iVar9 + 4);
            param_1[0x1ac] = *(int *)(iVar9 + 8);
            param_1[0x1ad] = *(int *)(iVar9 + 0xc);
            uVar11 = FUN_00013672();
            if (((uVar11 & 3) == 1) && (0x32a0 < *(int *)((int)param_1 + 0x62a))) {
              sVar10 = FUN_0000f87c((int)*(short *)(param_1 + 8));
              iVar14 = FUN_00003908((int)sVar10);
              if (0x1ff < iVar14) goto LAB_0001c714;
              *(undefined1 *)((int)param_1 + 0x6aa) = 3;
            }
            else {
LAB_0001c714:
              if ((uVar11 & 3) < 2) {
                *(undefined1 *)((int)param_1 + 0x6aa) = 2;
              }
              else {
                *(undefined1 *)((int)param_1 + 0x6aa) = 1;
              }
            }
            *(undefined1 *)((int)param_1 + 0x6ab) = 0;
            param_1[0x1ae] = 0;
            iVar14 = 1;
          }
          else {
            iVar14 = FUN_000048a6(*(undefined4 *)(iVar9 + 4),*(undefined4 *)(iVar9 + 8),
                                  *(undefined4 *)(iVar9 + 0xc),param_1[0x1ab],param_1[0x1ac],
                                  param_1[0x1ad]);
          }
          if ((iVar14 < 2000) && ((param_1[0x1ae] < iVar14 || (iVar14 < 400)))) {
            cVar5 = *(char *)((int)param_1 + 0x6aa);
            if (cVar5 == '\x02') {
              if (*(char *)((int)param_1 + 0x6ab) == '\0') {
                param_1[0x1ac] = *(int *)(iVar9 + 8);
                uVar8 = FUN_00013672();
                if ((uVar8 & 1) == 0) {
                  sVar10 = 0x200;
                  goto LAB_0001c860;
                }
                sVar10 = -0x200;
LAB_0001c84c:
                uVar11 = sVar10 - *(short *)((int)param_1 + 0x22);
                *(undefined1 *)((int)param_1 + 0x6ab) = 2;
              }
              else {
                uVar8 = FUN_00013672();
                if ((uVar8 & 1) != 0) {
                  *(undefined1 *)((int)param_1 + 0x6aa) = 0;
                }
                if (*(char *)((int)param_1 + 0x6ab) == '\x01') {
                  sVar10 = -0x400;
                  goto LAB_0001c84c;
                }
                sVar10 = 0x400;
LAB_0001c860:
                uVar11 = sVar10 - *(short *)((int)param_1 + 0x22);
                *(undefined1 *)((int)param_1 + 0x6ab) = 1;
              }
              sVar10 = FUN_000023c4(uVar11 & 0x3fff);
              param_1[0x1ab] =
                   *(int *)(iVar9 + 4) +
                   ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x2d >> 6) >> 0xc);
              sVar10 = FUN_00002426(uVar11 & 0x3fff);
              param_1[0x1ad] =
                   *(int *)(iVar9 + 0xc) +
                   ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x2d >> 6) >> 0xc);
            }
            else if (cVar5 < '\x03') {
              if (cVar5 == '\x01') {
                if (*(char *)((int)param_1 + 0x6ab) == '\0') {
                  param_1[0x1ac] = *(int *)(iVar9 + 8);
                  sVar10 = FUN_000023c4(-(int)*(short *)((int)param_1 + 0x22) & 0x3fff);
                  param_1[0x1ab] =
                       *(int *)(iVar9 + 4) +
                       ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x4b >> 6) >> 0xc);
                  sVar10 = FUN_00002426(-(int)*(short *)((int)param_1 + 0x22) & 0x3fff);
                  param_1[0x1ad] =
                       *(int *)(iVar9 + 0xc) +
                       ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x4b >> 6) >> 0xc);
LAB_0001c93e:
                  *(undefined1 *)((int)param_1 + 0x6ab) = 1;
                }
                else {
LAB_0001c9b8:
                  *(undefined1 *)((int)param_1 + 0x6aa) = 0;
                }
              }
            }
            else if (cVar5 == '\x03') {
              cVar5 = *(char *)((int)param_1 + 0x6ab);
              if (cVar5 == '\x01') {
                uVar11 = FUN_00013672();
                sVar12 = (uVar11 & 0xff) << 6;
                sVar10 = FUN_000023c4(sVar12);
                param_1[0x1ab] =
                     *(int *)(iVar9 + 4) +
                     ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x78 >> 6) >> 0xc);
                sVar10 = FUN_00002426(sVar12);
                param_1[0x1ad] =
                     *(int *)(iVar9 + 0xc) +
                     ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x78 >> 6) >> 0xc);
                param_1[0x1ac] = *(int *)(iVar9 + 8) + 3000;
                *(undefined1 *)((int)param_1 + 0x6ab) = 2;
              }
              else if (cVar5 < '\x02') {
                if (cVar5 == '\0') {
                  uVar11 = -*(short *)((int)param_1 + 0x22) & 0x3fff;
                  sVar10 = FUN_000023c4(uVar11);
                  param_1[0x1ab] =
                       *(int *)(iVar9 + 4) +
                       ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x2d >> 6) >> 0xc);
                  sVar10 = FUN_00002426(uVar11);
                  param_1[0x1ad] =
                       *(int *)(iVar9 + 0xc) +
                       ((int)sVar10 * (*(int *)((int)param_1 + 0x62a) * 0x2d >> 6) >> 0xc);
                  param_1[0x1ac] = *(int *)(iVar9 + 8) + 5000;
                  goto LAB_0001c93e;
                }
              }
              else if (cVar5 == '\x02') goto LAB_0001c9b8;
            }
            iVar14 = 0x7fffffff;
          }
        } while (*(char *)((int)param_1 + 0x6aa) == '\0');
        param_1[0x1ae] = iVar14;
        FUN_000129b4(param_1,param_1[0x1ab],param_1[0x1ac],param_1[0x1ad]);
        goto LAB_0001c9e6;
      }
      *(undefined1 *)((int)param_1 + 0x6aa) = 4;
    }
    iVar14 = *piVar3;
    iVar14 = FUN_000048a6(*(undefined4 *)(iVar9 + 4),*(undefined4 *)(iVar9 + 8),
                          *(undefined4 *)(iVar9 + 0xc),*(undefined4 *)(iVar14 + 4),
                          *(undefined4 *)(iVar14 + 8),*(undefined4 *)(iVar14 + 0xc));
    FUN_00012840(param_1,local_14,auStack_10,(int)local_16,(int)local_18);
    if (iVar7 < 0x3000) {
      iVar13 = *(int *)((int)piVar3 + 0x62a) >> 2;
      if (param_1[0x1af] == 0) {
        if (iVar14 < 0x5dc) {
          param_1[0x1af] = *(int *)((int)param_1 + 0x6a2);
          *(char *)(param_1[0x1af] + 0x29) = *(char *)(param_1[0x1af] + 0x29) + '\x01';
          iVar13 = 0;
        }
        else if (iVar14 < 10000) {
          sVar10 = FUN_0000f87c((int)*(short *)((int)piVar3 + 0x22) -
                                (int)*(short *)((int)param_1 + 0x22));
          iVar14 = FUN_00003908((int)sVar10);
          if (0x1000 < iVar14) {
            param_1[0x1af] = *(int *)((int)param_1 + 0x6a2);
            *(char *)(param_1[0x1af] + 0x29) = *(char *)(param_1[0x1af] + 0x29) + '\x01';
          }
        }
      }
      if (iVar7 < 0x800) {
        iVar13 = 0;
      }
      if (iVar7 < 0x1000) {
        iVar7 = iVar7 * iVar13;
        if (iVar7 < 0) {
          iVar7 = iVar7 + 0xfff;
        }
        iVar7 = iVar7 >> 0xc;
      }
      else {
        iVar14 = (iVar7 + -0x2000) * (0x1c21 - iVar13);
        if (iVar14 < 0) {
          iVar14 = iVar14 + 0x1fff;
        }
        iVar7 = (iVar14 >> 0xd) + iVar13;
      }
      FUN_00012aba(param_1,iVar7);
    }
    else {
      FUN_00012b40(param_1);
    }
    sVar10 = 0;
    for (uVar8 = *(uint *)((int)param_1 + 0x6a6); (uVar8 & 0xffff8000) != 0; uVar8 = (int)uVar8 >> 1
        ) {
      sVar10 = sVar10 + 1;
    }
    uVar8 = (uint)(short)(sVar10 + 1);
    local_22 = (short)(piVar4[1] - *(int *)(iVar9 + 4) >> (uVar8 & 0x3f));
    local_1e = (short)(piVar4[2] - *(int *)(iVar9 + 8) >> (uVar8 & 0x3f));
    local_1a = (short)(piVar4[3] - *(int *)(iVar9 + 0xc) >> (uVar8 & 0x3f));
    FUN_00002b72(-(int)*(short *)(param_1 + 8) & 0x3fff,
                 -(int)*(short *)((int)param_1 + 0x22) & 0x3fff,
                 -(int)*(short *)(param_1 + 9) & 0x3fff,auStack_36);
    FUN_00003220(1,auStack_24,auStack_24,auStack_36);
    iVar9 = (int)local_1a * (int)local_1a +
            (int)local_1e * (int)local_1e + (int)local_22 * (int)local_22;
    sVar10 = FUN_0000f7e0(iVar9);
    FUN_00002574((int)local_22,(int)local_1e,(int)local_1a,(int)sVar10,iVar9,&local_38,&local_3a);
    local_16 = (*(code *)&LAB_0000f858_4)((int)local_38);
    local_18 = (*(code *)&LAB_0000f858_4)((int)local_3a);
    iVar9 = FUN_000038e8((int)local_16 -
                         ((int)*(short *)((int)param_1 + 0x626) - (int)*(short *)(param_1[1] + 0x46)
                         ));
    if (((iVar9 < 0x40) && (iVar9 = FUN_000038e8((int)local_18), iVar9 < 0x40)) &&
       (*(char *)((int)param_1 + 0x687) == '\0')) {
      *(undefined1 *)((int)param_1 + 0x687) = 1;
    }
    if ((*(char *)((int)param_1 + 0x6aa) != '\x04') ||
       (uVar11 = FUN_00013672(), (uVar11 & 0x1f) == 0)) {
      *(undefined1 *)((int)param_1 + 0x6aa) = 0;
    }
  }
  else {
    FUN_000129b4(param_1,piVar4[1],piVar4[2],piVar4[3]);
    FUN_00012b40(param_1);
    *(undefined1 *)((int)param_1 + 0x6aa) = 0;
  }
LAB_0001c9e6:
  FUN_0000fd16(param_1);
  return;
}



// FUN_0001c9fa @ 0x1c9fa

int * FUN_0001c9fa(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x36);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x112000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  FUN_00008014();
  return piVar1;
}



// FUN_0001ca40 @ 0x1ca40

undefined4 FUN_0001ca40(undefined4 param_1,int param_2)

{
  ushort uVar1;
  
  uVar1 = 0;
  do {
    if ((&DAT_0002cd70)[(short)uVar1] == 0) {
      (&DAT_0002cd70)[(short)uVar1] = param_1;
      break;
    }
    uVar1 = uVar1 + 1;
  } while (uVar1 < 6);
  if (4 < uVar1) {
    *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1c9da;
  }
  return 0;
}



// FUN_0001ca8e @ 0x1ca8e

void FUN_0001ca8e(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x32) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x32) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  return;
}



// FUN_0001caa4 @ 0x1caa4

int FUN_0001caa4(undefined4 param_1,undefined4 param_2,undefined4 param_3,int param_4,int param_5)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(*(undefined4 *)(&DAT_0002cd6c + param_4 * 4),param_1,param_2,param_3,
                               0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    if ((SUB_00020b28 != (int *)0x0) && (param_5 == *SUB_00020b28)) {
      piVar1[6] = 1000000000;
    }
    if (*(short *)(SUB_000207fc + 0xb0) == 0) {
      piVar1[5] = piVar1[5] | 0x40;
    }
    iVar2 = *piVar1;
    *(int *)(iVar2 + 0x32) = param_5;
    *(char *)(param_5 + 0x29) = *(char *)(param_5 + 0x29) + '\x01';
  }
  return iVar2;
}



// FUN_0001cb24 @ 0x1cb24

void FUN_0001cb24(int *param_1)

{
  int iVar1;
  int *piVar2;
  int *piVar3;
  
  iVar1 = *param_1;
  piVar3 = *(int **)((int)param_1 + 0x32);
  piVar2 = (int *)*piVar3;
  if (((*(byte *)((int)piVar3 + 0x17) & 2) == 0) || (*piVar2 != 0)) {
    *(int *)(iVar1 + 4) = piVar2[2] + *(int *)(*(int *)((int)param_1 + 0x32) + 4);
    *(undefined4 *)(iVar1 + 8) = *(undefined4 *)((int)piVar2 + 0x2e);
    *(int *)(iVar1 + 0xc) = piVar2[4] + *(int *)(*(int *)((int)param_1 + 0x32) + 0xc);
    *(undefined2 *)((int)param_1 + 0x22) = *(undefined2 *)((int)piVar2 + 0x22);
  }
  else {
    FUN_0000605a(iVar1);
    *(char *)((int)piVar3 + 0x29) = *(char *)((int)piVar3 + 0x29) + -1;
    *(undefined4 *)((int)param_1 + 0x32) = 0;
  }
  return;
}



// FUN_0001cb86 @ 0x1cb86

undefined4 FUN_0001cb86(int *param_1)

{
  int *piVar1;
  int *piVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  short sVar6;
  undefined4 uVar7;
  short sVar9;
  short sVar10;
  short sVar11;
  int iVar8;
  int iVar12;
  short *psVar13;
  
  iVar12 = *param_1;
  piVar2 = *(int **)((int)param_1 + 0x32);
  if ((*(byte *)((int)piVar2 + 0x17) & 2) == 0) {
    if (**(short **)(*piVar2 + 4) < 8) {
      iVar8 = *(int *)(iVar12 + 0x4a);
      iVar5 = *(int *)(*(int *)(*(int *)(iVar12 + 0x24) + 0x2a) + 0x2e +
                      (uint)*(ushort *)(iVar12 + 0x10) * 0x18);
      iVar3 = *(int *)(SUB_000207fc + 0x8c);
      iVar4 = *(int *)(iVar12 + 0x46);
      sVar9 = (short)(0x32 << (*(ushort *)(iVar12 + 0x36) & 0x3f));
      sVar10 = FUN_00002426((int)*(short *)(*piVar2 + 0x24));
      sVar11 = FUN_00003908((int)sVar10);
      psVar13 = *(short **)(iVar8 + 0x18);
      sVar10 = *(short *)(iVar8 + 0x14) + -1;
      if (sVar10 != -1) {
        iVar12 = (int)sVar9;
        do {
          piVar2 = (int *)(iVar3 + iVar5 + 0x22 + *psVar13 * 0xc);
          iVar8 = FUN_000038e8(*piVar2);
          if (iVar12 < iVar8) {
            piVar1 = (int *)(iVar4 + *psVar13 * 0xc);
            sVar6 = (short)(*piVar2 * (int)sVar11 >> 0xc);
            iVar8 = FUN_000038e8((int)sVar6);
            if (iVar12 < iVar8) {
              *piVar1 = (int)sVar6;
            }
            else {
              if (sVar6 < 0) {
                iVar8 = -iVar12;
              }
              else {
                iVar8 = (int)sVar9;
              }
              *piVar1 = iVar8;
            }
          }
          psVar13 = psVar13 + 1;
          sVar10 = sVar10 + -1;
        } while (sVar10 != -1);
      }
    }
    uVar7 = 0;
  }
  else {
    uVar7 = 4;
  }
  return uVar7;
}



// FUN_0001cc92 @ 0x1cc92

int * FUN_0001cc92(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0xfa);
  *piVar1 = param_1;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(undefined2 *)((int)piVar1 + 0x46) = 0x3c;
  *(undefined **)((int)piVar1 + 0x42) = &DAT_00026ac0;
  *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 2;
  if (*(short *)(param_1 + 0x3c) < 5) {
    *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 1;
    *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 0x10;
  }
  return piVar1;
}



// FUN_0001ccee @ 0x1ccee

void FUN_0001ccee(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x36) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x36) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  if (*(int *)(param_1 + 0x32) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x32) + 0x29);
    *pcVar1 = *pcVar1 + -1;
  }
  return;
}



// FUN_0001cd14 @ 0x1cd14

uint FUN_0001cd14(int *param_1,int *param_2)

{
  int iVar1;
  ushort uVar2;
  char *pcVar3;
  uint uVar4;
  uint uVar5;
  int iVar6;
  int iVar7;
  
  iVar1 = *param_1;
  uVar2 = **(ushort **)(*param_2 + 4);
  uVar4 = (uint)uVar2;
  if (((((*(short *)(iVar1 + 0x3e) == 0x200) && (4 < (short)uVar2)) && ((short)uVar2 < 8)) ||
      ((*(short *)(iVar1 + 0x3e) == 0x100 && ((short)uVar2 < 5)))) &&
     (*(short *)(iVar1 + 0x40) != 0)) {
    uVar5 = FUN_00013672();
    uVar4 = uVar5 & 0xffff0003;
    if ((uVar5 & 3) == 0) {
      iVar6 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]);
      if (iVar6 == 0) {
        iVar7 = 0x7fffffff;
      }
      else {
        iVar7 = 0x339000 / iVar6;
      }
      if (*(int *)(iVar1 + 0x36) != 0) {
        iVar7 = iVar7 >> 1;
      }
      uVar5 = FUN_00013672();
      uVar4 = uVar5 & 0xffff007f;
      if ((int)(uVar5 & 0x7f) < iVar7) {
        if (*(int *)(iVar1 + 0x36) == 0) {
          *(int **)(iVar1 + 0x36) = param_2;
          *(int *)(iVar1 + 0x3a) = iVar6;
        }
        else {
          uVar4 = iVar6 * 2;
          if (*(int *)(iVar1 + 0x3a) <= (int)uVar4) {
            return uVar4;
          }
          *(int *)(iVar1 + 0x3a) = iVar6;
          pcVar3 = (char *)(*(int *)(iVar1 + 0x36) + 0x29);
          *pcVar3 = *pcVar3 + -1;
          *(int **)(iVar1 + 0x36) = param_2;
        }
        *(char *)((int)param_2 + 0x29) = *(char *)((int)param_2 + 0x29) + '\x01';
      }
    }
  }
  return uVar4;
}



// FUN_0001cdee @ 0x1cdee

void FUN_0001cdee(int *param_1)

{
  undefined4 *puVar1;
  int iVar2;
  int *piVar3;
  char *pcVar4;
  int iVar5;
  undefined2 uVar8;
  ushort uVar9;
  uint uVar6;
  short sVar10;
  int iVar7;
  ushort uVar11;
  short sVar12;
  short sVar13;
  int iVar14;
  short sVar15;
  int iVar16;
  ushort uVar17;
  short *psVar18;
  int iVar19;
  short *local_20;
  int local_18;
  short local_c;
  short local_a;
  undefined2 local_8;
  undefined2 local_6;
  
  iVar7 = *param_1;
  if ((*(byte *)(iVar7 + 0x17) & 2) == 0) {
    if (*(short *)(param_1 + 0x10) == 0) {
      if ((*(byte *)(iVar7 + 0x15) & 0x10) == 0) {
        iVar5 = *(int *)(iVar7 + 0x46) + *(short *)(iVar7 + 0x38) * 0xc;
        iVar19 = *(int *)(iVar7 + 0x4a);
        sVar10 = 0;
        if (0 < *(short *)(iVar7 + 0x3c)) {
          local_20 = (short *)(iVar19 + 0x14);
LAB_0001ce7c:
          if (*(char *)(iVar19 + 0x17) != '\x01') goto LAB_0001cf64;
          local_18 = 0;
          iVar16 = 0;
          iVar14 = 0;
          sVar12 = *local_20 + -1;
          if (sVar12 != -1) {
            iVar2 = *(int *)(iVar7 + 0x46);
            uVar6 = (uint)*(short *)(iVar7 + 0x36);
            psVar18 = *(short **)(iVar19 + 0x18);
            do {
              iVar19 = *psVar18 * 0xc;
              iVar14 = (*(int *)(iVar2 + iVar19) >> (uVar6 & 0x3f)) + iVar14;
              iVar16 = (*(int *)(iVar2 + 4 + iVar19) >> (uVar6 & 0x3f)) + iVar16;
              local_18 = (*(int *)(iVar2 + 8 + iVar19) >> (uVar6 & 0x3f)) + local_18;
              sVar12 = sVar12 + -1;
              psVar18 = psVar18 + 1;
            } while (sVar12 != -1);
          }
          param_1[0x12] = iVar14 / (int)*local_20;
          param_1[0x13] = iVar16 / (int)*local_20;
          param_1[0x14] = local_18 / (int)*local_20;
          iVar19 = sVar10 * 0xc;
          piVar3 = *(int **)(iVar7 + 0x46);
          FUN_00002574(*(int *)(iVar5 + iVar19) - *piVar3,*(int *)(iVar5 + 4 + iVar19) - piVar3[1],
                       *(int *)(iVar5 + 8 + iVar19) - piVar3[2],0x4000,0x10000000,&local_6,&local_8)
          ;
          *(undefined2 *)(param_1 + 0x15) = local_6;
          *(undefined2 *)((int)param_1 + 0x56) = local_8;
          *(undefined2 *)(param_1 + 0x16) = 0;
        }
      }
      else {
        *(undefined2 *)(param_1 + 0x15) = 0;
        *(undefined2 *)((int)param_1 + 0x56) = *(undefined2 *)((int)param_1 + 0x22);
        *(undefined2 *)(param_1 + 0x16) = 0;
        param_1[0x12] = *(int *)(iVar7 + 4);
        param_1[0x13] = *(int *)(iVar7 + 8);
        param_1[0x14] = *(int *)(iVar7 + 0xc);
      }
LAB_0001cf78:
      uVar8 = FUN_0000042c(iVar7);
      *(undefined2 *)((int)param_1 + 0x3e) = uVar8;
      *(undefined2 *)(param_1 + 0x10) = 1;
    }
    if ((*(int *)((int)param_1 + 0x36) != 0) && (DAT_00021058 == 0)) {
      uVar9 = 0;
      do {
        iVar19 = (int)(short)uVar9;
        if ((*(int *)((int)param_1 + iVar19 * 4 + 0xd2) != 0) &&
           (*(int *)((int)param_1 + iVar19 * 4 + 0xd2) <= DAT_00021020)) {
          *(undefined4 *)((int)param_1 + iVar19 * 4 + 0xd2) = 0;
          puVar1 = (undefined4 *)((int)param_1 + iVar19 * 0xc + 0x5a);
          FUN_00013d80(2,100,0x1e,0,0,*puVar1,puVar1[1],puVar1[2]);
          FUN_000151b2(*puVar1,puVar1[1],puVar1[2],500,0,1,0);
        }
        uVar9 = uVar9 + 1;
      } while (uVar9 < 10);
      piVar3 = *(int **)((int)param_1 + 0x36);
      if (((*(short *)((int)param_1 + 0x46) == 0) || ((*(byte *)((int)piVar3 + 0x17) & 2) != 0)) ||
         (*(int *)((int)piVar3 + 0x46) == 0)) {
        *(char *)((int)piVar3 + 0x29) = *(char *)((int)piVar3 + 0x29) + -1;
        *(undefined4 *)((int)param_1 + 0x36) = 0;
      }
      else {
        uVar9 = FUN_00013672();
        if ((uVar9 & 0x3f) == 0x15) {
          uVar6 = FUN_000048a6(*(undefined4 *)(iVar7 + 4),*(undefined4 *)(iVar7 + 8),
                               *(undefined4 *)(iVar7 + 0xc),piVar3[1],piVar3[2],piVar3[3]);
          *(uint *)((int)param_1 + 0x3a) = uVar6;
          if ((int)uVar6 <= *(int *)((int)param_1 + 0x42)) {
            sVar10 = 0;
            for (; (uVar6 & 0xffff8000) != 0; uVar6 = (int)uVar6 >> 1) {
              sVar10 = sVar10 + 1;
            }
            uVar6 = (uint)(short)(sVar10 + 1);
            sVar12 = (short)(piVar3[1] - *(int *)(iVar7 + 4) >> (uVar6 & 0x3f));
            sVar13 = (short)(piVar3[2] - *(int *)(iVar7 + 8) >> (uVar6 & 0x3f));
            sVar15 = (short)(piVar3[3] - *(int *)(iVar7 + 0xc) >> (uVar6 & 0x3f));
            iVar7 = (int)sVar15 * (int)sVar15 +
                    (int)sVar13 * (int)sVar13 + (int)sVar12 * (int)sVar12;
            sVar10 = FUN_0000f7e0(iVar7);
            FUN_00002574((int)sVar12,(int)sVar13,(int)sVar15,(int)sVar10,iVar7,&local_a,&local_c);
            if (0x3f < local_a) {
              sVar10 = FUN_0000f87c((int)local_c - (int)*(short *)((int)param_1 + 0x56));
              iVar7 = FUN_00003908((int)sVar10);
              if (iVar7 < 0x1001) {
                uVar17 = 0xffff;
                uVar9 = 0;
                do {
                  if (*(int *)((int)param_1 + (short)uVar9 * 4 + 0xd2) == 0) {
                    uVar17 = uVar9;
                    break;
                  }
                  uVar9 = uVar9 + 1;
                } while (uVar9 < 10);
                if (uVar17 != 0xffff) {
                  *(short *)((int)param_1 + 0x46) = *(short *)((int)param_1 + 0x46) + -1;
                  iVar7 = *(int *)((int)param_1 + 0x3a);
                  if (iVar7 < 0) {
                    iVar7 = iVar7 + 0x3ff;
                  }
                  sVar10 = (short)(iVar7 >> 10);
                  *(int *)((int)param_1 + (short)uVar17 * 4 + 0xd2) = DAT_00021020 + sVar10;
                  uVar9 = FUN_00013652();
                  iVar7 = *piVar3;
                  uVar11 = FUN_00013652();
                  param_1 = (int *)((int)param_1 + (short)uVar17 * 0xc + 0x5a);
                  iVar19 = (int)(short)((uVar9 & 0x3f) + sVar10);
                  *param_1 = (uint)(uVar11 & 0xfff) +
                             piVar3[1] + *(int *)(iVar7 + 8) * iVar19 + -0x800;
                  uVar9 = FUN_00013652();
                  param_1[1] = (uint)(uVar9 & 0xfff) +
                               piVar3[2] + *(int *)(iVar7 + 0xc) * iVar19 + -0x800;
                  uVar9 = FUN_00013652();
                  param_1[2] = (uint)(uVar9 & 0xfff) +
                               piVar3[3] + *(int *)(iVar7 + 0x10) * iVar19 + -0x800;
                  if (param_1[1] + -500 < *(int *)(iVar7 + 0x2e)) {
                    param_1[1] = *(int *)(iVar7 + 0x2e) + 500;
                  }
                }
              }
            }
          }
        }
      }
    }
  }
  else if (*(int *)((int)param_1 + 0x36) != 0) {
    pcVar4 = (char *)(*(int *)((int)param_1 + 0x36) + 0x29);
    *pcVar4 = *pcVar4 + -1;
    *(undefined4 *)((int)param_1 + 0x36) = 0;
  }
  return;
LAB_0001cf64:
  sVar10 = sVar10 + 1;
  local_20 = local_20 + 0x10;
  iVar19 = iVar19 + 0x20;
  if (*(short *)(iVar7 + 0x3c) <= sVar10) goto LAB_0001cf78;
  goto LAB_0001ce7c;
}



// FUN_0001d23a @ 0x1d23a

int * FUN_0001d23a(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x54);
  *piVar1 = param_1;
  *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 2;
  if (*(int *)(param_1 + 8) == 0) {
    *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 0x10;
  }
  else {
    *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 1;
  }
  *(byte *)(param_1 + 0x15) = *(byte *)(param_1 + 0x15) | 0x10;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(undefined4 *)((int)piVar1 + 0x42) = 0xce40;
  return piVar1;
}



// FUN_0001d296 @ 0x1d296

undefined4 FUN_0001d296(undefined4 param_1,int param_2)

{
  DAT_0002cd88 = param_1;
  *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1d21a;
  return 0;
}



// FUN_0001d2b8 @ 0x1d2b8

int FUN_0001d2b8(int param_1)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(DAT_0002cd88,*(undefined4 *)(param_1 + 4),
                               *(undefined4 *)(param_1 + 8),*(undefined4 *)(param_1 + 0xc),0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(int *)(iVar2 + 0x32) = param_1;
    *(char *)(param_1 + 0x29) = *(char *)(param_1 + 0x29) + '\x01';
    *(ushort *)((int)piVar1 + 0x16) = *(ushort *)((int)piVar1 + 0x16) & 0xfbff;
  }
  return iVar2;
}



// FUN_0001d302 @ 0x1d302

void FUN_0001d302(int *param_1)

{
  int *piVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  int iVar5;
  undefined2 uVar8;
  uint uVar6;
  short sVar9;
  int iVar7;
  ushort uVar10;
  
  iVar7 = *param_1;
  if (*(short *)(param_1 + 0x10) == 0) {
    uVar8 = FUN_0000042c(iVar7);
    *(undefined2 *)((int)param_1 + 0x3e) = uVar8;
    *(undefined2 *)(param_1 + 0x10) = 1;
  }
  if (*(int *)((int)param_1 + 0x32) != 0) {
    piVar1 = *(int **)((int)param_1 + 0x32);
    if ((((*(byte *)((int)piVar1 + 0x17) & 2) == 0) && (*(int *)((int)piVar1 + 0x46) != 0)) &&
       ((*piVar1 == 0 || ((*(byte *)(*piVar1 + 0x699) & 0x10) == 0)))) {
      *(int *)(iVar7 + 4) = piVar1[1];
      *(int *)(iVar7 + 8) = piVar1[2];
      *(int *)(iVar7 + 0xc) = piVar1[3];
    }
    else {
      *(char *)((int)piVar1 + 0x29) = *(char *)((int)piVar1 + 0x29) + -1;
      *(undefined4 *)((int)param_1 + 0x32) = 0;
      FUN_000060fc(iVar7);
    }
  }
  if (((*(byte *)(iVar7 + 0x17) & 2) == 0) && (*(int *)((int)param_1 + 0x36) != 0)) {
    iVar2 = *(int *)((int)param_1 + 0x36);
    if (((*(byte *)(iVar2 + 0x17) & 2) == 0) && (*(int *)(iVar2 + 0x46) != 0)) {
      if (*(short *)((int)param_1 + 0x46) == 0) {
        uVar6 = FUN_000048a6(*(undefined4 *)(iVar7 + 4),*(undefined4 *)(iVar7 + 8),
                             *(undefined4 *)(iVar7 + 0xc),*(undefined4 *)(iVar2 + 4),
                             *(undefined4 *)(iVar2 + 8),*(undefined4 *)(iVar2 + 0xc));
        *(uint *)((int)param_1 + 0x3a) = uVar6;
        if ((int)uVar6 <= *(int *)((int)param_1 + 0x42)) {
          sVar9 = 0;
          for (; (uVar6 & 0xffff8000) != 0; uVar6 = (int)uVar6 >> 1) {
            sVar9 = sVar9 + 1;
          }
          uVar6 = (uint)(short)(sVar9 + 1);
          iVar3 = *(int *)(iVar2 + 4) - *(int *)(iVar7 + 4) >> (uVar6 & 0x3f);
          iVar4 = *(int *)(iVar2 + 8) - *(int *)(iVar7 + 8) >> (uVar6 & 0x3f);
          iVar5 = *(int *)(iVar2 + 0xc) - *(int *)(iVar7 + 0xc) >> (uVar6 & 0x3f);
          iVar2 = iVar5 * iVar5 + iVar4 * iVar4 + iVar3 * iVar3;
          sVar9 = FUN_0000f7e0(iVar2);
          FUN_00002574(iVar3,iVar4,iVar5,(int)sVar9,iVar2,param_1 + 0x13,(int)param_1 + 0x4e);
          if (DAT_00021058 == 0) {
            if (*(short *)((int)param_1 + 0x4a) == 0) {
              uVar10 = FUN_00013652();
              if ((uVar10 & 0xf) == 0) {
                uVar10 = FUN_00013652();
                *(ushort *)((int)param_1 + 0x4a) = (uVar10 & 7) + 7;
                uVar10 = FUN_00013652();
                *(ushort *)(param_1 + 0x14) = 0x80 - (uVar10 & 0xff);
                uVar10 = FUN_00013652();
                *(ushort *)((int)param_1 + 0x52) = 0x80 - (uVar10 & 0xff);
              }
            }
            else {
              *(short *)(param_1 + 0x12) = *(short *)(param_1 + 0x12) + -1;
              if (*(short *)(param_1 + 0x12) < 1) {
                FUN_00019d6e(0,0,*(undefined4 *)(iVar7 + 4),*(undefined4 *)(iVar7 + 8),
                             *(undefined4 *)(iVar7 + 0xc),
                             (int)*(short *)(param_1 + 0x14) + (int)*(short *)(param_1 + 0x13) &
                             0x3fff,(int)*(short *)((int)param_1 + 0x52) +
                                    (int)*(short *)((int)param_1 + 0x4e) & 0x3fff,0);
                iVar7 = *(short *)(param_1 + 0x14) * 7;
                if (iVar7 < 0) {
                  iVar7 = iVar7 + 7;
                }
                *(short *)(param_1 + 0x14) = (short)(iVar7 >> 3);
                iVar7 = *(short *)((int)param_1 + 0x52) * 7;
                if (iVar7 < 0) {
                  iVar7 = iVar7 + 7;
                }
                *(short *)((int)param_1 + 0x52) = (short)(iVar7 >> 3);
                *(undefined2 *)(param_1 + 0x12) = 2;
              }
              *(short *)((int)param_1 + 0x4a) = *(short *)((int)param_1 + 0x4a) + -1;
            }
          }
        }
      }
    }
    else {
      *(char *)(iVar2 + 0x29) = *(char *)(iVar2 + 0x29) + -1;
      *(undefined4 *)((int)param_1 + 0x36) = 0;
    }
  }
  return;
}



// FUN_0001d516 @ 0x1d516

undefined4 FUN_0001d516(int param_1)

{
  if ((*(int *)(param_1 + 0x36) != 0) && (*(short *)(param_1 + 0x46) == 0)) {
    *(ushort *)(param_1 + 0x22) = *(ushort *)(param_1 + 0x4e) & 0x3fff;
  }
  return 0;
}



// FUN_0001d536 @ 0x1d536

int * FUN_0001d536(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x6be);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x132000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  FUN_0000f346((int)piVar1 + 0x32,*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
               *(undefined4 *)(param_1 + 0xc));
  return piVar1;
}



// FUN_0001d58e @ 0x1d58e

undefined4 FUN_0001d58e(undefined4 param_1,int param_2)

{
  DAT_0002cd8c = param_1;
  *(code **)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = FUN_0001d516;
  return 0;
}



// FUN_0001d5b0 @ 0x1d5b0

int FUN_0001d5b0(undefined2 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
                short param_5,short param_6,short param_7)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int *piVar4;
  int iVar5;
  short sVar6;
  
  piVar4 = (int *)FUN_00005f84(DAT_0002cd8c,param_2,param_3,param_4,(int)param_5,(int)param_6,
                               (int)param_7,0);
  if (piVar4 == (int *)0x0) {
    iVar5 = 0;
  }
  else {
    iVar5 = *piVar4;
    *(undefined4 *)(iVar5 + 0x610) = DAT_00021020;
    *(undefined2 *)(iVar5 + 0x614) = param_1;
    *(short *)(iVar5 + 0x606) = param_5;
    *(short *)(iVar5 + 0x608) = param_6;
    *(short *)(iVar5 + 0x60a) = param_7;
    iVar2 = *(int *)((int)piVar4 + 0x46);
    sVar6 = 0;
    do {
      iVar3 = sVar6 * 0xc;
      *(int *)(iVar2 + iVar3) = *(int *)(iVar2 + iVar3) >> 3;
      *(int *)(iVar2 + 4 + iVar3) = *(int *)(iVar2 + 4 + iVar3) >> 3;
      *(int *)(iVar2 + 8 + iVar3) = *(int *)(iVar2 + 8 + iVar3) >> 3;
      iVar1 = iVar5 + sVar6 * 4;
      *(int *)(iVar1 + 0x622) = *(int *)(iVar2 + iVar3) * 0x1c6;
      *(int *)(iVar1 + 0x656) = *(int *)(iVar2 + 4 + iVar3) * 0x1c6;
      *(int *)(iVar1 + 0x68a) = *(int *)(iVar2 + 8 + iVar3) * 0x255;
      sVar6 = sVar6 + 1;
    } while (sVar6 < 0xd);
    *(undefined4 *)(iVar5 + 0x618) = 7;
    *(undefined2 *)(iVar5 + 0x61c) = 0x62;
  }
  return iVar5;
}



// FUN_0001d69c @ 0x1d69c

void FUN_0001d69c(int param_1)

{
  if (param_1 == DAT_00021064) {
    DAT_00021064 = 0;
  }
  return;
}



// FUN_0001d6b0 @ 0x1d6b0

void FUN_0001d6b0(int *param_1)

{
  int *piVar1;
  int iVar2;
  short sVar3;
  short sVar4;
  bool bVar5;
  int iVar6;
  short sVar7;
  short sVar8;
  ushort uVar9;
  short sVar10;
  int iVar11;
  short sVar12;
  uint uVar13;
  short sVar14;
  int iVar15;
  ushort local_26;
  short local_24;
  undefined1 auStack_22 [18];
  undefined1 auStack_10 [2];
  short local_e;
  short local_a;
  short local_6;
  
  iVar2 = *param_1;
  sVar12 = DAT_00021020._2_2_ - *(short *)((int)param_1 + 0x612);
  iVar6 = param_1[0x186];
  if (0xf < sVar12) {
    if ((DAT_00021058 == 0) && (sVar12 < 0x1f)) {
      if (sVar12 == 0x10) {
        FUN_0001caa4(*(undefined4 *)(iVar2 + 4),0,*(undefined4 *)(iVar2 + 0xc),3,iVar2);
      }
      iVar6 = *(int *)(iVar2 + 0x46);
      sVar14 = 0;
      do {
        iVar15 = (int)sVar14;
        iVar11 = iVar15 * 0xc;
        *(int *)(iVar6 + iVar11) =
             (*(int *)((int)param_1 + iVar15 * 4 + 0x622) >> 8) + *(int *)(iVar6 + iVar11);
        piVar1 = (int *)(iVar6 + 4 + iVar11);
        *piVar1 = (*(int *)((int)param_1 + iVar15 * 4 + 0x656) >> 8) + *piVar1;
        piVar1 = (int *)(iVar6 + 8 + iVar11);
        *piVar1 = (*(int *)((int)param_1 + iVar15 * 4 + 0x68a) >> 8) + *piVar1;
        iVar11 = *(int *)((int)param_1 + iVar15 * 4 + 0x622) +
                 *(int *)((int)param_1 + iVar15 * 4 + 0x622) * 2;
        if (iVar11 < 0) {
          iVar11 = iVar11 + 3;
        }
        *(int *)((int)param_1 + iVar15 * 4 + 0x622) = iVar11 >> 2;
        iVar15 = (int)sVar14;
        iVar11 = *(int *)((int)param_1 + iVar15 * 4 + 0x656) +
                 *(int *)((int)param_1 + iVar15 * 4 + 0x656) * 2;
        if (iVar11 < 0) {
          iVar11 = iVar11 + 3;
        }
        *(int *)((int)param_1 + iVar15 * 4 + 0x656) = iVar11 >> 2;
        *(int *)((int)param_1 + sVar14 * 4 + 0x68a) =
             (*(int *)((int)param_1 + sVar14 * 4 + 0x68a) << 1) / 3;
        sVar14 = sVar14 + 1;
      } while (sVar14 < 0xd);
      param_1[0x186] = param_1[0x186] + (int)*(short *)(param_1 + 0x187);
      iVar6 = *(short *)(param_1 + 0x187) * 0xf;
      if (iVar6 < 0) {
        iVar6 = iVar6 + 0xf;
      }
      *(short *)(param_1 + 0x187) = (short)(iVar6 >> 4);
      iVar6 = param_1[0x186];
    }
    else if ((*(byte *)(iVar2 + 0x15) & 1) != 0) {
      *(uint *)(iVar2 + 0x14) = *(uint *)(iVar2 + 0x14) & 0xfffeffff;
      FUN_00016136(iVar2);
    }
  }
  if (*(short *)((int)param_1 + 0x61e) == 0) {
    local_24 = *(short *)(param_1 + 8);
    local_26 = *(ushort *)((int)param_1 + 0x22);
    sVar14 = *(short *)(param_1 + 9);
    sVar8 = *(short *)(param_1 + 0x185);
    iVar11 = (int)(sVar8 >> 2);
    sVar7 = FUN_0000f89a(*(undefined4 *)(iVar2 + 8));
    iVar6 = (int)*(short *)((int)param_1 + 0x616) +
            (iVar11 * ((iVar11 * iVar6) / (int)sVar7) * 2) / 0xa0;
    *(short *)((int)param_1 + 0x616) = (short)(iVar6 % 10);
    local_a = 0;
    local_e = 0;
    local_6 = sVar8 - (short)(iVar6 / 10);
    FUN_00002d02((int)local_24,(int)(short)local_26,(int)sVar14,auStack_22);
    FUN_00003220(1,auStack_10,auStack_10,auStack_22);
    local_a = local_a + -0x5b;
    param_1[2] = (int)(local_e >> 6);
    param_1[3] = (int)(local_a >> 6);
    param_1[4] = (int)(local_6 >> 6);
    if (0xf < sVar12) {
      param_1[2] = param_1[2] + -0xd;
      param_1[4] = param_1[4] + -7;
    }
    iVar6 = (int)local_6 * (int)local_6 + (int)local_a * (int)local_a + (int)local_e * (int)local_e;
    sVar8 = FUN_0000f7e0(iVar6);
    FUN_00002574((int)local_e,(int)local_a,(int)local_6,(int)sVar8,iVar6,&local_24,&local_26);
    sVar12 = local_26 - *(short *)((int)param_1 + 0x22);
    if (sVar12 < 0x2001) {
      if (sVar12 < -0x2000) {
        sVar12 = sVar12 + 0x4000;
      }
    }
    else {
      sVar12 = sVar12 + -0x4000;
    }
    *(short *)(param_1 + 8) = local_24;
    *(ushort *)((int)param_1 + 0x22) = local_26 & 0x3fff;
    sVar7 = FUN_000023c4((int)local_24);
    *(ushort *)(param_1 + 9) = sVar14 + (short)((int)sVar7 * (int)sVar12 >> 0xc) & 0x3fff;
    *(short *)(param_1 + 0x185) = sVar8;
    if (param_1[3] + *(int *)(iVar2 + 8) <= *(int *)((int)param_1 + 0x2e) + 0x17f) {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
      *(undefined2 *)((int)param_1 + 0x61e) = 1;
    }
  }
  else {
    sVar12 = *(short *)(iVar2 + 0x36);
    uVar9 = FUN_0000f87c(0x588 - *(short *)((int)param_1 + 0x22));
    iVar6 = *(int *)(iVar2 + 0x46);
    sVar8 = FUN_00002426(uVar9 & 0x3fff);
    sVar7 = FUN_000023c4(uVar9 & 0x3fff);
    bVar5 = false;
    sVar14 = 0;
    uVar13 = (uint)sVar12;
    do {
      iVar11 = sVar14 * 0xc;
      if (*(int *)(iVar6 + 8 + iVar11) < 0x17f << (uVar13 & 0x3f)) {
        bVar5 = true;
        sVar3 = *(short *)(iVar6 + 2 + iVar11) -
                (short)((sVar8 * 0xf << ((int)sVar12 & 0x3fU)) >> 0xc);
        sVar4 = *(short *)(iVar6 + 6 + iVar11) -
                (short)((sVar7 * 0xf << ((int)sVar12 & 0x3fU)) >> 0xc);
        piVar1 = (int *)(iVar6 + 8 + iVar11);
        *piVar1 = (10 << (uVar13 & 0x3f)) + *piVar1;
        sVar10 = 0x15e - *(short *)(iVar6 + 10 + iVar11);
        sVar10 = FUN_0000f7e0((int)sVar4 * (int)sVar4 + (int)sVar3 * (int)sVar3 +
                              (int)sVar10 * (int)sVar10);
        if ((int)sVar10 < 0x15e << (uVar13 & 0x3f)) {
          *(int *)(iVar6 + iVar11) = (int)sVar3;
          *(int *)(iVar6 + 4 + iVar11) = (int)sVar4;
        }
      }
      sVar14 = sVar14 + 1;
    } while (sVar14 < 0xd);
    if (!bVar5) {
      FUN_000060fc(iVar2);
    }
  }
  return;
}



// FUN_0001daba @ 0x1daba

void FUN_0001daba(int *param_1)

{
  int *piVar1;
  int iVar2;
  short *psVar3;
  
  iVar2 = *param_1;
  psVar3 = (short *)((int)param_1 + 0x60e);
  piVar1 = (int *)((int)param_1 + *psVar3 * 0x14 + 0x32);
  *psVar3 = *psVar3 + 1;
  if (*(short *)((int)param_1 + 0x60e) == 0x4b) {
    *psVar3 = 0;
  }
  if (DAT_00021058 == 0) {
    FUN_0001d6b0(param_1);
    *piVar1 = param_1[2] + *(int *)(iVar2 + 4);
    piVar1[1] = param_1[3] + *(int *)(iVar2 + 8);
    piVar1[2] = param_1[4] + *(int *)(iVar2 + 0xc);
    *(undefined2 *)(piVar1 + 3) = *(undefined2 *)(param_1 + 8);
    *(undefined2 *)((int)piVar1 + 0xe) = *(undefined2 *)((int)param_1 + 0x22);
    *(undefined2 *)(piVar1 + 4) = *(undefined2 *)(param_1 + 9);
  }
  else if (*(int *)(iVar2 + 0x42) <= DAT_00021020) {
    if ((DAT_00021058 == 1) || (DAT_00021020 == *(int *)(iVar2 + 0x42))) {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
      *(int *)(iVar2 + 4) = *piVar1;
      *(int *)(iVar2 + 8) = piVar1[1];
      *(int *)(iVar2 + 0xc) = piVar1[2];
    }
    else {
      param_1[2] = *piVar1 - *(int *)(iVar2 + 4);
      param_1[3] = piVar1[1] - *(int *)(iVar2 + 8);
      param_1[4] = piVar1[2] - *(int *)(iVar2 + 0xc);
    }
    *(undefined2 *)(param_1 + 8) = *(undefined2 *)(piVar1 + 3);
    *(undefined2 *)((int)param_1 + 0x22) = *(undefined2 *)((int)piVar1 + 0xe);
    *(undefined2 *)(param_1 + 9) = *(undefined2 *)(piVar1 + 4);
  }
  return;
}



// FUN_0001db9a @ 0x1db9a

void FUN_0001db9a(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x40);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x132000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_0001dbd4 @ 0x1dbd4

undefined4 FUN_0001dbd4(undefined *param_1,int param_2)

{
  ushort uVar1;
  
  uVar1 = 0;
  do {
    if ((&PTR_DAT_0002cd90)[(short)uVar1] == (undefined *)0x0) {
      (&PTR_DAT_0002cd90)[(short)uVar1] = param_1;
      break;
    }
    uVar1 = uVar1 + 1;
  } while (uVar1 < 5);
  if (3 < uVar1) {
    *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1db7a;
  }
  return 0;
}



// FUN_0001dc22 @ 0x1dc22

int FUN_0001dc22(int param_1,int param_2,int param_3,int param_4,undefined2 param_5,
                undefined2 param_6,undefined2 param_7)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84((&PTR_DAT_0002cd90)[param_1],DAT_00023d68 + param_2,
                               DAT_00023d64 + param_3,DAT_00023d60 + param_4,0,0,0,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(undefined2 *)(iVar2 + 0x20) = param_5;
    *(undefined2 *)(iVar2 + 0x22) = param_6;
    *(undefined2 *)(iVar2 + 0x24) = param_7;
    *(int *)(iVar2 + 0x32) = param_2;
    *(int *)(iVar2 + 0x36) = param_3;
    *(int *)(iVar2 + 0x3a) = param_4;
    *(short *)(iVar2 + 0x3e) = (short)param_1;
  }
  return iVar2;
}



// FUN_0001dcae @ 0x1dcae

void FUN_0001dcae(int *param_1)

{
  int iVar1;
  
  iVar1 = *param_1;
  param_1[2] = (DAT_00023d68 + *(int *)((int)param_1 + 0x32)) - *(int *)(iVar1 + 4);
  if (*(int *)((int)param_1 + 0x36) + -10000 < DAT_00023d64) {
    param_1[3] = DAT_00023d64 - *(int *)(iVar1 + 8);
  }
  else {
    param_1[3] = 0;
  }
  param_1[4] = (DAT_00023d60 + *(int *)((int)param_1 + 0x3a)) - *(int *)(iVar1 + 0xc);
  return;
}



// FUN_0001dd02 @ 0x1dd02

void FUN_0001dd02(int param_1,int param_2)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x32);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x132000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_0001dd3c @ 0x1dd3c

void FUN_0001dd3c(int *param_1)

{
  int iVar1;
  
  iVar1 = *param_1;
  param_1[2] = DAT_00023d68 - (*(int *)(iVar1 + 4) + 0xdff);
  param_1[3] = DAT_00023d64 - (*(int *)(iVar1 + 8) + -0x133e);
  param_1[4] = DAT_00023d60 - (*(int *)(iVar1 + 0xc) + 0x161d);
  *(ushort *)((int)param_1 + 0x22) = -DAT_00023d3e & 0x3fff;
  *(ushort *)(param_1 + 8) = -DAT_00023d40 & 0x3fff;
  return;
}



// FUN_0001dda6 @ 0x1dda6

void FUN_0001dda6(int param_1,int param_2,int param_3,uint param_4)

{
  int *in_A1;
  
  *in_A1 = param_1 >> (param_4 & 0x3f);
  in_A1[1] = param_2 >> (param_4 & 0x3f);
  in_A1[2] = param_3 >> (param_4 & 0x3f);
  return;
}



// FUN_0001ddde @ 0x1ddde

/* WARNING: Restarted to delay deadcode elimination for space: stack */

undefined4 FUN_0001ddde(int param_1,int *param_2,int *param_3)

{
  undefined4 *puVar1;
  int iVar2;
  undefined4 uVar3;
  undefined4 uVar4;
  int iVar5;
  short *psVar6;
  short sVar7;
  int iVar8;
  int iVar9;
  int iVar10;
  int iVar11;
  int iVar12;
  int iVar13;
  int iVar14;
  int iVar15;
  int iVar16;
  int iVar17;
  int iVar18;
  int iVar19;
  undefined4 uVar20;
  bool bVar21;
  int local_68;
  int local_64;
  int *local_60;
  short local_5c;
  short local_5a;
  int local_58;
  int local_54;
  int local_50;
  int local_4c;
  int local_48;
  int local_44;
  int local_34;
  int local_30;
  int local_2c;
  int local_28;
  int local_24;
  int local_20;
  int local_1c;
  int local_18;
  int local_14;
  int local_10;
  int local_c;
  int local_8;
  
  local_68 = 0x7fffffff;
  local_64 = 0x7fffffff;
  uVar20 = *(undefined4 *)(param_1 + 4);
  uVar3 = *(undefined4 *)(param_1 + 8);
  uVar4 = *(undefined4 *)(param_1 + 0xc);
  local_60 = &DAT_00020804 + DAT_00020b24;
  local_5c = DAT_00020b24._2_2_;
  local_5a = 0;
  do {
    local_5c = local_5c + -1;
    if (local_5c != -1) {
      do {
        local_60 = local_60 + -1;
        iVar2 = *local_60;
        if (((*(char *)(iVar2 + 0x28) == '\x02') && (iVar12 = FUN_0000042c(iVar2), iVar12 == 0x200))
           && (iVar12 = FUN_000048a6(uVar20,uVar3,uVar4,*(undefined4 *)(iVar2 + 4),
                                     *(undefined4 *)(iVar2 + 8),*(undefined4 *)(iVar2 + 0xc)),
              iVar12 < local_64)) {
          iVar5 = *(int *)(iVar2 + 0x4a);
          sVar7 = *(short *)(iVar2 + 0x3c);
          while (sVar7 = sVar7 + -1, sVar7 != -1) {
            if ((*(char *)(iVar5 + 0x16) == 'p') && (*(short *)(iVar5 + 0x14) == 4)) {
              iVar15 = *(int *)(iVar2 + 0x46);
              psVar6 = *(short **)(iVar5 + 0x18);
              puVar1 = (undefined4 *)(iVar15 + *psVar6 * 0xc);
              FUN_0001dda6(*puVar1,puVar1[1],puVar1[2],(int)*(short *)(iVar2 + 0x36));
              puVar1 = (undefined4 *)(iVar15 + psVar6[1] * 0xc);
              FUN_0001dda6(*puVar1,puVar1[1],puVar1[2],(int)*(short *)(iVar2 + 0x36));
              puVar1 = (undefined4 *)(iVar15 + psVar6[2] * 0xc);
              FUN_0001dda6(*puVar1,puVar1[1],puVar1[2],(int)*(short *)(iVar2 + 0x36));
              puVar1 = (undefined4 *)(iVar15 + psVar6[3] * 0xc);
              FUN_0001dda6(*puVar1,puVar1[1],puVar1[2],(int)*(short *)(iVar2 + 0x36));
              iVar13 = FUN_00004886(local_10,0,local_8,local_1c,0,local_14);
              iVar14 = FUN_00004886(local_1c,0,local_14,local_28,0,local_20);
              iVar15 = local_34;
              iVar16 = local_30;
              iVar17 = local_2c;
              iVar18 = local_28;
              iVar19 = local_24;
              iVar8 = local_20;
              iVar9 = local_1c;
              iVar10 = local_18;
              iVar11 = local_14;
              if (iVar14 < iVar13) {
                iVar15 = local_10;
                iVar16 = local_c;
                iVar17 = local_8;
                iVar18 = local_34;
                iVar19 = local_30;
                iVar8 = local_2c;
                iVar9 = local_28;
                iVar10 = local_24;
                iVar11 = local_20;
                local_10 = local_1c;
                local_c = local_18;
                local_8 = local_14;
              }
              local_14 = iVar11;
              local_18 = iVar10;
              local_1c = iVar9;
              local_20 = iVar8;
              local_24 = iVar19;
              local_28 = iVar18;
              local_2c = iVar17;
              local_30 = iVar16;
              local_34 = iVar15;
              local_4c = local_1c + local_10;
              if (local_4c < 0) {
                local_4c = local_4c + 1;
              }
              local_4c = local_4c >> 1;
              local_48 = local_18 + local_c;
              if (local_48 < 0) {
                local_48 = local_48 + 1;
              }
              local_48 = local_48 >> 1;
              local_44 = local_14 + local_8;
              if (local_44 < 0) {
                local_44 = local_44 + 1;
              }
              local_44 = local_44 >> 1;
              iVar15 = local_34 + local_28;
              if (iVar15 < 0) {
                iVar15 = iVar15 + 1;
              }
              iVar15 = iVar15 >> 1;
              iVar16 = local_30 + local_24;
              if (iVar16 < 0) {
                iVar16 = iVar16 + 1;
              }
              iVar16 = iVar16 >> 1;
              iVar17 = local_2c + local_20;
              if (iVar17 < 0) {
                iVar17 = iVar17 + 1;
              }
              iVar17 = iVar17 >> 1;
              iVar18 = FUN_00004886(uVar20,uVar3,uVar4,local_4c,local_48,local_44);
              iVar19 = FUN_00004886(uVar20,uVar3,uVar4,iVar15,iVar16,iVar17);
              local_58 = iVar15;
              local_54 = iVar16;
              local_50 = iVar17;
              if (iVar19 < iVar18) {
                iVar18 = iVar19;
                local_58 = local_4c;
                local_54 = local_48;
                local_50 = local_44;
                local_4c = iVar15;
                local_48 = iVar16;
                local_44 = iVar17;
              }
              local_64 = iVar12;
              if (iVar18 < local_68) {
                *param_2 = local_4c;
                param_2[1] = local_48;
                param_2[2] = local_44;
                *param_3 = local_58;
                param_3[1] = local_54;
                param_3[2] = local_50;
                local_68 = iVar18;
              }
            }
            iVar5 = iVar5 + 0x20;
          }
        }
        bVar21 = local_5c != 0;
        local_5c = local_5c + -1;
      } while (bVar21);
    }
    local_60 = &DAT_00020b44 + DAT_00020f2c;
    local_5c = DAT_00020f2c._2_2_;
    local_5a = local_5a + 1;
  } while (local_5a < 2);
  if ((local_68 == 0x7fffffff) || (0x40740 < local_68)) {
    uVar20 = 1;
  }
  else {
    uVar20 = 0;
  }
  return uVar20;
}



// FUN_0001e152 @ 0x1e152

undefined4 FUN_0001e152(int *param_1)

{
  int iVar1;
  int iVar2;
  short sVar3;
  int local_1c;
  int local_18;
  int local_14;
  int local_10;
  int local_c;
  int local_8;
  
  iVar1 = *param_1;
  iVar2 = FUN_0001ddde(iVar1,&local_10,&local_1c);
  if (((iVar2 != 0) ||
      (iVar2 = FUN_000048a6(*(undefined4 *)(iVar1 + 4),*(undefined4 *)(iVar1 + 8),
                            *(undefined4 *)(iVar1 + 0xc),local_10,local_c,local_8), iVar2 < 10000))
     || (0x26ac0 < iVar2)) {
    return 1;
  }
  iVar2 = (local_1c - local_10) * 6;
  if (iVar2 < 0) {
    iVar2 = iVar2 + 0x3f;
  }
  DAT_0002cdc8 = local_10 + (iVar2 >> 6);
  DAT_0002cdcc = local_c;
  iVar2 = (local_14 - local_8) * 6;
  if (iVar2 < 0) {
    iVar2 = iVar2 + 0x3f;
  }
  DAT_0002cdd0 = local_8 + (iVar2 >> 6);
  DAT_0002cdbe = (short)(local_1c - local_10 >> 3);
  DAT_0002cdc2 = (short)(local_18 - local_c >> 3);
  DAT_0002cdc6 = (short)(local_14 - local_8 >> 3);
  sVar3 = FUN_0000f7e0((int)DAT_0002cdc2 * (int)DAT_0002cdc2 + (int)DAT_0002cdbe * (int)DAT_0002cdbe
                       + (int)DAT_0002cdc6 * (int)DAT_0002cdc6);
  DAT_0002cdbe = (short)(((int)DAT_0002cdbe << 0xc) / (int)sVar3);
  DAT_0002cdc2 = 0xfdc0;
  DAT_0002cdc6 = (short)(((int)DAT_0002cdc6 << 0xc) / (int)sVar3);
  iVar2 = DAT_0002cdbe * 10000;
  if (iVar2 < 0) {
    iVar2 = iVar2 + 0xfff;
  }
  DAT_0002cdc8 = DAT_0002cdc8 + (iVar2 >> 0xc);
  DAT_0002cdcc = DAT_0002cdcc + 0x43;
  iVar2 = DAT_0002cdc6 * 10000;
  if (iVar2 < 0) {
    iVar2 = iVar2 + 0xfff;
  }
  DAT_0002cdd0 = DAT_0002cdd0 + (iVar2 >> 0xc);
  DAT_0002cdb8 = 0;
  if ('\0' < *(char *)(param_1 + 0x1a3)) {
    *(char *)(param_1 + 0x1a3) = -*(char *)(param_1 + 0x1a3);
  }
  *(ushort *)(param_1 + 0x1a6) = *(ushort *)(param_1 + 0x1a6) & 0xbfff;
  *(undefined4 *)((int)param_1 + 0x622) = 0;
  *(undefined2 *)(param_1 + 0x188) = 0;
  DAT_0002cdb4 = 0;
  PTR_DAT_0002cdb0 = (undefined *)0x0;
  DAT_0002cdac = *(undefined4 *)(iVar1 + 4);
  DAT_0002cda8 = *(undefined4 *)(iVar1 + 0xc);
  if (DAT_0002cdcc < 0x44) {
    DAT_0002cdba = 0x11b;
  }
  else {
    DAT_0002cdba = 0x181;
  }
  DAT_0002cda4 = *(undefined4 *)((int)param_1 + 0x32);
  *(undefined4 *)((int)param_1 + 0x32) = 0x1e31e;
  return 0;
}



// FUN_0001e33e @ 0x1e33e

/* WARNING: Globals starting with '_' overlap smaller symbols at the same address */

void FUN_0001e33e(int *param_1)

{
  undefined4 uVar1;
  undefined1 uVar2;
  int *piVar3;
  int iVar4;
  int iVar5;
  int iVar6;
  short sVar11;
  short sVar12;
  undefined *puVar7;
  int iVar8;
  int iVar9;
  int iVar10;
  uint uVar13;
  int iVar14;
  short local_12;
  short local_6;
  
  iVar9 = DAT_0002cdd0;
  iVar14 = DAT_0002cdcc;
  iVar8 = DAT_0002cdc8;
  uVar1 = *(undefined4 *)((int)param_1 + 0x2e);
  uVar2 = *(undefined1 *)((int)param_1 + 0x68a);
  iVar10 = *param_1;
  iVar4 = FUN_000048a6(*(undefined4 *)(iVar10 + 4),*(undefined4 *)(iVar10 + 8),
                       *(undefined4 *)(iVar10 + 0xc),DAT_0002cdc8,DAT_0002cdcc,DAT_0002cdd0);
  *(undefined4 *)((int)param_1 + 0x2e) = 0xfffff060;
  if (iVar4 < 0xae31) {
    if (iVar4 < 0x8e31) {
      iVar6 = iVar4 + -10000;
    }
    else {
      iVar6 = (iVar4 + -0x8e30) * 3000;
      if (iVar6 < 0) {
        iVar6 = iVar6 + 0x1fff;
      }
      iVar6 = (iVar4 + -10000) - (iVar6 >> 0xd);
    }
  }
  else {
    iVar6 = iVar4 + -13000;
  }
  iVar5 = iVar6 * DAT_0002cdbe;
  if (iVar5 < 0) {
    iVar5 = iVar5 + 0xfff;
  }
  iVar8 = iVar8 - (iVar5 >> 0xc);
  iVar5 = iVar6 * DAT_0002cdc2;
  if (iVar5 < 0) {
    iVar5 = iVar5 + 0xfff;
  }
  iVar14 = iVar14 - (iVar5 >> 0xc);
  if (DAT_0002cdcc + DAT_0002cdba < iVar14) {
    if (iVar6 < 0x9c41) {
      if (iVar6 < 0x8c41) goto LAB_0001e472;
      iVar5 = (iVar6 + -0x8c40) * (*(int *)(iVar10 + 8) - iVar14);
      if (iVar5 < 0) {
        iVar5 = iVar5 + 0xfff;
      }
      iVar5 = iVar5 >> 0xc;
    }
    else {
      iVar5 = *(int *)(iVar10 + 8) - iVar14;
    }
    if (iVar5 < 0) {
      iVar5 = iVar5 + 7;
    }
    iVar14 = (iVar5 >> 3) + iVar14;
  }
  else {
    iVar14 = DAT_0002cdcc + DAT_0002cdba;
  }
LAB_0001e472:
  iVar5 = iVar6 * DAT_0002cdc6;
  if (iVar5 < 0) {
    iVar5 = iVar5 + 0xfff;
  }
  iVar9 = iVar9 - (iVar5 >> 0xc);
  if (iVar6 < 0x9ab1) {
    if (iVar6 < 4000) {
      local_12 = 0;
    }
    else {
      local_12 = 0x7bc;
    }
  }
  else {
    local_12 = 0x9d8;
  }
  iVar6 = FUN_000038e8(*(int *)(iVar10 + 4) - iVar8);
  iVar5 = FUN_000038e8(*(int *)(iVar10 + 0xc) - iVar9);
  sVar11 = 0;
  for (uVar13 = iVar5 + iVar6; (uVar13 & 0xffff8000) != 0; uVar13 = (int)uVar13 >> 1) {
    sVar11 = sVar11 + 1;
  }
  FUN_00002574(iVar8 - *(int *)(iVar10 + 4) >> ((int)(short)(sVar11 + 1) & 0x3fU),0,
               iVar9 - *(int *)(iVar10 + 0xc) >> ((int)(short)(sVar11 + 1) & 0x3fU),0,0,0,&local_6);
  iVar6 = (*(int *)(iVar10 + 4) - DAT_0002cdc8) * (int)DAT_0002cdc6 -
          (*(int *)(iVar10 + 0xc) - DAT_0002cdd0) * (int)DAT_0002cdbe;
  if (iVar6 < 0) {
    iVar6 = iVar6 + 0xfff;
  }
  sVar11 = FUN_00003908((int)(short)(iVar6 >> 0xc));
  sVar12 = FUN_000023c4((int)local_6);
  iVar6 = (int)sVar12 * (int)sVar11;
  if (iVar6 < 0) {
    iVar6 = iVar6 + 0xfff;
  }
  iVar8 = iVar8 - (iVar6 >> 0xc);
  sVar12 = FUN_00002426((int)local_6);
  iVar6 = (int)sVar12 * (int)sVar11;
  if (iVar6 < 0) {
    iVar6 = iVar6 + 0xfff;
  }
  iVar9 = (iVar6 >> 0xc) + iVar9;
  if (*(char *)(param_1 + 0x193) != '\0') {
    *(undefined4 *)((int)param_1 + 0x32) = DAT_0002cda4;
  }
  if (DAT_0002cdb8 != 0) {
    iVar8 = (int)DAT_0002cdb8 * (iVar8 - DAT_0002cdc8) +
            (2 - DAT_0002cdb8) * ((*(int *)(iVar10 + 4) + param_1[2] * 0x2d) - DAT_0002cdc8);
    if (iVar8 < 0) {
      iVar8 = iVar8 + 1;
    }
    iVar14 = iVar14 * DAT_0002cdb8 + (*(int *)(iVar10 + 8) + param_1[3] * 0x2d) * (2 - DAT_0002cdb8)
    ;
    if (iVar14 < 0) {
      iVar14 = iVar14 + 1;
    }
    iVar14 = iVar14 >> 1;
    iVar9 = (int)DAT_0002cdb8 * (iVar9 - DAT_0002cdd0) +
            (2 - DAT_0002cdb8) * ((*(int *)(iVar10 + 0xc) + param_1[4] * 0x2d) - DAT_0002cdd0);
    if (iVar9 < 0) {
      iVar9 = iVar9 + 1;
    }
    iVar8 = DAT_0002cdc8 + (iVar8 >> 1);
    iVar9 = DAT_0002cdd0 + (iVar9 >> 1);
    if (DAT_0002cdb8 == 1) {
      DAT_0002cda8 = iVar9;
      DAT_0002cdac = iVar8;
    }
    DAT_0002cdb8 = DAT_0002cdb8 + 1;
    if (DAT_0002cdb8 == 2) {
      DAT_0002cdb8 = 0;
    }
  }
  iVar6 = iVar14 - *(int *)(iVar10 + 8);
  iVar10 = *(int *)((int)param_1 + 0x622);
  *(int *)((int)param_1 + 0x622) = iVar6;
  piVar3 = DAT_00027078;
  puVar7 = PTR_DAT_0002cdb0 + iVar6 * 0x21 + iVar10 * -0x20;
  PTR_DAT_0002cdb0 = (undefined *)((int)puVar7 % 0x10);
  if ((int)puVar7 < 0) {
    puVar7 = puVar7 + 0xf;
  }
  DAT_0002cdb4 = ((int)puVar7 >> 4) + DAT_0002cdb4;
  if (DAT_0002cdb4 < 0x4b1) {
    if (DAT_0002cdb4 < -0x4b0) {
      DAT_0002cdb4 = -0x4b0;
    }
  }
  else {
    DAT_0002cdb4 = 0x4b0;
  }
  iVar14 = DAT_0002cdb4 + iVar14;
  iVar8 = iVar8 - DAT_0002cdac;
  if (iVar8 < 0) {
    iVar8 = iVar8 + 7;
  }
  iVar10 = DAT_0002cdac + (iVar8 >> 3);
  iVar9 = iVar9 - DAT_0002cda8;
  if (iVar9 < 0) {
    iVar9 = iVar9 + 7;
  }
  iVar8 = DAT_0002cda8 + (iVar9 >> 3);
  DAT_0002cda8 = iVar8;
  DAT_0002cdac = iVar10;
  if (DAT_00027078 != (int *)0x0) {
    *(int *)(*DAT_00027078 + 4) = iVar10;
    *(int *)(*piVar3 + 8) = iVar14;
    *(int *)(*piVar3 + 0xc) = iVar8;
  }
  FUN_00012aba(param_1,(int)local_12);
  FUN_000129b4(param_1,iVar10,iVar14,iVar8);
  if (((iVar4 < 18000) && (15000 < iVar4)) &&
     ((iVar10 = FUN_000038e8(iVar6), 100 < iVar10 ||
      (iVar10 = FUN_000038e8((int)local_12 - (*(int *)((int)param_1 + 0x62a) >> 2)), 0x5a < iVar10))
     )) {
    _DAT_00021024 = 1;
    *(undefined4 *)((int)param_1 + 0x32) = DAT_0002cda4;
  }
  *(undefined4 *)((int)param_1 + 0x2e) = uVar1;
  *(undefined1 *)((int)param_1 + 0x68a) = uVar2;
  FUN_0000fd16(param_1);
  return;
}



// FUN_0001e7d6 @ 0x1e7d6

int * FUN_0001e7d6(int param_1,int param_2,int param_3)

{
  int *piVar1;
  int iVar2;
  short unaff_D3w;
  undefined1 auStack_e [4];
  undefined4 *local_a;
  undefined2 local_6;
  
  piVar1 = (int *)FUN_000021a4(0x61e);
  *piVar1 = param_1;
  *(short *)(piVar1 + 0x184) = (short)param_3;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x102000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  if ((param_3 == 99) && (*(int *)((int)piVar1 + 0x612) == 0)) {
    iVar2 = FUN_0001a956(*piVar1,8,&local_6);
    *(int *)((int)piVar1 + 0x612) = iVar2;
    if (iVar2 == 0) {
      *(undefined2 *)(piVar1 + 0x184) = 0;
    }
    else {
      iVar2 = *(int *)((int)piVar1 + 0x612);
      *(undefined2 *)(iVar2 + 4) = local_6;
      FUN_0001e8c0(param_1,iVar2 + 4,&local_a,auStack_e);
      unaff_D3w = FUN_00018732(param_1,*local_a,local_a[1],local_a[2]);
    }
  }
  FUN_0000f346((int)piVar1 + 0x32,*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
               *(undefined4 *)(param_1 + 0xc));
  FUN_0000f380((int)piVar1 + 0x32,0,(int)unaff_D3w,0);
  return piVar1;
}



// FUN_0001e8a8 @ 0x1e8a8

void FUN_0001e8a8(int param_1)

{
  if (*(int *)(param_1 + 0x612) != 0) {
    FUN_00002008(*(undefined4 *)(param_1 + 0x612));
  }
  return;
}



// FUN_0001e8c0 @ 0x1e8c0

undefined4 FUN_0001e8c0(int param_1,short *param_2,undefined4 *param_3,int *param_4)

{
  short *psVar1;
  int iVar2;
  undefined4 uVar3;
  
  psVar1 = param_2 + *param_2 * 6 + 2;
  iVar2 = FUN_000048a6(*(undefined4 *)(param_1 + 4),0,*(undefined4 *)(param_1 + 0xc),
                       *(undefined4 *)psVar1,0,*(undefined4 *)(psVar1 + 4));
  *param_4 = iVar2;
  if (iVar2 < 0xaa) {
    *param_2 = *param_2 + 1;
    if (param_2[1] <= *param_2) {
      *param_2 = 0;
    }
    *param_3 = param_2 + *param_2 * 6 + 2;
    uVar3 = 1;
  }
  else {
    *param_3 = psVar1;
    uVar3 = 0;
  }
  return uVar3;
}



// FUN_0001e932 @ 0x1e932

void FUN_0001e932(undefined4 *param_1)

{
  undefined4 uVar1;
  short *psVar2;
  ushort uVar3;
  short sVar5;
  int iVar4;
  int local_c;
  undefined4 *local_8;
  
  uVar1 = *param_1;
  psVar2 = *(short **)((int)param_1 + 0x612);
  if (*psVar2 == 0) {
    *psVar2 = 1;
    *(undefined4 *)((int)param_1 + 0x616) = 0x168;
    sVar5 = FUN_000023c4((int)*(short *)((int)param_1 + 0x22));
    param_1[2] = (int)sVar5 * -*(int *)((int)param_1 + 0x616) >> 0x10;
    sVar5 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
    param_1[4] = *(int *)((int)param_1 + 0x616) * (int)sVar5 >> 0x10;
    FUN_0001e8c0(uVar1,psVar2 + 2,&local_8,&local_c);
  }
  else {
    iVar4 = FUN_0001e8c0(uVar1,psVar2 + 2,&local_8,&local_c);
    if ((iVar4 != 0) || (sVar5 = *psVar2, *psVar2 = sVar5 + -1, (short)(sVar5 + -1) == 0)) {
      sVar5 = FUN_00018732(uVar1,*local_8,local_8[1],local_8[2]);
      sVar5 = FUN_0000f87c((int)sVar5 - (int)*(short *)((int)param_1 + 0x22));
      iVar4 = FUN_00003908((int)sVar5);
      if (iVar4 < 0x101) {
        *psVar2 = 0xf;
      }
      else {
        if (sVar5 < 0) {
          sVar5 = -0x100;
        }
        else {
          sVar5 = 0x100;
        }
        *psVar2 = 1;
      }
      uVar3 = *(short *)((int)param_1 + 0x22) + sVar5 & 0x3fff;
      *(ushort *)((int)param_1 + 0x22) = uVar3;
      sVar5 = FUN_000023c4(uVar3);
      param_1[2] = (int)sVar5 * -*(int *)((int)param_1 + 0x616) >> 0x10;
      sVar5 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
      param_1[4] = *(int *)((int)param_1 + 0x616) * (int)sVar5 >> 0x10;
    }
    if (local_c < 0x4b1) {
      if (*(int *)((int)param_1 + 0x616) < 0x169) {
        return;
      }
      iVar4 = -9;
    }
    else {
      if (0x437 < *(int *)((int)param_1 + 0x616)) {
        return;
      }
      iVar4 = 9;
    }
    *(int *)((int)param_1 + 0x616) = iVar4 + *(int *)((int)param_1 + 0x616);
    sVar5 = FUN_000023c4((int)*(short *)((int)param_1 + 0x22));
    param_1[2] = (int)sVar5 * -*(int *)((int)param_1 + 0x616) >> 0x10;
    sVar5 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
    param_1[4] = *(int *)((int)param_1 + 0x616) * (int)sVar5 >> 0x10;
  }
  return;
}



// FUN_0001eace @ 0x1eace

void FUN_0001eace(int param_1)

{
  if (*(short *)(param_1 + 0x610) == 99) {
    FUN_0001e932(param_1);
  }
  return;
}



// FUN_0001eae4 @ 0x1eae4

void FUN_0001eae4(int *param_1)

{
  int *piVar1;
  int iVar2;
  short *psVar3;
  
  iVar2 = *param_1;
  psVar3 = (short *)((int)param_1 + 0x60e);
  piVar1 = (int *)((int)param_1 + *psVar3 * 0x14 + 0x32);
  *psVar3 = *psVar3 + 1;
  if (*(short *)((int)param_1 + 0x60e) == 0x4b) {
    *psVar3 = 0;
  }
  if (DAT_00021058 == 0) {
    FUN_0001eace(param_1);
    *piVar1 = param_1[2] + *(int *)(iVar2 + 4);
    piVar1[1] = param_1[3] + *(int *)(iVar2 + 8);
    piVar1[2] = param_1[4] + *(int *)(iVar2 + 0xc);
    *(undefined2 *)(piVar1 + 3) = *(undefined2 *)(param_1 + 8);
    *(undefined2 *)((int)piVar1 + 0xe) = *(undefined2 *)((int)param_1 + 0x22);
    *(undefined2 *)(piVar1 + 4) = *(undefined2 *)(param_1 + 9);
    *(undefined2 *)((int)piVar1 + 0x12) = *(undefined2 *)((int)param_1 + 0x61a);
  }
  else if (*(int *)(iVar2 + 0x42) <= DAT_00021020) {
    if ((DAT_00021058 == 1) || (DAT_00021020 == *(int *)(iVar2 + 0x42))) {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
      *(int *)(iVar2 + 4) = *piVar1;
      *(int *)(iVar2 + 8) = piVar1[1];
      *(int *)(iVar2 + 0xc) = piVar1[2];
    }
    else {
      param_1[2] = *piVar1 - *(int *)(iVar2 + 4);
      param_1[3] = piVar1[1] - *(int *)(iVar2 + 8);
      param_1[4] = piVar1[2] - *(int *)(iVar2 + 0xc);
    }
    *(undefined2 *)((int)param_1 + 0x61a) = *(undefined2 *)((int)piVar1 + 0x12);
    *(undefined2 *)(param_1 + 8) = *(undefined2 *)(piVar1 + 3);
    *(undefined2 *)((int)param_1 + 0x22) = *(undefined2 *)((int)piVar1 + 0xe);
    *(undefined2 *)(param_1 + 9) = *(undefined2 *)(piVar1 + 4);
    *(undefined2 *)(param_1 + 0x187) = *(undefined2 *)((int)param_1 + 0x61a);
  }
  return;
}



// FUN_0001ebd6 @ 0x1ebd6

void FUN_0001ebd6(int param_1)

{
  int *piVar1;
  
  piVar1 = (int *)FUN_000021a4(0x32);
  *piVar1 = param_1;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x112000;
  piVar1[1] = DAT_00020788;
  return;
}



// FUN_0001ec00 @ 0x1ec00

undefined4 FUN_0001ec00(undefined4 param_1,int param_2)

{
  short sVar1;
  
  sVar1 = 0;
  do {
    if ((&DAT_0002cdd4)[sVar1] == 0) {
      (&DAT_0002cdd4)[sVar1] = param_1;
      break;
    }
    sVar1 = sVar1 + 1;
  } while (sVar1 < 9);
  if (sVar1 == 8) {
    *(undefined4 *)(*(int *)(&DAT_0002074c + param_2 * 4) + 6) = 0x1ebb6;
  }
  return 0;
}



// FUN_0001ec4e @ 0x1ec4e

undefined4
FUN_0001ec4e(undefined4 param_1,undefined4 param_2,undefined4 param_3,undefined4 param_4,
            undefined4 param_5,undefined4 param_6,int param_7)

{
  undefined4 *puVar1;
  undefined4 uVar2;
  
  puVar1 = (undefined4 *)
           FUN_00005f84((&DAT_0002cdd4)[param_7],param_1,param_2,param_3,param_4,param_5,param_6,0);
  if (puVar1 == (undefined4 *)0x0) {
    uVar2 = 0;
  }
  else {
    puVar1[6] = 1000000000;
    uVar2 = *puVar1;
  }
  return uVar2;
}



// FUN_0001ec9e @ 0x1ec9e

int * FUN_0001ec9e(int param_1,int param_2,int param_3)

{
  int *piVar1;
  int iVar2;
  undefined2 uVar3;
  undefined1 auStack_e [4];
  undefined4 *local_a;
  undefined2 local_6;
  
  piVar1 = (int *)FUN_000021a4(0x634);
  *piVar1 = param_1;
  *(short *)(piVar1 + 0x184) = (short)param_3;
  *(uint *)(param_1 + 0x14) = *(uint *)(param_1 + 0x14) | 0x102000;
  piVar1[1] = *(int *)(&DAT_0002074c + param_2 * 4);
  *(undefined2 *)((int)piVar1 + 0x62a) = 0xffff;
  *(undefined4 *)((int)piVar1 + 0x616) = 0;
  if ((param_3 == 99) && (*(int *)((int)piVar1 + 0x612) == 0)) {
    iVar2 = FUN_0001a956(*piVar1,8,&local_6);
    *(int *)((int)piVar1 + 0x612) = iVar2;
    if (iVar2 == 0) {
      *(undefined2 *)(piVar1 + 0x184) = 0;
    }
    else {
      *(undefined2 *)(*(int *)((int)piVar1 + 0x612) + 4) = local_6;
      FUN_0001ef0e(piVar1,&local_a,auStack_e);
      uVar3 = FUN_00018732(param_1,*local_a,local_a[1],local_a[2]);
      *(undefined2 *)((int)piVar1 + 0x22) = uVar3;
    }
  }
  FUN_0000f346((int)piVar1 + 0x32,*(undefined4 *)(param_1 + 4),*(undefined4 *)(param_1 + 8),
               *(undefined4 *)(param_1 + 0xc));
  FUN_0000f380((int)piVar1 + 0x32,0,(int)*(short *)((int)piVar1 + 0x22),0);
  return piVar1;
}



// FUN_0001ed7c @ 0x1ed7c

undefined4 FUN_0001ed7c(undefined4 param_1)

{
  ushort uVar1;
  
  uVar1 = 0;
  do {
    if ((&DAT_0002ce04)[(short)uVar1] == 0) {
      (&DAT_0002ce04)[(short)uVar1] = param_1;
      break;
    }
    uVar1 = uVar1 + 1;
  } while (uVar1 < 2);
  if (uVar1 != 0) {
    *(undefined4 *)(DAT_000207e8 + 6) = 0x1ec7e;
    *(undefined4 *)(DAT_000207ec + 6) = 0x1ec7e;
  }
  return 0;
}



// FUN_0001edc0 @ 0x1edc0

int FUN_0001edc0(int param_1,undefined2 param_2,undefined4 param_3,undefined4 param_4,
                undefined4 param_5,undefined4 param_6,undefined4 param_7,undefined4 param_8)

{
  int *piVar1;
  int iVar2;
  
  piVar1 = (int *)FUN_00005f84(*(undefined4 *)(&DAT_0002cd68 + param_1 * 4),param_3,param_4,param_5,
                               param_6,param_7,param_8,0);
  if (piVar1 == (int *)0x0) {
    iVar2 = 0;
  }
  else {
    iVar2 = *piVar1;
    *(undefined2 *)(iVar2 + 0x62a) = param_2;
  }
  return iVar2;
}



// FUN_0001ee0e @ 0x1ee0e

void FUN_0001ee0e(int param_1)

{
  char *pcVar1;
  
  if (*(int *)(param_1 + 0x612) != 0) {
    FUN_00002008(*(undefined4 *)(param_1 + 0x612));
  }
  if (*(int *)(param_1 + 0x61a) != 0) {
    pcVar1 = (char *)(*(int *)(param_1 + 0x61a) + 0x29);
    *pcVar1 = *pcVar1 + -1;
    *(undefined4 *)(param_1 + 0x61a) = 0;
  }
  return;
}



// FUN_0001ee3e @ 0x1ee3e

uint FUN_0001ee3e(int *param_1,int *param_2)

{
  int iVar1;
  ushort uVar2;
  char *pcVar3;
  uint uVar4;
  uint uVar5;
  uint uVar6;
  int iVar7;
  
  iVar1 = *param_1;
  uVar2 = **(ushort **)(*param_2 + 4);
  uVar6 = (uint)uVar2;
  if (((uVar2 == 0x27) || (uVar2 == 0x28)) && (**(ushort **)(iVar1 + 4) != uVar2)) {
    uVar4 = FUN_00013672();
    uVar6 = uVar4 & 0xffff0003;
    if (((uVar4 & 3) == 0) &&
       (uVar4 = FUN_000048a6(param_1[1],param_1[2],param_1[3],param_2[1],param_2[2],param_2[3]),
       uVar6 = uVar4, (int)uVar4 < 0x26ac1)) {
      if (uVar4 == 0) {
        iVar7 = 0x7fffffff;
      }
      else {
        iVar7 = 0x672000 / (int)uVar4;
      }
      if (*(int *)(iVar1 + 0x61a) != 0) {
        iVar7 = iVar7 >> 1;
      }
      uVar5 = FUN_00013672();
      uVar6 = uVar5 & 0xffff007f;
      if ((int)(uVar5 & 0x7f) < iVar7) {
        if (*(int *)(iVar1 + 0x61a) == 0) {
          *(int **)(iVar1 + 0x61a) = param_2;
          *(uint *)(iVar1 + 0x61e) = uVar4;
        }
        else {
          uVar6 = uVar4 * 2;
          if (*(int *)(iVar1 + 0x61e) <= (int)uVar6) {
            return uVar6;
          }
          *(uint *)(iVar1 + 0x61e) = uVar4;
          pcVar3 = (char *)(*(int *)(iVar1 + 0x61a) + 0x29);
          *pcVar3 = *pcVar3 + -1;
          *(int **)(iVar1 + 0x61a) = param_2;
        }
        *(char *)((int)param_2 + 0x29) = *(char *)((int)param_2 + 0x29) + '\x01';
      }
    }
  }
  return uVar6;
}



// FUN_0001ef0e @ 0x1ef0e

undefined4 FUN_0001ef0e(int *param_1,int *param_2,int *param_3)

{
  undefined4 *puVar1;
  int iVar2;
  int iVar3;
  short *psVar4;
  
  iVar3 = *param_1;
  iVar2 = *(int *)((int)param_1 + 0x612);
  if (((*(byte *)((int)param_1 + 0x62d) & 2) == 0) && (iVar2 != 0)) {
    psVar4 = (short *)(iVar2 + 4);
    puVar1 = (undefined4 *)(iVar2 + 8 + *psVar4 * 0xc);
    iVar3 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc),*puVar1,0,
                         puVar1[2]);
    *param_3 = iVar3;
    if (iVar3 < 0xaa) {
      *psVar4 = *psVar4 + 1;
      if (*(short *)(iVar2 + 6) <= *psVar4) {
        *psVar4 = 0;
      }
      *param_2 = iVar2 + 8 + *psVar4 * 0xc;
      return 1;
    }
    *param_2 = (int)puVar1;
  }
  else {
    DAT_0002cdf8 = *(undefined4 *)((int)param_1 + 0x622);
    DAT_0002cdfc = 0;
    DAT_0002ce00 = *(undefined4 *)((int)param_1 + 0x626);
    *param_2 = (int)&DAT_0002cdf8;
    iVar3 = FUN_000048a6(*(undefined4 *)(iVar3 + 4),0,*(undefined4 *)(iVar3 + 0xc),
                         *(undefined4 *)((int)param_1 + 0x622),0,
                         *(undefined4 *)((int)param_1 + 0x626));
    *param_3 = iVar3;
    if (iVar3 < 0xaa) {
      return 1;
    }
  }
  return 0;
}



// FUN_0001efe0 @ 0x1efe0

void FUN_0001efe0(int param_1)

{
  short sVar1;
  
  if (*(int *)(param_1 + 0x616) < 0) {
    *(undefined4 *)(param_1 + 0x616) = 0;
  }
  sVar1 = FUN_000023c4((int)*(short *)(param_1 + 0x22));
  *(int *)(param_1 + 8) = (int)sVar1 * -*(int *)(param_1 + 0x616) >> 0x10;
  sVar1 = FUN_00002426((int)*(short *)(param_1 + 0x22));
  *(int *)(param_1 + 0x10) = *(int *)(param_1 + 0x616) * (int)sVar1 >> 0x10;
  return;
}



// FUN_0001f034 @ 0x1f034

int FUN_0001f034(undefined4 *param_1)

{
  undefined4 uVar1;
  short *psVar2;
  short sVar4;
  short sVar5;
  int iVar3;
  undefined1 auStack_c [4];
  undefined4 *local_8;
  
  uVar1 = *param_1;
  sVar4 = FUN_0001ef0e(param_1,&local_8,auStack_c);
  if (sVar4 == 0) {
    psVar2 = (short *)((int)param_1 + 0x62e);
    sVar5 = *psVar2;
    *psVar2 = *psVar2 + -1;
    if (1 < sVar5) goto LAB_0001f0d6;
  }
  sVar5 = FUN_00018732(uVar1,*local_8,local_8[1],local_8[2]);
  sVar5 = FUN_0000f87c((int)sVar5 - (int)*(short *)((int)param_1 + 0x22));
  iVar3 = FUN_00003908((int)sVar5);
  if (iVar3 < 0x101) {
    *(undefined2 *)((int)param_1 + 0x62e) = 0xf;
  }
  else {
    if (sVar5 < 0) {
      sVar5 = -0x100;
    }
    else {
      sVar5 = 0x100;
    }
    *(undefined2 *)((int)param_1 + 0x62e) = 1;
  }
  *(ushort *)((int)param_1 + 0x22) = *(short *)((int)param_1 + 0x22) + sVar5 & 0x3fff;
  FUN_0001efe0(param_1);
LAB_0001f0d6:
  if (*(int *)((int)param_1 + 0x616) < 0x168) {
    *(int *)((int)param_1 + 0x616) = *(int *)((int)param_1 + 0x616) + 4;
    FUN_0001efe0(param_1);
  }
  return (int)sVar4;
}



// FUN_0001f0f8 @ 0x1f0f8

int * FUN_0001f0f8(int param_1)

{
  int *piVar1;
  undefined4 uVar2;
  undefined4 uVar3;
  int iVar4;
  short sVar5;
  int iVar6;
  int *unaff_D7;
  undefined4 *puVar7;
  
  uVar2 = *(undefined4 *)(param_1 + 4);
  uVar3 = *(undefined4 *)(param_1 + 0xc);
  puVar7 = &DAT_00020b44 + DAT_00020f2c._2_2_;
  iVar4 = 0x7fffffff;
  sVar5 = DAT_00020f2c._2_2_;
  while (sVar5 = sVar5 + -1, sVar5 != -1) {
    puVar7 = puVar7 + -1;
    piVar1 = (int *)*puVar7;
    if (*piVar1 != 0) {
      if (**(short **)(*piVar1 + 4) == 0x29) {
        if ((piVar1[5] & 0x2002U) == 0) {
          iVar6 = FUN_000048a6(uVar2,0,uVar3,piVar1[1],0,piVar1[3]);
          if (iVar6 < iVar4) {
            iVar4 = iVar6;
            unaff_D7 = piVar1;
          }
        }
      }
    }
  }
  if (0x26ac0 < iVar4) {
    unaff_D7 = (int *)0x0;
  }
  return unaff_D7;
}



// FUN_0001f184 @ 0x1f184

void FUN_0001f184(int *param_1)

{
  int iVar1;
  int iVar2;
  int iVar3;
  int iVar4;
  short sVar10;
  int iVar5;
  short sVar11;
  short sVar12;
  short sVar13;
  short sVar14;
  short sVar15;
  int iVar6;
  ushort uVar16;
  ushort uVar17;
  char cVar18;
  int iVar7;
  uint uVar8;
  int iVar9;
  short sVar19;
  short sVar20;
  short sVar21;
  
  iVar7 = *param_1;
  if (*(int *)((int)param_1 + 0x61a) == 0) {
    if (*(short *)(param_1 + 0x184) == 99) {
      FUN_0001f034(param_1);
      return;
    }
    if (*(short *)((int)param_1 + 0x62a) == -1) {
      if (*(int *)((int)param_1 + 0x616) == 0) {
        return;
      }
      *(int *)((int)param_1 + 0x616) = *(int *)((int)param_1 + 0x616) + -4;
    }
    else {
      sVar10 = FUN_0000f87c((int)*(short *)((int)param_1 + 0x62a) -
                            (int)*(short *)((int)param_1 + 0x22));
      iVar7 = FUN_00003908((int)sVar10);
      if (0x100 < iVar7) {
        if (sVar10 < 0) {
          sVar10 = -0x100;
        }
        else {
          sVar10 = 0x100;
        }
      }
      if (sVar10 != 0) {
        *(ushort *)((int)param_1 + 0x22) = *(short *)((int)param_1 + 0x22) + sVar10 & 0x3fff;
        FUN_0001efe0(param_1);
      }
      if (0x167 < *(int *)((int)param_1 + 0x616)) {
        return;
      }
      *(int *)((int)param_1 + 0x616) = *(int *)((int)param_1 + 0x616) + 4;
    }
    FUN_0001efe0(param_1);
    return;
  }
  iVar5 = *(int *)((int)param_1 + 0x61a);
  if (((*(byte *)(iVar5 + 0x17) & 2) == 0) && (*(int *)(iVar5 + 0x46) != 0)) {
    if ((*(byte *)((int)param_1 + 0x62d) & 1) == 0) {
      if ((*(byte *)((int)param_1 + 0x62d) & 2) == 0) {
        if (*(int *)((int)param_1 + 0x616) != 0) {
          *(int *)((int)param_1 + 0x616) = *(int *)((int)param_1 + 0x616) + -4;
          FUN_0001efe0(param_1);
        }
      }
      else {
        sVar10 = FUN_0001f034(param_1);
        if (sVar10 != 0) {
          *(ushort *)(param_1 + 0x18b) = *(ushort *)(param_1 + 0x18b) & 0xfffd;
        }
      }
      if (((*(byte *)((int)param_1 + 0x62d) & 4) == 0) || (cVar18 = FUN_00013672(), cVar18 == '\0'))
      {
        iVar7 = FUN_0001f0f8(iVar7);
        if (iVar7 != 0) {
          sVar10 = 0;
          do {
            uVar8 = FUN_0001369e();
            iVar5 = (*(int *)(iVar7 + 4) + uVar8 % (uint)(*(int *)(iVar7 + 0x20) << 1)) -
                    *(int *)(iVar7 + 0x20);
            uVar8 = FUN_0001369e();
            iVar4 = (*(int *)(iVar7 + 0xc) + uVar8 % (uint)(*(int *)(iVar7 + 0x20) << 1)) -
                    *(int *)(iVar7 + 0x20);
            iVar9 = FUN_00015b5a(iVar5,iVar4,iVar7,*(undefined4 *)(iVar7 + 0x4a));
            if (iVar9 != 0) {
              *(int *)((int)param_1 + 0x622) = iVar5;
              *(int *)((int)param_1 + 0x626) = iVar4;
              *(ushort *)(param_1 + 0x18b) = *(ushort *)(param_1 + 0x18b) | 2;
              break;
            }
            sVar10 = sVar10 + 1;
          } while (sVar10 < 0x32);
        }
        *(ushort *)(param_1 + 0x18b) = *(ushort *)(param_1 + 0x18b) | 4;
      }
      uVar16 = FUN_00013672();
      if ((uVar16 & 0x1ff) == 0) {
        *(ushort *)(param_1 + 0x18b) = *(ushort *)(param_1 + 0x18b) | 1;
        return;
      }
      return;
    }
    iVar4 = FUN_000048a6(*(undefined4 *)(iVar7 + 4),*(undefined4 *)(iVar7 + 8),
                         *(undefined4 *)(iVar7 + 0xc),*(undefined4 *)(iVar5 + 4),
                         *(undefined4 *)(iVar5 + 8),*(undefined4 *)(iVar5 + 0xc));
    *(int *)((int)param_1 + 0x61e) = iVar4;
    if (iVar4 < 0x26ac1) {
      if (*(int *)((int)param_1 + 0x616) != 0) {
        *(int *)((int)param_1 + 0x616) = *(int *)((int)param_1 + 0x616) + -4;
        FUN_0001efe0(param_1);
      }
      sVar10 = FUN_00018732(iVar7,*(undefined4 *)(iVar5 + 4),0,*(undefined4 *)(iVar5 + 0xc));
      sVar10 = FUN_0000f87c((int)sVar10 - (int)*(short *)((int)param_1 + 0x22));
      iVar5 = FUN_00003908((int)sVar10);
      if (0x100 < iVar5) {
        if (sVar10 < 0) {
          sVar10 = -0x100;
        }
        else {
          sVar10 = 0x100;
        }
      }
      iVar5 = FUN_00003908((int)sVar10);
      if (0xf < iVar5) {
        *(ushort *)((int)param_1 + 0x22) = *(short *)((int)param_1 + 0x22) + sVar10 & 0x3fff;
        return;
      }
      sVar11 = FUN_000023c4((int)*(short *)((int)param_1 + 0x22));
      iVar4 = *(int *)(iVar7 + 0x20);
      iVar5 = *(int *)(iVar7 + 4);
      iVar9 = *(int *)(iVar7 + 0x20) + *(int *)(iVar7 + 0x20) * 2;
      if (iVar9 < 0) {
        iVar9 = iVar9 + 3;
      }
      iVar1 = *(int *)(iVar7 + 8);
      sVar12 = FUN_00002426((int)*(short *)((int)param_1 + 0x22));
      iVar3 = *(int *)(iVar7 + 0x20);
      iVar2 = *(int *)(iVar7 + 0xc);
      sVar21 = 0x800;
      sVar20 = 0;
      sVar19 = 0;
      do {
        iVar6 = (int)sVar20 + (int)sVar21;
        if (iVar6 < 0) {
          iVar6 = iVar6 + 1;
        }
        sVar13 = (short)(iVar6 >> 1);
        sVar14 = FUN_000023c4((int)sVar13);
        sVar15 = FUN_00002426((int)sVar13);
        iVar6 = (((((int)sVar14 << 0x10) / 0x5b >> 6) * (int)sVar15 >> 0xc) << 0xf) >> 6;
        if (iVar6 < 0) {
          iVar6 = iVar6 + 0x3f;
        }
        if (iVar6 >> 6 < *(int *)((int)param_1 + 0x61e)) {
          sVar20 = sVar13;
        }
        else {
          sVar21 = sVar13;
        }
        sVar19 = sVar19 + 1;
      } while (sVar19 < 0xc);
      uVar16 = FUN_00013652();
      uVar17 = FUN_00013652();
      uVar16 = FUN_00013652(1,iVar5 - (iVar4 * sVar11 >> 0xc),iVar1 + (iVar9 >> 2),
                            iVar2 + (iVar3 * sVar12 >> 0xc),
                            ((uVar17 & 0x1f) + sVar13) - 0xf & 0x3fff,
                            (int)(short)(((uVar16 & 0x1f) + sVar10) - 0xf & 0x3fff) +
                            (int)*(short *)((int)param_1 + 0x22) & 0x3fff,0);
      FUN_000149fa(iVar7,0x7fff - (uint)(uVar16 & 0xff));
      *(ushort *)(param_1 + 0x18b) = *(ushort *)(param_1 + 0x18b) & 0xfffe;
      return;
    }
  }
  *(char *)(iVar5 + 0x29) = *(char *)(iVar5 + 0x29) + -1;
  *(undefined4 *)((int)param_1 + 0x61a) = 0;
  *(undefined2 *)(param_1 + 0x18b) = 0;
  return;
}



// FUN_0001f51c @ 0x1f51c

void FUN_0001f51c(int *param_1)

{
  int *piVar1;
  int iVar2;
  short *psVar3;
  
  iVar2 = *param_1;
  psVar3 = (short *)((int)param_1 + 0x60e);
  piVar1 = (int *)((int)param_1 + *psVar3 * 0x14 + 0x32);
  *psVar3 = *psVar3 + 1;
  if (*(short *)((int)param_1 + 0x60e) == 0x4b) {
    *psVar3 = 0;
  }
  if (DAT_00021058 == 0) {
    FUN_0001f184(param_1);
    *piVar1 = param_1[2] + *(int *)(iVar2 + 4);
    piVar1[1] = param_1[3] + *(int *)(iVar2 + 8);
    piVar1[2] = param_1[4] + *(int *)(iVar2 + 0xc);
    *(undefined2 *)(piVar1 + 3) = *(undefined2 *)(param_1 + 8);
    *(undefined2 *)((int)piVar1 + 0xe) = *(undefined2 *)((int)param_1 + 0x22);
    *(undefined2 *)(piVar1 + 4) = *(undefined2 *)(param_1 + 9);
    *(undefined2 *)((int)piVar1 + 0x12) = *(undefined2 *)(param_1 + 0x18c);
  }
  else if (*(int *)(iVar2 + 0x42) <= DAT_00021020) {
    if ((DAT_00021058 == 1) || (DAT_00021020 == *(int *)(iVar2 + 0x42))) {
      param_1[4] = 0;
      param_1[3] = 0;
      param_1[2] = 0;
      *(int *)(iVar2 + 4) = *piVar1;
      *(int *)(iVar2 + 8) = piVar1[1];
      *(int *)(iVar2 + 0xc) = piVar1[2];
    }
    else {
      param_1[2] = *piVar1 - *(int *)(iVar2 + 4);
      param_1[3] = piVar1[1] - *(int *)(iVar2 + 8);
      param_1[4] = piVar1[2] - *(int *)(iVar2 + 0xc);
    }
    *(undefined2 *)(param_1 + 0x18c) = *(undefined2 *)((int)piVar1 + 0x12);
    *(undefined2 *)(param_1 + 8) = *(undefined2 *)(piVar1 + 3);
    *(undefined2 *)((int)param_1 + 0x22) = *(undefined2 *)((int)piVar1 + 0xe);
    *(undefined2 *)(param_1 + 9) = *(undefined2 *)(piVar1 + 4);
    *(undefined2 *)((int)param_1 + 0x632) = *(undefined2 *)(param_1 + 0x18c);
  }
  return;
}



// FUN_0001f60e @ 0x1f60e

void FUN_0001f60e(undefined4 param_1,int param_2)

{
  undefined4 *puVar1;
  
  puVar1 = (undefined4 *)FUN_000021a4(0x32);
  *puVar1 = param_1;
  puVar1[1] = *(undefined4 *)(&DAT_0002074c + param_2 * 4);
  return;
}



// FUN_0001f63a @ 0x1f63a

undefined4 * FUN_0001f63a(undefined4 *param_1,undefined4 *param_2,undefined4 *param_3)

{
  undefined4 *puVar1;
  int iVar2;
  int iVar3;
  uint uVar4;
  uint uVar5;
  short sVar6;
  undefined4 *puVar7;
  undefined4 *puVar8;
  
  puVar1 = (undefined4 *)((int)param_1 - (int)param_2);
  if (puVar1 != (undefined4 *)0x0) {
    puVar7 = puVar1;
    if (((int)param_1 < (int)param_2) &&
       (puVar7 = (undefined4 *)-(int)puVar1,
       (undefined4 *)-(int)param_3 == puVar1 || (int)puVar7 < (int)param_3)) {
      puVar1 = (undefined4 *)((uint)param_1 | (uint)param_2);
      param_2 = (undefined4 *)((int)param_3 + (int)param_2);
      param_1 = (undefined4 *)((int)param_3 + (int)param_1);
      if ((((uint)puVar1 & 1) == 0) &&
         (puVar1 = (undefined4 *)((int)param_1 + 2), puVar1 != param_2)) {
        puVar1 = (undefined4 *)(CONCAT22((short)((uint)puVar1 >> 0x10),(short)param_3) & 0xffff0003)
        ;
        if (((uint)param_3 & 3) != 0) {
          puVar1 = (undefined4 *)CONCAT22((short)((uint)puVar1 >> 0x10),(short)puVar1 + -1);
          do {
            param_1 = (undefined4 *)((int)param_1 + -1);
            param_2 = (undefined4 *)((int)param_2 + -1);
            *(undefined1 *)param_2 = *(undefined1 *)param_1;
            sVar6 = (short)puVar1 + -1;
            puVar1 = (undefined4 *)CONCAT22((short)((uint)puVar1 >> 0x10),sVar6);
          } while (sVar6 != -1);
        }
        uVar4 = (uint)param_3 >> 2;
        if (uVar4 != 0) {
          uVar4 = uVar4 - 1;
        }
        do {
          do {
            param_1 = param_1 + -1;
            param_2 = param_2 + -1;
            *param_2 = *param_1;
            sVar6 = (short)uVar4 + -1;
            uVar5 = CONCAT22((short)(uVar4 >> 0x10),sVar6);
            uVar4 = uVar5;
          } while (sVar6 != -1);
          uVar4 = uVar5 - 0x10000;
        } while (0xffff < (int)uVar5);
      }
      else {
        iVar2 = (int)param_3 - 1;
        if ((int)param_3 < 1) {
          return puVar1;
        }
        do {
          do {
            param_1 = (undefined4 *)((int)param_1 + -1);
            param_2 = (undefined4 *)((int)param_2 + -1);
            *(undefined1 *)param_2 = *(undefined1 *)param_1;
            sVar6 = (short)iVar2 + -1;
            iVar3 = CONCAT22((short)((uint)iVar2 >> 0x10),sVar6);
            iVar2 = iVar3;
          } while (sVar6 != -1);
          iVar2 = iVar3 + -0x10000;
        } while (0xffff < iVar3);
      }
      return puVar1;
    }
    puVar1 = puVar7;
    if (((0x19 < (int)param_3) && (((uint)puVar7 & 1) == 0)) &&
       (puVar1 = (undefined4 *)0x0, puVar7 != (undefined4 *)0x2)) {
      puVar1 = param_3;
      if (((uint)param_1 & 1) != 0) {
        *(undefined1 *)param_2 = *(undefined1 *)param_1;
        puVar1 = (undefined4 *)((int)param_3 - 1);
        param_2 = (undefined4 *)((int)param_2 + 1);
        param_1 = (undefined4 *)((int)param_1 + 1);
      }
      param_3 = (undefined4 *)((uint)puVar1 & 3);
      if ((uint)puVar1 >> 2 != 0) {
        iVar2 = ((uint)puVar1 >> 2) - 1;
        puVar1 = param_2;
        puVar7 = param_1;
        do {
          do {
            param_1 = puVar7 + 1;
            param_2 = puVar1 + 1;
            *puVar1 = *puVar7;
            sVar6 = (short)iVar2 + -1;
            iVar3 = CONCAT22((short)((uint)iVar2 >> 0x10),sVar6);
            iVar2 = iVar3;
            puVar1 = param_2;
            puVar7 = param_1;
          } while (sVar6 != -1);
          iVar2 = iVar3 + -0x10000;
        } while (0xffff < iVar3);
      }
      puVar1 = param_3;
    }
    iVar2 = (int)param_3 - 1;
    if (0 < (int)param_3) {
      do {
        do {
          puVar8 = (undefined4 *)((int)param_1 + 1);
          puVar7 = (undefined4 *)((int)param_2 + 1);
          *(undefined1 *)param_2 = *(undefined1 *)param_1;
          sVar6 = (short)iVar2 + -1;
          iVar3 = CONCAT22((short)((uint)iVar2 >> 0x10),sVar6);
          iVar2 = iVar3;
          param_2 = puVar7;
          param_1 = puVar8;
        } while (sVar6 != -1);
        iVar2 = iVar3 + -0x10000;
      } while (0xffff < iVar3);
    }
  }
  return puVar1;
}



// FUN_0001f712 @ 0x1f712

undefined4 FUN_0001f712(undefined4 *param_1,uint param_2)

{
  int iVar1;
  short sVar3;
  int iVar2;
  undefined4 *puVar4;
  undefined4 *puVar5;
  
  if (-1 < (int)param_2) {
    puVar4 = param_1;
    if (((uint)param_1 & 1) != 0) {
      puVar4 = (undefined4 *)((int)param_1 + 1);
      *(undefined1 *)param_1 = 0;
      param_2 = param_2 - 1;
    }
    if (param_2 >> 2 != 0) {
      iVar1 = (param_2 >> 2) - 1;
      puVar5 = puVar4;
      do {
        do {
          puVar4 = puVar5 + 1;
          *puVar5 = 0;
          sVar3 = (short)iVar1 + -1;
          iVar2 = CONCAT22((short)((uint)iVar1 >> 0x10),sVar3);
          iVar1 = iVar2;
          puVar5 = puVar4;
        } while (sVar3 != -1);
        iVar1 = iVar2 + -0x10000;
      } while (0xffff < iVar2);
    }
    if ((param_2 & 3) != 0) {
      sVar3 = ((byte)param_2 & 3) - 1;
      do {
        *(undefined1 *)puVar4 = 0;
        sVar3 = sVar3 + -1;
        puVar4 = (undefined4 *)((int)puVar4 + 1);
      } while (sVar3 != -1);
    }
  }
  return 0;
}



// FUN_0001f75a @ 0x1f75a

char * FUN_0001f75a(char *param_1)

{
  char cVar1;
  short in_D0w;
  char *pcVar3;
  char *pcVar2;
  
  pcVar3 = param_1;
LAB_0001f764:
  do {
    pcVar2 = pcVar3 + 1;
    cVar1 = *pcVar3;
    pcVar3 = pcVar2;
    if (cVar1 != '\0') {
      in_D0w = in_D0w + -1;
      if (in_D0w != -1) goto LAB_0001f764;
    }
    if (cVar1 == '\0') {
      return pcVar2 + (-1 - (int)param_1);
    }
  } while( true );
}


