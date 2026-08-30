struct Big {
    long a;
    long b;
    long c;
    long d;
};

int main(void)
{
    struct Big x = {1, 2, 3, 4};
    struct Big y = x;
    return (int)(y.a + y.b + y.c + y.d);
}
