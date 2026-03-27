document.addEventListener('DOMContentLoaded', function() {
        // Search form auto-submit
        const searchInput = document.querySelector('.search-bar input');
        const searchForm = document.getElementById('searchForm');
        
        if (searchInput && searchForm) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchForm.submit();
                }, 500);
            });
        }
        
        // Join Group functionality
        const joinBtns = document.querySelectorAll('.btn-join:not(.joined)');
        
        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        joinBtns.forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const groupId = this.dataset.groupId;
                const groupCard = this.closest('.group-card');
                const groupName = groupCard.querySelector('.group-name')?.textContent;
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Joining...';
                this.disabled = true;
                
                try {
                    const formData = new FormData();
                    formData.append('group_id', groupId);
                    formData.append('action', 'join');
                    
                    const response = await fetch('/G7social_networking/app/Controllers/Groups/GroupController.php?action=join', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.innerHTML = '<i class="fa-solid fa-check"></i> Joined';
                        this.classList.add('joined');
                        this.disabled = true;
                        groupCard.dataset.isMember = '1';
                        
                        // Update member count
                        const memberSpan = groupCard.querySelector('.group-meta span:first-child');
                        if (memberSpan) {
                            const currentCount = parseInt(memberSpan.textContent.replace(/[^0-9]/g, ''));
                            const newCount = currentCount + 1;
                            memberSpan.innerHTML = `<i class="fa-solid fa-user-group"></i> ${newCount.toLocaleString()} Members`;
                        }
                        
                        showToast(`Successfully joined ${groupName}!`, 'success');
                    } else {
                        throw new Error(data.message || 'Failed to join group');
                    }
                } catch (error) {
                    console.error('Error joining group:', error);
                    this.innerHTML = originalText;
                    this.disabled = false;
                    showToast(error.message || 'Failed to join group. Please try again.', 'error');
                }
            });
        });
    });