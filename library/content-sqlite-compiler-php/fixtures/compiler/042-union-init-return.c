union U {
    int i;
    char c;
};

union U make(void)
{
    return (union U){42};
}

int main(void)
{
    union U a = {.c = 41};
    union U b = make();
    return a.c + 1 == 42 && b.i == 42 && make().i == 42 ? 42 : 0;
}
