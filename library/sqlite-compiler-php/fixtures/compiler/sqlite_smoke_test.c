#include <stdio.h>

typedef struct sqlite3 sqlite3;
typedef struct sqlite3_stmt sqlite3_stmt;

extern const char *sqlite3_libversion(void);
extern int sqlite3_open(const char *filename, sqlite3 **ppDb);
extern int sqlite3_close(sqlite3 *db);
extern int sqlite3_exec(sqlite3 *db, const char *sql,
    int (*callback)(void*, int, char**, char**),
    void *arg, char **errmsg);
extern int sqlite3_prepare_v2(sqlite3 *db, const char *zSql, int nByte,
    sqlite3_stmt **ppStmt, const char **pzTail);
extern int sqlite3_step(sqlite3_stmt *pStmt);
extern int sqlite3_finalize(sqlite3_stmt *pStmt);
extern const char *sqlite3_column_text(sqlite3_stmt *pStmt, int iCol);
extern const char *sqlite3_errmsg(sqlite3 *db);
extern void sqlite3_free(void *p);

#define SQLITE_OK 0
#define SQLITE_ROW 100
#define SQLITE_DONE 101

int main(void)
{
    sqlite3 *db = 0;
    char *errmsg = 0;
    int rc;

    rc = sqlite3_open(":memory:", &db);
    if (rc != SQLITE_OK) {
        fprintf(stderr, "FAIL: open: %s\n", sqlite3_errmsg(db));
        return 1;
    }

    rc = sqlite3_exec(db,
        "CREATE TABLE test (value TEXT);"
        "INSERT INTO test VALUES ('darkwood');",
        0, 0, &errmsg);
    if (rc != SQLITE_OK) {
        fprintf(stderr, "FAIL: exec: %s\n", errmsg ? errmsg : "(null)");
        return 1;
    }

    sqlite3_stmt *stmt = 0;
    rc = sqlite3_prepare_v2(db, "SELECT value FROM test;", -1, &stmt, 0);
    if (rc != SQLITE_OK) {
        fprintf(stderr, "FAIL: prepare: %s\n", sqlite3_errmsg(db));
        return 1;
    }

    rc = sqlite3_step(stmt);
    if (rc != SQLITE_ROW) {
        fprintf(stderr, "FAIL: step rc=%d\n", rc);
        return 1;
    }

    const char *value = sqlite3_column_text(stmt, 0);
    printf("%s\n", value ? value : "(null)");

    sqlite3_finalize(stmt);
    sqlite3_close(db);

    return 0;
}
