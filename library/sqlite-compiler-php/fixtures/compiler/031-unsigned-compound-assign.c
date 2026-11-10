int main(void)
{
    unsigned long a = ~0UL;
    a /= 2UL;
    a >>= 1;
    return a == 0x3FFFFFFFFFFFFFFFUL ? 42 : 1;
}
