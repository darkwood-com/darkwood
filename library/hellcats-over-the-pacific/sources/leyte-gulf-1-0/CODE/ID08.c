// Leyte Gulf CODE 8 (Leyte Gulf:Leyte Gulf resource fork), Ghidra 12.2 headless: Motorola 68000 BE, raw binary at base 0 (file offsets), tools/leyte/ghidra.

// FUN_00000004 @ 0x4

void FUN_00000004(void)

{
  return;
}



// FUN_00000006 @ 0x6

/* WARNING: Control flow encountered bad instruction data */

void FUN_00000006(void)

{
  int iVar1;
  int iVar2;
  int *piVar3;
  int unaff_A5;
  undefined2 *puVar4;
  undefined1 *puVar5;
  undefined1 *puVar6;
  undefined1 local_23e [4];
  undefined2 uStack_23a;
  undefined2 local_238;
  undefined4 uStack_236;
  undefined2 local_232;
  int local_12c;
  int local_128;
  undefined1 auStack_124 [260];
  int local_20;
  int local_1c;
  int local_18;
  undefined4 local_14;
  short local_10;
  undefined2 local_e;
  undefined4 local_c;
  int local_8;
  
  local_c = 0;
  local_232 = 0;
  uStack_236 = 0x910;
  local_238 = 0;
  unique0x00012700 = &local_e;
  local_23e._0_2_ = 0x1e;
  (**(code **)(unaff_A5 + 0x2c2))();
  puVar4 = &uStack_23a;
  if (local_23e._2_2_ != 0) {
    local_23e = (undefined1  [4])auStack_124;
    (**(code **)(unaff_A5 + 0x4e2))((short)(**(int **)(unaff_A5 + -0x142a) + 0x2e));
    local_23e = (undefined1  [4])auStack_124;
    (**(code **)(unaff_A5 + 0x17a))();
                    /* WARNING: Bad instruction - Truncating control flow here */
    halt_baddata();
  }
  if (*(int *)(unaff_A5 + -0x1048) != 0) {
    local_23e._0_2_ = SUB42((uint)*(undefined4 *)(unaff_A5 + -0x1048) >> 0x10,0);
    local_23e._2_2_ = SUB42(*(undefined4 *)(unaff_A5 + -0x1048),0);
    (**(code **)(unaff_A5 + 0x24a))();
    puVar4 = (undefined2 *)local_23e;
  }
  local_14 = 0x20;
  *(undefined2 *)((int)puVar4 + -2) = 0;
  *(undefined2 *)((int)puVar4 + -4) = local_e;
  *(undefined4 **)((int)puVar4 + -8) = &local_14;
  *(int *)((int)puVar4 + -0xc) = unaff_A5 + -0x1068;
  *(undefined4 *)((int)puVar4 + -0x10) = 0x6a;
  (**(code **)(unaff_A5 + 0x2d2))();
  local_10 = *(short *)((int)puVar4 + -0xc);
  if (local_10 == 0) {
    local_18 = *(int *)(unaff_A5 + -0x105c) +
               *(int *)(unaff_A5 + -0x1060) + *(int *)(unaff_A5 + -0x1064);
    *(undefined4 *)((int)puVar4 + -0xe) = 0;
    *(int *)((int)puVar4 + -0x12) = local_18 + 4;
    *(undefined4 *)((int)puVar4 + -0x16) = 0xa4;
    (**(code **)(unaff_A5 + 0x242))();
    iVar1 = *(int *)((int)puVar4 + -0x12);
    *(int *)(unaff_A5 + -0x1048) = iVar1;
    if (iVar1 != 0) {
      *(int *)(unaff_A5 + -0x1048) =
           (4 - (*(uint *)(unaff_A5 + -0x1048) & 3) & 3) + *(int *)(unaff_A5 + -0x1048);
      local_14 = *(undefined4 *)(unaff_A5 + -0x1064);
      *(undefined2 *)((int)puVar4 + -0x10) = 0;
      *(undefined2 *)((int)puVar4 + -0x12) = local_e;
      *(undefined4 **)((int)puVar4 + -0x16) = &local_14;
      *(undefined4 *)((int)puVar4 + -0x1a) = *(undefined4 *)(unaff_A5 + -0x1048);
      *(undefined4 *)((int)puVar4 + -0x1e) = 0xec;
      (**(code **)(unaff_A5 + 0x2d2))();
      puVar6 = (undefined1 *)((int)puVar4 + -0x18);
      if (*(int *)(unaff_A5 + -0x1060) != 0) {
        local_14 = *(undefined4 *)(unaff_A5 + -0x1060);
        iVar1 = *(int *)(unaff_A5 + -0x1048);
        iVar2 = *(int *)(unaff_A5 + -0x1064);
        *(undefined2 *)((int)puVar4 + -0x1a) = 0;
        *(undefined2 *)((int)puVar4 + -0x1c) = local_e;
        *(undefined4 **)((int)puVar4 + -0x20) = &local_14;
        *(int *)((int)puVar4 + -0x24) = iVar2 + iVar1;
        *(undefined4 *)((int)puVar4 + -0x28) = 0x112;
        (**(code **)(unaff_A5 + 0x2d2))();
        puVar6 = (undefined1 *)((int)puVar4 + -0x22);
      }
      local_1c = *(int *)(unaff_A5 + -0x1050) / 8;
      local_20 = *(int *)(unaff_A5 + -0x104c) / 8;
      puVar5 = puVar6;
      for (local_8 = 0; local_8 < local_20 + local_1c; local_8 = local_8 + 1) {
        local_14 = 8;
        *(undefined2 *)(puVar5 + -2) = 0;
        *(undefined2 *)(puVar5 + -4) = local_e;
        *(undefined4 **)(puVar5 + -8) = &local_14;
        *(int **)(puVar5 + -0xc) = &local_12c;
        *(undefined4 *)(puVar5 + -0x10) = 0x154;
        (**(code **)(unaff_A5 + 0x2d2))();
        if ((local_128 >> 7 & 1U) == 0) {
          if ((local_128 >> 4 & 1U) != 0) {
            *(undefined2 *)(puVar5 + -0xc) = 0;
            *(undefined4 *)(puVar5 + -0x10) = *(undefined4 *)(unaff_A5 + -0xe2c);
            *(undefined4 *)(puVar5 + -0x14) = 0x17a;
            (**(code **)(unaff_A5 + 0x4da))();
            puVar5 = puVar5 + -10;
            goto LAB_000001f8;
          }
          if ((local_128 >> 5 & 3U) != 2) {
            *(undefined2 *)(puVar5 + -0xc) = 0;
            *(undefined4 *)(puVar5 + -0x10) = *(undefined4 *)(unaff_A5 + -0xe28);
            *(undefined4 *)(puVar5 + -0x14) = 0x1d0;
            (**(code **)(unaff_A5 + 0x4da))();
            puVar5 = puVar5 + -10;
            goto LAB_000001f8;
          }
          if (local_8 < local_1c) {
            piVar3 = (int *)(local_12c + *(int *)(unaff_A5 + -0x1048));
            *piVar3 = *(int *)(unaff_A5 + -0x1048) + *piVar3;
          }
          else {
            piVar3 = (int *)(local_12c + *(int *)(unaff_A5 + -0x1064) + *(int *)(unaff_A5 + -0x1048)
                            );
            *piVar3 = *(int *)(unaff_A5 + -0x1048) + *piVar3;
          }
        }
        puVar5 = puVar5 + -10;
      }
      *(undefined2 *)(puVar5 + -2) = local_e;
      *(undefined4 *)(puVar5 + -6) = 0x1f0;
      FUN_00000350();
      *(undefined4 *)(puVar5 + -4) = 0x1f6;
      (**(code **)(unaff_A5 + 0x51a))();
      goto LAB_000001fc;
    }
    *(undefined2 *)((int)puVar4 + -0x10) = 0;
    *(undefined4 *)((int)puVar4 + -0x14) = *(undefined4 *)(unaff_A5 + -0xe30);
    *(undefined4 *)((int)puVar4 + -0x18) = 0xba;
    (**(code **)(unaff_A5 + 0x4da))();
    puVar5 = (undefined1 *)((int)puVar4 + -0xe);
  }
  else {
    *(short *)((int)puVar4 + -0xc) = local_10;
    *(undefined4 *)((int)puVar4 + -0x10) = *(undefined4 *)(unaff_A5 + -0xe34);
    *(undefined4 *)((int)puVar4 + -0x14) = 0x80;
    (**(code **)(unaff_A5 + 0x4da))();
    puVar5 = (undefined1 *)((int)puVar4 + -10);
  }
LAB_000001f8:
  *(undefined4 *)(unaff_A5 + -0x1048) = 0;
LAB_000001fc:
  *(undefined2 *)(puVar5 + -2) = 0;
  *(undefined2 *)(puVar5 + -4) = local_e;
  *(undefined4 *)(puVar5 + -8) = 0x206;
  (**(code **)(unaff_A5 + 0x2ca))();
  *(undefined4 *)(puVar5 + -6) = 0x20c;
  (**(code **)(unaff_A5 + 0x5ba))();
  *(undefined2 *)(puVar5 + -4) = 0;
  *(int *)(puVar5 + -8) = unaff_A5 + -0x1768;
                    /* WARNING: Bad instruction - Truncating control flow here */
  halt_baddata();
}



