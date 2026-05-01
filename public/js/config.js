/**
 * Global Application Configuration
 * This file should be included in all pages before other scripts
 */

// Initialize APP_CONFIG if not already set
if (typeof window.APP_CONFIG === 'undefined') {
    window.APP_CONFIG = {
        baseUrl: '/ss/G7social_networking',
        imagePath: '/ss/G7social_networking/public/uploads/avatars/',
        postsImagePath: '/ss/G7social_networking/public/uploads/avatars/posts/',
        uploadsPath: '/ss/G7social_networking/public/uploads/',
        chatPath: '/ss/G7social_networking/public/uploads/chat/'
    };
}

// Helper function to get full image URL
function getImagePath(imagePath, type = 'default') {
    if (!imagePath) return 'https://via.placeholder.com/50';
    if (imagePath.startsWith('http')) return imagePath;
    
    let basePath = window.APP_CONFIG.imagePath;
    if (type === 'posts') {
        basePath = window.APP_CONFIG.postsImagePath;
    } else if (type === 'chat') {
        basePath = window.APP_CONFIG.chatPath;
    }
    
    return basePath + imagePath.replace(/^\//, '');
}
