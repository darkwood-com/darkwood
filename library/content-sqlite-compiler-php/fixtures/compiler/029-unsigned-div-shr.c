int main(void)
{
    unsigned long a = ~0UL;
    unsigned long b = a / 2UL;
    unsigned long c = a % 3UL;
    unsigned long d = a >> 1;
    if (b != 0x7FFFFFFFFFFFFFFFUL) {
        return 1;
    }
    if (c != 0UL) {
        return 2;
    }
    if (d != 0x7FFFFFFFFFFFFFFFUL) {
        return 3;
    }
    return 42;
}
