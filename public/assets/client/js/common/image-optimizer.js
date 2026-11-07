/**
 * Mây Hẹn (mayhen.vn) — Client-side Smart Image Optimizer
 * Pure Native JavaScript (HTML5 Canvas API) & Zero External Libraries.
 * Tự động tối ưu hóa và nén ảnh phía trình duyệt di động trước khi tải lên.
 */

export const MayHenImageOptimizer = {
    /**
     * Tối ưu hóa một tệp ảnh đơn lẻ.
     *
     * @param {File} file
     * @param {Object} options
     * @returns {Promise<File>}
     */
    async optimizeFile(file, options = {}) {
        const maxWidth = options.maxWidth || 2048;
        const maxHeight = options.maxHeight || 2048;
        const quality = options.quality || 0.82;
        const targetType = options.mimeType || 'image/webp';

        // Nếu file quá nhỏ (< 400KB) hoặc không phải ảnh, bỏ qua nén
        if (!file.type.startsWith('image/') || file.size < 400 * 1024) {
            return file;
        }

        // Nếu là GIF động hoặc SVG, không nén qua canvas để tránh mất chuyển động
        if (file.type === 'image/gif' || file.type === 'image/svg+xml') {
            return file;
        }

        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    try {
                        let width = img.width;
                        let height = img.height;

                        // Tính toán kích thước co giãn giữ nguyên tỷ lệ
                        if (width > maxWidth || height > maxHeight) {
                            if (width / height > maxWidth / maxHeight) {
                                height = Math.round((height * maxWidth) / width);
                                width = maxWidth;
                            } else {
                                width = Math.round((width * maxHeight) / height);
                                height = maxHeight;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');

                        if (!ctx) {
                            resolve(file);
                            return;
                        }

                        // Vẽ nền trắng phòng trường hợp ảnh PNG trong suốt chuyển sang JPEG
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(0, 0, width, height);
                        ctx.drawImage(img, 0, 0, width, height);

                        // Thử nghiệm xuất định dạng WebP (hoặc JPEG nếu không hỗ trợ)
                        canvas.toBlob((blob) => {
                            if (!blob || blob.size >= file.size) {
                                // Nếu nén xong dung lượng lại lớn hơn hoặc lỗi, giữ nguyên file gốc
                                resolve(file);
                                return;
                            }

                            // Tạo File object mới từ blob đã nén
                            const ext = blob.type === 'image/webp' ? '.webp' : '.jpg';
                            const baseName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                            const optimizedFileName = `${baseName}${ext}`;

                            const optimizedFile = new File([blob], optimizedFileName, {
                                type: blob.type,
                                lastModified: Date.now(),
                            });

                            resolve(optimizedFile);
                        }, targetType, quality);
                    } catch (err) {
                        // Bất kỳ lỗi canvas nào đều an toàn fallback về file gốc
                        resolve(file);
                    }
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    },

    /**
     * Tối ưu hóa danh sách nhiều tệp ảnh.
     *
     * @param {File[]} files
     * @param {Function} onProgress
     * @returns {Promise<File[]>}
     */
    async optimizeFiles(files, onProgress = null) {
        const results = [];
        const total = files.length;

        for (let i = 0; i < total; i++) {
            if (onProgress) {
                onProgress(i + 1, total, files[i].name);
            }
            const optimized = await this.optimizeFile(files[i]);
            results.push(optimized);
        }

        return results;
    }
};

if (typeof window !== 'undefined') {
    window.MayHenImageOptimizer = MayHenImageOptimizer;
}

export default MayHenImageOptimizer;

