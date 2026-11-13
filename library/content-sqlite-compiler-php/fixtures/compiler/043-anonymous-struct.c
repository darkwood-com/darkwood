struct Outer {
    struct {
        int a;
        int b;
    };
    int c;
};

int main(void)
{
    struct Outer o;
    o.a = 10;
    o.b = 20;
    o.c = 12;
    return o.a + o.b + o.c;
}
