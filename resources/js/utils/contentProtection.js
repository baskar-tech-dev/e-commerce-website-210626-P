/**
 * Frontend Content Protection Utility for Maya Sree South Indian Fashion
 */

// Branded toast alert matching the application's aesthetic
export function showProtectionToast(message = "Content is protected.") {
    // Remove existing toast if present to support consecutive alerts
    const existing = document.getElementById('content-protection-toast');
    if (existing) {
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'content-protection-toast';
    
    // Embed party paper blast emoji side-by-side with the message
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px; min-height: 24px;">
            <span style="font-size: 1.4rem; line-height: 1; flex-shrink: 0;">🎉</span>
            <span style="font-family: 'Poppins', sans-serif; font-size: 0.9rem; font-weight: 500; color: #1e293b; line-height: 1.4;">${message}</span>
        </div>
    `;
    
    // Premium styling following Design Guidelines
    toast.style.position = 'fixed';
    toast.style.top = '40px'; // Show in top
    toast.style.bottom = 'auto';
    toast.style.left = '50%';
    toast.style.transform = 'translate(-50%, -10px)'; // Slide down animation
    toast.style.background = '#ffffff'; // White background
    toast.style.border = '1px solid #e2e8f0';
    toast.style.borderLeft = '4px solid #d4af37'; // Zari Gold accent
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04)';
    toast.style.zIndex = '999999';
    toast.style.opacity = '0';
    toast.style.pointerEvents = 'none';
    toast.style.transition = 'opacity 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)';
    
    document.body.appendChild(toast);
    
    // Trigger layout flow and fade-in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translate(-50%, 0)';
    }, 50);
    
    // Fade-out and delete
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translate(-50%, -20px)'; // Slide back up
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 2500);
}

// Event handler to disable image right-click
function handleImageContextMenu(e) {
    e.preventDefault();
    showProtectionToast("Image is protected by copyright.");
    return false;
}

// Event handler to disable image dragging
function handleImageDragStart(e) {
    e.preventDefault();
    return false;
}

// Event handler to disable common developer shortcut hotkeys
function preventDeveloperKeys(e) {
    // F12 key
    if (e.keyCode === 123) {
        e.preventDefault();
        showProtectionToast("Developer options are protected.");
        return false;
    }
    
    // Ctrl+Shift+I (DevTools inspector)
    // Ctrl+Shift+J (DevTools console)
    // Ctrl+Shift+C (DevTools element selector)
    if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
        e.preventDefault();
        showProtectionToast("Developer options are protected.");
        return false;
    }
    
    // Ctrl+U (View Source)
    if (e.ctrlKey && e.keyCode === 85) {
        e.preventDefault();
        showProtectionToast("Source view is protected.");
        return false;
    }
    
    // Ctrl+S (Save Page)
    if (e.ctrlKey && e.keyCode === 83) {
        e.preventDefault();
        showProtectionToast("Saving pages is disabled.");
        return false;
    }
}

/**
 * Initializes frontend content protection.
 * Registers global event listeners and the Vue `v-protect-image` directive.
 */
export function initContentProtection(app) {
    const isProduction = import.meta.env.PROD;
    const isProtectionEnabled = import.meta.env.VITE_ENABLE_CONTENT_PROTECTION === 'true';

    // Enable content protection ONLY if both flags are satisfied
    if (!isProduction || !isProtectionEnabled) {
        // Register a no-op directive so component templates compiled with v-protect-image don't fail
        app.directive('protect-image', {});
        return;
    }

    // Toggle global text selection restriction class on document body
    document.body.classList.add('content-protected');

    // Register v-protect-image directive globally
    app.directive('protect-image', {
        mounted(el) {
            // Apply lightweight CSS rules to the image element
            el.style.userSelect = 'none';
            el.style.webkitUserSelect = 'none';
            el.style.webkitUserDrag = 'none';
            
            el.addEventListener('contextmenu', handleImageContextMenu);
            el.addEventListener('dragstart', handleImageDragStart);
        },
        unmounted(el) {
            el.removeEventListener('contextmenu', handleImageContextMenu);
            el.removeEventListener('dragstart', handleImageDragStart);
        }
    });

    // Register global shortcut key blockages on window
    window.addEventListener('keydown', preventDeveloperKeys);
}
