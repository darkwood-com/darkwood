struct S {
    int a;
    int b;
    int c;
};

int main(void)
{
    struct S s = {.b = 20, .c = 22};
    int arr[4] = {[1] = 10, [3] = 32};
    return s.a + s.b + s.c + arr[1] + arr[3];
}
