int main(void)
{
    int a = -16;
    int q = a / 4;
    int r = a % 4;
    if (q != -4) {
        return 1;
    }
    if (r != 0) {
        return 2;
    }
    return 42;
}
