static int arr[4] = {1, 2, 3, 4};
static int *ptr = &arr[2];

int main(void)
{
    return *ptr;
}
