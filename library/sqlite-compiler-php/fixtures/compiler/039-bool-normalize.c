int main(void)
{
    int x = 5;
    _Bool b = x;
    _Bool z = 0;
    if (b != 1) {
        return 1;
    }
    if (z != 0) {
        return 2;
    }
    return 42;
}
