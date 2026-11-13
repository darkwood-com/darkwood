struct S {
    int a;
    int b;
};

int main(void)
{
    struct S *p = &(struct S){20, 22};
    return p->a + p->b;
}
