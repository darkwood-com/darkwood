typedef struct Node {
    int v;
    struct Node *next;
} Node;

int main(void)
{
    Node a = { .v = 1, .next = 0 };
    return a.v + (a.next == 0);
}
