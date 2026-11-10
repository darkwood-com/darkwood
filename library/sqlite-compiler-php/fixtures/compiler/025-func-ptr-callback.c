static int dbl(int x)
{
    return x + x;
}

int call(int (*cb)(int), int x)
{
    return cb(x);
}

int main(void)
{
    return call(dbl, 21);
}
