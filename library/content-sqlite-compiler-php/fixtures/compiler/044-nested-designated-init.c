struct Inner {
    int x;
    int y;
};

struct Outer {
    struct Inner i;
    int z;
};

struct Arr {
    int a[2];
};

int main(void)
{
    struct Outer o = {.z = 3, .i.y = 2, .i.x = 1};
    struct Arr s = {.a[1] = 40, .a[0] = 2};
    return o.i.x + o.i.y + o.z + s.a[0] + s.a[1];
}
