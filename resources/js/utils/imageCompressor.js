/**
 * Utility for validating max file size and compressing images client-side before upload.
 */

export const MAX_CATEGORY_IMAGE_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB

export function formatBytes(bytes, decimals = 1) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Validates max size and compresses an input File using HTML Canvas.
 * 
 * @param {File} file - The file selected by user
 * @param {Object} options - Compression options
 * @returns {Promise<Object>} Compression result with file, sizes, and savings stats
 */
export async function validateAndCompressImage(file, options = {}) {
  const {
    maxSizeBytes = MAX_CATEGORY_IMAGE_SIZE_BYTES,
    maxWidth = 1000,
    maxHeight = 1200,
    quality = 0.82,
    outputType = 'image/webp'
  } = options;

  if (!file) {
    throw new Error('No file provided for compression.');
  }

  // 1. Max Size Check
  if (file.size > maxSizeBytes) {
    const maxMb = (maxSizeBytes / (1024 * 1024)).toFixed(0);
    throw new Error(`File size (${formatBytes(file.size)}) exceeds the maximum allowed size of ${maxMb}MB. Please select a smaller image.`);
  }

  // 2. Type Check
  const allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/avif', 'image/bmp'];
  if (!allowedMimeTypes.includes(file.type.toLowerCase())) {
    throw new Error(`Unsupported image format (${file.type || 'unknown'}). Please upload JPG, PNG, or WEBP images.`);
  }

  // 3. Compress using Canvas
  return new Promise((resolve, reject) => {
    const img = new Image();
    const url = URL.createObjectURL(file);

    img.onload = () => {
      URL.revokeObjectURL(url);

      let width = img.naturalWidth || img.width;
      let height = img.naturalHeight || img.height;

      // Calculate aspect ratio downscaling
      if (width > maxWidth || height > maxHeight) {
        const widthRatio = maxWidth / width;
        const heightRatio = maxHeight / height;
        const ratio = Math.min(widthRatio, heightRatio);
        width = Math.round(width * ratio);
        height = Math.round(height * ratio);
      }

      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;

      const ctx = canvas.getContext('2d');
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(img, 0, 0, width, height);

      // Determine export format
      let targetMime = outputType;
      
      canvas.toBlob(
        (blob) => {
          if (!blob) {
            // Fallback to original file if blob creation fails
            resolve({
              file,
              originalSize: file.size,
              compressedSize: file.size,
              formattedOriginalSize: formatBytes(file.size),
              formattedCompressedSize: formatBytes(file.size),
              reductionPercentage: 0,
              isCompressed: false
            });
            return;
          }

          // Generate compressed File object
          const originalNameWithoutExt = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
          const ext = targetMime === 'image/webp' ? 'webp' : 'jpg';
          const compressedFileName = `${originalNameWithoutExt}_compressed.${ext}`;
          
          const compressedFile = new File([blob], compressedFileName, {
            type: targetMime,
            lastModified: Date.now()
          });

          const originalSize = file.size;
          const compressedSize = compressedFile.size;
          const reduction = originalSize > compressedSize 
            ? Math.round(((originalSize - compressedSize) / originalSize) * 100)
            : 0;

          resolve({
            file: compressedFile,
            originalSize,
            compressedSize,
            formattedOriginalSize: formatBytes(originalSize),
            formattedCompressedSize: formatBytes(compressedSize),
            reductionPercentage: reduction,
            isCompressed: true
          });
        },
        targetMime,
        quality
      );
    };

    img.onerror = () => {
      URL.revokeObjectURL(url);
      reject(new Error('Failed to read image file. The file may be corrupt or invalid.'));
    };

    img.src = url;
  });
}
