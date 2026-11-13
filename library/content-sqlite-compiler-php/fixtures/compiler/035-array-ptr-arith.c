int main(void)
{
    int a[3] = {10, 20, 12};
    int *p = a + 1;
    return *(a + 2) == 12 && *p == 20 ? 42 : 0;
}
