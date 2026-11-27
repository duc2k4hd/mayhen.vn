/**
 * MAYHEN.VN — UNIFIED CLIENT-SIDE WEBP IMAGE COMPRESSOR
 * Chuyển đổi toàn bộ ảnh tạo thiệp sang định dạng WebP theo 5 mốc dung lượng chuẩn.
 * Strict: Pure Vanilla JavaScript (ES Modules), zero external dependencies.
 */

const KB = 1024;
const MB = 1024 * 1024;

/**
 * Xác định chất lượng nén WebP (0.0 -> 1.0) theo 5 mốc dung lượng file gốc:
 * - <= 100KB: chỉ chuyển sang WebP (0.92)
 * - > 100KB đến <= 200KB: quality 0.80
 * - > 200KB đến <= 500KB: quality 0.70
 * - > 500KB đến <= 1MB: quality 0.50
 * - > 1MB: quality 0.30 (nén mạnh giảm 70%)
 */
export function resolveWebpQualityByBytes(byteSize) {
    const bytes = Number(byteSize) || 0;
    if (bytes > 1 * MB) return 0.30;
    if (bytes > 500 * KB) return 0.50;
    if (bytes > 200 * KB) return 0.70;
    if (bytes > 100 * KB) return 0.80;
    return 0.92;
}

/**
 * Xuất HTMLCanvasElement sang chuỗi data:image/webp;base64,... theo mốc dung lượng.
 * Tự động fallback sang image/jpeg nếu trình duyệt không hỗ trợ encode WebP.
 */
export function canvasToWebpDataUrl(canvas, sourceBytes = 0) {
    const quality = resolveWebpQualityByBytes(sourceBytes);
    const webpUrl = canvas.toDataURL('image/webp', quality);
    if (typeof webpUrl === 'string' && webpUrl.startsWith('data:image/webp')) {
        return webpUrl;
    }
    return canvas.toDataURL('image/jpeg', quality);
}

/**
 * Nén và chuyển đổi File/Blob ảnh sang chuỗi data:image/webp;base64,... tối ưu.
 * Giữ nguyên kênh trong suốt (alpha) cho PNG/WebP khi xuất WebP.
 */
export function compressImageFileToWebpDataUrl(fileOrBlob, { maxWidth = 1200, maxHeight = 1600 } = {}) {
    return new Promise((resolve, reject) => {
        if (!fileOrBlob) {
            reject(new Error('Không tìm thấy tệp ảnh hợp lệ.'));
            return;
        }

        const sourceBytes = Number(fileOrBlob.size) || 0;
        const objectUrl = URL.createObjectURL(fileOrBlob);
        const img = new Image();

        img.onload = () => {
            try {
                let w = img.naturalWidth || 1;
                let h = img.naturalHeight || 1;

                if (w > maxWidth || h > maxHeight) {
                    const ratio = Math.min(maxWidth / w, maxHeight / h);
                    w = Math.max(1, Math.round(w * ratio));
                    h = Math.max(1, Math.round(h * ratio));
                }

                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);

                const dataUrl = canvasToWebpDataUrl(canvas, sourceBytes);
                resolve(dataUrl);
            } catch (error) {
                reject(error);
            } finally {
                URL.revokeObjectURL(objectUrl);
            }
        };

        img.onerror = (err) => {
            URL.revokeObjectURL(objectUrl);
            reject(err instanceof Error ? err : new Error('Không thể đọc dữ liệu ảnh.'));
        };

        img.src = objectUrl;
    });
}
