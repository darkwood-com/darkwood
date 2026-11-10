struct Pair {
    int x;
    int y;
};

struct Pair make(void)
{
    return (struct Pair){20, 22};
}

int main(void)
{
    struct Pair p = make();
    return p.x + p.y;
}
