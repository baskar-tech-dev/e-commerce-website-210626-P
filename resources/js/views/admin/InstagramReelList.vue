<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Storefront Reels & Videos</h1>
      <span class="admin-page__subtitle">Manage autoplaying vertical video reels and YouTube videos/Shorts displayed on the storefront.</span>
    </div>
    <button class="btn btn--primary" @click="openCreateModal" style="display: inline-flex; align-items: center; gap: 8px;">
      <Plus :size="18" /> Add Video/Reel
    </button>
  </div>

  <!-- Loading State -->
  <div v-if="loading && reels.length === 0" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading videos...</div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
    <AlertTriangle :size="18" /> {{ errorMsg }}
  </div>

  <!-- Success Alert -->
  <div v-if="successMsg" class="badge badge--success" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px; display: flex; align-items: center; gap: 8px; background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;">
    <CheckCircle :size="18" /> {{ successMsg }}
  </div>

  <!-- Videos Grid / List -->
  <div class="glass-panel" style="overflow: hidden; margin-top: 1rem; padding: var(--spacing-md);">
    
    <!-- Desktop View Table -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 120px;">Preview</th>
          <th>Caption</th>
          <th>Type</th>
          <th>Video URL</th>
          <th>Social Link</th>
          <th style="width: 100px;">Sort Order</th>
          <th style="width: 100px;">Status</th>
          <th style="text-align: right; width: 160px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="reel in reels" :key="reel.id">
          <td>
            <div class="video-preview-thumbnail" style="width: 80px; height: 120px; border-radius: 8px; overflow: hidden; background: #000; position: relative;">
              <!-- Local MP4 player -->
              <video v-if="reel.type === 'file'" :src="reel.video_url" muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
              <!-- YouTube player placeholder/iframe -->
              <iframe v-else-if="reel.type === 'youtube'" :src="getYouTubeEmbedUrl(reel.video_url, true)" frameborder="0" style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;"></iframe>
              
              <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.6); color: #fff; padding: 2px 4px; font-size: 0.65rem; border-radius: 4px; display: flex; align-items: center; gap: 2px;">
                <Video v-if="reel.type === 'file'" :size="10" />
                <Youtube v-else :size="10" style="color: #ef4444;" />
                {{ reel.type === 'youtube' ? 'YouTube' : 'Instagram' }}
              </div>
            </div>
          </td>
          <td style="font-weight: 500; color: #1e293b; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ reel.caption || '(No Caption)' }}
          </td>
          <td>
            <span :class="['badge', reel.type === 'youtube' ? 'badge--info' : 'badge--neutral']" style="font-size: 0.7rem; font-weight: bold; padding: 2px 8px; border-radius: 20px;">
              {{ reel.type === 'youtube' ? 'YouTube Video' : 'Instagram Reel' }}
            </span>
          </td>
          <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            <code style="font-size: 0.8rem;">{{ reel.video_url }}</code>
          </td>
          <td>
            <a v-if="reel.instagram_url" :href="reel.instagram_url" target="_blank" class="text-primary" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.85rem; font-weight: 600; color: var(--color-primary); text-decoration: none;">
              <ExternalLink :size="14" /> Visit Link
            </a>
            <span v-else style="color: #94a3b8; font-size: 0.85rem;">—</span>
          </td>
          <td>
            <span style="font-weight: 600; color: #475569;">{{ reel.sort_order }}</span>
          </td>
          <td>
            <span :class="['badge', reel.is_active ? 'badge--success' : 'badge--danger']" style="padding: 2px 8px; border-radius: 20px; font-size: 0.75rem;">
              {{ reel.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <button class="btn btn--secondary btn--sm" @click="openEditModal(reel)" style="display: inline-flex; align-items: center; gap: 4px;">
                <Edit :size="14" /> Edit
              </button>
              <button class="btn btn--danger btn--sm" @click="deleteReel(reel.id)" style="display: inline-flex; align-items: center; gap: 4px;">
                <Trash :size="14" /> Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="reels.length === 0 && !loading">
          <td colspan="8" style="text-align: center; padding: 4rem; color: #94a3b8;">
            <Film :size="48" style="margin: 0 auto 1rem auto; opacity: 0.4; display: block;" />
            No video reels uploaded yet. Click "Add Video/Reel" to publish your first vertical video!
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Mobile View Cards -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="reel in reels" :key="reel.id" style="padding: var(--spacing-md); border-bottom: 1px solid #f1f5f9;">
        <div style="display: flex; gap: var(--spacing-md);">
          <div style="width: 70px; height: 105px; border-radius: 6px; overflow: hidden; background: #000; flex-shrink: 0; position: relative;">
            <video v-if="reel.type === 'file'" :src="reel.video_url" muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
            <iframe v-else-if="reel.type === 'youtube'" :src="getYouTubeEmbedUrl(reel.video_url, true)" frameborder="0" style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;"></iframe>
            <div style="position: absolute; bottom: 2px; right: 2px; background: rgba(0,0,0,0.6); color: #fff; padding: 1px 3px; font-size: 0.55rem; border-radius: 3px;">
              {{ reel.type === 'youtube' ? 'YouTube' : 'Instagram' }}
            </div>
          </div>
          
          <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="font-weight: 600; color: #1e293b; font-size: 0.95rem; margin-bottom: 4px;">
                {{ reel.caption || '(No Caption)' }}
              </div>
              <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                <span :class="['badge', reel.is_active ? 'badge--success' : 'badge--danger']" style="font-size: 0.7rem; padding: 1px 6px;">
                  {{ reel.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span style="font-size: 0.75rem; color: #64748b;">Order: {{ reel.sort_order }}</span>
              </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <a v-if="reel.instagram_url" :href="reel.instagram_url" target="_blank" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;">
                  <ExternalLink :size="12" /> Social Link
                </a>
              </div>
              <div style="display: flex; gap: 4px;">
                <button class="btn btn--secondary btn--sm" @click="openEditModal(reel)" style="padding: 4px 8px; font-size: 0.75rem;">Edit</button>
                <button class="btn btn--danger btn--sm" @click="deleteReel(reel.id)" style="padding: 4px 8px; font-size: 0.75rem;">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="reels.length === 0 && !loading" style="text-align: center; padding: 3rem; color: #94a3b8;">
        No video reels found.
      </div>
    </div>
  </div>

  <!-- Create/Edit Modal -->
  <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container" style="max-width: 500px; background: #fff; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
      <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: var(--spacing-md) var(--spacing-lg);">
        <h3 class="modal-title" style="font-family: 'Playfair Display', serif; font-weight: 700; color: var(--color-primary);">
          {{ isEdit ? 'Edit Video Reel' : 'Add New Video Reel' }}
        </h3>
        <button class="modal-close" @click="closeModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8;">&times;</button>
      </div>
      
      <form @submit.prevent="saveReel">
        <div class="modal-body" style="padding: var(--spacing-lg); max-height: 70vh; overflow-y: auto; display: flex; flex-direction: column; gap: var(--spacing-md);">
          
          <!-- Caption -->
          <div class="form-group">
            <label class="form-label" style="font-weight: 600; color: #334155;">Caption / Description</label>
            <input type="text" v-model="form.caption" class="form-input" placeholder="e.g. Elegant South Indian Saree Showcase" />
          </div>

          <!-- Video Source Method (Upload vs Link) -->
          <div class="form-group">
            <label class="form-label" style="font-weight: 600; color: #334155;">Video Source Method</label>
            <select v-model="sourceOption" class="form-input" style="background-color: #fff; appearance: auto;">
              <option value="upload">Upload Video File (Instagram Reels / Local Videos)</option>
              <option value="link">Link YouTube Video or Direct MP4 Link</option>
            </select>
          </div>

          <!-- File Upload Option (Visible only for 'upload' option when no file is uploaded yet) -->
          <div v-if="sourceOption === 'upload' && !form.video_url" class="form-group" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: var(--spacing-md); text-align: center;">
            <label class="form-label" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">Upload Video File (.mp4, .mov, etc)</label>
            <input type="file" ref="fileInput" @change="handleFileUpload" accept="video/*" style="display: none;" />
            
            <div v-if="!uploading" @click="$refs.fileInput.click()" style="cursor: pointer; padding: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
              <UploadCloud :size="36" style="color: var(--color-primary);" />
              <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">Click to browse and upload vertical video</span>
              <span style="font-size: 0.7rem; color: #94a3b8;">Max file size: 50MB</span>
            </div>
            
            <div v-else style="padding: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
              <div class="spinner" style="width: 24px; height: 24px; border: 3px solid #f3f3f3; border-top: 3px solid var(--color-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
              <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-primary);">Uploading video file... Please wait.</span>
            </div>
          </div>

          <!-- Video Preview & Change option (Visible only for 'upload' option when video is uploaded) -->
          <div v-if="sourceOption === 'upload' && form.video_url" class="form-group" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: var(--spacing-md); text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px;">
            <label class="form-label" style="font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Uploaded Video Preview</label>
            <video :src="form.video_url" controls muted style="max-height: 180px; border-radius: 8px; border: 1px solid #cbd5e1;"></video>
            <button type="button" class="btn btn--danger btn--sm" @click="form.video_url = ''" style="display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
              <Trash :size="12" /> Delete / Change Video
            </button>
          </div>

          <!-- YouTube / MP4 Video URL Link input (Visible only for 'link' option) -->
          <div v-if="sourceOption === 'link'" class="form-group">
            <label class="form-label" style="font-weight: 600; color: #334155;">Video Link / YouTube URL</label>
            <input 
              type="text" 
              v-model="form.video_url" 
              class="form-input" 
              placeholder="e.g. https://www.youtube.com/shorts/abcd1234 or https://domain.com/video.mp4" 
            />
            <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">
              Supports standard YouTube links, YouTube Shorts, or direct links to external MP4 video files.
            </span>
          </div>

          <!-- Preview for linked video -->
          <div v-if="sourceOption === 'link' && form.video_url" style="margin-top: 10px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: var(--spacing-sm); text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px;">
            <label class="form-label" style="font-weight: 700; color: #334155; margin-bottom: 0; display: block;">Linked Video Preview</label>
            <div style="width: 100px; height: 150px; border-radius: 8px; overflow: hidden; background: #000; position: relative; margin: 0 auto; border: 1px solid #cbd5e1;">
              <!-- Local/External MP4 player preview -->
              <video v-if="!isYouTubeUrl(form.video_url)" :src="form.video_url" controls muted style="width: 100%; height: 100%; object-fit: cover;"></video>
              <!-- YouTube preview iframe -->
              <iframe v-else :src="getYouTubeEmbedUrl(form.video_url, true)" frameborder="0" style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;"></iframe>
            </div>
          </div>

          <!-- Instagram / Social Redirection URL -->
          <div class="form-group">
            <label class="form-label" style="font-weight: 600; color: #334155;">Original Instagram Reel / Social URL (Optional)</label>
            <input type="text" v-model="form.instagram_url" class="form-input" placeholder="e.g. https://www.instagram.com/reel/C3B4d5e/" />
            <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">
              Clicking the video on the storefront will show a redirect link button to this social post.
            </span>
          </div>

          <!-- Grid formatting details -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
            <!-- Sort Order -->
            <div class="form-group">
              <label class="form-label" style="font-weight: 600; color: #334155;">Sort Order</label>
              <input type="number" v-model.number="form.sort_order" required class="form-input" />
            </div>

            <!-- Active Toggle -->
            <div class="form-group" style="display: flex; flex-direction: column; justify-content: center; padding-top: 20px;">
              <label class="form-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                <input type="checkbox" v-model="form.is_active" style="width: 18px; height: 18px; accent-color: var(--color-primary);" />
                <span style="font-weight: 600; color: #334155;">Active (Show on Storefront)</span>
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: var(--spacing-md) var(--spacing-lg);">
          <button type="button" class="btn btn--secondary" @click="closeModal" :disabled="submitting || uploading">
            Cancel
          </button>
          <button type="submit" class="btn btn--primary" :disabled="submitting || uploading" style="display: inline-flex; align-items: center; gap: 8px;">
            <Save :size="16" /> {{ submitting ? 'Saving...' : 'Save Video' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { 
  Plus, Film, Video, Youtube, ExternalLink, Edit, Trash, 
  UploadCloud, Save, AlertTriangle, CheckCircle 
} from 'lucide-vue-next';

const reels = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const submitting = ref(false);
const uploading = ref(false);
const errorMsg = ref(null);
const successMsg = ref(null);
const currentId = ref(null);
const sourceOption = ref('upload');

const form = ref({
  caption: '',
  type: 'file',
  video_url: '',
  instagram_url: '',
  is_active: true,
  sort_order: 0
});

onMounted(() => {
  fetchReels();
});

const fetchReels = async () => {
  loading.value = true;
  errorMsg.value = null;
  try {
    const response = await axios.get('/api/admin/instagram-reels');
    if (response.data && response.data.success) {
      reels.value = response.data.data;
    }
  } catch (err) {
    errorMsg.value = 'Failed to load video list from server.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEdit.value = false;
  currentId.value = null;
  sourceOption.value = 'upload';
  form.value = {
    caption: '',
    type: 'file',
    video_url: '',
    instagram_url: '',
    is_active: true,
    sort_order: reels.value.length * 10
  };
  errorMsg.value = null;
  successMsg.value = null;
  showModal.value = true;
};

const openEditModal = (reel) => {
  isEdit.value = true;
  currentId.value = reel.id;
  
  if (reel.type === 'youtube' || (reel.video_url && (reel.video_url.startsWith('http') || !reel.video_url.startsWith('/storage/')))) {
    sourceOption.value = 'link';
  } else {
    sourceOption.value = 'upload';
  }
  
  form.value = {
    caption: reel.caption,
    type: reel.type,
    video_url: reel.video_url,
    instagram_url: reel.instagram_url || '',
    is_active: reel.is_active,
    sort_order: reel.sort_order
  };
  errorMsg.value = null;
  successMsg.value = null;
  showModal.value = true;
};

const closeModal = () => {
  if (uploading.value) return;
  showModal.value = false;
};

const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate size < 50MB
  if (file.size > 50 * 1024 * 1024) {
    errorMsg.value = 'File size exceeds maximum 50MB limit.';
    return;
  }

  uploading.value = true;
  errorMsg.value = null;
  
  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await axios.post('/api/admin/instagram-reels/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data && response.data.success) {
      form.value.video_url = response.data.url;
      successMsg.value = 'Video uploaded successfully. Save the form to publish.';
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Video upload failed. Check file type and format.';
    console.error(err);
  } finally {
    uploading.value = false;
  }
};

const saveReel = async () => {
  submitting.value = true;
  errorMsg.value = null;
  successMsg.value = null;

  // Auto-detect type if source option is link and url is set
  if (sourceOption.value === 'link' && form.value.video_url) {
    const url = form.value.video_url.toLowerCase();
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
      form.value.type = 'youtube';
    } else {
      form.value.type = 'file';
    }
  } else {
    form.value.type = 'file';
  }

  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/api/admin/instagram-reels/${currentId.value}`, form.value);
    } else {
      response = await axios.post('/api/admin/instagram-reels', form.value);
    }

    if (response.data && response.data.success) {
      successMsg.value = isEdit.value ? 'Video updated successfully.' : 'Video added successfully.';
      showModal.value = false;
      fetchReels();
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save video reel parameters.';
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

const deleteReel = async (id) => {
  if (confirm('Are you sure you want to permanently delete this video/reel? This will also remove the video file if it was uploaded locally.')) {
    errorMsg.value = null;
    successMsg.value = null;
    try {
      const response = await axios.delete(`/api/admin/instagram-reels/${id}`);
      if (response.data && response.data.success) {
        successMsg.value = 'Video deleted successfully.';
        fetchReels();
      }
    } catch (err) {
      errorMsg.value = 'Failed to delete video reel.';
      console.error(err);
    }
  }
};

// YouTube helper to get embeddable iframe URL with autoplay parameters
const getYouTubeEmbedUrl = (url, isMutedPreview = false) => {
  if (!url) return '';
  
  let videoId = '';
  
  // Extract video ID from standard URL, share link, or Shorts link
  if (url.includes('youtube.com/shorts/')) {
    videoId = url.split('youtube.com/shorts/')[1]?.split('?')[0]?.split('&')[0];
  } else if (url.includes('youtube.com/watch')) {
    const urlParams = new URLSearchParams(new URL(url).search);
    videoId = urlParams.get('v');
  } else if (url.includes('youtu.be/')) {
    videoId = url.split('youtu.be/')[1]?.split('?')[0]?.split('&')[0];
  } else if (url.includes('youtube.com/embed/')) {
    videoId = url.split('youtube.com/embed/')[1]?.split('?')[0]?.split('&')[0];
  } else {
    // Treat the input as raw ID if no domain matched
    videoId = url;
  }

  if (!videoId) return '';

  const autoplay = isMutedPreview ? '1' : '0';
  const mute = isMutedPreview ? '1' : '0';

  return `https://www.youtube.com/embed/${videoId}?autoplay=${autoplay}&mute=${mute}&loop=1&playlist=${videoId}&controls=0&modestbranding=1&playsinline=1&enablejsapi=1`;
};

const isYouTubeUrl = (url) => {
  if (!url) return false;
  const lowerUrl = url.toLowerCase();
  return lowerUrl.includes('youtube.com') || lowerUrl.includes('youtu.be');
};
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}

.spinner {
  border-radius: 50%;
}

.video-preview-thumbnail::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
  pointer-events: none;
}
</style>