// FUN_00000340 @ 0x340

/* WARNING: Control flow encountered bad instruction data */

void FUN_00000340(void)

{
                    /* WARNING: Bad instruction - Truncating control flow here */
  halt_baddata();
}



// FUN_00000350 @ 0x350

undefined4 FUN_00000350(undefined4 param_1)

{
  undefined2 extraout_D0u;
  int unaff_A5;
  undefined4 uVar1;
  undefined2 local_20;
  undefined2 uVar2;
  int local_c;
  int local_8;
  
  (**(code **)(unaff_A5 + 0x2f2))(&local_c,0);
  (**(code **)(unaff_A5 + 0x2ea))((short)&local_8);
  local_8 = local_8 - local_c;
  local_20 = (undefined2)((uint)local_8 >> 0x10);
  uVar2 = (undefined2)local_8;
  (**(code **)(unaff_A5 + 0x23a))(uVar2,0,param_1._0_2_);
  *(uint *)(unaff_A5 + -0x137a) = CONCAT22(local_20,uVar2);
  uVar1 = *(undefined4 *)(unaff_A5 + -0x137a);
  (**(code **)(unaff_A5 + 0x2d2))(uVar1,&local_8,0);
  return CONCAT22(extraout_D0u,(short)((uint)uVar1 >> 0x10));
}


