
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('shortenForm');
    const submitBtn = document.getElementById('shortenBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const resultContainer = document.getElementById('resultContainer');
    const urlError = document.getElementById('urlError');
    
    // Real-time URL validation
    document.getElementById('original_url').addEventListener('input', function() {
        const url = this.value.trim();
        if (url && !isValidUrl(url)) {
            this.classList.add('border-yellow-500');
            this.classList.remove('border-red-500', 'border-green-500');
        } else if (url && isValidUrl(url)) {
            this.classList.remove('border-yellow-500', 'border-red-500');
            this.classList.add('border-green-500');
        } else {
            this.classList.remove('border-yellow-500', 'border-red-500', 'border-green-500');
        }
    });

    // Custom code validation
    document.getElementById('custom_code').addEventListener('input', function() {
        const code = this.value.trim();
        const hint = document.getElementById('customCodeHint');
        
        if (code && !/^[a-zA-Z0-9]+$/.test(code)) {
            hint.textContent = '❌ Only letters and numbers allowed';
            hint.classList.add('text-red-600');
            hint.classList.remove('text-gray-500');
            this.classList.add('border-red-500');
        } else if (code && (code.length < 4 || code.length > 20)) {
            hint.textContent = '❌ Must be 4-20 characters';
            hint.classList.add('text-red-600');
            hint.classList.remove('text-gray-500');
            this.classList.add('border-red-500');
        } else if (code) {
            hint.textContent = '✅ Valid custom code';
            hint.classList.remove('text-red-600', 'text-gray-500');
            hint.classList.add('text-green-600');
            this.classList.remove('border-red-500');
            this.classList.add('border-green-500');
        } else {
            hint.textContent = '4-20 characters (letters and numbers only)';
            hint.classList.remove('text-red-600', 'text-green-600');
            hint.classList.add('text-gray-500');
            this.classList.remove('border-red-500', 'border-green-500');
        }
    });

    // Form submission via AJAX (only for authenticated users)
    @auth
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const url = document.getElementById('original_url').value.trim();
        
        // Validate URL
        if (!url) {
            showError('Please enter a URL to shorten.');
            return;
        }
        
        if (!isValidUrl(url)) {
            showError('Please enter a valid URL (http:// or https://).');
            return;
        }
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Shortening...';
        submitBtn.classList.add('opacity-50');
        loadingSpinner.classList.remove('hidden');
        resultContainer.classList.add('hidden');
        urlError.classList.add('hidden');
        
        try {
            // Get CSRF token
            const token = document.querySelector('input[name="_token"]').value;
            
            // Get form data
            const formData = new FormData(form);
            
            // Make AJAX request
            const response = await fetch('{{ route("links.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show result
                showResult(data.link.short_url, data.link.short_code);
                
                // Clear form
                document.getElementById('original_url').value = '';
                document.getElementById('custom_code').value = '';
                document.getElementById('expires_at').value = '';
                
                // Update stats
                updateStats();
            } else {
                showError(data.message || 'Something went wrong.');
            }
        } catch (error) {
            showError('The Link is already a ZapLink shortened link. Please try a different URL.');
            console.error('Error:', error);
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Shorten URL';
            submitBtn.classList.remove('opacity-50');
            loadingSpinner.classList.add('hidden');
        }
    });
    @endauth
});

// Helper Functions
function isValidUrl(string) {
    try {
        const url = new URL(string);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (_) {
        return false;
    }
}

function showError(message) {
    const errorEl = document.getElementById('urlError');
    errorEl.textContent = message;
    errorEl.classList.remove('hidden');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        errorEl.classList.add('hidden');
    }, 5000);
}

function showResult(shortUrl, shortCode) {
    const container = document.getElementById('resultContainer');
    const input = document.getElementById('shortUrlResult');
    const link = document.getElementById('resultLink');
    
    input.value = shortUrl;
    link.href = '/' + shortCode;
    
    container.classList.remove('hidden');
    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function copyResult() {
    const input = document.getElementById('shortUrlResult');
    input.select();
    document.execCommand('copy');
    
    const btn = event.target;
    const originalText = btn.textContent;
    btn.textContent = 'Copied!';
    btn.classList.add('bg-green-600');
    setTimeout(() => {
        btn.textContent = originalText;
        btn.classList.remove('bg-green-600');
    }, 2000);
}

function dismissResult() {
    const container = document.getElementById('resultContainer');
    container.classList.add('hidden');
}

async function updateStats() {
    try {
        const response = await fetch('/api/stats');
        const data = await response.json();
        if (data.success) {
            document.getElementById('totalLinks').textContent = data.total_links || 0;
            document.getElementById('totalClicks').textContent = data.total_clicks || 0;
        }
    } catch (error) {
        // Silently fail - stats are cosmetic
    }
}

// Initial stats load
document.addEventListener('DOMContentLoaded', updateStats);
</script>