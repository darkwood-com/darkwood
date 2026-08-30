struct S {
    struct {
        enum { X = 1, Y = 2 } e;
    };
    int n;
};

int main(void)
{
    struct S s = { .e = Y, .n = 40 };
    return s.e + s.n;
}
