<!-- Global Toast Container -->
<div id="globalToastContainer" class="fixed z-[60] right-4 top-6 space-y-3 pointer-events-none" aria-live="polite" aria-atomic="true"></div>

<script>
    (function(){
        // Use jQuery if available, otherwise use vanilla DOM
        var $ = window.jQuery || null;

        function createToastElement(type, message, options) {
            options = options || {};
            var id = 'toast-' + Date.now() + '-' + Math.floor(Math.random()*1000);
            var colors = {
                success: 'bg-emerald-600',
                error: 'bg-red-600',
                info: 'bg-blue-600',
                warning: 'bg-yellow-500'
            };
            var icons = {
                success: '<i class="uil uil-check-circle text-white"></i>',
                error: '<i class="uil uil-times-circle text-white"></i>',
                info: '<i class="uil uil-info-circle text-white"></i>',
                warning: '<i class="uil uil-exclamation-triangle text-white"></i>'
            };

            var toast = document.createElement('div');
            toast.id = id;
            toast.className = 'pointer-events-auto max-w-sm w-full rounded-md shadow-lg overflow-hidden ring-1 ring-black ring-opacity-5 transform transition-all duration-300 translate-y-2 opacity-0';
            // allowClose: when false the close button is not rendered and clicking will not remove the toast
            var allowClose = options.allowClose === undefined ? true : !!options.allowClose;
            var allowHtml = options.allowHtml === true;

            var contentHtml = allowHtml ? (message || '') : escapeHtml(message);

            toast.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0 px-4 py-3 ${colors[type] || colors.info} flex items-center justify-center w-12">
                        ${icons[type] || icons.info}
                    </div>
                    <div class="flex-1 px-4 py-3 bg-white">
                        <div class="text-sm text-gray-900">${contentHtml}</div>
                    </div>
                    <div class="flex-shrink-0 px-2 py-2 bg-white">
                        ${allowClose ? '<button class="closeToast text-gray-400 hover:text-gray-700 focus:outline-none">&times;</button>' : ''}
                    </div>
                </div>
            `;

            return toast;
        }

        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;');
        }

        function showToast(type, message, opts) {
            opts = opts || {};
            var container = document.getElementById('globalToastContainer');
            if (!container) return;

            var toast = createToastElement(type, message, opts);
            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(function(){
                toast.classList.remove('translate-y-2','opacity-0');
                toast.classList.add('translate-y-0','opacity-100');
            });

            var dismissAfter = (opts.duration === 0) ? 0 : (opts.duration || 4000);
            var dismissTimer = null;

            function removeToast() {
                toast.classList.remove('translate-y-0','opacity-100');
                toast.classList.add('translate-y-2','opacity-0');
                setTimeout(function(){
                    if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }

            // Close button (only if present and allowed)
            var closeBtn = toast.querySelector('.closeToast');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e){
                    removeToast();
                });
            }

            // Pause on hover
            toast.addEventListener('mouseenter', function(){
                if (dismissTimer) {
                    clearTimeout(dismissTimer);
                    dismissTimer = null;
                }
            });
            toast.addEventListener('mouseleave', function(){
                if (dismissAfter > 0 && !dismissTimer) {
                    dismissTimer = setTimeout(removeToast, dismissAfter);
                }
            });

            // Auto dismiss
            if (dismissAfter > 0) {
                dismissTimer = setTimeout(removeToast, dismissAfter);
            }

            return toast;
        }

        // Expose globally
        window.showToast = showToast;

        // Show server flash messages if present
        document.addEventListener('DOMContentLoaded', function(){
            // Laravel flash messages available via window.__serverToast if set by backend
            if (window.__serverToast && Array.isArray(window.__serverToast)) {
                window.__serverToast.forEach(function(t){
                    showToast(t.type || 'info', t.message || '', t.options || {});
                });
            }
        });
    })();
</script>
