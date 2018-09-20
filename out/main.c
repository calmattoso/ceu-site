#include <stdlib.h>
#include <stdio.h>

int ceu_callback_ceu (int cmd, tceu_callback_val p1, tceu_callback_val p2
#ifdef CEU_FEATURES_TRACE
                     , tceu_trace trace
#endif
                     )
{
    int is_handled = 1;

    switch (cmd) {
        case CEU_CALLBACK_WCLOCK_DT:
            ceu_callback_ret.num  = CEU_WCLOCK_INACTIVE;
            break;
        case CEU_CALLBACK_ABORT:
            abort();
            break;
        case CEU_CALLBACK_LOG: {
            switch (p1.num) {
                case 0:
                    fprintf(stderr, "%s", (char*)p2.ptr);
                    break;
                case 1:
                    fprintf(stderr, "%p", p2.ptr);
                    break;
                case 2:
                    fprintf(stderr, "%d", p2.num);
                    break;
            }
            break;
        }
        case CEU_CALLBACK_REALLOC:
#ifdef CEU_TESTS_REALLOC
        {
            static int _ceu_tests_realloc_ = 0;
            if (p2.size == 0) {
                _ceu_tests_realloc_--;
            } else {
                if (_ceu_tests_realloc_ >= CEU_TESTS_REALLOC) {
                    ceu_callback_ret.ptr = NULL;
                }
                _ceu_tests_realloc_++;
            }
        }
#endif
            ceu_callback_ret.ptr = realloc(p1.ptr, p2.size);
        default:
            is_handled = 0;
    }
    return is_handled;
}

int main (int argc, char* argv[])
{
    tceu_callback cb = { &ceu_callback_ceu, NULL };
    int ret = ceu_loop(&cb, argc, argv);
    fprintf(stderr, "*** END: %d\n", ret);
    return ret;
}
