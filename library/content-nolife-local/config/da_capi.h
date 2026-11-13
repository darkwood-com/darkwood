typedef struct da_ctx da_ctx;

int da_capi_abi_version(void);
da_ctx* da_capi_load(const char* gguf_path, int n_threads);
void da_capi_free(da_ctx* ctx);
const char* da_capi_last_error(da_ctx* ctx);
void da_capi_free_floats(float* p);

int da_capi_depth_dense(da_ctx* ctx, const char* image_path, int* out_h, int* out_w,
    float** out_depth, float** out_conf, float** out_sky,
    float out_ext[12], float out_intr[9], int* out_is_metric);

float* da_capi_depth_path(da_ctx* ctx, const char* image_path, int* out_h, int* out_w);

int da_capi_pose_path(da_ctx* ctx, const char* image_path, float out_ext[12], float out_intr[9]);

int da_capi_export_glb(da_ctx* ctx, const char* image_path, const char* out_glb);

int da_capi_export_glb_cached(da_ctx* ctx, const char* out_glb);
