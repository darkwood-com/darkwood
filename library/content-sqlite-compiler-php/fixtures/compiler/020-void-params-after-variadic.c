int printf(const char *format, ...);

int helper(void)
{
    return 40;
}

int main(void)
{
    return helper() + 2;
}
