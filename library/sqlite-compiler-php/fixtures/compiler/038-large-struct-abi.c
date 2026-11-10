struct Big {
    long a;
    long b;
    long c;
};

struct Big make(void)
{
    struct Big b = {10, 20, 12};
    return b;
}

int sum(struct Big b)
{
    return (int)(b.a + b.b + b.c);
}

int main(void)
{
    struct Big b = make();
    return sum(b);
}
