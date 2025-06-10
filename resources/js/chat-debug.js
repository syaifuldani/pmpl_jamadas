// Chat Debug Script untuk troubleshooting masalah cache
console.log('Chat Debug Script loaded');

// Function untuk debug chat functionality
function debugChatSetup() {
    console.log('=== CHAT DEBUG REPORT ===');
    
    // Check elemen-elemen penting
    const chatToggle = document.querySelector('.chat-toggle') || document.getElementById('chatToggleBtn');
    const chatContainer = document.querySelector('.chat-container');
    const chatInput = document.getElementById('chat-input');
    
    console.log('Chat Toggle Element:', chatToggle);
    console.log('Chat Container Element:', chatContainer);
    console.log('Chat Input Element:', chatInput);
    
    if (chatToggle) {
        console.log('Chat Toggle Styles:', window.getComputedStyle(chatToggle));
        console.log('Chat Toggle Position:', chatToggle.getBoundingClientRect());
        console.log('Chat Toggle Z-Index:', window.getComputedStyle(chatToggle).zIndex);
        console.log('Chat Toggle Pointer Events:', window.getComputedStyle(chatToggle).pointerEvents);
        
        // Test click programmatically
        chatToggle.addEventListener('click', function() {
            console.log('Chat toggle clicked successfully!');
        });
    }
    
    // Check if toggleChat function exists
    console.log('toggleChat function exists:', typeof toggleChat === 'function');
    console.log('sendMessage function exists:', typeof sendMessage === 'function');
    
    // Check overlapping elements
    if (chatToggle) {
        const rect = chatToggle.getBoundingClientRect();
        const elementBelow = document.elementFromPoint(rect.x + rect.width/2, rect.y + rect.height/2);
        console.log('Element at chat toggle position:', elementBelow);
        
        if (elementBelow !== chatToggle) {
            console.warn('WARNING: Another element is overlapping the chat toggle:', elementBelow);
        }
    }
    
    console.log('=== END DEBUG REPORT ===');
}

// Auto run debug setelah page load
window.addEventListener('load', function() {
    setTimeout(debugChatSetup, 2000);
});

// Manual debug function yang bisa dipanggil dari console
window.debugChat = debugChatSetup;

// Force enable chat jika ada masalah
window.forceEnableChat = function() {
    console.log('Force enabling chat...');
    
    const chatToggle = document.querySelector('.chat-toggle') || document.getElementById('chatToggleBtn');
    const chatContainer = document.querySelector('.chat-container');
    
    if (chatToggle && chatContainer) {
        // Remove all existing event listeners
        const newToggle = chatToggle.cloneNode(true);
        chatToggle.parentNode.replaceChild(newToggle, chatToggle);
        
        // Add simple click handler
        newToggle.onclick = function() {
            console.log('Force toggle activated');
            chatContainer.classList.toggle('active');
        };
        
        // Add visual feedback
        newToggle.style.background = '#ff4444';
        setTimeout(function() {
            newToggle.style.background = '#4CAF50';
        }, 1000);
        
        console.log('Chat force enabled successfully!');
    } else {
        console.error('Could not find required elements for force enable');
    }
};

console.log('Chat debug utilities loaded. Use debugChat() or forceEnableChat() from console if needed.');
