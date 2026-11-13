struct S {
    int a;
    int b;
};

int main(void)
{
    struct S s;
    s = (struct S){20, 22};
    return s.a + s.b;
}
