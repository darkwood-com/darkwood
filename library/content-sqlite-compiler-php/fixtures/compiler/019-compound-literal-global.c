struct Pair {
    int a;
    int b;
};

static struct Pair global = (struct Pair){20, 22};

int main(void)
{
    return global.a + global.b;
}
