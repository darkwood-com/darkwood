struct Pair {
    int a;
    int b;
};

struct Pair idp(struct Pair p)
{
    return p;
}

int main(void)
{
    struct Pair p = {10, 32};
    struct Pair s = idp(p);
    return s.a + s.b;
}
