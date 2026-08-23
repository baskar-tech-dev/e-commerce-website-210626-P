/**
 * Client-Side Smart Image Compressor
 * Automatically compresses large camera/DSLR/mobile photos before upload to prevent 413 Payload Too Large errors
 * and reduce file size to the optimal 200KB - 400KB range while maintaining pristine visual quality.
 */
export async function compressImage(file, maxDimension = 1800, targetMaxKB = 400, targetMinKB = 200) {
  // If file is not an image or is SVG, return directly
  if (!file || !file.type || !file.type.startsWith('image/') || file.type.includes('svg')) {
    return file;
  }

  // If file is already small (<= 250KB), return directly without re-compression
  if (file.size <= targetMinKB * 1024) {
    return file;
  }

  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (event) => {
      const img = new Image();
      img.src = event.target.result;
      img.onload = () => {
        let width = img.width;
        let height = img.height;

        // 1. Calculate scaled dimensions if larger than maxDimension
        if (width > maxDimension || height > maxDimension) {
          if (width > height) {
            height = Math.round((height * maxDimension) / width);
            width = maxDimension;
          } else {
            width = Math.round((width * maxDimension) / height);
            height = maxDimension;
          }
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, width, height);

        // Prefer modern WebP format for optimal 200-400KB compression
        const outputMime = 'image/webp';
        const targetMaxBytes = targetMaxKB * 1024; // 400KB in bytes

        // Helper to try quality levels
        const attemptCompression = (quality) => {
          canvas.toBlob(
            (blob) => {
              if (!blob) {
                return resolve(file);
              }

              // If blob is still > 400KB and quality is above 0.65, retry with lower quality
              if (blob.size > targetMaxBytes && quality > 0.65) {
                return attemptCompression(Math.max(0.60, quality - 0.12));
              }

              // Create new compressed File object
              const baseName = file.name.replace(/\.[^/.]+$/, '');
              const compressedFile = new File([blob], `${baseName}.webp`, {
                type: outputMime,
                lastModified: Date.now(),
              });

              resolve(compressedFile);
            },
            outputMime,
            quality
          );
        };

        // Start compression at 0.85 quality
        attemptCompression(0.85);
      };

      img.onerror = () => resolve(file);
    };

    reader.onerror = () => resolve(file);
  });
}
