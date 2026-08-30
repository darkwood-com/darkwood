struct S {
    char c[3];
    int n;
};

int main(void)
{
    struct S s = { "ab", 5 };
    return s.c[0] + s.c[1] + s.n;
}
