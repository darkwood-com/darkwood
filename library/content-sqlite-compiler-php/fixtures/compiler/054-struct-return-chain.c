struct S {
    int a;
    int b;
};

struct S a(void)
{
    return (struct S){1, 2};
}

struct S b(void)
{
    return a();
}

int main(void)
{
    struct S s = b();
    return s.a + s.b;
}
