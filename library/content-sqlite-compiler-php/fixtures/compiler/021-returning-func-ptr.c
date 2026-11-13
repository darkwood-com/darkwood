static void handler(void)
{
}

static void (*make_handler(int unused))(void)
{
    (void)unused;
    return handler;
}

int main(void)
{
    void (*h)(void) = make_handler(0);
    h();
    return 42;
}
