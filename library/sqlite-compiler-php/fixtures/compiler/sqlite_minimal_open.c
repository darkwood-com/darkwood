#include <stdio.h>

typedef struct sqlite3 sqlite3;

extern const char *sqlite3_libversion(void);
extern int sqlite3_open(const char *filename, sqlite3 **ppDb);
extern int sqlite3_close(sqlite3 *db);
extern const char *sqlite3_errmsg(sqlite3 *db);

#define SQLITE_OK 0

int main(void)
{
    printf("version: %s\n", sqlite3_libversion());

    sqlite3 *db = 0;
    int rc = sqlite3_open(":memory:", &db);
    if (rc != SQLITE_OK) {
        fprintf(stderr, "open failed: %s\n", sqlite3_errmsg(db));
        return 1;
    }

    printf("open ok\n");
    sqlite3_close(db);
    return 0;
}
