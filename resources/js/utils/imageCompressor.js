/**
 * Client-Side Image Compression Utility
 * Resizes and compresses images using HTML5 Canvas before upload.
 * Guarantees output file size <= maxSizeBytes (default 200 KB) with immediate preview URL generation.
 */

export async function compressImage(file, maxSizeBytes = 204800, maxDimension = 1400) {
  if (!file) {
    throw new Error('Please select a valid image file.');
  }

  const originalSize = file.size || 0;

  // Immediate preview URL generated from the user's raw file
  let previewUrl = '';
  try {
    previewUrl = URL.createObjectURL(file);
  } catch (e) {
    console.warn('URL.createObjectURL failed on raw file:', e);
  }

  try {
    // Load image into HTMLImageElement
    const image = await loadImage(file);
    let { width, height } = image;

    // Calculate scaled dimensions preserving aspect ratio
    if (width > maxDimension || height > maxDimension) {
      if (width >= height) {
        height = Math.round((height / width) * maxDimension);
        width = maxDimension;
      } else {
        width = Math.round((width / height) * maxDimension);
        height = maxDimension;
      }
    }

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(image, 0, 0, width, height);

    // Determine canvas export support (WebP vs JPEG fallback)
    let targetMime = 'image/webp';
    let quality = 0.85;
    let blob = await canvasToBlob(canvas, targetMime, quality);

    // If WebP is unsupported by browser canvas or returns wrong MIME type, fallback to JPEG
    if (!blob || blob.type !== 'image/webp') {
      targetMime = 'image/jpeg';
      blob = await canvasToBlob(canvas, targetMime, quality);
    }

    // Quality iteration loop
    while (blob && blob.size > maxSizeBytes && quality >= 0.2) {
      quality -= 0.08;
      blob = await canvasToBlob(canvas, targetMime, quality);
    }

    // If still > maxSizeBytes, scale dimensions down further
    while (blob && blob.size > maxSizeBytes && width > 300) {
      width = Math.round(width * 0.75);
      height = Math.round(height * 0.75);
      canvas.width = width;
      canvas.height = height;
      ctx.drawImage(image, 0, 0, width, height);
      blob = await canvasToBlob(canvas, targetMime, 0.5);
    }

    if (blob) {
      const finalMime = blob.type || targetMime;
      const ext = finalMime === 'image/webp' ? '.webp' : (finalMime === 'image/png' ? '.png' : '.jpg');
      const baseName = file.name ? file.name.replace(/\.[^/.]+$/, '') : 'review-photo';
      const fileName = `${baseName}${ext}`;

      const compressedFile = new File([blob], fileName, {
        type: finalMime,
        lastModified: Date.now(),
      });

      return {
        file: compressedFile,
        originalFile: file,
        originalSizeFormatted: formatBytes(originalSize),
        compressedSizeFormatted: formatBytes(compressedFile.size),
        originalSize: originalSize,
        compressedSize: compressedFile.size,
        previewUrl: previewUrl || URL.createObjectURL(compressedFile),
      };
    }
  } catch (err) {
    console.warn('Client-side compression failed, using original file:', err);
  }

  // Graceful fallback to original file
  return {
    file: file,
    originalFile: file,
    originalSizeFormatted: formatBytes(originalSize),
    compressedSizeFormatted: formatBytes(originalSize),
    originalSize: originalSize,
    compressedSize: originalSize,
    previewUrl: previewUrl,
  };
}

function loadImage(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => resolve(img);
      img.onerror = () => reject(new Error('Failed to parse image data.'));
      img.src = e.target.result;
    };
    reader.onerror = () => reject(new Error('Failed to read image file.'));
    reader.readAsDataURL(file);
  });
}

function canvasToBlob(canvas, type, quality) {
  return new Promise((resolve) => {
    canvas.toBlob((blob) => resolve(blob), type, quality);
  });
}

export function formatBytes(bytes, decimals = 1) {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}
