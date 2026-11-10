int main(void)
{
    int a[2][2] = { {1, 2}, {3, 4} };
    int (*p)[2] = a;
    return (*p)[0] + (*p)[1];
}
