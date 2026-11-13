int main(void)
{
    unsigned a = 0;
    unsigned long b = 0UL;
    if (~a != 0xFFFFFFFFu) {
        return 1;
    }
    if (~b != ~0UL) {
        return 2;
    }
    return 42;
}
