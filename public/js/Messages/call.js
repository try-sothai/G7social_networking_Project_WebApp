// This code should be placed where `activeReceiverID` is available,
// likely within the same scope as message.js or passed to it.
// Assuming it's in the same scope for this example.

document.querySelectorAll('.call-btn').forEach((btn, index) => {
    btn.onclick = async function() {
        if (!activeReceiverID) {
            alert('Please select a user to call.');
            return;
        }

        const isVideo = index === 1; // Index 1 for video, 0 for voice
        const callType = isVideo ? 'Video Call' : 'Voice Call';

        try {
            // We can ask for permission first
            const stream = await navigator.mediaDevices.getUserMedia({ video: isVideo, audio: true });
            
            // If permission is granted, send a message to notify the other user.
            const formData = new FormData();
            formData.append('action', 'send');
            formData.append('receiver_id', activeReceiverID);
            formData.append('message', `started a ${callType}.`);
            // We need a new 'type' for calls, let's use 'call'
            formData.append('type', 'call'); 

            fetch(API_PATH, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadMessages(); // Refresh chat to show the call message
                        alert(`Starting ${callType}...`);
                         // Here you would integrate a WebRTC solution to handle the actual call
                        // For now, we just close the stream tracks as we are not using them.
                        stream.getTracks().forEach(track => track.stop());
                    } else {
                        alert('Failed to initiate call. Please try again.');
                    }
                })
                .catch(err => {
                    console.error('Call initiation error:', err);
                    alert('An error occurred while trying to start the call.');
                });

        } catch (err)
 {
            console.error("Camera or microphone permission denied:", err);
            alert("You need to grant camera and microphone access to make calls.");
        }
    };
});
