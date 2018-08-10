// taken from: https://gist.github.com/colinmollenhour/cf23b0f7e955267ed1107c9edb07f7c2

// gcc -O2 -Wall -pedantic process-mysqldump.c -o process-mysqldump
// Usage: cat dump.sql | process-mysqldump
//   Or : process-mysqldump dump.sql

#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <string.h>

#define BUFFER 100000

bool is_escaped(char* string, int offset) {
    if (offset == 0) {
        return false;
    } else if (string[offset - 1] == '\\') {
        return !is_escaped(string, offset - 1);
    } else {
        return false;
    }
}

bool is_insert(char* string) {
    char buffer[] = "INSERT INTO ";

    return strncmp(buffer, string, 12) == 0;
}

int main(int argc, char *argv[])
{
    FILE* file = argc > 1 ? fopen(argv[1], "rb") : stdin;

    char buffer[BUFFER];
    char* line;
    int pos;
    int parenthesis = 0;
    bool quote = false;
    bool escape = false;
    bool check_prefix = true;
    bool wasnt_insert = false;

    while (fgets(buffer, BUFFER, file) != NULL) {
        line = buffer;

        // skip non-INSERT INTO statements
        if (check_prefix && (wasnt_insert || ! is_insert(line))) {
            check_prefix = line[strlen(line) - 1] == '\n';
            wasnt_insert = ! check_prefix;
            fputs(line, stdout);
            continue;
        }

        check_prefix = line[strlen(line) - 1] == '\n';
        pos = 0;

        nullchar:
        while (line[pos] != '\0') {
            // if we are still in escape state, we need to check first char.
            if (!escape) {
                // find any character in ()'
                pos = strcspn(line, "()'\\");
            }

            if (pos > 0) {
                // print before match
                printf("%.*s", pos, line);
            }

            switch (line[pos]) {
                case '(':
                    if (!quote) {
                        if (parenthesis == 0) {
                            puts("");
                        }
                        parenthesis++;
                    }
                    if (escape) {
                        escape = false;
                    }
                    break;

                case ')':
                    if (!quote) {
                        if (parenthesis > 0) {
                            parenthesis--;
                        } else {
                            // whoops
                            puts("");
                            fputs(line, stdout);
                            fputs("Found closing parenthesis without opening one.\n", stderr);
                            exit(1);
                        }
                    }
                    if (escape) {
                        escape = false;
                    }
                    break;

                case '\\':
                    escape = !escape;
                    break;

                case '\'':
                    if (escape) {
                        escape = false;
                    } else {
                        quote = !quote;
                    }
                    break;

                case '\0':
                    goto nullchar;

                default:
                    if (escape) {
                        escape = false;
                    }
                    break;
            }

            // print char then skip it (to make sure we don’t double match)
            putchar(line[pos]);
            line = line + pos + 1;
            pos = 0;
        }
    }

    return 0;
}
