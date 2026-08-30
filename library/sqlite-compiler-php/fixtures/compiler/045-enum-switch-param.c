enum E { A = 1, B = 2 };

int f(enum E e)
{
    switch (e) {
        case A:
            return 10;
        case B:
            return 20;
        default:
            return 0;
    }
}

int main(void)
{
    return f(B);
}
