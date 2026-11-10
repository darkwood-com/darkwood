struct Quad {
    long a;
    long b;
};

struct Quad make(void)
{
    return (struct Quad){20, 22};
}

int main(void)
{
    struct Quad q = make();
    return (int)(q.a + q.b);
}
