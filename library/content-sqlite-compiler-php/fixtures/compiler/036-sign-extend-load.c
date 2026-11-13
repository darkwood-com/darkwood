int main(void)
{
    signed char c = -1;
    short s = -2;
    int i = -3;
    long Lc = c;
    long Ls = s;
    long Li = i;
    if (Lc != -1L) {
        return 1;
    }
    if (Ls != -2L) {
        return 2;
    }
    if (Li != -3L) {
        return 3;
    }
    return 42;
}
