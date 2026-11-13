#define BYTEORDER 4321
int value = BYTEORDER;
int main(void)
{
    return value == 4321 ? 42 : 1;
}
