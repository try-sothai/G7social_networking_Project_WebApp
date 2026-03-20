// ក្នុង attachEvents() ត្រង់កន្លែង Render Header
document.querySelectorAll('.call-btn').forEach((btn, index) => {
    btn.onclick = async function() {
        try {
            const isVideo = index === 1; // ប៊ូតុងទី២ ជា Video
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: isVideo, 
                audio: true 
            });
            
            alert(isVideo ? "Starting Video Call..." : "Starting Voice Call...");
            
            // នៅត្រង់នេះ អ្នកត្រូវផ្ញើ Signal ទៅកាន់អ្នកម្ខាងទៀតតាមរយៈ WebSocket
            // ដើម្បីឱ្យគេលោតផ្ទាំង "Incoming Call"
            
        } catch (err) {
            console.error("កាមេរ៉ា ឬមីក្រូហ្វូនត្រូវបានបដិសេធ:", err);
        }
    };
});