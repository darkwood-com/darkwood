#include <stdio.h>

extern const char *sqlite3_libversion(void);

int main(void)
{
    printf("before\n");
    const char *v = sqlite3_libversion();
    printf("version: %s\n", v ? v : "(null)");
    return 0;
}
