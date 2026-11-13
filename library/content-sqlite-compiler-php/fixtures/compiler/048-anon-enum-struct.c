struct S {
    enum { A = 1, B = 2 } e;
    int x;
};

int main(void)
{
    struct S s = { .e = B, .x = 40 };
    return s.e + s.x;
}
