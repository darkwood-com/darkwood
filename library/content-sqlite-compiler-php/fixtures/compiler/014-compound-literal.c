struct Pair {
    int a;
    int b;
};

int main(void)
{
    struct Pair p = (struct Pair){20, 22};
    return p.a + p.b;
}
