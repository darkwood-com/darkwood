int printf(const char *format, ...);

static int counter = 0;

static int next(void)
{
    return ++counter;
}

int main(void)
{
    printf("%d%d%d", next(), next(), next());
    return counter == 3 ? 42 : 1;
}
